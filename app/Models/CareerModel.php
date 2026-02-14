<?php
namespace App\Models;

use CodeIgniter\Model;

/**
 * Career Model
 */
class CareerModel extends Model
{
    protected $table = 'careers';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'career_code', 'career_title', 'career_category', 'short_description',
        'detailed_description', 'day_in_life', 'educational_requirements',
        'skill_requirements', 'certifications', 'experience_required',
        'riasec_profile', 'mbti_fit', 'gardner_requirements',
        'eq_requirements', 'aptitude_requirements', 'salary_range',
        'job_outlook', 'growth_rate', 'work_environment', 'typical_hours',
        'physical_demands', 'demand_by_country', 'licensing_requirements',
        'entry_level_positions', 'mid_level_positions', 'senior_level_positions',
        'related_careers', 'alternative_careers', 'career_image',
        'video_url', 'is_active', 'popularity_score'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'last_updated';

    public function searchCareers(string $query)
    {
        return $this->like('career_title', $query)
                    ->orLike('short_description', $query)
                    ->where('is_active', true)
                    ->findAll();
    }

    public function getCareersByCategory(string $category)
    {
        return $this->where('career_category', $category)
                    ->where('is_active', true)
                    ->findAll();
    }
}

