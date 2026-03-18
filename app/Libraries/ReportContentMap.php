<?php namespace App\Libraries;

class ReportContentMap {
    public static function getMap() {
        return [
            'profiling_stage' => [
                'Confused' => [
                    'desc' => 'You are at the confused stage in career planning. We understand that you are having little idea of career planning, but usually confused among various career options...',
                    'risk' => 'Wrong selection of a career path, career dissatisfaction, and self-interest mismatch.',
                    'action' => 'Explore your strengths and weakness > Explore career options > Gather information > Match best suitable option > Early execution.'
                ]
            ]
        ];
    }
}