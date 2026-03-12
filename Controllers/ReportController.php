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
        $data = $this->collectReportData();
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

    private function collectReportData() {
        $userId = $this->requireAuth();
        $db = \Config\Database::connect();
        
        // 1. Get User Info
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        // 2. Fetch all completed tests for this user
        $results = $db->table('test_results')->where('user_id', $userId)->get()->getResultArray();
        
        // 3. Initialize Base Data Structure
        $data = [
            'student_name' => $user['full_name'] ?? 'Student', 
            'age_grade'    => $user['educational_level'] ?? 'N/A',
            'gender'       => $user['gender'] ?? 'N/A',     
            'user_email'   => $user['email'] ?? '',
            'report_id'    => 'PH-' . date('Ymd') . '-' . $userId,
            'date'         => date('d-M-Y'),
            
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
            // This ensures that even if the T-scores were stripped from the main JSON, 
            // the controller forcefully injects them back into the array for the Engine to read.
            if (!empty($row['normalized_scores'])) {
                $normScores = json_decode($row['normalized_scores'], true);
                // Forcefully ensure the T-scores exist where the engine expects them
                $data[$module]['scores']['standardized']['t_scores'] = $normScores;
            }
        }

        // 5. Run the Advanced Psychometric Engine
        $advEngine = new AdvancedCareerEngine();
        
        // We pass the ENTIRE $data array to the new engine, 
        // and assign the output to 'advData' so the PDF View can access it.
        $data['advData'] = $advEngine->generateDeepAnalysis($data);

        return $data;
    }
}