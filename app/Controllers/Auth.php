<?php namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;

class Auth extends BaseController
{
    use ResponseTrait;
    protected $userModel;
    
    public function __construct()
    {
        $this->userModel = new UserModel();
    }
    
    public function index() {
        return redirect()->to('login');
    } 

    public function login() {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('dashboard');
        }
        $this->response->noCache();
        return $this->spa_view('auth/index',useLayout:false);
    }

    public function register() {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('dashboard');
        }
        return $this->spa_view('auth/register');
    }

    public function authenticate() {
        // 1. Rate Limiting
        $throttler = \Config\Services::throttler();
        $ipAddress = $this->request->getIPAddress();
        $throttleKey = md5('login_attempts_' . $ipAddress);

        if ($throttler->check($throttleKey, 5, 60) === false) {
            return $this->fail('Too many login attempts. Please wait 1 minute.', 429);
        }

        // 2. Validation
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]'
        ];

        if (! $this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        $email = $this->request->getVar('email');
        $password = $this->request->getVar('password');
        
        $user = $this->userModel->where('email', $email)->first();

        // 3. Verify Credentials
        if ($user && password_verify($password, $user['password'])) {
            
            if ($user['is_active'] != 1) {
                return $this->fail('Your account is not activated yet. Contact to support@pharosEducation.in', 401);
            }
            
            // 4. Session Security
            session()->regenerate(true);

            session()->set([
                'id'         => $user['id'], 
                'user_id'   => $user['id'],
                'username'   => $user['username'],
                'fname'      => $user['full_name'],
                'email'      => $user['email'], 
                'category'   => $user['category'],
                'school_id' => $user['school_id'],
                'educational_level' => $user['educational_level'],
                'isLoggedIn' => TRUE
            ]);
            
            // FIX: Use Cache service to delete the key (Throttler->remove does not exist)
            \Config\Services::cache()->delete($throttleKey);

            //return $this->respond(['status' => 'success', 'redirect' => base_url('tests/dashboard')]);
            $redirectUrl = base_url('dashboard');
            if($this->request->isAJAX()){
                return $this->respond(['status' => 'success', 'redirect' => $redirectUrl]);
            }
            return redirect()->to($redirectUrl);
        } else {
            return $this->fail('Invalid Credentials', 401);
        }
    }

    public function store() {
        $throttler = \Config\Services::throttler();
        $ipAddress = $this->request->getIPAddress();
        $throttleKey = md5('register_attempts_' . $ipAddress);

        // Limit to 3 registrations per IP per 5 minutes (300 seconds)
        if ($throttler->check($throttleKey, 3, 300) === false) {
            return $this->fail('Too many registration attempts. Please try again later.', 429);
        }
        
        $rules = [
            'username'  => 'required|min_length[3]|max_length[50]|alpha_numeric_space',
            'email'     => 'required|valid_email|is_unique[users.email]',
            'phone'     => 'required|min_length[10]|max_length[15]|numeric',
            'password'  => 'required|min_length[8]',
            'full_name' => 'required|alpha_space',
            'educational_level' => 'required|alpha_dash',
        ];

        if (! $this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        $data = [
            'username'  => esc($this->request->getVar('username')),
            'email'     => esc($this->request->getVar('email')),
            'phone'     => esc($this->request->getVar('phone')),
            'full_name' => esc($this->request->getVar('full_name')),
            'educational_level' => esc($this->request->getVar('educational_level')),
            'password'  => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
            'category'  => 'student',
            'is_active' => 1 // Pending approval
        ];
        $email = $this->request->getVar('email');
        
        $result = $this->userModel->insert($data);
        if ($result) {
            $userId = $this->userModel->getInsertID();
            $token = md5($userId . $email . 'pharos_admin_secret');
            $reviewLink = base_url("auth/review_user/{$userId}/{$token}");
            
            $adminEmails = ['shubham@pharoseducation.in', 'support@pharoseducation.in', 'arshia@pharoseducation.in'];
            $this->send_system_email($adminEmails, 'Action Required: New User Registration', $this->emailNotiy($reviewLink,$data));
            
            $userHtml = '
            <div style="font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ddd; max-width: 600px;">
                <h2 style="color: #2c3e50;">Welcome to Pharos Education!</h2>
                <p>Hi ' . esc($data['full_name']) . ',</p>
                <p>Thank you for registering. Your account has been successfully created and is currently <strong>pending review by our administration team</strong>.</p>
                <p>You will receive another email as soon as your account is approved and ready to use.</p>
            </div>';
            
            $this->send_system_email($email, 'Registration Received - Pending Approval', $userHtml);
            
            return $this->response->setJSON([
                'status' => 'success', 
                'msg' => 'Registration successful! Your account is pending for approval.',
                //'redirect' => base_url('login')
            ]);
        } else {
            return $this->failServerError('Database insertion failed.');
        }
    }
    
    public function review_user($id, $token) {
        $user = $this->userModel->find($id);
        
        if (!$user) {
            return "<div style='text-align:center; padding:50px; font-family:sans-serif;'><h3>Error: User not found or has already been deleted.</h3></div>";
        }
        
        // Verify secure token
        $expectedToken = md5($id . $user['email'] . 'pharos_admin_secret');
        if ($token !== $expectedToken) {
            return "<div style='text-align:center; padding:50px; font-family:sans-serif;'><h3>Error: Invalid or expired authorization token.</h3></div>";
        }

        $data = [
            'user' => $user,
            'token' => $token
        ];

        return view('auth/admin_review', $data);
    }

    
    protected function emailNotiy($reviewLink,$data) {
           return $htmlMessage = '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;">
                <div style="background-color: #2c3e50; padding: 20px; text-align: center; color: white;">
                    <h2 style="margin: 0;">New User Registration</h2>
                    <p style="margin: 5px 0 0 0; font-size: 14px;">Action Required</p>
                </div>
                <div style="padding: 30px;">
                    <p>A new candidate has registered and is awaiting system approval.</p>
                    <table width="100%" cellpadding="10" cellspacing="0" style="border-collapse: collapse; border: 1px solid #eeeeee;">
                        <tr style="background-color: #f9f9f9;">
                            <td width="30%" style="font-weight: bold; border: 1px solid #eeeeee;">Full Name</td>
                            <td style="border: 1px solid #eeeeee;">' . esc($data['full_name']) . '</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; border: 1px solid #eeeeee;">Username</td>
                            <td style="border: 1px solid #eeeeee;">' . esc($data['username']) . '</td>
                        </tr>
                        <tr style="background-color: #f9f9f9;">
                            <td style="font-weight: bold; border: 1px solid #eeeeee;">Email</td>
                            <td style="border: 1px solid #eeeeee;">' . esc($data['email']) . '</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; border: 1px solid #eeeeee;">Phone</td>
                            <td style="border: 1px solid #eeeeee;">' . esc($data['phone']) . '</td>
                        </tr>
                    </table>
                    <div style="text-align: center; margin-top: 30px;">
                        <a href="' . $reviewLink . '" style="background-color: #2980b9; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">Review & Manage Candidate</a>
                    </div>
                </div>
            </div>';
    }
    
    

    public function process_review() {
        if (!$this->request->is('post')) {
            return $this->renderMessageScreen('Error', 'Invalid Request Method.', '#e74c3c');
        }
        $id = $this->request->getPost('id');
        $token = $this->request->getPost('token');
        $action = $this->request->getPost('action'); 
        
        $user = $this->userModel->find($id);
        
        if (!$user) return "User not found.";
        
        // Verify token
        $expectedToken = md5($id . $user['email'] . 'pharos_admin_secret');
        if ($token !== $expectedToken) return "Security verification failed.";

        if ($action === 'approve') {
            // 1. Activate User
            $this->userModel->update($id, ['is_active' => 1]);
            
            // 2. Notify User
            $loginLink = base_url('login');
            $userHtml = '
            <div style="font-family: Arial, sans-serif; padding: 20px; border: 1px solid #27ae60; max-width: 600px; border-top: 5px solid #27ae60;">
                <h2 style="color: #27ae60;">Account Approved!</h2>
                <p>Hi ' . esc($user['full_name']) . ',</p>
                <p>Great news! Your account has been reviewed and approved.</p>
                <p>You can now log in to the assessment dashboard using your email and password.</p>
                <a href="' . $loginLink . '" style="background-color: #27ae60; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 15px;">Log In Now</a>
            </div>';
            
            $this->send_system_email($user['email'], 'Your Account is Approved - Pharos Education', $userHtml);

            return "<h2 style='color:#27ae60; text-align:center; padding:50px;'>Candidate Approved! An email has been sent to them.</h2>";
            
        } elseif ($action === 'reject') {
            
            // 1. Notify User FIRST (before deleting them!)
            $userHtml = '
            <div style="font-family: Arial, sans-serif; padding: 20px; border: 1px solid #e74c3c; max-width: 600px; border-top: 5px solid #e74c3c;">
                <h2 style="color: #e74c3c;">Account Update</h2>
                <p>Hi ' . esc($user['full_name']) . ',</p>
                <p>We regret to inform you that your registration at Pharos Education could not be approved at this time.</p>
                <p>If you believe this is a mistake, please reach out to our support team.</p>
            </div>';
            
            $this->send_system_email($user['email'], 'Registration Update - Pharos Education', $userHtml);
            
            // 2. Delete User
            $this->userModel->delete($id);

            return "<h2 style='color:#e74c3c; text-align:center; padding:50px;'>Candidate Rejected & Deleted. They have been notified.</h2>";
        }
        
        return "Invalid Action.";
    }
    
    public function logout() {
        session()->destroy();
        helper('cookie');
        delete_cookie('ci_session');
        return redirect()->to('login');
    }
    
    /**
     * PRIVATE HELPER: Handles all secure SMTP Decryption and Sending
     */
    private function send_system_email($to, $subject, $message) {
        $settingsModel = new \App\Models\SmtpSettingsModel();
        // Grab the most recent config
        $smtpSettings = $settingsModel->orderBy('id', 'DESC')->first(); 
        
        if ($smtpSettings && !empty($smtpSettings['smtp_password_encrypted'])) {
            try {
                $encrypter = \Config\Services::encrypter();
                $decryptedSmtpPass = $encrypter->decrypt(hex2bin($smtpSettings['smtp_password_encrypted']));

                $emailConfig = [
                    'protocol'   => 'smtp',
                    'SMTPHost'   => $smtpSettings['smtp_host'],
                    'SMTPUser'   => $smtpSettings['smtp_user'],
                    'SMTPPass'   => $decryptedSmtpPass,
                    'SMTPPort'   => (int)$smtpSettings['smtp_port'],
                    'SMTPCrypto' => 'ssl', 
                    'mailType'   => 'html',
                    'charset'    => 'utf-8',
                    'CRLF'       => "\r\n",
                    'newline'    => "\r\n"
                ];

                $emailService = \Config\Services::email();
                $emailService->initialize($emailConfig);
                $emailService->setFrom($smtpSettings['smtp_user'], 'Pharos Education');
                $emailService->setTo($to);
                $emailService->setSubject($subject);
                $emailService->setMessage($message);
                
                if (! $emailService->send()) {
                    log_message('error', 'Email failed to send: ' . $emailService->printDebugger(['headers']));
                    return false;
                }
                return true;
            } catch (\Exception $e) {
                log_message('error', 'Failed to send email: ' . $e->getMessage());
                return false;
            }
        }
        return false;
    }
}