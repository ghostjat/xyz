<?php namespace App\Models;

use CodeIgniter\Model;

class CounselorModel extends Model
{
    protected $table = 'users';
    
    public function getCounselors($city = null, $specialization = null)
    {
        $builder = $this->db->table('users');
        $builder->select('users.id, users.name, counselor_profiles.city, counselor_profiles.specialization, counselor_profiles.experience_years');
        $builder->join('counselor_profiles', 'counselor_profiles.user_id = users.id');
        $builder->where('users.role', 'counselor');

        if ($city && $city != '') {
            $builder->like('counselor_profiles.city', $city);
        }
        if ($specialization && $specialization != '') {
            $builder->like('counselor_profiles.specialization', $specialization);
        }

        return $builder->get()->getResultArray();
    }
}