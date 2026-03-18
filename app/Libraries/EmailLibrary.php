<?php namespace App\Libraries;

use App\Models\SmtpSettingsModel;

class EmailLibrary
{
    /**
     * Sends a system email using dynamic database SMTP settings.
     *
     * @param string|array $to      Recipient email(s)
     * @param string       $subject Email Subject
     * @param string       $message Email Body (HTML/Text)
     * @param string|array $bcc     Optional BCC email(s) for admin copies
     * @return bool
     */
    public function send_system_email($to, $subject, $message, $bcc = null): bool
    {
        $settingsModel = new SmtpSettingsModel();
        
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
                    'SMTPCrypto' => 'ssl', // Change to 'tls' if using port 587
                    'mailType'   => 'html',
                    'charset'    => 'utf-8',
                    'CRLF'       => "\r\n",
                    'newline'    => "\r\n"
                ];

                $emailService = \Config\Services::email();
                $emailService->initialize($emailConfig);
                
                // Set Pharos Education as the sender
                $emailService->setFrom($smtpSettings['smtp_user'], 'Pharos Education');
                $emailService->setTo($to);
                
                // Add BCC if provided (useful for support@domain.in)
                if ($bcc !== null) {
                    $emailService->setBCC($bcc);
                }

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
        
        log_message('error', 'Email failed: SMTP Settings not found or password missing in database.');
        return false;
    }
}