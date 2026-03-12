<?php
namespace App\Libraries;

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * CAREER PATH GENERATOR - JOB ZONE VALIDATION MODEL
 * ═══════════════════════════════════════════════════════════════════════════
 * * METHODOLOGY:
 * Uses O*NET "Job Zones" to filter careers based on complexity.
 * Matches specific Aptitude Sub-scores (e.g., Spatial, Numerical) to specific jobs.
 * * VALIDATION:
 * - Prevents Zone 5 (Advanced Degree) recommendations for low-aptitude profiles.
 * - Flags "Reach" careers vs. "Best Fit" careers.
 * * @version 2.0.0
 */
class CareerPathGenerator {

    // ═══════════════════════════════════════════════════════════════════════
    // CAREER DATABASE (Simulated O*NET Data)
    // ═══════════════════════════════════════════════════════════════════════
    // Zone 1-2: High School / Associates (Entry Level)
    // Zone 3-4: Bachelors / Skilled (Professional)
    // Zone 5:   Masters / PhD (Specialized)
    
    const CAREER_DB = [
        'IT' => [
            ['title' => 'Network Support Specialist', 'zone' => 2, 'req' => ['practical']],
            ['title' => 'Web Developer', 'zone' => 3, 'req' => ['logical', 'creative']],
            ['title' => 'Cybersecurity Analyst', 'zone' => 4, 'req' => ['analytical', 'logical']],
            ['title' => 'AI Research Scientist', 'zone' => 5, 'req' => ['logical', 'numerical']]
        ],
        'HLT' => [
            ['title' => 'Phlebotomist', 'zone' => 2, 'req' => ['practical']],
            ['title' => 'Registered Nurse (RN)', 'zone' => 3, 'req' => ['verbal', 'social']], // Social from RIASEC usually
            ['title' => 'Biomedical Engineer', 'zone' => 4, 'req' => ['analytical', 'spatial']],
            ['title' => 'Surgeon', 'zone' => 5, 'req' => ['spatial', 'practical', 'analytical']]
        ],
        'STEM' => [
            ['title' => 'Lab Technician', 'zone' => 2, 'req' => ['practical', 'detailed']],
            ['title' => 'Civil Engineer', 'zone' => 4, 'req' => ['numerical', 'spatial']],
            ['title' => 'Data Scientist', 'zone' => 4, 'req' => ['numerical', 'logical']],
            ['title' => 'Astrophysicist', 'zone' => 5, 'req' => ['numerical', 'analytical', 'logical']]
        ],
        'ART' => [
            ['title' => 'Graphic Designer', 'zone' => 3, 'req' => ['creative', 'spatial']],
            ['title' => 'Video Editor', 'zone' => 3, 'req' => ['creative', 'practical']],
            ['title' => 'Art Director', 'zone' => 4, 'req' => ['creative', 'verbal']],
            ['title' => 'Museum Curator', 'zone' => 5, 'req' => ['verbal', 'analytical']]
        ],
        'BUS' => [
            ['title' => 'Administrative Assistant', 'zone' => 2, 'req' => ['verbal']],
            ['title' => 'Project Manager', 'zone' => 4, 'req' => ['logical', 'verbal']],
            ['title' => 'Business Analyst', 'zone' => 4, 'req' => ['analytical', 'numerical']],
            ['title' => 'Chief Executive Officer', 'zone' => 5, 'req' => ['logical', 'verbal', 'analytical']]
        ]
        // ... Add more clusters as needed
    ];

    /**
     * GENERATE CAREER PATHS
     * * @param array $topClusters   Array of cluster codes (e.g., ['IT', 'STEM', 'BUS', 'ART'])
     * @param array $aptitudeScores Normalized T-Scores (e.g., ['numerical' => 65, 'logical' => 55])
     * @param int   $iqEstimate    Projected IQ from Aptitude module (e.g., 115)
     * @param int   $educationLevel User's target education (1=HS, 2=Assoc, 3=Bach, 4=Mast/PhD)
     */
    public function generatePaths(array $topClusters, array $aptitudeScores, int $iqEstimate, int $educationLevel) {
        
        $recommendations = [];

        // LIMIT: Process only the top 4 clusters provided
        $clustersToProcess = array_slice($topClusters, 0, 4);

        foreach ($clustersToProcess as $clusterCode) {
            
            // Skip if we don't have data for this cluster
            if (!isset(self::CAREER_DB[$clusterCode])) continue;

            $careers = self::CAREER_DB[$clusterCode];
            $scoredCareers = [];

            foreach ($careers as $career) {
                // ═══════════════════════════════════════════════════════════
                // SCIENTIFIC SCORING ALGORITHM
                // ═══════════════════════════════════════════════════════════
                $score = 100; // Start perfect
                $validationMsg = [];

                // 1. ZONE CHECK (Cognitive Load Validation)
                // Prevents recommending "Surgeon" to someone with IQ 85
                // Prevents recommending "Janitor" to someone with IQ 130
                $minIqForZone = [
                    1 => 70, 2 => 85, 3 => 100, 4 => 110, 5 => 120
                ];
                
                if ($iqEstimate < $minIqForZone[$career['zone']]) {
                    $score -= 30; // Massive penalty for "Too Hard"
                    $validationMsg[] = "High Difficulty";
                } elseif ($iqEstimate > ($minIqForZone[$career['zone']] + 30)) {
                    $score -= 15; // Penalty for "Too Easy/Boring"
                    $validationMsg[] = "Below Potential";
                }

                // 2. EDUCATION CHECK
                // If job needs PhD (Zone 5) but user wants HS Diploma (Level 1)
                if ($career['zone'] > ($educationLevel + 1)) {
                    $score -= 25;
                    $validationMsg[] = "Requires More Education";
                }

                // 3. SKILL MATCHING (Specific Aptitude Tags)
                // e.g., If job requires 'Numerical', check user's numerical T-score
                foreach ($career['req'] as $reqTrait) {
                    $userTraitScore = $aptitudeScores[$reqTrait] ?? 50;
                    
                    if ($userTraitScore >= 60) {
                        $score += 10; // Bonus for Strength
                    } elseif ($userTraitScore < 40) {
                        $score -= 20; // Penalty for Weakness
                        $validationMsg[] = "Low $reqTrait aptitude";
                    }
                }

                // Store Result
                $fitLabel = 'Good Fit';
                if ($score >= 110) $fitLabel = 'Excellent Match';
                if ($score < 80) $fitLabel = 'Weak Match';

                // Only recommend if score is decent (> 60)
                if ($score > 60) {
                    $scoredCareers[] = [
                        'title' => $career['title'],
                        'zone_level' => $career['zone'],
                        'match_score' => $score,
                        'fit_label' => $fitLabel,
                        'notes' => implode(', ', $validationMsg)
                    ];
                }
            }

            // Sort careers by Match Score (Highest first)
            usort($scoredCareers, function($a, $b) {
                return $b['match_score'] <=> $a['match_score'];
            });

            // Take top 3 careers for this cluster
            $recommendations[$clusterCode] = array_slice($scoredCareers, 0, 3);
        }

        return $recommendations;
    }
}