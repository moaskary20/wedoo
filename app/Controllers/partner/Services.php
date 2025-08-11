<?php
namespace App\Controllers\partner;
use App\Models\Service_model;
use PhpOffice\PhpSpreadsheet\IOFactory;
class Services extends Partner
{
    public $service, $validations, $db;
    public function __construct()
    {
        parent::__construct();
        $this->service = new Service_model();
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
            $tax_details = fetch_details('taxes', ['status' => 1]);
            setPageInfo($this->data, 'Services | Provider Panel', 'services');
            $this->data['tax_details'] = $tax_details;
            $this->data['tax'] = get_settings('system_tax_settings', true);
            $this->data['categories'] = fetch_details('categories', []);
            $tax_data = fetch_details('taxes', ['status' => '1'], ['id', 'title', 'percentage']);
            $this->data['tax_data'] = $tax_data;
            return view('backend/partner/template', $this->data);
        } else {
            return redirect('partner/login');
        }
    }
    public function add()
    {
        if ($this->isLoggedIn) {
            if (!exists(['partner_id' => $this->userId, 'is_approved' => 1], 'partner_details')) {
                return redirect('partner/profile');
            }
            setPageInfo($this->data, 'Add Services | Provider Panel', FORMS . 'add_services');
            $this->data['categories'] = fetch_details('categories', []);
            $this->data['tax'] = get_settings('system_tax_settings', true);
            $tax_details = fetch_details('taxes', ['status' => 1]);
            $this->data['tax_details'] = $tax_details;
            $tax_data = fetch_details('taxes', ['status' => '1'], ['id', 'title', 'percentage']);
            $this->data['tax_data'] = $tax_data;
            return view('backend/partner/template', $this->data);
        } else {
            return redirect('partner/login');
        }
    }
    public function add_service()
    {
        try {
            if ($this->isLoggedIn) {
                if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                    $response['error'] = true;
                    $response['message'] = DEMO_MODE_ERROR;
                    $response['csrfName'] = csrf_token();
                    $response['csrfHash'] = csrf_hash();
                    return $this->response->setJSON($response);
                }
                if (isset($_POST) && !empty($_POST)) {
                    $price = $this->request->getPost('price');
                    $this->validation->setRules(
                        [
                            'title' => [
                                "rules" => 'required',
                                "errors" => [
                                    "required" => "Please enter service title"
                                ]
                            ],
                            'categories' => [
                                "rules" => 'required',
                                "errors" => [
                                    "required" => "Please select category"
                                ]
                            ],
                            'tags' => [
                                "rules" => 'required',
                                "errors" => [
                                    "required" => "Please enter service tag"
                                ]
                            ],
                            'price' => [
                                "rules" => 'required|numeric',
                                "errors" => [
                                    "required" => "Please enter price",
                                    "numeric" => "Please enter numeric value for price"
                                ]
                            ],
                            'discounted_price' => [
                                "rules" => 'required|numeric|less_than[' . $price . ']',
                                "errors" => [
                                    "required" => "Please enter discounted price",
                                    "numeric" => "Please enter numeric value for discounted price",
                                    "less_than" => "Discounted price should be less than price"
                                ]
                            ],
                            'members' => [
                                "rules" => 'required|numeric',
                                "errors" => [
                                    "required" => "Please enter required member for service",
                                    "numeric" => "Please enter numeric value for required member"
                                ]
                            ],
                            'duration' => [
                                "rules" => 'required|numeric',
                                "errors" => [
                                    "required" => "Please enter duration to perform task",
                                    "numeric" => "Please enter numeric value for duration of task"
                                ]
                            ],
                            'max_qty' => [
                                "rules" => 'required|numeric',
                                "errors" => [
                                    "required" => "Please enter max quantity allowed for services",
                                    "numeric" => "Please enter numeric value for max quantity allowed for services"
                                ]
                            ],
                        'service_slug' => ["rules" => 'required', "errors" => ["required" => "Please enter service slug",]],
                        ],
                    );
                    if (!$this->validation->withRequest($this->request)->run()) {
                        $errors  = $this->validation->getErrors();
                        return ErrorResponse($errors, true, [], [], 200, csrf_token(), csrf_hash());
                    } else {
                        if (isset($_POST['tags'][0]) && !empty($_POST['tags'][0])) {
                            $base_tags = $this->request->getPost('tags');
                            $s_t = $base_tags;
                            $val = explode(',', str_replace(']', '', str_replace('[', '', $s_t[0])));
                            $tags = [];
                            foreach ($val as $s) {
                                $tags[] = json_decode($s, true)['value'];
                            }
                        }
                        $title = $this->removeScript($this->request->getPost('title'));
                        $description = $this->removeScript($this->request->getPost('description'));
                        $path = "./public/uploads/services/";
                        if (isset($_POST['service_id']) && !empty($_POST['service_id'])) {
                            $service_id = $_POST['service_id'];
                            $old_icon = fetch_details('services', ['id' => $service_id], ['image'])[0]['image'];
                        } else {
                            $service_id = "";
                            $old_icon = "";
                        }
                        // $uploadedFiles = $this->request->getFiles('filepond');
                        // if (!empty($uploadedFiles)) {
                        //     $imagefile = $uploadedFiles['image'];
                        //     $files_selector = [];
                        //     $main_image_name = "";
                        //     if ($imagefile->isValid()) {
                        //         $name = $imagefile->getRandomName();
                        //         $tempPath = $imagefile->getTempName();
                        //         compressImage($tempPath, "./public/uploads/services/" . $name, 70);
                        //         $main_image_name = 'public/uploads/services/' . $name;
                        //     }
                        // }
                        // if (!empty($uploadedFiles)) {
                        //     $imagefile = $uploadedFiles['files'];
                        //     $files_selector = [];
                        //     foreach ($imagefile as $key => $img) {
                        //         if ($img->isValid()) {
                        //             $name = $img->getName();
                        //             $name = str_replace([' ', '_', '@', '#', '$', '%'], '-', $name);
                        //             if ($img->move($path, $name)) {
                        //                 $image_name = $name;
                        //                 $files_selector[$key] = "public/uploads/services/" . $image_name;
                        //             }
                        //         }
                        //     }
                        //     $files = ['files' => !empty($files_selector) ? json_encode($files_selector) : "",];
                        // }
                        // if (!empty($uploadedFiles)) {
                        //     $imagefile = $uploadedFiles['other_service_image_selector'];
                        //     $other_service_image_selector = [];
                        //     foreach ($imagefile as $key => $img) {
                        //         if ($img->isValid()) {
                        //             $name = $img->getRandomName();
                        //             $tempPath = $img->getTempName();
                        //             compressImage($tempPath, "./public/uploads/services/" . $name, 70);
                        //             $other_service_image_selector[$key] = "public/uploads/services/" . $name;
                        //             $image_name = $name;
                        //         }
                        //     }
                        //     $other_images = ['other_images' => !empty($other_service_image_selector) ? json_encode($other_service_image_selector) : "",];
                        // }
                        $paths = [
                            'image' => [
                                'file' => $this->request->getFile('image'),
                                'path' => 'public/uploads/services/',
                                'error' => 'Failed to create services folders',
                                'folder' => 'services'
                            ],
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
                        $multipleFiles = $this->request->getFiles('filepond');
                        $otherImagesConfig = [
                            'path' => 'public/uploads/services/',
                            'error' => 'Failed to upload other images',
                            'folder' => 'services'
                        ];
                        $uploadedOtherImages = [];
                        if (isset($multipleFiles['other_service_image_selector'])) {
                            $files = $multipleFiles['other_service_image_selector'];
                            foreach ($files as $file) {
                                if ($file->isValid()) {
                                    $result = upload_file($file, $otherImagesConfig['path'], $otherImagesConfig['error'], $otherImagesConfig['folder']);
                                    if ($result['error'] == false) {
                                        if ($result['disk'] == "local_server") {
                                            $uploadedOtherImages[] = 'public/uploads/services/' . $result['file_name'];
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
                        $uploadedFilesDocuments = [];
                        $FilesDocumentsConfig = [
                            'path' => 'public/uploads/services/',
                            'error' => 'Failed to upload files',
                            'folder' => 'services'
                        ];
                        // $multipleFilesDocument = $this->request->getFiles('filepond');
                        if (isset($multipleFiles['files'])) {
                            $files = $multipleFiles['files'];
                            foreach ($files as $file) {
                                if ($file->isValid()) {
                                    $result = upload_file($file, $FilesDocumentsConfig['path'], $FilesDocumentsConfig['error'], $FilesDocumentsConfig['folder']);
                                    if ($result['error'] == false) {
                                        if ($result['disk'] == "local_server") {
                                            $uploadedFilesDocuments[] = 'public/uploads/services/' . $result['file_name'];
                                        } elseif ($result['disk'] == "aws_s3") {
                                            $uploadedFilesDocuments[] = $result['file_name'];
                                        } else {
                                            $uploadedFilesDocuments[] = $result['file_name'];
                                        }
                                    } else {
                                        return ErrorResponse($result['message'], true, [], [], 200, csrf_token(), csrf_hash());
                                    }
                                }
                            }
                        }
                        $files = [
                            'files' => !empty($uploadedFilesDocuments) ? json_encode($uploadedFilesDocuments) : "",
                        ];
                        if (isset($_POST['sub_category']) && !empty($_POST['sub_category'])) {
                            $category_id = $_POST['sub_category'];
                        } else {
                            $category_id = $_POST['categories'];
                        }
                        $discounted_price = $this->request->getPost('discounted_price');
                        $price = $this->request->getPost('price');
                        if ($discounted_price >= $price && $discounted_price == $price) {
                            return ErrorResponse("discounted price can not be higher than or equal to the price", true, [], [], 200, csrf_token(), csrf_hash());
                        }
                        $partner_data = fetch_details('partner_details', ['partner_id' => $this->ionAuth->getUserId()]);
                        if ($this->request->getVar('members') > $partner_data[0]['number_of_members']) {
                            return ErrorResponse("Number Of member could not greater than " . $partner_data[0]['number_of_members'], true, [], [], 200, csrf_token(), csrf_hash());
                        }
                        $user_id = $this->ionAuth->getUserId();
                        if (isset($_POST['is_cancelable']) && $_POST['is_cancelable'] == 'on') {
                            $is_cancelable = "1";
                        } else {
                            $is_cancelable = "0";
                        }
                        if ($is_cancelable == "1" && $this->request->getVar('cancelable_till') == "") {
                            return ErrorResponse("Please Add Minutes", true, [], [], 200, csrf_token(), csrf_hash());
                        }
                        $faqs = $this->request->getPost('faqs');
                        if (!empty($faqs)) {
                            if ($faqs[0][0] == "" && $faqs[0][1] == "") {
                                $empty = [];
                                $faqData = ['faqs' => json_encode($empty)];
                            } else {
                                $faqData = ['faqs' => !empty($faqs) ? json_encode($faqs) : ""];
                            }
                            // $faqData = ['faqs' => !empty($faqs) ? json_encode($faqs) : ""];
                        } else {
                            $empty = [];
                            $faqData = ['faqs' => json_encode($empty)];
                        }
                        $status = ($this->request->getPost('status') == "on") ? "1" : "0";
                        $partner_details = fetch_details('partner_details', ['partner_id' => $user_id]);
                        if ($partner_details[0]['need_approval_for_the_service'] == 1) {
                            $approved_by_admin = 0;
                        } else {
                            $approved_by_admin = 1;
                        }
                        if (isset($uploadedFiles['image']['disk']) && $uploadedFiles['image']['disk'] == 'local_server') {
                            $image = 'public/uploads/services/' . $uploadedFiles['image']['url'];
                        } else {
                            $image = $uploadedFiles['image']['url'];
                        }
                        $service = array(
                            'id' => $service_id,
                            'user_id' => $user_id,
                            'category_id' => $category_id,
                            'tax_type' => $this->request->getVar('tax_type'),
                            'tax_id' => $this->request->getVar('tax_id'),
                            'title' => $title,
                            'description' => $description,
                            'slug' => '',
                            'tags' =>  implode(',', $tags),
                            'price' => $price,
                            'discounted_price' => $discounted_price,
                            'image' => $image,
                            'other_images' => $other_images['other_images'] ?? '',
                            'number_of_members_required' => $this->request->getVar('members'),
                            'duration' => $this->request->getVar('duration'),
                            'rating' => 0,
                            'number_of_ratings' => 0,
                            'status' => $status,
                            'is_pay_later_allowed' => ($this->request->getPost('pay_later') == "on") ? 1 : 0,
                            'is_cancelable' => $is_cancelable,
                            'cancelable_till' => ($is_cancelable == "1") ? $this->request->getVar('cancelable_till') : '',
                            'max_quantity_allowed' => $this->request->getPost('max_qty'),
                            'long_description' => (isset($_POST['long_description'])) ? $_POST['long_description'] : "",
                            'files' => isset($files) ? $files : "",
                            'faqs' => isset($faqData) ? $faqData : "",
                            'at_store' => ($this->request->getPost('at_store') == "on") ? 1 : 0,
                            'at_doorstep' => ($this->request->getPost('at_doorstep') == "on") ? 1 : 0,
                            'approved_by_admin' => $approved_by_admin,
                            'slug' => generate_unique_slug($this->request->getPost('service_slug'), 'services'),
                        );
                        $service_model = new Service_model();
                        if ($service_model->save($service)) {
                            return successResponse("Service saved successfully", false, [], [], 200, csrf_token(), csrf_hash());
                        } else {
                            return ErrorResponse("Service can not be Save!", true, [], [], 200, csrf_token(), csrf_hash());
                        }
                    }
                } else {
                    return redirect()->to('partner/services');
                }
            } else {
                return redirect('partner/login');
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/Services.php - add_service()');
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
        $service_model = new Service_model();
        $where['s.user_id'] = $_SESSION['user_id'];
        $services =  $service_model->list(false, $search, $limit, $offset, $sort, $order, $where);
        return $services;
    }
    public function update_service()
    {
        try {
            if ($this->isLoggedIn) {
                if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                    $response['error'] = true;
                    $response['message'] = DEMO_MODE_ERROR;
                    $response['csrfName'] = csrf_token();
                    $response['csrfHash'] = csrf_hash();
                    return $this->response->setJSON($response);
                }
                $price = $this->request->getPost('price');
                $this->validation->setRules(
                    [
                        'title' => [
                            "rules" => 'required',
                            "errors" => [
                                "required" => "Please enter service title"
                            ]
                        ],
                        'categories' => [
                            "rules" => 'required',
                            "errors" => [
                                "required" => "Please select category"
                            ]
                        ],
                        'tags' => [
                            "rules" => 'required',
                            "errors" => [
                                "required" => "Please enter service tag"
                            ]
                        ],
                        'price' => [
                            "rules" => 'required|numeric',
                            "errors" => [
                                "required" => "Please enter price",
                                "numeric" => "Please enter numeric value for price"
                            ]
                        ],
                        'discounted_price' => [
                            "rules" => 'required|numeric|less_than[' . $price . ']',
                            "errors" => [
                                "required" => "Please enter discounted price",
                                "numeric" => "Please enter numeric value for discounted price",
                                "less_than" => "Discounted price should be less than price"
                            ]
                        ],
                        'members' => [
                            "rules" => 'required|numeric',
                            "errors" => [
                                "required" => "Please enter required member for service",
                                "numeric" => "Please enter numeric value for required member"
                            ]
                        ],
                        'duration' => [
                            "rules" => 'required|numeric',
                            "errors" => [
                                "required" => "Please enter duration to perform task",
                                "numeric" => "Please enter numeric value for duration of task"
                            ]
                        ],
                        'max_qty' => [
                            "rules" => 'required|numeric',
                            "errors" => [
                                "required" => "Please enter max quantity allowed for services",
                                "numeric" => "Please enter numeric value for max quantity allowed for services"
                            ]
                        ],
                    'service_slug' => ["rules" => 'required', "errors" => ["required" => "Please enter service slug",]],

                    ],
                );
                $disk =fetch_current_file_manager();

                if (!$this->validation->withRequest($this->request)->run()) {
                    $errors  = $this->validation->getErrors();
                    return ErrorResponse($errors, true, [], [], 200, csrf_token(), csrf_hash());
                } else {
                    $base_tags = $this->request->getPost('tags');;
                    $s_t = $base_tags;
                    $val = explode(',', str_replace(']', '', str_replace('[', '', $s_t[0])));
                    $tags = [];
                    foreach ($val as $s) {
                        $tags[] = json_decode($s, true)['value'];
                    }
                    $id = $this->request->getPost('service_id');
                    $old_images_and_documents=fetch_details('services',['id'=>$id],['image','other_images','files']);
                  
                    $paths = [
                        'image' => [
                            'file' => $this->request->getFile('service_image_selector_edit'),
                            'path' => 'public/uploads/services/',
                            'error' => 'Failed to create services folders',
                            'folder' => 'services',
                            'old_file' => $old_images_and_documents[0]['image'],
                            'disk' => $disk,
                        ],
                    ];
                    $uploadedFiles = [];
                    foreach ($paths as $key => $upload) {
                        if ($upload['file'] && $upload['file']->isValid()) {
                            if (!empty($upload['old_file'])) {
                                delete_file_based_on_server($upload['folder'], $upload['old_file'], $upload['disk']);
                            }
                            $result = upload_file($upload['file'], $upload['path'], $upload['error'], $upload['folder']);
                            if ($result['error'] === false) {
                                $uploadedFiles[$key] = [
                                    'url' => $result['file_name'],
                                    'disk' => $result['disk']
                                ];
                            } else {
                                return ErrorResponse($result['message'], true, [], [], 200, csrf_token(), csrf_hash());
                            }
                        }
                    }
                    $image_name = isset($uploadedFiles['image']['url']) ? $uploadedFiles['image']['url'] : $old_images_and_documents[0]['image'];
                    // Handle Other Images Upload
                    $multipleFiles = $this->request->getFiles('filepond');
                    $uploadedOtherImages = [];
                    if (isset($multipleFiles['other_service_image_selector_edit'])) {
                        foreach ($multipleFiles['other_service_image_selector_edit'] as $file) {
                            if ($file->isValid()) {
                                // Handle Other Images Deletion
                                if (!empty($old_images_and_documents[0]['other_images'])) {
                                    $old_other_images = json_decode($old_images_and_documents[0]['other_images'], true);
                                    if (!empty($old_other_images)) {
                                        foreach ($old_other_images as $old_image) {
                                            delete_file_based_on_server('services', $old_image, $disk);
                                        }
                                    }
                                }
                                $result = upload_file($file, 'public/uploads/services/', 'Failed to upload other images', 'services');
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
                    // Handle Files Upload
                    $uploadedFilesDocuments = [];
                    if (isset($multipleFiles['files_edit'])) {
                        foreach ($multipleFiles['files_edit'] as $file) {
                            if ($file->isValid()) {
                                // Handle Files Deletion
                                if (!empty($old_images_and_documents[0]['files'])) {
                                    $old_files = json_decode($old_images_and_documents[0]['files'], true);
                                    if (!empty($old_files)) {
                                        foreach ($old_files as $old_file) {
                                            delete_file_based_on_server('services', $old_file, $disk);
                                        }
                                    }
                                }
                                $result = upload_file($file, 'public/uploads/services/', 'Failed to upload files', 'services');
                                if ($result['error'] == false) {
                                    $uploadedFilesDocuments[] = $result['disk'] === "local_server"
                                        ? 'public/uploads/services/' . $result['file_name']
                                        : $result['file_name'];
                                } else {
                                    return ErrorResponse($result['message'], true, [], [], 200, csrf_token(), csrf_hash());
                                }
                            }
                        }
                    }
                    $other_images = !empty($uploadedOtherImages) ? json_encode($uploadedOtherImages) : $old_images_and_documents[0]['other_images'];
                    $files = !empty($uploadedFilesDocuments) ? json_encode($uploadedFilesDocuments) : $old_images_and_documents[0]['files'];
                    $category = $this->request->getPost('categories');
                    if ($category == "select_category" || $category == "Select Category") {
                        return ErrorResponse("Please select anything other than Select Category", true, [], [], 200, csrf_token(), csrf_hash());
                    }
                    $discounted_price = $this->request->getPost('discounted_price');
                    $price = $this->request->getPost('price');
                    if ($discounted_price >= $price && $discounted_price == $price) {
                        return ErrorResponse("discounted price can not be higher than or equal to the price", true, [], [], 200, csrf_token(), csrf_hash());
                    }
                    $user_id = $this->ionAuth->user()->row()->id;
                    if (isset($_POST['is_cancelable']) && $_POST['is_cancelable'] == 'on') {
                        $is_cancelable = "1";
                    } else {
                        $is_cancelable = "0";
                    }
                    if ($is_cancelable == "1" && $this->request->getVar('cancelable_till') == "") {
                        return ErrorResponse("Please Add Minutes", true, [], [], 200, csrf_token(), csrf_hash());
                    }
                    $tax_data = fetch_details('taxes', ['id' => $this->request->getVar('edit_tax_id')], ['id', 'title', 'percentage']);
                    $faqs = $this->request->getPost('faqs');
                if (!empty($faqs)) {
                    $faqData = ['faqs' => !empty($faqs) ? json_encode($faqs) : ""];
                } else {
                    $faqData = ['faqs' => json_encode([])];
                }


                $partner_details = fetch_details('partner_details', ['partner_id' => $user_id]);
                if ($partner_details[0]['need_approval_for_the_service'] == 1) {
                    $approved_by_admin = 0;
                } else {
                    $approved_by_admin = 1;
                }
                    $data['category_id'] = $category;
                    $data['tax_id'] = $this->request->getVar('tax_id');
                    $data['tax'] = $this->request->getPost('tax');
                    $data['tax_type'] = $this->request->getVar('tax_type');
                    $data['title'] = $this->request->getPost('title');
                    $data['slug'] = generate_unique_slug($this->request->getPost('service_slug'), 'services', $id);
                    $data['description'] = $this->request->getPost('description');
                    $data['tags'] =  implode(',', $tags);
                    $data['price'] = $this->request->getPost('price');
                    $data['discounted_price'] = $this->request->getPost('discounted_price');
                    $data['image'] = $image_name;
                    $data['number_of_members_required'] = $this->request->getPost('members');
                    $data['duration'] = $this->request->getPost('duration');
                    $data['rating'] = 0;
                    $data['number_of_ratings'] = 0;
                    $data['max_quantity_allowed'] = $this->request->getPost('max_qty');
                    $data['is_pay_later_allowed'] = ($this->request->getPost('pay_later') == "on") ? 1 : 0;
                    $data['status'] =  ($this->request->getPost('status') == "on") ? 1 : 0;
                    $data['is_cancelable'] = $is_cancelable;
                    $data['cancelable_till'] = ($is_cancelable == "1") ? $this->request->getVar('cancelable_till') : '';
                    $data['long_description'] = (isset($_POST['long_description'])) ? $_POST['long_description'] : "";
                    $data['files'] = isset($files) ? $files : "";
                    $data['faqs'] = isset($faqData) ? $faqData : "";
                    $data['at_store'] = ($this->request->getPost('at_store') == "on") ? 1 : 0;
                    $data['at_doorstep'] = ($this->request->getPost('at_doorstep') == "on") ? 1 : 0;
                    $data['other_images'] = $other_images;
                    $data['files'] = $files;
                    $data['approved_by_admin']=$approved_by_admin;
                    if ($this->db->table('services')->update($data, ['id' => $id])) {
                        return successResponse("Service has been added", false, [], [], 200, csrf_token(), csrf_hash());
                    }
                }
            } else {
                return redirect('partner/login');
            }
        } catch (\Throwable $th) {
            throw $th;
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/Services.php - update_service()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function delete()
    {
        $disk =fetch_current_file_manager();

        try {
            if ($this->isLoggedIn) {
                if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                    $response['error'] = true;
                    $response['message'] = DEMO_MODE_ERROR;
                    $response['csrfName'] = csrf_token();
                    $response['csrfHash'] = csrf_hash();
                    return $this->response->setJSON($response);
                }
                $id = $this->request->getPost('id');
                $db      = \Config\Database::connect();
                $old_data = fetch_details('services', ['id' => $id]);
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
                $builder = $db->table('services')->delete(['id' => $id]);
                $builder2 = $this->db->table('cart')->delete(['service_id' => $id]);
                if ($builder) {
                    return successResponse("service deleted successfully", false, [], [], 200, csrf_token(), csrf_hash());
                } else {
                    return ErrorResponse("service can not be deleted!", true, [], [], 200, csrf_token(), csrf_hash());
                }
            } else {
                return redirect('partner/login');
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/Services.php - delete()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function edit_service()
    {
        $disk =fetch_current_file_manager();

        try {
            helper('function');
            $uri = service('uri');
            if ($this->isLoggedIn) {
                if (!exists(['partner_id' => $this->userId, 'is_approved' => 1], 'partner_details')) {
                    return redirect('partner/profile');
                }
                $service_id = $uri->getSegments()[3];
                setPageInfo($this->data, 'Edit Services | Provider Panel', FORMS . 'edit_service');
                $this->data['categories'] = fetch_details('categories', []);
                $this->data['tax'] = get_settings('system_tax_settings', true);
                $tax_details = fetch_details('taxes', ['status' => 1]);
                $this->data['tax_details'] = $tax_details;
                $tax_data = fetch_details('taxes', ['status' => '1'], ['id', 'title', 'percentage']);
                $service = fetch_details('services', ['id' => $service_id]);
                if ($disk == 'local_server') {
                    $localPath = base_url('/public/uploads/services/' . $service[0]['image']);
                    if (check_exists($localPath)) {
                        $service[0]['image'] = $localPath;
                    } else {
                        $service[0]['image'] = '';
                    }
                } else if ($disk == "aws_s3") {
                    $service[0]['image'] = fetch_cloud_front_url('services', $service[0]['image']);
                } else {
                    $service[0]['image'] = $service[0]['image'];
                }
                if (!empty($service[0]['other_images'])) {
                    $decodedOtherImages = json_decode($service[0]['other_images'], true);
                    if (is_array($decodedOtherImages)) {
                        $service[0]['other_images'] = array_map(function ($data) use ($service,$disk) {
                            if ($disk === "local_server") {
                                return base_url($data);
                            } elseif ($disk === "aws_s3") {
                                return fetch_cloud_front_url('services', $data);
                            }
                            return null;
                        }, $decodedOtherImages);
                    } else {
                        $service[0]['other_images'] = [];
                    }
                } else {
                    $service[0]['other_images'] = [];
                }
                if (!empty($service[0]['files'])) {
                    $decodedFiles = json_decode($service[0]['files'], true);
                    if (is_array($decodedFiles)) {
                        $service[0]['files'] = array_map(function ($data) use ($service,$disk) {
                            if ($disk === "local_server") {
                                return base_url($data);
                            } elseif ($disk === "aws_s3") {
                                return fetch_cloud_front_url('services', $data);
                            }
                            return null;
                        }, $decodedFiles);
                    } else {
                        $service[0]['files'] = [];
                    }
                } else {
                    $service[0]['files'] = [];
                }
                $this->data['service'] = $service[0];
                // $this->data['service'] = fetch_details('services', ['id' => $service_id])[0];
                $this->data['tax_data'] = $tax_data;
                return view('backend/partner/template', $this->data);
            } else {
                return redirect('partner/login');
            }
        } catch (\Throwable $th) {
        
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/Services.php - edit_service()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function duplicate()
    {
        try {
            helper('function');
            $uri = service('uri');
            if ($this->isLoggedIn) {
                if (!exists(['partner_id' => $this->userId, 'is_approved' => 1], 'partner_details')) {
                    return redirect('partner/profile');
                }
                $service_id = $uri->getSegments()[3];
                setPageInfo($this->data, 'Duplicate Services | Provider Panel', FORMS . 'duplicate_service');
                $this->data['categories'] = fetch_details('categories', []);
                $this->data['tax'] = get_settings('system_tax_settings', true);
                $tax_details = fetch_details('taxes', ['status' => 1]);
                $this->data['tax_details'] = $tax_details;
                $tax_data = fetch_details('taxes', ['status' => '1'], ['id', 'title', 'percentage']);
                $this->data['service'] = fetch_details('services', ['id' => $service_id])[0];
                $this->data['tax_data'] = $tax_data;
                return view('backend/partner/template', $this->data);
            } else {
                return redirect('partner/login');
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/Services.php - duplicate()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    //=======================================================
    public function bulk_import_services()
    {
        if ($this->isLoggedIn) {
            setPageInfo($this->data, 'Services | Provider Panel', 'bulk_import_services');
            return view('backend/partner/template', $this->data);
        } else {
            return redirect('partner/login');
        }
    }
    public function bulk_import_service_upload()
    {
        $file = $this->request->getFile('file');
        $filePath = FCPATH . 'public/uploads/service_bulk_upload/';
        if (!is_dir($filePath)) {
            if (!mkdir($filePath, 0775, true)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Failed to create folders'
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
        $faqHeaders = [];
        $other_image_Headers = [];
        $FilesHeaders = [];
        $columnIndex = 0;
        $OtherImagecolumnIndex = 0;
        $FilescolumnIndex = 0;
        $headerRow = $sheet->rangeToArray('A1:' . $sheet->getHighestColumn() . '1', NULL, TRUE, TRUE, TRUE);
        $headers = explode(',', $headerRow[1]['A']);
        $headers = array_map(function ($header) {
            return trim($header, ' "');
        }, $headers);
        if (!in_array('ID', $headers)) {
            //insert
            //faq
            foreach ($cellIterator as $cell) {
                $header = $cell->getValue();
                if (preg_match('/^faq\[question\]\[(\d+)\]$/', $header, $matches)) {
                    $faqNumber = $matches[1];
                    $faqHeaders[$faqNumber]['question'] = $columnIndex;
                } elseif (preg_match('/^faq\[answer\]\[(\d+)\]$/', $header, $matches)) {
                    $faqNumber = $matches[1];
                    $faqHeaders[$faqNumber]['answer'] = $columnIndex;
                }
                $columnIndex++;
            }
            //other_image
            foreach ($cellIterator as $cell) {
                $header = $cell->getValue();
                if (preg_match('/^Other Image\[(\d+)\]$/', $header, $matches)) {
                    $other_image_number = $matches[1];
                    $other_image_Headers[$other_image_number] = $OtherImagecolumnIndex;
                } elseif (preg_match('/^Other Image\[(\d+)\]$/', $header, $matches)) {
                    $other_image_number = $matches[1];
                    $other_image_Headers[$other_image_number] = $OtherImagecolumnIndex;
                }
                $OtherImagecolumnIndex++;
            }
            //file
            foreach ($cellIterator as $cell) {
                $header = $cell->getValue();
                if (preg_match('/^Files\[(\d+)\]$/', $header, $matches)) {
                    $fileNumber = $matches[1];
                    $FilesHeaders[$fileNumber] = $FilescolumnIndex;
                } elseif (preg_match('/^Files\[(\d+)\]$/', $header, $matches)) {
                    $fileNumber = $matches[1];
                    $FilesHeaders[$fileNumber] = $FilescolumnIndex;
                }
                $FilescolumnIndex++;
            }
            $data = $sheet->toArray();
            array_shift($data);
            $data = array_filter($data, function ($row) {
                return !empty(array_filter($row));
            });
            $services = [];
            foreach ($data as $rowIndex => $row) {
                $provider = fetch_details('partner_details', ['partner_id' => $row[0]]);
                if (empty($provider)) {
                    return ErrorResponse("Provider ID :: " . $row[0] . " not found", true, [], [], 200, csrf_token(), csrf_hash());
                } else if ($row[0] != $this->userId) {
                    return ErrorResponse("Provider ID must be logged in user id", true, [], [], 200, csrf_token(), csrf_hash());
                }
                $category = fetch_details('categories', ['id' => $row[1]]);
                if (empty($category)) {
                    return ErrorResponse("Category ID :: " . $row[1] . " not found", true, [], [], 200, csrf_token(), csrf_hash());
                }
                $tax = fetch_details('taxes', ['id' => $row[10]]);
                if (empty($tax)) {
                    return ErrorResponse("Tax ID :: " . $row[10] . " not found", true, [], [], 200, csrf_token(), csrf_hash());
                }
                $faqs = [];
                foreach ($faqHeaders as $faqNumber => $indexes) {
                    $question = isset($row[$indexes['question']]) ? trim($row[$indexes['question']]) : '';
                    $answer = isset($row[$indexes['answer']]) ? trim($row[$indexes['answer']]) : '';
                    if (!empty($question) || !empty($answer)) {
                        $faqs[] = [$question, $answer];
                    }
                }
                $other_images = [];
                foreach ($other_image_Headers as $indexes) {
                    $other_image = isset($row[$indexes]) ? trim($row[$indexes]) : '';
                    if (!empty($other_image)) {
                        copy_image($row[$indexes], '/public/uploads/services/');
                        if (!empty($other_image)) {
                            $other_images[] = $other_image;
                        }
                    }
                }
                $files = [];
                foreach ($FilesHeaders as $indexes) {
                    $file = isset($row[$indexes]) ? trim($row[$indexes]) : '';
                    if (!empty($file)) {
                        copy_image($row[$indexes], '/public/uploads/services/');
                        if (!empty($file)) {
                            $files[] = $file;
                        }
                    }
                }
                $image = !empty($row[21]) ? copy_image($row[21], '/public/uploads/services/') : "";
                $services[] = [
                    'user_id' => $row[0],
                    'category_id' => $row[1],
                    'title' => $row[2],
                    'tags' => $row[3],
                    'description' => $row[4],
                    'duration' => $row[5],
                    'number_of_members_required' => $row[6],
                    'max_quantity_allowed' => $row[7],
                    'long_description' => $row[8],
                    'tax_type' => $row[9],
                    'tax_id' => $row[10],
                    'price' => $row[11],
                    'discounted_price' => $row[12],
                    'is_cancelable' => $row[13],
                    'cancelable_till' => ($row[13] == 1) ? $row[14] : "",
                    'is_pay_later_allowed' => $row[15],
                    'at_store' => $row[16],
                    'at_doorstep' => $row[17],
                    'status' => $row[18],
                    'approved_by_admin' => ($provider[0]['need_approval_for_the_service'] == "1") ? "0" : "1",
                    'faqs' => json_encode($faqs),
                    'other_images' => json_encode($other_images),
                    'image' => $image,
                    'files' => json_encode($files),
                ];
            }
            $serviceModel = new Service_model();
            foreach ($services as  $service) {
                if (!$serviceModel->insert($service)) {
                    return ErrorResponse("Failed to add service", true, [], [], 200, csrf_token(), csrf_hash());
                }
            }
            return successResponse("Services added successfully", false, [], [], 200, csrf_token(), csrf_hash());
        } else {
            //update
            foreach ($cellIterator as $cell) {
                $header = $cell->getValue();
                if (preg_match('/^faq\[question\]\[(\d+)\]$/', $header, $matches)) {
                    $faqNumber = $matches[1];
                    $faqHeaders[$faqNumber]['question'] = $columnIndex;
                } elseif (preg_match('/^faq\[answer\]\[(\d+)\]$/', $header, $matches)) {
                    $faqNumber = $matches[1];
                    $faqHeaders[$faqNumber]['answer'] = $columnIndex;
                }
                $columnIndex++;
            }
            //other_image
            foreach ($cellIterator as $cell) {
                $header = $cell->getValue();
                if (preg_match('/^Other Image\[(\d+)\]$/', $header, $matches)) {
                    $other_image_number = $matches[1];
                    $other_image_Headers[$other_image_number] = $OtherImagecolumnIndex;
                } elseif (preg_match('/^Other Image\[(\d+)\]$/', $header, $matches)) {
                    $other_image_number = $matches[1];
                    $other_image_Headers[$other_image_number] = $OtherImagecolumnIndex;
                }
                $OtherImagecolumnIndex++;
            }
            //files
            foreach ($cellIterator as $cell) {
                $header = $cell->getValue();
                if (preg_match('/^Files\[(\d+)\]$/', $header, $matches)) {
                    $fileNumber = $matches[1];
                    $FilesHeaders[$fileNumber] = $FilescolumnIndex;
                } elseif (preg_match('/^Files\[(\d+)\]$/', $header, $matches)) {
                    $fileNumber = $matches[1];
                    $FilesHeaders[$fileNumber] = $FilescolumnIndex;
                }
                $FilescolumnIndex++;
            }
            $data = $sheet->toArray();
            array_shift($data);
            $data = array_filter($data, function ($row) {
                return !empty(array_filter($row));
            });
            $services = [];
            foreach ($data as $rowIndex => $row) {
                $fetch_service_data = fetch_details('services', ['id' => $row[0]], ['image', 'other_images', 'files']);
                $old_other_images = json_decode($fetch_service_data[0]['other_images']);
                $old_files = json_decode($fetch_service_data[0]['files']);
                $provider = fetch_details('partner_details', ['partner_id' => $row[1]]);
                if (empty($provider)) {
                    return ErrorResponse("Provider ID :: " . $row[1] . " not found", true, [], [], 200, csrf_token(), csrf_hash());
                } else if ($row[1] != $this->userId) {
                    return ErrorResponse("The provider ID must match the logged-in user ID.", true, [], [], 200, csrf_token(), csrf_hash());
                }
                $category = fetch_details('categories', ['id' => $row[2]]);
                if (empty($category)) {
                    return ErrorResponse("Category ID :: " . $row[2] . " not found", true, [], [], 200, csrf_token(), csrf_hash());
                }
                $tax = fetch_details('taxes', ['id' => $row[11]]);
                if (empty($tax)) {
                    return ErrorResponse("Tax ID :: " . $row[11] . " not found", true, [], [], 200, csrf_token(), csrf_hash());
                }
                $faqs = [];
                foreach ($faqHeaders as $faqNumber => $indexes) {
                    $question = isset($row[$indexes['question']]) ? trim($row[$indexes['question']]) : '';
                    $answer = isset($row[$indexes['answer']]) ? trim($row[$indexes['answer']]) : '';
                    if (!empty($question) || !empty($answer)) {
                        $faqs[] = [$question, $answer];
                    }
                }
                $other_images = [];
                foreach ($other_image_Headers as $indexes) {
                    $other_image = isset($row[$indexes]) ? trim($row[$indexes]) : '';
                    if (!empty($other_image) && !in_array($other_image, $old_other_images)) {
                        $oi = copy_image($row[$indexes], '/public/uploads/services/');
                        if (!empty($other_image)) {
                            $other_images[] = $oi;
                        }
                    } else if (!empty($old_other_images)) {
                        $other_images = $old_other_images;
                    } else {
                        $other_images = [];
                    }
                }
                $files = [];
                foreach ($FilesHeaders as $indexes) {
                    $file = isset($row[$indexes]) ? trim($row[$indexes]) : '';
                    if (!empty($file) && !in_array($file, $old_files)) {
                        $oi = copy_image($row[$indexes], '/public/uploads/services/');
                        if (!empty($file)) {
                            $files[] = $oi;
                        }
                    } else if (!empty($old_files)) {
                        $files = $old_files;
                    } else {
                        $files = [];
                    }
                }
                $image = !empty($row[21]) ? copy_image($row[21], '/public/uploads/services/') : "";
                $services[] = [
                    'id' => $row[0],
                    'user_id' => $row[1],
                    'category_id' => $row[2],
                    'title' => $row[3],
                    'tags' => $row[4],
                    'description' => $row[5],
                    'duration' => $row[6],
                    'number_of_members_required' => $row[7],
                    'max_quantity_allowed' => $row[8],
                    'long_description' => $row[9],
                    'tax_type' => $row[10],
                    'tax_id' => $row[11],
                    'price' => $row[12],
                    'discounted_price' => $row[13],
                    'is_cancelable' => $row[14],
                    'cancelable_till' => ($row[14 == 1]) ? $row[15] : "",
                    'is_pay_later_allowed' => $row[16],
                    'at_store' => $row[17],
                    'at_doorstep' => $row[18],
                    'status' => $row[19],
                    'image' => $image,
                    'approved_by_admin' => ($provider[0]['need_approval_for_the_service'] == "1") ? "0" : "1",
                    'faqs' => json_encode($faqs),
                    'other_images' => json_encode($other_images),
                    'files' => json_encode($files),
                ];
            }
            $serviceModel = new Service_model();
            foreach ($services as $service) {
                $id = $service['id'];
                unset($service['id']);
                if (!$serviceModel->update($id, $service)) {
                    return ErrorResponse("Failed to update service", true, [], [], 200, csrf_token(), csrf_hash());
                }
            }
            return successResponse("Services updated successfully", false, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function downloadSampleForInsert()
    {
        try {
            $headers = [
                'Provider ID',
                'Category ID',
                'Title',
                'Tags',
                'Short Description',
                'Duration to perform task',
                'Members Required to Perform Task',
                'Max Quantity allowed for services',
                'Description',
                'Price Type',
                'Tax ID',
                'Price',
                'Discounted Price',
                'Is Cancelable',
                'Cancelable before',
                'Pay Later Allowed',
                'At Store',
                'At Doorstep',
                'Status',
                'Approve Service',
                'Image',
                'faq[question][1]',
                'faq[answer][1]',
                'faq[question][2]',
                'faq[answer][2]',
                'Other Image[1]',
                'Other Image[2]',
                'Files[1]',
                'Files[2]',
            ];
            $sampleData = [
                [
                    '1',
                    '1',
                    'Sample Service',
                    'Tag1,Tag2',
                    'Sample Service Description',
                    '60',
                    '1',
                    '2',
                    'Sample Description',
                    'included',
                    '1',
                    '300',
                    '250',
                    '1',
                    '30',
                    '1',
                    '1',
                    '1',
                    '1',
                    '1',
                    'public/upload/service/test1.png',
                    'Sample Question 1',
                    'Sample Answer 1',
                    'Sample Question 2',
                    'Sample Answer 2',
                    'public/upload/service/test1.png',
                    'public/upload/service/test2.png',
                    'public/upload/service/test1.pdf',
                    'public/upload/service/test2.pdf',
                ],
            ];
            $output = fopen('php://output', 'w');
            if ($output === false) {
                throw new \Exception('Failed to open output stream.');
            }
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="service_sample_without_data.csv"');
            fputcsv($output, $headers);
            foreach ($sampleData as $row) {
                fputcsv($output, $row);
            }
            fclose($output);
            exit;
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Services.php - download-sample-for-insert()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function downloadSampleForUpdate()
    {
        try {
            $headers = [
                'ID',
                'Provider ID',
                'Category ID',
                'Title',
                'Tags',
                'Short Description',
                'Duration to perform task',
                'Members Required to Perform Task',
                'Max Quantity allowed for services',
                'Description',
                'Price Type',
                'Tax ID',
                'Price',
                'Discounted Price',
                'Is Cancelable',
                'Cancelable before',
                'Pay Later Allowed',
                'At Store',
                'At Doorstep',
                'Status',
                'Approve Service',
                'Image'
            ];
            $services = fetch_details('services', ['user_id' => $this->userId]);
            $all_data = [];
            $max_faqs = 0;
            $max_other_images = 0;
            $max_files = 0;
            foreach ($services as $row) {
                $faqs = json_decode($row['faqs'], true);
                if (is_array($faqs)) {
                    $max_faqs = max($max_faqs, count($faqs));
                }
            }
            for ($i = 1; $i <= $max_faqs; $i++) {
                $headers[] = "faq[question][$i]";
                $headers[] = "faq[answer][$i]";
            }
            foreach ($services as $row) {
                $other_images1 = json_decode($row['other_images'], true);
                if (is_array($other_images1)) {
                    $max_other_images = max($max_other_images, count($other_images1));
                }
            }
            for ($i = 1; $i <= $max_other_images; $i++) {
                $headers[] = "Other Image[$i]";
            }
            foreach ($services as $row) {
                $files1 = json_decode($row['files'], true);
                if (is_array($files1)) {
                    $max_files = max($max_files, count($files1));
                }
            }
            for ($i = 1; $i <= $max_files; $i++) {
                $headers[] = "Files[$i]";
            }
            foreach ($services as $row) {
                $faqs = json_decode($row['faqs'], true);
                $other_images = json_decode($row['other_images'], true);
                $files = json_decode($row['files'], true);
                $rowData = [
                    'ID' => $row['id'],
                    'Provider ID' => $row['user_id'],
                    'Category ID' => $row['category_id'],
                    'Title' => $row['title'],
                    'Tags' => $row['tags'],
                    'Short Description' => $row['description'],
                    'Duration to perform task' => $row['duration'],
                    'Members Required to Perform Task' => $row['number_of_members_required'],
                    'Max Quantity allowed for services' => $row['max_quantity_allowed'],
                    'Description' => $row['long_description'],
                    'Price Type' => $row['tax_type'],
                    'Tax ID' => $row['tax_id'],
                    'Price' => $row['price'],
                    'Discounted Price' => $row['discounted_price'],
                    'Is Cancelable' => $row['is_cancelable'],
                    'Cancelable before' => $row['cancelable_till'],
                    'Pay Later Allowed' => $row['is_pay_later_allowed'],
                    'At Store' => $row['at_store'],
                    'At Doorstep' => $row['at_doorstep'],
                    'Status' => $row['status'],
                    'Approve Service' => $row['approved_by_admin'],
                    'Image' => $row['image'],
                ];
                if (is_array($faqs)) {
                    foreach ($faqs as $index => $faq) {
                        $rowData["faq[question][" . ($index + 1) . "]"] = isset($faq[0]) ? $faq[0] : '';
                        $rowData["faq[answer][" . ($index + 1) . "]"] = isset($faq[1]) ? $faq[1] : '';
                    }
                }
                for ($i = count($faqs ?? []); $i < $max_faqs; $i++) {
                    $rowData["faq[question][" . ($i + 1) . "]"] = '';
                    $rowData["faq[answer][" . ($i + 1) . "]"] = '';
                }
                if (is_array($other_images)) {
                    foreach ($other_images as $index => $other_image) {
                        $rowData["Other Image[" . ($index + 1) . "]"] = isset($other_image) ? $other_image : '';
                    }
                }
                for ($i = count($other_images ?? []); $i < $max_other_images; $i++) {
                    $rowData["Other Image[" . ($i + 1) . "]"] = '';
                }
                if (is_array($files)) {
                    foreach ($files as $index => $file) {
                        $rowData["Files[" . ($index + 1) . "]"] = isset($file) ? $file : '';
                    }
                }
                for ($i = count($files ?? []); $i < $max_files; $i++) {
                    $rowData["Files[" . ($i + 1) . "]"] = '';
                }
                $all_data[] = $rowData;
            }
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="service_sample_with_data.csv"');
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
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Services.php - download-sample-for-insert()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function ServiceAddInstructions()
    {
        try {
            $filePath = (FCPATH . '/public/uploads/site/Service-Add-Instructions.pdf');
            $fileName = 'Service-Add-Instructions.pdf';
            if (file_exists($filePath)) {
                return $this->response->download($filePath, null)->setFileName($fileName);
            } else {
                $_SESSION['toastMessage'] = "Cannot download";
                $_SESSION['toastMessageType'] = 'error';
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/services')->withCookies();
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Services.php - download_service_add_instruction_file()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function ServiceUpdateInstructions()
    {
        try {
            $filePath = (FCPATH . '/public/uploads/site/Service-Update-Instructions.pdf');
            $fileName = 'Service-Update-Instructions.pdf';
            if (file_exists($filePath)) {
                return $this->response->download($filePath, null)->setFileName($fileName);
            } else {
                $_SESSION['toastMessage'] = "Cannot download";
                $_SESSION['toastMessageType'] = 'error';
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/services')->withCookies();
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Services.php - download_service_add_instruction_file()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
}
