<?php
namespace App\Models;
use CodeIgniter\Model;
class Custom_job_request_model extends Model
{
    protected $DBGroup = 'default';
    protected $table = ' custom_job_requests';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = ['*'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    public $base, $admin_id, $db;
    public function list($from_app = false, $search = '', $limit = 10, $offset = 0, $sort = 'id', $order = 'ASC')
    {
        $db      = \Config\Database::connect();
        $builder = $db->table('custom_job_requests');
        if (isset($search) and $search != '') {
            $multipleWhere = ['`id`' => $search, '`service_title`' => $search, '`service_short_description`' => $search];
        }
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->orWhere($multipleWhere);
        }
        $Faq_count = $builder->select(' COUNT(id) as `total` ')->get()->getResultArray();
        $total = $Faq_count[0]['total'];
        $builder->select();
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->orLike($multipleWhere);
        }
        $faq_record = $builder->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        foreach ($faq_record as $row) {
            $tempRow['id'] = $row['id'];
            $tempRow['question'] = $row['question'];
            $tempRow['answer'] = $row['answer'];
            $tempRow['status'] = labels($row['status'], ucfirst($row['status']));
            $tempRow['created_at'] =format_date( $row['created_at'],'d-m-Y');
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
