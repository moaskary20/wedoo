<?php
namespace App\Models;
use CodeIgniter\Model;
use PDO;
class Partner_subscription_model extends Model
{
    protected $table = 'partner_subscriptions';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'subscription_id', 'status', 'created_at', 'updated_at', 'id', 'is_payment', 'partner_id',
        'purchase_date', 'expiry_date', 'name', 'description', 'duration', 'price', 'discount_price', 'publish', 'order_type', 'max_order_limit', 'service_type', 'max_service_limit', 'tax_type', 'tax_id', 'is_commision', 'commission_threshold', 'commission_percentage'
    ];
    public function list($from_app = false, $search = '', $limit = 10, $offset = 0, $sort = 'id', $order = 'ASC', $where = [])
    {
        $db      = \Config\Database::connect();
        $builder = $db->table('partner_subscriptions ps');
        $multipleWhere = [];
        $bulkData = $rows = $tempRow = [];
        if (isset($_GET['offset'])) {
            $offset = $_GET['offset'];
        }
        if (isset($_GET['limit'])) {
            $limit = $_GET['limit'];
        }
        $sort = "ps.id";
        if (isset($_GET['sort'])) {
            if ($_GET['sort'] == 'ps.id') {
                $sort = "ps.id";
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
                '`ps.id`' => $search,
                '`ps.name`' => $search,
                '`ps.description`' => $search,
                '`ps.status`' => $search,
            ];
        }
        $subscription = $builder->select('count(ps.id) as total');
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->groupStart();
            $builder->orLike($multipleWhere);
            $builder->groupEnd();
        }
        if (isset($_GET['subscription_filter']) && $_GET['subscription_filter'] != '') {
            $builder->where('ps.status',  $_GET['subscription_filter']);
        }
        $subscription = $builder->get()->getResultArray();
        $total = $subscription[0]['total'];
          $builder->select('ps.*,pd.company_name,pd.banner')->join('partner_details pd', 'pd.partner_id=ps.partner_id');
        if (isset($_GET['subscription_filter']) && $_GET['subscription_filter'] != '') {
            $builder->where('ps.status',  $_GET['subscription_filter']);
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->groupStart();
            $builder->orLike($multipleWhere);
            $builder->groupEnd();
        }
        $subscription_record = [];
        $subscription_record = $builder->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        $operations = "";
        foreach ($subscription_record as $row) {
            $publish_badge = ($row['publish'] == 1) ?
                "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-success text-emerald-success dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>Yes
                    </div>" :
                "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-danger text-emerald-danger dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>No
                    </div>";
            $status_badge = ($row['status'] == 'active') ?
                "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-success text-emerald-success dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>Active
                        </div>" :
                "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-danger text-emerald-danger dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>Deactive
                        </div>";
            $is_commision_badge = ($row['is_commision'] == "yes") ?
                "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-success text-emerald-success dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>Yes
                            </div>" :
                "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-danger text-emerald-danger dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>No
                            </div>";
            if (($row['is_payment'] == "1")) {
                $is_payment =    "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-success text-emerald-success dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>Success
                </div>";
            } elseif ($row['is_payment'] = "2") {
                $is_payment =    "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-danger text-emerald-danger dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>Failed                </div>";
            }
            if (!empty($row['banner'])) {
                $row['banner'] = (file_exists($row['banner'])) ? base_url($row['banner']) : base_url('public/backend/assets/profiles/default.png');
                $tempRow['banner_image'] = $row['banner'];
            } else {
                $tempRow['banner_image'] = '';
            }
            if (isset($row['banner']) && !empty($row['banner']) && check_exists(base_url($row['banner']))) {
                $images = '<a  href="' . ($row['banner'])  . '" data-lightbox="image-1"><img height="80px" width="80px" style="border-radius: 40px!important;" class="rounded p-1" src="' . ($row['banner']) . '" alt=""></a>';
            } else {
                $images = 'nothing found';
            }
            $operations = '<a href="' . base_url('/admin/partners/partner_subscription/' . $row['partner_id']) . '" class="btn btn-info ml-1 btn-sm"  title = "view partner"> <i class="fa fa-eye" aria-hidden="true"></i> </a> ';
            $tempRow['id'] = $row['id'];
            $tempRow['name'] = $row['name'];
            $tempRow['description'] = $row['description'];
            $tempRow['duration'] = $row['duration'];
            $tempRow['price'] = $row['price'];
            $tempRow['discount_price'] = $row['discount_price'];
            $tempRow['publish'] = $row['publish'];
            $tempRow['publish_badge'] = $publish_badge;
            $tempRow['order_type'] = $row['order_type'];
            $tempRow['max_order_limit'] = $row['max_order_limit'];
            $tempRow['tax_type'] = $row['tax_type'];
            $tempRow['purchase_date'] = format_date($row['purchase_date'], 'd-m-Y');
            $tempRow['expiry_date'] = format_date($row['expiry_date'], 'd-m-Y');
            $tempRow['tax_id'] = $row['tax_id'];
            $tempRow['is_commision'] = $row['is_commision'];
            $tempRow['commission_threshold'] = $row['commission_threshold'];
            $tempRow['commission_percentage'] = $row['commission_percentage'];
            $tempRow['status'] = $row['status'];
            $tempRow['status_badge'] = $status_badge;
            $tempRow['is_commision_badge'] = $is_commision_badge;
            $tempRow['operations'] = $operations;
            $price = calculate_partner_subscription_price($row['partner_id'], $row['subscription_id'], $row['id']);
            $tempRow['tax_value'] = $price[0]['tax_value'];
            $tempRow['price_with_tax']  = $price[0]['price_with_tax'];
            $tempRow['original_price_with_tax'] = $price[0]['original_price_with_tax'];
            $tempRow['tax_percentage'] = $price[0]['tax_percentage'] . "%";
            $tempRow['banner_image'] = $images;
            $tempRow['company_name'] = $row['company_name'];
            $tempRow['is_payment'] = $is_payment;
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
    public function subscriber_list($from_app = false, $search = '', $limit = 10, $offset = 0, $sort = 'id', $order = 'ASC', $where = [])
    {
        $db = \Config\Database::connect();
        $builder = $db->table('partner_subscriptions ps');
        $multipleWhere = [];
        $bulkData = $rows = $tempRow = [];
        if (isset($_GET['offset'])) {
            $offset = $_GET['offset'];
        }
        if (isset($_GET['limit'])) {
            $limit = $_GET['limit'];
        }
        if (isset($_GET['subscription_filter']) && $_GET['subscription_filter'] != '') {
            $builder->where('ps.status',  $_GET['subscription_filter']);
        }
        $sort = "ps.id";
        if (isset($_GET['sort'])) {
            if ($_GET['sort'] == 'ps.id') {
                $sort = "ps.id";
            } else {
                $sort = $_GET['sort'];
            }
        }
        $order = "DESC";
        if (isset($_GET['order'])) {
            $order = $_GET['order'];
        }
        $search = '';
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            $multipleWhere = [
                'ps.id' => $search,
                'ps.name' => $search,
                'ps.description' => $search,
                'ps.status' => $search,
            ];
        }
        $subscription = $builder->select('count(ps.id) as total');
           $subQuery = $db->table('partner_subscriptions sub')
            ->select('MAX(id) as latest_id, partner_id')
            ->groupBy('partner_id')
            ->get();
        $latestIds = [];
        foreach ($subQuery->getResult() as $row) {
            $latestIds[$row->partner_id] = $row->latest_id;
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        if (!empty($multipleWhere)) {
            $builder->groupStart();
            $builder->orLike($multipleWhere);
            $builder->groupEnd();
        }
        $subscription = $builder->whereIn('ps.id', $latestIds)->get()->getResultArray();
        $total = $subscription[0]['total'];
        $builder
            ->select('ps.*, pd.company_name, pd.banner')
            ->join('partner_details pd', 'pd.partner_id = ps.partner_id','left')
            ->orderBy($sort, $order)
            ->groupBy('partner_id')
            ->limit($limit, $offset);
            if (isset($_GET['subscription_filter']) && $_GET['subscription_filter'] != '') {
                $builder->where('ps.status',  $_GET['subscription_filter']);
            }
            if (isset($where) && !empty($where)) {
                $builder->where($where);
            }
            if (!empty($multipleWhere)) {
                $builder->groupStart();
                $builder->orLike($multipleWhere);
                $builder->groupEnd();
            }
        $subscription_record = $builder->whereIn('ps.id', $latestIds)->get()->getResultArray();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        $operations = "";
        foreach ($subscription_record as $row) {
            $publish_badge = ($row['publish'] == 1) ?
                "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-success text-emerald-success dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>Yes
                    </div>" :
                "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-danger text-emerald-danger dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>No
                    </div>";
            $status_badge = ($row['status'] == 'active') ?
                "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-success text-emerald-success dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>Active
                        </div>" :
                "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-danger text-emerald-danger dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>Deactive
                        </div>";
            $is_commision_badge = ($row['is_commision'] == "yes") ?
                "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-success text-emerald-success dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>Yes
                            </div>" :
                "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-danger text-emerald-danger dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>No
                            </div>";
            if (($row['is_payment'] == "1")) {
                $is_payment =    "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-success text-emerald-success dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>Success
                </div>";
            } elseif ($row['is_payment'] = "2") {
                $is_payment =    "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-danger text-emerald-danger dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>Failed                </div>";
            }
            if (!empty($row['banner'])) {
                $row['banner'] = (file_exists($row['banner'])) ? base_url($row['banner']) : base_url('public/backend/assets/profiles/default.png');
                $tempRow['banner_image'] = $row['banner'];
            } else {
                $tempRow['banner_image'] = '';
            }
            if (isset($row['banner']) && !empty($row['banner']) && check_exists(base_url($row['banner']))) {
                $images = '<a  href="' . ($row['banner'])  . '" data-lightbox="image-1"><img height="80px" width="80px" style="border-radius: 40px!important;" class="rounded p-1" src="' . ($row['banner']) . '" alt=""></a>';
            } else {
                $images = 'nothing found';
            }
            $operations = '<a href="' . base_url('/admin/partners/partner_subscription/' . $row['partner_id']) . '" class="btn btn-info ml-1 btn-sm"  title = "view partner"> <i class="fa fa-eye" aria-hidden="true"></i> </a> ';
            $tempRow['id'] = $row['id'];
            $tempRow['name'] = $row['name'];
            $tempRow['description'] = $row['description'];
            $tempRow['duration'] = $row['duration'];
            $tempRow['price'] = $row['price'];
            $tempRow['discount_price'] = $row['discount_price'];
            $tempRow['publish'] = $row['publish'];
            $tempRow['publish_badge'] = $publish_badge;
            $tempRow['order_type'] = $row['order_type'];
            $tempRow['max_order_limit'] = $row['max_order_limit'];
            $tempRow['tax_type'] = $row['tax_type'];
            $tempRow['purchase_date'] = format_date($row['purchase_date'], 'd-m-Y');
            $tempRow['expiry_date'] = format_date($row['expiry_date'], 'd-m-Y');
            $tempRow['tax_id'] = $row['tax_id'];
            $tempRow['is_commision'] = $row['is_commision'];
            $tempRow['commission_threshold'] = $row['commission_threshold'];
            $tempRow['commission_percentage'] = $row['commission_percentage'];
            $tempRow['status'] = $row['status'];
            $tempRow['status_badge'] = $status_badge;
            $tempRow['is_commision_badge'] = $is_commision_badge;
            $tempRow['operations'] = $operations;
            $price = calculate_partner_subscription_price($row['partner_id'], $row['subscription_id'], $row['id']);
            $tempRow['tax_value'] = $price[0]['tax_value'];
            $tempRow['price_with_tax']  = $price[0]['price_with_tax'];
            $tempRow['original_price_with_tax'] = $price[0]['original_price_with_tax'];
            $tempRow['tax_percentage'] = $price[0]['tax_percentage'] . "%";
            $tempRow['banner_image'] = $images;
            $tempRow['company_name'] = $row['company_name'];
            $tempRow['is_payment'] = $is_payment;
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
