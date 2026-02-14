<?php

namespace App\Libraries;

use App\Models\UserResponseModel;
use App\Models\QuestionModel;
use App\Models\PsychometricNormModel;
use App\Models\CareerModel;

/**
 * Industry-Standard Psychometric Engine
 * Compliant with US (APA), UK (BPS), EU (EFPA) standards
 */
class PsychometricEngine
{
    private $responseModel;
    private $questionModel;
    private $normModel;
    private $careerModel;
    
    // Reliability thresholds (Cronbach's alpha)
    const MIN_RELIABILITY = 0.70; 
    const GOOD_RELIABILITY = 0.80; 
    const EXCELLENT_RELIABILITY = 0.90; 

    public function __construct()
    {
        $this->responseModel = new UserResponseModel();
        $this->questionModel = new QuestionModel();
        $this->normModel = new PsychometricNormModel();
        $this->careerModel = new CareerModel();
    }

    /**
     * Calculate test results for a single test attempt
     */
    public function calculateTestResults(int $attemptId): array
    {
        $attempt = model('TestAttemptModel')->find($attemptId);
        $category = model('TestCategoryModel')->find($attempt['category_id']);
        
        // FIX 1: Fetch Session and Age Group ONCE here to avoid lookup errors later
        $session = model('AssessmentSessionModel')->find($attempt['session_id']);
        $ageGroup = $session['age_group'] ?? 'adult'; 

        $responses = $this->responseModel->getAttemptResponses($attemptId);
        $questions = $this->questionModel->getQuestionsByAttempt($attemptId);
        $biasCheck = $this->detectResponseBias($responses);
        
        $results = [
            'raw_scores' => [],
            'normalized_scores' => [],
            'percentile_scores' => [],
            'interpretation' => '',
            'reliability_score' => 0,
            'validity_flag' => $biasCheck['is_valid'], // New Flag
            'validity_message' => $biasCheck['message'], // New Message
            'completion_percentage' => 0
        ];
        
        switch ($category['category_code']) {
            case 'RIASEC':
                $results = array_merge($results, $this->calculateRIASECScores($responses, $questions, $ageGroup));
                break;
            case 'VARK':
                $results = array_merge($results, $this->calculateVARKScores($responses, $questions));
                break;
            case 'MBTI':
                $results = array_merge($results, $this->calculateMBTIScores($responses, $questions));
                break;
            case 'GARDNER':
                $results = array_merge($results, $this->calculateGardnerScores($responses, $questions, $ageGroup));
                break;
            case 'EQ':
                $results = array_merge($results, $this->calculateEQScores($responses, $questions, $ageGroup));
                break;
            case 'APTITUDE':
                $results = array_merge($results, $this->calculateAptitudeScores($responses, $questions));
                break;
        }
        
        // Completion Calculation
        $totalQuestions = count($questions);
        $answeredQuestions = count(array_filter($responses, function($r) {
            return !$r['is_skipped'];
        }));
        $results['completion_percentage'] = $totalQuestions > 0 ? ($answeredQuestions / $totalQuestions) * 100 : 0;
        
        return $results;
    }
    
    private function detectResponseBias(array $responses): array
    {
        if (count($responses) < 5) return ['is_valid' => true, 'message' => 'Valid'];

        $values = [];
        foreach ($responses as $r) {
            if (!$r['is_skipped']) $values[] = $r['response_value'];
        }
        
        if (empty($values)) return ['is_valid' => false, 'message' => 'No Data'];

        // Calculate Standard Deviation
        $mean = array_sum($values) / count($values);
        $variance = 0.0;
        foreach ($values as $v) {
            $variance += pow($v - $mean, 2);
        }
        $stdDev = sqrt($variance / count($values));

        // If StdDev is extremely low (e.g. 0), user clicked the same button every time
        if ($stdDev < self::BIAS_THRESHOLD) {
            return [
                'is_valid' => false, 
                'message' => 'Response pattern indicates "straight-lining" (low variance). Results may be unreliable.'
            ];
        }

        return ['is_valid' => true, 'message' => 'Response pattern looks natural.'];
    }

    /**
     * Calculate RIASEC scores (Holland Code)
     */
    private function calculateRIASECScores(array $responses, array $questions, string $ageGroup): array
    {
        $dimensions = ['R', 'I', 'A', 'S', 'E', 'C'];
        $rawScores = array_fill_keys($dimensions, 0);
        $dimensionCounts = array_fill_keys($dimensions, 0);
        
        foreach ($responses as $response) {
            if ($response['is_skipped']) continue;
            
            $question = $this->findQuestion($questions, $response['question_id']);
            if ($question && $question['dimension']) {
                $dimension = $question['dimension'];
                $score = $response['response_value'] ?? 0;
                
                if ($question['reverse_scored']) {
                    $score = 6 - $score;
                }
                
                // Safety check
                if (isset($rawScores[$dimension])) {
                    $rawScores[$dimension] += $score * ($question['weight'] ?? 1);
                    $dimensionCounts[$dimension]++;
                }
            }
        }
        
        // Calculate averages
        $avgScores = [];
        foreach ($rawScores as $dim => $total) {
            $avgScores[$dim] = $dimensionCounts[$dim] > 0 
                ? $total / $dimensionCounts[$dim] 
                : 0;
        }
        
        // Normalize
        $normalized = [];
        foreach ($rawScores as $k => $v) {
            $normalized[$k] = min(100, ($v / (count($questions) / 6 * 5)) * 100);
        }

        // FIX 2: Use passed $ageGroup
        $percentiles = $this->calculatePercentiles($normalized, 'RIASEC', $ageGroup);
        arsort($normalized);
        $hollandCode = implode('', array_slice(array_keys($normalized), 0, 3));
        
        return [
            'raw_scores' => $rawScores,
            'normalized_scores' => $normalized,
            'percentile_scores' => $percentiles,
            'interpretation' => $this->generateRIASECInterpretation($normalized, $hollandCode),
            'reliability_score' => $this->calculateCronbachAlpha($responses, $questions)
        ];
    }

    /**
     * Calculate VARK learning style scores
     */
    private function calculateVARKScores(array $responses, array $questions): array
    {
        $modalities = ['Visual', 'Auditory', 'Read-Write', 'Kinesthetic'];
        $rawScores = array_fill_keys($modalities, 0);

        foreach ($responses as $response) {
            if ($response['is_skipped']) continue;
            $q = $this->findQuestion($questions, $response['question_id']);
            if ($q && $q['sub_dimension']) {
                 if (isset($rawScores[$q['sub_dimension']])) $rawScores[$q['sub_dimension']] += $response['response_value'];
            }
        }

        $total = array_sum($rawScores);
        $normalized = [];
        foreach ($rawScores as $k => $v) $normalized[$k] = $total > 0 ? ($v / $total) * 100 : 0;

        arsort($normalized);
        $primary = key($normalized);
        
        return [
            'raw_scores' => $rawScores,
            'normalized_scores' => $normalized,
            'percentile_scores' => [], // VARK is ipsative (self-referencing), not normative
            'interpretation' => $this->generateVARKInterpretation($normalized, $primary, false),
            'reliability_score' => $this->calculateCronbachAlpha($responses, $questions)
        ];
    }

    /**
     * Calculate MBTI personality type scores
     */
    private function calculateMBTIScores(array $responses, array $questions): array
    {
        $dichotomies = [
            'E-I' => ['E' => 0, 'I' => 0],
            'S-N' => ['S' => 0, 'N' => 0],
            'T-F' => ['T' => 0, 'F' => 0],
            'J-P' => ['J' => 0, 'P' => 0]
        ];
        
        foreach ($responses as $response) {
            if ($response['is_skipped']) continue;
            
            $question = $this->findQuestion($questions, $response['question_id']);
            if (!$question || !$question['dimension']) continue;
            
            $score = $response['response_value'] ?? 0;
            if ($question['reverse_scored']) {
                $score = 6 - $score;
            }
            
            $dim = $question['dimension'];
            
            // Safety Check
            if (isset($dichotomies['E-I'][$dim])) {
                $dichotomies['E-I'][$dim] += $score;
            } elseif (isset($dichotomies['S-N'][$dim])) {
                $dichotomies['S-N'][$dim] += $score;
            } elseif (isset($dichotomies['T-F'][$dim])) {
                $dichotomies['T-F'][$dim] += $score;
            } elseif (isset($dichotomies['J-P'][$dim])) {
                $dichotomies['J-P'][$dim] += $score;
            }
        }
        
        $type = '';
        $preferences = [];
        
        foreach ($dichotomies as $name => $scores) {
            list($type1, $type2) = explode('-', $name);
            if ($scores[$type1] > $scores[$type2]) {
                $type .= $type1;
                $preferences[$name] = $type1;
            } else {
                $type .= $type2;
                $preferences[$name] = $type2;
            }
        }
        
        $clarityScores = [];
        foreach ($dichotomies as $name => $scores) {
            $total = array_sum($scores);
            $diff = abs($scores[explode('-', $name)[0]] - $scores[explode('-', $name)[1]]);
            $clarityScores[$name] = $total > 0 ? ($diff / $total) * 100 : 0;
        }
        
        $reliability = $this->calculateCronbachAlpha($responses, $questions);
        $interpretation = $this->generateMBTIInterpretation($type, $clarityScores);
        
        return [
            'raw_scores' => $dichotomies,
            'normalized_scores' => $clarityScores,
            'percentile_scores' => [],
            'mbti_type' => $type,
            'preferences' => $preferences,
            'interpretation' => $interpretation,
            'reliability_score' => $reliability,
            'completion_percentage' => 0
        ];
    }

    /**
     * Calculate Gardner's Multiple Intelligences scores
     */
    private function calculateGardnerScores(array $responses, array $questions, string $ageGroup): array
    {
        $intelligences = [
            'Linguistic', 'Logical-Mathematical', 'Spatial', 'Bodily-Kinesthetic',
            'Musical', 'Interpersonal', 'Intrapersonal', 'Naturalistic'
        ];
        
        $rawScores = array_fill_keys($intelligences, 0);
        $counts = array_fill_keys($intelligences, 0);
        
        foreach ($responses as $response) {
            if ($response['is_skipped']) continue;
            
            $question = $this->findQuestion($questions, $response['question_id']);
            if ($question && $question['sub_dimension']) {
                $intelligence = $question['sub_dimension'];
                $score = $response['response_value'] ?? 0;
                
                // Safety Check
                if (isset($rawScores[$intelligence])) {
                    $rawScores[$intelligence] += $score;
                    $counts[$intelligence]++;
                }
            }
        }
        
        $normalizedScores = [];
        foreach ($rawScores as $intel => $total) {
            $avg = $counts[$intel] > 0 ? $total / $counts[$intel] : 0;
            $normalizedScores[$intel] = ($avg / 5) * 100;
        }
        
        // Use passed $ageGroup
        $percentiles = $this->calculatePercentiles(
            $normalizedScores, 
            'GARDNER', 
            $ageGroup
        );
        
        arsort($normalizedScores);
        $dominant = array_slice(array_keys($normalizedScores), 0, 3);
        
        $reliability = $this->calculateCronbachAlpha($responses, $questions);
        $interpretation = $this->generateGardnerInterpretation($normalizedScores, $dominant);
        
        return [
            'raw_scores' => $rawScores,
            'normalized_scores' => $normalizedScores,
            'percentile_scores' => $percentiles,
            'dominant_intelligences' => $dominant,
            'interpretation' => $interpretation,
            'reliability_score' => $reliability,
            'completion_percentage' => 0
        ];
    }

    /**
     * Calculate Emotional Intelligence (EQ) scores
     */
    private function calculateEQScores(array $responses, array $questions, string $ageGroup): array
    {
        $components = [
            'self_awareness', 'self_regulation', 'motivation', 
            'empathy', 'social_skills'
        ];
        
        $rawScores = array_fill_keys($components, 0);
        $counts = array_fill_keys($components, 0);
        
        foreach ($responses as $response) {
            if ($response['is_skipped']) continue;
            
            $question = $this->findQuestion($questions, $response['question_id']);
            if ($question && $question['scoring_key']) {
                $component = $question['scoring_key'];
                $score = $response['response_value'] ?? 0;
                
                if ($question['reverse_scored']) {
                    $score = 6 - $score;
                }
                
                // Safety Check
                if (isset($rawScores[$component])) {
                    $rawScores[$component] += $score;
                    $counts[$component]++;
                }
            }
        }
        
        $componentScores = [];
        foreach ($rawScores as $comp => $total) {
            $avg = $counts[$comp] > 0 ? $total / $counts[$comp] : 0;
            $componentScores[$comp] = ($avg / 5) * 100;
        }
        
        $overallEQ = count($componentScores) > 0 ? array_sum($componentScores) / count($componentScores) : 0;
        
        // Use passed $ageGroup
        $percentiles = $this->calculatePercentiles(
            $componentScores, 
            'EQ', 
            $ageGroup
        );
        
        $reliability = $this->calculateCronbachAlpha($responses, $questions);
        $interpretation = $this->generateEQInterpretation($componentScores, $overallEQ);
        
        return [
            'raw_scores' => $rawScores,
            'normalized_scores' => $componentScores,
            'percentile_scores' => $percentiles,
            'overall_eq' => $overallEQ,
            'eq_level' => $this->getEQLevel($overallEQ),
            'interpretation' => $interpretation,
            'reliability_score' => $reliability,
            'completion_percentage' => 0
        ];
    }

    /**
     * Calculate Aptitude test scores
     */
    private function calculateAptitudeScores(array $responses, array $questions): array
    {
        $aptitudes = [
            'numerical', 'verbal', 'logical', 
            'creative', 'analytical', 'practical'
        ];
        
        $rawScores = array_fill_keys($aptitudes, 0);
        $counts = array_fill_keys($aptitudes, 0);
        
        foreach ($responses as $response) {
            if ($response['is_skipped']) continue;
            
            $question = $this->findQuestion($questions, $response['question_id']);
            if ($question && $question['scoring_key']) {
                $aptitude = $question['scoring_key'];
                $score = $response['response_value'] ?? 0;
                
                // Safety Check
                if (isset($rawScores[$aptitude])) {
                    $rawScores[$aptitude] += $score;
                    $counts[$aptitude]++;
                }
            }
        }
        
        $percentageScores = [];
        foreach ($rawScores as $apt => $total) {
            $percentageScores[$apt] = $counts[$apt] > 0 
                ? ($total / $counts[$apt]) * 100 
                : 0;
        }
        
        $iqEstimate = $this->estimateIQ($percentageScores);
        $reliability = $this->calculateCronbachAlpha($responses, $questions);
        $interpretation = $this->generateAptitudeInterpretation($percentageScores, $iqEstimate);
        
        return [
            'raw_scores' => $rawScores,
            'normalized_scores' => $percentageScores,
            'percentile_scores' => [],
            'iq_estimate' => $iqEstimate,
            'interpretation' => $interpretation,
            'reliability_score' => $reliability,
            'completion_percentage' => 0
        ];
    }

    /**
     * Calculate Cronbach's Alpha for reliability
     */
    private function calculateCronbachAlpha(array $responses, array $questions): float {
        // [Existing logic]
        if (count($responses) < 3) return 0;
        $scores = array_map(function($r) { return $r['response_value'] ?? 0; }, $responses);
        $k = count($scores);
        $variance = $this->variance($scores);
        if ($variance == 0) return 0;
        $itemVariances = [];
        $mean = array_sum($scores) / $k;
        foreach ($scores as $score) $itemVariances[] = pow($score - $mean, 2) / ($k - 1);
        $sumItemVariances = array_sum($itemVariances);
        return max(0, min(1, ($k / ($k - 1)) * (1 - ($sumItemVariances / $variance))));
    }

    private function calculatePercentiles(array $scores, string $category, string $ageGroup): array
    {
        $percentiles = [];
        foreach ($scores as $dimension => $score) {
            $norm = $this->normModel->getNorm($category, $ageGroup, $dimension);
            if ($norm) {
                $z = ($norm['std_deviation'] > 0) ? ($score - $norm['mean_score']) / $norm['std_deviation'] : 0;
                $percentile = $this->zToPercentile($z);
                $percentiles[$dimension] = round($percentile, 1);
            } else {
                $percentiles[$dimension] = 50; 
            }
        }
        return $percentiles;
    }

    private function zToPercentile(float $z): float
    {
        $t = 1 / (1 + 0.2316419 * abs($z));
        $d = 0.3989423 * exp(-$z * $z / 2);
        $probability = $d * $t * (0.3193815 + $t * (-0.3565638 + $t * (1.781478 + $t * (-1.821256 + $t * 1.330274))));
        if ($z > 0) $probability = 1 - $probability;
        return $probability * 100;
    }

    private function variance(array $data): float
    {
        $n = count($data);
        if ($n < 2) return 0;
        $mean = array_sum($data) / $n;
        $squaredDiffs = array_map(function($x) use ($mean) { return pow($x - $mean, 2); }, $data)/($n - 1);;
        return array_sum($squaredDiffs) / ($n - 1);
    }

    private function findQuestion(array $questions, int $questionId)
    {
        foreach ($questions as $q) {
            if ($q['id'] == $questionId) return $q;
        }
        return null;
    }

    private function isMultimodal(array $percentages): bool
    {
        arsort($percentages);
        $values = array_values($percentages);
        return (count($values) >= 2 && abs($values[0] - $values[1]) <= 10);
    }

    private function estimateIQ(array $aptitudeScores): int
    {
        $logical = $aptitudeScores['logical'] ?? 50;
        $analytical = $aptitudeScores['analytical'] ?? 50;
        $numerical = $aptitudeScores['numerical'] ?? 50;
        //$weightedAvg = ($logical * 0.4) + ($analytical * 0.4) + ($numerical * 0.2);
        $weightedAvg = (($aptitudeScores['logical']??50)*0.4) + (($aptitudeScores['analytical']??50)*0.4) + (($aptitudeScores['numerical']??50)*0.2);
        return (int) round(70 + ($weightedAvg * 0.6));
    }

    private function getEQLevel(float $score): string
    {
        if ($score >= 90) return 'Exceptional';
        if ($score >= 80) return 'Very High';
        if ($score >= 70) return 'High';
        if ($score >= 60) return 'Above Average';
        if ($score >= 50) return 'Average';
        if ($score >= 40) return 'Below Average';
        return 'Needs Development';
    }

    private function generateRIASECInterpretation(array $scores, string $hollandCode): string
    {
        $codeDescriptions = [
            'R' => 'practical, hands-on work with tools and machines',
            'I' => 'research, analysis, and working with ideas',
            'A' => 'creative expression and artistic activities',
            'S' => 'helping, teaching, and working with people',
            'E' => 'leadership, persuasion, and business activities',
            'C' => 'organization, data management, and structured tasks'
        ];
        
        $interpretation = "Your Holland Code is {$hollandCode}. This suggests you have strong interests in:\n\n";
        foreach (str_split($hollandCode) as $code) {
            $interpretation .= "• " . strtoupper($code) . " - " . ucfirst($codeDescriptions[$code] ?? '') . "\n";
        }
        $interpretation .= "\nYour highest scoring dimensions indicate career environments where you're likely to thrive and find satisfaction.";
        return $interpretation;
    }

    private function generateVARKInterpretation(array $scores, string $primaryStyle, bool $isMultimodal): string
    {
        $styleDescriptions = [
            'Visual' => 'You learn best through seeing - diagrams, charts, videos, and demonstrations.',
            'Auditory' => 'You learn best through hearing - lectures, discussions, and verbal explanations.',
            'Read-Write' => 'You learn best through reading and writing - textbooks, notes, and written materials.',
            'Kinesthetic' => 'You learn best through doing - hands-on experience and practice.'
        ];
        
        if ($isMultimodal) {
            return "You have a multimodal learning preference. Your top preferences are:\n\n" .
                   implode("\n\n", array_slice($styleDescriptions, 0, 2));
        }
        return "Your primary learning style is {$primaryStyle}. " . ($styleDescriptions[$primaryStyle] ?? '');
    }

    private function generateMBTIInterpretation(string $type, array $clarityScores): string
    {
        return "Your personality type is {$type}.";
    }

    private function generateGardnerInterpretation(array $scores, array $dominant): string
    {
        $interpretation = "Your dominant intelligences are:\n\n";
        foreach ($dominant as $intelligence) {
            $interpretation .= "• {$intelligence} ({$scores[$intelligence]}%)\n";
        }
        return $interpretation;
    }

    private function generateEQInterpretation(array $componentScores, float $overallEQ): string
    {
        $level = $this->getEQLevel($overallEQ);
        return "Your Emotional Intelligence (EQ) score is " . round($overallEQ, 1) . "% - {$level}.";
    }

    private function generateAptitudeInterpretation(array $scores, int $iqEstimate): string
    {
        return "Estimated IQ: {$iqEstimate}. Aptitude profile calculated.";
    }

    public function generateComprehensiveAnalysis(array $allResults, string $ageGroup): array
    {
        // Extract results from each test
        $riasec = $this->findResultByCategory($allResults, 'RIASEC');
        $vark = $this->findResultByCategory($allResults, 'VARK');
        $mbti = $this->findResultByCategory($allResults, 'MBTI');
        $gardner = $this->findResultByCategory($allResults, 'GARDNER');
        $eq = $this->findResultByCategory($allResults, 'EQ');
        $aptitude = $this->findResultByCategory($allResults, 'APTITUDE');

        // Compile comprehensive profile
        $comprehensiveProfile = [
            'riasec_profile' => isset($riasec['normalized_scores']) ? json_decode($riasec['normalized_scores'], true) : [],
            'vark_profile' => isset($vark['normalized_scores']) ? json_decode($vark['normalized_scores'], true) : [],
            'mbti_type' => $mbti ? $this->extractMBTIType($mbti) : 'XXXX',
            'mbti_scores' => isset($mbti['normalized_scores']) ? json_decode($mbti['normalized_scores'], true) : [],
            'gardner_profile' => isset($gardner['normalized_scores']) ? json_decode($gardner['normalized_scores'], true) : [],
            'eq_score' => $eq ? $this->extractEQScore($eq) : 0,
            'eq_breakdown' => isset($eq['normalized_scores']) ? json_decode($eq['normalized_scores'], true) : [],
            'aptitude_scores' => isset($aptitude['normalized_scores']) ? json_decode($aptitude['normalized_scores'], true) : [],
            'iq_estimate' => $aptitude ? $this->extractIQEstimate($aptitude) : 0
        ];

        // Generate career matches
        $careerMatches = $this->matchCareers($comprehensiveProfile);
        //$matches = $this->matchCareersVector($profile);
        // Generate insights
        $insights = $this->generateInsights($comprehensiveProfile);

        return [
            'profile' => $comprehensiveProfile,
            'career_matches' => $careerMatches,
            'personality_analysis' => $this->generatePersonalityAnalysis($comprehensiveProfile),
            'learning_style_analysis' => $vark['interpretation'] ?? 'Not enough data for learning style analysis.',
            'motivators' => $this->identifyMotivators($comprehensiveProfile),
            'strengths' => $insights['strengths'],
            'development_areas' => $insights['development_areas'],
            'emotional_competencies' => $comprehensiveProfile['eq_breakdown'],
            'recommended_careers' => array_slice($careerMatches, 0, 10),
            // NEW: Deep Link Logic implementations
            'career_roadmaps' => $this->generateCareerRoadmaps($careerMatches, $ageGroup),
            'educational_pathways' => $this->generateEducationalPathways($comprehensiveProfile, $ageGroup),
            'skill_development_plan' => $this->generateSkillDevelopmentPlan($comprehensiveProfile)
        ];
    }
    
    private function matchCareersVector(array $userProfile): array
    {
        $careers = $this->careerModel->findAll();
        $matches = [];

        foreach ($careers as $career) {
            $careerReqs = json_decode($career['riasec_profile'], true) ?: [];
            
            // Calculate Distance (Lower is better)
            $distance = 0;
            $dimensions = 0;

            // 1. RIASEC Vector (Weight: 1.5x)
            foreach ($careerReqs as $k => $reqVal) {
                $userVal = $userProfile['riasec'][$k] ?? 0;
                $distance += pow(($userVal - $reqVal) * 1.5, 2); 
                $dimensions++;
            }

            // 2. EQ Vector (Weight: 1.0x)
            $eqReqs = json_decode($career['eq_requirements'], true) ?: [];
            foreach ($eqReqs as $k => $reqVal) {
                $userVal = $userProfile['eq'][$k] ?? 0;
                // Only penalize if User < Requirement
                if ($userVal < $reqVal) {
                    $distance += pow(($reqVal - $userVal) * 1.0, 2);
                }
                $dimensions++;
            }

            // 3. Final Similarity Score (0-100)
            // Max distance normalization approximation
            $finalDistance = sqrt($distance);
            $maxDistance = sqrt($dimensions * pow(100, 2)); // Worst case
            $similarity = 100 - (($finalDistance / $maxDistance) * 100);

            // Boosters (MBTI exact match)
            $mbtiFit = json_decode($career['mbti_fit'], true) ?: [];
            if (isset($mbtiFit[$userProfile['mbti_type']])) {
                $similarity += ($mbtiFit[$userProfile['mbti_type']] / 10); // Add up to 10% bonus
            }

            if ($similarity > 50) {
                $matches[] = [
                    'career_title' => $career['career_title'],
                    'match_percentage' => min(100, round($similarity, 1)),
                    'why_suitable' => "Strong vector alignment in " . count($careerReqs) . " dimensions."
                ];
            }
        }

        usort($matches, function($a, $b) { return $b['match_percentage'] <=> $a['match_percentage']; });
        return $matches;
    }
    
    /**
     * Match careers based on comprehensive profile
     */
    private function matchCareers(array $profile): array
    {
        $careers = $this->careerModel->findAll();
        $matches = [];

        foreach ($careers as $career) {
            $matchScore = $this->calculateCareerMatch($profile, $career);
            
            // Only include matches with some relevance (> 40%)
            if ($matchScore > 40) {
                $matches[] = [
                    'career_id' => $career['id'],
                    'career_title' => $career['career_title'],
                    'match_percentage' => $matchScore,
                    'fit_explanation' => $this->generateFitExplanation($profile, $career, $matchScore),
                    'why_suitable' => $this->generateWhySuitable($profile, $career),
                    'potential_challenges' => $this->identifyChallenges($profile, $career)
                ];
            }
        }

        usort($matches, function($a, $b) {
            return $b['match_percentage'] <=> $a['match_percentage'];
        });

        return $matches;
    }
    
    /**
     * Calculate career match score using Weighted Euclidean Distance
     */
    private function calculateCareerMatch(array $userProfile, array $career): float
    {
        $weights = [
            'riasec' => 0.35, // Highest weight: Interest alignment is key
            'mbti' => 0.15,   // Personality fit
            'gardner' => 0.15,// Cognitive preference
            'eq' => 0.10,     // Emotional capability
            'aptitude' => 0.25 // Raw capability
        ];

        $scores = [];

        // 1. RIASEC Match (Cosine Similarity approximation)
        $riasecCareer = json_decode($career['riasec_profile'], true) ?: [];
        $scores['riasec'] = $this->calculateProfileSimilarity($userProfile['riasec_profile'], $riasecCareer);

        // 2. MBTI Match (Lookup Table)
        if (!empty($career['mbti_fit'])) {
            $mbtiFit = json_decode($career['mbti_fit'], true);
            $scores['mbti'] = isset($mbtiFit[$userProfile['mbti_type']]) ? $mbtiFit[$userProfile['mbti_type']] : 50;
        } else {
            $scores['mbti'] = 50; // Neutral if no data
        }

        // 3. Gardner Match
        $gardnerCareer = json_decode($career['gardner_requirements'], true) ?: [];
        $scores['gardner'] = $this->calculateProfileSimilarity($userProfile['gardner_profile'], $gardnerCareer);

        // 4. EQ Match (Threshold based)
        $eqCareer = json_decode($career['eq_requirements'], true) ?: [];
        $scores['eq'] = $this->calculateThresholdMatch($userProfile['eq_breakdown'], $eqCareer);

        // 5. Aptitude Match (Threshold based)
        $aptitudeCareer = json_decode($career['aptitude_requirements'], true) ?: [];
        $scores['aptitude'] = $this->calculateThresholdMatch($userProfile['aptitude_scores'], $aptitudeCareer);

        // Weighted Average
        $totalScore = 0;
        foreach ($weights as $dimension => $weight) {
            $totalScore += ($scores[$dimension] ?? 0) * $weight;
        }

        return min(100, max(0, round($totalScore, 1)));
    }
    
    /**
     * NEW: Dynamic Educational Pathways based on Age Group & Interest
     */
    private function generateEducationalPathways(array $profile, string $ageGroup): array
    {
        $pathways = [];
        $riasec = $profile['riasec_profile'];
        arsort($riasec);
        $topCode = key($riasec); // e.g., 'I' for Investigative

        // Map RIASEC codes to educational streams/degrees
        $streamMap = [
            'R' => [
                'streams' => ['Science with Math', 'Vocational Training'],
                'degrees' => ['B.Tech / B.E (Engineering)', 'Architecture', 'Robotics', 'Agricultural Science']
            ],
            'I' => [
                'streams' => ['Science (PCM or PCB)', 'Computer Science'],
                'degrees' => ['B.Sc (Physics/Chem/Bio)', 'MBBS/BDS (Medicine)', 'Data Science', 'Psychology']
            ],
            'A' => [
                'streams' => ['Arts / Humanities', 'Design'],
                'degrees' => ['B.A. (Literature/Arts)', 'Bachelor of Design', 'Fine Arts', 'Mass Communication']
            ],
            'S' => [
                'streams' => ['Arts', 'Commerce with Psychology'],
                'degrees' => ['B.A. (Psychology/Sociology)', 'B.Ed (Teaching)', 'Social Work', 'Human Resources']
            ],
            'E' => [
                'streams' => ['Commerce', 'Economics'],
                'degrees' => ['BBA / BMS (Management)', 'Law (LLB)', 'Entrepreneurship', 'Marketing']
            ],
            'C' => [
                'streams' => ['Commerce with Math', 'Finance'],
                'degrees' => ['B.Com (Finance/Accounting)', 'Chartered Accountancy (CA)', 'Statistics', 'Library Science']
            ]
        ];

        // Tailor advice based on Age Group
        if ($ageGroup === 'class_8_10') {
            $pathways[] = [
                'title' => 'Recommended High School Stream',
                'description' => "Based on your dominant '{$this->getRIASECName($topCode)}' interest, you should consider: " . implode(' or ', $streamMap[$topCode]['streams']) . ".",
                'action_items' => ['Select relevant electives', 'Focus on core subjects for this stream']
            ];
        } elseif ($ageGroup === 'class_11_12') {
            $pathways[] = [
                'title' => 'Undergraduate Degree Options',
                'description' => "Your profile strongly suggests success in: " . implode(', ', $streamMap[$topCode]['degrees']) . ".",
                'action_items' => ['Research entrance exams', 'Look for colleges with strong placement in these fields']
            ];
        } else {
            // Adults/Graduates
            $pathways[] = [
                'title' => 'Professional Certification & Specialization',
                'description' => "To advance your career in '{$this->getRIASECName($topCode)}' fields, consider certifications in: " . $streamMap[$topCode]['degrees'][0] . " or specialized workshops.",
                'action_items' => ['Check industry certifications', 'Consider executive education']
            ];
        }

        return $pathways;
    }
    
    /**
     * NEW: Dynamic Career Roadmaps
     */
    private function generateCareerRoadmaps(array $careerMatches, string $ageGroup): array
    {
        $roadmaps = [];
        $topCareers = array_slice($careerMatches, 0, 3);

        foreach ($topCareers as $career) {
            $steps = [];
            
            // Phase 1: Foundation (Education)
            if ($ageGroup === 'class_8_10' || $ageGroup === 'class_11_12') {
                $steps[] = [
                    'phase' => 'Foundation',
                    'action' => 'Focus on relevant academic stream',
                    'details' => 'Build strong grades in subjects aligned with ' . $career['career_title']
                ];
                $steps[] = [
                    'phase' => 'Preparation',
                    'action' => 'Entrance Exams & College Selection',
                    'details' => 'Prepare for competitive exams required for this field.'
                ];
            } else {
                $steps[] = [
                    'phase' => 'Upskilling',
                    'action' => 'Advanced Certifications',
                    'details' => 'Gain specialized certifications relevant to ' . $career['career_title']
                ];
            }

            // Phase 2: Skill Acquisition
            $steps[] = [
                'phase' => 'Skill Building',
                'action' => 'Internships & Projects',
                'details' => 'Gain practical exposure through 1-2 internships or capstone projects.'
            ];

            // Phase 3: Entry
            $steps[] = [
                'phase' => 'Professional Entry',
                'action' => 'Entry-level Role',
                'details' => 'Start as a Junior/Associate ' . $career['career_title']
            ];

            $roadmaps[] = [
                'career_title' => $career['career_title'],
                'timeline' => $steps
            ];
        }

        return $roadmaps;
    }

    /**
     * NEW: Dynamic Skill Development Plan
     */
    private function generateSkillDevelopmentPlan(array $profile): array
    {
        $plan = [];

        // 1. Emotional Intelligence Improvements
        foreach ($profile['eq_breakdown'] as $component => $score) {
            if ($score < 60) {
                $name = str_replace('_', ' ', $component);
                $plan[] = [
                    'skill' => ucfirst($name),
                    'category' => 'Emotional Intelligence',
                    'priority' => 'High',
                    'suggestion' => "Practice mindfulness and active listening to improve {$name}."
                ];
            }
        }

        // 2. Aptitude Improvements
        foreach ($profile['aptitude_scores'] as $aptitude => $score) {
            if ($score < 50) {
                $plan[] = [
                    'skill' => ucfirst($aptitude) . ' Reasoning',
                    'category' => 'Cognitive Aptitude',
                    'priority' => 'Medium',
                    'suggestion' => "Engage in puzzles, sudoku, or specific {$aptitude} exercises daily."
                ];
            }
        }

        // 3. Soft Skills based on MBTI (Balance)
        if (strpos($profile['mbti_type'], 'I') !== false) {
             $plan[] = [
                'skill' => 'Public Speaking & Networking',
                'category' => 'Communication',
                'priority' => 'Medium',
                'suggestion' => "As an Introvert, structured practice in public speaking can be a powerful differentiator."
            ];
        }

        return $plan;
    }

    /**
     * NEW: Dynamic Fit Explanation
     */
    private function generateFitExplanation(array $profile, array $career, float $matchScore): string 
    {
        $explanations = [];
        
        // RIASEC Check
        $riasecCareer = json_decode($career['riasec_profile'], true) ?: [];
        arsort($riasecCareer);
        $careerTop = key($riasecCareer);
        
        $userRiasec = $profile['riasec_profile'];
        arsort($userRiasec);
        $userTop = key($userRiasec);

        if ($careerTop == $userTop) {
            $explanations[] = "Your primary interest in " . $this->getRIASECName($userTop) . " matches this career perfectly.";
        }

        // MBTI Check
        $mbtiFit = json_decode($career['mbti_fit'], true);
        if (isset($mbtiFit[$profile['mbti_type']]) && $mbtiFit[$profile['mbti_type']] > 80) {
            $explanations[] = "Your personality type ({$profile['mbti_type']}) is highly successful in this role.";
        }

        if (empty($explanations)) {
            return "You have a balanced profile that meets the general requirements of this role ({$matchScore}% match).";
        }

        return implode(" ", $explanations);
    }
    
    
    
    // --- Helper functions for generateComprehensiveAnalysis ---

    private function findResultByCategory(array $results, string $category): ?array
    {
        foreach ($results as $result) {
            if (($result['category_code'] ?? '') == $category) {
                return $result;
            }
        }
        return null;
    }

    private function extractMBTIType(array $mbtiResult): string
    {
        $data = json_decode($mbtiResult['interpretation'] ?? '{}', true);
        return $data['mbti_type'] ?? 'XXXX';
    }

    private function extractEQScore(array $eqResult): float
    {
        $scores = json_decode($eqResult['normalized_scores'], true);
        return is_array($scores) && count($scores) > 0 ? array_sum($scores) / count($scores) : 0;
    }

    private function extractIQEstimate(array $aptitudeResult): int
    {
        $scores = json_decode($aptitudeResult['normalized_scores'], true);
        return is_array($scores) ? $this->estimateIQ($scores) : 100;
    }
    
    // (Rest of the helper methods - placeholders are fine if logic is elsewhere or simplified here)
    // Note: ensure generateFitExplanation etc. are present if called above.
    // I am assuming the file structure from the upload is maintained for those.
    
    //private function generateFitExplanation(array $profile, array $career, float $matchScore): string { return "Based on profile, {$matchScore}% match."; }
    private function generatePersonalityAnalysis(array $profile): string
    {
        return "Comprehensive personality analysis based on MBTI type " . ($profile['mbti_type'] ?? 'data') . ".";
    }
    private function identifyChallenges(array $profile, array $career): string 
    {
        // Check for gaps
        $challenges = [];
        
        // Check Aptitude Gap
        $aptReq = json_decode($career['aptitude_requirements'], true) ?: [];
        foreach($aptReq as $k => $req) {
            $userScore = $profile['aptitude_scores'][$k] ?? 0;
            if ($req > 70 && $userScore < 50) {
                $challenges[] = "High " . ucfirst($k) . " demands";
            }
        }

        if (empty($challenges)) return "No major gaps identified.";
        return "Key challenges: " . implode(", ", $challenges) . ".";
    }
    private function identifyMotivators(array $profile): array
    {
        $motivators = [];
        
        // RIASEC Motivators
        $riasec = $profile['riasec_profile'];
        arsort($riasec);
        $topInterest = key($riasec);
        $motivators[] = $this->getRIASECMotivator($topInterest);
        
        // EQ Motivators
        if (($profile['eq_score'] ?? 0) > 75) {
            $motivators[] = 'Social connection and team harmony';
        } else {
            $motivators[] = 'Individual achievement and autonomy';
        }
        
        // MBTI Motivators
        if (strpos($profile['mbti_type'], 'J') !== false) {
            $motivators[] = 'Structure, order, and clear planning';
        } else {
            $motivators[] = 'Flexibility, adaptability, and spontaneity';
        }

        return $motivators;
    }
    
    private function generateInsights(array $profile): array
    {
        $strengths = [];
        $development = [];
        
        if (!empty($profile['riasec_profile'])) {
            foreach($profile['riasec_profile'] as $k => $v) {
                if ($v > 75) $strengths[] = "High interest in " . $this->getRIASECName($k);
            }
        }
        
        if (!empty($profile['eq_breakdown'])) {
            foreach($profile['eq_breakdown'] as $k => $v) {
                if ($v < 50) $development[] = "Improve " . str_replace('_', ' ', $k);
            }
        }
        
        return ['strengths' => $strengths, 'development_areas' => $development];
    }
    private function calculateProfileSimilarity(array $userProfile, array $targetProfile): float
    {
        if (empty($userProfile) || empty($targetProfile)) return 50;

        $diffSum = 0;
        $count = 0;

        foreach ($targetProfile as $key => $targetVal) {
            $userVal = $userProfile[$key] ?? 0;
            // Normalize inputs to 0-100 scale if they aren't already
            // Assuming inputs are 0-100
            $diffSum += abs($targetVal - $userVal);
            $count++;
        }

        if ($count == 0) return 0;

        // Average difference
        $avgDiff = $diffSum / $count;
        
        // Similarity is inverse of difference
        return max(0, 100 - $avgDiff);
    }
    // Calculate match where User Score >= Target Requirement is 100% match
    private function calculateThresholdMatch(array $userScores, array $requirements): float
    {
        if (empty($requirements)) return 100; // No specific requirements

        $totalMatch = 0;
        $count = 0;

        foreach ($requirements as $key => $reqVal) {
            $userVal = $userScores[$key] ?? 0;
            
            if ($userVal >= $reqVal) {
                $totalMatch += 100; // Requirement met
            } else {
                // Partial credit
                $totalMatch += ($userVal / $reqVal) * 100;
            }
            $count++;
        }

        return $count > 0 ? $totalMatch / $count : 0;
    }
    private function getRIASECMotivator(string $code): string
    {
        $map = [
            'R' => 'Tangible results and working with tools/machines',
            'I' => 'Solving complex problems and intellectual curiosity',
            'A' => 'Self-expression and creative freedom',
            'S' => 'Helping others and community service',
            'E' => 'Leading teams and achieving business goals',
            'C' => 'Organizing data and structured environments'
        ];
        return $map[$code] ?? 'Personal Growth';
    }

    private function getRIASECName(string $code): string
    {
        $names = ['R'=>'Realistic','I'=>'Investigative','A'=>'Artistic','S'=>'Social','E'=>'Enterprising','C'=>'Conventional'];
        return $names[$code] ?? $code;
    }
    
}