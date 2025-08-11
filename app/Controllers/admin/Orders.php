<?php
namespace App\Controllers\admin;
use App\Models\Orders_model;
use App\Models\Transaction_model;
use App\Models\Users_model;
class Orders extends Admin
{
    public $orders, $creator_id, $transaction, $user_model;
    protected $superadmin;
    protected $db; 

    public function __construct()
    {
        parent::__construct();
        $this->orders = new Orders_model();
        $this->creator_id = $this->userId;
        $this->transaction = new Transaction_model();
        $this->user_model = new Users_model();
        helper(['form', 'url', 'upload', 'ResponceServices']);
        $this->superadmin = $this->session->get('email');
        $this->db = \Config\Database::connect();
    }
    public function index()
    {
        if (!$this->isLoggedIn || !$this->userIsAdmin) {
            return redirect('admin/login');
        }
        setPageInfo($this->data, labels('booking', 'Booking') . ' | ' . labels('admin_panel', 'Admin Panel'), 'orders');
        $partner_data = $this->db->table('users u')
            ->select('u.id,u.username,pd.company_name,pd.number_of_members')
            ->join('partner_details pd', 'pd.partner_id = u.id')
            ->where('is_approved', '1')
            ->get()->getResultArray();
        $this->data['partner_name'] = $partner_data;
        return view('backend/admin/template', $this->data);
    }
    public function list()
    {
        $orders_model = new Orders_model();
        return $orders_model->list();
    }
    public function view_orders()
    {
        try {
            $uri = service('uri');
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            $order_id = $uri->getSegments()[3];
            $orders  = fetch_details('orders', ['id' => $order_id]);
            $sub_orders = fetch_details('orders', ['parent_id' => $order_id]);
            setPageInfo($this->data,  $order_id . ' ' . labels('ID - Booking Details', 'ID - Booking Details') . ' | ' . labels('admin_panel', 'Admin Panel'), 'order_details');
            if (isset($orders) && empty($orders)) {
                return redirect('admin/orders');
            } else {
                $orders = $orders[0];
            }
            $partner_id = $orders['partner_id'];
            $user_id = $orders['user_id'];
            $partner  = fetch_details('partner_details', ['partner_id' => $partner_id])[0];
            $partner_personal_data  = fetch_details('users', ['id' => $partner_id])[0];
            $customer  = fetch_details('users', ['id' => $user_id])[0];
            $payment  = fetch_details('transactions', ['order_id' => $order_id]);
            $order_services = fetch_details('order_services', ['order_id' => $order_id, 'status!='=>  'cancelled'])[0];
            $service_id = $order_services['service_id'];
            $service = fetch_details('services', ['id' => $service_id]);
            if (!empty($service)) {
                $service = $service[0];
            } else {
                $service = [];
            }
            $where = "o.id = $order_id";
            $order_details = $this->orders->list(true, '', 10, 0, '', '', $where);
            $where = "u.id = $user_id";
            $currency = get_settings('general_settings', true);
            $tax = get_settings('system_tax_settings', true);
            $this->data['currency'] = $currency['currency'];
            $this->data['tax'] = $tax['tax'];
            $this->data['order'] = $orders;
            $this->data['order_services'] = $order_services;
            $this->data['partner'] = $partner;
            $this->data['order_details'] = $order_details['data'][0];
            $this->data['personal_data'] = $partner_personal_data;
            $this->data['customer'] = $customer;
            $this->data['payment'] = $payment;
            $this->data['service'] = $service;
            $this->data['sub_order'] = $sub_orders;
            
            return view('backend/admin/template', $this->data);
        } catch (\Throwable $th) {

            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Orders.php - view_orders()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function view_user()
    {
        try {
            $uri = service('uri');
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            $order_id = $uri->getSegments()[3];
            $orders  = fetch_details('orders', ['id' => $order_id]);
            if (isset($orders) && empty($orders)) {
                return redirect('admin/orders');
            } else {
                $orders = $orders[0];
            }
            $user_id = $orders['user_id'];
            $where['u.id'] = $user_id;
            $users  = fetch_details('users', ['id' => $user_id]);
            $user_details = $this->user_model->get_user($user_id, '');
            return json_encode($user_details);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Orders.php - view_user()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function view_payment_details()
    {
        try {
            $uri = service('uri');
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            $order_id = $uri->getSegments()[3];
            $orders  = fetch_details('orders', ['id' => $order_id]);
            if (isset($orders) && empty($orders)) {
                return redirect('admin/orders');
            } else {
                $orders = $orders[0];
            }
            $order_id = $orders['id'];
            $db      = \Config\Database::connect();
            $where['t.order_id']  = $order_id;
            $payment_details  = $this->transaction->list_transactions(false, '', 10, 0, 't.id', 'desc', $where);
            return $payment_details;
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Orders.php - view_payment_details()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function customer_details($user_id = "")
    {
        try {
            $uri = service('uri');
            $order_id = $uri->getSegments()[3];
            $order_details = fetch_details('orders', ['id' => $order_id], ['user_id', 'city', 'address_id'])[0];
            $user_id = $order_details['user_id'];
            $address_id = $order_details['address_id'];
            $db      = \Config\Database::connect();
            $builder = $db->table('orders o');
            $count = $builder->select('COUNT(o.id) as total')
                ->join('users u', "u.id = $user_id")
                ->join('addresses a', "a.id =  $address_id")
                ->where('o.id', $order_id)->get()->getResultArray();
            $total = $count[0]['total'];
            $tempRow = array();
            $data =  $builder->select('u.username, u.email, u.phone, a.type, a.address,a.city')
                ->join('users u', "u.id = $user_id")
                ->join('addresses a', "a.id =  $address_id")
                ->where('o.id', $order_id)->get()->getResultArray();
            $rows = [];
            foreach ($data as $row) {
                $tempRow['name'] = $row['username'];
                $tempRow['email'] = ($row['email'] != '') ? $row['email'] : '-';
                $tempRow['phone'] = $row['phone'];
                $tempRow['city_name'] = $row['city'];
                $tempRow['type'] = $row['type'];
                $tempRow['address'] = $row['address'];
                $rows[] = $tempRow;
            }
            $bulkData['total'] = $total;
            $bulkData['rows'] = $rows;
            return json_encode($bulkData);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Orders.php - customer_details()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function payment_details($order_id = "")
    {
        try {
            $uri = service('uri');
            $order_id = $uri->getSegments()[3];
            $db      = \Config\Database::connect();
            $builder = $db->table('transactions t');
            $count = $builder->select('COUNT(t.id) as total')
                ->where('t.order_id', $order_id)->get()->getResultArray();
            $total = $count[0]['total'];
            $tempRow = array();
            $data =  $builder->select('t.*')
                ->where('t.order_id', $order_id)->get()->getResultArray();
            $rows = [];
            foreach ($data as $row) {
                $tempRow['transaction_type'] = $row['transaction_type'];
                $tempRow['payment_method'] = $row['type'];
                $tempRow['txn_id'] = $row['txn_id'];
                $tempRow['amount'] = $row['amount'];
                $tempRow['status'] = $row['status'];
                $tempRow['currency_code'] = $row['currency_code'];
                $tempRow['message'] = $row['message'];
                $tempRow['transaction_date'] = $row['transaction_date'];
                $rows[] = $tempRow;
            }
            $bulkData['total'] = $total;
            $bulkData['rows'] = $rows;
            return json_encode($bulkData);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Orders.php - payment_details()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function partner_details($order_id = "")
    {
        try {
            $uri = service('uri');
            $order_id = $uri->getSegments()[3];
            $partner_id = fetch_details('orders', ['id' => $order_id], ['partner_id'])[0];
            $db      = \Config\Database::connect();
            $builder = $db->table('partner_details pd');
            $count = $builder->select('COUNT(pd.id) as total')
                ->where('pd.partner_id', $partner_id)->get()->getResultArray();
            $total = $count[0]['total'];
            $tempRow = array();
            $data =  $builder->select('pd.*, u.username, u.email, u.phone, u.image')
                ->join('users u', 'u.id = pd.partner_id')
                ->where('pd.partner_id', $partner_id)->get()->getResultArray();
            $rows = [];
            foreach ($data as $row) {
                $profile = '<a  href="' . base_url($row['image'])  . '" data-lightbox="image-1"><img height="80px" class="rounded-circle" src="' . base_url($row['image']) . '" alt=""></a>';
                $tempRow['user_image'] = $profile;
                $tempRow['name'] = $row['username'];
                $tempRow['email'] = $row['email'];
                $tempRow['phone'] = $row['phone'];
                $tempRow['company_name'] = $row['company_name'];
                $tempRow['about'] = $row['about'];
                $rows[] = $tempRow;
            }
            $bulkData['total'] = $total;
            $bulkData['rows'] = $rows;
            return json_encode($bulkData);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Orders.php - partner_details()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function delete_orders()
    {
        try {
            $result = checkModificationInDemoMode($this->superadmin);
            if ($result !== true) {
                return $this->response->setJSON($result);
            }
            if (!$this->isLoggedIn) {
                return redirect('admin/login');
            }
            $order_id = $this->request->getPost('id');
            $db      = \Config\Database::connect();
            $builder = $db->table('orders')->where('id', $order_id)
                ->orWhere('parent_id', $order_id)
                ->delete();
            $builder = $db->table('order_services')->delete(['order_id' => $order_id]);
            if ($builder) {
                return successResponse("order deleted successfully", false, [], [], 200, csrf_token(), csrf_hash());
            } else {
                return ErrorResponse("order does not exist!", true, [], [], 200, csrf_token(), csrf_hash());
            }
            return $this->response->setJSON($response);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Orders.php - delete_orders()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function view_details()
    {
        if (!$this->isLoggedIn || !$this->userIsAdmin) {
            return redirect('admin/login');
        }
        setPageInfo($this->data, labels('view_order', 'View Booking') . ' | ' . labels('admin_panel', 'Admin Panel'), 'order_details');
        return view('backend/admin/template', $this->data);
    }
    public function invoice()
    {

        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            $db      = \Config\Database::connect();
            setPageInfo($this->data, 'View Invoice | '. labels('admin_panel', 'Admin Panel'), 'invoice');
            $uri = service('uri');
            $order_id = $uri->getSegments()[3];
            $orders  = fetch_details('orders', ['id' => $order_id]);
            if (isset($orders) && empty($orders)) {
                return redirect('admin/orders');
            }
            $order_details = $this->orders->invoice($order_id)['order'];
            $partner_id = $order_details['partner_id'];
            $partner_details = $db
                ->table('partner_details pd')
                ->select('pd.company_name,pd.address, u.*')
                ->join('users u', 'u.id = pd.partner_id')
                ->where('partner_id', $partner_id)->get()->getResultArray();
            $user_id = $order_details['user_id'];
            $user_details = $db
                ->table('users u')
                ->select('u.*')
                ->where('u.id', $user_id)
                ->get()->getResultArray();
            $data = get_settings('general_settings', true);
            $this->data['currency'] = $data['currency'];
            $this->data['order'] = $order_details;
            $this->data['partner_details'] = $partner_details[0];
            $this->data['user_details'] = $user_details[0];
            return view('backend/admin/template', $this->data);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Orders.php - invoice()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    
    public function invoice_table()
{
    try {
        if (!$this->isLoggedIn || !$this->userIsAdmin) {
            return redirect('admin/login');
        }
        $uri = service('uri');
        $order_id = $uri->getSegments()[3];
        $orders  = fetch_details('orders', ['id' => $order_id]);
        if (isset($orders) && empty($orders)) {
            return redirect('admin/orders');
        }
        $orders_model = new Orders_model();
        $data = get_settings('general_settings', true);
        $currency = $data['currency'];
        $tax = get_settings('system_tax_settings', true);
        $orders = $orders_model->invoice($order_id)['order'];
        $services = $orders['services'];
        $total = count($services);
        if (!empty($orders)) {
            $i = 0;
            $total_tax_amount = 0;
            $rows = []; 
         
            foreach ($services as $service) {
                $original_price = $service['price'];
                $discount_price = $service['discount_price'];
                $tax_amount = $service['tax_amount'];
                $currency_symbol = $currency; // Assuming $currency contains the currency symbol
            
                // Calculate net amount (ensure no currency symbol in calculations)
                $net_amount_value = ($discount_price != 0) ? $discount_price : $original_price;
                $net_amount = ($service['tax_type'] == "excluded") ? $net_amount_value : ($net_amount_value - $tax_amount);
            
                $rows[$i] = [
                    'service_title' => ucwords($service['service_title']),
                    'price' => $currency_symbol . number_format($original_price, 2),
                    'discount' => ($discount_price == 0) ? $currency_symbol . "0.00" : $currency_symbol . number_format(($original_price - $discount_price), 2),
                    'net_amount' => $currency_symbol . number_format($net_amount, 2),
                    'tax' => $service['tax_percentage'] . '%',
                    'tax_amount' => $currency_symbol . number_format($tax_amount, 2),
                    'quantity' => ucwords($service['quantity']),
                    'subtotal' => $currency_symbol . number_format($service['sub_total'], 2)
                ];
                $i++;
            }
            
            // Empty row for formatting
            $empty_row = [
                'service_title' => "",
                'price' => "",
                'discount' => "",
                'net_amount' => "",
                'tax' => "",
                'tax_amount' => "",
                'quantity' => "",
                'subtotal' => "",
            ];
            // Total row
            $row = [
                'service_title' => "",
                'price' => "",
                'discount' => "",
                'net_amount' => "",
                'tax' => "",
                'tax_amount' => "",
                'quantity' => "<strong class='text-dark'>". labels('total', 'Total') ."</strong>",
                'subtotal' => "<strong class='text-dark '>" . $currency . strval(str_replace(',', '', number_format(strval((($orders['total']))), 2))) . "</strong>",
            ];
         
            // Visiting charges row
            if ($orders['visiting_charges'] != "0") {
                $visiting_charges = [
                    'service_title' => "",
                    'price' => "",
                    'discount' => "",
                    'net_amount' => "",
                    'tax' => "",
                    'tax_amount' => "",
                    'quantity' => "<strong class='text-dark '>".labels('visiting_charges', 'Visiting Charges')."</strong>",
                    'subtotal' => "<strong class='text-dark '>" . $currency . $orders['visiting_charges'] . "</strong>",
                ];
            }
            // Promo code discount row
            $promo_code_discount = [
                'service_title' => "",
                'price' => "",
                'discount' => "",
                'net_amount' => "",
                'tax' => "",
                'tax_amount' => "",
                'quantity' => "<strong class='text-dark '>".labels('promocode_discount', 'Promo Code Discount')."</strong>",
                'subtotal' => "<strong class='text-dark '>" . $currency . $orders['promo_discount'] . "</strong>",
            ];
            // Final total row
            $payble_amount = $orders['final_total'] - $orders['promo_discount'];
            $final_total = [
                'service_title' => "",
                'price' => "",
                'discount' => "",
                'net_amount' => "",
                'tax' => "",
                'tax_amount' => "",
                'quantity' => "<strong class='text-dark '>". labels('final_total', 'Final Total') ."</strong>",
                'subtotal' => "<strong class='text-dark '>" . $currency . $payble_amount . "</strong>",
            ];
            // Additional charges
            if (!empty($orders['additional_charges'])) {
                $additional_charges = json_decode($orders['additional_charges'], true);
                foreach ($additional_charges as $charge) {
                    // Append each additional charge to the rows array
                    $rows[] = [
                        'service_title' => "",
                        'price' => "",
                        'discount' => "",
                        'net_amount' => "",
                        'tax' => "",
                        'tax_amount' => "",
                        'quantity' => "<strong class='text-dark'>" . $charge['name'] . "</strong>",
                        'subtotal' => "<strong class='text-dark'>" . $currency . $charge['charge'] . "</strong>",
                    ];
                }
            }
            // Pushing final rows
            array_push($rows, $empty_row);
            array_push($rows, $row);
            if ($orders['visiting_charges'] != "0") {
                array_push($rows, $visiting_charges);
            }
            array_push($rows, $promo_code_discount);
            array_push($rows, $final_total);
            $array['total'] = $total;
            $array['rows'] = $rows;
            return json_encode($array);
        }
    } catch (\Throwable $th) {
        log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Orders.php - invoice_table()');
        return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
    }
}
    public function change_order_status()
    {
        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            $result = checkModificationInDemoMode($this->superadmin);
            if ($result !== true) {
                return $this->response->setJSON($result);
            }
            $uri = service('uri');
            $order_id = $this->request->getPost('order_id');
            $status = $this->request->getPost('status');
            $otp = $this->request->getPost('otp');
            $uploadedFiles = $this->request->getFiles('filepond');
            $date = $this->request->getPost('rescheduled_date');
            $selected_time = $this->request->getPost('reschedule');
            $partner_id = fetch_details('orders', ['id' => $order_id], ['partner_id'])[0]['partner_id'];
            if ($status == "rescheduled" && $selected_time == "") {
                return ErrorResponse("Please select reschedule timing!", true, [], [], 200, csrf_token(), csrf_hash());
            }
            $orders = fetch_details('orders', ['id' => $order_id]);
            $service_total_duration = 0;
            $service_duration = 0;
            foreach ($orders as $row) {
                $service_duration = ($row['duration']);
                $service_total_duration = $service_total_duration + $service_duration;
            }
            $availability =  checkPartnerAvailability($partner_id, $date . ' ' . $selected_time, $service_total_duration, $date, $selected_time);
            if ($status == "rescheduled" && $availability['message'] = "Slot is available at this time") {
                $response = validate_status($order_id, $status, $date, $selected_time);
                return json_encode($response);
            } else {
                if ($status == "completed") {
                    $response = validate_status($order_id, $status, '', '', $otp);
                } elseif ($status == "started") {
                    $response = validate_status($order_id, $status, '', '', '', isset($uploadedFiles) ? $uploadedFiles : "");
                } elseif ($status == "booking_ended") {
                    $additionalCharge = $this->request->getPost('booking_ended_additional_charges') ?? '';
                    $response = validate_status($order_id, $status, '', '', '', isset($uploadedFiles) ? $uploadedFiles : "",$additionalCharge);
                } else {
                    $response =  validate_status($order_id, $status);
                }
                return json_encode($response);
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Orders.php - change_order_status()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function upload_file()
    {
        try {
            $order_id = $this->request->getPost('order_id');
            $status = $this->request->getPost('status');
            $imagefile = $this->request->getFiles();
            $is_completed = $this->request->getPost('is_completed');
            $db      = \Config\Database::connect();
            $builder = $db->table('orders');
            $builder->select('status,payment_method,user_id,otp')->where('id', $order_id);
            $active_status1 = $builder->get()->getResultArray();
            $active_status = (isset($active_status1[0]['status'])) ? $active_status1[0]['status'] : "";
            if ($active_status == $status) {
                return ErrorResponse("You cant update the same status again", true, [], [], 200, csrf_token(), csrf_hash());
            }
            if ($active_status == 'cancelled' || $active_status == 'completed') {
                return ErrorResponse("You cant update status once item cancelled or completed", true, [], [], 200, csrf_token(), csrf_hash());
            }
            $work_started_images = [];
            $imagefile = $_FILES('documents');
            foreach ($_FILES['documents'] as $key => $img) {
                if ($img->isValid() && !$img->hasMoved()) {
                    $newName = $img->getName();
                    echo $newName;
                    $fileNameParts = explode('.', $newName);
                    $ext = end($fileNameParts);
                    $newName = 'data_' . uniqid() . "." . $ext;
                    $work_started_images[$key]  = "/public/backend/assets/provider_work_evidence/" . $newName;
                    $img->move('./public/backend/assets/provider_work_evidence/', $newName);
                } else {
                    echo labels('file_is_not_valid', 'file is not valid');
                }
            }
            if (isset($is_completed)) {
                $update = update_details(
                    [
                        'work_completed_proof' => json_encode((object)$work_started_images),
                    ],
                    ['id' => $order_id],
                    'orders',
                    false
                );
            } else {
                $update = update_details(
                    [
                        'work_started_proof' => json_encode((object)$work_started_images),
                    ],
                    ['id' => $order_id],
                    'orders',
                    false
                );
            }
            if ($update) {
                return successResponse("Success", false, [], [], 200, csrf_token(), csrf_hash());
            } else {
                return ErrorResponse("Failed", true, [], [], 200, csrf_token(), csrf_hash());
            }
        } catch (\Exception $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Orders.php - upload_file()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function view_ordered_services()
    {
        if (!$this->isLoggedIn || !$this->userIsAdmin) {
            return redirect('admin/login');
        }
        setPageInfo($this->data, labels('booking', 'Booking') . ' | ' . labels('admin_panel', 'Admin Panel'), 'view_ordered_services');
        return view('backend/admin/template', $this->data);
    }
    public function view_ordered_services_list()
    {
        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'desc';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
            $this->orders = new Orders_model();
            $data = $this->orders->ordered_services_list(false, $search, $limit, $offset, $sort, $order);
            return $data;
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Orders.php - view_ordered_services_list()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function cancel_order_service()
    {
        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            $result = checkModificationInDemoMode($this->superadmin);
            if ($result !== true) {
                return $this->response->setJSON($result);
            }
            $id = $this->request->getPost('id');
            $service_id = $this->request->getPost('service_id');
            $service_details =  fetch_details('services', ['id' => $service_id]);
            $order_service_data = fetch_details('order_services', ['id' => $id]);
            $order_id = $order_service_data[0]['order_id'];
            $order_data = fetch_details('orders', ['id' => $order_id]);
            if (!empty($service_details)) {
                $date_of_service  = $order_service_data[0]['created_at'];
                $starting_time = (!empty($order_data)) ? $order_data[0]['starting_time'] : '';
                $cancelable_before = $service_details[0]['cancelable_till'];
                $change = update_details(['status' => "cancelled"], ['id' => $id], 'order_services');
                if ($change) {
                    $sub_total = $order_service_data[0]['sub_total'];
                    $amount = floatval($order_data[0]['total']) -  floatval($order_service_data[0]['sub_total']);
                    $final_total = $order_data[0]['final_total'] - floatval($order_service_data[0]['sub_total']);
                    $promo_code_discount = ($order_data[0]['promo_discount'] != '' || $order_data[0]['promo_discount'] > 0) ? $order_data[0]['promo_discount'] : '';
                    $customer_id = $order_data[0]['user_id'];
                    if ($promo_code_discount != '' && $promo_code_discount > 0) {
                        $discountable_amount =  ($final_total * $promo_code_discount) / 100;
                        $final_total = $final_total - $discountable_amount;
                    }
                    $change =  update_details(['total' => $amount, 'final_total' => $final_total], ['id' => $order_id], 'orders');
                    if ($change) {
                        $refund_process = process_service_refund($order_id, $service_id, 'cancelled', $customer_id,  $sub_total);
                        return $this->response->setJSON($refund_process);
                    } else {
                        return ErrorResponse("could not change order status", true, [], [], 200, csrf_token(), csrf_hash());
                    }
                }
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Orders.php - view_ordered_services_list()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function get_slots()
    {
        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            $order_id = $this->request->getPost('id');
            $date = $this->request->getPost('date');
            $partner_id =  fetch_details('orders', ['id' => $order_id], ['partner_id'])[0];
            $slots =  get_available_slots($partner_id, $date);
            return $this->response->setJSON($slots);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Orders.php - get_slots()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
}
