<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Base Controller
 * Provides authentication and common functionality
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = ['url', 'form', 'session', 'cookie'];

    /**
     * Session instance
     * @var \CodeIgniter\Session\Session
     */
    protected $session;

    /**
     * Current user data
     * @var array|null
     */
    protected $currentUser;

    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        $this->session = \Config\Services::session();
        
        // Load current user if logged in
        $this->loadCurrentUser();
    }

    /**
     * Load current user from session
     */
    protected function loadCurrentUser()
    {
        $userId = $this->session->get('user_id');
        if ($userId) {
            $userModel = new \App\Models\UserModel();
            $this->currentUser = $userModel->find($userId);
            
            // Update last activity
            if ($this->currentUser) {
                $this->currentUser['last_activity'] = date('Y-m-d H:i:s');
            }
        }
    }

    /**
     * Check if user is logged in
     * @return bool
     */
    protected function isLoggedIn(): bool
    {
        return !empty($this->currentUser);
    }

    /**
     * Require authentication - redirect if not logged in
     */
    protected function requireAuth()
    {
        if (!$this->isLoggedIn()) {
            $this->session->setFlashdata('error', 'Please login to continue');
            return redirect()->to('/login');
        }
    }

    /**
     * JSON response helper
     * @param mixed $data
     * @param int $status
     * @return ResponseInterface
     */
    protected function jsonResponse($data, int $status = 200): ResponseInterface
    {
        return $this->response
            ->setStatusCode($status)
            ->setJSON($data);
    }

    /**
     * Success JSON response
     */
    protected function success($data = null, string $message = 'Success', int $status = 200): ResponseInterface
    {
        return $this->jsonResponse([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ], $status);
    }

    /**
     * Error JSON response
     */
    protected function error(string $message, $errors = null, int $status = 400): ResponseInterface
    {
        return $this->jsonResponse([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'timestamp' => date('Y-m-d H:i:s')
        ], $status);
    }

    /**
     * Log user activity
     */
    protected function logActivity(string $action, string $table = null, int $recordId = null, array $data = [])
    {
        try {
            //$auditModel = new \App\Models\AuditLogModel();
            $db = \Config\Database::connect();
            // Prepare log data
            $logData = [
                'user_id' => $this->currentUser['id'] ?? null,
                'action_type' => $action,
                'table_name' => $table,
                'record_id' => $recordId,
                'new_values' => !empty($data) ? json_encode($data) : null,
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent() ? $this->request->getUserAgent()->getAgentString() : null
            ];
            
            
            //$auditModel->insert($logData);
            $db->table('audit_logs')->insert($logData);
        } catch (\Exception $e) {
            // Silently fail - don't break the application if logging fails
            log_message('error', 'Failed to log activity: ' . $e->getMessage());
        }
    }
}