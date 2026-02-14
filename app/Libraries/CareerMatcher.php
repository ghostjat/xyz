<?php

namespace App\Libraries;

use App\Models\CareerModel;
use App\Models\CareerRoadmapModel;

/**
 * Career Matcher Library
 * Advanced algorithms for career matching and recommendations
 * Based on multi-dimensional psychometric profiles
 */
class CareerMatcher
{
    private $careerModel;
    private $roadmapModel;

    // Matching weights for different dimensions
    const WEIGHTS = [
        'riasec' => 0.30,      // Holland Code - Primary career interests
        'mbti' => 0.15,        // Personality type
        'gardner' => 0.20,     // Multiple intelligences
        'eq' => 0.15,          // Emotional intelligence
        'aptitude' => 0.20     // Cognitive abilities
    ];

    // Minimum match threshold
    const MIN_MATCH_THRESHOLD = 40.0;

    public function __construct()
    {
        $this->careerModel = new CareerModel();
        $this->roadmapModel = new CareerRoadmapModel();
    }

    /**
     * Generate personalized career recommendations
     * 
     * @param array $userProfile Complete psychometric profile
     * @param string $educationalLevel User's current educational level
     * @param int $limit Number of recommendations to return
     * @return array Ranked career recommendations
     */
    public function generateRecommendations(array $userProfile, string $educationalLevel, int $limit = 15): array
    {
        // Get all active careers
        $allCareers = $this->careerModel->where('is_active', true)->findAll();
        
        $recommendations = [];

        foreach ($allCareers as $career) {
            // Calculate comprehensive match score
            $matchData = $this->calculateMatch($userProfile, $career);
            
            // Only include careers above minimum threshold
            if ($matchData['overall_match'] >= self::MIN_MATCH_THRESHOLD) {
                $recommendations[] = [
                    'career_id' => $career['id'],
                    'career_code' => $career['career_code'],
                    'career_title' => $career['career_title'],
                    'career_category' => $career['career_category'],
                    'short_description' => $career['short_description'],
                    'match_percentage' => $matchData['overall_match'],
                    'match_breakdown' => $matchData['breakdown'],
                    'fit_level' => $this->getFitLevel($matchData['overall_match']),
                    'why_suitable' => $this->generateWhySuitable($userProfile, $career, $matchData),
                    'key_strengths' => $this->identifyKeyStrengths($userProfile, $career, $matchData),
                    'potential_challenges' => $this->identifyPotentialChallenges($userProfile, $career, $matchData),
                    'development_areas' => $this->identifyDevelopmentAreas($userProfile, $career),
                    'salary_range' => json_decode($career['salary_range'] ?? '{}', true),
                    'job_outlook' => $career['job_outlook'],
                    'growth_rate' => $career['growth_rate']
                ];
            }
        }

        // Sort by match percentage (descending)
        usort($recommendations, function($a, $b) {
            return $b['match_percentage'] <=> $a['match_percentage'];
        });

        // Limit results
        return array_slice($recommendations, 0, $limit);
    }

    /**
     * Calculate comprehensive match score
     * 
     * @param array $userProfile User's psychometric profile
     * @param array $career Career data
     * @return array Match data with breakdown
     */
    public function calculateMatch(array $userProfile, array $career): array
    {
        $breakdown = [];
        $totalScore = 0;

        // RIASEC Match (30%)
        $riasecScore = $this->calculateRIASECMatch(
            $userProfile['riasec_profile'],
            json_decode($career['riasec_profile'] ?? '{}', true)
        );
        $breakdown['riasec'] = [
            'score' => $riasecScore,
            'weight' => self::WEIGHTS['riasec'],
            'weighted_score' => $riasecScore * self::WEIGHTS['riasec']
        ];
        $totalScore += $breakdown['riasec']['weighted_score'];

        // MBTI Match (15%)
        $mbtiScore = $this->calculateMBTIMatch(
            $userProfile['mbti_type'],
            json_decode($career['mbti_fit'] ?? '{}', true)
        );
        $breakdown['mbti'] = [
            'score' => $mbtiScore,
            'weight' => self::WEIGHTS['mbti'],
            'weighted_score' => $mbtiScore * self::WEIGHTS['mbti']
        ];
        $totalScore += $breakdown['mbti']['weighted_score'];

        // Gardner Match (20%)
        $gardnerScore = $this->calculateGardnerMatch(
            $userProfile['gardner_profile'],
            json_decode($career['gardner_requirements'] ?? '{}', true)
        );
        $breakdown['gardner'] = [
            'score' => $gardnerScore,
            'weight' => self::WEIGHTS['gardner'],
            'weighted_score' => $gardnerScore * self::WEIGHTS['gardner']
        ];
        $totalScore += $breakdown['gardner']['weighted_score'];

        // EQ Match (15%)
        $eqScore = $this->calculateEQMatch(
            $userProfile['eq_breakdown'],
            json_decode($career['eq_requirements'] ?? '{}', true)
        );
        $breakdown['eq'] = [
            'score' => $eqScore,
            'weight' => self::WEIGHTS['eq'],
            'weighted_score' => $eqScore * self::WEIGHTS['eq']
        ];
        $totalScore += $breakdown['eq']['weighted_score'];

        // Aptitude Match (20%)
        $aptitudeScore = $this->calculateAptitudeMatch(
            $userProfile['aptitude_scores'],
            json_decode($career['aptitude_requirements'] ?? '{}', true)
        );
        $breakdown['aptitude'] = [
            'score' => $aptitudeScore,
            'weight' => self::WEIGHTS['aptitude'],
            'weighted_score' => $aptitudeScore * self::WEIGHTS['aptitude']
        ];
        $totalScore += $breakdown['aptitude']['weighted_score'];

        return [
            'overall_match' => round($totalScore, 2),
            'breakdown' => $breakdown,
            'confidence' => $this->calculateConfidence($breakdown)
        ];
    }

    /**
     * Calculate RIASEC match using cosine similarity
     */
    private function calculateRIASECMatch(array $userProfile, array $careerProfile): float
    {
        if (empty($careerProfile)) {
            return 50.0; // Neutral score if no career profile
        }

        $dimensions = ['R', 'I', 'A', 'S', 'E', 'C'];
        $dotProduct = 0;
        $userMagnitude = 0;
        $careerMagnitude = 0;

        foreach ($dimensions as $dim) {
            $userScore = ($userProfile[$dim] ?? 0) / 100; // Normalize to 0-1
            $careerScore = ($careerProfile[$dim] ?? 0);
            
            // Ensure career score is also 0-1
            $careerScore = $careerScore > 1 ? $careerScore / 100 : $careerScore;

            $dotProduct += $userScore * $careerScore;
            $userMagnitude += $userScore * $userScore;
            $careerMagnitude += $careerScore * $careerScore;
        }

        $userMagnitude = sqrt($userMagnitude);
        $careerMagnitude = sqrt($careerMagnitude);

        if ($userMagnitude == 0 || $careerMagnitude == 0) {
            return 0;
        }

        // Cosine similarity
        $similarity = $dotProduct / ($userMagnitude * $careerMagnitude);
        
        return round($similarity * 100, 2);
    }

    /**
     * Calculate MBTI match
     */
    private function calculateMBTIMatch(string $userType, array $careerFit): float
    {
        if (empty($careerFit)) {
            return 50.0; // Neutral if no preference
        }

        // Check if user's MBTI type is in career fit data
        if (isset($careerFit[$userType])) {
            return $careerFit[$userType] * 100;
        }

        // Calculate partial match based on shared preferences
        $userPrefs = str_split($userType);
        $maxMatch = 0;

        foreach ($careerFit as $type => $score) {
            $careerPrefs = str_split($type);
            $matches = 0;
            
            for ($i = 0; $i < 4; $i++) {
                if (isset($userPrefs[$i]) && isset($careerPrefs[$i]) && $userPrefs[$i] == $careerPrefs[$i]) {
                    $matches++;
                }
            }
            
            // Calculate partial match score
            $partialMatch = ($matches / 4) * $score * 100;
            $maxMatch = max($maxMatch, $partialMatch);
        }

        return round($maxMatch, 2);
    }

    /**
     * Calculate Gardner intelligence match
     */
    private function calculateGardnerMatch(array $userProfile, array $careerRequirements): float
    {
        if (empty($careerRequirements)) {
            return 50.0;
        }

        $intelligences = [
            'Linguistic', 'Logical-Mathematical', 'Spatial', 'Bodily-Kinesthetic',
            'Musical', 'Interpersonal', 'Intrapersonal', 'Naturalistic'
        ];

        $totalSimilarity = 0;
        $count = 0;

        foreach ($intelligences as $intelligence) {
            if (isset($careerRequirements[$intelligence])) {
                $userScore = ($userProfile[$intelligence] ?? 0) / 100;
                $careerReq = $careerRequirements[$intelligence];
                $careerReq = $careerReq > 1 ? $careerReq / 100 : $careerReq;

                // Calculate similarity (1 - absolute difference)
                $similarity = 1 - abs($userScore - $careerReq);
                $totalSimilarity += $similarity;
                $count++;
            }
        }

        return $count > 0 ? round(($totalSimilarity / $count) * 100, 2) : 50.0;
    }

    /**
     * Calculate EQ match
     */
    private function calculateEQMatch(array $userEQ, array $careerEQReq): float
    {
        if (empty($careerEQReq)) {
            return 50.0;
        }

        $components = ['self_awareness', 'self_regulation', 'motivation', 'empathy', 'social_skills'];
        
        $totalSimilarity = 0;
        $count = 0;

        foreach ($components as $component) {
            if (isset($careerEQReq[$component])) {
                $userScore = ($userEQ[$component] ?? 0) / 100;
                $careerReq = $careerEQReq[$component];
                $careerReq = $careerReq > 1 ? $careerReq / 100 : $careerReq;

                $similarity = 1 - abs($userScore - $careerReq);
                $totalSimilarity += $similarity;
                $count++;
            }
        }

        return $count > 0 ? round(($totalSimilarity / $count) * 100, 2) : 50.0;
    }

    /**
     * Calculate aptitude match
     */
    private function calculateAptitudeMatch(array $userAptitude, array $careerAptitudeReq): float
    {
        if (empty($careerAptitudeReq)) {
            return 50.0;
        }

        $aptitudes = ['numerical', 'verbal', 'logical', 'creative', 'analytical', 'practical'];
        
        $totalSimilarity = 0;
        $count = 0;

        foreach ($aptitudes as $aptitude) {
            if (isset($careerAptitudeReq[$aptitude])) {
                $userScore = ($userAptitude[$aptitude] ?? 0) / 100;
                $careerReq = $careerAptitudeReq[$aptitude];
                $careerReq = $careerReq > 1 ? $careerReq / 100 : $careerReq;

                $similarity = 1 - abs($userScore - $careerReq);
                $totalSimilarity += $similarity;
                $count++;
            }
        }

        return $count > 0 ? round(($totalSimilarity / $count) * 100, 2) : 50.0;
    }

    /**
     * Calculate confidence in the match
     */
    private function calculateConfidence(array $breakdown): float
    {
        $scores = [];
        foreach ($breakdown as $dimension => $data) {
            $scores[] = $data['score'];
        }

        // Confidence is higher when scores are consistent
        $mean = array_sum($scores) / count($scores);
        $variance = 0;
        
        foreach ($scores as $score) {
            $variance += pow($score - $mean, 2);
        }
        
        $variance = $variance / count($scores);
        $stdDev = sqrt($variance);

        // Lower standard deviation = higher confidence
        // Map std dev (0-50) to confidence (100-50)
        $confidence = max(50, 100 - $stdDev);
        
        return round($confidence, 2);
    }

    /**
     * Get fit level description
     */
    private function getFitLevel(float $matchPercentage): string
    {
        if ($matchPercentage >= 85) return 'Excellent Fit';
        if ($matchPercentage >= 75) return 'Very Good Fit';
        if ($matchPercentage >= 65) return 'Good Fit';
        if ($matchPercentage >= 55) return 'Moderate Fit';
        return 'Potential Fit';
    }

    /**
     * Generate "Why Suitable" explanation
     */
    private function generateWhySuitable(array $userProfile, array $career, array $matchData): string
    {
        $reasons = [];

        // Check RIASEC alignment
        if ($matchData['breakdown']['riasec']['score'] >= 70) {
            $reasons[] = "Your career interests strongly align with this field";
        }

        // Check aptitude match
        if ($matchData['breakdown']['aptitude']['score'] >= 70) {
            $reasons[] = "You possess the cognitive abilities well-suited for this career";
        }

        // Check EQ match
        if ($matchData['breakdown']['eq']['score'] >= 70) {
            $reasons[] = "Your emotional intelligence matches the interpersonal demands";
        }

        // Check Gardner match
        if ($matchData['breakdown']['gardner']['score'] >= 70) {
            $topIntelligences = $this->getTopIntelligences($userProfile['gardner_profile'], 2);
            $reasons[] = "Your strengths in " . implode(' and ', $topIntelligences) . " are valuable in this field";
        }

        if (empty($reasons)) {
            $reasons[] = "This career offers opportunities that complement your profile";
        }

        return implode('. ', $reasons) . '.';
    }

    /**
     * Identify key strengths for this career
     */
    private function identifyKeyStrengths(array $userProfile, array $career, array $matchData): array
    {
        $strengths = [];

        // RIASEC strengths
        $riasecCareer = json_decode($career['riasec_profile'] ?? '{}', true);
        foreach ($riasecCareer as $dimension => $careerScore) {
            $userScore = $userProfile['riasec_profile'][$dimension] ?? 0;
            if ($userScore >= 70 && $careerScore >= 0.6) {
                $strengths[] = $this->getRIASECStrength($dimension);
            }
        }

        // Aptitude strengths
        $aptitudeReq = json_decode($career['aptitude_requirements'] ?? '{}', true);
        foreach ($aptitudeReq as $aptitude => $required) {
            $userScore = $userProfile['aptitude_scores'][$aptitude] ?? 0;
            if ($userScore >= 70 && $required >= 0.6) {
                $strengths[] = ucfirst($aptitude) . " ability";
            }
        }

        return array_slice($strengths, 0, 5); // Top 5 strengths
    }

    /**
     * Identify potential challenges
     */
    private function identifyPotentialChallenges(array $userProfile, array $career, array $matchData): array
    {
        $challenges = [];

        // Check for significant gaps
        foreach ($matchData['breakdown'] as $dimension => $data) {
            if ($data['score'] < 50) {
                $challenges[] = $this->getChallengeDescription($dimension, $data['score']);
            }
        }

        // Check EQ components
        $eqReq = json_decode($career['eq_requirements'] ?? '{}', true);
        foreach ($eqReq as $component => $required) {
            $userScore = $userProfile['eq_breakdown'][$component] ?? 0;
            if ($required > 0.7 && $userScore < 60) {
                $challenges[] = "Developing " . str_replace('_', ' ', $component) . " may be beneficial";
            }
        }

        return array_slice($challenges, 0, 3); // Top 3 challenges
    }

    /**
     * Identify development areas
     */
    private function identifyDevelopmentAreas(array $userProfile, array $career): array
    {
        $developmentAreas = [];

        // Skills to develop
        $skillReq = json_decode($career['skill_requirements'] ?? '[]', true);
        $developmentAreas = array_slice($skillReq, 0, 5);

        return $developmentAreas;
    }

    /**
     * Get top intelligences
     */
    private function getTopIntelligences(array $gardnerProfile, int $count = 2): array
    {
        arsort($gardnerProfile);
        return array_slice(array_keys($gardnerProfile), 0, $count);
    }

    /**
     * Get RIASEC strength description
     */
    private function getRIASECStrength(string $dimension): string
    {
        $strengths = [
            'R' => 'Practical and hands-on skills',
            'I' => 'Analytical and investigative abilities',
            'A' => 'Creative and artistic talents',
            'S' => 'People-oriented and helping skills',
            'E' => 'Leadership and persuasion skills',
            'C' => 'Organizational and detail-oriented skills'
        ];

        return $strengths[$dimension] ?? '';
    }

    /**
     * Get challenge description
     */
    private function getChallengeDescription(string $dimension, float $score): string
    {
        $descriptions = [
            'riasec' => "Building stronger interest alignment through exploration and experience",
            'mbti' => "Adapting your work style to match career demands",
            'gardner' => "Developing complementary skills and intelligences",
            'eq' => "Enhancing emotional intelligence competencies",
            'aptitude' => "Strengthening specific cognitive abilities through practice"
        ];

        return $descriptions[$dimension] ?? "Continued development in this area";
    }

    /**
     * Find similar careers
     */
    public function findSimilarCareers(int $careerId, int $limit = 5): array
    {
        $baseCareer = $this->careerModel->find($careerId);
        
        if (!$baseCareer) {
            return [];
        }

        $allCareers = $this->careerModel
            ->where('id !=', $careerId)
            ->where('is_active', true)
            ->findAll();

        $similarCareers = [];
        $baseRIASEC = json_decode($baseCareer['riasec_profile'] ?? '{}', true);

        foreach ($allCareers as $career) {
            $careerRIASEC = json_decode($career['riasec_profile'] ?? '{}', true);
            
            // Calculate similarity
            $similarity = $this->calculateProfileSimilarity($baseRIASEC, $careerRIASEC);
            
            if ($similarity >= 0.6) { // 60% similarity threshold
                $similarCareers[] = [
                    'career_id' => $career['id'],
                    'career_title' => $career['career_title'],
                    'similarity_score' => round($similarity * 100, 2)
                ];
            }
        }

        // Sort by similarity
        usort($similarCareers, function($a, $b) {
            return $b['similarity_score'] <=> $a['similarity_score'];
        });

        return array_slice($similarCareers, 0, $limit);
    }

    /**
     * Calculate profile similarity
     */
    private function calculateProfileSimilarity(array $profile1, array $profile2): float
    {
        if (empty($profile1) || empty($profile2)) {
            return 0;
        }

        $totalSimilarity = 0;
        $count = 0;

        foreach ($profile1 as $key => $value1) {
            if (isset($profile2[$key])) {
                $value2 = $profile2[$key];
                
                // Normalize values
                $value1 = $value1 > 1 ? $value1 / 100 : $value1;
                $value2 = $value2 > 1 ? $value2 / 100 : $value2;
                
                $similarity = 1 - abs($value1 - $value2);
                $totalSimilarity += $similarity;
                $count++;
            }
        }

        return $count > 0 ? $totalSimilarity / $count : 0;
    }
}