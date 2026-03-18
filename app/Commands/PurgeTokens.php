<?php 
namespace Modules\PharosEd\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\I18n\Time;

class PurgeTokens extends BaseCommand
{
    // The command line grouping
    protected $group       = 'ERP Security';

    // The command's name
    protected $name        = 'tokens:purge';

    // The command's short description
    protected $description = 'Deletes expired and used route tokens from the database.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        
        // Get the current time
        $now = Time::now()->toDateTimeString();

        // Count how many we are about to delete (optional, good for logging)
        $expiredCount = $db->table('route_tokens')
                           ->where('expires_at <', $now)
                           ->countAllResults();

        if ($expiredCount > 0) {
            // Delete tokens where the expiration date is in the past
            $db->table('route_tokens')->where('expires_at <', $now)->delete();
            
            // Log the action so you have an audit trail
            log_message('info', "Successfully purged {$expiredCount} expired ERP tokens.");
            CLI::write("Successfully purged {$expiredCount} expired tokens.", 'green');
        } else {
            CLI::write('No expired tokens found to purge.', 'yellow');
        }
    }
}