<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Routing extends BaseConfig
{
    public $defaultNamespace = 'App\Controllers'; 
    public $defaultController = 'Home';
    public $defaultMethod = 'index';
    public $translateURIDashes = true;
    public $override404 = null; 
    public $autoRoute = false; 
    public $routeFiles = []; 
    public $prioritize = []; 
    public bool $multipleSegmentsOneParam = false;
    public $routes = [
        'default_controller' => 'Home', 
        'routes' => [
            // Define any general routes here if needed
        ],
    ];
}