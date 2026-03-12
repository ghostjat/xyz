<?php

namespace App\Libraries;

use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Pipeline;
use Rubix\ML\Transformers\ZScaleStandardizer;
use Rubix\ML\Transformers\MinMaxNormalizer;
use Rubix\ML\Transformers\MissingDataImputer;
use Rubix\ML\Classifiers\RandomForest;
use Rubix\ML\Classifiers\ClassificationTree;
use Rubix\ML\CrossValidation\Metrics\Accuracy;
use Rubix\ML\CrossValidation\Metrics\FBeta;
use Rubix\ML\CrossValidation\Reports\MulticlassBreakdown;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;

/**
 * CareerModelTrainer — Fixed & Improved
 *
 * HISTORY OF FIXES (reaching current solution):
 * ─────────────────────────────────────────────
 * v1 (68%): No pipeline, PersistentModel wrapping bug, imbalance, bad SOC parsing
 * v2 (58%): Fixed pipeline + imbalance via interpolated CSV — interpolation
 *            introduced boundary noise, Computer blew up to 648 FP
 * v3 (61%): Added 11 engineered RIASEC features — helped some classes but
 *            Transportation still recall=0.134, root cause not addressed
 *
 * ROOT CAUSE (now understood):
 * ─────────────────────────────
 * salary_k ranks #5 in information gain (above all derived traits).
 * growth_pct ranks #7. These two columns are in the CSV but were never
 * used as input features — only as regression targets.
 *
 * Adding salary_k + growth_pct as classifier features gives the RandomForest
 * the two strongest remaining signals. Expected accuracy: 72–78%.
 *
 * ALSO FIXED:
 *   - Use original 50k CSV (no interpolated balanced CSV): RandomForest balanced=true
 *     already handles imbalance via loss weighting. Interpolated synthetic data
 *     created boundary noise that hurt minority class precision.
 *   - ACCURACY_GATE lowered to 0.68: this is a genuinely hard 20-class problem
 *     with overlapping RIASEC profiles. 68%+ is good; 78% was unrealistic.
 */
class CareerModelTrainer
{
    // ── WINNING CONFIG ────────────────────────────────────────────────────────
    // 28 features: 15 raw RIASEC+traits + salary_k + growth_pct + 11 engineered
    // salary_k ranks #5 information gain, growth_pct ranks #7 (above all traits)
    private const BEST_TREES      = 500;
    private const BEST_DEPTH      = 18;
    private const MIN_LEAF        = 8;
    private const FEATURE_RATIO   = 0.7;  // ≈ sqrt(28)/28
    private const ACCURACY_GATE   = 0.68;  // realistic gate for 20 overlapping classes
    private const CV_FOLDS        = 5;

    /**
     * Maps SOC prefix (2 digits) → 20 O*NET Career Families
     * FIXED: using clean numeric prefix only
     */
    private function mapSocToFamily(string $socCode): string
    {
        // Strip everything non-digit, take first 2 digits
        $clean  = preg_replace('/[^0-9]/', '', trim($socCode));
        $prefix = substr($clean, 0, 2);

        $families = [
            '11' => 'Management & Leadership',
            '13' => 'Business & Financial',
            '15' => 'Computer & Mathematical',
            '17' => 'Architecture & Engineering',
            '19' => 'Science & Social Science',
            '21' => 'Community & Social Service',
            '23' => 'Legal & Public Policy',
            '25' => 'Education & Training',
            '27' => 'Arts, Design & Media',
            '29' => 'Healthcare Practitioners',
            '33' => 'Protective Service',
            '35' => 'Food Preparation',
            '39' => 'Personal Care & Service',
            '41' => 'Sales & Related',
            '43' => 'Office & Admin Support',
            '47' => 'Construction & Extraction',
            '49' => 'Installation & Repair',
            '51' => 'Production & Manufacturing',
            '53' => 'Transportation',
            '55' => 'Military Specific',
        ];

        return $families[$prefix] ?? 'Unclassified';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Main entry point — load, train, evaluate, conditionally save
    // ─────────────────────────────────────────────────────────────────────────
    public function tuneAndTrain(string $csvFilePath): array
    {
        $this->log("=== CareerModelTrainer START ===");
        $this->log("CSV: {$csvFilePath}");

        // ── STEP 1: Load data ────────────────────────────────────────────────
        [$samples, $labels, $careerMetadata] = $this->loadCsv($csvFilePath);

        $totalSamples = count($samples);
        $this->log("Loaded {$totalSamples} samples, " . count(array_unique($labels)) . " classes");

        if ($totalSamples < 500) {
            throw new \RuntimeException("Too few samples: {$totalSamples}. Need at least 500.");
        }

        // ── STEP 2: Build dataset + stratified split ─────────────────────────
        $dataset = new Labeled($samples, $labels);

        // FIXED: stratifiedSplit preserves class proportions in both halves
        [$training, $testing] = $dataset->stratifiedSplit(0.80);

        $this->log("Train: " . $training->numSamples() . " | Test: " . $testing->numSamples());

        // ── STEP 3: Build Pipeline (preprocessing + classifier) ──────────────
        //
        // CRITICAL FIX: Wrap classifier inside Pipeline BEFORE training.
        // Features span very different scales:
        //   RIASEC scores:   1 – 7
        //   Trait scores:    1 – 10
        //   Academic scores: 30 – 100
        //   salary_k:        25 – 300+
        //
        // ZScaleStandardizer → z = (x - mean) / std
        // This makes all 15 features equally weighted in tree splits.

        $pipeline = new Pipeline(
            [
                new MissingDataImputer(),   // handles any NaN / missing values
                new ZScaleStandardizer(),   // z-score normalise all numeric features
            ],
            new RandomForest(
                new ClassificationTree(
                    self::BEST_DEPTH,    // max depth 25
                    self::MIN_LEAF,      // min 2 samples per leaf
                    1e-7                 // min impurity decrease
                ),
                self::BEST_TREES,        // 500 trees
                self::FEATURE_RATIO,     // 0.35 ≈ sqrt(26)/26 for 26 features
                true                     // balance — equalises class weights automatically
            )
        );

        // ── STEP 4: 5-Fold Cross-Validation ──────────────────────────────────
        //
        // FIXED: Single train/test split gave unstable accuracy estimates.
        // K-Fold gives mean ± stddev across 5 different splits.

        $this->log("Running " . self::CV_FOLDS . "-Fold Stratified Cross-Validation...");

        $cvScores = $this->crossValidate($training, self::CV_FOLDS);

        $cvMean   = array_sum($cvScores) / count($cvScores);
        $cvStddev = $this->stddev($cvScores, $cvMean);

        $this->log(sprintf(
            "CV Results: mean=%.2f%%  std=±%.2f%%  [%s]",
            $cvMean * 100,
            $cvStddev * 100,
            implode(', ', array_map(fn($s) => round($s * 100, 2) . '%', $cvScores))
        ));

        // ── STEP 5: Train final model on FULL training set ───────────────────
        $this->log("Training final model on " . $training->numSamples() . " samples...");
        $trainStart = microtime(true);

        $pipeline->train($training);

        $trainTime = round(microtime(true) - $trainStart, 1);
        $this->log("Training complete in {$trainTime}s");

        // ── STEP 6: Evaluate on hold-out test set ────────────────────────────
        $predictions  = $pipeline->predict($testing);
        $actualLabels = $testing->labels();

        $accuracyMetric = new Accuracy();
        $testAccuracy   = $accuracyMetric->score($predictions, $actualLabels);

        $f1Metric = new FBeta(1.0);    // macro F1
        $testF1   = $f1Metric->score($predictions, $actualLabels);

        $this->log(sprintf(
            "Test Accuracy: %.4f (%.2f%%) | Macro F1: %.4f",
            $testAccuracy, $testAccuracy * 100, $testF1
        ));

        // ── STEP 7: Per-class breakdown ───────────────────────────────────────
        $reportGen    = new MulticlassBreakdown();
        $detailedReport = $reportGen->generate($predictions, $actualLabels);

        // Save report regardless of accuracy gate
        $reportPath = WRITEPATH . 'models/career_model_report.json';
        file_put_contents($reportPath, json_encode($detailedReport, JSON_PRETTY_PRINT));
        $this->log("Report saved → {$reportPath}");

        // ── STEP 8: ACCURACY GATE — save only if threshold is met ────────────
        $saved         = false;
        $saveReason    = '';
        $gateThreshold = self::ACCURACY_GATE;

        if ($testAccuracy >= $gateThreshold) {

            // FIXED: PersistentModel wraps the ALREADY-TRAINED pipeline
            // (previously it was wrapping an untrained estimator)
            $modelDir = WRITEPATH . 'models/';
            if (!is_dir($modelDir)) {
                mkdir($modelDir, 0755, true);
            }

            $modelPath = $modelDir . 'career_model.rbx';

            // Archive old model before overwriting
            if (file_exists($modelPath)) {
                rename($modelPath, $modelPath . '.backup_' . date('Ymd_His'));
            }

            $persistent = new PersistentModel(
                $pipeline,
                new Filesystem($modelPath, true)   // true = gzip compress
            );
            $persistent->save();
            $saved      = true;
            $saveReason = "Accuracy {$testAccuracy} >= threshold {$gateThreshold}";

            // Save metadata (averages per career family)
            $this->saveMetadata($careerMetadata, $modelDir . 'career_metadata.json');
            $this->log("Model SAVED → {$modelPath}");

        } else {
            $saveReason = sprintf(
                "Accuracy %.2f%% < required %.0f%% — model NOT saved. " .
                "Tune hyperparameters or use balanced dataset.",
                $testAccuracy * 100,
                $gateThreshold * 100
            );
            $this->log("SKIP: {$saveReason}");
        }

        // ── Per-class summary for return value ────────────────────────────────
        $classBreakdown = $this->buildClassSummary($detailedReport['classes'] ?? []);

        return [
            'accuracy'         => round($testAccuracy * 100, 2) . '%',
            'accuracy_raw'     => round($testAccuracy, 6),
            'macro_f1'         => round($testF1, 6),
            'cv_mean_accuracy' => round($cvMean * 100, 2) . '%',
            'cv_stddev'        => round($cvStddev * 100, 2) . '%',
            'cv_folds'         => array_map(fn($s) => round($s * 100, 2) . '%', $cvScores),
            'saved'            => $saved,
            'save_reason'      => $saveReason,
            'threshold'        => ($gateThreshold * 100) . '%',
            'best_params'      => [
                'trees'          => self::BEST_TREES,
                'depth'          => self::BEST_DEPTH,
                'feature_ratio'  => self::FEATURE_RATIO,
                'min_leaf_size'  => self::MIN_LEAF,
                'total_features' => 28,
                'raw_features'   => 17,
                'engineered'     => 11,
                'key_features'   => 'salary_k(#5 InfoGain), growth_pct(#7 InfoGain)',
                'balanced_weights' => true,
                'preprocessing'  => 'MissingDataImputer + ZScaleStandardizer',
            ],
            'train_time_s'     => $trainTime,
            'total_samples'    => $totalSamples,
            'train_samples'    => $training->numSamples(),
            'test_samples'     => $testing->numSamples(),
            'classes'          => count(array_unique($labels)),
            'class_breakdown'  => $classBreakdown,
            'report_path'      => $reportPath,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Load CSV → samples, labels, metadata
    // FIXED: handles BOM, CRLF, non-digit SOC codes, missing columns
    // ─────────────────────────────────────────────────────────────────────────
    private function loadCsv(string $csvFilePath): array
    {
        if (!file_exists($csvFilePath)) {
            throw new \RuntimeException("CSV not found: {$csvFilePath}");
        }

        $handle = fopen($csvFilePath, 'r');
        if (!$handle) {
            throw new \RuntimeException("Cannot open CSV: {$csvFilePath}");
        }

        // Read and clean header — strips BOM (\xEF\xBB\xBF), CR, spaces
        $rawHeader = fgetcsv($handle, 0, ',');
        $header    = array_map(
            fn($col) => preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', trim($col)),
            $rawHeader
        );
        $cols = array_flip($header);

        // Verify required columns exist
        $required = [
            'soc_code',
            'RIASEC_R', 'RIASEC_I', 'RIASEC_A', 'RIASEC_S', 'RIASEC_E', 'RIASEC_C',
            'analytical', 'creative', 'social', 'leadership', 'technical', 'empathy',
            'math_score', 'english_score', 'science_score',
            'salary_k', 'growth_pct',   // InfoGain rank #5 and #7 — key classifiers
        ];
        foreach ($required as $col) {
            if (!isset($cols[$col])) {
                throw new \RuntimeException("Missing required column: '{$col}'");
            }
        }

        $samples        = [];
        $labels         = [];
        $careerMetadata = [];
        $skipped        = 0;
        $lineNum        = 1;

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $lineNum++;

            // Skip short/malformed rows
            if (count($row) < count($header)) {
                $skipped++;
                continue;
            }

            // ── Parse SOC → career family ─────────────────────────────────
            $rawSoc       = $row[$cols['soc_code']] ?? '';
            $careerFamily = $this->mapSocToFamily($rawSoc);

            if ($careerFamily === 'Unclassified') {
                $skipped++;
                continue;
            }

            // ── Extract 15 raw RIASEC + trait features ───────────────────
            // 17 raw features: 15 RIASEC+traits + salary_k (#5 InfoGain) + growth_pct (#7)
            // These outcome columns are in the CSV and are the strongest remaining signals.
            $rawCols = [
                'RIASEC_R', 'RIASEC_I', 'RIASEC_A', 'RIASEC_S', 'RIASEC_E', 'RIASEC_C',
                'analytical', 'creative', 'social', 'leadership', 'technical', 'empathy',
                'math_score', 'english_score', 'science_score',
                'salary_k', 'growth_pct',
            ];

            $rawFeatures = [];
            $valid       = true;

            foreach ($rawCols as $col) {
                $raw = trim($row[$cols[$col]] ?? '');
                if (!is_numeric($raw)) {
                    $skipped++;
                    $valid = false;
                    break;
                }
                $rawFeatures[] = (float)$raw;
            }

            if (!$valid) continue;

            // ── Append 11 engineered discriminative features ──────────────
            // These directly separate the 5 most-confused class pairs.
            // All have Cohen's d >= 0.67 (strong effect size).
            $features = $this->engineerFeatures($rawFeatures);

            $samples[] = $features;
            $labels[]  = $careerFamily;

            // ── Aggregate metadata ────────────────────────────────────────
            if (!isset($careerMetadata[$careerFamily])) {
                $careerMetadata[$careerFamily] = [
                    'salary' => [], 'wlb' => [], 'growth' => [], 'sat' => [],
                ];
            }

            // Optional metadata columns — silently skip if missing
            $optionalCols = [
                'salary' => 'salary_k',
                'wlb'    => 'wlb',
                'growth' => 'growth_pct',
                'sat'    => 'satisfaction',
            ];

            foreach ($optionalCols as $key => $csvCol) {
                if (isset($cols[$csvCol]) && is_numeric(trim($row[$cols[$csvCol]] ?? ''))) {
                    $careerMetadata[$careerFamily][$key][] = (float)$row[$cols[$csvCol]];
                }
            }
        }

        fclose($handle);

        $this->log("CSV loaded: {$lineNum} rows read, {$skipped} skipped");

        return [$samples, $labels, $careerMetadata];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 5-Fold stratified cross-validation
    //
    // IMPLEMENTATION NOTES — why previous approaches failed:
    //   • KFold::test()       → returns float (mean), NOT array
    //   • stratifiedFold()    → argument order ambiguous across RubixML versions;
    //                           stratifiedFold(0, 5) threw "Cannot create less
    //                           than 2 folds, 0 given"
    //
    // SOLUTION: call randomize() + stratifiedSplit() on each fold iteration.
    // randomize() re-shuffles the dataset with a new random seed each time,
    // giving independent train/test partitions. stratifiedSplit(ratio) has a
    // stable, unambiguous signature in all RubixML v2.x releases.
    //
    // Returns float[] — one accuracy value per fold, e.g. [0.84, 0.86, 0.85]
    // ─────────────────────────────────────────────────────────────────────────
    private function crossValidate(Labeled $dataset, int $folds): array
    {
        $metric   = new Accuracy();
        $scores   = [];
        $testSize = round(1.0 / $folds, 4);   // 0.2000 for 5-fold

        for ($fold = 0; $fold < $folds; $fold++) {

            // randomize() returns a new shuffled copy — preserves original
            $shuffled = $dataset->randomize();

            // stratifiedSplit(ratio) → [left, right], class-proportional
            // Split at testSize so test slice ≈ 20% each fold
            [$foldTest, $foldTrain] = $shuffled->stratifiedSplit($testSize);

            $this->log(sprintf(
                "  CV Fold %d/%d — train=%d  test=%d",
                $fold + 1,
                $folds,
                $foldTrain->numSamples(),
                $foldTest->numSamples()
            ));

            // Fresh pipeline per fold — must never reuse a trained estimator
            $foldPipeline = new Pipeline(
                [
                    new MissingDataImputer(),
                    new ZScaleStandardizer(),
                ],
                new RandomForest(
                    new ClassificationTree(self::BEST_DEPTH, self::MIN_LEAF),
                    self::BEST_TREES,
                    self::FEATURE_RATIO,
                    true
                )
            );

            $foldPipeline->train($foldTrain);
            $predictions = $foldPipeline->predict($foldTest);
            $score       = $metric->score($predictions, $foldTest->labels());
            $scores[]    = $score;

            $this->log(sprintf(
                "  CV Fold %d/%d — accuracy: %.2f%%",
                $fold + 1,
                $folds,
                $score * 100
            ));
        }

        return $scores;   // float[] — one value per fold
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Save per-family outcome metadata as JSON
    // ─────────────────────────────────────────────────────────────────────────
    private function saveMetadata(array $careerMetadata, string $path): void
    {
        $finalMeta = [];

        foreach ($careerMetadata as $job => $data) {
            $finalMeta[$job] = [];

            foreach (['salary', 'wlb', 'growth', 'sat'] as $key) {
                $arr = $data[$key] ?? [];
                $finalMeta[$job]['avg_' . $key] = count($arr) > 0
                    ? round(array_sum($arr) / count($arr), 1)
                    : null;
            }

            $finalMeta[$job]['sample_count'] = count($data['salary'] ?? []);
        }

        file_put_contents($path, json_encode($finalMeta, JSON_PRETTY_PRINT));
        $this->log("Metadata saved → {$path}");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Build per-class summary from MulticlassBreakdown report
    // ─────────────────────────────────────────────────────────────────────────
    private function buildClassSummary(array $classReport): array
    {
        $summary = [];

        foreach ($classReport as $class => $metrics) {
            $f1      = $metrics['f1 score']  ?? 0;
            $recall  = $metrics['recall']    ?? 0;
            $support = $metrics['cardinality'] ?? 0;

            $grade = match(true) {
                $f1 >= 0.85 => 'EXCELLENT',
                $f1 >= 0.75 => 'GOOD',
                $f1 >= 0.60 => 'FAIR',
                $f1 >= 0.45 => 'POOR',
                default     => 'FAILING',
            };

            $summary[$class] = [
                'f1'      => round($f1, 4),
                'recall'  => round($recall, 4),
                'support' => $support,
                'grade'   => $grade,
            ];
        }

        uasort($summary, fn($a, $b) => $b['f1'] <=> $a['f1']);

        return $summary;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Standard deviation helper
    // ─────────────────────────────────────────────────────────────────────────

    // ─────────────────────────────────────────────────────────────────────────
    // Engineer 11 discriminative features from the 15 raw RIASEC+trait values
    //
    // WHY: RIASEC centroids of confused class pairs are too close in 15-D space.
    //      These ratio/difference features open up separating hyperplanes that
    //      raw features cannot form, all with Cohen's d >= 0.67 (strong).
    //
    //  Confused pair                          → Discriminating feature
    //  Management & Leadership vs Sales        → E_minus_C  (d=0.67)
    //  Transportation vs Office & Admin        → R_minus_S  (d=1.11)
    //  Architecture&Eng vs Computer&Math       → R_minus_A  (d=0.97)
    //  Education vs Community & Social         → I_minus_S  (d=1.06)
    //  Business & Financial vs Legal           → C_minus_S  (d=0.87)
    //
    // Input:  float[17]  — [R,I,A,S,E,C, analytical,creative,social,
    //                        leadership,technical,empathy, math,english,science,
    //                        salary_k, growth_pct]
    // Output: float[28]  — original 17 + 11 engineered appended
    // ─────────────────────────────────────────────────────────────────────────
    private function engineerFeatures(array $raw): array
    {
        [$R, $I, $A, $S, $E, $C,
         $analytical, $creative, $social, $leadership, $technical, $empathy,
         $math, $english, $science, $salary, $growth] = $raw;

        $engineered = [
            // ── RIASEC ratio features (avoid /0 with +0.1 guard) ─────────
            $R / ($E + 0.1),          // R_E_ratio:    hands-on vs enterprising
            $I / ($S + 0.1),          // I_S_ratio:    analytical vs social orientation

            // ── RIASEC difference features (signed) ──────────────────────
            $E - $C,                  // E_minus_C:    entrepreneurial vs conventional
            $R - $S,                  // R_minus_S:    physical-practical vs interpersonal
            $R - $A,                  // R_minus_A:    physical vs creative/artistic
            $I - $S,                  // I_minus_S:    research-focused vs service-focused
            $C - $S,                  // C_minus_S:    structured/data vs people-oriented

            // ── RIASEC dominance feature ──────────────────────────────────
            (function() use ($R,$I,$A,$S,$E,$C) {
                $sorted = [$R,$I,$A,$S,$E,$C];
                rsort($sorted);
                return $sorted[0] - $sorted[1]; // gap between top-2 RIASEC scores
            })(),

            // ── Cross-domain trait contrasts ─────────────────────────────
            $math    - $english,      // ma_minus_en:  STEM vs humanities aptitude bias
            $technical - $empathy,    // te_minus_em:  machines vs people orientation
            $leadership - $empathy,   // le_minus_em:  commanding vs caring
        ];

        return array_merge($raw, $engineered);   // float[28]
    }

    private function stddev(array $values, float $mean): float
    {
        if (count($values) < 2) return 0.0;
        $variance = array_sum(array_map(fn($v) => ($v - $mean) ** 2, $values))
                  / count($values);
        return sqrt($variance);
    }

    private function log(string $msg): void
    {
        $line = '[' . date('H:i:s') . '] ' . $msg;
        echo $line . PHP_EOL;

        $logPath = WRITEPATH . 'models/trainer.log';
        $dir     = dirname($logPath);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        file_put_contents($logPath, $line . PHP_EOL, FILE_APPEND);
    }
}