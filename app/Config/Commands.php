<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Queue\Commands\QueueWork;

class Commands extends BaseConfig
{
    public $commands = [
        'queue:work' =>QueueWork::class,
    ];
}
