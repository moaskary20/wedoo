<?php

namespace App\Exceptions;

use CodeIgniter\Exceptions\PageNotFoundException as CodeIgniterPageNotFoundException;

class PageNotFoundException extends CodeIgniterPageNotFoundException
{
    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        return view('errors/html/error_404'); // Load your custom 404 view
    }
}


?>