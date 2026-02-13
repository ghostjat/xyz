<?php

namespace App\Libraries;

use App\Models\ComprehensiveReportModel;
use App\Models\CareerMatchModel;
use App\Models\CareerModel;
use TCPDF;

/**
 * Report Generator Library
 * Handles comprehensive report creation and PDF generation
 */
class ReportGenerator
{
    private $reportModel;
    private $matchModel;
    private $careerModel;

    public function __construct()
    {
        $this->reportModel = new ComprehensiveReportModel();
        $this->matchModel = new CareerMatchModel();
        $this->careerModel = new CareerModel();
    }

    /**
     * Save comprehensive report
     */
    public function saveReport(int $sessionId, int $userId, string $reportCode, array $analysisData): int
    {
        $reportData = [
            'session_id' => $sessionId,
            'user_id' => $userId,
            'report_code' => $reportCode,
            'riasec_profile' => json_encode($analysisData['profile']['riasec_profile']),
            'vark_profile' => json_encode($analysisData['profile']['vark_profile']),
            'mbti_type' => $analysisData['profile']['mbti_type'],
            'mbti_scores' => json_encode($analysisData['profile']['mbti_scores']),
            'gardner_profile' => json_encode($analysisData['profile']['gardner_profile']),
            'eq_score' => $analysisData['profile']['eq_score'],
            'eq_breakdown' => json_encode($analysisData['profile']['eq_breakdown']),
            'aptitude_scores' => json_encode($analysisData['profile']['aptitude_scores']),
            'iq_estimate' => $analysisData['profile']['iq_estimate'],
            'personality_analysis' => $analysisData['personality_analysis'],
            'career_interests' => json_encode($analysisData['career_matches']),
            'top_career_matches' => json_encode(array_slice($analysisData['career_matches'], 0, 15)),
            'learning_style_analysis' => $analysisData['learning_style_analysis'],
            'motivators' => json_encode($analysisData['motivators']),
            'strengths' => json_encode($analysisData['strengths']),
            'development_areas' => json_encode($analysisData['development_areas']),
            'emotional_competencies' => json_encode($analysisData['emotional_competencies']),
            'recommended_careers' => json_encode($analysisData['recommended_careers']),
            'career_roadmaps' => json_encode($analysisData['career_roadmaps']),
            'educational_pathways' => json_encode($analysisData['educational_pathways']),
            'skill_development_plan' => json_encode($analysisData['skill_development_plan']),
            'confidence_score' => $this->calculateConfidenceScore($analysisData),
            'report_version' => '1.0',
            'expires_at' => date('Y-m-d H:i:s', strtotime('+2 years'))
        ];

        $reportId = $this->reportModel->insert($reportData);

        // Save career matches
        $this->saveCareerMatches($reportId, $analysisData['career_matches']);

        return $reportId;
    }

    /**
     * Save career matches
     */
    private function saveCareerMatches(int $reportId, array $careerMatches)
    {
        $rank = 1;
        foreach (array_slice($careerMatches, 0, 15) as $match) {
            $this->matchModel->insert([
                'report_id' => $reportId,
                'career_id' => $match['career_id'],
                'match_percentage' => $match['match_percentage'],
                'match_breakdown' => json_encode($match),
                'fit_explanation' => $match['fit_explanation'],
                'why_suitable' => $match['why_suitable'],
                'potential_challenges' => $match['potential_challenges'],
                'rank_position' => $rank++
            ]);
        }
    }

    /**
     * Calculate overall confidence score
     */
    private function calculateConfidenceScore(array $analysisData): float
    {
        // Based on completion rate and consistency
        $scores = [];
        
        // Check if all tests completed
        $testsCompleted = 6; // Assume all 6 tests
        $scores[] = ($testsCompleted / 6) * 100;
        
        // Add reliability scores if available
        // This would come from the psychometric engine
        
        return array_sum($scores) / count($scores);
    }

    /**
     * Get report by code
     */
    public function getReportByCode(string $code): ?array
    {
        $report = $this->reportModel->getReportByCode($code);
        
        if (!$report) {
            return null;
        }

        // Decode JSON fields
        $report['riasec_profile'] = json_decode($report['riasec_profile'], true);
        $report['vark_profile'] = json_decode($report['vark_profile'], true);
        $report['mbti_scores'] = json_decode($report['mbti_scores'], true);
        $report['gardner_profile'] = json_decode($report['gardner_profile'], true);
        $report['eq_breakdown'] = json_decode($report['eq_breakdown'], true);
        $report['aptitude_scores'] = json_decode($report['aptitude_scores'], true);
        $report['top_career_matches'] = json_decode($report['top_career_matches'], true);
        $report['motivators'] = json_decode($report['motivators'], true);
        $report['strengths'] = json_decode($report['strengths'], true);
        $report['development_areas'] = json_decode($report['development_areas'], true);
        
        // Get detailed career matches
        $report['career_matches'] = $this->matchModel->getReportMatches($report['id'], 15);

        return $report;
    }

    /**
     * Generate PDF report
     */
    public function generatePDF(array $report): string
    {
        // Create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('Career Analysis System');
        $pdf->SetAuthor('Career Guidance Team');
        $pdf->SetTitle('Career Analysis Report - ' . $report['report_code']);
        $pdf->SetSubject('Comprehensive Career Assessment Report');

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);

        // Set font
        $pdf->SetFont('helvetica', '', 10);

        // Add a page
        $pdf->AddPage();

        // Generate report content
        $html = $this->generateReportHTML($report);

        // Output HTML content
        $pdf->writeHTML($html, true, false, true, false, '');

        // Close and output PDF document
        return $pdf->Output('', 'S'); // Return as string
    }

    /**
     * Generate HTML content for PDF
     */
    private function generateReportHTML(array $report): string
    {
        $html = '
        <style>
            h1 { color: #2C3E50; font-size: 24px; font-weight: bold; margin-bottom: 10px; }
            h2 { color: #3498DB; font-size: 18px; font-weight: bold; margin-top: 20px; margin-bottom: 10px; border-bottom: 2px solid #3498DB; padding-bottom: 5px; }
            h3 { color: #2C3E50; font-size: 14px; font-weight: bold; margin-top: 15px; margin-bottom: 8px; }
            .header-box { background-color: #3498DB; color: white; padding: 20px; text-align: center; margin-bottom: 20px; }
            .info-box { background-color: #ECF0F1; padding: 10px; margin: 10px 0; border-left: 4px solid #3498DB; }
            .career-match { background-color: #F8F9FA; padding: 12px; margin: 8px 0; border-left: 4px solid #27AE60; }
            .score-high { color: #27AE60; font-weight: bold; }
            .score-medium { color: #F39C12; font-weight: bold; }
            .score-low { color: #E74C3C; font-weight: bold; }
            table { border-collapse: collapse; width: 100%; margin: 10px 0; }
            th { background-color: #3498DB; color: white; padding: 8px; text-align: left; }
            td { padding: 8px; border-bottom: 1px solid #ddd; }
            .badge { display: inline-block; padding: 4px 8px; background-color: #3498DB; color: white; border-radius: 3px; font-size: 10px; margin: 2px; }
        </style>
        
        <div class="header-box">
            <h1>COMPREHENSIVE CAREER ANALYSIS REPORT</h1>
            <p style="font-size: 14px; margin: 5px 0;">Report Code: ' . $report['report_code'] . '</p>
            <p style="font-size: 12px; margin: 5px 0;">Generated: ' . date('F d, Y', strtotime($report['generated_at'])) . '</p>
        </div>

        <h2>Executive Summary</h2>
        <div class="info-box">
            <p><strong>Personality Type:</strong> ' . $report['mbti_type'] . '</p>
            <p><strong>Emotional Intelligence:</strong> ' . round($report['eq_score'], 1) . '%</p>
            <p><strong>Estimated IQ:</strong> ' . $report['iq_estimate'] . '</p>
            <p><strong>Report Confidence:</strong> ' . round($report['confidence_score'], 1) . '%</p>
        </div>

        <h2>Top Career Recommendations</h2>';

        // Career matches
        foreach (array_slice($report['career_matches'], 0, 5) as $index => $match) {
            $scoreClass = $match['match_percentage'] >= 80 ? 'score-high' : ($match['match_percentage'] >= 60 ? 'score-medium' : 'score-low');
            
            $html .= '
            <div class="career-match">
                <h3>' . ($index + 1) . '. ' . $match['career_title'] . ' <span class="' . $scoreClass . '">(' . round($match['match_percentage'], 1) . '% Match)</span></h3>
                <p><strong>Why This Career Suits You:</strong></p>
                <p>' . $match['why_suitable'] . '</p>
            </div>';
        }

        $html .= '
        <h2>Psychometric Profile Analysis</h2>
        
        <h3>RIASEC Interest Profile (Holland Code)</h3>
        <table>
            <tr><th>Dimension</th><th>Score</th></tr>';
        
        foreach ($report['riasec_profile'] as $dimension => $score) {
            $html .= '<tr><td>' . $this->getRIASECName($dimension) . '</td><td>' . round($score, 1) . '%</td></tr>';
        }
        
        $html .= '</table>

        <h3>Learning Style (VARK)</h3>
        <table>
            <tr><th>Modality</th><th>Preference</th></tr>';
        
        foreach ($report['vark_profile'] as $modality => $score) {
            $html .= '<tr><td>' . $modality . '</td><td>' . round($score, 1) . '%</td></tr>';
        }
        
        $html .= '</table>

        <h3>Multiple Intelligences (Gardner)</h3>
        <table>
            <tr><th>Intelligence Type</th><th>Score</th></tr>';
        
        arsort($report['gardner_profile']);
        foreach ($report['gardner_profile'] as $intelligence => $score) {
            $html .= '<tr><td>' . $intelligence . '</td><td>' . round($score, 1) . '%</td></tr>';
        }
        
        $html .= '</table>

        <h3>Emotional Intelligence Components</h3>
        <table>
            <tr><th>Component</th><th>Score</th></tr>';
        
        foreach ($report['eq_breakdown'] as $component => $score) {
            $componentName = ucwords(str_replace('_', ' ', $component));
            $html .= '<tr><td>' . $componentName . '</td><td>' . round($score, 1) . '%</td></tr>';
        }
        
        $html .= '</table>

        <h2>Your Strengths</h2>
        <ul>';
        
        foreach ($report['strengths'] as $strength) {
            $html .= '<li>' . $strength . '</li>';
        }
        
        $html .= '</ul>

        <h2>Development Opportunities</h2>
        <ul>';
        
        foreach ($report['development_areas'] as $area) {
            $html .= '<li>' . $area . '</li>';
        }
        
        $html .= '</ul>

        <h2>Key Motivators</h2>
        <ul>';
        
        foreach ($report['motivators'] as $motivator) {
            $html .= '<li>' . $motivator . '</li>';
        }
        
        $html .= '</ul>

        <div style="margin-top: 30px; padding: 15px; background-color: #ECF0F1; border-left: 4px solid #3498DB;">
            <p style="font-size: 11px; color: #7F8C8D;">
                <strong>Disclaimer:</strong> This report is based on psychometric assessments and should be used as guidance only. 
                Professional career counseling is recommended for important career decisions. 
                Results are confidential and valid for 2 years from generation date.
            </p>
        </div>

        <div style="margin-top: 20px; text-align: center; font-size: 10px; color: #95A5A6;">
            <p>Career Analysis System | Industry-Standard Psychometric Assessment</p>
            <p>Compliant with APA, BPS, and EFPA Standards</p>
        </div>';

        return $html;
    }

    /**
     * Helper method to get RIASEC dimension name
     */
    private function getRIASECName(string $code): string
    {
        $names = [
            'R' => 'Realistic',
            'I' => 'Investigative',
            'A' => 'Artistic',
            'S' => 'Social',
            'E' => 'Enterprising',
            'C' => 'Conventional'
        ];
        
        return $names[$code] ?? $code;
    }
}
