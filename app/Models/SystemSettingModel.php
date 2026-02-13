<?php

namespace App\Models;

use CodeIgniter\Model;
/**
 * System Settings Model
 */
class SystemSettingModel extends Model
{
    protected $table = 'system_settings';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'setting_key', 'setting_value', 'setting_type', 'description'
    ];
    protected $useTimestamps = true;
    protected $updatedField = 'updated_at';
    protected $createdField = false;

    public function getSetting(string $key, $default = null)
    {
        $setting = $this->where('setting_key', $key)->first();
        
        if (!$setting) {
            return $default;
        }
        
        return $this->castValue($setting['setting_value'], $setting['setting_type']);
    }

    public function setSetting(string $key, $value, string $type = 'string')
    {
        $existing = $this->where('setting_key', $key)->first();
        
        $data = [
            'setting_key' => $key,
            'setting_value' => is_array($value) ? json_encode($value) : $value,
            'setting_type' => $type
        ];
        
        if ($existing) {
            return $this->update($existing['id'], $data);
        }
        
        return $this->insert($data);
    }

    private function castValue($value, $type)
    {
        switch ($type) {
            case 'integer':
                return (int) $value;
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }
}