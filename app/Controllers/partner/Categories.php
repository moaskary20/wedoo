<?php
namespace App\Controllers\partner;
use App\Models\Category_model;
use App\Models\Partners_model;

class Categories extends Partner
{
    public  $validations, $db;
    protected Category_model $category; // Declare the $partner property with the correct type


    public function __construct()
    {
        parent::__construct();
        $this->category = new Category_model();
        $this->validation = \Config\Services::validation();
        $this->db      = \Config\Database::connect();
        helper('ResponceServices');
    }
    public function index()
    {
        if ($this->isLoggedIn) {
            if (!exists(['partner_id' => $this->userId, 'is_approved' => 1], 'partner_details')) {
                return redirect('partner/profile');
            }
            $is_already_subscribe = fetch_details('partner_subscriptions', ['partner_id' => $this->userId, 'status' => 'active']);
        if (empty($is_already_subscribe)) {
            return redirect('partner/subscription');
        }
            setPageInfo($this->data, 'Categories | Provider Panel', 'categories');
            return view('backend/partner/template', $this->data);
        } else {
            return redirect('partner/login');
        }
    }
}
