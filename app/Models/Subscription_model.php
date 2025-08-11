<?php
namespace App\Models;
use \Config\Database;
use CodeIgniter\Model;
use  app\Controllers\BaseController;
class Subscription_model  extends Model
{
    protected $table = 'subscriptions';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'description', 'duration', 'price', 'discount_price', 'publish', 'order_type', 'max_order_limit', 'service_type', 'max_service_limit', 'tax_type', 'tax_id', 'is_commision', 'commission_threshold', 'commission_percentage', 'status'];
    public function list($from_app = false, $search = '', $limit = 10, $offset = 0, $sort = 'id', $order = 'ASC', $where = [])
    {
        $db      = \Config\Database::connect();
        $builder = $db->table('subscriptions s');
        $multipleWhere = [];
        $bulkData = $rows = $tempRow = [];
        if (isset($_GET['offset'])) {
            $offset = $_GET['offset'];
        }
        if (isset($_GET['limit'])) {
            $limit = $_GET['limit'];
        }
        $sort = "s.id";
        if (isset($_GET['sort'])) {
            if ($_GET['sort'] == 's.id') {
                $sort = "s.id";
            } else {
                $sort = $_GET['sort'];
            }
        }
        $order = "DESC";
        if (isset($_GET['order'])) {
            $order = $_GET['order'];
        }
        if ((isset($search) && !empty($search) && $search != "") || (isset($_GET['search']) && $_GET['search'] != '')) {
            $search = (isset($_GET['search']) && $_GET['search'] != '') ? $_GET['search'] : $search;
            $multipleWhere = [
                '`s.id`' => $search,
                '`s.name`' => $search,
                '`s.description`' => $search,
                '`s.duration`' => $search,
                '`s.price`' => $search,
                '`s.discount_price`' => $search,
                '`s.publish`' => $search,
                '`s.order_type`' => $search,
                '`s.max_order_limit`' => $search,
                '`s.service_type`' => $search,
                '`s.max_service_limit`' => $search,
                '`s.tax_type`' => $search,
                '`s.tax_id`' => $search,
                '`s.is_commision`' => $search,
                '`s.commission_threshold`' => $search,
                '`s.commission_percentage`' => $search,
                '`s.status`' => $search,
            ];
        }
        $subscription = $builder->select('count(s.id) as total');
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->groupStart();
            $builder->orLike($multipleWhere);
            $builder->groupEnd();
        }
        if (isset($_GET['subscription_filter']) && $_GET['subscription_filter'] != '') {
            $builder->where('s.status',  $_GET['subscription_filter']);
        }
        $subscription = $builder->get()->getResultArray();
        $total = $subscription[0]['total'];
        $builder->select('s.*');
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->groupStart();
            $builder->orLike($multipleWhere);
            $builder->groupEnd();
        }
        if (isset($_GET['subscription_filter']) && $_GET['subscription_filter'] != '') {
            $builder->where('s.status',  $_GET['subscription_filter']);
        }
        $subscription_record = [];
        $subscription_record = $builder->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        if ($from_app == false) {
            $db      = \Config\Database::connect();
            $builder = $db->table('users u');
            $builder->select('u.*,ug.group_id')
                ->join('users_groups ug', 'ug.user_id = u.id')
                ->where('ug.group_id', 1)
                ->where(['phone' => $_SESSION['identity']]);
            $user1 = $builder->get()->getResultArray();
            $permissions = get_permission($user1[0]['id']);
        }
        $operations = "";
        foreach ($subscription_record as $row) {
            if ($from_app == false) {
                $operations = '<div class="dropdown">
                <a class="" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <button class="btn btn-secondary   btn-sm px-3"> <i class="fas fa-ellipsis-v "></i></button>
                </a><div class="dropdown-menu" aria-labelledby="dropdownMenuLink">';
                if ($permissions['update']['subscription'] == 1) {
                    $operations .= '<a class="dropdown-item" href="' . base_url('/admin/subscription/edit_subscription_page/' . $row['id']) . '" ><i class="fa fa-pen mr-1 text-primary"></i> Edit</a>';
                   }
                if ($permissions['delete']['subscription'] == 1) {
                    $operations .= '<a class="dropdown-item delete" data-id="' . $row['id'] . '"> <i class="fa fa-trash text-danger mr-1"></i> Delete</a>';
                }
                $operations .= '</div></div>';
            }
            $publish_badge = ($row['publish'] == 1) ?
                "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-success text-emerald-success dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>Yes
                </div>" :
                "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-danger text-emerald-danger dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>No
                </div>";
            $status_badge = ($row['status'] == 1) ?
                "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-success text-emerald-success dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>Active
                    </div>" :
                "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-danger text-emerald-danger dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>Deactive
                    </div>";
            $is_commision_badge = ($row['is_commision'] == "yes") ?
                "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-success text-emerald-success dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>Yes
                        </div>" :
                "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-danger text-emerald-danger dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>No
                        </div>";
            $tempRow['id'] = $row['id'];
            $tempRow['name'] = $row['name'];
            $tempRow['description'] = $row['description'];
            $tempRow['duration'] = $row['duration'];
            $tempRow['price'] = $row['price'];
            $tempRow['discount_price'] = $row['discount_price'];
            $tempRow['publish'] = $row['publish'];
            $tempRow['publish_badge'] = $publish_badge;
            $tempRow['order_type'] = $row['order_type'];
            $tempRow['max_order_limit'] = ($row['order_type'] == "limited") ? $row['max_order_limit'] : "-";
            $tempRow['service_type'] = $row['service_type'];
            $tempRow['max_service_limit'] = $row['max_service_limit'];
            $tempRow['tax_type'] = $row['tax_type'];
            $tempRow['tax_id'] = $row['tax_id'];
            $tempRow['is_commision'] = $row['is_commision'];
            $tempRow['commission_threshold'] = $row['commission_threshold'];
            $tempRow['commission_percentage'] = $row['commission_percentage'];
            $tempRow['status'] = $row['status'];
            $tempRow['status_badge'] = $status_badge;
            $tempRow['is_commision_badge'] = $is_commision_badge;
            $tempRow['operations'] = $operations;
            $price = calculate_subscription_price($row['id']);
            $tempRow['tax_value'] = $price[0]['tax_value'];
            $tempRow['tax_percentage'] = $price[0]['tax_percentage'];
            $tempRow['price_with_tax']  = $price[0]['price_with_tax'];
            $tempRow['original_price_with_tax'] = $price[0]['original_price_with_tax'];
            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        if ($from_app) {
            $response['total'] = $total;
            $response['data'] = $rows;
            return $response;
        } else {
            $tempRow['operations'] = $operations;
            $bulkData['rows'] = $rows;
        }
        return $bulkData;
    }
}
