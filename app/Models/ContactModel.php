<?php
namespace App\Models;

use CodeIgniter\Model;

class ContactModel extends Model
{
    protected $table            = 'contact_messages';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    // Added 'role' to allowed fields
    protected $allowedFields    = ['name', 'email', 'role', 'subject', 'message', 'ip_address', 'status'];
    
    protected $useTimestamps = false; 
}