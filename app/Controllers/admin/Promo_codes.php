<?php

namespace App\Controllers\admin;

use App\Models\Promo_code_model;

class Promo_codes extends Admin
{
    public $orders, $creator_id, $ionAuth;
    protected $superadmin; 
    protected Promo_code_model $promo_codes;
    protected $db;
    protected $validation; 

    public function __construct()
    {
        parent::__construct();
        $this->promo_codes = new Promo_code_model();
        $this->creator_id = $this->userId;
        $this->db = \Config\Database::connect();
        $this->ionAuth = new \IonAuth\Libraries\IonAuth();
        $this->validation = \Config\Services::validation();
        $this->creator_id = $this->userId;
        $this->superadmin = $this->session->get('email');
        helper('ResponceServices');
    }
    public function index()
    {
        if (!$this->isLoggedIn || !$this->userIsAdmin) {
            return redirect('admin/login');
        }
        setPageInfo($this->data, labels('promo_codes', 'Promo Codes') . ' | ' . labels('admin_panel', 'Admin Panel'), 'promo_codes');
        $partner_data = $this->db->table('users u')
            ->select('u.id,u.username,pd.company_name')
            ->join('partner_details pd', 'pd.partner_id = u.id')
            ->where('is_approved', '1')
            ->get()
            ->getResultArray();
        $this->data['partner_name'] = $partner_data;
        return view('backend/admin/template', $this->data);
    }
    public function list()
    {
        try {
            $promocode_model = new Promo_code_model();
            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
            $data = $promocode_model->admin_list(false, $search, $limit, $offset, $sort, $order);
            return json_encode($data);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Promo_codes.php - list()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function delete_promo_code()
    {
        try {
            $result = checkModificationInDemoMode($this->superadmin);
            if ($result !== true) {
                return $this->response->setJSON($result);
            }
            $permission = is_permitted($this->creator_id, 'delete', 'promo_code');
            if (!$permission) {
                return NoPermission();
            }
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            $id = $this->request->getPost('id');
            $db = \Config\Database::connect();
            $builder = $db->table('promo_codes');
            $disk = fetch_current_file_manager();

            $old_image = fetch_details('promo_codes', ['id' => $id], ['image']);
            if ($builder->delete(['id' => $id])) {

                delete_file_based_on_server('promocodes', $old_image[0]['image'], $disk);

                return successResponse("Promo Codes section deleted successfully", false, [], [], 200, csrf_token(), csrf_hash());
            } else {
                return ErrorResponse("An error occurred during deleting this item", true, [], [], 200, csrf_token(), csrf_hash());
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Promo_codes.php - delete_promo_code()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function add()
    {
        try {
            if (!$this->isLoggedIn && !$this->userIsPartner) {
                return redirect('admin/login');
            } else {
                setPageInfo($this->data, labels('promo_codes', 'Promo Codes') . ' | ' . labels('admin_panel', 'Admin Panel'), 'add_promocode');
                $partner_data = $this->db->table('users u')
                    ->select('u.id,u.username,pd.company_name')
                    ->join('partner_details pd', 'pd.partner_id = u.id')
                    ->where('is_approved', '1')
                    ->get()->getResultArray();
                $this->data['partner_name'] = $partner_data;
                return view('backend/admin/template', $this->data);
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Promo_codes.php - add()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function save()
    {
        try {
            if (!$this->isLoggedIn && !$this->userIsPartner) {
                return redirect('unauthorised');
            } else {
                $result = checkModificationInDemoMode($this->superadmin);
                if ($result !== true) {
                    return $this->response->setJSON($result);
                }
                if (isset($_POST) && !empty($_POST)) {
                    $repeat_usage = isset($_POST['repeat_usage']) ? $_POST['repeat_usage'] : '';
                    $id = isset($_POST['promo_id']) ? $_POST['promo_id'] : '';
                    $validationRules = [
                        'promo_code' => ["rules" => 'required', "errors" => ["required" => "Please enter promo code name"]],
                        'start_date' => ["rules" => 'required', "errors" => ["required" => "Please select start date"]],
                        'end_date' => ["rules" => 'required', "errors" => ["required" => "Please select end date"]],
                        'no_of_users' => ["rules" => 'required|numeric|greater_than[0]', "errors" => ["required" => "Please enter number of users",    "numeric" => "Please enter numeric value for number of users",    "greater_than" => "number of users must be greater than 0",]],
                        'minimum_order_amount' => ["rules" => 'required|numeric|greater_than[0]', "errors" => ["required" => "Please enter minimum order amount",    "numeric" => "Please enter numeric value for minimum order amount",    "greater_than" => "minimum order amount must be greater than 0",]],
                        'discount' => ["rules" => 'required|numeric|greater_than[0]', "errors" => ["required" => "Please enter discount",    "numeric" => "Please enter numeric value for discount",    "greater_than" => "discount must be greater than 0",]],
                        'max_discount_amount' => ["rules" => 'required|numeric|greater_than[0]',  "errors" => ["required" => "Please enter max discount amount",      "numeric" => "Please enter numeric value for max discount amount",      "greater_than" => "discount amount must be greater than 0",]],
                    ];
                    if ($repeat_usage == 'on' && empty($id) && $id == '') {
                        $validationRules = array_merge($validationRules, [
                            'no_of_repeat_usage' => ["rules" => 'required|numeric|greater_than[0]', "errors" => ["required" => "Please enter number of repeat usage",    "numeric" => "Please enter numeric value for number of repeat usage",    "greater_than" => "number of repeat usage must be greater than 0",]],
                        ]);
                    }
                    if (!$this->validate($validationRules)) {
                        $errors = $this->validator->getErrors();
                        return ErrorResponse($errors, true, [], [], 200, csrf_token(), csrf_hash());
                    } else {
                        $promo_id = isset($_POST['promo_id']) ? $_POST['promo_id'] : '';
                        $paths = [
                            'image' => [
                                'file' => $this->request->getFile('image'),
                                'path' => 'public/uploads/promocodes/',
                                'error' => labels('Failed to create promocodes folders', 'Failed to create promocodes folders'),
                                'folder' => 'promocodes'
                            ],
                        ];
                        $uploadedFiles = [];
                        foreach ($paths as $key => $upload) {
                            if ($upload['file'] && $upload['file']->isValid()) {
                                $result = upload_file($upload['file'], $upload['path'], $upload['error'], $upload['folder']);
                                $image_disk = $result['disk'];
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
                        if (isset($uploadedFiles['image']['disk']) && $uploadedFiles['image']['disk'] == 'local_server') {
                            $image = 'public/uploads/promocodes/' . $uploadedFiles['image']['url'];
                        } else {
                            $image = $uploadedFiles['image']['url'];
                        }
                        $promocode_model = new Promo_code_model();
                        $repeat_usage = isset($_POST['repeat_usage']) ? "1" : "0";
                        $status = isset($_POST['status']) ? "1" : "0";
                        $users = isset($_POST['no_of_users']) ?  $this->request->getVar('no_of_users') : "1";
                        $promocode = array(
                            'id' => $promo_id,
                            'partner_id' => $this->request->getVar('partner'),
                            'promo_code' => $this->request->getVar('promo_code'),
                            'message' => $this->request->getVar('message'),
                            'start_date' => $this->request->getVar('start_date'),
                            'end_date' => $this->request->getVar('end_date'),
                            'no_of_users' => $users,
                            'minimum_order_amount' => $this->request->getVar('minimum_order_amount'),
                            'max_discount_amount' => $this->request->getVar('max_discount_amount'),
                            'discount' => $this->request->getVar('discount'),
                            'discount_type' => $this->request->getVar('discount_type'),
                            'repeat_usage' => $repeat_usage,
                            'no_of_repeat_usage' => $this->request->getVar('no_of_repeat_usage'),
                            'image' => $image,
                            'status' => $status,
                        );
                        $promocode_model->save($promocode);
                        return successResponse("Promocode saved successfully", false, [], [], 200, csrf_token(), csrf_hash());
                    }
                } else {
                    return redirect()->back();
                }
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Promo_codes.php - save()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function update()
    {
        try {
            if (!$this->isLoggedIn && !$this->userIsPartner) {
                return redirect('unauthorised');
            } else {
                $result = checkModificationInDemoMode($this->superadmin);
                if ($result !== true) {
                    return $this->response->setJSON($result);
                }
                if (isset($_POST) && !empty($_POST)) {
                    $repeat_usage = isset($_POST['repeat_usage']) ? $_POST['repeat_usage'] : '';
                    $id = isset($_POST['promo_id']) ? $_POST['promo_id'] : '';
                    $validation_rules = [
                        'promo_code' => ["rules" => 'required', "errors" => ["required" => "Please enter promo code name"]],
                        'start_date' => ["rules" => 'required', "errors" => ["required" => "Please select start date"]],
                        'end_date' => ["rules" => 'required', "errors" => ["required" => "Please select end date"]],
                        'no_of_users' => ["rules" => 'required|numeric|greater_than[0]', "errors" => ["required" => "Please enter number of users",    "numeric" => "Please enter numeric value for number of users",    "greater_than" => "number of users must be greater than 0",]],
                        'minimum_order_amount' => ["rules" => 'required|numeric|greater_than[0]', "errors" => ["required" => "Please enter minimum order amount",     "numeric" => "Please enter numeric value for minimum order amount",     "greater_than" => "minimum order amount must be greater than 0",]],
                        'discount' => ["rules" => 'required|numeric|greater_than[0]', "errors" => ["required" => "Please enter discount",    "numeric" => "Please enter numeric value for discount",    "greater_than" => "discount must be greater than 0",]],
                        'max_discount_amount' => ["rules" => 'required|numeric|greater_than[0]', "errors" => ["required" => "Please enter max discount amount",    "numeric" => "Please enter numeric value for max discount amount",    "greater_than" => "discount amount must be greater than 0",]]
                    ];
                    if ($repeat_usage == 'on') {
                        $validation_rules['no_of_repeat_usage'] = ["rules" => 'required|numeric|greater_than[0]', "errors" => ["required" => "Please enter number of repeat usage",     "numeric" => "Please enter numeric value for number of repeat usage",     "greater_than" => "number of repeat usage must be greater than 0",]];
                    }
                    $disk = fetch_current_file_manager();

                    $this->validation->setRules($validation_rules);
                    if (!$this->validation->withRequest($this->request)->run()) {
                        $errors = $this->validation->getErrors();
                        return ErrorResponse($errors, true, [], [], 200, csrf_token(), csrf_hash());
                    } else {
                        $promo_id = isset($_POST['promo_id']) ? $_POST['promo_id'] : '';
                        $old_image = fetch_details('promo_codes', ['id' => $promo_id], ['image']) ?? '';
                        $paths = [
                            'image' => [
                                'file' => $this->request->getFile('image'),
                                'path' => 'public/uploads/promocodes/',
                                'error' => labels('Failed to create promocodes folders', 'Failed to create promocodes folders'),
                                'folder' => 'promocodes',
                                'old_file' => $old_image[0]['image'],
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
                        $image_name = isset($uploadedFiles['image']['url']) ? $uploadedFiles['image']['url'] : $old_image[0]['image'];

                        $promocode_model = new Promo_code_model();
                        $repeat_usage = isset($_POST['repeat_usage']) ? "1" : "0";
                        $status = isset($_POST['status']) ? "1" : "0";
                        $users = isset($_POST['no_of_users']) ? $this->request->getVar('no_of_users') : "1";
                        $promocode = array(
                            'id' => $promo_id,
                            'partner_id' => $this->request->getVar('partner'),
                            'promo_code' => $this->request->getVar('promo_code'),
                            'message' => $this->request->getVar('message'),
                            'start_date' => (format_date($this->request->getVar('start_date'), 'Y-m-d')),
                            'end_date' => (format_date($this->request->getVar('end_date'), 'Y-m-d')),
                            'no_of_users' => $users,
                            'minimum_order_amount' => $this->request->getVar('minimum_order_amount'),
                            'max_discount_amount' => $this->request->getVar('max_discount_amount'),
                            'discount' => $this->request->getVar('discount'),
                            'discount_type' => $this->request->getVar('discount_type'),
                            'repeat_usage' => $repeat_usage,
                            'no_of_repeat_usage' => isset($_POST['no_of_repeat_usage']) ? $this->request->getVar('no_of_repeat_usage') : "0",
                            'image' => $image_name,
                            'status' => $status,
                        );
                        $promocode_model->save($promocode);
                        return successResponse("Promocode saved successfully", false, [], [], 200, csrf_token(), csrf_hash());
                    }
                } else {
                    return redirect()->back();
                }
            }
        } catch (\Throwable $th) {
         
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Promo_codes.php - update()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function duplicate()
    {
        try {
            helper('function');
            $uri = service('uri');
            $promocode_id = $uri->getSegments()[3];
            if ($this->isLoggedIn) {
                $partner_data = $this->db->table('users u')
                    ->select('u.id,u.username,pd.company_name')
                    ->join('partner_details pd', 'pd.partner_id = u.id')
                    ->where('is_approved', '1')
                    ->get()->getResultArray();
                $this->data['partner_name'] = $partner_data;
                $this->data['promocode'] = fetch_details('promo_codes', ['id' => $promocode_id])[0];
                setPageInfo($this->data, labels('promo_codes', 'Promo Codes') . ' | ' . labels('admin_panel', 'Admin Panel'), 'duplicate_promocode');
                return view('backend/admin/template', $this->data);
            } else {
                return redirect('partner/login');
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Promo_codes.php - duplicate()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
}
