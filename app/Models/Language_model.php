<?php
namespace App\Models;
use CodeIgniter\Model;
class Language_model extends Model
{
    public $admin_id;
    public function __construct()
    {
        $ionAuth = new \IonAuth\Libraries\IonAuth();
        $this->admin_id = ($ionAuth->isAdmin()) ? $ionAuth->user()->row()->id : 0;
    }
    protected $table = 'languages';
    protected $primaryKey = 'id';
    protected $allowedFields = ['language', 'code', 'is_default','is_rtl'];
    public function list($from_app = false, $search = '', $limit = 10, $offset = 0, $sort = 'id', $order = 'ASC', $where = [])
    {
        $db      = \Config\Database::connect();
        $builder = $db->table('languages');
        $multipleWhere = [];
        $condition = $bulkData = $rows = $tempRow = [];
        if (isset($_GET['offset']))
            $offset = $_GET['offset'];
        if ((isset($search) && !empty($search) && $search != "") || (isset($_GET['search']) && $_GET['search'] != '')) {
            $search = (isset($_GET['search']) && $_GET['search'] != '') ? $_GET['search'] : $search;
            $multipleWhere = [
                '`id`' => $search,
                '`name`' => $search,
                '`code`' => $search
            ];
        }
        if (isset($_GET['limit'])) {
            $limit = $_GET['limit'];
        }
        if (isset($_GET['sort'])) {
            if ($_GET['sort'] == 'id') {
                $sort = "id";
            } else {
                $sort = $_GET['sort'];
            }
        }
        if (isset($_GET['order'])) {
            $order = $_GET['order'];
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
        $category_record = $builder->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        $settings = fetch_details('languages', ['is_default' => '1']);
        $default_language_value = $settings[0]['id'];
        foreach ($category_record as $row) {
            $tempRow['id'] = $row['id'];
            $tempRow['language'] = $row['language'];
            $tempRow['code'] = $row['code'];
            $tempRow['is_rtl'] = $row['is_rtl'];
            $is_rtl = ($row['is_rtl'] == 1) ?
                "<div class='  text-emerald-success  ml-3 mr-3 mx-5'>Yes
        </div>" :
                "<div class=' text-emerald-danger ml-3 mr-3 '>No
        </div>";
            $operations = '';
            if ($this->admin_id != 0) {
                if (count($category_record) == 1) {
                    $operations = "";
                } else {
                    $operations = "
                         <button  class='btn btn-danger  delete-language btn ml-2' title='Delete' onclick='language_id(this)'> <i class='fa fa-trash' aria-hidden='true'></i> </button>";
                    $operations .= ' <button class="btn btn-success edit-language" title="Edit" data-id="' . $row['id'] . '" data-toggle="modal" data-target="#update_modal" "> <i class="fa fa-pen" aria-hidden="true"></i> </button> ';
                }
            }
            if ($from_app == false) {
                $tempRow['language'] = $row['language'];
                $tempRow['code'] = $row['code'];
                $tempRow['is_rtl'] = $is_rtl;
                $tempRow['is_rtl_og'] =$row['is_rtl'] ;
                $tempRow['operations'] = $operations;
                $tempRow['default'] = ($default_language_value == $row['id']) ?
                    '<span class="badge badge-secondary"><em class="fa fa-check"></em> Default</span>' :
                    '<a class="btn btn-icon btn-sm btn-info text-white store_default_language" data-id="' . $row['id'] . '"> Set as Default</a>';
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
