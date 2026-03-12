<?php namespace App\Models;

use CodeIgniter\Model;

class AppointmentModel extends Model {
    protected $table = 'appointments';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'school_id', 'preferred_datetime', 'topic', 'status', 'counselor_notes'];
    protected $useTimestamps = false; // We use database default CURRENT_TIMESTAMP
}