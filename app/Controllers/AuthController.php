<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\UserSessionModel;

class AuthController extends BaseController
{
    protected $userModel;
    protected $sessionModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->sessionModel = new UserSessionModel();
    }

    /**
     * Display login page
     */
    public function login()
    {
        if ($this->isLoggedIn()) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    /**
     * Process login
     */
    public function processLogin()
    {
        $validation = \Config\Services::validation();
        
        $validation->setRules([
            'email' => 'required|valid_email',
            'password' => 'required|min_length[6]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->error('Validation failed', $validation->getErrors(), 422);
        }

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $remember = $this->request->getPost('remember_me');

        // Find user
        $user = $this->userModel->where('email', $email)->first();

        if (!$user) {
            return $this->error('Invalid email or password', null, 401);
        }

        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            return $this->error('Invalid email or password', null, 401);
        }

        // Check if account is active
        if (!$user['is_active']) {
            return $this->error('Your account is inactive. Please contact support.', null, 403);
        }

        // Create session
        $sessionToken = bin2hex(random_bytes(32));
        $expiresAt = $remember ? date('Y-m-d H:i:s', strtotime('+30 days')) : date('Y-m-d H:i:s', strtotime('+24 hours'));

        $this->sessionModel->insert([
            'user_id' => $user['id'],
            'session_token' => $sessionToken,
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString(),
            'expires_at' => $expiresAt
        ]);

        // Set session data
        $this->session->set([
            'user_id' => $user['id'],
            'session_token' => $sessionToken,
            'logged_in' => true
        ]);

        // Update last login
        $this->userModel->update($user['id'], [
            'last_login' => date('Y-m-d H:i:s')
        ]);

        // Log activity
        $this->logActivity('login', 'users', $user['id']);

        return $this->success([
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'full_name' => $user['full_name'],
                'educational_level' => $user['educational_level']
            ]
        ], 'Login successful');
    }

    /**
     * Display registration page
     */
    public function register()
    {
        if ($this->isLoggedIn()) {
            return redirect()->to('/dashboard');
        }

        return view('auth/register');
    }

    /**
     * Process registration
     */
    public function processRegister()
    {
        $validation = \Config\Services::validation();
        
        $validation->setRules([
            'username' => 'required|min_length[3]|max_length[100]|is_unique[users.username]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
            'full_name' => 'required|min_length[2]|max_length[255]',
            'date_of_birth' => 'required|valid_date',
            'educational_level' => 'required|in_list[class_8,class_9,class_10,class_11,class_12]',
            'gender' => 'required|in_list[male,female,other,prefer_not_to_say]',
            'phone' => 'permit_empty|min_length[10]|max_length[20]',
            'country' => 'required',
            'terms_accepted' => 'required|in_list[1]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->error('Validation failed', $validation->getErrors(), 422);
        }

        // Calculate age from date of birth
        $dob = new \DateTime($this->request->getPost('date_of_birth'));
        $now = new \DateTime();
        $age = $now->diff($dob)->y;

        // Validate age
        if ($age < 13 || $age > 25) {
            return $this->error('You must be between 13 and 25 years old to register.', null, 422);
        }

        // Create user
        $userData = [
            'username' => $this->request->getPost('username'),
            'email' => strtolower($this->request->getPost('email')),
            'password_hash' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'full_name' => $this->request->getPost('full_name'),
            'date_of_birth' => $this->request->getPost('date_of_birth'),
            'gender' => $this->request->getPost('gender'),
            'phone' => $this->request->getPost('phone'),
            'country' => $this->request->getPost('country'),
            'state' => $this->request->getPost('state'),
            'city' => $this->request->getPost('city'),
            'educational_level' => $this->request->getPost('educational_level'),
            'school_name' => $this->request->getPost('school_name'),
            'is_active' => true,
            'email_verified' => false
        ];

        $userId = $this->userModel->insert($userData);

        if (!$userId) {
            return $this->error('Failed to create account. Please try again.', null, 500);
        }

        // Send verification email (implement this based on your email service)
        // $this->sendVerificationEmail($userData['email']);

        // Log activity
        $this->logActivity('register', 'users', $userId, $userData);

        return $this->success([
            'user_id' => $userId
        ], 'Registration successful! Please check your email to verify your account.');
    }

    /**
     * Logout
     */
    public function logout()
    {
        $sessionToken = $this->session->get('session_token');
        
        if ($sessionToken) {
            // Delete session from database
            $this->sessionModel->where('session_token', $sessionToken)->delete();
        }

        // Log activity before destroying session
        if ($this->currentUser) {
            $this->logActivity('logout', 'users', $this->currentUser['id']);
        }

        // Destroy session
        $this->session->destroy();

        return redirect()->to('/login')->with('success', 'You have been logged out successfully');
    }

    /**
     * Check authentication status (AJAX)
     */
    public function checkAuth()
    {
        if ($this->isLoggedIn()) {
            return $this->success([
                'authenticated' => true,
                'user' => [
                    'id' => $this->currentUser['id'],
                    'username' => $this->currentUser['username'],
                    'email' => $this->currentUser['email'],
                    'full_name' => $this->currentUser['full_name']
                ]
            ]);
        }

        return $this->error('Not authenticated', null, 401);
    }

    /**
     * Send verification email
     */
    private function sendVerificationEmail(string $email)
    {
        // Implement email verification logic
        // Generate verification token
        // Send email with verification link
    }

    /**
     * Verify email
     */
    public function verifyEmail($token)
    {
        // Implement email verification logic
    }

    /**
     * Forgot password
     */
    public function forgotPassword()
    {
        return view('auth/forgot_password');
    }

    /**
     * Process forgot password
     */
    public function processForgotPassword()
    {
        $validation = \Config\Services::validation();
        
        $validation->setRules([
            'email' => 'required|valid_email'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->error('Please provide a valid email address', $validation->getErrors(), 422);
        }

        $email = $this->request->getPost('email');
        $user = $this->userModel->where('email', $email)->first();

        // Always return success to prevent email enumeration
        if ($user) {
            // Generate reset token and send email
            // Implement this based on your email service
        }

        return $this->success(null, 'If an account exists with this email, you will receive a password reset link.');
    }

    /**
     * Reset password
     */
    public function resetPassword($token)
    {
        // Implement password reset logic
        return view('auth/reset_password', ['token' => $token]);
    }
}
