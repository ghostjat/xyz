<?php 
namespace App\Libraries;

use App\Models\CareerModel;

class CareerPathGenerator_V2 {

    // ADDED: $topStreamName parameter to provide context for reality-checking
    public function generateRankedPaths($studentRiasec, $studentMbti, $studentAptitude, $studentClusters = [], $topStreamName = 'Any Stream') {
        $rankedPaths = [];
        
        $careerModel = new CareerModel();
        $allCareers = $careerModel->getActiveCareersForEngine();

        // 1. Sanitize Student Data
        $topStudentRiasec = is_array($studentRiasec) ? array_slice(array_keys($studentRiasec), 0, 3) : [];
        $topStudentRiasec = array_values(array_map('strtolower', $topStudentRiasec));
        $studentMbti = strtoupper(trim($studentMbti));
        $studentAptitude = is_array($studentAptitude) ? array_change_key_case($studentAptitude, CASE_LOWER) : [];

        $topClusterNames = is_array($studentClusters) ? array_slice(array_keys($studentClusters), 0, 4) : [];
        $topClusterNames = array_values(array_map('strtolower', $topClusterNames));

        $seenBaseTitles = [];
        
        foreach ($allCareers as $job) {
            
            $rawTitle = trim($job['title']);
            $baseTitle = preg_replace('/^(Junior|Senior|Lead|Chief|Consultant|Assistant|Principal|Trainee|Head of)\s+/i', '', $rawTitle);
            $baseTitle = strtolower(trim($baseTitle));
            
            if (in_array($baseTitle, $seenBaseTitles)) {
                continue; 
            }
            
            $rawRiasec = is_string($job['riasec']) ? json_decode($job['riasec'], true) : $job['riasec'];
            $rawMbti   = is_string($job['mbti']) ? json_decode($job['mbti'], true) : $job['mbti'];
            $rawApt    = is_string($job['aptitudes']) ? json_decode($job['aptitudes'], true) : $job['aptitudes'];
            
            $jobRiasec = is_array($rawRiasec) ? array_values(array_map('strtolower', $rawRiasec)) : [];
            $jobMbti   = is_array($rawMbti) ? array_values(array_map('strtoupper', $rawMbti)) : [];
            $jobAptitudes = is_array($rawApt) ? array_change_key_case($rawApt, CASE_LOWER) : [];
            $jobCluster = strtolower(trim($job['cluster'] ?? 'General')); 

            // ---------------------------------------------------------
            // 2. PSYCHOMETRIC SCORING 
            // ---------------------------------------------------------
            $psyScore = 0;
            
            if (!empty($jobRiasec) && !empty($topStudentRiasec)) {
                if ($jobRiasec[0] === $topStudentRiasec[0]) { $psyScore += 50; } 
                elseif (in_array($jobRiasec[0], $topStudentRiasec)) { $psyScore += 48; } 
                elseif (count(array_intersect($topStudentRiasec, $jobRiasec)) >= 2) { $psyScore += 45; } 
                elseif (count(array_intersect($topStudentRiasec, $jobRiasec)) == 1) { $psyScore += 35; } 
                else { $psyScore += 20; }
            } else { $psyScore += 25; }

            if (!empty($jobMbti) && $studentMbti !== 'XXXX') {
                if (in_array($studentMbti, $jobMbti)) { $psyScore += 50; } 
                else {
                    $bestMatchCount = 0;
                    foreach ($jobMbti as $ideal) {
                        $matches = 0;
                        for ($i=0; $i<4; $i++) { 
                            if (($studentMbti[$i] ?? '') === ($ideal[$i] ?? '-')) $matches++; 
                        }
                        if ($matches > $bestMatchCount) $bestMatchCount = $matches;
                    }
                    if ($bestMatchCount == 3) { $psyScore += 45; }
                    elseif ($bestMatchCount == 2) { $psyScore += 35; }
                    elseif ($bestMatchCount == 1) { $psyScore += 25; }
                    else { $psyScore += 15; }
                }
            } else { $psyScore += 25; }

            // ---------------------------------------------------------
            // 3. APTITUDE LOGIC WITH REALITY CHECK
            // ---------------------------------------------------------
            $skillScore = 0;
            $rawSkillScore = 0; // NEW: Keep track of absolute truth
            
            if (!empty($jobAptitudes)) {
                $totalWeight = 0;
                $earnedScore = 0;
                $rawEarnedScore = 0;
                
                foreach ($jobAptitudes as $aptKey => $weight) {
                    $rawScore = $studentAptitude[$aptKey] ?? 0; 
                    
                    // Keep the commercial floor so passion sorts high
                    $boostedScore = max($rawScore, 40); 
                    
                    $earnedScore += ($boostedScore * $weight);  
                    $rawEarnedScore += ($rawScore * $weight); // Reality tracking
                    $totalWeight += $weight;   
                }
                
                if ($totalWeight > 0) {
                    $skillScore = ($earnedScore / $totalWeight);
                    $rawSkillScore = ($rawEarnedScore / $totalWeight);
                }
            } else {
                $skillScore = 75; 
                $rawSkillScore = 75;
            }
            
            $skillScore = min(99, round($skillScore));
            $rawSkillScore = min(99, round($rawSkillScore));

            // ---------------------------------------------------------
            // 4. CLUSTER MULTIPLIER
            // ---------------------------------------------------------
            $clusterBonus = 0;
            if (in_array($jobCluster, $topClusterNames)) {
                $clusterRank = array_search($jobCluster, $topClusterNames);
                if ($clusterRank === 0) $clusterBonus = 22;      
                elseif ($clusterRank === 1) $clusterBonus = 18;  
                elseif ($clusterRank === 2) $clusterBonus = 14;  
                elseif ($clusterRank === 3) $clusterBonus = 10;  
            }

            // ---------------------------------------------------------
            // 5. NEW: SMART RECOMMENDATION LOGIC (TATA iON / Edumilestones standard)
            // ---------------------------------------------------------
            // Default commercial logic
            $comment = ($psyScore >= 60 && $skillScore >= 45) ? 'Good Choice' : 'Develop';
            
            // THE REALITY CHECK OVERRIDE:
            // If their actual unboosted aptitude is too low, warn them they have a skill gap
            if ($rawSkillScore < 45 && $psyScore >= 60) {
                $comment = 'Develop Skill Gap';
            }

            // THE STREAM CONTEXT OVERRIDE:
            // Prevent Humanities students from getting "Good Choice" on hardcore STEM careers
            $heavyStemClusters = ['science, technology, engineering and mathematics', 'architecture and construction', 'information technology', 'data science and artificial intelligence'];
            if (in_array($jobCluster, $heavyStemClusters) && $topStreamName === 'Humanities / Arts') {
                $comment = 'Challenging Path';
            }
            
            $overallFit = ($psyScore * 0.65) + ($skillScore * 0.35) + $clusterBonus;

            // ---------------------------------------------------------
            // 6. NEW: EXPANDED EXECUTION ROADMAP GENERATOR
            // ---------------------------------------------------------
            $eduData = $job['educational_requirements'] ?? null;
            $rawEdu = is_string($eduData) ? json_decode($eduData, true) : (is_array($eduData) ? $eduData : []);
            
            // Vastly expanded to provide hyper-specific guidance
            $fallbackMap = [
                'health science' => ['stream' => 'Science (PCB)', 'degrees' => ['MBBS', 'BDS', 'B.Sc Nursing', 'B.Pharm'], 'exams' => ['NEET-UG', 'AIIMS', 'State Medical']],
                'information technology' => ['stream' => 'Science (PCM)', 'degrees' => ['B.Tech Computer Science', 'BCA', 'B.Sc IT'], 'exams' => ['JEE Main', 'BITSAT', 'State CET']],
                'data science and artificial intelligence' => ['stream' => 'Science (PCM)', 'degrees' => ['B.Tech AI/Data Science', 'B.Sc Statistics/Data'], 'exams' => ['JEE Main', 'CUET (Maths focus)']],
                'business management and administration' => ['stream' => 'Commerce / Any Stream', 'degrees' => ['BBA', 'BMS', 'B.Com (Hons)'], 'exams' => ['IPMAT', 'CUET', 'NMIMS NPAT']],
                'accounts and finance' => ['stream' => 'Commerce with Maths', 'degrees' => ['B.Com (Hons)', 'CA Foundation', 'BBA Finance'], 'exams' => ['CUET', 'ICAI Exams']],
                'law, public safety and security' => ['stream' => 'Humanities / Commerce', 'degrees' => ['B.A. LLB', 'BBA LLB', 'B.Com LLB'], 'exams' => ['CLAT', 'AILET', 'LSAT India']],
                'science, technology, engineering and mathematics' => ['stream' => 'Science (PCM)', 'degrees' => ['B.Tech', 'B.E.', 'B.Sc (Hons)'], 'exams' => ['JEE Advanced', 'JEE Main', 'State CET']],
                'architecture and construction' => ['stream' => 'Science (PCM)', 'degrees' => ['B.Arch', 'B.Planning'], 'exams' => ['NATA', 'JEE Main Paper 2']],
                'arts, a/v technology and communication' => ['stream' => 'Humanities / Arts', 'degrees' => ['B.A. Mass Comm', 'BFA', 'B.Des'], 'exams' => ['CUET', 'NID DAT', 'University Entrances']],
                'media and journalism' => ['stream' => 'Humanities / Arts', 'degrees' => ['B.A. Journalism', 'BMM (Mass Media)'], 'exams' => ['CUET', 'JMI Entrance', 'IIMC']],
                'animation and game design' => ['stream' => 'Any Stream', 'degrees' => ['B.Sc Animation', 'B.Des Game Design', 'BFA'], 'exams' => ['UCEED', 'NID DAT', 'Portfolio Review']],
                'psychology and behavioral sciences' => ['stream' => 'Humanities / PCB', 'degrees' => ['B.A. Psychology', 'B.Sc Clinical Psychology'], 'exams' => ['CUET', 'University Entrances']],
                'hospitality and tourism' => ['stream' => 'Any Stream', 'degrees' => ['BHM (Hotel Mgmt)', 'BBA Tourism'], 'exams' => ['NCHMCT JEE', 'CUET']],
                'fashion and lifestyle' => ['stream' => 'Any Stream', 'degrees' => ['B.Des Fashion Design', 'B.FTech'], 'exams' => ['NIFT Entrance', 'NID DAT', 'Pearl Entrance']],
            ];

            // Match cluster to fallback, or use an intelligent dynamic default based on the student's top stream
            $fb = $fallbackMap[$jobCluster] ?? [
                'stream' => $topStreamName !== 'Any Stream' ? $topStreamName : 'Relevant Stream in 11th/12th', 
                'degrees' => ['Bachelors in relevant field', 'Specialized Diploma'], 
                'exams' => ['CUET', 'University Specific Entrances']
            ];

            $streamInfo = $rawEdu['stream'] ?? $fb['stream'];
            $degreesInfo = $rawEdu['degrees'] ?? $fb['degrees'];
            $examsInfo = $rawEdu['exams'] ?? $fb['exams'];
            
            $rankedPaths[] = [
                    'title' => ucwords($rawTitle) . ' - ' . ucwords($jobCluster),
                    'roles' => $job['roles'] ?? 'Role details pending...', 
                    'psy' => ['score' => min(99, $psyScore), 'band' => $this->getPsyBand($psyScore)],
                    'skill' => ['score' => $skillScore, 'band' => $this->getSkillBand($skillScore)],
                    'comment' => $comment, 
                    'sort_metric' => $overallFit, 
                    'roadmap' => [
                        'stream' => $streamInfo,
                        'degrees' => is_array($degreesInfo) ? $degreesInfo : [$degreesInfo],
                        'exams' => is_array($examsInfo) ? $examsInfo : [$examsInfo]
                    ]
                ];
            
            $seenBaseTitles[] = $baseTitle;
        }

        usort($rankedPaths, function($a, $b) {
            return $b['sort_metric'] <=> $a['sort_metric'];
        });

        return array_slice($rankedPaths, 0, 45);
    }

    private function getPsyBand($score) {
        if ($score >= 85) return 'Very High';
        if ($score >= 60) return 'High';
        if ($score >= 45) return 'Average';
        return 'Low';
    }

    private function getSkillBand($score) {
        if ($score >= 70) return 'Good';
        if ($score >= 40) return 'Average';
        if ($score >= 35) return 'Fair';
        return 'Improve';
    }
}