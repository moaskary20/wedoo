<?php
namespace App\Models;
use CodeIgniter\Model;
class Payment_request_model extends Model
{
    protected $table = 'payment_request';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'user_type', 'payment_address', 'amount', 'remarks', 'status'];
    public function list($from_app = false, $search = '', $limit = 20, $offset = 0, $sort = 'id', $order = 'DESC', $where = [])
    {
        $ionAuth = new \IonAuth\Libraries\IonAuth();
        $db      = \Config\Database::connect();
        $builder = $db->table('payment_request p');
        $multipleWhere = [];
        $condition = $bulkData = $rows = $tempRow = [];
        if (isset($_GET['offset']))
            $offset = $_GET['offset'];
        if ((isset($search) && !empty($search) && $search != "") || (isset($_GET['search']) && $_GET['search'] != '')) {
            $search = (isset($_GET['search']) && $_GET['search'] != '') ? $_GET['search'] : $search;
            $multipleWhere = [
                'p.id' => $search,
                'p.user_id' => $search,
                'p.user_type' => $search,
                'p.payment_address' => $search,
                'p.amount' => $search,
                'p.remarks' => $search,
                'p.status' => $search,
                'u.username' => $search,
            ];
        }
        if (isset($_GET['limit'])) {
            $limit = $_GET['limit'];
        }
        if (isset($_GET['sort'])) {
            if ($_GET['sort'] == 'p.id') {
                $sort = "p.id";
            } else {
                $sort = $_GET['sort'];
            }
        }
        if (isset($_GET['order'])) {
            $order = $_GET['order'];
        }
        if (isset($_POST['order'])) {
            $order = $_POST['order'];
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->groupStart();
            $builder->orLike($multipleWhere);
            $builder->groupEnd();
        }
        $count = $builder->select('count(p.id) as total')
            ->join('users u', 'u.id=p.user_id')
            ->join('partner_details pd', 'pd.partner_id=u.id', 'left');
        if (isset($_GET['withdraw_request_filter']) && $_GET['withdraw_request_filter'] != '') {
            $builder->where('p.status', $_GET['withdraw_request_filter']);
        }
        $count = $builder->get()->getResultArray();
        $total = $count[0]['total'];
        $builder->select('p.*,p.id as payment_request_id,p.created_at as payment_created_at,pd.*,u.username as partner_name');
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->groupStart();
            $builder->orLike($multipleWhere);
            $builder->groupEnd();
        }
        if (isset($_GET['withdraw_request_filter']) && $_GET['withdraw_request_filter'] != '') {
            $builder->where('p.status', $_GET['withdraw_request_filter']);
        }
        $record = $builder->join('users u', 'u.id=p.user_id', 'left')
            ->join('partner_details pd', 'pd.partner_id=u.id', 'left')
            ->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        foreach ($record as $row) {
            $operations = "";
            if ($row['status'] == 0) {
                if (isset($where['user_id']) && !empty($where['user_id'])) {
                    $operations .= '<button class="btn btn-success btn-sm edit" data-id="' . $row['id'] . '" data-toggle="modal" data-target="#update_modal"><i class="fa fa-pen" aria-hidden="true"></i> </button> ';
                }
                if ($ionAuth->isAdmin()) {
                    $operations .= '<button class="btn btn-success btn-sm edit_request" data-id="' . $row['id'] . '" data-toggle="modal" data-target="#edit_modal"><i class="fa fa-check" aria-hidden="true"></i> </button> ';
                }
            }
            if ($row['status'] == 1) {
                if (isset($where['user_id']) && !empty($where['user_id'])) {
                    $operations .= '<button   class="btn btn-success btn-sm set_settlement_status" 164id="' . $row['payment_request_id'] . '" value="' . $row['payment_request_id'] . '" >'. labels('settle', 'Settle') .'</button> ';
                }
                if ($ionAuth->isAdmin()) {
                    $operations .= '<button   class="btn btn-success btn-sm set_settlement_status" id="' . $row['payment_request_id'] . '" value="' . $row['payment_request_id'] . '" >' . labels('settle', 'Settle') . '</button> ';
                }
            }
            if ($from_app) {
                if ($row['status'] == 0) {
                    $status = "Pending";
                } elseif ($row['status'] == 1) {
                    $status = "Approved";
                } else if($row['status']==2){
                    $status = "Rejected";
                }else if($row['status']==3){
                    $status="Settled";
                }
            }else{

                if ($row['status'] == 0) {
                    $status =   "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-grey text-emerald-grey dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3 mx-5'>". labels('pending', 'Pending') .
                    "</div>";
                } elseif ($row['status'] == 1) {
                    $status =    "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-success text-emerald-success dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3 mx-5'>" . labels('approved', 'Approved') .
                    "</div>";
                } elseif ($row['status'] == 2) {
                    $status =    "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-danger text-emerald-danger dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3 mx-5'>" . labels('rejected', 'Rejected') .
                    "</div>";
                } else if ($row['status'] == 3) {
                    $status =    "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-warning text-emerald-warning dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3 mx-5'>" . labels('settled', 'Settled') .
                    "</div>";
                }
            }

            $tempRow['id'] = $row['payment_request_id'];
            $tempRow['user_id'] = $row['user_id'];
            $tempRow['partner_name'] = $row['partner_name'];
            $tempRow['bank_name'] = $row['bank_name'];
            $tempRow['account_number'] = $row['account_number'];
            $tempRow['account_name'] = $row['account_name'];
            $tempRow['bank_code'] = $row['bank_code'];
            $tempRow['swift_code'] = $row['swift_code'];
            $tempRow['user_type'] = $row['user_type'];
            $tempRow['payment_address'] = $row['payment_address'];
            $tempRow['amount'] = $row['amount'];
            $tempRow['remarks'] = $row['remarks'];
            $tempRow['status'] = $status;
            if (!$from_app) {
                $tempRow['created_at'] =  date("d-M-Y h:i A", strtotime($row['payment_created_at']));
            } else {
                $tempRow['created_at'] = $row['payment_created_at'];
            }
            $tempRow['operations'] =  $operations;
            if ($from_app == true) {
                $tempRow['status'] =  strip_tags($status);
            } else {
                $tempRow['status'] =  $status;
            }
            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        if ($from_app) {
            $data['total'] = $total;
            $data['data'] = $rows;
            return $data;
        } else {
            return json_encode($bulkData);
        }
    }
}
