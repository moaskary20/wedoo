<?php
namespace App\Models;
use CodeIgniter\Model;
class Admin_contact_query extends Model
{
    public function list($where, $is_admin_panel, $from_app = false, $limit = 10, $offset = 0, $sort = 'id', $order = 'DESC', $search = '')
    {
        $db      = \Config\Database::connect();
        $builder = $db->table('admin_contact_query a');
        $multipleWhere = [];
        $condition = $bulkData = $rows = $tempRow = [];
        if ((isset($search) && !empty($search) && $search != "") || (isset($_GET['search']) && $_GET['search'] != '')) {
            $search = (isset($_GET['search']) && $_GET['search'] != '') ? $_GET['search'] : $search;
            $multipleWhere = [
                'a.id' => $search,
                'a.email' => $search,
                'a.name' => $search,
                'a.message' => $search,
                'a.subject' => $search,
            ];
        }
        if (isset($_GET['offset']))
            $offset = $_GET['offset'];
        if (isset($_GET['limit'])) {
            $limit = $_GET['limit'];
        }
        if (isset($_GET['sort'])) {
            if ($_GET['sort'] == 'pc.id') {
                $sort = "a.id";
            } else {
                $sort = $_GET['sort'];
            }
        }
        if (isset($_GET['order'])) {
            $order = $_GET['order'];
        }
        $count =  $builder->select(' COUNT(a.id) as `total` ');
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->groupStart();
            $builder->orLike($multipleWhere);
            $builder->groupEnd();
        }
        $count = $builder->get()->getResultArray();
        $total = $count[0]['total'];
        $builder->select('a.*');
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->groupStart();
            $builder->orLike($multipleWhere);
            $builder->groupEnd();
        }
        $admin_contact_query = $builder->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        foreach ($admin_contact_query as $row) {
            $tempRow = [
                'id' => $row['id'],
                'username' => $row['name'],
                'email' => $row['email'],
                'name' => $row['name'],
                'message' => $row['message'],
                'subject' => $row['subject'],
            ];
            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        if ($from_app) {
            return $rows;
        } else {
            return json_encode($bulkData);
        }
    }
}
