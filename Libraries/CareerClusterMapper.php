<?php
namespace App\Libraries;

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * CAREER CLUSTER MAPPER - SCIENTIFIC VECTOR MODEL
 * ═══════════════════════════════════════════════════════════════════════════
 * * METHODOLOGY:
 * Uses a weighted matrix to map standardized T-Scores (Mean=50, SD=10) 
 * from RIASEC, Aptitude, and Gardner tests onto the 16 US Career Clusters.
 * * ALGORITHM:
 * Cluster Score = (RIASEC_Weight * 0.60) + (Aptitude_Weight * 0.25) + (Gardner_Weight * 0.15)
 * * @version 2.0.0
 */
class CareerClusterMapper {

    // The 16 US Dept of Education Career Clusters
    const CLUSTERS = [
        'AGR' => 'Agriculture, Food & Natural Resources',
        'ARC' => 'Architecture & Construction',
        'ART' => 'Arts, A/V Technology & Communications',
        'BUS' => 'Business Management & Administration',
        'EDU' => 'Education & Training',
        'FIN' => 'Finance',
        'GOV' => 'Government & Public Administration',
        'HLT' => 'Health Science',
        'HOSP'=> 'Hospitality & Tourism',
        'HUM' => 'Human Services',
        'IT'  => 'Information Technology',
        'LAW' => 'Law, Public Safety, Corrections & Security',
        'MFG' => 'Manufacturing',
        'MKT' => 'Marketing',
        'STEM'=> 'Science, Technology, Engineering & Mathematics',
        'TRN' => 'Transportation, Distribution & Logistics'
    ];

    /**
     * ═══════════════════════════════════════════════════════════════════════
     * MAPPING MATRICES (The "Brain" of the System)
     * ═══════════════════════════════════════════════════════════════════════
     * Values (0.0 to 1.0) represent how much a specific trait contributes 
     * to a cluster. Based on O*NET data patterns.
     */

    // RIASEC WEIGHTS (Primary Driver)
    // R=Realistic, I=Investigative, A=Artistic, S=Social, E=Enterprising, C=Conventional
    private $riasecMap = [
        'AGR' => ['Realistic' => 1.0, 'Investigative' => 0.4, 'Conventional' => 0.3],
        'ARC' => ['Realistic' => 1.0, 'Artistic' => 0.4, 'Investigative' => 0.3],
        'ART' => ['Artistic' => 1.0, 'Enterprising' => 0.3, 'Realistic' => 0.2],
        'BUS' => ['Enterprising' => 1.0, 'Conventional' => 0.8, 'Social' => 0.3],
        'EDU' => ['Social' => 1.0, 'Investigative' => 0.4, 'Artistic' => 0.3],
        'FIN' => ['Conventional' => 1.0, 'Enterprising' => 0.8, 'Investigative' => 0.4],
        'GOV' => ['Enterprising' => 0.7, 'Social' => 0.7, 'Conventional' => 0.6],
        'HLT' => ['Investigative' => 0.9, 'Social' => 0.8, 'Realistic' => 0.4],
        'HOSP'=> ['Enterprising' => 0.8, 'Social' => 0.8, 'Realistic' => 0.3],
        'HUM' => ['Social' => 1.0, 'Enterprising' => 0.4, 'Artistic' => 0.2],
        'IT'  => ['Investigative' => 1.0, 'Realistic' => 0.5, 'Conventional' => 0.4],
        'LAW' => ['Realistic' => 0.7, 'Enterprising' => 0.6, 'Social' => 0.5],
        'MFG' => ['Realistic' => 1.0, 'Conventional' => 0.5, 'Investigative' => 0.3],
        'MKT' => ['Enterprising' => 1.0, 'Artistic' => 0.6, 'Social' => 0.4],
        'STEM'=> ['Investigative' => 1.0, 'Realistic' => 0.6, 'Conventional' => 0.3],
        'TRN' => ['Realistic' => 1.0, 'Conventional' => 0.6, 'Enterprising' => 0.3]
    ];

    // APTITUDE WEIGHTS (Cognitive Capability)
    private $aptitudeMap = [
        'AGR' => ['practical' => 0.8, 'spatial' => 0.4],
        'ARC' => ['spatial' => 1.0, 'numerical' => 0.6],
        'ART' => ['creative' => 1.0, 'spatial' => 0.7],
        'BUS' => ['logical' => 0.7, 'verbal' => 0.6],
        'EDU' => ['verbal' => 0.9, 'logical' => 0.4],
        'FIN' => ['numerical' => 1.0, 'logical' => 0.8],
        'GOV' => ['verbal' => 0.8, 'logical' => 0.6],
        'HLT' => ['analytical' => 0.9, 'verbal' => 0.6],
        'HOSP'=> ['verbal' => 0.7, 'practical' => 0.6],
        'HUM' => ['verbal' => 0.8, 'logical' => 0.3],
        'IT'  => ['logical' => 1.0, 'analytical' => 0.9],
        'LAW' => ['logical' => 0.7, 'practical' => 0.7],
        'MFG' => ['spatial' => 0.7, 'practical' => 0.9],
        'MKT' => ['creative' => 0.8, 'verbal' => 0.8],
        'STEM'=> ['logical' => 1.0, 'numerical' => 0.9],
        'TRN' => ['spatial' => 0.8, 'practical' => 0.8]
    ];

    // GARDNER WEIGHTS (Intrinsic Talent)
    private $gardnerMap = [
        'AGR' => ['Naturalistic' => 1.0, 'Bodily-Kinesthetic' => 0.6],
        'ARC' => ['Spatial' => 0.9, 'Logical-Mathematical' => 0.5],
        'ART' => ['Musical' => 0.5, 'Spatial' => 0.8, 'Linguistic' => 0.4],
        'BUS' => ['Interpersonal' => 0.8, 'Logical-Mathematical' => 0.5],
        'EDU' => ['Interpersonal' => 0.9, 'Linguistic' => 0.7],
        'FIN' => ['Logical-Mathematical' => 1.0, 'Intrapersonal' => 0.4],
        'GOV' => ['Interpersonal' => 0.7, 'Linguistic' => 0.6],
        'HLT' => ['Logical-Mathematical' => 0.7, 'Interpersonal' => 0.6],
        'HOSP'=> ['Interpersonal' => 0.9, 'Bodily-Kinesthetic' => 0.4],
        'HUM' => ['Interpersonal' => 1.0, 'Intrapersonal' => 0.6],
        'IT'  => ['Logical-Mathematical' => 1.0],
        'LAW' => ['Interpersonal' => 0.6, 'Bodily-Kinesthetic' => 0.6],
        'MFG' => ['Bodily-Kinesthetic' => 0.9, 'Spatial' => 0.5],
        'MKT' => ['Interpersonal' => 0.9, 'Linguistic' => 0.6],
        'STEM'=> ['Logical-Mathematical' => 1.0, 'Naturalistic' => 0.3],
        'TRN' => ['Bodily-Kinesthetic' => 0.7, 'Spatial' => 0.8]
    ];

    /**
     * GENERATE CAREER CLUSTER REPORT
     * * @param array $riasecT Standardized T-Scores (Keys: Realistic, etc.)
     * @param array $aptitudeT Standardized T-Scores (Keys: numerical, etc.)
     * @param array $gardnerT Standardized T-Scores (Keys: Spatial, etc.)
     */
    public function generateReport(array $riasecT, array $aptitudeT, array $gardnerT) {
        
        $clusterScores = [];
        
        // 1. Calculate Score for each Cluster
        foreach (self::CLUSTERS as $code => $name) {
            
            // A. RIASEC Contribution (60% Weight)
            $rScore = $this->calculateContribution($this->riasecMap[$code], $riasecT);
            
            // B. Aptitude Contribution (25% Weight)
            $aScore = $this->calculateContribution($this->aptitudeMap[$code], $aptitudeT);
            
            // C. Gardner Contribution (15% Weight)
            $gScore = $this->calculateContribution($this->gardnerMap[$code], $gardnerT);
            
            // D. Weighted Sum
            // Base score of 50 (Average) + deviations
            $finalT = ($rScore * 0.60) + ($aScore * 0.25) + ($gScore * 0.15);
            
            $clusterScores[$code] = round($finalT, 1);
        }

        // 2. Sort Highest to Lowest
        arsort($clusterScores);

        // 3. Validation: Check for Differentiation
        // If all scores are effectively the same (low standard deviation), the test is invalid
        $differentiation = $this->calculateStandardDeviation(array_values($clusterScores));
        $validity = ($differentiation >= 3.0) ? 'Valid' : 'Low Differentiation';

        // 4. Format Output
        $rankedClusters = [];
        $rank = 1;
        foreach ($clusterScores as $code => $score) {
            $fitLevel = 'Moderate Fit';
            if ($score >= 60) $fitLevel = 'Strong Fit';
            if ($score >= 70) $fitLevel = 'Very Strong Fit (Top Match)';
            if ($score < 45)  $fitLevel = 'Low Fit';

            $rankedClusters[] = [
                'rank' => $rank++,
                'code' => $code,
                'name' => self::CLUSTERS[$code],
                'match_score' => $score, // T-Score formatted
                'fit_level' => $fitLevel
            ];
        }

        return [
            'validity_check' => [
                'status' => $validity,
                'differentiation_score' => round($differentiation, 2),
                'message' => $validity === 'Valid' 
                    ? 'Profile shows distinct career preferences.' 
                    : 'Profile is flat. User may lack exposure or interest.'
            ],
            'top_3_clusters' => array_slice($rankedClusters, 0, 3),
            'full_ranking' => $rankedClusters
        ];
    }

    /**
     * Helper: Calculate Weighted Contribution
     */
    private function calculateContribution(array $map, array $userScores): float {
        $totalWeight = 0;
        $weightedSum = 0;
        
        // If map is empty for this test (rare), return neutral T-score
        if (empty($map)) return 50.0;

        foreach ($map as $trait => $weight) {
            // Default to 50 if trait is missing in user scores
            $score = $userScores[$trait] ?? 50; 
            $weightedSum += ($score * $weight);
            $totalWeight += $weight;
        }

        // Return weighted average
        return $totalWeight > 0 ? ($weightedSum / $totalWeight) : 50.0;
    }

    /**
     * Helper: Calculate Standard Deviation (for Validity)
     */
    private function calculateStandardDeviation(array $a): float {
        $n = count($a);
        if ($n === 0) return 0.0;
        $mean = array_sum($a) / $n;
        $carry = 0.0;
        foreach ($a as $val) {
            $d = $val - $mean;
            $carry += $d * $d;
        }
        return sqrt($carry / $n);
    }
}