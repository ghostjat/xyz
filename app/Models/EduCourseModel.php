<?php namespace App\Models;

use CodeIgniter\Model;

class EduCourseModel extends Model
{
    protected $table = 'edu_courses';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    
    // We only need read access for the engine
    protected $allowedFields = ['cluster_id', 'level_id', 'course_title', 'focus_areas', 'is_active'];

    /**
     * Fetches formatted course recommendations for a specific cluster name.
     * Uses query builder JOINs for high performance.
     */
    public function getCoursesByClusterName(string $clusterName)
    {
        $builder = $this->db->table($this->table);
        $builder->select('edu_courses.course_title, edu_courses.focus_areas, edu_levels.level_name');
        $builder->join('edu_clusters', 'edu_clusters.id = edu_courses.cluster_id');
        $builder->join('edu_levels', 'edu_levels.id = edu_courses.level_id');
        $builder->where('edu_clusters.cluster_name', $clusterName);
        $builder->where('edu_courses.is_active', 1);
        
        $results = $builder->get()->getResultArray();

        // Format the database rows into the exact array structure the PDF view expects
        $formatted = [
            'Bachelors' => [],
            'Vocational' => [],
            'Focus_Areas' => ''
        ];

        foreach ($results as $row) {
            $level = $row['level_name'];
            if (!isset($formatted[$level])) {
                $formatted[$level] = [];
            }
            $formatted[$level][] = $row['course_title'];
            
            // Grab focus areas from the first matching row to summarize the track
            if (empty($formatted['Focus_Areas']) && !empty($row['focus_areas'])) {
                $formatted['Focus_Areas'] = $row['focus_areas'];
            }
        }

        // Fallback if the database has no courses for a newly calculated cluster yet
        if (empty($formatted['Bachelors']) && empty($formatted['Vocational'])) {
            return [
                'Bachelors' => ['Relevant Bachelor Degree'],
                'Vocational' => ['Relevant Diploma / Certification'],
                'Focus_Areas' => 'General Industry Studies'
            ];
        }

        return $formatted;
    }
}