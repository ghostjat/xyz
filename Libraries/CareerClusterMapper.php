<?php namespace App\Libraries;
use App\Models\CareerClusterModel;
class CareerClusterMapper {
    /**
     * Maps Global Career Clusters to their ideal Psychometric Baselines.
     * Evaluated against the student's actual RIASEC, MBTI, and Aptitude Scores.
     */
    public static function getClusters() {
        return [
            'Science, Maths and Engineering' => [
                'riasec' => ['Investigative', 'Realistic'],
                'mbti' => ['INTJ', 'INTP', 'ENTJ', 'ENTP', 'ISTJ'],
                'aptitude_weights' => ['numerical' => 0.5, 'logical' => 0.5]
            ],
            'Manufacturing' => [
                'riasec' => ['Realistic', 'Conventional'],
                'mbti' => ['ISTJ', 'ESTJ', 'ISTP', 'ESTP'],
                'aptitude_weights' => ['mechanical' => 0.6, 'spatial' => 0.4]
            ],
            'Agriculture' => [
                'riasec' => ['Realistic', 'Investigative'],
                'mbti' => ['ISTP', 'ISFP', 'ESTJ', 'ISTJ'],
                'aptitude_weights' => ['logical' => 0.5, 'mechanical' => 0.5]
            ],
            'Logistics and Transportation' => [
                'riasec' => ['Realistic', 'Enterprising'],
                'mbti' => ['ESTJ', 'ISTJ', 'ESTP', 'ENTJ'],
                'aptitude_weights' => ['spatial' => 0.5, 'administrative' => 0.5]
            ],
            'Information Technology' => [
                'riasec' => ['Investigative', 'Conventional'],
                'mbti' => ['INTP', 'INTJ', 'ISTJ', 'ENTP'],
                'aptitude_weights' => ['logical' => 0.6, 'numerical' => 0.4]
            ],
            'Architecture and Construction' => [
                'riasec' => ['Realistic', 'Artistic'],
                'mbti' => ['ISTP', 'INTJ', 'ESTP', 'ISFP'],
                'aptitude_weights' => ['spatial' => 0.7, 'mechanical' => 0.3]
            ],
            'Accounts and Finance' => [
                'riasec' => ['Conventional', 'Enterprising'],
                'mbti' => ['ESTJ', 'ISTJ', 'ENTJ', 'INTJ'],
                'aptitude_weights' => ['numerical' => 0.7, 'administrative' => 0.3]
            ],
            'Health Science' => [
                'riasec' => ['Investigative', 'Social'],
                'mbti' => ['ISFJ', 'ESFJ', 'INFJ', 'ENFJ'],
                'aptitude_weights' => ['logical' => 0.5, 'verbal' => 0.5]
            ],
            'Education and Training' => [
                'riasec' => ['Social', 'Artistic'],
                'mbti' => ['ENFJ', 'INFJ', 'ENFP', 'ESFJ'],
                'aptitude_weights' => ['verbal' => 0.7, 'social' => 0.3]
            ],
            'Human Service' => [
                'riasec' => ['Social', 'Investigative'],
                'mbti' => ['ENFJ', 'INFJ', 'ESFJ', 'ISFJ'],
                'aptitude_weights' => ['social' => 0.7, 'verbal' => 0.3]
            ]
        ];
    }
    
    public static function _getClusters() {
        $model = new CareerClusterModel();
        $clusters = $model->getActiveClustersFormatted();
        
        // Fallback in case the database is completely empty
        if(empty($clusters)) {
            return [
                'Default Sector' => [
                    'riasec' => ['Realistic'], 'mbti' => ['ISTJ'], 'aptitude_weights' => ['logical' => 1.0]
                ]
            ];
        }
        
        return $clusters;
    }
}