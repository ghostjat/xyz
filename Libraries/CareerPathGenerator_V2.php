<?php
namespace App\Libraries;

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * CAREER PATH GENERATOR - MULTI-DIMENSIONAL VALIDATION ENGINE
 * ═══════════════════════════════════════════════════════════════════════════
 * * METHODOLOGY:
 * Generates specific job roles from Career Clusters and validates them using
 * a "3-Point Check" (Interest + Ability + Emotional Fit).
 * * * SCORING ALGORITHM:
 * - Base Score: 100
 * - RIASEC Match: +/- 20 points (Passion/Boredom factor)
 * - Aptitude Match: +/- 30 points (Competence/Incompetence factor)
 * - EQ Match: +/- 20 points (Burnout/Culture Fit factor)
 * * * OUTPUT:
 * Returns ranked careers with specific "Red Flags" or "Green Flags".
 * * @version 3.0.0 (Scientific Triangulation)
 */
class CareerPathGenerator_V2 {

    // ═══════════════════════════════════════════════════════════════════════
    // JOB DATABASE (Sample Data - Expand with O*NET)
    // ═══════════════════════════════════════════════════════════════════════
    // 'code': Holland Code for the specific job
    // 'apt_req': Critical aptitudes (Must be > 45 T-score)
    // 'eq_req': Critical EQ domains (Must be > 45 T-score)
    // 'zone': Job Zone (1-5 Difficulty)
    
    const CAREERS = [
        'IT' => [
            [
                'title' => 'Software Architect',
                'code'  => 'IRE', // Investigative, Realistic, Enterprising
                'apt_req' => ['logical', 'analytical', 'numerical'],
                'eq_req'  => ['self_regulation'], // High pressure
                'zone' => 4
            ],
            [
                'title' => 'IT Project Manager',
                'code'  => 'ECS', // Enterprising, Conventional, Social
                'apt_req' => ['logical', 'verbal'],
                'eq_req'  => ['social_skills', 'empathy', 'motivation'], // Leadership
                'zone' => 4
            ],
            [
                'title' => 'UX/UI Designer',
                'code'  => 'AIR', // Artistic, Investigative, Realistic
                'apt_req' => ['spatial', 'creative', 'practical'],
                'eq_req'  => ['empathy'], // Understanding user pain points
                'zone' => 3
            ]
        ],
        'HLT' => [
            [
                'title' => 'Trauma Surgeon',
                'code'  => 'IRS', // Investigative, Realistic, Social
                'apt_req' => ['spatial', 'practical', 'analytical'],
                'eq_req'  => ['self_regulation', 'self_awareness'], // Stress management
                'zone' => 5
            ],
            [
                'title' => 'Clinical Psychologist',
                'code'  => 'SIA', // Social, Investigative, Artistic
                'apt_req' => ['verbal', 'analytical', 'logical'],
                'eq_req'  => ['empathy', 'social_skills', 'self_awareness'], // Critical
                'zone' => 5
            ],
            [
                'title' => 'Radiology Technician',
                'code'  => 'RIS', // Realistic, Investigative, Social
                'apt_req' => ['practical', 'spatial'],
                'eq_req'  => ['social_skills'], // Patient interaction
                'zone' => 3
            ]
        ],
        'BUS' => [
            [
                'title' => 'Financial Analyst',
                'code'  => 'CIE', // Conventional, Investigative, Enterprising
                'apt_req' => ['numerical', 'analytical', 'logical'],
                'eq_req'  => ['self_regulation'], // Detail oriented focus
                'zone' => 4
            ],
            [
                'title' => 'Human Resources Manager',
                'code'  => 'SEC', // Social, Enterprising, Conventional
                'apt_req' => ['verbal', 'logical'],
                'eq_req'  => ['empathy', 'social_skills', 'self_regulation'], // Conflict resolution
                'zone' => 4
            ]
        ],
        'ART' => [
            [
                'title' => 'Art Director',
                'code'  => 'AE',  // Artistic, Enterprising
                'apt_req' => ['creative', 'spatial', 'verbal'],
                'eq_req'  => ['social_skills', 'motivation'],
                'zone' => 4
            ],
            [
                'title' => 'Technical Writer',
                'code'  => 'AIC', // Artistic, Investigative, Conventional
                'apt_req' => ['verbal', 'analytical'],
                'eq_req'  => ['self_regulation'], // Solitary work
                'zone' => 3
            ]
        ]
        // Add more clusters/jobs as needed...
    ];

    /**
     * GENERATE VALIDATED CAREER PATHS
     * * @param array $topClusters   Array of cluster codes (e.g., ['IT', 'HLT'])
     * @param array $riasecT       User's RIASEC T-Scores (Keys: 'Realistic', etc.)
     * @param array $aptitudeT     User's Aptitude T-Scores (Keys: 'numerical', etc.)
     * @param array $eqT           User's EQ T-Scores (Keys: 'empathy', etc.)
     */
    public function generatePaths(array $topClusters, array $riasecT, array $aptitudeT, array $eqT) {
        
        $finalPaths = [];

        // Map short codes back to full names for RIASEC matching
        $riasecMap = [
            'R' => 'Realistic', 'I' => 'Investigative', 'A' => 'Artistic',
            'S' => 'Social',    'E' => 'Enterprising',  'C' => 'Conventional'
        ];

        foreach ($topClusters as $clusterCode) {
            
            if (!isset(self::CAREERS[$clusterCode])) continue;

            $clusterPaths = [];

            foreach (self::CAREERS[$clusterCode] as $job) {
                
                // ═══════════════════════════════════════════════════════════
                // VALIDATION ALGORITHM
                // ═══════════════════════════════════════════════════════════
                $score = 100;
                $flags = []; // Validation Messages
                $warnings = 0;

                // 1. RIASEC VALIDATION (Interest Fit)
                // Check if user has the specific traits for this job
                // e.g. An IT Manager needs 'Enterprising' (Leadership), not just 'Investigative' (Coding)
                $jobTraits = str_split($job['code']); // ['E', 'C', 'S']
                $interestScore = 0;
                
                foreach ($jobTraits as $traitChar) {
                    $traitName = $riasecMap[$traitChar];
                    $userScore = $riasecT[$traitName] ?? 50;
                    
                    if ($userScore >= 55) $interestScore += 5; // Bonus for interest
                    if ($userScore < 40) {
                        $interestScore -= 10; // Penalty for dislike
                        $flags[] = "Low Interest in " . $traitName . " tasks";
                    }
                }
                $score += $interestScore;


                // 2. APTITUDE VALIDATION (Competence Fit)
                // Can they actually do the work?
                foreach ($job['apt_req'] as $apt) {
                    $userApt = $aptitudeT[$apt] ?? 50;
                    
                    if ($userApt >= 60) {
                        $score += 10; // Strength bonus
                    } elseif ($userApt < 45) {
                        $score -= 20; // Competence Risk
                        $flags[] = "Requires higher $apt ability";
                        $warnings++;
                    }
                }


                // 3. EQ VALIDATION (Culture/Burnout Fit)
                // Can they handle the emotional demands?
                foreach ($job['eq_req'] as $eqDomain) {
                    $userEq = $eqT[$eqDomain] ?? 50;
                    
                    if ($userEq >= 60) {
                        $score += 5; // Emotional Asset
                    } elseif ($userEq < 45) {
                        $score -= 15; // Burnout Risk
                        $flags[] = "Role may be emotionally draining (Low $eqDomain)";
                        $warnings++;
                    }
                }

                // ═══════════════════════════════════════════════════════════
                // FINAL CLASSIFICATION
                // ═══════════════════════════════════════════════════════════
                
                // Fit Label Logic
                $fitLabel = 'Moderate Fit';
                $color = 'orange';
                
                if ($score >= 120) { $fitLabel = 'Perfect Match'; $color = 'green'; }
                elseif ($score >= 100) { $fitLabel = 'Strong Fit'; $color = 'blue'; }
                elseif ($score < 80) { $fitLabel = 'Weak Match'; $color = 'red'; }
                
                // If aptitude warnings exist, downgrade the label regardless of score
                if ($warnings >= 2) {
                    $fitLabel = 'High Risk (Skill Gap)';
                    $color = 'red';
                }

                $clusterPaths[] = [
                    'job_title' => $job['title'],
                    'match_score' => $score,
                    'fit_label' => $fitLabel,
                    'color_code' => $color,
                    'validation_notes' => empty($flags) ? ['No risks identified'] : $flags
                ];
            }

            // Sort jobs within this cluster by score
            usort($clusterPaths, function($a, $b) {
                return $b['match_score'] <=> $a['match_score'];
            });

            $finalPaths[$clusterCode] = $clusterPaths;
        }

        return $finalPaths;
    }
}