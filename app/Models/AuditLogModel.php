<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Audit Log Model
 */
class AuditLogModel extends Model
{
    protected $table = 'audit_logs';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'user_id', 'action_type', 'table_name', 'record_id',
        'old_values', 'new_values', 'ip_address', 'user_agent'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = false;

    public function getUserActivity(int $userId, int $limit = 50)
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }
}