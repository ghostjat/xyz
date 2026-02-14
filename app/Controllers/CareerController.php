<?php

namespace App\Controllers;

use App\Models\CareerModel;
use App\Models\CareerRoadmapModel;
use App\Models\CareerMatchModel;
use App\Models\ComprehensiveReportModel;
use App\Libraries\CareerMatcher;

/**
 * Career Controller
 * Handles career exploration, search, and detailed career information
 */
class CareerController extends BaseController
{
    protected $careerModel;
    protected $roadmapModel;
    protected $matchModel;
    protected $reportModel;
    protected $careerMatcher;

    public function __construct()
    {
        $this->careerModel = new CareerModel();
        $this->roadmapModel = new CareerRoadmapModel();
        $this->matchModel = new CareerMatchModel();
        $this->reportModel = new ComprehensiveReportModel();
        $this->careerMatcher = new CareerMatcher();
    }

    /**
     * Browse all careers
     * GET /api/careers
     */
    public function index()
    {
        $page = $this->request->getGet('page') ?? 1;
        $perPage = $this->request->getGet('per_page') ?? 20;
        $category = $this->request->getGet('category');
        $search = $this->request->getGet('search');
        $sortBy = $this->request->getGet('sort_by') ?? 'popularity_score';
        $sortOrder = $this->request->getGet('sort_order') ?? 'DESC';

        $builder = $this->careerModel->where('is_active', true);

        // Filter by category
        if ($category) {
            $builder->where('career_category', $category);
        }

        // Search functionality
        if ($search) {
            $builder->groupStart()
                ->like('career_title', $search)
                ->orLike('short_description', $search)
                ->orLike('career_category', $search)
                ->groupEnd();
        }

        // Sorting
        $allowedSortFields = ['career_title', 'popularity_score', 'growth_rate', 'created_at'];
        if (in_array($sortBy, $allowedSortFields)) {
            $builder->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $total = $builder->countAllResults(false);
        $careers = $builder->paginate($perPage, 'default', $page);

        // Get all categories for filters
        $categories = $this->careerModel->select('career_category')
            ->distinct()
            ->where('career_category IS NOT NULL')
            ->where('is_active', true)
            ->findAll();

        return $this->success([
            'careers' => $careers,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage)
            ],
            'categories' => array_column($categories, 'career_category')
        ]);
    }

    /**
     * Get single career details
     * GET /api/careers/{id}
     */
    public function show($id)
    {
        $career = $this->careerModel->find($id);

        if (!$career || !$career['is_active']) {
            return $this->error('Career not found', null, 404);
        }

        // Decode JSON fields
        $career = $this->decodeCareerFields($career);

        // Get related careers
        $relatedCareerIds = json_decode($career['related_careers'] ?? '[]', true);
        $relatedCareers = [];
        if (!empty($relatedCareerIds)) {
            $relatedCareers = $this->careerModel->whereIn('id', array_slice($relatedCareerIds, 0, 5))
                ->where('is_active', true)
                ->findAll();
        }

        // Increment popularity
        $this->careerModel->update($id, [
            'popularity_score' => ($career['popularity_score'] ?? 0) + 1
        ]);

        $this->logActivity('view_career', 'careers', $id);

        return $this->success([
            'career' => $career,
            'related_careers' => $relatedCareers
        ]);
    }

    /**
     * Search careers
     * GET /api/careers/search
     */
    public function search()
    {
        $query = $this->request->getGet('q');
        $limit = $this->request->getGet('limit') ?? 10;

        if (empty($query)) {
            return $this->error('Search query is required', null, 400);
        }

        $careers = $this->careerModel->searchCareers($query);
        $careers = array_slice($careers, 0, $limit);

        return $this->success([
            'query' => $query,
            'results' => $careers,
            'count' => count($careers)
        ]);
    }

    /**
     * Get career roadmap
     * GET /api/careers/{id}/roadmap
     */
    public function roadmap($careerId)
    {
        $ageGroup = $this->request->getGet('age_group') ?? 'class_11_12';
        $region = $this->request->getGet('region') ?? 'Global';

        $career = $this->careerModel->find($careerId);
        if (!$career) {
            return $this->error('Career not found', null, 404);
        }

        $roadmap = $this->roadmapModel->getRoadmap($careerId, $ageGroup, $region);

        if (!$roadmap) {
            // Generate default roadmap if not exists
            $roadmap = $this->generateDefaultRoadmap($career, $ageGroup);
        } else {
            // Decode JSON fields
            $roadmap = $this->decodeRoadmapFields($roadmap);
        }

        return $this->success([
            'career' => [
                'id' => $career['id'],
                'title' => $career['career_title'],
                'category' => $career['career_category']
            ],
            'roadmap' => $roadmap,
            'age_group' => $ageGroup,
            'region' => $region
        ]);
    }

    /**
     * Get career matches for current user
     * GET /api/careers/matches
     */
    public function matches()
    {
        $auth = $this->requireAuth();
        if ($auth !== null) return $auth;

        $limit = $this->request->getGet('limit') ?? 10;

        // Get user's latest report
        $reports = $this->reportModel->getUserReports($this->currentUser['id']);
        
        if (empty($reports)) {
            return $this->error('No assessment reports found. Please complete an assessment first.', null, 404);
        }

        $latestReport = $reports[0];

        // Get career matches for this report
        $matches = $this->matchModel->getReportMatches($latestReport['id'], $limit);

        // Enhance matches with full career details
        foreach ($matches as &$match) {
            $careerDetails = $this->careerModel->find($match['career_id']);
            if ($careerDetails) {
                $match['career_details'] = [
                    'title' => $careerDetails['career_title'],
                    'category' => $careerDetails['career_category'],
                    'short_description' => $careerDetails['short_description'],
                    'salary_range' => json_decode($careerDetails['salary_range'] ?? '{}', true),
                    'job_outlook' => $careerDetails['job_outlook'],
                    'growth_rate' => $careerDetails['growth_rate']
                ];
            }
        }

        return $this->success([
            'report_code' => $latestReport['report_code'],
            'generated_at' => $latestReport['generated_at'],
            'matches' => $matches,
            'total_matches' => count($matches)
        ]);
    }

    /**
     * Get personalized career recommendations
     * GET /api/careers/recommendations
     */
    public function recommendations()
    {
        $auth = $this->requireAuth();
        if ($auth !== null) return $auth;

        // Get user's latest report
        $reports = $this->reportModel->getUserReports($this->currentUser['id']);
        
        if (empty($reports)) {
            return $this->error('No assessment reports found. Please complete an assessment first.', null, 404);
        }

        $latestReport = $reports[0];

        // Decode profiles
        $userProfile = [
            'riasec_profile' => json_decode($latestReport['riasec_profile'], true),
            'vark_profile' => json_decode($latestReport['vark_profile'], true),
            'mbti_type' => $latestReport['mbti_type'],
            'mbti_scores' => json_decode($latestReport['mbti_scores'], true),
            'gardner_profile' => json_decode($latestReport['gardner_profile'], true),
            'eq_score' => $latestReport['eq_score'],
            'eq_breakdown' => json_decode($latestReport['eq_breakdown'], true),
            'aptitude_scores' => json_decode($latestReport['aptitude_scores'], true),
            'iq_estimate' => $latestReport['iq_estimate']
        ];

        // Get personalized recommendations using CareerMatcher
        $recommendations = $this->careerMatcher->generateRecommendations(
            $userProfile,
            $this->currentUser['educational_level']
        );

        return $this->success([
            'recommendations' => $recommendations,
            'profile_summary' => [
                'mbti_type' => $userProfile['mbti_type'],
                'eq_score' => round($userProfile['eq_score'], 1),
                'iq_estimate' => $userProfile['iq_estimate']
            ]
        ]);
    }

    /**
     * Compare multiple careers
     * POST /api/careers/compare
     */
    public function compare()
    {
        $careerIds = $this->request->getJSON(true)['career_ids'] ?? [];

        if (empty($careerIds) || count($careerIds) < 2 || count($careerIds) > 5) {
            return $this->error('Please select between 2 and 5 careers to compare', null, 400);
        }

        $careers = $this->careerModel->whereIn('id', $careerIds)
            ->where('is_active', true)
            ->findAll();

        if (count($careers) !== count($careerIds)) {
            return $this->error('One or more careers not found', null, 404);
        }

        // Prepare comparison data
        $comparison = [
            'careers' => [],
            'comparison_matrix' => $this->buildComparisonMatrix($careers)
        ];

        foreach ($careers as $career) {
            $comparison['careers'][] = [
                'id' => $career['id'],
                'title' => $career['career_title'],
                'category' => $career['career_category'],
                'salary_range' => json_decode($career['salary_range'] ?? '{}', true),
                'job_outlook' => $career['job_outlook'],
                'growth_rate' => $career['growth_rate'],
                'work_environment' => $career['work_environment'],
                'educational_requirements' => json_decode($career['educational_requirements'] ?? '[]', true),
                'skill_requirements' => json_decode($career['skill_requirements'] ?? '[]', true)
            ];
        }

        return $this->success($comparison);
    }

    /**
     * Get career categories with counts
     * GET /api/careers/categories
     */
    public function categories()
    {
        $categories = $this->db->table('careers')
            ->select('career_category, COUNT(*) as count')
            ->where('is_active', true)
            ->where('career_category IS NOT NULL')
            ->groupBy('career_category')
            ->orderBy('count', 'DESC')
            ->get()
            ->getResultArray();

        return $this->success([
            'categories' => $categories,
            'total_categories' => count($categories)
        ]);
    }

    /**
     * Get trending/popular careers
     * GET /api/careers/trending
     */
    public function trending()
    {
        $limit = $this->request->getGet('limit') ?? 10;
        $timeframe = $this->request->getGet('timeframe') ?? '30days'; // 7days, 30days, 90days

        $careers = $this->careerModel
            ->where('is_active', true)
            ->orderBy('popularity_score', 'DESC')
            ->orderBy('growth_rate', 'DESC')
            ->limit($limit)
            ->findAll();

        return $this->success([
            'trending_careers' => $careers,
            'timeframe' => $timeframe,
            'count' => count($careers)
        ]);
    }

    /**
     * Get career statistics
     * GET /api/careers/{id}/statistics
     */
    public function statistics($careerId)
    {
        $career = $this->careerModel->find($careerId);
        
        if (!$career) {
            return $this->error('Career not found', null, 404);
        }

        // Get match statistics
        $matchStats = $this->db->table('career_matches')
            ->select('AVG(match_percentage) as avg_match, COUNT(*) as total_matches, MAX(match_percentage) as highest_match')
            ->where('career_id', $careerId)
            ->get()
            ->getRowArray();

        // Get demographic breakdown
        $demographics = $this->db->table('career_matches')
            ->select('users.educational_level, COUNT(*) as count')
            ->join('comprehensive_reports', 'comprehensive_reports.id = career_matches.report_id')
            ->join('users', 'users.id = comprehensive_reports.user_id')
            ->where('career_matches.career_id', $careerId)
            ->groupBy('users.educational_level')
            ->get()
            ->getResultArray();

        return $this->success([
            'career_id' => $careerId,
            'career_title' => $career['career_title'],
            'statistics' => [
                'total_matches' => (int)($matchStats['total_matches'] ?? 0),
                'average_match_percentage' => round($matchStats['avg_match'] ?? 0, 2),
                'highest_match_percentage' => round($matchStats['highest_match'] ?? 0, 2),
                'popularity_score' => $career['popularity_score'] ?? 0
            ],
            'demographics' => $demographics
        ]);
    }

    /**
     * Get career by RIASEC code
     * GET /api/careers/by-riasec/{code}
     */
    public function byRiasec($code)
    {
        $limit = $this->request->getGet('limit') ?? 20;

        // Validate RIASEC code (should be 1-3 letters from RIASEC)
        if (!preg_match('/^[RIASEC]{1,3}$/', $code)) {
            return $this->error('Invalid RIASEC code. Use 1-3 letters from RIASEC.', null, 400);
        }

        // Get all careers and filter by RIASEC profile
        $allCareers = $this->careerModel->where('is_active', true)->findAll();
        $matchingCareers = [];

        foreach ($allCareers as $career) {
            $riasecProfile = json_decode($career['riasec_profile'] ?? '{}', true);
            
            // Calculate match score for this RIASEC code
            $matchScore = 0;
            foreach (str_split($code) as $letter) {
                $matchScore += $riasecProfile[$letter] ?? 0;
            }
            
            if ($matchScore > 0) {
                $career['riasec_match_score'] = $matchScore / strlen($code);
                $matchingCareers[] = $career;
            }
        }

        // Sort by match score
        usort($matchingCareers, function($a, $b) {
            return $b['riasec_match_score'] <=> $a['riasec_match_score'];
        });

        $matchingCareers = array_slice($matchingCareers, 0, $limit);

        return $this->success([
            'riasec_code' => $code,
            'careers' => $matchingCareers,
            'count' => count($matchingCareers)
        ]);
    }

    /**
     * Helper Methods
     */

    /**
     * Decode career JSON fields
     */
    private function decodeCareerFields(array $career): array
    {
        $jsonFields = [
            'educational_requirements', 'skill_requirements', 'certifications',
            'riasec_profile', 'mbti_fit', 'gardner_requirements',
            'eq_requirements', 'aptitude_requirements', 'salary_range',
            'demand_by_country', 'licensing_requirements', 'entry_level_positions',
            'mid_level_positions', 'senior_level_positions', 'related_careers',
            'alternative_careers'
        ];

        foreach ($jsonFields as $field) {
            if (isset($career[$field])) {
                $career[$field] = json_decode($career[$field], true);
            }
        }

        return $career;
    }

    /**
     * Decode roadmap JSON fields
     */
    private function decodeRoadmapFields(array $roadmap): array
    {
        $jsonFields = [
            'immediate_steps', 'short_term_goals', 'medium_term_goals',
            'long_term_goals', 'subject_focus', 'exam_preparation',
            'extracurricular_activities', 'internship_opportunities',
            'networking_tips', 'recommended_courses', 'online_resources',
            'books_and_materials'
        ];

        foreach ($jsonFields as $field) {
            if (isset($roadmap[$field])) {
                $roadmap[$field] = json_decode($roadmap[$field], true);
            }
        }

        return $roadmap;
    }

    /**
     * Generate default roadmap
     */
    private function generateDefaultRoadmap(array $career, string $ageGroup): array
    {
        $eduRequirements = json_decode($career['educational_requirements'] ?? '[]', true);
        
        return [
            'career_id' => $career['id'],
            'age_group' => $ageGroup,
            'immediate_steps' => [
                'Research the field thoroughly',
                'Talk to professionals in this career',
                'Identify required educational qualifications'
            ],
            'short_term_goals' => [
                'Focus on relevant subjects in school',
                'Build foundational skills',
                'Participate in related extracurricular activities'
            ],
            'medium_term_goals' => [
                'Pursue required education/training',
                'Gain practical experience through internships',
                'Build a professional network'
            ],
            'long_term_goals' => [
                'Obtain necessary certifications',
                'Build expertise in the field',
                'Advance to senior positions'
            ],
            'educational_requirements' => $eduRequirements,
            'subject_focus' => $this->suggestSubjects($career),
            'recommended_courses' => []
        ];
    }

    /**
     * Suggest relevant subjects
     */
    private function suggestSubjects(array $career): array
    {
        $category = $career['career_category'];
        
        $subjectMap = [
            'Technology' => ['Mathematics', 'Computer Science', 'Physics'],
            'Healthcare' => ['Biology', 'Chemistry', 'Health Science'],
            'Business' => ['Economics', 'Mathematics', 'Business Studies'],
            'Arts' => ['Art', 'Literature', 'History'],
            'Science' => ['Physics', 'Chemistry', 'Biology', 'Mathematics']
        ];

        return $subjectMap[$category] ?? ['General Studies'];
    }

    /**
     * Build comparison matrix
     */
    private function buildComparisonMatrix(array $careers): array
    {
        $matrix = [
            'salary' => [],
            'growth' => [],
            'education' => [],
            'skills' => []
        ];

        foreach ($careers as $career) {
            $salaryRange = json_decode($career['salary_range'] ?? '{}', true);
            
            $matrix['salary'][$career['id']] = [
                'min' => $salaryRange['min'] ?? 'N/A',
                'max' => $salaryRange['max'] ?? 'N/A',
                'median' => $salaryRange['median'] ?? 'N/A'
            ];
            
            $matrix['growth'][$career['id']] = $career['growth_rate'] ?? 'N/A';
            
            $matrix['education'][$career['id']] = json_decode(
                $career['educational_requirements'] ?? '[]', 
                true
            );
            
            $matrix['skills'][$career['id']] = json_decode(
                $career['skill_requirements'] ?? '[]', 
                true
            );
        }

        return $matrix;
    }
}