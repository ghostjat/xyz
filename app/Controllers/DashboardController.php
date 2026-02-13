<?php
namespace App\Controllers;

class DashboardController extends BaseController
{
    public function index()
    {
        $auth = $this->requireAuth();
        if ($auth !== null) return $auth;
        
        $data = [
            'user' => $this->currentUser,
            'recent_sessions' => model('AssessmentSessionModel')->getUserSessions($this->currentUser['id']),
            'reports' => model('ComprehensiveReportModel')->getUserReports($this->currentUser['id'])
        ];
        
        return view('dashboard/index', $data);
    }
}