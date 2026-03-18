<?php namespace Modules\PharosEd\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SetupEmail extends BaseCommand
{
    // The command group and name used to call it via the terminal
    protected $group       = 'Setup';
    protected $name        = 'setup:email';
    protected $description = 'Securely sets up the encrypted SMTP credentials in the database.';

    public function run(array $params)
    {
        CLI::write('--- Secure SMTP Setup ---', 'cyan');

        // Prompt the user for details directly in the terminal
        $host = CLI::prompt('SMTP Host', 'mail.pharoseducation.in');
        $user = CLI::prompt('SMTP User', 'noreply@pharoseducation.in');
        $port = CLI::prompt('SMTP Port', '465');
        
        // Prompt for the password (this requires you to type it in the terminal)
        $password = CLI::prompt('Enter the REAL SMTP Password');

        if (empty($password)) {
            CLI::error('Password cannot be empty. Setup aborted.');
            return;
        }

        try {
            $encrypter = \Config\Services::encrypter();
            
            // Encrypt and convert to hex
            $encryptedPassHex = bin2hex($encrypter->encrypt($password));

            // Determine correct Model (Update this if your model is SmtpSettingsModel)
            $settingsModel = new \App\Models\SmtpSettingsModel();
            
            // Optional: Truncate table to ensure only one active config exists
            $settingsModel->truncate(); 

            $settingsModel->insert([
                'smtp_host'               => $host,
                'smtp_user'               => $user,
                'smtp_password_encrypted' => $encryptedPassHex,
                'smtp_port'               => $port
            ]);

            CLI::newLine();
            CLI::write('✅ Secure Setup Complete!', 'green');
            CLI::write('Your SMTP password has been encrypted and saved to the database.', 'green');
            
        } catch (\Exception $e) {
            CLI::error('An error occurred: ' . $e->getMessage());
        }
    }
}