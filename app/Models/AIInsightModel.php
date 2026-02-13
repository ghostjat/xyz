<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * AI Insights Model
 */
class AIInsightModel extends Model
{
    protected $table = 'ai_insights';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'report_id', 'insight_type', 'insight_title', 'insight_text', 'priority'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = false;

    public function getReportInsights(int $reportId, string $type = null)
    {
        $builder = $this->where('report_id', $reportId);
        
        if ($type) {
            $builder->where('insight_type', $type);
        }
        
        return $builder->orderBy('priority', 'DESC')
                      ->orderBy('created_at', 'ASC')
                      ->findAll();
    }
}