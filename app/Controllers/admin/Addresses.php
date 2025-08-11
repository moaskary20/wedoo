<?php
namespace App\Controllers\admin;
use App\Models\Addresses_model;
class Addresses extends Admin
{
    public $addresses,  $validation;
    public function __construct()
    {
        parent::__construct();
        $this->addresses = new Addresses_model();
        $this->validation = \Config\Services::validation();
    }
    public function index()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            setPageInfo($this->data, labels('addresses', 'Addresses') . ' | ' . labels('admin_panel', 'Admin Panel'), 'addresses');
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }
    public function list()
    {
        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
        print_r($this->addresses->list(false, $search, $limit, $offset, $sort, $order));
    }
}
