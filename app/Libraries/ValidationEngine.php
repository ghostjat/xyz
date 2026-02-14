<?php

namespace App\Libraries;

/**
 * Validation Engine Library
 * Comprehensive validation for psychometric data and user inputs
 * Ensures data integrity and quality for accurate assessments
 */
class ValidationEngine
{
    private $errors = [];
    private $warnings = [];

    /**
     * Validate complete user profile
     * 
     * @param array $profile User profile data
     * @return array Validation result
     */
    public function validateUserProfile(array $profile): array
    {
        $this->errors = [];
        $this->warnings = [];

        // Required fields
        $requiredFields = ['username', 'email', 'full_name', 'date_of_birth', 'educational_level'];
        
        foreach ($requiredFields as $field) {
            if (empty($profile[$field])) {
                $this->errors[] = "Field '{$field}' is required";
            }
        }

        // Email validation
        if (!empty($profile['email']) && !filter_var($profile['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "Invalid email format";
        }

        // Date of birth validation
        if (!empty($profile['date_of_birth'])) {
            $dob = strtotime($profile['date_of_birth']);
            if (!$dob) {
                $this->errors[] = "Invalid date of birth format";
            } else {
                $age = floor((time() - $dob) / (365.25 * 24 * 60 * 60));
                if ($age < 13 || $age > 25) {
                    $this->errors[] = "Age must be between 13 and 25 years";
                }
            }
        }

        // Educational level validation
        $validLevels = ['class_8', 'class_9', 'class_10', 'class_11', 'class_12', 'graduate', 'postgraduate'];
        if (!empty($profile['educational_level']) && !in_array($profile['educational_level'], $validLevels)) {
            $this->errors[] = "Invalid educational level";
        }

        return [
            'valid' => empty($this->errors),
            'errors' => $this->errors,
            'warnings' => $this->warnings
        ];
    }

    /**
     * Validate assessment response
     * 
     * @param array $response Response data
     * @param array $question Question data
     * @return array Validation result
     */
    public function validateResponse(array $response, array $question): array
    {
        $this->errors = [];
        $this->warnings = [];

        // Check required fields
        if (empty($response['attempt_id'])) {
            $this->errors[] = "Attempt ID is required";
        }

        if (empty($response['question_id'])) {
            $this->errors[] = "Question ID is required";
        }

        // Validate response based on question type
        switch ($question['question_type']) {
            case 'likert_5':
                $this->validateLikert5Response($response);
                break;
            case 'likert_7':
                $this->validateLikert7Response($response);
                break;
            case 'yes_no':
                $this->validateYesNoResponse($response);
                break;
            case 'multiple_choice':
                $this->validateMultipleChoiceResponse($response, $question);
                break;
            case 'ranking':
                $this->validateRankingResponse($response, $question);
                break;
            case 'scenario':
                $this->validateScenarioResponse($response);
                break;
        }

        // Check response time (flag unusually fast responses)
        if (isset($response['time_taken_seconds']) && $response['time_taken_seconds'] < 2) {
            $this->warnings[] = "Response time is unusually fast";
        }

        return [
            'valid' => empty($this->errors),
            'errors' => $this->errors,
            'warnings' => $this->warnings
        ];
    }

    /**
     * Validate 5-point Likert scale response
     */
    private function validateLikert5Response(array $response): void
    {
        if (isset($response['response_value'])) {
            $value = $response['response_value'];
            if (!is_numeric($value) || $value < 1 || $value > 5) {
                $this->errors[] = "Likert-5 response must be between 1 and 5";
            }
        } elseif (empty($response['is_skipped'])) {
            $this->errors[] = "Response value is required for Likert scale questions";
        }
    }

    /**
     * Validate 7-point Likert scale response
     */
    private function validateLikert7Response(array $response): void
    {
        if (isset($response['response_value'])) {
            $value = $response['response_value'];
            if (!is_numeric($value) || $value < 1 || $value > 7) {
                $this->errors[] = "Likert-7 response must be between 1 and 7";
            }
        } elseif (empty($response['is_skipped'])) {
            $this->errors[] = "Response value is required for Likert scale questions";
        }
    }

    /**
     * Validate Yes/No response
     */
    private function validateYesNoResponse(array $response): void
    {
        if (isset($response['response_value'])) {
            $value = $response['response_value'];
            if (!in_array($value, [0, 1])) {
                $this->errors[] = "Yes/No response must be 0 (No) or 1 (Yes)";
            }
        } elseif (empty($response['is_skipped'])) {
            $this->errors[] = "Response is required for Yes/No questions";
        }
    }

    /**
     * Validate multiple choice response
     */
    private function validateMultipleChoiceResponse(array $response, array $question): void
    {
        if (isset($response['response_value'])) {
            $options = json_decode($question['options'] ?? '[]', true);
            $value = $response['response_value'];
            
            if (!is_numeric($value) || $value < 0 || $value >= count($options)) {
                $this->errors[] = "Invalid option selected";
            }
        } elseif (empty($response['is_skipped'])) {
            $this->errors[] = "Response is required for multiple choice questions";
        }
    }

    /**
     * Validate ranking response
     */
    private function validateRankingResponse(array $response, array $question): void
    {
        if (isset($response['response_json'])) {
            $rankings = json_decode($response['response_json'], true);
            $options = json_decode($question['options'] ?? '[]', true);
            
            if (!is_array($rankings)) {
                $this->errors[] = "Rankings must be provided as an array";
                return;
            }

            // Check if all options are ranked
            if (count($rankings) !== count($options)) {
                $this->errors[] = "All options must be ranked";
            }

            // Check for duplicate rankings
            if (count($rankings) !== count(array_unique($rankings))) {
                $this->errors[] = "Duplicate rankings are not allowed";
            }
        } elseif (empty($response['is_skipped'])) {
            $this->errors[] = "Rankings are required for ranking questions";
        }
    }

    /**
     * Validate scenario response
     */
    private function validateScenarioResponse(array $response): void
    {
        if (isset($response['response_text'])) {
            $text = trim($response['response_text']);
            
            if (strlen($text) < 10) {
                $this->warnings[] = "Response seems too brief for a scenario question";
            }
            
            if (strlen($text) > 1000) {
                $this->errors[] = "Response exceeds maximum length of 1000 characters";
            }
        } elseif (empty($response['is_skipped'])) {
            $this->errors[] = "Text response is required for scenario questions";
        }
    }

    /**
     * Validate psychometric test results
     * Checks for data quality and consistency
     * 
     * @param array $results Test results
     * @param string $testType Type of test (RIASEC, VARK, etc.)
     * @return array Validation result with quality metrics
     */
    public function validateTestResults(array $results, string $testType): array
    {
        $this->errors = [];
        $this->warnings = [];

        // Check completion rate
        $completionRate = $results['completion_percentage'] ?? 0;
        if ($completionRate < 80) {
            $this->warnings[] = "Low completion rate ({$completionRate}%) may affect result accuracy";
        }

        // Check reliability score
        $reliability = $results['reliability_score'] ?? 0;
        if ($reliability < 0.70) {
            $this->warnings[] = "Reliability score below acceptable threshold (α < 0.70)";
        } elseif ($reliability >= 0.90) {
            // Excellent reliability
        }

        // Test-specific validations
        switch ($testType) {
            case 'RIASEC':
                $this->validateRIASECResults($results);
                break;
            case 'VARK':
                $this->validateVARKResults($results);
                break;
            case 'MBTI':
                $this->validateMBTIResults($results);
                break;
            case 'GARDNER':
                $this->validateGardnerResults($results);
                break;
            case 'EQ':
                $this->validateEQResults($results);
                break;
            case 'APTITUDE':
                $this->validateAptitudeResults($results);
                break;
        }

        // Calculate quality score
        $qualityScore = $this->calculateQualityScore($results, $completionRate, $reliability);

        return [
            'valid' => empty($this->errors),
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'quality_score' => $qualityScore,
            'quality_level' => $this->getQualityLevel($qualityScore)
        ];
    }

    /**
     * Validate RIASEC results
     */
    private function validateRIASECResults(array $results): void
    {
        $scores = $results['normalized_scores'] ?? [];
        $dimensions = ['R', 'I', 'A', 'S', 'E', 'C'];

        foreach ($dimensions as $dim) {
            if (!isset($scores[$dim])) {
                $this->errors[] = "Missing score for RIASEC dimension: {$dim}";
            } elseif ($scores[$dim] < 0 || $scores[$dim] > 100) {
                $this->errors[] = "Invalid score for dimension {$dim}: must be 0-100";
            }
        }

        // Check for flat profile (all scores similar - may indicate random responding)
        if (!empty($scores)) {
            $variance = $this->calculateVariance(array_values($scores));
            if ($variance < 50) {
                $this->warnings[] = "Profile shows low differentiation - results may be unreliable";
            }
        }
    }

    /**
     * Validate VARK results
     */
    private function validateVARKResults(array $results): void
    {
        $scores = $results['normalized_scores'] ?? [];
        $modalities = ['Visual', 'Auditory', 'Read-Write', 'Kinesthetic'];

        foreach ($modalities as $modality) {
            if (!isset($scores[$modality])) {
                $this->errors[] = "Missing score for VARK modality: {$modality}";
            }
        }

        // Check if percentages sum to approximately 100
        if (!empty($scores)) {
            $sum = array_sum($scores);
            if (abs($sum - 100) > 5) {
                $this->warnings[] = "VARK percentages do not sum to 100% (sum: {$sum}%)";
            }
        }
    }

    /**
     * Validate MBTI results
     */
    private function validateMBTIResults(array $results): void
    {
        $type = $results['mbti_type'] ?? '';
        
        // Check type format (4 letters from correct pairs)
        if (!preg_match('/^[EI][SN][TF][JP]$/', $type)) {
            $this->errors[] = "Invalid MBTI type format: {$type}";
        }

        // Check preference clarity
        $clarityScores = $results['normalized_scores'] ?? [];
        foreach ($clarityScores as $dimension => $clarity) {
            if ($clarity < 10) {
                $this->warnings[] = "Very slight preference in {$dimension} - type may be ambiguous";
            }
        }
    }

    /**
     * Validate Gardner results
     */
    private function validateGardnerResults(array $results): void
    {
        $scores = $results['normalized_scores'] ?? [];
        $intelligences = [
            'Linguistic', 'Logical-Mathematical', 'Spatial', 'Bodily-Kinesthetic',
            'Musical', 'Interpersonal', 'Intrapersonal', 'Naturalistic'
        ];

        foreach ($intelligences as $intelligence) {
            if (!isset($scores[$intelligence])) {
                $this->errors[] = "Missing score for intelligence: {$intelligence}";
            } elseif ($scores[$intelligence] < 0 || $scores[$intelligence] > 100) {
                $this->errors[] = "Invalid score for {$intelligence}: must be 0-100";
            }
        }
    }

    /**
     * Validate EQ results
     */
    private function validateEQResults(array $results): void
    {
        $scores = $results['normalized_scores'] ?? [];
        $components = ['self_awareness', 'self_regulation', 'motivation', 'empathy', 'social_skills'];

        foreach ($components as $component) {
            if (!isset($scores[$component])) {
                $this->errors[] = "Missing score for EQ component: {$component}";
            } elseif ($scores[$component] < 0 || $scores[$component] > 100) {
                $this->errors[] = "Invalid score for {$component}: must be 0-100";
            }
        }

        // Check overall EQ
        $overallEQ = $results['overall_eq'] ?? 0;
        if ($overallEQ < 0 || $overallEQ > 100) {
            $this->errors[] = "Invalid overall EQ score: must be 0-100";
        }
    }

    /**
     * Validate Aptitude results
     */
    private function validateAptitudeResults(array $results): void
    {
        $scores = $results['normalized_scores'] ?? [];
        $aptitudes = ['numerical', 'verbal', 'logical', 'creative', 'analytical', 'practical'];

        foreach ($aptitudes as $aptitude) {
            if (!isset($scores[$aptitude])) {
                $this->errors[] = "Missing score for aptitude: {$aptitude}";
            } elseif ($scores[$aptitude] < 0 || $scores[$aptitude] > 100) {
                $this->errors[] = "Invalid score for {$aptitude}: must be 0-100";
            }
        }

        // Validate IQ estimate
        $iq = $results['iq_estimate'] ?? 0;
        if ($iq < 70 || $iq > 150) {
            $this->warnings[] = "IQ estimate outside typical range (70-150)";
        }
    }

    /**
     * Calculate quality score for test results
     */
    private function calculateQualityScore(array $results, float $completionRate, float $reliability): float
    {
        $score = 0;

        // Completion rate (40%)
        $score += ($completionRate / 100) * 40;

        // Reliability (40%)
        $score += ($reliability) * 40;

        // Response consistency (20%)
        // Based on variance in response times and patterns
        $consistencyScore = 20; // Default
        
        // Deduct for warnings
        $consistencyScore -= count($this->warnings) * 2;
        
        $score += max(0, $consistencyScore);

        return round(min(100, max(0, $score)), 2);
    }

    /**
     * Get quality level description
     */
    private function getQualityLevel(float $score): string
    {
        if ($score >= 90) return 'Excellent';
        if ($score >= 80) return 'Very Good';
        if ($score >= 70) return 'Good';
        if ($score >= 60) return 'Acceptable';
        return 'Needs Review';
    }

    /**
     * Calculate variance
     */
    private function calculateVariance(array $values): float
    {
        if (empty($values)) return 0;

        $mean = array_sum($values) / count($values);
        $squaredDiffs = array_map(function($x) use ($mean) {
            return pow($x - $mean, 2);
        }, $values);

        return array_sum($squaredDiffs) / count($values);
    }

    /**
     * Validate career match data
     * 
     * @param array $matchData Career match data
     * @return array Validation result
     */
    public function validateCareerMatch(array $matchData): array
    {
        $this->errors = [];
        $this->warnings = [];

        // Check required fields
        if (empty($matchData['career_id'])) {
            $this->errors[] = "Career ID is required";
        }

        if (!isset($matchData['match_percentage'])) {
            $this->errors[] = "Match percentage is required";
        } elseif ($matchData['match_percentage'] < 0 || $matchData['match_percentage'] > 100) {
            $this->errors[] = "Match percentage must be between 0 and 100";
        }

        // Check breakdown
        if (isset($matchData['match_breakdown'])) {
            $breakdown = $matchData['match_breakdown'];
            
            $requiredDimensions = ['riasec', 'mbti', 'gardner', 'eq', 'aptitude'];
            foreach ($requiredDimensions as $dim) {
                if (!isset($breakdown[$dim])) {
                    $this->warnings[] = "Missing breakdown for dimension: {$dim}";
                }
            }
        }

        // Check confidence
        if (isset($matchData['confidence']) && ($matchData['confidence'] < 0 || $matchData['confidence'] > 100)) {
            $this->errors[] = "Confidence score must be between 0 and 100";
        }

        return [
            'valid' => empty($this->errors),
            'errors' => $this->errors,
            'warnings' => $this->warnings
        ];
    }

    /**
     * Validate comprehensive report
     * 
     * @param array $reportData Complete report data
     * @return array Validation result
     */
    public function validateComprehensiveReport(array $reportData): array
    {
        $this->errors = [];
        $this->warnings = [];

        // Check required sections
        $requiredSections = [
            'riasec_profile', 'vark_profile', 'mbti_type', 'mbti_scores',
            'gardner_profile', 'eq_score', 'eq_breakdown', 'aptitude_scores',
            'iq_estimate', 'personality_analysis', 'career_interests',
            'top_career_matches', 'strengths', 'development_areas'
        ];

        foreach ($requiredSections as $section) {
            if (!isset($reportData[$section])) {
                $this->errors[] = "Missing required section: {$section}";
            }
        }

        // Validate IQ estimate
        if (isset($reportData['iq_estimate'])) {
            $iq = $reportData['iq_estimate'];
            if ($iq < 70 || $iq > 150) {
                $this->warnings[] = "IQ estimate outside typical range";
            }
        }

        // Validate EQ score
        if (isset($reportData['eq_score'])) {
            $eq = $reportData['eq_score'];
            if ($eq < 0 || $eq > 100) {
                $this->errors[] = "EQ score must be between 0 and 100";
            }
        }

        // Check career matches count
        if (isset($reportData['top_career_matches'])) {
            $matchCount = count($reportData['top_career_matches']);
            if ($matchCount < 5) {
                $this->warnings[] = "Low number of career matches ({$matchCount})";
            }
        }

        // Validate confidence score
        if (isset($reportData['confidence_score'])) {
            $confidence = $reportData['confidence_score'];
            if ($confidence < 60) {
                $this->warnings[] = "Low confidence score - results may need review";
            }
        }

        return [
            'valid' => empty($this->errors),
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'completeness' => $this->calculateCompleteness($reportData, $requiredSections)
        ];
    }

    /**
     * Calculate completeness percentage
     */
    private function calculateCompleteness(array $data, array $requiredFields): float
    {
        $presentFields = 0;
        
        foreach ($requiredFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                $presentFields++;
            }
        }

        return round(($presentFields / count($requiredFields)) * 100, 2);
    }

    /**
     * Detect suspicious response patterns
     * 
     * @param array $responses Array of user responses
     * @return array Detection result
     */
    public function detectSuspiciousPatterns(array $responses): array
    {
        $patterns = [];

        // Check for straight-lining (all same responses)
        $values = array_column($responses, 'response_value');
        $uniqueValues = array_unique($values);
        
        if (count($uniqueValues) == 1 && count($values) > 10) {
            $patterns[] = [
                'type' => 'straight_lining',
                'severity' => 'high',
                'description' => 'All responses are identical - may indicate random responding'
            ];
        }

        // Check for alternating pattern
        $isAlternating = true;
        for ($i = 1; $i < count($values) - 1; $i++) {
            if (($values[$i-1] < $values[$i] && $values[$i] < $values[$i+1]) ||
                ($values[$i-1] > $values[$i] && $values[$i] > $values[$i+1])) {
                $isAlternating = false;
                break;
            }
        }
        
        if ($isAlternating && count($values) > 10) {
            $patterns[] = [
                'type' => 'alternating_pattern',
                'severity' => 'medium',
                'description' => 'Responses show alternating pattern - may not be genuine'
            ];
        }

        // Check response time consistency
        $times = array_column($responses, 'time_taken_seconds');
        $avgTime = array_sum($times) / count($times);
        $tooFastCount = 0;
        
        foreach ($times as $time) {
            if ($time < 2) {
                $tooFastCount++;
            }
        }
        
        if ($tooFastCount / count($times) > 0.5) {
            $patterns[] = [
                'type' => 'rapid_responding',
                'severity' => 'high',
                'description' => 'More than 50% of responses were unusually fast'
            ];
        }

        return [
            'suspicious' => !empty($patterns),
            'patterns_detected' => $patterns,
            'recommendation' => $this->getSuspiciousPatternRecommendation($patterns)
        ];
    }

    /**
     * Get recommendation based on suspicious patterns
     */
    private function getSuspiciousPatternRecommendation(array $patterns): string
    {
        if (empty($patterns)) {
            return 'No suspicious patterns detected - responses appear valid';
        }

        $highSeverity = array_filter($patterns, function($p) {
            return $p['severity'] === 'high';
        });

        if (!empty($highSeverity)) {
            return 'High-severity patterns detected - results should be reviewed or assessment retaken';
        }

        return 'Some irregular patterns detected - results should be interpreted with caution';
    }
}