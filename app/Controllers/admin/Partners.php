<?php

namespace App\Controllers\admin;

use App\Models\Cash_collection_model;
use App\Models\Orders_model;
use App\Models\Partners_model;
use App\Models\Payment_request_model;
use App\Models\Promo_code_model;
use App\Models\Service_model;
use App\Models\Users_model;
use App\Models\Service_ratings_model;
use App\Models\Settlement_CashCollection_history_model;
use App\Models\Settlement_model;
use CodeIgniter\Session\Session;
use Config\ApiResponseAndNotificationStrings;
use IonAuth\Models\IonAuthModel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Partners extends Admin
{
    protected Users_model $users;
    protected Cash_collection_model $cash_collection;
    protected Settlement_model $settle_commission;
    protected $superadmin;
    protected ApiResponseAndNotificationStrings $trans;

    public $partner,  $validation, $db, $ionAuth, $creator_id;
    public function __construct()
    {
        parent::__construct();
        $this->partner = new Partners_model();
        $this->users = new Users_model();
        $this->cash_collection = new Cash_collection_model();
        $this->settle_commission = new Settlement_model();
        $this->validation = \Config\Services::validation();
        $this->db = \Config\Database::connect();
        $this->ionAuth = new \IonAuth\Libraries\IonAuth();
        $this->creator_id = $this->userId;
        $this->superadmin = $this->session->get('email');
        $this->trans = new ApiResponseAndNotificationStrings();
        helper('ResponceServices');
    }
    public function index()
    {
        helper('function');
        if (!$this->isLoggedIn || !$this->userIsAdmin) {
            return redirect('admin/login');
        }
        setPageInfo($this->data, labels('partners', 'Partners') . ' | ' . labels('admin_panel', 'Admin Panel'), 'partners');
        return view('backend/admin/template', $this->data);
    }
    public function add_partner()
    {
        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            $permission = is_permitted($this->creator_id, 'create', 'partner');
            if (!$permission) {
                return NoPermission();
            }
            if ($this->isLoggedIn && $this->userIsAdmin) {
                setPageInfo($this->data, labels('add_partners', 'Add Partners') . ' | ' . labels('admin_panel', 'Admin Panel'), 'add_partner');
                $partner_details = !empty(fetch_details('partner_details', ['partner_id' => $this->userId])) ? fetch_details('partner_details', ['partner_id' => $this->userId])[0] : [];
                $partner_timings = !empty(fetch_details('partner_timings', ['partner_id' => $this->userId])) ? fetch_details('partner_timings', ['partner_id' => $this->userId]) : [];
                $this->data['data'] = fetch_details('users', ['id' => $this->userId])[0];
                $currency = get_settings('general_settings', true);
                if (empty($currency)) {
                    $_SESSION['toastMessage'] = labels('first_add_currency_and_basic_details', 'Please first add currency and basic details in general settings');
                    $_SESSION['toastMessageType'] = 'error';
                    $this->session->markAsFlashdata('toastMessage');
                    $this->session->markAsFlashdata('toastMessageType');
                    return redirect()->to('admin/settings/general-settings')->withCookies();
                }
                $this->data['currency'] = $currency['currency'];
                $this->data['allow_pre_booking_chat'] = $currency['allow_pre_booking_chat'] ?? 0;
                $this->data['allow_post_booking_chat'] = $currency['allow_post_booking_chat'] ?? 0;
                $this->data['partner_details'] = $partner_details;
                $this->data['partner_timings'] = $partner_timings;
                $this->data['city_name'] = fetch_details('cities', [], ['id', 'name']);
                $subscription_details = fetch_details('subscriptions', ['status' => 1]);
                $this->data['subscription_details'] = $subscription_details;
                return view('backend/admin/template', $this->data);
            } else {
                return redirect('admin/login');
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - add_partner()');
            return ErrorResponse('Something Went Wrong', true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function list()
    {
        try {
            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 20;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
            print_r(json_encode($this->partner->list(false, $search, $limit, $offset, $sort, $order)));
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - list()');
            return ErrorResponse('Something Went Wrong', true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function view_partner()
    {
        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            helper('function');
            $uri = service('uri');
            $partner_id = $uri->getSegments()[3];
            $data = fetch_details('partner_details', ['partner_id' => $partner_id]);
            if (empty($data)) {
                return redirect('admin/partners');
            }
            $partner_details = $data[0];
            $user_details = fetch_details('users', ['id' => $partner_id])[0];
            setPageInfo($this->data, labels('partners', 'Partners') . ' | ' . labels('admin_panel', 'Admin Panel'), 'view_partner');
            $this->data['partner_details'] = $partner_details;
            $this->data['personal_details'] = $user_details;
            return view('backend/admin/template', $this->data);
        } catch (\Exception $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - view_partner()');
            return ErrorResponse('Something Went Wrong', true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function edit_partner()
    {
        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            helper('function');
            $uri = service('uri');
            $partner_id = $uri->getSegments()[3];
            $data = fetch_details('partner_details', ['partner_id' => $partner_id]);
            if (empty($data)) {
                return redirect('admin/partners');
            }
            $partner_details = $data[0];
            $user_details = fetch_details('users', ['id' => $partner_id])[0];
            $currency = get_settings('general_settings', true);
            $partner_timings = fetch_details('partner_timings', ['partner_id' => $partner_id], '', '', '', '', 'ASC');
            $this->data['currency'] = $currency['currency'];
            setPageInfo($this->data, labels('partners', 'Partners') . ' | ' . labels('admin_panel', 'Admin Panel'), 'edit_partner');

            $disk = fetch_current_file_manager();


            $partner_details['banner'] = get_file_url($disk, $partner_details['banner'], '', 'banner');
            $partner_details['national_id'] = get_file_url($disk,  $partner_details['national_id'],  '',  'national_id');
            $partner_details['address_id'] = get_file_url($disk, $partner_details['address_id'], '', 'address_id');
            $partner_details['passport'] = get_file_url($disk,  $partner_details['passport'],  '',  'passport');
            // Process other images
            if (!empty($partner_details['other_images'])) {
                $decodedImages = json_decode($partner_details['other_images'], true);
                $updatedImages = [];
                foreach ($decodedImages as $data) {
                    $updatedImages[] = get_file_url($disk, $data, '', 'partner');
                }
                $partner_details['other_images'] = $updatedImages;
            } else {
                $partner_details['other_images'] = [];
            }

            // print_R($user_details['image']);
            // die;

            $user_details['image'] = get_file_url($disk,  $user_details['image'],  '',  'profile');
            $this->data['partner_details'] = $partner_details;
            $this->data['personal_details'] = $user_details;
            $this->data['partner_timings'] = $partner_timings;
            $this->data['allow_pre_booking_chat'] = ($currency['allow_pre_booking_chat']) ?? 0;
            $this->data['allow_post_booking_chat'] = $currency['allow_post_booking_chat'] ?? 0;
            $active_subscription_details = fetch_details('partner_subscriptions', ['partner_id' => $partner_id, 'status' => 'active']);
            $symbol =   get_currency();
            $this->data['currency'] = $symbol;
            $this->data['active_subscription_details'] = $active_subscription_details;
            $this->data['partner_id'] = $partner_id;
            $subscription_details = fetch_details('subscriptions', ['status' => 1]);
            $this->data['subscription_details'] = $subscription_details;
            return view('backend/admin/template', $this->data);
        } catch (\Throwable $th) {

            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - edit_partner()');
            return ErrorResponse('Something Went Wrong', true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function insert_partner()
    {
        try {
            $result = checkModificationInDemoMode($this->superadmin);
            if ($result !== true) {
                return $this->response->setJSON($result);
            }
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            $permission = is_permitted($this->creator_id, 'create', 'partner');
            if (!$permission) {
                return NoPermission();
            }
            $t = time();
            $this->validation->setRules(
                [
                    'company_name' => ["rules" => 'required|trim', "errors" => ["required" => "Please enter company_name"]],
                    'city' => ["rules" => 'required|trim', "errors" => ["required" => "Please enter city",]],
                    'address' => ["rules" => 'required|trim', "errors" => ["required" => "Please enter address",]],
                    'partner_latitude' => ["rules" => 'required|trim', "errors" => ["required" => "Please choose provider location",]],
                    'type' => ["rules" => 'required', "errors" => ["required" => "Please select providers type",]],
                    'number_of_members' => ["rules" => 'required|numeric', "errors" => ["required" => "Please enter number of members",    "numeric" => "Please enter numeric value for members"]],
                    'visiting_charges' => ["rules" => 'required|numeric', "errors" => ["required" => "Please enter visiting charges",    "numeric" => "Please enter numeric value for visiting charges"]],
                    'advance_booking_days' => ["rules" => 'required|numeric', "errors" => ["required" => "Please enter advance booking days",    "numeric" => "Please enter numeric advance booking days"]],
                    'start_time' => ["rules" => 'required', "errors" => ["required" => "Please enter providers working days",]],
                    'end_time' => ["rules" => 'required', "errors" => ["required" => "Please enter providers working properly",]],
                    'username' => ["rules" => 'required|trim', "errors" => ["required" => "Please enter providers name",]],
                    'email' => ["rules" => 'required|trim', "errors" => ["required" => "Please enter providers email",]],
                    'phone' => ["rules" => 'required|numeric', "errors" => ["required" => "Please enter providers phone number",    "numeric" => "Please enter numeric phone number"]],
                    'provider_slug' => ["rules" => 'required|trim', "errors" => ["required" => "Please enter providers slug",]],
                    'password' => ["rules" => 'required|trim', "errors" => ["required" => "Please enter password",]],
                    'image' => ["rules" => 'uploaded[image]'],
                    'banner_image' => ["rules" => 'uploaded[banner_image]'],
                    'passport' => ["rules" => 'uploaded[passport]'],
                    'national_id' => ["rules" => 'uploaded[national_id]'],
                ],
            );
            if (!$this->validation->withRequest($this->request)->run()) {
                $errors = $this->validation->getErrors();
                return ErrorResponse($errors, true, [], [], 200, csrf_token(), csrf_hash());
            }
            $db      = \Config\Database::connect();
            $builder = $db->table('users u');
            $builder->select('u.*,ug.group_id')
                ->join('users_groups ug', 'ug.user_id = u.id')
                ->where('ug.group_id', 3)
                ->where(['phone' =>  $_POST['phone']]);
            $mobile_data = $builder->get()->getResultArray();
            if (!empty($mobile_data) && $mobile_data[0]['phone']) {
                return ErrorResponse("Phone number already exists please use another one", true, [], [], 200, csrf_token(), csrf_hash());
            }
            if (!preg_match('/^-?(90|[1-8][0-9][.][0-9]{1,20}|[0-9][.][0-9]{1,20})$/', $this->request->getPost('partner_latitude'))) {
                return ErrorResponse("Please enter valid latitude", true, [], [], 200, csrf_token(), csrf_hash());
            }
            if (!preg_match('/^-?(180(\.0{1,20})?|1[0-7][0-9](\.[0-9]{1,20})?|[1-9][0-9](\.[0-9]{1,20})?|[0-9](\.[0-9]{1,20})?)$/', $this->request->getPost('partner_longitude'))) {
                return ErrorResponse("Please enter a valid Longitude", true, [], [], 200, csrf_token(), csrf_hash());
            }
            $ion_auth = new IonAuthModel();
            $paths = [
                'profile' => ['file' => $this->request->getFile('image'), 'path' => 'public/backend/assets/profile/', 'error' => labels('Failed to create profile folders', 'Failed to create profile folders'), 'folder' => 'profile'],
                'banner' => ['file' => $this->request->getFile('banner_image'), 'path' => 'public/backend/assets/banner/', 'error' => labels('Failed to create banner folders', 'Failed to create banner folders'), 'folder' => 'banner'],
                'national_id' => ['file' => $this->request->getFile('national_id'), 'path' => 'public/backend/assets/national_id/', 'error' => labels('Failed to create national_id folders', 'Failed to create national_id folders'), 'folder' => 'national_id'],
                'address_id' => ['file' => $this->request->getFile('address_id'), 'path' => 'public/backend/assets/address_id/', 'error' => labels('Failed to create address_id folders', 'Failed to create address_id folders'), 'folder' => 'address_id'],
                'passport' => ['file' => $this->request->getFile('passport'), 'path' => 'public/backend/assets/passport/', 'error' => labels('Failed to create passport folders', 'Failed to create passport folders'), 'folder' => 'passport']
            ];

            $uploadedFiles = [];
            foreach ($paths as $key => $upload) {

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
            $image = $uploadedFiles['profile']['url'] ?? 'public/backend/assets/profile/' . $this->request->getFile('image')->getName();
            if (isset($uploadedFiles['profile']['disk']) && $uploadedFiles['profile']['disk'] == 'local_server') {
                $image = 'public/backend/assets/profile/' . $uploadedFiles['profile']['url'];
            }
            $users_details = [
                'username' => trim($_POST['username']),
                'password' => $ion_auth->hashPassword($_POST['password']),
                'email' => strtolower($_POST['email']),
                'latitude' => $this->request->getPost('partner_latitude'),
                'longitude' => $this->request->getPost('partner_longitude'),
                'phone' => $_POST['phone'],
                'country_code' => $_POST['country_code'],
                'city' => $_POST['city'],
                'image' => $image,
                'is_approved' => isset($_POST['is_approved']) ? 1 : 0,
                'active' => 1,
            ];
            $insert_id = $this->users->save($users_details);
            if ($insert_id) {
                $multipleFiles = $this->request->getFiles('filepond');
                $otherImagesConfig = [
                    'path' => 'public/uploads/partner/',
                    'error' => labels('Failed to upload other images', 'Failed to upload other images'),
                    'folder' => 'partner'
                ];
                $uploadedOtherImages = [];
                if (isset($multipleFiles['other_service_image_selector'])) {
                    $files = $multipleFiles['other_service_image_selector'];
                    foreach ($files as $file) {
                        if ($file->isValid()) {
                            $result = upload_file($file, $otherImagesConfig['path'], $otherImagesConfig['error'], $otherImagesConfig['folder']);
                            if ($result['error'] == false) {
                                if ($result['disk'] == "local_server") {
                                    $uploadedOtherImages[] = 'public/uploads/partner/' . $result['file_name'];
                                } elseif ($result['disk'] == "aws_s3") {
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
                $other_images = [
                    'other_images' => !empty($uploadedOtherImages) ? json_encode($uploadedOtherImages) : "",
                ];
                $partner_id = $this->users->getInsertID();
                $banner = $uploadedFiles['banner']['url'] ?? 'public/backend/assets/banner/' . $this->request->getFile('banner_image')->getName();
                $national_id = $uploadedFiles['national_id']['url'] ?? 'public/backend/assets/national_id/' . $this->request->getFile('national_id')->getName();
                $address_id = $uploadedFiles['address_id']['url'] ?? 'public/backend/assets/address_id/' . $this->request->getFile('address_id')->getName();
                $passport = $uploadedFiles['passport']['url'] ?? 'public/backend/assets/passport/' . $this->request->getFile('passport')->getName();

                if (isset($uploadedFiles['banner']['disk']) && $uploadedFiles['banner']['disk'] == 'local_server') {
                    $banner = 'public/backend/assets/banner/' . $uploadedFiles['banner']['url'];
                }
                if (isset($uploadedFiles['national_id']['disk']) && $uploadedFiles['national_id']['disk'] == 'local_server') {
                    $national_id = 'public/backend/assets/national_id/' . $uploadedFiles['national_id']['url'];
                }
                if (isset($uploadedFiles['address_id']['disk']) && $uploadedFiles['address_id']['disk'] == 'local_server') {
                    $address_id = 'public/backend/assets/address_id/' . $uploadedFiles['address_id']['url'];
                }
                if (isset($uploadedFiles['passport']['disk']) && $uploadedFiles['passport']['disk'] == 'local_server') {
                    $passport = 'public/backend/assets/passport/' . $uploadedFiles['passport']['url'];
                }
                $partners = [
                    'partner_id' => $partner_id,
                    'banner' => $banner,
                    'company_name' => trim($_POST['company_name']),
                    'national_id' => $national_id,
                    'address_id' => $address_id,
                    'passport' => $passport,
                    'address' => trim($_POST['address']),
                    'tax_name' => $_POST['tax_name'] ?? '',
                    'tax_number' => $_POST['tax_number'] ?? '',
                    'bank_name' => $_POST['bank_name'] ?? '',
                    'account_number' => $_POST['account_number'] ?? '',
                    'account_name' => $_POST['account_name'] ?? '',
                    'bank_code' => $_POST['bank_code'] ?? '',
                    'swift_code' => $_POST['swift_code'] ?? '',
                    'advance_booking_days' => $_POST['advance_booking_days'],
                    'about' => $_POST['about'] ?? '',
                    'admin_commission' => 0,
                    'type' => $_POST['type'],
                    'number_of_members' => $_POST['number_of_members'],
                    'visiting_charges' => $_POST['visiting_charges'],
                    'is_approved' => isset($_POST['is_approved']) ? 1 : 0,
                    'long_description' => $_POST['long_description'] ?? '',
                    'other_images' => $other_images['other_images'] ?? '',
                    'at_store' => isset($_POST['at_store']) ? 1 : 0,
                    'at_doorstep' => isset($_POST['at_doorstep']) ? 1 : 0,
                    'need_approval_for_the_service' => isset($_POST['need_approval_for_the_service']) ? 1 : 0,
                    'chat' => isset($_POST['chat']) ? 1 : 0,
                    'pre_chat' => isset($_POST['pre_chat']) ? 1 : 0,
                    'slug' => generate_unique_slug($this->request->getPost('company_name'), 'partner_details'),


                ];
                if ($this->partner->save($partners)) {;
                    $days = [
                        0 => 'monday',
                        1 => 'tuesday',
                        2 => 'wednesday',
                        3 => 'thursday',
                        4 => 'friday',
                        5 => 'saturday',
                        6 => 'sunday'
                    ];
                    for ($i = 0; $i < count($_POST['start_time']); $i++) {
                        $partner_timing = [];
                        $partner_timing['day'] = $days[$i];
                        if (isset($_POST['start_time'][$i])) {
                            $partner_timing['opening_time'] = $_POST['start_time'][$i];
                        }
                        if (isset($_POST['end_time'][$i])) {
                            $partner_timing['closing_time'] = $_POST['end_time'][$i];
                        }
                        $partner_timing['is_open'] = (isset($_POST[$days[$i]])) ? 1 : 0;
                        $partner_timing['partner_id'] = $partner_id;
                        insert_details($partner_timing, 'partner_timings');
                    }
                    if (!exists(["user_id" => $partner_id, "group_id" => 3], 'users_groups')) {
                        $group_data['user_id'] = $partner_id;
                        $group_data['group_id'] = 3;
                        insert_details($group_data, 'users_groups');
                    }
                    return successResponse("Congratulations! Partner Added", false, ['partner_id' => $partner_id], [], 200, csrf_token(), csrf_hash());
                } else {
                    return ErrorResponse("some error while adding partner", true, [], [], 200, csrf_token(), csrf_hash());
                }
            } else {
                return ErrorResponse("some error while adding partner", true, [], [], 200, csrf_token(), csrf_hash());
            }
        } catch (\Throwable $th) {

            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - insert_partner()');
            return ErrorResponse('Something Went Wrong', true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function deactivate_partner()
    {
        try {
            $result = checkModificationInDemoMode($this->superadmin);
            if ($result !== true) {
                return $this->response->setJSON($result);
            }
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            $permission = is_permitted($this->creator_id, 'update', 'partner');
            if (!$permission) {
                return NoPermission();
            }
            $partner_id = $this->request->getPost('partner_id');
            $partner_details = fetch_details('users', ['id' => $partner_id])[0];
            $operation =  $this->ionAuth->deactivate($partner_id);
            if ($operation) {
                return successResponse("successfully disabled", false, [], [], 200, csrf_token(), csrf_hash());
            } else {
                return ErrorResponse("unsuccessful attempt to disable the user", true, [], [], 200, csrf_token(), csrf_hash());
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - deactivate_partner()');
            return ErrorResponse('Something Went Wrong', true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function activate_partner()
    {
        try {
            $permission = is_permitted($this->creator_id, 'update', 'partner');
            if (!$permission) {
                return NoPermission();
            }
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            $partner_id = $this->request->getPost('partner_id');
            $operation =  $this->ionAuth->activate($partner_id);
            if ($operation) {
                return successResponse("successfully activated", false, [], [], 200, csrf_token(), csrf_hash());
            } else {
                return ErrorResponse('unsuccessful attempt to disable the user', true, [], [], 200, csrf_token(), csrf_hash());
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - activate_partner()');
            return ErrorResponse('Something Went Wrong', true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function approve_partner()
    {
        try {
            $result = checkModificationInDemoMode($this->superadmin);
            if ($result !== true) {
                return $this->response->setJSON($result);
            }
            try {
                $permission = is_permitted($this->creator_id, 'update', 'partner');
                if (!$permission) {
                    return NoPermission();
                }
                if (!$this->isLoggedIn || !$this->userIsAdmin) {
                    return redirect('admin/login');
                }
                $partner_id = $this->request->getPost('partner_id');
                $builder = $this->db->table('partner_details');
                $partner_approval = $builder->set('is_approved', 1)->where('partner_id', $partner_id)->update();
                $to_send_id = $partner_id;
                $builder = $this->db->table('users')->select('fcm_id,email,platform');
                $users_fcm = $builder->where('id', $to_send_id)->get()->getResultArray();
                foreach ($users_fcm as $ids) {
                    if ($ids['fcm_id'] != "") {
                        $fcm_ids['fcm_id'] = $ids['fcm_id'];
                        $fcm_ids['platform'] = $ids['platform'];
                        $email = $ids['email'];
                    }
                }
                if ($partner_approval) {
                    if (!empty($fcm_ids)) {
                        if ($partner_approval && check_notification_setting('provider_approved', 'notification')) {
                            $fcmMsg = array(
                                'content_available' => "true",
                                'title' =>  $this->trans->registrationRequestApproval,
                                'body' => $this->trans->registrationRequestApprovedMessage,
                                'type' => 'provider_request_status',
                                'status' => 'approve',
                                'type_id' => "$to_send_id",
                                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            );
                            $registrationIDs_chunks = array_chunk($users_fcm, 1000);
                            send_notification($fcmMsg, $registrationIDs_chunks);
                        }
                    }
                    if (!empty($users_fcm[0]['email']) && check_notification_setting('provider_approved', 'email') && is_unsubscribe_enabled($partner_id) == 1) {
                        send_custom_email('provider_approved', $partner_id, $users_fcm[0]['email']);
                    }
                    if (check_notification_setting('provider_approved', 'sms')) {
                        send_custom_sms('provider_approved', $partner_id, $users_fcm[0]['email']);
                    }
                    return successResponse("Partner approved", false, [$partner_approval], [], 200, csrf_token(), csrf_hash());
                } else {
                    return successResponse("Could not approve partner", false, [], [], 200, csrf_token(), csrf_hash());
                }
            } catch (\Exception $th) {

                return ErrorResponse('Something Went Wrong', true, [], [], 200, csrf_token(), csrf_hash());
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - approve_partner()');
            return ErrorResponse('Something Went Wrong', true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function disapprove_partner()
    {
        try {
            $result = checkModificationInDemoMode($this->superadmin);
            if ($result !== true) {
                return $this->response->setJSON($result);
            }
            try {
                $permission = is_permitted($this->creator_id, 'update', 'partner');
                if (!$permission) {
                    return NoPermission();
                }
                if (!$this->isLoggedIn || !$this->userIsAdmin) {
                    return redirect('admin/login');
                }
                $partner_id = $this->request->getPost('partner_id');
                $builder = $this->db->table('partner_details');
                $partner_approval = $builder->set('is_approved', 0)->where('partner_id', $partner_id)->update();
                $partner_details = fetch_details('partner_details', ['partner_id' => $partner_id])[0];
                $to_send_id = $partner_id;
                $builder = $this->db->table('users')->select('fcm_id,email,platform');
                $users_fcm = $builder->where('id', $to_send_id)->get()->getResultArray();
                foreach ($users_fcm as $ids) {
                    if ($ids['fcm_id'] != "") {
                        $fcm_ids['fcm_id'] = $ids['fcm_id'];
                        $fcm_ids['platform'] = $ids['platform'];
                        $email = $ids['email'];
                    }
                }
                if ($partner_approval) {
                    if (!empty($fcm_ids)  && check_notification_setting('provider_disapproved', 'notification')) {
                        $fcmMsg = array(
                            'content_available' => "true",
                            'title' => $this->trans->registrationRequestRejection,
                            'body' => $this->trans->registrationRequestRejectedMessage,
                            'type_id' => "$to_send_id",
                            'type' => 'provider_request_status',
                            'status' => 'reject',
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        );
                        $registrationIDs_chunks = array_chunk($users_fcm, 1000);
                        send_notification($fcmMsg, $registrationIDs_chunks);
                    }
                    if (!empty($users_fcm[0]['email']) && check_notification_setting('provider_disapproved', 'email') && is_unsubscribe_enabled($partner_id) == 1) {
                        send_custom_email('provider_disapproved', $partner_id, $users_fcm[0]['email']);
                    }
                    if (check_notification_setting('provider_disapproved', 'sms')) {
                        send_custom_sms('provider_disapproved', $partner_id, $users_fcm[0]['email']);
                    }
                    return successResponse("Partner is disapproved", false, [$partner_approval], [], 200, csrf_token(), csrf_hash());
                } else {
                    return successResponse("Could not disapprove partner", false, [$partner_approval], [], 200, csrf_token(), csrf_hash());
                }
            } catch (\Exception $th) {

                return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - disapprove_partner()');
            return ErrorResponse('Something Went Wrong', true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function delete_partner()
    {
        try {
            $result = checkModificationInDemoMode($this->superadmin);
            if ($result !== true) {
                return $this->response->setJSON($result);
            }
            $permission = is_permitted($this->creator_id, 'delete', 'partner');
            if (!$permission) {
                return NoPermission();
            }
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            $partner_id = $this->request->getPost('partner_id');
            $service_details = fetch_details('services', ['user_id' => $partner_id]);
            $partner_timing_details = fetch_details('partner_timings', ['partner_id' => $partner_id]);
            $partner_details = fetch_details('partner_details', ['partner_id' => $partner_id]);
            $user_details = fetch_details('users', ['id' => $partner_id]);
            $user_group_details = fetch_details('users_groups', ['user_id' => $partner_id]);
            if (!empty($service_details)) {
                $builder = $this->db->table('services');
                $builder->delete(['user_id' => $partner_id]);
            }
            if (!empty($partner_timing_details)) {
                $builder = $this->db->table('partner_timings');
                $builder->delete(['partner_id' => $partner_id]);
            }
            if (!empty($user_group_details)) {
                $builder = $this->db->table('users_groups');
                $builder->delete(['user_id' => $partner_id]);
            }

            $disk = fetch_current_file_manager();

            if (!empty($partner_details)) {
                if (!empty($partner_details[0]['banner'])) {
                    delete_file_based_on_server('banner', $partner_details[0]['banner'],  $disk);
                }
                if (!empty($partner_details[0]['address_id'])) {
                    delete_file_based_on_server('address_id', $partner_details[0]['address_id'],  $disk);
                }
                if (!empty($partner_details[0]['passport'])) {
                    delete_file_based_on_server('passport', $partner_details[0]['passport'], $disk);
                }
                if (!empty($partner_details[0]['national_id'])) {
                    delete_file_based_on_server('national_id', $partner_details[0]['national_id'], $disk);
                }
                $builder = $this->db->table('partner_details');
                $builder->delete(['partner_id' => $partner_id]);
            }
            if (!empty($user_details)) {
                $builder = $this->db->table('users');
                $partner_approval = $builder->delete(['id' => $partner_id]);
                if ($partner_approval) {
                    return successResponse("Partner is Removed", false, [$partner_approval], [], 200, csrf_token(), csrf_hash());
                } else {
                    return successResponse("Could not Delete partner", false, [$partner_approval], [], 200, csrf_token(), csrf_hash());
                }
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - delete_partner()');
            return ErrorResponse('Something Went Wrong', true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function payment_request()
    {
        try {
            helper('function');
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            setPageInfo($this->data, labels('partners', 'Partners') . ' | ' . labels('admin_panel', 'Admin Panel'), 'payment_request');
            return view('backend/admin/template', $this->data);
        } catch (\Exception $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - payment_request()');
            return ErrorResponse('Something Went Wrong', true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function payment_request_list()
    {
        try {
            $payment_requests = new Payment_request_model();
            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'p.id';
            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
            $data = $payment_requests->list(false, $search, $limit, $offset, $sort, $order);
            return $data;
        } catch (\Exception $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - payment_request_list()');
            return ErrorResponse('Something Went Wrong', true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function pay_partner()
    {
        try {
            $result = checkModificationInDemoMode($this->superadmin);
            if ($result !== true) {
                return $this->response->setJSON($result);
            }
            $admin_id =  $this->userId;
            $pr_id = $this->request->getPost('request_id');
            $user_id = $this->request->getPost('user_id');
            $reason = $this->request->getPost('reason');
            $amount = $this->request->getPost('amount');
            $status = $this->request->getPost('status');
            $partner_details  = fetch_details('users', ['id' => $user_id]);
            $admin_details  = fetch_details('users', ['id' => $admin_id]);
            if ($status == 1) {
                if (!empty($partner_details)) {
                    $update_request = update_details(
                        ['remarks' => $reason, 'status' => $status],
                        ['id' => $pr_id],
                        'payment_request'
                    );
                    $update_balance =  (int)$admin_details[0]['balance'] + $amount;
                    $update_admin = update_details(
                        ['balance' => $update_balance],
                        ['id' => $admin_id],
                        'users'
                    );
                    add_settlement_cashcollection_history($reason, 'settled_by_payment_request', date('Y-m-d'), date('H:i:s'), $amount, $user_id, '', $pr_id, '', $amount, '');
                    if ($update_admin) {
                        $to_send_id = $user_id;
                        $builder = $this->db->table('users')->select('fcm_id,email,platform');
                        $users_fcm = $builder->where('id', $to_send_id)->get()->getResultArray();
                        foreach ($users_fcm as $ids) {
                            if ($ids['fcm_id'] != "") {
                                $fcm_ids['fcm_id'] = $ids['fcm_id'];
                                $fcm_ids['platform'] = $ids['platform'];
                                $email = $ids['email'];
                            }
                        }
                        if (!empty($fcm_ids) && check_notification_setting('withdraw_request_approved', 'notification')) {
                            $fcmMsg = array(
                                'content_available' => "true",
                                'title' => $this->trans->paymentRequestApproval,
                                'body' => $this->trans->paymentRequestApprovedMessage,
                                'type' => 'withdraw_request',
                                'status' => 'approve',
                                'type_id' => "$to_send_id",
                                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            );
                            $registrationIDs_chunks = array_chunk($users_fcm, 1000);
                            send_notification($fcmMsg, $registrationIDs_chunks);
                        }
                        if (!empty($users_fcm[0]['email']) && check_notification_setting('withdraw_request_approved', 'email') && is_unsubscribe_enabled($user_id) == 1) {
                            send_custom_email('withdraw_request_approved', $user_id, $users_fcm[0]['email'], $amount);
                        }
                        if (check_notification_setting('withdraw_request_approved', 'sms')) {
                            send_custom_sms('withdraw_request_approved', $user_id, $users_fcm[0]['email'], $amount);
                        }
                        return successResponse("debited amount $amount", false, [], [], 200, csrf_token(), csrf_hash());
                    }
                }
            } else {
                $update_balance =  (int)$partner_details[0]['balance'] + $amount;
                $update_id = update_details(['balance' => $update_balance], ['id' => $user_id], 'users');
                update_details(
                    [
                        'remarks' => $reason,
                        'status' => $status
                    ],
                    ['id' => $pr_id],
                    'payment_request'
                );
                if ($update_id) {
                    $to_send_id = $user_id;
                    $builder = $this->db->table('users')->select('fcm_id,email,platform');
                    $users_fcm = $builder->where('id', $to_send_id)->get()->getResultArray();
                    foreach ($users_fcm as $ids) {
                        if ($ids['fcm_id'] != "") {
                            $fcm_ids['fcm_id'] = $ids['fcm_id'];
                            $fcm_ids['platform'] = $ids['platform'];
                            $email = $ids['email'];
                        }
                    }
                    if (!empty($fcm_ids) && check_notification_setting('withdraw_request_disapproved', 'notification')) {
                        $registrationIDs = $fcm_ids;
                        $fcmMsg = array(
                            'content_available' => "true",
                            'title' => $this->trans->paymentRequestRejection,
                            'body' =>  $this->trans->paymentRequestRejectedMessage,
                            'type' => 'withdraw_request',
                            'status' => 'reject',
                            'type_id' => "$to_send_id",
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        );
                        $registrationIDs_chunks = array_chunk($users_fcm, 1000);
                        send_notification($fcmMsg, $registrationIDs_chunks);
                    }
                    if (!empty($users_fcm[0]['email']) && check_notification_setting('withdraw_request_disapproved', 'email') && is_unsubscribe_enabled($user_id) == 1) {
                        send_custom_email('withdraw_request_disapproved', $user_id, $users_fcm[0]['email'], $amount);
                    }
                    if (check_notification_setting('withdraw_request_disapproved', 'sms')) {
                        send_custom_sms('withdraw_request_disapproved', $user_id, $users_fcm[0]['email'], $amount);
                    }
                    return successResponse("Rejection occurred", false, [], [], 200, csrf_token(), csrf_hash());
                } else {
                    return ErrorResponse("Some error occurred", true, [], [], 200, csrf_token(), csrf_hash());
                }
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - pay_partner()');
            return ErrorResponse('Something Went Wrong', true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function delete_request()
    {
        try {
            $result = checkModificationInDemoMode($this->superadmin);
            if ($result !== true) {
                return $this->response->setJSON($result);
            }
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            try {
                $id = $this->request->getPost('id');
                $builder = $this->db->table('payment_request')->delete(['id' => $id]);
                if ($builder) {
                    return successResponse("Deleted payment request success", false, [], [], 200, csrf_token(), csrf_hash());
                } else {
                    return ErrorResponse("Couldnt delete payment request", true, [], [], 200, csrf_token(), csrf_hash());
                }
            } catch (\Exception $th) {
                return ErrorResponse('Something Went Wrong', true, [], [], 200, csrf_token(), csrf_hash());
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - delete_request()');
            return ErrorResponse('Something Went Wrong', true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function partner_details()
    {
        try {
            helper('function');
            $uri = service('uri');
            $partner_id = $uri->getSegments()[3];
            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
            print_r(json_encode($this->partner->list(false, $search, $limit, $offset, $sort, $order, ["pd.partner_id " => $partner_id])));
        } catch (\Exception $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - partner_details()');
            return ErrorResponse('Something Went Wrong', true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function banking_details()
    {
        try {
            $uri = service('uri');
            $partner_id = $uri->getSegments()[3];
            $db      = \Config\Database::connect();
            $builder = $db->table('partner_details pd');
            $count = $builder->select('COUNT(pd.id) as total')
                ->where('pd.partner_id', $partner_id)->get()->getResultArray();
            $total = $count[0]['total'];
            $tempRow = array();
            $data =  $builder->select('pd.*, u.city')
                ->join('users u', 'u.id = pd.partner_id')
                ->where('pd.partner_id', $partner_id)->get()->getResultArray();
            $rows = [];
            foreach ($data as $row) {
                $tempRow['partner_id'] = $row['partner_id'];
                $tempRow['name'] = $row['city'];
                $tempRow['passport'] = $row['passport'];
                $tempRow['tax_name'] = $row['tax_name'];
                $tempRow['tax_number'] = $row['tax_number'];
                $tempRow['bank_name'] = $row['bank_name'];
                $tempRow['account_number'] = $row['account_number'];
                $tempRow['account_name'] = $row['account_name'];
                $tempRow['bank_code'] = $row['bank_code'];
                $tempRow['swift_code'] = $row['swift_code'];
                $rows[] = $tempRow;
            }
            $bulkData['total'] = $total;
            $bulkData['rows'] = $rows;
            return json_encode($bulkData);
        } catch (\Exception $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - banking_details()');
            return ErrorResponse('Something Went Wrong', true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function timing_details()
    {
        try {
            $uri = service('uri');
            $partner_id = $uri->getSegments()[3];
            $db      = \Config\Database::connect();
            $builder = $db->table('partner_timings pt');
            $count = $builder->select('COUNT(pt.id) as total')
                ->where('pt.partner_id', $partner_id)->get()->getResultArray();
            $total = $count[0]['total'];
            $tempRow = array();
            $data =  $builder->select('pt.*,')
                ->where('pt.partner_id', $partner_id)->get()->getResultArray();
            $rows = [];
            foreach ($data as $row) {
                $label = ($row['is_open'] == 1) ?
                    '<div class="badge badge-success projects-badge">'. labels('open', 'Open') .'</div>' :
                    '<div class="badge badge-danger projects-badge">'. labels('closed', 'Closed') .'</div>';
                $tempRow['partner_id'] = $row['partner_id'];
                $label_new = ($row['is_open'] == 1) ?
                    "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-success text-emerald-success dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>". labels('open', 'Open') ."
                    </div>" :
                    "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-danger text-emerald-danger dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>" . labels('closed', 'Closed') ."
                    </div>";
                $tempRow['partner_id'] = $row['partner_id'];
                $tempRow['day'] = $row['day'];
                $tempRow['opening_time'] = $row['opening_time'];
                $tempRow['closing_time'] = $row['closing_time'];
                $tempRow['is_open'] = $label;
                $tempRow['is_open_new'] = $label_new;
                $rows[] = $tempRow;
            }
            $bulkData['total'] = $total;
            $bulkData['rows'] = $rows;
            return json_encode($bulkData);
        } catch (\Exception $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - timing_details()');
            return ErrorResponse('Something Went Wrong', true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function service_details()
    {
        try {
            $uri = service('uri');
            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
            $service_model = new Service_model();
            $where['s.user_id'] = $uri->getSegments()[3];
            $services =  $service_model->list(false, $search, $limit, $offset, $sort, $order, $where);
            return ($services);
        } catch (\Exception $th) {

            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - service_details()');
            return ErrorResponse('Something Went Wrong', true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function settle_commission()
    {
        try {
            helper('function');
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            setPageInfo($this->data, labels('commission_settlement', 'Commission Settlement') . ' | ' . labels('admin_panel', 'Admin Panel'), 'manage_commission');
            return view('backend/admin/template', $this->data);
        } catch (\Exception $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - settle_commission()');
            return ErrorResponse('Something Went Wrong', true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function cash_collection()
    {
        try {
            helper('function');
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            setPageInfo($this->data, labels('cash_collection', 'Cash Collection') . ' | ' . labels('admin_panel', 'Admin Panel'), 'cash_collection');
            return view('backend/admin/template', $this->data);
        } catch (\Exception $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - cash_collection()');
            return ErrorResponse('Something Went Wrong', true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function commission_list()
    {
        try {
            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
            return json_encode($this->partner->unsettled_commission_list(false, $search, $limit, $offset, $sort, $order));
        } catch (\Exception $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - commission_list()');
            return ErrorResponse('Something Went Wrong', true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function cash_collection_list()
    {
        try {
            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
            $data = json_encode($this->partner->list(false, $search, $limit, $offset, $sort, $order));
            print_r(json_encode($this->partner->list(false, $search, $limit, $offset, $sort, $order)));
        } catch (\Exception $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - cash_collection_list()');
            return ErrorResponse('Something Went Wrong', true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function commission_pay_out()
    {
        try {
            $result = checkModificationInDemoMode($this->superadmin);
            if ($result !== true) {
                return $this->response->setJSON($result);
            }
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            $partner_id = $this->request->getPost('partner_id');
            $amount = $this->request->getPost('amount');
            $current_balance = fetch_details('users', ['id' => $partner_id], ['balance', 'email'])[0];
            $partner_data    = fetch_details('partner_details', ['partner_id' => $partner_id], ['company_name'])[0];
            $rules = [
                'amount' => ['rules' => 'required|numeric|greater_than[0]', 'errors' => ['required' => 'Please enter commission',    'numeric' => 'Please enter a numeric value for commission',    'greater_than' => 'Amount must be greater than 0',]],
            ];
            if ($current_balance['balance'] > 0) {
                $rules['amount']['rules'] .= '|less_than_equal_to[' . $current_balance['balance'] . ']';
                $rules['amount']['errors']['less_than_equal_to'] = 'Amount must be less than or equal to current balance';
            } else {
                $rules['amount']['rules'] .= '|max_length[0]';
                $rules['amount']['errors']['max_length'] = 'Cannot withdraw when balance is 0 or less';
            }
            $this->validation->setRules($rules);
            if (!$this->validation->withRequest($this->request)->run()) {
                $errors  = $this->validation->getErrors();
                return ErrorResponse($errors, true, [], [], 200, csrf_token(), csrf_hash());
            }
            $updated_balance = $current_balance['balance'] - $amount;
            $update = update_details(['balance' => $updated_balance], ['id' => $partner_id], 'users');
            $t = time();
            $data = [
                'transaction_type' => 'transaction',
                'user_id' => $this->userId,
                'partner_id' => $partner_id,
                'order_id' =>  "TXN-$t",
                'type' => 'fund_transfer',
                'txn_id' => '',
                'amount' =>  $amount,
                'status' => 'success',
                'currency_code' => NULL,
                'message' => 'commission settled'
            ];
            $settlement_history = [
                'provider_id' => $partner_id,
                'message' =>   $this->request->getPost('message'),
                'amount' =>  $amount,
                'status' => 'credit',
                'date' => date("Y-m-d H:i:s"),
            ];
            insert_details($settlement_history, 'settlement_history');
            add_settlement_cashcollection_history('Settled By admin', 'settled_by_settlement', date('d-m-t'), date('h:i'), $amount, $partner_id, '', '', '', $amount, '');
            if ($update) {
                if (add_transaction($data)) {
                    $to_send_id = $partner_id;
                    $builder = $this->db->table('users')->select('fcm_id,email,platform');
                    $users_fcm = $builder->where('id', $to_send_id)->get()->getResultArray();
                    foreach ($users_fcm as $ids) {
                        if ($ids['fcm_id'] != "") {
                            $fcm_ids['fcm_id'] = $ids['fcm_id'];
                            $fcm_ids['platform'] = $ids['platform'];
                            $email = $ids['email'];
                        }
                    }
                    if (!empty($fcm_ids) &&  check_notification_setting('payment_settlement', 'notification')) {
                        $registrationIDs_chunks = array_chunk($users_fcm, 1000);
                        $fcmMsg = array(
                            'content_available' => "true",
                            'title' => $this->trans->paymentSettlementFor . " " . $partner_data['company_name'],
                            'body' => $this->trans->paymentSettlementConfirmation,
                            'type' => 'settlement',
                            'type_id' => "$to_send_id",
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        );
                        send_notification($fcmMsg, $registrationIDs_chunks);
                    }
                    if (!empty($users_fcm[0]['email']) && check_notification_setting('payment_settlement', 'email') && is_unsubscribe_enabled($partner_id) == 1) {
                        send_custom_email('payment_settlement', $partner_id, $users_fcm[0]['email'], $amount);
                    }
                    if (check_notification_setting('payment_settlement', 'sms')) {
                        send_custom_sms('payment_settlement', $partner_id, $users_fcm[0]['email'], $amount);
                    }
                    return successResponse("Commission Settled Successfully", false, [], [], 200, csrf_token(), csrf_hash());
                } else {
                    return ErrorResponse("Unsuccessful while adding transaction", true, [], [], 200, csrf_token(), csrf_hash());
                }
            } else {
                return ErrorResponse("Unsuccessful while Updating settling status", true, [], [], 200, csrf_token(), csrf_hash());
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - commission_pay_out()');
            return ErrorResponse('Something Went Wrong', true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function view_ratings()
    {
        try {
            $uri = service('uri');
            $partner_id = $uri->getSegments()[3];
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            $ratings_model = new Service_ratings_model();
            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
            return json_encode($ratings_model->ratings_list(false, $search, $limit, $offset, $sort, $order, ['s.user_id' => $partner_id]));
        } catch (\Exception $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - view_ratings()');
            return ErrorResponse('Something Went Wrong', true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function delete_rating()
    {
        $result = checkModificationInDemoMode($this->superadmin);
        if ($result !== true) {
            return $this->response->setJSON($result);
        }
        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            $id = $this->request->getPost('id');
            $data = $this->db->table('services_ratings')->delete(['id' => $id]);
            if ($data) {
                return successResponse("Rating deleted successfully", false, [], [], 200, csrf_token(), csrf_hash());
            } else {
                return ErrorResponse('unsuccessful in deletion of rating', true, [], [], 200, csrf_token(), csrf_hash());
            }
        } catch (\Exception $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - delete_rating()');
            return ErrorResponse('Something Went Wrong', true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function update_partner()
    {
        try {
            $result = checkModificationInDemoMode($this->superadmin);
            if ($result !== true) {
                return $this->response->setJSON($result);
            }
            if (isset($_POST) && !empty($_POST)) {
                $config = new \Config\IonAuth();
                $tables  = $config->tables;
                $this->validation->setRules(
                    [
                        'username' => ["rules" => 'required|trim', "errors" => ["required" => "Please enter username"]],
                        'email' => ["rules" => 'required|trim', "errors" => ["required" => "Please enter providers email",]],
                        'address' => ["rules" => 'required|trim', "errors" => ["required" => "Please enter address",]],
                        'type' => ["rules" => 'required', "errors" => ["required" => "Please select providers type",]],
                        'visiting_charges' => ["rules" => 'required|numeric', "errors" => ["required" => "Please enter visiting charges",    "numeric" => "Please enter numeric value for visiting charges"]],
                        'advance_booking_days' => ["rules" => 'required|numeric', "errors" => ["required" => "Please enter advance booking days",    "numeric" => "Please enter numeric advance booking days"]],
                        'start_time' => ["rules" => 'required', "errors" => ["required" => "Please enter providers working days",]],
                        'end_time' => ["rules" => 'required', "errors" => ["required" => "Please enter providers working properly",]],
                        'provider_slug' => ["rules" => 'required|trim', "errors" => ["required" => "Please enter providers slug",]],
                    ],
                );
                if (!$this->validation->withRequest($this->request)->run()) {
                    $errors = $this->validation->getErrors();
                    return ErrorResponse($errors, true, [], [], 200, csrf_token(), csrf_hash());
                }
                if (!preg_match('/^-?(90|[1-8][0-9][.][0-9]{1,20}|[0-9][.][0-9]{1,20})$/', $this->request->getPost('partner_latitude'))) {
                    return ErrorResponse('Please enter valid latitude', true, [], [], 200, csrf_token(), csrf_hash());
                }
                if (!preg_match('/^-?(180(\.0{1,20})?|1[0-7][0-9](\.[0-9]{1,20})?|[1-9][0-9](\.[0-9]{1,20})?|[0-9](\.[0-9]{1,20})?)$/', $this->request->getPost('partner_longitude'))) {
                    return ErrorResponse('Please enter a valid Longitude', true, [], [], 200, csrf_token(), csrf_hash());
                }
                $disk = fetch_current_file_manager();

                $folders = [
                    'public/backend/assets/profile/' => "Failed to create profile folders",
                    'public/backend/assets/national_id/' => "Failed to create national_id folders",
                    'public/backend/assets/address_id/' => "Failed to create address_id folders",
                    'public/backend/assets/passport/' => "Failed to create passport folders",
                    'public/backend/assets/banner/' => "Failed to create banner folders",
                ];
                foreach ($folders as $path => $errorMessage) {
                    if (!create_folder($path)) {
                        return ErrorResponse($errorMessage, true, [], [], 200, csrf_token(), csrf_hash());
                    }
                }
                $data = fetch_details('users', ['id' => $this->request->getPost('partner_id')], ['image'])[0];
                $IdProofs = fetch_details('partner_details', ['partner_id' => $this->request->getPost('partner_id'),], ['national_id', 'other_images', 'address_id', 'passport', 'banner',])[0];
                $old_national_id = $IdProofs['national_id'];
                $old_address_id = $IdProofs['address_id'];
                $old_passport = $IdProofs['passport'];
                $old_banner = $IdProofs['banner'];
                $old_image = $data['image'];
                $paths = [
                    'image' => ['file' => $this->request->getFile('image'), 'path' => 'public/backend/assets/profile/', 'error' => labels('Failed to create profile folders', 'Failed to create profile folders'), 'folder' => 'profile', 'old_file' => $old_image, 'disk' =>  $disk,],
                    'banner_image' => ['file' => $this->request->getFile('banner_image'), 'path' => 'public/backend/assets/banner/', 'error' => labels('Failed to create banner folders', 'Failed to create banner folders'), 'folder' => 'banner', 'old_file' => $old_banner, 'disk' =>  $disk,],
                    'national_id' => ['file' => $this->request->getFile('national_id'), 'path' => 'public/backend/assets/national_id/', 'error' => labels('Failed to create national_id folders', 'Failed to create national_id folders'), 'folder' => 'national_id', 'old_file' => $old_national_id, 'disk' =>  $disk,],
                    'address_id' => ['file' => $this->request->getFile('address_id'), 'path' => 'public/backend/assets/address_id/', 'error' => labels('Failed to create address_id folders', 'Failed to create address_id folders'), 'folder' => 'address_id', 'old_file' => $old_address_id, 'disk' =>  $disk,],
                    'passport' => ['file' => $this->request->getFile('passport'), 'path' => 'public/backend/assets/passport/', 'error' => labels('Failed to create passport folders', 'Failed to create passport folders'), 'folder' => 'passport', 'old_file' => $old_passport, 'disk' =>  $disk]
                ];
                // Process single file uploads
                $uploadedFiles = [];
                foreach ($paths as $key => $config) {
                    if (!empty($_FILES[$key]) && isset($_FILES[$key])) {


                        $file = $config['file'];
                        $path = './' . $config['path'];
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



                $multipleFiles = $this->request->getFiles('filepond');
                $uploadedOtherImages = [];
                $old_other_images_array = json_decode($IdProofs['other_images'], true);
                $other_images_disk = $disk;
                if (isset($multipleFiles['other_service_image_selector_edit'])) {
                    foreach ($multipleFiles['other_service_image_selector_edit'] as $file) {
                        if ($file->isValid()) {
                            // Handle Other Images Deletion
                            if (!empty($old_other_images_array)) {
                                foreach ($old_other_images_array as $old_image) {
                                    delete_file_based_on_server('partner', $old_image, $other_images_disk);
                                }
                            }
                            $result = upload_file($file, 'public/uploads/partner/', labels('Failed to upload other images', 'Failed to upload other images'), 'partner');
                            if ($result['error'] == false) {
                                $uploadedOtherImages[] = $result['disk'] === "local_server"
                                    ? 'public/uploads/partner/' . $result['file_name']
                                    : $result['file_name'];
                                $other_images_disk = $result['disk']; // Update disk
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
                    // $banner = 'public/backend/assets/banner/' . $uploadedFiles['banner_image']['url'];
                    $uploadedFiles['banner_image']['url'] = preg_replace('#(public/backend/assets/banner/)+#', '', $uploadedFiles['banner_image']['url']);
                    $banner = 'public/backend/assets/banner/' . $uploadedFiles['banner_image']['url'];
                }
                if (isset($uploadedFiles['national_id']['disk']) && $uploadedFiles['national_id']['disk'] == 'local_server') {
                    // $national_id = 'public/backend/assets/national_id/' . $uploadedFiles['national_id']['url'];
                    $uploadedFiles['national_id']['url'] = preg_replace('#^public/backend/assets/national_id/#', '', $uploadedFiles['national_id']['url']);
                    $national_id = 'public/backend/assets/national_id/' . $uploadedFiles['national_id']['url'];
                }
                if (isset($uploadedFiles['address_id']['disk']) && $uploadedFiles['address_id']['disk'] == 'local_server') {
                    // $address_id = 'public/backend/assets/address_id/' . $uploadedFiles['address_id']['url'];
                    $uploadedFiles['address_id']['url'] = preg_replace('#^public/backend/assets/address_id/#', '', $uploadedFiles['address_id']['url']);
                    $address_id = 'public/backend/assets/address_id/' . $uploadedFiles['address_id']['url'];
                }
                if (isset($uploadedFiles['passport']['disk']) && $uploadedFiles['passport']['disk'] == 'local_server') {
                    // $passport = 'public/backend/assets/passport/' . $uploadedFiles['passport']['url'];

                    $uploadedFiles['passport']['url'] = preg_replace('#^public/backend/assets/passport/#', '', $uploadedFiles['passport']['url']);
                    $passport = 'public/backend/assets/passport/' . $uploadedFiles['passport']['url'];
                }
                // Update partner details
                $partnerIDS = [
                    'address_id' => $address_id,
                    'national_id' => $national_id,
                    'passport' => $passport,
                    'banner' => $banner,
                ];
                if ($partnerIDS) {
                    update_details(
                        $partnerIDS,
                        ['partner_id' => $this->request->getPost('partner_id')],
                        'partner_details',
                        false
                    );
                }
                $phone = $_POST['phone'];
                $country_code = $_POST['country_code'];
                $image = $uploadedFiles['image']['url'] ?? 'public/backend/assets/profile/' . $this->request->getFile('image')->getName();
                if (isset($uploadedFiles['image']['disk']) && $uploadedFiles['image']['disk'] == 'local_server') {
                    // $image = 'public/backend/assets/profile/' . $uploadedFiles['image']['url'];

                    $uploadedFiles['image']['url'] = preg_replace('#^public/backend/assets/profile/#', '', $uploadedFiles['image']['url']);
                    $image = 'public/backend/assets/profile/' . $uploadedFiles['image']['url'];
                }
                $userData = [
                    'username' => $this->request->getPost('username'),
                    'email' => $this->request->getPost('email'),
                    'phone' => $phone,
                    'country_code' => $country_code,
                    'image' =>  $image,
                    'latitude' => $this->request->getPost('partner_latitude'),
                    'longitude' => $this->request->getPost('partner_longitude'),
                    'city' => $this->request->getPost('city'),
                ];
                // Sanitize all inputs
                $userData = sanitizeInput($userData);
                if ($userData) {
                    update_details($userData, ['id' => $this->request->getPost('partner_id')], 'users');
                }
                $is_approved = isset($_POST['is_approved']) ? "1" : "0";
                $partner_details = [
                    'company_name' => $this->request->getPost('company_name'),
                    'type' => $this->request->getPost('type'),
                    'visiting_charges' => $this->request->getPost('visiting_charges'),
                    'about' => $this->request->getPost('about'),
                    'advance_booking_days' => $this->request->getPost('advance_booking_days'),
                    'bank_name' => $this->request->getPost('bank_name'),
                    'account_number' => $this->request->getPost('account_number'),
                    'account_name' => $this->request->getPost('account_name'),
                    'account_name' => $this->request->getPost('account_name'),
                    'bank_code' => $this->request->getPost('bank_code'),
                    'tax_name' => $this->request->getPost('bank_code'),
                    'tax_number' => $this->request->getPost('tax_number'),
                    'swift_code' => $this->request->getPost('swift_code'),
                    'number_of_members' => $this->request->getPost('number_of_members'),
                    'is_approved' => $is_approved,
                    'other_images' => $other_images,
                    'long_description' => (isset($_POST['long_description'])) ? $_POST['long_description'] : "",
                    'address' => $this->request->getPost('address'),
                    'at_store' => (isset($_POST['at_store'])) ? 1 : 0,
                    'at_doorstep' => (isset($_POST['at_doorstep'])) ? 1 : 0,
                    'need_approval_for_the_service' => (isset($_POST['need_approval_for_the_service'])) ? 1 : 0,
                    'chat' => (isset($_POST['chat'])) ? 1 : 0,
                    'pre_chat' => (isset($_POST['pre_chat'])) ? 1 : 0,

                    'slug' => generate_unique_slug($this->request->getPost('provider_slug'), 'partner_details', $this->request->getPost('partner_id')),
                ];
                if (!empty($partner_details)) {
                    update_details($partner_details, ['partner_id' =>  $this->request->getPost('partner_id')], 'partner_details', false);
                }
                $days = [
                    0 => 'monday',
                    1 => 'tuesday',
                    2 => 'wednesday',
                    3 => 'thursday',
                    4 => 'friday',
                    5 => 'saturday',
                    6 => 'sunday'
                ];
                for ($i = 0; $i < count($_POST['start_time']); $i++) {
                    $partner_timing = [];
                    $partner_timing['day'] = $days[$i];
                    if (isset($_POST['start_time'][$i])) {
                        $partner_timing['opening_time'] = $_POST['start_time'][$i];
                    }
                    if (isset($_POST['end_time'][$i])) {
                        $partner_timing['closing_time'] = $_POST['end_time'][$i];
                    }
                    $partner_timing['is_open'] = (isset($_POST[$days[$i]])) ? 1 : 0;
                    $timing_data = fetch_details('partner_timings', ['partner_id' => $this->request->getPost('partner_id'), 'day' => $days[$i]]);
                    if (count($timing_data) > 0) {
                        update_details($partner_timing, ['partner_id' => $this->request->getPost('partner_id'), 'day' => $days[$i]], 'partner_timings');
                    } else {
                        $partner_timing['partner_id'] = $this->request->getPost('partner_id');
                        insert_details($partner_timing, 'partner_timings');
                    }
                }
                return successResponse("Partner updated successfully", false, [], [], 200, csrf_token(), csrf_hash());
            }
        } catch (\Throwable $th) {

            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - update_partner()');
            return ErrorResponse('Something Went Wrong', true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function cash_collection_deduct()
    {
        try {
            $result = checkModificationInDemoMode($this->superadmin);
            if ($result !== true) {
                return $this->response->setJSON($result);
            }
            $partner_id = $this->request->getPost('partner_id');
            $amount = $this->request->getPost('amount');
            $message = $this->request->getPost('message');
            $current_balance = fetch_details('users', ['id' => $partner_id], ['payable_commision', 'email'])[0];
            $this->validation->setRules(
                [
                    'amount' => [
                        "rules" => 'required|numeric|less_than_equal_to[' . $current_balance['payable_commision'] . ']',
                        "errors" => [
                            "required" => "Please enter commission",
                            "numeric" => "Please enter numeric value for commission",
                            "less_than" => "Amount must be less than current payable commision",
                        ]
                    ],
                ],
            );
            if (!$this->validation->withRequest($this->request)->run()) {
                $errors  = $this->validation->getErrors();
                return ErrorResponse($errors, true, [], [], 200, csrf_token(), csrf_hash());
            }
            $cash_collecetion_data = [
                'user_id' => $this->userId,
                'message' => $message,
                'status' => 'admin_cash_recevied',
                'commison' => intval($amount),
                'partner_id' => $partner_id,
                'date' => date("Y-m-d"),
            ];
            insert_details($cash_collecetion_data, 'cash_collection');
            $updated_balance = $current_balance['payable_commision'] - intval($amount);
            $update = update_details(['payable_commision' => $updated_balance], ['id' => $partner_id], 'users');
            add_settlement_cashcollection_history($message, 'cash_collection_by_admin', date('Y-m-d'), date('h:i:s'), $amount, $partner_id, '', '', '', $amount, '');
            if ($update) {
                return successResponse("Successfully collected commision", false, [], [], 200, csrf_token(), csrf_hash());
            } else {
                return ErrorResponse('Unsuccessful while Updating settling status', true, [], [], 200, csrf_token(), csrf_hash());
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - cash_collection_deduct()');
            return ErrorResponse('Something Went Wrong', true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function cash_collection_history()
    {
        if (!$this->isLoggedIn || !$this->userIsAdmin) {
            return redirect('admin/login');
        }
        setPageInfo($this->data, labels('cash_collection', 'Cash Collection') . ' | '. labels('admin_panel', 'Admin Panel'), 'cash_collection_history');
        return view('backend/admin/template', $this->data);
    }
    public function settle_commission_history()
    {
        if (!$this->isLoggedIn || !$this->userIsAdmin) {
            return redirect('admin/login');
        }
        setPageInfo($this->data, labels('commission_settlement', 'Commision Settlement') . ' | ' . labels('admin_panel', 'Admin Panel'), 'commision_history');
        return view('backend/admin/template', $this->data);
    }
    public function manage_commission_history_list()
    {
        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
        print_r(json_encode($this->settle_commission->list(false, $search, $limit, $offset, $sort, $order)));
    }
    public function cash_collection_history_list()
    {
        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
        print_r(json_encode($this->cash_collection->list(false, $search, $limit, $offset, $sort, $order)));
    }
    public function payment_request_multiple_update()
    {
        try {
            $result = checkModificationInDemoMode($this->superadmin);
            if ($result !== true) {
                return $this->response->setJSON($result);
            }
            $db      = \Config\Database::connect();
            $builder = $db->table('payment_request');
            $count = true;
            for ($i = 0; $i < count($_POST['request_ids']); $i++) {
                $payment_request = fetch_details('payment_request', ['id' => $_POST['request_ids'][$i]]);
                foreach ($payment_request as $row) {
                    if (($row['status'] != $_POST['status'])) {
                        if (($row['status'] == "0" && ($_POST['status'] == "1" || $_POST['status'] == "2" || $_POST['status'] == "3"))) {
                            $builder->where('id', $row['id']);
                            $builder->update(['status' => $_POST['status']]);
                            $count = false;
                        } else if (($row['status'] == "1" && $_POST['status'] == "3")) {
                            $builder->where('id', $row['id']);
                            $builder->update(['status' => $_POST['status']]);
                            $count = false;
                        }
                    }
                    if ($count == true) {
                        return ErrorResponse('Cannot Update', true, [], [], 200, csrf_token(), csrf_hash());
                    } else {
                        return successResponse('Bulk update successfully', false, [], [], 200, csrf_token(), csrf_hash());
                    }
                }
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - payment_request_multiple_update()');
            return ErrorResponse('Something Went Wrong', true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function payment_request_settement_status()
    {
        try {
            $db     = \Config\Database::connect();
            $builder = $db->table('payment_request');
            $builder->where('id', $_POST['id']);
            $builder->update(['status' => '3']);
            return successResponse("Payment Request Settled Succssfully", false, [], [], 200, csrf_token(), csrf_hash());
        } catch (\Exception $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - payment_request_settement_status()');
            return ErrorResponse('Something Went Wrong', true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function bulk_commission_settelement()
    {
        try {
            $result = checkModificationInDemoMode($this->superadmin);
            if ($result !== true) {
                return $this->response->setJSON($result);
            }
            if (empty($_POST['request_ids'])) {
                return ErrorResponse('Select Provider', true, [], [], 200, csrf_token(), csrf_hash());
            }
            $db      = \Config\Database::connect();
            $builder = $db->table('users');
            $count = true;
            for ($i = 0; $i < count($_POST['request_ids']); $i++) {
                $user_details = fetch_details('users', ['id' => $_POST['request_ids'][$i]]);
                if ($user_details[0]['balance'] > 0) {
                    $count = false;
                    $data = [
                        'balance' => 0,
                    ];
                    $builder->where('id', $_POST['request_ids'][$i]);
                    $builder->update($data);
                    $settlement_history = [
                        'provider_id' => $_POST['request_ids'][$i],
                        'message' =>   $this->request->getPost('message'),
                        'amount' =>  $user_details[0]['balance'],
                        'status' => 'credit',
                        'date' => date("Y-m-d H:i:s"),
                    ];
                    insert_details($settlement_history, 'settlement_history');
                }
            }
            if ($count == true) {
                return ErrorResponse('Cannot Update', true, [], [], 200, csrf_token(), csrf_hash());
            } else {
                return successResponse("Bulk update successfully", false, [], [], 200, csrf_token(), csrf_hash());
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - bulk_commission_settelement()');
            return ErrorResponse('Something Went Wrong', true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function bulk_cash_collection()
    {
        try {
            $result = checkModificationInDemoMode($this->superadmin);
            if ($result !== true) {
                return $this->response->setJSON($result);
            }
            $db      = \Config\Database::connect();
            $builder = $db->table('users');
            $count = true;
            for ($i = 0; $i < count($_POST['request_ids']); $i++) {
                $user_details = fetch_details('users', ['id' => $_POST['request_ids'][$i]]);
                if ($user_details[0]['payable_commision'] > 0) {
                    $count = false;
                    $builder->where('id', $_POST['request_ids'][$i]);
                    $builder->update(['payable_commision' => 0]);
                    $cash_collecetion_data = [
                        'user_id' => $this->userId,
                        'message' => $this->request->getPost('message'),
                        'status' => 'admin_cash_recevied',
                        'commison' => intval($user_details[0]['payable_commision']),
                        'partner_id' => $_POST['request_ids'][$i],
                        'date' => date("Y-m-d"),
                    ];
                    insert_details($cash_collecetion_data, 'cash_collection');
                }
            }
            if ($count == true) {
                return ErrorResponse('Cannot Update', true, [], [], 200, csrf_token(), csrf_hash());
            } else {
                return successResponse("Bulk update successfully", false, [], [], 200, csrf_token(), csrf_hash());
            }
        } catch (\Exception $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - bulk_cash_collection()');
            return ErrorResponse('Something Went Wrong', true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function provider_details()
    {
        helper('function');
        if ($this->isLoggedIn && $this->userIsAdmin) {
            setPageInfo($this->data, labels('partner_detail', 'Partner Detail') . ' | ' . labels('admin_panel', 'Admin Panel'), 'provider_details');
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }
    public function general_outlook()
    {
        try {
            $uri = service('uri');
            helper('function');
            $partner_id = $uri->getSegments()[3];
            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'pd.id';
            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
            $this->data['partner'] = (($this->partner->list(false, $search, $limit, $offset, $sort, $order, ["pd.partner_id " => $partner_id])));
            $db = \Config\Database::connect();
            $id =  $uri->getSegments()[3];
            $builder = $db->table('orders o');
            $order_count = $builder->select('count(DISTINCT(o.id)) as total')->where(['o.partner_id' => $id])->get()->getResultArray();
            $total_services = $db->table('services s')->select('count(s.id) as `total`')->where(['user_id' => $id])->get()->getResultArray()[0]['total'];
            $total_balance = unsettled_commision($id);
            $total_promocodes = $db->table('promo_codes p')->select('count(p.id) as `total`')->where(['partner_id' => $id])->get()->getResultArray()[0]['total'];
            $provider_total_earning_chart = provider_total_earning_chart($id);
            $provider_already_withdraw_chart = provider_already_withdraw_chart($id);
            $provider_pending_withdraw_chart = provider_pending_withdraw_chart($id);
            $provider_withdraw_chart = provider_withdraw_chart($id);
            $where['partner_id'] =  $uri->getSegments()[3];
            $db = \Config\Database::connect();
            $id = $partner_id;
            $promo_codes = $db->table('promo_codes')->where(['partner_id' => $id])->where('start_date >', date('Y-m-d'))->orderBy('id', 'DESC')->limit(5, 0)->get()->getResultArray();
            $promocode_dates = array();
            $tempRow = array();
            $promocode_dates = array();
            foreach ($promo_codes as $promo_code) {
                $date = explode('-', $promo_code['start_date']);
                $newDate = $date[1] . '-' . $date[2];
                $newDate = explode(' ', $newDate);
                $newDate = $newDate[0];
                $tempRow['start_date'] = $newDate;
                $tempRow['promo_code'] = $promo_code['promo_code'];
                $tempRow['end_date'] = $promo_code['end_date'];
                $promocode_dates[] = $tempRow;
            }
            $ratings = new Service_ratings_model();
            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 0;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
            $partner_id_for_rating = $uri->getSegments()[3];
            $where_for_rating = "(s.user_id = {$partner_id_for_rating}) OR (pb.partner_id = {$partner_id_for_rating} AND sr.custom_job_request_id IS NOT NULL)";
            $data = $ratings->ratings_list(true, $search, $limit, $offset, $sort, $order, $where_for_rating);
            $total_review = $data['total'];
            $total_ratings = $db->table('partner_details p')->select('count(p.ratings) as `total`')->where(['id' => $id])->get()->getResultArray()[0]['total'];
            $already_withdraw = $db->table('payment_request p')->select('sum(p.amount) as total')->where(['user_id' => $id, "status" => 1])->get()->getResultArray()[0]['total'];
            $pending_withdraw = $db->table('payment_request p')->select('sum(p.amount) as total')->where(['user_id' => $id, "status" => 0])->get()->getResultArray()[0]['total'];
            $total_withdraw_request = $db->table('payment_request p')->select('count(p.id) as `total`')->where(['user_id' => $id])->get()->getResultArray()[0]['total'];
            $number_or_ratings = $db->table('partner_details p')->select('count(p.number_of_ratings) as `total`')->where(['id' => $id])->get()->getResultArray()[0]['total'];
            $income = $db->table('orders o')->select('count(o.id) as `total`')->where(['user_id' => $id])->where("created_at >= DATE(now()) - INTERVAL 7 DAY")->get()->getResultArray()[0]['total'];
            $symbol =   get_currency();
            $this->data['total_services'] = $total_services;
            $this->data['total_orders'] = $order_count[0]['total'];
            $this->data['total_balance'] =  number_format($total_balance, 2, ".", "");
            $this->data['total_ratings'] = $total_ratings;
            $this->data['total_review'] = $total_review;
            $this->data['number_of_ratings'] = $number_or_ratings;
            $this->data['currency'] = $symbol;
            $this->data['total_promocodes'] = $total_promocodes;
            $this->data['already_withdraw'] = $already_withdraw;
            $this->data['pending_withdraw'] = $pending_withdraw;
            $this->data['total_withdraw_request'] = $total_withdraw_request;
            $this->data['promocode_dates'] = $promocode_dates;
            $this->data['provider_total_earning_chart'] = $provider_total_earning_chart;
            $this->data['provider_already_withdraw_chart'] = $provider_already_withdraw_chart;
            $this->data['provider_pending_withdraw_chart'] = $provider_pending_withdraw_chart;
            $this->data['provider_withdraw_chart'] = $provider_withdraw_chart;
            $this->data['income'] = number_format($income, 2, ".", "");
            if ($this->isLoggedIn && $this->userIsAdmin) {
                setPageInfo($this->data, labels('provider_general_outlook', 'Provider General Outlook') . ' | ' . labels('admin_panel', 'Admin Panel'), 'partner_general_outlook');
                return view('backend/admin/template', $this->data);
            } else {
                return redirect('admin/login');
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - general_outlook()');
            return ErrorResponse('Something Went Wrong', true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function partner_company_information()
    {
        try {
            helper('function');
            $uri = service('uri');
            helper('function');
            $partner_id = $uri->getSegments()[3];
            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'pd.id';
            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
            $this->data['partner'] = (($this->partner->list(false, $search, $limit, $offset, $sort, $order, ["pd.partner_id " => $partner_id])));
            if ($this->isLoggedIn && $this->userIsAdmin) {
                setPageInfo($this->data, labels('partner_company_information', 'Partner Company Information') . ' | ' . labels('admin_panel', 'Admin Panel'), 'partner_company_information');
                return view('backend/admin/template', $this->data);
            } else {
                return redirect('admin/login');
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - partner_company_information()');
            return ErrorResponse('Something Went Wrong', true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function partner_service_details()
    {
        try {
            helper('function');
            $uri = service('uri');
            $partner_id = $uri->getSegments()[3];
            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
            $this->data['partner'] = (($this->partner->list(false, $search, $limit, $offset, $sort, $order, ["pd.partner_id " => $partner_id])));
            if ($this->isLoggedIn && $this->userIsAdmin) {
                setPageInfo($this->data, labels('Partner Service List', 'Partner Service List') . ' | ' . labels('admin_panel', 'Admin Panel'), 'partner_service_list');
                return view('backend/admin/template', $this->data);
            } else {
                return redirect('admin/login');
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - partner_service_details()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function partner_order_details()
    {
        try {
            helper('function');
            $uri = service('uri');
            $segments = $uri->getSegments();
            $partner_id = end($segments);
            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'pd.id';
            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
            $this->data['partner'] = (($this->partner->list(false, $search, $limit, $offset, $sort, $order, ["pd.partner_id " => $partner_id])));
            if ($this->isLoggedIn && $this->userIsAdmin) {
                setPageInfo($this->data, labels('Partner Order List', 'Partner Order List') . ' | ' . labels('admin_panel', 'Admin Panel'), 'partner_order_list');
                return view('backend/admin/template', $this->data);
            } else {
                return redirect('admin/login');
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - partner_order_details()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function partner_order_details_list()
    {
        try {
            helper('function');
            $uri = service('uri');
            $partner_id = $uri->getSegments()[3];
            $orders_model = new Orders_model();
            $where = ['o.partner_id' => $partner_id];
            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'pd.id';
            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'DESC';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
            return $orders_model->list(false, $search, $limit, $offset, $sort, $order, $where, '', '', '', '', '', '');
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - partner_order_details_list()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function partner_promocode_details()
    {
        try {
            helper('function');
            $uri = service('uri');
            $partner_id = $uri->getSegments()[3];
            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'pd.id';
            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
            $this->data['partner'] = (($this->partner->list(false, $search, $limit, $offset, $sort, $order, ["pd.partner_id " => $partner_id])));
            $partner_data = $this->db->table('users u')
                ->select('u.id,u.username,pd.company_name')
                ->join('partner_details pd', 'pd.partner_id = u.id')
                ->where('is_approved', '1')
                ->get()->getResultArray();
            $this->data['partner_name'] = $partner_data;
            if ($this->isLoggedIn && $this->userIsAdmin) {
                setPageInfo($this->data, labels('Partner Promocode List', 'Partner Promocode List') . ' | ' . labels('admin_panel', 'Admin Panel'), 'partner_promocode_details');
                return view('backend/admin/template', $this->data);
            } else {
                return redirect('admin/login');
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - partner_promocode_details()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function partner_promocode_details_list()
    {
        try {
            helper('function');
            $uri = service('uri');
            $partner_id = $uri->getSegments()[3];
            $promocode_model = new Promo_code_model();
            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'pd.id';
            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
            $where['partner_id'] = $partner_id;
            $promo_codes =  $promocode_model->list(false, $search, $limit, $offset, $sort, $order, $where);
            return $promo_codes;
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - partner_promocode_details_list()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function partner_review_details()
    {
        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            if ($this->isLoggedIn && $this->userIsAdmin) {
                helper('function');
                $uri = service('uri');
                $partner_id = $uri->getSegments()[3];
                $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
                $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
                $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'pd.id';
                $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
                $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
                $this->data['partner'] = (($this->partner->list(false, $search, $limit, $offset, $sort, $order, ["pd.partner_id " => $partner_id])));
                $rate_data = get_ratings($partner_id);
                $db      = \Config\Database::connect();

                $average_rating = $db->table('services_ratings sr')
                    ->select('
                    (SUM(sr.rating) / COUNT(sr.rating)) as average_rating
                ')
                    ->join('services s', 'sr.service_id = s.id', 'left')
                    ->join('custom_job_requests cj', 'sr.custom_job_request_id = cj.id', 'left')
                    ->join('partner_bids pd', 'pd.custom_job_request_id = cj.id', 'left')
                    ->where("(s.user_id = {$partner_id}) OR (pd.partner_id = {$partner_id})")
                    ->get()->getResultArray();
                $ratingData = array();
                $rows = array();
                $tempRow = array();
                foreach ($average_rating as $row) {
                    $tempRow['average_rating'] = (isset($row['average_rating']) &&  $row['average_rating'] != "") ?  number_format($row['average_rating'], 2) : 0;
                }
                foreach ($rate_data as $row) {
                    $tempRow['total_ratings'] = (isset($row['total_ratings']) && $row['total_ratings'] != "") ? $row['total_ratings'] : 0;
                    $tempRow['rating_5_percentage'] = (isset($row['rating_5']) && $row['rating_5'] != "") ? (($row['rating_5'] * 100) / $row['total_ratings']) : 0;
                    $tempRow['rating_4_percentage'] = (isset($row['rating_4']) && $row['rating_4'] != "") ? (($row['rating_4'] * 100) / $row['total_ratings'])  : 0;
                    $tempRow['rating_3_percentage'] = (isset($row['rating_3']) && $row['rating_3'] != "") ? (($row['rating_3'] * 100) / $row['total_ratings']) : 0;
                    $tempRow['rating_2_percentage'] = (isset($row['rating_2']) && $row['rating_2'] != "") ? (($row['rating_2'] * 100) / $row['total_ratings']) : 0;
                    $tempRow['rating_1_percentage'] = (isset($row['rating_1']) && $row['rating_1'] != "") ? (($row['rating_1'] * 100) / $row['total_ratings']) : 0;
                    $tempRow['rating_5'] = (isset($row['rating_5']) && $row['rating_5'] != "") ? ($row['rating_5']) : 0;
                    $tempRow['rating_4'] = (isset($row['rating_4']) && $row['rating_4'] != "") ?  ($row['rating_4'])  : 0;
                    $tempRow['rating_3'] = (isset($row['rating_3']) && $row['rating_3'] != "") ?  ($row['rating_3']) : 0;
                    $tempRow['rating_2'] = (isset($row['rating_2']) && $row['rating_2'] != "") ?  ($row['rating_2']) : 0;
                    $tempRow['rating_1'] = (isset($row['rating_1']) && $row['rating_1'] != "") ? ($row['rating_1']) : 0;
                    $rows[] = $tempRow;
                }
                $ratingData = $rows;
                $this->data['ratingData'] = $ratingData;
                setPageInfo($this->data, labels('Partner Review List', 'Partner Review List') . ' | ' . labels('admin_panel', 'Admin Panel'), 'partner_review_details');
                return view('backend/admin/template', $this->data);
            } else {
                return redirect('admin/login');
            }
            if ($this->isLoggedIn && $this->userIsAdmin) {
                setPageInfo($this->data, labels('Partner Review List', 'Partner Review List') . ' | ' . labels('admin_panel', 'Admin Panel'), 'partner_review_details');
                return view('backend/admin/template', $this->data);
            } else {
                return redirect('admin/login');
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - partner_review_details()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function partner_review_details_list()
    {
        try {
            helper('function');
            $uri = service('uri');
            $partner_id = $uri->getSegments()[3];
            $ratings_model = new Service_ratings_model();
            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'pd.id';
            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
            $where_for_rating = "(s.user_id = {$partner_id}) OR (pb.partner_id = {$partner_id} AND sr.custom_job_request_id IS NOT NULL)";
            return json_encode($ratings_model->ratings_list(false, $search, $limit, $offset, $sort, $order, $where_for_rating));
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - partner_review_details_list()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function partner_fetch_sales()
    {
        try {
            helper('function');
            $uri = service('uri');
            $partner_id = $uri->getSegments()[3];
            if (!$this->isLoggedIn) {
                return redirect('admin/login');
            } else {
                $sales[] = array();
                $db = \Config\Database::connect();
                $month_res = $db->table('orders')
                    ->select('SUM(final_total) AS total_sale,DATE_FORMAT(created_at,"%b") AS month_name ')
                    ->where('partner_id', $partner_id)
                    ->where('status', 'completed')
                    ->groupBy('year(CURDATE()),MONTH(created_at)')
                    ->orderBy('year(CURDATE()),MONTH(created_at)')
                    ->get()->getResultArray();
                $month_wise_sales['total_sale'] = array_map('intval', array_column($month_res, 'total_sale'));
                $month_wise_sales['month_name'] = array_column($month_res, 'month_name');
                $sales = $month_wise_sales;
                print_r(json_encode($sales));
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - partner_fetch_sales()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function partner_subscription()
    {
        try {
            helper('function');
            $uri = service('uri');
            $db      = \Config\Database::connect();
            $builder = $db->table('partner_subscriptions ps');
            $partner_id = $uri->getSegments()[3];
            $active_subscription_details = fetch_details('partner_subscriptions', ['partner_id' => $partner_id, 'status' => 'active']);
            $symbol =   get_currency();
            $this->data['currency'] = $symbol;
            $this->data['active_subscription_details'] = $active_subscription_details;
            $this->data['partner_id'] = $partner_id;
            $subscription_details = fetch_details('subscriptions', ['status' => 1]);
            $this->data['subscription_details'] = $subscription_details;
            if ($this->isLoggedIn && $this->userIsAdmin) {
                setPageInfo($this->data, labels('Partner Subscription', 'Partner Subscription') . ' | ' . labels('admin_panel', 'Admin Panel'), 'partner_subscription');
                return view('backend/admin/template', $this->data);
            } else {
                return redirect('admin/login');
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - partner_subscription()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function assign_subscription_to_partner()
    {
        try {
            $partner_id = $_POST['partner_id'];
            $subscription_id = $_POST['subscription_id'];
            $subscription_details = fetch_details('subscriptions', ['id' => $subscription_id]);
            $db      = \Config\Database::connect();
            $is_already_subscribe_builder = $db->table('partner_subscriptions')
                ->where(['partner_id' => $partner_id, 'status' => 'active']);
            $active_subscriptions = $is_already_subscribe_builder->get()->getResult();
            if (!empty($active_subscriptions) && !empty($active_subscriptions[0])) {
                $subscriptionToDelete = $active_subscriptions[0];
                $db->table('partner_subscriptions')
                    ->where('id', $subscriptionToDelete->id)
                    ->delete();
            }
            $price = calculate_subscription_price($subscription_details[0]['id']);
            $purchaseDate = date('Y-m-d');
            $subscriptionDuration = $subscription_details[0]['duration'];
            if ($subscriptionDuration == "unlimited") {
                $subscriptionDuration = 0;
            }
            $expiryDate = date('Y-m-d', strtotime($purchaseDate . ' + ' . $subscriptionDuration . ' days')); // Add the duration to the purchase date
            $partner_subscriptions = [
                'partner_id' =>  $partner_id,
                'subscription_id' => $subscription_id,
                'is_payment' => "1",
                'status' => "active",
                'purchase_date' => date('Y-m-d'),
                'expiry_date' =>  $expiryDate,
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
                'transaction_id' => '0',
                'tax_percentage' => $price[0]['tax_percentage']
            ];
            if ($subscription_details[0]['is_commision'] == "yes") {
                $commission = $subscription_details[0]['commission_percentage'];
            } else {
                $commission = 0;
            }
            update_details(['admin_commission' => $commission], ['partner_id' => $partner_id], 'partner_details');
            $data = insert_details($partner_subscriptions, 'partner_subscriptions');
            $errorMessage = labels('Assigned Subscription successfully', 'Assigned Subscription successfully');
            session()->setFlashdata('success', $errorMessage);
            return redirect()->to('admin/partners/partner_subscription/' . $partner_id);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - assign_subscription_to_partner()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function assign_subscription_to_partner_from_edit_provider()
    {
        try {
            $partner_id = $_POST['partner_id'];
            $subscription_id = $_POST['subscription_id'];
            $subscription_details = fetch_details('subscriptions', ['id' => $subscription_id]);
            $db      = \Config\Database::connect();
            $is_already_subscribe_builder = $db->table('partner_subscriptions')
                ->where(['partner_id' => $partner_id, 'status' => 'active']);
            $active_subscriptions = $is_already_subscribe_builder->get()->getResult();
            if (!empty($active_subscriptions) && !empty($active_subscriptions[0])) {
                $subscriptionToDelete = $active_subscriptions[0];
                $db->table('partner_subscriptions')
                    ->where('id', $subscriptionToDelete->id)
                    ->delete();
            }
            $price = calculate_subscription_price($subscription_details[0]['id']);
            $purchaseDate = date('Y-m-d');
            $subscriptionDuration = $subscription_details[0]['duration'];
            if ($subscriptionDuration == "unlimited") {
                $subscriptionDuration = 0;
            }
            $expiryDate = date('Y-m-d', strtotime($purchaseDate . ' + ' . $subscriptionDuration . ' days')); // Add the duration to the purchase date
            $partner_subscriptions = [
                'partner_id' =>  $partner_id,
                'subscription_id' => $subscription_id,
                'is_payment' => "1",
                'status' => "active",
                'purchase_date' => date('Y-m-d'),
                'expiry_date' =>  $expiryDate,
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
                'transaction_id' => '0',
                'tax_percentage' => $price[0]['tax_percentage']
            ];
            if ($subscription_details[0]['is_commision'] == "yes") {
                $commission = $subscription_details[0]['commission_percentage'];
            } else {
                $commission = 0;
            }
            update_details(['admin_commission' => $commission], ['partner_id' => $partner_id], 'partner_details');
            $data = insert_details($partner_subscriptions, 'partner_subscriptions');
            $errorMessage = labels('Assigned Subscription successfully', 'Assigned Subscription successfully');
            $response = [
                'error' => true,
                'message' => labels('Assigned Subscription successfully', 'Assigned Subscription successfully'),
                'csrfName' => csrf_token(),
                'csrfHash' => csrf_hash(),
            ];
            return $this->response->setJSON($response);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - assign_subscription_to_partner_from_edit_provider()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function cancel_subscription_plan()
    {
        try {
            $partner_id = $_POST['partner_id'];
            $db      = \Config\Database::connect();
            $is_already_subscribe_builder = $db->table('partner_subscriptions')
                ->where(['partner_id' => $partner_id, 'status' => 'active']);
            $active_subscriptions = $is_already_subscribe_builder->get()->getResult();
            if (!empty($active_subscriptions) && !empty($active_subscriptions[0])) {
                $subscriptionToDelete = $active_subscriptions[0];
                $data['status'] = 'deactive';
                $res = update_details($data, ['id' => $subscriptionToDelete->id], 'partner_subscriptions', true);
                $db = \Config\Database::connect();
            }
            $errorMessage = labels('Subscription Cancelled Successfully', 'Subscription Cancelled Successfully');
            session()->setFlashdata('success', $errorMessage);
            return redirect()->to('admin/partners/partner_subscription/' . $partner_id);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - assign_subscription_to_partner_from_edit_provider()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function cancel_subscription_plan_from_edit_partner()
    {
        try {
            $partner_id = $_POST['partner_id'];
            $db      = \Config\Database::connect();
            $is_already_subscribe_builder = $db->table('partner_subscriptions')
                ->where(['partner_id' => $partner_id, 'status' => 'active']);
            $active_subscriptions = $is_already_subscribe_builder->get()->getResult();
            if (!empty($active_subscriptions) && !empty($active_subscriptions[0])) {
                $subscriptionToDelete = $active_subscriptions[0];
                $data['status'] = 'deactive';
                $res = update_details($data, ['id' => $subscriptionToDelete->id], 'partner_subscriptions', true);
                $db = \Config\Database::connect();
            }
            $response = [
                'error' => true,
                'message' => labels('Subscription Cancelled Successfully', 'Subscription Cancelled Successfully'),
                'csrfName' => csrf_token(),
                'csrfHash' => csrf_hash(),
            ];
            return $this->response->setJSON($response);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - cancel_subscription_plan_from_edit_partner()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function all_subscription_list()
    {
        try {
            if ($this->isLoggedIn && $this->userIsAdmin) {
                setPageInfo($this->data, labels('All Subscription', 'All Subscription') . ' | ' . labels('admin_panel', 'Admin Panel'), 'all_subscription_list');
                $symbol =   get_currency();
                $this->data['currency'] = $symbol;
                $uri = service('uri');
                $partner_id = $uri->getSegments()[3];
                $this->data['partner_id'] = $partner_id;
                $subscription_details = fetch_details('subscriptions', ['status' => 1]);
                $this->data['subscription_details'] = $subscription_details;
                return view('backend/admin/template', $this->data);
            } else {
                return redirect('unauthorised');
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - all_subscription_list()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function partner_settlement_and_cash_collection_history()
    {
        try {
            helper('function');
            $uri = service('uri');
            $db      = \Config\Database::connect();
            $builder = $db->table('settlement_cashcollection_history ps');
            $partner_id = $uri->getSegments()[3];
            $active_subscription_details = fetch_details('settlement_cashcollection_history', ['provider_id' => $partner_id]);
            $symbol =   get_currency();
            $this->data['currency'] = $symbol;
            $this->data['active_subscription_details'] = $active_subscription_details;
            $this->data['partner_id'] = $partner_id;
            $subscription_details = fetch_details('subscriptions', ['status' => 1]);
            $this->data['subscription_details'] = $subscription_details;
            if ($this->isLoggedIn && $this->userIsAdmin) {
                setPageInfo($this->data, labels('Settlement And Cash Collection History', 'Settlement And Cash Collection History') . ' | ' . labels('admin_panel', 'Admin Panel'), 'partner_settlement_cashcollection_history');
                $partner_data = $this->db->table('users u')
                    ->select('u.id,u.username,pd.company_name')
                    ->join('partner_details pd', 'pd.partner_id = u.id')
                    ->where('u.id',  $partner_id)
                    ->get()->getResultArray();
                $this->data['partner'] = $partner_data;
                return view('backend/admin/template', $this->data);
            } else {
                return redirect('admin/login');
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - partner_settlement_and_cash_collection_history()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function partner_settlement_and_cash_collection_history_list()
    {
        try {
            helper('function');
            $uri = service('uri');
            $partner_id = $uri->getSegments()[3];
            $Settlement_CashCollection_history_model = new Settlement_CashCollection_history_model();
            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'DESC';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
            $where = ['sc.provider_id' => $partner_id];
            $data = $Settlement_CashCollection_history_model->list($where, 'no', false, $limit, $offset, $sort, $order, $search);
            return $data;
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - partner_settlement_and_cash_collection_history_list()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function all_settlement_cashcollection_history()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            setPageInfo($this->data, labels('Booking Payment Management', 'Booking Payment Management') . ' | ' . labels('admin_panel', 'Admin Panel'), 'all_settlement_cashcollection_history');
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }
    public function all_settlement_cashcollection_history_list()
    {
        try {
            $Settlement_CashCollection_history_model = new Settlement_CashCollection_history_model();
            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'DESC';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
            $where = [];
            $data = $Settlement_CashCollection_history_model->list($where, 'yes', false, $limit, $offset, $sort, $order, $search);
            return $data;
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - all_settlement_cashcollection_history_list()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function duplicate()
    {
        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            $permission = is_permitted($this->creator_id, 'create', 'partner');
            if (!$permission) {
                return NoPermission();
            }
            if ($this->isLoggedIn && $this->userIsAdmin) {
                setPageInfo($this->data, labels('Duplicate Partners', 'Duplicate Partners') . ' | ' . labels('admin_panel', 'Admin Panel'), 'duplicate_provider');
                $uri = service('uri');
                $partner_id = $uri->getSegments()[3];
                $partner_details = (fetch_details('partner_details', ['partner_id' => $partner_id]))[0];
                $partner_timings = (fetch_details('partner_timings', ['partner_id' => $partner_id]));
                $this->data['data'] = fetch_details('users', ['id' => $this->userId])[0];
                $currency = get_settings('general_settings', true);
                if (empty($currency)) {
                    $_SESSION['toastMessage'] = labels('Please first add currency and basic details in general settings', 'Please first add currency and basic details in general settings');
                    $_SESSION['toastMessageType'] = 'error';
                    $this->session->markAsFlashdata('toastMessage');
                    $this->session->markAsFlashdata('toastMessageType');
                    return redirect()->to('admin/settings/general-settings')->withCookies();
                }
                $this->data['currency'] = $currency['currency'];
                $this->data['partner_details'] = $partner_details;
                $this->data['partner_timings'] = $partner_timings;
                $user_details = fetch_details('users', ['id' => $partner_id])[0];
                $this->data['personal_details'] = $user_details;
                $this->data['city_name'] = fetch_details('cities', [], ['id', 'name']);
                $subscription_details = fetch_details('subscriptions', ['status' => 1]);
                $this->data['subscription_details'] = $subscription_details;
                return view('backend/admin/template', $this->data);
            } else {
                return redirect('admin/login');
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partners.php - duplicate()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function bulk_import()
    {
        if (!$this->isLoggedIn || !$this->userIsAdmin) {
            return redirect('admin/login');
        }
        $permission = is_permitted($this->creator_id, 'create', 'partner');
        if (!$permission) {
            return NoPermission();
        }
        setPageInfo($this->data, labels('Bulk Provider Update', 'Bulk Provider Update') . ' | ' . labels('admin_panel', 'Admin Panel'), 'bulk_add_partners');
        return view('backend/admin/template', $this->data);
    }
    public function downloadSampleForInsert()
    {
        $result = checkModificationInDemoMode($this->superadmin);
        if ($result !== true) {
            $_SESSION['toastMessage'] = $result['message'];
            $_SESSION['toastMessageType'] = 'error';
            $this->session->markAsFlashdata('toastMessage');
            $this->session->markAsFlashdata('toastMessageType');
            return redirect()->to('admin/partners/bulk_import')->withCookies();
        }
        try {
            $headers = [
                'Company Name',
                'Type',
                'Visiting Charge',
                'Advance Booking Days',
                'Number of Members',
                'At Store',
                'At Doorstep',
                'Need Approval for Service',
                'About Provider',
                'Description',
                'Address',
                'Tax Name',
                'Tax Number',
                'Account Number',
                'Account Name',
                'Bank Code',
                'Bank Name',
                'Swift Code',
                'Is Approved',
                'Username',
                'Email',
                'Phone',
                'Country Code',
                'Password',
                'City',
                'Latitude',
                'Longitude',
                'Monday Start Time',
                'Monday End Time',
                'Monday Is Open',
                'Tuesday Start Time',
                'Tuesday End Time',
                'Tuesday Is Open',
                'Wednesday Start Time',
                'Wednesday End Time',
                'Wednesday Is Open',
                'Thursday Start Time',
                'Thursday End Time',
                'Thursday Is Open',
                'Friday Start Time',
                'Friday End Time',
                'Friday Is Open',
                'Saturday Start Time',
                'Saturday End Time',
                'Saturday Is Open',
                'Sunday Start Time',
                'Sunday End Time',
                'Sunday Is Open',
                'Image',
                'Banner Image',
                'Passport',
                'National Identity',
                'Address id',
                'Other Image[1]',
                'Other Image[2]'
            ];
            $sampleData = [
                [
                    'Sample Company',
                    '1',
                    '60',
                    '365',
                    '3',
                    '1',
                    '1',
                    '0',
                    'Sample Company About provider',
                    'Sample Company long description',
                    'test123 , near test',
                    'TEST_TAX',
                    '46',
                    '781592',
                    'Sample Company',
                    'R9841',
                    'YYY',
                    'SWT12d',
                    '1',
                    'Sample Company',
                    'sample_company@gmail.com',
                    '4848945845',
                    '91',
                    '12345678',
                    'Test',
                    '28.743580',
                    '45.623705',
                    '09:00:00',
                    '18:00:00',
                    '1',
                    '09:00:00',
                    '18:00:00',
                    '1',
                    '09:00:00',
                    '18:00:00',
                    '1',
                    '09:00:00',
                    '18:00:00',
                    '1',
                    '09:00:00',
                    '18:00:00',
                    '1',
                    '09:00:00',
                    '18:00:00',
                    '1',
                    '09:00:00',
                    '18:00:00',
                    '1',
                    'public/backend/assets/profile/test.png',
                    'public/backend/assets/banner/test.png',
                    'public/backend/assets/passport/test.png',
                    'public/backend/assets/national_id/test.png',
                    'public/backend/assets/address_id/test.png',
                    'public/uploads/partner/test1.png',
                    'public/uploads/partner/test2.png',
                ],
            ];
            $output = fopen('php://output', 'w');
            if ($output === false) {
                throw new \Exception('Failed to open output stream.');
            }
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="providers_sample_without_data.csv"');
            fputcsv($output, $headers);
            foreach ($sampleData as $row) {
                fputcsv($output, $row);
            }
            fclose($output);
            exit;
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Partner.php - bulk_import_provider_sample_file_download()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function bulk_import_provider_upload()
    {
        if (!is_dir(FCPATH . 'public/backend/assets/profile/')) {
            if (!mkdir(FCPATH . 'public/backend/assets/profile/', 0775, true)) {
                return ErrorResponse("Failed to create folders", true, [], [], 200, csrf_token(), csrf_hash());
            }
        }
        if (!is_dir(FCPATH . 'public/backend/assets/banner/')) {
            if (!mkdir(FCPATH . 'public/backend/assets/banner/', 0775, true)) {
                return ErrorResponse("Failed to create folders", true, [], [], 200, csrf_token(), csrf_hash());
            }
        }
        if (!is_dir(FCPATH . 'public/backend/assets/national_id/')) {
            if (!mkdir(FCPATH . 'public/backend/assets/national_id/', 0775, true)) {
                return ErrorResponse("Failed to create folders", true, [], [], 200, csrf_token(), csrf_hash());
            }
        }
        if (!is_dir(FCPATH . 'public/backend/assets/address_id/')) {
            if (!mkdir(FCPATH . 'public/backend/assets/address_id/', 0775, true)) {
                return ErrorResponse("Failed to create folders", true, [], [], 200, csrf_token(), csrf_hash());
            }
        }
        if (!is_dir(FCPATH . 'public/backend/assets/passport/')) {
            if (!mkdir(FCPATH . 'public/backend/assets/passport/', 0775, true)) {
                die('Failed to create folders...');
            }
        }
        if (!is_dir(FCPATH . 'public/uploads/partner/')) {
            if (!mkdir(FCPATH . 'public/uploads/partner/', 0775, true)) {
                die('Failed to create folders...');
            }
        }
        $result = checkModificationInDemoMode($this->superadmin);
        if ($result !== true) {
            return $this->response->setJSON($result);
        }
        $file = $this->request->getFile('file');
        $filePath = FCPATH . 'public/uploads/provider_bulk_file/';
        if (!is_dir($filePath)) {
            if (!mkdir($filePath, 0775, true)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => labels('Failed to create folders', 'Failed to create folders')
                ]);
            }
        }
        $newName = $file->getRandomName();
        $file->move($filePath, $newName);
        $fullPath = $filePath . $newName;
        $spreadsheet = IOFactory::load($fullPath);
        $sheet = $spreadsheet->getActiveSheet();
        $headerRow = $sheet->getRowIterator(1)->current();
        $cellIterator = $headerRow->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);
        $other_image_Headers = [];
        $columnIndex = 0;
        $data = $sheet->toArray();
        array_shift($data);
        $data = array_filter($data, function ($row) {
            return !empty(array_filter($row));
        });
        $headerRow = $sheet->rangeToArray('A1:' . $sheet->getHighestColumn() . '1', NULL, TRUE, TRUE, TRUE);
        $headers = explode(',', $headerRow[1]['A']);
        $headers = array_map(function ($header) {
            return trim($header, ' "');
        }, $headers);
        if (!in_array('ID', $headers)) {
            //insert
            foreach ($cellIterator as $cell) {
                $header = $cell->getValue();
                if (preg_match('/^Other Image\[(\d+)\]$/', $header, $matches)) {
                    $other_image_number = $matches[1];
                    $other_image_Headers[$other_image_number] = $columnIndex;
                } elseif (preg_match('/^Other Image\[(\d+)\]$/', $header, $matches)) {
                    $other_image_number = $matches[1];
                    $other_image_Headers[$other_image_number] = $columnIndex;
                }
                $columnIndex++;
            }
            $data = $sheet->toArray();
            array_shift($data);
            $data = array_filter($data, function ($row) {
                return !empty(array_filter($row));
            });
            $providers = [];
            $users = [];
            $partnerTimings = [];
            $userIds = [];
            $ion_auth = new IonAuthModel();
            foreach ($data as $row) {
                $db      = \Config\Database::connect();
                $builder = $db->table('users u');
                $builder->select('u.*,ug.group_id')
                    ->join('users_groups ug', 'ug.user_id = u.id')
                    ->where('ug.group_id', 3)
                    ->where(['phone' =>   $row[21]]);
                $mobile_data = $builder->get()->getResultArray();
                if (!empty($mobile_data) && $mobile_data[0]['phone']) {
                    return ErrorResponse($mobile_data[0]['phone'] . " - " . labels('Phone number already exists please use another one', 'Phone number already exists please use another one'), true, [], [], 200, csrf_token(), csrf_hash());
                }
                if (!preg_match('/^-?(90|[1-8][0-9][.][0-9]{1,20}|[0-9][.][0-9]{1,20})$/',  $row[25])) {
                    return ErrorResponse("Please enter valid latitude", true, [], [], 200, csrf_token(), csrf_hash());
                }
                if (!preg_match('/^-?(180(\.0{1,20})?|1[0-7][0-9](\.[0-9]{1,20})?|[1-9][0-9](\.[0-9]{1,20})?|[0-9](\.[0-9]{1,20})?)$/',  $row[26])) {
                    return ErrorResponse("Please enter a valid Longitude", true, [], [], 200, csrf_token(), csrf_hash());
                }


                $image = !empty($row[48]) ? copy_image($row[48], '/public/backend/assets/profile/') : "";
                $banner_image = !empty($row[49]) ? copy_image($row[49], '/public/backend/assets/banner/') : "";
                $passport = !empty($row[50]) ? copy_image($row[50], '/public/backend/assets/passport/') : "";
                $national_id = !empty($row[51]) ? copy_image($row[51], '/public/backend/assets/national_id/') : "";
                $address_id = !empty($row[52]) ? copy_image($row[52], '/public/backend/assets/address_id/') : "";
                $other_images = [];
                foreach ($other_image_Headers as $indexes) {
                    $other_image = isset($row[$indexes]) ? trim($row[$indexes]) : '';
                    if (!empty($other_image)) {
                        copy_image($row[$indexes], '/public/uploads/partner/');
                        if (!empty($other_image)) {
                            $other_images[] = $other_image;
                        }
                    }
                }
                $providers[] = [
                    'company_name' => $row[0] ?? "",
                    'type' => "$row[1]" ?? "",
                    'visiting_charges' => $row[2] ?? "",
                    'advance_booking_days' => "$row[3]" ?? "",
                    'number_of_members' => ($row[1] == 1) ? "$row[4]" : "0",
                    'at_store' => $row[5] ?? "",
                    'at_doorstep' => $row[6] ?? "",
                    'need_approval_for_the_service' => $row[7] ?? "",
                    'about' => $row[8] ?? "",
                    'long_description' => $row[9] ?? "",
                    'address' => $row[10] ?? "",
                    'tax_name' => $row[11] ?? "",
                    'tax_number' => $row[12] ?? "",
                    'account_number' => "$row[13]" ?? "",
                    'account_name' => $row[14] ?? "",
                    'bank_code' => $row[15] ?? "",
                    'bank_name' => $row[16] ?? "",
                    'swift_code' => $row[17] ?? "",
                    'is_approved' => $row[18] ?? "",
                    'banner' => $banner_image ?? "",
                    'passport' => $passport ?? "",
                    'national_id' => $national_id ?? "",
                    'address_id' => $address_id ?? "",
                    'other_images' => json_encode($other_images) ?? ""
                ];
                $users[] = [
                    'username' => $row[19] ?? "",
                    'email' => $row[20] ?? "",
                    'phone' => $row[21] ?? "",
                    'country_code' => "+" . "$row[22]",
                    'password' =>   $ion_auth->hashPassword($row[23]),
                    'city' => $row[24] ?? "",
                    'latitude' => $row[25] ?? "",
                    'longitude' => $row[26] ?? "",
                    'active' => 1,
                    'image' => $image ?? "",
                ];
                $days = [
                    0 => 'monday',
                    1 => 'tuesday',
                    2 => 'wednesday',
                    3 => 'thursday',
                    4 => 'friday',
                    5 => 'saturday',
                    6 => 'sunday'
                ];
                for ($i = 0; $i < count($days); $i++) {
                    $day = $days[$i];
                    $partnerTimings[] = [
                        'day' => $day,
                        'opening_time' => $row[27 + ($i * 3)],
                        'closing_time' => $row[28 + ($i * 3)],
                        'is_open' => $row[29 + ($i * 3)],
                        'provider_index' => count($providers) - 1,
                    ];
                }
            }
            $providerModel = new Partners_model();
            $userModel = new Users_model();
            $db = \Config\Database::connect();
            $db->transStart();
            try {
                foreach ($users as $userIndex => $user) {
                    if (!$userModel->insert($user)) {
                        return ErrorResponse("Failed to insert User", true, [], [], 200, csrf_token(), csrf_hash());
                    }
                    $userIds[$userIndex] = $userModel->insertID();
                    $group_data['user_id'] = $userIds[$userIndex];
                    $group_data['group_id'] = 3;
                    insert_details($group_data, 'users_groups');
                }
                foreach ($providers as $providerIndex => $provider) {
                    $provider['partner_id'] = $userIds[$providerIndex];
                    $this->partner->save($provider);
                    foreach ($partnerTimings as $timingIndex => $timing) {
                        if ($timing['provider_index'] === $providerIndex) {
                            unset($timing['provider_index']);
                            $timing['partner_id'] = $userIds[$providerIndex];
                            $db->table('partner_timings')->insert($timing);
                        }
                    }
                }
                $db->transComplete();
                if ($db->transStatus() === false) {
                    throw new \Exception('Transaction failed');
                }
                return successResponse("Providers imported successfully", false, [], [], 200, csrf_token(), csrf_hash());
            } catch (\Exception $e) {
                $db->transRollback();
                log_the_responce($e, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Partner.php - bulk_import_provider_upload()');
                return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
            }
        } else {
            //update
            $data = $sheet->toArray();
            array_shift($data);
            $data = array_filter($data, function ($row) {
                return !empty(array_filter($row));
            });
            $providers = [];
            $users = [];
            $partnerTimings = [];
            $ion_auth = new IonAuthModel();
            $other_image_Headers = [];
            $columnIndex = 0;
            foreach ($cellIterator as $cell) {
                $header = $cell->getValue() ?? '';
                if (preg_match('/^Other Image\[(\d+)\]$/', $header, $matches)) {
                    $other_image_number = $matches[1];
                    $other_image_Headers[$other_image_number] = $columnIndex;
                }
                $columnIndex++;
            }
            foreach ($data as $row) {
                $fetch_images = fetch_details('partner_details', ['partner_id' => $row[1]], ['banner', 'passport', 'national_id', 'address_id', 'other_images']);
                $banner_image = ($row[50] != $fetch_images[0]['banner']) ? (!empty($row[50]) ? copy_image($row[50], '/public/backend/assets/banner/') : "") : $fetch_images[0]['banner'];
                $passport = ($row[51] != $fetch_images[0]['passport']) ? (!empty($row[51]) ? copy_image($row[51], '/public/backend/assets/passport/') : "") : $fetch_images[0]['passport'];
                $national_id = ($row[52] != $fetch_images[0]['national_id']) ? (!empty($row[52]) ? copy_image($row[52], '/public/backend/assets/national_id/') : "") : $fetch_images[0]['national_id'];
                $address_id = ($row[53] != $fetch_images[0]['address_id']) ? (!empty($row[53]) ? copy_image($row[53], '/public/backend/assets/address_id/') : "") : $fetch_images[0]['address_id'];
                $old_other_images = json_decode($fetch_images[0]['other_images'], true); // Ensure to decode as array
                if (!is_array($old_other_images)) {
                    $old_other_images = [];
                }
                $other_images = [];
                foreach ($other_image_Headers as $key => $indexes) {
                    $other_image = isset($row[$indexes]) ? trim($row[$indexes]) : '';
                    if (!empty($other_image) && !in_array($other_image, $old_other_images)) {
                        $oi = copy_image($row[$indexes], 'public/uploads/partner/');
                        if (!empty($oi)) {
                            $other_images[] = $oi;
                        }
                    } else {
                        $other_images = $old_other_images;
                    }
                }
                $providers[] = [
                    'id' => $row[0] ?? "",
                    'partner_id' => $row[1] ?? "",
                    'company_name' => $row[2] ?? "",
                    'type' => $row[3] ?? "",
                    'visiting_charges' => $row[4] ?? "",
                    'advance_booking_days' => $row[5] ?? "",
                    'number_of_members' => ($row[3] == 1) ? $row[6] : "0",
                    'at_store' => $row[7] ?? "",
                    'at_doorstep' => $row[8] ?? "",
                    'need_approval_for_the_service' => $row[9] ?? "",
                    'about' => $row[10] ?? "",
                    'long_description' => $row[11] ?? "",
                    'address' => $row[12] ?? "",
                    'tax_name' => $row[13] ?? "",
                    'tax_number' => $row[14] ?? "",
                    'account_number' => $row[15] ?? "",
                    'account_name' => $row[16] ?? "",
                    'bank_code' => $row[17] ?? "",
                    'bank_name' => $row[18] ?? "",
                    'swift_code' => $row[19] ?? "",
                    'is_approved' => $row[20] ?? "",
                    'banner' => $banner_image ?? "",
                    'passport' => $passport ?? "",
                    'national_id' => $national_id ?? "",
                    'address_id' => $address_id ?? "",
                    'other_images' => json_encode($other_images),
                ];
                $fetch_user_image = fetch_details('users', ['id' => $row[1]], ['image']);
                $image = $row[50] != $fetch_user_image[0]['image'] ? (!empty($row[50]) ? copy_image($row[50], '/public/backend/assets/profile/') : "") : $fetch_user_image[0]['image'];
                $users[] = [
                    'id' => $row[1] ?? "",
                    'username' => $row[21] ?? "",
                    'email' => $row[22] ?? "",
                    'phone' => $row[23] ?? "",
                    'country_code' => '+' . $row[24],
                    'city' => $row[25] ?? "",
                    'latitude' => $row[26] ?? "",
                    'longitude' => $row[27] ?? "",
                    'active' => 1,
                    'image' => $image,
                ];
                $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                for ($i = 0; $i < count($days); $i++) {
                    $day = $days[$i];
                    $partnerTimings[] = [
                        'day' => $day ?? "",
                        'opening_time' => $row[28 + ($i * 3)] ?? "",
                        'closing_time' => $row[29 + ($i * 3)] ?? "",
                        'is_open' => $row[30 + ($i * 3)] ?? "",
                        'provider_index' => count($providers) - 1 ?? "",
                        'partner_id' => $row[1] ?? "",
                    ];
                }
                $other_images = [];
            }
            try {
                foreach ($users as $userIndex => $user) {
                    update_details($user, ['id' => $user['id']], 'users');
                    $userIds[$userIndex] = $user['id'];
                }
                foreach ($providers as $providerIndex => $provider) {
                    $provider['partner_id'] = $userIds[$providerIndex];
                    update_details($provider, ['id' => $provider['id'], 'partner_id' => $provider['partner_id']], 'partner_details', false);
                    foreach ($partnerTimings as $timingIndex => $timing) {
                        $condition = [
                            'partner_id' => $timing['partner_id'],
                            'day' => $timing['day']
                        ];
                        $updateData = array_diff_key($timing, array_flip(['provider_index', 'partner_id']));
                        update_details($updateData, $condition, 'partner_timings');
                    }
                }
                return successResponse("Providers Updated successfully", false, [], [], 200, csrf_token(), csrf_hash());
            } catch (\Exception $e) {
                log_the_responce($e, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Partner.php - bulk_import_provider_upload()');
                return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
            }
        }
    }
    public function ProviderAddInstructions()
    {
        try {
            $filePath = (FCPATH . '/public/uploads/site/Provider-Add-Instructions.pdf');
            $fileName = 'Provider-Add-Instructions.pdf';
            if (file_exists($filePath)) {
                return $this->response->download($filePath, null)->setFileName($fileName);
            } else {
                $_SESSION['toastMessage'] = labels('cant_download', 'Cannot download');
                $_SESSION['toastMessageType'] = 'error';
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/partners')->withCookies();
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Partner.php - bulk_import_provider_sample_instruction_file_download()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function ProviderUpdateInstructions()
    {
        try {
            $filePath = (FCPATH . '/public/uploads/site/Provider-Update-Instructions.pdf');
            $fileName = 'Provider-Update-Instructions.pdf';
            if (file_exists($filePath)) {
                return $this->response->download($filePath, null)->setFileName($fileName);
            } else {
                $_SESSION['toastMessage'] = labels('cant_download', 'Cannot download');
                $_SESSION['toastMessageType'] = 'error';
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/partners')->withCookies();
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Partner.php - bulk_import_provider_sample_instruction_file_download()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function downloadSampleForUpdate()
    {
        $result = checkModificationInDemoMode($this->superadmin);
        if ($result !== true) {
            $_SESSION['toastMessage'] = $result['message'];
            $_SESSION['toastMessageType'] = 'error';
            $this->session->markAsFlashdata('toastMessage');
            $this->session->markAsFlashdata('toastMessageType');
            return redirect()->to('admin/partners/bulk_import')->withCookies();
        }
        try {
            $headers = [
                'ID',
                'User ID',
                'Company Name',
                'Type',
                'Visiting Charge',
                'Advance Booking Days',
                'Number of Members',
                'At Store',
                'At Doorstep',
                'Need Approval for Service',
                'About Provider',
                'Description',
                'Address',
                'Tax Name',
                'Tax Number',
                'Account Number',
                'Account Name',
                'Bank Code',
                'Bank Name',
                'Swift Code',
                'Is Approved',
                'Username',
                'Email',
                'Phone',
                'Country Code',
                'City',
                'Latitude',
                'Longitude',
                'Monday Start Time',
                'Monday End Time',
                'Monday Is Open',
                'Tuesday Start Time',
                'Tuesday End Time',
                'Tuesday Is Open',
                'Wednesday Start Time',
                'Wednesday End Time',
                'Wednesday Is Open',
                'Thursday Start Time',
                'Thursday End Time',
                'Thursday Is Open',
                'Friday Start Time',
                'Friday End Time',
                'Friday Is Open',
                'Saturday Start Time',
                'Saturday End Time',
                'Saturday Is Open',
                'Sunday Start Time',
                'Sunday End Time',
                'Sunday Is Open',
                'Image',
                'Banner Image',
                'Passport',
                'National Identity',
                'Address id',
            ];
            $providers = fetch_details('partner_details', [], [], '', 0, 'id', 'ASC');
            if (empty($providers)) {
                http_response_code(400);
                echo json_encode(["message" => labels('No providers found', 'No providers found') . "."]);
                return;
            }
            $all_data = [];
            $max_other_image = 0;
            foreach ($providers as $row) {
                $other_image = json_decode($row['other_images'], true);
                if (is_array($other_image)) {
                    $max_other_image = max($max_other_image, count($other_image));
                }
            }
            for ($i = 1; $i <= $max_other_image; $i++) {
                $headers[] = "Other Image[$i]";
            }
            foreach ($providers as $provider) {
                $other_images = json_decode($provider['other_images'], true);
                $user = fetch_details('users', ['id' => $provider['partner_id']], ['username', 'email', 'phone', 'country_code', 'password', 'city', 'latitude', 'longitude', 'image']);
                $provide_timings = fetch_details('partner_timings', ['partner_id' => $provider['partner_id']]);
                if (!empty($user) && !empty($provide_timings)) {
                    $rowData = [
                        'ID' => $provider['id'],
                        'User ID' => $provider['partner_id'],
                        'Company Name' => $provider['company_name'],
                        'Type' => $provider['type'],
                        'Visiting Charge' => $provider['visiting_charges'],
                        'Advance Booking Days' => $provider['advance_booking_days'],
                        'Number of Members' => $provider['number_of_members'],
                        'At Store' => $provider['at_store'],
                        'At Doorstep' => $provider['at_doorstep'],
                        'Need Approval for Service' => $provider['need_approval_for_the_service'],
                        'About Provider' => $provider['about'],
                        'long_description' => strip_tags(htmlspecialchars_decode(stripslashes($provider['long_description'])), '<p><br>'),
                        'Address' => $provider['address'],
                        'Tax Name' => $provider['tax_name'],
                        'Tax Number' => $provider['tax_number'],
                        'Account Number' => $provider['account_number'],
                        'Account Name' => $provider['account_name'],
                        'Bank Code' => $provider['bank_code'],
                        'Bank Name' => $provider['bank_name'],
                        'Swift Code' => $provider['swift_code'],
                        'Is Approved' => $provider['is_approved'],
                    ];
                    if (!empty($user)) {
                        $user = $user[0];
                        $rowData['Username'] = $user['username'];
                        $rowData['Email'] = $user['email'];
                        $rowData['Phone'] = $user['phone'];
                        $rowData['Country Code'] = $user['country_code'];
                        $rowData['City'] = $user['city'];
                        $rowData['Latitude'] = $user['latitude'];
                        $rowData['Longitude'] = $user['longitude'];
                    }
                    if (!empty($provide_timings)) {
                        foreach ($provide_timings as $timing) {
                            $day = strtolower($timing['day']);
                            $rowData[$day . ' Start Time'] = $timing['opening_time'];
                            $rowData[$day . ' End Time'] = $timing['closing_time'];
                            $rowData[$day . ' Is Open'] = $timing['is_open'];
                        }
                    }
                    $rowData['Image'] = (!empty($user)) ? ($user['image']) : "";
                    $rowData['Banner Image'] = $provider['banner'];
                    $rowData['Passport'] = $provider['passport'];
                    $rowData['National Identity'] = $provider['national_id'];
                    $rowData['Address id'] = $provider['address_id'];
                    if (is_array($other_images)) {
                        foreach ($other_images as $index => $other_image) {
                            $rowData["Other Image[" . ($index + 1) . "]"] = isset($other_image) ? $other_image : '';
                        }
                    }
                    for ($i = count($other_images ?? []); $i < $max_other_image; $i++) {
                        $rowData["Other Image[" . ($i + 1) . "]"] = '';
                    }
                    $all_data[] = $rowData;
                }
            }
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="providers_sample_with_data.csv"');
            $output = fopen('php://output', 'w');
            if ($output === false) {
                throw new \Exception('Failed to open output stream.');
            }
            fputcsv($output, $headers);
            foreach ($all_data as $rowData) {
                fputcsv($output, $rowData);
            }
            fclose($output);
            exit;
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Partner.php - downloadSampleForUpdate()');
            http_response_code(500);
            echo json_encode(["message" => labels('Something Went Wrong', 'Something Went Wrong')]);
        }
    }
}
