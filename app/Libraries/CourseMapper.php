<?php namespace App\Libraries;

use App\Models\EduCourseModel;

class CourseMapper {

    /**
     * Dynamically fetches Course Recommendations from the live database.
     */
    public static function getRecommendations($clusterName) {
        
        $courseModel = new EduCourseModel();
        
        // Fetch real-time data from the MySQL database
        return $courseModel->getCoursesByClusterName($clusterName);
        
    }
}