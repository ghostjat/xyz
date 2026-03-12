<?php namespace App\Controllers;

use App\Models\AppointmentModel;
use App\Models\CounselorModel;

class AppointmentController extends BaseController 
{
    public function index() 
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('login');
        }

        // Fetch available counselors from your existing model
        $counselorModel = new CounselorModel();
        $data['counselors'] = $counselorModel->getCounselors();
        $data['user'] = session()->get();

        return $this->spa_view('dashboard/appointment/book', $data, false);
    }

    public function store() 
    {
        // 1. Strict AJAX Check
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setBody('Invalid Request Method');
        }

        $userId = session()->get('user_id');
        $schoolId = session()->get('school_id');

        // 2. DDoS / Rate Limiting (Prevents booking spam)
        $throttler = \Config\Services::throttler();
        $throttleKey = md5('appointment_booking_' . $this->request->getIPAddress());
        
        // Limit to 3 booking attempts per 5 minutes per IP
        if ($throttler->check($throttleKey, 3, 300) === false) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Too many booking requests. Please wait a few minutes.']);
        }

        // 3. Strict Input Validation (Prevents XSS & Malicious Payloads)
        $rules = [
            'preferred_datetime' => 'required|valid_date[Y-m-d\TH:i]',
            'topic'              => 'required|max_length[150]|alpha_numeric_space'
        ];

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            return $this->response->setJSON(['status' => 'error', 'msg' => implode(' | ', $errors)]);
        }

        // 4. Sanitize Inputs & Check Date Logic
        $datetime = esc($this->request->getPost('preferred_datetime'));
        $topic = esc($this->request->getPost('topic'));

        if (strtotime($datetime) < time()) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'You cannot book an appointment in the past.']);
        }

        $appModel = new AppointmentModel();

        // 5. Insert into DB based on your schema
        $data = [
            'user_id'            => $userId,
            'school_id'          => $schoolId ?? null,
            'preferred_datetime' => $datetime,
            'topic'              => $topic,
            'status'             => 'pending',
            'counselor_notes'    => '' 
        ];

        if ($appModel->insert($data)) {
            return $this->response->setJSON([
                'status' => 'success',
                'msg'    => 'Session requested successfully! Our team will confirm your time slot shortly.',
                'redirect' => base_url('dashboard')
            ]);
        }

        return $this->response->setJSON(['status' => 'error', 'msg' => 'Failed to book appointment. Please contact support.']);
    }
}