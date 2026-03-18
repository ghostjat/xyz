<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        return $this->renderPage('pages/home', ['title' => 'Home']);
    }

    public function about()
    {
        return $this->renderPage('pages/about', ['title' => 'About Us']);
    }
    
    public function contact() {
        return $this->renderPage('pages/contact', ['title' => 'Contact Us']);
    }

    // ==========================================
    // SPECIFIC HANDLERS (Easier to Debug)
    // ==========================================

    public function viewNep($page)
    {
        // Force the folder to be 'nep'
        return $this->loadView('nep', $page);
    }
    
    public function viewPolicy($page)
    {
        // Force the folder to be 'nep'
        return $this->loadView('policy', $page);
    }

    public function viewSchool($page)
    {
        // Force the folder to be 'school'
        return $this->loadView('school', $page);
    }

    public function viewService($page)
    {
        // Force the folder to be 'schools' (plural)
        return $this->loadView('service', $page);
    }
    
    public function viewSoltions($page)
    {
        // Force the folder to be 'schools' (plural)
        return $this->loadView('solutions', $page);
    }
    
    public function submitContact()
    {
        // Ensure request is AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Invalid Request Type']);
        }

        // 1. DDoS / Bot Mitigation: Rate Limiting (FIXED FOR IPv6 COLONS)
        $throttler = \Config\Services::throttler();
        $safeIpHash = md5($this->request->getIPAddress());
        
        if ($throttler->check('contact_form_' . $safeIpHash, 3, MINUTE) === false) {
            return $this->response->setJSON([
                'status'  => 'error', 
                'message' => 'Too many requests detected. Please try again later.'
            ]);
        }

        // 2. Strict Validation Rules
        $rules = [
            'name'    => 'required|min_length[2]|max_length[100]',
            'email'   => 'required|valid_email|max_length[150]',
            'role'    => 'required|in_list[student,parent,school_admin,counselor,other]',
            'subject' => 'required|min_length[4]|max_length[200]',
            'message' => 'required|min_length[10]|max_length[3000]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'validation_error', 
                'errors' => $this->validator->getErrors(),
                'csrf'   => csrf_hash() // Send back new CSRF token if regeneration is enabled in config
            ]);
        }

        // 3. Data Sanitization & Database Storage
        $contactModel = new \App\Models\ContactModel();
        
        $data = [
            'name'       => strip_tags($this->request->getPost('name')),
            'email'      => $this->request->getPost('email'),
            'role'       => $this->request->getPost('role'),
            'subject'    => strip_tags($this->request->getPost('subject')),
            'message'    => strip_tags($this->request->getPost('message')),
            'ip_address' => $this->request->getIPAddress()
        ];

        try {
            $contactModel->insert($data);
            
            // 4. Trigger Professional Emails (Using the private method from previous step)
            $this->dispatchContactEmails($data);

            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Thank you! We have received your message and will contact you shortly.'
            ]);

        } catch (\Exception $e) {
            log_message('error', '[Contact Form Error] ' . $e->getMessage());
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'A server error occurred while processing your request. Please try again.'
            ]);
        }
    }

   /**
     * Dispatches the Admin Notification and User Auto-Responder using the Database SMTP Library
     */
    private function dispatchContactEmails(array $data)
    {
        // 1. Load your custom Email Library
        $emailLib = new \App\Libraries\EmailLibrary();

        // Format role for display (e.g., 'school_admin' -> 'School Admin')
        $displayRole = ucwords(str_replace('_', ' ', $data['role']));

        // --- Email 1: Internal Alert to Support Team ---
        $adminMessage = "
            <h3>New Contact Inquiry</h3>
            <p><strong>Name:</strong> " . esc($data['name']) . "</p>
            <p><strong>Email:</strong> " . esc($data['email']) . "</p>
            <p><strong>Role:</strong> " . esc($displayRole) . "</p>
            <p><strong>Subject:</strong> " . esc($data['subject']) . "</p>
            <hr>
            <p><strong>Message:</strong><br>" . nl2br(esc($data['message'])) . "</p>
            <br><small>IP Address: {$data['ip_address']}</small>
        ";

        $adminSubject = 'New Support Inquiry: ' . esc($data['subject']);
        
        // Send to admin using the library
        $emailLib->send_system_email('support@pharoseducation.in', $adminSubject, $adminMessage);

        // --- Email 2: Professional Auto-Responder to the User ---
        $userMessage = "
            <div style='font-family: Arial, sans-serif; color: #333;'>
                <h2>Thank you for contacting Pharos Education</h2>
                <p>Dear " . esc($data['name']) . ",</p>
                <p>We have successfully received your inquiry. Our support team is routing your message to the appropriate department for " . esc($displayRole) . "s.</p>
                <p>We aim to provide a tailored response within 24-48 business hours.</p>
                <br>
                <p>Warm regards,</p>
                <p><strong>The Pharos Education Team</strong><br>
                <a href='https://pharoseducation.in'>pharoseducation.in</a></p>
            </div>
        ";

        $userSubject = 'We have received your message';
        
        // Send to user using the library
        $emailLib->send_system_email($data['email'], $userSubject, $userMessage);
    }

    // ==========================================
    // THE LOADER LOGIC
    // ==========================================
    private function loadView($folder, $page)
    {
        // 1. Sanitize: Remove .php if the user typed it in the URL
        $page = str_replace('.php', '', $page);

        // 2. Construct path: pages/nep/nep2020
        $viewName = "pages/{$folder}/{$page}";
        

        // 4. Render if found
        $data = ['title' => ucfirst($folder) . ' | ' . ucfirst($page)];
        return $this->renderPage($viewName, $data);
    }
}