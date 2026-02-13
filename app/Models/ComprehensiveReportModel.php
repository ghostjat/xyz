<?php
namespace App\Models;

use CodeIgniter\Model;
/**
 * Comprehensive Report Model
 */
class ComprehensiveReportModel extends Model
{
    protected $table = 'comprehensive_reports';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'session_id', 'user_id', 'report_code', 'riasec_profile',
        'vark_profile', 'mbti_type', 'mbti_scores', 'gardner_profile',
        'eq_score', 'eq_breakdown', 'aptitude_scores', 'iq_estimate',
        'personality_analysis', 'career_interests', 'top_career_matches',
        'learning_style_analysis', 'motivators', 'strengths',
        'development_areas', 'emotional_competencies', 'recommended_careers',
        'career_roadmaps', 'educational_pathways', 'skill_development_plan',
        'confidence_score', 'report_version', 'expires_at'
    ];
    protected $useTimestamps = false;
    protected $createdField = 'generated_at';

    public function getReportByCode(string $code)
    {
        return $this->where('report_code', $code)->first();
    }

    public function getUserReports(int $userId)
    {
        return $this->where('user_id', $userId)
                    ->orderBy('generated_at', 'DESC')
                    ->findAll();
    }
}