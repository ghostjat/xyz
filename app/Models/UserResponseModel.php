<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * User Response Model
 */
class UserResponseModel extends Model
{
    protected $table = 'user_responses';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'attempt_id', 'question_id', 'response_value', 'response_text',
        'response_json', 'time_taken_seconds', 'is_skipped'
    ];
    protected $useTimestamps = false;
    protected $createdField = 'answered_at';

    public function getAttemptResponses(int $attemptId)
    {
        return $this->where('attempt_id', $attemptId)->findAll();
    }

    public function getResponseCount(int $attemptId, bool $excludeSkipped = true)
    {
        $builder = $this->where('attempt_id', $attemptId);
        
        if ($excludeSkipped) {
            $builder->where('is_skipped', false);
        }
        
        return $builder->countAllResults();
    }
}