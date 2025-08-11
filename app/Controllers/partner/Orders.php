<?php

namespace App\Controllers\partner;

use App\Models\Orders_model;

class Orders extends Partner
{
    protected $validation; // Declare the $validation property

    public $orders;
    public $data;
    public function __construct()
    {
        parent::__construct();
        $this->orders = new Orders_model();
        helper('ResponceServices');
    }
    public function index()
    {
        if ($this->isLoggedIn && !$this->userIsAdmin) {
            if (!exists(['partner_id' => $this->userId, 'is_approved' => 1], 'partner_details')) {
                return redirect('partner/profile');
            }
            setPageInfo($this->data, 'Bookings | Provider Panel', 'orders');
            if (!exists(['partner_id' => $this->userId, 'is_approved' => 1], 'partner_details')) {
                return redirect('partner/profile');
            }
            return view('backend/partner/template', $this->data);
        } else {
            return redirect('partner/login');
        }
    }
    public function list()
    {
        try {
            if (!exists(['partner_id' => $this->userId, 'is_approved' => 1], 'partner_details')) {
                return redirect('partner/profile');
            }
            $orders_model = new Orders_model();
            $where = ['o.partner_id' => $this->userId];
            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 20;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';

            // Pass filter values to the model
            $order_status_filter = (isset($_GET['order_status_filter']) && !empty($_GET['order_status_filter'])) ? $_GET['order_status_filter'] : '';
            $final_total_filter = (isset($_GET['final_total_filter']) && !empty($_GET['final_total_filter'])) ? $_GET['final_total_filter'] : '';

            return $orders_model->list(false, $search, $limit, $offset, $sort, $order, $where, '', '', '', '', '', '');
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/Orders.php - list()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function view_orders()
    {
        try {
            $uri = service('uri');
            if (!$this->isLoggedIn && !$this->userIsPartner) {
                return redirect('partner/login');
            } else {
                $this->orders = new Orders_model();
                setPageInfo($this->data, 'Bookings | Provider Panel', 'order_details');
                $order_id = $uri->getSegments()[3];
                $order_data =  fetch_details('orders', ['id' => $order_id]);
                if (empty($order_data)) {
                    return redirect('partner/orders');
                }
                $where['o.id'] = $order_id;
                $order_details = $this->orders->list(true, '', 10, 0, '', '', $where);
                if ((empty($order_details['data']))) {
                    return redirect('partner/orders');
                }
                $this->data['order_details'] = $order_details['data'][0];
                $subtotal = 0.00;
                $tax_amount = 0.00;
                foreach ($order_details['data'][0]['services'] as $service) {
                    $subtotal += floatval($service['sub_total']);
                    $tax_amount = ($service['tax_type'] == "excluded") ? $tax_amount + floatval($service['tax_amount'] * $service['quantity']) : $tax_amount;
                }
                $promocode_discount = 0.00;
                if (isset($order_details) && !empty($order_details['data']) && !empty($order_details['data'][0]['promo_discount'])) {
                    $promocode_discount = intval($order_details['data'][0]['total'] + $order_details['data'][0]['visiting_charges']) * intval($order_details['data'][0]['promo_discount']) / 100;
                }
                $data = get_settings('general_settings', true);
                $tax = get_settings('system_tax_settings', true);
                $this->data['currency'] = $data['currency'];
                $this->data['tax'] = $tax['tax'];
                $this->data['promocode_discount'] = $order_details['data'][0]['promo_discount'];
                $this->data['subtotal'] = $subtotal;
                $this->data['tax_amount'] = $tax_amount;
                $sub_orders = fetch_details('orders', ['parent_id' => $order_id]);
                $this->data['sub_order'] = $sub_orders;
                $partner_personal_data  = fetch_details('users', ['id' => $this->userId], ['email'])[0];
                $this->data['personal_data'] = $partner_personal_data;
                return view('backend/partner/template', $this->data);
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/Orders.php - view_orders()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function order_summary_table($order_id = "")
    {
        try {
            $uri = service('uri');
            $order_id = $uri->getSegments()[3];
            $orders_model = new Orders_model();
            $data = get_settings('general_settings', true);
            $currency = $data['currency'];
            $orders = $orders_model->invoice($order_id);
            $services = $orders['order']['services'];
            $total =  count($services);
            $subtotal = 0.00;
            $tax_amount = 0;
            if (!empty($orders)) {
                $i = 0;
                $rows = [];
                foreach ($services as $service) {
                    $subtotal += floatval($service['sub_total']);
                    $tax_amount = ($service['tax_type'] == "excluded") ? $tax_amount + floatval($service['tax_amount'] * $service['quantity']) : $tax_amount;
                    $operations = '<button class="btn btn-danger btn-sm cancel_service" data-id="' . $service['id'] . '"> <i class="fas fa-trash"></i> </button>';
                    if (empty($service['service_image'])) {
                        $profile = '
                        <a href="#" id="pop">
                            <img id="profile_picture" onerror="this.onerror=null;this.src=\'' . base_url('public/backend/assets/img/news/img01.jpg') . '\'" 
                                 src="' . base_url('public/backend/assets/profiles/2022-10-07-633eb8af367e5.png') . '" 
                                 height="50px" width="50px" class="rounded-circle mr-4" style="border-radius:5px !important">
                        </a>';
                    } else {
                        $profile =
                            '<a href="#" id="pop">
                                <img id="profile_picture"  onerror="this.onerror=null;this.src=\'' . base_url('public/backend/assets/img/news/img01.jpg') . '\'"  src="' . base_url($service['service_image']) . '" height="50px" width="50px"  class="rounded-circle mr-4" style="border-radius:5px !important">
                         </a>';
                    }
                    $profile =
                        '<li class="media p-2" >' . $profile . ' <div class="media-body"> <div class="media-title mt-3">' .     $service['service_title'] . '</div>
                       </div></li>';
                    $rows[$i] = [
                        'service_title' => $profile,
                        'price' => $currency . number_format($service['price']),
                        'discount' => ($service['discount_price'] == 0) ? "0" : $currency . (($service['price'] - $service['discount_price'])),
                        'net_amount' => ($service['discount_price'] != 0) ? $currency . number_format($service['discount_price']) : $currency . ($service['price']),
                        'tax' => ($service['tax_type'] == "excluded") ? $service['tax_percentage'] . '%' : '0%',
                        'tax_amount' => ($service['tax_type'] == "excluded") ? $service['tax_amount'] : 0,
                        'quantity' =>  ucwords($service['quantity']),
                        'duration' =>  ucwords($service['duration'] * $service['quantity']),
                        'subtotal' => $currency . $service['sub_total'],
                        'operations' => $operations,
                    ];
                    $i++;
                }
                $array['total'] = $total;
                $array['rows'] = $rows;
                echo json_encode($array);
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/Orders.php - order_summary_table()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function invoice_table($order_id = "")
    {
        try {
            $uri = service('uri');
            $order_id = $uri->getSegments()[3];
            $orders_model = new Orders_model();
            $data = get_settings('general_settings', true);
            $currency = $data['currency'];
            $orders = $orders_model->invoice($order_id);
            $services = $orders['order']['services'];
            $total =  count($services);
            $tax = get_settings('system_tax_settings', true);
            $subtotal = 0.00;
            $tax_amount = 0;
            if (!empty($orders)) {
                $i = 0;
                $rows = [];
                foreach ($services as $service) {
                    $subtotal += floatval($service['sub_total'] - $service['tax_amount']);
                    $tax_amount += floatval($service['tax_amount']);
                    $operations = '<button class="btn btn-danger btn-sm cancel_service" data-id="' . $service['id'] . '"> <i class="fas fa-trash"></i> </button>';
                    $rows[$i] = [
                        'service_title' => ucwords($service['service_title']),
                        'price' => $currency . number_format($service['price']),
                        'discount' => ($service['discount_price'] == 0) ? "0" : $currency . (($service['price'] - $service['discount_price'])),
                        'net_amount' => ($service['discount_price'] != 0) ? $currency . number_format($service['discount_price']) : $currency . ($service['price']),
                        'tax' => ($service['tax_type'] == "excluded") ? $service['tax_percentage'] . '%' : '0%',
                        'tax_amount' => ($service['tax_type'] == "excluded") ? $currency . ($service['tax_amount']) : '$0',
                        'quantity' => ucwords($service['quantity']),
                        'subtotal' => $currency . (number_format($service['sub_total']))
                    ];
                    $i++;
                }
                $row = [
                    'service_title' => "",
                    'quantity' => "<strong>Total</strong>",
                    'price' => "",
                    'discount' => "",
                    'net_amount' => '',
                    'tax' => "",
                    'tax_amount' => "",
                    'subtotal' => "<strong>" . $currency . $orders['order']['total'] . "</strong>",
                ];
                if ($orders['order']['visiting_charges'] != "0") {
                    $visiting_charges = [
                        'service_title' => "",
                        'quantity' => "<strong>Visiting Charges</strong>",
                        'price' => "",
                        'discount' => "",
                        'net_amount' => '',
                        'tax' => "",
                        'tax_amount' => "",
                        'subtotal' => "<strong>" . $currency . $orders['order']['visiting_charges'] . "</strong>",
                    ];
                }
                $promo_code_discount_amount = (($orders['order']['total'] + $orders['order']['visiting_charges']) * $orders['order']['promo_discount']) / 100;
                $promo_code_discount = [
                    'service_title' => "",
                    'quantity' => "<strong>Promo Code Discount</strong>",
                    'price' => "",
                    'discount' => "",
                    'net_amount' => '',
                    'tax' => "",
                    'tax_amount' => "",
                    'subtotal' => "<strong>" . $currency . $orders['order']['promo_discount'] . "</strong>",
                ];
                $payble_amount = $orders['order']['total']  - $orders['order']['promo_discount'];
                $final_total = [
                    'service_title' => "",
                    'quantity' => "<strong>Final Total</strong>",
                    'price' => "",
                    'discount' => "",
                    'net_amount' => '',
                    'tax' => "",
                    'tax_amount' => "",
                    'subtotal' => "<strong>" . $currency . $payble_amount . "</strong>",
                ];
                if (!empty($rows)) {
                    array_push($rows, $row);
                    if ($orders['order']['visiting_charges'] != "0") {
                        array_push($rows, $visiting_charges);
                    }
                    array_push($rows, $promo_code_discount);
                    array_push($rows, $final_total);
                }
                $array['total'] = $total;
                $array['rows'] = $rows;
                echo json_encode($array);
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/Orders.php - invoice_table()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function invoice()
    {
        try {
            if (!$this->isLoggedIn && !$this->userIsPartner) {
                return redirect('partner/login');
            } else {
                $uri = service('uri');
                $order_id = $uri->getSegments()[3];
                $order_data =  fetch_details('orders', ['id' => $order_id]);
                if (empty($order_data)) {
                    return redirect('partner/orders');
                }
                $this->orders = new Orders_model();
                setPageInfo($this->data, 'Boooking | Partner Panel', 'invoice');
                $order_details = $this->orders->invoice($order_id);
                $subtotal = 0.00;
                foreach ($order_details['order']['services'] as $service) {
                    $subtotal += floatval($service['sub_total']);
                }
                $promocode_discount = 0.00;
                if (isset($order_details) && !empty($order_details['order']['promo_discount'])) {
                    $promocode_discount = intval($order_details['order']['total'] + $order_details['order']['visiting_charges']) * intval($order_details['order']['promo_discount']) / 100;
                }
                $this->data['promocode_discount'] = $promocode_discount;
                $this->data['subtotal'] = $subtotal;
                $data = get_settings('general_settings', true);
                $this->data['currency'] = $data['currency'];
                $this->data['logo'] = $data['partner_logo'];
                $this->data['order'] = $order_details['order'];
                return view('backend/partner/template', $this->data);
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/Orders.php - invoice()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function update_order_status()
    {
        try {
            if ($this->isLoggedIn && $this->userIsPartner) {
                if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                    $response['error'] = true;
                    $response['message'] = DEMO_MODE_ERROR;
                    $response['csrfName'] = csrf_token();
                    $response['csrfHash'] = csrf_hash();
                    return $this->response->setJSON($response);
                }
                $order_id = $this->request->getPost('order_id');
                $status = $this->request->getPost('status');
                $date = $this->request->getPost('rescheduled_date');
                $selected_time = $this->request->getPost('reschedule');
                $otp = $this->request->getPost('otp');
                $uploadedFiles = $this->request->getFiles('filepond');
                $partner_id = fetch_details('orders', ['id' => $order_id], ['partner_id'])[0]['partner_id'];
                if ($status == "rescheduled" && $selected_time == "") {
                    $response = [
                        'error' => true,
                        'message' => ' Please select reschedule timing******!',
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                }
                $is_provider_available = check_availability($partner_id, $date, $selected_time);
                if ($status == "rescheduled" && $is_provider_available) {
                    $response = validate_status($order_id, $status, $date, $selected_time);

                    return json_encode($response);
                } else {
                    if ($status == "completed") {
                        $response = validate_status($order_id, $status, '', '', $otp, "");
                    } elseif ($status == "started") {
                        $response = validate_status($order_id, $status, '', '', '', isset($uploadedFiles) ? $uploadedFiles : "");
                    } elseif ($status == "booking_ended") {
                        $additionalCharge = $this->request->getPost('booking_ended_additional_charges') ?? '';
                        $response = validate_status($order_id, $status, '', '', '', isset($uploadedFiles) ? $uploadedFiles : "", $additionalCharge);
                    } else {
                        $response =  validate_status($order_id, $status);
                    }
                    return json_encode($response);
                }
            } else {
                return redirect('admin/login');
            }
        } catch (\Exception $e) {
            log_the_responce($e, date("Y-m-d H:i:s") . '--> app/Controllers/partner/Orders.php - update_order_status()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function get_slots()
    {
        if ($this->isLoggedIn) {
            $order_id = $this->request->getPost('id');
            $date = $this->request->getPost('date');
            $partner_id =  fetch_details('orders', ['id' => $order_id], ['partner_id'])[0];
            $slots =  get_available_slots($partner_id, $date);
            return $this->response->setJSON($slots);
        } else {
            return redirect('partner/login');
        }
    }
    public function newlist()
    {
        $orders_model = new Orders_model();
        if (!exists(['partner_id' => $this->userId, 'is_approved' => 1], 'partner_details')) {
            return redirect('partner/profile');
        }
        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 5;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
        $where['o.partner_id'] = $this->userId;
        return $orders_model->list(false, $search, $limit, $offset, $sort, $order, $where, '', '', '', '', true);
    }
}
