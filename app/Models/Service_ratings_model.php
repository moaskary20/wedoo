<?php

namespace App\Models;

use CodeIgniter\Model;

class Service_ratings_model extends Model
{
    protected $table = 'services_ratings';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'service_id', 'rating', 'comment', 'images','custom_job_request_id'];
    public function ratings_list($from_app = false, $search = '', $limit = 10, $offset = 0, $sort = 'id', $order = 'ASC', $where = [], $column_name = 'id', $whereIn = [], $additional_data = [])
    {
        $multipleWhere = '';
        $db      = \Config\Database::connect();
        $builder = $db->table('services_ratings sr');
        if ($search and $search != '') {
            $multipleWhere = [
                '`sr.id`' => $search,
                '`sr.user_id`' => $search,
                '`sr.rating`' => $search,
                '`sr.comment`' => $search,
                '`sr.created_at`' => $search,
                '`u.username`' => $search,
                '`s.title`' => $search,
            ];
        }

        // $builder->select(' COUNT(sr.id) as `total` ')
        //     ->join('users u', 'u.id = sr.user_id')
        //     ->join('services s', 's.id = sr.service_id');

        $builder->select('COUNT(sr.id) as `total`')
            ->join('users u', 'u.id = sr.user_id')
            ->join('services s', 's.id = sr.service_id', 'left')
            ->join('partner_details pd', 'pd.id = s.user_id', 'left')
            ->join('custom_job_requests cj', 'cj.id = sr.custom_job_request_id', 'left')
            ->join('partner_bids pb', 'pb.custom_job_request_id = cj.id', 'left');


        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->orLike($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        if (isset($whereIn) && !empty($whereIn)) {
            $builder->whereIn($column_name, $whereIn);
        }
        if (isset($_GET['rating_star_filter']) && $_GET['rating_star_filter'] != '') {
            $builder->where('sr.rating', $_GET['rating_star_filter']);
        }
        $ratings_total_count = $builder->get()->getResultArray();

        $total = $ratings_total_count[0]['total'];
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->groupStart()->orLike($multipleWhere)->groupEnd();
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        if (isset($whereIn) && !empty($whereIn)) {
            $builder->whereIn($column_name, $whereIn);
        }



        $builder->select(
            '
            sr.*,
            u.image as profile_image,
            u.username, 
            COALESCE(s.user_id, pb.partner_id) as partner_id,
            COALESCE(s.title, cj.service_title) as service_name,
            '
        )
            ->join('users u', 'u.id = sr.user_id')
            ->join('services s', 's.id = sr.service_id', 'left')
            ->join('custom_job_requests cj', 'cj.id = sr.custom_job_request_id', 'left')
            ->join('partner_bids pb', 'pb.custom_job_request_id = cj.id', 'left');



        if (isset($_GET['rating_star_filter']) && $_GET['rating_star_filter'] != '') {
            $builder->where('sr.rating', $_GET['rating_star_filter']);
        }



        $rating_records = $builder->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        $disk =fetch_current_file_manager();

        foreach ($rating_records as $row) {
          
            $partner_name = fetch_details('users', ['id' => $row['partner_id']], ['username'])[0]['username'];
            $tempRow['id'] = $row['id'];
            $tempRow['partner_id'] = $row['partner_id'];
            $tempRow['partner_name'] = $partner_name;
            $tempRow['user_name'] = $row['username'];

            if($disk=="aws_s3"){
                $tempRow['profile_image'] =fetch_cloud_front_url('profile',$row['profile_image']);

            }else if($disk=="local_server"){

                if(!empty($row['profile_image'])){

                    $imagePath =  'public/backend/assets/profiles/' . $row['profile_image'];
                    if (check_exists(base_url($imagePath)) || check_exists(base_url('/public/uploads/users/partners/' . $row['profile_image'])) || check_exists($imagePath)) {
                        if (filter_var($row['profile_image'], FILTER_VALIDATE_URL)) {
                            $tempRow['profile_image'] = base_url($row['profile_image']);
                        } else {
                            $imagePath = (file_exists($imagePath)) ? $imagePath : FCPATH . $row['profile_image'];
                            $tempRow['profile_image'] = base_url($imagePath);
                        }
                    }
                }else{
                $tempRow['profile_image'] = base_url("public/backend/assets/profiles/default.png");

                }
            }else{
                $tempRow['profile_image'] = base_url("public/backend/assets/profiles/default.png");

            }
            
            $tempRow['user_id'] = $row['user_id'];
            $tempRow['service_id'] = $row['service_id'];
            $tempRow['service_name'] = $row['service_name'];
            $tempRow['rating'] = $row['rating'];
            $tempRow['comment'] = ($row['comment'] != "") ? $row['comment'] : "";
            $tempRow['rated_on'] = $row['created_at'];
            $tempRow['custom_job_request_id'] = $row['custom_job_request_id'];

            $tempRow['rate_updated_on'] = $row['updated_at'];
            if ($from_app == false) {
                $tempRow['stars'] = '<i class="fa-solid fa-star text-warning"></i>' .  $row['rating'];
                if ($row['images'] != "") {
                    $images =  rating_images($row['id'], false);
                    $tempRow['images'] = $images;
                } else {
                    $tempRow['images'] = array();
                }
                if ($row['images'] != "") {
                    $images = rating_images($row['id'], false);
                    $decodedImages = array_map('urldecode', $images);

                    $imageUrls = array_map(function ($htmlString) {
                        preg_match('/src="([^"]+)"/', $htmlString, $matches);
                        return isset($matches[1]) ? $matches[1] : '';
                    }, $decodedImages);
                } else {
                    $imageUrls = array();
                }

                $tempRow['view_images'] = '<button class="btn btn-success view-rating-images" title="View" data-id="' . $row['id'] . '" data-images=\'' . json_encode($imageUrls) . '\' data-toggle="modal" data-target="#viewImages"><i class="fa fa-eye" aria-hidden="true"></i></button>';
            } else {
                if ($row['images'] != "") {
                    $images =  rating_images($row['id'], true);
                    $tempRow['images'] = $images;
                } else {
                    $tempRow['images'] = array();
                }
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
}
