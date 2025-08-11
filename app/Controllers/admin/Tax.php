<?php

namespace App\Controllers\admin;

use App\Models\Tax_model;

class Tax extends Admin
{
    public   $validation, $taxes, $creator_id;
    protected $superadmin;

    public function __construct()
    {
        parent::__construct();
        helper(['form', 'url']);
        $this->taxes = new Tax_model();
        $this->validation = \Config\Services::validation();
        $this->creator_id = $this->userId;
        $this->superadmin = $this->session->get('email');
        helper(['form', 'url', 'ResponceServices']);
    }
    public function index()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            setPageInfo($this->data, labels('tax', 'Tax') . ' | ' . labels('admin_panel', 'Admin Panel'), 'tax');
            $this->data['taxes'] = fetch_details('taxes');
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('unauthorised');
        }
    }
    public function add_tax()
    {

        try {
            $result = checkModificationInDemoMode($this->superadmin);
            if ($result !== true) {
                return $this->response->setJSON($result);
            }
            $permission = is_permitted($this->creator_id, 'create', 'tax');
            if ($permission) {
                if ($this->isLoggedIn && $this->userIsAdmin) {
                    $this->validation->setRules(
                        [
                            'title' => 'required|trim',
                            'percentage' => 'required|trim',
                        ]
                    );
                    if (!$this->validation->withRequest($this->request)->run()) {
                        $errors  = $this->validation->getErrors();
                        return ErrorResponse($errors, true, [], [], 200, csrf_token(), csrf_hash());
                    }
                    $title = trim($_POST['title']);
                    $percentage = ($_POST['percentage']);
                    $data['title'] = $title;
                    $data['percentage'] = $percentage;
                    $data['status'] = ($this->request->getPost('tax_status') == "on") ? 1 : 0;
                    if ($this->taxes->save($data)) {
                        return successResponse("Tax added successfully", false, [], [], 200, csrf_token(), csrf_hash());
                    } else {
                        return ErrorResponse("Please try again....", true, [], [], 200, csrf_token(), csrf_hash());
                    }
                } else {
                    return redirect('unauthorised');
                }
            } else {
                return ErrorResponse("Sorry! You are not permitted to take this action", true, [], [], 200, csrf_token(), csrf_hash());
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Tax.php - add_tax()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function list($from_app = false, $search = '', $limit = 10, $offset = 0, $sort = 'id', $order = 'asc', $where = [])
    {

        try {

            $multipleWhere = '';
            $db      = \Config\Database::connect();
            $builder = $db->table('taxes');
            $sortable_fields = ['id' => 'id', 'title' => 'title', 'percentage' => 'percentage'];
            $sort = 'id';
            $limit = 10;
            $condition  = [];
            $offset = 0;
            if (isset($_GET['offset'])) {
                $offset = $_GET['offset'];
            }
            if (isset($_GET['limit'])) {
                $limit = $_GET['limit'];
            }
            if (isset($_GET['sort'])) {
                if ($_GET['sort'] == 'id') {
                    $sort = (isset($sortable_fields[$sort])) ? $sortable_fields[$sort] : "id";
                } else {
                    $sort = $_GET['sort'];
                }
            }
            $order = "asc";
            if (isset($_GET['order'])) {
                $order = $_GET['order'];
            }
            if (isset($_GET['search']) and $_GET['search'] != '') {
                $search = $_GET['search'];
                $multipleWhere = ['`id`' => $search, '`title`' => $search, '`percentage`' => $search];
            }
            $total  = $builder->select(' COUNT(id) as `total` ');
            if (isset($_GET['id']) && $_GET['id'] != '') {
                $builder->where($condition);
            }
            if (isset($multipleWhere) && !empty($multipleWhere)) {
                $builder->orWhere($multipleWhere);
            }
            if (isset($where) && !empty($where)) {
                $builder->where($where);
            }
            $offer_count = $builder->get()->getResultArray();
            $total = $offer_count[0]['total'];
            $builder->select();
            if (isset($multipleWhere) && !empty($multipleWhere)) {
                $builder->orLike($multipleWhere);
            }
            if (isset($where) && !empty($where)) {
                $builder->where($where);
            }
            $offer_recored = $builder->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();
            $bulkData = array();
            $bulkData['total'] = $total;
            $rows = array();
            $tempRow = array();
            foreach ($offer_recored as $row) {
                $operations = '
               <button class="btn btn-primary edit_taxes" data-id="' . $row['id'] . '"  data-toggle="modal" data-target="#update_modal" onclick="taxes_id(this)"
               title = "Update the taxes"> <i class="fa fa-pen" aria-hidden="true"></i> </button>  
           ';
                $status = ($row['status'] == 0) ?
                    '<span class="badge badge-danger"> In Active </span>' :
                    ' <span class="badge badge-success"> Active </span>';
                $tempRow['id'] = $row['id'];
                $tempRow['title'] = $row['title'];
                $tempRow['percentage'] = $row['percentage'];
                $tempRow['status']  = $status;
                $tempRow['og_status'] = $row['status'];
                $tempRow['operations'] = $operations;
                $rows[] = $tempRow;
            }
            $bulkData['rows'] = $rows;
            return json_encode($bulkData);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Tax.php - list()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function remove_taxes()
    {

        try {
            $result = checkModificationInDemoMode($this->superadmin);
            if ($result !== true) {
                return $this->response->setJSON($result);
            }
            $permission = is_permitted($this->creator_id, 'delete', 'tax');
            if ($permission) {
                if ($this->isLoggedIn && $this->userIsAdmin) {
                    $id = $this->request->getPost('id');
                    $db      = \Config\Database::connect();
                    $builder = $db->table('taxes');
                    if ($builder->delete(['id' => $id])) {
                        return successResponse("Tax deleted successfully", false, [], [], 200, csrf_token(), csrf_hash());
                    } else {
                        return ErrorResponse("An error occured during deleting this item", true, [], [], 200, csrf_token(), csrf_hash());
                    }
                } else {
                    return redirect('unauthorised');
                }
            } else {
                return ErrorResponse("Sorry! You are not permitted to take this action", true, [], [], 200, csrf_token(), csrf_hash());
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Tax.php - remove_taxes()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function edit_taxes()
    {
        try {
            $result = checkModificationInDemoMode($this->superadmin);
            if ($result !== true) {
                return $this->response->setJSON($result);
            }
            $permission = is_permitted($this->creator_id, 'update', 'tax');
            if ($permission) {
                $id = $this->request->getPost('id');
                $db      = \Config\Database::connect();
                $builder = $db->table('taxes');
                if ($this->isLoggedIn && $this->userIsAdmin) {
                    $id = $this->request->getPost('id');
                    $title = $this->request->getPost('title');
                    $percentage = $this->request->getPost('percentage');
                    $data['title'] = $title;
                    $data['percentage'] = $percentage;
                    $data['status'] = ($this->request->getPost('tax_status_edit') == "on") ? 1 : 0;
                    if ($builder->update($data, ['id' => $id])) {
                        return successResponse("Tax updated successfully", false, [], [], 200, csrf_token(), csrf_hash());
                    } else {
                        return ErrorResponse("Some error occurred", true, [], [], 200, csrf_token(), csrf_hash());
                    }
                } else {
                    return redirect('unauthorised');
                }
            } else {
                return ErrorResponse("Sorry! You are not permitted to take this action", true, [], [], 200, csrf_token(), csrf_hash());
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Tax.php - edit_taxes()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
}
