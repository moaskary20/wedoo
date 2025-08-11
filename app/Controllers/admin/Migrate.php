<?php

namespace App\Controllers\admin;

class Migrate extends Admin
{
    public $migrate;   
    
    public function __construct()
    {
        $this->migrate =  \Config\Services::migrations(); 
    }

    public function index()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            if (!$this->is_dir_empty(FCPATH . "\\app\\Database\\Migrations")) {
                try {
                    echo"<pre>";
                    print_r($this->migrate->findMigrations());
                    $this->migrate->latest();
                } catch (\Throwable $e) {
                    echo $e->getMessage();
                }
            }
        } else {
            echo labels('unauthorized_to_access_this_part', 'Unauthorized to access this part');
        }
    }
    
    public function rollback(int $version = 0)
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            if (!$this->is_dir_empty(FCPATH . "\\app\\Database\\Migrations")) {
                try {
                    if(!empty($version) && is_numeric($version)){
                        $this->migrate->regress($version);
                    } else {
                        echo labels('version_not_specified', 'Version not specified');
                    }
                } catch (\Throwable $e) {
                    echo $e->getMessage();
                }
            }
        } else {
            echo labels('unauthorized_to_access_this_part', 'Unauthorized to access this part');
        }
    }

    public function is_dir_empty($dir)
    {
        if (!is_readable($dir)) return NULL;
        return (count(scandir($dir)) == 2);
    }
    
}
