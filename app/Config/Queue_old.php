<?php

namespace Config;

use App\Jobs\Email;
use CodeIgniter\Queue\Config\Queue as BaseQueue;

class Queue extends BaseQueue
{
    // Default driver for the queue
    public $driver = 'database';

    // Configuration for the database queue
    public array $database = [
        'table'      => 'ci_jobs',      // Table to store queued jobs
        'connection' => 'default',      // Connection name (set this according to your db config)
        'dbGroup'    => 'default',      // Specify the dbGroup
        'getShared'  => true,           // Whether to use a shared connection
        'skipLocked'=>false,
    ];
    public array $jobHandlers = [
        'email' => Email::class,
    ];
}
