<?php

namespace App\Controllers\admin;

use App\Models\Chat_model;
use App\Models\Enquiries_model;

class Chats extends Admin
{
    protected $db; // Declare the $db property
    protected Chat_model $chat; 
    protected Enquiries_model $enquiry; 

    protected $validation; // Declare the $validation property
    public function __construct()
    {
        parent::__construct();
        $this->validation = \Config\Services::validation();
        $this->db      = \Config\Database::connect();
        $this->chat = new Chat_model();
        $this->enquiry = new Enquiries_model();
        helper('ResponceServices');
        helper('api');
    }
    public function index()
    {               
        $disk =fetch_current_file_manager();
                                                                                                                           
        try {
            if ($this->isLoggedIn && $this->userIsAdmin) {
                setPageInfo($this->data, labels('chat', 'Chat') . ' | ' . labels('admin_panel', 'Admin Panel'), 'chat');
                $db = \Config\Database::connect();
                $builder = $db->table('users u');
                $builder->select('u.*,u.image as profile_image, MAX(c.created_at) AS last_chat_date')
                    ->join('chats c', "(c.sender_id = u.id AND c.sender_type = 2)
                     OR (c.sender_id = u.id AND c.receiver_type = 0) 
                         OR (c.receiver_id = u.id AND c.receiver_type = 0)
                     OR (c.receiver_id = u.id AND c.receiver_type = 2) ")
                    ->where('c.booking_id', NULL)
                    ->where('c.receiver_type', 0)->orwhere('c.receiver_type', 2)
                    ->groupBy('u.id')
                    ->orderBy('last_chat_date', 'DESC');
                $customers_with_chats = $builder->get()->getResultArray();
                foreach ($customers_with_chats as $key => $row) {
                    if($disk=="local_server"){

                        $customers_with_chats[$key]['profile_image'] = base_url('public/backend/assets/profiles/' . $row['profile_image']);
                    }else if($disk=="aws_s3"){
                        $customers_with_chats[$key]['profile_image']=fetch_cloud_front_url('profile',$row['profile_image']);
                    }else{
                        $customers_with_chats[$key]['profile_image'] = base_url('public/backend/assets/profiles/' . $row['profile_image']);

                    }
                }



                foreach ($customers_with_chats as $key => $customer) {
                    $check_user = fetch_details('users_groups', ['user_id' => $customer['id'], 'group_id' => 2]);
                
                    if (empty($check_user)) {
                        unset($customers_with_chats[$key]); 
                    }
                }
                $this->data['customers'] = $customers_with_chats;


            
                $builder = $db->table('users u');
                $builder->select('u.*,pd.company_name as username, u.image as profile_image, MAX(c.created_at) AS last_chat_date')
                    ->join('chats c', "(c.sender_id = u.id AND c.sender_type = 1 AND c.receiver_type = 0) OR (c.receiver_id = u.id AND c.receiver_type = 1 AND c.sender_type = 0)")
                    ->join('partner_details pd', 'pd.partner_id = u.id')
                    ->groupBy('u.id')
                    ->orderBy('last_chat_date', 'DESC');
                $provider_with_chats = $builder->get()->getResultArray();
                foreach ($provider_with_chats as $key => $row) {
                    if (isset($row['profile_image'])) {
                        if($disk=="local_server"){

                            $provider_with_chats[$key]['profile_image'] = base_url('public/backend/assets/profiles/' . $row['profile_image']);
                        }else if($disk=="aws_s3"){
                            $provider_with_chats[$key]['profile_image']=fetch_cloud_front_url('profile',$row['profile_image']);
                        }else{
                            $provider_with_chats[$key]['profile_image'] = base_url('public/backend/assets/profiles/' . $row['profile_image']);
                        }
               
                    }
                }
                $this->data['providers'] = $provider_with_chats;
                $this->data['current_user_id'] = $this->userId;
                $chat_settings = get_settings('general_settings', true);
                $this->data['maxFilesOrImagesInOneMessage'] = $chat_settings['maxFilesOrImagesInOneMessage'] ?? 10;
                $this->data['maxFileSizeInBytesCanBeSent'] = $chat_settings['maxFileSizeInBytesCanBeSent'] ?? 20000000;
                $this->data['maxCharactersInATextMessage'] = $chat_settings['maxCharactersInATextMessage'] ?? 500;
                return view('backend/admin/template', $this->data);
            } else {
                return redirect('unauthorised');
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . ' -->  app/Controllers/admin/Chats.php - index()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function store_chat()
    {

        if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
       
            $response['error'] = true;
            $response['message'] = DEMO_MODE_ERROR;
            $response['csrfName'] = csrf_token();
            $response['csrfHash'] = csrf_hash();
            return $response;
        }     
        try {
            $message = isset($_POST['message']) ? trim($_POST['message']) : '';
            
            
            $sender_id = $this->userId;
            $receiver_id = isset($_POST['receiver_id']) ? $_POST['receiver_id'] : '';
            $user_type_for_send_message = $_POST['user_type_for_send_message'];
            $attachment_image = null;
            $is_file = false;
            if (!empty($_FILES['attachment']['name'])) {
                $attachment_image = $_FILES['attachment'];
                $is_file = true;
            }
            if ($user_type_for_send_message == "provider") {
                $data['receiver_type'] = 1;
                $receiver_type = 1;

                $enquiry = fetch_details('enquiries', ['customer_id' => null, 'userType' => 1, 'booking_id' => NULL, 'provider_id' => $receiver_id]);
                if (empty($enquiry[0])) {
                    $user = fetch_details('users', ['id' => $sender_id], ['username'])[0];
                    $data['title'] =  $user['username'] . '_query';
                    $data['status'] =  1;
                    $data['userType'] =  1;
                    $data['customer_id'] = null;
                    $data['provider_id'] = $sender_id;
                    $data['date'] =  now();
                    $store = insert_details($data, 'enquiries');
                    $e_id = $store['id'];
                } else {
                    $e_id = $enquiry[0]['id'];
                }

                // $e_id = add_enquiry_for_chat($user_type_for_send_message, $receiver_id);
            } elseif ($user_type_for_send_message == "customer") {
                $data['receiver_type'] = 2;
                $receiver_type = 2;
                $enquiry = fetch_details('enquiries', ['customer_id' => $receiver_id, 'userType' => 2, 'booking_id' => NULL, 'provider_id' => NULL]);
                if (empty($enquiry[0])) {
                    $customer = fetch_details('users', ['id' => $receiver_id], ['username'])[0];
                    $data['title'] =  $customer['username'] . '_query';
                    $data['status'] =  1;
                    $data['userType'] =  2;
                    $data['customer_id'] = $receiver_id;
                    $data['provider_id'] = NULL;
                    $data['date'] =  now();
                    $store = insert_details($data, 'enquiries');
                    $e_id = $store['id'];
                } else {
                    $e_id = $enquiry[0]['id'];
                }
            }
            $data = insert_chat_message_for_chat($sender_id, $receiver_id, $message, $e_id, 0, $receiver_type, date('Y-m-d H:i:s'), $is_file, $attachment_image);
            $last_date = getLastMessageDateFromChat($e_id);
            if ($data) {
                if (!empty($data)) {
                    if ($user_type_for_send_message == "provider") {
                        $new_data = getSenderReceiverDataForChatNotification($sender_id, $receiver_id, $data['id'], $last_date, 'admin');
                        send_panel_chat_notification('Check New Messages', $message, $receiver_id, '', 'new_chat', $new_data);
                        send_app_chat_notification('Provider Support', $message, $receiver_id, '', 'new_chat', $new_data);
                    } else if ($user_type_for_send_message == "customer") {
                        $new_data = getSenderReceiverDataForChatNotification($sender_id, $receiver_id, $data['id'], $last_date, 'admin');
                        send_app_chat_notification('Customer Support', $message, $receiver_id, '', 'new_chat', $new_data);
                        send_customer_web_chat_notification('Customer Support', $message, $receiver_id, '', 'new_chat', $new_data);
                    }
                    return successResponse("Chat successfully", false, $data, [], 200, csrf_token(), csrf_hash());
                } else {
                    return ErrorResponse("Chat not found after saving", true, [], [], 200, csrf_token(), csrf_hash());
                }
            } else {
                return ErrorResponse("Please try again....", true, [], [], 200, csrf_token(), csrf_hash());
            }
        } catch (\Throwable $th) {
            throw $th;
          
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Chats.php - store_chat()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function getAllMessage()
    {


        // if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
        //     return ErrorResponse("Modification in demo version is not allowed", true, [], [], 200, csrf_token(), csrf_hash());
           
        // }     
        try {
            $user_type = $_POST['user_type'];
            if ($user_type == 'provider') {
                $enquiry = fetch_details('enquiries', ['customer_id' => null, 'userType' => 1, 'booking_id' => NULL, 'provider_id' => $_POST['receiver_id']]);
                if (empty($enquiry[0])) {
                    $provider = fetch_details('users', ['id' => $_POST['receiver_id']], ['username'])[0];
                    $data['title'] =  $provider['username'] . '_query';
                    $data['status'] =  1;
                    $data['userType'] =  1;
                    $data['customer_id'] = NULL;
                    $data['provider_id'] = $_POST['receiver_id'];
                    $data['date'] =  now();
                    $store = insert_details($data, 'enquiries');
                    $e_id = $store['id'];
                } else {
                    $e_id = $enquiry[0]['id'];
                }
            } else if ($user_type == "customer") {
                $enquiry = fetch_details('enquiries', ['customer_id' =>  $_POST['receiver_id'], 'userType' => 2, 'booking_id' => NULL, 'provider_id' => NULL]);
                if (empty($enquiry[0])) {
                    $provider = fetch_details('users', ['id' => $_POST['receiver_id']], ['username'])[0];
                    $data['title'] =  $provider['username'] . '_query';
                    $data['status'] =  1;
                    $data['userType'] =  2;
                    $data['customer_id'] =  $_POST['receiver_id'];
                    $data['provider_id'] = NULL;
                    $data['date'] =  now();
                    $store = insert_details($data, 'enquiries');
                    $e_id = $store['id'];
                } else {
                    $e_id = $enquiry[0]['id'];
                }
            } else {
                $e_id = 0;
            }
            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
            $receiver_id = $_POST['receiver_id'];
            $data = $this->chat->chat_list($limit, $offset, $sort, $order, $e_id = $e_id, ['e_id' => $e_id], [], $search, false, $receiver_id, $user_type);
            return $data;
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Chats.php - getAllMessage()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function get_customers()
    {
        try {
            $db = \Config\Database::connect();
            $builder = $db->table('users u');
            $builder->select('u.*,u.image as profile_image,ug.group_id')
                ->join('users_groups ug', 'ug.user_id = u.id')
                ->where('ug.group_id', 2);
            $search = $this->request->getPost('search');
            $users = [];
            if ($search != "") {
                $builder->groupStart()
                    ->like('u.id', $search)
                    ->orLike('u.username', $search)
                    ->orLike('u.email', $search)
                    ->orLike('u.phone', $search)
                    ->groupEnd();
                $users = $builder->get()->getResultArray();
            } else {
                $db = \Config\Database::connect();
                $builder = $db->table('chats c');
                $builder->distinct()->select('c.e_id');
                $customer_chat = $builder->get()->getResultArray();
                $e_ids = [];
                $users = [];
                foreach ($customer_chat as $row) {
                    $e_ids[] = $row['e_id'];
                }
                if (!empty($e_ids)) {
                    $customer_ids = fetch_chat_ids('enquiries', 'customer', [], ['customer_id'], '', 0, 'id', 'ASC', ['id' => $e_ids],);
                    $db = \Config\Database::connect();
                    $builder = $db->table('users u');
                    $builder->select('u.*,u.image as profile_image, ug.group_id')
                        ->join('users_groups ug', 'ug.user_id = u.id')
                        ->where('ug.group_id', 2)
                        ->whereIn('u.id', $customer_ids);
                    $users = $builder->get()->getResultArray();
                }
            }
            foreach ($users as $key => $row) {
                $users[$key]['profile_image'] = base_url('public/backend/assets/profiles/' . $row['profile_image']);
            }
            return json_encode($users);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Chats.php - get_customers()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function get_providers()
    {
        try {
            $db = \Config\Database::connect();
            $builder = $db->table('users u');
            $builder->select('u.id,ug.group_id,pd.company_name as username,pd.id as partner_id ,u.image as profile_image,u.phone,u.country_code')
                ->join('users_groups ug', 'ug.user_id = u.id')
                ->join('partner_details pd', 'pd.partner_id = u.id')
                ->where('ug.group_id', 3);
            $search = $this->request->getPost('search');
            $users = [];
            if ($search != "") {
                $builder->groupStart()
                    ->like('u.id', $search)
                    ->orLike('u.username', $search)
                    ->orLike('pd.company_name', $search)
                    ->orLike('u.email', $search)
                    ->orLike('u.phone', $search)
                    ->groupEnd();
                $users = $builder->get()->getResultArray();
                foreach ($users as $key => $row) {
                    if (check_exists(base_url('public/backend/assets/profiles/' . $row['profile_image'])) || check_exists(base_url('/public/uploads/users/partners/' . $row['profile_image'])) || check_exists($row['profile_image'])) {
                        if (filter_var($row['profile_image'], FILTER_VALIDATE_URL)) {
                            $users[$key]['profile_image'] = $row['profile_image'];
                        } else {
                            $users[$key]['profile_image'] = (file_exists(FCPATH . 'public/backend/assets/profiles/' . $row['profile_image'])) ? base_url('public/backend/assets/profiles/' . $row['profile_image']) : ((file_exists(FCPATH . $row['profile_image'])) ? base_url($row['profile_image']) : ((!file_exists(FCPATH . "public/uploads/users/partners/" . $row['profile_image'])) ? base_url("public/backend/assets/profiles/default.png") : base_url("public/uploads/users/partners/" . $row['profile_image'])));
                        }
                    }
                }
            } else {
                $db = \Config\Database::connect();
                $builder = $db->table('chats c');
                $builder->distinct()->select('c.e_id');
                $provider_chat = $builder->where("(c.sender_id = u.id AND c.sender_type = 1) OR (c.receiver_id = u.id AND c.receiver_type = 1)")
                    ->join('users u', 'u.id = c.sender_id OR u.id = c.receiver_id')->get()->getResultArray();
                $e_ids = [];
                $users = [];
                foreach ($provider_chat as $row) {
                    $e_ids[] = $row['e_id'];
                }
                if (!empty($e_ids)) {
                    $provider_ids = fetch_chat_ids('enquiries', 'provider', [], ['provider_id'], '', 0, 'id', 'ASC', ['id' => $e_ids]);
                    $db = \Config\Database::connect();
                    $builder = $db->table('users u');
                    $builder->select('u.id,ug.group_id,pd.company_name as username,pd.id as partner_id ,u.image as profile_image ,u.phone,u.country_code')
                        ->join('users_groups ug', 'ug.user_id = u.id')
                        ->join('partner_details pd', 'pd.partner_id = u.id')
                        ->where('ug.group_id', 3)
                        ->whereIn('u.id', $provider_ids);
                    $users = $builder->get()->getResultArray();
                }
            }
            foreach ($users as $key => $row) {
                if (check_exists(base_url('public/backend/assets/profiles/' . $row['profile_image'])) || check_exists(base_url('/public/uploads/users/partners/' . $row['profile_image'])) || check_exists($row['profile_image'])) {
                    if (filter_var($row['profile_image'], FILTER_VALIDATE_URL)) {
                        $users[$key]['profile_image'] = $row['profile_image'];
                    } else {
                        $users[$key]['profile_image'] = (file_exists(FCPATH . 'public/backend/assets/profiles/' . $row['profile_image'])) ? base_url('public/backend/assets/profiles/' . $row['profile_image']) : ((file_exists(FCPATH . $row['profile_image'])) ? base_url($row['profile_image']) : ((!file_exists(FCPATH . "public/uploads/users/partners/" . $row['profile_image'])) ? base_url("public/backend/assets/profiles/default.png") : base_url("public/uploads/users/partners/" . $row['profile_image'])));
                    }
                }
            }
            return json_encode($users);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Chats.php - get_providers()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
}
