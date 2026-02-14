<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Test Result Model
 */
class TestResultModel extends Model
{
    protected $table = 'test_results';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'attempt_id', 'category_id', 'user_id', 'raw_scores',
        'normalized_scores', 'percentile_scores', 'interpretation',
        'reliability_score', 'completion_percentage'
    ];
    protected $useTimestamps = false;
    protected $createdField = 'calculated_at';

    public function getSessionResults(int $sessionId)
    {
        return $this->select('test_results.*, test_categories.category_code, test_categories.category_name')
                    ->join('test_attempts', 'test_attempts.id = test_results.attempt_id')
                    ->join('test_categories', 'test_categories.id = test_results.category_id')
                    ->where('test_attempts.session_id', $sessionId)
                    ->findAll();
    }

    public function getUserResults(int $userId)
    {
        return $this->where('user_id', $userId)
                    ->orderBy('calculated_at', 'DESC')
                    ->findAll();
    }
}