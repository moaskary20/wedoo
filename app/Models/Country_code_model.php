<?php
namespace App\Models;
use CodeIgniter\Model;
class Country_code_model extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'country_codes';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'name', 'code','is_default'
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
        $builder = $db->table('country_codes');
        $sortable_fields = ['id' => 'id', 'name' => 'name', 'code	' => 'code'];
        $condition  = [];
        if (isset($search) and $search != '') {
            $multipleWhere = ['`id`' => $search, '`name`' => $search, '`code`' => $search];
        }
        if (isset($_GET['id']) && $_GET['id'] != '') {
            $builder->where($condition);
        }
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->orWhere($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        $contry_code_total = $builder->select(' COUNT(id) as `total` ')->get()->getResultArray();
        $total = $contry_code_total[0]['total'];
        $builder->select();
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->orLike($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        $contry_code_record = $builder->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        $settings = fetch_details('country_codes', ['is_default' => 1]);
        foreach ($contry_code_record as $row) {
            $operations = '<div class="dropdown">
            <a class="" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <button class="btn btn-secondary   btn-sm px-3"> <i class="fas fa-ellipsis-v "></i></button>
            </a>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">';
            $operations .= '<a class="dropdown-item edit_country_code" " data-id="' . $row['id'] . '"  data-toggle="modal" data-target="#update_modal" onclick="category_id(this)"  title = "Edit ">  <i class="fa fa-pen text-primary mr-1" aria-hidden="true"></i>'. labels('edit', 'Edit') .'</a>';
            $operations .= '<a class="dropdown-item  delete-country_code" data-id="' . $row['id'] . '"> <i class="fa fa-trash text-danger mr-1"></i>'. labels('delete', 'Delete') .'</a>';
            $operations .= '</div></div>';
            $default_language_value = $settings[0]['id'];
            $tempRow['id'] = $row['id'];
            $tempRow['name'] = $row['name'];
            $tempRow['code'] = $row['code'];
            $tempRow['created_at'] = $row['created_at'];
            $tempRow['operations'] = $operations;
            $tempRow['default'] = ($default_language_value == $row['id']) ?
                '<span class="badge badge-secondary"><em class="fa fa-check"></em> Default</span>' :
                '<a class="btn btn-icon btn-sm btn-info text-white store_default_country_code" data-id="' . $row['id'] . '"> Set as Default</a>';
            $rows[] = $tempRow;
        }
        if ($from_app) {
            $data['total'] = (empty($total)) ? (string) count($rows) : $total;
            $data['data'] = $rows;
            return $data;
        } else {
            $bulkData['rows'] = $rows;
            return json_encode($bulkData);
        }
    }
}
