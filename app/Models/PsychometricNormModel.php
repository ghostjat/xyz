<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Psychometric Norm Model
 */
class PsychometricNormModel extends Model
{
    protected $table = 'psychometric_norms';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'category_code', 'age_group', 'region', 'dimension',
        'mean_score', 'std_deviation', 'percentile_25',
        'percentile_50', 'percentile_75', 'percentile_90', 'sample_size'
    ];
    protected $useTimestamps = true;
    protected $updatedField = 'last_updated';
    protected $createdField = false;

    public function getNorm(string $categoryCode, string $ageGroup, string $dimension, string $region = 'Global')
    {
        return $this->where('category_code', $categoryCode)
                    ->where('age_group', $ageGroup)
                    ->where('dimension', $dimension)
                    ->groupStart()
                        ->where('region', $region)
                        ->orWhere('region', 'Global')
                    ->groupEnd()
                    ->first();
    }
}