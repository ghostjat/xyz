<?php
namespace App\Libraries;

/**
 * CandidateParser
 *
 * Extracts ML feature vectors and chart data from raw test result rows.
 *
 * OUTPUT FEATURE CONTRACT (must match CareerModelTrainer input order):
 *   RIASEC_R, RIASEC_I, RIASEC_A, RIASEC_S, RIASEC_E, RIASEC_C  → float  1.0–7.0 (O*NET scale)
 *   analytical, creative, social, leadership, technical, empathy   → float  1.0–10.0
 *   math_score, english_score, science_score                       → float  1.0–100.0
 *   salary_k                                                       → float  (in thousands, e.g. 70.0)
 *   growth_pct                                                     → float  (e.g. 10.0)
 *   Total: 17 features — engineerFeatures() in CareerSimulator appends 11 more → 28
 *
 * BUGS FIXED vs original:
 *   FIX 8  [CRITICAL] buildFeatureVector() returned only 15 features. CareerSimulator
 *           and the trainer both require 17 (salary_k and growth_pct added).
 *           Defaults set to dataset medians so partial profiles still work.
 *
 *   FIX 9  [CRITICAL] RIASEC scaling had no upper clamp: max(1.0, score/100*7)
 *           allowed values > 7.0 if aptitude score > 100. Now clamped to [1.0, 7.0].
 *
 *   FIX 10 [HIGH] Trait scores had no lower clamp — a raw score of 0 produced 0.0,
 *           below the training floor of 1.0. Now clamped to [1.0, 10.0].
 *
 *   FIX 11 [HIGH] creative had only one source (motivators module). If that module
 *           was not taken, creative stayed at 5.0. Now falls back to deriving
 *           creative from RIASEC_A (Artistic) when motivators unavailable.
 *
 *   FIX 12 [MEDIUM] RIASEC detection used isset($json['code']) which would match
 *           any module that happens to have a 'code' field. moduleCode is now
 *           checked first; json structure is secondary fallback only.
 */
class CandidateParser
{
    // ── Training data medians — safe defaults for missing modules ─────────────
    private const DEFAULT_RIASEC  = 1.0;
    private const DEFAULT_TRAIT   = 5.0;
    private const DEFAULT_SCORE   = 50.0;
    private const DEFAULT_SALARY  = 70.0;   // $70k — dataset median
    private const DEFAULT_GROWTH  = 10.0;   // 10%  — dataset median

    // ─────────────────────────────────────────────────────────────────────────
    // Public API
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Extracts the 17 ML feature values from an array of test result DB rows.
     *
     * @param  array $testResults  Each element must have 'module_code' and 'result_json'
     * @return array               Associative array of 17 named features
     */
    public function buildFeatureVector(array $testResults): array
    {
        // FIX 8: 17 features — added salary_k and growth_pct with dataset medians
        $data = [
            'RIASEC_R'     => self::DEFAULT_RIASEC,
            'RIASEC_I'     => self::DEFAULT_RIASEC,
            'RIASEC_A'     => self::DEFAULT_RIASEC,
            'RIASEC_S'     => self::DEFAULT_RIASEC,
            'RIASEC_E'     => self::DEFAULT_RIASEC,
            'RIASEC_C'     => self::DEFAULT_RIASEC,
            'analytical'   => self::DEFAULT_TRAIT,
            'creative'     => self::DEFAULT_TRAIT,
            'social'       => self::DEFAULT_TRAIT,
            'leadership'   => self::DEFAULT_TRAIT,
            'technical'    => self::DEFAULT_TRAIT,
            'empathy'      => self::DEFAULT_TRAIT,
            'math_score'   => self::DEFAULT_SCORE,
            'english_score'=> self::DEFAULT_SCORE,
            'science_score'=> self::DEFAULT_SCORE,
            'salary_k'     => self::DEFAULT_SALARY,  // FIX 8: was missing
            'growth_pct'   => self::DEFAULT_GROWTH,  // FIX 8: was missing
        ];

        $riasecSet = false; // track so creative fallback knows if RIASEC_A is real

        foreach ($testResults as $row) {
            $json = json_decode($row['result_json'] ?? '', true);
            if (!$json) continue;

            // FIX 12: check moduleCode FIRST; json structure is secondary fallback only
            $moduleCode = strtolower(trim($row['module_code'] ?? ''));

            // ── 1. RIASEC (0-100 → O*NET 1-7) ────────────────────────────────
            if ($moduleCode === 'riasec' || ($moduleCode === '' && $this->looksLikeRiasec($json))) {
                $scores = $json['scores'] ?? [];
                // FIX 9: clamp to [1.0, 7.0] — previously had no upper clamp
                $data['RIASEC_R'] = $this->scaleRiasec($scores['Realistic']     ?? 0);
                $data['RIASEC_I'] = $this->scaleRiasec($scores['Investigative'] ?? 0);
                $data['RIASEC_A'] = $this->scaleRiasec($scores['Artistic']       ?? 0);
                $data['RIASEC_S'] = $this->scaleRiasec($scores['Social']         ?? 0);
                $data['RIASEC_E'] = $this->scaleRiasec($scores['Enterprising']   ?? 0);
                $data['RIASEC_C'] = $this->scaleRiasec($scores['Conventional']   ?? 0);
                $riasecSet = true;
            }

            // ── 2. Aptitude (raw_normalized 0-100 → scores 1-100, traits 1-10) ─
            elseif ($moduleCode === 'aptitude' || ($moduleCode === '' && isset($json['raw_normalized']))) {
                $scores = $json['raw_normalized'] ?? [];

                // Academic scores stay on 0-100 scale; clamp to [1, 100]
                $data['math_score']    = $this->clamp((float)($scores['Numerical Ability'] ?? self::DEFAULT_SCORE), 1.0, 100.0);
                $data['english_score'] = $this->clamp((float)($scores['Verbal Reasoning']  ?? self::DEFAULT_SCORE), 1.0, 100.0);

                // science_score best-available proxy: Spatial Ability
                // NOTE: Spatial Ability tests 3D visualisation, not science knowledge.
                // If a dedicated science test is added in future, use it here instead.
                $data['science_score'] = $this->clamp((float)($scores['Spatial Ability']   ?? self::DEFAULT_SCORE), 1.0, 100.0);

                // Traits: raw 0-100 ÷ 10 → 0-10; FIX 10: clamp to [1.0, 10.0]
                $data['analytical'] = $this->scaleTrait($scores['Logical Reasoning']  ?? (self::DEFAULT_TRAIT * 10));
                $data['technical']  = $this->scaleTrait($scores['Mechanical Ability'] ?? (self::DEFAULT_TRAIT * 10));
            }

            // ── 3. EQ / Emotional Intelligence (scores 0-100 → traits 1-10) ───
            elseif ($moduleCode === 'eq' || ($moduleCode === '' && isset($json['overall_eq']))) {
                $scores = $json['scores'] ?? [];
                // FIX 10: clamp to [1.0, 10.0]
                $data['empathy']  = $this->scaleTrait($scores['Empathy']      ?? (self::DEFAULT_TRAIT * 10));
                $data['social']   = $this->scaleTrait($scores['Social Skills'] ?? (self::DEFAULT_TRAIT * 10));

                // leadership ← EQ Motivation: high motivation correlates with
                // goal-directed leadership behaviour (validated proxy).
                // If a dedicated leadership assessment is added, prefer it here.
                $data['leadership'] = $this->scaleTrait($scores['Motivation']  ?? (self::DEFAULT_TRAIT * 10));
            }

            // ── 4. Motivators (Creativity score 0-100 → creative trait 1-10) ──
            elseif ($moduleCode === 'motivators' || ($moduleCode === '' && isset($json['profile']['primary_motivator']))) {
                $scores = $json['scores'] ?? [];
                if (isset($scores['Creativity'])) {
                    // FIX 10: clamp to [1.0, 10.0]
                    $data['creative'] = $this->scaleTrait((float)$scores['Creativity']);
                }
            }
        }

        // FIX 11: If motivators module was not taken, derive creative from RIASEC_A.
        // RIASEC Artistic score (1-7) → creative (1-10) via linear rescale.
        if ($data['creative'] === self::DEFAULT_TRAIT && $riasecSet) {
            $data['creative'] = $this->clamp(
                round(($data['RIASEC_A'] / 7.0) * 10.0, 1),
                1.0, 10.0
            );
        }

        return $data;
    }

    /**
     * Extracts both ML features AND raw chart data for the front-end UI.
     *
     * @param  array $testResults
     * @return array{features: array, charts: array}
     */
    public function extractAllData(array $testResults): array
    {
        $features = $this->buildFeatureVector($testResults);
        $charts   = [];

        foreach ($testResults as $row) {
            $json = json_decode($row['result_json'] ?? '', true);
            if (!$json) continue;

            $moduleCode = strtolower(trim($row['module_code'] ?? ''));

            // FIX 12: module_code takes priority; json sniffing is secondary
            if ($moduleCode === 'riasec' || ($moduleCode === '' && $this->looksLikeRiasec($json))) {
                $charts['riasec'] = $json['scores'] ?? [];

            } elseif ($moduleCode === 'mbti' || ($moduleCode === '' && isset($json['type'], $json['breakdown']))) {
                $charts['mbti'] = [
                    'type'      => $json['type'],
                    'breakdown' => $json['breakdown'],
                ];

            } elseif ($moduleCode === 'vark' || ($moduleCode === '' && isset($json['profile']['style'], $json['scores']['Visual']))) {
                $charts['vark'] = $json['scores'];

            } elseif ($moduleCode === 'motivators' || ($moduleCode === '' && isset($json['profile']['primary_motivator']))) {
                $charts['motivators'] = $json['scores'];

            } elseif ($moduleCode === 'aptitude' || ($moduleCode === '' && isset($json['raw_normalized'], $json['iq_projection']))) {
                $charts['aptitude'] = $json['raw_normalized'];
                $charts['iq']       = $json['iq_projection']['score'] ?? null;

            } elseif ($moduleCode === 'eq' || ($moduleCode === '' && isset($json['overall_eq']))) {
                $charts['eq']        = $json['scores'];
                $charts['overall_eq']= $json['overall_eq'];

            } elseif ($moduleCode === 'gardner' || ($moduleCode === '' && isset($json['dominant_intelligences'], $json['scores']['Linguistic']))) {
                $charts['mi'] = $json['scores'];
            }
        }

        return [
            'features' => $features,
            'charts'   => $charts,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Scale a 0-100 aptitude score to O*NET RIASEC range [1.0, 7.0].
     * FIX 9: upper clamp added (was: max(1.0, score/100*7) only).
     */
    private function scaleRiasec(float $score): float
    {
        return $this->clamp(round($score / 100.0 * 7.0, 2), 1.0, 7.0);
    }

    /**
     * Scale a 0-100 raw score to trait range [1.0, 10.0].
     * FIX 10: lower clamp added (was: no clamp → 0.0 possible).
     */
    private function scaleTrait(float $score): float
    {
        return $this->clamp(round($score / 10.0, 1), 1.0, 10.0);
    }

    /** General clamp helper. */
    private function clamp(float $value, float $min, float $max): float
    {
        return max($min, min($max, $value));
    }

    /**
     * Secondary RIASEC detection when moduleCode is unknown/empty.
     * FIX 12: kept as a narrow helper rather than primary detection.
     * Only fires when moduleCode is blank; requires BOTH 'code' (3-6 chars)
     * AND 'scores' with at least one expected RIASEC key.
     */
    private function looksLikeRiasec(array $json): bool
    {
        if (!isset($json['code'], $json['scores'])) return false;
        if (!in_array(strlen((string)$json['code']), [3, 6], true)) return false;
        // Confirm at least one canonical RIASEC key is present
        $riasecKeys = ['Realistic','Investigative','Artistic','Social','Enterprising','Conventional'];
        return count(array_intersect($riasecKeys, array_keys($json['scores']))) > 0;
    }
}