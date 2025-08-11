<?php

namespace App\Models;

use CodeIgniter\Model;

class Users_model extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'id',
        'username',
        'active',
        'first_name',
        'last_name',
        'ip_address',
        'password',
        'email',
        'balance',
        'activation_selector',
        'activation_code',
        'forgotten_password_selector',
        'forgotten_password_code',
        'forgotten_password_time',
        'remember_selector',
        'remember_code',
        'created_on',
        'last_login',
        'company',
        'phone',
        'fcm_id',
        'image',
        'api_key',
        'friends_code',
        'referral_code',
        'city_id',
        'city',
        'latitude',
        'longitude',
        'country_code',
        'platform',
        'web_fcm_id',
        'panel_fcm_id',
    ];
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $useTimestamps = true;
    public function get_records($select_field = '*', $where = '')
    {
        $this->builder()->like("email", $where, 'before');
        $this->builder()->like("first_name", $where);
        $this->builder()->like("last_name", $where);
        $this->builder()->select($select_field);
        $data = [];
        foreach ($this->builder()->get()->getResultArray() as $record) {
            $data[] = array("id" => $record['id'], "email" => $record['email']);
        }
        return $data;
    }
    public  function list($from_app = false, $search = '', $limit = 10, $offset = 0, $sort = 'id', $order = 'ASC', $where = [], $column_name = 'pd.id', $whereIn = [])
    {
        $multipleWhere = '';
        $db      = \Config\Database::connect();
        $builder = $db->table('users u');
        if ($search and $search != '') {
            $multipleWhere = [
                '`u.id`' => $search,
                '`u.username`' => $search,
                '`u.email`' => $search,
                '`u.phone`' => $search,
            ];
        }
        $builder->select(' COUNT(u.id) as `total` ')
            ->join('users_groups ug', 'ug.user_id = u.id')
            ->where('ug.group_id', "2");
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
        if (isset($_GET['customer_filter']) && $_GET['customer_filter'] != '') {
            $builder->where('u.active',  $_GET['customer_filter']);
        }
        $user_count = $builder->get()->getResultArray();
        $total = $user_count[0]['total'];
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
        $builder->select('u.*,ug.group_id')
            ->join('users_groups ug', 'ug.user_id = u.id')
            ->where('ug.group_id', "2");
        if (isset($_GET['customer_filter']) && $_GET['customer_filter'] != '') {
            $builder->where('u.active',  $_GET['customer_filter']);
        }
        $user_record = $builder->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        $disk =fetch_current_file_manager();

        foreach ($user_record as $row) {
            if ($from_app) {
                // if (!empty($row['image'])) {
                //     $row['image'] = base_url('public/backend/assets/profiles/' . $row['image']);
                // }
                if(!empty($row['image'])){

                    $row['image'] = base_url($row['image']);
                }else{
                    $row['image'] = ($row['image']);
                    
                }
            }
            $profile = '';
            $profile = '<div class="o-media o-media--middle">';


            if($disk=="local_server"){
                if(!empty($row['image'])){

                    $imageUrl = base_url($row['image']);
                }else{
                    $imageUrl = ($row['image']);
                    
                }
            }else if($disk=="aws_s3"){
                $imageUrl = fetch_cloud_front_url('profile', $row['image']); // Construct the CloudFront URL
            }else{
                $imageUrl = "";
            }

            if ($row['image'] != '' && check_exists($imageUrl)) {
                $profile .= '<a href="' . $imageUrl . '" data-lightbox="image-1">';
                $profile .= '<img class="o-media__img images_in_card" src="' . $imageUrl . '" alt="' . $row['id'] . '">';
                $profile .= '</a>';
            } else {
                $profile .= '<a href="' . $imageUrl . '" data-lightbox="image-1">';
                $profile .= '<img class="o-media__img images_in_card" src="' .  base_url('public/backend/assets/profiles/default.png')  . '" data-lightbox="image-1" alt="' . $row['id'] . '">';
                $profile .= '</a>';
            }
            $email = (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0)
                ? (!empty($row['email']) ? 'wrteam.' . substr($row['email'], 7) : 'WRTEAM.test.com')
                : $row['email'];
            $phone = (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0)
                ?  ((!empty($row['phone'])) ? 'XXXX' . substr($row['phone'], 7) : "XXX-XX-XX  ")  : $row['phone'];
            $profile .= '<div class="o-media__body">
                <div class="provider_name_table">' . $row['username'] . '</div>
                <div class="provider_email_table">' . $email . ' - ' . $phone . '</div>
            </div>
            </div>';
            $operations = ($row['active'] == 1) ?
                '<button class="btn btn-warning deactivate_user" title="'. labels('deactivate_customer', 'Deactivate Customer') .'"> <i class="fa fa-ban" aria-hidden="true"></i> </button>'
                :
                '<button class="btn btn-success activate_user" title="'. labels('activate_customer', 'Activate Customer') .'"> <i class="fa fa-check" aria-hidden="true"></i> </button> ';
            $status = ($row['active'] == 1) ?
                "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-success text-emerald-success dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3 mx-5'>". labels('active', 'Active') ."
                </div>" :
                "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-danger text-emerald-danger dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3 '>". labels('deactive', 'Deactive') ."
                </div>";
            $tempRow['id'] = $row['id'];
            $tempRow['image'] = $profile;
            $tempRow['profile'] = $profile;
            $tempRow['username'] = $row['username'];
            $tempRow['active'] = $status;
            $tempRow['operations'] = $operations;
            $tempRow['email'] = ((defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0)) ? ((!empty($row['email'])) ? 'wrteam.' . substr($row['email'], 7) : "WRTEAM.test.com")  : $row['email'];
            $tempRow['phone'] = ((defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0)) ? ((!empty($row['phone'])) ? 'XXXX' . substr($row['phone'], 7) : "XXX-XX-XX  ")  : $row['phone'];
            $rows[] = $tempRow;
        }
        if ($from_app) {
            $response['total'] = $total;
            $response['data'] = $rows;
            return $response;
        } else {
            $bulkData['rows'] = $rows;
            return $bulkData;
        }
    }
    public function get_user($user_id, $select_field = '*')
    {
        $this->builder()->select($select_field)->where(['id' => $user_id]);
        $data = $this->builder()->get()->getResultArray();
        return $data;
    }
}
