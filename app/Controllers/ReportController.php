<?php namespace App\Controllers;

use App\Models\TestResultModel;
use App\Models\UserModel;
use App\Libraries\AdvancedCareerEngine;
use App\Libraries\ReportContentMap;
use Mpdf\Mpdf;

class ReportController extends BaseController {

    private function requireAuth() {
        $userId = session()->get('user_id');
        if (!$userId) {
            header("Location: " . base_url('login'));
            exit();
        }
        return $userId;
    }

    public function index() {
        return $this->viewReport();
    }

    public function viewReport() {
        $data = $this->collectReportData();
        return $this->spa_view('reports/final_pharos_report', $data, false);
    }
    
    public function downloadPdf() {
        
        // 1. Collect your existing report data
        $data = $this->collectReportData();

        $userId = session()->get('user_id') ?? session()->get('id'); 
        $userModel = null;
        
        // =====================================================================
        // PRE-CLEANUP (DELETE OLD REPORT IF IT EXISTS)
        // =====================================================================
        if ($userId) {
            $userModel = new \App\Models\UserModel(); // Ensure correct namespace
            $existingUser = $userModel->find($userId);
            
            // Check if the user already has a report saved
            if ($existingUser && !empty($existingUser['report_file_path'])) {
                $oldFilePath = WRITEPATH . $existingUser['report_file_path'];
                
                // If the physical file still exists on the server, delete it
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }
            }
        }
        // =====================================================================

        // 2. CRYPTOGRAPHIC TOKEN GENERATION (Using Anchored Date)
        $userEmail = $data['user_email'] ?? 'student@example.com';
        $reportId  = $data['report_id']; // Accurately pulled from collectReportData
        $evalDate  = $data['raw_eval_date']; // Accurately pulled from collectReportData

        // Generate the secure token using the locked evaluation date
        $rawString = $reportId . '|' . $userEmail . '|' . $evalDate . '|PharosSecureSalt2026';
        $token = 'PH-' . strtoupper(substr(hash('sha256', $rawString), 0, 12));
        
        $verificationUrl = base_url('verify?token=' . $token);

        // Inject the verification variables into your $data array
        $data['verification_token'] = $token;
        $data['verification_url']   = $verificationUrl;

        // 3. Initialize mPDF with your specific layout
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 5,
            'margin_right' => 5,
            'margin_top' => 15,
            'margin_bottom' => 5,
            'margin_header' => 0,
            'margin_footer' => 10,
            'default_font' => 'Helvetica'
        ]);

        $mpdf->SetWatermarkText('PHAROS EDUCATION');
        $mpdf->watermarkTextAlpha = 0.05; 
        $mpdf->showWatermarkText = true;  
        
        // 4. Render the HTML
        $html = view('reports/final_pdf', $data);
        $mpdf->WriteHTML($html);
        
        // 5. SAVE TO SERVER & UPDATE DATABASE
        $cleanName = preg_replace('/[^a-zA-Z0-9]/', '_', $data['student_name'] ?? 'Student');
        $fileName = 'Pharos_Report_' . $cleanName . '_' . $token . '.pdf';
        
        $saveDirectory = WRITEPATH . 'records/';
        if (!is_dir($saveDirectory)) {
            mkdir($saveDirectory, 0755, true);
        }
        
        $fullFilePath = $saveDirectory . $fileName;

        // Save the NEW physical file to your server
        $mpdf->Output($fullFilePath, \Mpdf\Output\Destination::FILE);

        // Update the database with the NEW token and file path
        if ($userId && $userModel) {
            $userModel->update($userId, [
                'verification_token' => $token,
                'report_file_path'   => 'records/' . $fileName
            ]);
        }

        // 6. Force download the file to the user's browser
        $mpdf->Output($fileName, \Mpdf\Output\Destination::DOWNLOAD);
        exit;
    }

    private function collectReportData() {
        $userId = $this->requireAuth();
        $db = \Config\Database::connect();
        
        // 1. Get User Info
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        // 2. Fetch all completed tests for this user
        $results = $db->table('test_results')->where('user_id', $userId)->get()->getResultArray();
        
        // =====================================================================
        // TRUE EVALUATION DATE CALCULATION
        // Finds the exact timestamp of the student's final submitted test.
        // =====================================================================
        $latestTestTime = 0;
        foreach ($results as $r) {
            $time = 0;
            // Check standard CodeIgniter timestamp columns
            if (!empty($r['created_at'])) {
                $time = strtotime($r['created_at']);
            } elseif (!empty($r['updated_at'])) {
                $time = strtotime($r['updated_at']);
            }
            if ($time > $latestTestTime) {
                $latestTestTime = $time;
            }
        }
        
        // Safe Fallback: If test dates are missing, use account creation date or today.
        if ($latestTestTime === 0) {
            $latestTestTime = !empty($user['created_at']) ? strtotime($user['created_at']) : time();
        }

        // Format the locked dates
        $evalDateDisplay = date('d-M-Y', $latestTestTime); // e.g., 17-Mar-2026
        $evalDateID      = date('Ymd', $latestTestTime);   // e.g., 20260317
        $evalDateRaw     = date('Y-m-d', $latestTestTime); // e.g., 2026-03-17
        // =====================================================================

        // 3. Initialize Base Data Structure
        $data = [
            'student_name' => $user['full_name'] ?? 'Student', 
            'age_grade'    => $user['educational_level'] ?? 'N/A',
            'gender'       => $user['gender'] ?? 'N/A',     
            'user_email'   => $user['email'] ?? '',
            
            // Anchored to the TRUE completion date
            'report_id'    => 'PH-' . $evalDateID . '-' . $userId,
            'date'         => $evalDateDisplay,
            'raw_eval_date'=> $evalDateRaw, 
            
            // Pre-fill modules so the engine doesn't crash if a test is missing
            'riasec'   => ['scores' => [], 'trait' => 'Pending'], 
            'mbti'     => ['scores' => [], 'trait' => 'Pending'], 
            'eq'       => ['scores' => [], 'trait' => 'Pending'], 
            'gardner'  => ['scores' => [], 'trait' => 'Pending'], 
            'aptitude' => ['scores' => [], 'trait' => 'Pending']
        ];

        // 4. Populate Data from Database
        foreach ($results as $row) {
            $decoded = json_decode($row['result_json'], true);
            $module = $row['module_code'];
            
            $data[$module] = [
                'trait'          => $row['primary_trait'],
                'scores'         => $decoded, 
                'admin_feedback' => $decoded['admin_feedback'] ?? null
            ];
            
            // CRITICAL SAFETY CHECK: We know your database uses a strict 'normalized_scores' column.
            if (!empty($row['normalized_scores'])) {
                $normScores = json_decode($row['normalized_scores'], true);
                $data[$module]['scores']['standardized']['t_scores'] = $normScores;
            }
        }

        // 5. Run the Advanced Psychometric Engine
        $advEngine = new AdvancedCareerEngine();
        $data['advData'] = $advEngine->generateDeepAnalysis($data);

        // =====================================================================
        // INJECT SECURE TOKEN FOR HTML VIEWS
        // Ensures the QR code is generated even when viewing the report in-browser.
        // =====================================================================
        if (empty($user['verification_token'])) {
            // Use the locked evaluation date for the hash, not the current date!
            $rawString = $data['report_id'] . '|' . ($user['email'] ?? '') . '|' . $data['raw_eval_date'] . '|PharosSecureSalt2026';
            $token = 'PH-' . strtoupper(substr(hash('sha256', $rawString), 0, 12));
            
            $userModel->update($userId, ['verification_token' => $token]);
        } else {
            $token = $user['verification_token'];
        }

        $data['verification_token'] = $token;
        $data['verification_url']   = base_url('verify?token=' . $token);
        // =====================================================================

        return $data;
    }
}