<?php

namespace App\Controllers\admin;

use App\Models\Slider_model;

class Sliders extends Admin
{
    public $sliders, $creator_id;
    protected $superadmin;
    protected $db;
    protected $validation;

    public function __construct()
    {
        parent::__construct();
        $this->sliders = new Slider_model();
        $this->creator_id = $this->userId;
        $this->db = \Config\Database::connect();
        $this->validation = \Config\Services::validation();
        $this->superadmin = $this->session->get('email');
        helper('ResponceServices');
    }
    public function index()
    {
        if (!$this->isLoggedIn || !$this->userIsAdmin) {
            return redirect('admin/login');
        }
        setPageInfo($this->data, labels('Sliders', 'Sliders') . '  | ' . labels('admin_panel', 'Admin Panel'), 'sliders');
        $this->data['categories_name'] = fetch_details('categories', [], ['id', 'name']);
        $this->data['provider_title'] = fetch_details('partner_details', [], ['id', 'partner_id', 'company_name']);
        $this->data['services_title'] = $this->db->table('services s')
            ->select('s.id,s.title')
            ->join('users u', 's.user_id = u.id')
            ->where('status', '1')
            ->get()->getResultArray();
        return view('backend/admin/template', $this->data);
    }
    public function add_slider()
    {
        try {
            if (!checkModificationInDemoMode($this->superadmin)) {
                return $this->response->setJSON(checkModificationInDemoMode($this->superadmin));
            }
            if (!is_permitted($this->creator_id, 'create', 'sliders')) {
                return NoPermission();
            }
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            $type = $this->request->getPost('type');
            $common_rules = [
                'app_image' => ["rules" => 'uploaded[app_image]', "errors" => ["uploaded" => "The app_image field is required",]],
                'web_image' => ["rules" => 'uploaded[web_image]', "errors" => ["uploaded" => "The web_image field is required",]]
            ];
            if ($type == "Category" || $type == "provider" || $type == "url" || $type == "typeurl") {
                $specific_rule = '';
                $specific_error = '';
                $string = "";
                if ($type == "Category") {
                    $specific_rule = 'Category_item';
                    $specific_error = 'category';
                    $string = 'select';
                } elseif ($type == "provider") {
                    $specific_rule = 'service_item';
                    $specific_error = 'provider';
                    $string = 'select';
                } elseif ($type == "url") {
                    $specific_rule = 'url';
                    $specific_error = 'url';
                    $string = 'add';
                }
                $specific_rules = [
                    $specific_rule => ["rules" => 'required', "errors" => ["required" => "Please $string $specific_error"]]
                ];
            } else {
                $specific_rules = [
                    'type' => ["rules" => 'required', "errors" => ["required" => "Please select type of slider"]]
                ];
            }
            $validation_rules = array_merge($common_rules, $specific_rules);
            $this->validation->setRules($validation_rules);
            if (!$this->validation->withRequest($this->request)->run()) {
                $errors  = $this->validation->getErrors();
                return ErrorResponse($errors, true, [], [], 200, csrf_token(), csrf_hash());
            }
            $name = $this->request->getPost('type');
            $url = "";
            if ($name == "Category") {
                $id = $this->request->getPost('Category_item');
            } else if ($name == "provider") {
                $id = $this->request->getPost('service_item');
            } else if ($name == "url") {
                $url = $this->request->getPost('url');
                $id = "000";
            } else {
                $id = "000";
            }
            $paths = [
                'app_image' => ['file' => $this->request->getFile('app_image'), 'path' => 'public/uploads/sliders/', 'error' => 'Failed to create sliders folders', 'folder' => 'sliders'],
                'web_image' => ['file' => $this->request->getFile('web_image'), 'path' => 'public/uploads/sliders/', 'error' => 'Failed to create sliders folders', 'folder' => 'sliders'],
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
            $data['type'] = $name;
            $data['type_id'] = $id;
            $data['app_image'] = $uploadedFiles['app_image']['url'] ??  $this->request->getFile('app_image')->getName();
            $data['web_image'] = $uploadedFiles['web_image']['url'] ??  $this->request->getFile('web_image')->getName();
            $data['status'] = (isset($_POST['slider_switch'])) ? 1 : 0;
            $data['url'] = $url;
            if (!is_dir(FCPATH . 'public/uploads/sliders/')) {
                if (!mkdir(FCPATH . 'public/uploads/sliders/', 0775, true)) {
                    return ErrorResponse("Failed to create folders", true, [], [], 200, csrf_token(), csrf_hash());
                }
            }
            if ($this->sliders->save($data)) {
                return successResponse("Slider added successfully", false, [], [], 200, csrf_token(), csrf_hash());
            } else {
                return ErrorResponse("Some error occurred", true, [], [], 200, csrf_token(), csrf_hash());
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Sliders.php - add_slider()');
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
        print_r($this->sliders->list(false, $search, $limit, $offset, $sort, $order));
    }
    public function update_slider()
    {
        try {
            $disk = fetch_current_file_manager();

            $result = checkModificationInDemoMode($this->superadmin);
            if ($result !== true) {
                return $this->response->setJSON($result);
            }
            $permission = is_permitted($this->creator_id, 'update', 'sliders');
            if ($permission) {
                if ($this->isLoggedIn && $this->userIsAdmin) {
                    $type = $this->request->getPost('type_1');
                    $common_rules = [];
                    if ($type == "Category" || $type == "services" || $type == "url" || $type == "typeurl") {
                        $specific_rule = '';
                        $specific_error = '';
                        $string = "";
                        if ($type == "Category") {
                            $specific_rule = 'Category_item_1';
                            $specific_error = 'category';
                            $string = 'select';
                        } elseif ($type == "provider") {
                            $specific_rule = 'service_item_1';
                            $specific_error = 'service';
                            $string = 'select';
                        } elseif ($type == "url") {
                            $specific_rule = 'url';
                            $specific_error = 'url';
                            $string = 'add';
                        }
                        $specific_rules = [
                            $specific_rule => ["rules" => 'required', "errors" => ["required" => "Please $string $specific_error"]]
                        ];
                    } else {
                        $specific_rules = [
                            'type_1' => ["rules" => 'required', "errors" => ["required" => "Please select type of slider"]]
                        ];
                    }
                    $validation_rules = array_merge($common_rules, $specific_rules);
                    $this->validation->setRules($validation_rules);
                    if (!$this->validation->withRequest($this->request)->run()) {
                        $errors  = $this->validation->getErrors();
                        return ErrorResponse($errors, true, [], [], 200, csrf_token(), csrf_hash());
                    }
                    $id = $this->request->getPost('id');
                    $name = $this->request->getPost('type_1');
                    $old_data = fetch_details('sliders', ['id' => $id]);
                    $old_app_image = $old_data[0]['app_image'];
                    $old_web_image = $old_data[0]['web_image'];
                    $url = "";
                    if ($name == "Category") {
                        $type_id = $this->request->getPost('Category_item_1');
                    } else if ($name == "provider") {
                        $type_id = $this->request->getPost('service_item_1');
                    } else if ($name == "url") {
                        $url = $this->request->getPost('url');
                        $type_id = "000";
                    } else {
                        $type_id = "000";
                    }
                    $paths = [
                        'app_image' => ['file' => $this->request->getFile('app_image'), 'path' => 'public/uploads/sliders/', 'old_image' => $old_app_image, 'error' => 'Failed to upload app image', 'folder' => 'sliders', 'disk' => $disk,],
                        'web_image' => ['file' =>  $this->request->getFile('web_image'), 'path' => 'public/uploads/sliders/', 'old_image' => $old_web_image, 'error' => 'Failed to upload web image', 'folder' => 'sliders', 'disk' => $disk,],
                    ];
                    $uploadedFiles = [];
                    foreach ($paths as $key => $upload) {
                        if ($upload['file']->getName() != "") {
                            delete_file_based_on_server('sliders', $upload['old_image'], $upload['disk']);
                            $result = upload_file($upload['file'], $upload['path'], $upload['error'], $upload['folder']);
                            if ($result['error'] === false) {
                                if ($upload['disk'] == "local_server") {
                                    $upload['old_image'] = "public/uploads/sliders/" . $upload['old_image'];
                                }
                                $uploadedFiles[$key] = [
                                    'url' => $result['file_name'],
                                    'disk' => $result['disk']
                                ];
                            } else {
                                return ErrorResponse($result['message'], true, [], [], 200, csrf_token(), csrf_hash());
                            }
                        } else {
                            $uploadedFiles[$key] = [
                                'url' => $upload['old_image'],
                                'disk' => $upload['disk']
                            ];
                        }
                    }
                    $data['type'] = $name;
                    $data['type_id'] = $type_id;
                    $data['app_image'] =  $uploadedFiles['app_image']['url'] ?? $this->request->getFile('app_image')->getName();
                    $data['web_image'] = $uploadedFiles['web_image']['url'] ?? $this->request->getFile('web_image')->getName();
                    $data['status'] = (isset($_POST['edit_slider_switch'])) ? 1 : 0;
                    $data['url'] = $url;
                    $upd =  $this->sliders->update($id, $data);
                    if ($upd) {
                        return successResponse("Slider updated successfully", false, [], [], 200, csrf_token(), csrf_hash());
                    }
                } else {
                    return redirect('admin/login');
                }
            } else {
                return NoPermission();
            }
        } catch (\Throwable $th) {
           
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Sliders.php - update_slider()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }

    public function delete_sliders()
    {
        try {
            $disk = fetch_current_file_manager();

            $result = checkModificationInDemoMode($this->superadmin);
            if ($result !== true) {
                return $this->response->setJSON($result);
            }
            if (!is_permitted($this->creator_id, 'delete', 'sliders')) {
                return NoPermission();
            }
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            $db = \Config\Database::connect();
            $id = $this->request->getPost('user_id');
            $old_data = fetch_details('sliders', ['id' => $id]);
            if (empty($old_data)) {
                return ErrorResponse("Slider not found", true, [], [], 200, csrf_token(), csrf_hash());
            }
            $app_image = "";
            $web_image = "";
            if ($disk === "local_server") {
                $app_image = "public/uploads/sliders/" . $old_data[0]['app_image'];
            } elseif ($disk === "aws_s3") {
                $app_image = $old_data[0]['app_image'];
            }
            if ($disk === "local_server") {
                $web_image = "public/uploads/sliders/" . $old_data[0]['web_image'];
            } elseif ($disk === "aws_s3") {
                $web_image = $old_data[0]['web_image'];
            }
            $builder = $db->table('sliders');
            if ($builder->delete(['id' => $id])) {
                if (!empty($app_image)) {
                    delete_file_based_on_server('sliders', $app_image, $disk);
                }
                if (!empty($web_image)) {
                    delete_file_based_on_server('sliders', $web_image, $disk);
                }
                return successResponse("Successfully deleted", false, [], [], 200, csrf_token(), csrf_hash());
            }
            return ErrorResponse("Some error occurred", true, [], [], 200, csrf_token(), csrf_hash());
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Sliders.php - delete_sliders()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
}
