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
                'riasec' => $this->decodeRiasec($row['riasec']),
                'mbti' => $this->safeParseArray($row['mbti']),
                'aptitude_weights' => $this->safeParseAptitude($row['aptitude_weights'])
            ];
        }
        return $formatted;
    }

    // NEW: intercepts the ["ISR"] string and translates it to full traits
    private function decodeRiasec($data) {
        $parsed = $this->safeParseArray($data);
        $riasecDictionary = ['r' => 'realistic', 'i' => 'investigative', 'a' => 'artistic', 's' => 'social', 'e' => 'enterprising', 'c' => 'conventional'];
        
        $fullTraits = [];
        if (!empty($parsed)) {
            $code = strtolower(trim($parsed[0]));
            if (strlen($code) <= 3) {
                foreach (str_split($code) as $char) {
                    if (isset($riasecDictionary[$char])) {
                        $fullTraits[] = $riasecDictionary[$char];
                    }
                }
            } else {
                $fullTraits = array_values(array_map('strtolower', $parsed));
            }
        }
        return $fullTraits;
    }

    private function safeParseArray($data) {
        if (empty($data)) return [];
        $decoded = json_decode($data, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) return $decoded;
        
        $clean = str_replace(['"', "'", '[', ']', '{', '}'], '', $data);
        return array_filter(array_map('trim', explode(',', $clean)));
    }

    private function safeParseAptitude($data) {
        if (empty($data)) return [];
        $decoded = json_decode($data, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) return $decoded;
        
        $decoded = json_decode('{' . $data . '}', true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) return $decoded;

        return [];
    }
}