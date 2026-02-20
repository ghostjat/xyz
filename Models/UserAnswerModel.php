<?php namespace App\Models;

use CodeIgniter\Model;

class UserAnswerModel extends Model
{
    protected $table      = 'user_answers';
    protected $primaryKey = 'id';

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'user_id', 
        'question_id', 
        'answer_value'
    ];

    protected $useTimestamps = true; // Auto-fill created_at
    protected $createdField  = 'created_at';
    protected $updatedField  = ''; // No updated_at in schema
}