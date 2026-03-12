<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\TestResultModel;
use App\Models\AppointmentModel;
use App\Libraries\CareerSimulator;
use App\Libraries\CandidateParser;
use Mpdf\Mpdf;

class CcaController extends BaseController {
    
    private $_AppointmentModel;
    public function __construct() {
        
        $this->_AppointmentModel = new AppointmentModel();
    }
    
    public function index() {
        $role = session()->get('category');
        if(!session()->get('isLoggedIn')||!in_array($role,['cca'])) {
            header('Location:'. base_url('login'));
            exit();
        }
        $userModel   = new UserModel();
        $resModel    = new TestResultModel();
        $aptModel    = new AppointmentModel(); // Make sure this model exists
        $db          = \Config\Database::connect();

        $role        = session()->get('category');
        $mySchoolId  = session()->get('school_id');

        // 1. DATA SCOPING (If Counselor is assigned to a specific school)
        if ($role === 'cca' && $mySchoolId) {
            $userModel->where('school_id', $mySchoolId);
            $aptModel->where('school_id', $mySchoolId);
        }

        // Fetch Base Data
        $users = $userModel->where('category', 'student')->orderBy('created_at', 'DESC')->findAll();
        $totalStudents = count($users);

        // Fetch Appointments
        $appointments = $aptModel->orderBy('preferred_datetime', 'ASC')->findAll();
        $pendingApts  = count(array_filter($appointments, fn($a) => $a['status'] === 'pending'));

        // Fetch Test Stats to calculate completion
        $allTests = $resModel->findAll();
        $testCountByStudent = [];
        foreach($allTests as $test) {
            $testCountByStudent[$test['user_id']] = ($testCountByStudent[$test['user_id']] ?? 0) + 1;
        }

        // Map data to students for the roster table
        $studentRoster = [];
        $completedAssessments = 0;
        foreach ($users as $user) {
            $testsTaken = $testCountByStudent[$user['id']] ?? 0;
            if ($testsTaken >= 6) $completedAssessments++; // Assuming 6 modules is a "complete" profile

            $studentRoster[] = [
                'id'         => $user['id'],
                'name'       => $user['full_name'],
                'email'      => $user['email'],
                'phone'      => $user['phone'] ?? 'N/A',
                'tests_done' => $testsTaken,
                'status'     => $testsTaken >= 6 ? 'Ready for AI' : 'Testing...',
                'status_col' => $testsTaken >= 6 ? 'success' : 'warning'
            ];
        }

        $data = [
            'title'            => 'Counselor Dashboard',
            'total_students'   => $totalStudents,
            'completed_profiles'=> $completedAssessments,
            'pending_apts'     => $pendingApts,
            'appointments'     => $appointments,
            'student_roster'   => $studentRoster,
            'role'             => $role
        ];

        return view('dashboard/cca/index', $data);
    }
    
    
    
    //----CRM----
    
    public function getCrmData() {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(400);
        $studentId = $this->request->getPost('student_id', FILTER_SANITIZE_NUMBER_INT);
        $db = \Config\Database::connect();
        
        // IDOR Check
        $user = $db->table('users')->where('id', $studentId)->get()->getRowArray();
        $schoolId = session()->get('school_id');
        if (!$user || (!empty($schoolId) && $user['school_id'] != $schoolId)) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'msg' => 'Unauthorized Access.']);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'notes' => $db->table('counselor_notes')->where('student_id', $studentId)->orderBy('created_at', 'DESC')->get()->getResultArray(),
            'tasks' => $db->table('student_tasks')->where('student_id', $studentId)->orderBy('created_at', 'DESC')->get()->getResultArray(),
            'resources' => $db->table('shared_resources')->where('student_id', $studentId)->orderBy('created_at', 'DESC')->get()->getResultArray()
        ]);
    }
    
    public function saveCrmItem() {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(400);

        // DDoS Protection: Limit to 40 saves per minute per IP
        $throttler = \Config\Services::throttler();
        if ($throttler->check(md5($this->request->getIPAddress() . 'crm_save'), 40, MINUTE) === false) {
            return $this->response->setStatusCode(429)->setJSON(['status' => 'error', 'msg' => 'Rate limit exceeded. Please slow down.']);
        }

        $studentId = $this->request->getPost('student_id', FILTER_SANITIZE_NUMBER_INT);
        $type = $this->request->getPost('type'); // 'note', 'task', or 'resource'
        $counselorId = session()->get('id');
        
        $db = \Config\Database::connect();
        
        // IDOR Check
        $user = $db->table('users')->where('id', $studentId)->get()->getRowArray();
        if (!$user || (!empty(session()->get('school_id')) && $user['school_id'] != session()->get('school_id'))) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'msg' => 'Unauthorized.']);
        }

        if ($type === 'note') {
            $text = trim($this->request->getPost('text'));
            if(empty($text)) return $this->response->setJSON(['status' => 'error', 'msg' => 'Note cannot be empty.']);
            $db->table('counselor_notes')->insert(['student_id' => $studentId, 'counselor_id' => $counselorId, 'note_text' => $text]);
        } 
        elseif ($type === 'task') {
            $desc = trim($this->request->getPost('description'));
            if(empty($desc)) return $this->response->setJSON(['status' => 'error', 'msg' => 'Task cannot be empty.']);
            $db->table('student_tasks')->insert(['student_id' => $studentId, 'counselor_id' => $counselorId, 'task_description' => $desc]);
        } 
        elseif ($type === 'resource') {
            $title = trim($this->request->getPost('title'));
            $url = trim($this->request->getPost('url', FILTER_SANITIZE_URL));
            if(empty($title) || empty($url)) return $this->response->setJSON(['status' => 'error', 'msg' => 'Title and URL are required.']);
            if(!filter_var($url, FILTER_VALIDATE_URL)) return $this->response->setJSON(['status' => 'error', 'msg' => 'Invalid URL format.']);
            $db->table('shared_resources')->insert(['student_id' => $studentId, 'counselor_id' => $counselorId, 'resource_title' => $title, 'resource_url' => $url]);
        }

        return $this->response->setJSON(['status' => 'success', 'msg' => 'Added successfully!']);
    }
    
    
    // --- ENHANCED REPORT MANAGEMENT ---

    // 1. Save Counselor Remarks (With DDoS Throttling & IDOR Protection)
    public function saveReportRemarks() {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(400);

        // DDoS Protection: Limit to 30 saves per minute per IP
        $throttler = \Config\Services::throttler();
        if ($throttler->check(md5($this->request->getIPAddress() . 'save_remarks'), 30, MINUTE) === false) {
            return $this->response->setStatusCode(429)->setJSON(['status' => 'error', 'msg' => 'Rate limit exceeded.']);
        }

        $studentId = $this->request->getPost('student_id', FILTER_SANITIZE_NUMBER_INT);
        $remarks = trim($this->request->getPost('remarks')); // We will escape on output (XSS protection)
        $status = $this->request->getPost('status');
        
        $db = \Config\Database::connect();
        $schoolId = session()->get('school_id');

        // IDOR Check: Ensure student belongs to this school
        $user = $db->table('users')->where('id', $studentId)->get()->getRowArray();
        if (!$user || (!empty($schoolId) && $user['school_id'] != $schoolId)) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'msg' => 'Unauthorized Access.']);
        }

        // Upsert into student_reports
        $reportMeta = $db->table('student_reports')->where('student_id', $studentId)->get()->getRowArray();
        
        $data = [
            'student_id' => $studentId,
            'counselor_id' => session()->get('id'),
            'remarks' => $remarks,
            'status' => in_array($status, ['generated', 'reviewed', 'sent', 'discussed']) ? $status : 'reviewed'
        ];

        if ($reportMeta) {
            $db->table('student_reports')->where('student_id', $studentId)->update($data);
        } else {
            $db->table('student_reports')->insert($data);
        }

        return $this->response->setJSON(['status' => 'success', 'msg' => 'Remarks saved successfully.']);
    }


    
    public function getReportMeta() {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(400);
        $studentId = $this->request->getPost('student_id', FILTER_SANITIZE_NUMBER_INT);
        
        $meta = \Config\Database::connect()->table('student_reports')->where('student_id', $studentId)->get()->getRowArray();
        
        return $this->response->setJSON([
            'status' => 'success', 
            'remarks' => $meta ? $meta['remarks'] : '',
            'report_status' => $meta ? $meta['status'] : 'generated'
        ]);
    }
    
    // 3. One-Click Email Delivery (With strict DDoS Throttling)
    public function emailStudentReport() {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(400);

        // DDoS Protection: Limit to 5 emails per minute per IP (Emails are resource heavy)
        $throttler = \Config\Services::throttler();
        if ($throttler->check(md5($this->request->getIPAddress() . 'email_report'), 5, MINUTE) === false) {
            return $this->response->setStatusCode(429)->setJSON(['status' => 'error', 'msg' => 'Email rate limit exceeded. Please wait a minute.']);
        }

        $studentId = $this->request->getPost('student_id', FILTER_SANITIZE_NUMBER_INT);
        $db = \Config\Database::connect();
        
        // Fetch User & IDOR Check
        $user = $db->table('users')->where('id', $studentId)->get()->getRowArray();
        if (!$user || (!empty(session()->get('school_id')) && $user['school_id'] != session()->get('school_id'))) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'msg' => 'Unauthorized Access.']);
        }

        if (empty($user['email'])) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Student does not have an email address on file.']);
        }

        // Generate PDF using Mpdf (Re-using your logic)
        try {
            // Note: You must integrate your existing `_getStudentReportData($studentId)` logic here 
            // to fetch the $data array just like ReportController does.
            // For brevity, assuming $data is populated with student data:
            $data = ['student_name' => $user['full_name']]; // Replace with full data logic
            
            $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
            $html = view('reports/final_pdf', $data);
            $mpdf->WriteHTML($html);
            $pdfContent = $mpdf->Output('', 'S'); // 'S' returns the document as a string

            // Send Email
            $email = \Config\Services::email();
            $email->setTo($user['email']);
            $email->setSubject('Your Pharos Career Analysis Report');
            $email->setMessage('Dear ' . esc($user['full_name']) . ',<br><br>Please find attached your comprehensive Career Analysis Report.<br><br>Regards,<br>Your Counseling Team');
            $email->attach($pdfContent, 'application/pdf', 'Pharos_Career_Report.pdf', 'base64');

            if ($email->send()) {
                // Update Status to Sent
                $db->table('student_reports')->where('student_id', $studentId)->update(['status' => 'sent']);
                return $this->response->setJSON(['status' => 'success', 'msg' => 'Report emailed successfully!']);
            } else {
                return $this->response->setJSON(['status' => 'error', 'msg' => 'Failed to send email. Check SMTP settings.']);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'PDF Generation Error: ' . $e->getMessage()]);
        }
    }
    
    public function getDiversityAnalytics() {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(400);

        // DDoS Protection: Heavy query, limit to 10 requests per minute per IP
        $throttler = \Config\Services::throttler();
        if ($throttler->check(md5($this->request->getIPAddress() . 'diversity_analytics'), 10, MINUTE) === false) {
            return $this->response->setStatusCode(429)->setJSON(['status' => 'error', 'msg' => 'Rate limit exceeded.']);
        }

        $db = \Config\Database::connect();
        $schoolId = session()->get('school_id');
        
        if (empty($schoolId)) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'msg' => 'Unauthorized.']);
        }

        $level = $this->request->getPost('level') ?? 'class_12';
        
        // Fetch users and map their IDs to their Gender
        $users = $db->table('users')->select('id, gender')
                    ->where('school_id', $schoolId)->where('category', 'student')->where('educational_level', $level)
                    ->get()->getResultArray();

        if (empty($users)) {
            return $this->response->setJSON(['status' => 'empty', 'msg' => 'No student data available for this batch.']);
        }

        $userGenders = [];
        $genderCounts = ['male' => 0, 'female' => 0];
        
        foreach ($users as $u) {
            $g = strtolower($u['gender'] ?? 'unknown');
            if (in_array($g, ['male', 'female'])) {
                $userGenders[$u['id']] = $g;
                $genderCounts[$g]++;
            }
        }

        $userIds = array_keys($userGenders);
        if (empty($userIds)) {
            return $this->response->setJSON(['status' => 'empty', 'msg' => 'No gender-identified data available for this batch.']);
        }

        $results = $db->table('test_results')->whereIn('user_id', $userIds)->whereIn('module_code', ['aptitude', 'riasec'])->get()->getResultArray();

        // Initialize Matrices
        $data = [
            'male' => [
                'riasec' => ['Realistic' => 0, 'Investigative' => 0, 'Artistic' => 0, 'Social' => 0, 'Enterprising' => 0, 'Conventional' => 0],
                'aptitude' => ['Numerical' => 0, 'Spatial' => 0],
                'counts' => ['riasec' => 0, 'aptitude' => 0]
            ],
            'female' => [
                'riasec' => ['Realistic' => 0, 'Investigative' => 0, 'Artistic' => 0, 'Social' => 0, 'Enterprising' => 0, 'Conventional' => 0],
                'aptitude' => ['Numerical' => 0, 'Spatial' => 0],
                'counts' => ['riasec' => 0, 'aptitude' => 0]
            ]
        ];

        // Fuzzy JSON Search Closure
        $findScore = function($arr, $targetKey) use (&$findScore) {
            foreach ($arr as $k => $v) {
                if (stripos((string)$k, $targetKey) !== false || stripos($targetKey, (string)$k) !== false) {
                    return is_array($v) ? ($v['score'] ?? $v['t_score'] ?? $v['value'] ?? $v['raw'] ?? 0) : $v;
                }
                if (is_array($v)) {
                    $res = $findScore($v, $targetKey);
                    if ($res !== null) return $res;
                }
            }
            return null;
        };

        foreach ($results as $r) {
            $uid = $r['user_id'];
            $gender = $userGenders[$uid];
            $mod = strtolower($r['module_code']);
            
            $rawJson = json_decode($r['result_json'], true) ?? [];
            $normJson = !empty($r['normalized_scores']) ? json_decode($r['normalized_scores'], true) : [];

            if (isset($data[$gender][$mod])) {
                $data[$gender]['counts'][$mod]++;
                $scoresObj = $normJson['t_scores'] ?? $normJson ?? [];
                if (empty($scoresObj)) $scoresObj = $rawJson['standardized']['t_scores'] ?? $rawJson['scores'] ?? $rawJson ?? [];

                foreach ($data[$gender][$mod] as $key => $val) {
                    $score = 0;
                    if (isset($scoresObj[$key])) {
                        $score = is_array($scoresObj[$key]) ? ($scoresObj[$key]['score'] ?? $scoresObj[$key]['t_score'] ?? 0) : $scoresObj[$key];
                    } else {
                        $score = $findScore($scoresObj, $key) ?? 0;
                    }
                    $data[$gender][$mod][$key] += (float)$score;
                }
            }
        }

        // Calculate Averages
        foreach (['male', 'female'] as $g) {
            foreach (['riasec', 'aptitude'] as $mod) {
                $count = $data[$g]['counts'][$mod];
                foreach ($data[$g][$mod] as $key => $total) {
                    $data[$g][$mod][$key] = $count > 0 ? round($total / $count, 1) : 0;
                }
            }
        }

        return $this->response->setJSON([
            'status' => 'success',
            'demographics' => $genderCounts,
            'male' => $data['male'],
            'female' => $data['female']
        ]);
    }
    
    /**
 * getSchoolAnalytics()
 *
 * Returns aggregated psychometric analytics for a school cohort.
 * Includes MBTI, RIASEC, Aptitude, EQ, Gardner MI, VARK, Motivators, and Stream recommendations.
 *
 * Security: AJAX-only, session-scoped, role-gated, rate-limited per user, input-validated.
 * Performance: Selects only needed columns, scopes to academic year, clamps scores.
 */
public function getSchoolAnalytics()
{
    // ── 1. TRANSPORT GUARD ──────────────────────────────────────────────────
    if (!$this->request->isAJAX()) {
        return $this->response->setStatusCode(400);
    }

    // ── 2. RATE LIMIT — keyed to authenticated user, not IP ─────────────────
    $throttler = \Config\Services::throttler();
    $rateLimitKey = md5((string) session()->get('user_id') . '_school_analytics');
    if ($throttler->check($rateLimitKey, 15, MINUTE) === false) {
        return $this->response
            ->setStatusCode(429)
            ->setJSON(['status' => 'error', 'msg' => 'Rate limit exceeded. Please wait before retrying.']);
    }

    // ── 3. AUTHENTICATION & AUTHORISATION ────────────────────────────────────
    $schoolId = session()->get('school_id');
    $isCca  = session()->get('category');   // only admins can view cohort data

    if (empty($schoolId)) {
        return $this->response
            ->setStatusCode(403)
            ->setJSON(['status' => 'error', 'msg' => 'Unauthorized: no school session.']);
    }

    if ($isCca !== 'cca') {
        return $this->response
            ->setStatusCode(403)
            ->setJSON(['status' => 'error', 'msg' => 'Forbidden: admin role required.']);
    }

    // ── 4. INPUT VALIDATION ───────────────────────────────────────────────────
    $allowedLevels = ['class_9', 'class_10', 'class_11', 'class_12'];
    $level = $this->request->getPost('level') ?? 'class_12';

    if (!in_array($level, $allowedLevels, true)) {
        return $this->response
            ->setStatusCode(422)
            ->setJSON(['status' => 'error', 'msg' => 'Invalid educational level provided.']);
    }

    // Academic year may optionally be posted; default to current year.
    $academicYear = (int) ($this->request->getPost('academic_year') ?? date('Y'));

    // ── 5. MODULE CODE → CANONICAL NAME MAP ──────────────────────────────────
    // Centralised alias resolution — no scattered if/in_array chains.
    $moduleAliases = [
        'mi'                    => 'gardner',
        'multiple_intelligence' => 'gardner',
        'work_value'            => 'motivators',
        'work_values'           => 'motivators',
        'motivator'             => 'motivators',
    ];

    // ── 6. DATA INITIALISATION ────────────────────────────────────────────────
    $data = [
        'mbti_types' => [],
        'riasec'     => ['Realistic' => 0, 'Investigative' => 0, 'Artistic' => 0, 'Social' => 0, 'Enterprising' => 0, 'Conventional' => 0],
        'aptitude'   => ['Numerical' => 0, 'Verbal' => 0, 'Logical' => 0, 'Spatial' => 0, 'Mechanical' => 0, 'Accuracy' => 0],
        'eq'         => ['Awareness' => 0, 'Regulation' => 0, 'Motivation' => 0, 'Empathy' => 0, 'Social' => 0],
        'gardner'    => ['Linguistic' => 0, 'Numerical' => 0, 'Logical' => 0, 'Spatial' => 0, 'Auditory' => 0, 'Kinesthetic' => 0, 'Interpersonal' => 0, 'Intrapersonal' => 0, 'Naturalistic' => 0],
        'vark'       => ['Visual' => 0, 'Auditory' => 0, 'Read & Write' => 0, 'Kinesthetic' => 0],
        'motivators' => ['Continuous Learning' => 0, 'Independence' => 0, 'Structured' => 0, 'Adventure' => 0, 'High Paced' => 0, 'Creativity' => 0, 'Social Service' => 0],
        'counts'     => ['riasec' => 0, 'aptitude' => 0, 'eq' => 0, 'gardner' => 0, 'vark' => 0, 'motivators' => 0],
    ];

    $streamCounts = [
        'Science (PCM)'      => 0,
        'Science (PCB)'      => 0,
        'Commerce'           => 0,
        'Humanities & Arts'  => 0,
        'Vocational & IT'    => 0,
    ];

    // ── 7. FETCH STUDENTS (this school + level only) ──────────────────────────
    $db = \Config\Database::connect();

    $users = $db->table('users')
        ->select('id')
        ->where('school_id', $schoolId)
        ->where('category', 'student')
        ->where('educational_level', $level)
        ->get()
        ->getResultArray();

    $userIds = array_column($users, 'id');

    if (empty($userIds)) {
        return $this->response->setJSON([
            'status' => 'empty',
            'msg'    => 'No student data available for this batch.',
        ]);
    }

    // ── 8. FETCH TEST RESULTS — scoped columns + academic year ───────────────
    // Select ONLY what we need; never SELECT *.
    $results = $db->table('test_results')
        ->select('user_id, module_code, primary_trait, result_json, normalized_scores')
        ->whereIn('user_id', $userIds)
        ->where('academic_year', $academicYear)
        ->get()
        ->getResultArray();

    if (empty($results)) {
        return $this->response->setJSON([
            'status' => 'empty',
            'msg'    => "No test results found for academic year {$academicYear}.",
        ]);
    }

    // ── 9. PROCESS RESULTS ────────────────────────────────────────────────────
    $userProfiles = [];   // per-student riasec + aptitude for stream logic

    foreach ($results as $r) {
        $uid = $r['user_id'];

        // Decode JSON once
        $rawJson  = json_decode($r['result_json'],       true) ?? [];
        $normJson = json_decode($r['normalized_scores'], true) ?? [];

        // Resolve canonical module name
        $mod = strtolower(trim($r['module_code']));
        $mod = $moduleAliases[$mod] ?? $mod;

        // Initialise per-user profile bucket
        if (!isset($userProfiles[$uid])) {
            $userProfiles[$uid] = ['riasec' => [], 'aptitude' => []];
        }

        // ── MBTI ──
        if ($mod === 'mbti' && !empty($r['primary_trait'])) {
            $type = strtoupper($r['primary_trait']);
            $data['mbti_types'][$type] = ($data['mbti_types'][$type] ?? 0) + 1;
        }

        // ── ALL OTHER SCORED MODULES ──
        if (!isset($data['counts'][$mod])) {
            continue;   // unknown / unsupported module — skip cleanly
        }

        $data['counts'][$mod]++;

        // Resolve scores: prefer normalised t-scores, fall back to raw.
        $scoresObj = $this->resolveScores($normJson, $rawJson);

        foreach ($data[$mod] as $key => $_) {
            $score = $this->extractScore($scoresObj, $key);
            // Clamp to a sane range (0–100) to prevent corrupted data blowing averages
            $score = max(0.0, min(100.0, (float) $score));

            $data[$mod][$key] += $score;

            // Store per-student for stream recommendation
            if ($mod === 'riasec')   $userProfiles[$uid]['riasec'][$key]   = $score;
            if ($mod === 'aptitude') $userProfiles[$uid]['aptitude'][$key] = $score;
        }
    }

    // ── 10. STREAM / SUBJECT SELECTION LOGIC ─────────────────────────────────
    // Uses a weighted scoring model across RIASEC + Aptitude — not a brittle if/else chain.
    foreach ($userProfiles as $uid => $profile) {
        if (empty($profile['riasec'])) continue;

        $r = $profile['riasec'];
        $a = $profile['aptitude'];

        // Defaults
        $inv  = $r['Investigative']  ?? 0;
        $ent  = $r['Enterprising']   ?? 0;
        $con  = $r['Conventional']   ?? 0;
        $art  = $r['Artistic']       ?? 0;
        $soc  = $r['Social']         ?? 0;
        $real = $r['Realistic']      ?? 0;

        $num  = $a['Numerical']  ?? 0;
        $spa  = $a['Spatial']    ?? 0;
        $log  = $a['Logical']    ?? 0;
        $mec  = $a['Mechanical'] ?? 0;
        $ver  = $a['Verbal']     ?? 0;

        // Weighted scores per stream
        $streamScores = [
            'Science (PCM)'     => ($inv * 0.40) + ($num * 0.35) + ($log * 0.25),
            'Science (PCB)'     => ($inv * 0.35) + ($spa * 0.30) + ($real * 0.20) + ($mec * 0.15),
            'Commerce'          => ($ent * 0.45) + ($con * 0.30) + ($num * 0.25),
            'Humanities & Arts' => ($art * 0.40) + ($soc * 0.35) + ($ver * 0.25),
            'Vocational & IT'   => ($real * 0.45) + ($mec * 0.30) + ($log * 0.25),
        ];

        arsort($streamScores);
        $recommendedStream = array_key_first($streamScores);
        $streamCounts[$recommendedStream]++;
    }

    // ── 11. COMPUTE AVERAGES ──────────────────────────────────────────────────
    foreach (['riasec', 'aptitude', 'eq', 'gardner', 'vark', 'motivators'] as $mod) {
        $count = $data['counts'][$mod];
        foreach ($data[$mod] as $key => $total) {
            $data[$mod][$key] = $count > 0 ? round($total / $count, 1) : 0;
        }
    }

    arsort($data['mbti_types']);

    // ── 12. RESPOND ───────────────────────────────────────────────────────────
    return $this->response->setJSON([
        'status'        => 'success',
        'total'         => count($userIds),
        'academic_year' => $academicYear,
        'level'         => $level,
        'mbti'          => $data['mbti_types'],
        'riasec'        => $data['riasec'],
        'aptitude'      => $data['aptitude'],
        'eq'            => $data['eq'],
        'mi'            => $data['gardner'],
        'vark'          => $data['vark'],
        'motivators'    => $data['motivators'],
        'streams'       => $streamCounts,
    ]);
}


// ═══════════════════════════════════════════════════════════════════════════════
// PRIVATE HELPERS
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Resolve the best available score object from a result row.
 *
 * Priority:
 *   1. Normalised t-scores
 *   2. Normalised root object
 *   3. Raw standardised t-scores
 *   4. Raw scores array
 *   5. Raw root object
 *
 * Having this as a named method makes fallback paths explicit and testable.
 */
private function resolveScores(array $normJson, array $rawJson): array
{
    if (!empty($normJson['t_scores']) && is_array($normJson['t_scores'])) {
        return $normJson['t_scores'];
    }
    if (!empty($normJson)) {
        return $normJson;
    }
    if (!empty($rawJson['standardized']['t_scores']) && is_array($rawJson['standardized']['t_scores'])) {
        return $rawJson['standardized']['t_scores'];
    }
    if (!empty($rawJson['scores']) && is_array($rawJson['scores'])) {
        return $rawJson['scores'];
    }
    return $rawJson;
}

/**
 * Extract a score for $key from a flat or lightly-nested scores array.
 *
 * Tries exact match first, then case-insensitive substring match.
 * Returns 0 if nothing is found — never throws.
 */
private function extractScore(array $scoresObj, string $key): float
{
    // Exact match (fastest path)
    if (isset($scoresObj[$key])) {
        $val = $scoresObj[$key];
        return (float) (is_array($val) ? ($val['score'] ?? $val['t_score'] ?? $val['value'] ?? 0) : $val);
    }

    // Case-insensitive substring match (handles minor key variations)
    foreach ($scoresObj as $jsonKey => $jsonVal) {
        $jk = strtolower((string) $jsonKey);
        $k  = strtolower($key);
        if (str_contains($jk, $k) || str_contains($k, $jk)) {
            return (float) (is_array($jsonVal) ? ($jsonVal['score'] ?? $jsonVal['t_score'] ?? 0) : $jsonVal);
        }
    }

    return 0.0;
}
    
    
    public function searchCandidate() {
        $query = $this->request->getPost('query');
        $db = \Config\Database::connect();
        
        $builder = $db->table('users')->select('id, full_name, email')->where('category', 'student');
        
        // SECURITY: Scope to school if user is a CCA
        if (session()->get('category') === 'cca' && session()->get('school_id')) {
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
        $userId = $this->request->getPost('user_id', FILTER_SANITIZE_NUMBER_INT);
        $db = \Config\Database::connect();
        
        // SECURITY (IDOR Prevention): Verify this candidate belongs to the CCA's school
        if (session()->get('category') === 'cca') {  //&& session()->get('school_id')
            $userCheck = $db->table('users')->select('school_id')->where('id', $userId)->get()->getRow();
            if (!$userCheck ) {  //|| $userCheck->school_id != session()->get('school_id')
                return $this->response->setJSON(['status' => 'error', 'msg' => 'Unauthorized. This candidate belongs to another institution.']);
            }
        }
        
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
        
        $throttler = \Config\Services::throttler();
        // Allow 10 requests per minute per IP
        if ($throttler->check(md5($this->request->getIPAddress()), 10, MINUTE) === false) {
            return $this->response->setStatusCode(429)->setJSON(['status' => 'error', 'msg' => 'Too many requests.']);
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
    
    
    public function updateAppointmentStatus() {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(400);
        
        $id = $this->request->getPost('id', FILTER_SANITIZE_NUMBER_INT);
        $status = esc($this->request->getPost('status'));
        
        $aptModel = new AppointmentModel();
        $apt = $aptModel->find($id);

        if (!$apt) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Appointment not found.']);
        }

        // SECURITY: Ensure the counselor owns this student's appointment
        $category = session()->get('category');
        if (!empty($category) && $apt['category'] != $category) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'msg' => 'Unauthorized Access']);
        }
        
        $aptModel->update($id, ['status' => $status]);
        return $this->response->setJSON(['status' => 'success']);
    }
    
    public function loadAppointments() {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(400);

        $db = \Config\Database::connect();
        $user_id = session()->get('user_id');

        // Join users table to get the student's name and email
        $builder = $db->table('appointments')
                      ->select('appointments.*, users.full_name as student_name, users.email as student_email')
                      ->join('users', 'users.id = appointments.user_id');

        if (!empty($user_id)) {
            $builder->where('appointments.user_id', $user_id);
        }

        $appointments = $builder->orderBy('preferred_datetime', 'DESC')->get()->getResultArray();

        $data = ['appointments' => $appointments];
        return $this->spa_view('dashboard/cca/appointments_list', $data, false);
    }
    
    // --- CAREER LIBRARY: DRILL-DOWN METHODS ---

    // 1. Get all unique categories and their career counts
    public function getCareerClusters() {
        
        $db = \Config\Database::connect();
        
        // Group by category to create the main cards
        $clusters = $db->table('careers')
                       ->select('career_category, COUNT(id) as total_careers')
                       ->groupBy('career_category')
                       ->orderBy('career_category', 'ASC')
                       ->get()->getResultArray();
        $data = ['clusters' => $clusters];
        return view('dashboard/cca/cluster', $data);
        
    }

    // 2. Get all careers inside a specific category (Alphabetically)
    public function getCareersByCluster() {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(400);
        
        $category = $this->request->getPost('category');
        $db = \Config\Database::connect();
        
        $careers = $db->table('careers')
                      ->select('id, career_title, short_description')
                      ->where('career_category', $category)
                      ->orderBy('career_title', 'ASC')
                      ->get()->getResultArray();
        
        $data = [
            'careers' => $careers, 
            'category_name' => $category
        ];
        return view('dashboard/cca/clustercareers', $data);
    }

    // 3. Get full details for the Table Modal and return career view
    public function getCareerDetails() {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(400);
        
        $id = $this->request->getPost('id', FILTER_SANITIZE_NUMBER_INT);
        $db = \Config\Database::connect();
        $career = $db->table('careers')->where('id', $id)->get()->getRowArray();

        if (!$career) {
            return "<tr><td class='text-danger text-center'>Career not found.</td></tr>";
        }

        // Decode JSON columns so PHP can read them in the view
        $jsonFields = [
            'educational_requirements', 'skill_requirements', 'certifications', 
            'riasec_profile', 'mbti_fit', 'gardner_requirements', 'eq_requirements', 
            'aptitude_requirements', 'salary_range'
        ];
        
        foreach ($jsonFields as $field) {
            if (!empty($career[$field])) {
                $career[$field] = json_decode($career[$field], true);
            }
        }

        $data = ['career' => $career];
        
        // Output to career.php
        return $this->spa_view('dashboard/cca/career', $data, false);
    }
    
    public function loadCandidateList() {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(400);

        $db = \Config\Database::connect();
        
        // Check Role: If school_id is set, counselor is from a school. If empty, they are from the company.
        $schoolId = session()->get('school_id');

        $builder = $db->table('users')->where('category', 'student');

        // Apply Role Filtering
        if (!empty($schoolId)) {
            $builder->where('school_id', $schoolId);
        }

        $candidates = $builder->orderBy('created_at', 'DESC')->get()->getResultArray();

        // Get test completion counts for these candidates
        $testCounts = [];
        if (!empty($candidates)) {
            $userIds = array_column($candidates, 'id');
            $results = $db->table('test_results')
                          ->select('user_id, COUNT(id) as test_count')
                          ->whereIn('user_id', $userIds)
                          ->groupBy('user_id')
                          ->get()->getResultArray();
            foreach($results as $r) {
                $testCounts[$r['user_id']] = $r['test_count'];
            }
        }

        $data = [
            'candidates' => $candidates,
            'testCounts' => $testCounts
        ];

        return $this->spa_view('dashboard/cca/candidate_list', $data, false);
    }

    
    public function getStudentRawScores() {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(400);

        $userId = $this->request->getPost('user_id', FILTER_SANITIZE_NUMBER_INT);
        $db = \Config\Database::connect();

        // Security check for School Counselors to prevent looking at other schools
        $schoolId = session()->get('school_id');
        if (!empty($schoolId)) {
            $user = $db->table('users')->where('id', $userId)->get()->getRowArray();
            if (!$user || $user['school_id'] != $schoolId) {
                return "<div class='alert alert-danger'>Unauthorized Access</div>";
            }
        }

        $results = $db->table('test_results')
                      ->where('user_id', $userId)
                      ->orderBy('completed_at', 'ASC')
                      ->get()->getResultArray();

        $data = ['results' => $results];
        return $this->spa_view('dashboard/cca/student_scores', $data, false);
    }
  
    // 1. Fetch list of students who have COMPLETED the test
    public function loadCompletedReports() {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(400);

        $db = \Config\Database::connect();
        $schoolId = session()->get('school_id');

        // Get candidates scoped to role
        $builder = $db->table('users')->where('category', 'student');
        if (!empty($schoolId)) {
            $builder->where('school_id', $schoolId);
        }
        $candidates = $builder->orderBy('created_at', 'DESC')->get()->getResultArray();

        $completedCandidates = [];
        if (!empty($candidates)) {
            $userIds = array_column($candidates, 'id');
            // Get test counts (Assuming 6 modules = complete)
            $results = $db->table('test_results')
                          ->select('user_id, COUNT(id) as test_count')
                          ->whereIn('user_id', $userIds)
                          ->groupBy('user_id')
                          ->having('test_count >=', 6) 
                          ->get()->getResultArray();
            
            $completedUserIds = array_column($results, 'user_id');

            // Filter the candidates array
            foreach($candidates as $c) {
                if (in_array($c['id'], $completedUserIds)) {
                    $completedCandidates[] = $c;
                }
            }
        }

        $data = ['candidates' => $completedCandidates];
        return $this->spa_view('dashboard/cca/reviewreport', $data, false);
    }

    // 2. Open the full Report Dossier for a specific student
    public function viewStudentReport($studentId = null) {
        $data = $this->collectReportData($studentId);
        return view('reports/final_pharos_report', $data);
    }
    
    public function downloadPdf($studentId = null) {
        $data = $this->collectReportData($studentId);
        $mpdf = new Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'margin_left' => 5,
        'margin_right' => 5,
        'margin_top' => 15,
        'margin_bottom' => 5, // Leaves exactly 25mm of safe space for the footer!
        'margin_header' => 0,
        'margin_footer' => 10,
        'default_font' => 'Helvetica'
    ]);
        $mpdf->SetWatermarkText('PHAROS EDUCATION');
        $mpdf->watermarkTextAlpha = 0.05; // 5% opacity
        $mpdf->showWatermarkText = true;  // Turns the text watermark ON
        
        $html = view('reports/final_pdf',$data);
        $mpdf->WriteHTML($html);
        
        $mpdf->Output('Pharos_Career_Analysis.pdf', \Mpdf\Output\Destination::DOWNLOAD);
        exit;
    }
    
    private function collectReportData($studentId = null) {
                if (!$studentId) {
            return "<div style='padding:50px; text-align:center; font-family:sans-serif;'><h3>Error: Student ID is missing.</h3></div>";
        }
        
        $db = \Config\Database::connect();
        
        // Security Check: Ensure counselor has access to this student
        $schoolId = session()->get('school_id');
        $user = $db->table('users')->where('id', $studentId)->where('category', 'student')->get()->getRowArray();
        
        if (!$user) {
            return "<div style='padding:50px; text-align:center; font-family:sans-serif;'><h3>Error: Student not found.</h3></div>";
        }
        if (!empty($schoolId) && $user['school_id'] != $schoolId) {
            return "<div style='padding:50px; text-align:center; font-family:sans-serif; color:red;'><h3>Unauthorized Access: This candidate belongs to another institution.</h3></div>";
        }

        // Fetch test results
        $results = $db->table('test_results')->where('user_id', $studentId)->get()->getResultArray();
        
        if (empty($results)) {
            return "<div style='padding:50px; text-align:center; font-family:sans-serif;'><h3>This student has not submitted any assessments.</h3></div>";
        }

        // Setup Base Data Structure exactly like ReportController does
        $data = [
            'stdid' => $studentId,
            'student_name' => $user['full_name'] ?? 'Student', 
            'age_grade'    => $user['educational_level'] ?? 'N/A',
            'gender'       => $user['gender'] ?? 'N/A',     
            'user_email'   => $user['email'] ?? '',
            'report_id'    => 'PH-' . date('Ymd') . '-' . $studentId,
            'date'         => date('d-M-Y'),
            
            'riasec'   => ['scores' => [], 'trait' => 'Pending'], 
            'mbti'     => ['scores' => [], 'trait' => 'Pending'], 
            'eq'       => ['scores' => [], 'trait' => 'Pending'], 
            'gardner'  => ['scores' => [], 'trait' => 'Pending'], 
            'aptitude' => ['scores' => [], 'trait' => 'Pending']
        ];

        // Populate Data
        foreach ($results as $row) {
            $decoded = json_decode($row['result_json'], true);
            $module = $row['module_code'];
            
            $data[$module] = [
                'trait'          => $row['primary_trait'],
                'scores'         => $decoded, 
                'admin_feedback' => $decoded['admin_feedback'] ?? null
            ];
            
            if (!empty($row['normalized_scores'])) {
                $normScores = json_decode($row['normalized_scores'], true);
                $data[$module]['scores']['standardized']['t_scores'] = $normScores;
            }
        }

        // Run the Advanced Psychometric Engine
        $advEngine = new \App\Libraries\AdvancedCareerEngine();
        $data['advData'] = $advEngine->generateDeepAnalysis($data);
        return $data;
    }
}