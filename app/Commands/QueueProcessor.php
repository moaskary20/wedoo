<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use App\Services\SimpleQueueService;

class QueueProcessor extends BaseCommand {
    protected $group = 'Queue';
    protected $name = 'queue';
    protected $description = 'Process the queue';
    protected $usage = 'queue process';
    
    public function run(array $params) {
        if (isset($params[0]) && $params[0] === 'process') {
            // Disable time limit for CLI
            set_time_limit(0);
            
            $queueService = new SimpleQueueService();
            $queueService->processQueue();
        }
    }
}