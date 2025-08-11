<?php
namespace App\Controllers\partner;
use App\Models\Promo_code_model;
class Promo_codes extends Partner
{
    public $orders;
    protected Promo_code_model $promo_codes; // Declare the $partner property with the correct type

    public function __construct()
    {
        parent::__construct();
        $this->promo_codes = new Promo_code_model();
        $this->validation = \Config\Services::validation();
        helper('ResponceServices');
    }
    public function index()
    {
        if ($this->isLoggedIn && $this->userIsPartner) {
            if (!exists(['partner_id' => $this->userId, 'is_approved' => 1], 'partner_details')) {
                return redirect('partner/profile');
            }
            setPageInfo($this->data, 'Promo codes | Provider Panel', 'promo_codes');
            return view('backend/partner/template', $this->data);
        } else {
            return redirect('partner/login');
        }
    }
    public function add()
    {
        if (!$this->isLoggedIn && !$this->userIsPartner) {
            return redirect('partner/login');
        } else {
            if (!exists(['partner_id' => $this->userId, 'is_approved' => 1], 'partner_details')) {
                return redirect('partner/profile');
            }
            setPageInfo($this->data, 'Promo codes | Provider Panel', FORMS . 'add_promocode');
            return view('backend/partner/template', $this->data);
        }
    }
    public function save()
    {
        try {
            if (!$this->isLoggedIn && !$this->userIsPartner) {
                return redirect('unauthorised');
            } else {

            $disk =fetch_current_file_manager();
                
                if (isset($_POST) && !empty($_POST)) {
                    $repeat_usage = isset($_POST['repeat_usage']) ? $_POST['repeat_usage'] : '';
                    $id = isset($_POST['promo_id']) ? $_POST['promo_id'] : '';
                    if ($repeat_usage == 'on' && empty($id) && $id == '') {
                        $this->validation->setRules(
                            [
                                'promo_code' => ["rules" => 'required', "errors" => ["required" => "Please enter promo code name"]],
                                'start_date' => ["rules" => 'required', "errors" => ["required" => "Please select start date"]],
                                'end_date' => ["rules" => 'required', "errors" => ["required" => "Please select end date"]],
                                'no_of_users' => ["rules" => 'required|numeric|greater_than[0]', "errors" => ["required" => "Please enter number of users",    "numeric" => "Please enter numeric value for number of users",    "greater_than" => "number of users must be greater than 0",]],
                                'no_of_repeat_usage' => ["rules" => 'required|numeric|greater_than[0]', "errors" => ["required" => "Please enter number of repeat usage",    "numeric" => "Please enter numeric value for number of repeat usage",    "greater_than" => "number of repeat usage must be greater than 0",]],
                                'minimum_order_amount' => ["rules" => 'required|numeric|greater_than[0]', "errors" => ["required" => "Please enter minimum order amount",    "numeric" => "Please enter numeric value for minimum order amount",    "greater_than" => "minimum order amount must be greater than 0",]],
                                'discount' => ["rules" => 'required|numeric|greater_than[0]', "errors" => ["required" => "Please enter discount",    "numeric" => "Please enter numeric value for discount",    "greater_than" => "discount must be greater than 0",]],
                                'max_discount_amount' => ["rules" => 'required|numeric|greater_than[0]', "errors" => ["required" => "Please enter max discount amount",    "numeric" => "Please enter numeric value for max discount amount",    "greater_than" => "discount amount must be greater than 0",]],
                            ],
                        );
                    } else {
                        $this->validation->setRules(
                            [
                                'promo_code' => ["rules" => 'required', "errors" => ["required" => "Please enter promo code name"]],
                                'start_date' => ["rules" => 'required', "errors" => ["required" => "Please select start date"]],
                                'end_date' => ["rules" => 'required', "errors" => ["required" => "Please select end date"]],
                                'no_of_users' => ["rules" => 'required|numeric|greater_than[0]', "errors" => ["required" => "Please enter number of users",    "numeric" => "Please enter numeric value for number of users",    "greater_than" => "number of users must be greater than 0",]],
                                'minimum_order_amount' => ["rules" => 'required|numeric|greater_than[0]', "errors" => ["required" => "Please enter minimum order amount",    "numeric" => "Please enter numeric value for minimum order amount",    "greater_than" => "minimum order amount must be greater than 0",]],
                                'discount' => ["rules" => 'required|numeric|greater_than[0]', "errors" => ["required" => "Please enter discount",    "numeric" => "Please enter numeric value for discount",    "greater_than" => "discount must be greater than 0",]],
                                'max_discount_amount' => ["rules" => 'required|numeric|greater_than[0]', "errors" => ["required" => "Please enter max discount amount",    "numeric" => "Please enter numeric value for max discount amount",    "greater_than" => "discount amount must be greater than 0",]],
                            ],
                        );
                    }
                    if (!$this->validation->withRequest($this->request)->run()) {
                        $errors  = $this->validation->getErrors();
                        $response['error'] = true;
                        $response['message'] = $errors;
                        $response['csrfName'] = csrf_token();
                        $response['csrfHash'] = csrf_hash();
                        $response['data'] = [];
                        return $this->response->setJSON($response);
                    } else {
                        if (isset($_POST['promo_id']) && !empty($_POST['promo_id'])) {
                            $promo_id = $_POST['promo_id'];
                            $old_image = fetch_details('promo_codes', ['id' => $_POST['promo_id']]);
                        } else {
                            $promo_id = '';
                            $old_image = '';
                        }
                        $image = "";
                        
                        $paths = [
                            'image' => [
                                'file' => $this->request->getFile('image'),
                                'path' => 'public/uploads/promocodes/',
                                'error' => 'Failed to create promocodes folders',
                                'folder' => 'promocodes',
                                'old_file' => $old_image[0]['image'] ?? null,
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
                        $image_name = $uploadedFiles['image']['url'] ?? $old_image[0]['image'] ?? null;
                        $image_disk = $uploadedFiles['image']['disk'] ?? $disk ?? null;
                        // Default to local_server path if applicable
                        $image = isset($uploadedFiles['image']['disk']) && $uploadedFiles['image']['disk'] == 'local_server'
                            ? 'public/uploads/promocodes/' . $uploadedFiles['image']['url']
                            : $uploadedFiles['image']['url'] ?? null;
                        // Handle scenario when no file is uploaded
                        if (empty($image)) {
                            $image = $old_image[0]['image'] ?? null;
                        }
                        $promocode_model = new Promo_code_model();
                        $partner_id = $_SESSION['user_id'];
                        if (isset($_POST['repeat_usage'])) {
                            $repeat_usage = "1";
                        } else {
                            $repeat_usage = "0";
                        }
                        if (isset($_POST['status'])) {
                            $status = "1";
                        } else {
                            $status = "0";
                        }
                        if (isset($_POST['no_of_users'])) {
                            $users = $this->request->getVar('no_of_users');
                        } else {
                            $users = "1";
                        }
                        $promocode = array(
                            'id' => $promo_id,
                            'partner_id' => $partner_id,
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
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/Promo_codes.php - save()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function list()
    {
        $promocode_model = new Promo_code_model();
        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
        $where['pc.partner_id'] = $_SESSION['user_id'];
        $promo_codes =  $promocode_model->list(false, $search, $limit, $offset, $sort, $order, $where);
        return $promo_codes;
    }
    public function delete()
    {
        try {
            $id = $this->request->getPost('id');
            $db = \Config\Database::connect();
            $old_image = fetch_details('promo_codes', ['id' => $id], ['image']);
            $disk =fetch_current_file_manager();

            if (!empty($old_image)) {
                delete_file_based_on_server('promocodes', $old_image[0]['image'], $disk);
            }
            $builder = $db->table('promo_codes')->delete(['id' => $id]);
            if ($builder) {
                return successResponse("Promocode deleted successfully", false, [], [], 200, csrf_token(), csrf_hash());
            } else {
                return ErrorResponse("Promocode cannot be deleted!", true, [], [], 200, csrf_token(), csrf_hash());
            }
        } catch (\Throwable $th) {
            log_message('error', $th->getMessage() . "\n" . $th->getTraceAsString());
            return ErrorResponse("Something went wrong while deleting the promo code", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function duplicate()
    {
        try {
            helper('function');
            $uri = service('uri');
            $promocode_id = $uri->getSegments()[3];
            if ($this->isLoggedIn && $this->userIsPartner) {
                if (!exists(['partner_id' => $this->userId, 'is_approved' => 1], 'partner_details')) {
                    return redirect('partner/profile');
                }
                $this->data['promocode'] = fetch_details('promo_codes', ['id' => $promocode_id])[0];
                setPageInfo($this->data, 'Promo codes | Provider Panel', FORMS . 'duplicate_promocode');
                return view('backend/partner/template', $this->data);
            } else {
                return redirect('partner/login');
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/partner/Promo_codes.php - duplicate()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
}
