<?php namespace App\Models;

use CodeIgniter\Model;

class CareerModel extends Model
{
    protected $table = 'careers';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    
    // Allow interactions with all schema fields
    protected $allowedFields = [
        'career_code', 'career_title', 'career_category', 'short_description', 
        'riasec_profile', 'mbti_fit', 'aptitude_requirements', 'is_active', 'popularity_score'
    ];

    /**
     * Fetches all active careers and decodes the JSON psychometric requirements.
     */
    public function getActiveCareersForEngine()
    {
        $careers = $this->where('is_active', 1)->findAll();
        $formatted = [];

        foreach ($careers as $job) {
            $formatted[] = [
                'id'            => $job['id'],
                'title'         => $job['career_title'],
                'cluster'       => $job['career_category'],
                'roles'         => $job['short_description'],
                // Safely decode the JSON fields into usable PHP Arrays
                'riasec'        => json_decode($job['riasec_profile'] ?? '[]', true) ?? [],
                'mbti'          => json_decode($job['mbti_fit'] ?? '[]', true) ?? [],
                'aptitudes'     => json_decode($job['aptitude_requirements'] ?? '{}', true) ?? []
            ];
        }

        return $formatted;
    }
}