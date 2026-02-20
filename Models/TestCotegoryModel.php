<?php namespace App\Models;

use CodeIgniter\Model;

class TestCategoryModel extends Model
{
    protected $table      = 'test_category';
    protected $primaryKey = 'id';

    protected $returnType     = 'array'; // Returns results as arrays (matches Controller logic)
    protected $useSoftDeletes = false;

    // columns that can be inserted/updated via code
    protected $allowedFields = [
        'moduel_id', 
        'category_name', 
        'description'
    ];
}