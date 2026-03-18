<?php namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\UserModel;
use App\Libraries\AdvancedCareerEngine;

class VerifyController extends Controller
{
    public function index()
    {
        $token = $this->request->getGet('token');

        if (empty($token)) {
            return "<h2 style='text-align:center; margin-top:50px; font-family:sans-serif;'>Invalid Verification Link</h2>";
        }

        // 1. Look up the token in the secure database
        $userModel = new UserModel();
        $user = $userModel->where('verification_token', $token)->first();

        // 2. Reject if the token is tampered or fake
        if (!$user) {
            return "<div style='text-align:center; padding: 50px; font-family: sans-serif;'>
                        <h2 style='color:#c0392b;'>Authenticity Alert</h2>
                        <p>We cannot verify this document. The cryptographic token does not exist in the Pharos secure database. This report may have been altered or falsified.</p>
                    </div>";
        }

        // =========================================================================
        // 3. SECURE DATA FETCHING FOR THE VERIFIED TOKEN
        // We replicate the exact logic of collectReportData() here, securely tied
        // ONLY to the user ID that matched the cryptographic token.
        // =========================================================================
        $userId = $user['id'];
        $db = \Config\Database::connect();
        
        $results = $db->table('test_results')->where('user_id', $userId)->get()->getResultArray();
        
        $data = [
            'student_name' => $user['full_name'] ?? 'Student', 
            'age_grade'    => $user['educational_level'] ?? 'N/A',
            'gender'       => $user['gender'] ?? 'N/A',     
            'user_email'   => $user['email'] ?? '',
            'report_id'    => 'PH-' . date('Ymd') . '-' . $userId,
            'date'         => date('d-M-Y'),
            'riasec'       => ['scores' => [], 'trait' => 'Pending'], 
            'mbti'         => ['scores' => [], 'trait' => 'Pending'], 
            'eq'           => ['scores' => [], 'trait' => 'Pending'], 
            'gardner'      => ['scores' => [], 'trait' => 'Pending'], 
            'aptitude'     => ['scores' => [], 'trait' => 'Pending']
        ];

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

        // Run the Psychometric Engine
        $advEngine = new AdvancedCareerEngine();
        $data['advData'] = $advEngine->generateDeepAnalysis($data);

        // Inject the verification details for the view
        $data['verification_token'] = $token;
        $data['verification_url']   = base_url('verify?token=' . $token);

        // Safely render the HTML report. Because there is no active session, 
        // the "Save as PDF" buttons will automatically be hidden from the public viewer!
        return view('reports/final_pharos_report', $data);
    }
}