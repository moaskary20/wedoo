<?php

namespace App\Controllers;

use App\Controllers\Frontend;


class Home extends Frontend
{
	public function __construct()
	{
		parent::__construct();
	}
	public function index()
	{

		if ($this->isLoggedIn && $this->userIsAdmin) {
			$data['admin'] = true;
		} else {
			$data['admin'] = false;
		}
		$data['title'] = "Login | $this->appName ";

		$data['main_page'] = "view_signup";
		$this->data['meta_keywords'] = "On Demand, Services,On Demand Services, Service Provider";
		$this->data['meta_description'] = "$this->appName is one of the leading ";
	}
}
