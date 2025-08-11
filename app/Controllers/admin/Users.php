<?php
namespace App\Controllers\admin;
class Users extends Admin
{
    public $user_model, $admin_id;
    protected $superadmin; 

    public function __construct()
    {
        parent::__construct();
        $this->user_model = new \App\Models\Users_model();
        $this->ionAuth = new \IonAuth\Libraries\IonAuth();
        $this->admin_id = ($this->ionAuth->isAdmin()) ? $this->ionAuth->user()->row()->id : 0;
        $this->superadmin = $this->session->get('email');
        helper(['ResponceServices']);
    }
    public function index()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            setPageInfo($this->data, labels('user_list', 'User List') . ' | ' . labels('admin_panel', 'Admin Panel'), 'users');
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }
    public function list_user()
    {
        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
        $data = json_encode($this->user_model->list(false, $search, $limit, $offset, $sort, $order));
        return $data;
    }
    public function deactivate()
    {
        try {
            if ($this->isLoggedIn && $this->userIsAdmin) {
                $result = checkModificationInDemoMode($this->superadmin);
                if ($result !== true) {
                    return $this->response->setJSON($result);
                }
                $id = $this->request->getVar('user_id');
                $userdata = fetch_details('users', ['id' => $id], ['email', 'username']);
                if (!empty($userdata[0]['email']) && check_notification_setting('user_account_deactive', 'email') && is_unsubscribe_enabled($id) == 1) {
                    send_custom_email('user_account_deactive', null, $userdata[0]['email'], null, $id);
                }

                if (check_notification_setting('user_account_deactive', 'sms')) {
                    send_custom_sms('user_account_deactive', null, $userdata[0]['email'], null, $id);
                }
                $operations = $this->ionAuth->deactivate($id);
                if ($operations) {
                    // $this->ionAuth->logout();
                    delete_details(['user_id' => $id], 'users_tokens');

                    return successResponse("Email sent to the user successfully and user is disabled", false, [], [], 200, csrf_token(), csrf_hash());
                } else {
                    return ErrorResponse("Could not deactivate user", true, [], [], 200, csrf_token(), csrf_hash());
                }
            } else {
                return redirect('admin/login');
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Users.php - deactivate()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function activate()
    {
        try {
            if ($this->isLoggedIn && $this->userIsAdmin) {
                $result = checkModificationInDemoMode($this->superadmin);
                if ($result !== true) {
                    return $this->response->setJSON($result);
                }
                $id = $this->request->getVar('user_id');
                $operations =   $this->ionAuth->activate($id);
                $userdata = fetch_details('users', ['id' => $id], ['email', 'username']);
                if ($operations) {
                    if (!empty($userdata[0]['email']) && check_notification_setting('user_account_active', 'email') && is_unsubscribe_enabled($id) == 1) {
                        send_custom_email('user_account_active', null, $userdata[0]['email'], null, $id);
                    }

                    if (check_notification_setting('user_account_active', 'sms')) {
                        send_custom_sms('user_account_active', null, $userdata[0]['email'], null, $id);
                    }
                    return successResponse("Email sent to the user successfully and user is active", false, [], [], 200, csrf_token(), csrf_hash());
                } else {
                    return ErrorResponse("Some error occurred", true, [], [], 200, csrf_token(), csrf_hash());
                }
            } else {
                return redirect('admin/login');
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Users.php - activate()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
}
