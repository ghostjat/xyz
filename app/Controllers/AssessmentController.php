<?php

namespace App\Controllers;

use App\Models\AssessmentSessionModel;
use App\Models\TestAttemptModel;
use App\Models\QuestionModel;
use App\Models\UserResponseModel;
use App\Models\TestResultModel;
use App\Models\TestCategoryModel;
use App\Libraries\PsychometricEngine;
use App\Libraries\ReportGenerator;

class AssessmentController extends BaseController
{
    protected $sessionModel;
    protected $attemptModel;
    protected $questionModel;
    protected $responseModel;
    protected $resultModel;
    protected $categoryModel;
    protected $psychometricEngine;
    protected $reportGenerator;

    public function __construct()
    {
        $this->sessionModel = new AssessmentSessionModel();
        $this->attemptModel = new TestAttemptModel();
        $this->questionModel = new QuestionModel();
        $this->responseModel = new UserResponseModel();
        $this->resultModel = new TestResultModel();
        $this->categoryModel = new TestCategoryModel();
        $this->psychometricEngine = new PsychometricEngine();
        $this->reportGenerator = new ReportGenerator();
    }

    /**
     * Assessment dashboard - test selection
     */
    public function index()
    {
        $auth = $this->requireAuth();
        if ($auth !== null) return $auth;

        $data = [
            'user' => $this->currentUser,
            'sessions' => $this->sessionModel->getUserSessions($this->currentUser['id']),
            'available_tests' => $this->getAvailableTests()
        ];

        return view('assessment/test_selection', $data);
    }
    
    /**
     * Test Interface (Page Load)
     * Route: test/(:num)/(:any) -> test/sessionId/categoryCode
     */
    public function test($sessionId, $categoryCode)
    {
        $auth = $this->requireAuth();
        if ($auth !== null) return $auth;

        // 1. Verify Session & Ownership
        $session = $this->sessionModel->find($sessionId);
        if (!$session || $session['user_id'] != $this->currentUser['id']) {
            return redirect()->to('/assessment')->with('error', 'Invalid assessment session.');
        }

        // 2. Verify Category
        $category = $this->getCategoryByCode($categoryCode);
        if (!$category) {
            return redirect()->to('/assessment')->with('error', 'Test category not found.');
        }

        // 3. Get or Validate Attempt
        // Note: Attempts are usually created in startSession(), but we verify here.
        $attempt = $this->attemptModel->where([
            'session_id'  => $sessionId,
            'category_id' => $category['id']
        ])->first();

        if (!$attempt) {
            // Lazy-create if missing for some reason
            $attemptId = $this->attemptModel->insert([
                'session_id'  => $sessionId,
                'category_id' => $category['id'],
                'status'      => 'not_started',
                'created_at'  => date('Y-m-d H:i:s')
            ]);
            $attempt = $this->attemptModel->find($attemptId);
        }

        // 4. Redirect if already completed
        if ($attempt['status'] === 'completed') {
            return redirect()->to('/assessment')->with('info', 'You have already completed the ' . $category['name'] . ' test.');
        }

        // 5. Load View with Data
        // The view (test_interface) will likely use JS to fetch specific questions using the API
        $data = [
            'user'       => $this->currentUser,
            'session'    => $session,
            'category'   => $category,
            'attempt'    => $attempt,
            'page_title' => $category['category_name'] . ' - Assessment'
        ];

        return view('assessment/test_interface', $data);
    }
    /**
     * Start new assessment session
     */
    public function startSession()
    {
        $auth = $this->requireAuth();
        if ($auth !== null) return $auth;

        $validation = \Config\Services::validation();
        $validation->setRules([
            'age_group' => 'required|in_list[class_8_10,class_11_12]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->error('Invalid age group', $validation->getErrors(), 422);
        }

        // Create new session
        $sessionCode = $this->generateSessionCode();
        $sessionData = [
            'user_id' => $this->currentUser['id'],
            'session_code' => $sessionCode,
            'age_group' => $this->request->getPost('age_group'),
            'status' => 'not_started',
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString()
        ];

        $sessionId = $this->sessionModel->insert($sessionData);

        if (!$sessionId) {
            return $this->error('Failed to create assessment session', null, 500);
        }

        // Create test attempts for all categories
        $categories = $this->getTestCategories();
        foreach ($categories as $category) {
            $this->attemptModel->insert([
                'session_id' => $sessionId,
                'category_id' => $category['id'],
                'status' => 'not_started'
            ]);
        }

        $this->logActivity('start_assessment_session', 'assessment_sessions', $sessionId);

        return $this->success([
            'session_id' => $sessionId,
            'session_code' => $sessionCode
        ], 'Assessment session created successfully');
    }

    /**
     * Get test questions
     */
    public function getQuestions($sessionId, $categoryCode)
    {
        $auth = $this->requireAuth();
        if ($auth !== null) return $auth;

        // Verify session belongs to user
        $session = $this->sessionModel->find($sessionId);
        if (!$session || $session['user_id'] != $this->currentUser['id']) {
            return $this->error('Invalid session', null, 403);
        }

        // Get questions for category
        $questions = $this->questionModel->getQuestionsByCategory(
            $categoryCode, 
            $session['age_group']
        );

        // Format questions for frontend
        $formattedQuestions = array_map(function($q) {
            return [
                'id' => $q['id'],
                'text' => $q['question_text'],
                'type' => $q['question_type'],
                'options' => json_decode($q['options'] ?? '[]'),
                'dimension' => $q['dimension']
            ];
        }, $questions);

        // Get or create attempt
        $category = $this->getCategoryByCode($categoryCode);
        $attempt = $this->attemptModel->where([
            'session_id' => $sessionId,
            'category_id' => $category['id']
        ])->first();

        if ($attempt['status'] == 'not_started') {
            $this->attemptModel->update($attempt['id'], [
                'status' => 'in_progress',
                'started_at' => date('Y-m-d H:i:s'),
                'total_questions' => count($formattedQuestions)
            ]);
        }

        return $this->success([
            'questions' => $formattedQuestions,
            'attempt_id' => $attempt['id'],
            'category' => $category,
            'total_questions' => count($formattedQuestions)
        ]);
    }

    /**
     * Save response
     */
    public function saveResponse()
    {
        $auth = $this->requireAuth();
        if ($auth !== null) return $auth;

        $validation = \Config\Services::validation();
        $validation->setRules([
            'attempt_id' => 'required|integer',
            'question_id' => 'required|integer',
            'response_value' => 'permit_empty|integer',
            'response_text' => 'permit_empty|string',
            'time_taken' => 'permit_empty|integer'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->error('Invalid data', $validation->getErrors(), 422);
        }

        $attemptId = $this->request->getPost('attempt_id');
        
        // Verify attempt belongs to user's session
        $attempt = $this->attemptModel->find($attemptId);
        if (!$attempt) {
            return $this->error('Invalid attempt', null, 403);
        }

        $session = $this->sessionModel->find($attempt['session_id']);
        if ($session['user_id'] != $this->currentUser['id']) {
            return $this->error('Unauthorized', null, 403);
        }

        // Check if response already exists
        $existing = $this->responseModel->where([
            'attempt_id' => $attemptId,
            'question_id' => $this->request->getPost('question_id')
        ])->first();

        $responseData = [
            'attempt_id' => $attemptId,
            'question_id' => $this->request->getPost('question_id'),
            'response_value' => $this->request->getPost('response_value'),
            'response_text' => $this->request->getPost('response_text'),
            'time_taken_seconds' => $this->request->getPost('time_taken') ?? 0,
            'is_skipped' => empty($this->request->getPost('response_value')) && empty($this->request->getPost('response_text'))
        ];

        if ($existing) {
            // Update existing response
            $this->responseModel->update($existing['id'], $responseData);
        } else {
            // Create new response
            $this->responseModel->insert($responseData);
        }

        // Update attempt progress
        $answeredCount = $this->responseModel
            ->where('attempt_id', $attemptId)
            ->where('is_skipped', false)
            ->countAllResults();

        $this->attemptModel->update($attemptId, [
            'answered_questions' => $answeredCount
        ]);

        return $this->success([
            'answered_count' => $answeredCount,
            'total_questions' => $attempt['total_questions']
        ], 'Response saved');
    }

    /**
     * Complete test attempt
     */
    public function completeTest()
    {
        $auth = $this->requireAuth();
        if ($auth !== null) return $auth;

        $attemptId = $this->request->getPost('attempt_id');
        
        $attempt = $this->attemptModel->find($attemptId);
        if (!$attempt) {
            return $this->error('Invalid attempt', null, 404);
        }

        // Verify ownership
        $session = $this->sessionModel->find($attempt['session_id']);
        if ($session['user_id'] != $this->currentUser['id']) {
            return $this->error('Unauthorized', null, 403);
        }

        // Calculate duration & Update status
        $startTime = strtotime($attempt['started_at']);
        $duration = time() - $startTime;

        $this->attemptModel->update($attemptId, [
            'status' => 'completed',
            'completed_at' => date('Y-m-d H:i:s'),
            'duration_seconds' => $duration
        ]);

        // Calculate test results
        $results = $this->psychometricEngine->calculateTestResults($attemptId);
        
        // Save results
        $this->resultModel->insert([
            'attempt_id' => $attemptId,
            'category_id' => $attempt['category_id'],
            'user_id' => $this->currentUser['id'],
            'raw_scores' => json_encode($results['raw_scores']),
            'normalized_scores' => json_encode($results['normalized_scores']),
            'percentile_scores' => json_encode($results['percentile_scores']),
            'interpretation' => $results['interpretation'],
            'reliability_score' => $results['reliability_score'],
            'completion_percentage' => $results['completion_percentage'],
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // LOGIC TO DETERMINE NEXT STEP
        $nextTestUrl = null;
        $reportUrl = null;
        $message = 'Test completed successfully';

        // 1. Find the next incomplete attempt in this session
        $nextAttempt = $this->attemptModel
            ->where('session_id', $session['id'])
            ->where('status !=', 'completed')
            ->orderBy('id', 'ASC') // Preserves the order of tests
            ->first();

        if ($nextAttempt) {
            // Found a next test! Get its category code to build the URL
            $nextCategory = $this->categoryModel->find($nextAttempt['category_id']);
            if ($nextCategory) {
                $nextTestUrl = base_url("assessment/test/{$session['id']}/{$nextCategory['category_code']}");
                $message = "Test completed! Moving to " . $nextCategory['category_name'] . "...";
            }
        } else {
            // No more tests? Check if session is fully complete and get report
            $reportCode = $this->checkSessionCompletion($session['id']);
            if ($reportCode) {
                $reportUrl = base_url("assessment/report/{$reportCode}");
                $message = "All assessments completed! Generating report...";
            } else {
                // Fallback (e.g., if report generation failed or already done)
                $reportUrl = base_url('dashboard');
            }
        }

        $this->logActivity('complete_test', 'test_attempts', $attemptId);

        return $this->success([
            'results' => $results,
            'next_test_url' => $nextTestUrl, // JS will look for this
            'report_url' => $reportUrl
        ], $message);
    }

    /**
     * Check if all tests in session are complete and generate report
     */
    private function checkSessionCompletion($sessionId)
    {
        $attempts = $this->attemptModel->where('session_id', $sessionId)->findAll();
        $allComplete = true;

        foreach ($attempts as $attempt) {
            if ($attempt['status'] != 'completed') {
                $allComplete = false;
                break;
            }
        }

        if ($allComplete) {
            // Update session status
            $this->sessionModel->update($sessionId, [
                'status' => 'completed',
                'completed_at' => date('Y-m-d H:i:s')
            ]);

            // Generate and RETURN the report code
            return $this->generateComprehensiveReport($sessionId);
        }
        
        return null;
    }

    /**
     * Generate comprehensive career analysis report
     */
    private function generateComprehensiveReport($sessionId)
    {
        $session = $this->sessionModel->find($sessionId);
        
        // Get all test results for this session
        $allResults = $this->resultModel->getSessionResults($sessionId);
        
        // Generate comprehensive analysis
        $comprehensiveAnalysis = $this->psychometricEngine->generateComprehensiveAnalysis(
            $allResults,
            $session['age_group']
        );
        
        // Save comprehensive report
        $reportCode = $this->generateReportCode();
        $reportId = $this->reportGenerator->saveReport(
            $sessionId,
            $session['user_id'],
            $reportCode,
            $comprehensiveAnalysis
        );
        
        $this->logActivity('generate_report', 'comprehensive_reports', $reportId);
        
        return $reportCode;
    }

    /**
     * View comprehensive report
     */
    public function viewReport($reportCode)
    {
        $auth = $this->requireAuth();
        if ($auth !== null) return $auth;

        $report = $this->reportGenerator->getReportByCode($reportCode);
        
        if (!$report || $report['user_id'] != $this->currentUser['id']) {
            return $this->error('Report not found', null, 404);
        }

        return view('assessment/report', ['report' => $report]);
    }

    /**
     * Download report as PDF
     */
    public function downloadReport($reportCode)
    {
        $auth = $this->requireAuth();
        if ($auth !== null) return $auth;

        $report = $this->reportGenerator->getReportByCode($reportCode);
        
        if (!$report || $report['user_id'] != $this->currentUser['id']) {
            return $this->error('Report not found', null, 404);
        }

        // Generate PDF
        $pdf = $this->reportGenerator->generatePDF($report);
        
        $this->logActivity('download_report', 'comprehensive_reports', $report['id']);
        
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="career_report_'.$reportCode.'.pdf"')
            ->setBody($pdf);
    }

    /**
     * Helper methods
     */
    
    private function generateSessionCode()
    {
        return 'SES-' . strtoupper(bin2hex(random_bytes(8)));
    }

    private function generateReportCode()
    {
        return 'REP-' . strtoupper(bin2hex(random_bytes(8)));
    }

    private function getTestCategories()
    {
        return $this->categoryModel
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get()
            ->getResultArray();
    }

    private function getCategoryByCode($code)
    {
        return $this->categoryModel
            ->where('category_code', $code)
            ->get()
            ->getRowArray();
    }

    private function getAvailableTests()
    {
        return $this->getTestCategories();
    }
}
