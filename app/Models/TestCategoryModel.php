<?php
namespace App\Models;

use CodeIgniter\Model;
/**
 * Test Category Model
 */
class TestCategoryModel extends Model
{
    protected $table = 'test_categories';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'category_code', 'category_name', 'description', 'min_age', 'max_age',
        'duration_minutes', 'total_questions', 'is_active', 'display_order'
    ];
    protected $useTimestamps = true;

    public function getActiveCategories()
    {
        return $this->where('is_active', true)
                    ->orderBy('display_order', 'ASC')
                    ->findAll();
    }

    public function getCategoryByCode(string $code)
    {
        return $this->where('category_code', $code)->first();
    }
}