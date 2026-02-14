<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Career Match Model
 */
class CareerMatchModel extends Model
{
    protected $table = 'career_matches';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'report_id', 'career_id', 'match_percentage', 'match_breakdown',
        'fit_explanation', 'why_suitable', 'potential_challenges', 'rank_position'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = false;

    public function getReportMatches(int $reportId, int $limit = 10)
    {
        return $this->select('career_matches.*, careers.career_title, careers.career_category')
                    ->join('careers', 'careers.id = career_matches.career_id')
                    ->where('report_id', $reportId)
                    ->orderBy('match_percentage', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }
}