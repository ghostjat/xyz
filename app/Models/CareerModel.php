<?php namespace App\Models;

use CodeIgniter\Model;

class CareerModel extends Model
{
    protected $table = 'career_paths'; // Fixed: Pointing to correct table
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    
    // Updated to match the generated SQL schema
    protected $allowedFields = [
        'cluster_id', 'title', 'intro', 'roles','educational_requirements', 'path', 'skills', 'opportunities', 
        'salary', 'fess', 'demand', 's_level', 'prep_level', 'colleges', 
        'riasec', 'mbti', 'aptitude_weights', 'gardner_requirements', 'eq_requirements', 'is_active','ai_resilience'
    ];

    /**
     * Fetches all active careers, joins cluster names, and passes JSON fields safely.
     */
    public function getActiveCareersForEngine()
    {
        $builder = $this->db->table($this->table);
        $builder->select('career_paths.*, career_clusters.cluster_name as cluster');
        // Joins the cluster table to ensure $job['cluster'] exists for the engine's fallback logic
        $builder->join('career_clusters', 'career_clusters.id = career_paths.cluster_id', 'left');
        $builder->where('career_paths.is_active', 1);
        
        $careers = $builder->get()->getResultArray();
        $formatted = [];

        foreach ($careers as $job) {
            $formatted[] = [
                'id'                   => $job['id'],
                'title'                => $job['title'],
                'cluster'              => $job['cluster'] ?? 'General',
                'roles'                => $job['roles'],
                'educational_requirements' => $job['educational_requirements'] ?? null,
                'colleges'                 => $job['colleges'] ?? null,
                'ai_resilience' => $job['ai_resilience'] ?? null,
                // Passed as raw JSON strings/arrays to be parsed by the Engine
                'riasec'               => $job['riasec'] ?? '[]',
                'mbti'                 => $job['mbti'] ?? '[]',
                'aptitude_weights'     => $job['aptitude_weights'] ?? '{}',
                'gardner_requirements' => $job['gardner_requirements'] ?? '{}',
                'eq_requirements'      => $job['eq_requirements'] ?? '{}'
            ];
        }

        return $formatted;
    }
}