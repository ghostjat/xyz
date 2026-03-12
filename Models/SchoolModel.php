<?php namespace App\Models;

use CodeIgniter\Model;

class SchoolModel extends Model {
    protected $table = 'schools';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['name', 'contact_person', 'contact_email'];
}