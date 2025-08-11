<?php

namespace App\Models;

use CodeIgniter\Model;

class Slider_model extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'sliders';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = ['type', 'type_id', 'app_image', 'status', 'url', 'web_image'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
    protected $url  = 'url';

    public $base, $admin_id, $db;

    public function list($from_app = false, $search = '', $limit = 10, $offset = 0, $sort = 'sl.id', $order = 'ASC', $where = [])
    {
        $multipleWhere = '';
        $db      = \Config\Database::connect();
        $condition = "";
        $builder = $db->table('sliders sl');

        // Search handling
        if (isset($search) and $search != '') {
            $multipleWhere = ['`sl.id`' => $search, '`sl.type`' => $search, '`sl.status`' => $search];
        }

        // App-specific filtering
        if ($from_app) {
            $where['sl.status'] = 1;
        }

        // First query to get total count
        $total_builder = clone $builder;
        $total_builder->select('COUNT(id) as total');

        // Additional filtering conditions
        if (isset($_GET['id']) && $_GET['id'] != '') {
            $total_builder->where($condition);
        }

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $total_builder->groupStart();
            $total_builder->orLike($multipleWhere);
            $total_builder->groupEnd();
        }

        if (isset($where) && !empty($where)) {
            $total_builder->where($where);
        }

        if (isset($_GET['slider_filter']) && $_GET['slider_filter'] != '') {
            $total_builder->where('sl.status', $_GET['slider_filter']);
        }

        $slider_count = $total_builder->get()->getResultArray();
        $total = $slider_count[0]['total'];

        // Main query to get slider records
        $builder->select('sl.*');

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->groupStart();
            $builder->orLike($multipleWhere);
            $builder->groupEnd();
        }

        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }

        if (isset($_GET['slider_filter']) && $_GET['slider_filter'] != '') {
            $builder->where('sl.status', $_GET['slider_filter']);
        }
        
        // Get all slider records first
        $slider_records = $builder->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();
         
        // Process records with improved data retrieval
        $processed_records = [];
        $category_ids = [];
        $provider_ids = [];
        
        // First pass: Collect IDs for batch processing
        foreach ($slider_records as $record) {
            $record_type = strtolower($record['type']);
            if ($record_type == 'category') {
                $category_ids[] = $record['type_id'];
            } elseif ($record_type == 'provider') {
                $provider_ids[] = $record['type_id'];
            }
        }
        
        // Fetch categories in batch
        $categories = [];
        if (!empty($category_ids)) {
            $category_builder = $db->table('categories');
            $category_builder->whereIn('id', $category_ids);
            $category_results = $category_builder->get()->getResultArray();
            
            foreach ($category_results as $category) {
                $categories[$category['id']] = $category;
            }
        }
        
        // Fetch providers 
        $valid_providers = [];
        if (!empty($provider_ids)) {
            $provider_builder = $db->table('partner_details pd');
            $provider_builder->select('pd.partner_id, pd.company_name, pd.is_approved');
            $provider_builder->whereIn('pd.partner_id', $provider_ids);
            $provider_results = $provider_builder->get()->getResultArray();
            
            foreach ($provider_results as $provider) {
                $valid_providers[$provider['partner_id']] = $provider;
            }
        }
        
        // Second pass: Process records with more lenient conditions
        foreach ($slider_records as $record) {
            $record_type = strtolower($record['type']);
            $record['category_name'] = '';
            $record['provider_name'] = '';
            $record['category_parent_id'] = 0;
            
            // Add category details if exists
            if ($record_type == 'category') {
                if (isset($categories[$record['type_id']])) {
                    $record['category_name'] = $categories[$record['type_id']]['name'] ?? '';
                    $record['category_parent_id'] = $categories[$record['type_id']]['parent_id'] ?? 0;
                }
            } 
            // Add provider details if exists
            elseif ($record_type == 'provider') {
                if (isset($valid_providers[$record['type_id']])) {
                    $record['provider_name'] = $valid_providers[$record['type_id']]['company_name'] ?? '';
                }
            }
            
            // Always add the record
            $processed_records[] = $record;
        }
      
        // Format output
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        
        // User permissions check for admin panel
        if ($from_app == false) {
            $db = \Config\Database::connect();
            $builder = $db->table('users u');
            $builder->select('u.*,ug.group_id')
                ->join('users_groups ug', 'ug.user_id = u.id')
                ->where('ug.group_id', 1)
                ->where(['phone' => $_SESSION['identity']]);
            $user1 = $builder->get()->getResultArray();
            $permissions = get_permission($user1[0]['id']);
        }

        // Get current file manager
        $disk = fetch_current_file_manager();

        // Process each record for output
        foreach ($processed_records as $row) {
            // Image handling
            $image_sources = [
                'app_image' => '1',
                'web_image' => '2'
            ];
            
            $image_urls = [];
            $image_tags = [];

            // Process images based on storage type
            foreach ($image_sources as $image_key => $disk_key) {
                $image_url = null;
            
                if ($disk === "aws_s3") {
                    $image_url = fetch_cloud_front_url('sliders', $row[$image_key]);
                } elseif ($disk === "local_server") {
                    $image_path = '/public/uploads/sliders/' . $row[$image_key];
                    $image_url = check_exists(base_url($image_path)) ? base_url($image_path) : null;
                }
            
                $image_urls[$image_key] = $image_url;
                $image_tags[$image_key] = $image_url 
                    ? '<a href="' . $image_url . '" data-lightbox="image-1">
                        <img class="o-media__img images_in_card" src="' . $image_url . '" alt="' . $row['id'] . '">
                    </a>'
                    : 'nothing found';
            }
            
            // Set images based on context
            if ($from_app == false) {
                $app_image = $image_tags['app_image'];
                $web_image = $image_tags['web_image'];
            } else {
                $app_image = $image_urls['app_image'] !== 'nothing found' ? $image_urls['app_image'] : null;
                $web_image = $image_urls['web_image'] !== 'nothing found' ? $image_urls['web_image'] : null;
            }
            
            // Operations for admin panel
            $operations = "";
            $type_all = $row['type'];
            if ($from_app == false) {
                $operations = '<div class="dropdown">
            <a class="" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <button class="btn btn-secondary btn-sm px-3"> <i class="fas fa-ellipsis-v"></i></button>
            </a>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">';
                if ($permissions['update']['sliders'] == 1) {
                    $operations .= '<a class="dropdown-item edite-slider" data-id="' . $row['id'] . '"  data-toggle="modal" data-target="#update_modal" onclick="update_slider(this)"><i class="fa fa-pen mr-1 text-primary"></i>'. labels('edit_slider', 'Edit Slider') .'</a>';
                }
                if ($permissions['delete']['sliders'] == 1) {
                    $operations .= '<a class="dropdown-item delete-slider" data-id="' . $row['id'] . '" data-toggle="modal" data-target="#delete_modal" onclick="category_id(this)" title="Delete the slider"><i class="fa fa-trash text-danger mr-1"></i>'. labels('delete_slider', 'Delete the slider') .'</a>';
                }
                $operations .= '</div></div>';
            }

            // Prepare row data
            $tempRow = [];
            $tempRow['id'] = $row['id'];
            $tempRow['type'] = $row['type'];
            $tempRow['type_id'] = $row['type_id'];
            $tempRow['slider_app_image'] = $app_image;
            $tempRow['slider_web_image'] = $web_image;
            $tempRow['category_parent_id'] = $row['category_parent_id'];
            $tempRow['category_name'] = $row['category_name'] ?? '';
            $tempRow['provider_name'] = $row['provider_name'] ?? '';
            $tempRow['url'] = $row['url'] ?? '';

            // Status handling for admin panel
            if ($from_app == false) {
                $status = ($row['status'] == 1) ?
                    "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-success text-emerald-success dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3 mx-5'>Active
                </div>" :
                    "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-danger text-emerald-danger dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3 '>Deactive
                </div>";
                $tempRow['status'] = $status;
                $tempRow['og_status'] = $row['status'];
                $tempRow['operations'] = $operations;
            }

            // Add created date for admin panel
            if ($from_app == false) {
                $tempRow['created_at'] =  format_date($row['created_at'], 'd-m-Y');
            }

            $rows[] = $tempRow;
        }

        // Return data based on context
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