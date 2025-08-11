<?php
// app/Services/SimpleQueueService.php
namespace App\Services;

class SimpleQueueService
{
    private $logFile;

    public function __construct()
    {
        $this->logFile = WRITEPATH . 'logs/queue_process.log';
    }

    public function startBackgroundProcess($file_manager)
    {


        try {
            // Clear old log file
            if (file_exists($this->logFile)) {
                unlink($this->logFile);
            }

            // Write initial log
            $this->writeLog("Queue started at: " . date('Y-m-d H:i:s'));
            $this->writeLog("file_manager: " . $file_manager);

            // Build the command to run our simple script
            $scriptPath = APPPATH . 'Scripts/process_queue.php';
            
            $phpBinary = $this->getPhpBinaryPath();

            if (empty($phpBinary)) {
                // Fallback to default path if 'which php' does not return any result
                $phpBinary = '/usr/bin/php';
            }
            if (empty($phpBinary)) {
                // Fallback to default path if 'which php' does not return any result
                $phpBinary = '/usr/bin/php';
            }

            $command = sprintf(
                'nohup %s %s > /dev/null 2>&1 & echo $!',
                $phpBinary,
                $scriptPath
            );


            $this->writeLog("Executing command: " . $command);
            $this->writeLog("Executing script: " . $scriptPath);
            require $scriptPath;
            $this->writeLog("Script execution completed.");

            $this->writeLog("Background process initiated");
            return true;
        } catch (\Exception $e) {
            $this->writeLog("Error: " . $e->getMessage());
            return false;
        }
    }

    private function writeLog($message)
    {
        file_put_contents($this->logFile, $message . "\n", FILE_APPEND);
    }

    private function getPhpBinaryPath()
    {
        // Check for PHP_BINARY constant (most reliable)
        if (defined('PHP_BINARY') && PHP_BINARY && is_file(PHP_BINARY)) {
            return PHP_BINARY;
        }

        // Try to find PHP in common locations
        $commonPaths = [
            '/usr/bin/php',
            '/usr/local/bin/php',
            '/opt/local/bin/php',
            // Add more common paths as needed
        ];

        foreach ($commonPaths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        // Fallback: Use a default path or throw an exception
        // This is a last resort and may not work on all systems
        return '/usr/bin/php';
        // Or throw an exception:
        // throw new \RuntimeException("Could not determine PHP binary path."); 
    }
}
