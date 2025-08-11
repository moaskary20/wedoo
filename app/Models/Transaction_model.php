<?php
namespace App\Models;
use CodeIgniter\Model;
class Transaction_model extends Model
{
    protected $table = 'transactions';
    protected $primaryKey = 'id';
    protected $allowedFields = ['transaction_type', 'user_id', 'order_id', 'type', 'txn_id', 'amount', 'status', 'currency_code', 'message'];
    public function list_transactions($from_app = false, $search = '', $limit = 10, $offset = 0, $sort = 't.id', $order = 'DESC', $where = [], $where_in_key = '', $where_in_value = [])
    {
        $db      = \Config\Database::connect();
        $builder = $db->table('transactions t');
        $multipleWhere = [];
        $condition = $bulkData = $rows = $tempRow = [];
        if (isset($_GET['offset']))
            $offset = $_GET['offset'];
        if ((isset($search) && !empty($search) && $search != "") || (isset($_GET['search']) && $_GET['search'] != '')) {
            $search = (isset($_GET['search']) && $_GET['search'] != '') ? $_GET['search'] : $search;
            $multipleWhere = [
                '`t.id`' => $search,
                '`t.user_id`' => $search,
                '`t.transaction_type`' => $search,
                '`t.order_id`' => $search,
                '`t.type`' => $search,
                '`t.txn_id`' => $search,
                '`t.amount`' => $search,
                '`t.status`' => $search,
                '`t.currency_code`' => $search,
                '`t.message`' => $search,
                '`u.username`' => $search,
            ];
        }
        if (isset($_GET['limit'])) {
            $limit = $_GET['limit'];
        }
        if (isset($_GET['sort'])) {
            if ($_GET['sort'] == 't.id') {
                $sort = "t.id";
            } else {
                $sort = $_GET['sort'];
            }
        }
        if (isset($_GET['order'])) {
            $order = $_GET['order'];
        }
        $order_count = $builder->select('count(t.id) as total')
            ->join('users u', 'u.id=t.user_id');
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        if (isset($where_in_key) && !empty($wherwhere_in_key) && isset($where_in_value) && !empty($where_in_value)) {
            $builder->whereIn($where_in_key, $where_in_value);
        }
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->groupStart();
            $builder->orLike($multipleWhere);
            $builder->groupEnd();
        }
        $order_count = $builder->get()->getResultArray();
        $total = $order_count[0]['total'];
        $builder->select('u.username,t.*')
            ->join('users u', 'u.id=t.user_id');
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        if (isset($where_in_key) && !empty($wherwhere_in_key) && isset($where_in_value) && !empty($where_in_value)) {
            $builder->whereIn($where_in_key, $where_in_value);
        }
        if (isset($_GET['txn_provider']) && $_GET['txn_provider'] != '') {
            $builder->where('t.type', $_GET['txn_provider']);
        }
        if (isset($_GET['transaction_status']) && $_GET['transaction_status'] != '') {
            $builder->where('t.status', $_GET['transaction_status']);
        }
        if (isset($_GET['start_date']) && $_GET['start_date'] != '' && isset($_GET['end_date']) && $_GET['end_date'] != '') {
            $builder->where(["t.transaction_date >=" => $_GET['start_date'], "t.transaction_date <=" => $_GET['end_date']]);
        }
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->groupStart();
            $builder->orLike($multipleWhere);
            $builder->groupEnd();
        }
        $order_record = $builder->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        if (empty($order_record)) {
            $bulkData = array();
        } else {
            foreach ($order_record as $row) {
                $tempRow['id'] = $row['id'];
                $tempRow['user_id'] = $row['user_id'];
                $tempRow['partner_id'] = $row['partner_id'];
                $tempRow['name'] = $row['username'];
                $tempRow['type'] = $row['type'];
                $tempRow['txn_id'] = $row['txn_id'];
                $tempRow['transaction_type'] = $row['transaction_type'];
                $tempRow['amount'] = $row['amount'];
                $tempRow['currency_code'] = $row['currency_code'];
                $tempRow['status'] = $row['status'];
                $tempRow['created_at'] =  date("d-M-Y h:i A", strtotime($row['created_at']));
                if ($from_app) {
                    unset($tempRow['created_at']);
                }
                $rows[] = $tempRow;
            }
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
