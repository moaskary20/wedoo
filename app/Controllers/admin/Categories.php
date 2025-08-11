<?php

namespace App\Controllers\admin;

use App\Models\Category_model;
use App\Models\Service_model;
use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use CodeIgniter\HTTP\Files\UploadedFile;
use Exception;

class Categories extends Admin
{
    public $category,  $validation;
    protected $superadmin; // No type declaration
    protected $service; // Explicitly declare the $service property

    public function __construct()
    {
        parent::__construct();
        $this->category = new Category_model();
        $this->validation = \Config\Services::validation();
        $this->service = new Service_model();
        $this->superadmin = $this->session->get('email');
        helper('ResponceServices');
    }
    public function index()
    {


        if (!$this->isLoggedIn || !$this->userIsAdmin) {
            return redirect('admin/login');
        }
        setPageInfo($this->data, labels('categories', 'Categories') . ' | ' . labels('admin_panel', 'Admin Panel'), 'categories');
        $this->data['categories'] = fetch_details('categories', [], ['id', 'name']);
        $this->data['parent_categories'] = fetch_details('categories', ['parent_id' => '0'], ['id', 'name']);
        return view('backend/admin/template', $this->data);
    }
    public function add_category()
    {
        if (!$this->isLoggedIn || !$this->userIsAdmin) {
            return redirect('admin/login');
        }
        $result = checkModificationInDemoMode($this->superadmin);
        if ($result !== true) {
            return $this->response->setJSON($result);
        }
        try {
            $rules = [
                'name' => [
                    "rules" => 'required|trim',
                    "errors" => [
                        "required" => "Please enter name for category"
                    ]
                ],
                'image' => [
                    "rules" => 'uploaded[image]',
                    "errors" => [
                        "uploaded" => "Please select an image",
                    ]
                ],
                'category_slug' => [
                    "rules" => 'required|trim',
                    "errors" => [
                        "uploaded" => "Please enter slug for category",
                    ]
                ],
            ];
            $type = $this->request->getPost('make_parent');
            if (isset($type) && $type == "1") {
                $rules['parent_id'] = [
                    "rules" => 'required|trim',
                    "errors" => [
                        "required" => "Please select parent category"
                    ]
                ];
            }
            $this->validation->setRules($rules);
            if (!$this->validation->withRequest($this->request)->run()) {
                $errors  = $this->validation->getErrors();
                return ErrorResponse($errors, true, [], [], 200, csrf_token(), csrf_hash());
            }
            $Category_image = $this->request->getFile('image');
            $paths = [
                'profile' => ['file' => $Category_image, 'path' => 'public/uploads/categories/', 'error' => 'Failed to create categories folders'],
            ];
            foreach ($paths as $key => $upload) {
                $result = upload_file($upload['file'], $upload['path'], $upload['error'], 'categories');
                if ($result['error'] == false) {
                    $url = $result['file_name'];
                  
                } else {
                    return ErrorResponse($result['message'], true, [], [], 200, csrf_token(), csrf_hash());
                }
            }
            $data['name'] = trim($_POST['name']);
            $data['image'] = $url;
            $data['slug'] = generate_unique_slug($this->request->getPost('name'), 'categories');

            $data['slug'] = generate_unique_slug($this->request->getPost('category_slug'), 'categories');
            $data['admin_commission'] = "0";
            $data['parent_id'] = $_POST['parent_id']??0;
            $data['dark_color'] = $_POST['dark_theme_color'] != "#000000" ? $_POST['dark_theme_color'] : "#2A2C3E";
            $data['light_color'] = $_POST['light_theme_color'] != "#000000" ? $_POST['light_theme_color'] : "#FFFFFF";
            $data['status'] = 1;
            if ($this->category->save($data)) {
                return successResponse("Category added successfully", false, [], [], 200, csrf_token(), csrf_hash());
            } else {
                return ErrorResponse("some error while addding category", true, [], [], 200, csrf_token(), csrf_hash());
            }
        } catch (\Throwable $th) {
           
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Categories.php - add_category()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
        $creator_id = $this->userId;
        $permission = is_permitted($creator_id, 'create', 'categories');
        if (!$permission) {
            return NoPermission();
        }
    }
    public function list()
    {
        try {
            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
            $where = [];
            $from_app = false;
            if (isset($_POST['id']) && !empty($_POST['id'])) {
                $where['parent_id'] = $_POST['id'];
                $from_app = true;
            }
            $data = $this->category->list($from_app, $search, $limit, $offset, $sort, $order, $where);
            if (isset($_POST['id']) && !empty($_POST['id'])) {
                if (!empty($data['data'])) {
                    return successResponse("Sub Categories fetched successfully", false, $data['data'], [], 200, csrf_token(), csrf_hash());
                } else {
                    return ErrorResponse("Sub Categories not found on this category", true,  $data['data'], [], 200, csrf_token(), csrf_hash());
                }
            }
            return $data;
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Categories.php - list()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function get_categories()
    {
        try {
            $limit = $_GET['limit'] ?? 10;
            $offset = $_GET['offset'] ?? 0;
            $sort = $_GET['sort'] ?? 'id';
            $order = $_GET['order'] ?? 'ASC';
            $search = $_GET['search'] ?? '';
            $where = [];
            $from_app = false;
            if (isset($_POST['id']) && !empty($_POST['id'])) {
                $where['parent_id'] = $_POST['id'];
                $from_app = true;
            }
            $data = $this->category->list($from_app, $search, $limit, $offset, $sort, $order, $where);
            return $this->response->setJSON($data);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Categories.php - get_categories()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function update_category()
    {
        try {
            $result = checkModificationInDemoMode($this->superadmin);
            if ($result !== true) {
                return $this->response->setJSON($result);
            }
            $creator_id = $this->userId;
            $permission = is_permitted($creator_id, 'update', 'categories');
            if (!$permission) {
                return NoPermission();
            }
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            $type = $this->request->getPost('edit_make_parent');
            $rules = [
                'name' => [
                    "rules" => 'required|trim',
                    "errors" => [
                        "required" => "Please enter name for category"
                    ]
                ],
                'category_slug' => [
                    "rules" => 'required|trim',
                    "errors" => [
                        "required" => "Please enter slug for category"
                    ]
                ]
            ];
            if (isset($type) && $type == "1") {
                $rules['edit_parent_id'] = [
                    "rules" => 'required|trim',
                    "errors" =>
                    ["required" => "Please select parent category"]
                ];
            }
            $this->validation->setRules($rules);
            if (!$this->validation->withRequest($this->request)->run()) {
                $errors = $this->validation->getErrors();
                return ErrorResponse($errors, true, [], [], 200, csrf_token(), csrf_hash());
            }
            if (!create_folder('public/uploads/categories/')) {
                return ErrorResponse("Failed to create folders", true, [], [], 200, csrf_token(), csrf_hash());
            }
            $id = $this->request->getPost('id');
            $old_data = fetch_details('categories', ['id' => $id]);
            $old_image = $old_data[0]['image'];
        

            $old_disk=fetch_current_file_manager();
            $image = $this->request->getFile('image');
            $image_name = !empty($image) && $image->getName() != "" ? $image->getName() : $old_image;
            $slug = generate_unique_slug($this->request->getPost('category_slug'), 'categories',$id);
            $slug = generate_unique_slug($this->request->getPost('name'), 'categories',$id);

            $data = [
                'parent_id' => $type == "1" ? $this->request->getPost(('edit_parent_id')) : "0",
                'name' => $this->request->getPost('name'),
                'admin_commission' => "0",
                'dark_color' => $_POST['edit_dark_theme_color'],
                'light_color' => $_POST['edit_light_theme_color'],
                'status' => 1,
                'slug' => $slug,
            ];
            $old_path = "public/uploads/categories/" . $old_image;
          
            $Category_image = $this->request->getFile('image');
            // If a new image is uploaded
            if ($Category_image && $Category_image->isValid() && !$Category_image->hasMoved()) {
              
                if (!empty($old_image)) {
                    delete_file_based_on_server('categories', $old_image, $old_disk);
                }
                $paths = [
                    'category' => [
                        'file' => $Category_image,
                        'path' => 'public/uploads/categories/',
                        'error' => 'Failed to create categories folders'
                    ],
                ];
                foreach ($paths as $key => $upload) {
                    $result = upload_file($upload['file'], $upload['path'], $upload['error'], 'categories');
                    if ($result['error'] == false) {
                        $image_name = $result['file_name'];
                    } else {
                        return ErrorResponse($result['message'], true, [], [], 200, csrf_token(), csrf_hash());
                    }
                }
            } else {
                $image_name = $old_image;
            }
            // Update the data array with the image name
            $data['image'] = $image_name;
            $upd = $this->category->update($id, $data);
            if ($upd) {
                return successResponse("Category updated successfully", false, [], [], 200, csrf_token(), csrf_hash());
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Categories.php - update_category()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function remove_category()
    {
        try {
            $result = checkModificationInDemoMode($this->superadmin);
            if ($result !== true) {
                return $this->response->setJSON($result);
            }
            $creator_id = $this->userId;
            $permission = is_permitted($creator_id, 'delete', 'categories');
            if (!$permission) {
                return NoPermission();
            }
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            $id = $this->request->getPost('user_id');
            $db = \Config\Database::connect();
            $builder = $db->table('categories');
            $cart_builder = $db->table('cart');
            $icons = fetch_details('categories', ['id' => $id]);
            $subcategories = fetch_details('categories', ['parent_id' => $id], ['id', 'name']);
            $services = fetch_details('services', ['category_id' => $id], ['id']);
            foreach ($subcategories as $sb) {
                $sb['status'] = 0;
                $this->category->update($sb['id'], $sb);
            }
            foreach ($services as $s) {
                $s['status'] = 0;
                $this->service->update($s['id'], $s);
                $cart_builder->delete(['service_id' => $s['id']]);
            }
            $category_image = $icons[0]['image'];
            $disk =fetch_current_file_manager();
            if ($builder->delete(['id' => $id])) {
                delete_file_based_on_server('categories', $category_image, $disk);
                return successResponse("Category Removed successfully", false, [], [], 200, csrf_token(), csrf_hash());
            }
            return ErrorResponse("An error occured during deleting this item", true, [], [], 200, csrf_token(), csrf_hash());
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Categories.php - remove_category()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
}
