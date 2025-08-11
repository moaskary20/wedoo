<?php

namespace App\Controllers\admin;

use App\Models\Partner_subscription_model;
use App\Models\Subscription_model;

class Subscription extends Admin
{
    public $cities,  $validation, $db;
    protected $superadmin;
    protected Subscription_model $subscription;
    protected Partner_subscription_model $partner_Subscription;

    public function __construct()
    {
        parent::__construct();
        $this->subscription = new Subscription_model();
        $this->validation = \Config\Services::validation();
        $this->db      = \Config\Database::connect();
        $this->superadmin = $this->session->get('email');
        $this->partner_Subscription = new Partner_subscription_model();
        helper('ResponceServices');
    }
    public function index()
    {
        if (!$this->isLoggedIn || !$this->userIsAdmin) {
            return redirect('unauthorised');
        }
        setPageInfo($this->data, labels('subscription', 'Subscription') . '  | ' . labels('admin_panel', 'Admin Panel'), 'subscription');
        return view('backend/admin/template', $this->data);
    }
    public function add_ons_index()
    {
        if (!$this->isLoggedIn || !$this->userIsAdmin) {
            return redirect('unauthorised');
        }
        setPageInfo($this->data, labels('add_ons', 'Add Ons') . '  | ' . labels('admin_panel', 'Admin Panel'), 'add_on');
        return view('backend/admin/template', $this->data);
    }
    public function add_subscription()
    {
        if (!$this->isLoggedIn || !$this->userIsAdmin) {
            return redirect('unauthorised');
        }
        setPageInfo($this->data, labels('add_subscription', 'Add Subscription') . '  | ' . labels('admin_panel', 'Admin Panel'), 'add_subscription');
        $tax_data = fetch_details('taxes', ['status' => '1'], ['id', 'title', 'percentage']);
        $this->data['tax_data'] = $tax_data;
        return view('backend/admin/template', $this->data);
    }
    public function edit_subscription_page()
    {

        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('unauthorised');
            }
            helper('function');
            $uri = service('uri');
            $subscription_id = $uri->getSegments()[3];
            $subscription_data = fetch_details('subscriptions', ['id' => $subscription_id]);
            setPageInfo($this->data, labels('edit_subscription', 'Edit Subscription') . '  | ' . labels('admin_panel', 'Admin Panel'), 'edit_subscription');
            $this->data['subscription_data'] = $subscription_data;
            $tax_data = fetch_details('taxes', ['status' => '1'], ['id', 'title', 'percentage']);
            $this->data['tax_data'] = $tax_data;
            return view('backend/admin/template', $this->data);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Subscription.php - edit_subscription_page()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function edit_subscription()
    {

        try {
            $price = $this->request->getPost('price');
            $this->validation->setRules(
                [
                    'name' => ["rules" => 'required', "errors" => ["required" => "Please enter name"]],
                    'description' => ["rules" => 'required', "errors" => ["required" => "Please enter description",]],
                    'price' => ["rules" => 'required|numeric', "errors" => ["required" => "Please enter price",    "numeric" => "Please enter numeric value for price"]],
                    'discount_price' => ["rules" => 'required|numeric', "errors" => ["required" => "Please enter discounted price",    "numeric" => "Please enter numeric value for discounted price",]],
                ],
            );
            if (!$this->validation->withRequest($this->request)->run()) {
                $errors  = $this->validation->getErrors();
                return ErrorResponse($errors, true, [], [], 200, csrf_token(), csrf_hash());
            }
            $discount_price = $this->request->getPost('discount_price');
            $price = $this->request->getPost('price');


            if ($discount_price == 0 && $price == 0) {
            } elseif ($discount_price >= $price && $discount_price == $price) {
                return ErrorResponse("Discount price can not be higher than or equal to the price", true, [], [], 200, csrf_token(), csrf_hash());
            }
            $order_type = $this->request->getVar('order_type') == "limited" ? "limited" : "unlimited";
            if ($order_type == "limited" && $this->request->getVar('max_order') == "") {
                return ErrorResponse("Please add maximum number of order", true, [], [], 200, csrf_token(), csrf_hash());
            }
            $commission_type = $this->request->getVar('commission_type') == "yes" ? "yes" : "no";
            $duration = $this->request->getVar('duration_type') != "unlimited" ? $this->request->getVar('duration') : "unlimited";
            $publish = $this->request->getVar('publish') == "on" ? "1" : "0";
            $status = $this->request->getVar('status') == "on" ? "1" : "0";

            $check_payment_gateway = get_settings('payment_gateways_settings', true);
            $cod_setting =  $check_payment_gateway['cod_setting'];

            if (($commission_type == "yes")) {

                if ($cod_setting == 1) {
                    if ((($this->request->getVar('threshold') == "") || ($this->request->getVar('percentage') == ""))) {
                        return ErrorResponse("Please add commission fields", true, [], [], 200, csrf_token(), csrf_hash());
                    }
                } else if ($cod_setting == 0) {

                    if ((($this->request->getVar('percentage') == ""))) {
                        return ErrorResponse("Please add commission fields", true, [], [], 200, csrf_token(), csrf_hash());
                    }
                }
            }
            $subscription = [
                'name' => $this->removeScript($this->request->getVar('name')),
                'description' => $this->removeScript($this->request->getVar('description')),
                'duration' => $duration,
                'price' => $price,
                'discount_price' => $discount_price,
                'publish' => $publish,
                'order_type' => $order_type,
                'max_order_limit' => $this->request->getVar('max_order'),
                'service_type' => "unlimited",
                'max_service_limit' => $this->request->getVar('max_service'),
                'tax_type' => $this->request->getVar('tax_type'),
                'tax_id' => $this->request->getVar('tax_id'),
                'is_commision' => $commission_type,
                'commission_threshold' => $this->request->getVar('threshold'),
                'commission_percentage' => $this->request->getVar('percentage'),
                'status' => $status,
            ];
            $subscription_id = $this->request->getPost('subscription_id');
            if ($this->subscription->update($subscription_id, $subscription)) {
                return successResponse("Subscription updated successfully", false, [], [], 200, csrf_token(), csrf_hash());
            } else {
                return ErrorResponse("Subscription can not be saved", true, [], [], 200, csrf_token(), csrf_hash());
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Subscription.php - edit_subscription()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
        if (!$this->isLoggedIn || !$this->userIsAdmin) {
            return redirect('unauthorised');
        }
    }
    public function delete_subscription()
    {
        try {

            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('partner/login');
            }
            $result = checkModificationInDemoMode($this->superadmin);
            if ($result !== true) {
                return $this->response->setJSON($result);
            }
            $id = $this->request->getPost('id');
            $deleted = $this->db->table('subscriptions')->delete(['id' => $id]);
            if ($deleted) {
                return successResponse("Success in deleting the subscription", false, [], [], 200, csrf_token(), csrf_hash());
            } else {
                return ErrorResponse("Unsuccessful in deleting subscription", true, [], [], 200, csrf_token(), csrf_hash());
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Subscription.php - delete_subscription()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function add_store_subscription()
    {

        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('unauthorised');
            }
            $result = checkModificationInDemoMode($this->superadmin);
            if ($result !== true) {
                return $this->response->setJSON($result);
            }
            $this->validation->setRules([
                'name' => ["rules" => 'required', "errors" => ["required" => "Please enter name"]],
                'description' => ["rules" => 'required', "errors" => ["required" => "Please enter description",]],
                'price' => ["rules" => 'required|numeric', "errors" => ["required" => "Please enter price",     "numeric" => "Please enter numeric value for price"]],
                'discount_price' => ["rules" => 'required|numeric', "errors" => ["required" => "Please enter discounted price",    "numeric" => "Please enter numeric value for discounted price",]],
            ]);
            if (!$this->validation->withRequest($this->request)->run()) {
                $errors = $this->validation->getErrors();
                return ErrorResponse($errors, true, [], [], 200, csrf_token(), csrf_hash());
            }
            $price = $this->request->getPost('price');
            $discount_price = $this->request->getPost('discount_price');

            if ($discount_price == 0 && $price == 0) {
            } elseif ($discount_price >= $price && $discount_price == $price) {
                return ErrorResponse("Discount price can not be higher than or equal to the price", true, [], [], 200, csrf_token(), csrf_hash());
            }
            $order_type = $_POST['order_type'] == "limited" ? "limited" : "unlimited";
            $duration_type = $_POST['duration_type'] == "limited" ? "limited" : "unlimited";
            $duartion = $duration_type == "limited" ? $this->request->getVar('duration') : "unlimited";
            if ($order_type == "limited" && empty($this->request->getVar('max_order'))) {
                return ErrorResponse("Please add maximum number of order", true, [], [], 200, csrf_token(), csrf_hash());
            }
            if ($duration_type == "limited" && (empty($this->request->getVar('duration')) || $this->request->getVar('duration') == 0)) {
                return ErrorResponse("Please add duration", true, [], [], 200, csrf_token(), csrf_hash());
            }
            $commission_type = $_POST["commission_type"] == "yes" ? "yes" : "no";
            $publish = !empty($_POST["publish"]) && $_POST["publish"] == "on" ? "1" : "0";
            $status = !empty($_POST["status"]) && $_POST["status"] == "on" ? "1" : "0";

            $check_payment_gateway = get_settings('payment_gateways_settings', true);
            $cod_setting =  $check_payment_gateway['cod_setting'];

            if (($commission_type == "yes")) {

                if ($cod_setting == 1) {
                    if ((($this->request->getVar('threshold') == "") || ($this->request->getVar('percentage') == ""))) {
                        return ErrorResponse("Please add commission fields", true, [], [], 200, csrf_token(), csrf_hash());
                    }
                } else if ($cod_setting == 0) {

                    if ((($this->request->getVar('percentage') == ""))) {
                        return ErrorResponse("Please add commission fields", true, [], [], 200, csrf_token(), csrf_hash());
                    }
                }
            }
            $subscription = [
                'name' => $this->removeScript($this->request->getVar('name')),
                'description' => $this->removeScript($this->request->getVar('description')),
                'duration' => $duartion,
                'price' => $price,
                'discount_price' => $discount_price,
                'publish' => $publish,
                'order_type' => $order_type,
                'max_order_limit' => !empty($this->request->getVar('max_order')) ? $this->request->getVar('max_order') : 0,
                'service_type' => "limited",
                'max_service_limit' => !empty($this->request->getVar('max_service')) ? $this->request->getVar('max_service') : 0,
                'tax_type' => $this->request->getVar('tax_type'),
                'tax_id' => $this->request->getVar('tax_id'),
                'is_commision' => $commission_type,
                'commission_threshold' => !empty($this->request->getVar('threshold')) ? $this->request->getVar('threshold') : 0,
                'commission_percentage' => !empty($this->request->getVar('percentage')) ? $this->request->getVar('percentage') : 0,
                'status' => $status,
            ];
            if ($this->subscription->save($subscription)) {
                return successResponse("Subscription saved successfully", false, [], [], 200, csrf_token(), csrf_hash());
            } else {
                return ErrorResponse("Subscription can not be saved", true, [], [], 200, csrf_token(), csrf_hash());
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Subscription.php - add_store_subscription()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function list()
    {
        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
        print_r(json_encode($this->subscription->list(false, $search, $limit, $offset, $sort, $order)));
    }
    public function add_on_create_page()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            setPageInfo($this->data, labels('add_ons', 'Add Ons') . '  | ' . labels('admin_panel', 'Admin Panel'), 'create_add_ons');
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('unauthorised');
        }
    }
    public function subscriber_list()
    {
        try {
            if ($this->isLoggedIn && $this->userIsAdmin) {
                setPageInfo($this->data, labels('subscriber_list', 'Subscriber List') . '  | ' . labels('admin_panel', 'Admin Panel'), 'subscriber_list');
                $db      = \Config\Database::connect();
                $totalSubscriptionCount = $db->table('partner_subscriptions')->countAll();
                $activeSubscriptionCount = $db->table('partner_subscriptions')
                    ->where('status', 'active')
                    ->countAllResults();
                $expiredSubscriptionCount = $db->table('partner_subscriptions')
                    ->where('status', 'deactive')
                    ->countAllResults();
                $expiringSoonSubscriptionCount = $db->table('partner_subscriptions')
                    ->where('status', 'active')
                    ->where('expiry_date <=', date('Y-m-d', strtotime('+7 days')))
                    ->countAllResults();
                $this->data['totalSubscriptionCount'] = $totalSubscriptionCount;
                $this->data['activeSubscriptionCount'] = $activeSubscriptionCount;
                $this->data['expiredSubscriptionCount'] = $expiredSubscriptionCount;
                $this->data['expiringSoonSubscriptionCount'] = $expiringSoonSubscriptionCount;
                return view('backend/admin/template', $this->data);
            } else {
                return redirect('unauthorised');
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Subscription.php - subscriber_list()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function partner_subscription_list()
    {
        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
        print_r(json_encode($this->partner_Subscription->subscriber_list(false, $search, $limit, $offset, $sort, $order)));
    }
}
