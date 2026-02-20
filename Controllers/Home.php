<?php namespace App\Controllers;

use App\Models\CounselorModel;

class Home extends BaseController
{
    public function index()
    {
        return $this->spa_view('home_view');
    }

    // Pure API Endpoint (Always JSON)
    public function search()
    {
        $city = $this->request->getVar('city');
        $spec = $this->request->getVar('specialization');
        $model = new CounselorModel();
        return $this->response->setJSON(['counselors' => $model->getCounselors($city, $spec)]);
    }
    
    public function audit() {
        // Load the specialized dark view for the audit page
        return $this->spa_view('audit_view');
    }
}