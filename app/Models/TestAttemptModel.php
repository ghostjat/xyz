<?php
namespace App\Models;

use CodeIgniter\Model;

/**
 * Test Attempt Model
 */
class TestAttemptModel extends Model
{
    protected $table = 'test_attempts';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'session_id', 'category_id', 'status', 'started_at',
        'completed_at', 'duration_seconds', 'total_questions', 'answered_questions'
    ];
    protected $useTimestamps = true;

    public function getSessionAttempts(int $sessionId)
    {
        return $this->select('test_attempts.*, test_categories.category_name, test_categories.category_code')
                    ->join('test_categories', 'test_categories.id = test_attempts.category_id')
                    ->where('session_id', $sessionId)
                    ->findAll();
    }
}