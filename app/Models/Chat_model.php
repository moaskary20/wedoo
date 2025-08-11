<?php
namespace App\Models;
use \Config\Database;
use CodeIgniter\Model;
use  app\Controllers\BaseController;
class Chat_model  extends Model
{
    protected $table = 'chats';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id', 'sender_id', 'receiver_id', 'message', 'file', 'file_type', 'receiver_type', 'sender_type', 'e_id'];
    public function chat_list($limit = 10, $offset = 0, $sort = 'id', $order = 'ASC', $e_id = null, $where = [], $orwhere = [], $search = '', $from_app = false, $receiver_id = null, $user_type = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('chats c');
        $multipleWhere = [];
        $bulkData = $rows = $tempRow = [];
        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : $limit;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : $offset;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        if ((isset($search) && !empty($search) && $search != "") || (isset($_GET['search']) && $_GET['search'] != '')) {
            $search = (isset($_GET['search']) && $_GET['search'] != '') ? $_GET['search'] : $search;
            $multipleWhere = [
                '`c.message`' => $search,
            ];
        }
        $chat_count = $builder->select('count(c.id) as total')
            ->join('users u', 'u.id=c.receiver_id');
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->groupStart();
            $builder->orLike($multipleWhere);
            $builder->groupEnd();
        }
        $chat_count = $builder->get()->getRowArray();
        $total = $chat_count['total'];
        if ($user_type == "provider") {
            $builder->select('c.id,c.sender_id,c.receiver_id,c.message,c.file,c.file_type,c.created_at,c.updated_at,u.username,u.image as profile_image,
            pd.company_name as receiver_name,r.image as receiver_profile_image,')
                ->join('users u', 'u.id=c.sender_id')
                ->join('users r', 'r.id = c.receiver_id')
                ->join('partner_details pd', '(pd.partner_id = c.receiver_id) OR (pd.partner_id = c.sender_id)');
        } else if ($user_type == "customer") {
            $builder->select('c.id,c.sender_id,c.receiver_id,c.booking_id,c.message,c.file,c.file_type,c.created_at,c.updated_at,u.username,u.image as profile_image,r.username as receiver_name,r.image as receiver_profile_image,')
                ->join('users u', 'u.id=c.sender_id');
            $builder->join('users r', 'r.id = c.receiver_id');
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->groupStart();
            $builder->orLike($multipleWhere);
            $builder->groupEnd();
        }
        $builder->orderBy($sort, $order);
        $chat_record = $builder->get()->getResultArray();
        $db = \Config\Database::connect();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        $disk =fetch_current_file_manager();

        if (empty($chat_record)) {
            if ($user_type == "customer") {
                $users = $db->table('users')->select('id,username,image')->where('id', $receiver_id)->get()->getResultArray();
            } elseif ($user_type == "provider") {
                $users = $db->table('users u')->select('u.id,u.image,pd.company_name as username')->where('u.id', $receiver_id)->join('partner_details pd', 'pd.partner_id = u.id')->get()->getResultArray();
            }
            $users[0]['receiver_id'] = $users[0]['id'];
            $users[0]['receiver_name'] = $users[0]['username'];
            if($disk == 'local_server'){
                $users[0]['receiver_profile_image'] = base_url('public/backend/assets/profiles/' . $users[0]['image']);
            } else if($disk == 'aws_s3'){
                $users[0]['receiver_profile_image'] =fetch_cloud_front_url('profile', $users[0]['image']);
            }else{
                 $users[0]['receiver_profile_image'] = base_url('public/backend/assets/profiles/' . $users[0]['image']);
            }
            $bulkData['receiver_id'] = $receiver_id;
            $bulkData['receiver_name'] = $users[0]['username'];
            if ($user_type == "provider") {
                if (isset($users[0]['image'])) {

                    if($disk=="local_server"){
                        $imagePath = $users[0]['image'];
                    $bulkData['receiver_profile_image'] = fix_provider_path($imagePath);
                    }else if($disk=="aws_s3"){
                        $bulkData['receiver_profile_image'] = fetch_cloud_front_url('profile',$users[0]['image']);

                    }else{
                        $imagePath = $users[0]['image'];
                        $bulkData['receiver_profile_image'] = fix_provider_path($imagePath);
                    }
                    
                }
            } else if ($user_type == "customer") {
                if($disk=="local_server"){
                    $imagePath = $users[0]['image'];
                $bulkData['receiver_profile_image'] = fix_provider_path($imagePath);
                }else if($disk=="aws_s3"){
                    $bulkData['receiver_profile_image'] = fetch_cloud_front_url('profile',$users[0]['image']);

                }else{
                    $imagePath = $users[0]['image'];
                    $bulkData['receiver_profile_image'] = fix_provider_path($imagePath);
                }
                // $bulkData['receiver_profile_image'] =  base_url('public/backend/assets/profiles/' . $users[0]['image']);
            }
            $bulkData['rows'] = $users;
            return json_encode($bulkData);
        } else {
            foreach ($chat_record as $row) {
                $tempRow['id'] = $row['id'];
                $tempRow['sender_id'] = $row['sender_id'];
                $tempRow['receiver_id'] = $row['receiver_id'];
                $tempRow['message'] = $row['message'];
                $tempRow['created_at'] = $row['created_at'];
                $tempRow['updated_at'] =  $row['updated_at'];
                $tempRow['sender_name'] = $row['username'];
                $tempRow['receiver_name'] = $row['receiver_name'];
                $tempRow['booking_id'] = $row['booking_id'] ?? null;

                if($disk=="local_server"){

                    $tempRow['receiver_profile_image'] = base_url('public/backend/assets/profiles/' . $row['receiver_profile_image']);
                }else if($disk=="aws_s3"){
                    $tempRow['receiver_profile_image'] = fetch_cloud_front_url('profile',$row['receiver_profile_image']);

                }else{
                    $tempRow['receiver_profile_image'] = fetch_cloud_front_url('profile',$row['receiver_profile_image']);

                }

                if($disk=="local_server"){
                
                    $tempRow['profile_image'] =  base_url('public/backend/assets/profiles/' . $row['profile_image']);
                
                }else if($disk=="aws_s3"){
                    $tempRow['profile_image'] = fetch_cloud_front_url('profile',$row['profile_image']);
                }else{
                    $tempRow['profile_image'] =  base_url('public/backend/assets/profiles/' . $row['profile_image']);

                }
                if (isset($row['profile_image'])) {
                    if($disk=="local_server"){
                

                        $imagePath = $row['profile_image'];
                        $tempRow['profile_image'] = fix_provider_path($imagePath);
                    }else if($disk=="aws_s3"){
                        $tempRow['profile_image'] = fetch_cloud_front_url('profile',$row['profile_image']);
                    }else{
                        $tempRow['profile_image'] =  base_url('public/backend/assets/profiles/' . $row['profile_image']);
    
                    }

                }
                $tempRow['file_type'] =  $row['file_type'];

                if (!empty($row['file'])) {
                    $tempRow['file'] = []; // Initialize the array to store the formatted data
                    $decodedFiles = json_decode($row['file'], true); // Decode the JSON string into an array
                    
                    foreach ($decodedFiles as $data) {

                        if($disk=="local_server"){
                            $file= base_url('public/uploads/chat_attachment/' . $data['file']);
                        }else if($disk=="aws_s3"){
                            $file=fetch_cloud_front_url('chat_attachment',$data['file']);
                        }else{
                            $file= base_url('public/uploads/chat_attachment/' . $data['file']);

                        }
                        $tempRow['file'][] = [
                            'file' => $file,
                            'file_type' => $data['file_type'],
                            'file_name' => $data['file_name'],
                            'file_size' => $data['file_size'],
                        ];
                    }
                    
                } else {
                    $tempRow['file'] = is_array($row['file']) ? [] : "";
                }
                $rows[] = $tempRow;
            }
            $bulkData['rows'] = $rows;
            if ($user_type == "customer") {
                $users = $db->table('users')->select('id,username,image')->where('id', $receiver_id)->get()->getResultArray();
            } elseif ($user_type == "provider") {
                $users = $db->table('users u')->select('u.id,u.image,pd.company_name as username')->where('u.id', $receiver_id)->join('partner_details pd', 'pd.partner_id = u.id')->get()->getResultArray();
            }
            $bulkData['receiver_id'] = $receiver_id;
            $bulkData['receiver_name'] = $users[0]['username'];
            if ($user_type == "provider") {
                if (isset($users[0]['image'])) {
                    if( $disk=="local_server"){
                        $imagePath = $users[0]['image'];
                        $bulkData['receiver_profile_image'] = fix_provider_path($imagePath);
                    }else if($disk=="aws_s3"){
                        $bulkData['receiver_profile_image'] = fetch_cloud_front_url('profile',$users[0]['image']);
                    }else{
                        $imagePath = $users[0]['image'];
                        $bulkData['receiver_profile_image'] = fix_provider_path($imagePath);
                    }
                    
                }
            } else if ($user_type == "customer") {
                if( $disk=="local_server"){
                    $imagePath = $users[0]['image'];
                    $bulkData['receiver_profile_image'] = fix_provider_path($imagePath);
                }else if($disk=="aws_s3"){
                    $bulkData['receiver_profile_image'] = fetch_cloud_front_url('profile',$users[0]['image']);
                }else{
                    $imagePath = $users[0]['image'];
                    $bulkData['receiver_profile_image'] = fix_provider_path($imagePath);
                }
            }
            if ($from_app) {
                $data['total'] = $total;
                $data['data'] = $rows;
                return $data;
            } else {
                return json_encode($bulkData);
            }
        }
    }
    public function provider_booking_chat_list(
        $limit = 10,
        $offset = 0,
        $sort = 'id',
        $order = 'ASC',
        $e_id = null,
        $where = [],
        $orwhere = [],
        $search = '',
        $from_app = false,
        $receiver_id = null,
        $user_type = null,
        $booking_id = null,
        $provider_id = null
    ) {
        $db = \Config\Database::connect();
        $builder = $db->table('chats c');
        $multipleWhere = [];
        $bulkData = $rows = $tempRow = [];
        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : $limit;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : $offset;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        if ((isset($search) && !empty($search) && $search != "") || (isset($_GET['search']) && $_GET['search'] != '')) {
            $search = (isset($_GET['search']) && $_GET['search'] != '') ? $_GET['search'] : $search;
            $multipleWhere = [
                '`c.message`' => $search,
            ];
        }
        $chat_count = $builder->select('count(c.id) as total')
            ->join('users u', 'u.id=c.receiver_id');
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->groupStart();
            $builder->orLike($multipleWhere);
            $builder->groupEnd();
        }
        $chat_count = $builder->get()->getRowArray();
        $total = $chat_count['total'];
        $builder->select('c.id,c.sender_id,c.receiver_id,c.booking_id,c.message,c.file,c.file_type,c.created_at,c.updated_at,u.username,u.image as profile_image,r.username as receiver_name,r.image as receiver_profile_image,')
            ->join('users u', 'u.id=c.sender_id')
            ->join('users r', 'r.id = c.receiver_id');
        $builder->where('c.booking_id', $booking_id)
            ->where('c.receiver_id', $provider_id);
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->groupStart();
            $builder->orLike($multipleWhere);
            $builder->groupEnd();
        }
        $builder->orderBy($sort, $order);
        $chat_record = $builder->get()->getResultArray();
        $db = \Config\Database::connect();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        $disk =fetch_current_file_manager();

        if (empty($chat_record)) {
            if ($user_type == "customer") {
                $users = $db->table('users')->select('id,username,image')->where('id', $receiver_id)->get()->getResultArray();
            } elseif ($user_type == "provider") {
                $users = $db->table('users u')->select('u.id,u.image,pd.company_name as username')->where('u.id', $receiver_id)->join('partner_details pd', 'pd.partner_id = u.id')->get()->getResultArray();
            }
            $users[0]['receiver_id'] = $users[0]['id'];
            $users[0]['receiver_name'] = $users[0]['username'];

            if($disk=="local_server"){

                $users[0]['receiver_profile_image'] = base_url('public/backend/assets/profiles/' . $users[0]['image']);
            }else if($disk=="aws_s3"){
                
                $users[0]['receiver_profile_image'] =fetch_cloud_front_url('profile', $users[0]['image']);
            }else{
                $users[0]['receiver_profile_image'] = base_url('public/backend/assets/profiles/' . $users[0]['image']);

            }
            $bulkData['receiver_id'] = $receiver_id;
            $bulkData['receiver_name'] = $users[0]['username'];
            if ($user_type == "provider") {
                if (isset($users[0]['image'])) {
                    if($disk=="local_server"){

                        $imagePath = $users[0]['image'];
                        $bulkData['receiver_profile_image'] = fix_provider_path($imagePath);
                    }else if($disk=="aws_s3"){
                        $bulkData['receiver_profile_image'] = fetch_cloud_front_url('profile',$users[0]['image']);
                    }else{
                        $imagePath = $users[0]['image'];
                        $bulkData['receiver_profile_image'] = fix_provider_path($imagePath);
                    }
                }
            } else if ($user_type == "customer") {
                if($disk=="local_server"){

                    $imagePath = $users[0]['image'];
                    $bulkData['receiver_profile_image'] = fix_provider_path($imagePath);
                }else if($disk=="aws_s3"){
                    $bulkData['receiver_profile_image'] = fetch_cloud_front_url('profile',$users[0]['image']);
                }else{
                    $imagePath = $users[0]['image'];
                    $bulkData['receiver_profile_image'] = fix_provider_path($imagePath);
                }
                // $bulkData['receiver_profile_image'] =  base_url('public/backend/assets/profiles/' . $users[0]['image']);
            }
            $bulkData['rows'] = $users;
            return json_encode($bulkData);
        } else {
            foreach ($chat_record as $row) {
                $tempRow['id'] = $row['id'];
                $tempRow['sender_id'] = $row['sender_id'];
                $tempRow['receiver_id'] = $row['receiver_id'];
                $tempRow['message'] = $row['message'];
                $tempRow['created_at'] = $row['created_at'];
                $tempRow['updated_at'] =  $row['updated_at'];
                $tempRow['sender_name'] = $row['username'];
                $tempRow['receiver_name'] = $row['receiver_name'];
                $tempRow['booking_id'] = $row['booking_id'] ?? null;
                if($disk=="local_server"){

                    $tempRow['receiver_profile_image'] = base_url('public/backend/assets/profiles/' . $row['receiver_profile_image']);
                }else if($disk=="aws_s3"){
                    $tempRow['receiver_profile_image'] = fetch_cloud_front_url('profile',$row['receiver_profile_image']);
                }else{
                    $tempRow['receiver_profile_image'] = base_url('public/backend/assets/profiles/' . $row['receiver_profile_image']);
                }
                // $tempRow['receiver_profile_image'] = base_url('public/backend/assets/profiles/' . $row['receiver_profile_image']);
                // $tempRow['profile_image'] =  base_url('public/backend/assets/profiles/' . $row['profile_image']);

                
                if (isset($row['profile_image'])) {
                
                    if($disk=="local_server"){

                        $imagePath = $row['profile_image'];
                        $tempRow['profile_image'] = fix_provider_path($imagePath);
                    }else if($disk=="aws_s3"){

                        $tempRow['profile_image'] = fetch_cloud_front_url('profile',$row['profile_image']);

                    }  else{
                        
                        $imagePath = $row['profile_image'];
                        $tempRow['profile_image'] = fix_provider_path($imagePath);
                    }              }
                if (!empty($row['file'])) {
                    $decodedFiles = json_decode($row['file'], true); // Decode the JSON string into an array
                    $row['file'] = []; // Initialize the array to store the formatted data
                    
                    foreach ($decodedFiles as $data) {
                        if($disk=="local_server"){
                            $file= base_url('public/uploads/chat_attachment/' . $data['file']);
                        }else if($disk=="aws_s3"){
                            $file= fetch_cloud_front_url('chat_attachment',$data['file']);
                        }else{
                            $file= base_url('public/uploads/chat_attachment/' . $data['file']);

                        }
                        $row['file'][] = [
                            'file' => $file,
                            'file_type' => $data['file_type'],
                            'file_name' => $data['file_name'],
                            'file_size' => $data['file_size'],
                        ];
                    }
                    
                } else {
                    $row['file'] = is_array($row['file']) ? [] : "";
                }
                $tempRow['file_type'] =  $row['file_type'];
                $rows[] = $tempRow;
            }
            $bulkData['rows'] = $rows;
            if ($user_type == "customer") {
                $users = $db->table('users')->select('id,username,image')->where('id', $receiver_id)->get()->getResultArray();
            } elseif ($user_type == "provider") {
                $users = $db->table('users u')->select('u.id,u.image,pd.company_name as username')->where('u.id', $receiver_id)->join('partner_details pd', 'pd.partner_id = u.id')->get()->getResultArray();
            }
            $bulkData['receiver_id'] = $receiver_id;
            $bulkData['receiver_name'] = $users[0]['username'];
            if ($user_type == "provider") {
                if (isset($users[0]['image'])) {
                    if($disk=="local_server"){

                        $imagePath = $users[0]['image'];
                        $bulkData['receiver_profile_image'] = fix_provider_path($imagePath);
                    }else if($disk=="aws_s3"){
                        $bulkData['receiver_profile_image'] =fetch_cloud_front_url('profile', $users[0]['image']);
                    }else{
                        $imagePath = $users[0]['image'];
                        $bulkData['receiver_profile_image'] = fix_provider_path($imagePath);
                    }
                }
            } else if ($user_type == "customer") {
                if($disk=="local_server"){
                     $bulkData['receiver_profile_image'] =  base_url('public/backend/assets/profiles/' . $users[0]['image']);
                }else if($disk=="aws_s3"){
                    $bulkData['receiver_profile_image'] =  fetch_cloud_front_url('profile', $users[0]['image']);
                }else{
                    $bulkData['receiver_profile_image'] =  base_url('public/backend/assets/profiles/' . $users[0]['image']);
                }
            }
            if ($from_app) {
                $data['total'] = $total;
                $data['data'] = $rows;
                return $data;
            } else {
                return json_encode($bulkData);
            }
        }
    }
}
