<?php
namespace App\Models;
use CodeIgniter\Model;
class Email_model extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'emails';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    public function list($from_app = false, $search = '', $limit = 10, $offset = 0, $sort = 'id', $order = 'ASC', $where = [],  $whereIn = [], $orWhere_column = '', $orWhere_value = '')
    {
        $multipleWhere = '';
        $db      = \Config\Database::connect();
        $builder = $db->table('emails e');
        if ($search and $search != '') {
            $multipleWhere = [
                '`e.id`' => $search,
                '`e.subject`' => $search,
                '`e.type`' => $search,
            ];
        }
        $total  = $builder->select(' COUNT(e.id) as `total` ');
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
        $email_record = $builder->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        foreach ($email_record as $key => $email) {
            $operations = '
                <button class="btn btn-danger delete-email" data-id="' . $email['id'] . '" data-toggle="modal" data-target="#delete_modal" onclick="email_id(this)"title = "Delete the email"> <i class="fa fa-trash" aria-hidden="true"></i> </button> 
            ';
            $tempRow['id'] = $email['id'];
            $tempRow['subject'] = $email['subject'];
            $tempRow['content'] = $email['content'];
            $tempRow['type'] = $email['type'];
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
