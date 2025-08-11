<?php

namespace App\Controllers\admin;

class CustomJobRequest extends Admin
{
    public function __construct()
    {
        parent::__construct();
        helper('ResponceServices');
    }
    public function index()
    {
        if (!$this->isLoggedIn || !$this->userIsAdmin) {
            return redirect('admin/login');
        }
        setPageInfo($this->data, labels('custom_job_requests', 'Custom Job Requests') . ' | ' . labels('admin_panel', 'Admin Panel'), 'custom_job_requests');
        return view('backend/admin/template', $this->data);
    }
   
    public function list($from_app = false, $search = '', $limit = 10, $offset = 0, $sort = 'id', $order = 'ASC', $where = [])
    {
        try {
            $db      = \Config\Database::connect();
            $builder = $db->table('custom_job_requests cj');
            $sortable_fields = ['id' => 'cj.id'];
            $offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
            $limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
            $sort = isset($_GET['sort']) && in_array($_GET['sort'], $sortable_fields) ? $_GET['sort'] : 'cj.id';
            $order = isset($_GET['order']) && in_array($_GET['order'], ['ASC', 'DESC']) ? $_GET['order'] : 'ASC';
            $search = isset($_GET['search']) ? $_GET['search'] : '';
    
            $multipleWhere = [];
            if (!empty($search)) {
                $multipleWhere = [
                    'cj.id' => $search,
                    'c.name' => $search,
                    'u.username' => $search,
                ];
            }
    
            // Count total records with search and where conditions
            $count_builder = $db->table('custom_job_requests cj');
            $count_builder->select('COUNT(cj.id) as `total`');
            $count_builder->join('categories c', 'c.id = cj.category_id', 'left');
            $count_builder->join('users u', 'u.id = cj.user_id', 'left');
            if ($multipleWhere) {
                $count_builder->groupStart();
                foreach ($multipleWhere as $field => $value) {
                    $count_builder->orLike($field, $value);
                }
                $count_builder->groupEnd();
            }
            if ($where) {
                $count_builder->where($where);
            }
            $offer_count = $count_builder->get()->getRowArray();
            $total = $offer_count['total'];
    
            // Fetch records with search and where conditions
            $builder->select('cj.*, c.name as category_name, u.username, u.image');
            $builder->join('categories c', 'c.id = cj.category_id', 'left');
            $builder->join('users u', 'u.id = cj.user_id', 'left');
            if ($multipleWhere) {
                $builder->groupStart();
                foreach ($multipleWhere as $field => $value) {
                    $builder->orLike($field, $value);
                }
                $builder->groupEnd();
            }
            if ($where) {
                $builder->where($where);
            }
    
            $offer_recored = $builder->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();
            $bulkData = array();
            $bulkData['total'] = $total;
            $rows = array();
            foreach ($offer_recored as $row) {
                $tempRow['id'] = $row['id'];
                $tempRow['user_id'] = $row['user_id'];
                $tempRow['category_id'] = $row['category_id'];
                $tempRow['service_title'] = $row['service_title'];
                $tempRow['service_short_description'] = $row['service_short_description'];
                $tempRow['truncateWords_service_short_description'] =  truncateWords($row['service_short_description'], $limit = 5);
                $tempRow['min_price'] = $row['min_price'];
                $tempRow['max_price'] = $row['max_price'];
                $tempRow['requested_start_date'] = $row['requested_start_date'];
                $tempRow['requested_start_time'] = $row['requested_start_time'];
                $tempRow['requested_end_date'] = $row['requested_end_date'];
                $tempRow['requested_end_time'] = $row['requested_end_time'];
                $tempRow['status'] = labels($row['status'], ucfirst($row['status']));
                $tempRow['created_at'] = $row['created_at'];
                $tempRow['username'] = $row['username'];
                $tempRow['category_name'] = $row['category_name'];
                $totalBuilder = $db->table('partner_bids pb')
                    ->select('COUNT(pb.id) as total_bidders')
                    ->where('pb.custom_job_request_id', $row['id'])
                    ->get()
                    ->getRowArray();
                $tempRow['total_bids'] = $totalBuilder['total_bidders'];
    
                $operations = '<button class="btn btn-secondary btn-sm pay-out" onclick="window.location.href=\'' . base_url('/admin/custom-job/bidders/' . $row['id']) . '\'">' . labels('view_details', 'View Details') . '</button>';
    
                $tempRow['operation'] = $operations;
    
                $rows[] = $tempRow;
            }
            $bulkData['rows'] = $rows;
            return json_encode($bulkData);
        } catch (\Throwable $th) {
           
            log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Faqs.php - list()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    
    

    public function bidders_list($from_app = false, $search = '', $limit = 10, $offset = 0, $sort = 'id', $order = 'ASC', $where = [])
{
    $uri = service('uri');
    $segments = $uri->getSegments();
    $custom_job_request_id = $segments[3];
    try {
        $db = \Config\Database::connect();
        $builder = $db->table('partner_bids pb');
        $sortable_fields = ['id' => 'pb.id'];

        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $sort = isset($_GET['sort']) && in_array($_GET['sort'], array_keys($sortable_fields)) ? $_GET['sort'] : 'pb.id';
        $order = isset($_GET['order']) && in_array($_GET['order'], ['ASC', 'DESC']) ? $_GET['order'] : 'ASC';
        $search = isset($_GET['search']) ? $_GET['search'] : '';

        $multipleWhere = [];
        if (!empty($search)) {
            $multipleWhere = [
                'pb.id' => $search,
                'pd.company_name' => $search,
            ];
        }

        // Count total records with search and where conditions
        $count_builder = $db->table('partner_bids pb');
        $count_builder->select('COUNT(pb.id) as total');
        $count_builder->join('partner_details pd', 'pd.partner_id = pb.partner_id', 'left');
        if (!empty($multipleWhere)) {
            $count_builder->groupStart();
            foreach ($multipleWhere as $field => $value) {
                $count_builder->orLike($field, $value);
            }
            $count_builder->groupEnd();
        }
        if (!empty($where)) {
            $count_builder->where($where);
        }
        $count_builder->where('pb.custom_job_request_id', $custom_job_request_id);
        $offer_count = $count_builder->get()->getRowArray();
        $total = $offer_count['total'] ?? 0;

        // Fetch records with search and where conditions
        $builder->select('pb.*, pd.company_name as provider_name, pd.banner as provider_image')
            ->join('partner_details pd', 'pd.partner_id = pb.partner_id', 'left');
        if (!empty($multipleWhere)) {
            $builder->groupStart();
            foreach ($multipleWhere as $field => $value) {
                $builder->orLike($field, $value);
            }
            $builder->groupEnd();
        }
        if (!empty($where)) {
            $builder->where($where);
        }
        $builder->where('pb.custom_job_request_id', $custom_job_request_id);

        $builder->orderBy($sort, $order)
            ->limit($limit, $offset);

        $offer_records = $builder->get()->getResultArray();

        $bulkData = [];
        $bulkData['total'] = $total;
        $rows = [];

        foreach ($offer_records as $row) {
            $tempRow = [
                'id' => $row['id'],
                'partner_id' => $row['partner_id'],
                'counter_price' => $row['counter_price'],
                'truncateWords_note' => truncateWords($row['note'], $limit = 5),
                'note' => $row['note'],
                'duration' => $row['duration'],
                'created_at' => $row['created_at'],
                'status' => $row['status'],
                'provider_name' => $row['provider_name'],
                'provider_image' => $row['provider_image'],
            ];
            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;

        return json_encode($bulkData);

    } catch (\Throwable $th) {
        log_the_responce($th, date("Y-m-d H:i:s") . ' --> app/Controllers/admin/Faqs.php - list()');
        return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
    }
}

    public function bidders_list_page(){
        
        if (!$this->isLoggedIn || !$this->userIsAdmin) {
            return redirect('admin/login');
        }
        $uri = service('uri');
        $segments = $uri->getSegments();
        $custom_job_request_id = $segments[3];

        $custom_job=fetch_details('custom_job_requests',['id'=>$custom_job_request_id]);
        $this->data['custom_job']=$custom_job[0];
        setPageInfo($this->data, labels('custom_job_requests_bids', 'Custom Job Requests Bids') . ' | ' . labels('admin_panel', 'Admin Panel'), 'partners_bids');
        return view('backend/admin/template', $this->data);
    }
}
