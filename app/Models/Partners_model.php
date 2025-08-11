<?php

namespace App\Models;

use CodeIgniter\Model;

class Partners_model extends Model
{
    protected $table = 'partner_details';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'partner_id',
        'company_name',
        'about',
        'national_id',
        'address',
        'passport',
        'address_id',
        'banner',
        'tax_name',
        'tax_number',
        'bank_name',
        'account_number',
        'account_name',
        'bank_code',
        'swift_code',
        'advance_booking_days',
        'type',
        'number_of_members',
        'admin_commission',
        'visiting_charges',
        'is_approved',
        'service_range',
        'ratings',
        'number_of_ratings',
        'payable_commision',
        'other_images',
        'long_description',
        'at_store',
        'at_doorstep',
        'chat',
        'need_approval_for_the_service',
        'pre_chat',
        'custom_job_categories',
        'slug',

    ];
   
    public function list($from_app = false, $search = '', $limit = 10, $offset = 0, $sort = 'id', $order = 'ASC', $where = [], $column_name = 'pd.id', $whereIn = [], $additional_data = [], $limit_for_subscription = null)
    {
        $disk = fetch_current_file_manager();

// print_r($where); echo "<br>";
// print_R($column_name);
//         echo "<br>";
//         print_R($whereIn); die;
        
        $multipleWhere = '';
        $db      = \Config\Database::connect();
        $builder = $db->table('partner_details pd');
        $values = ['7'];
        if ($search and $search != '') {
            $searchWhere = [
                '`pd.id`' => $search,
                '`pd.company_name`' => $search,
                '`pd.tax_name`' => $search,
                '`pd.tax_number`' => $search,
                '`pd.bank_name`' => $search,
                '`pd.account_number`' => $search,
                '`pd.account_name`' => $search,
                '`pd.bank_code`' => $search,
                '`pd.swift_code`' => $search,
                '`pd.created_at`' => $search,
                '`pd.updated_at`' => $search,
                '`username`' => $search,
                '`ps.status`' => $search,
            ];
            if (isset($searchWhere) && !empty($searchWhere)) {
                $builder->groupStart();
                $builder->orLike($searchWhere);
                $builder->groupEnd();
            }
        }

        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        if (isset($searchWhere) && !empty($searchWhere)) {
            $builder->groupStart();
            $builder->orLike($searchWhere);
            $builder->groupEnd();
        }
        $builder->select(' COUNT( DISTINCT pd.id) as `total`')
            ->join('users u', 'pd.partner_id = u.id')
            ->join('users_groups ug', 'ug.user_id = u.id')
            ->join('partner_subscriptions ps', 'ps.partner_id = pd.partner_id', 'left')
            ->where('ug.group_id', 3)->whereNotIn('pd.is_approved', $values);
        if (isset($additional_data['latitude']) && !empty($additional_data['latitude'])) {
            $parnter_ids = get_near_partners($additional_data['latitude'], $additional_data['longitude'], $additional_data['max_serviceable_distance'], true);
            if (isset($parnter_ids) && !empty($parnter_ids) && !isset($parnter_ids['error'])) {
                $builder->whereIn('pd.partner_id', $parnter_ids);
            }
        }
        if (isset($_GET['partner_filter']) && $_GET['partner_filter'] != '') {
            $builder->where('pd.is_approved', $_GET['partner_filter']);
        }
        if (isset($whereIn) && !empty($whereIn)) {
            $builder->where('ps.status', 'active')->whereIn($column_name, $whereIn);
            // print_r($builder->get()->getResultArray()); die;
        }
        if (isset($additional_data['latitude']) && !empty($additional_data['latitude'])) {
            $latitude = $additional_data['latitude'];
            $longitude = $additional_data['longitude'];
        }

        $partner_count = $builder->get()->getResultArray();
        // print_r($db->getLastQuery());
        // die;
        $total = $partner_count[0]['total'];
        if (isset($additional_data['latitude']) && !empty($additional_data['latitude']) &&  !empty($limit_for_subscription)  && isset($limit_for_subscription)) {
            if (isset($where) && !empty($where)) {
                $builder->where($where);
            }
            if (isset($searchWhere) && !empty($searchWhere)) {
                $builder->groupStart();
                $builder->orLike($searchWhere);
                $builder->groupEnd();
            }
            $builder->select(
                "pd.*,
                u.username as partner_name, u.balance, u.image, u.active, u.email, u.phone, u.city, u.longitude, u.latitude, u.payable_commision,
                ug.user_id, ug.group_id,
                ps.id as partner_subscription_id, ps.status as partner_subscription_status, ps.max_order_limit,
                COUNT(DISTINCT CASE WHEN pd.partner_id AND o.status = 'completed' then o.id END) as number_of_orders,
                st_distance_sphere(POINT('$longitude','$latitude'), POINT(`u`.`longitude`, `u`.`latitude`))/1000  as distance,
                MAX(DISTINCT CASE WHEN pd.partner_id then pc.discount END) as maximum_discount_percentage,
                MAX(DISTINCT CASE WHEN pd.partner_id then pc.max_discount_amount END) as maximum_discount_up_to,
                (st_distance_sphere(POINT('$longitude','$latitude'), POINT(`u`.`longitude`, `u`.`latitude`))/1000) < " . $additional_data['max_serviceable_distance'] . " as is_Available_at_location"
            )
                ->join('users u', 'pd.partner_id = u.id')
                ->join('users_groups ug', 'ug.user_id = u.id')
                ->join('orders o', 'o.partner_id = pd.partner_id AND o.parent_id IS NULL', 'left')
                ->join('partner_subscriptions ps', 'ps.partner_id = pd.partner_id')
                ->join('promo_codes pc', 'pc.partner_id = pd.partner_id', 'left')
                ->where('ug.group_id', 3)->where('pd.is_approved','1');
            // for web :: web ma scroll ma issue ave ena mate aa karel che  jyare partner id pass thay tyare distance vadi condition n check thavi joiye
            if (!array_key_exists('pd.partner_id', $where)) {
                $builder->having('distance < ' . $additional_data['max_serviceable_distance']);
            }
            $builder->where('ps.status', 'active')
                ->groupBy('pd.partner_id');
        } else if (isset($additional_data['latitude']) && !empty($additional_data['latitude'])) {
            $builder->select(
                "pd.*,
            u.username as partner_name,u.balance,u.image,u.active, u.email, u.phone, u.city,u.longitude,u.latitude,u.payable_commision,
            ug.user_id,ug.group_id,
            ps.id as partner_subscription_id,ps.status,ps.max_order_limit,
            COUNT(DISTINCT CASE WHEN pd.partner_id AND o.status = 'completed' then o.id END) as number_of_orders,
            st_distance_sphere(POINT('$longitude','$latitude'), POINT(`u`.`longitude`, `u`.`latitude` ))/1000  as distance,
            MAX(DISTINCT CASE WHEN pd.partner_id then pc.discount END) as maximum_discount_percentage,
            MAX(DISTINCT CASE WHEN pd.partner_id then pc.max_discount_amount END) as maximum_discount_up_to,"
            )
                ->join('users u', 'pd.partner_id = u.id')
                ->join('users_groups ug', 'ug.user_id = u.id')
                ->join('orders o', 'o.partner_id = pd.partner_id AND o.parent_id IS NULL', 'left')
                ->join('partner_subscriptions ps', 'ps.partner_id = pd.partner_id', 'left')
                ->join('promo_codes pc', 'pc.partner_id = pd.partner_id', 'left')
                ->having('distance < ' . $additional_data['max_serviceable_distance'])
                ->where('ug.group_id', 3)->where('pd.is_approved','1')
                ->groupBy('pd.partner_id');
        } else {

            $subQueryOrders = "(SELECT o.partner_id, COUNT(o.id) AS number_of_orders
                    FROM orders o
                    WHERE o.status = 'completed' AND o.parent_id IS NULL
                    GROUP BY o.partner_id)";

            $subQueryDiscounts = "(SELECT pc.partner_id, 
                              MAX(pc.discount) AS maximum_discount_percentage, 
                              MAX(pc.max_discount_amount) AS maximum_discount_up_to
                       FROM promo_codes pc
                       GROUP BY pc.partner_id)";

            $builder->select("
                pd.*,
                u.username as partner_name, u.balance, u.image, u.active, u.email, u.phone, 
                u.city, u.longitude, u.latitude, u.payable_commision,
                ug.user_id, ug.group_id,
                ps.id as partner_subscription_id, ps.status,
                pt.day, pt.opening_time, pt.closing_time, pt.is_open,
                COALESCE(OrdersSummary.number_of_orders, 0) AS number_of_orders,
                COALESCE(DiscountSummary.maximum_discount_percentage, 0) AS maximum_discount_percentage,
                COALESCE(DiscountSummary.maximum_discount_up_to, 0) AS maximum_discount_up_to
            ")
                ->join('users u', 'pd.partner_id = u.id')
                ->join('users_groups ug', 'ug.user_id = u.id')
                ->join("($subQueryOrders) AS OrdersSummary", 'OrdersSummary.partner_id = pd.partner_id', 'left')
                ->join("($subQueryDiscounts) AS DiscountSummary", 'DiscountSummary.partner_id = pd.partner_id', 'left')
                ->join('partner_subscriptions ps', 'ps.partner_id = pd.partner_id', 'left')
                ->join('partner_timings pt', 'pt.partner_id = pd.partner_id', 'left')
                ->where('ug.group_id', 3)
                ->groupBy('pd.partner_id');
        }
        if (isset($_GET['partner_filter']) && $_GET['partner_filter'] != '') {
            $builder->where('pd.is_approved', $_GET['partner_filter']);
        }
        if (isset($searchWhere) && !empty($searchWhere)) {
            $builder->groupStart();
            $builder->orLike($searchWhere);
            $builder->groupEnd();
        }
        if (isset($whereIn) && !empty($whereIn)) {
            $builder->where('ps.status', 'active')->whereIn($column_name, $whereIn);
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        $partner_record = $builder->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();
        $bulkData = array();
        $bulkData['total'] = $total;
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
        $rows = array();
        $tempRow = array();
        foreach ($partner_record as $row) {

            $profile = "";
            $defaultImage = base_url('public/backend/assets/profiles/default.png');
            $imagePath = '';



            if ($disk == "local_server") {
                if (!empty($row['image'])) {
                    $imageUrl = filter_var($row['image'], FILTER_VALIDATE_URL) ? $row['image'] : '';
                    $imagePath = file_exists(FCPATH . 'public/backend/assets/profile/' . $row['image']) ? base_url('public/backend/assets/profile/' . $row['image']) : (file_exists(FCPATH . 'public/uploads/users/partners/' . $row['image']) ? base_url('public/uploads/users/partners/' . $row['image']) : (file_exists(FCPATH . $row['image']) ? base_url($row['image']) : $defaultImage));
                    $imageSrc = $imageUrl ?: $imagePath;
                } else {
                    $imageSrc = $defaultImage;
                }
            } else if ($disk == "aws_s3") {
                $imageSrc = fetch_cloud_front_url('profile', $row['image']);
            } else {
                $imageSrc = $defaultImage;
            }


            $profile = '<div class="o-media o-media--middle">
                        <a href="' . $imageSrc . '" data-lightbox="image-1">
                            <img class="o-media__img images_in_card" src="' . $imageSrc . '" alt="' . $row['partner_name'] . '">
                        </a>';

            if ($row['email'] != '' && $row['phone'] != "") {
                $contact_detail =
                    '<span>
                    ' .  ((defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0)  ? 'wrteam.' . substr($row['email'], 6) : $row['email']) . '
                </span>';
            } elseif ($row['email'] != '') {
                $contact_detail =  ((defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0)) ?  'wrteam.' . substr($row['email'], 6) : $row['email'];
            } else {
                $contact_detail = ((defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0)) ? 'XXX-' . substr($row['phone'], 6) :   $row['phone'];
            }
            $profile .= '<a href="' . base_url('/admin/partners/general_outlook/' . $row['partner_id']) . '"><div class="o-media__body">
                <div class="provider_name_table">' .     $row['partner_name'] . '</div>
                <div class="provider_email_table">' . $contact_detail . '</div>
                </div>
                </div></a>';
            $status = '';
            $status = '<div class="dropdown ">
            <a class="" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <button class="btn btn-secondary   btn-sm px-3"><i class="fas fa-ellipsis-v "></i></button>
          </a>
            <div class="dropdown-menu dropdown-scrollbar custom_dropdown" aria-labelledby="dropdownMenuButton">';
            if ($from_app == false) {
                if ($permissions['update']['partner'] == 1) {
                    $status .= '<a class="dropdown-item" href="' . base_url('/admin/partners/edit_partner/' . $row['partner_id']) . '"><i class="fa fa-pen mr-1 text-primary"></i>'. labels('edit_provider' ,'Edit Provider') . '</a>';
                }
                if ($permissions['delete']['partner'] == 1) {
                    $status .= '<a class="dropdown-item delete_partner" href="#" id="delete_partner"> <i class="fa fa-trash mr-1 text-danger"></i>'. labels('delete_provider', 'Delete Provider') .'</a>';
                }
                if ($permissions['read']['partner'] == 1) {
                    $status .= '</i><a class="dropdown-item" href="' . base_url('/admin/partners/general_outlook/' . $row['partner_id']) . '"> <i class="fa fa-eye mr-1 text-success"></i>'. labels('view_provider', 'View Provider') .'</a>';
                }
                $status .= '<a class="dropdown-item" href="' . base_url('/admin/partners/duplicate/' . $row['partner_id']) . '"><i class="fa fa-copy mr-1 text-primary"></i>'. labels('duplicate_provider', 'Duplicate Provider') .'</a>';
            }
            $status .= ($row['is_approved'] == 1) ?
                '<a class="dropdown-item disapprove_partner" href="#" id="disapprove_partner"> <i class="fas fa-times text-danger mr-1"></i>'. labels('disapprove_provider' ,'Disapprove Provider') .'</a>' :
                '<a class="dropdown-item approve_partner" href="#" id="approve_partner" ><i class="fas fa-check text-success mr-1"></i>'. labels('approve_provider' ,'Approve Provider') .'</a>';
            $status .= '</div></div>';
            if ($from_app) {
                // if (check_exists(base_url('public/backend/assets/profiles/' . $row['image'])) || check_exists(base_url('/public/uploads/users/partners/' . $row['image'])) || check_exists($row['image'])) {
                //     if (filter_var($row['image'], FILTER_VALIDATE_URL)) {
                //         $tempRow['image'] = $row['image'];
                //     } else {
                //         $row['image'] = (file_exists(FCPATH . 'public/backend/assets/profiles/' . $row['image'])) ? base_url('public/backend/assets/profiles/' . $row['image']) : ((file_exists(FCPATH . $row['image'])) ? base_url($row['image']) : ((!file_exists(FCPATH . "public/uploads/users/partners/" . $row['image'])) ? base_url("public/backend/assets/profiles/default.png") : base_url("public/uploads/users/partners/" . $row['image'])));
                //         $tempRow['image'] = $row['image'];
                //     }
                // }

                if (isset($additional_data['customer_id']) && !empty($additional_data['customer_id'])) {
                    $is_bookmarked = is_bookmarked($additional_data['customer_id'], $row['partner_id'])[0]['total'];
                    // $is_bookmarked=is_bookmarked($additional_data['customer_id'],$row['partner_id']);
                    if (isset($is_bookmarked) && $is_bookmarked == 1) {
                        $tempRow['is_bookmarked'] = '1';
                    } else if (isset($is_bookmarked) && $is_bookmarked == 0) {
                        $tempRow['is_bookmarked'] = '0';
                    } else {
                        $tempRow['is_bookmarked'] = '0';
                    }
                }
                if ($disk === "local_server") {
                    $image_paths = [
                        base_url('public/backend/assets/profile/' . $row['image']),
                        base_url('public/uploads/users/partners/' . $row['image']),
                        $row['image']
                    ];

                    $valid_image_path = array_filter($image_paths, 'check_exists');
                    $valid_image_path = reset($valid_image_path);

                    if ($valid_image_path) {
                        if (filter_var($row['image'], FILTER_VALIDATE_URL)) {
                            $tempRow['image'] = $row['image'];
                        } else {
                            if (file_exists(FCPATH . 'public/backend/assets/profile/' . $row['image'])) {
                                $row['image'] = base_url('public/backend/assets/profile/' . $row['image']);
                            } elseif (file_exists(FCPATH . $row['image'])) {
                                $row['image'] = base_url($row['image']);
                            } elseif (!file_exists(FCPATH . "public/uploads/users/partners/" . $row['image'])) {
                                $row['image'] = base_url("public/backend/assets/profile/default.png");
                            } else {
                                $row['image'] = base_url("public/uploads/users/partners/" . $row['image']);
                            }
                            $tempRow['image'] = $row['image'];
                        }
                    } else {
                        $tempRow['image'] = base_url("public/backend/assets/profiles/default.png");
                    }
                } elseif ($disk === "aws_s3") {

                    $row['image'] = fetch_cloud_front_url('profile', $row['image']);
                    $tempRow['image'] = $row['image'];
                } else {
                    $tempRow['image'] = $row['image'];
                }
            }
            $tempRow['address'] = (!empty($row['address']) && isset($row['address'])) ? $row['address'] : '';
            if (($row['type'] == 0)) {
                $type = "Individual";
            } else {
                $type = "Organization";
            }
            $label = ($row['is_approved'] == 1) ?
                "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-success text-emerald-success dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3 mx-5'>Approved
                    </div>" :
                "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-danger text-emerald-danger dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3 '>Disapproved
                    </div>";

            $rating_data = $db->table('services_ratings sr')
                ->select('
                    COUNT(sr.rating) as number_of_rating,
                    SUM(sr.rating) as total_rating,
                    (SUM(sr.rating) / COUNT(sr.rating)) as average_rating
                ')
                ->join('services s', 'sr.service_id = s.id', 'left')
                ->join('custom_job_requests cj', 'sr.custom_job_request_id = cj.id', 'left')
                ->join('partner_bids pd', 'pd.custom_job_request_id = cj.id', 'left')
                ->where("(s.user_id = {$row['partner_id']}) OR (pd.partner_id = {$row['partner_id']})")
                ->get()->getResultArray();

            if ($disk == "local_server") {
                $tempRow['banner_edit'] = base_url($row['banner']);
            } else if ($disk == "aws_s3") {
                $tempRow['banner_edit'] = fetch_cloud_front_url('banner', $row['banner']);;
            } else {
                $tempRow['banner_edit'] = base_url($row['banner']);
            }
            if (!empty($row['banner'])) {


                if ($disk == "local_server") {
                    if ((file_exists($row['banner']))) {
                        $row['banner'] =  base_url($row['banner']);
                    } else {
                        $row['banner'] = base_url('public/backend/assets/profiles/default.png');
                    }
                } else if ($disk == "aws_s3") {
                    $row['banner'] =  fetch_cloud_front_url('banner', $row['banner']);
                } else {
                    $row['banner'] = base_url('public/backend/assets/profiles/default.png');
                }
                $tempRow['banner_image'] = $row['banner'];
            } else {
                $tempRow['banner_image'] = '';
            }

            if (!empty($row['other_images'])) {
                $row['other_images'] = array_map(function ($data) use ($row, $disk) {
                    if ($disk === "local_server") {
                        return base_url($data);
                    } elseif ($disk === "aws_s3") {
                        return fetch_cloud_front_url('partner', $data);
                    }
                }, json_decode($row['other_images'], true));
            } else {
                $row['other_images'] = [];
            }

            $cash_collection_button = '<button class="btn btn-success btn-sm edit_cash_collection" data-id="' . $row['id'] . '" data-toggle="modal" data-target="#update_modal"><i class="fa fa-pen" aria-hidden="true"></i> </button> ';


            $tempRow['id'] = $row['id'];
            $tempRow['is_Available_at_location'] = isset($row['is_Available_at_location']) ? $row['is_Available_at_location'] : "";
            $tempRow['partner_id'] = $row['partner_id'];
            $tempRow['city'] = $row['city'];
            $tempRow['partner_profile'] = $profile;
            $tempRow['company_name'] = $row['company_name'];
            $tempRow['balance'] = $row['balance'];
            $tempRow['longitude'] = $row['longitude'];
            $tempRow['latitude'] = $row['latitude'];
            $tempRow['mobile'] = ((defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0)) ?  "XXXXX" . substr($row['phone'], 6) : $row['phone'];
            $tempRow['about'] = $row['about'];
            $tempRow['address'] = (!empty($row['address']) && isset($row['address'])) ? $row['address'] : '';

            if ($disk == "local_server") {
                $national_id = ($row['national_id'] != '') ?  base_url($row['national_id']) : '';
            } else if ($disk == "aws_s3") {
                $national_id = fetch_cloud_front_url('national_id', $row['national_id']);
            } else {
                $national_id = ($row['national_id'] != '') ?  base_url($row['national_id']) : '';
            }

            if ($disk == "local_server") {
                $address_id = ($row['address_id'] != '') ? base_url($row['address_id']) : '';
            } else if ($disk == "aws_s3") {
                $address_id = fetch_cloud_front_url('address_id', $row['address_id']);
            } else {
                $address_id = ($row['address_id'] != '') ?  base_url($row['address_id']) : '';
            }



            if ($disk == "local_server") {
                $passport = ($row['passport'] != '') ? base_url($row['passport']) : '';
            } else if ($disk == "aws_s3") {
                $passport = fetch_cloud_front_url('passport', $row['passport']);
            } else {
                $passport = ($row['passport'] != '') ?  base_url($row['passport']) : '';
            }

            $tempRow['national_id'] = $national_id;
            $tempRow['passport'] = $passport;
            $tempRow['partner_name'] = $row['partner_name'];
            $tempRow['tax_name'] = $row['tax_name'];
            $tempRow['tax_number'] = $row['tax_number'];
            $tempRow['bank_name'] = $row['bank_name'];
            $tempRow['account_number'] = $row['account_number'];
            $tempRow['account_name'] = $row['account_name'];
            $tempRow['bank_code'] = $row['bank_code'];
            $tempRow['swift_code'] = $row['swift_code'];
            $tempRow['number_of_members'] = $row['number_of_members'];
            $tempRow['admin_commission'] = $row['admin_commission'];
            $tempRow['type'] = $type;
            $tempRow['email'] = ((defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0)) ? 'wrteam.' . substr($row['email'], 6) :  $row['email'];
            $tempRow['image'] =  (!empty($row['image']) && isset($row['image'])) ? $row['image'] : '';
            $tempRow['advance_booking_days'] = $row['advance_booking_days'];
            $tempRow['number_of_members'] = $row['number_of_members'];
            $tempRow['ratings'] = $row['ratings'];
            $tempRow['number_of_ratings'] = $row['number_of_ratings'];
            $tempRow['visiting_charges'] = $row['visiting_charges'];
            $tempRow['contact_detail'] = $contact_detail;
            $tempRow['is_approved_edit'] = $row['is_approved'];
            $tempRow['payable_commision'] = intval($row['payable_commision']);
            $tempRow['cash_collection_button'] = $cash_collection_button;
            $tempRow['checkbox'] = "  <input type='checkbox' class='select-item checkbox' name='select-item'";
            $tempRow['long_description'] = $row['long_description'];
            $tempRow['other_images'] = $row['other_images'];
            $tempRow['at_doorstep'] = isset($row['at_doorstep']) ? $row['at_doorstep'] : "0";
            $tempRow['at_store'] = isset($row['at_store']) ? $row['at_store'] : "0";
            $tempRow['post_booking_chat'] = isset($row['chat']) ? $row['chat'] : "0";
            $tempRow['pre_booking_chat'] = isset($row['pre_chat']) ? $row['pre_chat'] : "0";
            $tempRow['address_id'] = $address_id;
            $tempRow['slug'] = $row['slug'];



            if (isset($additional_data['latitude']) && !empty($additional_data['latitude'])) {
                $tempRow['distance'] = $row['distance'];
            }


            $total_services_of_providers = fetch_details('services', ['user_id' => $row['partner_id'], 'at_store' => $row['at_store'], 'at_doorstep' => $row['at_doorstep']], ['id']);
            $tempRow['total_services'] = count($total_services_of_providers);


            if (check_partner_availibility($row['partner_id'])) {
                $tempRow['is_available_now'] = true;
            } else {
                $tempRow['is_available_now'] = false;
            }
            $tempRow['status'] = $label;
            if (!empty($rating_data)) {
                $tempRow['ratings'] = '<i class="fa-solid fa-star text-warning"></i>(' . (($rating_data[0]['average_rating'] != "") ? sprintf('%0.1f', $rating_data[0]['average_rating']) : '0.0') . ')';
                if ($from_app == false) {
                    $tempRow['ratings'] = '<i class="fa-solid fa-star text-warning"></i>(' . (($rating_data[0]['average_rating'] != "") ? sprintf('%0.1f', $rating_data[0]['average_rating']) : '0.0') . ')';
                } else {
                    $tempRow['ratings'] =  (($rating_data[0]['average_rating'] != "") ? sprintf('%0.1f', $rating_data[0]['average_rating']) : '0.0');
                }
            }
            $rate_data = get_ratings($row['partner_id']);
            $tempRow['1_star'] = $rate_data[0]['rating_1'];
            $tempRow['2_star'] = $rate_data[0]['rating_2'];
            $tempRow['3_star'] = $rate_data[0]['rating_3'];
            $tempRow['4_star'] = $rate_data[0]['rating_4'];
            $tempRow['5_star'] = $rate_data[0]['rating_5'];
            $partner_timings = (fetch_details('partner_timings', ['partner_id' => $row['partner_id']]));
            foreach ($partner_timings as $pt) {
                $tempRow[$pt['day'] . '_is_open'] = $pt['is_open'];
                $tempRow[$pt['day'] . '_opening_time'] = $pt['opening_time'];
                $tempRow[$pt['day'] . '_closing_time'] = $pt['closing_time'];
            }
            if ($from_app == false) {
                $tempRow['discount'] =  $row['maximum_discount_percentage'];
                $tempRow['discount_up_to'] =  $row['maximum_discount_up_to'];
                $tempRow['is_approved'] = ($from_app == true) ? $row['is_approved'] : $status;
                $tempRow['created_at'] = $row['created_at'];
            } else {
                if (isset($additional_data['customer_id']) && !empty($additional_data['customer_id'])) {
                    $customer_id = $additional_data['customer_id'];
                    $is_favorite = is_favorite($customer_id, $row['partner_id']);
                    $tempRow['is_favorite'] = ($is_favorite) ? '1' : '0';
                }
                $tempRow['discount'] =  $row['maximum_discount_percentage'];
                $tempRow['discount_up_to'] =  $row['maximum_discount_up_to'];
                $tempRow['number_of_orders'] = $row['number_of_orders'];
                $tempRow['status'] = $row['is_approved'];
                unset($tempRow['partner_profile']);
                unset($tempRow['contact_detail']);
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
    public function unsettled_commission_list($from_app = false, $search = '', $limit = 10, $offset = 0, $sort = 'id', $order = 'ASC', $where = [], $column_name = 'pd.id', $whereIn = [], $additional_data = [])
    {
        $multipleWhere = '';
        $db      = \Config\Database::connect();
        $builder = $db->table('partner_details pd');
        $values = ['7'];
        if ($search and $search != '') {
            $multipleWhere = [
                '`pd.id`' => $search,
                '`pd.company_name`' => $search,
                '`pd.tax_name`' => $search,
                '`pd.tax_number`' => $search,
                '`pd.bank_name`' => $search,
                '`pd.account_number`' => $search,
                '`pd.account_name`' => $search,
                '`pd.bank_code`' => $search,
                '`pd.swift_code`' => $search,
                '`pd.created_at`' => $search,
                '`pd.updated_at`' => $search,
            ];
        }
        $builder->select(' COUNT(pd.id) as `total` ')->join('users u', 'pd.partner_id = u.id')
            ->join('users_groups ug', 'ug.user_id = u.id')
            ->where('ug.group_id', 3)->whereNotIn('pd.is_approved', $values);
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->groupStart();
            $builder->orWhere($multipleWhere);
            $builder->groupEnd();
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        if (isset($whereIn) && !empty($whereIn)) {
            $builder->whereIn($column_name, $whereIn);
        }
        if (isset($additional_data['latitude']) && !empty($additional_data['latitude'])) {
            $parnter_ids = get_near_partners($additional_data['latitude'], $additional_data['longitude'], $additional_data['max_serviceable_distance'], true);
            if (isset($parnter_ids) && !empty($parnter_ids) && !isset($parnter_ids['error'])) {
                $builder->whereIn('pd.partner_id', $parnter_ids);
            }
        }
        $partner_count = $builder->get()->getResultArray();
        $total = $partner_count[0]['total'];
        if (isset($additional_data['latitude']) && !empty($additional_data['latitude'])) {
            $parnter_ids = get_near_partners($additional_data['latitude'], $additional_data['longitude'], $additional_data['city_id'], true);
            if (isset($parnter_ids) && !empty($parnter_ids) && !isset($parnter_ids['error'])) {
                $builder->whereIn('pd.partner_id', $parnter_ids);
            }
        }
        $builder->select('pd.*,u.username as partner_name,u.balance,u.image,u.active, u.email, u.phone, ug.user_id,ug.group_id')
            ->join('users u', 'pd.partner_id = u.id')
            ->join('users_groups ug', 'ug.user_id = u.id')
            ->where('ug.group_id', 3);
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->groupStart();
            $builder->orLike($multipleWhere);
            $builder->groupEnd();
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        if (isset($whereIn) && !empty($whereIn)) {
            $builder->whereIn($column_name, $whereIn);
        }
        $builder->whereNotIn('pd.is_approved', $values);
        $partner_record = $builder->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        foreach ($partner_record as $row) {
            $operations =  '<button class="btn btn-success btn-sm pay-out" data-toggle="modal" data-target="#exampleModal"> 
            <i class="fa fa-pencil" aria-hidden="true"></i> 
            </button> ';
            $tempRow['partner_id'] = $row['partner_id'];
            $tempRow['balance'] = $row['balance'];
            $tempRow['company_name'] = $row['company_name'];
            $tempRow['operations'] = $operations;
            $tempRow['partner_name'] = $row['partner_name'];
            if ($from_app == false) {
                $tempRow['created_at'] = $row['created_at'];
            } else {
                $tempRow['status'] = $row['is_approved'];
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
    public function review()
    {
        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
        $ratings = new Service_ratings_model();
        $data = $ratings->ratings_list(true, $search, $limit, $offset, $sort, $order, ['s.user_id' => $this->user_details['id']]);
        $bulkData = array();
        $rows = array();
        $tempRow = array();
        foreach ($data['data'] as $row) {
            $tempRow['id'] = $row['id'];
            $tempRow['user_name'] = $row['user_name'];
            $tempRow['profile_image'] = (!empty($row['profile_image']) && isset($row['profile_image'])) ? $row['profile_image'] : '';
            $tempRow['service_name'] = $row['service_name'];
            $tempRow['rating'] = $row['rating'];
            $tempRow['comment'] = $row['comment'];
            $tempRow['rated_on'] = $row['rated_on'];
            $tempRow['images'] = $row['images'];
            $rate_data = get_ratings($row['partner_id']);
            $tempRow['1_star'] = $rate_data[0]['rating_1'];
            $tempRow['2_star'] = $rate_data[0]['rating_2'];
            $tempRow['3_star'] = $rate_data[0]['rating_3'];
            $tempRow['4_star'] = $rate_data[0]['rating_4'];
            $tempRow['5_star'] = $rate_data[0]['rating_5'];
            $rows[] = $tempRow;
        }
        return $bulkData;
    }
}
