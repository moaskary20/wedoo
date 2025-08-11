<?php

namespace App\Controllers\partner;

use App\Models\Promo_code_model;
use App\Models\Service_ratings_model;

class Dashboard extends Partner
{
    protected $validation; // Declare the $validation property
    protected $data; // Declare the $data property

    public function __construct()
    {
        parent::__construct();
        helper('function');
        helper('ResponceServices');
    }
    public function index()
    {
        try {
            if ($this->isLoggedIn && $this->userIsPartner) {
                if (!exists(['partner_id' => $this->userId, 'is_approved' => 1], 'partner_details')) {
                    return redirect('partner/profile');
                }
                $db = \Config\Database::connect();
                $id = $this->userId;
                $builder = $db->table('orders o');
                $order_count = $builder->select('count(DISTINCT(o.id)) as total')->where(['o.partner_id' => $id, 'o.parent_id' => "null"])->get()->getResultArray();
                $total_services = $db->table('services s')->select('count(s.id) as `total`')->where(['user_id' => $id])->get()->getResultArray()[0]['total'];
                $total_balance = unsettled_commision($id);
                $total_promocodes = $db->table('promo_codes p')->select('count(p.id) as `total`')->where(['partner_id' => $id])->get()->getResultArray()[0]['total'];
                $provider_total_earning_chart = provider_total_earning_chart($id);
                $provider_already_withdraw_chart = provider_already_withdraw_chart($id);
                $provider_pending_withdraw_chart = provider_pending_withdraw_chart($id);
                $provider_withdraw_chart = provider_withdraw_chart($id);
                $promocode_model = new Promo_code_model();
                $where['partner_id'] = $_SESSION['user_id'];
                $db = \Config\Database::connect();
                $id = $this->userId;
                $promo_codes = $db->table('promo_codes')->where(['partner_id' => $id])->where('start_date >', date('Y-m-d'))->orderBy('id', 'DESC')->limit(5, 0)->get()->getResultArray();
                $db = \Config\Database::connect();
                $promocode_dates = array();
                $tempRow = array();
                foreach ($promo_codes as $promo_code) {
                    $date = explode('-', $promo_code['start_date']);
                    $newDate = $date[1] . '-' . $date[2];
                    $newDate = explode(' ', $newDate);
                    $newDate = $newDate[0];
                    $tempRow['start_date'] = $newDate;
                    $tempRow['promo_code'] = $promo_code['promo_code'];
                    $tempRow['end_date'] = $promo_code['end_date'];
                    $promocode_dates[] = $tempRow;
                }
                $ratings = new Service_ratings_model();
                $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 0;
                $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
                $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
                $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
                $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
                // $data = $ratings->ratings_list(true, $search, $limit, $offset, $sort, $order, ['s.user_id' => $this->userId]);
                $where = "(s.user_id = {$this->userId}) OR (pb.partner_id = {$this->userId} AND sr.custom_job_request_id IS NOT NULL)";

                $data = $ratings->ratings_list(true, $search, $limit, $offset, $sort, $order, $where);

                $total_review = $data['total'];
                $total_ratings = $db->table('partner_details p')->select('count(p.ratings) as `total`')->where(['id' => $id])->get()->getResultArray()[0]['total'];
                $already_withdraw = $db->table('payment_request p')->select('sum(p.amount) as total')->where(['user_id' => $id, "status" => 1])->get()->getResultArray()[0]['total'];
                $pending_withdraw = $db->table('payment_request p')->select('sum(p.amount) as total')->where(['user_id' => $id, "status" => 0])->get()->getResultArray()[0]['total'];
                $total_withdraw_request = $db->table('payment_request p')->select('count(p.id) as `total`')->where(['user_id' => $id])->get()->getResultArray()[0]['total'];
                $number_or_ratings = $db->table('partner_details p')->select('count(p.number_of_ratings) as `total`')->where(['id' => $id])->get()->getResultArray()[0]['total'];
                $income = $db->table('orders o')->select('count(o.id) as `total`')->where(['user_id' => $id])->where("created_at >= DATE(now()) - INTERVAL 7 DAY")->get()->getResultArray()[0]['total'];
                $symbol =   get_currency();


                $partner_id = $this->userId;
                $custom_job_categories = fetch_details('partner_details', ['partner_id' => $this->userId], ['custom_job_categories', 'is_accepting_custom_jobs']);
                $partner_categoried_preference = !empty($custom_job_categories) &&
                    isset($custom_job_categories[0]['custom_job_categories']) &&
                    !empty($custom_job_categories[0]['custom_job_categories']) ?
                    json_decode($custom_job_categories[0]['custom_job_categories']) : [];
                $symbol =   get_currency();
                $partner_id = $this->userId;

                $totalJobsQuery = $db->table('custom_job_requests cj')
                    ->select('cj.id')  // Only select the ID for counting
                    ->join('users u', 'u.id = cj.user_id')
                    ->join('categories c', 'c.id = cj.category_id')
                    ->where('cj.status', 'pending')
                    ->where("(SELECT COUNT(1) FROM partner_bids pb WHERE pb.custom_job_request_id = cj.id AND pb.partner_id = $partner_id) = 0");
                if (!empty($partner_categoried_preference)) {
                    $totalJobsQuery->whereIn('cj.category_id', $partner_categoried_preference);
                }
                $totalJobsQueryResult = $totalJobsQuery->get()->getResultArray();

                $total_filteredJobs = [];
                foreach ($totalJobsQueryResult as $row) {

                    $did_partner_bid = fetch_details('partner_bids', [
                        'custom_job_request_id' => $row['id'],
                        'partner_id' => $partner_id,
                    ]);
                    if (empty($did_partner_bid)) {
                        $check = fetch_details('custom_job_provider', ['partner_id' => $partner_id, 'custom_job_request_id' => $row['id']]);

                        if (!empty($check)) {
                            $total_filteredJobs[] = $row;
                        }
                    }
                }

                $builder = $db->table('custom_job_requests cj');
                $builder->select('cj.*, u.username, u.image, c.id as category_id, c.name as category_name, c.image as category_image')
                    ->join('users u', 'u.id = cj.user_id')
                    ->join('categories c', 'c.id = cj.category_id')
                    ->join('partner_bids pb', "pb.custom_job_request_id = cj.id AND pb.partner_id = $partner_id", 'left')
                    ->where('cj.status', 'pending')
                    ->where('pb.id IS NULL')
                    ->orderBy('cj.created_at', 'DESC')
                    ->limit(2);

                // Handle the whereIn condition separately
                if (!empty($partner_categoried_preference)) {
                    $builder->whereIn('cj.category_id', $partner_categoried_preference);
                }

                $custom_job_requests = $builder->get()->getResultArray();


                $filteredJobs = [];
                foreach ($custom_job_requests as $row) {
                    $check = fetch_details('custom_job_provider', ['partner_id' => $partner_id, 'custom_job_request_id' => $row['id']]);

                    if (!empty($check)) {
                        $filteredJobs[] = $row;
                    }
                }
                $custom_job_requests = $filteredJobs;
                if (!empty($partner_categoried_preference)) {

                    $custom_job_requests =  $custom_job_requests;
                } else {
                    $custom_job_requests = [];
                }



                $this->data['total_custom_job_requests'] = count($total_filteredJobs);

                $this->data['custom_job_requests'] = $custom_job_requests;

                $this->data['total_services'] = $total_services;
                $this->data['total_orders'] = $order_count[0]['total'];
                $this->data['total_balance'] =  number_format($total_balance, 2, ".", "");
                $this->data['total_ratings'] = $total_ratings;
                $this->data['total_review'] = $total_review;
                $this->data['number_of_ratings'] = $number_or_ratings;
                $this->data['currency'] = $symbol;
                $this->data['total_promocodes'] = $total_promocodes;
                $this->data['already_withdraw'] = $already_withdraw;
                $this->data['pending_withdraw'] = $pending_withdraw;
                $this->data['total_withdraw_request'] = $total_withdraw_request;
                $this->data['promocode_dates'] = $promocode_dates;
                $this->data['provider_total_earning_chart'] = $provider_total_earning_chart;
                $this->data['provider_already_withdraw_chart'] = $provider_already_withdraw_chart;
                $this->data['provider_pending_withdraw_chart'] = $provider_pending_withdraw_chart;
                $this->data['provider_withdraw_chart'] = $provider_withdraw_chart;
                $this->data['income'] = number_format($income, 2, ".", "");
                setPageInfo($this->data, 'Dashboard | Provider Panel', 'dashboard');
                return view('backend/partner/template', $this->data);
            } else {
                return redirect('partner/login');
            }
        } catch (\Throwable $th) {

            log_the_responce($th, date("Y-m-d H:i:s") . '--> aapp/Controllers/partner/Dashboard.php - index()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function fetch_sales()
    {
        try {
            if (!$this->isLoggedIn) {
                return redirect('partner/login');
            } else {
                $sales[] = array();
                $db = \Config\Database::connect();
                $last_monthly_sales = (isset($_POST['last_monthly_sales']) && !empty(trim($_POST['last_monthly_sales']))) ? $this->request->getPost("last_monthly_sales") : 6;

                $month_res1 = $db->table('orders')
                    ->select('SUM(final_total) AS total_sale,DATE_FORMAT(created_at,"%b") AS month_name ')
                    ->where('partner_id', $_SESSION['user_id'])
                    ->where('status', 'completed')
                    ->groupBy('year(CURDATE()),MONTH(created_at)')
                    ->orderBy('year(CURDATE()),MONTH(created_at)')
                    ->get()->getResultArray();



                $month_res = $db->table('orders')
                    ->select('MONTHNAME(date_of_service) as month_name, SUM(final_total) as total_sale')
                    ->where('date_of_service BETWEEN CURDATE() - INTERVAL ' . $last_monthly_sales . ' MONTH AND CURDATE()')
                    ->where(['partner_id' =>  $_SESSION['user_id'], 'date_of_service < ' => date("Y-m-d H:i:s"), "status" => "completed"])
                    ->groupBy("MONTH(date_of_service)")
                    ->get()->getResultArray();


                $month_wise_sales['total_sale'] = array_map('intval', array_column($month_res, 'total_sale'));
                $month_wise_sales['month_name'] = array_column($month_res, 'month_name');
                $sales = $month_wise_sales;
                print_r(json_encode($sales));
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> aapp/Controllers/partner/Dashboard.php - fetch_sales()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function fetch_data()
    {
        try {
            $db = \Config\Database::connect();
            $id = $this->userId;
            $res = $db->table('categories as c')
                ->select('c.name as category,count(c.id) as counter')
                ->join('services s', 's.category_id=c.id ')
                ->where(['s.user_id' => $id, 's.status' => '1', 'c.status' => '1'])
                ->groupBy('c.id')
                ->get()->getResultArray();
            $response['category'] = array_column($res, 'category');
            $response['counter'] = array_column($res, 'counter');
            print_r(json_encode($response));
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> aapp/Controllers/partner/Dashboard.php - fetch_sales()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function fetch_total_earning()
    {
        if (!$this->isLoggedIn) {
            return redirect('partner/login');
        } else {
            $id = $this->userId;
            $data = provider_total_earning_chart($id);
            return $data;
            // print_r(($data));
            // die;
        }
    }
}
