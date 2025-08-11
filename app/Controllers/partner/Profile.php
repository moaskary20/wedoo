<?php

namespace App\Controllers\partner;

class Profile extends Partner
{
    protected $validationListTemplate = 'list';
    public function __construct()
    {
        parent::__construct();
        helper('ResponceServices');
    }
    public function index()
    {
        if ($this->isLoggedIn) {
            setPageInfo($this->data, 'Profile | Provider Panel', 'profile');
            $partner_details = !empty(fetch_details('partner_details', ['partner_id' => $this->userId])) ? fetch_details('partner_details', ['partner_id' => $this->userId])[0] : [];
            $partner_timings = !empty(fetch_details('partner_timings', ['partner_id' => $this->userId])) ? fetch_details('partner_timings', ['partner_id' => $this->userId]) : [];
            $disk = fetch_current_file_manager();

            $partner_details['banner'] = get_file_url($disk, $partner_details['banner'], 'public/backend/assets/profiles/default.png', 'banner');

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
            // Process user details
            $user_details = fetch_details('users', ['id' => $this->userId])[0];
            $user_details['image'] = get_file_url($disk,  $user_details['image'],  '',  'profile');
            $this->data['data'] = $user_details;

            $this->data['partner_details'] = $partner_details;
            $this->data['partner_timings'] = array_reverse($partner_timings);
            $settings = get_settings('general_settings', true);
            $user_id = $this->ionAuth->getUserId();
            $admin_commission = fetch_details('partner_details', ['partner_id' => $user_id], 'admin_commission');
            $this->data['city_id']  = fetch_details('users', ['id' => $user_id], 'city')[0]['city'];
            $this->data['city'] = $this->data['city_id'];
            $this->data['admin_commission'] = $admin_commission[0]['admin_commission'];
            $this->data['currency'] = $settings['currency'];
            $this->data['city_name'] = $this->data['city_id'];

            $this->data['allow_pre_booking_chat'] = $settings['allow_pre_booking_chat'] ?? 0;
            $this->data['allow_post_booking_chat'] = $settings['allow_post_booking_chat'] ?? 0;

            return view('backend/partner/template', $this->data);
        } else {
            return redirect('partner/login');
        }
    }
    public function update_profile()
    {
        try {
            if (isset($_POST) && !empty($_POST)) {
                try {
                    $config = new \Config\IonAuth();
                    $tables  = $config->tables;
                    $this->validation->setRules(
                        [
                            'username' => [
                                "rules" => 'required|trim',
                                "errors" => [
                                    "required" => "Please enter username"
                                ]
                            ],
                            'email' => [
                                "rules" => 'required|trim',
                                "errors" => [
                                    "required" => "Please enter providers email",
                                ]
                            ],
                            'phone' => [
                                "rules" => 'required|numeric|',
                                "errors" => [
                                    "required" => "Please enter admin phone number",
                                    "numeric" => "Please enter numeric phone number",
                                    "is_unique" => "This phone number is already registered"
                                ]
                            ],
                            'address' => [
                                "rules" => 'required|trim',
                                "errors" => [
                                    "required" => "Please enter address",
                                ]
                            ],
                            'latitude' => [
                                "rules" => 'required|trim',
                                "errors" => [
                                    "required" => "Please choose provider location",
                                ]
                            ],
                            'longitude' => [
                                "rules" => 'required|trim',
                                "errors" => [
                                    "required" => "Please choose provider location",
                                ]
                            ],
                            'type' => [
                                "rules" => 'required',
                                "errors" => [
                                    "required" => "Please select providers type",
                                ]
                            ],
                            'visiting_charges' => [
                                "rules" => 'required|numeric',
                                "errors" => [
                                    "required" => "Please enter visiting charges",
                                    "numeric" => "Please enter numeric value for visiting charges"
                                ]
                            ],
                            'advance_booking_days' => [
                                "rules" => 'required|numeric',
                                "errors" => [
                                    "required" => "Please enter advance booking days",
                                    "numeric" => "Please enter numeric advance booking days"
                                ]
                            ],
                            'start_time' => [
                                "rules" => 'required',
                                "errors" => [
                                    "required" => "Please enter providers working days",
                                ]
                            ],
                            'end_time' => [
                                "rules" => 'required',
                                "errors" => [
                                    "required" => "Please enter providers working properly ",
                                ]
                            ],

                        ],
                    );
                    if (!$this->validation->withRequest($this->request)->run()) {
                        $errors = $this->validation->getErrors();
                        return ErrorResponse($errors, true, [], [], 200, csrf_token(), csrf_hash());
                    } else {
                        if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                            $response['error'] = true;
                            $response['message'] = DEMO_MODE_ERROR;
                            $response['csrfName'] = csrf_token();
                            $response['csrfHash'] = csrf_hash();
                            return $this->response->setJSON($response);
                        }
                        $data = fetch_details('users', ['id' => $this->userId])[0];
                        $IdProofs = fetch_details('partner_details', ['partner_id' => $this->userId], ['national_id', 'other_images', 'address_id', 'passport', 'banner'])[0];
                        $old_image = $data['image'];
                        $old_banner = $IdProofs['banner'];
                        $old_national_id = $IdProofs['national_id'];
                        $old_address_id = $IdProofs['address_id'];
                        $old_passport = $IdProofs['passport'];
                        $old_other_images = fetch_details('partner_details', ['partner_id' => $this->userId], ['other_images']);
                        $disk = fetch_current_file_manager();

                        $paths = [
                            'image' => [
                                'file' => $this->request->getFile('image'),
                                'path' => 'public/backend/assets/profile/',
                                'error' => 'Failed to create profile folders',
                                'folder' => 'profile',
                                'old_file' => $old_image,
                                'disk' => $disk,
                            ],
                            'banner' => [
                                'file' => $this->request->getFile('banner'),
                                'path' => 'public/backend/assets/banner/',
                                'error' => 'Failed to create banner folders',
                                'folder' => 'banner',
                                'old_file' => $old_banner,
                                'disk' => $disk,
                            ],
                            'national_id' => [
                                'file' => $this->request->getFile('national_id'),
                                'path' => 'public/backend/assets/national_id/',
                                'error' => 'Failed to create national_id folders',
                                'folder' => 'national_id',
                                'old_file' => $old_national_id,
                                'disk' => $disk,
                            ],
                            'address_id' => [
                                'file' => $this->request->getFile('address_id'),
                                'path' => 'public/backend/assets/address_id/',
                                'error' => 'Failed to create address_id folders',
                                'folder' => 'address_id',
                                'old_file' => $old_address_id,
                                'disk' => $disk,
                            ],
                            'passport' => [
                                'file' => $this->request->getFile('passport'),
                                'path' => 'public/backend/assets/passport/',
                                'error' => 'Failed to create passport folders',
                                'folder' => 'passport',
                                'old_file' => $old_passport,
                                'disk' => $disk
                            ]
                        ];

                        // Process single file uploads
                        $uploadedFiles = [];
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
                                    $result = upload_file($file, 'public/uploads/partner/', 'Failed to upload other images', 'partner');
                                    if ($result['error'] == false) {
                                        $uploadedOtherImages[] = $result['disk'] === "local_server"
                                            ? 'public/uploads/services/' . $result['file_name']
                                            : $result['file_name'];
                                    } else {
                                        return ErrorResponse($result['message'], true, [], [], 200, csrf_token(), csrf_hash());
                                    }
                                }
                            }
                        }




                        $other_images = !empty($uploadedOtherImages) ? json_encode($uploadedOtherImages) : $IdProofs['other_images'];


                        $banner = $uploadedFiles['banner']['url'] ?? 'public/backend/assets/banner/' . $this->request->getFile('banner_image')->getName();
                        $national_id = $uploadedFiles['national_id']['url'] ?? 'public/backend/assets/national_id/' . $this->request->getFile('national_id')->getName();
                        $address_id = $uploadedFiles['address_id']['url'] ?? 'public/backend/assets/address_id/' . $this->request->getFile('address_id')->getName();
                        $passport = $uploadedFiles['passport']['url'] ?? 'public/backend/assets/passport/' . $this->request->getFile('passport')->getName();

                        if (isset($uploadedFiles['banner']['disk']) && $uploadedFiles['banner']['disk'] == 'local_server') {
                            // $banner = 'public/backend/assets/banner/' . $uploadedFiles['banner']['url'];
                            $uploadedFiles['banner']['url'] = preg_replace('#(public/backend/assets/banner/)+#', '', $uploadedFiles['banner']['url']);
                            $banner = 'public/backend/assets/banner/' . $uploadedFiles['banner']['url'];
        
                        } else if (isset($uploadedFiles['banner']['disk']) && $uploadedFiles['banner']['disk'] == 'aws_s3') {
                            $banner = $uploadedFiles['banner']['url'];
                        } else {
                            $banner = 'public/backend/assets/banner/' . $uploadedFiles['banner']['url'];
                            $uploadedFiles['banner']['url'] = preg_replace('#(public/backend/assets/banner/)+#', '', $uploadedFiles['banner']['url']);
                            $banner = 'public/backend/assets/banner/' . $uploadedFiles['banner']['url'];
        
                        }
                        if (isset($uploadedFiles['national_id']['disk']) && $uploadedFiles['national_id']['disk'] == 'local_server') {
                            // $national_id = 'public/backend/assets/national_id/' . $uploadedFiles['national_id']['url'];
                            $uploadedFiles['national_id']['url'] = preg_replace('#^public/backend/assets/national_id/#', '', $uploadedFiles['national_id']['url']);
                            $national_id = 'public/backend/assets/national_id/' . $uploadedFiles['national_id']['url'];
        
                        } else if (isset($uploadedFiles['national_id']['disk']) && $uploadedFiles['national_id']['disk'] == 'aws_s3') {
                            $national_id = $uploadedFiles['national_id']['url'];
                        } else {
                            // $national_id = 'public/backend/assets/national_id/' . $uploadedFiles['national_id']['url'];
                            $uploadedFiles['national_id']['url'] = preg_replace('#^public/backend/assets/national_id/#', '', $uploadedFiles['national_id']['url']);
                            $national_id = 'public/backend/assets/national_id/' . $uploadedFiles['national_id']['url'];
        
                        }
                        if (isset($uploadedFiles['address_id']['disk']) && $uploadedFiles['address_id']['disk'] == 'local_server') {
                            // $address_id = 'public/backend/assets/address_id/' . $uploadedFiles['address_id']['url'];
                            $uploadedFiles['address_id']['url'] = preg_replace('#^public/backend/assets/address_id/#', '', $uploadedFiles['address_id']['url']);
                            $address_id = 'public/backend/assets/address_id/' . $uploadedFiles['address_id']['url'];
                 
                        } else if (isset($uploadedFiles['address_id']['disk']) && $uploadedFiles['address_id']['disk'] == 'aws_s3') {
                            $address_id = $uploadedFiles['address_id']['url'];
                        } else {
                            $uploadedFiles['address_id']['url'] = preg_replace('#^public/backend/assets/address_id/#', '', $uploadedFiles['address_id']['url']);
                            $address_id = 'public/backend/assets/address_id/' . $uploadedFiles['address_id']['url'];
                 
                            // $address_id = 'public/backend/assets/address_id/' . $uploadedFiles['address_id']['url'];
                        }
                        if (isset($uploadedFiles['passport']['disk']) && $uploadedFiles['passport']['disk'] == 'local_server') {
                            // $passport = 'public/backend/assets/passport/' . $uploadedFiles['passport']['url'];
                            $uploadedFiles['passport']['url'] = preg_replace('#^public/backend/assets/passport/#', '', $uploadedFiles['passport']['url']);
                            $passport = 'public/backend/assets/passport/' . $uploadedFiles['passport']['url'];
                    
                        } else if (isset($uploadedFiles['passport']['disk']) && $uploadedFiles['passport']['disk'] == 'aws_s3') {
                            $passport = $uploadedFiles['passport']['url'];
                        } else {
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
                                ['partner_id' => $this->userId],
                                'partner_details',
                                false
                            );
                        }
                        $image = $uploadedFiles['image']['url'] ?? 'public/backend/assets/profile/' . $this->request->getFile('image')->getName();
                        if (isset($uploadedFiles['image']['disk']) && $uploadedFiles['image']['disk'] == 'local_server') {
                            $uploadedFiles['image']['url'] = preg_replace('#^public/backend/assets/profile/#', '', $uploadedFiles['image']['url']);

                            $image = 'public/backend/assets/profile/' . $uploadedFiles['image']['url'];
                        }
                        $userData = [
                            'username' => $this->request->getPost('username'),
                            'email' => $this->request->getPost('email'),
                            'phone' => $this->request->getPost('phone'),
                            'image' => $image,
                            'latitude' => $this->request->getPost('latitude'),
                            'longitude' => $this->request->getPost('longitude'),
                            'city' => $this->request->getPost('city'),
                        ];
                        if ($userData) {
                            update_details($userData, ['id' => $this->userId], 'users');
                        }
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
                            'long_description' => (isset($_POST['long_description'])) ? $_POST['long_description'] : "",
                            'address' => $this->request->getPost('address'),
                            'at_store' => (isset($_POST['at_store'])) ? 1 : 0,
                            'at_doorstep' => (isset($_POST['at_doorstep'])) ? 1 : 0,
                            'chat' => (isset($_POST['chat'])) ? 1 : 0,
                            'pre_chat' => (isset($_POST['pre_chat'])) ? 1 : 0,
                            'other_images' => $other_images,
                          




                        ];
                        if ($partner_details) {
                            update_details($partner_details, ['partner_id' => $this->userId], 'partner_details', false);
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
                            $timing_data = fetch_details('partner_timings', ['partner_id' => $this->userId, 'day' => $days[$i]]);
                            if (count($timing_data) > 0) {
                                update_details($partner_timing, ['partner_id' => $this->userId, 'day' => $days[$i]], 'partner_timings');
                            } else {
                                $partner_timing['partner_id'] = $this->userId;
                                insert_details($partner_timing, 'partner_timings');
                            }
                        }
                        return successResponse("Profile updated successfully!", false, [], [], 200, csrf_token(), csrf_hash());
                    }
                } catch (\Throwable $th) {
                  
                }
            }
        } catch (\Throwable $th) {
            
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/Profile.php - update_profile()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function update()
    {
        try {
            $national_id = $this->request->getFile('national_id');
            $address_id = $this->request->getFile('address_id');
            $passport = $this->request->getFile('passport');
            if ($this->isLoggedIn) {
                if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                    $response['error'] = true;
                    $response['message'] = DEMO_MODE_ERROR;
                    $response['csrfName'] = csrf_token();
                    $response['csrfHash'] = csrf_hash();
                    return $this->response->setJSON($response);
                }
                if ($this->request->getFile('national_id') && !empty($this->request->getFile('national_id'))) {
                    $file = $this->request->getFile('national_id');
                    if (!$file->isValid()) {
                        return ErrorResponse("Something went wrong please try after some time", true, [], [], 200, csrf_token(), csrf_hash());
                    }
                    $type = $file->getMimeType();
                    if ($type == 'image/jpeg' || $type == 'image/png' || $type == 'image/jpg') {
                        $path = FCPATH . 'public/backend/assets/kyc-details/';
                        if (!empty($check_image)) {
                            $image_name = $check_image[0]['image'];
                            unlink($path . '' . $image_name);
                        }
                        $image = $file->getName();
                        $newName = $file->getRandomName();
                        $file->move($path, $newName);
                        $data['national_id'] =  $newName;
                    } else {
                        return ErrorResponse("Please attach a valid image file.", true, [], [], 200, csrf_token(), csrf_hash());
                    }
                }
                if ($this->request->getFile('address_id') && !empty($this->request->getFile('address_id'))) {
                    $file = $this->request->getFile('address_id');
                    if (!$file->isValid()) {
                        return ErrorResponse("Something went wrong please try after some time.", true, [], [], 200, csrf_token(), csrf_hash());
                    }
                    $type = $file->getMimeType();
                    if ($type == 'image/jpeg' || $type == 'image/png' || $type == 'image/jpg') {
                        $path = FCPATH . 'public/backend/assets/kyc-details/';
                        if (!empty($check_image)) {
                            $image_name = $check_image[0]['image'];
                            unlink($path . '' . $image_name);
                        }
                        $image = $file->getName();
                        $newName = $file->getRandomName();
                        $file->move($path, $newName);
                        $data['address_id'] =  $newName;
                    } else {
                        return ErrorResponse("Please attach a valid image file.", true, [], [], 200, csrf_token(), csrf_hash());
                    }
                }
                if ($this->request->getFile('passport') && !empty($this->request->getFile('passport'))) {
                    $file = $this->request->getFile('passport');
                    if (!$file->isValid()) {
                        return ErrorResponse("Something went wrong please try after some time.", true, [], [], 200, csrf_token(), csrf_hash());
                    }
                    $type = $file->getMimeType();
                    if ($type == 'image/jpeg' || $type == 'image/png' || $type == 'image/jpg') {
                        $path = FCPATH . 'public/backend/assets/kyc-details/';
                        if (!empty($check_image)) {
                            $image_name = $check_image[0]['image'];
                            unlink($path . '' . $image_name);
                        }
                        $image = $file->getName();
                        $newName = $file->getRandomName();
                        $file->move($path, $newName);
                        $data['passport'] =  $newName;
                    } else {
                        return ErrorResponse("Please attach a valid image file.", true, [], [], 200, csrf_token(), csrf_hash());
                    }
                }
                if (isset($_POST['bank_name']) && !empty($_POST['bank_name'])) {
                    $data['bank_name'] = $_POST['bank_name'];
                }
                if (isset($_POST['account_number']) && !empty($_POST['account_number'])) {
                    $data['account_number'] = $_POST['account_number'];
                }
                if (isset($_POST['account_name']) && !empty($_POST['account_name'])) {
                    $data['account_name'] = $_POST['account_name'];
                }
                if (isset($_POST['bank_code']) && !empty($_POST['bank_code'])) {
                    $data['bank_code'] = $_POST['bank_code'];
                }
                if (isset($_POST['advance_booking_days']) && !empty($_POST['advance_booking_days'])) {
                    $data['advance_booking_days'] = $_POST['advance_booking_days'];
                }
                if (isset($_POST['type']) && !empty($_POST['type'])) {
                    $data['type'] = $_POST['type'];
                }
                if (isset($_POST['visiting_charges']) && !empty($_POST['visiting_charges'])) {
                    $data['visiting_charges'] = $_POST['visiting_charges'];
                }
                $days = [
                    0 => 'monday',
                    1 => 'tuesday',
                    2 => 'wednsday',
                    3 => 'thursday',
                    4 => 'friday',
                    5 => 'staturday',
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
                    if (exists(['partner_id' => $this->userId, 'day' => $days[$i]], 'partner_timings')) {
                        update_details($partner_timing, ['partner_id' => $this->userId, 'day' => $days[$i]], 'partner_timings');
                    } else {
                        $partner_timing['partner_id'] = $this->userId;
                        insert_details($partner_timing, 'partner_timings');
                    }
                }
                if (exists(['partner_id' => $this->userId], 'partner_details')) {
                    update_details($data, ['partner_id' => $this->userId], 'partner_details');
                } else {
                    $data['partner_id'] = $this->userId;
                    insert_details($data, 'partner_details');
                }
                $data = [
                    'username' => $_POST['username'],
                    'email' => $_POST['email'],
                    'phone' => $_POST['phone'],
                ];
                if ($this->request->getPost('profile')) {
                    $img = $this->request->getPost('profile');
                    $f = finfo_open();
                    $mime_type = finfo_buffer($f, $img, FILEINFO_MIME_TYPE);
                    if ($mime_type != 'text/plain') {
                        $response['error'] = true;
                        return $this->response->setJSON([
                            'csrfName' => csrf_token(),
                            'csrfHash' => csrf_hash(),
                            'error' => true,
                            'message' => "Please Insert valid image",
                            "data" => []
                        ]);
                    }
                    $data_photo = $img;
                    $img_dir = './public/backend/assets/profiles/';
                    list($type, $data_photo) = explode(';', $data_photo);
                    list(, $data_photo) = explode(',', $data_photo);
                    $data_photo = base64_decode($data_photo);
                    $filename = microtime(true) . '.jpg';
                    if (!is_dir($img_dir)) {
                        mkdir($img_dir, 0777, true);
                    }
                    if (file_put_contents($img_dir . $filename, $data_photo)) {
                        $profile = $filename;
                        $data['image'] = $filename;
                        $old_image = fetch_details('users', ['id' => $this->userId], ['image']);
                        if ($old_image[0]['image'] != "") {
                            if (is_readable("public/backend/assets/profiles/" . $old_image[0]['image']) && unlink("public/backend/assets/profiles/" . $old_image[0]['image'])) {
                            }
                        }
                    } else {
                        $data['image'] = $this->input->post('old_profile');
                        $profile = $this->input->post('old_profile');
                    }
                }
                $status = update_details(
                    $data,
                    ['id' => $this->userId],
                    'users'
                );
                if ($status) {
                    if (isset($_POST['old']) && isset($_POST['new']) && ($_POST['new'] != "") && ($_POST['old'] != "")) {
                        $identity = $this->session->get('identity');
                        $change = $this->ionAuth->changePassword($identity, $this->request->getPost('old'), $this->request->getPost('new'), $this->userId);
                        if ($change) {
                            $this->ionAuth->logout();
                            return successResponse("User updated successfully", false, $_POST, [], 200, csrf_token(), csrf_hash());
                        } else {
                            return ErrorResponse("Old password did not matched.", true, [], [], 200, csrf_token(), csrf_hash());
                        }
                    }
                    return successResponse("User updated successfully", false, $_POST, [], 200, csrf_token(), csrf_hash());
                } else {
                    return ErrorResponse("Something went wrong...", true, [], [], 200, csrf_token(), csrf_hash());
                }
            } else {
                return ErrorResponse("unauthorized", true, [], [], 200, csrf_token(), csrf_hash());
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/Profile.php - update()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
}
