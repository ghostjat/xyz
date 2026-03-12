<?php

namespace App\Libraries;

use App\Libraries\CourseMapper;
use App\Libraries\CareerClusterMapper;
use App\Libraries\CareerPathGenerator_V2;

class AdvancedCareerEngine {

    public function generateDeepAnalysis($resultsData) {
        $output = [];

        // --- 1. EXTRACT DATA & FORCE LOWERCASE ---
        $raw_r_scores = $resultsData['riasec']['scores']['standardized']['t_scores'] ?? $resultsData['riasec']['scores']['scores'] ?? [];
        $m_scores = $resultsData['mbti']['scores']['breakdown'] ?? $resultsData['mbti']['scores']['standardized']['t_scores'] ?? $resultsData['mbti']['scores']['scores'] ?? [];
        $apt = $resultsData['aptitude']['scores']['standardized']['t_scores'] ?? $resultsData['aptitude']['scores']['scores'] ?? [];
        $gardner = $resultsData['gardner']['scores']['standardized']['t_scores'] ?? $resultsData['gardner']['scores']['scores'] ?? [];
        $output['gardner_scores'] = $gardner;
        $output['learning_styles'] = $resultsData['vark']['scores']['scores'] ?? $resultsData['vark']['scores'] ?? [];
        $output['motivators'] = $resultsData['motivators']['scores']['scores'] ?? $resultsData['motivators']['scores'] ?? [];
        $mbtiType = isset($resultsData['mbti']['trait']) ? strtoupper(trim($resultsData['mbti']['trait'])) : 'XXXX';

        $r_scores = [];
        if (!empty($raw_r_scores)) {
            foreach ($raw_r_scores as $trait => $score) {
                // Critical: Force student traits to lowercase for comparison
                $r_scores[strtolower(trim($trait))] = $score;
            }
        } else {
            $r_scores = ['realistic' => 0, 'investigative' => 0, 'artistic' => 0, 'conventional' => 0, 'enterprising' => 0, 'social' => 0];
        }
        arsort($r_scores);
        $output['riasec_scores'] = $r_scores;

        // --- 2. THE KEY MAPPING FIX ---
        // CRITICAL FIX: The EQ scores are nested one level deeper!
        $eqScores = $resultsData['eq']['scores']['scores'] ?? []; 

        $mappedAptitude = [
            'numerical'      => $apt['Numerical Ability'] ?? $apt['Reasoning'] ?? $apt['numerical'] ?? 0,
            'logical'        => $apt['Logical Reasoning'] ?? $apt['logical'] ?? 0,
            'verbal'         => $apt['Verbal Reasoning'] ?? $apt['verbal'] ?? 0,
            'administrative' => $apt['Accuracy'] ?? $apt['administrative'] ?? 0,
            'spatial'        => $apt['Spatial Ability'] ?? $apt['Spatial Reasoning'] ?? $apt['spatial'] ?? 0,
            'mechanical'     => $apt['Mechanical Ability'] ?? $apt['Mechanical'] ?? $apt['mechanical'] ?? 0,
            
            // Now pulling from the correctly nested $eqScores array
            'leadership'     => $apt['Leadership'] ?? $eqScores['Motivation'] ?? $eqScores['Self-Regulation'] ?? 0,
            'social'         => $apt['Social'] ?? $eqScores['Social Skills'] ?? $eqScores['Empathy'] ?? 0,
        ];

        // --- 3. MBTI PERCENTAGE CALCULATION ---
        $output['mbti_percentages'] = [
            'E' => $this->calcRatio($m_scores['E'] ?? 0, $m_scores['I'] ?? 0, 'E', $mbtiType, 0),
            'S' => $this->calcRatio($m_scores['S'] ?? 0, $m_scores['N'] ?? 0, 'S', $mbtiType, 1),
            'T' => $this->calcRatio($m_scores['T'] ?? 0, $m_scores['F'] ?? 0, 'T', $mbtiType, 2),
            'P' => $this->calcRatio($m_scores['P'] ?? 0, $m_scores['J'] ?? 0, 'P', $mbtiType, 3)
        ];

        // --- 4. SKILLS & ABILITIES MAP ---
        $output['skills'] = [
            'Numerical Ability' => ['score' => $mappedAptitude['numerical'], 'band' => $this->getSkillBand($mappedAptitude['numerical']), 'color' => '#0033cc'],
            'Logical Ability' => ['score' => $mappedAptitude['logical'], 'band' => $this->getSkillBand($mappedAptitude['logical']), 'color' => '#f39c12'],
            'Verbal Ability' => ['score' => $mappedAptitude['verbal'], 'band' => $this->getSkillBand($mappedAptitude['verbal']), 'color' => '#0033cc'],
            'Administrative Skills' => ['score' => $mappedAptitude['administrative'], 'band' => $this->getSkillBand($mappedAptitude['administrative']), 'color' => '#f39c12'],
            'Spatial Ability' => ['score' => $mappedAptitude['spatial'], 'band' => $this->getSkillBand($mappedAptitude['spatial']), 'color' => '#e74c3c'],
            'Leadership Skills' => ['score' => $mappedAptitude['leadership'], 'band' => $this->getSkillBand($mappedAptitude['leadership']), 'color' => '#f39c12'],
            'Social Skills' => ['score' => $mappedAptitude['social'], 'band' => $this->getSkillBand($mappedAptitude['social']), 'color' => '#e74c3c'],
            'Mechanical Abilities' => ['score' => $mappedAptitude['mechanical'], 'band' => $this->getSkillBand($mappedAptitude['mechanical']), 'color' => '#0033cc']
        ];

        // --- 5. OVERALL COGNITIVE SCORE (Dynamic Divisor) ---
        $totalSkills = 0;
        $validSkillsCount = 0;
        
        foreach ($output['skills'] as $skill) {
            // Ignore 0% scores so missing tests don't drag down the student's average
            if ($skill['score'] > 0) {
                $totalSkills += $skill['score'];
                $validSkillsCount++;
            }
        }
        
        // Safely calculate the true average
        $avgSkill = $validSkillsCount > 0 ? round($totalSkills / $validSkillsCount) : 0;
        $output['skills_overall'] = ['score' => $avgSkill, 'band' => $this->getSkillBand($avgSkill)];

        // --- 6. DYNAMIC CLUSTER MATCHING ALGORITHM ---
        // FIX: Now calling the database function, NOT the hardcoded array
        $clusters = CareerClusterMapper::_getClusters();
        $clusterScores = [];
        $topRiasec = array_slice(array_keys($r_scores), 0, 3); // These are now guaranteed lowercase

        foreach ($clusters as $name => $profile) {
            $score = 0;

            // Critical Fix: Force Database Traits to Lowercase for Matching
            $dbRiasec = isset($profile['riasec']) && is_array($profile['riasec']) ? array_map('strtolower', $profile['riasec']) : [];
            $dbMbti = isset($profile['mbti']) && is_array($profile['mbti']) ? array_map('strtoupper', $profile['mbti']) : [];

            // RIASEC Math
            if (in_array($topRiasec[0] ?? '', $dbRiasec))
                $score += 45;
            elseif (count(array_intersect($topRiasec, $dbRiasec)) > 0)
                $score += 25;

            // MBTI Math
            if (in_array($mbtiType, $dbMbti)) {
                $score += 35;
            } else {
                foreach ($dbMbti as $ideal) {
                    $matches = 0;
                    for ($i = 0; $i < 4; $i++) {
                        if (($mbtiType[$i] ?? '') === ($ideal[$i] ?? '-'))
                            $matches++;
                    }
                    if ($matches >= 3) {
                        $score += 15;
                        break;
                    }
                }
            }

            // Aptitude Math (Smart Mapping)
            $skillMatch = 0;
            $totalWeight = 0;
            
            if (isset($profile['aptitude_weights'])) {
                foreach ($profile['aptitude_weights'] as $key => $weight) {
                    $k = strtolower($key);
                    $val = 0;
                    
                    // Smart Keyword Detection (Prevents 0-score disconnects)
                    if (strpos($k, 'numer') !== false || strpos($k, 'math') !== false) {
                        $val = $mappedAptitude['numerical'] ?? 0;
                    } elseif (strpos($k, 'logic') !== false || strpos($k, 'reason') !== false) {
                        $val = $mappedAptitude['logical'] ?? 0;
                    } elseif (strpos($k, 'verb') !== false || strpos($k, 'word') !== false) {
                        $val = $mappedAptitude['verbal'] ?? 0;
                    } elseif (strpos($k, 'admin') !== false || strpos($k, 'accur') !== false) {
                        $val = $mappedAptitude['administrative'] ?? 0;
                    } elseif (strpos($k, 'spat') !== false || strpos($k, 'visual') !== false) {
                        $val = $mappedAptitude['spatial'] ?? 0;
                    } elseif (strpos($k, 'mechan') !== false) {
                        $val = $mappedAptitude['mechanical'] ?? 0;
                    } elseif (strpos($k, 'lead') !== false) {
                        $val = $mappedAptitude['leadership'] ?? 0;
                    } elseif (strpos($k, 'social') !== false || strpos($k, 'empath') !== false) {
                        $val = $mappedAptitude['social'] ?? 0;
                    } else {
                        $val = $mappedAptitude[$k] ?? 0; // Fallback
                    }
                    
                    $skillMatch += ($val * $weight);
                    $totalWeight += $weight;
                }
                
                // Mathematically normalize to exactly 20% of the overall cluster score
                if ($totalWeight > 0) {
                    $score += (($skillMatch / $totalWeight) * 0.20); 
                }
            }
            
            // Gardner Multiple Intelligences Math (Smart Bonus)
            if (!empty($gardner)) {
                $cleanName = strtolower(trim($name));
                $gardnerBonus = 0;

                // Map specific industries to their required Gardner intelligence
                $gMap = [
                    'agriculture'      => 'naturalistic',
                    'environmental'    => 'naturalistic',
                    'science'          => 'logical',
                    'data'             => 'logical',
                    'information'      => 'logical',
                    'arts'             => 'spatial',
                    'architecture'     => 'spatial',
                    'sports'           => 'kinesthetic',
                    'defense'          => 'kinesthetic',
                    'manufacturing'    => 'kinesthetic',
                    'law'              => 'linguistic',
                    'media'            => 'linguistic',
                    'human service'    => 'interpersonal',
                    'hospitality'      => 'interpersonal',
                    'psychology'       => 'intrapersonal',
                    'entrepreneurship' => 'intrapersonal'
                ];

                foreach ($gMap as $keyword => $targetIntelligence) {
                    if (strpos($cleanName, $keyword) !== false) {
                        // Find this intelligence in the student's scores
                        foreach ($gardner as $gTrait => $gScore) {
                            if (strpos(strtolower($gTrait), $targetIntelligence) !== false) {
                                // Add up to 15 bonus points based on their intelligence score
                                $gardnerBonus += ($gScore * 0.15); 
                                break;
                            }
                        }
                    }
                }
                $score += min(15, $gardnerBonus); // Cap bonus at 15 points
            }

            // Ensure cluster score doesn't default to 0 entirely
            $clusterScores[$name] = min(99, round($score)) ?: 1;
        }

        // Sort them from highest to lowest
        arsort($clusterScores);
        
        // STRICT FILTER: Keep only clusters with a meaningful match score (>= 5)
        $filteredClusters = array_filter($clusterScores, function($score) {
            return $score >= 5;
        });
        
        // SAFETY FALLBACK: If the student scored so low that nothing hit 50%, 
        // return just their #1 highest match so the report doesn't break.
        if (empty($filteredClusters) && !empty($clusterScores)) {
            $topKey = array_key_first($clusterScores);
            $filteredClusters[$topKey] = $clusterScores[$topKey];
        }

        // Save the filtered list to the output
        $output['cluster_scores'] = $filteredClusters;

        // --- 7. CAREER PATH INITIATION ---
        $pathGen = new CareerPathGenerator_V2();

        // Pass the mapped, lowercase data to the micro-generator
        $output['career_paths'] = $pathGen->generateRankedPaths(
                $r_scores,
                $mbtiType,
                $mappedAptitude,
                $filteredClusters
        );

        $output['course_recommendations'] = [];
        $output['subject_recommendations'] = [];

        if (!empty($clusterScores)) {
            $topClusters = array_slice(array_keys($clusterScores), 0, 2);
            foreach ($topClusters as $clusterName) {
                $output['course_recommendations'][$clusterName] = CourseMapper::getRecommendations($clusterName);
                $output['subject_recommendations'][$clusterName] = $this->getHighSchoolSubjects($clusterName);
            }
        }
        
        // ========================================================================
        // PHASE 2: ACADEMIC ROADMAP (STREAM SELECTION ALGORITHM)
        // ========================================================================
        $streams = [
            'Science (PCM)' => ['score' => 0, 'desc' => 'Physics, Chemistry, Mathematics. Ideal for Engineering, Architecture, and Data Science.', 'color' => '#2980b9'],
            'Science (PCB)' => ['score' => 0, 'desc' => 'Physics, Chemistry, Biology. Ideal for Medicine, Biotechnology, and Environmental Science.', 'color' => '#27ae60'],
            'Commerce'      => ['score' => 0, 'desc' => 'Business, Accountancy, Economics. Ideal for Finance, Management, and Entrepreneurship.', 'color' => '#f39c12'],
            'Humanities / Arts' => ['score' => 0, 'desc' => 'Psychology, Literature, Political Science. Ideal for Law, Media, Design, and Civil Services.', 'color' => '#8e44ad']
        ];

        // 1. RIASEC Weighting
        $streams['Science (PCM)']['score'] += ($r_scores['investigative'] * 1.5) + ($r_scores['realistic'] * 1.0);
        $streams['Science (PCB)']['score'] += ($r_scores['investigative'] * 1.5) + ($r_scores['social'] * 0.8);
        $streams['Commerce']['score']      += ($r_scores['enterprising'] * 1.5) + ($r_scores['conventional'] * 1.2);
        $streams['Humanities / Arts']['score'] += ($r_scores['artistic'] * 1.5) + ($r_scores['social'] * 1.2);

        // 2. Aptitude & Intelligence Weighting (The Reality Check)
        // If they have high math/logic, boost Science/Commerce. If high verbal/interpersonal, boost Arts.
        $mathLogic = ($mappedAptitude['numerical'] + $mappedAptitude['logical']) / 2;
        $verbalSocial = ($mappedAptitude['verbal'] + $mappedAptitude['social']) / 2;

        $streams['Science (PCM)']['score'] += ($mathLogic * 1.2);
        $streams['Commerce']['score']      += ($mathLogic * 0.8);
        $streams['Humanities / Arts']['score'] += ($verbalSocial * 1.5);
        
        // Add Gardner Naturalistic bonus to Biology
        $naturalistic = 0;
        foreach($gardner as $trait => $val) { if(strpos(strtolower($trait), 'natural') !== false) $naturalistic = $val; }
        $streams['Science (PCB)']['score'] += ($naturalistic * 1.2);

        // 3. Sort and Format
        uasort($streams, function($a, $b) { return $b['score'] <=> $a['score']; });
        
        // Convert to percentages relative to the top score
        $topScore = reset($streams)['score'];
        foreach ($streams as $key => &$data) {
            $data['match_percentage'] = $topScore > 0 ? round(($data['score'] / $topScore) * 100) : 0;
        }
        unset($data); // Prevent reference leak after foreach with &$data

        $output['academic_roadmap'] = $streams;

        return $output;
    }

    private function calcRatio($a, $b, $traitA, $mbtiString, $index) {
        $total = $a + $b;
        if ($total > 0)
            return round(($a / $total) * 100);
        return (isset($mbtiString[$index]) && $mbtiString[$index] === $traitA) ? 75 : 25;
    }

    private function getSkillBand($score) {
        if ($score >= 70)
            return 'Good';
        if ($score >= 45)
            return 'Average';
        if ($score >= 30)
            return 'Fair';
        return 'Improve';
    }

    private function getHighSchoolSubjects($cluster) {
        $cleanCluster = strtolower(trim($cluster));
        $map = [
            /* ===================== AGRICULTURE ===================== */
            'agriculture' => [
                'core'     => ['Biology' => 95, 'Chemistry' => 90], 
                'elective' => ['Geography' => 80, 'Economics' => 75, 'Mathematics' => 70],
                'skill'    => ['Precision Agriculture / Agri-Tech' => 85, 'Environmental Management' => 80]
            ],

            /* ===================== STEM & ENGINEERING ===================== */
            // Included both naming variations to prevent Database 'Default' fallback bugs
            'science, maths and engineering' => [
                'core' => ['Physics' => 95, 'Mathematics' => 95, 'Chemistry' => 85],
                'elective' => ['Computer Science' => 80, 'Statistics' => 75],
                'skill' => ['CAD / Engineering Graphics' => 85, 'Data Analysis' => 80]
            ],
            'science, technology, engineering and mathematics' => [
                'core' => ['Physics' => 95, 'Mathematics' => 95, 'Chemistry' => 85],
                'elective' => ['Computer Science' => 80, 'Statistics' => 75],
                'skill' => ['Data Analysis' => 85, 'Research Methodology' => 80]
            ],
            'data science and artificial intelligence' => [
                'core' => ['Mathematics' => 95, 'Computer Science' => 95],
                'elective' => ['Statistics' => 85, 'Physics' => 75],
                'skill' => ['Python Programming' => 90, 'Machine Learning Basics' => 85]
            ],
            'aviation and aerospace' => [
                'core' => ['Physics' => 95, 'Mathematics' => 90],
                'elective' => ['Computer Science' => 75, 'Geography' => 70],
                'skill' => ['Aerodynamics Basics' => 85, 'Simulation Training' => 80]
            ],
            'energy and utilities' => [
                'core' => ['Physics' => 90, 'Mathematics' => 85],
                'elective' => ['Chemistry' => 75, 'Environmental Science' => 70],
                'skill' => ['Electrical Systems' => 85, 'Renewable Energy Basics' => 80]
            ],

            /* ===================== IT & DIGITAL ===================== */
            'information technology' => [
                'core' => ['Computer Science' => 95, 'Mathematics' => 90],
                'elective' => ['Physics' => 70, 'Information Practices' => 75],
                'skill' => ['Coding (Python/Java)' => 90, 'UI/UX Design' => 75]
            ],
            'cybersecurity' => [
                'core' => ['Computer Science' => 95, 'Mathematics' => 90],
                'elective' => ['Information Practices' => 80, 'Physics' => 70],
                'skill' => ['Network Security' => 90, 'Ethical Hacking Basics' => 85]
            ],
            'blockchain and fintech' => [
                'core' => ['Mathematics' => 95, 'Economics' => 85],
                'elective' => ['Computer Science' => 85, 'Accountancy' => 80],
                'skill' => ['Cryptography Basics' => 85, 'Financial Modeling' => 80]
            ],

            /* ===================== BUSINESS, FINANCE & ADMIN ===================== */
            'business management and administration' => [
                'core' => ['Business Studies' => 95, 'Economics' => 90],
                'elective' => ['Accountancy' => 85, 'Mathematics' => 80],
                'skill' => ['Leadership Skills' => 90, 'Operations Management' => 85]
            ],
            'entrepreneurship and startups' => [
                'core' => ['Business Studies' => 90, 'Economics' => 85],
                'elective' => ['Mathematics' => 75, 'Accountancy' => 75],
                'skill' => ['Startup Strategy' => 90, 'Pitching & Communication' => 85]
            ],
            'e-commerce and digital business' => [
                'core' => ['Business Studies' => 90, 'Mathematics' => 85],
                'elective' => ['Economics' => 80, 'Computer Science' => 75],
                'skill' => ['Digital Marketing' => 85, 'Web Analytics' => 80]
            ],
            'marketing, sales and service' => [
                'core' => ['Business Studies' => 90, 'Economics' => 85],
                'elective' => ['Psychology' => 75, 'Mathematics' => 70],
                'skill' => ['Sales Strategy' => 85, 'Brand Communication' => 80]
            ],
            'accounts and finance' => [
                'core' => ['Accountancy' => 95, 'Mathematics' => 90, 'Economics' => 85],
                'elective' => ['Business Studies' => 80, 'Statistics' => 75],
                'skill' => ['Financial Literacy' => 85, 'Spreadsheet Modeling' => 80]
            ],

            /* ===================== LAW, GOV & PUBLIC ADMIN ===================== */
            'law, public safety and legal services' => [
                'core' => ['Political Science' => 95, 'Literature / English' => 90],
                'elective' => ['History' => 85, 'Sociology' => 80, 'Psychology' => 75],
                'skill' => ['Debate / Public Speaking' => 90, 'Legal Drafting & Logic' => 85]
            ],
            'government and public administration' => [
                'core' => ['Political Science' => 95, 'Economics' => 85],
                'elective' => ['History' => 80, 'Sociology' => 75],
                'skill' => ['Public Policy Analysis' => 85, 'Administrative Skills' => 80]
            ],
            'international relations and diplomacy' => [
                'core' => ['Political Science' => 95, 'History' => 90],
                'elective' => ['Geography' => 80, 'Economics' => 75, 'Foreign Languages' => 85],
                'skill' => ['Foreign Policy Analysis' => 85, 'Negotiation Skills' => 85]
            ],

            /* ===================== HEALTH, SOCIAL & HUMANITIES ===================== */
            'health science' => [
                'core' => ['Biology' => 95, 'Chemistry' => 90, 'Physics' => 80],
                'elective' => ['Psychology' => 75, 'Physical Education' => 65],
                'skill' => ['First Aid / CPR' => 80, 'Health Informatics' => 70]
            ],
            'psychology and behavioral sciences' => [
                'core' => ['Psychology' => 95, 'Biology' => 85],
                'elective' => ['Sociology' => 80, 'Political Science' => 70],
                'skill' => ['Counseling Skills' => 85, 'Behavioral Analysis' => 80]
            ],
            'education and training' => [
                'core' => ['Psychology' => 85, 'Sociology' => 80],
                'elective' => ['History' => 75, 'Literature' => 70],
                'skill' => ['Public Speaking' => 90, 'Lesson Planning' => 80]
            ],
            'human service' => [
                'core' => ['Psychology' => 90, 'Sociology' => 85],
                'elective' => ['Political Science' => 75, 'Foreign Languages' => 70],
                'skill' => ['Community Engagement' => 85, 'Conflict Resolution' => 80]
            ],

            /* ===================== CREATIVE, MEDIA & ARTS ===================== */
            'arts, a/v technology and communication' => [
                'core' => ['Fine Arts' => 95, 'Literature / English' => 85],
                'elective' => ['Psychology' => 75, 'History' => 70],
                'skill' => ['Creative Writing' => 90, 'Video Production' => 85]
            ],
            'media and journalism' => [
                'core' => ['Literature / English' => 95, 'Political Science' => 85],
                'elective' => ['Psychology' => 75, 'History' => 75],
                'skill' => ['News Writing' => 90, 'Public Communication' => 85]
            ],
            'animation and game design' => [
                'core' => ['Computer Science' => 90, 'Fine Arts' => 85],
                'elective' => ['Mathematics' => 75, 'Physics' => 70],
                'skill' => ['Game Engines (Unity)' => 85, 'Storyboarding' => 80]
            ],
            'fashion and lifestyle' => [
                'core' => ['Fine Arts' => 90, 'Home Science' => 80],
                'elective' => ['Business Studies' => 75, 'Psychology' => 70],
                'skill' => ['Design Software (CAD)' => 85, 'Trend Forecasting' => 80]
            ],

            /* ===================== INDUSTRIAL, TRANSPORT & SPORTS ===================== */
            'architecture and construction' => [
                'core' => ['Mathematics' => 90, 'Physics' => 85],
                'elective' => ['Fine Arts' => 80, 'Geography' => 70],
                'skill' => ['Technical Drawing' => 90, '3D Modeling' => 85]
            ],
            'manufacturing' => [
                'core' => ['Physics' => 85, 'Mathematics' => 85],
                'elective' => ['Chemistry' => 70, 'Business Studies' => 65],
                'skill' => ['Industrial Design' => 80, 'Robotics / IoT' => 75]
            ],
            'logistics and transportation' => [
                'core' => ['Business Studies' => 85, 'Mathematics' => 80],
                'elective' => ['Economics' => 75, 'Geography' => 70],
                'skill' => ['Supply Chain Management' => 85, 'Operations Analysis' => 75]
            ],
            'defense and armed forces' => [
                'core' => ['Mathematics' => 90, 'Physics' => 85],
                'elective' => ['Geography' => 75, 'Physical Education' => 80],
                'skill' => ['Strategic Planning' => 85, 'Physical Training' => 90]
            ],
            'sports, fitness and kinesiology' => [
                'core' => ['Physical Education' => 95, 'Biology' => 85],
                'elective' => ['Psychology' => 80, 'Business Studies' => 70],
                'skill' => ['Sports Management' => 85, 'Nutrition Planning' => 80]
            ],
            'hospitality and tourism' => [
                'core' => ['Business Studies' => 85, 'Geography' => 80],
                'elective' => ['Economics' => 75, 'Foreign Languages' => 85],
                'skill' => ['Customer Service' => 90, 'Event Management' => 85]
            ],
            'environmental and sustainability' => [
                'core' => ['Environmental Science' => 95, 'Biology' => 85],
                'elective' => ['Geography' => 80, 'Chemistry' => 75],
                'skill' => ['Sustainability Practices' => 85, 'Climate Analysis' => 80]
            ],

            /* ===================== DEFAULT FALLBACK ===================== */
            'default' => [
                'core' => ['Mathematics' => 80, 'Literature / English' => 80],
                'elective' => ['Economics' => 70, 'Computer Science' => 70],
                'skill' => ['Communication Skills' => 85, 'Digital Literacy' => 80]
            ]
        ];

       // 2. Direct Match Check using the sanitized lowercase key
        if (isset($map[$cleanCluster])) {
            return $map[$cleanCluster];
        }

        // 3. Fuzzy Match Check (In case the database has extra words like "Sector" or "Field")
        foreach ($map as $key => $subjects) {
            if (strpos($cleanCluster, $key) !== false || strpos($key, $cleanCluster) !== false) {
                return $subjects;
            }
        }

        // 4. Ultimate Fallback
        return $map['default'];
    }
}