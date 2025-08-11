<?php

namespace App\Services;

use Symfony\Component\Process\Process;

class SimpleQueueService {
    private $logFile;

    public function __construct() {
        $this->logFile = WRITEPATH . 'logs/queue_process.log';
    }

    public function startBackgroundProcess($file_manager) {
        try {
            // Clear old log file
            if (file_exists($this->logFile)) {
                unlink($this->logFile);
            }

            // Write initial log
            $this->writeLog("Queue started at: " . date('Y-m-d H:i:s'));
            $this->writeLog("file_manager: " .$file_manager);

            // Build the command to run our simple script
            $scriptPath = APPPATH . 'Scripts/process_queue.php';
            $process = new Process([
                'php',
                $scriptPath
            ]);

            $process->setTimeout(null); // Disable timeout
            $process->start();

            $process->waitUntil(function ($type, $buffer) {
                return strpos($buffer, 'Process completed successfully') !== false;
            });

            $pid = $process->getPid();

            $this->writeLog("Executing command: php $scriptPath");
            if ($pid) {
                $this->writeLog("Process ID: " . $pid);
            } else {
                $this->writeLog("Unable to fetch Process ID");
            }
            $this->writeLog("Background process initiated");

            return true;

        } catch (\Exception $e) {
            $this->writeLog("Error: " . $e->getMessage());
            return false;
        }
    }

    private function writeLog($message) {
        file_put_contents($this->logFile, $message . "\n", FILE_APPEND);
    }
}
