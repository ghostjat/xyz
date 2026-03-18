<?php namespace App\Models;

use CodeIgniter\Model;

class CareerPathModel extends Model
{
    protected $table = 'career_paths';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['cluster_id', 'title', 'roles', 'is_active'];

    public function getActivePathsWithClusters()
    {
        $builder = $this->db->table($this->table);
        $builder->select('career_paths.title, career_paths.roles, career_clusters.cluster_name as cluster');
        $builder->join('career_clusters', 'career_clusters.id = career_paths.cluster_id');
        $builder->where('career_paths.is_active', 1);
        $builder->where('career_clusters.is_active', 1);
        
        return $builder->get()->getResultArray();
    }
}