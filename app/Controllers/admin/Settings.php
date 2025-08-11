<?php

namespace App\Controllers\admin;

use App\Jobs\Email;
use App\Jobs\NumberLoggerJob;
use App\Models\Country_code_model;
use App\Models\Email_template_model;

use CodeIgniter\Queue\Queue;

class Settings extends Admin
{
    private $db, $builder;
    protected $superadmin;
    protected $validation;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
        $this->validation = \Config\Services::validation();
        $this->builder = $this->db->table('settings');
        $this->superadmin = $this->session->get('email');
        helper('ResponceServices');
        helper('events');
    }
    public function __destruct()
    {
        $this->db->close();
        $this->data = [];
    }
    public function main_system_setting_page()
    {
        if (!$this->isLoggedIn || !$this->userIsAdmin) {
            return redirect('admin/login');
        }
        setPageInfo($this->data, labels('System Settings', 'System Settings') . ' | ' . labels('admin_panel', 'Admin Panel'), 'main_system_settings');
        return view('backend/admin/template', $this->data);
    }
    public function general_settings()
    {


        try {
            helper('form');
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            if ($this->request->getPost('update')) {


                if ($this->superadmin == "superadmin@gmail.com") {
                    defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
                } else {
                    if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                        $_SESSION['toastMessage'] = labels(DEMO_MODE_ERROR, DEMO_MODE_ERROR);
                        $_SESSION['toastMessageType']  = 'error';
                        $this->session->markAsFlashdata('toastMessage');
                        $this->session->markAsFlashdata('toastMessageType');
                        return redirect()->to('admin/settings/general-settings')->withCookies();
                    }
                }
                $updatedData = $this->request->getPost();
                $data = get_settings('general_settings', true);

                $disk = fetch_current_file_manager();

                $files = [
                    'favicon' => ['file' => $this->request->getFile('favicon'), 'path' => 'public/uploads/site/', 'error' => labels('Failed to upload favicon', 'Failed to upload favicon'), 'folder' => 'site', 'old_file' => $data['favicon'] ?? null, 'disk' => $disk],
                    'half_logo' => ['file' => $this->request->getFile('half_logo'), 'path' => 'public/uploads/site/', 'error' => labels('Failed to upload half_logo', 'Failed to upload half_logo'), 'folder' => 'site', 'old_file' => $data['half_logo'] ?? null, 'disk' => $disk],
                    'logo' => ['file' => $this->request->getFile('logo'), 'path' => 'public/uploads/site/', 'error' => labels('Failed to upload logo', 'Failed to upload logo'), 'folder' => 'site', 'old_file' => $data['logo'] ?? null, 'disk' => $disk],
                    'partner_favicon' => ['file' => $this->request->getFile('partner_favicon'), 'path' => 'public/uploads/site/', 'error' => labels('Failed to upload partner_favicon', 'Failed to upload partner_favicon'), 'folder' => 'site', 'old_file' => $data['partner_favicon'] ?? null, 'disk' => $disk],
                    'partner_half_logo' => ['file' => $this->request->getFile('partner_half_logo'), 'path' => 'public/uploads/site/', 'error' => labels('Failed to upload partner_half_logo', 'Failed to upload partner_half_logo'), 'folder' => 'site', 'old_file' => $data['partner_half_logo'] ?? null, 'disk' => $disk],
                    'partner_logo' => ['file' => $this->request->getFile('partner_logo'), 'path' => 'public/uploads/site/', 'error' => labels('Failed to upload partner_logo', 'Failed to upload partner_logo'), 'folder' => 'site', 'old_file' => $data['partner_logo'] ?? null, 'disk' => $disk],
                    'login_image' => ['file' => $this->request->getFile('login_image'), 'path' => 'public/frontend/retro/', 'error' => labels('Failed to upload login_image', 'Failed to upload login_image'), 'folder' => 'site', 'old_file' => $data['login_image'] ?? null, 'disk' => $disk],
                ];

                $uploadedFiles = [];
                foreach ($files as $key => $config) {

                    if (!empty($_FILES[$key]) && isset($_FILES[$key])) {
                        $file = $config['file'];
                        if ($file && $file->isValid()) {
                            if (!empty($config['old_file'])) {
                                delete_file_based_on_server($config['folder'], $config['old_file'], $config['disk']);
                            }
                            $result = upload_file($config['file'], $config['path'], $config['error'], $config['folder'], 'yes');

                            if ($result['error'] == false) {

                                if ($key == "login_image") {


                                    $uploadedFiles[$key] = [
                                        'url' => "Login_BG.jpg",
                                        'disk' => $result['disk']
                                    ];
                                } else {

                                    $uploadedFiles[$key] = [
                                        'url' => $result['file_name'],
                                        'disk' => $result['disk']
                                    ];
                                }
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
                foreach ($uploadedFiles as $key => $value) {
                    $updatedData[$key] = isset($value['url']) ? $value['url'] : (isset($data[$key]) ? $data[$key] : '');
                }

                unset($updatedData['update']);
                unset($updatedData[csrf_token()]);
                $updatedData['currency'] = (!empty($this->request->getPost('currency'))) ? $this->request->getPost('currency') : (isset($data['currency']) ? $data['currency'] : "");
                $updatedData['country_currency_code'] = (!empty($this->request->getPost('country_currency_code'))) ? $this->request->getPost('country_currency_code') : (isset($data['country_currency_code']) ? $data['country_currency_code'] : "");
                if ($this->request->getPost('decimal_point') == 0) {
                    $updatedData['decimal_point'] = "0";
                } elseif (!empty($this->request->getPost('decimal_point'))) {
                    $updatedData['decimal_point'] = $this->request->getPost('decimal_point');
                } else {
                    $updatedData['decimal_point'] = $data['decimal_point'];
                }
                if ($updatedData['distance_unit'] == 'miles') {
                    $distanceInMiles = $this->request->getPost('max_serviceable_distance');
                    $updatedData['distance_unit'] = $this->request->getPost('distance_unit');
                    $distanceInKm = $distanceInMiles * 1.60934;
                    $updatedData['max_serviceable_distance'] = round($distanceInKm);
                }
                if (!empty($this->request->getPost('otp_system'))) {
                    $updatedData['otp_system'] = (!empty($this->request->getPost('otp_system'))) ? $this->request->getPost('otp_system') : (isset($data['otp_system']) ? ($data['otp_system']) : "");
                }
                if (!empty($this->request->getPost('allow_pre_booking_chat'))) {
                    $updatedData['allow_pre_booking_chat'] = (!empty($this->request->getPost('allow_pre_booking_chat'))) ? $this->request->getPost('allow_pre_booking_chat') : (isset($data['allow_pre_booking_chat']) ? ($data['allow_pre_booking_chat']) : "");
                }
                if (!empty($this->request->getPost('allow_post_booking_chat'))) {
                    $updatedData['allow_post_booking_chat'] = (!empty($this->request->getPost('allow_post_booking_chat'))) ? $this->request->getPost('allow_post_booking_chat') : (isset($data['allow_post_booking_chat']) ? ($data['allow_post_booking_chat']) : "");
                }
                $keys = ['customer_current_version_ios_app', 'customer_compulsary_update_force_update', 'provider_current_version_android_app', 'provider_current_version_ios_app', 'provider_compulsary_update_force_update', 'customer_app_maintenance_schedule_date', 'message_for_customer_application', 'customer_app_maintenance_mode', 'provider_app_maintenance_schedule_date', 'message_for_provider_application', 'provider_app_maintenance_mode', 'provider_location_in_provider_details', 'company_title', 'support_name', 'support_email', 'phone', 'system_timezone_gmt', 'system_timezone', 'primary_color', 'secondary_color', 'primary_shadow', 'address', 'short_description', 'copyright_details', 'booking_auto_cancle_duration', 'customer_playstore_url', 'customer_appstore_url', 'provider_playstore_url', 'provider_appstore_url', 'maxFilesOrImagesInOneMessage', 'maxFileSizeInMBCanBeSent', 'maxCharactersInATextMessage', 'android_google_interstitial_id', 'android_google_banner_id', 'ios_google_interstitial_id', 'ios_google_banner_id', "android_google_ads_status", "ios_google_ads_status", 'authentication_mode', 'company_map_location', 'support_hours', 'file_manager', 'aws_access_key_id', 'aws_secret_access_key', 'aws_secret_access_key', 'aws_default_region', 'aws_bucket', 'aws_url'];
                foreach ($keys as $key) {
                    $updatedData[$key] = (!empty($this->request->getPost($key))) ? $this->request->getPost($key) : (isset($data[$key]) ? ($data[$key]) : "");
                }
                $updatedData['customer_current_version_android_app'] = (!empty($this->request->getPost('customer_current_version_android_app'))) ? $this->request->getPost('customer_current_version_android_app') : (isset($data['customer_current_version_android_app']) ? $data['customer_current_version_android_app'] : "");
                $updatedData['customer_current_version_ios_app'] = (!empty($this->request->getPost('customer_current_version_ios_app'))) ? $this->request->getPost('customer_current_version_ios_app') : (isset($data['customer_current_version_ios_app']) ? $data['customer_current_version_ios_app'] : "");
                $updatedData['provider_current_version_android_app'] = (!empty($this->request->getPost('provider_current_version_android_app'))) ? $this->request->getPost('provider_current_version_android_app') : (isset($data['provider_current_version_android_app']) ? $data['provider_current_version_android_app'] : "");
                $updatedData['provider_current_version_ios_app'] = (!empty($this->request->getPost('provider_current_version_ios_app'))) ? $this->request->getPost('provider_current_version_ios_app') : (isset($data['provider_current_version_ios_app']) ? $data['provider_current_version_ios_app'] : "");
                $updatedData['customer_app_maintenance_schedule_date'] = (!empty($this->request->getPost('customer_app_maintenance_schedule_date'))) ? $this->request->getPost('customer_app_maintenance_schedule_date') : (isset($data['customer_app_maintenance_schedule_date']) ? $data['customer_app_maintenance_schedule_date'] : "");
                $updatedData['message_for_customer_application'] = (!empty($this->request->getPost('message_for_customer_application'))) ? $this->request->getPost('message_for_customer_application') : (isset($data['message_for_customer_application']) ? $data['message_for_customer_application'] : "");
                $updatedData['provider_app_maintenance_schedule_date'] = (!empty($this->request->getPost('provider_app_maintenance_schedule_date'))) ? ($this->request->getPost('provider_app_maintenance_schedule_date')) : (isset($data['provider_app_maintenance_schedule_date']) ? $data['provider_app_maintenance_schedule_date'] : "");
                $updatedData['message_for_provider_application'] = (!empty($this->request->getPost('message_for_provider_application'))) ? $this->request->getPost('message_for_provider_application') : (isset($data['message_for_provider_application']) ? $data['message_for_provider_application'] : "");

                $updatedData['customer_compulsary_update_force_update'] = $data['customer_compulsary_update_force_update'] ?? '0';
                $updatedData['provider_compulsary_update_force_update'] = $data['provider_compulsary_update_force_update'] ?? '0';
                $updatedData['provider_location_in_provider_details'] = $data['provider_location_in_provider_details'] ?? '0';
                $updatedData['provider_app_maintenance_mode'] = $data['provider_app_maintenance_mode'] ?? '0';
                $updatedData['customer_app_maintenance_mode'] = $data['customer_app_maintenance_mode'] ?? '0';
                $updatedData['android_google_ads_status'] = $data['android_google_ads_status'] ?? '0';
                $updatedData['ios_google_ads_status'] = $data['ios_google_ads_status'] ?? '0';
                $updatedData['decimal_point'] = $data['decimal_point'] ?? '0';
                



                $updatedData['currency'] = (!empty($this->request->getPost('currency'))) ? $this->request->getPost('currency') : (isset($data['currency']) ? $data['currency'] : "");
                $updatedData['country_currency_code'] = (!empty($this->request->getPost('country_currency_code'))) ? $this->request->getPost('country_currency_code') : (isset($data['country_currency_code']) ? $data['country_currency_code'] : "");


                if ($this->request->getPost('image_compression_preference') == 0) {
                    $updatedData['image_compression_preference'] = "0";
                    $updatedData['image_compression_quality'] = "0";
                } elseif (!empty($this->request->getPost('image_compression_preference'))) {
                    $updatedData['image_compression_preference'] = $this->request->getPost('image_compression_preference');
                } else {
                    $updatedData['image_compression_preference'] = $data['image_compression_preference'];
                }
                if (!empty($updatedData['system_timezone_gmt'])) {
                    if ($updatedData['system_timezone_gmt'] == " 00:00") {
                        $updatedData['system_timezone_gmt'] = '+' . trim($updatedData['system_timezone_gmt']);
                    }
                }

                if (isset($updatedData['aws_url'])) {
                    $updatedData['aws_url'] = rtrim($updatedData['aws_url'], '/');
                }


                if (($this->request->getPost('company_map_location'))) {
                    $iframe = $this->request->getPost('company_map_location');

                    preg_match('/src="([^"]+)"/', $iframe, $matches);

                    if (!empty($matches[1])) {
                        $updatedData['company_map_location'] = $matches[1];
                    } else {
                        $updatedData['company_map_location'] = $iframe;
                    }
                }

                $json_string = json_encode($updatedData);



                $file_transfer_process = $_POST['file_transfer_process'];
                $file_manager = $_POST['file_manager'];

                if ($file_transfer_process == 1) {
                    $queue = service('queue');
                    $jobId = $queue->push('filemanagerchanges', 'fileManagerChangesJob', ['file_manager' => $file_manager]);
                }
                update_details(['value' => $file_manager], ['variable' => 'storage_disk'], 'settings');


                if ($this->update_setting('general_settings', $json_string)) {


                    $_SESSION['toastMessage']  = labels('Unable to update the settings', 'Unable to update the settings');
                    $_SESSION['toastMessageType']  = 'error';
                } else {
                    $_SESSION['toastMessage'] = labels('Settings has been successfuly updated', 'Settings has been successfuly updated');
                    $_SESSION['toastMessageType']  = 'success';
                }
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/settings/general-settings')->withCookies();
            }
            $this->builder->select('value');
            $this->builder->where('variable', 'general_settings');
            $query = $this->builder->get()->getResultArray();

            if (count($query) == 1) {
                $settings = $query[0]['value'];
                $settings = json_decode($settings, true);

                $imageSettings = ['half_logo', 'partner_favicon', 'partner_half_logo', 'partner_logo', 'login_image', 'favicon', 'logo'];

                $disk = fetch_current_file_manager();

                foreach ($imageSettings as $key) {


                    if (isset($settings[$key])) {

                        if (isset($disk)) {
                            switch ($disk) {
                                case 'local_server':
                                    if ($key == 'login_image') {

                                        $settings[$key] = base_url('public/frontend/retro/') . $settings[$key];
                                    } else {

                                        $settings[$key] = base_url('public/uploads/site/') . $settings[$key];
                                    }
                                    break;
                                case 'aws_s3':
                                    $settings[$key] = fetch_cloud_front_url('site', $settings[$key]);
                                    break;
                                default:
                                    $settings[$key] = "";
                            }
                        } else {
                            $settings[$key] = "";
                        }
                    }
                }
                if (!empty($settings)) {
                    $this->data = array_merge($this->data, $settings);
                }
                // die;

                // public/frontend/retro
            }
            $settings['distance_unit'] = isset($settings['distance_unit']) ? $settings['distance_unit'] : 'km';
            if ($settings['distance_unit'] == "miles") {
                $this->data['max_serviceable_distance'] = round($settings['max_serviceable_distance'] * 0.621371);
            };

            $this->data['timezones'] = get_timezone_array();
            setPageInfo($this->data, labels('General Settings', 'General Settings') . ' | ' . labels('admin_panel', 'Admin Panel'), 'general_settings');
            return view('backend/admin/template', $this->data);
        } catch (\Throwable $th) {
            throw $th;
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Settings.php - general_settings()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }


    public function startQueueWorker()
    {
        $output = null;
        $retval = null;

        // Run the queue worker command and capture the output
        exec('/opt/lampp/bin/php /opt/lampp/htdocs/edemand/index.php queue:work 2>&1', $output, $retval);

        // Log output and return code
        log_message('error', 'Queue Worker Output: ' . implode("\n", $output));
        log_message('error', 'Queue Worker Return Code: ' . $retval);
    }




    public function email_settings()
    {
        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            if ($this->request->getGet('update')) {
                if ($this->superadmin == "superadmin@gmail.com") {
                    defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
                } else {
                    if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                        $_SESSION['toastMessage'] = labels(DEMO_MODE_ERROR, DEMO_MODE_ERROR);
                        $_SESSION['toastMessageType']  = 'error';
                        $this->session->markAsFlashdata('toastMessage');
                        $this->session->markAsFlashdata('toastMessageType');
                        return redirect()->to('admin/settings/general-settings')->withCookies();
                    }
                }
                $this->validation->setRules(
                    [
                        'smtpHost' => ["rules" => 'required', "errors" => ["required" => "Please enter SMTP Host"]],
                        'smtpUsername' => ["rules" => 'required', "errors" => ["required" => "Please enter SMTP Username"]],
                        'smtpPassword' => ["rules" => 'required', "errors" => ["required" => "Please enter SMTP Password"]],
                        'smtpPort' => ["rules" => 'required|numeric', "errors" => ["required" => "Please enter SMTP Port Number",    "numeric" => "Please enter numeric value for SMTP Port Number"]],
                    ],
                );
                if (!$this->validation->withRequest($this->request)->run()) {
                    $errors  = $this->validation->getErrors();
                    $response['error'] = true;
                    $response['message'] = labels($errors, $errors);
                    $response['csrfName'] = csrf_token();
                    $response['csrfHash'] = csrf_hash();
                    $response['data'] = [];
                    return $this->response->setJSON($response);
                }
                $updatedData = $this->request->getGet();
                $json_string = json_encode($updatedData);
                if ($this->update_setting('email_settings', $json_string)) {
                    $_SESSION['toastMessage']  = labels('Unable to update the email settings', 'Unable to update the email settings');
                    $_SESSION['toastMessageType']  = 'error';
                } else {
                    $_SESSION['toastMessage'] = labels('Email settings has been successfuly updated', 'Email settings has been successfuly updated');
                    $_SESSION['toastMessageType']  = 'success';
                }
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/settings/email-settings')->withCookies();
            }
            $this->builder->select('value');
            $this->builder->where('variable', 'email_settings');
            $query = $this->builder->get()->getResultArray();
            if (count($query) == 1) {
                $settings = $query[0]['value'];
                $settings = json_decode($settings, true);
                $this->data = array_merge($this->data, $settings);
            }
            setPageInfo($this->data, labels('Email Settings', 'Email Settings') . ' | ' . labels('admin_panel', 'Admin Panel'), 'email_settings');
            return view('backend/admin/template', $this->data);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Settings.php - email_settings()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function pg_settings()
    {
        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            if ($this->request->getPost('update')) {
                if ($this->superadmin == "superadmin@gmail.com") {
                    defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
                } else {
                    if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                        $_SESSION['toastMessage'] = labels(DEMO_MODE_ERROR, DEMO_MODE_ERROR);
                        $_SESSION['toastMessageType']  = 'error';
                        $this->session->markAsFlashdata('toastMessage');
                        $this->session->markAsFlashdata('toastMessageType');
                        return redirect()->to('admin/settings/general-settings')->withCookies();
                    }
                }
                $updatedData = $this->request->getPost();
                $updatedData['cod_setting'] = isset($updatedData['cod_setting']) ? 1 : 0;
                $updatedData['payment_gateway_setting'] = isset($updatedData['payment_gateway_setting']) ? 1 : 0;
                $paypal_status = isset($updatedData['paypal_status']) ? 1 : 0;
                $razorpayApiStatus = isset($updatedData['razorpayApiStatus']) ? 1 : 0;
                $paystack_status = isset($updatedData['paystack_status']) ? 1 : 0;
                $stripe_status = isset($updatedData['stripe_status']) ? 1 : 0;
                $flutterwave_status = isset($updatedData['flutterwave_status']) ? 1 : 0;
                if ($paypal_status == 0 && $razorpayApiStatus == 0 && $paystack_status == 0 && $stripe_status == 0 && $flutterwave_status == 0) {
                    $_SESSION['toastMessage'] = labels('At least one payment method must be enabled', 'At least one payment method must be enabled');
                    $_SESSION['toastMessageType']  = 'error';
                    $this->session->markAsFlashdata('toastMessage');
                    $this->session->markAsFlashdata('toastMessageType');
                    return redirect()->to('admin/settings/pg-settings')->withCookies();
                }
                unset($updatedData['update']);
                unset($updatedData[csrf_token()]);
                if (isset($updatedData['paypal_website_url'])) {
                    $updatedData['paypal_website_url'] = rtrim($updatedData['paypal_website_url'], '/');
                }
                if (isset($updatedData['flutterwave_website_url'])) {
                    $updatedData['flutterwave_website_url'] = rtrim($updatedData['flutterwave_website_url'], '/');
                }
                if (isset($updatedData['flutterwave_webhook_secret_key'])) {
                    updateEnv('FLUTTERWAVE_SECRET_KEY', $updatedData['flutterwave_webhook_secret_key']);
                }
                $json_string = json_encode($updatedData);
                if ($this->update_setting('payment_gateways_settings', $json_string)) {
                    $_SESSION['toastMessage']  = labels('Unable to update the payment gateways settings', 'Unable to update the payment gateways settings');
                    $_SESSION['toastMessageType']  = 'error';
                } else {
                    $_SESSION['toastMessage'] = labels('Payment gateway settings has been successfully updated', 'Payment gateway settings has been successfully updated');
                    $_SESSION['toastMessageType']  = 'success';
                    $this->session->markAsFlashdata('toastMessage');
                    $this->session->markAsFlashdata('toastMessageType');
                }
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/settings/pg-settings')->withCookies();
            } else {
                $this->builder->select('value');
                $this->builder->where('variable', 'payment_gateways_settings');
                $query = $this->builder->get()->getResultArray();
                if (count($query) == 1) {
                    $settings = $query[0]['value'];
                    $settings = json_decode($settings, true);
                    $this->data = array_merge($this->data, $settings);
                }
                setPageInfo($this->data, labels('Payment Gateways Settings', 'Payment Gateways Settings') . ' | ' . labels('admin_panel', 'Admin Panel'), 'payment_gateways');
                return view('backend/admin/template', $this->data);
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Settings.php - pg_settings()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function privacy_policy()
    {
        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            if ($this->request->getPost('update')) {
                if ($this->superadmin == "superadmin@gmail.com") {
                    defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
                } else {
                    if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                        $_SESSION['toastMessage'] = labels(DEMO_MODE_ERROR, DEMO_MODE_ERROR);
                        $_SESSION['toastMessageType']  = 'error';
                        $this->session->markAsFlashdata('toastMessage');
                        $this->session->markAsFlashdata('toastMessageType');
                        return redirect()->to('admin/settings/general-settings')->withCookies();
                    }
                }
                $updatedData = $this->request->getPost();
                unset($updatedData['update']);
                unset($updatedData['files']);
                unset($updatedData[csrf_token()]);
                $json_string = json_encode($updatedData);
                if ($this->update_setting('privacy_policy', $json_string)) {
                    $_SESSION['toastMessage']  = labels('Unable to update the privacy policy', 'Unable to update the privacy policy');
                    $_SESSION['toastMessageType']  = 'error';
                } else {
                    $_SESSION['toastMessage'] = labels('privacy Policy has been successfuly updated', 'privacy Policy has been successfuly updated');
                    $_SESSION['toastMessageType']  = 'success';
                }
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/settings/privacy-policy')->withCookies();
            }
            $this->builder->select('value');
            $this->builder->where('variable', 'privacy_policy');
            $query = $this->builder->get()->getResultArray();
            if (count($query) == 1) {
                $settings = $query[0]['value'];
                $settings = json_decode($settings, true);
                $this->data = array_merge($this->data, $settings);
            }
            setPageInfo($this->data, labels('Privacy Policy Settings', 'Privacy Policy Settings') . ' | ' . labels('admin_panel', 'Admin Panel'), 'privacy_policy');
            return view('backend/admin/template', $this->data);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Settings.php - privacy_policy()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function customer_privacy_policy_page()
    {
        $settings = get_settings('general_settings', true);
        $this->data['title'] = 'Privacy Policy | ' . $settings['company_title'];
        $this->data['meta_description'] = 'Privacy Policy | ' . $settings['company_title'];
        $this->data['privacy_policy'] = get_settings('customer_privacy_policy', true);
        $this->data['settings'] =  $settings;
        return view('backend/admin/pages/customer_app_privacy_policy', $this->data);
    }
    public function customer_tearms_and_condition()
    {
        $settings = get_settings('general_settings', true);
        $this->data['title'] = 'Customer Terms & Condition  | ' . $settings['company_title'];
        $this->data['meta_description'] = 'Customer Terms & Condition  | ' . $settings['company_title'];
        $this->data['customer_terms_conditions'] = get_settings('customer_terms_conditions', true);
        $this->data['settings'] =  $settings;
        return view('backend/admin/pages/customer_terms_and_condition_page', $this->data);
    }
    public function provider_terms_and_condition()
    {
        $settings = get_settings('general_settings', true);
        $companyTitle = $settings['company_title'];

        $this->data = [
            'title'             => "Provider Privacy Policy  | $companyTitle",
            'meta_description'  => "Provider Privacy Policy  | $companyTitle",
            'terms_conditions'  => get_settings('terms_conditions', true),
            'settings'          => $settings
        ];

        return view('backend/admin/pages/provider_terms_and_condition_page', $this->data);
    }
    public function partner_privacy_policy_page()
    {
        $settings = get_settings('general_settings', true);
        $this->data['title'] = 'Privacy Policy | ' . $settings['company_title'];
        $this->data['meta_description'] = 'Privacy Policy | ' . $settings['company_title'];
        $this->data['privacy_policy'] = get_settings('privacy_policy', true);
        $this->data['settings'] =  $settings;
        return view('backend/admin/pages/partner_app_privacy_policy', $this->data);
    }
    public function customer_privacy_policy()
    {
        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            if ($this->request->getPost('update')) {
                if ($this->superadmin == "superadmin@gmail.com") {
                    defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
                } else {
                    if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                        $_SESSION['toastMessage'] = labels(DEMO_MODE_ERROR, DEMO_MODE_ERROR);
                        $_SESSION['toastMessageType']  = 'error';
                        $this->session->markAsFlashdata('toastMessage');
                        $this->session->markAsFlashdata('toastMessageType');
                        return redirect()->to('admin/settings/general-settings')->withCookies();
                    }
                }
                $updatedData = $this->request->getPost();
                unset($updatedData['update']);
                unset($updatedData['files']);
                unset($updatedData[csrf_token()]);
                $json_string = json_encode($updatedData);
                if ($this->update_setting('customer_privacy_policy', $json_string)) {
                    $_SESSION['toastMessage']  = labels('Unable to update the privacy policy', 'Unable to update the privacy policy');
                    $_SESSION['toastMessageType']  = 'error';
                } else {
                    $_SESSION['toastMessage'] = labels('privacy Policy has been successfully updated', 'privacy Policy has been successfully updated');
                    $_SESSION['toastMessageType']  = 'success';
                }
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/settings/customer-privacy-policy')->withCookies();
            }
            $this->builder->select('value');
            $this->builder->where('variable', 'customer_privacy_policy');
            $query = $this->builder->get()->getResultArray();
            if (count($query) == 1) {
                $settings = $query[0]['value'];
                $settings = json_decode($settings, true);
                $this->data = array_merge($this->data, $settings);
            }
            $this->data['title'] = 'Privacy Policy Settings | Admin Panel';
            $this->data['main_page'] = 'customer_privacy_policy';
            return view('backend/admin/template', $this->data);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Settings.php - customer_privacy_policy()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function refund_policy_page()
    {
        $settings = get_settings('general_settings', true);
        $this->data['title'] = 'Refund Policy | ' . $settings['company_title'];
        $this->data['meta_description'] = 'Refund Policy | ' . $settings['company_title'];
        $this->data['refund_policy'] = get_settings('refund_policy', true);
        $this->data['settings'] =  $settings;
        return view('backend/admin/pages/refund_policy_page', $this->data);
    }
    public function refund_policy()
    {
        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            if ($this->request->getPost('update')) {
                if ($this->superadmin == "superadmin@gmail.com") {
                    defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
                } else {
                    if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                        $_SESSION['toastMessage'] = labels(DEMO_MODE_ERROR, DEMO_MODE_ERROR);
                        $_SESSION['toastMessageType']  = 'error';
                        $this->session->markAsFlashdata('toastMessage');
                        $this->session->markAsFlashdata('toastMessageType');
                        return redirect()->to('admin/settings/general-settings')->withCookies();
                    }
                }
                $updatedData = $this->request->getPost();
                unset($updatedData['update']);
                unset($updatedData['files']);
                unset($updatedData[csrf_token()]);
                $json_string = json_encode($updatedData);
                if ($this->update_setting('refund_policy', $json_string)) {
                    $_SESSION['toastMessage']  = labels('Unable to update the refund policy', 'Unable to update the refund policy');
                    $_SESSION['toastMessageType']  = 'error';
                } else {
                    $_SESSION['toastMessage'] = labels('refund policy has been successfully updated', 'refund policy has been successfully updated');
                    $_SESSION['toastMessageType']  = 'success';
                }
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/settings/refund-policy')->withCookies();
            }
            $this->builder->select('value');
            $this->builder->where('variable', 'refund_policy');
            $query = $this->builder->get()->getResultArray();
            if (count($query) == 1) {
                $settings = $query[0]['value'];
                $settings = json_decode($settings, true);
                $this->data = array_merge($this->data, $settings);
            }
            setPageInfo($this->data, labels('Refund Policy Settings', 'Refund Policy Settings') . ' | ' . labels('admin_panel', 'Admin Panel'), 'refund_policy');
            return view('backend/admin/template', $this->data);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Settings.php - refund_policy()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function updater()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            setPageInfo($this->data, labels('Updater', 'Updater') . ' | ' . labels('admin_panel', 'Admin Panel'), 'updater');
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }
    public function terms_and_conditions()
    {
        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            if ($this->request->getPost('update')) {
                if ($this->superadmin == "superadmin@gmail.com") {
                    defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
                } else {
                    if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                        $_SESSION['toastMessage'] = labels(DEMO_MODE_ERROR, DEMO_MODE_ERROR);
                        $_SESSION['toastMessageType']  = 'error';
                        $this->session->markAsFlashdata('toastMessage');
                        $this->session->markAsFlashdata('toastMessageType');
                        return redirect()->to('admin/settings/general-settings')->withCookies();
                    }
                }
                $updatedData = $this->request->getPost();
                unset($updatedData['files']);
                unset($updatedData['update']);
                unset($updatedData[csrf_token()]);
                $json_string = json_encode($updatedData);
                if ($this->update_setting('terms_conditions', $json_string)) {
                    $_SESSION['toastMessage']  = labels('Unable to update the terms & conditions', 'Unable to update the terms & conditions');
                    $_SESSION['toastMessageType']  = 'error';
                } else {
                    $_SESSION['toastMessage'] = labels('Terms & Conditions has been successfully updated', 'Terms & Conditions has been successfully updated');
                    $_SESSION['toastMessageType']  = 'success';
                }
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/settings/terms-and-conditions')->withCookies();
            }
            $this->builder->select('value');
            $this->builder->where('variable', 'terms_conditions');
            $query = $this->builder->get()->getResultArray();
            if (count($query) == 1) {
                $settings = $query[0]['value'];
                $settings = json_decode($settings, true);
                $this->data = array_merge($this->data, $settings);
            }
            setPageInfo($this->data, labels('Terms & Conditions Settings', 'Terms & Conditions Settings') . ' | ' . labels('admin_panel', 'Admin Panel'), 'terms_and_conditions');
            return view('backend/admin/template', $this->data);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Settings.php - terms_and_conditions()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function customer_terms_and_conditions()
    {
        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            if ($this->request->getPost('update')) {
                if ($this->superadmin == "superadmin@gmail.com") {
                    defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
                } else {
                    if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                        $_SESSION['toastMessage'] = labels(DEMO_MODE_ERROR, DEMO_MODE_ERROR);
                        $_SESSION['toastMessageType']  = 'error';
                        $this->session->markAsFlashdata('toastMessage');
                        $this->session->markAsFlashdata('toastMessageType');
                        return redirect()->to('admin/settings/customer-terms-and-conditions')->withCookies();
                    }
                }
                $updatedData = $this->request->getPost();
                unset($updatedData['files']);
                unset($updatedData['update']);
                unset($updatedData[csrf_token()]);
                $json_string = json_encode($updatedData);
                if ($this->update_setting('customer_terms_conditions', $json_string)) {
                    $_SESSION['toastMessage']  = labels('Unable to update the terms & conditions', 'Unable to update the terms & conditions');
                    $_SESSION['toastMessageType']  = 'error';
                } else {
                    $_SESSION['toastMessage'] = labels('Terms & Conditions has been successfully updated', 'Terms & Conditions has been successfully updated');
                    $_SESSION['toastMessageType']  = 'success';
                }
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/settings/customer-terms-and-conditions')->withCookies();
            }
            $this->builder->select('value');
            $this->builder->where('variable', 'customer_terms_conditions');
            $query = $this->builder->get()->getResultArray();
            if (count($query) == 1) {
                $settings = $query[0]['value'];
                $settings = json_decode($settings, true);
                $this->data = array_merge($this->data, $settings);
            }
            setPageInfo($this->data, labels('Terms & Conditions Settings', 'Terms & Conditions Settings') . ' | ' . labels('admin_panel', 'Admin Panel'), 'customer_terms_and_conditions');
            return view('backend/admin/template', $this->data);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Settings.php - customer_terms_and_conditions()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function about_us()
    {
        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            if ($this->request->getPost('update')) {
                if ($this->superadmin == "superadmin@gmail.com") {
                    defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
                } else {
                    if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                        $_SESSION['toastMessage'] = labels(DEMO_MODE_ERROR, DEMO_MODE_ERROR);
                        $_SESSION['toastMessageType']  = 'error';
                        $this->session->markAsFlashdata('toastMessage');
                        $this->session->markAsFlashdata('toastMessageType');
                        return redirect()->to('admin/settings/general-settings')->withCookies();
                    }
                }
                $updatedData = $this->request->getPost();
                unset($updatedData['files']);
                unset($updatedData['update']);
                unset($updatedData[csrf_token()]);
                $json_string = json_encode($updatedData);
                if ($this->update_setting('about_us', $json_string)) {
                    $_SESSION['toastMessage']  = labels('Unable to update about-us section', 'Unable to update about-us section');
                    $_SESSION['toastMessageType']  = 'error';
                } else {
                    $_SESSION['toastMessage'] = labels('About-us section has been successfully updated', 'About-us section has been successfully updated');
                    $_SESSION['toastMessageType']  = 'success';
                }
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/settings/about-us')->withCookies();
            }
            $this->builder->select('value');
            $this->builder->where('variable', 'about_us');
            $query = $this->builder->get()->getResultArray();
            if (count($query) == 1) {
                $settings = $query[0]['value'];
                $settings = json_decode($settings, true);
                $this->data = array_merge($this->data, $settings);
            }
            setPageInfo($this->data, labels('About us Settings', 'About us Settings') . ' | ' . labels('admin_panel', 'Admin Panel'), 'about_us');
            return view('backend/admin/template', $this->data);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Settings.php - customer_terms_and_conditions()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function contact_us()
    {
        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            if ($this->request->getPost('update')) {
                if ($this->superadmin == "superadmin@gmail.com") {
                    defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
                } else {
                    if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                        $_SESSION['toastMessage'] = labels(DEMO_MODE_ERROR, DEMO_MODE_ERROR);
                        $_SESSION['toastMessageType']  = 'error';
                        $this->session->markAsFlashdata('toastMessage');
                        $this->session->markAsFlashdata('toastMessageType');
                        return redirect()->to('admin/settings/general-settings')->withCookies();
                    }
                }
                $updatedData = $this->request->getPost();
                unset($updatedData['files']);
                unset($updatedData['update']);
                unset($updatedData[csrf_token()]);
                $json_string = json_encode($updatedData);
                if ($this->update_setting('contact_us', $json_string)) {
                    $_SESSION['toastMessage']  = labels('Unable to update contact-us section', 'Unable to update contact-us section');
                    $_SESSION['toastMessageType']  = 'error';
                } else {
                    $_SESSION['toastMessage'] = labels('Contact-us section has been successfully updated', 'Contact-us section has been successfully updated');
                    $_SESSION['toastMessageType']  = 'success';
                }
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/settings/contact-us')->withCookies();
            }
            $this->builder->select('value');
            $this->builder->where('variable', 'contact_us');
            $query = $this->builder->get()->getResultArray();
            if (count($query) == 1) {
                $settings = $query[0]['value'];
                $settings = json_decode($settings, true);
                $this->data = array_merge($this->data, $settings);
            }
            setPageInfo($this->data, labels('Contact us Settings', 'Contact us Settings') . ' | ' . labels('admin_panel', 'Admin Panel'), 'contact_us');
            return view('backend/admin/template', $this->data);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Settings.php - contact_us()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function api_key_settings()
    {
        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            if ($this->request->getPost('update')) {
                if ($this->superadmin == "superadmin@gmail.com") {
                    defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
                } else {
                    if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                        $_SESSION['toastMessage'] = labels(DEMO_MODE_ERROR, DEMO_MODE_ERROR);
                        $_SESSION['toastMessageType']  = 'error';
                        $this->session->markAsFlashdata('toastMessage');
                        $this->session->markAsFlashdata('toastMessageType');
                        return redirect()->to('admin/settings/general-settings')->withCookies();
                    }
                }
                $updatedData = $this->request->getPost();
                unset($updatedData['files']);
                unset($updatedData[csrf_token()]);
                unset($updatedData['update']);
                $json_string = json_encode($updatedData);
                if ($this->update_setting('api_key_settings', $json_string)) {
                    $_SESSION['toastMessage']  = labels('Unable to update API key section', 'Unable to update API key section');
                    $_SESSION['toastMessageType']  = 'error';
                } else {
                    $_SESSION['toastMessage'] = labels('API key section has been successfully updated', 'API key section has been successfully updated');
                    $_SESSION['toastMessageType']  = 'success';
                }
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/settings/api_key_settings')->withCookies();
            }
            $this->builder->select('value');
            $this->builder->where('variable', 'api_key_settings');
            $query = $this->builder->get()->getResultArray();
            if (count($query) == 1) {
                $settings = $query[0]['value'];
                $settings = json_decode($settings, true);
                $this->data = array_merge($this->data, $settings);
            }
            setPageInfo($this->data, labels('API key Settings', 'API key Settings') . ' | ' . labels('admin_panel', 'Admin Panel'), 'api_key_settings');
            return view('backend/admin/template', $this->data);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Settings.php - api_key_settings()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    private function update_setting($variable, $value)
    {
        try {
            $this->builder->where('variable', $variable);
            if (exists(['variable' => $variable], 'settings')) {
                $this->db->transStart();
                $this->builder->update(['value' => $value]);
                $this->db->transComplete();
            } else {
                $this->db->transStart();
                $this->builder->insert(['variable' => $variable, 'value' => $value]);
                $this->db->transComplete();
            }
            return $this->db->transComplete() ? true : false;
        } catch (\Throwable $th) {

            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Settings.php - update_setting()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function themes()
    {
        if (!$this->isLoggedIn || !$this->userIsAdmin) {
            return redirect('admin/login');
        }
        if ($this->request->getPost('update')) {
        }
        $this->data["themes"] = fetch_details('themes', [], [], null, '0', 'id', "ASC");
        setPageInfo($this->data, labels('About us Settings', 'About us Settings') . ' | ' . labels('admin_panel', 'Admin Panel'), 'themes');
        return view('backend/admin/template', $this->data);
    }
    public function system_tax_settings()
    {
        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            if ($this->request->getPost('update')) {
                if ($this->superadmin == "superadmin@gmail.com") {
                    defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
                } else {
                    if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                        $_SESSION['toastMessage'] = labels(DEMO_MODE_ERROR, DEMO_MODE_ERROR);
                        $_SESSION['toastMessageType']  = 'error';
                        $this->session->markAsFlashdata('toastMessage');
                        $this->session->markAsFlashdata('toastMessageType');
                        return redirect()->to('admin/settings/general-settings')->withCookies();
                    }
                }
                $updatedData = $this->request->getPost();
                unset($updatedData['files']);
                unset($updatedData[csrf_token()]);
                unset($updatedData['update']);
                $json_string = json_encode($updatedData);
                if ($this->update_setting('system_tax_settings', $json_string)) {
                    $_SESSION['toastMessage']  = labels('Unable to update system tax settings', 'Unable to update system tax settings');
                    $_SESSION['toastMessageType']  = 'error';
                } else {
                    $_SESSION['toastMessage'] = labels('System Tax settings successfully updated', 'System Tax settings successfully updated');
                    $_SESSION['toastMessageType']  = 'success';
                }
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/settings/system_tax_settings')->withCookies();
            }
            $this->builder->select('value');
            $this->builder->where('variable', 'system_tax_settings');
            $query = $this->builder->get()->getResultArray();
            if (count($query) == 1) {
                $settings = $query[0]['value'];
                $settings = json_decode($settings, true);
                $this->data = array_merge($this->data, $settings);
            }
            setPageInfo($this->data, labels('System Tax Settings', 'System Tax Settings') . ' | ' . labels('admin_panel', 'Admin Panel'), 'system_tax_settings');
            return view('backend/admin/template', $this->data);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Settings.php - system_tax_settings()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function app_settings()
    {

        try {
            if ($this->request->getPost('update')) {

                if ($this->superadmin == "superadmin@gmail.com") {
                    defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
                } else {
                    if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                        $_SESSION['toastMessage'] = labels(DEMO_MODE_ERROR, DEMO_MODE_ERROR);
                        $_SESSION['toastMessageType']  = 'error';
                        $this->session->markAsFlashdata('toastMessage');
                        $this->session->markAsFlashdata('toastMessageType');
                        return redirect()->to('admin/settings/general-settings')->withCookies();
                    }
                }
                $updatedData = $this->request->getPost();

                $data = get_settings('general_settings', true);
                $disk = fetch_current_file_manager();

                $files = [
                    'favicon' => ['file' => $this->request->getFile('favicon'), 'path' => 'public/uploads/site/', 'error' => labels('Failed to upload favicon', 'Failed to upload favicon'), 'folder' => 'site', 'old_file' => $data['favicon'] ?? null, 'disk' => $disk],
                    'half_logo' => ['file' => $this->request->getFile('half_logo'), 'path' => 'public/uploads/site/', 'error' => labels('Failed to upload half_logo', 'Failed to upload half_logo'), 'folder' => 'site', 'old_file' => $data['half_logo'] ?? null, 'disk' => $disk],
                    'logo' => ['file' => $this->request->getFile('logo'), 'path' => 'public/uploads/site/', 'error' => labels('Failed to upload logo', 'Failed to upload logo'), 'folder' => 'site', 'old_file' => $data['logo'] ?? null, 'disk' => $disk],
                    'partner_favicon' => ['file' => $this->request->getFile('partner_favicon'), 'path' => 'public/uploads/site/', 'error' => labels('Failed to upload partner_favicon', 'Failed to upload partner_favicon'), 'folder' => 'site', 'old_file' => $data['partner_favicon'] ?? null, 'disk' => $disk],
                    'partner_half_logo' => ['file' => $this->request->getFile('partner_half_logo'), 'path' => 'public/uploads/site/', 'error' => labels('Failed to upload partner_half_logo', 'Failed to upload partner_half_logo'), 'folder' => 'site', 'old_file' => $data['partner_half_logo'] ?? null, 'disk' => $disk],
                    'partner_logo' => ['file' => $this->request->getFile('partner_logo'), 'path' => 'public/uploads/site/', 'error' => labels('Failed to upload partner_logo', 'Failed to upload partner_logo'), 'folder' => 'site', 'old_file' => $data['partner_logo'] ?? null, 'disk' => $disk],
                    'login_image' => ['file' => $this->request->getFile('login_image'), 'path' => 'public/frontend/retro/', 'error' => labels('Failed to upload login_image', 'Failed to upload login_image'), 'folder' => 'site', 'old_file' => $data['login_image'] ?? null, 'disk' => $disk],
                ];
                $uploadedFiles = [];
                foreach ($files as $key => $config) {
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
                foreach ($uploadedFiles as $key => $value) {
                    $updatedData[$key] = isset($value['url']) ? $value['url'] : (isset($data[$key]) ? $data[$key] : '');
                }

                // Cleanup
                unset($updatedData['halfLogo']);
                unset($updatedData['partner_halfLogo']);
                unset($updatedData['update']);
                unset($updatedData[csrf_token()]);
                $updatedData['currency'] = (!empty($this->request->getPost('currency'))) ? $this->request->getPost('currency') : (isset($data['currency']) ? $data['currency'] : "");
                $updatedData['country_currency_code'] = (!empty($this->request->getPost('country_currency_code'))) ? $this->request->getPost('country_currency_code') : (isset($data['country_currency_code']) ? $data['country_currency_code'] : "");
                if ($this->request->getPost('decimal_point') == 0) {
                    $updatedData['decimal_point'] = "0";
                } elseif (!empty($this->request->getPost('decimal_point'))) {
                    $updatedData['decimal_point'] = $this->request->getPost('decimal_point');
                } else {
                    $updatedData['decimal_point'] = $data['decimal_point'];
                }
                $updatedData['customer_current_version_android_app'] = (!empty($this->request->getPost('customer_current_version_android_app'))) ? $this->request->getPost('customer_current_version_android_app') : (isset($data['customer_current_version_android_app']) ? $data['customer_current_version_android_app'] : "");
                $updatedData['customer_current_version_ios_app'] = (!empty($this->request->getPost('customer_current_version_ios_app'))) ? $this->request->getPost('customer_current_version_ios_app') : (isset($data['customer_current_version_ios_app']) ? $data['customer_current_version_ios_app'] : "");
                $updatedData['provider_current_version_android_app'] = (!empty($this->request->getPost('provider_current_version_android_app'))) ? $this->request->getPost('provider_current_version_android_app') : (isset($data['provider_current_version_android_app']) ? $data['provider_current_version_android_app'] : "");
                $updatedData['provider_current_version_ios_app'] = (!empty($this->request->getPost('provider_current_version_ios_app'))) ? $this->request->getPost('provider_current_version_ios_app') : (isset($data['provider_current_version_ios_app']) ? $data['provider_current_version_ios_app'] : "");
                $updatedData['customer_app_maintenance_schedule_date'] = (!empty($this->request->getPost('customer_app_maintenance_schedule_date'))) ? $this->request->getPost('customer_app_maintenance_schedule_date') : (isset($data['customer_app_maintenance_schedule_date']) ? $data['customer_app_maintenance_schedule_date'] : "");
                $updatedData['message_for_customer_application'] = (!empty($this->request->getPost('message_for_customer_application'))) ? $this->request->getPost('message_for_customer_application') : (isset($data['message_for_customer_application']) ? $data['message_for_customer_application'] : "");
                $updatedData['provider_app_maintenance_schedule_date'] = (!empty($this->request->getPost('provider_app_maintenance_schedule_date'))) ? ($this->request->getPost('provider_app_maintenance_schedule_date')) : (isset($data['provider_app_maintenance_schedule_date']) ? $data['provider_app_maintenance_schedule_date'] : "");
                $updatedData['message_for_provider_application'] = (!empty($this->request->getPost('message_for_provider_application'))) ? $this->request->getPost('message_for_provider_application') : (isset($data['message_for_provider_application']) ? $data['message_for_provider_application'] : "");

                $keys = ['customer_current_version_android_app', 'customer_current_version_ios_app', 'provider_current_version_android_app', 'provider_current_version_ios_app', 'customer_app_maintenance_schedule_date', 'message_for_customer_application', 'customer_app_maintenance_mode', 'provider_app_maintenance_schedule_date', 'message_for_provider_application', 'provider_app_maintenance_mode', 'company_title', 'support_name', 'support_email', 'phone', 'system_timezone_gmt', 'system_timezone', 'primary_color', 'secondary_color', 'primary_shadow', 'max_serviceable_distance', 'distance_unit', 'address', 'short_description', 'copyright_details', 'booking_auto_cancle_duration', 'customer_playstore_url', 'customer_appstore_url', 'provider_playstore_url', 'provider_appstore_url', 'maxFilesOrImagesInOneMessage', 'maxFileSizeInMBCanBeSent', 'maxCharactersInATextMessage', 'android_google_interstitial_id', 'android_google_banner_id', 'ios_google_interstitial_id', 'ios_google_banner_id', 'otp_system', 'authentication_mode', 'company_map_location', 'support_hours', 'allow_pre_booking_chat', 'allow_post_booking_chat', 'file_manager', 'aws_access_key_id', 'aws_secret_access_key', 'aws_secret_access_key', 'aws_default_region', 'aws_bucket', 'aws_url', 'storage_disk',];
                foreach ($keys as $key) {
                    $updatedData[$key] = (!empty($this->request->getPost($key))) ? $this->request->getPost($key) : (isset($data[$key]) ? ($data[$key]) : "");
                }

                if ($this->request->getPost('customer_compulsary_update_force_update') == 0) {
                    $updatedData['customer_compulsary_update_force_update'] = "0";
                } elseif (!empty($this->request->getPost('customer_compulsary_update_force_update'))) {
                    $updatedData['customer_compulsary_update_force_update'] = $this->request->getPost('customer_compulsary_update_force_update');
                } else {
                    $updatedData['customer_compulsary_update_force_update'] = $data['customer_compulsary_update_force_update'];
                }
                if ($this->request->getPost('provider_compulsary_update_force_update') == 0) {
                    $updatedData['provider_compulsary_update_force_update'] = "0";
                } elseif (!empty($this->request->getPost('provider_compulsary_update_force_update'))) {
                    $updatedData['provider_compulsary_update_force_update'] = $this->request->getPost('provider_compulsary_update_force_update');
                } else {
                    $updatedData['provider_compulsary_update_force_update'] = $data['provider_compulsary_update_force_update'];
                }
                if ($this->request->getPost('provider_location_in_provider_details') == 0) {
                    $updatedData['provider_location_in_provider_details'] = "0";
                } elseif (!empty($this->request->getPost('provider_location_in_provider_details'))) {
                    $updatedData['provider_location_in_provider_details'] = $this->request->getPost('provider_location_in_provider_details');
                } else {
                    $updatedData['provider_location_in_provider_details'] = $data['provider_location_in_provider_details'];
                }
                if ($this->request->getPost('provider_app_maintenance_mode') == 0) {
                    $updatedData['provider_app_maintenance_mode'] = "0";
                } elseif (!empty($this->request->getPost('provider_app_maintenance_mode'))) {
                    $updatedData['provider_app_maintenance_mode'] = $this->request->getPost('provider_app_maintenance_mode');
                } else {
                    $updatedData['provider_app_maintenance_mode'] = 0;
                }
                if ($this->request->getPost('customer_app_maintenance_mode') == 0) {
                    $updatedData['customer_app_maintenance_mode'] = "0";
                } elseif (!empty($this->request->getPost('customer_app_maintenance_mode'))) {
                    $updatedData['customer_app_maintenance_mode'] = $this->request->getPost('customer_app_maintenance_mode');
                } else {
                    $updatedData['customer_app_maintenance_mode'] = "0";
                }

                if ($this->request->getPost('android_google_ads_status') == 0) {
                    $updatedData['android_google_ads_status'] = "0";
                } elseif (!empty($this->request->getPost('android_google_ads_status'))) {
                    $updatedData['android_google_ads_status'] = $this->request->getPost('android_google_ads_status');
                } else {
                    $updatedData['android_google_ads_status'] = $data['android_google_ads_status'];
                }
                if ($this->request->getPost('ios_google_ads_status') == 0) {
                    $updatedData['ios_google_ads_status'] = "0";
                } elseif (!empty($this->request->getPost('ios_google_ads_status'))) {
                    $updatedData['ios_google_ads_status'] = $this->request->getPost('ios_google_ads_status');
                } else {
                    $updatedData['ios_google_ads_status'] = $data['ios_google_ads_status'];
                }



                if ($this->request->getPost('image_compression_preference') == 0) {
                    $updatedData['image_compression_preference'] = "0";
                    $updatedData['image_compression_quality'] = "0";
                } elseif (!empty($this->request->getPost('image_compression_preference'))) {
                    $updatedData['image_compression_preference'] = $this->request->getPost('image_compression_preference');
                } else {
                    $updatedData['image_compression_preference'] = $data['image_compression_preference'];
                }
                if (!empty($updatedData['system_timezone_gmt'])) {
                    if ($updatedData['system_timezone_gmt'] == " 00:00") {
                        $updatedData['system_timezone_gmt'] = '+' . trim($updatedData['system_timezone_gmt']);
                    }
                }

                unset($updatedData['update']);
                unset($updatedData[csrf_token()]);
                $json_string = json_encode($updatedData);
                if ($this->update_setting('general_settings', $json_string)) {
                    $_SESSION['toastMessage']  = labels('Unable to update the App settings', 'Unable to update the App settings');
                    $_SESSION['toastMessageType']  = 'error';
                } else {
                    $_SESSION['toastMessage'] = labels('App settings has been successfully updated', 'App settings has been successfully updated');
                    $_SESSION['toastMessageType']  = 'success';
                }
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/settings/app')->withCookies();
            }
            $this->builder->select('value');
            $this->builder->where('variable', 'general_settings');
            $query = $this->builder->get()->getResultArray();
            if (count($query) == 1) {
                $settings = $query[0]['value'];
                $settings = json_decode($settings, true);
                if (!empty($settings)) {
                    $this->data = array_merge($this->data, $settings);
                }
            }
            $this->data['timezones'] = get_timezone_array();
            setPageInfo($this->data, labels('App Settings', 'App Settings') . ' | ' . labels('admin_panel', 'Admin Panel'), 'app');
            return view('backend/admin/template', $this->data);
        } catch (\Throwable $th) {

            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Settings.php - app_settings()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
        if (!$this->isLoggedIn || !$this->userIsAdmin) {
            return redirect('admin/login');
        }
    }
    public function firebase_settings()
    {
        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            if ($this->request->getPost('update')) {
                if ($this->superadmin == "superadmin@gmail.com") {
                    defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
                } else {
                    if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                        $_SESSION['toastMessage'] = labels(DEMO_MODE_ERROR, DEMO_MODE_ERROR);
                        $_SESSION['toastMessageType']  = 'error';
                        $this->session->markAsFlashdata('toastMessage');
                        $this->session->markAsFlashdata('toastMessageType');
                        return redirect()->to('admin/settings/general-settings')->withCookies();
                    }
                }
                $updatedData = $this->request->getPost();
                unset($updatedData[csrf_token()]);
                unset($updatedData['update']);
                $json_file = false;
                $flag = 0;
                if (!empty($_FILES['json_file'])) {
                    if ($_FILES['json_file']['name'] != "") {
                        if (!valid_image('json_file')) {
                            $flag = 1;
                        } else {
                            $json_file = true;
                        }
                    }
                }
                if ($json_file) {
                    $file = $this->request->getFile('json_file');
                    $path = FCPATH . 'public/';
                    $newName = "firebase_config.json";
                    if (file_exists($path . $newName)) {
                        unlink($path . $newName);
                    }
                    $file->move($path, $newName);
                    $updatedData['json_file'] = $newName;
                } else {
                    $updatedData['json_file'] = isset($data['json_file']) ? $data['json_file'] : "";
                }
                $json_string = json_encode($updatedData);
                if ($this->update_setting('firebase_settings', $json_string)) {
                    $_SESSION['toastMessage']  = labels('Unable to update Firebase section', 'Unable to update Firebase section');
                    $_SESSION['toastMessageType']  = 'error';
                } else {
                    $_SESSION['toastMessage'] = labels('Firebase has been successfully updated', 'Firebase has been successfully updated');
                    $_SESSION['toastMessageType']  = 'success';
                }
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/settings/firebase_settings')->withCookies();
            }
            $this->builder->select('value');
            $this->builder->where('variable', 'firebase_settings');
            $query = $this->builder->get()->getResultArray();
            if (count($query) == 1) {
                $settings = $query[0]['value'];
                $settings = json_decode($settings, true);
                $this->data = array_merge($this->data, $settings);
            }
            setPageInfo($this->data, labels('Firebase Settings', 'Firebase Settings') . ' | ' . labels('admin_panel', 'Admin Panel'), 'firebase_settings');
            return view('backend/admin/template', $this->data);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Settings.php - firebase_settings()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function web_setting_page()
    {
        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            setPageInfo($this->data, labels('Web Settings', 'Web Settings') . ' | ' . labels('admin_panel', 'Admin Panel'), 'web_settings');
            $this->builder->select('value');
            $this->builder->where('variable', 'web_settings');
            $query = $this->builder->get()->getResultArray();
            if (count($query) == 1) {
                $settings = $query[0]['value'];
                $settings = json_decode($settings, true);
                $this->data = array_merge($this->data, $settings);
            }
            return view('backend/admin/template', $this->data);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Settings.php - web_setting_page()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function web_setting_update()
    {
        $path = FCPATH . "/public/uploads/web_settings/";
        if (!is_dir($path)) {
            mkdir($path, 0775, true);
        }
        try {
            $social_media = [];
            $updatedData['social_media'] = ($social_media);
            $updatedData['web_title'] = $_POST['web_title'];
            $updatedData['playstore_url'] = $_POST['playstore_url'];
            $updatedData['web_maintenance_mode'] = isset($_POST['web_maintenance_mode']) ? 1 : 0;


            
            $updatedData['applestore_url'] = $_POST['applestore_url'];
            if ($this->isLoggedIn && $this->userIsAdmin) {
                if ($this->request->getPost('update')) {
                    $old_settings = get_settings('web_settings', true);
                    $old_data = ['category_section_title', 'category_section_description', 'rating_section_title', 'rating_section_description', 'faq_section_title', 'faq_section_description', 'landing_page_logo', 'landing_page_backgroud_image', 'rating_section_status', 'faq_section_status', 'category_section_status', 'category_ids', 'rating_ids', 'landing_page_title', 'process_flow_title', 'process_flow_description', 'footer_description', 'step_1_title', 'step_2_title', 'step_3_title', 'step_4_title', 'step_1_description', 'step_2_description', 'step_3_description', 'step_4_description', 'step_1_image', 'step_2_image', 'step_3_image', 'step_4_image', 'process_flow_status'];
                    foreach ($old_data as $key) {
                        $updatedData[$key] = (!empty($this->request->getPost($key))) ? $this->request->getPost($key) : (isset($old_settings[$key]) ? ($old_settings[$key]) : "");
                    }
                    if ($this->superadmin == "superadmin@gmail.com") {
                        defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
                    } else {
                        if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                            $_SESSION['toastMessage'] = labels(DEMO_MODE_ERROR, DEMO_MODE_ERROR);
                            $_SESSION['toastMessageType']  = 'error';
                            $this->session->markAsFlashdata('toastMessage');
                            $this->session->markAsFlashdata('toastMessageType');
                            return redirect()->to('admin/settings/general-settings')->withCookies();
                        }
                    }
                    $data = get_settings('web_settings', true);
                    $files_to_check = array('web_logo', 'web_favicon', 'web_half_logo', 'footer_logo');
                    $path = FCPATH . 'public/uploads/web_settings/';
                    foreach ($files_to_check as $row) {
                        $file = $this->request->getFile($row);

                        if ($file && $file->isValid()) {
                            if (!valid_image($row)) {
                            } else {
                                $result = upload_file(
                                    $file,
                                    "public/uploads/web_settings/",
                                    labels('error uploading web settings file', 'error uploading web settings file'),
                                    'web_settings'
                                );

                                if ($result['error'] == false) {
                                    $updatedData[$row] = $result['file_name'];
                                } else {
                                    return ErrorResponse($result['message'], true, [], [], 200, csrf_token(), csrf_hash());
                                }
                            }
                        } else {
                            $updatedData[$row] = isset($data[$row]) ? $data[$row] : "";
                        }
                    }


                    $files_to_check = array('landing_page_logo', 'landing_page_backgroud_image', 'web_logo', 'web_favicon', 'web_half_logo', 'footer_logo', 'step_1_image', 'step_2_image', 'step_3_image', 'step_4_image');

                    foreach ($files_to_check as $row) {
                        $file = $this->request->getFile($row);

                        if ($file && $file->isValid()) {
                            if (!valid_image($row)) {
                                $flag = 1;
                            } else {
                                $result = upload_file(
                                    $file,
                                    "public/uploads/web_settings/",
                                    labels('error uploading web settings file', 'error uploading web settings file'),
                                    'web_settings'
                                );

                                if ($result['error'] == false) {
                                    $updatedData[$row] = $result['file_name'];
                                } else {
                                    return ErrorResponse($result['message'], true, [], [], 200, csrf_token(), csrf_hash());
                                }
                            }
                        } else {
                            $updatedData[$row] = isset($data[$row]) ? $data[$row] : "";
                        }
                    }
                    $updatedSocialMedia = [];
                    if (!empty($data['social_media'])) {
                        $updatedSocialMedia = [];
                    }
                    $updatedData1 = [];
                    $updatedSocialMedia = [];
                    $request = \Config\Services::request();
                    $updatedSocialMedia = [];
                    foreach ($_POST['social_media'] as $i => $item) {
                        $upload_path = 'public/uploads/web_settings/';

                        if ($item['exist_url'] == 'new') {
                            $file = $request->getFile("social_media.{$i}.file");
                            if ($file && $file->isValid()) {
                                $result = upload_file($file, $upload_path, labels('error uploading web settings file', 'error uploading web settings file'), 'web_settings');
                                if ($result['error'] == false) {
                                    $updatedSocialMedia[] = [
                                        'url' => $item['url'],
                                        'file' => $result['file_name']
                                    ];
                                } else {
                                    return ErrorResponse($result['message'], true, [], [], 200, csrf_token(), csrf_hash());
                                }
                            } else {
                                $disk = fetch_current_file_manager();

                                if (!empty($item['url'])) {
                                    $updatedSocialMedia[] = [
                                        'url' => $item['url'],
                                        'file' => ''
                                    ];
                                }
                            }
                        } else {
                            $updatedData1 = [
                                'url' => ($item['exist_url'] != $item['url']) ? $item['url'] : $item['exist_url']
                            ];
                            $file = $request->getFile("social_media.{$i}.file");
                            if ($file && $file->isValid()) {
                                $result = upload_file($file, $upload_path, labels('error uploading web settings file', 'error uploading web settings file'), 'web_settings');
                                if ($result['error'] == false) {
                                    $updatedData1['file'] = $result['file_name'];
                                } else {
                                    return ErrorResponse($result['message'], true, [], [], 200, csrf_token(), csrf_hash());
                                }
                            } else {
                                $disk = fetch_current_file_manager();

                                $updatedData1['file'] = $item['exist_file'];
                            }
                            $updatedSocialMedia[] = $updatedData1;
                        }
                    }
                    $updatedData['social_media'] = $updatedSocialMedia;

                  

                    unset($updatedData[csrf_token()]);
                    unset($updatedData['update']);
                    $json_string = json_encode($updatedData);
                    if ($this->update_setting('web_settings', $json_string)) {
                        $_SESSION['toastMessage']  = labels('Unable to update Web Settings', 'Unable to update Web Settings');
                        $_SESSION['toastMessageType']  = 'error';
                    } else {
                        $_SESSION['toastMessage'] = labels('Web Settings has been successfully updated', 'Web Settings has been successfully updated');
                        $_SESSION['toastMessageType']  = 'success';
                    }
                    $this->session->markAsFlashdata('toastMessage');
                    $this->session->markAsFlashdata('toastMessageType');
                    return redirect()->to('admin/settings/web_setting')->withCookies();
                }
                $this->builder->select('value');
                $this->builder->where('variable', 'web_settings');
                $query = $this->builder->get()->getResultArray();
                if (count($query) == 1) {
                    $settings = $query[0]['value'];
                    $settings = json_decode($settings, true);
                    $this->data = array_merge($this->data, $settings);
                }
                setPageInfo($this->data, labels('Web Settings', 'Web Settings') . ' | ' . labels('admin_panel', 'Admin Panel'), 'web_settings');
                return view('backend/admin/template', $this->data);
            } else {
                return redirect('admin/login');
            }
        } catch (\Throwable $th) {

            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Settings.php - web_setting_page()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function contry_codes()
    {
        setPageInfo($this->data, labels('Country Code Settings', 'Country Code Settings') . '  | ' . labels('admin_panel', 'Admin Panel'), 'country_code');
        return view('backend/admin/template', $this->data);
    }
    public function add_contry_code()
    {
        try {
            $result = checkModificationInDemoMode($this->superadmin);
            if ($result !== true) {
                return $this->response->setJSON($result);
            }
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            $this->validation->setRules(
                [
                    'name' => ["rules" => 'required', "errors" => ["required" => "Please enter name"]],
                    'code' => ["rules" => 'required', "errors" => ["required" => "Please enter code"]],
                ],
            );
            if (!$this->validation->withRequest($this->request)->run()) {
                $errors  = $this->validation->getErrors();
                $response['error'] = true;
                $response['message'] = labels($errors, $errors);
                $response['csrfName'] = csrf_token();
                $response['csrfHash'] = csrf_hash();
                $response['data'] = [];
                return $this->response->setJSON($response);
            }
            $data['code'] = ($_POST['code']);
            $data['name'] = ($_POST['name']);
            $contry_code = new Country_code_model();
            if ($contry_code->save($data)) {
                $response = [
                    'error' => false,
                    'message' => labels('Country code added successfully', 'Country code added successfully'),
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return json_encode($response);
            } else {
                $response = [
                    'error' => true,
                    'message' => labels('Please try again....', 'Please try again....'),
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return json_encode($response);
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Settings.php - add_contry_code()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function fetch_contry_code()
    {
        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
        $where = [];
        $from_app = false;
        $contry_code = new Country_code_model();
        $data = $contry_code->list($from_app, $search, $limit, $offset, $sort, $order, $where);
        return $data;
    }
    public function delete_contry_code()
    {
        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                $response['error'] = true;
                $response['message'] = labels(DEMO_MODE_ERROR, DEMO_MODE_ERROR);
                $response['csrfName'] = csrf_token();
                $response['csrfHash'] = csrf_hash();
                return $this->response->setJSON($response);
            }
            $db = \Config\Database::connect();
            $id = $this->request->getVar('id');
            $builder = $db->table('country_codes');
            $builder->where('id', $id);
            $data = fetch_details("country_codes", ['id' => $id]);
            $settings = fetch_details('country_codes', ['is_default' => 1]);
            if ($settings[0]['id'] ==  $id) {
                $response = [
                    'error' => true,
                    'message' => labels('Default country code cannot be removed', 'Default country code cannot be removed'),
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
            if ($builder->delete()) {
                $response = [
                    'error' => false,
                    'message' => labels('Country code Removed successfully', 'Country code Removed successfully'),
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Settings.php - delete_contry_code()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function store_default_country_code()
    {
        try {
            $settings = fetch_details('country_codes', ['is_default' => 1]);
            if (!empty($settings)) {
                $country_codes = fetch_details('country_codes', ['is_default' => 1]);
                $Country_code_model = new Country_code_model();
                $data['is_default'] = 0;
                $Country_code_model->update($country_codes[0]['id'], $data);
                $data2['is_default'] = 1;
                $Country_code_model2 = new Country_code_model();
                $Country_code_model2->update($_POST['id'], $data2);
            }
            $response = [
                'error' => false,
                'message' => labels('Default setted', 'Default setted'),
                'csrfName' => csrf_token(),
                'csrfHash' => csrf_hash(),
                'data' => []
            ];
            return $this->response->setJSON($response);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Settings.php - store_default_country_code()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function update_country_codes()
    {
        try {
            $result = checkModificationInDemoMode($this->superadmin);
            if ($result !== true) {
                return $this->response->setJSON($result);
            }
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            $this->validation->setRules(
                [
                    'name' => ["rules" => 'required', "errors" => ["required" => "Please enter name"]],
                    'code' => ["rules" => 'required', "errors" => ["required" => "Please enter code"]],
                ],
            );
            if (!$this->validation->withRequest($this->request)->run()) {
                $errors  = $this->validation->getErrors();
                $response['error'] = true;
                $response['message'] = $errors;
                $response['csrfName'] = csrf_token();
                $response['csrfHash'] = csrf_hash();
                $response['data'] = [];
                return $this->response->setJSON($response);
            }
            $data['code'] = ($_POST['code']);
            $data['name'] = ($_POST['name']);
            $contry_code = new Country_code_model();
            if ($contry_code->update($_POST['id'], $data)) {
                $response = [
                    'error' => false,
                    'message' => labels('Country code updated successfully', 'Country code updated successfully'),
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return json_encode($response);
            } else {
                $response = [
                    'error' => true,
                    'message' => labels('Please try again....', 'Please try again....'),
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return json_encode($response);
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Settings.php - update_country_codes()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function about_us_page_preview()
    {
        $settings = get_settings('general_settings', true);
        $this->data['title'] = 'About Us | ' . $settings['company_title'];
        $this->data['meta_description'] = 'About Us | ' . $settings['company_title'];
        $this->data['about_us'] = get_settings('about_us', true);
        $this->data['settings'] =  $settings;
        return view('backend/admin/pages/about_us_preview', $this->data);
    }
    public function contact_us_page_preview()
    {
        $settings = get_settings('general_settings', true);
        $this->data['title'] = 'Contact Us | ' . $settings['company_title'];
        $this->data['meta_description'] = 'Contact Us | ' . $settings['company_title'];
        $this->data['contact_us'] = get_settings('contact_us', true);
        $this->data['settings'] =  $settings;
        return view('backend/admin/pages/contact_us_preview', $this->data);
    }
    public function email_template_configuration()
    {
        if (!$this->isLoggedIn && !$this->userIsPartner) {
            return redirect('unauthorised');
        }
        setPageInfo($this->data, labels('Email Configuration', 'Email Configuration') . '  | ' . labels('admin_panel', 'Admin Panel'), 'email_template_configuration');
        return view('backend/admin/template', $this->data);
    }
    public function email_template_configuration_update()
    {
        try {
            $validationRules = [
                'subject' => ["rules" => 'required', "errors" => ["required" => "Please enter Subject"]],
                'email_type' => ["rules" => 'required', "errors" => ["required" => "Please select type"]],
                'template' => ["rules" => 'required', "errors" => ["required" => "Please select Template"]],
            ];
            if (!$this->validate($validationRules)) {
                $errors = $this->validator->getErrors();
                return ErrorResponse($errors, true, [], [], 200, csrf_token(), csrf_hash());
            }
            $updatedData = $this->request->getPost('template');
            $email_type = $this->request->getPost('email_type');
            $subject = $this->request->getPost('subject');
            $email_to = $this->request->getPost('email_to');
            $bcc = $this->request->getPost('bcc');
            $cc = $this->request->getPost('cc');
            $template = htmlspecialchars($updatedData);
            $parameters = extractVariables($updatedData);
            $data['type'] = $email_type;
            $data['subject'] = $subject;
            $data['to'] = json_encode($email_to);
            $data['template'] = $template;
            $data['bcc'] = $bcc;
            $data['cc'] = $cc;
            $data['parameters'] = json_encode($parameters);
            $insert = insert_details($data, 'email_templates');
            if ($insert) {
                $response = [
                    'error' => false,
                    'message' => labels('Template Saved successfully!', 'Template Saved successfully!'),
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
            } else {
                $response = [
                    'error' => true,
                    'message' => labels('Something Went Wrong', 'Something Went Wrong'),
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
            }
            return json_encode($response);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Settings.php - email_template_configuration_update()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function email_template_list()
    {
        if (!$this->isLoggedIn && !$this->userIsPartner) {
            return redirect('unauthorised');
        }
        setPageInfo($this->data, labels('Email Templates', 'Email Templates') . ' | ' . labels('admin_panel', 'Admin Panel'), 'email_template_list');
        return view('backend/admin/template', $this->data);
    }
    public function email_template_list_fetch()
    {
        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
        $where = [];
        $from_app = false;
        $email_templates = new Email_template_model();
        $data = $email_templates->list($from_app, $search, $limit, $offset, $sort, $order, $where);
        return $data;
    }
    public function edit_email_template()
    {
        if (!$this->isLoggedIn && !$this->userIsPartner) {
            return redirect('unauthorised');
        }
        helper('function');
        $uri = service('uri');
        $template_id = $uri->getSegments()[3];
        $templates = fetch_details('email_templates', ['id' => $template_id])[0];
        $this->data['template'] =  $templates;
        setPageInfo($this->data, labels('Email Templates', 'Email Templates') . ' | ' . labels('admin_panel', 'Admin Panel'), 'email_template_edit');
        return view('backend/admin/template', $this->data);
    }
    public function edit_email_template_operation()
    {
        try {
            $validationRules = [
                'subject' => ["rules" => 'required', "errors" => ["required" => "Please enter Subject"]],
                'email_type' => ["rules" => 'required', "errors" => ["required" => "Please select type"]],
                'template' => ["rules" => 'required', "errors" => ["required" => "Please select Template"]],
            ];
            if (!$this->validate($validationRules)) {
                $errors = $this->validator->getErrors();
                return ErrorResponse($errors, true, [], [], 200, csrf_token(), csrf_hash());
            }
            $updatedData = $this->request->getPost('template');
            $email_type = $this->request->getPost('email_type');
            $subject = $this->request->getPost('subject');
            $email_to = $this->request->getPost('email_to');
            $bcc = $this->request->getPost('bcc');
            $cc = $this->request->getPost('cc');
            $template = ($updatedData);
            $parameters = extractVariables($updatedData);
            $data['type'] = $email_type;
            $data['subject'] = $subject;
            $data['to'] = json_encode($email_to);
            $data['template'] = $this->request->getPost('template');;
            if (isset($_POST['bcc'][0]) && !empty($_POST['bcc'][0])) {
                $base_tags = $this->request->getPost('bcc');
                $s_t = $base_tags;
                $val = explode(',', str_replace(']', '', str_replace('[', '', $s_t[0])));
                $bcc = [];
                foreach ($val as $s) {
                    $bcc[] = json_decode($s, true)['value'];
                }
                $data['bcc'] = implode(',', $bcc);
            }
            if (isset($_POST['cc'][0]) && !empty($_POST['cc'][0])) {
                $base_tags = $this->request->getPost('cc');
                $s_t = $base_tags;
                $val = explode(',', str_replace(']', '', str_replace('[', '', $s_t[0])));
                $cc = [];
                foreach ($val as $s) {
                    $cc[] = json_decode($s, true)['value'];
                }
                $data['cc'] = implode(',', $cc);
            }
            $data['parameters'] = json_encode($parameters);
            $update = update_details($data, ['id' => $_POST['template_id']], 'email_templates', false);
            if ($update) {
                $response = [
                    'error' => false,
                    'message' => labels('Template updated successfully!'),
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
            } else {
                $response = [
                    'error' => true,
                    'message' => labels('Something Went Wrong', 'Something Went Wrong'),
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
            }
            return json_encode($response);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Settings.php - edit_email_template_operation()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function delete_email_template()
    {
        try {
            if ($this->superadmin == "superadmin@gmail.com") {
                defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
            } else {
                if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                    $_SESSION['toastMessage'] = labels(DEMO_MODE_ERROR, DEMO_MODE_ERROR);
                    $_SESSION['toastMessageType']  = 'error';
                    $this->session->markAsFlashdata('toastMessage');
                    $this->session->markAsFlashdata('toastMessageType');
                    return redirect()->to('admin/settings/general-settings')->withCookies();
                }
            }
            $creator_id = $this->userId;
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            $id = $this->request->getPost('id');
            $db = \Config\Database::connect();
            $builder = $db->table('email_templates');
            if ($builder->delete(['id' => $id])) {
                return successResponse("Email template deleted successfully", false, [], [], 200, csrf_token(), csrf_hash());
            }
            return ErrorResponse("An error occured during deleting this item", true, [], [], 200, csrf_token(), csrf_hash());
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Settings.php - delete_email_template()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function sms_gateway_setting_index()
    {
        if (!$this->isLoggedIn) {
            return redirect('unauthorised');
        }
        $this->builder->select('value');
        $this->builder->where('variable', 'sms_gateway_setting');
        $query = $this->builder->get()->getResultArray();
        if (count($query) == 1) {
            $settings = $query[0]['value'];
            $settings = json_decode($settings, true);
            if (!empty($settings)) {
                $this->data = array_merge($this->data, $settings);
            }
        }
        setPageInfo($this->data, labels('SMS Gateway settings', 'SMS Gateway settings') . ' | ' . labels('admin_panel', 'Admin Panel'), 'sms_gateways');
        return view('backend/admin/template', $this->data);
    }
    public function sms_gateway_setting_update()
    {
        if ($this->superadmin == "superadmin@gmail.com") {
            defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
        } else {
            if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                $_SESSION['toastMessage'] = labels(DEMO_MODE_ERROR, DEMO_MODE_ERROR);
                $_SESSION['toastMessageType']  = 'error';
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/settings/sms-gateways')->withCookies();
            }
        }
        $smsgateway_data = array();
        $smsgateway_data['twilio']['twilio_status'] = isset($_POST['twilio_status']) ? '1' : '0';
        $smsgateway_data['twilio']['twilio_account_sid'] = isset($_POST['twilio_account_sid']) ? $_POST['twilio_account_sid'] : '';
        $smsgateway_data['twilio']['twilio_auth_token'] = isset($_POST['twilio_auth_token']) ? $_POST['twilio_auth_token'] : '';
        $smsgateway_data['twilio']['twilio_from'] = isset($_POST['twilio_from']) ? $_POST['twilio_from'] : '';
        $smsgateway_data['vonage']['vonage_status'] = isset($_POST['vonage_status']) ? '1' : '0';
        $smsgateway_data['vonage']['vonage_api_key'] = isset($_POST['vonage_api_key']) ? $_POST['vonage_api_key'] : '';
        $smsgateway_data['vonage']['vonage_api_secret'] = isset($_POST['vonage_api_secret']) ? $_POST['vonage_api_secret'] : '';
        $current_sms_gateway = ''; // Default to null if none is active
        if ($smsgateway_data['twilio']['twilio_status'] === '1') {
            $current_sms_gateway = 'twilio';
        } elseif ($smsgateway_data['vonage']['vonage_status'] === '1') {
            $current_sms_gateway = 'vonage';
        }
        $smsgateway_data['current_sms_gateway'] = $current_sms_gateway;
        $smsgateway_data = json_encode($smsgateway_data);
        $this->update_setting('sms_gateway_setting', $smsgateway_data);
        if ($this->update_setting('sms_gateway_setting', $smsgateway_data)) {
            $_SESSION['toastMessage']  = labels('Unable to update the SMS Gateway settings', 'Unable to update the SMS Gateway settings');
            $_SESSION['toastMessageType']  = 'error';
        } else {
            $_SESSION['toastMessage'] = labels('SMS Gateway settings has been successfully updated', 'SMS Gateway settings has been successfully updated');
            $_SESSION['toastMessageType']  = 'success';
        }
        $this->session->markAsFlashdata('toastMessage');
        $this->session->markAsFlashdata('toastMessageType');
        return redirect()->to('admin/settings/sms-gateways')->withCookies();
    }
    public function sms_templates()
    {
        try {
            $validationRules = [
                'title' => ["rules" => 'required', "errors" => ["required" => "Please enter Title"]],
                'type' => ["rules" => 'required', "errors" => ["required" => "Please select type"]],
                'template' => ["rules" => 'required', "errors" => ["required" => "Please select Template"]],
            ];
            if (!$this->validate($validationRules)) {
                $errors = $this->validator->getErrors();
                return ErrorResponse($errors, true, [], [], 200, csrf_token(), csrf_hash());
            }
            $updatedData = $this->request->getPost('template');
            $type = $this->request->getPost('type');
            $title = $this->request->getPost('title');
            $template = htmlspecialchars($updatedData);
            $parameters = extractVariables($updatedData);
            $data['type'] = $type;
            $data['title'] = $title;
            $data['template'] = $template;
            $data['parameters'] = json_encode($parameters);
            $insert = insert_details($data, 'sms_templates');
            if ($insert) {
                $response = [
                    'error' => false,
                    'message' => labels('Template Saved successfully!', 'Template Saved successfully!'),
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
            } else {
                $response = [
                    'error' => true,
                    'message' => labels('Something Went Wrong', 'Something Went Wrong'),
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
            }
            return json_encode($response);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Settings.php - sms_templates()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function sms_template_list()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('sms_templates');
        $multipleWhere = [];
        $condition = $bulkData = $rows = $tempRow = [];
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
        $sort = ($_GET['sort'] ?? '') == 'id' ? 'id' : ($_GET['sort'] ?? 'id');
        $order = $_GET['order'] ?? 'DESC';
        $offset = $_GET['offset'] ?? '0';
        if (!empty($search)) {
            $multipleWhere = [
                'id' => $search,
                'type' => $search,
            ];
        }
        if (!empty($where)) {
            $builder->where($where);
        }
        if (!empty($multipleWhere)) {
            $builder->groupStart()->orLike($multipleWhere)->groupEnd();
        }
        $total = $builder->countAllResults(false);
        $template_record = $builder->select('*')
            ->orderBy($sort, $order)
            ->limit($limit, $offset)
            ->get()
            ->getResultArray();
        foreach ($template_record as $row) {
            $operations = '';
            $operations = '<div class="dropdown">
                    <a class="" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <button class="btn btn-secondary btn-sm px-3"> <i class="fas fa-ellipsis-v "></i></button>
                    </a>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">';
            $operations .= '<a class="dropdown-item" href="' . base_url('/admin/settings/edit_sms_template/' . $row['id']) . '"><i class="fa fa-pen mr-1 text-primary"></i>'. labels('edit_sms_template', 'Edit SMS Template') .'</a>';
            $operations .= '</div></div>';
            $tempRow['id'] = $row['id'];
            $tempRow['type'] = $row['type'];
            $tempRow['title'] = $row['title'];
            $tempRow['template'] = $row['template'];
            $tempRow['parameters'] =  substr($row['parameters'], 0, 30) . '...';
            $truncatedtemplate = substr($row['template'], 0, 30) . '...';
            $tempRow['truncatedtemplate'] = $truncatedtemplate;
            $tempRow['operations'] = $operations;
            $rows[] = $tempRow;
        }
        $bulkData['total'] = $total;
        $bulkData['rows'] = $rows;
        return json_encode($bulkData);
    }
    public function edit_sms_template()
    {
        if (!$this->isLoggedIn && !$this->userIsPartner) {
            return redirect('unauthorised');
        }
        helper('function');
        $uri = service('uri');
        $template_id = $uri->getSegments()[3];
        $templates = fetch_details('sms_templates', ['id' => $template_id]);
        if (empty($templates)) {
            $templates = fetch_details('sms_templates', [], [], [], 1, 0);
        }
        $this->data['template'] =  $templates[0];
        setPageInfo($this->data, labels('SMS Templates', 'SMS Templates') . ' | ' . labels('admin_panel', 'Admin Panel'), 'edit_sms_template');
        return view('backend/admin/template', $this->data);
    }
    public function edit_sms_template_update()
    {
        try {
            $validationRules = [
                'title' => ["rules" => 'required', "errors" => ["required" => "Please enter Title"]],
                'type' => ["rules" => 'required', "errors" => ["required" => "Please select type"]],
                'template' => ["rules" => 'required', "errors" => ["required" => "Please select Template"]],
            ];
            if (!$this->validate($validationRules)) {
                $errors = $this->validator->getErrors();
                return ErrorResponse($errors, true, [], [], 200, csrf_token(), csrf_hash());
            }
            $updatedData = $this->request->getPost('template');
            $id = $this->request->getPost('template_id');
            $type = $this->request->getPost('type');
            $title = $this->request->getPost('title');
            $template = htmlspecialchars($updatedData);
            $parameters = extractVariables($updatedData);
            $data['type'] = $type;
            $data['title'] = $title;
            $data['template'] = $template;
            $data['parameters'] = json_encode($parameters);
            $update = update_details($data, ['id' => $id], 'sms_templates', false);
            if ($update) {
                $response = [
                    'error' => false,
                    'message' => labels('Template updated successfully!', 'Template updated successfully!'),
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
            } else {
                $response = [
                    'error' => true,
                    'message' => labels('Something Went Wrong', 'Something Went Wrong'),
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
            }
            return json_encode($response);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Settings.php - sms_templates()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function notification_settings()
    {
        if (!$this->isLoggedIn) {
            return redirect('unauthorised');
        }
        $current_settings = get_settings('notification_settings', true);
        $notification_settings = ['provider_approved', 'provider_disapproved', 'withdraw_request_approved', 'withdraw_request_disapproved', 'payment_settlement', 'service_approved', 'service_disapproved', 'user_account_active', 'user_account_deactive', 'provider_update_information', 'new_provider_registerd', 'withdraw_request_received', 'booking_status_updated', 'new_booking_confirmation_to_customer', 'new_booking_received_for_provider', 'withdraw_request_send', 'new_rating_given_by_customer', 'rating_request_to_customer'];
        $this->data['notification_settings'] = $notification_settings;
        $this->data['current_settings'] = $current_settings; // Include current settings
        setPageInfo($this->data, labels('Notification settings', 'Notification settings') . ' | ' . labels('admin_panel', 'Admin Panel'), 'notification_settings');
        return view('backend/admin/template', $this->data);
    }
    public function notification_setting_update()
    {
        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            $updatedData = $this->request->getPost();
            unset($updatedData['update']);
            unset($updatedData[csrf_token()]);
            $json_string = json_encode($updatedData);
            if ($this->update_setting('notification_settings', $json_string)) {
                $_SESSION['toastMessage']  = labels('Unable to update the Notification settings', 'Unable to update the Notification settings');
                $_SESSION['toastMessageType']  = 'error';
            } else {
                $_SESSION['toastMessage'] = labels('Notification settings has been successfully updated', 'Notification settings has been successfully updated');
                $_SESSION['toastMessageType']  = 'success';
            }
            $this->session->markAsFlashdata('toastMessage');
            $this->session->markAsFlashdata('toastMessageType');
            return redirect()->to('admin/settings/notification-settings')->withCookies();
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Settings.php - notification_setting_update()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function sms_email_preview()
    {
        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            $uri = service('uri');
            $type = $uri->getSegments()[3];
            $email_template = fetch_details('email_templates', ['type' => $type]);
            $sms_template = fetch_details('sms_templates', ['type' => $type]);
            $this->data['email_template'] =  $email_template[0];
            $this->data['sms_template'] =  $sms_template[0];
            setPageInfo($this->data, labels('Preview Of Templates', 'Preview Of Templates') . ' | ' . labels('admin_panel', 'Admin Panel'), 'sms_email_preview');
            return view('backend/admin/template', $this->data);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Settings.php - sms_email_preview()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function web_landing_page_settings()
    {
        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            $this->builder->select('value');
            $this->builder->where('variable', 'web_settings');
            $query = $this->builder->get()->getResultArray();
            if (count($query) == 1) {
                $settings = $query[0]['value'];
                $settings = json_decode($settings, true);
                $this->data = array_merge($this->data, $settings);
            }
            $db = \Config\Database::connect();
            $builder = $db->table('services_ratings sr');
            $builder->select('sr.*,u.image as profile_image,u.username')
                ->join('users u', "(sr.user_id = u.id)")
                ->orderBy('id', 'DESC');
            $services_ratings = $builder->get()->getResultArray();
            foreach ($services_ratings as $key => $row) {
                $services_ratings[$key]['profile_image'] = base_url('public/backend/assets/profiles/' . $row['profile_image']);
            }
            $this->data['services_ratings'] = $services_ratings;

            $this->data['categories_name'] = fetch_details('categories', [], ['id', 'name']);
            setPageInfo($this->data, labels('Web Landing Page Settings', 'Web Landing Page Settings') . ' | ' . labels('admin_panel', 'Admin Panel'), 'web_landing_page');
            return view('backend/admin/template', $this->data);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Settings.php - web_landing_page_settings()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function web_setting_landing_page_update()
    {
        try {
            $updatedData['category_section_title'] = $_POST['category_section_title'];
            $updatedData['category_section_description'] = $_POST['category_section_description'];
            $updatedData['rating_section_title'] = $_POST['rating_section_title'];
            $updatedData['rating_section_description'] = $_POST['rating_section_description'];
            $updatedData['faq_section_title'] = $_POST['faq_section_title'];
            $updatedData['faq_section_description'] = $_POST['faq_section_description'];
            $updatedData['rating_section_status'] = isset($_POST['rating_section_status']) ? 1 : 0;
            $updatedData['faq_section_status'] = isset($_POST['faq_section_status']) ? 1 : 0;
            $updatedData['category_section_status'] = isset($_POST['category_section_status']) ? 1 : 0;
            $updatedData['process_flow_status'] = isset($_POST['process_flow_status']) ? 1 : 0;
            $updatedData['landing_page_title'] = $_POST['landing_page_title'];
            $updatedData['process_flow_title'] = $_POST['process_flow_title'];
            $updatedData['process_flow_description'] = $_POST['process_flow_description'];
            $updatedData['footer_description'] = $_POST['footer_description'];
            $updatedData['step_1_title'] = $_POST['step_1_title'];
            $updatedData['step_2_title'] = $_POST['step_2_title'];
            $updatedData['step_3_title'] = $_POST['step_3_title'];
            $updatedData['step_4_title'] = $_POST['step_4_title'];
            $updatedData['step_1_description'] = $_POST['step_1_description'];
            $updatedData['step_2_description'] = $_POST['step_2_description'];
            $updatedData['step_3_description'] = $_POST['step_3_description'];
            $updatedData['step_4_description'] = $_POST['step_4_description'];
            $categories = $this->request->getPost('categories');
            if (!empty($categories)) {
                $category_ids =  !empty($categories) ? ($categories) : "";
            }
            $updatedData['category_ids'] = isset($category_ids) ? $category_ids : '';
            $ratings = $this->request->getPost('new_rating_ids');
            if (!empty($ratings)) {
                $rating_ids =  !empty($ratings) ? ($ratings) : "";
            }
            $updatedData['rating_ids'] = isset($rating_ids) ? $rating_ids : '';
            $old_settings = get_settings('web_settings', true);
            $old_data = ['social_media', 'web_title', 'playstore_url','web_maintenance_mode', 'app_section_status', 'applestore_url', 'web_logo', 'web_favicon', 'web_half_logo', 'footer_logo',];
            foreach ($old_data as $key) {
                $updatedData[$key] = (!empty($this->request->getPost($key))) ? $this->request->getPost($key) : (isset($old_settings[$key]) ? ($old_settings[$key]) : "");
            }
            if ($this->isLoggedIn && $this->userIsAdmin) {
                if ($this->request->getPost('update')) {
                    if ($this->superadmin == "superadmin@gmail.com") {
                        defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
                    } else {
                        if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                            $_SESSION['toastMessage'] = labels(DEMO_MODE_ERROR, DEMO_MODE_ERROR);
                            $_SESSION['toastMessageType']  = 'error';
                            $this->session->markAsFlashdata('toastMessage');
                            $this->session->markAsFlashdata('toastMessageType');
                            return redirect()->to('admin/settings/general-settings')->withCookies();
                        }
                    }
                    $data = get_settings('web_settings', true);


                    $data = get_settings('web_settings', true);
                    $files_to_check = array('landing_page_logo', 'landing_page_backgroud_image', 'web_logo', 'web_favicon', 'web_half_logo', 'footer_logo', 'step_1_image', 'step_2_image', 'step_3_image', 'step_4_image');

                    foreach ($files_to_check as $row) {
                        $file = $this->request->getFile($row);

                        if ($file && $file->isValid()) {
                            if (!valid_image($row)) {
                                $flag = 1;
                            } else {
                                $result = upload_file(
                                    $file,
                                    "public/uploads/web_settings/",
                                    labels('error uploading web settings file', 'error uploading web settings file'),
                                    'web_settings'
                                );

                                if ($result['error'] == false) {
                                    $updatedData[$row] = $result['file_name'];
                                } else {
                                    return ErrorResponse($result['message'], true, [], [], 200, csrf_token(), csrf_hash());
                                }
                            }
                        } else {
                            $updatedData[$row] = isset($data[$row]) ? $data[$row] : "";
                        }
                    }
                    unset($updatedData[csrf_token()]);
                    unset($updatedData['update']);
                    $json_string = json_encode($updatedData);
                    if ($this->update_setting('web_settings', $json_string)) {
                        $_SESSION['toastMessage']  = labels('Unable to update Landing Page', 'Unable to update Landing Page');
                        $_SESSION['toastMessageType']  = 'error';
                    } else {
                        $_SESSION['toastMessage'] = labels('Landing Page Settings has been successfully updated', 'Landing Page Settings has been successfully updated');
                        $_SESSION['toastMessageType']  = 'success';
                    }
                    $this->session->markAsFlashdata('toastMessage');
                    $this->session->markAsFlashdata('toastMessageType');
                    return redirect()->to('admin/settings/web-landing-page-settings')->withCookies();
                }
            } else {
                return redirect('admin/login');
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Settings.php - web_setting_page()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function review_list()
    {
        $db      = \Config\Database::connect();
        $builder = $db->table('services_ratings sr');
        $builder->select('COUNT(sr.id) as total')
            ->join('users u', 'u.id = sr.user_id')
            ->join('services s', 's.id = sr.service_id');
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->orLike($multipleWhere);
        }
        $ratings_total_count = $builder->get()->getResultArray();
        $total = $ratings_total_count[0]['total'];
        $builder->select(
            'sr.id, sr.rating, sr.comment, sr.created_at as rated_on, 
         u.image as profile_image, u.username as user_name, 
         s.title as service_name, s.user_id as partner_id'
        )
            ->join('users u', 'u.id = sr.user_id')
            ->join('services s', 's.id = sr.service_id');
        if (isset($_GET['rating_star_filter']) && $_GET['rating_star_filter'] != '') {
            $builder->where('sr.rating', $_GET['rating_star_filter']);
        }
        $limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
        $offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
        $rating_records = $builder->orderBy('sr.id', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResultArray();
        $rows = [];
        foreach ($rating_records as $row) {
            $tempRow = [
                'id' => $row['id'],
                'comment' => $row['comment'],
                'profile_image' => $row['profile_image'],
                'user_name' => $row['user_name'],
                'service_name' => $row['service_name'],
                'rated_on' => $row['rated_on'],
                'stars' => '<i class="fa-solid fa-star text-warning"></i> ' . $row['rating'],
                'partner_name' => fetch_details('users', ['id' => $row['partner_id']], ['username'])[0]['username'],
            ];
            $rows[] = $tempRow;
        }
        $bulkData = [
            'total' => $total,
            'rows' => $rows
        ];
        return $this->response->setJSON($bulkData);
    }
    public function become_provider_setting_page()
    {
        if (!$this->isLoggedIn || !$this->userIsAdmin) {
            return redirect('admin/login');
        }
        $this->builder->select('value');
        $this->builder->where('variable', 'become_provider_page_settings');
        $query = $this->builder->get()->getResultArray();
        if (count($query) == 1) {
            $settings1 = $query[0]['value'];
            $settings1 = json_decode($settings1, true);

            $this->data = array_merge($this->data, $settings1);
        }
        setPageInfo($this->data, labels('Become Provider Settings', 'Become Provider Settings') . ' | ' . labels('admin_panel', 'Admin Panel'), 'become_provider_page_settings');
        return view('backend/admin/template', $this->data);
    }
    public function become_provider_setting_page_update()
    {
        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            $request = $this->request->getPost();
            $uploadedFiles = $this->request->getFiles();

            $rules = [];
            $sections = ['hero_section', 'how_it_work_section', 'category_section', 'subscription_section', 'top_providers_section', 'review_section', 'faq_section',];

            $rules = [];



            // Hero Section
            if (isset($request['hero_section_status']) && (($request['hero_section_status'] == "on") || $request['hero_section_status'] == "1")) {
                $rules = array_merge($rules, [
                    'hero_section_short_headline' => ["rules" => 'required|trim', "errors" => ["required" => "Please enter hero section short headline"]],
                    'hero_section_title' => ["rules" => 'required|trim', "errors" => ["required" => "Please enter hero section title"]],
                    'hero_section_description' => ["rules" => 'required|trim', "errors" => ["required" => "Please enter hero section description"]],
                ]);
            }

            // How It Works Section
            if (isset($request['how_it_work_section_status']) && (($request['how_it_work_section_status'] == "on") || $request['how_it_work_section_status'] == "1")) {
                $rules = array_merge($rules, [
                    'how_it_work_section_short_headline' => ["rules" => 'required|trim', "errors" => ["required" => "Please enter how it works section short headline"]],
                    'how_it_work_section_title' => ["rules" => 'required|trim', "errors" => ["required" => "Please enter how it works section title"]],
                    'how_it_work_section_description' => ["rules" => 'required|trim', "errors" => ["required" => "Please enter how it works section description"]],
                ]);
            }

            // Category Section
            if (isset($request['category_section_status']) && (($request['category_section_status'] == "on") || $request['category_section_status'] == "1")) {
                $rules = array_merge($rules, [
                    'category_section_short_headline' => ["rules" => 'required|trim', "errors" => ["required" => "Please enter category section short headline"]],
                    'category_section_title' => ["rules" => 'required|trim', "errors" => ["required" => "Please enter category section title"]],
                    'category_section_description' => ["rules" => 'required|trim', "errors" => ["required" => "Please enter category section description"]],
                ]);
            }

            // Subscription Section
            if (isset($request['subscription_section_status']) && (($request['subscription_section_status'] == "on") || $request['subscription_section_status'] == "1")) {
                $rules = array_merge($rules, [
                    'subscription_section_short_headline' => ["rules" => 'required|trim', "errors" => ["required" => "Please enter subscription section short headline"]],
                    'subscription_section_title' => ["rules" => 'required|trim', "errors" => ["required" => "Please enter subscription section title"]],
                    'subscription_section_description' => ["rules" => 'required|trim', "errors" => ["required" => "Please enter subscription section description"]],
                ]);
            }

            // Top Providers Section
            if (isset($request['top_providers_section_status']) && (($request['top_providers_section_status'] == "on") || $request['top_providers_section_status'] == "1")) {
                $rules = array_merge($rules, [
                    'top_providers_section_short_headline' => ["rules" => 'required|trim', "errors" => ["required" => "Please enter top providers section short headline"]],
                    'top_providers_section_title' => ["rules" => 'required|trim', "errors" => ["required" => "Please enter top providers section title"]],
                    'top_providers_section_description' => ["rules" => 'required|trim', "errors" => ["required" => "Please enter top providers section description"]],
                ]);
            }

            // Review Section
            if (isset($request['review_section_status']) && (($request['review_section_status'] == "on") || $request['review_section_status'] == "1")) {
                $rules = array_merge($rules, [
                    'review_section_short_headline' => ["rules" => 'required|trim', "errors" => ["required" => "Please enter review section short headline"]],
                    'review_section_title' => ["rules" => 'required|trim', "errors" => ["required" => "Please enter review section title"]],
                    'review_section_description' => ["rules" => 'required|trim', "errors" => ["required" => "Please enter review section description"]],
                ]);
            }

            // FAQ Section
            if (isset($request['faq_section_status']) && (($request['faq_section_status'] == "on") || $request['faq_section_status'] == "1")) {
                $rules = array_merge($rules, [
                    'faq_section_short_headline' => ["rules" => 'required|trim', "errors" => ["required" => "Please enter faq section short headline"]],
                    'faq_section_title' => ["rules" => 'required|trim', "errors" => ["required" => "Please enter faq section title"]],
                    'faq_section_description' => ["rules" => 'required|trim', "errors" => ["required" => "Please enter faq section description"]],
                ]);
            }



            if (!$this->validation->setRules($rules)->withRequest($this->request)->run()) {
                $errors = $this->validation->getErrors();
            }

            // Additional Validation for Steps
            if (
                isset($request['how_it_work_section_status']) &&
                (($request['how_it_work_section_status'] == "on") || $request['how_it_work_section_status'] == "1")
            ) {
                foreach ($request['how_it_work_section_steps'] as $index => $step) {
                    if (empty($step['title']) || empty($step['description'])) {
                        $errors["how_it_work_section_steps.$index"] = "Please enter how it works section steps";
                    }
                }
            }

            // Additional Validation for Steps
            if (
                isset($request['feature_section_status']) &&
                (($request['feature_section_status'] == "on") || $request['feature_section_status'] == "1")
            ) {

                foreach ($request['feature_section_feature'] as $index => $feature) {
                    if (empty($feature['title']) || empty($feature['description'])) {
                        $errors["feature_section_feature.$index"] = "Please enter features in feature section";
                    }
                }
            }



            // Check if there are errors
            if (!empty($errors)) {
                $errorMessages = implode('<br>', array_values($errors)); // Join errors with a line break
                $this->session->setFlashdata('toastMessage', $errorMessages);
                $this->session->setFlashdata('toastMessageType', 'error');
                return redirect()->back()->withInput();
            }

            // Process each section
            $settings = [];
            $disk = fetch_current_file_manager();

            foreach ($sections as $section) {
                $section_data = [
                    'status' => ((isset($request["{$section}_status"])) && ($request["{$section}_status"] == "on")) ? 1 : 0,
                    'short_headline' => $request["{$section}_short_headline"],
                    'title' => $request["{$section}_title"],
                    'description' => $request["{$section}_description"]
                ];
                if ($section == 'how_it_work_section') {
                    $section_data['steps'] = json_encode($request['how_it_work_section_steps']);
                } elseif ($section == 'hero_section') {
                    $hero_section_images_selector = [];
                    // Handle existing images first
                    $existing_images = $request['hero_section_images_existing'] ?? [];
                    if (!empty($existing_images)) {
                        foreach ($existing_images as $existing_image) {
                            $hero_section_images_selector[] = [
                                'image' => $existing_image['image'],
                            ];
                        }
                    }
                    // Handle new uploaded images
                    if (isset($uploadedFiles['hero_section_images'])) {
                        foreach ($uploadedFiles['hero_section_images'] as $img) {
                            if ($img->isValid()) {
                                $result = upload_file($img, "public/uploads/become_provider/", "error creating become_provider", 'become_provider');
                                if ($result['error'] == false) {
                                    $hero_section_images_selector[] = [
                                        'image' => $result['file_name'],
                                    ];
                                } else {
                                    return ErrorResponse($result['message'], true, [], [], 200, csrf_token(), csrf_hash());
                                }
                            }
                        }
                    }
                    $section_data['images'] = $hero_section_images_selector;
                }
                $settings[$section] = json_encode($section_data);
            }

            if (isset($request['feature_section_status']) && (($request['feature_section_status'] == "on") || $request['feature_section_status'] == "1")) {
                if (isset($request['feature_section_feature']) && $request['feature_section_feature']) {
                    $features = $request['feature_section_feature'];
                    $updatedFeatures = [];
                    $request = \Config\Services::request();
                    $uploadPath = 'public/uploads/become_provider/';
                    foreach ($features as $i => $item) {
                        // Handle new feature entry
                        if ($item['exist_image'] == 'new') {
                            $file = $request->getFile("feature_section_feature.{$i}.image");
                            if ($file && $file->isValid()) {
                                $result = upload_file($file, $uploadPath, "error creating feature section", 'become_provider');
                                if ($result['error'] == false) {
                                    $updatedFeatures[] = [
                                        'short_headline' => trim($item['short_headline']),
                                        'title' => trim($item['title']),
                                        'description' => trim($item['description']),
                                        'position' => $item['position'],
                                        'image' => $result['file_name']
                                    ];
                                } else {
                                    return ErrorResponse($result['message'], true, [], [], 200, csrf_token(), csrf_hash());
                                }
                            } else {

                                // If no file is uploaded for new entry, still add the feature
                                $updatedFeatures[] = [
                                    'short_headline' => trim($item['short_headline']),
                                    'title' => trim($item['title']),
                                    'description' => trim($item['description']),
                                    'position' => $item['position'],
                                    'image' => ''  // or set a default image
                                ];
                            }
                        }
                        // Handle existing feature entry
                        else {


                            $updatedData = [
                                'short_headline' => trim($item['short_headline']),
                                'title' => trim($item['title']),
                                'description' => trim($item['description']),
                                'position' => $item['position']
                            ];
                            // Check if a new file is being uploaded for existing entry
                            $file = $request->getFile("feature_section_feature.{$i}.image");
                            if ($file && $file->isValid()) {
                                $result = upload_file($file, $uploadPath, "error updating feature section", 'feature_section');
                                if ($result['error'] == false) {
                                    $updatedData['image'] = $result['file_name'];
                                    // Delete old image if exists
                                    if (!empty($item['exist_image']) && file_exists($uploadPath . $item['exist_image'])) {
                                        unlink($uploadPath . $item['exist_image']);
                                    }
                                } else {
                                    return ErrorResponse($result['message'], true, [], [], 200, csrf_token(), csrf_hash());
                                }
                            } else {
                                $disk = fetch_current_file_manager();

                                // Keep existing image if no new file uploaded
                                $updatedData['image'] = $item['exist_image'];
                            }
                            $updatedFeatures[] = $updatedData;
                        }
                    }
                    // Prepare final settings array
                    $feature_section = [
                        'status' => ($request->getPost("feature_section_status") !== null && $request->getPost("feature_section_status") === "on") ? 1 : 0,
                        'features' => ($updatedFeatures),
                    ];
                    $settings['feature_section'] = json_encode($feature_section);
                }
            }

            // Update settings with new data
            $json_string = json_encode($settings);
            if ($this->update_setting('become_provider_page_settings', $json_string)) {
                $_SESSION['toastMessage']  = labels('Unable to update the Become Provider Page settings', 'Unable to update the Become Provider Page settings');
                $_SESSION['toastMessageType']  = 'error';
            } else {
                $_SESSION['toastMessage'] = labels('Become Provider Page settings has been successfuly updated', 'Become Provider Page settings has been successfuly updated');
                $_SESSION['toastMessageType']  = 'success';
            }
            $this->session->markAsFlashdata('toastMessage');
            $this->session->markAsFlashdata('toastMessageType');
            return redirect()->to('admin/settings/become-provider-setting')->withCookies();
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
