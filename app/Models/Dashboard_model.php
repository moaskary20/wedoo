<?php
namespace App\Models;
use CodeIgniter\Model;
class Dashboard_model extends Model{
    protected $DBGroup = 'default';
    protected $table = 'orders';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = ['user_id', 'partner_id ', 'address_id ', 'city_id ', 'city','total', 'promo_code', 'promo_discount', 'final_total', 'admin_earnings', 'partner_earnings', 'address', '	date_of_service', 'starting_time', 'ending_time', 'duration', 'status', 'remarks'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
    public $base, $admin_id, $db;
    public function list($from_app = false, $search = '', $limit = 10, $offset = 0, $sort = 'id', $order = 'ASC', $where = [])
    {
        $multipleWhere = '';
        $condition = $bulkData = $rows = $tempRow = [];
        $db      = \Config\Database::connect();
        $builder = $db->table('orders o');
        if (isset($_GET['user_id']) && $_GET['user_id'] != '') {
            $user_id = $_GET['user_id'];
            $condition['o.user_id'] = $user_id;
            $builder->where($condition);
        }
        $limit = 10;
        $condition  = [];
        $offset = 0;
        if (isset($_GET['offset'])) {
            $offset = $_GET['offset'];
        }
        if (isset($_GET['limit'])) {
            $limit = $_GET['limit'];
        }
        if (isset($_GET['sort'])) {
            if ($_GET['sort'] == 'id') {
                $sort = "o.id";
            } else {
                $sort = $_GET['sort'];
            }
        }
        $order = "ASC";
        if (isset($_GET['order'])) {
            $order = $_GET['order'];
        }
        if (isset($_GET['search']) and $_GET['search'] != '') {
            $search = $_GET['search'];
            $multipleWhere = ['o.id' => $search, 'u.first_name' => $search, 'u.last_name' => $search];
            $multipleWhere = ['o.id' => $search, 'p.first_name' => $search, 'p.last_name' => $search];
        }
        $builder->join('users u', 'o.user_id=u.id');
        $builder->join('users p', 'o.partner_id=u.id');
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->orWhere($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        $partner_count = $builder->select('count(o.id) as total')->get()->getResultArray();
        $total = $partner_count[0]['total'];
        $builder->select('u.first_name as customer_first_name,u.last_name as customer_last_name,p.first_name as partner_first_name,p.last_name as partner_last_name,o.*', 'o.user_id as user_id')->join('users u', 'o.user_id=u.id')
        ->join('users p', 'o.partner_id=p.id');
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->orLike($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        $order_record = $builder->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        foreach ($order_record as $row) {
            if ($from_app == false) {
                $operations = '<a href="orders/veiw_orders/' . $row['id'] . '" class="btn btn-primary"  title = "view the order"> <i class="fa fa-eye" aria-hidden="true"></i> </a> ';
                $operations .= '<button class="btn btn-danger delete_orders" data-id="' . $row['id'] . '" onclick="order_id(this)" data-toggle="modal" data-target="#delete_modal" title = "Delete order"> <i class="fa fa-trash" aria-hidden="true"></i> </button> ';
                if (($row['status'] == 0)) {
                    $status = "<label class='badge badge-secondary'>Awaiting</label>";
                } elseif (($row['status'] == 1)) {
                    $status = "<label class='badge badge-primary'>Confirmed</label>";
                } elseif (($row['status'] == 2)) {
                    $status = "<label class='badge badge-info'>Rescheduled</label>";
                } elseif (($row['status'] == 3)) {
                    $status = "<label class='badge badge-danger'>Cancelled </label>";
                } elseif (($row['status'] == 4)) {
                    $status = "<label class='badge badge-success'>Completed</label>";
                } else {
                    echo "status not defined";
                }
            } else {
                $status = $row['status'];
            }
            $tempRow['id'] = $row['id'];
            $tempRow['customer'] = $row['customer_first_name'] . '  ' . $row['customer_last_name'];
            $tempRow['partner'] = $row['partner_first_name'] . '  ' . $row['partner_last_name'];
            $tempRow['user_id'] = $row['user_id'];
            $tempRow['partner_id'] = $row['partner_id'];
            $tempRow['city_id'] = $row['city'];
            $tempRow['total'] = $row['total'];
            $tempRow['promo_code'] = $row['promo_code'];
            $tempRow['promo_discount'] = $row['promo_discount'];
            $tempRow['final_total'] = $row['final_total'];
            $tempRow['admin_earnings'] = $row['admin_earnings'];
            $tempRow['partner_earnings'] = $row['partner_earnings'];
            $tempRow['address_id'] = $row['address_id'];
            $tempRow['address'] = $row['address'];
            $tempRow['date_of_service'] = $row['date_of_service'];
            $tempRow['starting_time'] = $row['starting_time'];
            $tempRow['ending_time'] = $row['ending_time'];
            $tempRow['duration'] = $row['duration'];
            $tempRow['status'] = $status;
            $tempRow['remarks'] = $row['remarks'];
            $tempRow['created_at'] = $row['created_at'];
            $tempRow['operations'] = $operations;
            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        return json_encode($bulkData);
    }
}
?>
