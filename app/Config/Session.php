<?php namespace Config;

use CodeIgniter\Config\BaseConfig;

class Session extends BaseConfig
{
    public $driver = 'CodeIgniter\Session\Handlers\FileHandler';
    public $cookieName = 'ci_session';
    public $expiration = 7200;
    public $savePath = WRITEPATH . 'session';
    public $matchIP = false;
    public $timeToUpdate = 300;
    public $regenerateDestroy = false;
}