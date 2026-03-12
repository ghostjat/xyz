<?php namespace App\Controllers;

use App\Models\UserModel;
use App\Models\QuestionModel;
use App\Models\TestResultModel;
use App\Models\SchoolModel;
use App\Libraries\CareerSimulator;
use App\Libraries\CandidateParser;

class AdminController extends BaseController {

    public function __construct() {
        // Enforce Admin/Counselor Role
        $role = session()->get('category');
        if (!session()->get('isLoggedIn') || !in_array($role, ['admin'])) {
            header("Location: " . base_url('login'));
            exit();
        }
    }

    public function index() {
        $userModel = new UserModel();
        $qModel = new QuestionModel();
        $resModel = new TestResultModel();
        $schoolModel = new SchoolModel();
        $db = \Config\Database::connect();

        $role = session()->get('category');
        $mySchoolId = session()->get('school_id'); // If logged in as a school counselor


        $users = $userModel->orderBy('created_at', 'DESC')->findAll();
        $schools = $schoolModel->findAll();
        $questions = $qModel->orderBy('module_code', 'ASC')->orderBy('display_order', 'ASC')->findAll();

        // =========================================================
        // NEW FIX: Fetch distinct module codes for the HTML Datalist
        // =========================================================
        $modules = $db->table('questions')
                      ->select('module_code')
                      ->distinct()
                      ->where('module_code !=', '')
                      ->where('module_code IS NOT NULL')
                      ->orderBy('module_code', 'ASC')
                      ->get()
                      ->getResultArray();

        // 2. Report Scoping
        $builder = $db->table('test_results');
        $builder->select('test_results.*, users.full_name, users.email, schools.name as school_name');
        $builder->join('users', 'users.id = test_results.user_id');
        $builder->join('schools', 'schools.id = users.school_id', 'left'); 
        
        if ($role === 'cca' && $mySchoolId) {
            $builder->where('users.school_id', $mySchoolId);
        }
        
        $builder->orderBy('test_results.completed_at', 'DESC');
        $reports = $builder->get()->getResultArray();

        // Stats calculation based on scoped data
        $stats = [
            'total_users' => count($users),
            'total_tests_taken' => count($reports),
            'active_students' => count(array_filter($users, fn($u) => $u['category'] === 'student')),
            'total_schools' => count($schools)
        ];

        // Format report JSON data for UI
        foreach ($reports as &$report) {
            $decoded = json_decode($report['result_json'], true);
            $report['admin_feedback'] = $decoded['admin_feedback'] ?? '';
            $report['primary_trait'] = is_array(json_decode($report['primary_trait'], true)) ? implode(', ', json_decode($report['primary_trait'], true)) : $report['primary_trait'];
        }

        $data = [
            'role' => $role,
            'stats' => $stats,
            'users' => $users,
            'schools' => $schools,
            'questions' => $questions,
            'reports' => $reports,
            'modules' => $modules // <-- ADDED THIS: Passing the modules to the view!
        ];

        return $this->spa_view('dashboard/admin/index', $data, false);
    }
    
    // ==========================================
    // B2B: BULK CSV IMPORT FOR SCHOOLS
    // ==========================================
    public function bulkImportUsers() {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(400);

        $file = $this->request->getFile('csv_file');
        $schoolId = $this->request->getPost('school_id'); // Optional

        if (!$file || !$file->isValid() || $file->getClientMimeType() !== 'text/csv') {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Please upload a valid CSV file.']);
        }

        $userModel = new UserModel();
        $csvData = array_map('str_getcsv', file($file->getTempName()));
        $header = array_shift($csvData); // Remove header row

        $successCount = 0;
        $errors = [];

        // Expected CSV Format: Full Name, Email, Password, Phone
        foreach ($csvData as $index => $row) {
            if (count($row) < 3) continue; // Skip malformed rows

            $email = trim($row[1]);
            
            // Check if email already exists
            if ($userModel->where('email', $email)->first()) {
                $errors[] = "Row ".($index+2).": Email $email already exists.";
                continue;
            }

            $userModel->insert([
                'school_id' => $schoolId ?: null,
                'full_name' => trim($row[0]),
                'email'     => $email,
                'password'  => password_hash(trim($row[2]), PASSWORD_DEFAULT),
                'phone'     => isset($row[3]) ? trim($row[3]) : null,
                'category'  => 'student',
                'username'  => explode('@', $email)[0] . rand(10,99)
            ]);
            $successCount++;
        }

        $msg = "Imported $successCount students successfully. " . count($errors) . " skipped.";
        return $this->response->setJSON(['status' => 'success', 'msg' => $msg, 'errors' => $errors]);
    }

    // ==========================================
    // B2B: SCHOOL CRUD
    // ==========================================
    public function saveSchool() {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(400);

        $id = $this->request->getPost('id');
        $data = [
            'name' => $this->request->getPost('name'),
            'contact_person' => $this->request->getPost('contact_person'),
            'contact_email' => $this->request->getPost('contact_email')
        ];

        $model = new SchoolModel();
        if ($id) {
            $model->update($id, $data);
            $msg = 'School updated successfully.';
        } else {
            $model->insert($data);
            $msg = 'School created successfully.';
        }
        return $this->response->setJSON(['status' => 'success', 'msg' => $msg]);
    }

    // ==========================================
    // USER CRUD OPERATIONS
    // ==========================================
    public function saveUser() {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(400);

        $id = $this->request->getPost('id');
        $data = [
            'full_name' => $this->request->getPost('full_name'),
            'email'     => $this->request->getPost('email'),
            'category'  => $this->request->getPost('category')
        ];

        // Only hash and update password if a new one is typed
        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $model = new UserModel();
        if ($id) {
            $model->update($id, $data);
            $msg = 'User updated successfully.';
        } else {
            // New user requires a password
            if(empty($password)) return $this->response->setJSON(['status' => 'error', 'msg' => 'Password is required for new users.']);
            $data['username'] = explode('@', $data['email'])[0] . rand(10,99); // Auto-generate username
            $model->insert($data);
            $msg = 'User created successfully.';
        }

        return $this->response->setJSON(['status' => 'success', 'msg' => $msg]);
    }

    public function deleteUser($id) {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(400);
        (new UserModel())->delete($id);
        return $this->response->setJSON(['status' => 'success', 'msg' => 'User deleted.']);
    }

    // ==========================================
    // QUESTION CRUD OPERATIONS
    // ==========================================
    public function saveQuestion() {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(400);

        $id = $this->request->getPost('id');
        $data = [
            'module_code'   => $this->request->getPost('module_code'),
            'category'      => $this->request->getPost('category'),
            'question_text' => $this->request->getPost('question_text'),
            'input_type'    => $this->request->getPost('input_type'),
            'options_json'  => $this->request->getPost('options_json') ?: null,
            'display_order' => $this->request->getPost('display_order') ?: 0
        ];

        $model = new QuestionModel();
        if ($id) {
            $model->update($id, $data);
            $msg = 'Question updated successfully.';
        } else {
            $model->insert($data);
            $msg = 'Question created successfully.';
        }

        return $this->response->setJSON(['status' => 'success', 'msg' => $msg]);
    }

    public function deleteQuestion($id) {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(400);
        (new QuestionModel())->delete($id);
        return $this->response->setJSON(['status' => 'success', 'msg' => 'Question deleted.']);
    }

    // ==========================================
    // REPORT CRUD OPERATIONS
    // ==========================================
    public function saveReportComment() {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(400);

        $resultId = $this->request->getPost('result_id');
        $comment = $this->request->getPost('comment');

        $resModel = new TestResultModel();
        $record = $resModel->find($resultId);

        if ($record) {
            $jsonData = json_decode($record['result_json'], true);
            $jsonData['admin_feedback'] = trim($comment);
            $resModel->update($resultId, ['result_json' => json_encode($jsonData)]);
            return $this->response->setJSON(['status' => 'success', 'msg' => 'Comment attached.']);
        }
        return $this->response->setJSON(['status' => 'error', 'msg' => 'Report not found.']);
    }

    public function deleteReport($id) {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(400);
        (new TestResultModel())->delete($id);
        return $this->response->setJSON(['status' => 'success', 'msg' => 'Report deleted.']);
    }
    
    public function updateAppointmentStatus() {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(400);
        $id = $this->request->getPost('id');
        $status = $this->request->getPost('status');
        
        (new \App\Models\AppointmentModel())->update($id, ['status' => $status]);
        return $this->response->setJSON(['status' => 'success']);
    }
    
    // Fetches distinct categories based on the selected module code
    public function getCategoriesByModule()
    {
        $moduleCode = $this->request->getPost('module_code');
        
        // Assuming your table is named 'questions'
        $db = \Config\Database::connect();
        $builder = $db->table('questions')
                      ->select('category')
                      ->distinct()
                      ->where('module_code', $moduleCode)
                      ->where('category !=', '')
                      ->orderBy('category', 'ASC')
                      ->get();
                      
        $categories = $builder->getResultArray();
        
        // Extract just the category names into a flat array
        $catList = array_column($categories, 'category');

        return $this->response->setJSON(['status' => 'success', 'data' => $catList]);
    }
    
    public function aiSimulator() {
        return view('dashboard/admin/ai_simulator');
    }
    
    public function searchCandidate() {
        $query = $this->request->getPost('query');
        $db = \Config\Database::connect();
        
        // Scope to school if user is a CCA
        $builder = $db->table('users')->select('id, full_name, email');
        if (session()->get('category') === 'cca') {
            $builder->where('school_id', session()->get('school_id'));
        }

        $users = $builder->groupStart()
                            ->like('full_name', $query)
                            ->orLike('email', $query)
                         ->groupEnd()
                         ->limit(10)->get()->getResultArray();

        return $this->response->setJSON(['status' => 'success', 'data' => $users]);
    }
    
    public function fetchCandidateData() {
        $userId = $this->request->getPost('user_id');
        $db = \Config\Database::connect();
        
        $testResults = $db->table('test_results')->where('user_id', $userId)->get()->getResultArray();
        
        if (empty($testResults)) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Candidate has not completed any tests.']);
        }

        $parser = new CandidateParser();
        $allData = $parser->extractAllData($testResults); // NEW EXTRACTOR
        

        return $this->response->setJSON([
            'status'   => 'success', 
            'features' => $allData['features'], 
            'charts'   => $allData['charts'] // Pass the raw chart data to the UI
        ]);
    }
    
    /**
     * AJAX: Run the Rubix ML Simulator with Strict Validation
     */
    public function runSimulation() {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'msg' => 'Invalid Request']);
        }

        // 1. DEFINE STRICT MATHEMATICAL BOUNDARIES FOR THE ML MODEL
        $validationRules = [
            'RIASEC_R'   => 'required|numeric|greater_than_equal_to[1]|less_than_equal_to[7]',
            'RIASEC_I'   => 'required|numeric|greater_than_equal_to[1]|less_than_equal_to[7]',
            'RIASEC_A'   => 'required|numeric|greater_than_equal_to[1]|less_than_equal_to[7]',
            'RIASEC_S'   => 'required|numeric|greater_than_equal_to[1]|less_than_equal_to[7]',
            'RIASEC_E'   => 'required|numeric|greater_than_equal_to[1]|less_than_equal_to[7]',
            'RIASEC_C'   => 'required|numeric|greater_than_equal_to[1]|less_than_equal_to[7]',
            
            'analytical' => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[10]',
            'creative'   => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[10]',
            'social'     => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[10]',
            'leadership' => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[10]',
            'technical'  => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[10]',
            'empathy'    => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[10]',
            
            'math_score'    => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]',
            'english_score' => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]',
            'science_score' => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]',
            
            'target_salary_k'    => 'required|numeric|greater_than_equal_to[0]',
            'desired_growth_pct' => 'required|numeric',
            
            // NEW: Ensure the Counselor actually picked two careers to compare
            'career_a' => 'required|string',
            'career_b' => 'required|string'
        ];

        // 2. EXECUTE CODEIGNITER VALIDATION
        if (!$this->validate($validationRules)) {
            return $this->response->setJSON([
                'status' => 'validation_error',
                'errors' => $this->validator->getErrors()
            ]);
        }

        // 3. SANITIZE AND CAST ALL VALIDATED DATA TO STRICT FLOATS
        $post = $this->request->getPost();
        
        $safeFeatures = [
            'RIASEC_R' => (float)$post['RIASEC_R'], 'RIASEC_I' => (float)$post['RIASEC_I'], 'RIASEC_A' => (float)$post['RIASEC_A'],
            'RIASEC_S' => (float)$post['RIASEC_S'], 'RIASEC_E' => (float)$post['RIASEC_E'], 'RIASEC_C' => (float)$post['RIASEC_C'],
            'analytical' => (float)$post['analytical'], 'creative' => (float)$post['creative'], 'social' => (float)$post['social'],
            'leadership' => (float)$post['leadership'], 'technical' => (float)$post['technical'], 'empathy' => (float)$post['empathy'],
            'math_score'         => (float)$post['math_score'], 
            'english_score'      => (float)$post['english_score'], 
            'science_score'      => (float)$post['science_score'],
            'target_salary_k'    => (float)$post['target_salary_k'],
            'desired_growth_pct' => (float)$post['desired_growth_pct']
        ];

        try {
            // 4. RUN THE ML SIMULATOR A vs B COMPARISON
            $simulator = new \App\Libraries\CareerSimulator();
            
            // Pass the 17 sanitized features, plus the two selected careers
            $htmlReport = $simulator->simulateComparison($safeFeatures, $post['career_a'], $post['career_b']);

            return $this->response->setJSON(['status' => 'success', 'html' => $htmlReport]);

        } catch (\Exception $e) {
            log_message('error', '[AI Simulator Error] ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error', 
                'msg'    => 'The neural network encountered an error: ' . $e->getMessage()
            ]);
        }
    }
    
    public function aiAnalysis($userId) {
        $db = \Config\Database::connect();

        // 1. Fetch Candidate info
        $user = $db->table('users')->where('id', $userId)->get()->getRowArray();

        // 2. Fetch all their test results across all modules
        $testResults = $db->table('test_resulst')->where('user_id', $userId)->get()->getResultArray();

        if (empty($testResults)) {
            return redirect()->back()->with('error', 'Candidate has not completed any tests yet.');
        }

        // 3. Parse the JSONs into the 15-feature array
        $parser = new CandidateParser();
        $features = $parser->buildFeatureVector($testResults);

        // 4. Append the 2 Target Requirements (Salary & Growth)
        // Note: You should ideally pull these from the user's profile table if you add them.
        // For now, we set a realistic baseline.
        $features['target_salary_k'] = 65.0; // Example: $65k
        $features['desired_growth_pct'] = 5.0; // Example: 5% growth
        // 5. Run the AI Simulator!
        $simulator = new CareerSimulator();
        $aiReportHtml = $simulator->generateCandidateReport($features, 3); // Get Top 3 matches

        $data = [
            'title' => 'AI Career Diagnostic',
            'user' => $user,
            'features' => $features,
            'ai_report' => $aiReportHtml
        ];

        return view('admin/ai_analysis', $data);
    }
}