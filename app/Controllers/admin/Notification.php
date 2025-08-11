<?php

namespace App\Controllers\admin;

use App\Models\Notification_model;

class Notification extends Admin
{
    public   $validation, $notification, $db;
    protected $superadmin; // No type declaration

    public function __construct()
    {
        parent::__construct();
        helper(['form', 'url']);
        $this->notification = new Notification_model();
        $this->validation = \Config\Services::validation();
        $this->db      = \Config\Database::connect();
        $this->superadmin = $this->session->get('email');
        helper('ResponceServices');
    }
    public function index()
    {
        if (!$this->isLoggedIn || !$this->userIsAdmin) {
            return redirect('admin/login');
        }
        setPageInfo($this->data, labels('send_notification', 'Send Notification') . ' | ' . labels('admin_panel', 'Admin Panel'), 'notification');
        $this->data['categories_name'] = fetch_details('categories', [], ['id', 'name']);
        $this->data['users'] = fetch_details('users', [], ['id', 'username']);
        $this->data['partners'] = fetch_details('partner_details', []);
        $this->data['notification'] = fetch_details('notifications');
        return view('backend/admin/template', $this->data);
    }
    public function add_notification()
    {
        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            $result = checkModificationInDemoMode($this->superadmin);
            if ($result !== true) {
                return $this->response->setJSON($result);
            }
            $type = $this->request->getPost('type');
            $common_rules = [
                'title' => ["rules" => 'required|trim', "errors" => ["required" => "Please enter title for notification"]],
                'message' => ["rules" => 'required', "errors" => ["required" => "Please enter message for notification"]]
            ];
            if (isset($type) && $type == "specific_user") {
                $specific_rules = [
                    'user_ids' => ["rules" => 'required', "errors" => ["required" => "Please select at least one user"]]
                ];
            } else {
                $specific_rules = [
                    'type' => ["rules" => 'required', "errors" => ["required" => "Please select type of notification"]]
                ];
            }
            $validation_rules = array_merge($common_rules, $specific_rules);
            $this->validation->setRules($validation_rules);
            if (!$this->validation->withRequest($this->request)->run()) {
                $errors  = $this->validation->getErrors();
                return ErrorResponse($errors, true, [], [], 200, csrf_token(), csrf_hash());
            }
            $t = time();
            $user_type = $this->request->getPost('user_type');
            $name = $this->request->getPost('type');
            $image_data = $this->request->getFile('image');
            $image = ($image_data->getName() != "") ? $image_data : '';
            $title = $this->request->getPost('title');
            $message = $this->request->getPost('message');
            $web_registrationIDs = [];
            if ($user_type == "all_users") {
                $data['user_id'] = ['0'];
                $data['target'] = "all_users";
            } else if ($user_type == "specific_user") {

                
                $data['user_id'] =  json_encode($_POST['user_ids']);
                $data['target'] = "specific_user";
            } elseif ($user_type == "provider") {
                $data['target'] = "provider";
            } elseif ($user_type == "customer") {
                $data['user_id'] = ['0'];
                $data['target'] = "customer";
            } else {
                $id = "000";
            }
            $ext = ($image != "") ? $image->getExtension() : '';
            $data['title'] = $title;
            $data['message'] = $message;
            $data['type'] = $name;
            if ($name == "general") {
                $data['type_id'] = "-";
            } else if ($name == "provider") {
                $data['type_id'] = $_POST['partner_id'];
                $data['user_id'] =  json_encode($_POST['partner_id']);
            } else if ($name == "category") {
                $data['type_id'] = $_POST['category_id'];
            } else if ($name == "url") {
                $data['type_id'] = "0";
            }
            if ($name == "general") {
                $data['notification_type'] = "general";
            } else if ($name == "provider") {
                $data['notification_type'] = "provider";
            } else if ($name == "category") {
                $data['notification_type'] = "category";
            } else if ($name == "url") {
                $data['notification_type'] = "url";
            }
            $paths = [
                'image' => [
                    'file' => $this->request->getFile('image'),
                    'path' => 'public/uploads/notification',
                    'error' => labels('failed_to_create_notification_folders', 'Failed to create notification folders'),
                    'folder' => 'notification'
                ],
            ];
            $uploadedFiles = [];
            $registrationIDs = [];
            foreach ($paths as $key => $upload) {
                if ($upload['file'] && $upload['file']->isValid()) {
                    $result = upload_file($upload['file'], $upload['path'], $upload['error'], $upload['folder']);
                    $image_disk = $result['disk'];
                    if ($result['error'] == false) {
                        $uploadedFiles[$key] = [
                            'url' => 'public/uploads/notification/' . $result['file_name'],
                            'disk' => $result['disk']
                        ];
                            $data['image'] =  $result['file_name'];

                    } else {
                        return ErrorResponse($result['message'], true, [], [], 200, csrf_token(), csrf_hash());
                    }
                }
            }
            
            $fcm_ids['fcm_id'] = '';
            $fcm_ids['platform'] = '';
            if ($this->notification->save($data)) {
                if ($user_type == "all_users") {
                    $where = "fcm_id IS NOT NULL AND fcm_id != '' AND platform IS NOT NULL AND platform!=''";
                    $users_fcm = $this->db->table('users')->select('fcm_id,platform')->where($where)->get()->getResultArray();
                    $fcm_ids = [];
                    foreach ($users_fcm as $ids) {
                        if ($ids['fcm_id'] != "") {
                            $fcm_ids['fcm_id'] = $ids['fcm_id'];
                            $fcm_ids['platform'] = $ids['platform'];
                        }
                        $registrationIDs[] = $fcm_ids;
                    }
                    //for web start
                    $web_where = "web_fcm_id IS NOT NULL AND fcm_id != ''";
                    $web_fcm_id = $this->db->table('users')->select('web_fcm_id')->where($web_where)->get()->getResultArray();
                    $webfcm_ids = [];
                    foreach ($web_fcm_id as $ids) {
                        if ($ids['web_fcm_id'] != "") {
                            $webfcm_ids['web_fcm_id'] = $ids['web_fcm_id'];
                        }
                        $web_registrationIDs[] = $webfcm_ids;
                    }
                    //for web end
                }
                //if user type is specifc user
                else if ($user_type == "specific_user") {
                    $to_send_id = $_POST['user_ids'];
                    $builder = $this->db->table('users')->select('fcm_id,platform');
                    $users_fcm = $builder->whereIn('id', $to_send_id)->get()->getResultArray();
                    foreach ($users_fcm as $ids) {
                        if ($ids['fcm_id'] != "") {
                            $fcm_ids['fcm_id'] = $ids['fcm_id'];
                            $fcm_ids['platform'] = $ids['platform'];
                        }
                        $registrationIDs[] = $fcm_ids;
                    }
                    //for web start
                    $web_where = "web_fcm_id IS NOT NULL AND web_fcm_id != ''";
                    $web_fcm_id = $this->db->table('users')->select('web_fcm_id')->where($web_where)->whereIn('id', $to_send_id)->get()->getResultArray();
                    $webfcm_ids = [];
                    foreach ($web_fcm_id as $ids) {
                        if ($ids['web_fcm_id'] != "") {
                            $webfcm_ids['web_fcm_id'] = $ids['web_fcm_id'];
                        }
                        $web_registrationIDs[] = $webfcm_ids;
                    }
                    //for web end
                }
                //if user type is provider
                else if ($user_type == "provider") {
                    $partner = fetch_details('partner_details', ['partner_id' => $_POST['partner_id']]);
                    foreach ($partner as $row) {
                        $to_send_id[] = $row['partner_id'];
                    }
                    $builder = $this->db->table('users')->select('fcm_id,platform');
                    $users_fcm = $builder->whereIn('id', $to_send_id)->get()->getResultArray();
                    foreach ($users_fcm as $ids) {
                        if ($ids['fcm_id'] != "") {
                            $fcm_ids['fcm_id'] = $ids['fcm_id'];
                            $fcm_ids['platform'] = $ids['platform'];
                        }
                        $registrationIDs[] = $fcm_ids;
                    }
                }
                //if user type is customer 
                else if ($user_type == "customer") {
                    $db      = \Config\Database::connect();
                    $builder = $db->table('users u');
                    $builder->select('u.*,ug.group_id')
                        ->join('users_groups ug', 'ug.user_id = u.id')
                        ->where('ug.group_id', "2");
                    $user_record = $builder->orderBy('id', 'DESC')->limit(0, 0)->get()->getResultArray();
                    foreach ($user_record as $row) {
                        $to_send_id[] = $row['id'];
                    }
                    $users_fcm = $builder->whereIn('id', $to_send_id)->get()->getResultArray();
                    foreach ($users_fcm as $ids) {
                        if ($ids['fcm_id'] != "") {
                            $fcm_ids['fcm_id'] = $ids['fcm_id'];
                            $fcm_ids['platform'] = $ids['platform'];
                        }
                        $registrationIDs[] = $fcm_ids;
                    }
                    //for web start
                    $web_where = "web_fcm_id IS NOT NULL AND web_fcm_id != ''";
                    $web_fcm_id = $this->db->table('users')->select('web_fcm_id')->where($web_where)->whereIn('id', $to_send_id)->get()->getResultArray();
                    $webfcm_ids = [];
                    foreach ($web_fcm_id as $ids) {
                        if ($ids['web_fcm_id'] != "") {
                            $webfcm_ids['web_fcm_id'] = $ids['web_fcm_id'];
                        }
                        $web_registrationIDs[] = $webfcm_ids;
                    }
                    //for web end
                }

             $disk =fetch_current_file_manager();

                //if notification type is general
                if ($name == "general") {
                    if ($ext != '') {
                        if ($disk== "local_server") {
                            $image_url = base_url($data['image']);
                        } else if ($disk== "aws_s3") {
                            $image_url = fetch_cloud_front_url('notification', $data['image']);
                        }
                        $fcmMsg = array(
                            'title' => "$title",
                            'body' => "$message",
                            'type' => $name,
                            'type_id' => $data['type_id'],
                            'image' => $image_url,
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        );
                    } else {
                        $fcmMsg = array(
                            'title' => "$title",
                            'body' => "$message",
                            'type' => $name,
                            'type_id' => $data['type_id'],
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        );
                    }
                    $registrationIDs_chunks = array_chunk($registrationIDs, 1000);
                    $not_data =  send_notification($fcmMsg, $registrationIDs_chunks);
                    $web_not_data =  send_customer_web_notification($fcmMsg, $web_registrationIDs);
                    if ($not_data == false && $web_not_data == false) {
                        $response = [
                            'error' => false,
                            'message' => labels('send_notification_successfully', 'Send notification successfully'),
                            'csrfName' => csrf_token(),
                            'csrfHash' => csrf_hash(),
                        ];
                        return $this->response->setJSON($response);
                    } else {
                        return ErrorResponse("Some error occurred", true, [], [], 200, csrf_token(), csrf_hash());
                    }
                } else if ($name == "provider") {
                    $provider_builder = $this->db->table('partner_details');
                    $provider_data = $provider_builder->where('partner_id', $_POST['partner_id'])->get()->getResultArray();
                    if ($ext != '') {
                        if ($disk == "local_server") {
                            $image_url = base_url($data['image']);
                        } else if ($disk == "aws_s3") {
                            $image_url = fetch_cloud_front_url('notification', $data['image']);
                        }
                        $fcmMsg = array(
                            'title' => "$title",
                            'body' => "$message",
                            'type' => $name,
                            'provider_id' => $provider_data[0]['partner_id'],
                            'provider_name' => $provider_data[0]['company_name'],
                            'type_id' => $data['type_id'],
                            'image' => $image_url,
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        );
                    } else {
                        $fcmMsg = array(
                            'title' => "$title",
                            'body' => "$message",
                            'type' => $name,
                            'provider_id' => $data['type_id'],
                            'provider_name' => $provider_data[0]['company_name'],
                            'type_id' => $data['type_id'],
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        );
                    }
                    $registrationIDs_chunks = array_chunk($registrationIDs, 1000);
                    $not_data =  send_notification($fcmMsg, $registrationIDs_chunks);
                    $web_not_data =  send_customer_web_notification($fcmMsg, $web_registrationIDs);
                    return successResponse("Send notification successfully", false, [], [], 200, csrf_token(), csrf_hash());
                } elseif ($name == "category") {
                    $builder = $this->db->table('categories')->select('id,name,parent_id');
                    $category_data = $builder->where('id', $_POST['category_id'])->get()->getResultArray();
                    if ($ext != '') {
                        if ($disk == "local_server") {
                            $image_url = base_url($data['image']);
                        } else if ($disk == "aws_s3") {
                            $image_url = fetch_cloud_front_url('notification', $data['image']);
                        }
                        $fcmMsg = array(
                            'title' => "$title",
                            'body' => "$message",
                            'type' => $name,
                            'category_id' => $data['type_id'],
                            'parent_id' => $category_data[0]['parent_id'],
                            'category_name' => $category_data[0]['name'],
                            'type_id' => $data['type_id'],
                            'image' => $image_url,
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        );
                    } else {
                        $fcmMsg = array(
                            'title' => "$title",
                            'body' => "$message",
                            'type' => $name,
                            'category_id' => $data['type_id'],
                            'parent_id' => $category_data[0]['parent_id'],
                            'category_name' => $category_data[0]['name'],
                            'type_id' => $data['type_id'],
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        );
                    }
                    $registrationIDs_chunks = array_chunk($registrationIDs, 1000);
                    $not_data =  send_notification($fcmMsg, $registrationIDs_chunks);
                    $web_not_data =  send_customer_web_notification($fcmMsg, $web_registrationIDs);
                    return successResponse("Send notification successfully", false, $not_data, [], 200, csrf_token(), csrf_hash());
                } elseif ($name == "url") {
                    if ($ext != '') {
                        if ($disk == "local_server") {
                            $image_url = base_url($data['image']);
                        } else if ($disk == "aws_s3") {
                            $image_url = fetch_cloud_front_url('notification', $data['image']);
                        }
                        $fcmMsg = array(
                            'title' => "$title",
                            'body' => "$message",
                            'type' => $name,
                            'url' => $_POST['url'],
                            'type_id' => $data['type_id'],
                            'image' => $image_url,
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        );
                    } else {
                        $fcmMsg = array(
                            'title' => "$title",
                            'body' => "$message",
                            'type' => $name,
                            'url' => $_POST['url'],
                            'type_id' => $data['type_id'],
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        );
                    }
                    $registrationIDs_chunks = array_chunk($registrationIDs, 1000);
                    $not_data =  send_notification($fcmMsg, $registrationIDs_chunks);
                    $web_not_data =  send_customer_web_notification($fcmMsg, $web_registrationIDs);
                    return successResponse("Send notification successfully", false, $not_data, [], 200, csrf_token(), csrf_hash());
                }
            } else {
                return ErrorResponse("Some error occurred", true, [], [], 200, csrf_token(), csrf_hash());
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Notification.php - add_notification()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
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
            $data = $this->notification->list(false, $search, $limit, $offset, $sort, $order);
            return $data;
        } catch (\Throwable $th) {
    
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Notification.php - list()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function  delete_notification()
    {
        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            $result = checkModificationInDemoMode($this->superadmin);
            if ($result !== true) {
                return $this->response->setJSON($result);
            }
            $id = $this->request->getPost('user_id');
            $icons = fetch_details('notifications', ['id' => $id]);
            $image = ($icons[0] != '') ? $icons[0]['image'] : '';
            $db      = \Config\Database::connect();
            $builder = $db->table('notifications');
            if ($builder->delete(['id' => $id])) {
                $path = ($image != "") ? "public/uploads/notification/" . $image : '';
                if ($image != "") {
                    unlink($path);
                }
                return successResponse("Notification deleted successfully", false, [], [], 200, csrf_token(), csrf_hash());
            } else {
                return ErrorResponse("An error occured during deleting this item", true, [], [], 200, csrf_token(), csrf_hash());
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Notification.php - delete_notification()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
}
