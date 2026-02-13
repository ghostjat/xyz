<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * User Model
 */
class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'username', 'email', 'password_hash', 'full_name', 'date_of_birth',
        'gender', 'phone', 'country', 'state', 'city', 'educational_level',
        'school_name', 'profile_image', 'is_active', 'email_verified', 'last_login'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $validationRules = [
        'username' => 'required|min_length[3]|max_length[100]|is_unique[users.username,id,{id}]',
        'email' => 'required|valid_email|is_unique[users.email,id,{id}]',
        'full_name' => 'required|min_length[2]',
        'educational_level' => 'required'
    ];
}