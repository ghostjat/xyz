<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * User Session Model
 */
class UserSessionModel extends Model
{
    protected $table = 'user_sessions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'user_id', 'session_token', 'ip_address', 'user_agent', 'expires_at'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = false;

    public function cleanupExpiredSessions()
    {
        return $this->where('expires_at <', date('Y-m-d H:i:s'))->delete();
    }
}

