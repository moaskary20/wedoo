<?php

namespace App\Controllers\admin;

use App\Models\Admin_contact_query;
use App\Models\Orders_model;
use App\Models\Partners_model;
use App\Models\Users_model;
class Dashboard extends Admin
{

    protected Users_model $user_model; 
    protected Orders_model $orders; 

    public function __construct()
    {
        parent::__construct();
        $this->user_model = new \App\Models\Users_model();
        $this->orders = new \App\Models\Orders_model();
        helper('ResponceServices');
    }
    public function cancle_elapsed_time_order()
    {
        try {
            $currentDate = date('Y-m-d');
            $currentTimestamp = time();
            $currentTime = date('H:i', $currentTimestamp);
            $prepaid_orders = fetch_details('orders', ['status' => 'awaiting', 'payment_status' => 0, 'date_of_service' => $currentDate]);
            $setting = get_settings('general_settings', true);
            $prepaid_booking_cancellation_time = (isset($setting['prepaid_booking_cancellation_time'])) ? intval($setting['prepaid_booking_cancellation_time']) : "30";
            foreach ($prepaid_orders as $order) {
                $serviceTime = strtotime($order['starting_time']);
                $checkTime = $serviceTime - ($prepaid_booking_cancellation_time * 60); // 1800 seconds = 30 minutes
                if ($checkTime <= strtotime($currentTime)) {
                    verify_transaction($order['id']);
                }
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Dashboard.php - cancle_elapsed_time_order()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function update_subscription_status()
    {
        try {
            $db = \Config\Database::connect();
            $builder1 = $db->table('users u1');
            $partners1 = $builder1->select("u1.username, u1.city, u1.latitude, u1.longitude, u1.id")
                ->join('users_groups ug1', 'ug1.user_id = u1.id')
                ->join('partner_subscriptions ps', 'ps.partner_id = u1.id')
                ->where('ps.status', 'active')
                ->where('ps.price !=', 0)
                ->where('ug1.group_id', '3')
                ->get()
                ->getResultArray();
            $ids = [];
            foreach ($partners1 as $key => $row1) {
                $ids[] = $row1['id'];
            }
            // Check order limit for each partner and deactivate subscription if reached
            foreach ($ids as $key => $id) {
                $partner_subscription_data = $db->table('partner_subscriptions ps');
                $partner_subscription_data = $partner_subscription_data->select('ps.*')->where('ps.status', 'active')->where('partner_id', $id)
                    ->get()
                    ->getRow();
                $subscription_order_limit = $partner_subscription_data->max_order_limit;
                // Fetch the count of orders placed after the purchase_date
                $orders_count = $db->table('orders')
                    ->where('partner_id', $id)
                    ->where('created_at >', $partner_subscription_data->updated_at)
                    ->countAllResults();
                if ($partner_subscription_data->order_type == "limited") {
                    if ($orders_count >= $subscription_order_limit) {
                        $data['status'] = 'deactive';
                        $where['partner_id'] = $id;
                        $where['status'] = 'active';
                        update_details($data, $where, 'partner_subscriptions');
                        log_message('error', 'updated');
                    }
                }
            }
            $subscription_list = fetch_details('partner_subscriptions', ['status' => 'active',]);
            $currentTimestamp = date("H-i A");
            $current_date = date('Y-m-d');
            $current_time = date("H:i"); 
            $current_date = date('Y-m-d');

            foreach ($subscription_list as $key => $row) {
                if ($row['duration'] != 'unlimited') {
                    if ($row['expiry_date'] <= $current_date) {
                     if ($current_time === "23:59"){
                            $data['status'] = 'deactive';
                            $where['id'] = $row['id'];
                            $where['status'] = 'active';
                            $where['duration !='] = 'unlimited';
                            update_details($data, $where, 'partner_subscriptions');
                            log_message('error', 'Subscription expired and updated to deactive');
                        }
                    }
                }
            }
            $currentDate = date('Y-m-d');
            $currentTimestamp = time();
            $currentTime = date('H:i', $currentTimestamp);
            //booking auto cancellation
            $orders = fetch_details('orders', ['status' => 'awaiting', 'date_of_service' => $currentDate]);
            $setting = get_settings('general_settings', true);
            $booking_auto_cancle = (isset($setting['booking_auto_cancle_duration'])) ? intval($setting['booking_auto_cancle_duration']) : "30";
            foreach ($orders as $order) {
                $serviceTime = strtotime($order['starting_time']);
                $checkTime = $serviceTime - ($booking_auto_cancle * 60); // 1800 seconds = 30 minutes
                if ($checkTime <= strtotime($currentTime)) {
                    $data = process_refund($order['id'], 'cancelled', $order['user_id']);
                    update_details(['status' => 'cancelled'], ['id' => $order['id']], 'orders');
                }
            }



            $custom_jobs = fetch_details('custom_job_requests', ['status !=' => 'booked']);
            $currentTimestamp = date('Y-m-d H:i:s');


            foreach ($custom_jobs as $job) {
                $jobEndDateTime = $job['requested_end_date'] . ' ' . $job['requested_end_time'];
                // if ($currentTimestamp == "11-59 PM") {
                    if ($jobEndDateTime <= $currentTimestamp) {
                        $data['status'] = 'cancelled';
                        $where['id'] = $job['id'];
                        $where['status !='] = "cancelled";

                        update_details($data, $where, 'custom_job_requests');
                        // log_message('error', 'custom_job_requests expired and updated to cancelled');
                    }
                // }
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Dashboard.php - update_subscription_status()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function index()
    {



           
    
        try {
            $db = \Config\Database::connect();
           
            if ($this->isLoggedIn && $this->userIsAdmin) {
                $db = \Config\Database::connect();
                $total_users = $db->table('users u')->select('count(u.id) as `total`')->get()->getResultArray()[0]['total'];


                $total_customers = $db->table('users u')
                ->join('users_groups ug', 'ug.user_id = u.id')
                ->where('ug.group_id', 2)
                ->select('COUNT(u.id) as total')
                ->get()
                ->getRowArray()['total'];
            

                $total_on_sale_service = $db->table('services s')->select('count(s.id) as `total`')->where(['discounted_price >=' => 0])->get()->getResultArray()[0]['total'];
                $this->data['total_on_sale_service'] = $total_on_sale_service;
                $total_orders = $db->table('orders o')->select('count(o.id) as `total`')->where('o.parent_id  IS NULL')->get()->getResultArray()[0]['total'];
                $symbol =   get_currency();
                $this->data['total_orders'] = $total_orders;
                $this->data['total_users']  = $total_users;
                $this->data['total_customers']  = $total_customers;

                $this->data['currency'] = $symbol;
                setPageInfo($this->data, labels('Dashboard', 'Dashboard') . ' | ' . labels('admin_panel', 'Admin Panel'), 'dashboard');
                $Partners_model = new Partners_model();
                $limit = 5;
                $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
                $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'ASC';
                $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
                $where = [];
                $rating_data = $Partners_model->list(true, $search, $limit, $offset, 'number_of_orders', 'desc', $where, 'partner_id', [], '');
                $income_revenue = total_income_revenue();
                $this->data['income_revenue'] = $income_revenue;
                $admin_income_revenue = admin_income_revenue();
                $this->data['admin_income_revenue'] = $admin_income_revenue;
                $provider_income_revenue = provider_income_revenue();
                $this->data['provider_income_revenue'] = $provider_income_revenue;
                $this->data['rating_data'] = $rating_data;
                $rating_wise_rating_data = $Partners_model->list(true, $search, 3, $offset, ' pd.ratings', 'desc', $where, 'pd.partner_id', [], '');


                $this->data['rating_wise_rating_data'] = $rating_wise_rating_data;
                $top_trending_services = $this->top_trending_services();
                $this->data['top_trending_services'] = $top_trending_services;
                $this->data['categories'] = fetch_details('categories', [], ['id', 'name']);
                return view('backend/admin/template', $this->data);
            } else {
                return redirect('admin/login');
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Dashboard.php - index()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function top_trending_services()
    {
        try {
            $top_trending_services = fetch_top_trending_services((!empty($this->request->getPost('data_trending_filter'))) ? $this->request->getPost('data_trending_filter') : "null");
            if ($this->request->isAJAX()) {
                $response = array('error' => false, 'data' => $top_trending_services);
                print_r(json_encode($response));
            } else {
                return $top_trending_services;
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '-->app/Controllers/admin/Dashboard.php  - index()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function fetch_details()
    {
        try {
            if ($this->isLoggedIn && $this->userIsAdmin) {
                $sales[] = array();
                $db = \Config\Database::connect();
                $month_total_earning = $db->table('orders o')
                    ->select('sum(o.final_total) AS total_earning,DATE_FORMAT(created_at,"%b") AS month_name')
                    ->where(['status' => 4])
                    ->groupBy('year(CURDATE()),MONTH(created_at)')
                    ->orderBy('year(CURDATE()),MONTH(created_at)')
                    ->get()->getResultArray();
                $month_wise_earning['total_earning'] = array_map('intval', array_column($month_total_earning, 'total_earning'));
                $month_wise_earning['month_name'] = array_column($month_total_earning, 'month_name');
                $sales = $month_total_earning;
                print_r(json_encode($sales));
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Dashboard.php - fetch_details()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function list()
    {
        try {
            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
            print_r(json_encode($this->partner->list(false, $search, $limit, $offset, $sort, $order)));
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Dashboard.php - list()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function save_web_token()
    {
        try {
            $user = fetch_details('users', ['id' => $this->userId], ['id', 'panel_fcm_id']);
            $token = $this->request->getPost('token');
            update_details(['panel_fcm_id' => $token,], ['id' => $user[0]['id']], 'users');
            print_r(json_encode("admin panel token saved"));
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Dashboard.php - save_web_token()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function test()
    {
        return view('main_system_settings');
    }
    public function forgot_password()
    {
        setPageInfo($this->data, labels('commission_settlement', 'Commission Settlement') . ' | ' . labels('admin_panel', 'Admin Panel'), 'manage_commission');
        return view('backend/forgot_password_otp');
    }
    public function recent_orders()
    {
        try {
            $orders_model = new Orders_model();
            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 7;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
            $where = [];
            print_r($orders_model->list(false, $search, $limit, $offset, $sort, $order, $where, '', '', '', '', '', ''));
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Dashboard.php - recent_orders()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function NotFoundController()
    {
        return view('404');
    }
    public function customer_queris()
    {
        try {
            helper('function');
            $uri = service('uri');
            $db      = \Config\Database::connect();
            $symbol =   get_currency();
            $this->data['currency'] = $symbol;
            if ($this->isLoggedIn && $this->userIsAdmin) {
                setPageInfo($this->data, labels('user_queries', 'User Queries') . ' | ' . labels('admin_panel', 'Admin Panel'), 'customer_query');
                return view('backend/admin/template', $this->data);
            } else {
                return redirect('admin/login');
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Dashboard.php - customer_queris()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function customer_queris_list()
    {
        try {
            helper('function');
            $uri = service('uri');
            $admin_contact_query = new Admin_contact_query();
            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'DESC';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
            $data = $admin_contact_query->list([], 'yes', false, $limit, $offset, $sort, $order, $search);
            return $data;
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Dashboard.php - customer_queris_list()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function upload_media()
    {
        try {
            $path = FCPATH . "/public/uploads/media/";
            if (!is_dir($path)) {
                mkdir($path, 0775, true);
            }
            $request = \Config\Services::request();
            $files = $request->getFiles();
            $other_image_info_error = "";
            foreach ($files['documents'] as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $newName = $file->getRandomName();
                    if (!$file->move($path, $newName)) {
                        $other_image_info_error .= 'Failed to move file: ' . $file->getErrorString() . "\n";
                    }
                } else {
                    $other_image_info_error .= 'Invalid file: ' . $file->getErrorString() . "\n";
                }
            }
            $response = [];
            if (!empty($other_image_info_error)) {
                $response['error'] = true;
                $response['file_name'] = '';
                $response['message'] = $other_image_info_error;
            } else {
                $response['error'] = false;
                $response['file_name'] = $files['documents'][0]->getName();
                $response['message'] = "Files Uploaded Successfully..!";
            }
            return $this->response->setJSON($response);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Dashboard.php - upload_media()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }

    
}
