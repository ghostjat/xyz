<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Career Roadmap Model
 */
class CareerRoadmapModel extends Model
{
    protected $table = 'career_roadmaps';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'career_id', 'age_group', 'region', 'immediate_steps',
        'short_term_goals', 'medium_term_goals', 'long_term_goals',
        'subject_focus', 'exam_preparation', 'extracurricular_activities',
        'internship_opportunities', 'networking_tips', 'recommended_courses',
        'online_resources', 'books_and_materials'
    ];
    protected $useTimestamps = true;

    public function getRoadmap(int $careerId, string $ageGroup, string $region = 'Global')
    {
        return $this->where('career_id', $careerId)
                    ->where('age_group', $ageGroup)
                    ->groupStart()
                        ->where('region', $region)
                        ->orWhere('region', 'Global')
                    ->groupEnd()
                    ->first();
    }
}