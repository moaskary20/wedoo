<?php

namespace App\Controllers\admin;

use CILogViewer\CILogViewer;

class LogViewerController extends Admin
{
    public $addresses,  $validation,$logViewer,$uri ;
    public function __construct()
    {
        parent::__construct();
        $this->validation = \Config\Services::validation();
    }
    public function index()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $logViewer = new CILogViewer();
            return $logViewer->showLogs();
        } else {
            return redirect('admin/login');
        }
    }
}
