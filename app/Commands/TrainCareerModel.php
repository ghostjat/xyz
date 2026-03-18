<?php 
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\CareerModelTrainer;

class TrainCareerModel extends BaseCommand
{
    // The group the command is lumped under when listing commands
    protected $group       = 'Machine Learning';

    // The command's name
    protected $name        = 'ml:train';

    // The command's short description
    protected $description = 'Trains the Rubix ML Career Prediction Model from a CSV dataset.';

    // The command's usage format
    protected $usage       = 'ml:train [csv_path]';

    // The command's arguments
    protected $arguments   = [
        'csv_path' => 'The relative path to the training CSV file (e.g., writable/uploads/data.csv).',
    ];

    public function run(array $params)
    {
        // ML training requires a lot of RAM. Temporarily boost it for the CLI process.
        //ini_set('memory_limit', '2048M'); 
        set_time_limit(0); // Prevent CLI timeouts

        CLI::write('=========================================', 'yellow');
        CLI::write('  PHAROS AI: CAREER MODEL TRAINING       ', 'yellow');
        CLI::write('=========================================', 'yellow');

        // Grab the CSV path from the command line argument
        $csvPath = array_shift($params);

        // Grab the CSV filename from the command line argument
        $csvFile = array_shift($params);

        // Default to onet_data.csv if nothing is typed
        if (empty($csvFile)) {
            $csvFile = CLI::prompt('Enter the CSV filename', 'onet_data.csv');
        }

        // BULLETPROOF PATH: Force CodeIgniter to look exactly in the writable/ folder
        // WRITEPATH automatically generates the absolute server path (e.g., /home/user/public_html/writable/)
        $absolutePath = WRITEPATH . basename($csvFile);

        // Validate the file exists
        if (!file_exists($absolutePath)) {
            CLI::error("CRITICAL ERROR: Dataset not found. The system searched exactly here: '{$absolutePath}'");
            CLI::newLine();
            return;
        }

        CLI::write("Loading dataset from: {$csvPath}", 'cyan');
        CLI::write("Extracting features, building Random Forest, and cross-validating...", 'cyan');
        CLI::newLine();

        try {
            $trainer = new CareerModelTrainer();
            
            $startTime = microtime(true);
            
            // Call the new tuneAndTrain function!
            CLI::write("Running Hyperparameter Grid Search... This will take a few minutes.", 'yellow');
            $result = $trainer->tuneAndTrain($absolutePath);
            
            $elapsedTime = round(microtime(true) - $startTime, 2);

            CLI::write('--- TRAINING & TUNING COMPLETE ---', 'green');
            CLI::write("Time Taken: {$elapsedTime} seconds", 'white');
            CLI::write("Models Evaluated: {$result['total_runs']} combinations", 'cyan');
            
            CLI::newLine();
            CLI::write("WINNING CONFIGURATION:", 'yellow');
            CLI::write("- Trees: " . $result['best_params']['trees'], 'white');
            CLI::write("- Max Depth: " . $result['best_params']['depth'], 'white');
            CLI::write("- Features Per Split: " . $result['best_params']['features'], 'white');
            CLI::newLine();

            CLI::write("Peak Accuracy Achieved: " . CLI::color($result['accuracy'], 'green'));

            if ($result['saved']) {
                CLI::write("Status: Model beat the threshold! Saved to writable/models/career_model.rbx", 'green');
                CLI::write("Metadata successfully exported to career_metadata.json", 'green');
            } else {
                CLI::write("Status: Peak accuracy failed to meet the threshold. NOT SAVED.", 'red');
            }
            

        } catch (\Exception $e) {
            CLI::newLine();
            CLI::error('TRAINING FAILED: ' . $e->getMessage());
            CLI::write('Stack Trace:', 'red');
            CLI::write($e->getTraceAsString(), 'red');
        }
    }
}