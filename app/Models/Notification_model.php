<?php

namespace App\Models;

use CodeIgniter\Model;

class Notification_model extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'notifications';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = ['title', 'message', 'type', 'type_id', 'image', 'order_id', 'user_id', 'is_readed', 'notification_type', 'date_sent', 'target'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    public function list($from_app = false, $search = '', $limit = 10, $offset = 0, $sort = 'id', $order = 'ASC', $where = [],  $whereIn = [], $orWhere_column = '', $orWhere_value = '')
    {
        $multipleWhere = '';
        $db      = \Config\Database::connect();
        $builder = $db->table('notifications n');
        if ($search and $search != '') {
            $multipleWhere = [
                '`n.id`' => $search,
                '`n.title`' => $search,
                '`n.message`' => $search,
                '`n.type`' => $search,
                '`n.type_id`' => $search,
            ];
        }
        $total  = $builder->select(' COUNT(n.id) as `total` ');
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->orWhere($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        if (!empty($orWhere_column)) {
            $builder->orWhere($orWhere_column, $orWhere_value);
        }
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->orLike($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        if (isset($whereIn['user_id'])) {
            $userId = $whereIn['user_id'];
            $builder->where("JSON_UNQUOTE(JSON_EXTRACT(user_id, '$[0]'))", $userId);
            unset($whereIn['user_id']);
        }
        if (!empty($whereIn)) {
            foreach ($whereIn as $key => $value) {
                $builder->groupStart();
                $builder->whereIn($key, $value);
                $builder->groupEnd();
            }
        }
        $notification_count = $builder->get()->getResultArray();
        $total = $notification_count[0]['total'];
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->orLike($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        if (isset($whereIn['user_id'])) {
            $userId = $whereIn['user_id'];
            $builder->where("JSON_UNQUOTE(JSON_EXTRACT(user_id, '$[0]'))", $userId);
            unset($whereIn['user_id']);
        }
        if (!empty($whereIn)) {
            foreach ($whereIn as $key => $value) {
                $builder->groupStart();
                $builder->whereIn($key, $value);
                $builder->groupEnd();
            }
        }
        $notification_record = $builder->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();
        
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        $disk = fetch_current_file_manager();
        foreach ($notification_record as $key => $notification) {
            if ($from_app == false) {
                if (!empty($notification['image'])) {
                    if ($disk == "local_server") {
                        if (check_exists(base_url('public/uploads/notification/' . $notification['image']))) {

                            $image_url = base_url('public/uploads/notification/' . $notification['image']);
                        } else {
                            $image_url =  base_url('public/backend/assets/profiles/default.png');
                        }
                    } else if ($disk == "aws_s3") {
                        $image_url = fetch_cloud_front_url('notification', $notification['image']);
                    } else {
                        $image_url =  base_url('public/backend/assets/profiles/default.png');
                    }
                    $image = '<a  href="' . $image_url . '" data-lightbox="image-1"><img class="o-media__img images_in_card" src="' . $image_url . '" alt="' .     $notification['id'] . '"></a>';
                } else{
                    $image='';
                }    
            } else {
                if (!empty($row['image'])) {
                    if ($disk == "local_server") {
                        if (check_exists(base_url('public/uploads/notification/' . $notification['image']))) {
                            $image = base_url($notification['image']);
                        } else {
                            $image = '';
                        }
                    } else if ($disk == "aws_s3") {
                        $image = fetch_cloud_front_url('notification', $notification['image']);
                    } else {
                        $image = '';
                    }
                } else {
                    $image = '';
                }
            }
           
            $operations = '
                <button class="btn btn-danger delete-notification" data-id="' . $notification['id'] . '" data-toggle="modal" data-target="#delete_modal" onclick="notification_id(this)"title = "Delete the notification"> <i class="fa fa-trash" aria-hidden="true"></i> </button> 
            ';
            $tempRow['id'] = $notification['id'];
            $tempRow['title'] = $notification['title'];
            $tempRow['message'] = $notification['message'];
            $tempRow['type'] = $notification['type'];
            $tempRow['user_id'] = $notification['user_id'];
            $tempRow['type_id'] = $notification['type_id'];
            $tempRow['image'] = $image;
            $tempRow['order_id'] = $notification['order_id'];
            $tempRow['is_readed'] = $notification['is_readed'];
            $tempRow['date_sent'] = $notification['date_sent'];
            $tempRow['notification_type'] = $notification['notification_type'];
            $tempRow['operations'] = $operations;
            if ($from_app ==  true) {
                unset($tempRow['operations']);
            }
            $rows[] = $tempRow;
        }
        if ($from_app) {
            $response['total'] = $total;
            $response['data'] = $rows;
            return $response;
        } else {
            $bulkData['rows'] = $rows;
            return json_encode($bulkData);
        }
    }
}
