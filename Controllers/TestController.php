<?php namespace App\Controllers;

use App\Models\QuestionModel;
use App\Models\UserAnswerModel;
use App\Models\TestCategoryModel;
use App\Models\TestResultModel;
use App\Libraries\PsychometricEngine;

class TestController extends BaseController {

    protected $engine;
    protected $testCategory;
    protected $db;

    public function __construct() {
        // Initialize the Calculation Engine
        $this->testCategory = new TestCategoryModel();
        $this->engine = new PsychometricEngine();
        $this->db = \Config\Database::connect();
    }

    /**
     * 1. THE TEST INTERFACE
     * Loads the questions for a specific module (e.g., RIASEC, MBTI).
     */
    public function index($moduleCode=null) {
        // -----------------------------------------------------------
        // SECURITY CHECK: Redirect to Login if not authenticated
        // -----------------------------------------------------------
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('auth/index')->with('error', 'Please login to access assessments.');
        }

        if(is_null($moduleCode)){
            return view('dashboard/index');
            
        }
        
        // Security: Whitelist allowed modules to prevent unauthorized access
        $validModules = ['riasec', 'mbti', 'eq', 'aptitude', 'gardner', 'vark'];
        
        if (!in_array($moduleCode, $validModules)) {
            return redirect()->to('tests/dashboard')->with('error', 'Invalid Test Module Requested');
        }

        // Fetch Questions for this module
        $qModel = new QuestionModel();
        $questions = $qModel->where('module_code', $moduleCode)
                            ->orderBy('display_order', 'ASC')
                            ->findAll();

        $data = [
            'module' => $moduleCode,
            'questions' => $questions
        ];

        // RENDER: Standalone page (false)
        return $this->spa_view('tests/take_test', $data, false);
    }

    /**
     * 2. THE SUBMISSION HANDLER (AJAX)
     * Processes the answers, calculates the score, and saves the result.
     */
    public function submit() {
        // -----------------------------------------------------------
        // SECURITY CHECK: JSON Error if session expired during test
        // -----------------------------------------------------------
        if (!session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error', 
                'msg' => 'Session expired. Please login again.'
            ]);
        }

        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'msg' => 'Invalid Request']);
        }

        // FIX: Removed "?? 1" fallback. Must be a real user.
        $userId = session()->get('user_id'); 
        $moduleCode = $this->request->getPost('module_code');
        $rawAnswers = $this->request->getPost('answers');

        if (!$moduleCode || empty($rawAnswers)) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'No answers provided']);
        }

        $this->db->transStart();

        try {
            $ansModel = new UserAnswerModel(); 
            $qModel   = new QuestionModel();
            $processedAnswers = []; 

            // 1. Save Answers & Prepare Data
            foreach ($rawAnswers as $qId => $val) {
                // Optional: Check if question exists to prevent ID spoofing
                $qInfo = $qModel->find($qId); 
                if($qInfo) {
                    $ansModel->insert([
                        'user_id' => $userId,
                        'question_id' => $qId,
                        'answer_value' => $val
                    ]);
                    // Pass raw value (String for MBTI, Int for Likert)
                    $processedAnswers[] = [
                        'category' => $qInfo['category'], 
                        'value' => $val 
                    ];
                }
            }

            // 2. Calculate Result (Scientific Engine)
            $resultData = $this->engine->calculateScore($moduleCode, $processedAnswers);
            
            // 3. EXTRACT PRIMARY TRAIT
            $primaryTrait = 'Completed'; 

            if ($moduleCode == 'riasec') {
                $primaryTrait = $resultData['dominant'] ?? 'N/A'; 
            } 
            elseif ($moduleCode == 'mbti') {
                $primaryTrait = $resultData['type'] ?? 'N/A'; 
            } 
            elseif ($moduleCode == 'eq') {
                $primaryTrait = $resultData['eq_level'] ?? 'N/A';
            } 
            elseif ($moduleCode == 'gardner') {
                $primaryTrait = isset($resultData['dominant_intelligences'][0]) ? $resultData['dominant_intelligences'][0] : 'N/A';
            } 
            elseif ($moduleCode == 'aptitude') {
                $primaryTrait = $resultData['iq_projection']['classification'] ?? 'N/A';
            }
            elseif ($moduleCode == 'vark') {
                $primaryTrait = $resultData['profile']['style'] ?? 'N/A';
            }

            // 4. Save Final Result
            $resModel = new TestResultModel();
            $resModel->insert([
                'user_id' => $userId,
                'module_code' => $moduleCode,
                'result_json' => json_encode($resultData),
                'primary_trait' => is_array($primaryTrait) ? json_encode($primaryTrait) : $primaryTrait
            ]);

            $this->db->transComplete();

            return $this->response->setJSON([
                'status' => 'success', 
                'redirect' => base_url("tests/results/$moduleCode")
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 3. THE RESULTS PAGE
     * Fetches the latest result and displays the report.
     */
    public function results($moduleCode) {
        // -----------------------------------------------------------
        // SECURITY CHECK: Redirect to Login
        // -----------------------------------------------------------
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('user_id');
        
        $resModel = new TestResultModel();
        
        // 1. Get the Database Record
        $dbRecord = $resModel->where('user_id', $userId)
                             ->where('module_code', $moduleCode)
                             ->orderBy('completed_at', 'DESC')
                             ->first();

        // If no result found, redirect
        if(!$dbRecord) {
            return redirect()->to("/tests/$moduleCode");
        }

        // 2. Decode the JSON (The actual Psychometric Data)
        $psychometricData = json_decode($dbRecord['result_json'], true);

        // 3. Merge DB columns with Psychometric Data
        $finalResult = array_merge($dbRecord, $psychometricData);

        $data = [
            'module' => $moduleCode,
            'result' => $finalResult,      
            'scores' => $psychometricData  
        ];

        // 4. Render
        return $this->spa_view('tests/results', $data, false);
    }
}