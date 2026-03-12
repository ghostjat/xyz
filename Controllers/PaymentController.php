<?php namespace App\Controllers;

use App\Models\UserModel;
use App\Libraries\EmailLibrary;

class PaymentController extends BaseController {

    protected $db;

    public function __construct() {
        $this->db = \Config\Database::connect();
    }

    // 1. Display the Payment Page
    public function index() {
        $userId = session()->get('user_id');
        if (!$userId) {
            return redirect()->to('/login');
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);

        $feeAmount = 1000.00; // ₹499
        
        // Check if user has an already successful payment
        $hasPaid = $this->db->table('payments')
            ->where('user_id', $userId)
            ->where('status', 'success')
            ->countAllResults() > 0;

        if ($hasPaid) {
            return redirect()->to('dashboard')->with('success', 'You have already paid. Tests are unlocked!');
        }

        // Create a pending payment record to link the manual submission to
        $builder = $this->db->table('payments');
        $builder->insert([
            'user_id' => $userId,
            'school_id' => $user['school_id'] ?? null,
            'payment_type' => 'assessment',
            'amount' => $feeAmount,
            'final_amount' => $feeAmount,
            'currency' => 'INR',
            'payment_method' => 'upi', 
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $paymentId = $this->db->insertID();

        $data = [
            'user' => $user,
            'amount' => $feeAmount,
            'payment_id' => $paymentId
        ];

        return $this->spa_view('payment/index', $data, false);
    }

    // 2. Verify Manual Payment
    public function verify() {
        if (!$this->request->isAJAX() && !$this->request->is('post')) {
            return $this->response->setStatusCode(400);
        }

        $userId = session()->get('user_id');
        
        // XSS & Input Sanitization
        $paymentId = $this->request->getPost('payment_id', FILTER_SANITIZE_NUMBER_INT);
        $upiReference = $this->request->getPost('upi_reference'); 
        $payerName = $this->request->getPost('payer_name');
        $amountPaid = $this->request->getPost('amount_paid', FILTER_SANITIZE_NUMBER_FLOAT);

        if (!$userId || !$paymentId || !$upiReference) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Invalid Request data. Please provide the UPI reference number.']);
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);
        
        // 1. Update Payment Record
        // We leave the status as 'pending' so an admin can manually approve it later
        $builder = $this->db->table('payments');
        $builder->where('id', $paymentId)
                ->where('user_id', $userId)
                ->update([
                    'status' => 'pending', // Remains pending until admin verification
                    'gateway_name' => 'Manual UPI',
                    'gateway_transaction_id' => $upiReference,
                    // Store the payer name in the gateway_response JSON field
                    'gateway_response' => json_encode(['payer_name' => $payerName]),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

        // 2. Send Email Notification
        $this->sendPaymentPendingEmail($user['email'], $payerName, $upiReference, $amountPaid, $paymentId);

        return $this->response->setJSON([
            'status' => 'success', 
            'msg' => 'Payment details submitted successfully! We will verify your payment and unlock your assessments shortly.',
            'redirect' => base_url('dashboard') // Or redirect to a "waiting for approval" page
        ]);
    }

    /**
     * Helper function to send the email
     */
    private function sendPaymentPendingEmail($userEmail, $payerName, $upiReference, $amount, $paymentId) {
        $mailer = new EmailLibrary();
        $subject = "Payment Verification Pending - Order #{$paymentId}";
        
        $message = "
        <html>
        <body>
            <h3>Payment Pending Verification</h3>
            <p>Dear {$payerName},</p>
            <p>We have successfully received your manual UPI payment submission. Our team is currently verifying the transaction.</p>
            <p><strong>Payment Details:</strong></p>
            <ul>
                <li><strong>Order ID:</strong> #{$paymentId}</li>
                <li><strong>Amount:</strong> ₹{$amount}</li>
                <li><strong>UPI Reference No:</strong> {$upiReference}</li>
            </ul>
            <p>Once verified, your assessments will be unlocked automatically, and you will receive a confirmation email.</p>
            <br>
            <p>Thank you,<br>Support Team</p>
            <br>
            <p>Pharos Education Consultancy</p>
        </body>
        </html>
        ";

        $mailer->send_system_email($userEmail, $subject, $message, ['support@pharoseducation.in','shubham@pharoseducation.in']);
    }
}