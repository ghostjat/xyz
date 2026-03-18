<?php namespace App\Models;

use CodeIgniter\Model;

class QuestionModel extends Model
{
    protected $table      = 'questions';
    protected $primaryKey = 'id';

    protected $returnType     = 'array'; // Returns results as arrays (matches Controller logic)
    protected $useSoftDeletes = false;

    // columns that can be inserted/updated via code
    protected $allowedFields = [
        'module_code', 
        'question_text', 
        'category', 
        'weight', 
        'display_order'
    ];

    protected $useTimestamps = false; // Questions table doesn't have created_at/updated_at in schema
}