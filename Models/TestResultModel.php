<?php namespace App\Models;

use CodeIgniter\Model;

class TestResultModel extends Model
{
    protected $table      = 'test_results';
    protected $primaryKey = 'id';

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'user_id', 
        'module_code', 
        'result_json', 
        'primary_trait',
        'completed_at'
    ];

    protected $useTimestamps = true; 
    protected $createdField  = 'completed_at'; // Maps to your schema
    protected $updatedField  = ''; 
}