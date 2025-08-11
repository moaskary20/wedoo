<?php
namespace App\Models;
use CodeIgniter\Model;
class Bookmarks_model  extends Model
{
    protected $table = 'bookmarks';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'partner_id'];
    public function list($from_app = false, $search = '', $limit = 10, $offset = 0, $sort = 'id', $order = 'ASC', $where = [], $column_name = 'b.id', $whereIn = [])
    {
        $db      = \Config\Database::connect();
        $builder = $db->table('bookmarks b');
        $multipleWhere = '';
        if ((isset($search) && !empty($search) && $search != "")) {
            $multipleWhere = [
                '`b.id`' => $search, '`b.user_id`' => $search, '`b.partner_id`' => $search,
            ];
        }
        if (isset($_GET['limit'])) {
            $limit = $_GET['limit'];
        }
        if (isset($_GET['offset'])) {
            $offset = $_GET['offset'];
        }
        if (isset($_GET['sort'])) {
            if ($_GET['sort'] == 'o.id') {
                $sort = "o.id";
            } else {
                $sort = $_GET['sort'];
            }
        }
        if (isset($_GET['order'])) {
            $order = $_GET['order'];
        }
        $builder->select(' COUNT(b.id) as `total` ')
            ->join('users u', 'u.id = b.partner_id', 'left')
            ->join('partner_details pd', 'pd.partner_id = b.partner_id', 'left');
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->orWhere($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        if (isset($whereIn) && !empty($whereIn)) {
            $builder->whereIn($column_name, $whereIn);
        }
        $partner_count = $builder->get()->getResultArray();
        $total = $partner_count[0]['total'];
        $builder->select('b.*,u.username as partner_name,u.image')
            ->join('users u', 'u.id = b.partner_id', 'left')
            ->join('partner_details pd', 'pd.partner_id = b.partner_id', 'left');
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->orWhere($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        if (isset($whereIn) && !empty($whereIn)) {
            $builder->whereIn($column_name, $whereIn);
        }
        $partner_record = $builder->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();
        $bulkData = array();
        $bulkData['total'] = $total;
        $tempRow = array();
        $rows = [];
        foreach ($partner_record as $row) {
            if ($from_app) {
                $row['image'] = (!empty($row['image'])) ? base_url('public/backend/assets/profiles/' . $row['image']) : "";
            }
            $tempRow = [
                'id' => $row['id'],
                'image' => $row['image'],
                'user_id' => $row['user_id'],
                'partner_id' => $row['partner_id']
            ];
            if (!$from_app) {
                $tempRow['created_at'] = $row['created_at'];
            }
            $rows[] = $tempRow;
        }
        
        if ($from_app) {
            $response['total'] = $total;
            $response['data'] = $rows;
            return $response;
        } else {
            $bulkData['rows'] = $rows;
        }
        return $bulkData;
    }
}
