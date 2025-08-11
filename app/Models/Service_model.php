<?php

namespace App\Models;

use CodeIgniter\Model;
use IonAuth\Libraries\IonAuth;
use Mpdf\Tag\Em;
use PDO;

class Service_model extends Model
{
    protected IonAuth $ionAuth;

    protected $table = 'services';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'user_id',
        'category_id',
        'tax_id',
        'tax',
        'title',
        'slug',
        'description',
        'tags',
        'image',
        'price',
        'discounted_price',
        'is_cancelable',
        'cancelable_till',
        'tax_type',
        'number_of_members_required',
        'duration',
        'rating',
        'number_of_ratings',
        'on_site_allowed',
        'max_quantity_allowed',
        'is_pay_later_allowed',
        'status',
        'price_with_tax',
        'tax_value',
        'original_price_with_tax',
        'other_images',
        'long_description',
        'files',
        'faqs',
        'at_store',
        'at_doorstep',
        'approved_by_admin',

    ];
    public $admin_id;
    public function __construct()
    {
        $ionAuth = new \IonAuth\Libraries\IonAuth();
        $this->admin_id = ($ionAuth->isAdmin()) ? $ionAuth->user()->row()->id : 0;
        $this->ionAuth = new \IonAuth\Libraries\IonAuth();
    }

    public function list($from_app = false, $search = '', $limit = 10, $offset = 0, $sort = 'id', $order = 'ASC', $where = [], $additional_data = [], $column_name = '', $whereIn = [], $for_new_total = null, $at_store = null, $at_doorstep = null)
    {
        $disk = fetch_current_file_manager();
        $db = \Config\Database::connect();

        // Initialize base query builder
        $builder = $db->table('services s');

        // Prepare search conditions once
        $searchFields = [];
        if ($search) {
            $fields = ['id', 'title', 'description', 'status', 'tags', 'price', 'discounted_price', 'rating', 'number_of_ratings', 'max_quantity_allowed'];
            foreach ($fields as $field) {
                $searchFields["`s.$field`"] = $search;
            }
        }

        // Build base query conditions
        $queryConditions = function ($builder) use ($searchFields, $where, $whereIn, $column_name, $additional_data, $at_doorstep, $at_store) {


            if (!empty($searchFields)) {
                $builder->groupStart()->orLike($searchFields)->groupEnd();
            }

            if (!empty($where)) {
                $builder->where($where);
            }

            if (!empty($whereIn)) {
                $builder->whereIn($column_name, $whereIn);
            }

            if (isset($additional_data['latitude']) && !empty($additional_data['latitude'])) {
                $parnter_ids = get_near_partners(
                    $additional_data['latitude'],
                    $additional_data['longitude'],
                    $additional_data['max_serviceable_distance'],
                    true
                );
                if (!empty($parnter_ids) && !isset($parnter_ids['error'])) {
                    $builder->whereIn('s.user_id', $parnter_ids);
                }
            }

            // Add filter conditions
            if (isset($_GET['service_filter_approve'])  && $_GET['service_filter_approve'] != '') {
                $builder->where('s.approved_by_admin', $_GET['service_filter_approve']);
            }
            if (isset($_GET['service_filter']) && $_GET['service_filter'] != "") {
                $builder->where('s.status', $_GET['service_filter']);
            }
            if (isset($_GET['service_custom_provider_filter']) && $_GET['service_custom_provider_filter'] != "") {
                $builder->where('s.user_id', $_GET['service_custom_provider_filter']);
            }
            if (isset($_GET['service_category_custom_filter']) && $_GET['service_category_custom_filter'] != "") {
                $builder->where('s.category_id', $_GET['service_category_custom_filter']);
            }
            if ($at_store != "") {

                $builder->where('s.at_store', $at_store);
            }

            if ($at_doorstep !== null && $at_doorstep !== '') {

                $builder->where('s.at_doorstep', $at_doorstep);
            }
        };

        // Get counts and price ranges in single query
        $stats = $builder->select('
            COUNT(s.id) as total,
            MAX(s.price) as max_price,
            MIN(s.price) as min_price,
            MIN(s.discounted_price) as min_discount_price,
            MAX(s.discounted_price) as max_discount_price
        ')->join('partner_details pd', 'pd.partner_id = s.user_id', 'left');
        $queryConditions($stats);
        $statsResult = $stats->get()->getRowArray();

        // print_r('before main query'); die;
        // Update mainQuery to include ratings data
        $mainQuery = $builder->select('
                s.*, 
                c.name as category_name,
                p.username as partner_name,
                c.parent_id,
                pd.need_approval_for_the_service,
                pd.slug as provider_slug,
                COUNT(DISTINCT sr.id) as total_ratings,
                SUM(CASE WHEN sr.rating = 5 THEN 1 ELSE 0 END) as rating_5,
                SUM(CASE WHEN sr.rating = 4 THEN 1 ELSE 0 END) as rating_4,
                SUM(CASE WHEN sr.rating = 3 THEN 1 ELSE 0 END) as rating_3,
                SUM(CASE WHEN sr.rating = 2 THEN 1 ELSE 0 END) as rating_2,
                SUM(CASE WHEN sr.rating = 1 THEN 1 ELSE 0 END) as rating_1,
                AVG(sr.rating) as average_rating,COUNT(DISTINCT os.id) as total_bookings

            ')
            ->join('users p', 'p.id = s.user_id', 'left')
            ->join('partner_details pd', 'pd.partner_id = s.user_id', 'left')
            ->join('categories c', 'c.id = s.category_id', 'left')
            ->join('services_ratings sr', 'sr.service_id = s.id', 'left') ->join('order_services os', 'os.service_id = s.id', 'left');

        $queryConditions($mainQuery);

        // Apply ordering and limits
        $records = $mainQuery->groupBy('s.id');

        // Handle sorting dynamically
        if ($sort === 'average_rating') {
            $records->orderBy('average_rating', $order); // Sorting by the calculated average_rating
        } else if ($sort === 'total_bookings') {
            $records->orderBy('total_bookings',$order);
        }else {
            $records->orderBy("s.$sort", $order); // Default sorting for other columns in services table
        }

        $records = $records->limit($limit, $offset)
            ->get()
            ->getResultArray();

        // print_R($records); die;
        // Process records
        $rows = [];
        foreach ($records as $row) {
            $tempRow = $this->processServiceRecord($row, $disk, $from_app, $additional_data);
            $rows[] = $tempRow;
        }

        // Return formatted response
        if ($from_app) {
            return $this->formatAppResponse($rows, $statsResult, $for_new_total);
        } else {
            return json_encode(['rows' => $rows, 'total' => $statsResult['total']]);
        }
    }
    private function processServiceRecord($row, $disk, $from_app, $additional_data)
    {


        $db = \Config\Database::connect();
        $tempRow = [];
        if ($disk == 'local_server') {
            $localPath = base_url($row['image']);

            $images = $localPath;
        } else if ($disk == "aws_s3") {
            $images = fetch_cloud_front_url('services', $row['image']);
        } else {
            $images = $row['image'];
        }



        // // Process images
        if ($from_app == false) {
            $images = '<div class="o-media o-media--middle">
            <a  href="' .  $images . '" data-lightbox="image-1"><img class="o-media__img images_in_card"  src="' .  $images . '" data-lightbox="image-1" alt="' .     $row['id'] . '"></a>';
        } else {

            if ($disk == "aws_s3") {
                $images = fetch_cloud_front_url('services', $row['image']);
            } else {
                $images = base_url($row['image']);
            }
        }
        if ($from_app == false) {

            if (!empty($row['other_images'])) {
                $row['other_images'] = array_map(function ($data) use ($row, $disk) {
                    if ($disk === "local_server") {
                        return base_url($data);
                    } elseif ($disk === "aws_s3") {
                        return fetch_cloud_front_url('services', $data);
                    }
                }, json_decode($row['other_images'], true));
            } else {
                $row['other_images'] = [];
            }


            if (!empty($row['files'])) {
                $row['files'] = array_map(function ($data) use ($row, $disk) {
                    if ($disk === "local_server") {
                        return base_url($data);
                    } elseif ($disk === "aws_s3") {
                        return fetch_cloud_front_url('services', $data);
                    }
                }, json_decode($row['files'], true));
            } else {
                $row['files'] = [];
            }



            $faqsData = json_decode($row['faqs'], true);
            if (is_array($faqsData)) {
                $faqs = [];
                foreach ($faqsData as $pair) {
                    $faq = [
                        'question' => $pair[0],
                        'answer' => $pair[1]
                    ];
                    $faqs[] = $faq;
                }
                $row['faqs'] = $faqs;
            } else {
            }
        } else {
            // Process other images and files
            foreach (['other_images', 'files'] as $field) {
                if (!empty($row[$field])) {
                    $row[$field] = array_map(function ($data) use ($disk) {
                        return ($disk === "local_server") ? base_url($data) : fetch_cloud_front_url('services', $data);
                    }, json_decode($row[$field], true) ?: []);
                } else {
                    $row[$field] = [];
                }
            }
            $faqsData = json_decode($row['faqs'], true);
            if (is_array($faqsData)) {
                $faqs = [];
                foreach ($faqsData as $pair) {
                    $faq = [
                        'question' => $pair[0],
                        'answer' => $pair[1]
                    ];
                    $faqs[] = $faq;
                }
                $row['faqs'] = $faqs;
            } else {
            }
        }

        // Get ratings data
        $rate_data = get_service_ratings($row['id']);

        // Get average rating
        $average_rating = $db->table('services s')
            ->select('(SUM(sr.rating) / COUNT(sr.rating)) as average_rating')
            ->join('services_ratings sr', 'sr.service_id = s.id')
            ->where('s.id', $row['id'])
            ->get()
            ->getRowArray();

        // Process ratings
        $ratings = [
            'average_rating' => isset($average_rating['average_rating']) ?
                number_format($average_rating['average_rating'], 2) : 0,
            'total_ratings' => "0",
            'rating_5' => 0,
            'rating_4' => 0,
            'rating_3' => 0,
            'rating_2' => 0,
            'rating_1' => 0
        ];

        // Update ratings if data exists
        if (!empty($rate_data)) {
            $rate_item = $rate_data[0];
            $ratings['total_ratings'] = $rate_item['total_ratings'] ?? "0";
            $ratings['rating_5'] = $rate_item['rating_5'] ?? 0;
            $ratings['rating_4'] = $rate_item['rating_4'] ?? 0;
            $ratings['rating_3'] = $rate_item['rating_3'] ?? 0;
            $ratings['rating_2'] = $rate_item['rating_2'] ?? 0;
            $ratings['rating_1'] = $rate_item['rating_1'] ?? 0;
        }


        if ($from_app == false) {
            $db      = \Config\Database::connect();
            $builder = $db->table('users u');
            $builder->select('u.*,ug.group_id')
                ->join('users_groups ug', 'ug.user_id = u.id')
                ->whereIn('ug.group_id', [1, 3])
                ->where(['phone' => $_SESSION['identity']]);
            $user1 = $builder->get()->getResultArray();
            $permissions = get_permission($user1[0]['id']);
        }
        // Get tax data
        $tax_data = fetch_details('taxes', ['id' => $row['tax_id']], ['title', 'percentage']);
        $taxInfo = [
            'tax_title' => !empty($tax_data) ? $tax_data[0]['title'] : "",
            'tax_percentage' => !empty($tax_data) ? $tax_data[0]['percentage'] : ""
        ];

        // Calculate tax values
        $taxData = $this->calculateTaxValues($row);

        // Create status badge
        $status_badge = ($row['status'] == 1) ?
            "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-success text-emerald-success dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3 mx-5'>". labels('active', 'Active') ."</div>" :
            "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-danger text-emerald-danger dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3 '>". labels('deactive', 'Deactive') ."</div>";

        // Create cancelable badge
        if ($from_app == false) {
            $is_cancelable = ($row['is_cancelable'] == 1) ?
                "<span class='badge badge-success'>". labels('yes', 'Yes') ."</span>" :
                "<span class='badge badge-danger'>". labels('not_allowed', 'Not Allowed') ."</span>";
        } else {
            $is_cancelable = ($row['is_cancelable']);
        }


        $operations = "";
        $operations = '<div class="dropdown">
            <a class="" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <button class="btn btn-secondary   btn-sm px-3"> <i class="fas fa-ellipsis-v "></i></button>
            </a>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">';
        if ($from_app == false) {
            if ($this->ionAuth->isAdmin()) {
                if ($permissions['update']['services'] == 1) {
                    $operations .= '<a class="dropdown-item"href="' . base_url('/admin/services/edit_service/' . $row['id']) . '"><i class="fa fa-pen mr-1 text-primary"></i>' . labels('edit_service', 'Edit Service') . '</a>';
                }
                $operations .= '<a class="dropdown-item"href="' . base_url('/admin/services/duplicate/' . $row['id']) . '"><i class="fas fa-copy text-info mr-1"></i>' . labels('duplicate_service', 'Duplicate Service') . '</a>';
            } else {
                // if ($row['need_approval_for_the_service'] == 0) {
                    if ($permissions['update']['services'] == 1) {
                        $operations .= '<a class="dropdown-item"href="' . base_url('/partner/services/edit_service/' . $row['id']) . '"><i class="fa fa-pen mr-1 text-primary"></i>' . labels('edit_service', 'Edit Service') . '</a>';
                    }
                // }
                $operations .= '<a class="dropdown-item"href="' . base_url('/partner/services/duplicate/' . $row['id']) . '"><i class="fas fa-copy text-info mr-1"></i>' . labels('duplicate_service', 'Duplicate Service') . '</a>';
            }
            if ($permissions['delete']['services'] == 1) {
                $operations .= '<a class="dropdown-item delete" data-id="' . $row['id'] . '" > <i class="fa fa-trash text-danger mr-1"></i>' . labels('delete', 'Delete') . '</a>';
            }
            if ($row['need_approval_for_the_service'] == 1) {
                $operations .= ($row['approved_by_admin'] == 1) ?
                    '<a class="dropdown-item disapprove_service" href="#" id="disapprove_service"> <i class="fas fa-times text-danger mr-1"></i>' . labels('disapprove_service', 'Disapprove Service') . '</a>' :
                    '<a class="dropdown-item approve_service" href="#" id="approve_service" ><i class="fas fa-check text-success mr-1"></i>' . labels('approve_service', 'Approve Service') . '</a>';
            }
        }
        if ($this->ionAuth->isAdmin()) {
        } else if (isset($where['user_id']) && !empty($where['user_id'])) {
            $operations .= '<a class="dropdown-item" href="' . base_url('/partner/services/edit_service/' . $row['id']) . '" ><i class="fa fa-pen mr-1 text-primary"></i>' . labels('edit_service', 'Edit Service') . '</a>';
        }
        if ($this->ionAuth->isAdmin()) {
            $operations .= '<a class="dropdown-item" href="' . base_url('/admin/services/service_detail/' . $row['id']) . '" ><i class="fa fa-eye mr-1 text-primary"></i>' . labels('view_service', 'View Service') . '</a>';
        }
        $operations .= '</div></div>';


        $total_orders=fetch_details('order_services',['service_id'=>$row['id']],'id');
    

        // Merge all data
        $tempRow = array_merge($row, [
            'image_of_the_service' => $images,
            'status' => ($row['status'] == 1) ? 'active' : 'deactive',
            'status_number' => ($row['status'] == 1) ? '1' : '0',
            'is_pay_later_allowed' => ($row['is_pay_later_allowed'] == 1) ? '1' : '0',
            'status_badge' => $status_badge,
            'is_cancelable' => $is_cancelable,
            'cancelable' => $row['is_cancelable'],
            'total_bookings'=>count($total_orders),
        ], $ratings, $taxInfo, $taxData);

        if ($from_app) {
            $tempRow['in_cart_quantity'] = isset($additional_data['user_id']) ?
                in_cart_qty($row['id'], $additional_data['user_id']) : "";
        }
        if ($from_app == false) {

            $tempRow['operations'] = $operations;
            $approved_by_admin_badge = ($row['approved_by_admin'] == 1) ?
                "<div class='  text-emerald-success  ml-3 mr-3 mx-5'>Yes
        </div>" :
                "<div class=' text-emerald-danger ml-3 mr-3 '>No
        </div>";
            $tempRow['approved_by_admin_badge'] = $approved_by_admin_badge;
        }
        return $tempRow;
    }


    private function calculateTaxValues($row)
    {
        $taxPercentageData = fetch_details('taxes', ['id' => $row['tax_id']], ['percentage']);
        $tempRow = [];
        if (!empty($taxPercentageData)) {
            $taxPercentage = $taxPercentageData[0]['percentage'];
        } else {
            $taxPercentage = 0;
        }
        if ($row['discounted_price'] == "0") {
            if ($row['tax_type'] == "excluded") {
                $tempRow['tax_value'] = number_format((intval(($row['price'] * ($taxPercentage) / 100))), 2);
                $tempRow['price_with_tax']  = strval($row['price'] + ($row['price'] * ($taxPercentage) / 100));
                $tempRow['original_price_with_tax'] = strval($row['price'] + ($row['price'] * ($taxPercentage) / 100));
            } else {
                $tempRow['tax_value'] = "";
                $tempRow['price_with_tax']  = strval($row['price']);
                $tempRow['original_price_with_tax'] = strval($row['price']);
            }
        } else {
            if ($row['tax_type'] == "excluded") {
                $tempRow['tax_value'] = number_format((intval(($row['discounted_price'] * ($taxPercentage) / 100))), 2);
                $tempRow['price_with_tax']  = strval($row['discounted_price'] + ($row['discounted_price'] * ($taxPercentage) / 100));
                $tempRow['original_price_with_tax'] = strval($row['price'] + ($row['discounted_price'] * ($taxPercentage) / 100));
            } else {
                $tempRow['tax_value'] = "";
                $tempRow['price_with_tax']  = strval($row['discounted_price']);
                $tempRow['original_price_with_tax'] = strval($row['price']);
            }
        }

        return $tempRow;
    }
    // Helper method to format app response
    private function formatAppResponse($rows, $stats, $for_new_total)
    {
        $db = \Config\Database::connect();

        if ($for_new_total) {
            $new_stats = $db->table('services s')
                ->select('COUNT(s.id) as total, MAX(s.price) as max_price, MIN(s.price) as min_price,
                         MIN(s.discounted_price) as min_discount_price, MAX(s.discounted_price) as max_discount_price')
                ->where('s.user_id', $for_new_total)
                ->get()
                ->getRowArray();
        }

        return [
            'total' => $stats['total'] ?? count($rows),
            'min_price' => $stats['min_price'],
            'max_price' => $stats['max_price'],
            'min_discount_price' => $stats['min_discount_price'],
            'max_discount_price' => $stats['max_discount_price'],
            'data' => $rows,
            'new_total' => $new_stats['total'] ?? null,
            'new_min_price' => $new_stats['min_price'] ?? null,
            'new_max_price' => $new_stats['max_price'] ?? null,
            'new_min_discount_price' => $new_stats['min_discount_price'] ?? null,
            'new_max_discount_price' => $new_stats['max_discount_price'] ?? null
        ];
    }
}
