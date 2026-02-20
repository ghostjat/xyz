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
    
    // Fixed: Redirect index to login instead of blank view
    public function index() {
        return redirect()->to('login');
    } 

    public function login() {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('tests/dashboard');
        }
        $this->response->noCache();
        return $this->spa_view('auth/index',useLayout:false);
    }

    public function register() {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('tests/dashboard');
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
            
            // 4. Session Security
            session()->regenerate();

            session()->set([
                'id'         => $user['id'], 
                'username'   => $user['username'],
                'fname'      => $user['full_name'],
                'email'      => $user['email'], 
                'category'   => $user['category'],
                'isLoggedIn' => TRUE
            ]);
            
            // FIX: Use Cache service to delete the key (Throttler->remove does not exist)
            \Config\Services::cache()->delete($throttleKey);

            //return $this->respond(['status' => 'success', 'redirect' => base_url('tests/dashboard')]);
            return $this->spa_view('dashboard/index', [], false);
        } else {
            return $this->fail('Invalid Credentials', 401);
        }
    }

    public function store() {
        $rules = [
            'username'  => 'required|min_length[3]|max_length[50]|alpha_numeric_space',
            'email'     => 'required|valid_email|is_unique[users.email]',
            'full_name' => 'required',
            'phone'     => 'required|min_length[10]|max_length[15]|numeric',
            'password'  => 'required|min_length[8]',
        ];

        if (! $this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        $data = [
            'username'  => $this->request->getVar('username'),
            'email'     => $this->request->getVar('email'),
            'phone'     => $this->request->getVar('phone'),
            'full_name' => $this->request->getVar('full_name'),
            'password'  => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
        ];

        $result = $this->userModel->insert($data);

        if ($result) {
            return $this->respondCreated(['status' => 'success', 'redirect' => base_url('tests/index')]);
        } else {
            return $this->failServerError('Database insertion failed.');
        }
    }

    public function logout() {
        session()->destroy();
        return redirect()->to('login');
    }
}