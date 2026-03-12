<?php namespace App\Models;

use CodeIgniter\Model;

class SmtpSettingsModel extends Model
{
    protected $table      = 'smtp_settings';
    protected $primaryKey = 'id';

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'smtp_host',
        'smtp_user',
        'smtp_password_encrypted',
        'smtp_port'
    ];

    // Automatically manage the updated_at timestamp whenever you change settings
    protected $useTimestamps = true; 
    protected $createdField  = '';
    protected $updatedField  = 'updated_at';
}