<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Question Model
 */
class QuestionModel extends Model
{
    protected $table = 'questions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'category_id', 'question_text', 'question_type', 'options', 'dimension',
        'sub_dimension', 'scoring_key', 'reverse_scored', 'weight',
        'difficulty_level', 'age_group', 'is_active', 'display_order'
    ];
    protected $useTimestamps = true;

    public function getQuestionsByCategory(string $categoryCode, string $ageGroup)
    {
        $categoryModel = new TestCategoryModel();
        $category = $categoryModel->getCategoryByCode($categoryCode);
        
        if (!$category) {
            return [];
        }

        return $this->where('category_id', $category['id'])
                    ->where('is_active', true)
                    ->groupStart()
                        ->where('age_group', $ageGroup)
                        ->orWhere('age_group', 'both')
                    ->groupEnd()
                    ->orderBy('display_order', 'ASC')
                    ->findAll();
    }

    public function getQuestionsByAttempt(int $attemptId)
    {
        $attemptModel = new TestAttemptModel();
        $attempt = $attemptModel->find($attemptId);
        
        if (!$attempt) {
            return [];
        }

        return $this->where('category_id', $attempt['category_id'])
                    ->where('is_active', true)
                    ->findAll();
    }
}