<?php

namespace App\Controllers\partner\api;

use App\Controllers\BaseController;
use App\Libraries\Flutterwave;
use App\Models\Orders_model;
use App\Models\Partners_model;
use App\Models\Category_model;
use App\Models\Payment_request_model;
use App\Models\Promo_code_model;
use App\Models\Service_model;
use App\Models\Tax_model;
use App\Libraries\Razorpay;
use App\Libraries\Paypal;
use App\Libraries\Paystack;
use App\Models\Transaction_model;
use App\Models\Service_ratings_model;
use App\Models\Notification_model;
use App\Models\Settlement_CashCollection_history_model;
use App\Models\Subscription_model;
use Config\ApiResponseAndNotificationStrings;
use DateTime;
use Exception;

class V1 extends BaseController
{
    protected $excluded_routes =
    [
        "partner/api/v1/index",
        "partner/api/v1",
        "partner/api/v1/manage_user",
        "partner/api/v1/register",
        "partner/api/v1/forgot_password",
        "partner/api/v1/login",
        "partner/api/v1/verify_user",
        "partner/api/v1/get_settings",
        "partner/api/v1/change-password",
        "partner/api/v1/forgot-password",
        "partner/api/v1/paypal_transaction_webview",
        "partner/api/v1/contact_us_api",
        "partner/api/v1/verify_otp",
        "partner/api/v1/resend_otp",
        "partner/api/v1/paystack_transaction_webview",
        "partner/api/v1/app_paystack_payment_status",
        "partner/api/v1/flutterwave_webview",
        "partner/api/v1/flutterwave_payment_status",
        "partner/api/v1/get_places_for_app",
        "partner/api/v1/get_place_details_for_app"
    ];
    protected $validationListTemplate = 'list';
    private  $user_details = [];
    private  $allowed_settings = ["general_settings", "terms_conditions", "privacy_policy", "about_us", "app_settings"];
    private  $user_data = ['id', 'first_name', 'last_name', 'phone', 'email', 'fcm_id', 'web_fcm_id', 'image'];
    protected Razorpay $razorpay;
    protected $configIonAuth, $trans, $data, $validation;
    function __construct()
    {
        helper('api');
        helper("function");
        helper('ResponceServices');
        $this->request = \Config\Services::request();
        $current_uri =  uri_string();
        if (!in_array($current_uri, $this->excluded_routes)) {
            $token = verify_app_request();
            if ($token['error']) {
                header('Content-Type: application/json');
                http_response_code($token['status']);
                print_r(json_encode($token));
                die();
            }
            $this->user_details = $token['data'];
        } else {
            $token = verify_app_request();
            if (!$token['error'] && isset($token['data']) && !empty($token['data'])) {
                $this->user_details = $token['data'];
            }
        }
        $this->razorpay = new Razorpay();
        $this->configIonAuth = config('IonAuth');
        helper('session');
        session()->remove('identity');
        $this->trans = new ApiResponseAndNotificationStrings();
    }
    public function index()
    {
        $response = \Config\Services::response();
        helper("filesystem");
        $response->setHeader('content-type', 'Text');
        return $response->setBody(file_get_contents(base_url('api-doc.txt')));
    }
    public function login()
    {
        try {
            $ionAuth = new \IonAuth\Libraries\IonAuth();
            $config = new \Config\IonAuth();
            $validation =  \Config\Services::validation();
            $request = \Config\Services::request();
            $identity_column = $config->identity;
            if ($identity_column == 'phone') {
                $identity = $request->getPost('mobile');
                $validation->setRule('mobile', 'Mobile', 'numeric|required');
            } elseif ($identity_column == 'email') {
                $identity = $request->getPost('email');
                $validation->setRule('email', 'Email', 'required|valid_email');
            } else {
                $validation->setRule('identity', 'Identity', 'required');
            }
            $validation->setRule('password', 'Password', 'required');
            $password = $request->getPost('password');
            if ($request->getPost('fcm_id')) {
                $validation->setRule('fcm_id', 'FCM ID', 'trim');
            }
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
            $login = $ionAuth->login($identity, $password, false, $request->getPost('country_code'));
            $db      = \Config\Database::connect();
            $builder = $db->table('users u');
            $builder->select('u.*,ug.group_id')
                ->join('users_groups ug', 'ug.user_id = u.id')
                ->where('ug.group_id', 3)
                ->where(['phone' => $identity]);
            $userCheck = $builder->get()->getResultArray();
            if (empty($userCheck)) {
                $response = [
                    'error' => true,
                    'message' => 'Oops, it seems like this number isn’t registered. Please register to use our services.',
                ];
                return $this->response->setJSON($response);
            }
            if (!empty($userCheck)) {
                if ((($userCheck[0]['country_code'] == null) || ($userCheck[0]['country_code'] == $request->getPost('country_code'))) && (($userCheck[0]['phone'] == $identity))) {
                    if ($login) {
                        if (($userCheck[0]['country_code'] == null)) {
                            update_details(['country_code' => $request->getPost('country_code')], ['phone' => $identity], 'users');
                        }
                        if (($request->getPost('fcm_id')) && !empty($request->getPost('fcm_id'))) {
                            update_details(['fcm_id' => $request->getPost('fcm_id')], ['phone' => $identity, 'id' => $userCheck[0]['id']], 'users');
                        }
                        if (($request->getPost('platform')) && !empty($request->getPost('platform'))) {
                            update_details(['platform' => $request->getPost('platform')], ['phone' => $identity], 'users');
                        }
                        $data = array();
                        array_push($this->user_data, "api_key");
                        $data = fetch_details('users', ['id' => $userCheck[0]['id']], ['id', 'username', 'country_code', 'phone', 'email', 'fcm_id', 'image', 'api_key'])[0];
                        $token = generate_tokens($identity, 3);
                        $token_data['user_id'] = $data['id'];
                        $token_data['token'] = $token;
                        if (isset($token_data) && !empty($token_data)) {
                            insert_details($token_data, 'users_tokens');
                        }
                        $getdData = fetch_partner_formatted_data($data['id']);
                        $response = [
                            'error' => false,
                            "token" => $token,
                            'message' => 'User Logged successfully',
                            'data' =>  $getdData
                        ];
                        return $this->response->setJSON($response);
                    } else {
                        if (!exists([$identity_column => $identity], 'users')) {
                            $response = [
                                'error' => true,
                                'message' => 'User does not exists !',
                            ];
                            return $this->response->setJSON($response);
                        } else {
                            $response = [
                                'error' => true,
                                'message' => 'Incorrect login credentials. Please check and try again.',
                            ];
                            return $this->response->setJSON($response);
                        }
                    }
                } else {
                    $response = [
                        'error' => true,
                        'message' => 'User does not exists !',
                    ];
                    return $this->response->setJSON($response);
                }
            } else {
                if (!exists([$identity_column => $identity], 'users')) {
                    $response = [
                        'error' => true,
                        'message' => 'User does not exists !',
                    ];
                    return $this->response->setJSON($response);
                }
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - login()');
            return $this->response->setJSON($response);
        }
    }
    public function get_statistics()
    {
        try {
            $db = \Config\Database::connect();
            $last_monthly_sales = (isset($_POST['last_monthly_sales']) && !empty(trim($_POST['last_monthly_sales']))) ? $this->request->getPost("last_monthly_sales") : 6;
            $partner_id = $this->user_details['id'];
            $categories = $db->table('categories c')->select('c.name as name,count(s.id) as total_services')
                ->where(['s.user_id' => $partner_id])
                ->join('services s', 's.category_id=c.id', 'left')
                ->groupBy('s.category_id')
                ->get()->getResultArray();
            if (!empty($categories)) {
                if ($categories[0]['name'] == '' && $categories[0]['total_services'] == 0) {
                    $this->data['caregories'] = [];
                } else {
                    $this->data['caregories'] = $categories;
                }
            } else {
                $categories = [];
            }
            $monthly_sales = $db->table('orders')
                ->select('MONTHNAME(date_of_service) as month, SUM(final_total) as total_amount')
                ->where('date_of_service BETWEEN CURDATE() - INTERVAL ' . $last_monthly_sales . ' MONTH AND CURDATE()')
                ->where(['partner_id' => $partner_id, 'date_of_service < ' => date("Y-m-d H:i:s"), "status" => "completed"])
                ->groupBy("MONTH(date_of_service)")
                ->get()->getResultArray();
            $month_wise_sales['monthly_sales'] = $monthly_sales;
            $this->data['monthly_earnings'] = $month_wise_sales;
            $total_orders = $db->table('orders o')->select('count(o.id) as `total`')->join('order_services os', 'os.order_id=o.id')
                ->join('users u', 'u.id=o.user_id')
                ->join('users up', 'up.id=o.partner_id')
                ->join('partner_details pd', 'o.partner_id = pd.partner_id')->where(['o.partner_id' => $partner_id])->get()->getResultArray()[0]['total'];
            $total_services = $db->table('services s')->select('count(s.id) as `total`')->where(['user_id' => $partner_id])->get()->getResultArray()[0]['total'];
            $amount = fetch_details('orders', ['partner_id' => $partner_id, 'is_commission_settled' => '0'], ['sum(final_total) as total']);
            $db = \config\Database::connect();
            $builder = $db
                ->table('orders')
                ->select('sum(final_total) as total')
                ->select('SUM(final_total) AS total_sale,DATE_FORMAT(created_at,"%b") AS month_name')
                ->where('partner_id', $partner_id)
                ->where('status', 'completed');
            $data = $builder->groupBy('created_at')->get()->getResultArray();
            $tempRow = array();
            $row1 = array();
            foreach ($data as $key => $row) {
                $tempRow = $row['total'];
                $row1[] = $tempRow;
            }
            $total_balance = unsettled_commision($partner_id);
            $total_ratings = $db->table('partner_details p')->select('count(p.ratings) as `total`')->where(['id' => $partner_id])->get()->getResultArray()[0]['total'];
            $number_or_ratings = $db->table('partner_details p')->select('count(p.number_of_ratings) as `total`')->where(['id' => $partner_id])->get()->getResultArray()[0]['total'];
            $income = $db->table('orders o')->select('count(o.id) as `total`')->where(['partner_id' => $partner_id])->where("created_at >= DATE(now()) - INTERVAL 7 DAY")->get()->getResultArray()[0]['total'];
            $total_cancel = $db->table('orders o')->select('count(o.id) as `total`')->where(['partner_id' => $partner_id])->where(["status" => "cancelled"])->get()->getResultArray()[0]['total'];
            $symbol =   get_currency();
            $this->data['total_services'] = ($total_services != 0) ? $total_services : "0";
            $this->data['total_orders'] = ($total_orders != 0) ? $total_orders : "0";
            $this->data['total_cancelled_orders'] = ($total_cancel != 0) ? $total_cancel : "0";
            $this->data['total_balance'] = ($total_balance != 0) ? strval($total_balance) : "0";
            $this->data['total_ratings'] = ($total_ratings != 0) ? $total_ratings : "0";
            $this->data['number_of_ratings'] = ($number_or_ratings != 0) ? $number_or_ratings : "0";
            $this->data['currency'] = $symbol;
            $this->data['income'] = ($income != 0) ? $income : "0";
            $db = \Config\Database::connect();
            $custom_job_categories = fetch_details('partner_details', ['partner_id' => $this->userId], ['custom_job_categories', 'is_accepting_custom_jobs']);
            $partner_categoried_preference = !empty($custom_job_categories) &&
                isset($custom_job_categories[0]['custom_job_categories']) &&
                !empty($custom_job_categories[0]['custom_job_categories']) ?
                json_decode($custom_job_categories[0]['custom_job_categories']) : [];
            $builder = $db->table('custom_job_requests cj')
                ->select('cj.*, u.username, u.image, c.id as category_id, c.name as category_name, c.image as category_image')
                ->join('users u', 'u.id = cj.user_id')
                ->join('categories c', 'c.id = cj.category_id')
                ->where('cj.status', 'pending')
                ->where("(SELECT COUNT(1) FROM partner_bids pb WHERE pb.custom_job_request_id = cj.id AND pb.partner_id = $partner_id) = 0");
            if (!empty($partner_categoried_preference)) {
                $builder->whereIn('cj.category_id', $partner_categoried_preference);
            }
            $builder->orderBy('cj.id', 'DESC');
            $custom_job_requests = $builder->get()->getResultArray();
            $filteredJobs = [];
            foreach ($custom_job_requests as $row) {
                $did_partner_bid = fetch_details('partner_bids', [
                    'custom_job_request_id' => $row['id'],
                    'partner_id' => $partner_id,
                ]);
                if (empty($did_partner_bid)) {
                    $check = fetch_details('custom_job_provider', [
                        'partner_id' => $partner_id,
                        'custom_job_request_id' => $row['id'],
                    ]);
                    if (!empty($check)) {
                        $filteredJobs[] = $row;
                    }
                }
            }
            if (!empty($filteredJobs)) {
                foreach ($filteredJobs as &$job) {
                    if (!empty($job['image'])) {
                        $job['image'] = base_url('public/backend/assets/profiles/' . $job['image']);
                    } else {
                        $job['image'] = base_url('public/backend/assets/profiles/default.png');
                    }
                }
            }
            $this->data['total_open_jobs'] = count($filteredJobs);
            $filteredJobs = array_slice($filteredJobs, 0, 2);

            $this->data['open_jobs'] = $filteredJobs;
            if (!empty($this->data)) {
                $response = [
                    'error' => false,
                    'message' => 'data fetched successfully.',
                    'data' => $this->data
                ];
                return $this->response->setJSON($response);
            } else {
                $response = [
                    'error' => true,
                    'message' => 'No data found',
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - get_statistics()');
            return $this->response->setJSON($response);
        }
    }
    public function verify_user()
    {
        // 101:- Mobile number already registered and Active
        // 102:- Mobile number is not registered
        // 103:- Mobile number is Deactive (edited) 
        try {
            $request = \Config\Services::request();
            $identity = $request->getPost('mobile');
            $country_code = $request->getPost('country_code');
            $db      = \Config\Database::connect();
            $builder = $db->table('partner_details pd');
            $builder->select(
                "pd.*,
            u.username as partner_name,u.balance,u.image,u.active,u.country_code, u.email, u.phone, u.city,u.longitude,u.latitude,u.payable_commision,
            ug.user_id,ug.group_id"
            )
                ->join('users u', 'pd.partner_id = u.id')
                ->join('users_groups ug', 'ug.user_id = u.id')
                ->where('ug.group_id', 3)
                ->where('u.phone', $identity)
                ->where('u.country_code', $country_code)
                ->groupBy('pd.partner_id');
            $user = $builder->orderBy('id', 'ASC')->limit(0, 0)->get()->getResultArray();
            if (!empty($user)) {
                $fetched_country_code = $user[0]['country_code'];
                $fetched_user_mobile = $user[0]['phone'];
                if (($fetched_user_mobile == $identity) && ($fetched_country_code == $country_code)) {
                    if (($user[0]['active'] == 1)) {
                        $response = [
                            'error' => true,
                            'message_code' => "101",
                        ];
                    } else {
                        $response = [
                            'error' => true,
                            'message_code' => "103",
                        ];
                    }
                } else if (($fetched_user_mobile == $identity)) {
                    $data = fetch_details('users', ["phone" => $identity], $this->user_data)[0];
                    $data['country_code'] = $update_data['country_code'] = $this->request->getPost('country_code');
                    update_details($update_data, ['phone' => $identity], "users", false);
                    if (($user[0]['active'] == 1)) {
                        $response = [
                            'error' => true,
                            'message_code' => "101",
                        ];
                    } else {
                        $response = [
                            'error' => true,
                            'message_code' => "103",
                        ];
                    }
                } else if (($fetched_user_mobile != $identity)) {
                    $response = [
                        'error' => false,
                        'message_code' => "102",
                    ];
                } else if (($fetched_user_mobile != $identity) && ($fetched_country_code != $country_code)) {
                    $response = [
                        'error' => false,
                        'message_code' => "102",
                    ];
                }
            } else {
                $response = [
                    'error' => false,
                    'message_code' => "102",
                ];
            }
            $authentication_mode = get_settings('general_settings', true);
            $response['authentication_mode'] = $authentication_mode['authentication_mode'];
            return $this->response->setJSON($response);
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - verify_user()');
            return $this->response->setJSON($response);
        }
    }
    // public function get_orders()
    // {
    //     try {
    //         $orders_model = new Orders_model();
    //         $partner_id = $this->user_details['id'];
    //         $limit = $this->request->getPost('limit') ?: 10;
    //         $offset = $this->request->getPost('offset') ?: 0;
    //         $sort = $this->request->getPost('sort') ?: 'id';
    //         $order = $this->request->getPost('order') ?: 'DESC';
    //         $search = $this->request->getPost('search') ?: '';
    //         $status = $this->request->getPost('status') ?: 0;
    //         $partner_id = $this->request->getPost('partner_id');
    //         $filter = array();
    //         $download_invoice = ($this->request->getPost('download_invoice') && !empty($this->request->getPost('download_invoice'))) ? $this->request->getPost('download_invoice') : 1;
    //         if (!empty($this->request->getPost('custom_request_orders'))) {
    //             $where['o.custom_job_request_id !='] = "";
    //             $where['o.partner_id'] = $this->user_details['id'];
    //             if ($this->request->getPost('status') && !empty($this->request->getPost('status'))) {
    //                 $where['o.status'] = $status;
    //             }
    //             $orders = new Orders_model();
    //             $orders = $orders->custom_booking_list(true, $search, $limit, $offset, $sort, $order, $where, $download_invoice, '', '', '', '', false);
    //             $total = $orders['total'];
    //             unset($orders['total']);
    //             if (!empty($orders) && $total != 0) {
    //                 $response = [
    //                     'error' => false,
    //                     'message' => 'Orders fetched successfully.',
    //                     'total' => $total,
    //                     'data' => $orders
    //                 ];
    //                 return $this->response->setJSON($response);
    //             } else {
    //                 $response = [
    //                     'error' => true,
    //                     'message' => 'No data found',
    //                     'data' => []
    //                 ];
    //                 return $this->response->setJSON($response);
    //             }
    //         }elseif(!empty($this->request->getPost('fetch_both_bookings'))){



    //         } else {
    //             $orders = $orders_model->list(true, $search, $limit, $offset, $sort, $order, ['o.partner_id' => $this->user_details['id'], 'o.status' => $status, 'o.custom_job_request_id' => NULL], '', '', '', '', '', true);
    //             $total = $orders['total'];
    //             unset($orders['total']);
    //             $filter['user_id'] = $partner_id;
    //             $filter['status'] = $status;
    //             if (!empty($orders) && $total != 0) {
    //                 $response = [
    //                     'error' => false,
    //                     'message' => 'Orders fetched successfully.',
    //                     'total' => $total,
    //                     'data' => $orders
    //                 ];
    //                 return $this->response->setJSON($response);
    //             } else {
    //                 $response = [
    //                     'error' => true,
    //                     'message' => 'No data found',
    //                     'data' => []
    //                 ];
    //                 return $this->response->setJSON($response);
    //             }
    //         }
    //     } catch (\Exception $th) {
    //         $response['error'] = true;
    //         $response['message'] = 'Something went wrong';
    //         log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - get_orders()');
    //         return $this->response->setJSON($response);
    //     }
    // }

    public function get_orders()
    {
        try {
            $orders_model = new Orders_model();
            $partner_id = $this->user_details['id'];
            $limit = $this->request->getPost('limit') ?: 10;
            $offset = $this->request->getPost('offset') ?: 0;
            $sort = $this->request->getPost('sort') ?: 'id';
            $order = $this->request->getPost('order') ?: 'DESC';
            $search = $this->request->getPost('search') ?: '';
            $status = $this->request->getPost('status') ?: 0;
            $partner_id = $this->request->getPost('partner_id');
            $download_invoice = ($this->request->getPost('download_invoice') && !empty($this->request->getPost('download_invoice'))) ? $this->request->getPost('download_invoice') : 1;

            // Fetch only Custom Job Request Orders
            if (!empty($this->request->getPost('custom_request_orders'))) {
                $where['o.custom_job_request_id !='] = "";
                $where['o.partner_id'] = $this->user_details['id'];
                if (!empty($this->request->getPost('status'))) {
                    $where['o.status'] = $status;
                }
                $orders = $orders_model->custom_booking_list(true, $search, $limit, $offset, $sort, $order, $where, $download_invoice);
            }
            // Fetch Both Custom Job Request Orders & Normal Bookings
            elseif (!empty($this->request->getPost('fetch_both_bookings'))) {
                // Fetch Custom Job Requests
                $custom_where = [
                    'o.custom_job_request_id !=' => '',
                    'o.partner_id' => $this->user_details['id']
                ];
                if (!empty($status)) {
                    $custom_where['o.status'] = $status;
                }
                $custom_orders = $orders_model->custom_booking_list(true, $search, $limit, $offset, $sort, $order, $custom_where, $download_invoice);

                // Fetch Normal Bookings
                $normal_where = [
                    'o.partner_id' => $this->user_details['id'],
                    'o.status' => $status,
                    'o.custom_job_request_id' => NULL
                ];
                $normal_orders = $orders_model->list(true, $search, $limit, $offset, $sort, $order, $normal_where, '', '', '', '', '', true);

                // Merge Results
                $orders['data'] = array_merge($custom_orders['data'] ?? [], $normal_orders['data'] ?? []);
                $total = ($custom_orders['total'] ?? 0) + ($normal_orders['total'] ?? 0);
            }
            // Fetch Only Normal Bookings
            else {
                $where = [
                    'o.partner_id' => $this->user_details['id'],
                    'o.status' => $status,
                    'o.custom_job_request_id' => NULL
                ];
                $orders = $orders_model->list(true, $search, $limit, $offset, $sort, $order, $where, '', '', '', '', '', true);
            }

            // Remove total key if present
            if (isset($orders['total'])) {
                $total = $orders['total'];
                unset($orders['total']);
            }

            // Response
            if (!empty($orders) && $total != 0) {
                return $this->response->setJSON([
                    'error' => false,
                    'message' => 'Orders fetched successfully.',
                    'total' => strval($total),
                    'data' => $orders
                ]);
            } else {
                return $this->response->setJSON([
                    'error' => true,
                    'message' => 'No data found',
                    'data' => []
                ]);
            }
        } catch (\Exception $th) {
            log_the_responce($this->request->header('Authorization') . ' Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - get_orders()');
            return $this->response->setJSON(['error' => true, 'message' => 'Something went wrong']);
        }
    }


    public function register()
    {


        try {
            $request = \Config\Services::request();
            if (!isset($_POST)) {
                $response = [
                    'error' => true,
                    'message' => "Please use Post request",
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
            $ionAuth    = new \IonAuth\Libraries\IonAuth();
            $validation =  \Config\Services::validation();
            $request = \Config\Services::request();
            $config = new \Config\IonAuth();
            $partners_model = new Partners_model();
            $db      = \Config\Database::connect();
            $builder = $db->table('users u');
            $builder->select('u.*,ug.group_id')
                ->join('users_groups ug', 'ug.user_id = u.id')
                ->where('ug.group_id', "3")
                ->where('u.phone', $request->getPost('mobile'));;
            $user_record = $builder->orderBy('id', 'DESC')->limit(0, 0)->get()->getResultArray();
            //update
            if (exists(['phone' => $request->getPost('mobile')], 'users') && !empty($user_record)) {
                $userdata = fetch_details('users', ['id' => $user_record[0]['id'], "phone" => $request->getPost('mobile')], ['id', 'username', 'email', 'balance', 'active', 'first_name', 'last_name', 'company', 'phone', 'country_code', 'fcm_id', 'image', 'city_id', 'city', 'latitude', 'longitude'])[0];
                $group_id = [
                    'group_id' => 3
                ];
                $user_id =  $user_record[0]['id'];
                $userdata = fetch_details('users', ['id' => $user_id], ['id', 'username',  'email', 'balance', 'active', 'first_name', 'last_name', 'company', 'phone', 'country_code', 'fcm_id', 'image', 'city_id', 'city', 'latitude', 'longitude'])[0];
                $partnerData = fetch_details('partner_details', ['partner_id' => $user_record[0]['id']])[0];

                $fields = [
                    'company_name',
                    'type',
                    'about_provider' => 'about',
                    'visiting_charges',
                    'advance_booking_days',
                    'number_of_members',
                    'tax_name',
                    'tax_number',
                    'account_number',
                    'account_name',
                    'bank_code',
                    'swift_code',
                    'bank_name',
                    'address',
                    'post_booking_chat' => 'chat',
                    'pre_booking_chat' => 'pre_chat',
                    'at_store',
                    'at_doorstep'
                ];

                foreach ($fields as $requestKey => $partnerKey) {
                    if (is_int($requestKey)) $requestKey = $partnerKey; // When the key is an integer, use it as a string

                    $partner[$partnerKey] = !empty($request->getPost($requestKey))
                        ? $request->getPost($requestKey)
                        : (in_array($partnerKey, ['chat', 'pre_chat', 'at_store', 'at_doorstep']) ? 0 : null);
                }

                $disk = fetch_current_file_manager();
                $IdProofs = fetch_details('partner_details', ['partner_id' => $user_id], ['national_id', 'address_id', 'passport', 'banner', 'other_images'])[0];
                $old_image = $userdata['image'];
                $old_banner = $IdProofs['banner'];
                $old_national_id = $IdProofs['national_id'];
                $old_address_id = $IdProofs['address_id'];
                $old_passport = $IdProofs['passport'];
                $old_other_images = $IdProofs['other_images'];



                $paths = [
                    'image' => ['file' => $this->request->getFile('image'), 'path' => 'public/backend/assets/profile/', 'error' => 'Failed to create profile folders', 'folder' => 'profile', 'old_file' => $old_image, 'disk' => $disk,],
                    'banner_image' => ['file' => $this->request->getFile('banner_image'), 'path' => 'public/backend/assets/banner/', 'error' => 'Failed to create banner folders', 'folder' => 'banner', 'old_file' => $old_banner, 'disk' => $disk,],
                    'national_id' => ['file' => $this->request->getFile('national_id'), 'path' => 'public/backend/assets/national_id/', 'error' => 'Failed to create national_id folders', 'folder' => 'national_id', 'old_file' => $old_national_id, 'disk' => $disk,],
                    'address_id' => ['file' => $this->request->getFile('address_id'), 'path' => 'public/backend/assets/address_id/', 'error' => 'Failed to create address_id folders', 'folder' => 'address_id', 'old_file' => $old_address_id, 'disk' => $disk,],
                    'passport' => ['file' => $this->request->getFile('passport'), 'path' => 'public/backend/assets/passport/', 'error' => 'Failed to create passport folders', 'folder' => 'passport', 'old_file' => $old_passport, 'disk' => $disk]
                ];
                // Process single file uploads
                $uploadedFiles = [];

                // print_R( $uploadedFiles);
                // die;

                foreach ($paths as $key => $config) {
                    if (!empty($_FILES[$key]) && isset($_FILES[$key])) {

                        $file = $config['file'];

                        if ($file && $file->isValid()) {
                           
                            if (!empty($config['old_file'])) {
                                delete_file_based_on_server($config['folder'], $config['old_file'], $config['disk']);
                            }
                            $result = upload_file($config['file'], $config['path'], $config['error'], $config['folder']);
                            if ($result['error'] == false) {
                                $uploadedFiles[$key] = [
                                    'url' => $result['file_name'],
                                    'disk' => $result['disk']
                                ];
                            } else {
                                return ErrorResponse($result['message'], true, [], [], 200, csrf_token(), csrf_hash());
                            }
                        } else {
                            
                            $uploadedFiles[$key] = [
                                'url' => $config['old_file'],
                                'disk' => $config['disk']
                            ];
                        }
                    } else {
                        
                        $uploadedFiles[$key] = [
                            'url' => $config['old_file'],
                            'disk' => $config['disk']
                        ];
                    }
                }

                // die;

                $multipleFiles = $this->request->getFiles('filepond');
                $uploadedOtherImages = [];
                $old_other_images_array = json_decode($IdProofs['other_images'], true);
                if (isset($multipleFiles['other_images'])) {
                    foreach ($multipleFiles['other_images'] as $file) {
                        if ($file->isValid()) {
                            // Handle Other Images Deletion
                            if (!empty($old_other_images_array)) {
                                foreach ($old_other_images_array as $old_image) {
                                    delete_file_based_on_server('partner', $old_image, $disk);
                                }
                            }
                            $result = upload_file($file, 'public/uploads/partner/', 'Failed to upload other images', 'partner');
                            if ($result['error'] == false) {
                                $uploadedOtherImages[] = $result['disk'] === "local_server"
                                    ? 'public/uploads/partner/' . $result['file_name']
                                    : $result['file_name'];
                            } else {
                                return ErrorResponse($result['message'], true, [], [], 200, csrf_token(), csrf_hash());
                            }
                        }
                    }
                }
                $other_images = !empty($uploadedOtherImages) ? json_encode($uploadedOtherImages) : $IdProofs['other_images'];
                $banner = $uploadedFiles['banner_image']['url'] ?? 'public/backend/assets/banner/' . $this->request->getFile('banner_image')->getName();
                $national_id = $uploadedFiles['national_id']['url'] ?? 'public/backend/assets/national_id/' . $this->request->getFile('national_id')->getName();
                $address_id = $uploadedFiles['address_id']['url'] ?? 'public/backend/assets/address_id/' . $this->request->getFile('address_id')->getName();
                $passport = $uploadedFiles['passport']['url'] ?? 'public/backend/assets/passport/' . $this->request->getFile('passport')->getName();


                if (isset($uploadedFiles['banner_image']['disk']) && $uploadedFiles['banner_image']['disk'] == 'local_server') {
                    // Use a proper regex pattern to match and remove repeated instances
                    $uploadedFiles['banner_image']['url'] = preg_replace('#(public/backend/assets/banner/)+#', '', $uploadedFiles['banner_image']['url']);
                    $banner = 'public/backend/assets/banner/' . $uploadedFiles['banner_image']['url'];
                }


                if (isset($uploadedFiles['national_id']['disk']) && $uploadedFiles['national_id']['disk'] == 'local_server') {
                    // Remove all path components from the URL
                    $uploadedFiles['national_id']['url'] = preg_replace('#(public/backend/assets/national_id/)+#', '', $uploadedFiles['national_id']['url']);
                    $national_id = 'public/backend/assets/national_id/' . $uploadedFiles['national_id']['url'];
                }

                if (isset($uploadedFiles['address_id']['disk']) && $uploadedFiles['address_id']['disk'] == 'local_server') {
                    // Remove all path components from the URL
                    $uploadedFiles['address_id']['url'] = preg_replace('#(public/backend/assets/address_id/)+#', '', $uploadedFiles['address_id']['url']);
                    $address_id = 'public/backend/assets/address_id/' . $uploadedFiles['address_id']['url'];
                }

                if (isset($uploadedFiles['passport']['disk']) && $uploadedFiles['passport']['disk'] == 'local_server') {
                    // Remove all path components from the URL
                    $uploadedFiles['passport']['url'] = preg_replace('#(public/backend/assets/passport/)+#', '', $uploadedFiles['passport']['url']);
                    $passport = 'public/backend/assets/passport/' . $uploadedFiles['passport']['url'];
                }

                $partner['other_images'] =  isset($other_images) ? $other_images : $old_other_images;
                $partner['long_description'] = (isset($_POST['long_description'])) ? $_POST['long_description'] : "";
                $partner['address_id'] = $address_id;
                $partner['national_id'] = $national_id;
                $partner['passport'] = $passport;
                $partner['banner'] = $banner;
                if (!empty($request->getPost('city'))) {
                    $user['city'] = $request->getPost('city');
                }
                if (!empty($request->getPost('latitude'))) {
                    if (!preg_match('/^-?(90|[1-8][0-9][.][0-9]{1,20}|[0-9][.][0-9]{1,20})$/', $this->request->getPost('latitude'))) {
                        $response['error'] = true;
                        $response['message'] = "Please enter valid latitude";
                        return $this->response->setJSON($response);
                    }
                    $user['latitude'] = $request->getPost('latitude');
                }
                if (!empty($request->getPost('longitude'))) {
                    if (!preg_match('/^-?(180(\.0{1,20})?|1[0-7][0-9](\.[0-9]{1,20})?|[1-9][0-9](\.[0-9]{1,20})?|[0-9](\.[0-9]{1,20})?)$/', $this->request->getPost('longitude'))) {
                        $response['error'] = true;
                        $response['message'] = "Please enter valid Longitude";
                        return $this->response->setJSON($response);
                    }
                    $user['longitude'] = $request->getPost('longitude');
                }
                if (!empty($request->getPost('username'))) {
                    $user['username'] = $request->getPost('username');
                }
                if (!empty($request->getPost('email'))) {
                    $user['email'] = $request->getPost('email');
                }
                $image = $uploadedFiles['image']['url'] ?? 'public/backend/assets/profile/' . $this->request->getFile('image')->getName();
                
                if (isset($uploadedFiles['image']['disk']) && $uploadedFiles['image']['disk'] == 'local_server') {
                    $uploadedFiles['image']['url'] = preg_replace('#(public/backend/assets/profile/)+#', '', $uploadedFiles['image']['url']);
                    $image = 'public/backend/assets/profile/' . $uploadedFiles['image']['url'];
                }
                
                $user['image'] = $image ?? $userdata['image'];
                if (!empty($request->getPost('days'))) {
                    $working_days = json_decode($request->getPost('days'), true);
                    $jsonString = $request->getPost('days');
                    $jsonString = html_entity_decode($jsonString);
                    $working_days = json_decode($jsonString, true);
                    $tempRowDaysIsOpen = array();
                    $rowsDays = array();
                    $tempRowDays = array();
                    $tempRowStartTime = array();
                    $tempRowEndTime = array();
                    foreach ($working_days as $row) {
                        $tempRowDaysIsOpen[] = $row['isOpen'];
                        $tempRowDays[] = $row['day'];
                        $tempRowStartTime[] = $row['start_time'];
                        $tempRowEndTime[] = $row['end_time'];
                    }
                    for ($i = 0; $i < count($tempRowStartTime); $i++) {
                        $partner_timing = [];
                        $partner_timing['day'] = $tempRowDays[$i];
                        if (isset($tempRowStartTime[$i])) {
                            $partner_timing['opening_time'] = $tempRowStartTime[$i];
                        }
                        if (isset($tempRowEndTime[$i])) {
                            $partner_timing['closing_time'] = $tempRowEndTime[$i];
                        }
                        $partner_timing['is_open'] = $tempRowDaysIsOpen[$i];
                        $partner_timing['partner_id'] = $userdata['id'];
                        update_details($partner_timing, ['partner_id' =>  $userdata['id'], 'day' => $tempRowDays[$i]], 'partner_timings');
                    }
                }
                $update_user = update_details($user, ['id' => $user_id], "users", false);
                $update_partner = update_details($partner, ['partner_id' => $user_id], 'partner_details', false);
                $partner_id = $user_id;
                $disk = fetch_current_file_manager();
                if ($update_user && $update_partner) {
                    $getData = fetch_partner_formatted_data($user_id);
                    $response = [
                        'error' => false,
                        'message' => 'User Updated successfully',
                        'data' => $getData,
                    ];
                    send_web_notification('Provider Updated',  $request->getPost('company_name') . ' Updated details', null, 'https://edemand-test.thewrteam.in/admin/partners');
                    $db      = \Config\Database::connect();
                    $builder = $db->table('users u');
                    $users = $builder->Select("u.id,u.fcm_id,u.username,u.email")
                        ->join('users_groups ug', 'ug.user_id=u.id')
                        ->where('ug.group_id', '1')
                        ->get()->getResultArray();
                    if (!empty($users[0]['email']) && check_notification_setting('provider_update_information', 'email') && is_unsubscribe_enabled($users[0]['id']) == 1) {
                        send_custom_email('provider_update_information', $partner_id, $users[0]['email']);
                    }
                    if (check_notification_setting('provider_update_information', 'sms')) {
                        send_custom_sms('provider_update_information', $partner_id, $users[0]['email']);
                    }
                    return $this->response->setJSON($response);
                } else {
                    $response = [
                        'error' => false,
                        'message' => 'Something went wrong',
                    ];
                }
                return $this->response->setJSON($response);
            }
            //new provider
            else {
                $validation->setRules(
                    [
                        'company_name' => 'required',
                        'country_code' => 'required',
                        'username' => 'required',
                        'email' => 'required|valid_email|',
                        'mobile' => 'required|numeric|',
                        'password' => 'required|matches[password_confirm]',
                        'password_confirm' => 'required',
                    ],
                );
                if (!$validation->withRequest($this->request)->run()) {
                    $errors = $validation->getErrors();
                    $response = [
                        'error' => true,
                        'message' => $errors,
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                }

                if (!empty($request->getPost('latitude'))) {
                    if (!preg_match('/^-?(90|[1-8][0-9][.][0-9]{1,20}|[0-9][.][0-9]{1,20})$/', $this->request->getPost('latitude'))) {
                        $response['error'] = true;
                        $response['message'] = "Please enter valid latitude";
                        return $this->response->setJSON($response);
                    }
                    $user['latitude'] = $request->getPost('latitude');
                }
                if (!empty($request->getPost('longitude'))) {
                    if (!preg_match('/^-?(180(\.0{1,20})?|1[0-7][0-9](\.[0-9]{1,20})?|[1-9][0-9](\.[0-9]{1,20})?|[0-9](\.[0-9]{1,20})?)$/', $this->request->getPost('longitude'))) {
                        $response['error'] = true;
                        $response['message'] = "Please enter valid Longitude";
                        return $this->response->setJSON($response);
                    }
                    $user['longitude'] = $request->getPost('longitude');
                }
                $paths = [
                    'profile' => ['file' => $this->request->getFile('image'), 'path' => 'public/backend/assets/profile/', 'error' => 'Failed to create profile folders', 'folder' => 'profile'],
                    'banner_image' => ['file' => $this->request->getFile('banner_image'), 'path' => 'public/backend/assets/banner/', 'error' => 'Failed to create banner folders', 'folder' => 'banner'],
                    'national_id' => ['file' => $this->request->getFile('national_id'), 'path' => 'public/backend/assets/national_id/', 'error' => 'Failed to create national_id folders', 'folder' => 'national_id'],
                    'address_id' => ['file' => $this->request->getFile('address_id'), 'path' => 'public/backend/assets/address_id/', 'error' => 'Failed to create address_id folders', 'folder' => 'address_id'],
                    'passport' => ['file' => $this->request->getFile('passport'), 'path' => 'public/backend/assets/passport/', 'error' => 'Failed to create passport folders', 'folder' => 'passport']
                ];
                // Process uploads
                $uploadedFiles = [];
                foreach ($paths as $key => $upload) {
                    // Check if file exists before uploading
                    if ($upload['file'] && $upload['file']->isValid()) {
                        $result = upload_file($upload['file'], $upload['path'], $upload['error'], $upload['folder']);
                        if ($result['error'] == false) {
                            $uploadedFiles[$key] = [
                                'url' => $result['file_name'],
                                'disk' => $result['disk']
                            ];
                        } else {
                            return ErrorResponse($result['message'], true, [], [], 200, csrf_token(), csrf_hash());
                        }
                    }
                }
                $multipleFiles = $this->request->getFiles('other_images'); // Retrieve all uploaded files
                $otherImagesConfig = [
                    'path' => 'public/uploads/partner/',
                    'error' => 'Failed to upload other images',
                    'folder' => 'partner'
                ];
                $uploadedOtherImages = []; // To store the uploaded image paths
                if (isset($multipleFiles['other_images'])) {
                    $files = $multipleFiles['other_images']; // Array of files for the key 'other_images'
                    foreach ($files as $file) {
                        if ($file->isValid()) {
                            $result = upload_file($file, $otherImagesConfig['path'], $otherImagesConfig['error'], $otherImagesConfig['folder']);
                            if ($result['error'] == false) {
                                if ($result['disk'] === "local_server") {
                                    $uploadedOtherImages[] = $otherImagesConfig['path'] . $result['file_name'];
                                } elseif ($result['disk'] === "aws_s3") {
                                    $uploadedOtherImages[] = $result['file_name'];
                                } else {
                                    $uploadedOtherImages[] = $result['file_name'];
                                }
                            } else {
                                return ErrorResponse($result['message'], true, [], [], 200, csrf_token(), csrf_hash());
                            }
                        }
                    }
                }
                // Prepare the final JSON response for other images
                $other_images = [
                    'other_images' => !empty($uploadedOtherImages) ? json_encode($uploadedOtherImages) : "",
                ];
                $image = $uploadedFiles['profile']['url'] ?? '';
                if (isset($uploadedFiles['profile']['disk']) && $uploadedFiles['profile']['disk'] == 'local_server') {
                    $image = 'public/backend/assets/profile/' . $uploadedFiles['profile']['url'];
                }
                $additional_data = [
                    'username' => $request->getPost('username'),
                    'active' => '1',
                    'phone' =>  $request->getPost('mobile'),
                    'latitude' => ($request->getPost('latitude')) ? $request->getPost('latitude') : "",
                    'longitude' => ($request->getPost('longitude')) ? $request->getPost('longitude') : "",
                    'city' => ($request->getPost('city_id')) ? $request->getPost('city_id') : "",
                    'image' => isset($image) ? $image : "",
                    'country_code' => $request->getPost('country_code'),
                ];
                if ($request->getPost('fcm_id')) {
                    $additional_data['fcm_id'] = ($request->getPost('fcm_id') && !empty($request->getPost('fcm_id'))) ? $request->getPost('fcm_id') : "";
                }
                $group_id = [
                    'group_id' => 3
                ];
                if ($this->request->getPost() && $validation->withRequest($this->request)->run() && $user_id = $ionAuth->register($request->getPost('mobile'), $request->getPost('password'), $request->getPost('email'), $additional_data, $group_id)) {
                    $data = array();
                    $token = generate_tokens($request->getPost('mobile'), 3);
                    $token_data['user_id'] = $user_id;
                    $token_data['token'] = $token;
                    if (isset($token_data) && !empty($token_data)) {
                        insert_details($token_data, 'users_tokens');
                    }
                    update_details(['api_key' => $token], ['username' =>  $request->getPost('username')], "users");
                    $data = fetch_details('users', ['id' => $user_id], $this->user_data)[0];
                    $data = remove_null_values($data);
                    $partner_id = $data['id'];
                    $banner = $uploadedFiles['banner_image']['url'] ?? '';
                    $national_id = $uploadedFiles['national_id']['url'] ?? '';
                    $address_id = $uploadedFiles['address_id']['url'] ?? '';
                    $passport = $uploadedFiles['passport']['url'] ?? '';
                    if (isset($uploadedFiles['banner_image']['disk']) && $uploadedFiles['banner_image']['disk'] == 'local_server') {
                        $banner = 'public/backend/assets/banner/' . $uploadedFiles['banner_image']['url'];
                    } else if (isset($uploadedFiles['banner']['disk']) && $uploadedFiles['banner']['disk'] == 'aws_s3') {
                        $banner = 'public/backend/assets/banner/' . $uploadedFiles['banner']['url'];
                    } else {
                        $banner = '';
                    }
                    if (isset($uploadedFiles['national_id']['disk']) && $uploadedFiles['national_id']['disk'] == 'local_server') {
                        $national_id = 'public/backend/assets/national_id/' . $uploadedFiles['national_id']['url'];
                    } else if (isset($uploadedFiles['national_id']['disk']) && $uploadedFiles['national_id']['disk'] == 'aws_s3') {
                        $national_id = 'public/backend/assets/national_id/' . $uploadedFiles['national_id']['url'];
                    } else {
                        $national_id = '';
                    }
                    if (isset($uploadedFiles['address_id']['disk']) && $uploadedFiles['address_id']['disk'] == 'local_server') {
                        $address_id = 'public/backend/assets/address_id/' . $uploadedFiles['address_id']['url'];
                    } else if (isset($uploadedFiles['address_id']['disk']) && $uploadedFiles['address_id']['disk'] == 'aws_s3') {
                        $address_id = 'public/backend/assets/address_id/' . $uploadedFiles['address_id']['url'];
                    } else {
                        $address_id = '';
                    }
                    if (isset($uploadedFiles['passport']['disk']) && $uploadedFiles['passport']['disk'] == 'local_server') {
                        $passport = 'public/backend/assets/passport/' . $uploadedFiles['passport']['url'];
                    } else if (isset($uploadedFiles['passport']['disk']) && $uploadedFiles['passport']['disk'] == 'aws_s3') {
                        $passport = 'public/backend/assets/passport/' . $uploadedFiles['passport']['url'];
                    } else {
                        $passport = '';
                    }
                    $partner = [
                        'partner_id' => $partner_id,
                        'company_name' =>  $request->getPost('company_name'),
                        'national_id' => isset($national_id) ? $national_id : "",
                        'address_id' => isset($address_id) ? $address_id : "",
                        'passport' => isset($passport) ? $passport : "",
                        'address' => ($request->getPost('address')) ? $request->getPost('address') : "",
                        'tax_name' => ($request->getPost('tax_name')) ? $request->getPost('tax_name') : "",
                        'tax_number' => ($request->getPost('tax_number')) ? $request->getPost('tax_number') : "",
                        'advance_booking_days' => ($request->getPost('advance_booking_days')) ? $request->getPost('advance_booking_days') : "",
                        'type' => ($request->getPost('type') && !empty($request->getPost('type'))) ? $request->getPost('type') : "",
                        'number_of_members' => ($request->getPost('number_of_members')) ? $request->getPost('number_of_members') : "",
                        'visiting_charges' => ($request->getPost('visiting_charges')) ? $request->getPost('visiting_charges') : "",
                        'account_number' => ($request->getPost('account_number')) ? $request->getPost('account_number') : "",
                        'account_name' => ($request->getPost('account_name')) ? $request->getPost('account_name') : "",
                        'bank_name' => ($request->getPost('bank_name')) ? $request->getPost('bank_name') : "",
                        'bank_code' => ($request->getPost('bank_code')) ? $request->getPost('bank_code') : "",
                        'swift_code' => ($request->getPost('swift_code')) ? $request->getPost('swift_code') : "",
                        'about' => ($request->getPost('about_provider')) ? $request->getPost('about_provider') : "",
                        'ratings' => 0,
                        'number_of_ratings' => 0,
                        'is_approved' => ((defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0)) ? 1 : 1,
                        'banner' => isset($banner) ? $banner : "",
                        'other_images' => isset($other_images) ? $other_images : "",
                        'long_description' => (isset($_POST['long_description'])) ? $_POST['long_description'] : "",
                        'chat' => (isset($_POST['post_booking_chat'])) ? $_POST['post_booking_chat'] : "0",
                        'pre_chat' => (isset($_POST['pre_booking_chat'])) ? $_POST['pre_booking_chat'] : "0",
                        'at_store' => (isset($_POST['at_store'])) ? $_POST['at_store'] : "0",
                        'at_doorstep' => (isset($_POST['at_doorstep'])) ? $_POST['at_doorstep'] : "0",
                    ];
                    $partners_model->insert($partner);
                    if ($request->getPost('days')) {
                        $working_days = json_decode($_POST['days'], true);

                        $tempRowDaysIsOpen = array();
                        $rowsDays = array();
                        $tempRowDays = array();
                        $tempRowStartTime = array();
                        $tempRowEndTime = array();
                        foreach ($working_days as $row) {
                            $tempRowDaysIsOpen[] = $row['isOpen'];
                            $tempRowDays[] = $row['day'];
                            $tempRowStartTime[] = $row['start_time'];
                            $tempRowEndTime[] = $row['end_time'];
                            $rowsDays[] = $tempRowDays;
                        }
                        for ($i = 0; $i < count($tempRowStartTime); $i++) {
                            $partner_timing = [];
                            $partner_timing['day'] = $tempRowDays[$i];
                            if (isset($tempRowStartTime[$i])) {
                                $partner_timing['opening_time'] = $tempRowStartTime[$i];
                            }
                            if (isset($tempRowEndTime[$i])) {
                                $partner_timing['closing_time'] = $tempRowEndTime[$i];
                            }
                            $partner_timing['is_open'] = $tempRowDaysIsOpen[$i];
                            $partner_timing['partner_id'] = $data['id'];
                            insert_details($partner_timing, 'partner_timings');
                        }
                    } else {
                        $tempRowDaysIsOpen = array(0, 0, 0, 0, 0, 0, 0);
                        $rowsDays = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
                        $tempRowDays = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
                        $tempRowStartTime = array('09:00:00', '09:00:00', '09:00:00', '09:00:00', '09:00:00', '09:00:00', '09:00:00');
                        $tempRowEndTime = array('10:00:00', '10:00:00', '10:00:00', '10:00:00', '10:00:00', '10:00:00', '10:00:00');
                        for ($i = 0; $i < count($tempRowStartTime); $i++) {
                            $partner_timing = [];
                            $partner_timing['day'] = $tempRowDays[$i];
                            if (isset($tempRowStartTime[$i])) {
                                $partner_timing['opening_time'] = $tempRowStartTime[$i];
                            }
                            if (isset($tempRowEndTime[$i])) {
                                $partner_timing['closing_time'] = $tempRowEndTime[$i];
                            }
                            $partner_timing['is_open'] = $tempRowDaysIsOpen[$i];
                            $partner_timing['partner_id'] = $data['id'];
                            insert_details($partner_timing, 'partner_timings');
                        }
                    }
                    $getData = fetch_partner_formatted_data($data['id']);
                    $response = [
                        'error' => false,
                        'token' => $token,
                        'message' => 'User Registered successfully',
                        'data' => $getData,
                    ];
                    send_web_notification('New Provider',  $request->getPost('company_name') . ' Registered');
                    $db      = \Config\Database::connect();
                    $builder = $db->table('users u');
                    $users = $builder->Select("u.id,u.fcm_id,u.username,u.email")
                        ->join('users_groups ug', 'ug.user_id=u.id')
                        ->where('ug.group_id', '1')
                        ->get()->getResultArray();
                    if (!empty($users[0]['email']) && check_notification_setting('new_provider_registerd', 'email') && is_unsubscribe_enabled($users[0]['id']) == 1) {
                        send_custom_email('new_provider_registerd', $partner_id, $users[0]['email']);
                    }
                    if (check_notification_setting('new_provider_registerd', 'sms')) {
                        send_custom_sms('new_provider_registerd', $partner_id, $users[0]['email']);
                    }
                    return $this->response->setJSON($response);
                } else {
                    $msg = trim(preg_replace('/\r+/', '', preg_replace('/\n+/', '', preg_replace('/\t+/', ' ', strip_tags($ionAuth->errors())))));
                    $response = [
                        'error' => true,
                        'message' => $msg,
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                }
            }
        } catch (\Exception $th) {

            log_the_responce(" Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - get_orders()');
            return $this->response->setJSON($response);
        }
    }
    public function get_settings()
    {
        try {
            $variable = (isset($_POST['variable']) && !empty($_POST['variable'])) ? $_POST['variable'] : 'all';
            $setting = array();
            $setting = fetch_details('settings', '', 'variable', '', '', '', 'ASC');
            if (isset($variable) && !empty($variable) && in_array(trim($variable), $this->allowed_settings)) {
                $setting_res[$variable] = get_settings($variable, true);
            } else {
                if (isset($this->user_details['id'])) {
                    $setting_res['balance'] = fetch_details("users", ["id" => $this->user_details['id']], ['balance', 'payable_commision']);
                    $setting_res['balance'] = (isset($setting_res['balance'][0]['balance'])) ? $setting_res['balance'][0]['balance'] : "0";
                    $setting_res['demo_mode'] = (ALLOW_MODIFICATION == 0) ? "1" : "0";
                    $setting_res['payable_commision'] = fetch_details("users", ["id" => $this->user_details['id']], ['balance', 'payable_commision']);
                    $setting_res['payable_commision'] = (isset($setting_res['payable_commision'][0]['payable_commision'])) ? $setting_res['payable_commision'][0]['payable_commision'] : "0";
                    $partner_details = fetch_details('partner_details', ['partner_id' => $this->user_details['id']], 'is_accepting_custom_jobs');
                    $setting_res['is_accepting_custom_jobs'] = $partner_details[0]['is_accepting_custom_jobs'] ?? 0;
                }
                foreach ($setting as $type) {
                    $notallowed_settings = ["languages", "email_settings", "country_codes", "api_key_settings", "test",];
                    if (!in_array($type['variable'], $notallowed_settings)) {
                        $setting_res[$type['variable']] = get_settings($type['variable'], true);
                    }
                    $setting_res['general_settings']['at_store'] = isset($setting_res['general_settings']['at_store']) ? $setting_res['general_settings']['at_store'] : "1";
                    $setting_res['general_settings']['at_doorstep'] = isset($setting_res['general_settings']['at_doorstep']) ? $setting_res['general_settings']['at_doorstep'] : "1";
                }
            }
            if (!empty($this->user_details['id'])) {
                $subscription = fetch_details('partner_subscriptions', ['partner_id' =>  $this->user_details['id']], [], 1, 0, 'id', 'DESC');
            }
            $subscription_information['subscription_id'] = isset($subscription[0]['subscription_id']) ? $subscription[0]['subscription_id'] : "";
            $subscription_information['isSubscriptionActive'] = isset($subscription[0]['status']) ? $subscription[0]['status'] : "deactive";
            $subscription_information['created_at'] = isset($subscription[0]['created_at']) ? $subscription[0]['created_at'] : "";
            $subscription_information['updated_at'] = isset($subscription[0]['updated_at']) ? $subscription[0]['updated_at'] : "";
            $subscription_information['is_payment'] = isset($subscription[0]['is_payment']) ? $subscription[0]['is_payment'] : "";
            $subscription_information['id'] = isset($subscription[0]['id']) ? $subscription[0]['id'] : "";
            $subscription_information['partner_id'] = isset($subscription[0]['partner_id']) ? $subscription[0]['partner_id'] : "";
            $subscription_information['purchase_date'] = isset($subscription[0]['purchase_date']) ? $subscription[0]['purchase_date'] : "";
            $subscription_information['expiry_date'] = isset($subscription[0]['expiry_date']) ? $subscription[0]['expiry_date'] : "";
            $subscription_information['name'] = isset($subscription[0]['name']) ? $subscription[0]['name'] : "";
            $subscription_information['description'] = isset($subscription[0]['description']) ? $subscription[0]['description'] : "";
            $subscription_information['duration'] = isset($subscription[0]['duration']) ? $subscription[0]['duration'] : "";
            $subscription_information['price'] = isset($subscription[0]['price']) ? $subscription[0]['price'] : "";
            $subscription_information['discount_price'] = isset($subscription[0]['discount_price']) ? $subscription[0]['discount_price'] : "";
            $subscription_information['order_type'] = isset($subscription[0]['order_type']) ? $subscription[0]['order_type'] : "";
            $subscription_information['max_order_limit'] = isset($subscription[0]['max_order_limit']) ? $subscription[0]['max_order_limit'] : "";
            $subscription_information['is_commision'] = isset($subscription[0]['is_commision']) ? $subscription[0]['is_commision'] : "";
            $subscription_information['commission_threshold'] = isset($subscription[0]['commission_threshold']) ? $subscription[0]['commission_threshold'] : "";
            $subscription_information['commission_percentage'] = isset($subscription[0]['commission_percentage']) ? $subscription[0]['commission_percentage'] : "";
            $subscription_information['publish'] = isset($subscription[0]['publish']) ? $subscription[0]['publish'] : "";
            $subscription_information['tax_id'] = isset($subscription[0]['tax_id']) ? $subscription[0]['tax_id'] : "";
            $subscription_information['tax_type'] = isset($subscription[0]['tax_type']) ? $subscription[0]['tax_type'] : "";
            if (!empty($subscription[0])) {
                $price = calculate_partner_subscription_price($subscription[0]['partner_id'], $subscription[0]['subscription_id'], $subscription[0]['id']);
            }
            $subscription_information['tax_value'] = isset($price[0]['tax_value']) ? $price[0]['tax_value'] : "";
            $subscription_information['price_with_tax']  = isset($price[0]['price_with_tax']) ? $price[0]['price_with_tax'] : "";
            $subscription_information['original_price_with_tax'] = isset($price[0]['original_price_with_tax']) ? $price[0]['original_price_with_tax'] : "";
            $subscription_information['tax_percentage'] = isset($price[0]['tax_percentage']) ? $price[0]['tax_percentage'] : "";
            $setting_res['subscription_information'] = json_decode(json_encode($subscription_information), true);
            if (!empty($setting_res['web_settings']['social_media'])) {
                foreach ($setting_res['web_settings']['social_media'] as &$row) {
                    $row['file'] = isset($row['file']) ? base_url("public/uploads/web_settings/" . $row['file']) : "";
                }
            } else {
                $setting_res['web_settings']['social_media'] = [];
            }
            $keys_to_unset = [
                'refund_policy',
                'become_provider_page_settings',
                'sms_gateway_setting',
                'notification_settings',
                'firebase_settings',
                'country_codes_old'
            ];
            foreach ($keys_to_unset as $key) {
                if (array_key_exists($key, $setting_res)) {
                    unset($setting_res[$key]);
                }
            }
            $setting_res['app_settings'] = [];
            $keys = [
                'customer_current_version_android_app',
                'customer_current_version_ios_app',
                'customer_compulsary_update_force_update',
                'provider_current_version_android_app',
                'provider_current_version_ios_app',
                'provider_compulsary_update_force_update',
                'essage_for_customer_application',
                'message_for_customer_application',
                'customer_app_maintenance_mode',
                'message_for_provider_application',
                'provider_app_maintenance_mode',
                'country_currency_code',
                'currency',
                'decimal_point',
                'customer_playstore_url',
                'customer_appstore_url',
                'provider_playstore_url',
                'provider_appstore_url',
                'android_google_interstitial_id',
                'android_google_banner_id',
                'android_google_ads_status',
                'ios_google_interstitial_id',
                'ios_google_banner_id',
                'ios_google_ads_status'
            ];
            foreach ($keys as $key) {
                $setting_res['app_settings'][$key] = isset($setting_res['general_settings'][$key]) ? $setting_res['general_settings'][$key] : "";
                unset($setting_res['general_settings'][$key]);
            }
            //for werb
            $setting_res['social_media'] = $setting_res['web_settings']['social_media'];
            $keys_to_unset = [
                'web_settings',
                'firebase_settings',
                'range_units',
                'country_code',
                'customer_privacy_policy',
                'customer_terms_conditions',
                'system_tax_settings',
            ];
            foreach ($keys_to_unset as $key) {
                unset($setting_res[$key]);
            }
            $general_settings_keys_to_unset = [
                'customer_app_maintenance_schedule_date',
                'provider_app_maintenance_schedule_date',
                'favicon',
                'logo',
                'half_logo',
                'partner_favicon',
                'partner_logo',
                'partner_half_logo',
                'provider_location_in_provider_details',
                'system_timezone',
                'primary_color',
                'secondary_color',
                'primary_shadow',
                'max_serviceable_distance',
                'booking_auto_cancle_duration',
            ];
            foreach ($general_settings_keys_to_unset as $key) {
                unset($setting_res['general_settings'][$key]);
            }
            $app_setting = [
                'customer_current_version_android_app',
                'customer_current_version_ios_app',
                'customer_compulsary_update_force_update',
                'message_for_customer_application',
                'customer_app_maintenance_mode'
            ];
            foreach ($app_setting as $key) {
                unset($setting_res['app_settings'][$key]);
            }
            $setting_res['demo_mode'] = (ALLOW_MODIFICATION == 0) ? "1" : "0";
            if (isset($setting_res) && !empty($setting_res)) {
                $response = [
                    'error' => false,
                    'message' => "setting recieved Successfully",
                    'data' => $setting_res
                ];
            } else {
                $response = [
                    'error' => true,
                    'message' => "No data found in setting",
                    'data' => $setting_res
                ];
            }
            return $this->response->setJSON($response);
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - get_settings()');
            return $this->response->setJSON($response);
        }
    }
    public function get_categories()
    {
        try {
            $categories = new Category_model();
            $limit = !empty($this->request->getPost('limit')) ?  $this->request->getPost('limit') : 10;
            $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
            $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
            $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'ASC';
            $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
            $where = [];
            if ($this->request->getPost('id')) {
                $where['id'] = $this->request->getPost('id');
            }
            if ($this->request->getPost('slug')) {
                $where['slug'] = $this->request->getPost('slug');
            }
            $where['parent_id'] = 0;
            $data = $categories->list(true, $search, $limit, $offset, $sort, $order, $where);
            if (!empty($data['data'])) {
                return response_helper('Categories fetched successfully', false, $data['data'], 200, ['total' => $data['total']]);
            } else {
                return response_helper('categories not found', false);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - get_categories()');
            return $this->response->setJSON($response);
        }
    }
    public function get_sub_categories()
    {
        try {
            $validation =  \Config\Services::validation();
            $validation->setRules(
                [
                    'category_id' => 'required',
                ]
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
            $categories = new Category_model();
            $limit = $this->request->getPost('limit') ?: 10;
            $offset = $this->request->getPost('offset') ?: 0;
            $sort = $this->request->getPost('sort') ?: 'id';
            $order = $this->request->getPost('order') ?: 'DESC';
            $search = $this->request->getPost('search') ?: '';
            $where = [];
            if ($this->request->getPost('id')) {
                $where['id'] = $this->request->getPost('id');
            }
            if ($this->request->getPost('slug')) {
                $where['slug'] = $this->request->getPost('slug');
            }
            if ($this->request->getPost('category_id')) {
                $where['parent_id'] = $this->request->getPost('category_id');
            }
            if (!exists(['parent_id' => $this->request->getPost('category_id')], 'categories')) {
                return response_helper('no sub categories found');
            }
            $data = $categories->list(true, $search, $limit, $offset, $sort, $order, $where);
            if (!empty($data['data'])) {
                return response_helper('Sub Categories fetched successfully', false, $data['data'], 200, ['total' => $data['total']]);
            } else {
                return response_helper('Sub categories not found', false);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - get_sub_categories()');
            return $this->response->setJSON($response);
        }
    }
    public function update_fcm()
    {
        try {
            $validation =  \Config\Services::validation();
            $validation->setRules(
                [
                    'platform' => 'required'
                ],
                []
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
            $fcm_id = $this->request->getPost('fcm_id');
            $platform = $this->request->getPost('platform');
            if (update_details(['fcm_id' => $fcm_id, 'platform' => $platform], ['id' => $this->user_details['id']], 'users')) {
                return response_helper('fcm id updated succesfully', false, ['fcm_id' => $fcm_id]);
            } else {
                return response_helper();
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - update_fcm()');
            return $this->response->setJSON($response);
        }
    }
    public function get_taxes()
    {
        try {
            $taxes = new Tax_model();
            $limit = $this->request->getPost('limit') ?: 10;
            $offset = $this->request->getPost('offset') ?: 0;
            $sort = $this->request->getPost('sort') ?: 'id';
            $order = $this->request->getPost('order') ?: 'ASC';
            $search = $this->request->getPost('search') ?: '';
            $where = [];
            if ($this->request->getPost('id')) {
                $where['id'] = $this->request->getPost('id');
            }
            $data = $taxes->list(true, $search, $limit, $offset, $sort, $order, $where);
            if (!empty($data['data'])) {
                return response_helper('Taxes fetched successfully', false, $data['data'], 200, ['total' => $data['total']]);
            } else {
                return response_helper('Taxes not found', false);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - get_taxes()');
            return $this->response->setJSON($response);
        }
    }
    public function get_services()
    {
        try {
            $Service_model = new Service_model();
            $limit = $this->request->getPost('limit') ?: 10;
            $offset = $this->request->getPost('offset') ?: 0;
            $sort = $this->request->getPost('sort') ?: 'id';
            $order = $this->request->getPost('order') ?: 'DESC';
            $search = $this->request->getPost('search') ?: '';
            $category_ids = $this->request->getPost('category_ids');
            $min_budget = $this->request->getPost('min_budget');
            $max_budget = $this->request->getPost('max_budget');
            $rating = $this->request->getPost('rating');
            $where_in = [];
            $additional_data = [];

            if (isset($category_ids) && !empty($category_ids)) {
                $where_in = explode(",", $category_ids);
            }

            if ($sort == 'price') {
                $sort = 'discounted_price'; // Sort by discounted price first
            }

            $settings = get_settings('general_settings', true);
            if (($this->request->getPost('latitude') && !empty($this->request->getPost('latitude')) && ($this->request->getPost('longitude') && !empty($this->request->getPost('longitude'))))) {
                $additional_data = [
                    'latitude' => $this->request->getPost('latitude'),
                    'longitude' => $this->request->getPost('longitude'),
                    'city_id' => $this->user_details['city_id'],
                    'max_serviceable_distance' => $settings['max_serviceable_distance'],
                ];
            }
            $where = 's.user_id = ' . $this->user_details['id'] . ' ';
            if (isset($min_budget) && !empty($min_budget) && isset($max_budget) && !empty($max_budget)) {
                if (isset($where)) {
                    $where .= '  AND (`s`.`price` BETWEEN "' . $min_budget . '" AND "' . $max_budget . '" OR `s`.`discounted_price` BETWEEN "' . $min_budget . '" AND "' . $max_budget . '")';
                } else {
                    $where = ' AND (`s`.`price` BETWEEN "' . $min_budget . '" AND "' . $max_budget . '" OR `s`.`discounted_price` BETWEEN "' . $min_budget . '" AND "' . $max_budget . '")';
                }
            } elseif (isset($min_budget) && !empty($min_budget)) {
                if (isset($where)) {
                    $where .= ' AND (`s`.`price` >= "' . $min_budget . '" OR `s`.`discounted_price` >= "' . $min_budget . '")';
                } else {
                    $where = '  AND (`s`.`price` >= "' . $min_budget . '" OR `s`.`discounted_price` >= "' . $min_budget . '")';
                }
            } elseif (isset($max_budget) && !empty($max_budget)) {
                if (isset($where)) {
                    $where .= ' AND (`s`.`price` <= "' . $max_budget . '" OR `s`.`discounted_price` <= "' . $max_budget . '")';
                } else {
                    $where = ' AND (`s`.`price` <= "' . $max_budget . '" OR `s`.`discounted_price` <= "' . $max_budget . '")';
                }
            }
            $at_store = 0;
            $at_doorstep = 0;
            $partner_details = fetch_details('partner_details', ['partner_id' =>  $this->user_details['id']]);
            if (isset($partner_details[0]['at_store']) && $partner_details[0]['at_store'] == 1) {
                $at_store = 1;
            }
            if (isset($partner_details[0]['at_doorstep']) && $partner_details[0]['at_doorstep'] == 1) {
                $at_doorstep = 1;
            }
            $data = $Service_model->list(true, $search, $limit, $offset, $sort, $order, $where, $additional_data, 'category_id', $where_in, $this->user_details['id'], '', '');
            foreach ($data['data'] as $key => $value) {
                $averageRating = $value['average_rating'];
                $shouldUnset = false;
                if (isset($rating) && !empty($rating)) {


                    //if rating paramter pass
                    if ($rating == 1) {
                        if (!($averageRating >= 1 && $averageRating < 2)) {
                            $shouldUnset = true;
                        }
                    } elseif ($rating == 2) {
                        if (!($averageRating >= 2 && $averageRating < 3)) {
                            $shouldUnset = true;
                        }
                    } elseif ($rating == 3) {
                        if (!($averageRating >= 3 && $averageRating < 4)) {
                            $shouldUnset = true;
                        }
                    } elseif ($rating == 4) {
                        if (!($averageRating >= 4 && $averageRating < 5)) {
                            $shouldUnset = true;
                        }
                    } elseif ($rating == 5) {
                        if ($averageRating != 5) {
                            $shouldUnset = true;
                        }
                    }

                    //if sort=price


                }
                if ($shouldUnset) {
                    unset($data['data'][$key]);
                }
            }
            $data['data'] = array_values($data['data']);
            if (isset($data['error'])) {
                return response_helper($data['message']);
            }
            if (!empty($data['data'])) {
                return response_helper(
                    'services fetched successfully',
                    false,
                    $data['data'],
                    200,
                    [
                        'total' => $data['new_total'],
                        'min_price' => $data['new_min_price'],
                        'max_price' => $data['new_max_price'],
                        'min_discount_price' => $data['new_min_discount_price'],
                        'max_discount_price' => $data['new_max_discount_price'],
                    ]
                );
            } else {
                return response_helper(
                    'services not found',
                    false,
                    [],
                    200,
                    [
                        'total' => $data['new_total'] ?? '0',
                        'min_price' => $data['new_min_price'] ?? '0',
                        'max_price' => $data['new_max_price'] ?? '0',
                        'min_discount_price' => $data['new_min_discount_price'] ?? '0',
                        'max_discount_price' => $data['new_max_discount_price'] ?? '0',
                    ]
                );
            }
        } catch (\Exception $th) {
            print_r($th); die;
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - get_services()');
            return $this->response->setJSON($response);
        }
    }

    // public function get_services()
    // {
    //     try {
    //         $Service_model = new Service_model();
    //         $limit = $this->request->getPost('limit') ?: 10;
    //         $offset = $this->request->getPost('offset') ?: 0;
    //         $sort = $this->request->getPost('sort') ?: 'id';
    //         $order = $this->request->getPost('order') ?: 'DESC';
    //         $search = $this->request->getPost('search') ?: '';
    //         $category_ids = $this->request->getPost('category_ids');
    //         $min_budget = $this->request->getPost('min_budget');
    //         $max_budget = $this->request->getPost('max_budget');
    //         $rating = $this->request->getPost('rating');
    //         $where_in = [];
    //         $additional_data = [];

    //         // Validate order parameter
    //         $order = strtoupper($order);
    //         if ($order !== 'ASC' && $order !== 'DESC') {
    //             $order = 'DESC'; // Default to DESC if invalid
    //         }

    //         // Use default sort for database query
    //         $db_sort = 'id';

    //         if (isset($category_ids) && !empty($category_ids)) {
    //             $where_in = explode(",", $category_ids);
    //         }

    //         $settings = get_settings('general_settings', true);
    //         if (($this->request->getPost('latitude') && !empty($this->request->getPost('latitude')) && ($this->request->getPost('longitude') && !empty($this->request->getPost('longitude'))))) {
    //             $additional_data = [
    //                 'latitude' => $this->request->getPost('latitude'),
    //                 'longitude' => $this->request->getPost('longitude'),
    //                 'city_id' => $this->user_details['city_id'],
    //                 'max_serviceable_distance' => $settings['max_serviceable_distance'],
    //             ];
    //         }
    //         $where = 's.user_id = ' . $this->user_details['id'] . ' ';
    //         if (isset($min_budget) && !empty($min_budget) && isset($max_budget) && !empty($max_budget)) {
    //             if (isset($where)) {
    //                 $where .= '  AND (`s`.`price` BETWEEN "' . $min_budget . '" AND "' . $max_budget . '" OR `s`.`discounted_price` BETWEEN "' . $min_budget . '" AND "' . $max_budget . '")';
    //             } else {
    //                 $where = ' AND (`s`.`price` BETWEEN "' . $min_budget . '" AND "' . $max_budget . '" OR `s`.`discounted_price` BETWEEN "' . $min_budget . '" AND "' . $max_budget . '")';
    //             }
    //         } elseif (isset($min_budget) && !empty($min_budget)) {
    //             if (isset($where)) {
    //                 $where .= ' AND (`s`.`price` >= "' . $min_budget . '" OR `s`.`discounted_price` >= "' . $min_budget . '")';
    //             } else {
    //                 $where = '  AND (`s`.`price` >= "' . $min_budget . '" OR `s`.`discounted_price` >= "' . $min_budget . '")';
    //             }
    //         } elseif (isset($max_budget) && !empty($max_budget)) {
    //             if (isset($where)) {
    //                 $where .= ' AND (`s`.`price` <= "' . $max_budget . '" OR `s`.`discounted_price` <= "' . $max_budget . '")';
    //             } else {
    //                 $where = ' AND (`s`.`price` <= "' . $max_budget . '" OR `s`.`discounted_price` <= "' . $max_budget . '")';
    //             }
    //         }
    //         $at_store = 0;
    //         $at_doorstep = 0;
    //         $partner_details = fetch_details('partner_details', ['partner_id' =>  $this->user_details['id']]);
    //         if (isset($partner_details[0]['at_store']) && $partner_details[0]['at_store'] == 1) {
    //             $at_store = 1;
    //         }
    //         if (isset($partner_details[0]['at_doorstep']) && $partner_details[0]['at_doorstep'] == 1) {
    //             $at_doorstep = 1;
    //         }

    //         // Use default sort for database query
    //         $data = $Service_model->list(true, $search, $limit, $offset, $db_sort, 'DESC', $where, $additional_data, 'category_id', $where_in, $this->user_details['id'], '', '');

    //         // Apply rating filter
    //         foreach ($data['data'] as $key => $value) {
    //             $averageRating = $value['average_rating'];
    //             $shouldUnset = false;
    //             if (isset($rating) && !empty($rating)) {
    //                 if ($rating == 1) {
    //                     if (!($averageRating >= 1 && $averageRating < 2)) {
    //                         $shouldUnset = true;
    //                     }
    //                 } elseif ($rating == 2) {
    //                     if (!($averageRating >= 2 && $averageRating < 3)) {
    //                         $shouldUnset = true;
    //                     }
    //                 } elseif ($rating == 3) {
    //                     if (!($averageRating >= 3 && $averageRating < 4)) {
    //                         $shouldUnset = true;
    //                     }
    //                 } elseif ($rating == 4) {
    //                     if (!($averageRating >= 4 && $averageRating < 5)) {
    //                         $shouldUnset = true;
    //                     }
    //                 } elseif ($rating == 5) {
    //                     if ($averageRating != 5) {
    //                         $shouldUnset = true;
    //                     }
    //                 }
    //             }
    //             if ($shouldUnset) {
    //                 unset($data['data'][$key]);
    //             }
    //         }

    //         // Reset array keys after filtering
    //         $data['data'] = array_values($data['data']);

    //         // Apply custom sorting based on requested sort field and order
    //         if (!empty($data['data'])) {
    //             // Determine the sort field based on requested sort parameter
    //             switch (strtolower($sort)) {
    //                 case 'booking':
    //                     $sort_field = 'booking_count'; // Assuming this field exists
    //                     break;
    //                 case 'price':
    //                     $sort_field = 'price';
    //                     break;
    //                 case 'rating':
    //                     $sort_field = 'average_rating';
    //                     break;
    //                 default:
    //                     $sort_field = 'id';
    //                     break;
    //             }

    //             // Apply sorting with the specified order
    //             if (!empty($sort_field)) {
    //                 usort($data['data'], function($a, $b) use ($sort_field, $order) {
    //                     // Check if field exists
    //                     if (!isset($a[$sort_field]) || !isset($b[$sort_field])) {
    //                         return 0;
    //                     }

    //                     // Handle null values
    //                     if ($a[$sort_field] === null && $b[$sort_field] === null) {
    //                         return 0;
    //                     } elseif ($a[$sort_field] === null) {
    //                         return ($order === 'ASC') ? -1 : 1;
    //                     } elseif ($b[$sort_field] === null) {
    //                         return ($order === 'ASC') ? 1 : -1;
    //                     }

    //                     // Convert string values to proper numeric format for comparison
    //                     $val_a = is_numeric($a[$sort_field]) ? (float)$a[$sort_field] : $a[$sort_field];
    //                     $val_b = is_numeric($b[$sort_field]) ? (float)$b[$sort_field] : $b[$sort_field];

    //                     // Compare as numbers if both are numeric
    //                     if (is_numeric($val_a) && is_numeric($val_b)) {
    //                         if ($val_a == $val_b) {
    //                             // If values are equal, sort by ID as secondary sort key
    //                             return ($order === 'ASC') ? ($a['id'] - $b['id']) : ($b['id'] - $a['id']);
    //                         }
    //                         return ($order === 'ASC') ? ($val_a > $val_b ? 1 : -1) : ($val_a < $val_b ? 1 : -1);
    //                     } else {
    //                         // String comparison
    //                         $cmp = strcmp(strval($val_a), strval($val_b));
    //                         return ($order === 'ASC') ? $cmp : -$cmp;
    //                     }
    //                 });

    //                 // Optional debugging code - uncomment if needed
    //                 /*
    //                 error_log("Sorted data by $sort_field ($order):");
    //                 foreach ($data['data'] as $item) {
    //                     error_log("ID: " . $item['id'] . ", Rating: " . $item['average_rating']);
    //                 }
    //                 */
    //             }
    //         }

    //         if (isset($data['error'])) {
    //             return response_helper($data['message']);
    //         }

    //         if (!empty($data['data'])) {
    //             return response_helper(
    //                 'services fetched successfully',
    //                 false,
    //                 $data['data'],
    //                 200,
    //                 [
    //                     'total' => $data['new_total'],
    //                     'min_price' => $data['new_min_price'],
    //                     'max_price' => $data['new_max_price'],
    //                     'min_discount_price' => $data['new_min_discount_price'],
    //                     'max_discount_price' => $data['new_max_discount_price'],
    //                 ]
    //             );
    //         } else {
    //             return response_helper(
    //                 'services not found',
    //                 false,
    //                 [],
    //                 200,
    //                 [
    //                     'total' => $data['new_total'] ?? '0',
    //                     'min_price' => $data['new_min_price'] ?? '0',
    //                     'max_price' => $data['new_max_price'] ?? '0',
    //                     'min_discount_price' => $data['new_min_discount_price'] ?? '0',
    //                     'max_discount_price' => $data['new_max_discount_price'] ?? '0',
    //                 ]
    //             );
    //         }
    //     } catch (\Exception $th) {
    //         $response['error'] = true;
    //         $response['message'] = 'Something went wrong';
    //         log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - get_services()');
    //         return $this->response->setJSON($response);
    //     }
    // }



    public function delete_orders()
    {
        try {
            $validation =  \Config\Services::validation();
            $validation->setRules(
                [
                    'order_id' => 'required|numeric',
                ]
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
            $order_id = $this->request->getPost('order_id');
            $partner_id = $this->user_details['id'];
            $orders = fetch_details('orders', ['id' => $order_id, 'partner_id' => $partner_id]);
            if (empty($orders)) {
                $response = [
                    'error' => true,
                    'message' => 'No, Order Found',
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
            $db      = \Config\Database::connect();
            $builder = $db->table('orders')->delete(['id' => $order_id, 'partner_id' => $partner_id]);
            if ($builder) {
                $builder = $db->table('order_services')->delete(['order_id' => $order_id]);
                if ($builder) {
                    $response = [
                        'error' => false,
                        'message' => 'Order deleted successfully!',
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                } else {
                    $response = [
                        'error' => true,
                        'message' => 'Order does not exist!',
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                }
            } else {
                $response = [
                    'error' => true,
                    'message' => 'Order Not Found',
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - delete_orders()');
            return $this->response->setJSON($response);
        }
    }
    public function get_promocodes()
    {
        try {
            $model = new Promo_code_model();
            $limit = $this->request->getPost('limit') ?: 10;
            $offset = $this->request->getPost('offset') ?: 0;
            $sort = $this->request->getPost('sort') ?: 'id';
            $order = $this->request->getPost('order') ?: 'DESC';
            $search = $this->request->getPost('search') ?: '';
            $where = [];
            if ($this->user_details['id'] != '') {
                $where['pc.partner_id'] = $this->user_details['id'];
            }
            $data = $model->list(true, $search, $limit, $offset, $sort, $order, $where);
            if (!empty($data['data'])) {
                return response_helper('Promocode fetched successfully', false, $data['data'], 200, ['total' => $data['total']]);
            } else {
                return response_helper('Promocode not found', false);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - get_promocodes()');
            return $this->response->setJSON($response);
        }
    }

    public function manage_promocode()
    {
        try {
            $db = \Config\Database::connect();
            $this->validation = \Config\Services::validation();
            $this->validation->setRules([
                'promo_code' => 'required',
                'start_date' => 'required',
                'end_date' => 'required',
                'minimum_order_amount' => 'required|numeric',
                'discount' => 'required|numeric',
                'discount_type' => 'required',
                'max_discount_amount' => 'required|numeric',
                'status' => 'required',
                'message' => 'required',
            ]);

            $partner_id = $this->user_details['id'];
            $disk = fetch_current_file_manager();
            $old_image = [];

            // Check if this is an update operation
            $is_update = isset($_POST['promo_id']) && !empty($_POST['promo_id']);
            if ($is_update) {
                $where['id'] = $_POST['promo_id'];
                $old_image = fetch_details('promo_codes', $where, ['image']);
            }

            // Handle image upload
            $image = "";
            $paths = [
                'image' => [
                    'file' => $this->request->getFile('image'),
                    'path' => 'public/uploads/promocodes/',
                    'error' => 'Failed to create promocodes folders',
                    'folder' => 'promocodes',
                    'old_file' => $old_image[0]['image'] ?? "",
                    'disk' => $disk,
                ],
            ];

            $uploadedFiles = [];
            foreach ($paths as $key => $upload) {
                if ($upload['file'] && $upload['file']->isValid()) {
                    if (!empty($upload['old_file'])) {
                        if ($disk == "local_server") {
                            $old_image[0]['image'] = "public/uploads/promocodes/" . $old_image[0]['image'];
                        }
                        delete_file_based_on_server($upload['folder'], $upload['old_file'], $upload['disk']);
                    }
                    $result = upload_file($upload['file'], $upload['path'], $upload['error'], $upload['folder']);
                    if ($result['error'] === false) {
                        $uploadedFiles[$key] = [
                            'url' => "public/uploads/promocodes/" . $result['file_name'],
                            'disk' => $result['disk']
                        ];
                    } else {
                        return ErrorResponse($result['message'], true, [], [], 200, csrf_token(), csrf_hash());
                    }
                }
            }

            $image = isset($uploadedFiles['image']['url']) ? $uploadedFiles['image']['url'] : ($old_image[0]['image'] ?? "");
            $image_disk = isset($uploadedFiles['image']['disk']) ? $uploadedFiles['image']['disk'] : $disk;

            if (!$this->validation->withRequest($this->request)->run()) {
                return $this->response->setJSON([
                    'error' => true,
                    'message' => $this->validation->getErrors(),
                    'data' => []
                ]);
            }

            $promocode_model = new Promo_code_model();

            // Prepare promocode data
            $promocode = [
                'partner_id' => $partner_id,
                'promo_code' => $this->request->getVar('promo_code'),
                'message' => $this->request->getVar('message'),
                'start_date' => $this->request->getVar('start_date'),
                'end_date' => $this->request->getVar('end_date'),
                'no_of_users' => $this->request->getPost('no_of_users') ?: 1,
                'minimum_order_amount' => $this->request->getVar('minimum_order_amount'),
                'max_discount_amount' => $this->request->getVar('max_discount_amount'),
                'discount' => $this->request->getVar('discount'),
                'discount_type' => $this->request->getVar('discount_type'),
                'repeat_usage' => $this->request->getPost('repeat_usage') ?: 0,
                'no_of_repeat_usage' => $this->request->getPost('no_of_repeat_usage') ?: 0,
                'image' => $image,
                'status' => $this->request->getPost('status') ?: 0,
            ];

            // Add ID for update operation
            if ($is_update) {
                $promocode['id'] = $_POST['promo_id'];
            }

            // Save the promocode
            if (!$promocode_model->save($promocode)) {
                throw new \Exception('Failed to save promocode');
            }

            // Get the correct ID based on operation type
            $promo_id = $is_update ? $_POST['promo_id'] : $promocode_model->getInsertID();

            // Fetch the saved promocode details
            $data = fetch_details('promo_codes', ['id' => $promo_id], [
                'id',
                'promo_code',
                'start_date',
                'end_date',
                'minimum_order_amount',
                'discount',
                'discount_type',
                'max_discount_amount',
                'repeat_usage',
                'no_of_repeat_usage',
                'no_of_users',
                'message',
                'status',
                'image',
                '(SELECT COUNT(*) FROM orders WHERE promo_code = promo_codes.promo_code) AS total_used_number',
            ]);

            // Handle image URL based on disk type
            if (!empty($data)) {
                switch ($disk) {
                    case 'aws_s3':
                        $data[0]['image'] = fetch_cloud_front_url('promocodes', $data[0]['image']);
                        break;
                    default:
                        $data[0]['image'] = base_url($data[0]['image']);
                        break;
                }
            }

            return $this->response->setJSON([
                'error' => false,
                'message' => $is_update ? 'Promocode updated successfully' : 'Promocode saved successfully',
                'data' => $data
            ]);
        } catch (\Exception $th) {
            log_the_responce(
                $this->request->header('Authorization') . '   Params passed :: '
                    . json_encode($_POST) . " Issue => " . $th,
                date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - manage_promocode()'
            );

            return $this->response->setJSON([
                'error' => true,
                'message' => 'Something went wrong'
            ]);
        }
    }

    public function delete_promocode()
    {
        try {
            $validation =  \Config\Services::validation();
            $validation->setRules(
                [
                    'promo_id' => 'required|numeric',
                ]
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
            $promo_id = $this->request->getPost('promo_id');
            $is_exist =  exists(['id' => $promo_id], 'promo_codes');
            if (!$is_exist) {
                $response = [
                    'error' => true,
                    'message' => 'Promo code does not exist!',
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
            $db      = \Config\Database::connect();
            $disk = fetch_current_file_manager();
            $old_image = fetch_details('promo_codes', ['id' => $promo_id], ['image',]);
            delete_file_based_on_server('promocodes', $old_image[0]['image'], $disk);
            $builder = $db->table('promo_codes')->delete(['id' => $promo_id]);
            if ($builder) {
                $response = [
                    'error' => false,
                    'message' => 'Promocode deleted successfully!',
                    'data' => []
                ];
                return $this->response->setJSON($response);
            } else {
                $response = [
                    'error' => true,
                    'message' => 'Promocode does not exist!',
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - delete_promocode()');
            return $this->response->setJSON($response);
        }
    }
    public function send_withdrawal_request()
    {
        try {
            $this->validation =  \Config\Services::validation();
            $this->validation->setRules([
                'payment_address' => 'required',
                'amount' => 'required|numeric',
                'user_type' => 'required',
            ]);
            if (!$this->validation->withRequest($this->request)->run()) {
                $errors = $this->validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => []
                ];
                return $this->response->setJSON($response);
            } else {
                $model = new Payment_request_model();
                if (isset($_POST['id']) && !empty($_POST['id'])) {
                    $request_id = $_POST['id'];
                } else {
                    $request_id = '';
                }
                $user_id = ($this->request->getVar('user_id') != '') ? $this->request->getVar('user_id') : $this->user_details['id'];
                $amount = $this->request->getVar('amount');
                $payment_request = array(
                    'id' => $request_id,
                    'user_id' => $user_id,
                    'user_type' => $this->request->getVar('user_type'),
                    'payment_address' => $this->request->getVar('payment_address'),
                    'amount' => $amount,
                    'remarks' => $this->request->getVar('remarks'),
                    'status' => 0,
                );
                $current_balance =  fetch_details('users', ['id' => $user_id], ['balance', 'username']);
                if ($current_balance[0]['balance'] >= $amount) {
                    $model->save($payment_request);
                    update_balance($this->request->getVar('amount'), $user_id, 'deduct');
                    $balance = fetch_details("users", ["id" => $this->user_details['id']], ['balance']);
                    $response = [
                        'error' => false,
                        'message' => 'payment request sent!',
                        'balance' => $balance[0]['balance'],
                        'data' => []
                    ];
                    send_web_notification('Withdraw Request',  $current_balance[0]['username'] . ' Withdraw request for ' . $amount, null, 'https://edemand-test.thewrteam.in/admin/partners/payment_request');
                    $db      = \Config\Database::connect();
                    $builder = $db->table('users u');
                    $users = $builder->Select("u.id,u.fcm_id,u.username,u.email")
                        ->join('users_groups ug', 'ug.user_id=u.id')
                        ->where('ug.group_id', '1')
                        ->get()->getResultArray();
                    if (!empty($users[0]['email']) && check_notification_setting('withdraw_request_send', 'email') && is_unsubscribe_enabled($user_id) == 1) {
                        send_custom_email('withdraw_request_send', $user_id, $users[0]['email']);
                    }
                    if (check_notification_setting('withdraw_request_send', 'sms')) {
                        send_custom_sms('withdraw_request_send', $user_id, $users[0]['email']);
                    }
                    return $this->response->setJSON($response);
                } else {
                    $response = [
                        'error' => true,
                        'message' => 'Insufficient Balance!',
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                }
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - send_withdrawal_request()');
            return $this->response->setJSON($response);
        }
    }
    public function get_withdrawal_request()
    {
        try {
            $model = new Payment_request_model();

            $limit = $this->request->getPost('limit') ?: 10;
            $offset = $this->request->getPost('offset') ?: 0;
            $sort = $this->request->getPost('sort') ?: 'p.id';
            $order = $this->request->getPost('order') ?: 'DESC';
            $search = $this->request->getPost('search') ?: '';
            $status_filter = (string)$this->request->getPost('status_filter');
            $where = [];
            if ($this->user_details['id'] !== '') {
                $where['user_id'] = $this->user_details['id'];
            }
            //0 was added in comparison because !empty was working with 0 in value
            if (!empty($status_filter) || $status_filter==0) {
                $where['status'] = $status_filter;
            }
            $data = $model->list(true, $search, $limit, $offset, $sort, $order, $where);
            $balance = fetch_details("users", ["id" => $this->user_details['id']], ['balance', 'payable_commision']);
            if (!empty($data['data'])) {
                return response_helper('Payment Request fetched successfully', false, $data['data'], 200, ['total' => $data['total'], 'balance' => $balance[0]['balance']]);
            } else {
                return response_helper('Payment Request not found', false,[],200,['total' => $data['total'], 'balance' => $balance[0]['balance']]);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - get_withdrawal_request()');
            return $this->response->setJSON($response);
        }
    }
    public function delete_withdrawal_request()
    {
        try {
            $validation =  \Config\Services::validation();
            $validation->setRules(
                [
                    'id' => 'required|numeric',
                ]
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
            $id = $this->request->getPost('id');
            $is_exist = fetch_details('payment_request', ['id' => $id, 'user_id' => $this->user_details['id']]);
            if (!empty($is_exist)) {
                $db      = \Config\Database::connect();
                $builder = $db->table('payment_request')->delete(['id' => $id]);
                $response = [
                    'error' => false,
                    'message' => 'Payment request deleted successfully!',
                    'data' => []
                ];
                return $this->response->setJSON($response);
            } else {
                $response = [
                    'error' => true,
                    'message' => 'Payment request does not exist!',
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - delete_withdrawal_request()');
            return $this->response->setJSON($response);
        }
    }
    public function manage_service()
    {
        try {
            $tax = get_settings('system_tax_settings', true);
            $this->validation =  \Config\Services::validation();
            $this->validation->setRules(
                [
                    'title' => 'required',
                    'description' => 'required',
                    'price' => 'required|numeric|greater_than[0]',
                    'duration' => 'required|numeric',
                    'max_qty' => 'required|numeric|greater_than[0]',
                    'tags' => 'required',
                    'members' => 'required|numeric|greater_than_equal_to[1]',
                    'categories' => 'required',
                    'discounted_price' => "permit_empty|numeric",
                    'is_cancelable' => 'numeric',
                    'at_store' => 'required',
                    'at_doorstep' => 'required',
                ],
            );
            if (!$this->validation->withRequest($this->request)->run()) {
                $errors = $this->validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => []
                ];
                return $this->response->setJSON($response);
            } else {
                if (isset($_POST['tags']) && !empty($_POST['tags'])) {
                    $convertedTags =  implode(', ', $_POST['tags']);
                } else {
                    $response = [
                        'error' => true,
                        'message' => "Tags required!",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                }
            }
            $title = $this->removeScript($this->request->getPost('title'));
            $description = $this->removeScript($this->request->getPost('description'));
            $path = "./public/uploads/services/";
            $disk = fetch_current_file_manager();
            if (isset($_POST['service_id']) && !empty($_POST['service_id'])) {
                $service_id = $_POST['service_id'];
                $old_icon = fetch_details('services', ['id' => $service_id], ['image'])[0]['image'];
                $old_files = fetch_details('services', ['id' => $service_id], ['files'])[0]['files'];
                $old_other_images = fetch_details('services', ['id' => $service_id], ['other_images'])[0]['other_images'];
            } else {
                $service_id = "";
                $old_icon = "";
                $old_files = "";
                $old_other_images = "";
            }
            $image_name = "";
            if (!empty($_FILES['image']) && isset($_FILES['image'])) {
                $file =  $this->request->getFile('image');
                if (!empty($old_icon)) {
                    delete_file_based_on_server('services', $old_icon, $disk);
                }
                $result = upload_file($file, 'public/uploads/services/', 'error creating services folder', 'services');
                if ($result['error'] === false) {
                    if ($result['disk'] == 'local_server') {
                        $image_name = 'public/uploads/services/' .  $result['file_name'];
                    } else if ($result['disk'] == "aws_s3") {
                        $image_name =   $result['file_name'];
                    } else {
                        $image_name = 'public/uploads/services/' .  $result['file_name'];
                    }
                } else {
                    return ErrorResponse($result['message'], true, [], [], 200, csrf_token(), csrf_hash());
                }
            } else {
                $image_name = $old_icon;
            }
            if (isset($_POST['sub_category']) && !empty($_POST['sub_category'])) {
                $category_id = $_POST['sub_category'];
            } else {
                $category_id = $_POST['categories'];
            }
            $discounted_price = $this->request->getPost('discounted_price');
            $price = $this->request->getPost('price');
            if ($discounted_price > $price) {
                $response = [
                    'error' => true,
                    'message' => "discounted price can not be higher than the price",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
            if ($discounted_price == $price) {
                $response = [
                    'error' => true,
                    'message' => "discounted price can not equal to the price",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
            $user_id = $this->user_details['id'];
            $uploaded_images = $this->request->getFiles('files');
            $image_names['name'] = [];
            $data['images'] = [];
            if (isset($uploaded_images['files'])) {
                foreach ($uploaded_images['files'] as $images) {
                    $validate_image = valid_image($images);
                    if ($validate_image == true) {
                        return response_helper("Invalid Image", true, []);
                    }
                    $result = upload_file($images, 'public/uploads/services/', 'Failed to upload services', 'services');
                    if (!empty($old_files)) {
                        $old_files = ($old_files);
                        $old_files_images_array = json_decode($old_files, true);
                        foreach ($old_files_images_array as $old) {
                            delete_file_based_on_server('services', $old, $disk);
                        }
                    }
                    if ($result['disk'] == "local_server") {
                        $name = "public/uploads/services/" . $result['file_name'];
                    } else if ($result['disk'] == "aws_s3") {
                        $name = $result['file_name'];
                    } else {
                        $name = "public/uploads/services/" . $result['file_name'];
                    }
                    array_push($image_names['name'], $name);
                }
                $files_names = json_encode($image_names['name']);
            } else {
                $files_names = $old_files;
            }
            $uploaded_other_images = $this->request->getFiles('other_images');
            $other_image_names['name'] = [];
            $data['images'] = [];
            if (isset($uploaded_other_images['other_images'])) {
                foreach ($uploaded_other_images['other_images'] as $images) {
                    $validate_image = valid_image($images);
                    if ($validate_image == true) {
                        return response_helper("Invalid Image", true, []);
                    }
                    $newName = $images->getRandomName();
                    if ($newName != null) {
                        $result = upload_file($images, 'public/uploads/services/', 'Failed to upload services', 'services');
                        if (!empty($old_other_images)) {
                            $old_other_images_array = json_decode($old_other_images, true);
                            foreach ($old_other_images_array as $old) {
                                delete_file_based_on_server('services', $old, $disk);
                            }
                        }
                        if ($result['disk'] == "local_server") {
                            $name = "public/uploads/services/" . $result['file_name'];
                        } else if ($result['disk'] == "aws_s3") {
                            $name = $result['file_name'];
                        } else {
                            $name = "public/uploads/services/" . $result['file_name'];
                        }
                        array_push($other_image_names['name'], $name);
                    }
                }
                $other_images = json_encode($other_image_names['name']);
            } else {
                $other_images = ($old_other_images);
            }
            $faqs = $this->request->getVar('faqs');
            if (isset($faqs)) {
                $array = json_decode(json_encode($faqs), true);
                $convertedArray = array_map(function ($item) {
                    return [$item['question'], $item['answer']];
                }, $array);
            }
            $partner_details = fetch_details('partner_details', ['partner_id' => $user_id]);
            $check_payment_gateway = get_settings('payment_gateways_settings', true);
            $cod_setting =  $check_payment_gateway['cod_setting'];
            if ($cod_setting == 1) {
                $is_pay_later_allowed = ($this->request->getPost('pay_later') == "1") ? 1 : 0;
            } else {
                $is_pay_later_allowed = 0;
            }

            $service_id_tmp = (empty($service_id) || $service_id=="") ? null : $service_id;
            $service = [
                'id' => $service_id,
                'user_id' => $user_id,
                'category_id' => $category_id,
                'tax_type' => ($this->request->getPost('tax_type') != '') ? $this->request->getPost('tax_type') : 'GST',
                'tax_id' => ($this->request->getVar('tax_id') != '') ? $this->request->getVar('tax_id') : '0',
                'title' => $title,
                'description' => $description,
                'slug' => generate_unique_slug($this->request->getPost('slug'),'services',$service_id_tmp),
                'tags' => $convertedTags,
                'price' => $price,
                'discounted_price' => ($discounted_price != '') ? $discounted_price : '00',
                'image' => $image_name,
                'number_of_members_required' => $this->request->getVar('members'),
                'duration' => $this->request->getVar('duration'),
                'rating' => 0,
                'number_of_ratings' => 0,
                'on_site_allowed' => ($this->request->getPost('on_site') == "on") ? 1 : 0,
                'is_pay_later_allowed' => $is_pay_later_allowed,
                'is_cancelable' => ($this->request->getPost('is_cancelable') == 1) ? 1 : 0,
                'cancelable_till' => ($this->request->getVar('cancelable_till') != "") ? $this->request->getVar('cancelable_till') : '00',
                'max_quantity_allowed' => $this->request->getPost('max_qty'),
                'long_description' => ($this->request->getVar('long_description')) ? ($this->request->getVar('long_description'))  : "",
                'files' => isset($files_names) ? $files_names : "",
                'other_images' => isset($other_images) ? $other_images : "",
                'faqs' => isset($convertedArray) ? json_encode($convertedArray) : "",
                'at_doorstep' => ($this->request->getPost('at_doorstep') == 1) ? 1 : 0,
                'at_store' => ($this->request->getPost('at_store') == 1) ? 1 : 0,
                'status' => ($this->request->getPost('status') == "active") ? 1 : 0,
            ];
            if ($service_id == '') {
                if ($partner_details[0]['need_approval_for_the_service'] == 1) {
                    $approved_by_admin = 0;
                } else {
                    $approved_by_admin = 1;
                }
                $service['approved_by_admin'] = $approved_by_admin;
            }
            $service_model = new Service_model;
            $db      = \Config\Database::connect();
            $disk = fetch_current_file_manager();
            if ($service_model->save($service)) {
                if ($id = $db->insertID()) {
                    $data = fetch_details('services', ['id' => $id]);
                    $new_service_id = $id;
                    if ($disk == "local_server") {
                        $data[0]['image'] = base_url($data[0]['image']);
                    } else if ($disk == "aws_s3") {
                        $data[0]['image'] = fetch_cloud_front_url('services', $data[0]['image']);
                    } else {
                        $data[0]['image'] = base_url($data[0]['image']);
                    }
                    if (!empty($faqs) && is_string($faqs)) {
                        $faqs = json_decode($faqs, true);
                    }
                    if (empty($faqs) || !is_array($faqs)) {
                        $data[0]['faqs'] = [];
                    } else {
                        $data[0]['faqs'] =  ($faqs);
                    }
                    if (is_string($other_images)) {
                        $other_images = json_decode($other_images, true);
                    }
                    if (empty($other_images) || !is_array($other_images)) {
                        $data[0]['other_images'] = [];
                    } else {
                        $data[0]['other_images'] = $other_images;
                    }
                    if (is_string($files_names)) {
                        $files_names = json_decode($files_names, true);
                    }
                    if (empty($files_names) || !is_array($files_names)) {
                        $data[0]['files'] = [];
                    } else {
                        $data[0]['files'] = $files_names;
                    }
                    $data[0]['status'] = ($data[0]['status']==1) ? "active" : "deactive";
                    $data[0]['image_of_the_service'] = $data[0]['image'];
                    $category = fetch_details('categories',['id'=>$category_id]);
                    $data[0]['category_name'] = $category[0]['name'];
                    $response = [
                        'error' => false,
                        'message' => "Service saved successfully!",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => $data
                    ];
                } else {
                    $new_service_id = $service_id;
                    $data = fetch_details('services', ['id' => $service_id]);
                    if ($disk == "local_server") {
                        $data[0]['image'] = base_url($data[0]['image']);
                    } else if ($disk == "aws_s3") {
                        $data[0]['image'] = fetch_cloud_front_url('services', $data[0]['image']);
                    } else {
                        $data[0]['image'] = base_url($data[0]['image']);
                    }

                    if (!empty($faqs) && is_string($faqs)) {
                        $faqs = json_decode($faqs, true);
                    }
                    if (empty($faqs) || !is_array($faqs)) {
                        $data[0]['faqs'] = [];
                    } else {
                        $data[0]['faqs'] =  ($faqs);
                    }
                    if (is_string($other_images)) {
                        $other_images = json_decode($other_images, true);
                    }
                    if (empty($other_images) || !is_array($other_images)) {
                        $data[0]['other_images'] = [];
                    } else {
                        $data[0]['other_images'] = $other_images;
                    }
                    if (is_string($files_names)) {
                        $files_names = json_decode($files_names, true);
                    }
                    if (empty($files_names) || !is_array($files_names)) {
                        $data[0]['files'] = [];
                    } else {
                        $data[0]['files'] = $files_names;
                    }
                    $data[0]['status'] = ($data[0]['status']==1) ? "active" : "deactive";
                    $data[0]['image_of_the_service'] = $data[0]['image'];
                    $category = fetch_details('categories',['id'=>$category_id]);
                    $data[0]['category_name'] = $category[0]['name'];
                    $response = [
                        'error' => false,
                        'message' => "Service updated successfully!",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => $data
                    ];
                }

                
                $response = [
                    'error' => false,
                    'message' => "Service saved successfully!",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => $data
                ];
                return $this->response->setJSON($response);
            } else {
                $response = [
                    'error' => true,
                    'message' => "Service can not be Saved!",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
        } catch (\Exception $th) {

            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - manage_service()');
            return $this->response->setJSON($response);
        }
    }
    public function delete_service()
    {
        try {
            $validation =  \Config\Services::validation();
            $validation->setRules(
                [
                    'service_id' => 'required|numeric',
                ]
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
            $service_id = $this->request->getPost('service_id');
            $exist_service = fetch_details('services', ['id' => $service_id, 'user_id' => $this->user_details['id']], ['id']);
            $disk = fetch_current_file_manager();
            if (!empty($exist_service)) {
                $db      = \Config\Database::connect();
                $builder2 = $db->table('cart')->delete(['service_id' => $service_id]);
                $old_data = fetch_details('services', ['id' => $service_id]);
                if ($old_data[0]['image'] != NULL &&  !empty($old_data[0]['image'])) {
                    delete_file_based_on_server('services', $old_data[0]['image'], $disk);
                }
                if ($old_data[0]['other_images'] != NULL &&  !empty($old_data[0]['other_images'])) {
                    $other_images = json_decode($old_data[0]['other_images'], true);
                    foreach ($other_images as $oi) {
                        delete_file_based_on_server('services', $oi, $disk);
                    }
                }
                if ($old_data[0]['files'] != NULL &&  !empty($old_data[0]['files'])) {
                    $files = json_decode($old_data[0]['files'], true);
                    foreach ($files as $oi) {
                        delete_file_based_on_server('services', $oi, $disk);
                    }
                }
                $builder = $db->table('services')->delete(['id' => $service_id, 'user_id' => $this->user_details['id']]);
                $builder3 = $db->table('services_ratings')->delete(['service_id' => $service_id]);
                if ($builder) {
                    $response = [
                        'error' => false,
                        'message' => 'Service deleted successfully!',
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                } else {
                    $response = [
                        'error' => true,
                        'message' => 'Service does not exist!',
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                }
            } else {
                $response = [
                    'error' => true,
                    'message' => 'Service does not exist!',
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
        } catch (\Exception $th) {

            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - delete_service()');
            return $this->response->setJSON($response);
        }
    }
    public function get_transactions()
    {
        try {
            $transaction_model = new Transaction_model;


            $limit = $this->request->getPost('limit') ?: 10;
            $offset = $this->request->getPost('offset') ?: 0;
            $sort = $this->request->getPost('sort') ?: 'id';
            $order = $this->request->getPost('order') ?: 'DESC';
            $search = $this->request->getPost('search') ?: '';
            $where = [];
            if ($this->user_details['id'] != '') {
                $where['partner_id'] = $this->user_details['id'];
            }
            $data = $transaction_model->list_transactions(true, $search, $limit, $offset, $sort, $order, $where);
            return response_helper('Transactions received successfully.', false, $data, 200);
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - get_transactions()');
            return $this->response->setJSON($response);
        }
    }
    public function update_service_status()
    {
        try {
            $validation =  \Config\Services::validation();
            $validation->setRules(
                [
                    'service_id' => 'required|numeric',
                    'status' => 'required',
                ]
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
            $service_id = $this->request->getPost('service_id');
            $status = $this->request->getPost('status');
            $exist_service = fetch_details('services', ['id' => $service_id, 'user_id' => $this->user_details['id']], ['id']);
            if (!empty($exist_service)) {
                $res = update_details(['status' => $status], ['id' => $service_id, 'user_id' => $this->user_details['id']], 'services');
                if ($res) {
                    $response = [
                        'error' => false,
                        'message' => 'Service status updated successfully!',
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                } else {
                    $response = [
                        'error' => true,
                        'message' => 'Service status cant be changed!',
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                }
            } else {
                $response = [
                    'error' => true,
                    'message' => 'Service status cant be changed!',
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - update_service_status()');
            return $this->response->setJSON($response);
        }
    }
    public function update_order_status()
    {
        try {
            $validation =  \Config\Services::validation();
            $validation->setRules(
                [
                    'order_id' => 'required|numeric',
                    'customer_id' => 'required|numeric',
                    'status' => 'required',
                ]
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
            $order_id = $this->request->getPost('order_id');
            $status = $this->request->getPost('status');
            $customer_id = $this->request->getPost('customer_id');
            $date = $this->request->getPost('date');
            $selected_time = $this->request->getPost('time');
            $otp = $this->request->getPost('otp');
            $work_complete_files = $this->request->getFiles('work_complete_files');
            $work_started_files = $this->request->getFiles('work_started_files');
            $disk = fetch_current_file_manager();
            if ($status == "rescheduled") {
                $res =  validate_status($order_id, $status, $date, $selected_time);
            } else {
                if ($status == "completed") {
                    $res = validate_status($order_id, $status, '', '', $otp, isset($work_complete_files) ? $work_complete_files : "");
                } elseif ($status == "started") {
                    $work_started_files_data = [];
                    $res = validate_status($order_id, $status, '', '', '', isset($work_started_files) ? $work_started_files : "");
                    $order_data = fetch_details('orders', ['id' => $order_id]);
                    if (!empty($order_data)) {
                        if (!empty($order_data[0]['work_started_proof'])) {
                            $work_started_files_data = json_decode($order_data[0]['work_started_proof'], true);
                            foreach ($work_started_files_data as &$data) {
                                if ($disk == "local_server") {
                                    $data = base_url($data);
                                } else if ($disk == "aws_s3") {
                                    $data = fetch_cloud_front_url('provider_work_evidence', $data);
                                } else {
                                    $data = base_url($data);
                                }
                            }
                        }
                    }
                } else if ($status == "booking_ended") {
                    $additional_charges = $this->request->getPost('additional_charges');
                    $res =  validate_status($order_id, $status, '', '', '', isset($work_complete_files) ? $work_complete_files : "", $additional_charges);
                    $work_completed_files_data = [];
                    $order_data = fetch_details('orders', ['id' => $order_id]);
                    if (!empty($order_data)) {
                        if (!empty($order_data[0]['work_completed_proof'])) {
                            $work_completed_files_data = json_decode($order_data[0]['work_completed_proof'], true);
                            foreach ($work_completed_files_data as &$data) {
                                if ($disk == "local_server") {
                                    $data = base_url($data);
                                } else if ($disk == "aws_s3") {
                                    $data = fetch_cloud_front_url('provider_work_evidence', $data);
                                } else {
                                    $data = base_url($data);
                                }
                            }
                        }
                    }
                } else {
                    $res =  validate_status($order_id, $status);
                }
            }
            if ($res['error']) {
                $response['error'] = true;
                $response['message'] = $res['message'];
                $response['data'] = array();
                return $this->response->setJSON($response);
            }
            if ($status == "rescheduled") {
                $user_no = fetch_details('users', ['id' => $customer_id], 'phone')[0]['phone'];
                $response = [
                    'error' => false,
                    'message' => "Order rescheduled successfully!",
                    'contact' => "You can call on '.$user_no.' number to reschedule",
                ];
                return $this->response->setJSON($response);
            }
            $custom_notification = fetch_details('notifications',  ['type' => "customer_order_started"]);
            if ($status == "awaiting") {
                $response = [
                    'error' => false,
                    'message' => "Order is in Awaiting!",
                ];
                // return $this->response->setJSON($response);
            }
            if ($status == "confirmed") {
                $response = [
                    'error' => false,
                    'message' => "Order is Confirmed!",
                ];
                // return $this->response->setJSON($response);
            }
            if ($status == "cancelled") {
                $response = [
                    'error' => false,
                    'message' => "Booking is cancelled!",
                ];
                // return $this->response->setJSON($response);
            }
            if ($status == "completed") {
                $response = [
                    'error' => false,
                    'message' => "Order Completed successfully!",
                ];
                // return $this->response->setJSON($response);
            }
            if ($status == "started") {
                $response = [
                    'error' => false,
                    'message' => "Order Started successfully!",
                    'data' =>   $work_started_files_data,
                ];
                // return $this->response->setJSON($response);
            }
            if ($status == "booking_ended") {
                $response = [
                    'error' => false,
                    'message' => "Order ended successfully!",
                    'data' => $work_completed_files_data
                ];
                // return $this->response->setJSON($response);
            }
            //custom notification message
            if ($status == 'awaiting') {
                $type = ['type' => "customer_order_awaiting"];
            } elseif ($status == 'confirmed') {
                $type = ['type' => "customer_order_confirmed"];
            } elseif ($status == 'rescheduled') {
                $type = ['type' => "customer_order_rescheduled"];
            } elseif ($status == 'cancelled') {
                $type = ['type' => "customer_order_cancelled"];
            } elseif ($status == 'started') {
                $type = ['type' => "customer_order_started"];
            } elseif ($status == 'completed') {
                $type = ['type' => "customer_order_completed"];
            } elseif ($status == 'booking_ended') {
                $type = ['type' => "customer_order_completed"];
            }
            $app_name = isset($settings['company_title']) && !empty($settings['company_title']) ? $settings['company_title'] : '';
            $user_res = fetch_details('users', ['id' => $customer_id], 'username,fcm_id,platform');
            $customer_msg = (!empty($custom_notification)) ? $custom_notification[0]['message'] :  'Hello Dear ' . $user_res[0]['username'] . ' order status updated to ' . $status . ' for your order ID #' . $order_id . ' please take note of it! Thank you for shopping with us. Regards ' . $app_name . '';
            $fcm_ids = array();
            // if (!empty($user_res[0]['fcm_id']) && check_notification_setting('booking_status_updated', 'notification')) {
            //     $fcmMsg = array(
            //         'title' => (!empty($custom_notification)) ? "$custom_notification[0]['title']" : "Order status updated",
            //         'body' => "$customer_msg",
            //         'type' => "order"
            //     );
            //     $fcm_ids['fcm_id'] = $user_res[0]['fcm_id'];
            //     $fcm_ids['platform'] = $user_res[0]['platform'];
            //     send_notification($fcmMsg, $fcm_ids);
            // }
            return $this->response->setJSON($response);
        } catch (\Exception $th) {

            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - update_order_status()');
            return $this->response->setJSON($response);
        }
    }
    public function get_service_ratings()
    {
        try {
            $db      = \Config\Database::connect();
            $this->validation =  \Config\Services::validation();
            $errors = $this->validation->getErrors();
            $response = [
                'error' => true,
                'message' => $errors,
                'data' => []
            ];
            $partner_id = $this->user_details['id'];


            $limit = $this->request->getPost('limit') ?: 10;
            $offset = $this->request->getPost('offset') ?: 0;
            $sort = $this->request->getPost('sort') ?: 'id';
            $order = $this->request->getPost('order') ?: 'DESC';
            $search = $this->request->getPost('search') ?: '';
            $Service_id = ($this->request->getPost('service_id') != '') ? $this->request->getPost('service_id') : '';
            if (!empty($this->request->getPost('service_id'))) {
                $where = " sr.service_id={$Service_id}";
            } else {
                // $where = "s.user_id={$partner_id} OR sr.service_id={$Service_id}";
                $where = " s.user_id = {$partner_id}  OR  (pb.partner_id = {$partner_id} AND sr.custom_job_request_id IS NOT NULL)";
            }
            $ratings = new Service_ratings_model();
            if ($partner_id != '') {
                $data = $ratings->ratings_list(true, $search, $limit, $offset, $sort, $order, $where);
            } else {
                $data = $ratings->ratings_list(true, $search, $limit, $offset, $sort, $order, $where);
            }
            $sort = (isset($_POST['sort']) && !empty($_POST['sort'])) ? $_POST['sort'] : 'id';
            usort($data['data'], function ($a, $b) use ($sort) {
                switch ($sort) {
                    case 'rating':
                        if ($a['rating'] === $b['rating']) {
                            return strtotime($b['rated_on']) - strtotime($a['rated_on']);
                        }
                        return $b['rating'] - $a['rating'];
                    case 'created_at':
                        return strtotime($b['rated_on']) - strtotime($a['rated_on']);
                    default:
                        return $a['id'] - $b['id'];
                }
            });
            if (!empty($Service_id)) {
                $rate_data = get_service_ratings($Service_id);
                $average_rating = $db->table('services s')
                    ->select(' 
                            (SUM(sr.rating) / count(sr.rating)) as average_rating
                            ')
                    ->join('services_ratings sr', 'sr.service_id = s.id')
                    ->where('s.id', $Service_id)
                    ->get()->getResultArray();
            } else {
                $rate_data = get_ratings($partner_id);

                $average_rating = $db->table('users p')
                    ->select('
                    (COALESCE(SUM(sr.rating), 0) + COALESCE(SUM(sr2.rating), 0)) / 
                    NULLIF((COUNT(sr.rating) + COUNT(sr2.rating)), 0) as average_rating,
                    MAX(GREATEST(COALESCE(sr.created_at, "1970-01-01"), 
                                COALESCE(sr2.created_at, "1970-01-01"))) as latest_rating_date
                ')
                    ->join('services s', 's.user_id = p.id', 'left')
                    ->join('services_ratings sr', 'sr.service_id = s.id', 'left')
                    // Custom job ratings
                    ->join('partner_bids pb', 'pb.partner_id = p.id', 'left')
                    ->join('custom_job_requests cj', 'cj.id = pb.custom_job_request_id', 'left')
                    ->join('services_ratings sr2', 'sr2.custom_job_request_id = cj.id', 'left')
                    ->where('p.id', $partner_id)
                    ->orderBy('average_rating', 'desc')
                    ->orderBy('latest_rating_date', 'desc')
                    ->orderBy('p.id', 'asc')
                    ->get()->getResultArray();
            }
            $ratingData = array();
            $rows = array();
            $tempRow = array();
            foreach ($average_rating as $row) {
                $tempRow['average_rating'] = (isset($row['average_rating']) && $row['average_rating'] != "") ? $row['average_rating'] : 0;
            }
            foreach ($rate_data as $row) {
                $tempRow['total_ratings'] = (isset($row['total_ratings']) && $row['total_ratings'] != "") ? $row['total_ratings'] : 0;
                $tempRow['rating_5'] = (isset($row['rating_5']) && $row['rating_5'] != "") ? $row['rating_5'] : 0;
                $tempRow['rating_4'] = (isset($row['rating_4']) && $row['rating_4'] != "") ? $row['rating_4'] : 0;
                $tempRow['rating_3'] = (isset($row['rating_3']) && $row['rating_3'] != "") ? $row['rating_3'] : 0;
                $tempRow['rating_2'] = (isset($row['rating_2']) && $row['rating_2'] != "") ? $row['rating_2'] : 0;
                $tempRow['rating_1'] = (isset($row['rating_1']) && $row['rating_1'] != "") ? $row['rating_1'] : 0;
                $rows[] = $tempRow;
            }
            $ratingData = $rows;
            $response = [
                'error' => false,
                'message' => "Data Retrieved successfully!",
                'ratings' => $ratingData,
                'total' => $data['total'],
                'data' => remove_null_values($data['data']),
            ];
            return $this->response->setJSON($response);
        } catch (\Exception $th) {

            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - get_service_ratings()');
            return $this->response->setJSON($response);
        }
    }
    public function get_notifications()
    {
        try {
            $partner_id = $this->user_details['id'];


            $limit = $this->request->getPost('limit') ?: 10;
            $offset = $this->request->getPost('offset') ?: 0;
            $sort = $this->request->getPost('sort') ?: 'id';
            $order = $this->request->getPost('order') ?: 'DESC';
            $search = $this->request->getPost('search') ?: '';
            $where = $additional_data = [];
            if ($this->request->getPost('id') && !empty($this->request->getPost('id'))) {
                $where['id'] = $this->request->getPost('id');
            }
            $where['user_id'] = $partner_id;
            $notifications = new Notification_model();
            $get_notifications = $notifications->list(true, $search, $limit, $offset, $sort, $order, $where);
            foreach ($get_notifications['data'] as $key => $notifcation) {
                $dateTime = new DateTime($notifcation['date_sent']);
                $date = $dateTime->format('Y-m-d');
                $time = $dateTime->format('H:i');
                if ($date == date('Y-m-d')) {
                    $start = strtotime($time);
                    $end = time();
                    $duration = $start - $end;
                    $duration = date('H', $duration) . ' hours ago';
                } else {
                    $now = time();
                    $date = strtotime($date);
                    $datediff = $now - $date;
                    $duration = round($datediff / (60 * 60 * 24)) . ' days ago';
                }
                $get_notifications['data'][$key]['duration'] = $duration;
            }
            if (!empty($id)) {
                return $get_notifications['data'];
            }
            if (!empty($get_notifications['data'])) {
                return response_helper('Notifications fetched successfully', false, remove_null_values($get_notifications['data']), 200, ['total' => $get_notifications['total']]);
            } else {
                return response_helper('Notification Not Found');
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - get_notifications()');
            return $this->response->setJSON($response);
        }
    }
    public function get_available_slots()
    {
        try {
            $validation =  \Config\Services::validation();
            $validation->setRules(
                [
                    'date' => 'required|valid_date[Y-m-d]',
                ]
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
            $days = [
                'Mon' => 'monday',
                'Tue' => 'tuesday',
                'Wed' => 'wednesday',
                'Thu' => 'thursday',
                'Fri' => 'friday',
                'Sat' => 'saturday',
                'Sun' => 'sunday'
            ];
            $partner_id = $this->user_details['id'];
            $date = $this->request->getPost('date');
            $time = $this->request->getPost('date');
            $date = new DateTime($date);
            $date = $date->format('Y-m-d');
            $day =  date('D', strtotime($date));
            $whole_day = $days[$day];
            $partner_data = fetch_details('partner_details', ['partner_id' => $partner_id], ['advance_booking_days']);
            $time_slots = get_available_slots($partner_id, $date);
            $available_slots = $busy_slots = $time_slots['all_slots'] = [];
            if (isset($time_slots['available_slots']) && !empty($time_slots['available_slots'])) {
                $available_slots = array_map(function ($time_slot) {
                    return ["time" => $time_slot, "is_available" => 1];
                }, $time_slots['available_slots']);
            }
            if (isset($time_slots['busy_slots']) && !empty($time_slots['busy_slots'])) {
                $busy_slots = array_map(function ($time_slot) {
                    return ["time" => $time_slot, "is_available" => 0];
                }, $time_slots['busy_slots']);
            }
            $time_slots['all_slots'] = array_merge($available_slots, $busy_slots);
            array_sort_by_multiple_keys($time_slots['all_slots'], ["time" => SORT_ASC]);
            $partner_timing = fetch_details('partner_timings', ['partner_id' => $partner_id, "day" => $whole_day]);
            if (!empty($partner_data) && $partner_data[0]['advance_booking_days'] > 0) {
                $allowed_advanced_booking_days = $partner_data[0]['advance_booking_days'];
                $current_date = new DateTime();
                $max_available_date =  $current_date->modify("+ $allowed_advanced_booking_days day")->format('Y-m-d');
                if ($date > $max_available_date) {
                    $response = [
                        'error' => true,
                        'message' => "You'can not choose date beyond available booking days which is + $allowed_advanced_booking_days days",
                        'data' => []
                    ];
                    return $this->response->setJSON(remove_null_values($response));
                }
            } else if (!empty($partner_data) && $partner_data[0]['advance_booking_days'] == 0) {
                $current_date = new DateTime();
                if ($date > $current_date->format('Y-m-d')) {
                    $response = [
                        'error' => true,
                        'message' => "Advanced Booking for this partner is not available",
                        'data' => []
                    ];
                    return $this->response->setJSON(remove_null_values($response));
                }
            } else {
                $response = [
                    'error' => true,
                    'message' => "No Partner Found",
                    'data' => []
                ];
                return $this->response->setJSON(remove_null_values($response));
            }
            if (!empty($time_slots)) {
                $response = [
                    'error' => $time_slots['error'],
                    'message' => ($time_slots['error'] == false) ? 'Found Time slots' : 'No slot available for this date',
                    'data' => [
                        'all_slots' => (!empty($time_slots) && $time_slots['error'] == false) ? $time_slots['all_slots'] : [],
                    ]
                ];
                return $this->response->setJSON(remove_null_values($response));
            } else {
                $response = [
                    'error' => true,
                    'message' => 'No slot is available on this date!',
                    'data' => []
                ];
                return $this->response->setJSON(remove_null_values($response));
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - get_available_slots()');
            return $this->response->setJSON($response);
        }
    }
    public function delete_provider_account()
    {
        try {
            $user_id = $this->user_details['id'];
            if (!exists(['id' => $user_id], 'users')) {
                return response_helper('user does not exist please enter valid user ID!', true);
            }
            $user_data = fetch_details('users_groups', ['user_id' => $user_id]);
            $disk = fetch_current_file_manager();
            if (!empty($user_data) && isset($user_data[0]['group_id']) && !empty($user_data[0]['group_id']) && $user_data[0]['group_id'] == 3) {
                $partner_data = fetch_details('partner_details', ['partner_id' => $user_id]);
                if (!empty($user_data[0]['image'])) {
                    delete_file_based_on_server('profile', $user_data[0]['image'], $disk);
                }
                if (!empty($partner_details[0]['banner'])) {
                    delete_file_based_on_server('banner', $partner_data[0]['banner'], $disk);
                }
                if (!empty($partner_data[0]['address_id'])) {
                    delete_file_based_on_server('address_id', $partner_data[0]['address_id'], $disk);
                }
                if (!empty($partner_data[0]['passport'])) {
                    delete_file_based_on_server('passport', $partner_data[0]['passport'], $disk);
                }
                if (!empty($partner_data[0]['national_id'])) {
                    delete_file_based_on_server('national_id', $partner_data[0]['national_id'], $disk);
                }
                if (delete_details(['id' => $user_id], 'users') && delete_details(['user_id' => $user_id], 'users_groups')) {
                    delete_details(['user_id' => $user_id], 'users_tokens');
                    delete_details(['partner_id' => $user_id], 'promo_codes');
                    $slider_data = fetch_details('sliders', ['type' => 'services'], 'type_id');
                    foreach ($slider_data as $row) {
                        $data = fetch_details('services', ['id' => $row['type_id']], 'user_id');
                        if ($data[0]['user_id'] == $user_id) {
                            delete_details(['type_id' => $row['type_id']], 'sliders');
                        }
                    }
                    return response_helper('User account deleted successfully', false);
                } else {
                    return response_helper('User account does not delete', true);
                }
            } else {
                return response_helper("This user's account can't delete ", true);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - delete_provider_account()');
            return $this->response->setJSON($response);
        }
    }
    public function change_password()
    {
        try {
            $validation =  \Config\Services::validation();
            $validation->setRules(
                [
                    'old' => 'required',
                    'new' => 'required',
                ]
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
            $user_id = $this->user_details['id'];
            $user_data = fetch_details('users', ['id' => $user_id]);
            $identity = $user_data[0]['phone'];
            $change = $this->ionAuth->changePassword($identity, $this->request->getPost('old'), $this->request->getPost('new'), $user_id);
            if ($change) {
                $this->ionAuth->logout();
                return $this->response->setJSON([
                    'error' => false,
                    'message' => "Password changes successfully",
                    "data" => $_POST,
                ]);
            } else {
                return $this->response->setJSON([
                    'error' => true,
                    'message' => "Old password did not matched.",
                    "data" => $_POST,
                ]);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - change_password()');
            return $this->response->setJSON($response);
        }
    }
    public function forgot_password()
    {
        try {
            $validation =  \Config\Services::validation();
            $validation->setRules(
                [
                    'new_password' => 'required',
                    'mobile_number' => 'required',
                    'country_code' => 'required',
                ]
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
            $identity = $this->request->getPost('mobile_number');
            $user_data = fetch_details('users', ['phone' => $identity]);
            $db      = \Config\Database::connect();
            $builder = $db->table('users u');
            $builder->select('u.*,ug.group_id')
                ->join('users_groups ug', 'ug.user_id = u.id')
                ->where('ug.group_id', 3)
                ->where(['phone' => $identity]);
            $user_data = $builder->get()->getResultArray();
            if (empty($user_data)) {
                return $this->response->setJSON([
                    'error' => false,
                    'message' => "User does not exist",
                    "data" => $_POST,
                ]);
            }
            if ((($user_data[0]['country_code'] == null) || ($user_data[0]['country_code'] == $this->request->getPost('country_code'))) && (($user_data[0]['phone'] == $identity))) {
                $change = $this->ionAuth->resetPassword($identity, $this->request->getPost('new_password'), $user_data[0]['id']);
                if ($change) {
                    $this->ionAuth->logout();
                    return $this->response->setJSON([
                        'error' => false,
                        'message' => "Forgot Password  successfully",
                        "data" => $_POST,
                    ]);
                } else {
                    return $this->response->setJSON([
                        'error' => true,
                        'message' => $this->ionAuth->errors($this->validationListTemplate),
                        "data" => $_POST,
                    ]);
                }
                $change = $this->ionAuth->resetPassword($identity, $this->request->getPost('new'));
                // $change = $this->ionAuth->resetPassword($identity, $this->request->getPost('new_password'));
                if ($change) {
                    $this->ionAuth->logout();
                    return $this->response->setJSON([
                        'error' => false,
                        'message' => "Forgot Password  successfully",
                        "data" => $_POST,
                    ]);
                } else {
                    return $this->response->setJSON([
                        'error' => true,
                        'message' => $this->ionAuth->errors($this->validationListTemplate),
                        "data" => $_POST,
                    ]);
                }
            } else {
                return $this->response->setJSON([
                    'error' => true,
                    'message' => "Faorgot Password Failed",
                    "data" => $_POST,
                ]);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - forgot_password()');
            return $this->response->setJSON($response);
        }
    }
    public function get_cash_collection()
    {
        try {


            $limit = $this->request->getPost('limit') ?: 10;
            $offset = $this->request->getPost('offset') ?: 0;
            $sort = $this->request->getPost('sort') ?: 'id';
            $order = $this->request->getPost('order') ?: 'DESC';
            $user_id = $this->user_details['id'];
            if (!exists(['id' => $user_id], 'users')) {
                $response = [
                    'error' => true,
                    'message' => 'Invalid User Id.',
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            $where = ['partner_id' => $user_id];
            if (!empty($this->request->getPost('admin_cash_recevied'))) {
                $where['status'] = "admin_cash_recevied";
            }
            if (!empty($this->request->getPost('provider_cash_recevied'))) {
                $where['status'] = "provider_cash_recevied";
            }
            $res = fetch_details('cash_collection', $where, '', $limit, $offset, $sort, $order);
            $payable_commision = fetch_details("users", ["id" => $this->user_details['id']], ['payable_commision']);
            $total = count($res);
            if (!empty($res)) {
                $response = [
                    'error' => false,
                    'message' => 'Cash collection history recieved successfully.',
                    'total' => strval($total),
                    'payable_commision' => isset($payable_commision[0]['payable_commision']) ? $payable_commision[0]['payable_commision'] : "0",
                    'data' => $res,
                ];
                return $this->response->setJSON($response);
            } else {
                $response = [
                    'error' => true,
                    'message' => 'No data found',
                    'payable_commision' => isset($payable_commision[0]['payable_commision']) ? $payable_commision[0]['payable_commision'] : "0",
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - get_cash_collection()');
            return $this->response->setJSON($response);
        }
    }
    public function get_settlement_history()
    {
        try {


            $limit = $this->request->getPost('limit') ?: 10;
            $offset = $this->request->getPost('offset') ?: 0;
            $sort = $this->request->getPost('sort') ?: 'id';
            $order = $this->request->getPost('order') ?: 'DESC';
            $status_filter = $this->request->getPost('status_filter') ?? null; // New filter

            $user_id = $this->user_details['id'];
            if (!exists(['id' => $user_id], 'users')) {
                $response = [
                    'error' => true,
                    'message' => 'Invalid User Id.',
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }

            $filter = ['provider_id' => $user_id];

            if ($status_filter) {
                $status_map = ['credited' => 'credit', 'debited' => 'debit'];
                if (isset($status_map[$status_filter])) {
                    $filter['status'] = $status_map[$status_filter];
                }
            }

            $res = fetch_details('settlement_history', $filter, '', $limit, $offset, $sort, $order);

            $balance = fetch_details("users", ["id" => $user_id], ['balance', 'payable_commision']);
            $total = count($res);
            if (!empty($res)) {

                foreach ($res as &$value) { // Add "&" to modify the original array
                    if ($value['status'] == "credit") {
                        $value['status'] = "credited";
                    } elseif ($value['status'] == "debit") {
                        $value['status'] = "debited";
                    }
                }
                unset($value); // Unset reference to avoid unexpected behavior

                $response = [
                    'error' => false,
                    'message' => 'Settlement history recieved successfully.',
                    'total' => $total,
                    'balance' => $balance[0]['balance'],
                    'data' => $res,
                ];
                return $this->response->setJSON($response);
            } else {
                $response = [
                    'error' => true,
                    'message' => 'No data found',
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - get_settlement_history()');
            return $this->response->setJSON($response);
        }
    }
    public function get_all_categories()
    {
        try {
            $categories = new Category_model();


            $limit = !empty($this->request->getPost('limit')) ?  $this->request->getPost('limit') : '0';
            $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
            $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
            $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'DESC';
            $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
            $where = [];
            if ($this->request->getPost('id')) {
                $where['id'] = $this->request->getPost('id');
            }
            if ($this->request->getPost('slug')) {
                $where['slug'] = $this->request->getPost('slug');
            }
            $data = $categories->list(true, $search, $limit, $offset, $sort, $order, $where);
            if (!empty($data['data'])) {
                return response_helper('Categories fetched successfully', false, $data['data'], 200, ['total' => $data['total']]);
            } else {
                return response_helper('categories not found', false);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - get_all_categories()');
            return $this->response->setJSON($response);
        }
    }
    public function get_subscription()
    {
        try {
            $where = [];
            $subscription_id = $this->request->getPost('subscription_id');
            if (null !== $subscription_id) {
                $where['id'] = $subscription_id;
            }
            $where['status'] = 1;
            $where['publish'] = 1;
            $subscription_details = fetch_details('subscriptions', $where);
            foreach ($subscription_details as $row) {
                $tempRow['id'] = $row['id'];
                $tempRow['name'] = $row['name'];
                $tempRow['description'] = $row['description'];
                $tempRow['duration'] = $row['duration'];
                $tempRow['price'] = $row['price'];
                $tempRow['discount_price'] = $row['discount_price'];
                $tempRow['publish'] = $row['publish'];
                $tempRow['order_type'] = $row['order_type'];
                $tempRow['max_order_limit'] = ($row['order_type'] == "limited") ? $row['max_order_limit'] : "-";
                $tempRow['service_type'] = $row['service_type'];
                $tempRow['max_service_limit'] = $row['max_service_limit'];
                $tempRow['tax_type'] = $row['tax_type'];
                $tempRow['tax_id'] = $row['tax_id'];
                $tempRow['is_commision'] = $row['is_commision'];
                $tempRow['commission_threshold'] = $row['commission_threshold'];
                $tempRow['commission_percentage'] = $row['commission_percentage'];
                $tempRow['status'] = $row['status'];
                $taxPercentageData = fetch_details('taxes', ['id' => $row['tax_id']], ['percentage']);
                if (!empty($taxPercentageData)) {
                    $taxPercentage = $taxPercentageData[0]['percentage'];
                } else {
                    $taxPercentage = 0;
                }
                $tempRow['tax_percentage'] = $taxPercentage;
                if ($row['discount_price'] == "0") {
                    if ($row['tax_type'] == "excluded") {
                        $tempRow['tax_value'] = number_format((intval(($row['price'] * ($taxPercentage) / 100))), 2);
                        $tempRow['price_with_tax']  = strval($row['price'] + ($row['price'] * ($taxPercentage) / 100));
                        $tempRow['original_price_with_tax'] = strval($row['price'] + ($row['price'] * ($taxPercentage) / 100));
                    } else {
                        $tempRow['tax_value'] = "";
                        $tempRow['price_with_tax']  = strval($row['price']);
                        $tempRow['original_price_with_tax'] = strval($row['price']);
                    }
                } else {
                    if ($row['tax_type'] == "excluded") {
                        $tempRow['tax_value'] = number_format((intval(($row['discount_price'] * ($taxPercentage) / 100))), 2);
                        $tempRow['price_with_tax']  = strval($row['discount_price'] + ($row['discount_price'] * ($taxPercentage) / 100));
                        $tempRow['original_price_with_tax'] = strval($row['price'] + ($row['discount_price'] * ($taxPercentage) / 100));
                    } else {
                        $tempRow['tax_value'] = "";
                        $tempRow['price_with_tax']  = strval($row['discount_price']);
                        $tempRow['original_price_with_tax'] = strval($row['price']);
                    }
                }
                $rows[] = $tempRow;
            }
            if (!empty($rows)) {
                return response_helper('Subscriptions fetched successfully', false, $rows, 200, ['total' => count($subscription_details)]);
            } else {
                return response_helper('Subscriptions not found', false);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - get_subscription()');
            return $this->response->setJSON($response);
        }
    }
    public function buy_subscription()
    {
        try {
            $validation =  \Config\Services::validation();
            $validation->setRules(
                [
                    'subscription_id' => 'required',
                ]
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
            $partner_id = $this->user_details['id'];
            $subscription_id = $this->request->getPost('subscription_id');
            $is_already_subscribe = fetch_details('partner_subscriptions', ['partner_id' => $partner_id, 'status' => 'active']);
            if (!empty($is_already_subscribe)) {
                return $this->response->setJSON([
                    'error' => false,
                    'message' => "Already have an active subscription",
                    'data' => []
                ]);
            }
            $subscription_details = fetch_details('subscriptions', ['id' => $subscription_id]);
            $price = $subscription_details[0]['price'];
            $is_commission_based = $subscription_details[0]['is_commision'] == "yes";
            if ($price == "0") {
                $partner_subscriptions = [
                    'partner_id' =>  $partner_id,
                    'subscription_id' => $subscription_id,
                    'is_payment' => "1",
                    'status' => "active",
                    'purchase_date' => date('Y-m-d'),
                    'expiry_date' => date('Y-m-d'),
                    'name' => $subscription_details[0]['name'],
                    'description' => $subscription_details[0]['description'],
                    'duration' => $subscription_details[0]['duration'],
                    'price' => $subscription_details[0]['price'],
                    'discount_price' => $subscription_details[0]['discount_price'],
                    'publish' => $subscription_details[0]['publish'],
                    'order_type' => $subscription_details[0]['order_type'],
                    'max_order_limit' => $subscription_details[0]['max_order_limit'],
                    'service_type' => $subscription_details[0]['service_type'],
                    'max_service_limit' => $subscription_details[0]['max_service_limit'],
                    'tax_type' => $subscription_details[0]['tax_type'],
                    'tax_id' => $subscription_details[0]['tax_id'],
                    'is_commision' => $subscription_details[0]['is_commision'],
                    'commission_threshold' => $subscription_details[0]['commission_threshold'],
                    'commission_percentage' => $subscription_details[0]['commission_percentage'],
                ];
                insert_details($partner_subscriptions, 'partner_subscriptions');
                $commission = $is_commission_based ? $subscription_details[0]['commission_percentage'] : 0;
                update_details(['admin_commission' => $commission], ['partner_id' => $partner_id], 'partner_details');
            } else {
                $subscriptionDuration = $subscription_details[0]['duration'];
                $purchaseDate = date('Y-m-d');
                $expiryDate = date('Y-m-d', strtotime($purchaseDate . ' + ' . $subscriptionDuration . ' days')); // Add the duration to the purchase date
                $details_for_subscription = fetch_details('subscriptions', ['id' => $subscription_id]);
                $subscriptionDuration = $details_for_subscription[0]['duration'];
                $partner_subscriptions = [
                    'partner_id' =>  $partner_id,
                    'subscription_id' => $subscription_id,
                    'is_payment' => "0",
                    'status' => "pending",
                    'purchase_date' => $purchaseDate,
                    'expiry_date' => $expiryDate,
                    'name' => $details_for_subscription[0]['name'],
                    'description' => $details_for_subscription[0]['description'],
                    'duration' => $details_for_subscription[0]['duration'],
                    'price' => $details_for_subscription[0]['price'],
                    'discount_price' => $details_for_subscription[0]['discount_price'],
                    'publish' => $details_for_subscription[0]['publish'],
                    'order_type' => $details_for_subscription[0]['order_type'],
                    'max_order_limit' => $details_for_subscription[0]['max_order_limit'],
                    'service_type' => $details_for_subscription[0]['service_type'],
                    'max_service_limit' => $details_for_subscription[0]['max_service_limit'],
                    'tax_type' => $details_for_subscription[0]['tax_type'],
                    'tax_id' => $details_for_subscription[0]['tax_id'],
                    'is_commision' => $details_for_subscription[0]['is_commision'],
                    'commission_threshold' => $details_for_subscription[0]['commission_threshold'],
                    'commission_percentage' => $details_for_subscription[0]['commission_percentage'],
                ];
                $data = insert_details($partner_subscriptions, 'partner_subscriptions');
            }
            $response = [
                'error' => false,
                'message' => 'Congratulations on your subscription! Now is the time to shine on eDEmand and seize new business opportunities. Welcome aboard and best of luck!',
                'data' => []
            ];
        } catch (Exception $th) {
            $response['error'] = true;
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - buy_subscription()');
            $response['message'] = 'Something went wrong';
        }
        return $this->response->setJSON($response);
    }
    public function add_transaction()
    {
        try {
            $validation = service('validation');
            $validation->setRules([
                'subscription_id' => 'required|numeric',
                'status' => 'required',
                'message' => 'required',
                'type' => 'required',
            ]);
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            $transaction_model = new Transaction_model();
            $subscription_id = (int) $this->request->getVar('subscription_id');
            $status = $this->request->getVar('status');
            $message = $this->request->getVar('message');
            $type = $this->request->getVar('type');
            $user = fetch_details('users', ['id' => $this->user_details['id']]);
            if (empty($user)) {
                $response = [
                    'error' => true,
                    'message' => "User not found!",
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            $subscription = fetch_details('subscriptions', ['id' => $this->request->getVar('subscription_id')]);
            $transaction_id = fetch_details('transactions', ['id' => $this->request->getVar('transaction_id')]);
            $price = $subscription[0]['price'];
            $discount_price = $subscription[0]['discount_price'];
            $is_commission_based = $subscription[0]['is_commision'] == "yes";
            if ($status != "success") {
                $is_already_subscribe = fetch_details('partner_subscriptions', ['partner_id' => $this->user_details['id'], 'status' => 'active']);
                if (!empty($is_already_subscribe)) {
                    return $this->response->setJSON([
                        'error' => true,
                        'message' => "Already have an active subscription",
                        'data' => []
                    ]);
                }
            }
            if (!empty($subscription)) {
                if (!empty($transaction_id)) {
                    $data1['status'] = $status;
                    $data1['type'] = $type;
                    $data1['message'] = $message;
                    $subscription_data['status'] = ($status == "failed") ? 'deactive' : 'active';
                    $subscription_data['is_payment'] = ($status == "failed") ? '2' : '1';
                    $condition = ['subscription_id' => $subscription_id, 'partner_id' => $this->user_details['id'], 'transaction_id' => $this->request->getVar('transaction_id')];
                    update_details($subscription_data, $condition, 'partner_subscriptions');
                    update_details($data1, ['id' => $this->request->getVar('transaction_id')], 'transactions');
                    $data['transaction'] = fetch_details('transactions', ['id' => $this->request->getVar('transaction_id') ?? null])[0];
                    $subscription = fetch_details('partner_subscriptions', ['partner_id' => $transaction_id[0]['user_id'], 'subscription_id' => $transaction_id[0]['subscription_id']]);
                    $subscription_information['subscription_id'] = isset($subscription[0]['subscription_id']) ? $subscription[0]['subscription_id'] : "";
                    $subscription_information['isSubscriptionActive'] = isset($subscription[0]['status']) ? $subscription[0]['status'] : "deactive";
                    $subscription_information['created_at'] = isset($subscription[0]['created_at']) ? $subscription[0]['created_at'] : "";
                    $subscription_information['updated_at'] = isset($subscription[0]['updated_at']) ? $subscription[0]['updated_at'] : "";
                    $subscription_information['is_payment'] = isset($subscription[0]['is_payment']) ? $subscription[0]['is_payment'] : "";
                    $subscription_information['id'] = isset($subscription[0]['id']) ? $subscription[0]['id'] : "";
                    $subscription_information['partner_id'] = isset($subscription[0]['partner_id']) ? $subscription[0]['partner_id'] : "";
                    $subscription_information['purchase_date'] = isset($subscription[0]['purchase_date']) ? $subscription[0]['purchase_date'] : "";
                    $subscription_information['expiry_date'] = isset($subscription[0]['expiry_date']) ? $subscription[0]['expiry_date'] : "";
                    $subscription_information['name'] = isset($subscription[0]['name']) ? $subscription[0]['name'] : "";
                    $subscription_information['description'] = isset($subscription[0]['description']) ? $subscription[0]['description'] : "";
                    $subscription_information['duration'] = isset($subscription[0]['duration']) ? $subscription[0]['duration'] : "";
                    $subscription_information['price'] = isset($subscription[0]['price']) ? $subscription[0]['price'] : "";
                    $subscription_information['discount_price'] = isset($subscription[0]['discount_price']) ? $subscription[0]['discount_price'] : "";
                    $subscription_information['order_type'] = isset($subscription[0]['order_type']) ? $subscription[0]['order_type'] : "";
                    $subscription_information['max_order_limit'] = isset($subscription[0]['max_order_limit']) ? $subscription[0]['max_order_limit'] : "";
                    $subscription_information['is_commision'] = isset($subscription[0]['is_commision']) ? $subscription[0]['is_commision'] : "";
                    $subscription_information['commission_threshold'] = isset($subscription[0]['commission_threshold']) ? $subscription[0]['commission_threshold'] : "";
                    $subscription_information['commission_percentage'] = isset($subscription[0]['commission_percentage']) ? $subscription[0]['commission_percentage'] : "";
                    $subscription_information['publish'] = isset($subscription[0]['publish']) ? $subscription[0]['publish'] : "";
                    $subscription_information['tax_id'] = isset($subscription[0]['tax_id']) ? $subscription[0]['tax_id'] : "";
                    $subscription_information['tax_type'] = isset($subscription[0]['tax_type']) ? $subscription[0]['tax_type'] : "";
                    if (!empty($subscription[0])) {
                        $price = calculate_partner_subscription_price($subscription[0]['partner_id'], $subscription[0]['subscription_id'], $subscription[0]['id']);
                    }
                    $subscription_information['tax_value'] = isset($price[0]['tax_percentage']) ? $price[0]['tax_percentage'] : "";
                    $subscription_information['price_with_tax']  = isset($price[0]['price_with_tax']) ? $price[0]['price_with_tax'] : "";
                    $subscription_information['original_price_with_tax'] = isset($price[0]['original_price_with_tax']) ? $price[0]['original_price_with_tax'] : "";
                    $data['subscription_information'] = json_decode(json_encode($subscription_information), true);
                    $response['error'] = false;
                    $response['data'] = $data;
                    $response['message'] = 'Transaction Updated successfully';
                } else {
                    $taxPercentageData = fetch_details('taxes', ['id' => $subscription[0]['tax_id']], ['percentage']);
                    if (!empty($taxPercentageData)) {
                        $taxPercentage = $taxPercentageData[0]['percentage'];
                    } else {
                        $taxPercentage = 0;
                    }
                    if (!empty($subscription[0])) {
                        $price = calculate_subscription_price($subscription[0]['id']);
                    }
                    $trsansction_data = [
                        'transaction_type' => 'transaction',
                        'user_id' => $this->user_details['id'],
                        'partner_id' => "",
                        'order_id' => "0",
                        'type' => $type,
                        'txn_id' => "0",
                        'amount' =>  $price[0]['price_with_tax'],
                        'status' => $status,
                        'currency_code' => "",
                        'subscription_id' => $subscription_id,
                        'message' => $message,
                    ];
                    $insert = add_transaction($trsansction_data);
                    if ($subscription[0]['price'] == "0") {
                        $subscriptionDuration = $subscription[0]['duration'];
                        if ($subscriptionDuration == "unlimited") {
                            $subscriptionDuration = 0;
                        }
                        $purchaseDate = date('Y-m-d');
                        $expiryDate = date('Y-m-d', strtotime($purchaseDate . ' + ' . $subscriptionDuration . ' days'));
                        if ($subscriptionDuration == "unlimited") {
                            $subscriptionDuration = 0;
                        }
                        $partner_subscriptions = [
                            'partner_id' =>   $this->user_details['id'],
                            'subscription_id' => $subscription_id,
                            'is_payment' => "1",
                            'status' => "active",
                            'purchase_date' => date('Y-m-d'),
                            'expiry_date' => $expiryDate,
                            'name' => $subscription[0]['name'],
                            'description' => $subscription[0]['description'],
                            'duration' => $subscription[0]['duration'],
                            'price' => $subscription[0]['price'],
                            'discount_price' => $subscription[0]['discount_price'],
                            'publish' => $subscription[0]['publish'],
                            'order_type' => $subscription[0]['order_type'],
                            'max_order_limit' => $subscription[0]['max_order_limit'],
                            'service_type' => $subscription[0]['service_type'],
                            'max_service_limit' => $subscription[0]['max_service_limit'],
                            'tax_type' => $subscription[0]['tax_type'],
                            'tax_id' => $subscription[0]['tax_id'],
                            'is_commision' => $subscription[0]['is_commision'],
                            'commission_threshold' => $subscription[0]['commission_threshold'],
                            'commission_percentage' => $subscription[0]['commission_percentage'],
                            'transaction_id' => 0,
                            'tax_percentage' => $price[0]['tax_percentage'],
                        ];
                        $insert_subscription =  insert_details($partner_subscriptions, 'partner_subscriptions');
                        $commission = $is_commission_based ? $subscription[0]['commission_percentage'] : 0;
                        update_details(['admin_commission' => $commission], ['partner_id' =>   $this->user_details['id']], 'partner_details');
                    } else {
                        $subscriptionDuration = $subscription[0]['duration'];
                        if ($subscriptionDuration == "unlimited") {
                            $subscriptionDuration = 0;
                        }
                        $purchaseDate = date('Y-m-d');
                        $expiryDate = date('Y-m-d', strtotime($purchaseDate . ' + ' . $subscriptionDuration . ' days'));
                        if ($subscriptionDuration == "unlimited") {
                            $subscriptionDuration = 0;
                        }
                        $details_for_subscription = fetch_details('subscriptions', ['id' => $subscription_id]);
                        $partner_subscriptions = [
                            'partner_id' =>    $this->user_details['id'],
                            'subscription_id' => $subscription_id,
                            'is_payment' => "0",
                            'status' => "pending",
                            'purchase_date' => $purchaseDate,
                            'expiry_date' => $expiryDate,
                            'name' => $details_for_subscription[0]['name'],
                            'description' => $details_for_subscription[0]['description'],
                            'duration' => $details_for_subscription[0]['duration'],
                            'price' => $details_for_subscription[0]['price'],
                            'discount_price' => $details_for_subscription[0]['discount_price'],
                            'publish' => $details_for_subscription[0]['publish'],
                            'order_type' => $details_for_subscription[0]['order_type'],
                            'max_order_limit' => $details_for_subscription[0]['max_order_limit'],
                            'service_type' => $details_for_subscription[0]['service_type'],
                            'max_service_limit' => $details_for_subscription[0]['max_service_limit'],
                            'tax_type' => $details_for_subscription[0]['tax_type'],
                            'tax_id' => $details_for_subscription[0]['tax_id'],
                            'is_commision' => $details_for_subscription[0]['is_commision'],
                            'commission_threshold' => $details_for_subscription[0]['commission_threshold'],
                            'commission_percentage' => $details_for_subscription[0]['commission_percentage'],
                            'transaction_id' => $insert,
                            'tax_percentage' => $price[0]['tax_percentage'],
                        ];
                        $insert_subscription = insert_details($partner_subscriptions, 'partner_subscriptions');
                        if ($details_for_subscription[0]['is_commision'] == "yes") {
                            $commission = $details_for_subscription[0]['commission_percentage'];
                        } else {
                            $commission = 0;
                        }
                        update_details(['admin_commission' => $commission], ['partner_id' => $this->user_details['id']], 'partner_details');
                    }
                    $data['transaction'] = fetch_details('transactions', ['id' => $insert ?? null])[0];
                    $subscription = fetch_details('partner_subscriptions', ['id' => $insert_subscription['id']]);
                    $subscription_information['subscription_id'] = isset($subscription[0]['subscription_id']) ? $subscription[0]['subscription_id'] : "";
                    $subscription_information['isSubscriptionActive'] = isset($subscription[0]['status']) ? $subscription[0]['status'] : "deactive";
                    $subscription_information['created_at'] = isset($subscription[0]['created_at']) ? $subscription[0]['created_at'] : "";
                    $subscription_information['updated_at'] = isset($subscription[0]['updated_at']) ? $subscription[0]['updated_at'] : "";
                    $subscription_information['is_payment'] = isset($subscription[0]['is_payment']) ? $subscription[0]['is_payment'] : "";
                    $subscription_information['id'] = isset($subscription[0]['id']) ? $subscription[0]['id'] : "";
                    $subscription_information['partner_id'] = isset($subscription[0]['partner_id']) ? $subscription[0]['partner_id'] : "";
                    $subscription_information['purchase_date'] = isset($subscription[0]['purchase_date']) ? $subscription[0]['purchase_date'] : "";
                    $subscription_information['expiry_date'] = isset($subscription[0]['expiry_date']) ? $subscription[0]['expiry_date'] : "";
                    $subscription_information['name'] = isset($subscription[0]['name']) ? $subscription[0]['name'] : "";
                    $subscription_information['description'] = isset($subscription[0]['description']) ? $subscription[0]['description'] : "";
                    $subscription_information['duration'] = isset($subscription[0]['duration']) ? $subscription[0]['duration'] : "";
                    $subscription_information['price'] = isset($subscription[0]['price']) ? $subscription[0]['price'] : "";
                    $subscription_information['discount_price'] = isset($subscription[0]['discount_price']) ? $subscription[0]['discount_price'] : "";
                    $subscription_information['order_type'] = isset($subscription[0]['order_type']) ? $subscription[0]['order_type'] : "";
                    $subscription_information['max_order_limit'] = isset($subscription[0]['max_order_limit']) ? $subscription[0]['max_order_limit'] : "";
                    $subscription_information['is_commision'] = isset($subscription[0]['is_commision']) ? $subscription[0]['is_commision'] : "";
                    $subscription_information['commission_threshold'] = isset($subscription[0]['commission_threshold']) ? $subscription[0]['commission_threshold'] : "";
                    $subscription_information['commission_percentage'] = isset($subscription[0]['commission_percentage']) ? $subscription[0]['commission_percentage'] : "";
                    $subscription_information['publish'] = isset($subscription[0]['publish']) ? $subscription[0]['publish'] : "";
                    $subscription_information['tax_id'] = isset($subscription[0]['tax_id']) ? $subscription[0]['tax_id'] : "";
                    $subscription_information['tax_type'] = isset($subscription[0]['tax_type']) ? $subscription[0]['tax_type'] : "";
                    if (!empty($subscription[0])) {
                        $price = calculate_partner_subscription_price($subscription[0]['partner_id'], $subscription[0]['subscription_id'], $subscription[0]['id']);
                    }
                    $subscription_information['tax_value'] = isset($price[0]['tax_percentage']) ? $price[0]['tax_percentage'] : "";
                    $subscription_information['price_with_tax']  = isset($price[0]['price_with_tax']) ? $price[0]['price_with_tax'] : "";
                    $subscription_information['original_price_with_tax'] = isset($price[0]['original_price_with_tax']) ? $price[0]['original_price_with_tax'] : "";
                    $subscription_information['tax_percentage'] = isset($price[0]['tax_percentage']) ? $price[0]['tax_percentage'] : "";
                    $data['subscription_information'] = json_decode(json_encode($subscription_information), true);
                    $param['client_id'] = $this->userId;
                    $param['insert_id'] = $insert;
                    $param['package_id'] =  isset($subscription[0]['subscription_id']) ? $subscription[0]['subscription_id'] : "";
                    $param['net_amount'] =  isset($price[0]['price_with_tax']) ? $price[0]['price_with_tax'] : "";
                    $data['paypal_link'] = ($type == "paypal") ? base_url() . '/partner/api/v1/paypal_transaction_webview?client_id=' . $this->user_details['id'] . '&insert_id=' . $insert . '&package_id=' . $subscription[0]['subscription_id'] . '&net_amount=' . $price[0]['price_with_tax'] : "";
                    $data['paystack_link'] = ($type == "paystack") ? base_url() . '/partner/api/v1/paystack_transaction_webview?client_id=' . $this->user_details['id'] . '&insert_id=' . $insert . '&package_id=' . $subscription[0]['subscription_id'] . '&net_amount=' . $price[0]['price_with_tax'] : "";
                    $data['flutterwave_link'] = ($type == "flutterwave") ? base_url() . '/partner/api/v1/flutterwave_webview?client_id=' . $this->user_details['id'] . '&insert_id=' . $insert . '&package_id=' . $subscription[0]['subscription_id'] . '&net_amount=' . $price[0]['price_with_tax'] : "";
                    $response['error'] = false;
                    $response['data'] = $data;
                    $response['message'] = 'Transaction addedd successfully';
                }
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - add_transaction()');
        }
        return $this->response->setJSON($response);
    }
    public function paypal_transaction_webview()
    {
        $this->paypal_lib = new Paypal();
        $insert_id = $_GET['insert_id'];
        $user_id = $_GET['client_id'];
        $net_amount = $_GET['net_amount'];
        $user = fetch_details('users', ['id' => $user_id]);
        $data['user'] = $user[0];
        $data['payment_type'] = "paypal";
        $returnURL = base_url() . '/partner/api/v1/app_payment_status';
        $cancelURL = base_url() . '/partner/api/v1/app_payment_status';
        $notifyURL = base_url() . '/api/webhooks/paypal';
        $payeremail = $data['user']['email'];   // Add fields to paypal form
        $this->paypal_lib->add_field('return', $returnURL);
        $this->paypal_lib->add_field('cancel_return', $cancelURL);
        $this->paypal_lib->add_field('notify_url', $notifyURL);
        $this->paypal_lib->add_field('item_name', 'Test');
        $this->paypal_lib->add_field('custom',  $insert_id . '|' . $payeremail . '|subscription');
        $this->paypal_lib->add_field('item_number', $insert_id);
        $this->paypal_lib->add_field('amount', $net_amount);
        $this->paypal_lib->paypal_auto_form();
    }
    public function paystack_transaction_webview()
    {
        header("Content-Type: text/html");
        $insert_id = $_GET['insert_id'];
        $user_id = $_GET['client_id'];
        $net_amount = $_GET['net_amount'];
        $user_data = fetch_details('users', ['id' => $user_id])[0];
        $paystack = new Paystack();
        $paystack_credentials = $paystack->get_credentials();
        $secret_key = $paystack_credentials['secret'];
        $url = "https://api.paystack.co/transaction/initialize";
        $fields = [
            'email' =>  $user_data['email'],
            'amount' =>  $net_amount * 100,
            'currency' => $paystack_credentials['currency'],
            'callback_url' => base_url() . '/partner/api/v1/app_paystack_payment_status?payment_status=Completed',
            'metadata' => ["cancel_action" => base_url() . '/partner/api/v1/app_paystack_payment_status?payment_status=Failed', 'transaction_id' => $insert_id]
        ];
        $fields_string = http_build_query($fields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer " . $secret_key,
            "Cache-Control: no-cache",
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        $result_data = json_decode($result, true);
        if (isset($result_data['data']['authorization_url'])) {
            header('Location: ' . $result_data['data']['authorization_url']);
            exit;
        } else {
            $response = [
                'error' => true,
                'message' => 'Failed to initialize transaction',
                'data' => $result_data,
            ];
            return $this->response->setJSON($response);
        }
    }
    public function app_paystack_payment_status()
    {
        $data = $_GET;
        if (isset($data['reference']) && isset($data['trxref']) && isset($data['payment_status'])) {
            $response['error'] = false;
            $response['message'] = "Payment Completed Successfully";
            $response['payment_status'] = "Completed";
            $response['data'] = $data;
        } elseif (isset($data['transaction_id']) && isset($data['payment_status'])) {
            $response['error'] = true;
            $response['message'] = "Payment Cancelled / Declined ";
            $response['payment_status'] = "Failed";
            $response['data'] = $_GET;
        }
        print_r(json_encode($response));
    }
    public function app_payment_status()
    {
        $paypalInfo = $_GET;
        if (!empty($paypalInfo) && isset($_GET['st']) && strtolower($_GET['st']) == "completed") {
            $response['error'] = false;
            $response['message'] = "Payment Completed Successfully";
            $response['data'] = $paypalInfo;
        } elseif (!empty($paypalInfo) && isset($_GET['st']) && strtolower($_GET['st']) == "authorized") {
            $response['error'] = false;
            $response['message'] = "Your payment is has been Authorized successfully. We will capture your transaction within 30 minutes, once we process your order. After successful capture coins wil be credited automatically.";
            $response['data'] = $paypalInfo;
        } elseif (!empty($paypalInfo) && isset($_GET['st']) && strtolower($_GET['st']) == "Pending") {
            $response['error'] = false;
            $response['message'] = "Your payment is pending and is under process. We will notify you once the status is updated.";
            $response['data'] = $paypalInfo;
        } else {
            $order_id = order_decrypt($_GET['order_id']);
            update_details(['payment_status' => 2], ['id' => $order_id[2]], 'orders');
            update_details(['status' => 'cancelled'], ['id' => $order_id[2]], 'orders');
            $data = [
                'transaction_type' => 'transaction',
                'user_id' => $order_id[0],
                'partner_id' => "",
                'order_id' => $order_id[2],
                'type' => 'paypal',
                'txn_id' => "",
                'amount' => $order_id[1],
                'status' => 'failed',
                'currency_code' => "",
                'message' => 'Booking is cancelled',
            ];
            $insert_id = add_transaction($data);
            $response['error'] = true;
            $response['message'] = "Payment Cancelled / Declined ";
            $response['data'] = $_GET;
        }
        print_r(json_encode($response));
    }
    public function razorpay_create_order()
    {
        try {
            $validation = \Config\Services::validation();
            $validation->setRules(
                [
                    'subscription_id' => 'required|numeric',
                ]
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            $subscription_id = $this->request->getPost('subscription_id');
            if ($this->request->getPost('subscription_id') && !empty($this->request->getPost('subscription_id'))) {
                $where['s.id'] = $this->request->getPost('subscription_id');
            }
            $subscription = new Subscription_model();
            $subscription_detail = $subscription->list(true, '', 10, 0, 's.id', 'DESC', $where);
            $settings = get_settings('payment_gateways_settings', true);
            if (!empty($subscription_detail) && !empty($settings)) {
                $currency = $settings['razorpay_currency'];
                $price = ($subscription_detail['data'][0]['discount_price'] == "0") ? $subscription_detail['data'][0]['price'] : $subscription_detail['data'][0]['discount_price'];
                $amount = intval($price * 100);
                $create_order = $this->razorpay->create_order($amount, $subscription_id, $currency);
                if (!empty($create_order)) {
                    $response = [
                        'error' => false,
                        'message' => 'razorpay order created',
                        'data' => $create_order,
                    ];
                } else {
                    $response = [
                        'error' => true,
                        'message' => 'razorpay order not created',
                        'data' => [],
                    ];
                }
            } else {
                $response = [
                    'error' => true,
                    'message' => 'details not found"',
                    'data' => [],
                ];
            }
            return $this->response->setJSON($response);
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - razorpay_create_order()');
            return $this->response->setJSON($response);
        }
    }
    public function get_subscription_history()
    {
        try {
            $request = \Config\Services::request();
            $limit = !empty($this->request->getPost('limit')) ? $this->request->getPost('limit') : 10;
            $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
            $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
            $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'DESC';
            $user_id = $this->user_details['id'];
            if (!exists(['id' => $user_id], 'users')) {
                $response = [
                    'error' => true,
                    'message' => 'Invalid User Id.',
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            $res = fetch_details('partner_subscriptions', ['partner_id' => $user_id, 'status' => 'deactive', 'is_payment' => '1'], '', $limit, $offset, $sort, $order);
            foreach ($res as $key => $row) {
                $price = calculate_partner_subscription_price($row['partner_id'], $row['subscription_id'], $row['id']);
                $res[$key]['tax_value'] = $price[0]['tax_value'];
                $res[$key]['price_with_tax'] = $price[0]['price_with_tax'];
                $res[$key]['original_price_with_tax'] = $price[0]['original_price_with_tax'];
                $res[$key]['tax_percentage'] = $price[0]['tax_percentage'];
                $res[$key]['isSubscriptionActive'] = $row['status'];
                unset($res[$key]['status']);
            }
            $total = fetch_details('partner_subscriptions', ['partner_id' => $user_id, 'status' => 'deactive', 'is_payment' => '1']);
            $total = count($total);
            if (!empty($res)) {
                $response = [
                    'error' => false,
                    'message' => 'Subscription history recieved successfully.',
                    'total' => $total,
                    'data' => $res,
                ];
                return $this->response->setJSON($response);
            } else {
                $response = [
                    'error' => true,
                    'message' => 'No data found',
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - get_subscription_history()');
            return $this->response->setJSON($response);
        }
    }
    public function get_booking_settle_manegement_history()
    {


        $limit = $this->request->getPost('limit') ?: 10;
        $offset = $this->request->getPost('offset') ?: 0;
        $sort = $this->request->getPost('sort') ?: 'id';
        $order = $this->request->getPost('order') ?: 'DESC';
        $search = $this->request->getPost('search') ?: '';
        $user_id = $this->user_details['id'];
        if (!exists(['id' => $user_id], 'users')) {
            $response = [
                'error' => true,
                'message' => 'Invalid User Id.',
                'data' => [],
            ];
            return $this->response->setJSON($response);
        }
        $where = ['sc.provider_id' => $user_id];
        $Settlement_CashCollection_history_model = new Settlement_CashCollection_history_model();
        $data = $Settlement_CashCollection_history_model->list($where, 'no', true, $limit, $offset, $sort, $order, $search);
        $for_total = $Settlement_CashCollection_history_model->list($where, 'no', true, 0, 0, $sort, $order, $search);
        if (!empty($data)) {
            $response = [
                'error' => false,
                'message' => 'Booking payment history recieved successfully.',
                'total' => count($for_total),
                'data' => $data,
            ];
            return $this->response->setJSON($response);
        } else {
            $response = [
                'error' => true,
                'message' => 'No data found',
                'data' => [],
            ];
            return $this->response->setJSON($response);
        }
    }
    public function contact_us_api()
    {
        $validation = \Config\Services::validation();
        $validation->setRules(
            [
                'name' => 'required',
                'subject' => 'required',
                'message' => 'required',
                'email' => 'required'
            ]
        );
        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            $response = [
                'error' => true,
                'message' => $errors,
                'data' => [],
            ];
            return $this->response->setJSON($response);
        }
        $name = $_POST['name'];
        $subject = $_POST['subject'];
        $message = $_POST['message'];
        $email = $_POST['email'];
        $admin_contact_query = [
            'name' => $name,
            'subject' => $subject,
            'message' => $message,
            'email' => isset($email) ? $email : "0",
        ];
        insert_details($admin_contact_query, 'admin_contact_query');
        $response['error'] = false;
        $response['message'] = "Query send successfully";
        $response['data'] = $admin_contact_query;
        return $this->response->setJSON($response);
    }
    public function send_chat_message()
    {
        try {
            $validation = \Config\Services::validation();
            $validation->setRules(
                [
                    'receiver_type' => 'required'
                ]
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            $attachments = isset($_FILES['attachment']) ? $_FILES['attachment'] : null;
            if (!$attachments) {
                $validation = \Config\Services::validation();
                $validation->setRules(
                    [
                        'message' => 'required',
                    ]
                );
                if (!$validation->withRequest($this->request)->run()) {
                    $errors = $validation->getErrors();
                    $response = [
                        'error' => true,
                        'message' => $errors,
                        'data' => [],
                    ];
                    return $this->response->setJSON($response);
                }
            }
            $message = $this->request->getPost('message') ?? "";
            $receiver_id = $this->request->getPost('receiver_id');
            if ($receiver_id == null) {
                $user_group = fetch_details('users_groups', ['group_id' => '1']);
                $receiver_id = end($user_group)['group_id'];
            }
            $receiver_type = $this->request->getPost('receiver_type');
            $sender_id =  $this->user_details['id'];
            $booking_id =  $this->request->getPost('booking_id');
            if (isset($booking_id)) {
                $e_id_data = fetch_details('enquiries', ['customer_id' => $receiver_id, 'userType' => 2, 'booking_id' => $booking_id]);
                $e_id = empty($e_id_data) ? add_enquiry_for_chat("customer", $_POST['receiver_id'], true, $_POST['booking_id']) : $e_id_data[0]['id'];
            } else {
                // if ($booking_id == null) {
                //     if ($receiver_type == "0") {
                //         $enquiry = fetch_details('enquiries', ['customer_id' => null, 'userType' => 1, 'booking_id' => NULL, 'provider_id' => $sender_id]);
                //         if (empty($enquiry[0])) {
                //             $user = fetch_details('users', ['id' => $sender_id], ['username'])[0];
                //             $data['title'] =  $user['username'] . '_query';
                //             $data['status'] =  1;
                //             $data['userType'] =  1;
                //             $data['customer_id'] = null;
                //             $data['provider_id'] = $sender_id;
                //             $data['date'] =  now();
                //             $store = insert_details($data, 'enquiries');
                //             $e_id = $store['id'];
                //         } else {
                //             $e_id = $enquiry[0]['id'];
                //         }
                //     } else {
                //         $enquiry = fetch_details('enquiries', ['customer_id' => $receiver_id, 'userType' => 2, 'booking_id' => NULL, 'provider_id' => $sender_id]);
                //         // print_r($enquiry);
                //         // die;
                //         if (empty($enquiry[0])) {
                //             $customer = fetch_details('users', ['id' => $sender_id], ['username'])[0];
                //             $data['title'] =  $customer['username'] . '_query';
                //             $data['status'] =  1;
                //             $data['userType'] =  2;
                //             $data['customer_id'] = $receiver_id;
                //             $data['provider_id'] = $sender_id;
                //             $data['date'] =  now();
                //             $store = insert_details($data, 'enquiries');
                //             $e_id = $store['id'];
                //         } else {
                //             $e_id = $enquiry[0]['id'];
                //         }
                //     }
                // }

                if ($booking_id == null) {
                    if ($receiver_type == "0") {
                        $enquiry = fetch_details('enquiries', ['customer_id' => null, 'userType' => 1, 'booking_id' => NULL, 'provider_id' => $sender_id]);
                        if (empty($enquiry[0])) {
                            $provider = fetch_details('users', ['id' => $sender_id], ['username'])[0];
                            $data['title'] =  $provider['username'] . '_query';
                            $data['status'] =  1;
                            $data['userType'] =  1;
                            $data['customer_id'] = null;
                            $data['provider_id'] = $sender_id;
                            $data['date'] =  now();
                            $store = insert_details($data, 'enquiries');
                            $e_id = $store['id'];
                        } else {
                            $e_id = $enquiry[0]['id'];
                        }
                    } else if ($receiver_type == "2") {
                        $enquiry = fetch_details('enquiries', ['customer_id' => $receiver_id, 'userType' => 2, 'booking_id' => NULL, 'provider_id' => $sender_id]);
                        if (empty($enquiry[0])) {
                            $customer = fetch_details('users', ['id' => $sender_id], ['username'])[0];
                            $data['title'] =  $customer['username'] . '_query';
                            $data['status'] =  1;
                            $data['userType'] =  2;
                            $data['customer_id'] = $receiver_id;
                            $data['provider_id'] = $sender_id;
                            $data['date'] =  now();
                            $store = insert_details($data, 'enquiries');
                            $e_id = $store['id'];
                        } else {
                            $e_id = $enquiry[0]['id'];
                        }
                    }
                }
            }
            $last_date = getLastMessageDateFromChat($e_id);
            $attachment_image = null;
            $is_file = false;
            if (!empty($_FILES['attachment']['name'])) {
                $attachment_image = $_FILES['attachment'];
                $is_file = true;
            }
            $booking_id = $this->request->getPost('booking_id') ?? null;
            $data = insert_chat_message_for_chat($sender_id, $receiver_id, $message, $e_id, 1, $receiver_type, date('Y-m-d H:i:s'), $is_file, $attachment_image, $booking_id);
            if (isset($booking_id)) {

                $new_data = getSenderReceiverDataForChatNotification($sender_id, $receiver_id, $data['id'], $last_date, 'provider_booking', 'yes');

                send_app_chat_notification($new_data['sender_details']['username'], $message, $receiver_id, '', 'new_chat', $new_data);
                send_customer_web_chat_notification('Booking ', $message, $receiver_id, '', 'new_chat', $new_data);
                // send_customer_web_chat_notification('Customer Support', $message, $receiver_id, '', 'new_chat', $new_data);
            } else if ($receiver_type == 2) {
                $new_data = getSenderReceiverDataForChatNotification($sender_id, $receiver_id, $data['id'], $last_date, 'provider', 'yes');
                send_app_chat_notification($new_data['sender_details']['username'], $message, $receiver_id, '', 'new_chat', $new_data);
                send_customer_web_chat_notification('Customer Support', $message, $receiver_id, '', 'new_chat', $new_data);
            } else if ($receiver_type == 0) {
                $new_data = getSenderReceiverDataForChatNotification($sender_id, $receiver_id, $data['id'], $last_date, 'admin');
                send_panel_chat_notification('Check New Messages', $message, $receiver_id, '', 'new_chat', $new_data);
            }
            return response_helper('Sent message successfully ', false, $data, 200);
        } catch (\Throwable $th) {

            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - send_chat_message()');
            return $this->response->setJSON($response);
        }
    }
    public function get_chat_history()
    {
        try {
            $validation = service('validation');
            $validation->setRules([
                'type' => 'required',
            ]);
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            $type = $this->request->getPost('type');
            $e_id = $this->request->getPost('e_id');


            $limit = $this->request->getPost('limit') ?: 10;
            $offset = $this->request->getPost('offset') ?: 0;
            $sort = $this->request->getPost('sort') ?: 'id';
            $order = $this->request->getPost('order') ?: 'DESC';
            $search = $this->request->getPost('search') ?: '';
            $db = \Config\Database::connect();
            $current_user_id = $this->user_details['id'];
            if ($type == "0") {
                $e_id_data = fetch_details('enquiries', ['customer_id' => NULL, 'userType' => 1, 'provider_id' => $current_user_id, 'booking_id' => null]);
                if (!empty($e_id_data)) {
                    $e_id = $e_id_data[0]['id'];
                    $countBuilder = $db->table('chats c');
                    $countBuilder->select('COUNT(*) as total')
                        ->where('c.booking_id', null)
                        ->where('c.e_id', $e_id);
                    $totalRecords = $countBuilder->get()->getRow()->total;
                    $mainBuilder = $db->table('chats c');
                    $mainBuilder->select('c.*')
                        ->where('c.e_id', $e_id)
                        ->where('c.booking_id', null)
                        ->limit($limit, $offset);
                    $chat_record = $mainBuilder->orderBy('c.created_at', 'DESC')->get()->getResultArray();
                    $disk = fetch_current_file_manager();
                    foreach ($chat_record as $key => $row) {
                        $new_data = getSenderReceiverDataForChatNotification($row['sender_id'], $row['receiver_id'], $row['id'], $row['created_at'], 'admin');
                        $chat_record[$key]['sender_details'] = $new_data['sender_details'];
                        $chat_record[$key]['receiver_details'] = $new_data['receiver_details'];
                        if (!empty($chat_record[$key]['file'])) {
                            $decoded_files = json_decode($chat_record[$key]['file'], true);
                            if (is_array($decoded_files)) {
                                $tempFiles = [];
                                foreach ($decoded_files as $data) {
                                    if ($disk == 'local_server') {
                                        $file = base_url('public/uploads/chat_attachment/' . $data['file']);
                                    } elseif ($disk == 'aws_s3') {
                                        $file = fetch_cloud_front_url('chat_attachment', $data['file']);
                                    } else {
                                        $file = base_url('public/uploads/chat_attachment/' . $data['file']);
                                    }
                                    $tempFiles[] = [
                                        'file' => $file,
                                        'file_type' => $data['file_type'],
                                        'file_name' => $data['file_name'],
                                        'file_size' => $data['file_size'],
                                    ];
                                }
                                $chat_record[$key]['file'] = $tempFiles;
                            } else {
                                $chat_record[$key]['file'] = [];
                            }
                        } else {
                            $chat_record[$key]['file'] = [];
                        }
                    }
                    return response_helper('Retrived successfully ', false, $chat_record, 200, ['total' => $totalRecords]);
                } else {
                    return response_helper('No data Found ', false, [], 200, ['total' => 0]);
                }
            } else if ($type = "2") {
                if ($this->request->getPost('booking_id') != null) {
                    $booking = fetch_details('orders', ['id' => $this->request->getPost('booking_id')], ['user_id']);
                }
                if (!empty($booking)) {
                    $e_id_data = fetch_details('enquiries', ['booking_id' => $this->request->getPost('booking_id'), 'customer_id' => $booking[0]['user_id']]);
                    if (!empty($e_id_data)) {
                        $e_id = $e_id_data[0]['id'];
                        $booking_id = $e_id_data[0]['booking_id'];
                        $countBuilder = $db->table('chats c');
                        $countBuilder->select('COUNT(*) as total')
                            ->where('c.e_id', $e_id)
                            ->where('c.booking_id', $booking_id);
                        $totalRecords = $countBuilder->get()->getRow()->total;
                        $mainBuilder = $db->table('chats c');
                        $mainBuilder->select('c.*')
                            ->where('c.e_id', $e_id)
                            ->where('c.booking_id', $booking_id)
                            ->limit($limit, $offset);
                        $chat_record = $mainBuilder->orderBy('c.created_at', 'DESC')->get()->getResultArray();
                        $disk = fetch_current_file_manager();
                        foreach ($chat_record as $key => $row) {
                            $new_data = getSenderReceiverDataForChatNotification($row['sender_id'], $row['receiver_id'], $row['id'], $row['created_at'], 'admin');
                            $chat_record[$key]['sender_details'] = $new_data['sender_details'];
                            $chat_record[$key]['receiver_details'] = $new_data['receiver_details'];
                            if (!empty($chat_record[$key]['file'])) {
                                $decoded_files = json_decode($chat_record[$key]['file'], true);
                                if (is_array($decoded_files)) {
                                    $tempFiles = [];
                                    foreach ($decoded_files as $data) {
                                        if ($disk == 'local_server') {
                                            $file = base_url('public/uploads/chat_attachment/' . $data['file']);
                                        } elseif ($disk == 'aws_s3') {
                                            $file = fetch_cloud_front_url('chat_attachment', $data['file']);
                                        } else {
                                            $file = base_url('public/uploads/chat_attachment/' . $data['file']);
                                        }
                                        $tempFiles[] = [
                                            'file' => $file,
                                            'file_type' => $data['file_type'],
                                            'file_name' => $data['file_name'],
                                            'file_size' => $data['file_size'],
                                        ];
                                    }
                                    $chat_record[$key]['file'] = $tempFiles;
                                } else {
                                    $chat_record[$key]['file'] = [];
                                }
                            } else {
                                $chat_record[$key]['file'] = [];
                            }
                        }
                        return response_helper('Retrived successfully ', false, $chat_record, 200, ['total' => $totalRecords]);
                    } else {
                        return response_helper('No data found ', false, [], 200, ['total' => 0]);
                    }
                } else {
                    if ($this->request->getPost('booking_id') == null) {
                        $customer_id = $this->request->getPost('customer_id');
                        $e_id_data = fetch_details('enquiries', ['booking_id' => NULL, 'customer_id' => $customer_id, 'provider_id' => $current_user_id]);
                        $e_id = $e_id_data[0]['id'];
                        $countBuilder = $db->table('chats c');
                        $countBuilder->select('COUNT(*) as total')
                            ->where('c.e_id', $e_id);
                        $totalRecords = $countBuilder->get()->getRow()->total;
                        $mainBuilder = $db->table('chats c');
                        $mainBuilder->select('c.*')
                            ->where('c.e_id', $e_id)
                            ->limit($limit, $offset);
                        $chat_record = $mainBuilder->orderBy('c.created_at', 'DESC')->get()->getResultArray();
                        foreach ($chat_record as $key => $row) {
                            $new_data = getSenderReceiverDataForChatNotification($row['sender_id'], $row['receiver_id'], $row['id'], $row['created_at'], 'provider_booking', 'yes');
                            $chat_record[$key]['sender_details'] = $new_data['sender_details'];
                            $chat_record[$key]['receiver_details'] = $new_data['receiver_details'];
                            if (!empty($chat_record[$key]['file'])) {
                                $decoded_files = json_decode($chat_record[$key]['file'], true);
                                if (is_array($decoded_files)) {
                                    $tempFiles = [];
                                    foreach ($decoded_files as $data) {
                                        if ($chat_record[$key]['disk'] == 'local_server') {
                                            $file = base_url('public/uploads/chat_attachment/' . $data['file']);
                                        } elseif ($chat_record[$key]['disk'] == 'aws_s3') {
                                            $file = fetch_cloud_front_url('chat_attachment', $data['file']);
                                        } else {
                                            $file = base_url('public/uploads/chat_attachment/' . $data['file']);
                                        }
                                        $tempFiles[] = [
                                            'file' => $file,
                                            'file_type' => $data['file_type'],
                                            'file_name' => $data['file_name'],
                                            'file_size' => $data['file_size'],
                                        ];
                                    }
                                    $chat_record[$key]['file'] = $tempFiles;
                                } else {
                                    $chat_record[$key]['file'] = [];
                                }
                            } else {
                                $chat_record[$key]['file'] = [];
                            }
                        }
                        return response_helper('Retrived successfully ', false, $chat_record, 200, ['total' => $totalRecords]);
                    }
                    return response_helper('No Booking found', false, [], 200, ['total' => 0]);
                }
            }
        } catch (\Throwable $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - get_chat_history()');
            return $this->response->setJSON($response);
        }
    }
    public function get_chat_customers_list()
    {
        try {


            $limit = $this->request->getPost('limit') ?: 10;
            $offset = $this->request->getPost('offset') ?: 0;
            $sort = $this->request->getPost('sort') ?: 'id';
            $order = $this->request->getPost('order') ?: 'DESC';
            $search = $this->request->getPost('search') ?: '';
            $db = \Config\Database::connect();
            $builder = $db->table('users u');
            $builder->select(' us.id as customer_id,us.username as customer_name,us.image as image,MAX(c.created_at) AS last_chat_date, c.booking_id, o.status as booking_status,')
                ->join('chats c', "(c.sender_id = u.id AND c.sender_type = 1) OR (c.receiver_id = u.id AND c.receiver_type = 1)")
                ->join('orders o', "o.id = c.booking_id")
                ->join('users us', "us.id = o.user_id")
                ->where('o.partner_id', $this->user_details['id'])
                ->groupBy('c.booking_id')
                ->orderBy('last_chat_date', 'DESC');
            $totalCustomersQuery1 = $builder->countAllResults(false);
            $customers_with_chats = $builder->get()->getResultArray();

            $disk = fetch_current_file_manager();
            foreach ($customers_with_chats as $key => $row) {
                if (isset($row['image'])) {
                    if ($disk == "local_server") {
                        $imagePath = $row['image'];
                        $customers_with_chats[$key]['image'] = fix_provider_path($imagePath);
                    } else if ($disk == "aws_s3") {
                        $customers_with_chats[$key]['image'] = fetch_cloud_front_url('profile', $row['image']);
                    } else {
                        $imagePath = $row['image'];
                        $customers_with_chats[$key]['image'] = fix_provider_path($imagePath);
                    }
                }
            }


            $builder1 = $db->table('users u');
            $builder1->select(' us.id as customer_id,us.username as customer_name,us.image as image,MAX(c.created_at) AS last_chat_date, c.booking_id,')
                ->join('chats c', "(c.sender_id = u.id AND c.sender_type = 1) OR (c.receiver_id = u.id AND c.receiver_type = 1)")
                ->join('enquiries e', "e.id = c.e_id")
                ->join('users us', "us.id = e.customer_id")
                ->where('e.provider_id', $this->user_details['id'])
                ->groupBy('e.customer_id')
                ->orderBy('last_chat_date', 'DESC');
            $totalCustomersQuery2 = $builder1->countAllResults(false);
            $customer_pre_booking_queries = $builder1->get()->getResultArray();
            // print_r($db->getLastQuery()); die;
            foreach ($customer_pre_booking_queries as $key => $row) {
                if (isset($row['image'])) {
                    if ($disk == "local_server") {
                        $imagePath = $row['image'];
                        $customer_pre_booking_queries[$key]['image'] = fix_provider_path($imagePath);
                    } else if ($disk == "aws_s3") {
                        $customer_pre_booking_queries[$key]['image'] = fetch_cloud_front_url('profile', $row['image']);
                    } else {
                        $imagePath = $row['image'];
                        $customer_pre_booking_queries[$key]['image'] = fix_provider_path($imagePath);
                    }
                    $customer_pre_booking_queries[$key]['order_id'] = "";
                    $customer_pre_booking_queries[$key]['order_status'] = "";
                }
            }

            //Ronik: If limit and offset are greater than total records, then array slice empty array is returned.
            $merged_array = array_merge($customers_with_chats, $customer_pre_booking_queries);
            $totalRecords = $totalCustomersQuery1 + $totalCustomersQuery2;
            $merged_array = array_slice($merged_array, $offset, $limit);

            usort($merged_array, function ($a, $b) {
                return ($b['last_chat_date'] <=> $a['last_chat_date']);
            });
            return response_helper('Retrived successfully ', false, $merged_array, 200, ['total' => $totalRecords]);
        } catch (\Throwable $th) {

            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - get_chat_customers_list()');
            return $this->response->setJSON($response);
        }
    }
    public function get_user_info()
    {
        // log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => ", date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - get_user_info()');

        try {
            $db      = \Config\Database::connect();
            $builder = $db->table('users u');
            // $builder->select('u.*, ug.group_id, (SELECT COUNT(*) FROM custom_job_requests cjr WHERE cjr.user_id = u.id) as total_job_request')->join('users_groups ug', 'ug.user_id = u.id')
            //     ->where('ug.group_id', 3)
            //     ->where(['u.id' => $this->user_details['id']]);
            $builder->select('u.*, ug.group_id, (
                    SELECT COUNT(DISTINCT cj.id)
                    FROM custom_job_requests cj
                    LEFT JOIN custom_job_provider cjp ON cjp.custom_job_request_id = cj.id
                    WHERE cj.status = "pending"
                    AND cjp.partner_id = u.id
                    AND NOT EXISTS (
                        SELECT 1 FROM partner_bids pb
                        WHERE pb.custom_job_request_id = cj.id
                        AND pb.partner_id = u.id
                    )
                ) as total_job_request')
                ->join('users_groups ug', 'ug.user_id = u.id')
                ->where('ug.group_id', 3)
                ->where('u.id', $this->user_details['id']);

            $userCheck = $builder->get()->getResultArray();
            // print_r($db->getLastQuery()); die;
            if (empty($userCheck)) {
                $response = [
                    'error' => true,
                    'message' => 'Oops, it seems like this number isn’t registered. Please register to use our services.',
                ];
                return $this->response->setJSON($response);
            }
            $data = array();
            array_push($this->user_data, "api_key");
            $data = fetch_details('users', ['id' => $userCheck[0]['id']], ['id', 'username',  'country_code', 'phone', 'email', 'fcm_id', 'image', 'api_key'])[0];
            $getData = fetch_partner_formatted_data($data['id']);
            // $getData['provder_information']['total_job_request'] = $userCheck[0]['total_job_request'];


            //custom job start
            $partner_id = $this->user_details['id'];

            $db = \Config\Database::connect();

            $custom_job_categories = fetch_details('partner_details', ['partner_id' => $this->userId], ['custom_job_categories', 'is_accepting_custom_jobs']);
            $partner_categoried_preference = !empty($custom_job_categories) &&
                isset($custom_job_categories[0]['custom_job_categories']) &&
                !empty($custom_job_categories[0]['custom_job_categories']) ?
                json_decode($custom_job_categories[0]['custom_job_categories']) : [];


            $builder = $db->table('custom_job_requests cj')
                ->select('cj.*, u.username, u.image, c.id as category_id, c.name as category_name, c.image as category_image')
                ->join('users u', 'u.id = cj.user_id')
                ->join('categories c', 'c.id = cj.category_id')
                ->where('cj.status', 'pending')
                ->where("(SELECT COUNT(1) FROM partner_bids pb WHERE pb.custom_job_request_id = cj.id AND pb.partner_id = $partner_id) = 0");
            if (!empty($partner_categoried_preference)) {
                $builder->whereIn('cj.category_id', $partner_categoried_preference);
            }
            $builder->orderBy('cj.id', 'DESC');
            $custom_job_requests = $builder->get()->getResultArray();


            $filteredJobs = [];
            foreach ($custom_job_requests as $row) {
                $did_partner_bid = fetch_details('partner_bids', [
                    'custom_job_request_id' => $row['id'],
                    'partner_id' => $partner_id,
                ]);
                if (empty($did_partner_bid)) {
                    $check = fetch_details('custom_job_provider', [
                        'partner_id' => $partner_id,
                        'custom_job_request_id' => $row['id'],
                    ]);
                    if (!empty($check)) {
                        $filteredJobs[] = $row;
                    }
                }
            }
            if (!empty($filteredJobs)) {
                foreach ($filteredJobs as &$job) {
                    if (!empty($job['image'])) {
                        $job['image'] = base_url('public/backend/assets/profiles/' . $job['image']);
                    } else {
                        $job['image'] = base_url('public/backend/assets/profiles/default.png');
                    }
                }
            }
            $getData['provder_information']['total_job_request'] = count($filteredJobs);
            //custom job end 
            $response = [
                'error' => false,
                'message' => 'Data fetched successfully',
                'data' => $getData
            ];
            return $this->response->setJSON($response);
        } catch (\Throwable $th) {
            print_r($th); die;
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            log_the_responce($this->request->header('Authorization') . '   Params passed :: ' . json_encode($_POST) . " Issue => " . $th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - get_user_info()');
            return $this->response->setJSON($response);
        }
    }
    public function verify_otp()
    {
        $validation = service('validation');
        $validation->setRules([
            'otp' => 'required',
            'mobile' => 'required',
        ]);
        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            $response = [
                'error' => true,
                'message' => $errors,
                'data' => [],
            ];
            return $this->response->setJSON($response);
        }
        $mobile = $this->request->getPost('mobile');
        $otp = $this->request->getPost('otp');
        $country_code = $this->request->getPost('country_code');
        $data = fetch_details('otps', ['mobile' => $country_code . $mobile, 'otp' => $otp]);
        if (!empty($data)) {
            $time = $data[0]['created_at'];
            $time_expire = checkOTPExpiration($time);
            if ($time_expire['error'] == 1) {
                $response['error'] = true;
                $response['message'] = $time_expire['message'];
                return $this->response->setJSON($response);
            }
        }
        if (!empty($data)) {
            $response['error'] = false;
            $response['message'] = "OTP verified";
            return $this->response->setJSON($response);
        } else {
            $response['error'] = true;
            $response['message'] = "OTP not verified";
            return $this->response->setJSON($response);
        }
    }
    public function resend_otp()
    {
        $validation = \Config\Services::validation();
        $validation->setRules([
            'mobile' => 'required',
        ]);
        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            $response = [
                'error' => true,
                'message' => $errors,
                'data' => [],
            ];
            return $this->response->setJSON($response);
        }
        $request = \Config\Services::request();
        $mobile = $request->getPost('mobile');
        $authentication_mode = get_settings('general_settings', true);
        if ($authentication_mode['authentication_mode'] == "sms_gateway") {
            $is_exist = fetch_details('otps', ['mobile' => $mobile]);
            if (isset($mobile) &&  empty($is_exist)) {
                $mobile_data = array(
                    'mobile' => $mobile,
                    'created_at' => date('Y-m-d H:i:s'),
                );
                insert_details($mobile_data, 'otps');
            }
            $otp = random_int(100000, 999999);
            $send_otp_response = set_user_otp($mobile, $otp, $mobile);
            if ($send_otp_response['error'] == false) {
                $response['error'] = false;
                $response['message'] = "OTP send successfully";
            } else {
                $response['error'] = true;
                $response['message'] = $send_otp_response['message'];
            }
            return $this->response->setJSON($response);
        }
    }
    public function flutterwave_webview()
    {
        header("Content-Type: application/json");
        $insert_id = $_GET['insert_id'];
        $user_id = $_GET['client_id'];
        $net_amount = $_GET['net_amount'];
        $settings = get_settings('general_settings', true);
        $logo = base_url("public/uploads/site/" . $settings['logo']);
        $user = fetch_details('users', ['id' => $user_id]);
        if (empty($user)) {
            $response = [
                'error' => true,
                'message' => "User not found!",
            ];
            return $this->response->setJSON($response);
        }
        $flutterwave = new Flutterwave();
        $flutterwave_credentials = $flutterwave->get_credentials();
        $currency = $flutterwave_credentials['currency_code'] ?? "NGN";
        $data = [
            'tx_ref' => "eDemand-" . time() . "-" . rand(1000, 9999),
            'amount' => $net_amount,
            'currency' => $currency,
            'redirect_url' => base_url('partner/api/v1/flutterwave_payment_status'),
            'payment_options' => 'card',
            'meta' => [
                'user_id' => $user_id,
                'transaction_id' => $insert_id,
            ],
            'customer' => [
                'email' => (!empty($user[0]['email'])) ? $user[0]['email'] : $settings['support_email'],
                'phonenumber' => $user[0]['phone'] ?? '',
                'name' => $user[0]['username'] ?? '',
            ],
            'customizations' => [
                'title' => $settings['company_title'] . " Payments",
                'description' => "Online payments on " . $settings['company_title'],
                'logo' => (!empty($logo)) ? $logo : "",
            ],
        ];
        $payment = $flutterwave->create_payment($data);
        if (!empty($payment)) {
            $payment = json_decode($payment, true);
            if (isset($payment['status']) && $payment['status'] == 'success' && isset($payment['data']['link'])) {
                $response = [
                    'error' => false,
                    'message' => "Payment link generated. Follow the link to make the payment!",
                    'link' => $payment['data']['link'],
                ];
                header('Location: ' . $payment['data']['link']);
                exit;
                $link = $payment['data']['link'];
            } else {
                $link = "";
            }
        } else {
            $link = "";
        }
        return $link;
    }
    public function flutterwave_payment_status()
    {
        if (isset($_GET['transaction_id']) && !empty($_GET['transaction_id'])) {
            $transaction_id = $_GET['transaction_id'];
            $flutterwave = new Flutterwave();
            $transaction = $flutterwave->verify_transaction($transaction_id);
            if (!empty($transaction)) {
                $transaction = json_decode($transaction, true);
                if ($transaction['status'] == 'error') {
                    $response['error'] = true;
                    $response['message'] = $transaction['message'];
                    $response['amount'] = 0;
                    $response['status'] = "failed";
                    $response['currency'] = "NGN";
                    $response['transaction_id'] = $transaction_id;
                    $response['reference'] = "";
                    print_r(json_encode($response));
                    return false;
                }
                if ($transaction['status'] == 'success' && $transaction['data']['status'] == 'successful') {
                    $response['error'] = false;
                    $response['message'] = "Payment has been completed successfully";
                    $response['amount'] = $transaction['data']['amount'];
                    $response['currency'] = $transaction['data']['currency'];
                    $response['status'] = $transaction['data']['status'];
                    $response['transaction_id'] = $transaction['data']['id'];
                    $response['reference'] = $transaction['data']['tx_ref'];
                    print_r(json_encode($response));
                    return false;
                } else if ($transaction['status'] == 'success' && $transaction['data']['status'] != 'successful') {
                    $response['error'] = true;
                    $response['message'] = "Payment is " . $transaction['data']['status'];
                    $response['amount'] = $transaction['data']['amount'];
                    $response['currency'] = $transaction['data']['currency'];
                    $response['status'] = $transaction['data']['status'];
                    $response['transaction_id'] = $transaction['data']['id'];
                    $response['reference'] = $transaction['data']['tx_ref'];
                    print_r(json_encode($response));
                    return false;
                }
            } else {
                $response['error'] = true;
                $response['message'] = "Transaction not found";
                print_r(json_encode($response));
            }
        } else {
            $response['error'] = true;
            $response['message'] = "Invalid request!";
            print_r(json_encode($response));
            return false;
        }
    }
    public function apply_for_custom_job()
    {
        try {
            $validation = \Config\Services::validation();
            $validation->setRules([
                'custom_job_request_id' => 'required',
                'counter_price' => 'required',
                'cover_note' => 'required',
                'duration' => 'required',
            ]);
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            $data['partner_id'] = $this->user_details['id'];
            $data['counter_price'] = $_POST['counter_price'];
            $data['note'] = $_POST['cover_note'];
            $data['duration'] = $_POST['duration'];
            $data['custom_job_request_id'] = $_POST['custom_job_request_id'];
            $data['status'] = 'pending';
            $data['status'] = 'pending';
            if (isset($_POST['tax_id']) && $_POST['tax_id'] != "") {
                $data['tax_id'] = $_POST['tax_id'] ?? "";
                $tax_details = fetch_details('taxes', ['id' => $_POST['tax_id']]);
                $data['tax_id'] = $tax_details[0]['id'];
                $data['tax_percentage'] = $tax_details[0]['percentage'];
                $data['tax_amount'] = ($_POST['counter_price'] * $tax_details[0]['percentage']) / 100;
            } else {
                $data['tax_id'] = "";
                $data['tax_percentage'] = "";
                $data['tax_amount'] = 0;
            }
            $insert = insert_details($data, 'partner_bids');
            if ($insert) {
                $fetch_custom_job_Data = fetch_details('custom_job_requests', ['id' => $_POST['custom_job_request_id']]);
                $fcmMsg = array(
                    'title' => $this->trans->bidRecevidedTitle,
                    'body' => $this->trans->bidRecevidedMessage . ' on ' . $fetch_custom_job_Data[0]['service_title'],
                    'type' => "bid_received",
                    'provider_id' => $this->user_details['id'],
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                );
                $db      = \Config\Database::connect();
                $builder = $db->table('users u')
                    ->where('id', $fetch_custom_job_Data[0]['user_id']);
                $users_fcm = $builder->get()->getResultArray();
                $fcm_ids['fcm_id'] = "";
                $fcm_ids['platform'] = "";
                foreach ($users_fcm as $ids) {
                    if ($ids['fcm_id'] != "") {
                        $fcm_ids['fcm_id'] = $ids['fcm_id'];
                        $fcm_ids['platform'] = $ids['platform'];
                    }
                    $registrationIDs[] = $fcm_ids;
                }
                //for web start
                $web_where = "web_fcm_id IS NOT NULL AND web_fcm_id != ''";
                $web_fcm_id = $db->table('users')->select('web_fcm_id')->where($web_where)->where('id',  $fetch_custom_job_Data[0]['user_id'])->get()->getResultArray();
                $webfcm_ids = [];
                foreach ($web_fcm_id as $ids) {
                    if ($ids['web_fcm_id'] != "") {
                        $webfcm_ids['web_fcm_id'] = $ids['web_fcm_id'];
                    }
                    $web_registrationIDs[] = $webfcm_ids;
                }
                //for web end
                $registrationIDs_chunks = array_chunk($registrationIDs, 1000);
                $not_data =  send_notification($fcmMsg, $registrationIDs_chunks);
                if (!empty($web_registrationIDs)) {
                    $web_not_data =  send_customer_web_notification($fcmMsg, $web_registrationIDs);
                }
                $response = [
                    'error' => false,
                    'message' => 'Your bid has been placed successfully',
                    'data' => $data
                ];
                return $this->response->setJSON($response);
            }
        } catch (\Throwable $th) {
            log_the_responce(
                $this->request->header('Authorization') . ' Params passed: ' . json_encode($_POST) . " Issue => " . $th,
                date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - apply_for_custom_job()'
            );
            return $this->response->setJSON([
                'error'   => true,
                'message' => 'Something went wrong',
            ]);
        }
    }
    public function get_custom_job_requests()
    {
        try {
            $validation = \Config\Services::validation();
            $validation->setRules([
                'job_type' => [
                    'label' => 'Field',
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'The {field} field is required. Note: The value can be either "applied_jobs" or "open_jobs".',
                    ],
                ],
            ]);
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            $partner_id = $this->user_details['id'];
            $limit = !empty($this->request->getPost('limit')) ?  $this->request->getPost('limit') : 10;
            $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
            $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
            $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'DESC';
            $custom_job_categories = fetch_details('partner_details', ['partner_id' => $partner_id], ['custom_job_categories', 'is_accepting_custom_jobs']);
            $partner_categoried_preference = !empty($custom_job_categories) &&
                isset($custom_job_categories[0]['custom_job_categories']) &&
                !empty($custom_job_categories[0]['custom_job_categories']) ?
                json_decode($custom_job_categories[0]['custom_job_categories']) : [];
            $db = \Config\Database::connect();
            $disk = fetch_current_file_manager();
            if ($this->request->getPost('job_type') == "applied_jobs") {
                $total_count = $db->table('partner_bids pb')
                    ->join('custom_job_requests cj', 'cj.id = pb.custom_job_request_id')
                    ->join('users u', 'u.id = cj.user_id')
                    ->join('categories c', 'c.id = cj.category_id')
                    ->where('pb.partner_id', $partner_id)
                    ->countAllResults(false);
                $jobs = $db->table('partner_bids pb')
                    ->select('pb.*, cj.user_id,cj.category_id,cj.service_title,cj.service_short_description,cj.min_price,cj.max_price,cj.requested_start_date,cj.requested_start_time,cj.requested_end_date,cj.requested_end_time,cj.status, u.username, u.image, c.id as category_id, c.name as category_name, c.image as category_image')
                    ->join('custom_job_requests cj', 'cj.id = pb.custom_job_request_id')
                    ->join('users u', 'u.id = cj.user_id')
                    ->join('categories c', 'c.id = cj.category_id')
                    ->where('pb.partner_id', $partner_id)
                    ->orderBy('pb.id', 'DESC')
                    ->limit($limit, $offset)
                    ->get()
                    ->getResultArray();
                foreach ($jobs as &$job) {

                    if ($job['tax_amount'] == "") {
                        $job['final_total'] =  $job['counter_price'];
                    } else {
                        $job['final_total'] =  $job['counter_price'] + ($job['tax_amount']);
                    }

                    if (!empty($job['image'])) {
                        if ($disk == "local_server") {
                            $job['image'] = base_url('public/backend/assets/profiles/' . $job['image']);
                        } else if ($disk == "aws_s3") {
                            $job['image'] = fetch_cloud_front_url('profile', $job['image']);
                        } else {
                            $job['image'] = base_url('public/backend/assets/profiles/' . $job['image']);
                        }
                    } else {
                        $job['image'] = base_url('public/backend/assets/profiles/default.png');
                    }
                    if ($disk == 'local_server') {
                        $localPath = base_url('/public/uploads/categories/' . $job['category_image']);
                        if (check_exists($localPath)) {
                            $job['category_image'] = $localPath;
                        } else {
                            $job['category_image'] = '';
                        }
                    } else if ($disk == "aws_s3") {
                        $job['category_image'] = fetch_cloud_front_url('categories', $job['category_image']);
                    } else {
                        $job['category_image'] = $job['category_image'];
                    }
                }
            } else if ($this->request->getPost('job_type') == "open_jobs") {

                $totalJobsQuery = $db->table('custom_job_requests cj')
                    ->select('cj.id')
                    ->join('users u', 'u.id = cj.user_id')
                    ->join('categories c', 'c.id = cj.category_id')
                    ->where('cj.status', 'pending')
                    ->where("(SELECT COUNT(1) FROM partner_bids pb WHERE pb.custom_job_request_id = cj.id AND pb.partner_id = $partner_id) = 0");
                if (!empty($partner_categoried_preference)) {
                    $totalJobsQuery->whereIn('cj.category_id', $partner_categoried_preference);
                }
                $totalJobsQueryResult = $totalJobsQuery->get()->getResultArray();
                $total_filteredJobs = [];
                foreach ($totalJobsQueryResult as $row) {
                    $did_partner_bid = fetch_details('partner_bids', [
                        'custom_job_request_id' => $row['id'],
                        'partner_id' => $partner_id,
                    ]);
                    if (empty($did_partner_bid)) {
                        $check = fetch_details('custom_job_provider', ['partner_id' => $partner_id, 'custom_job_request_id' => $row['id']]);
                        if (!empty($check)) {
                            $total_filteredJobs[] = $row;
                        }
                    }
                }
                // Get the total count
                // Now get the paginated results with limit and offset
                $jobsQuery = $db->table('custom_job_requests cj')
                    ->select('cj.*, u.username, u.image, c.id as category_id, c.name as category_name, c.image as category_image')
                    ->join('users u', 'u.id = cj.user_id')
                    ->join('categories c', 'c.id = cj.category_id')
                    ->where('cj.status', 'pending')
                    ->where("(SELECT COUNT(1) FROM partner_bids pb WHERE pb.custom_job_request_id = cj.id AND pb.partner_id = $partner_id) = 0");
                if (!empty($partner_categoried_preference)) {
                    $jobsQuery->whereIn('cj.category_id', $partner_categoried_preference);
                }
                // Apply limit and offset for pagination
                $jobsQuery->orderBy('cj.id', 'DESC')->limit($limit, $offset);
                $jobs = $jobsQuery->get()->getResultArray();
                // Filter out jobs with existing custom job provider records
                $filteredJobs = [];
                foreach ($jobs as $row) {
                    $check = fetch_details('custom_job_provider', ['partner_id' => $partner_id, 'custom_job_request_id' => $row['id']]);

                    if (!empty($check)) {
                        $filteredJobs[] = $row;
                    }
                }
                if (!empty($partner_categoried_preference)) {
                    $jobs =  $filteredJobs;
                } else {
                    $jobs = [];
                    $total_count = 0;
                }
                if (!empty($jobs)) {
                    foreach ($jobs as &$job) {
                        if (!empty($job['image'])) {
                            if ($disk == "local_server") {
                                $job['image'] = base_url('public/backend/assets/profiles/' . $job['image']);
                            } else if ($disk == "aws_s3") {
                                $job['image'] = fetch_cloud_front_url('profile', $job['image']);
                            } else {
                                $job['image'] = base_url('public/backend/assets/profiles/' . $job['image']);
                            }
                        } else {
                            $job['image'] = base_url('public/backend/assets/profiles/default.png');
                        }
                        if ($disk == 'local_server') {
                            $localPath = base_url('/public/uploads/categories/' . $job['category_image']);
                            if (check_exists($localPath)) {
                                $job['category_image'] = $localPath;
                            } else {
                                $job['category_image'] = '';
                            }
                        } else if ($disk == "aws_s3") {
                            $job['category_image'] = fetch_cloud_front_url('categories', $job['category_image']);
                        } else {
                            $job['category_image'] = $job['category_image'];
                        }
                    }
                }
            }
            $response = [
                'error' => false,
                'message' => 'Custom job fetched successfully',
                'data' => $jobs,
                'total' => ($this->request->getPost('job_type') == "open_jobs") ? count($total_filteredJobs) : $total_count,
            ];
            return $this->response->setJSON($response);
        } catch (\Throwable $th) {

            log_the_responce(
                $this->request->header('Authorization') . ' Params passed: ' . json_encode($_POST) . " Issue => " . $th,
                date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - get_custom_job_requests()'
            );
            return $this->response->setJSON([
                'error'   => true,
                'message' => 'Something went wrong',
            ]);
        }
    }
    public function manage_category_preference()
    {
        try {
            if (empty($_POST['category_id'])) {
                return ErrorResponse("Select at least one category", true, [], [], 200, csrf_token(), csrf_hash());
            }
            $selected_categories = $_POST['category_id'];
            update_details(
                ['custom_job_categories' => json_encode($selected_categories)],
                ['partner_id' => $this->user_details['id']],
                'partner_details',
                false
            );
            $response = [
                'error' => false,
                'message' => 'Category Preference set successfully',
            ];
            return $this->response->setJSON($response);
        } catch (\Throwable $th) {
            log_the_responce(
                $this->request->header('Authorization') . ' Params passed: ' . json_encode($_POST) . " Issue => " . $th,
                date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - manage_category_preference()'
            );
            return $this->response->setJSON([
                'error'   => true,
                'message' => 'Something went wrong',
            ]);
        }
    }
    public function manage_custom_job_request_setting()
    {
        try {
            $validation = \Config\Services::validation();
            $validation->setRules([
                'custom_job_value' => 'required',
            ]);
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            $update =  update_details(['is_accepting_custom_jobs' => $_POST['custom_job_value']], ['partner_id' => $this->user_details['id']], 'partner_details');
            if ($update) {
                $response = [
                    'error' => false,
                    'message' => 'Your setting has been successfully',
                ];
            } else {
                $response = [
                    'error' => true,
                    'message' => 'Something went wrong',
                ];
            }
            return $this->response->setJSON($response);
        } catch (\Throwable $th) {
            log_the_responce(
                $this->request->header('Authorization') . ' Params passed: ' . json_encode($_POST) . " Issue => " . $th,
                date("Y-m-d H:i:s") . '--> app/Controllers/partner/api/V1.php - manage_category_preference()'
            );
            return $this->response->setJSON([
                'error'   => true,
                'message' => 'Something went wrong',
            ]);
        }
    }
    public function get_places_for_app()
    {
        try {
            $validation = \Config\Services::validation();
            $validation->setRules([
                'input' => 'required',
            ]);
            if (!$validation->withRequest($this->request)->run()) {
                return $this->response->setJSON([
                    'error'   => true,
                    'message' => $validation->getErrors(),
                    'data'    => [],
                ]);
            }
            $input = $_GET['input'];
            $key = get_settings('api_key_settings', true);
            if (!isset($key['google_map_api'])) {
                return $this->response->setJSON([
                    'error'   => true,
                    'message' => 'MAP API key is not set',
                ]);
            }
            $google_map_api = $key['google_map_api'];
            $url = "https://maps.googleapis.com/maps/api/place/autocomplete/json?key=" . $google_map_api . "&input=" . $input;
            $response = file_get_contents($url);
            $responseData = json_decode($response, true);
            return $this->response->setJSON([
                'error' => false,
                'data'  => $responseData ?? [],
            ]);
        } catch (\Throwable $th) {

            log_the_responce(
                $this->request->header('Authorization') . ' Params passed: ' . json_encode($_POST) . " Issue => " . $th,
                date("Y-m-d H:i:s") . '--> app/Controllers/api/V1.php - get_places_for_app()'
            );
            return $this->response->setJSON([
                'error'   => true,
                'message' => 'Something went wrong',
            ]);
        }
    }
    public function get_place_details_for_app()
    {
        try {
            $validation = \Config\Services::validation();
            $validation->setRules([
                'placeid' => 'required',
            ]);
            if (!$validation->withRequest($this->request)->run()) {
                return $this->response->setJSON([
                    'error'   => true,
                    'message' => $validation->getErrors(),
                    'data'    => [],
                ]);
            }
            $placeid = $_GET['placeid'];
            $key = get_settings('api_key_settings', true);
            if (!isset($key['google_map_api'])) {
                return $this->response->setJSON([
                    'error'   => true,
                    'message' => 'MAP API key is not set',
                ]);
            }
            $google_map_api = $key['google_map_api'];
            $url = "https://maps.googleapis.com/maps/api/place/details/json?key=" . $google_map_api . "&placeid=" . $placeid;
            $response = file_get_contents($url);
            $responseData = json_decode($response, true);
            return $this->response->setJSON([
                'error' => false,
                'data'  => $responseData ?? [],
            ]);
        } catch (\Throwable $th) {

            log_the_responce(
                $this->request->header('Authorization') . ' Params passed: ' . json_encode($_POST) . " Issue => " . $th,
                date("Y-m-d H:i:s") . '--> app/Controllers/api/V1.php - get_places_for_app()'
            );
            return $this->response->setJSON([
                'error'   => true,
                'message' => 'Something went wrong',
            ]);
        }
    }

    public function get_home_data()
    {
        try {
            $partner_id = $this->user_details['id'];
            // print_r($partner_id); die;
            //-------------------------------SUBSCRIPTION INFORMATION------------------------------//
            $subscription = fetch_details('partner_subscriptions', ['partner_id' => $partner_id], [], 1, 0, 'id', 'DESC');
            $subscriptionInformation = [
                'subscription_id' => $subscription[0]['subscription_id'] ?? "",
                'isSubscriptionActive' => $subscription[0]['status'] ?? "deactive",
                'created_at' => $subscription[0]['created_at'] ?? "",
                'updated_at' => $subscription[0]['updated_at'] ?? "",
                'is_payment' => $subscription[0]['is_payment'] ?? "",
                'id' => $subscription[0]['id'] ?? "",
                'partner_id' => $subscription[0]['partner_id'] ?? "",
                'purchase_date' => $subscription[0]['purchase_date'] ?? "",
                'expiry_date' => $subscription[0]['expiry_date'] ?? "",
                'name' => $subscription[0]['name'] ?? "",
                'description' => $subscription[0]['description'] ?? "",
                'duration' => $subscription[0]['duration'] ?? "",
                'price' => $subscription[0]['price'] ?? "",
                'discount_price' => $subscription[0]['discount_price'] ?? "",
                'order_type' => $subscription[0]['order_type'] ?? "",
                'max_order_limit' => $subscription[0]['max_order_limit'] ?? "",
                'is_commision' => $subscription[0]['is_commision'] ?? "",
                'commission_threshold' => $subscription[0]['commission_threshold'] ?? "",
                'commission_percentage' => $subscription[0]['commission_percentage'] ?? "",
                'publish' => $subscription[0]['publish'] ?? "",
                'tax_id' => $subscription[0]['tax_id'] ?? "",
                'tax_type' => $subscription[0]['tax_type'] ?? ""
            ];

            if (!empty($subscription)) {
                $isCommissionBasedSubscription = ($subscription[0]['is_commision'] == "yes") ? 1 : 0;
            }

            if (!empty($subscription[0])) {
                $price = calculate_partner_subscription_price($subscription[0]['partner_id'], $subscription[0]['subscription_id'], $subscription[0]['id']);
            }
            $subscriptionInformation['tax_value'] = $price[0]['tax_value'] ?? "";
            $subscriptionInformation['price_with_tax'] = $price[0]['price_with_tax'] ?? "";
            $subscriptionInformation['original_price_with_tax'] = $price[0]['original_price_with_tax'] ?? "";
            $subscriptionInformation['tax_percentage'] = $price[0]['tax_percentage'] ?? "";

            if ($subscriptionInformation['isSubscriptionActive'] === 'deactive') {
                $data['subscription_information'] = (object)[];
            } else {
                $data['subscription_information'] = $subscriptionInformation;
            }


            //-------------------------------BOOKING INFORMATION------------------------------//
            $currentDate = (new DateTime())->format('Y-m-d');
            $tomorrowDate = (new DateTime('tomorrow'))->format('Y-m-d');

            $todayBooking = fetch_details('orders', [
                'status' => 'awaiting',
                'partner_id' => $partner_id
            ]);

         
            $todayBooking = array_filter($todayBooking, function ($order) use ($currentDate) {
                return date('Y-m-d', strtotime($order['date_of_service'])) === $currentDate;
            });
          
            $tomorrowBookings = array_filter($todayBooking, function ($order) use ($tomorrowDate) {
                return date('Y-m-d', strtotime($order['date_of_service'])) === $tomorrowDate;
            });

            $upcomingBooking = fetch_details('orders', [
                'status' => 'awaiting',
                'partner_id' => $partner_id,
                'created_at >=' => $currentDate
            ]);


            $bookings['today_bookings'] = count($todayBooking);
            $bookings['tommorrow_bookings'] = count($tomorrowBookings);
            $bookings['upcoming_bookings'] = count($upcomingBooking);

            $data['bookings'] = $bookings;

            //--------------------------------EARNING REPORT SECTION -------------------------------//

            $adminCommission = fetch_details('users', ['id' => $partner_id], ['payable_commision']);
            $data['earning_report']['admin_commission'] = $adminCommission[0]['payable_commision'];


            $total_balance = strval(unsettled_commision($partner_id));

            $data['earning_report']['my_income'] = $total_balance;

            $remainingIncome = fetch_details('users', ['id' => $partner_id], ['balance']);
            $data['earning_report']['remaining_income'] = $remainingIncome[0]['balance'];


            $amount = fetch_details('orders', ['partner_id' => $partner_id, 'is_commission_settled' => '0', 'status' => 'awaiting'], ['sum(final_total) as total']);
            if (isset($amount) && !empty($amount)) {
                $admin_commission_percentage = get_admin_commision($partner_id);
                $admin_commission_amount = intval($admin_commission_percentage) / 100;
                $total = $amount[0]['total'];
                $commision = intval($total) * $admin_commission_amount;
                $unsettled_amount = $total - $commision;
            } else {
                $unsettled_amount = 0.0;
            }
            $unsettled_amount = $unsettled_amount;


            $data['earning_report']['future_earning_from_bookings'] = (float)$unsettled_amount;

            //-------------------------CUSTOM JOB SECTION ------------------------------------------//
            $db = \Config\Database::connect();

            $custom_job_categories = fetch_details('partner_details', ['partner_id' => $this->userId], ['custom_job_categories', 'is_accepting_custom_jobs']);
            $partner_categoried_preference = !empty($custom_job_categories) &&
                isset($custom_job_categories[0]['custom_job_categories']) &&
                !empty($custom_job_categories[0]['custom_job_categories']) ?
                json_decode($custom_job_categories[0]['custom_job_categories']) : [];


            $builder = $db->table('custom_job_requests cj')
                ->select('cj.*, u.username, u.image, c.id as category_id, c.name as category_name, c.image as category_image')
                ->join('users u', 'u.id = cj.user_id')
                ->join('categories c', 'c.id = cj.category_id')
                ->where('cj.status', 'pending')
                ->where("(SELECT COUNT(1) FROM partner_bids pb WHERE pb.custom_job_request_id = cj.id AND pb.partner_id = $partner_id) = 0");
            if (!empty($partner_categoried_preference)) {
                $builder->whereIn('cj.category_id', $partner_categoried_preference);
            }
            $builder->orderBy('cj.id', 'DESC');
            $custom_job_requests = $builder->get()->getResultArray();
            $filteredJobs = [];
            foreach ($custom_job_requests as $row) {
                $did_partner_bid = fetch_details('partner_bids', [
                    'custom_job_request_id' => $row['id'],
                    'partner_id' => $partner_id,
                ]);
                if (empty($did_partner_bid)) {
                    $check = fetch_details('custom_job_provider', [
                        'partner_id' => $partner_id,
                        'custom_job_request_id' => $row['id'],
                    ]);
                    if (!empty($check)) {
                        $filteredJobs[] = $row;
                    }
                }
            }
            if (!empty($filteredJobs)) {
                foreach ($filteredJobs as &$job) {
                    if (!empty($job['image'])) {
                        $job['image'] = base_url('public/backend/assets/profiles/' . $job['image']);
                    } else {
                        $job['image'] = base_url('public/backend/assets/profiles/default.png');
                    }
                }
            }
            $data['custom_jobs']['total_open_jobs'] = count($filteredJobs);
            $filteredJobs = array_slice($filteredJobs, 0, 2);

            $data['custom_jobs']['open_jobs'] = $filteredJobs;

            //---------------------------SALES REPORT (CHARTS) --------------------------------//
            $last_monthly_sales = (isset($_POST['last_monthly_sales']) && !empty(trim($_POST['last_monthly_sales']))) ? $this->request->getPost("last_monthly_sales") : 12;


            $monthly_sales = $db->table('orders')
                ->select('YEAR(date_of_service) as year, MONTHNAME(date_of_service) as month, SUM(final_total) as total_amount')
                ->where('date_of_service >=', "DATE_SUB(CURDATE(), INTERVAL $last_monthly_sales MONTH)", false) // No binding needed
                ->where('date_of_service <=', date("Y-m-d"))
                ->where([
                    'partner_id' => $partner_id,
                    "status" => "completed"
                ])
                ->groupBy("YEAR(date_of_service), MONTH(date_of_service)")
                ->orderBy("YEAR(date_of_service), MONTH(date_of_service)")
                ->get()->getResultArray();





            $yearly_sales = $db->table('orders')
                ->select('YEAR(date_of_service) as year, SUM(final_total) as total_amount')
                ->where('date_of_service BETWEEN CURDATE() - INTERVAL 1 YEAR AND CURDATE()')
                ->where(['partner_id' => $partner_id, 'date_of_service < ' => date("Y-m-d H:i:s"), "status" => "completed"])
                ->groupBy("YEAR(date_of_service)")
                ->get()->getResultArray();

            $weekly_sales = $db->table('orders')
                ->select('WEEK(date_of_service) as week, SUM(final_total) as total_amount')
                ->where('date_of_service BETWEEN CURDATE() - INTERVAL 1 WEEK AND CURDATE()')
                ->where(['partner_id' => $partner_id, 'date_of_service < ' => date("Y-m-d H:i:s"), "status" => "completed"])
                ->groupBy("WEEK(date_of_service)")
                ->get()->getResultArray();

            $sales_data = [
                'monthly_sales' => $monthly_sales,
                'yearly_sales'  => $yearly_sales,
                'weekly_sales'  => $weekly_sales
            ];

            $data['sales_data'] = $sales_data;

            return $this->response->setJSON([
                'error' => false,
                'message' => 'data fetched successfully',
                'data'  => $data ?? [],
            ]);
        } catch (\Throwable $th) {
            throw $th;
            log_the_responce(
                $this->request->header('Authorization') . ' Params passed: ' . json_encode($_POST) . " Issue => " . $th,
                date("Y-m-d H:i:s") . '--> app/Controllers/api/V1.php - get_home_data()'
            );
            return $this->response->setJSON([
                'error'   => true,
                'message' => 'Something went wrong',
            ]);
        }
    }
}
