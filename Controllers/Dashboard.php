<?php namespace App\Controllers;

use App\Models\TestResultModel;
use App\Models\AppointmentModel;
use App\Models\UserModel;

class Dashboard extends BaseController {

    public function index() {
        
        $userId = $this->session->get('user_id');
        if (!$userId) {
            return redirect()->to('/login');
        }
        
        $userRole = session()->get('category');
        if($userRole === 'admin') {
            return redirect()->to('admin');
        }
        
        if($userRole === 'cca') {
            return redirect()->to('cca');
        }
        
        return $this->studentDashboard($userId);
    }
    
    private function studentDashboard($userId) {
        $aptModel = new AppointmentModel();
        $myAppointments = $aptModel->where('user_id', $userId)->orderBy('preferred_datetime', 'ASC')->findAll();
        $userModel = new UserModel();
        $user = $userModel->find($userId);
        
        // --- PAYMENT CHECK ALGORITHM ---
        $db = \Config\Database::connect();
        $paymentCount = $db->table('payments')
                           ->where('user_id', $userId)
                           ->where('status', 'success')
                           ->countAllResults();
        $hasPaid = ($paymentCount > 0);
        
        // 1. Define All Available Test Modules (ADDED VARK & MOTIVATORS)
        $modules = [
            'riasec'     => ['name' => 'Career Interest', 'icon' => 'fas fa-briefcase', 'url' => 'tests/riasec'],
            'mbti'       => ['name' => 'Personality Type', 'icon' => 'fas fa-fingerprint', 'url' => 'tests/mbti'],
            'eq'         => ['name' => 'Emotional Intelligence', 'icon' => 'fas fa-heart', 'url' => 'tests/eq'],
            'gardner'    => ['name' => 'Multiple Intelligences', 'icon' => 'fas fa-lightbulb', 'url' => 'tests/gardner'],
            'aptitude'   => ['name' => 'Professional Aptitude', 'icon' => 'fas fa-brain', 'url' => 'tests/aptitude'],
            'vark'       => ['name' => 'Learning Styles', 'icon' => 'fas fa-book-open', 'url' => 'tests/vark'],
            'motivators' => ['name' => 'Career Motivators', 'icon' => 'fas fa-bullseye', 'url' => 'tests/motivators'],
        ];

        $resModel = new TestResultModel();
        $recentActivity = $resModel->where('user_id', $userId)->orderBy('completed_at', 'DESC')->findAll();

        $completedMap = [];
        $insights = [
            'mbti' => 'Pending',
            'eq'   => 0,
            'iq'   => 'Pending',
            'vark' => 'Pending',       
            'motivator' => 'Pending'
        ];

        // 2. Extract Insights dynamically from the PsychometricEngine output
        foreach($recentActivity as $row) {
            $completedMap[$row['module_code']] = $row;
            $decoded = json_decode($row['result_json'], true);
            
            if ($row['module_code'] === 'mbti') $insights['mbti'] = $row['primary_trait'];
            if ($row['module_code'] === 'eq') $insights['eq'] = $decoded['overall_eq'] ?? 0;
            if ($row['module_code'] === 'aptitude') $insights['iq'] = $decoded['iq_projection']['score'] ?? 'N/A';
            if ($row['module_code'] === 'vark') $insights['vark'] = $decoded['profile']['style'] ?? 'Pending';
            if ($row['module_code'] === 'motivators') $insights['motivator'] = $decoded['profile']['primary_motivator'] ?? 'Pending';
        }

        // 3. Update the table status UI with Payment Validation
        foreach($modules as $code => &$details) {
            if(isset($completedMap[$code])) {
                $details['status'] = 'completed';
                $details['date'] = date('M d, Y', strtotime($completedMap[$code]['completed_at'] ?? 'now'));
                
                $details['action_url'] = 'javascript:void(0);'; 
                $details['action_text'] = '<i class="fas fa-check-circle me-1"></i> Completed';
                $details['badge'] = 'bg-success-subtle text-success border border-success';
                $details['btn_class'] = 'btn-success disabled';
            } else {
                $details['status'] = 'pending';
                $details['date'] = '-';
                
                // Lock the test if the user hasn't paid
                if (!$hasPaid) {
                    $details['action_url'] = 'javascript:void(0);'; 
                    $details['action_text'] = '<i class="fas fa-lock me-1"></i> Locked';
                    $details['badge'] = 'bg-secondary-subtle text-secondary border border-secondary';
                    $details['btn_class'] = 'btn-secondary disabled';
                } else {
                    $details['action_url'] = base_url($details['url']); 
                    $details['action_text'] = 'Start Test';
                    $details['badge'] = 'bg-warning-subtle text-warning border border-warning';
                    $details['btn_class'] = 'btn-primary';
                }
            }
        }

        $completionRate = count($modules) > 0 ? (count($completedMap) / count($modules)) * 100 : 0;

        $data = [
            'user'            => $user,
            'modules'         => $modules,
            'completed_count' => count($completedMap),
            'completion_rate' => round($completionRate),
            'insights'        => $insights,
            'recent_activity' => $recentActivity,
            'appointments'    => $myAppointments,
            'testResults'     => $completedMap,
            'hasPaid'         => $hasPaid // Pass payment status to the view
        ];

        return $this->spa_view('dashboard/index', $data, false);
    }
    
    public function bookAppointment() {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400);
        }

        $userId = session()->get('user_id');
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);

        $aptModel = new \App\Models\AppointmentModel();
        $aptModel->insert([
            'user_id' => $userId,
            'school_id' => $user['school_id'], 
            'preferred_datetime' => $this->request->getPost('preferred_datetime'),
            'topic' => $this->request->getPost('topic'),
            'status' => 'pending'
        ]);

        return $this->response->setJSON(['status' => 'success', 'msg' => 'Appointment requested successfully!']);
    }
}