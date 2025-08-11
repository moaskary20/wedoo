<?php
namespace App\Models;
use CodeIgniter\Model;
class Tax_model extends Model
{
    protected $table = 'taxes';
    protected $primaryKey = 'id';
    protected $allowedFields = ['title', 'percentage', 'status'];
    public function list($from_app = false, $search = '', $limit = 10, $offset = 0, $sort = 'id', $order = 'ASC', $where = [])
    {
        $db      = \Config\Database::connect();
        $builder = $db->table('taxes');
        $multipleWhere = [];
        $condition = $bulkData = $rows = $tempRow = [];
        if (isset($_GET['offset']))
            $offset = $_GET['offset'];
        if ((isset($search) && !empty($search) && $search != "") || (isset($_GET['search']) && $_GET['search'] != '')) {
            $search = (isset($_GET['search']) && $_GET['search'] != '') ? $_GET['search'] : $search;
            $multipleWhere = [
                '`id`' => $search,
                '`title`' => $search,
                '`percentage`' => $search,
                '`status`' => $search
            ];
        }
        if (isset($_GET['limit'])) {
            $limit = $_GET['limit'];
        }
        $sort = "id";
        if (isset($_GET['sort'])) {
            if ($_GET['sort'] == 'id') {
                $sort = "id";
            } else {
                $sort = $_GET['sort'];
            }
        }
        $order = "ASC";
        if (isset($_GET['order'])) {
            $order = $_GET['order'];
        }
        if ($from_app) {
            $where['status'] = 1;
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->groupStart();
            $builder->orLike($multipleWhere);
            $builder->groupEnd();
        }
        $builder->select('COUNT(id) as `total` ');
        $order_count = $builder->get()->getResultArray();
        $total = $order_count[0]['total'];
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->groupStart();
            $builder->orLike($multipleWhere);
            $builder->groupEnd();
        }
        $builder->select('*');
        $taxes = $builder->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        foreach ($taxes as $row) {
            $status =  ($row['status'] == 1) ? 'Enable' : 'Disable';
            $tempRow['id'] = $row['id'];
            $tempRow['title'] = $row['title'];
            $tempRow['percentage'] = $row['percentage'];
            if ($from_app == false) {
                $tempRow['title'] = $row['title'];
                $tempRow['percentage'] = $row['percentage'];
                $tempRow['status'] = ($row['status'] == 1) ? "<label class='badge badge-success'>Active</label>" : "<label class='badge badge-danger'>Deactive</label>";
            }
            $rows[] = $tempRow;
        }
        if ($from_app) {
            $data['total'] = $total;
            $data['data'] = $rows;
            return $data;
        } else {
            $bulkData['rows'] = $rows;
            return json_encode($bulkData);
        }
    }
}
