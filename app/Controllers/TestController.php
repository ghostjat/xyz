<?php 
namespace App\Controllers;

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
        
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('login')->with('error', 'Please login to access assessments.');
        }

        if(is_null($moduleCode)){
            return view('dashboard');
            
        }
        $validModules = ['riasec', 'mbti', 'eq', 'aptitude', 'gardner', 'vark','motivators'];
        
        if (!in_array($moduleCode, $validModules)) {
            return redirect()->to('dashboard')->with('error', 'Invalid Test Module Requested');
        }
        
        $userId = session()->get('user_id');
        $resModel = new TestResultModel();
        $alreadyCompleted = $resModel->where('user_id', $userId)
                                     ->where('module_code', $moduleCode)
                                     ->first();

        if ($alreadyCompleted) {
            // Redirect to results if they try to access a completed test url
            return redirect()->to("tests/results/$moduleCode")->with('info', 'You have already completed this assessment. Retakes are not permitted.');
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
        
        if (!session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error', 
                'msg' => 'Session expired. Please login again.'
            ]);
        }

        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'msg' => 'Invalid Request']);
        }
        
        // 1. Fetch the submitted answers array (adjust 'answers' to match your HTML input name, e.g., name="answers[1]")
        $answers = $this->request->getPost('answers');
        
        // 2. BACKEND VALIDATION: Check if it's completely blank or not an array
        if (empty($answers) || !is_array($answers)) {
            return redirect()->back()->with('error', 'Submission failed: No answers were received. Please complete the test.');
        }

        // FIX: Removed "?? 1" fallback. Must be a real user.
        $userId = session()->get('user_id'); 
        $moduleCode = $this->request->getPost('module_code');
        $rawAnswers = $this->request->getPost('answers');

        if (!$moduleCode || empty($rawAnswers)) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'No answers provided']);
        }
        
        $resModel = new TestResultModel();
        if ($resModel->where('user_id', $userId)->where('module_code', $moduleCode)->first()) {
             return $this->response->setJSON(['status' => 'error', 'msg' => 'Test already completed.']);
        }

        $this->db->transStart();

        try {
            $ansModel = new UserAnswerModel(); 
            $qModel   = new QuestionModel();
            $processedAnswers = []; 
            // Extract the newly added time_taken array
            $timeTakenData = $this->request->getPost('time_taken') ?? [];

            // 1. Save Answers & Prepare Data
            foreach ($rawAnswers as $qId => $val) {
                $qInfo = $qModel->find($qId); 
                if($qInfo) {
                    // Only insert into database if they actually answered it (Skip TIMEOUTs for DB)
                    if ($val !== 'TIMEOUT') {
                        $ansModel->insert([
                            'user_id' => $userId,
                            'question_id' => $qId,
                            'answer_value' => (string)$val
                        ]);
                    }
                    
                    $earnedValue = $val;  
                    $maxPossible = null;  

                    $normalizedModule = trim(strtolower($moduleCode));

                    // GRADING LOGIC
                    if ($normalizedModule === 'aptitude') {
                        $earnedValue = 0; // Default wrong
                        $maxPossible = (isset($qInfo['weight']) && $qInfo['weight'] > 0) ? (float)$qInfo['weight'] : 1;
                        
                        if (!empty($qInfo['options_json'])) {
                            $options = json_decode($qInfo['options_json'], true);
                            if (is_array($options)) {
                                foreach ($options as $optKey => $opt) {
                                    $expectedValue = $opt['label'] ?? $opt['val'] ?? (string)$optKey;
                                    
                                    if (trim(strtolower((string)$expectedValue)) === trim(strtolower((string)$val))) {
                                        if (isset($opt['is_correct']) && ($opt['is_correct'] === true || $opt['is_correct'] === 'true' || $opt['is_correct'] == 1 || $opt['is_correct'] === '1')) {
                                            $earnedValue = $maxPossible; // Correct!
                                        }
                                        break; 
                                    }
                                }
                            }
                        }
                    }
                    
                    // Attach the time taken (Default to 60 if it's not an aptitude test)
                    $timeTaken = isset($timeTakenData[$qId]) ? (int)$timeTakenData[$qId] : 60;

                    $processedAnswers[] = [
                        'category' => $qInfo['category'], 
                        'value'    => $earnedValue,       
                        'max'      => $maxPossible,
                        'time_taken' => $timeTaken        // Pass the speed metric to the Engine
                    ];
                }
            }

            // 2. Calculate Result (Scientific Engine)
            $resultData = $this->engine->calculateScore($moduleCode, $processedAnswers);
            
            // 3. EXTRACT PRIMARY TRAIT
            // 3. EXTRACT PRIMARY TRAIT
            $primaryTrait = 'Completed';
            
            // We use $normalizedModule because you already defined it earlier in this function!
            if ($normalizedModule === 'riasec') {
                $primaryTrait = $resultData['dominant'] ?? 'N/A';
            } elseif ($normalizedModule === 'mbti') {
                $primaryTrait = $resultData['type'] ?? 'N/A';
            } elseif ($normalizedModule === 'eq') {
                $primaryTrait = $resultData['eq_level'] ?? 'N/A';
            } elseif ($normalizedModule === 'gardner') {
                $primaryTrait = $resultData['dominant_intelligences'][0] ?? 'N/A';
            } elseif ($normalizedModule === 'aptitude') {
                $primaryTrait = $resultData['iq_projection']['classification'] ?? 'N/A';
            } elseif ($normalizedModule === 'vark') {
                $primaryTrait = $resultData['profile']['style'] ?? 'N/A';
            } elseif ($normalizedModule === 'motivators') {
                $primaryTrait = $resultData['profile']['primary_motivator'] ?? 'N/A';
            }

            // 4. Save Final Result
            $resModel = new TestResultModel();
            $resModel->insert([
                'user_id' => $userId,
                'module_code' => $moduleCode,
                'result_json' => json_encode($resultData, JSON_UNESCAPED_UNICODE),
                // Ensure Arrays are stringified to prevent insertion failure
                'primary_trait' => is_array($primaryTrait) ? json_encode($primaryTrait) : (string) $primaryTrait,
                // JSON_FORCE_OBJECT ensures empty arrays [] become valid JSON objects {} to satisfy MySQL
                'normalized_scores' => json_encode($resultData['scores'] ?? $resultData['standardized']['t_scores'] ?? [], JSON_FORCE_OBJECT)
            ]);

            $this->db->transComplete();

            return $this->response->setJSON([
                'status' => 'success', 
                'redirect' => base_url("dashboard")
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'An error occurred during submission: ' . $e->getMessage()]);
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