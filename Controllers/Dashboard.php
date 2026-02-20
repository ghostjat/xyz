<?php namespace App\Controllers;

use App\Models\TestResultModel;

class DashboardController extends BaseController {

    public function index() {
        // 1. Get User ID (Fallback to 1 for testing)
        $userId = $this->session->get('user_id') ?? 1;

        // 2. Define All Available Test Modules
        // In a real app, you might fetch this from the 'test_modules' table
        $modules = [
            'riasec'   => ['name' => 'Career Interest (RIASEC)', 'desc' => 'Discover the careers that match your personality.', 'icon' => 'briefcase'],
            'mbti'     => ['name' => 'Personality Type (MBTI)', 'desc' => 'Understand how you perceive the world and make decisions.', 'icon' => 'person-badge'],
            'eq'       => ['name' => 'Emotional Intelligence', 'desc' => 'Measure your ability to manage emotions and relationships.', 'icon' => 'heart'],
            'gardner'  => ['name' => 'Multiple Intelligences', 'desc' => 'Find out your unique learning style and strengths.', 'icon' => 'lightbulb'],
            'attitude' => ['name' => 'Professional Aptitude', 'desc' => 'Assess your workplace attitude and soft skills.', 'icon' => 'graph-up'],
        ];

        // 3. Fetch Completed Tests for this User
        $resModel = new TestResultModel();
        $completedRaw = $resModel->where('user_id', $userId)->findAll();

        // Map results to module codes for easy lookup
        $completedMap = [];
        foreach($completedRaw as $row) {
            // We store the primary trait so we can show it on the dashboard card (e.g., "ENTJ")
            $completedMap[$row['module_code']] = $row['primary_trait'];
        }

        // 4. Merge Status into Modules Array
        foreach($modules as $code => &$details) {
            if(isset($completedMap[$code])) {
                $details['status'] = 'completed';
                $details['result_summary'] = $completedMap[$code];
            } else {
                $details['status'] = 'pending';
                $details['result_summary'] = null;
            }
            $details['code'] = $code;
        }

        // 5. Render View using your BaseController's spa_view logic
        return $this->spa_view('dashboard', ['modules' => $modules]);
    }
}