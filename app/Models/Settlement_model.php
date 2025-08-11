<?php
namespace App\Models;
use CodeIgniter\Model;
class Settlement_model extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'cities';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'provider_id', 'message', 'status', 'amount', 'date'
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
    public $base, $admin_id, $db;
    public function list($from_app = false, $search = '', $limit = 10, $offset = 0, $sort = 'id', $order = 'ASC', $where = [], $user_details = [])
    {
        $multipleWhere = '';
        $db      = \Config\Database::connect();
        $builder = $db->table('settlement_history');
        $sortable_fields = ['id' => 'id', 'amount' => 'amount'];
        $condition  = [];
        if (isset($search) and $search != '') {
            $multipleWhere = ['`settlement_history.id`' => $search, '`settlement_history.amount`' => $search, 'pd.company_name' => $search];
        }
        if (isset($_GET['id']) && $_GET['id'] != '') {
            $builder->where($condition);
        }
        if (isset($_POST['order'])) {
            $order = $_POST['order'];
        }
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->orWhere($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        $total_count = $builder->select('COUNT(`settlement_history`.`id`) as `total`,pd.id as p_id')
            ->join('partner_details pd', 'settlement_history.provider_id = pd.partner_id', 'left')->get()->getResultArray();
        $total = $total_count[0]['total'];
        $builder->select('settlement_history.* ,pd.id as p_id, pd.company_name')
            ->join('partner_details pd', 'settlement_history.provider_id = pd.partner_id', 'left');
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->orLike($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        $settlement_data = $builder->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();
        $db = \Config\Database::connect();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        foreach ($settlement_data as $row) {
            $parter_details = (fetch_details('partner_details', ['partner_id' => $row['provider_id']]));
            $operations = '<button class="btn btn-success btn-sm edit_cash_collection" data-id="' . $row['id'] . '" data-toggle="modal" data-target="#update_modal"><i class="fa fa-pen" aria-hidden="true"></i> </button> ';
            $tempRow['id'] = $row['id'];
            $tempRow['provider_id'] = $row['provider_id'];
            $tempRow['message'] = $row['message'];
            $tempRow['amount'] = ($row['amount']);
            $tempRow['status'] = $row['status'];
            $tempRow['date'] = $row['date'];
            $tempRow['partner_name'] = $parter_details[0]['company_name'];
            if ($from_app == false) {
                $tempRow['operations'] = $operations;
            }
            $rows[] = $tempRow;
        }
        if ($from_app) {
            $data['total'] = (empty($total)) ? (string) count($rows) : $total;
            $data['data'] = $rows;
            return $data;
        } else {
            $bulkData['rows'] = $rows;
            return ($bulkData);
        }
    }
}
