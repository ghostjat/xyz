<?php namespace App\Models;

use CodeIgniter\Model;

class CareerClusterModel extends Model
{
    protected $table = 'career_clusters';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['cluster_name', 'riasec', 'mbti', 'aptitude_weights', 'is_active'];

    public function getActiveClustersFormatted()
    {
        $clusters = $this->where('is_active', 1)->findAll();
        $formatted = [];

        foreach ($clusters as $row) {
            $formatted[$row['cluster_name']] = [
                'riasec' => $this->safeParseArray($row['riasec']),
                'mbti' => $this->safeParseArray($row['mbti']),
                'aptitude_weights' => $this->safeParseAptitude($row['aptitude_weights'])
            ];
        }
        return $formatted;
    }

    // This catches database typos and forces them into arrays so the engine doesn't crash
    private function safeParseArray($data) {
        if (empty($data)) return [];
        $decoded = json_decode($data, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) return $decoded;
        
        // If someone typed "Realistic, Investigative" instead of strict JSON, fix it here:
        $clean = str_replace(['"', "'", '[', ']', '{', '}'], '', $data);
        return array_filter(array_map('trim', explode(',', $clean)));
    }

    private function safeParseAptitude($data) {
        if (empty($data)) return [];
        $decoded = json_decode($data, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) return $decoded;
        
        // Try wrapping it in brackets just in case they were forgotten
        $decoded = json_decode('{' . $data . '}', true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) return $decoded;

        return [];
    }
}