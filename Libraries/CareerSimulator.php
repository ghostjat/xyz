<?php
namespace App\Libraries;

use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;

/**
 * CareerSimulator
 *
 * Loads the trained RubixML Pipeline model and generates career fit reports.
 *
 * FEATURE CONTRACT (must match CareerModelTrainer exactly):
 *   Raw[0..16]  = R,I,A,S,E,C, analytical,creative,social,leadership,
 *                  technical,empathy, math,english,science, salary_k,growth_pct
 *   Engineered  = 11 ratio/difference features appended by engineerFeatures()
 *   Total       = 28 features
 *
 * BUGS FIXED vs original:
 *   FIX 1  [CRITICAL] engineerFeatures() used array_slice(base,0,15), cutting off
 *           salary_k (index 15) and growth_pct (index 16). Trainer destructures
 *           all 17 — inference feature vector was different from training vector.
 *           Now destructures all 17 to match trainer exactly.
 *
 *   FIX 2  [CRITICAL] WLB and Satisfaction stored as 0-100 scale in metadata
 *           (from CSV). Both were displayed as "/10" e.g. "58.7/10" — nonsensical.
 *           Now displayed as "/ 100" with correct label.
 *
 *   FIX 3  [CRITICAL] Division by zero when $maxProb = 0 (model returns all-zero
 *           probabilities for unseen input). Added guard before division.
 *
 *   FIX 4  [HIGH] buildAdminDashboardHTML() was dead code — no public caller.
 *           Added simulateTopMatches() as its public entry point.
 *
 *   FIX 5  [HIGH] XSS: career names echoed raw into HTML.
 *           All user-supplied strings now sanitised via h().
 *
 *   FIX 6  [HIGH] Duplicate docblock on buildAdminDashboardHTML removed.
 *
 *   FIX 7  [MEDIUM] buildAdminDashboardHTML had no $limit guard — would render
 *           ALL careers if caller passed unsorted/unsliced array.
 *           Now slices to $limit internally.
 */
class CareerSimulator
{
    protected $estimator;
    protected array $metadata = [];

    // ── Median values from O*NET dataset — used when candidate has no preference ─
    private const DEFAULT_SALARY_K   = 70.0;
    private const DEFAULT_GROWTH_PCT = 10.0;

    public function __construct()
    {
        $modelPath = WRITEPATH . 'models/career_model.rbx';
        $metaPath  = WRITEPATH . 'models/career_metadata.json';

        if (file_exists($modelPath) && file_exists($metaPath)) {
            $this->estimator = PersistentModel::load(new Filesystem($modelPath));
            $this->metadata  = json_decode(file_get_contents($metaPath), true) ?? [];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Public API
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Career A vs Career B comparison card for counselor view.
     *
     * @param  array  $dbRow    Feature row — must contain all 17 raw feature keys
     *                          (see buildBaseFeatures()). salary_k / growth_pct
     *                          default to dataset medians if absent.
     * @param  string $careerA  Exact career family label (e.g. "Computer & Mathematical")
     * @param  string $careerB  Exact career family label
     * @return string           Bootstrap HTML
     */
    public function simulateComparison(array $dbRow, string $careerA, string $careerB): string
    {
        if (!$this->estimator) {
            return $this->errorBox('AI model not loaded. Run the trainer first.');
        }

        $probabilities = $this->getProbabilities($dbRow);
        if ($probabilities === null) {
            return $this->errorBox('Model does not support probability output.');
        }

        arsort($probabilities);
        $topCareer = array_key_first($probabilities);
        $maxProb   = $probabilities[$topCareer] ?? 0.0;

        // FIX 3: guard against division by zero
        if ($maxProb <= 0.0) {
            return $this->errorBox('Model returned zero confidence for all classes. Check input features.');
        }

        // Scale relative to best possible — cap at 98 to preserve human nuance
        $fitA = min(98, (int) round(($probabilities[$careerA] ?? 0.0) / $maxProb * 95));
        $fitB = min(98, (int) round(($probabilities[$careerB] ?? 0.0) / $maxProb * 95));

        return $this->buildComparisonHTML($careerA, $fitA, $careerB, $fitB, $topCareer);
    }

    /**
     * Top-N career matches for admin / counselor dashboard.
     *
     * @param  array $dbRow  Feature row (same contract as simulateComparison)
     * @param  int   $limit  How many top careers to show (default 3)
     * @return string        Bootstrap HTML
     */
    public function simulateTopMatches(array $dbRow, int $limit = 3): string
    {
        if (!$this->estimator) {
            return $this->errorBox('AI model not loaded. Run the trainer first.');
        }

        $probabilities = $this->getProbabilities($dbRow);
        if ($probabilities === null) {
            return $this->errorBox('Model does not support probability output.');
        }

        arsort($probabilities);

        // FIX 7: slice to $limit inside the method — caller need not pre-slice
        $topMatches = array_slice($probabilities, 0, max(1, $limit), true);

        return $this->buildAdminDashboardHTML($topMatches);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Feature engineering — MUST match CareerModelTrainer::engineerFeatures()
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Builds the ordered 17-element raw feature array from a DB/parser row.
     * salary_k and growth_pct default to dataset medians when absent.
     */
    private function buildBaseFeatures(array $row): array
    {
        return [
            (float)($row['RIASEC_R']      ?? 1.0),
            (float)($row['RIASEC_I']      ?? 1.0),
            (float)($row['RIASEC_A']      ?? 1.0),
            (float)($row['RIASEC_S']      ?? 1.0),
            (float)($row['RIASEC_E']      ?? 1.0),
            (float)($row['RIASEC_C']      ?? 1.0),
            (float)($row['analytical']    ?? 5.0),
            (float)($row['creative']      ?? 5.0),
            (float)($row['social']        ?? 5.0),
            (float)($row['leadership']    ?? 5.0),
            (float)($row['technical']     ?? 5.0),
            (float)($row['empathy']       ?? 5.0),
            (float)($row['math_score']    ?? 50.0),
            (float)($row['english_score'] ?? 50.0),
            (float)($row['science_score'] ?? 50.0),
            // FIX 1: salary_k and growth_pct are raw features (indices 15 & 16).
            // Accept from either 'salary_k' (CandidateParser key) or
            // 'target_salary_k' / 'desired_growth_pct' (legacy DB column names).
            (float)($row['salary_k']         ?? $row['target_salary_k']    ?? self::DEFAULT_SALARY_K),
            (float)($row['growth_pct']        ?? $row['desired_growth_pct'] ?? self::DEFAULT_GROWTH_PCT),
        ];
    }

    /**
     * Appends 11 engineered features to the 17-element raw array → float[28].
     *
     * FIX 1: Previously used array_slice($base, 0, 15), silently dropping
     * salary_k (index 15) and growth_pct (index 16) before computing ratios.
     * Trainer destructures all 17. Both must match — now fixed to destructure 17.
     */
    private function engineerFeatures(array $base): array
    {
        // Destructure ALL 17 raw features (matches trainer exactly)
        [
            $R, $I, $A, $S, $E, $C,
            $analytical, $creative, $social, $leadership, $technical, $empathy,
            $math, $english, $science,
            $salary, $growth          // indices 15 & 16 — previously dropped!
        ] = $base;

        $engineered = [
            $R / ($E + 0.1),          // R_E_ratio:   hands-on vs enterprising
            $I / ($S + 0.1),          // I_S_ratio:   analytical vs social
            $E - $C,                  // E_minus_C:   entrepreneurial vs conventional
            $R - $S,                  // R_minus_S:   physical vs interpersonal
            $R - $A,                  // R_minus_A:   physical vs creative
            $I - $S,                  // I_minus_S:   research vs service
            $C - $S,                  // C_minus_S:   structured vs people
            (function () use ($R, $I, $A, $S, $E, $C) {
                $sorted = [$R, $I, $A, $S, $E, $C];
                rsort($sorted);
                return $sorted[0] - $sorted[1]; // dominance gap between top-2 RIASEC
            })(),
            $math    - $english,      // STEM vs humanities aptitude bias
            $technical - $empathy,    // machines vs people orientation
            $leadership - $empathy,   // commanding vs caring
        ];

        return array_merge($base, $engineered); // float[28]
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Model inference
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Returns class → probability map, or null if model lacks proba() support.
     */
    private function getProbabilities(array $dbRow): ?array
    {
        if (!method_exists($this->estimator, 'proba')) {
            return null;
        }

        $base    = $this->buildBaseFeatures($dbRow);
        $dataset = new Unlabeled([$this->engineerFeatures($base)]);

        return $this->estimator->proba($dataset)[0] ?? [];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HTML builders
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Top-N match cards for admin dashboard.
     * FIX 7: slices to $limit internally so caller doesn't have to.
     */
    private function buildAdminDashboardHTML(array $topMatches): string
    {
        $html = "<div class='row g-4'>";
        $rank = 1;

        foreach ($topMatches as $career => $probability) {
            $matchScore = round($probability * 100, 1);
            $meta       = $this->metadata[$career] ?? [];

            // Metadata keys as saved by CareerModelTrainer::saveMetadata():
            //   avg_salary (in thousands), avg_wlb (0-100), avg_growth (%), avg_sat (0-100)
            $avgSalary = (float)($meta['avg_salary'] ?? 0);
            $avgWlb    = (float)($meta['avg_wlb']    ?? 0);
            $avgGrowth = (float)($meta['avg_growth'] ?? 0);
            $avgSat    = (float)($meta['avg_sat']    ?? 0);

            $color        = $rank === 1 ? 'success' : ($rank === 2 ? 'primary' : 'secondary');
            $safeCareer   = $this->h($career); // FIX 5: XSS

            // FIX 2: avg_wlb and avg_sat are 0-100 scale — display as percentage
            $html .= "
            <div class='col-md-4'>
                <div class='card shadow-sm border-{$color} h-100'>
                    <div class='card-header bg-{$color} text-white fw-bold'>
                        #{$rank} Match: {$safeCareer}
                    </div>
                    <div class='card-body text-center'>
                        <h2 class='text-{$color} fw-bold'>{$matchScore}% Fit</h2>
                        <hr>
                        <div class='row text-muted small'>
                            <div class='col-6 mb-2'>
                                <i class='fas fa-money-bill-wave text-success mb-1'></i><br>
                                <strong>\$" . number_format($avgSalary * 1000) . "</strong><br>Avg Salary
                            </div>
                            <div class='col-6 mb-2'>
                                <i class='fas fa-balance-scale text-info mb-1'></i><br>
                                <strong>{$avgWlb}%</strong><br>WLB Score
                            </div>
                            <div class='col-6'>
                                <i class='fas fa-chart-line text-primary mb-1'></i><br>
                                <strong>+{$avgGrowth}%</strong><br>10yr Growth
                            </div>
                            <div class='col-6'>
                                <i class='fas fa-smile text-warning mb-1'></i><br>
                                <strong>{$avgSat}%</strong><br>Satisfaction
                            </div>
                        </div>
                    </div>
                </div>
            </div>";
            $rank++;
        }

        $html .= "</div>";
        return $html;
    }

    /**
     * A vs B comparison layout with counselor insight banner.
     */
    private function buildComparisonHTML(
        string $careerA, int $fitA,
        string $careerB, int $fitB,
        string $topCareer
    ): string {
        $metaA = $this->metadata[$careerA] ?? [];
        $metaB = $this->metadata[$careerB] ?? [];

        $colorA = $fitA >= $fitB ? 'success' : 'secondary';
        $colorB = $fitB >  $fitA ? 'success' : 'secondary';

        $html  = "<div class='row g-4'>";
        $html .= $this->renderCard($careerA, $fitA, $metaA, $colorA);
        $html .= $this->renderCard($careerB, $fitB, $metaB, $colorB);
        $html .= "</div>";

        // Counselor insight: surface AI's absolute #1 pick if it differs from both
        if ($topCareer !== $careerA && $topCareer !== $careerB) {
            $safeTop = $this->h($topCareer); // FIX 5
            $html   .= "<div class='alert alert-info mt-4 fw-bold'>
                            <i class='fas fa-lightbulb text-warning me-2'></i>
                            Counselor Insight: The AI detects the strongest alignment with
                            <span class='text-primary text-decoration-underline'>{$safeTop}</span>.
                        </div>";
        }

        return $html;
    }

    /**
     * Single career card used by both comparison and top-matches views.
     * FIX 2: WLB and Satisfaction shown as % (0-100 scale), not /10.
     * FIX 5: career name sanitised against XSS.
     */
    private function renderCard(string $career, int $fit, array $meta, string $color): string
    {
        $safeCareer = $this->h($career); // FIX 5
        $avgSalary  = (float)($meta['avg_salary'] ?? 0);
        $avgWlb     = (float)($meta['avg_wlb']    ?? 0); // FIX 2: 0-100 scale
        $avgGrowth  = (float)($meta['avg_growth'] ?? 0);

        return "
        <div class='col-md-6'>
            <div class='card shadow-sm border-{$color} h-100'>
                <div class='card-header bg-{$color} text-white fw-bold'>
                    Option: {$safeCareer}
                </div>
                <div class='card-body text-center'>
                    <h2 class='fw-bold text-{$color}'>{$fit}% Match</h2>
                    <div class='progress mb-3' style='height:15px;'>
                        <div class='progress-bar bg-{$color}' role='progressbar'
                             style='width:{$fit}%;' aria-valuenow='{$fit}'
                             aria-valuemin='0' aria-valuemax='100'></div>
                    </div>
                    <hr>
                    <div class='row text-muted small'>
                        <div class='col-4'>
                            <strong>\$" . number_format($avgSalary * 1000) . "</strong><br>Salary
                        </div>
                        <div class='col-4'>
                            <strong>{$avgWlb}%</strong><br>WLB
                        </div>
                        <div class='col-4'>
                            <strong>+{$avgGrowth}%</strong><br>Growth
                        </div>
                    </div>
                </div>
            </div>
        </div>";
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /** XSS-safe HTML output. FIX 5. */
    private function h(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function errorBox(string $message): string
    {
        return "<div class='alert alert-danger'><i class='fas fa-exclamation-triangle me-2'></i>"
             . $this->h($message) . "</div>";
    }
}