<?php
namespace App\Controllers;

use App\Libraries\ReportGenerator;

class ReportController extends BaseController
{
    public function view($reportCode)
    {
        $auth = $this->requireAuth();
        if ($auth !== null) return $auth;
        
        $reportGen = new ReportGenerator();
        $report = $reportGen->getReportByCode($reportCode);
        
        if (!$report || $report['user_id'] != $this->currentUser['id']) {
            return redirect()->to('/dashboard')->with('error', 'Report not found');
        }
        
        return view('assessment/report', ['report' => $report]);
    }
    
    public function download($reportCode)
    {
        $auth = $this->requireAuth();
        if ($auth !== null) return $auth;
        
        $reportGen = new ReportGenerator();
        $report = $reportGen->getReportByCode($reportCode);
        
        if (!$report || $report['user_id'] != $this->currentUser['id']) {
            return $this->error('Report not found', null, 404);
        }
        
        $pdf = $reportGen->generatePDF($report);
        
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="career_report_'.$reportCode.'.pdf"')
            ->setBody($pdf);
    }
}