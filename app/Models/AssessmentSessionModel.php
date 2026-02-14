<?php
namespace App\Models;

use CodeIgniter\Model;

/**
 * Assessment Session Model
 */
class AssessmentSessionModel extends Model
{
    protected $table = 'assessment_sessions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'user_id', 'session_code', 'age_group', 'status',
        'started_at', 'completed_at', 'total_duration_seconds',
        'ip_address', 'user_agent'
    ];
    protected $useTimestamps = true;

    public function getUserSessions(int $userId)
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    public function getActiveSession(int $userId)
    {
        return $this->where('user_id', $userId)
                    ->whereIn('status', ['not_started', 'in_progress'])
                    ->orderBy('created_at', 'DESC')
                    ->first();
    }
}