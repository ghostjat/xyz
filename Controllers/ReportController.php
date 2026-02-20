<?php namespace App\Controllers;

use App\Models\TestResultModel;
use App\Libraries\CareerEngine;

class ReportController extends BaseController {

    public function index() {
        $userId = $this->session->get('user_id') ?? 1;
        $db = \Config\Database::connect();
        
        $builder = $db->table('test_results');
        $results = $builder->where('user_id', $userId)->get()->getResultArray();

        // Initialize Data Structure
        $data = [
            'riasec' => null, 'mbti' => null, 'eq' => null, 'gardner' => null, 'aptitude' => null,
            'missing' => []
        ];

        // Populate Data
        foreach ($results as $row) {
            $data[$row['module_code']] = [
                'trait' => $row['primary_trait'],
                'result' => json_decode($row['result_json'], true) // Full Array
            ];
        }

        // Check Completeness
        $required = ['riasec', 'mbti', 'eq', 'gardner', 'aptitude'];
        foreach($required as $req) {
            if(empty($data[$req])) $data['missing'][] = strtoupper($req);
        }

        // RUN DEEP ANALYSIS
        $recommendations = [];
        if (empty($data['missing'])) {
            $engine = new CareerEngine();
            // Pass the FULL result arrays, not just the trait strings
            $recommendations = $engine->generateDeepReport(
                $data['riasec']['result'],
                $data['mbti']['result'],
                $data['gardner']['result'],
                $data['eq']['result']
            );
        }

        return $this->spa_view('reports/full_career_report', [
            'data' => $data,
            'careers' => $recommendations
        ], false);
    }
    
    public function viewReport() {
        $data = $this->collectReportData();
        $advEngine = new \App\Libraries\AdvancedCareerEngine();
        
        // Generate the Weighted Matrix
        $deepAnalysis = $advEngine->generateDeepAnalysis(
            $data['riasec']['scores']['breakdown'],
            $data['mbti']['scores']['breakdown'],
            $data['gardner']['scores']['breakdown'] ?? [],
            $data['eq']['scores']['breakdown'] ?? [],
            $data['aptitude']['scores']['breakdown'] ?? []
        );

        $data['career_clusters'] = $deepAnalysis;
        
        // Render the new "Pharos" template
        return $this->spa_view('reports/final_pharos_report', $data, false);
    }
    
    public function downloadPdf() {
        $data = $this->collectReportData();
        $data['is_pdf'] = true; // Flag to hide buttons in PDF

        $html = view('reports/final_pharos_report', $data);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        $dompdf->stream("Pharos_Career_Assessment.pdf", ["Attachment" => true]);
    }
    
    public function viewOfficialReport() {
        // 1. Fetch Basic Data
        $data = $this->collectReportData(); 

        // 2. RUN TRANSLATION ENGINE (Same as PDF)
        $translator = new \App\Libraries\ReportTranslationEngine();
        $derivedData = $translator->generateFullReportData(
            $data['riasec']['scores']['breakdown'],
            $data['mbti']['scores'], 
            $data['gardner']['scores']['breakdown'] ?? [],
            $data['eq']['scores']['breakdown'] ?? [],
            $data['aptitude']['scores']['breakdown'] ?? []
        );

        $data['derived'] = $derivedData;
        $data['is_preview'] = true; // Flag to show "Download" button

        // 3. Render directly to browser
        return view('reports/official_dossier', $data);
    }

    private function collectReportData() {
        $userId = $this->session->get('user_id') ?? 1;
        $db = \Config\Database::connect();
        
        // 1. Fetch Raw Results
        $results = $db->table('test_results')->where('user_id', $userId)->get()->getResultArray();
        
        // 2. Initialize Data Structure
        $data = [
            'student_name' => 'Student ID: ' . $userId, 
            'age_grade'    => 'Grade 12',     
            'report_id'    => 'PH-' . date('Ymd') . '-' . $userId,
            'date'         => date('F d, Y'),
            'riasec' => ['scores' => ['breakdown' => []], 'trait' => 'Pending'], 
            'mbti' => ['scores' => ['breakdown' => []], 'trait' => 'Pending'], 
            'eq' => ['scores' => ['breakdown' => []], 'trait' => 'Pending'], 
            'gardner' => ['scores' => ['breakdown' => []], 'trait' => 'Pending'], 
            'aptitude' => ['scores' => ['breakdown' => []], 'trait' => 'Pending'],
            'career_clusters' => [] // Default empty
        ];

        foreach ($results as $row) {
            $decoded = json_decode($row['result_json'], true);
            $data[$row['module_code']] = [
                'trait' => $row['primary_trait'],
                'scores' => $decoded
            ];
        }

        // 3. CRITICAL FIX: Use AdvancedCareerEngine to generate 'match' keys
        // Check if we have enough data to run the engine
        if (!empty($data['riasec']['scores']['breakdown']) && !empty($data['mbti']['scores']['breakdown'])) {
            
            $advEngine = new \App\Libraries\AdvancedCareerEngine();
            
            // This generates the array ['Cluster Name' => ['match' => 95, 'desc' => '...']]
            $data['career_clusters'] = $advEngine->generateDeepAnalysis(
                $data['riasec']['scores']['breakdown'],
                $data['mbti']['scores']['breakdown'],
                $data['gardner']['scores']['breakdown'] ?? [],
                $data['eq']['scores']['breakdown'] ?? [],
                $data['aptitude']['scores']['breakdown'] ?? []
            );
        } else {
            // Fallback if tests aren't taken yet
            $data['career_clusters'] = [
                'Data Insufficient' => ['match' => 0, 'desc' => 'Please complete all tests.']
            ];
        }
        // 1. Load the Content Map
        $contentMap = \App\Libraries\ReportContentMap::getMap();

        // 2. Resolve RIASEC Text
        $r_trait = $data['riasec']['trait'] ?? 'Realistic';
        $data['riasec_text'] = $contentMap['riasec'][$r_trait] ?? $contentMap['riasec']['Realistic'];

        // 3. Resolve MBTI Text
        $m_trait = $data['mbti']['trait'] ?? 'ISTJ';
        $data['mbti_text'] = $contentMap['mbti'][$m_trait] ?? $contentMap['mbti']['ISTJ'];

        // 4. Pass the entire Aptitude & Career Map to view for dynamic lookup
        $data['aptitude_map'] = $contentMap['aptitude'];
        $data['career_path_map'] = $contentMap['career_paths'];

        return $data;
    }
    
    // --- PRIVATE HELPER TO GATHER ALL DATA ---
    private function collectReportData_old() {
        $userId = $this->session->get('user_id') ?? 1;
        $db = \Config\Database::connect();
        
        // Fetch raw results
        $results = $db->table('test_results')->where('user_id', $userId)->get()->getResultArray();
        
        // Initialize placeholders
        $data = [
            'student_name' => 'Student ID: ' . $userId, 
            'age_grade'    => 'Grade 12 / Candidate',     
            'report_id'    => 'PH-' . date('Ymd') . '-' . $userId,
            'date'         => date('F d, Y'),
            'riasec' => [], 'mbti' => [], 'eq' => [], 'gardner' => [], 'aptitude' => [],
            'is_pdf' => false
        ];

        foreach ($results as $row) {
            $data[$row['module_code']] = [
                'trait' => $row['primary_trait'],
                'scores' => json_decode($row['result_json'], true)
            ];
        }

        // Generate Career Matches
        $engine = new CareerEngine();
        $rawCareers = [];
        
        if (!empty($data['riasec']) && !empty($data['mbti'])) {
             // Pass full score arrays to engine
             $rawCareers = $engine->generateDeepReport(
                $data['riasec']['scores'], 
                $data['mbti']['scores'], 
                $data['gardner']['scores'] ?? [], 
                $data['eq']['scores'] ?? []
            );
        }

        // CLUSTER LOGIC: Group the raw jobs into "Clusters"
        $data['career_clusters'] = $this->groupCareersByCluster($rawCareers);
        $data['helpers'] = $this->getReportHelpers($data);

        return $data;
    }

    // Helper: Group individual job titles into broad clusters
    private function groupCareersByCluster($careers) {
        $clusters = [];
        foreach($careers as $job) {
            // Simple keyword matching to simulate clustering
            $role = $job['role'];
            $cat = 'General';
            
            if(stripos($role, 'Engineer')!==false || stripos($role, 'Technician')!==false) $cat = 'Engineering & Technology';
            elseif(stripos($role, 'Designer')!==false || stripos($role, 'Artist')!==false || stripos($role, 'Creative')!==false) $cat = 'Design & Creativity';
            elseif(stripos($role, 'Manager')!==false || stripos($role, 'Business')!==false || stripos($role, 'Sales')!==false) $cat = 'Business & Management';
            elseif(stripos($role, 'Scientist')!==false || stripos($role, 'Researcher')!==false || stripos($role, 'Data')!==false) $cat = 'Science & Analytics';
            elseif(stripos($role, 'Doctor')!==false || stripos($role, 'Therapist')!==false) $cat = 'Healthcare & Medicine';
            
            $clusters[$cat][] = $job;
        }
        return $clusters;
    }

    private function getReportHelpers($data) {
        // ... (Same helpers as before for Learning Style/Env/Streams) ...
        return [
            'learning_style' => 'Visual-Spatial', // Placeholder or dynamic logic
            'environment'    => ['Structured', 'Goal-Oriented'],
            'streams'        => ['Science (PCM)', 'Computer Science']
        ];
    }

}