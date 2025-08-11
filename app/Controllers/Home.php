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
        return  redirect()->to('admin/login');
    }

    public function unauthorised()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $data['admin'] = true;
        } else {
            $data['admin'] = false;
        }
        return redirect('admin/login');

        $data['title'] = "Unauthorised Access | $this->appName - Get Services On Demand";
        $data['main_page'] = "unauthorised";
        $this->data['meta_keywords'] = "On Demand, Services,On Demand Services, Service Provider";
        $this->data['meta_description'] = "$this->appName is one of the leading ";

        return view('/frontend/' . config('Site')->theme . '/template', $data);
    }

    public function payment_success()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $data['admin'] = true;
        } else {
            $data['admin'] = false;
        }
        $data['title'] = "Payment Status | $this->appName - Get Services On Demand";
        $data['main_page'] = "payment_status";
        $data['status'] = true;
        $this->data['meta_keywords'] = "On Demand, Services,On Demand Services, Service Provider";
        $this->data['meta_description'] = "$this->appName is one of the leading ";

        return view('/frontend/' . config('Site')->theme . '/template', $data);
    }

    public function payment_failed()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $data['admin'] = true;
        } else {
            $data['admin'] = false;
        }
        $data['title'] = "Payment Status | $this->appName - Get Services On Demand";
        $data['main_page'] = "payment_status";
        $data['status'] = false;
        $this->data['meta_keywords'] = "On Demand, Services,On Demand Services, Service Provider";
        $this->data['meta_description'] = "$this->appName is one of the leading ";

        return view('/frontend/' . config('Site')->theme . '/template', $data);
    }

    public function terms_condition()
    {

        $data['title'] = "Terms and Condition &mdash; $this->appName ";
        $data['main_page'] = "terms_condition";
        $this->data['meta_keywords'] = "On Demand, Services,On Demand Services, Service Provider";
        $this->data['meta_description'] = "$this->appName is one of the leading ";

        $data['logged'] = false;
        if ($this->isLoggedIn) {
            $data['logged'] = true;
        }
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $data['admin'] = true;
        } else {
            $data['admin'] = false;
        }
        $data['text'] = get_settings('terms_conditions', true)['terms_conditions'];
        return view('/frontend/' . config('Site')->theme . '/template', $data);
    }
    public function privacy_policy()
    {

        $data['title'] = "Privacy Policy &mdash; $this->appName ";
        $data['main_page'] = "privacy_policy";
        $this->data['meta_keywords'] = "On Demand, Services,On Demand Services, Service Provider";
        $this->data['meta_description'] = "$this->appName is one of the leading ";

        $data['logged'] = false;
        if ($this->isLoggedIn) {
            $data['logged'] = true;
        }
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $data['admin'] = true;
        } else {
            $data['admin'] = false;
        }
        $data['text'] = get_settings('privacy_policy', true)['privacy_policy'];
        return view('/frontend/' . config('Site')->theme . '/template', $data);
    }
    public function refund_policy()
    {

        $data['title'] = "Refund Policy &mdash; $this->appName ";
        $data['main_page'] = "refund_policy";
        $this->data['meta_keywords'] = "On Demand, Services,On Demand Services, Service Provider";
        $this->data['meta_description'] = "$this->appName is one of the leading ";

        $data['logged'] = false;
        if ($this->isLoggedIn) {
            $data['logged'] = true;
        }
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $data['admin'] = true;
        } else {
            $data['admin'] = false;
        }
        $data['text'] = get_settings('refund_policy', true)['refund_policy'];
        return view('/frontend/' . config('Site')->theme . '/template', $data);
    }
    public function send_mail()
    {
    }
}
