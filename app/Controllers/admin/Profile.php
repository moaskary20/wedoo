<?php
namespace App\Controllers\admin;
class Profile extends Admin
{
    protected $superadmin,$validation;
    public function __construct()
    {
        parent::__construct();
        $this->validation = \Config\Services::validation();
        $this->superadmin = $this->session->get('email');
    }
    public function index()
    {
        helper('function');
        if ($this->isLoggedIn) {
            setPageInfo($this->data, labels('Profile', 'Profile') . ' | ' . labels('admin_panel', 'Admin Panel'), 'profile');
            $this->data['data'] = fetch_details('users', ['id' => $this->userId])[0];
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }
    public function update()
    {
        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            $result = checkModificationInDemoMode($this->superadmin);
            if ($result !== true) {
                return $this->response->setJSON($result);
            }
            $phoneNumber = $this->request->getPost('phone');
            $db = \Config\Database::connect();
            $query = $db->table('users');
            $query->selectCount('id');
            $query->where('phone', $phoneNumber);
            $query->where('id!=', $this->userId);
            $count = $query->get()->getRow()->id;
            $rules = [
                'username' => [
                    "rules" => 'required|trim',
                    "errors" => [
                        "required" => "Please enter username",
                    ],
                ],
                'phone' => [
                    "rules" => 'required',
                    "errors" => [
                        "required" => "Please enter admin phone number",
                        "numeric" => "Please enter a numeric phone number",
                    ],
                ],
            ];
            $this->validation->setRules($rules);
            if (!$this->validation->withRequest($this->request)->run()) {
                $errors = $this->validation->getErrors();
                $response['error'] = true;
                $response['message'] = $errors;
                $response['csrfName'] = csrf_token();
                $response['csrfHash'] = csrf_hash();
                $response['data'] = [];
                return $this->response->setJSON($response);
            }
            $data = [
                'username' => $this->request->getPost('username'),
                'phone' => $this->request->getPost('phone'),
            ];
            $old_image = fetch_details('users', ['id' => $this->userId], ['image']);
            if (isset($_FILES['profile'])) {
                $path = './public/backend/assets/profiles/';
                $path_db = 'public/backend/assets/profiles/';
                $file = $this->request->getFile('profile');
                if ($file->isValid()) {
                    if ($file->move($path)) {
                        if (!empty($old_image[0]['image'])) {
                            if (file_exists(FCPATH . "/public/backend/assets/profiles/" . $old_image[0]['image']) && !empty(FCPATH . "/public/backend/assets/profiles/" . $old_image[0]['image'])) {
                                unlink(FCPATH . "/public/backend/assets/profiles/" . $old_image[0]['image']);
                            }
                        }
                        $image = $file->getName();
                    }
                } else {
                    $image = $old_image[0]['image'];
                }
                $data['image'] = $image;
            } else {
                $data['image'] = $old_image[0]['image'];
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
                        return $this->response->setJSON([
                            'csrfName' => csrf_token(),
                            'csrfHash' => csrf_hash(),
                            'error' => false,
                            'message' => labels('User updated successfully', 'User updated successfully'),
                            "data" => $_POST,
                        ]);
                    } else {
                        return $this->response->setJSON([
                            'csrfName' => csrf_token(),
                            'csrfHash' => csrf_hash(),
                            'error' => true,
                            'message' => labels('Old password did not match', 'Old password did not match'),
                            "data" => $_POST,
                        ]);
                    }
                }
                $this->ionAuth->logout();
                return $this->response->setJSON([
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'error' => false,
                    'message' => labels('User updated successfully', 'User updated successfully'),
                    "data" => $_POST,
                ]);
            } else {
                return $this->response->setJSON([
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'error' => true,
                    'message' => labels('Something Went Wrong', 'Something Went Wrong'),
                    "data" => [],
                ]);
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Profile.php - update()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
}
