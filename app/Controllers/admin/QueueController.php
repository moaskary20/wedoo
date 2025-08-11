<?php

namespace App\Controllers\admin;

use CodeIgniter\API\ResponseTrait;
use Exception;
use App\Jobs\Email;
use CodeIgniter\CLI\CLI;
use CodeIgniter\I18n\Time;
use CodeIgniter\Queue\Handlers\DatabaseHandler;
use Config\Queue;
// use ResponseTrait;
// use ReflectionHelper;

class QueueController extends Admin

{
    protected $config; // Declare the property

    public function work()
    {
            // Check if this is a CLI request
        if (!is_cli()) {
            return $this->response->setStatusCode(404)->setBody('Not Found');
        }

        try {
            // Get the command instance
            $command = \Config\Services::commands();

            // Set up the arguments as they would appear in CLI
            $params = [
                'spark',
                'queue:work',
                'filemanagerchanges',
                '--force'
            ];

            // Run the command
            $command->run($params);

            // If you want to capture output, you can use:
            // ob_start();
            // $command->run($params);
            // $output = ob_get_clean();
            // CLI::write($output);

            return CLI::write('Queue worker started successfully', 'green');
        } catch (\Exception $e) {
            return CLI::error($e->getMessage());
        }
    }
    // Stop the queue worker
    public function stopWorker()
    {
        $command = 'pkill -f "php ' . FCPATH . '../spark queue:work"';
        @exec($command, $output, $returnVar);

        if ($returnVar === 0) {
            return $this->response->setJSON(['status' => 'success', 'message' => labels('Queue worker stopped', 'Queue worker stopped')]);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => labels('Failed to stop queue worker', 'Failed to stop queue worker')]);
    }

    // Flush the queue
    public function flushQueue()
    {
        $command = '/usr/bin/php ' . FCPATH . '../spark queue:flush';
        @exec($command, $output, $returnVar);

        if ($returnVar === 0) {
            return $this->response->setJSON(['status' => 'success', 'message' => labels('Queue flushed', 'Queue flushed')]);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => labels('Failed to flush queue', 'Failed to flush queue')]);
    }

    // public function queueNumbers()
    // {
    //     // $queue = service('queue');
    //     // $jobId = $queue->push('test', 'numberLoggerJob', ['message' => 'numberLoggerJob message goes here']);
    //     // echo "success";
    //     $queue = service('queue');
    //     $jobId = $queue->push('filemanagerchanges', 'fileManagerChangesJob', ['file_manager' => 'aws_s3']);
    // }

    // public function processQueue()
    // {
    //     // try {


    //     //     // Direct command execution
    //     //     $output = shell_exec('php ' . ROOTPATH . 'spark queue:work filemanagerchanges --force');
    //     //     print_r($output);
    //     //     die;
    //     //     return $output;
    //     // } catch (\Exception $e) {
    //     //     log_message('error', 'Queue processing error: ' . $e->getMessage());
    //     //     return $e->getMessage();
    //     // }
    //     if (!is_cli()) {
    //         return $this->response->setStatusCode(404)->setBody('Not Found');
    //     }

    //     try {
    //         // Get the command instance
    //         $command = \Config\Services::commands();

    //         // Set up the arguments as they would appear in CLI
    //         $params = [
    //             'spark',
    //             'queue:work',
    //             'filemanagerchanges',
    //             '--force'
    //         ];

    //         // Run the command
    //         $command->run($params);

    //         // If you want to capture output, you can use:
    //         // ob_start();
    //         // $command->run($params);
    //         // $output = ob_get_clean();
    //         // CLI::write($output);

    //         return CLI::write('Queue worker started successfully', 'green');
    //     } catch (\Exception $e) {
    //         return CLI::error($e->getMessage());
    //     }
    // }
    // public function processQueue()
    // {

    //     // Check if this is a CLI request
    //     if (!is_cli()) {
    //         return $this->response->setStatusCode(404)->setBody('Not Found');
    //     }

    //     try {
    //         // Get the command instance
    //         $command = \Config\Services::commands();

    //         // Set up the arguments as they would appear in CLI
    //         $params = [
    //             'spark',
    //             'queue:work',
    //             'filemanagerchanges',
    //             '--force'
    //         ];

    //         // Run the command
    //         $command->run($params);

    //         // If you want to capture output, you can use:
    //         // ob_start();
    //         // $command->run($params);
    //         // $output = ob_get_clean();
    //         // CLI::write($output);

    //         return CLI::write('Queue worker started successfully', 'green');
    //     } catch (\Exception $e) {
    //         return CLI::error($e->getMessage());
    //     }
    // }
}
