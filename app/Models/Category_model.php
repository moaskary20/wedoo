<?php

namespace App\Models;

use CodeIgniter\Model;

class Category_model extends Model
{
    public $admin_id;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $dateFormat    = 'datetime';
    public function __construct()
    {
        $ionAuth = new \IonAuth\Libraries\IonAuth();
        $this->admin_id = ($ionAuth->isAdmin()) ? $ionAuth->user()->row()->id : 0;
    }
    protected $table = 'categories';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'image', 'parent_id', 'slug', 'admin_commission', 'status', 'dark_color', 'light_color'];
    public function list($from_app = false, $search = '', $limit = 10, $offset = 0, $sort = 'id', $order = 'ASC', $where = [])
    {
        $db      = \Config\Database::connect();
        $builder = $db->table('categories');
        $multipleWhere = [];
        $condition = $bulkData = $rows = $tempRow = [];
        if (isset($_GET['offset']))
            $offset = $_GET['offset'];
        if ((isset($search) && !empty($search) && $search != "") || (isset($_GET['search']) && $_GET['search'] != '')) {
            $search = (isset($_GET['search']) && $_GET['search'] != '') ? $_GET['search'] : $search;
            $multipleWhere = [
                '`id`' => $search,
                '`name`' => $search,
                '`admin_commission`' => $search
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
        if ($from_app) {
            $where['status'] = 1;
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
        if (isset($_GET['category_filter']) && $_GET['category_filter'] != '') {
            $builder->where('status',  $_GET['category_filter']);
        }
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
        if (isset($_GET['category_filter']) && $_GET['category_filter'] != '') {
            $builder->where('status',  $_GET['category_filter']);
        }
        $category_record = $builder->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        if ($from_app == false) {
            $db      = \Config\Database::connect();
            $builder = $db->table('users u');
            $builder->select('u.*,ug.group_id')
                ->join('users_groups ug', 'ug.user_id = u.id')
                ->whereIn('ug.group_id', [3, 1])
                ->where(['phone' => $_SESSION['identity']]);
            $user1 = $builder->get()->getResultArray();
            $permissions = get_permission($user1[0]['id']);
        }
        $disk=fetch_current_file_manager();

        foreach ($category_record as $row) {
            if ($from_app == false) {

                $imageTag = '<img height="60px" width="70px" style="padding:2px" class="rounded" src="%s" alt="">';
                $linkTag = '<a href="%s" data-lightbox="image-1">' . $imageTag . '</a>';
                if ($disk === 'local_server') {
                    // Local server file path
                    $basePath = base_url('/public/uploads/categories/' . $row['image']);
                    if (check_exists($basePath)) {
                        $category_image = sprintf($linkTag, $basePath, $basePath);
                    } else {
                        $category_image = 'Image not found';
                    }
                } else if ($disk == "aws_s3") {
                    $cloudFrontUrl = fetch_cloud_front_url('categories', $row['image']);
                    $category_image = sprintf($linkTag, $cloudFrontUrl, $cloudFrontUrl);
                } else {
                    $category_image = "no image found";
                }

                $operations = '';
                if ($from_app == false) {
                    if ($this->admin_id != 0) {
                        $operations = '<div class="dropdown">
                <a class="" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <button class="btn btn-secondary   btn-sm px-3"> <i class="fas fa-ellipsis-v "></i></button>
                </a>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">';
                        if ($permissions['update']['categories'] == 1) {
                            $operations .= '<a class="dropdown-item edite-Category" " data-id="' . $row['id'] . '"  data-toggle="modal" data-target="#update_modal" onclick="category_id(this)"  title = "Edit ">  <i class="fa fa-pen text-primary mr-1" aria-hidden="true"></i>'. labels('edit', 'Edit') .'</a>';
                        }
                        if ($permissions['delete']['categories'] == 1) {
                            $operations .= '<a class="dropdown-item delete_orders delete-Category" data-id="' . $row['id'] . '" onclick="category_id(this)"> <i class="fa fa-trash text-danger mr-1"></i>'. labels('delete', 'Delete') .'</a>';
                        }
                        $operations .= '</div></div>';
                    }
                }
            } else {
                if ($disk== 'local_server') {

                    $localPath = base_url('/public/uploads/categories/' . $row['image']);

                    if (check_exists($localPath)) {
                        $category_image = $localPath; // Use the local server image URL
                    } else {
                        $category_image = ''; // File not found, return an empty string
                    }
                } else if ($disk== "aws_s3") {
                    $category_image = fetch_cloud_front_url('categories', $row['image']); // Construct the CloudFront URL
                } else {
                    $category_image = $row['image'];
                }
            }
            $status =  ($row['status'] == 1) ? 'Enable' : 'Disable';
            $parent_category_name = '';
            if (!empty($row['parent_id'])) {
                $parent_category_name = (!empty(fetch_details('categories', ['id' => $row['parent_id']]))) ? fetch_details('categories', ['id' => $row['parent_id']])[0]['name'] : '';
            }
            $tempRow['id'] = $row['id'];
            $tempRow['name'] = $row['name'];
            $tempRow['slug'] = $row['slug'];
            $tempRow['parent_id'] = $row['parent_id'];
            $tempRow['parent_category_name'] = ($parent_category_name != '') ? $parent_category_name : 'No Parent found';
            $tempRow['category_image'] = $category_image;
            $tempRow['admin_commission'] = $row['admin_commission'];
            $tempRow['status'] = $row['status'];
            $tempRow['dark_color'] = $row['dark_color'];
            $tempRow['light_color'] = $row['light_color'];
            if ($from_app == false) {
                $tempRow['admin_commission'] = $row['admin_commission'];
                $tempRow['created_at'] = $row['created_at'];
                $tempRow['dark_color'] = $row['dark_color'];
                $tempRow['light_color'] = $row['light_color'];
                $tempRow['dark_color_format'] = ($row['dark_color'] == "") ?  'No color' : ' <div style="border-radius: 30px;width: 80px; height: 20px;background-color: ' . $row['dark_color'] . '"> </div>';
                $tempRow['light_color_format'] = ($row['light_color'] == "") ?  'No color' : ' <div style="border-radius: 30px;width: 80px; height: 20px;background-color: ' . $row['light_color'] . '"> </div>';
                $tempRow['status'] = ($row['status'] == 1) ? "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-success text-emerald-success dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>Active
                </div>" : "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-danger text-emerald-danger dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>Deactive
                </div>";
                $tempRow['og_status'] = $row['status'] == 1;
                $tempRow['operations'] = $operations;
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
