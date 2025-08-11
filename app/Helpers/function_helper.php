<?php

use App\Libraries\Flutterwave;
use App\Libraries\Paypal;
use App\Libraries\Paystack;
use App\Libraries\Paytm;
use App\Libraries\Razorpay;
use App\Libraries\Stripe;
use App\Models\Orders_model;
use App\Models\Users_model;
use Aws\Exception\AwsException;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use CodeIgniter\HTTP\Files\UploadedFile;
use Config\ApiResponseAndNotificationStrings;
use Google\Client;
use GuzzleHttp\Exception\ClientException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Razorpay\Api\Api;
use GuzzleHttp\Client as GuzzleClient;
use Vonage\Client as VonageClient;
use Vonage\Client\Credentials\Basic;
use Vonage\SMS\Message\SMS;

function update_balance($amount, $partner_id, $action)
{
    $db = \Config\Database::connect();
    $builder = $db->table('users');
    if ($action == "add") {
        $builder->set('balance', 'balance+' . $amount, false);
    } elseif ($action == "deduct") {
        $builder->set('balance', 'balance-' . $amount, false);
    }
    return $builder->where('id', $partner_id)->update();
}
function order_details($order_id)
{
    $model = new Orders_model();
    $where_in_key = 'o.status';
    $where_in_value = ['awaiting', 'confirmed', 'rescheduled'];
    $data = [];
    $order_details = $model->list(false, '', 10, 0, '', '', ['o.id' => $order_id], $where_in_key, $where_in_value);
    if (isset($order_details) && !empty($order_details)) {
        $details = json_decode($order_details);
        $data['order'] = isset($details->rows[0]) ? $details->rows[0] : '';
        $services = isset($details->rows[0]->services) ? $details->rows[0]->services : '';
        $id = (!empty($services)) ? array_column($services, 'service_id') : "";
        $data['cancellable'] = fetch_details('services', [], ['duration', 'is_cancelable', 'cancelable_till'], null, '0', '', '', 'id', $id);
        unset($data['order']->services);
        return $data;
    } else {
        return new stdClass();
    }
}
function check_cancelable($date_of_service, $starting_time, $cancellable_befor_min)
{
    $today = strtotime(date('y-m-d H:i'));
    $format_date = date('y-m-d H:i', strtotime("$date_of_service $starting_time"));
    $service_date = strtotime($format_date);
    if ($service_date >= $today) {
        $i = ($service_date - $today) / 60;
        if (intval($cancellable_befor_min) > $i) {
            return false;
        } else {
            return true;
        }
    }
}
function get_service_details($order_id)
{
    $db = \Config\Database::connect();
    $data = $db
        ->table(' order_services os')
        ->select('os.*', 'o.partner_id', 'o.')
        ->where('order_id', $order_id)
        ->where('status != ', 'cancelled')
        ->get()->getResultArray();
    $results = [];
    for ($i = 0; $i < sizeof($data); $i++) {
        $id = $data[$i]['service_id'];
        $service_data = $db
            ->table('services')
            ->select('*')
            ->where('id', $id)
            ->get()->getResultArray();
        if (isset($service_data[0]) && !empty($service_data)) {
            array_push($results, $service_data[0]);
        }
    }
    if (!empty($results)) {
        return $results;
    } else {
        $response['error'] = true;
        $response['message'] = "No such service found!";
        return $response;
    }
}
function validate_status($order_id, $status, $date = '', $selected_time = "", $otp = null, $work_proof = null, $additional_charges = null)
{
    $trans = new ApiResponseAndNotificationStrings();
    if ($status == "awaiting") {
        $translated_status = $trans->awaiting;
    } else if ($status == "confirmed") {
        $translated_status = $trans->confirmed;
    } else if ($status == "rescheduled") {
        $translated_status = $trans->rescheduled;
    } else if ($status == "cancelled") {
        $translated_status = $trans->cancelled;
    } else if ($status == "cancelled") {
        $translated_status = $trans->cancelled;
    } else if ($status == "completed") {
        $translated_status = $trans->completed;
    } else if ($status == "started") {
        $translated_status = $trans->started;
    } else if ($status == "booking_ended") {
        $translated_status = $trans->bookingEnded;
    } else if ($status == "booking_ended") {
        $translated_status = $trans->bookingEnded;
    }
    $check_status = ['awaiting', 'confirmed', 'rescheduled', 'cancelled', 'completed', 'started', 'booking_ended'];
    if (in_array(($status), $check_status)) {
        $db = \Config\Database::connect();
        $builder = $db->table('orders');
        $builder->select('status,payment_method,user_id,otp,final_total,total_additional_charge,payment_status_of_additional_charge,payment_method_of_additional_charge')->where('id', $order_id);
        $active_status1 = $builder->get()->getResultArray();
        $active_status = (isset($active_status1[0]['status'])) ? $active_status1[0]['status'] : "";
        if ($active_status == $status) {
            $response['error'] = true;
            $response['message'] = "You can't update the same status again";
            $response['data'] = array();
            return $response;
        }
        if ($active_status == 'cancelled' || $active_status == 'completed') {
            $response['error'] = true;
            $response['message'] = "You can't update status once item cancelled OR completed";
            $response['data'] = array();
            return $response;
        }
        if (in_array($active_status, ["booking_ended"]) && (($status == "rescheduled") || ($status == "confirmed") || ($status == "awaiting") || ($status == "pending"))) {
            $response['error'] = true;
            $response['message'] = $trans->statusChangeBlockedMessage . " " . $translated_status;
            $response['data'] = array();
            return $response;
        }
        if (in_array($active_status, ["started"]) && (($status == "rescheduled") || ($status == "confirmed"))) {
            $response['error'] = true;
            $response['message'] = "Once you begin the booking process, you cannot change the booking time.";
            $response['data'] = array();
            return $response;
        }
        if (in_array($active_status, ["started"]) && (($status == "rescheduled") || ($status == "confirmed") || ($status == "awaiting") || ($status == "pending"))) {
            $response['error'] = true;
            $response['message'] = $trans->statusChangeBlockedMessage . " " . $translated_status;
            $response['data'] = array();
            return $response;
        }
       
        if ($active_status == '') {
            $response['error'] = true;
            $response['message'] = "Invalid booking or status data";
            $response['data'] = array();
            return $response;
        }
        if (in_array($active_status, ["confirmed", "rescheduled"]) && $status == "awaiting") {
            $response['error'] = true;
            $response['message'] = $trans->statusChangeBlockedMessage . " " . $translated_status;
            $response['data'] = array();
            return $response;
        }
        if (in_array($status, ["awaiting", "confirmed"])) {
            update_details(['status' => $status], ['id' => $order_id], 'orders');
            update_details(["status" => $status], ["order_id" => $order_id, "status!=" => "cancelled"], "order_services");
        }
        //if order status is completed
        if ($status == 'completed') {
            if (empty($active_status1[0]['payment_method_of_additional_charge']) || $active_status1[0]['payment_method_of_additional_charge'] != "cod" ) {
                if (($active_status1[0]['total_additional_charge'] != 0 || $active_status1[0]['total_additional_charge'] != "") && ($active_status1[0]['payment_status_of_additional_charge'] == '' || $active_status1[0]['payment_status_of_additional_charge'] == '0')) {
                    $response['error'] = true;
                    $response['message'] = "Booking cannot be completed with a pending payment.";
                    $response['data'] = array();
                    return $response;
                }
            }
            $settings = get_settings('general_settings', true);
            if (isset($settings['otp_system']) && $settings['otp_system'] == 1) {
                $settings['otp_system'] = 1;
            } else {
                $settings['otp_system'] = 0;
            }
            //if otp system is enabled
            if ($settings['otp_system'] == "1") {
                if(empty($otp)) {
                    $response['error'] = true;
                    $response['message'] = "OTP is required";
                    $response['data'] = [];
                    return $response;
                }
                //if otp is mathed then update status otherwise not
                if ($active_status1[0]['otp'] == $otp) {
                    $data = get_service_details($order_id);
                    $order_details = fetch_details('orders', ['id' => $order_id]);
                    update_details(['status' => $status], ['id' => $order_id], 'orders');
                    if ($order_details[0]['payment_method'] != "cod") {
                        $user_details = fetch_details('users', ['id' => $order_details[0]['partner_id']]);
                        $admin_commission_percentage = get_admin_commision($order_details[0]['partner_id']);
                        $admin_commission_amount = intval($admin_commission_percentage) / 100;
                        $total = $order_details[0]['final_total'];
                        $commision = intval($total) * $admin_commission_amount;
                        $unsettled_amount = $total - $commision;
                        update_details(["balance" => $user_details[0]['balance'] + $unsettled_amount], ["id" => $order_details[0]['partner_id']], "users");
                        add_settlement_cashcollection_history('Received by admin', 'received_by_admin', date('Y-m-d'), date('h:i:s'), $unsettled_amount, $order_details[0]['partner_id'], $order_id, '', $admin_commission_percentage, $total, $commision);
                        $customer_details = fetch_details('users', ['id' => $order_details[0]['user_id']]);
                        if (!empty($customer_details[0]['email']) && check_notification_setting('rating_request_to_customer', 'email') && is_unsubscribe_enabled($customer_details[0]['id']) == 1) {
                            send_custom_email('rating_request_to_customer', $order_details[0]['partner_id'], $customer_details[0]['email'],'',$customer_details[0]['id'],$order_id);
                        }
                        if (check_notification_setting('rating_request_to_customer', 'sms')) {
                            send_custom_sms('rating_request_to_customer',  $order_details[0]['partner_id'], $customer_details[0]['email'],'',$customer_details[0]['id'],$order_id);
                        }
                    }
                    if (($order_details[0]['payment_method']) == "cod") {
                        $admin_commission_percentage = get_admin_commision($order_details[0]['partner_id']);
                        $admin_commission_amount = intval($admin_commission_percentage) / 100;
                        $total = $order_details[0]['final_total'];
                        $commision = intval($total) * $admin_commission_amount;
                        $current_commision = fetch_details('users', ['id' => $order_details[0]['partner_id']], ['payable_commision', 'email'])[0];
                        $current_commision['payable_commision'] = ($current_commision['payable_commision'] == "") ? 0 : $current_commision['payable_commision'];
                        update_details(['payment_status' => '1'], ['id' => $order_id], 'orders');
                        if (($active_status1[0]['total_additional_charge'] != 0 || $active_status1[0]['total_additional_charge'] != "")) {
                            update_details(['payment_status_of_additional_charge' => '1'], ['id' => $order_id], 'orders');
                        }
                        update_details(['payable_commision' => $current_commision['payable_commision'] + $commision], ['id' => $order_details[0]['partner_id']], 'users');
                        $cash_collecetion_data = [
                            'user_id' => $order_details[0]['user_id'],
                            'order_id' => $order_id,
                            'message' => "provider received cash",
                            'status' => 'provider_cash_recevied',
                            'commison' => intval($commision),
                            'partner_id' => $order_details[0]['partner_id'],
                            'date' => date("Y-m-d"),
                        ];
                        insert_details($cash_collecetion_data, 'cash_collection');
                        add_settlement_cashcollection_history('Cash collected by provider', 'cash_collection_by_provider', date('Y-m-d'), date('h:i:s'), $commision, $order_details[0]['partner_id'], $order_id, '', $commision, $order_details[0]['final_total'], $admin_commission_amount);
                        $customer_details = fetch_details('users', ['id' => $order_details[0]['user_id']]);
                        if (!empty($customer_details[0]['email']) && check_notification_setting('rating_request_to_customer', 'email') && is_unsubscribe_enabled($customer_details[0]['id']) == 1) {
                            send_custom_email('rating_request_to_customer', $order_details[0]['partner_id'], $customer_details[0]['email'],'',$customer_details[0]['id'],$order_id);
                        }
                        if (check_notification_setting('rating_request_to_customer', 'sms')) {
                            send_custom_sms('rating_request_to_customer',  $order_details[0]['partner_id'], $customer_details[0]['email'],'',$customer_details[0]['id'],$order_id);
                        }
                    };

                    update_details(["status" => $status], ["order_id" => $order_id], "order_services");
                } else {
                    $response['error'] = true;
                    $response['message'] = "OTP does not match!";
                    $response['data'] = [];
                    return $response;
                }
            }
            //if otp system is disabled
            else {
                $data = get_service_details($order_id);
                $order_details = fetch_details('orders', ['id' => $order_id]);
                update_details(['status' => $status], ['id' => $order_id], 'orders');
                if ($order_details[0]['payment_method'] != "cod") {
                    $user_details = fetch_details('users', ['id' => $order_details[0]['partner_id']]);
                    $admin_commission_percentage = get_admin_commision($order_details[0]['partner_id']);
                    $admin_commission_amount = intval($admin_commission_percentage) / 100;
                    $total = $order_details[0]['final_total'];
                    $commision = intval($total) * $admin_commission_amount;
                    $unsettled_amount = $total - $commision;
                    update_details(["status" => $status], ["order_id" => $order_id], "order_services");
                    update_details(["balance" => $user_details[0]['balance'] + $unsettled_amount], ["id" => $order_details[0]['partner_id']], "users");
                    add_settlement_cashcollection_history('Received by admin', 'received_by_admin', date('Y-m-d'), date('h:i:s'), $unsettled_amount, $order_details[0]['partner_id'], $order_id, '', $admin_commission_percentage, $total, $commision);
                }
                if (($order_details[0]['payment_method']) == "cod") {
                    $admin_commission_percentage = get_admin_commision($order_details[0]['partner_id']);
                    $admin_commission_amount = intval($admin_commission_percentage) / 100;
                    $total = $order_details[0]['final_total'];
                    $commision = intval($total) * $admin_commission_amount;
                    $current_commision = fetch_details('users', ['id' => $order_details[0]['partner_id']], ['payable_commision', 'email'])[0];
                    $current_commision['payable_commision'] = ($current_commision['payable_commision'] == "") ? 0 : $current_commision['payable_commision'];
                    // update_details(['payable_commision' => $current_commision['payable_commision'] + $commision], ['id' => $order_details[0]['partner_id']], 'users');
                    $sum=$current_commision['payable_commision'] + $commision;
                    update_details(['payable_commision' =>$sum==0 ?"0":$sum], ['id' => $order_details[0]['partner_id']], 'users');
                    update_details(['payment_status' => '1'], ['id' => $order_id], 'orders');
                    if (($active_status1[0]['total_additional_charge'] != 0 || $active_status1[0]['total_additional_charge'] != "")) {
                        update_details(['payment_status_of_additional_charge' => '1'], ['id' => $order_id], 'orders');
                    }
                    $cash_collecetion_data = [
                        'user_id' => $order_details[0]['user_id'],
                        'order_id' => $order_id,
                        'message' => "provider received cash",
                        'status' => 'provider_cash_recevied',
                        'commison' => intval($commision),
                        'partner_id' => $order_details[0]['partner_id'],
                        'date' => date("Y-m-d"),
                    ];
                    insert_details($cash_collecetion_data, 'cash_collection');
                    $actual_amount_of_provider = $order_details[0]['final_total'] - $commision;
                    add_settlement_cashcollection_history(
                        'Cash collected by provider',
                        'cash_collection_by_provider',
                        date('Y-m-d'),
                        date('h:i:s'),
                        $actual_amount_of_provider,
                        $order_details[0]['partner_id'],
                        $order_id,
                        '',
                        $admin_commission_percentage,
                        $order_details[0]['final_total'],
                        $commision
                    );
                };

                $customer_details = fetch_details('users', ['id' => $order_details[0]['user_id']]);
                if (!empty($customer_details[0]['email']) && check_notification_setting('rating_request_to_customer', 'email') && is_unsubscribe_enabled($customer_details[0]['id']) == 1) {
                    send_custom_email('rating_request_to_customer', $order_details[0]['partner_id'], $customer_details[0]['email'],'',$customer_details[0]['id'],$order_id);
                }
                if (check_notification_setting('rating_request_to_customer', 'sms')) {
                    send_custom_sms('rating_request_to_customer',  $order_details[0]['partner_id'], $customer_details[0]['email'],'',$customer_details[0]['id'],$order_id);
                }
            }
        }




        if ($status == 'started') {
            if (!empty($work_proof)) {
                $imagefile = $work_proof['work_started_files'];
                $work_started_images = [];
                foreach ($imagefile as $key => $img) {
                    if ($img->isValid() && !$img->hasMoved()) {
                        $result = upload_file($img, 'public/backend/assets/provider_work_evidence', 'error creating provider work evidence folder', 'provider_work_evidence');
                        if ($result['disk'] == "local_server") {

                            $work_started_images[$key] = "/public/backend/assets/provider_work_evidence/" . $result['file_name'];
                        } else if ($result['disk'] == "aws_s3") {
                            $work_started_images[$key] = $result['file_name'];
                        }
                    }
                }
            }
            $dataToUpdate = [
                'status' => 'started',
                'work_started_proof' => !empty($work_started_images) ? json_encode($work_started_images) : "",
            ];
            update_details($dataToUpdate, ['id' => $order_id], 'orders', false);
            update_details(["status" => $status], ["order_id" => $order_id], "order_services");
        }
        if ($status == 'rescheduled') {
            $orders = fetch_details('orders', ['id' => $order_id]);
            if ($orders[0]['custom_job_request_id'] != "" || $orders[0]['custom_job_request_id'] != NULL) {
                $custom_job = fetch_details('partner_bids', ['custom_job_request_id' => $orders[0]['custom_job_request_id']]);
                $time_calc = $custom_job[0]['duration'];
            } else {
                $data = get_service_details($order_id);
                $sub_orders = fetch_details('orders', ['parent_id' => $order_id]);
                $time_calc = 0;
                for ($i = 0; $i < count($data); $i++) {
                    $time_calc += (int) $data[$i]['duration'];
                }
            }
            $partner_id = $orders[0]['partner_id'];
            $date_of_service = $date;
            $starting_time = $selected_time;
            $availability =  checkPartnerAvailability($partner_id, $date_of_service . ' ' . $starting_time, $orders[0]['duration'], $date_of_service, $starting_time);
            $time_slots = get_available_slots($partner_id, $date_of_service, isset($service_total_duration) ? $service_total_duration : 0, $starting_time); //working
            $current_date = date('Y-m-d');
            if (isset($availability) && $availability['error'] == "0") {
                $service_total_duration = 0;
                $service_duration = 0;
                $service_total_duration = $orders[0]['duration'];
                if (!empty($sub_orders)) {
                    $service_total_duration = $service_total_duration + $sub_orders[0]['duration'];
                }
                $time_slots = get_slot_for_place_order($partner_id, $date_of_service, $service_total_duration, $starting_time);
                $timestamp = date('Y-m-d h:i:s '); // Example timestamp format: 2023-08-08 03:30:00 PM
                if ($time_slots['suborder'] && !empty($time_slots['suborder_data'])) {
                    $total = (sizeof($time_slots['order_data']) * 30) + (sizeof($time_slots['suborder_data']) * 30);
                } else {
                    $total = (sizeof($time_slots['order_data']) * 30);
                }
                if ($service_total_duration > $total) {
                    $response['error'] = false;
                    $response['message'] = "There are currently no available slots.";
                    $response['data'] = array();
                    return $response;
                }
                if ($time_slots['slot_avaialble']) {
                    if ($time_slots['suborder']) {
                        $end_minutes = strtotime($starting_time) + ((sizeof($time_slots['order_data']) * 30) * 60);
                        $ending_time = date('H:i:s', $end_minutes);
                        $day = date('l', strtotime($date_of_service));
                        $timings = getTimingOfDay($partner_id, $day);
                        $closing_time = $timings['closing_time']; // Replace with the actual closing time
                        if ($ending_time > $closing_time) {
                            $ending_time = $closing_time;
                        }
                        $start_timestamp = strtotime($starting_time);
                        $ending_timestamp = strtotime($ending_time);
                        $duration_seconds = $ending_timestamp - $start_timestamp;
                        $duration_minutes = $duration_seconds / 60;
                    }
                    $end_minutes = strtotime($starting_time) + ($service_total_duration * 60);
                    $ending_time = date('H:i:s', $end_minutes);
                    $day = date('l', strtotime($date_of_service));
                    $timings = getTimingOfDay($partner_id, $day);
                    $closing_time = $timings['closing_time']; // Replace with the actual closing time
                    if ($ending_time > $closing_time) {
                        $ending_time = $closing_time;
                    }
                    $start_timestamp = strtotime($starting_time);
                    $ending_timestamp = strtotime($ending_time);
                    $duration_seconds = $ending_timestamp - $start_timestamp;
                    $duration_minutes = $duration_seconds / 60;
                    update_details(
                        [
                            'status' => 'rescheduled',
                            'date_of_service' => $date,
                            'starting_time' => $selected_time,
                            'ending_time' => $ending_time,
                            'duration' => $duration_minutes,
                        ],
                        ['id' => $order_id],
                        'orders'
                    );
                }
                if ($time_slots['suborder']) {
                    $next_day_date = date('Y-m-d', strtotime($date_of_service . ' +1 day'));
                    // $t=100;
                    $t = ($service_total_duration);
                    $next_day_slots = get_next_days_slots($closing_time, $date_of_service, $partner_id, $t, $current_date);
                    $next_day_available_slots = $next_day_slots['available_slots'];
                    if (empty($next_day_available_slots)) {
                        $response['error'] = false;
                        $response['message'] = "A time slot is currently unavailable at the present moment.";
                        $response['data'] = array();
                        return $response;
                    }
                    $next_Day_minutes = strtotime($next_day_available_slots[0]) + (($service_total_duration - $duration_minutes) * 60);
                    $next_day_ending_time = date('H:i:s', $next_Day_minutes);
                    $is_update = true;
                    if (!empty($sub_orders)) {
                        update_details(
                            [
                                'status' => 'rescheduled',
                                'date_of_service' => $next_day_date,
                                'starting_time' => isset($next_day_available_slots[0]) ? $next_day_available_slots[0] : 00,
                                'ending_time' =>  $next_day_ending_time,
                                'duration' =>  $service_total_duration - $duration_minutes,
                            ],
                            ['parent_id' => $order_id],
                            'orders'
                        );
                    } else {
                        $sub_order = [
                            'partner_id' => $partner_id,
                            'user_id' => $orders[0]['user_id'],
                            'city' => $orders[0]['city_id'],
                            'total' => $orders[0]['total'],
                            'payment_method' => $orders[0]['payment_method'],
                            'address_id' => $orders[0]['address_id'],
                            'visiting_charges' => $orders[0]['visiting_charges'],
                            'address' => $orders[0]['address'],
                            'date_of_service' =>   $next_day_date,
                            'starting_time' => isset($next_day_available_slots[0]) ? $next_day_available_slots[0] : 00,
                            'ending_time' => $next_day_ending_time,
                            'duration' => $service_total_duration - $duration_minutes,
                            'status' => $status,
                            'remarks' => "sub_order",
                            'otp' => random_int(100000, 999999),
                            'parent_id' =>  $orders[0]['id'],
                            'order_latitude' =>  $orders[0]['order_latitude'],
                            'order_longitude' =>  $orders[0]['order_longitude'],
                            'created_at' => $timestamp,
                        ];
                        $sub_order['final_total'] = $orders[0]['final_total'];
                        $sub_order = insert_details($sub_order, 'orders');
                    }
                    set_time_limit(60);
                }
                $response['error'] = false;
                $response['message'] = "The booking has been successfully rescheduled.";
                $response['data'] = array();
                $db = \Config\Database::connect();
                $order_details = fetch_details('orders', ['id' => $order_id]);
                $order_details = json_encode($order_details);
                $details = (json_decode($order_details, true));
                $customer_id = $details[0]['user_id'];
                $data['order'] = isset($details[0]) ? $details[0] : '';
                $to_send_id = $customer_id;
                $builder = $db->table('users')->select('fcm_id,email,username,platform');
                $users_fcm = $builder->where('id', $to_send_id)->get()->getResultArray();
                foreach ($users_fcm as $ids) {
                    if ($ids['fcm_id'] != "") {
                        $fcm_ids['fcm_id'] = $ids['fcm_id'];
                        $fcm_ids['platform'] = $ids['platform'];
                    }
                    $email = $ids['email'];
                    $username = $ids['username'];
                }
                if (!empty($fcm_ids) && check_notification_setting('booking_status_updated', 'notification')) {
                    $registrationIDs = $fcm_ids;
                    $trans = new ApiResponseAndNotificationStrings();
                    $fcmMsg = array(
                        'content_available' => "true",
                        'title' => $trans->bookingStatusChange,
                        'body' => $trans->bookingStatusUpdateMessage . $translated_status,
                        'type' => 'order',
                        'type_id' => "$to_send_id",
                        'order_id' => "$order_id",
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    );
                    $registrationIDs_chunks = array_chunk($users_fcm, 1000);
                    send_notification($fcmMsg, $registrationIDs_chunks);

                    $user_partner_data = fetch_details('users', ['id' => $to_send_id], ['email', 'username']);
                    if (!empty($user_partner_data[0]['email']) && check_notification_setting('new_booking_received_for_provider', 'email') && is_unsubscribe_enabled($partner_id) == 1) {
                        send_custom_email('booking_status_updated', $to_send_id, $user_partner_data[0]['email'], null, $to_send_id, $order_id);
                    }
                    if (check_notification_setting('booking_status_updated', 'sms')) {
                        send_custom_sms('booking_status_updated', $to_send_id, $user_partner_data[0]['email'], null, $to_send_id, $order_id);
                    }
                }
                return $response;
            } else {
                set_time_limit(60);
                $response['error'] = true;
                $response['message'] = $availability['message'];
                $response['data'] = array();
                return $response;
                return response_helper($availability['message'], true);
            }
        }
        if ($status == 'cancelled') {
            $order_details = fetch_details('orders', ['id' => $order_id]);
            $order_details = json_encode($order_details);
            $details = json_decode($order_details);
            $data['order'] = isset($details[0]) ? $details[0] : '';
            if ($details[0]->custom_job_request_id != "" || $details[0]->custom_job_request_id != NULL) {
                $order_services = fetch_details('partner_bids', ['custom_job_request_id' =>  $details[0]->custom_job_request_id]);
                $custom_job_request = get_settings('general_settings', true);
                $data['cancellable'] = [];
                $cancellable[0] = [
                    'id' => $order_services[0]['custom_job_request_id'],
                    'duration' => $order_services[0]['duration'],
                    'is_cancelable' => 1,
                    'cancelable_till' => $custom_job_request['booking_auto_cancle_duration']
                ];
                // $dat?a['cancellable'][] = $cancellable;
            } else {
                $order_services = fetch_details('order_services', ['order_id' => $order_id]);
                foreach ($order_services as $row) {
                    $services[] = $row['service_id'];
                }
                $data['cancellable'] = [];
                foreach ($services as $row) {
                    $data_of_service = fetch_details('services', ['id' => $row], ['id', 'duration', 'is_cancelable', 'cancelable_till'], null, '0', '', '');
                    foreach ($data_of_service as $data1) {
                        $cancellable[] = $data1;
                    }
                }
            }
            if (!empty($order_details)) {
                $order = $data['order'];
                $customer_id = $order->user_id;
                $date_of_service = $order->date_of_service;
                $starting_time = $order->starting_time;
                $cancellable = ($cancellable);
                $response = [];
                $response['status'] = $status;
                $can_cancle = false;
                foreach ($cancellable as $key) {
                    $can_cancle = ($key['is_cancelable'] == 1) ? true : false;
                    if ($key['is_cancelable'] == "1"  && $key['cancelable_till']) {
                        $is_cancelable = check_cancelable(date('y-m-d', strtotime($date_of_service)), $starting_time, $key['cancelable_till']);
                        if ($is_cancelable == true) {
                            if ($can_cancle == false) {
                                $response['error'] = true;
                                $response['message'] = "Booking is not cancelable!";
                                $response['data'] = [];
                                return $response;
                            } else {
                                update_details(['status' => $status], ['id' => $order_id], 'orders');
                                $refund = process_refund($order_id, $status, $customer_id);
                                $response['is_cancelable'] = true;
                                $response['error'] = false;
                                $response['message'] = "Booking updated successfully";
                                $response['data'] = $refund;
                                $db = \Config\Database::connect();
                                $order_details = fetch_details('orders', ['id' => $order_id]);
                                $order_details = json_encode($order_details);
                                $details = (json_decode($order_details, true));
                                $customer_id = $details[0]['user_id'];
                                $data['order'] = isset($details[0]) ? $details[0] : '';
                                $to_send_id = $customer_id;
                                $builder = $db->table('users')->select('fcm_id,email,username,platform');
                                $users_fcm = $builder->where('id', $to_send_id)->get()->getResultArray();
                                foreach ($users_fcm as $ids) {
                                    if ($ids['fcm_id'] != "") {
                                        $fcm_ids['fcm_id'] = $ids['fcm_id'];
                                        $fcm_ids['platform'] = $ids['platform'];
                                    }
                                    $email = $ids['email'];
                                    $username = $ids['username'];
                                }
                                if (!empty($fcm_ids) && check_notification_setting('booking_status_updated', 'notification')) {
                                    $trans = new ApiResponseAndNotificationStrings();
                                    $fcmMsg = array(
                                        'content_available' => "true",
                                        'title' => $trans->bookingStatusChange,
                                        'body' => $trans->bookingStatusUpdateMessage . " " . $translated_status,
                                        'type' => 'order',
                                        'type_id' => "$to_send_id",
                                        'order_id' => "$order_id",
                                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                                    );
                                    $registrationIDs_chunks = array_chunk($users_fcm, 1000);
                                    send_notification($fcmMsg, $registrationIDs_chunks);
                                }
                                return $response;
                            }
                        } else {
                            $response['error'] = true;
                            $response['message'] = "Booking is not cancelable !";
                            $response['data'] = [];
                            return $response;
                        }
                    } else {
                        $response['error'] = true;
                        $response['message'] = "Booking is not cancelable!";
                        $response['data'] = [];
                        return $response;
                    }
                }
            } else {
                $response['error'] = true;
                $response['message'] = "Booking data not found!";
                $response['data'] = [];
                return $response;
            }
        }
        if ($status == "booking_ended") {
            $dataToUpdate = [
                'status' => 'booking_ended',
            ];
            if ($additional_charges != "" && ($additional_charges[0]['name'] != "" && $additional_charges[0]['charge'] != "")) {
                $dataToUpdate['additional_charges'] = json_encode($additional_charges);
                $additional_total_charge = 0;
                if (isset($additional_charges)) {
                    foreach ($additional_charges as $charge) {
                        if (empty($charge['name']) || empty($charge['charge'])) {
                            $response['error'] = true;
                            $response['message'] = "All additional charge fields are required";
                            $response['data'] = [];
                            return $response;
                        }
                        if ((float)$charge['charge'] < 1) {
                            $response['error'] = true;
                            $response['message'] = "Charge amount must be greater than 0";
                            $response['data'] = [];
                            return $response;
                        }
                    }
                    foreach ($additional_charges as $key => $charge) {
                        $additional_total_charge += $charge['charge'];
                    }
                }
                $dataToUpdate['total_additional_charge'] = $additional_total_charge;
                // $dataToUpdate['payment_status_of_additional_charge'] = '0';
                $dataToUpdate['final_total'] = $active_status1[0]['final_total'] + $additional_total_charge;
            }
            update_details($dataToUpdate, ['id' => $order_id], 'orders', false);
            if (!empty($work_proof)) {

                $imagefile = $work_proof['work_complete_files'];
                $work_completed_images = [];
                foreach ($imagefile as $key => $img) {
                    if ($img->isValid() && !$img->hasMoved()) {

                        $result = upload_file($img, 'public/backend/assets/provider_work_evidence', 'error creating provider work evidence folder', 'provider_work_evidence');
                        if ($result['disk'] == "local_server") {

                            $work_completed_images[$key] = "/public/backend/assets/provider_work_evidence/" . $result['file_name'];
                        } else if ($result['disk'] == "aws_s3") {
                            $work_completed_images[$key] = $result['file_name'];
                        }
                    }
                }
                $dataToUpdate = [
                    'work_completed_proof' => !empty($work_completed_images) ? json_encode($work_completed_images) : "",
                ];
                update_details($dataToUpdate, ['id' => $order_id], 'orders', false);
            }
        }
        $response['error'] = false;
        $response['message'] = "Booking updated successfully ";
        $response['data'] = [];
        $db = \Config\Database::connect();
        $order_details = fetch_details('orders', ['id' => $order_id]);
        $order_details = json_encode($order_details);
        $details = (json_decode($order_details, true));
        $customer_id = $details[0]['user_id'];
        $data['order'] = isset($details[0]) ? $details[0] : '';
        $to_send_id = $customer_id;
        $builder = $db->table('users')->select('fcm_id,email,username,platform');
        $users_fcm = $builder->where('id', $to_send_id)->get()->getResultArray();
        foreach ($users_fcm as $ids) {
            if ($ids['fcm_id'] != "") {
                $fcm_ids['fcm_id'] = $ids['fcm_id'];
                $fcm_ids['platform'] = $ids['platform'];
            }
            $email = $ids['email'];
            $username = $ids['username'];
        }
        if (!empty($fcm_ids) && check_notification_setting('booking_status_updated', 'notification')) {
            $trans = new ApiResponseAndNotificationStrings();
            if ($status == "booking_ended" &&    ($additional_charges != "" && ($additional_charges[0]['name'] != "" && $additional_charges[0]['charge'] != ""))) {
                $fcmMsg = array(
                    'content_available' => "true",
                    'title' => $trans->bookingStatusChange,
                    'body' => $trans->bookingStatusUpdateMessage . " " . $translated_status . " and additional charges are added.",
                    'type' => 'order',
                    'type_id' => "$to_send_id",
                    'order_id' => "$order_id",
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                );
            } else {
                $fcmMsg = array(
                    'content_available' => "true",
                    'title' => $trans->bookingStatusChange,
                    'body' => $trans->bookingStatusUpdateMessage . " " . $translated_status,
                    'type' => 'order',
                    'type_id' => "$to_send_id",
                    'order_id' => "$order_id",
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                );
            }
            $registrationIDs_chunks = array_chunk($users_fcm, 1000);
            send_notification($fcmMsg, $registrationIDs_chunks);
        }
        $settings = get_settings('general_settings', true);
        $icon = $settings['logo'];
        // $data = array(
        //     'name' => (!empty($username)) ? $username : "",
        //     'title' => "Booking Status Update",
        //     'logo' => base_url("public/uploads/site/" . $icon),
        //     'first_paragraph' => 'We would like to inform you that the status of your Booking has been updated.',
        //     'second_paragraph' => 'Booking status is ' . $status,
        //     'third_paragraph' => 'Thank you for choosing our services. We look forward to providing you with excellent service in the future.',
        //     'company_name' => $settings['company_title'],
        // );
        if (!empty($users_fcm[0]['email']) && check_notification_setting('booking_status_updated', 'email') && is_unsubscribe_enabled($to_send_id) == 1) {
            send_custom_email('booking_status_updated', null, $email, null, $to_send_id,$order_id,$details[0]['date_of_service']);
        }
        return $response;
    } else {
        $response['error'] = true;
        $response['message'] = "Invalid Status Passed";
        $response['data'] = array();
        return $response;
    }
}
function unsettled_commision($partner_id = '')
{
    $amount = fetch_details('orders', ['partner_id' => $partner_id, 'is_commission_settled' => '0', 'status' => 'completed'], ['sum(final_total) as total']);
    if (isset($amount) && !empty($amount)) {
        $admin_commission_percentage = get_admin_commision($partner_id);
        $admin_commission_amount = intval($admin_commission_percentage) / 100;
        $total = $amount[0]['total'];
        $commision = intval($total) * $admin_commission_amount;
        $unsettled_amount = $total - $commision;
    } else {
        $unsettled_amount = 0;
    }
    return $unsettled_amount;
}
function get_admin_commision($partner_id = '')
{
    $commision = fetch_details('partner_details', ['partner_id' => $partner_id], ['admin_commission'])[0]['admin_commission'];
    return $commision;
}
function process_refund($order_id, $status, $customer_id)
{
    $possible_status = array("cancelled");
    if (!in_array($status, $possible_status)) {
        $response['error'] = true;
        $response['message'] = 'Refund cannot be processed. Invalid status';
        $response['data'] = array();
        return $response;
    }
    /* if complete order is getting cancelled */
    $transaction = fetch_details('transactions', ['order_id' => $order_id, 'transaction_type' => 'transaction'], ['amount', 'txn_id', 'type', 'currency_code', 'status', 'partner_id']);
    if (isset($transaction) && !empty($transaction)) {
        $type = $transaction[0]['type'];
        $currency = $transaction[0]['currency_code'];
        $txn_id = $transaction[0]['txn_id'];
        $amount = $transaction[0]['amount'];
        $partner_id = $transaction[0]['partner_id'];
        if ($type == 'flutterwave' && $transaction[0]['status'] == "successfull") {
            $flutterwave = new Flutterwave();
            $payment = $flutterwave->refund_payment($txn_id, $amount);
            if (isset($payment->status) && $payment->status == 'success') {
                $data = [
                    'transaction_type' => 'refund',
                    'order_id' => $order_id,
                    'user_id' => $customer_id,
                    'type' => 'flutterwave',
                    'txn_id' => $txn_id,
                    'amount' => $amount,
                    'currency_code' => $currency,
                    'status' => $payment->status,
                    'message' => "flutterwave_refund",
                    'partner_id' => $partner_id,
                ];
                $success = insert_details($data, 'transactions');
                $response['error'] = false;
                $response['csrfName'] = csrf_token();
                $response['csrfHash'] = csrf_hash();
                $response['message'] = "Payment Refund Successfully";
                if ($success) {
                    update_details(['status' => $status, 'isRefunded' => '1'], ['id' => $order_id], 'orders');
                    $response = [
                        'error' => false,
                        'message' => "Booking cancelled Successfully!",
                    ];
                    return $response;
                }
            } else {
                $message = json_decode($payment, true);
                $response['error'] = true;
                $response['csrfName'] = csrf_token();
                $response['csrfHash'] = csrf_hash();
                $response['message'] = $message['message'];
            }
        }
        if ($type == "stripe" && $transaction[0]['status'] == 'success') {
            $amount = $transaction[0]['amount'] / 100;
            $stripe = new Stripe();
            $payment = $stripe->refund($txn_id, $amount);
            if (isset($payment['status']) && $payment['status'] == "succeeded") {
                $amount = intval($payment['amount']);
                $data = [
                    'transaction_type' => $payment['object'],
                    'order_id' => $order_id,
                    'user_id' => $customer_id,
                    'type' => 'stripe',
                    'txn_id' => $payment['payment_intent'],
                    'amount' => $amount,
                    'currency_code' => $currency,
                    'status' => $payment['status'],
                    'message' => "stripe_refund",
                    'partner_id' => $partner_id,
                ];
                $success = insert_details($data, 'transactions');
                $response = [
                    'error' => false,
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'message' => "Payment Refund Successfully",
                ];
                if ($success) {
                    update_details(['status' => $status, 'isRefunded' => '1'], ['id' => $order_id], 'orders');
                    $response = [
                        'error' => false,
                        'message' => "Booking cancelled Successfully!",
                    ];
                    return $response;
                }
                return $response;
            } else {
                $res = json_decode($payment['body']);
                $msg = $res->error->message;
                $response = [
                    'error' => true,
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'message' => $msg,
                ];
                return $response;
            }
        }
        if ($type == "razorpay" && $transaction[0]['status'] == "success") {
            $razorpay = new Razorpay();
            $payment = $razorpay->refund_payment($txn_id, $amount);
            if (isset($payment['status']) && $payment['status'] == "processed") {
                $amount = intval($payment['amount']) / 100;
                $data = [
                    'transaction_type' => $payment['entity'],
                    'order_id' => $order_id,
                    'user_id' => $customer_id,
                    'type' => 'razorpay',
                    'txn_id' => $payment['payment_id'],
                    'amount' => $amount,
                    'currency_code' => $currency,
                    'status' => $payment['status'],
                    'message' => 'razorpay_refund',
                    'partner_id' => $partner_id,
                ];
                $success = insert_details($data, 'transactions');
                if ($success) {
                    update_details(['status' => $status, 'isRefunded' => '1'], ['id' => $order_id], 'orders');
                    $response = [
                        'error' => false,
                        'message' => "Booking cancelled Successfully!",
                    ];
                    return $response;
                } else {
                    $response = [
                        'error' => false,
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'message' => "Booking can not be cancelled",
                    ];
                    return $response;
                }
            } else {
                $res = json_decode($payment['body'], true);
                $msg = $res['error']['description'];
                $response = [
                    'error' => true,
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'message' => $msg,
                ];
                return $response;
            }
        }
        if ($type == "paystack" && $transaction[0]['status'] == "success") {
            $paystack = new Paystack();
            $amount = $transaction[0]['amount'] / 100;
            $payment = $paystack->refund($txn_id, $amount);
            $message = json_decode($payment, true);
            if (isset($message['status']) && $message['status'] == 1) {
                $amount = intval($message['data']['amount']);
                $data = [
                    'transaction_type' => 'refund',
                    'order_id' => $order_id,
                    'user_id' => $customer_id,
                    'type' => 'paystack',
                    'txn_id' => $message['data']['transaction']['id'],
                    'amount' => $amount,
                    'currency_code' => $currency,
                    'status' => $message['data']['status'],
                    'message' => 'paystack_refund',
                    'partner_id' => $partner_id
                ];
                $success = insert_details($data, 'transactions');
                update_details(['status' => $status], ['id' => $order_id, 'isRefunded' => '1'], 'orders');
                if ($success) {
                    $response = [
                        'error' => false,
                        'message' => "Booking cancelled Successfully!",
                    ];
                    return $response;
                } else {
                    $response = [
                        'error' => false,
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'message' => "Booking can not be cancelled",
                    ];
                    return $response;
                }
            } else {
                $res = json_decode($payment, true);
                $response = [
                    'error' => true,
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'message' => $res['message'],
                ];
                return $response;
            }
        }
        if ($type == "paypal" && $transaction[0]['status'] == 'success') {
            $paypal = new Paypal();
            $payment = $paypal->refund($txn_id, $amount, $transaction[0]['currency_code']);
            $message = json_decode($payment, true);
            if (isset($message['status']) && $message['status'] == "COMPLETED") {
                $data = [
                    'transaction_type' => 'refund',
                    'order_id' => $order_id,
                    'user_id' => $customer_id,
                    'type' => 'paypal',
                    'txn_id' => $txn_id,
                    'amount' => $amount,
                    'currency_code' => $currency,
                    'status' =>  $message['status'],
                    'message' => 'paypal_refund',
                    'partner_id' => $partner_id
                ];
                $success = insert_details($data, 'transactions');
                $response = [
                    'error' => false,
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'message' => "Payment Refund Successfully",
                ];
                if ($success) {
                    update_details(['status' => $status], ['id' => $order_id], 'orders');
                    $response = [
                        'error' => false,
                        'message' => "Booking cancelled Successfully!",
                    ];
                    return $response;
                }
                return $response;
            } else {
                $res = json_decode($payment['body']);
                $msg = $res->error->message;
                $response = [
                    'error' => true,
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'message' => $msg,
                ];
                return $response;
            }
        }
    } else {
        $response = [
            'error' => true,
            'csrfName' => csrf_token(),
            'csrfHash' => csrf_hash(),
            'message' => 'No transactio found of this order!',
        ];
        return $response;
    }
}
function process_service_refund($order_id, $ordered_service_id, $status, $customer_id, $amount)
{
    $transaction = fetch_details('transactions', ['order_id' => $order_id, 'transaction_type' => 'transaction'], ['amount', 'txn_id', 'type', 'currency_code', 'status']);
    if (isset($transaction) && !empty($transaction)) {
        $service_id = $ordered_service_id;
        $type = $transaction[0]['type'];
        $currency = $transaction[0]['currency_code'];
        $txn_id = $transaction[0]['txn_id'];
        $amount = $amount;
        if ($type == "stripe" && $transaction[0]['status'] == 'succeeded') {
            $stripe = new Stripe();
            $payment = $stripe->refund($txn_id, $amount);
            if (isset($payment['status']) && $payment['status'] == "succeeded") {
                $amount = intval($payment['amount']) / 100;
                $data = [
                    'transaction_type' => 'refund',
                    'order_id' => $order_id,
                    'user_id' => $customer_id,
                    'type' => 'stripe',
                    'txn_id' => $payment['payment_intent'],
                    'amount' => $amount,
                    'currency_code' => $currency,
                    'status' => $payment['status'],
                ];
                $success = insert_details($data, 'transactions');
                $response = [
                    'error' => false,
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'message' => "Payment Refund Successfully",
                ];
                if ($success) {
                    update_details(['status' => $status], ['id' => $order_id], 'orders');
                    $response = [
                        'error' => false,
                        'message' => "Booking cancelled Successfully!",
                    ];
                    return $response;
                }
                return $response;
            } else {
                $res = json_decode($payment['body']);
                $msg = $res->error->message;
                $response = [
                    'error' => true,
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'message' => $msg,
                ];
                return $response;
            }
        }
        if ($type == "razorpay" && $transaction[0]['status'] == "captured") {
            $razorpay = new Razorpay();
            $payment = $razorpay->refund_payment($txn_id, $amount);
            if (isset($payment['status']) && $payment['status'] == "processed") {
                $amount = intval($payment['amount']) / 100;
                $data = [
                    'transaction_type' => 'refund',
                    'order_id' => $order_id,
                    'user_id' => $customer_id,
                    'type' => 'razorpay',
                    'txn_id' => $payment['payment_id'],
                    'amount' => $amount,
                    'currency_code' => $currency,
                    'status' => $payment['status'],
                ];
                $success = insert_details($data, 'transactions');
                if ($success) {
                    update_details(['status' => $status], ['id' => $order_id], 'orders');
                    $response = [
                        'error' => false,
                        'message' => "Booking cancelle    d Successfully!",
                    ];
                    return $response;
                } else {
                    $response = [
                        'error' => false,
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'message' => "order can not be cancelled",
                    ];
                    return $response;
                }
            } else {
                $res = json_decode($payment['body'], true);
                $msg = $res['error']['description'];
                $response = [
                    'error' => true,
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'message' => $msg,
                ];
                return $response;
            }
        }
        if ($type == "paystack" && $transaction[0]['status'] == "success") {
            $paystack = new Paystack();
            $payment = $paystack->refund($txn_id, $amount);
            $message = json_decode($payment, true);
            if (isset($payment['status']) && $payment['status'] == "true") {
                update_details(['status' => $status], ['id' => $order_id], 'orders');
                $amount = intval($payment['amount']) / 100;
                $data = [
                    'transaction_type' => 'refund',
                    'order_id' => $order_id,
                    'user_id' => $customer_id,
                    'type' => 'paystack',
                    'txn_id' => $payment['payment_id'],
                    'amount' => $amount,
                    'currency_code' => $currency,
                    'status' => $payment['status'],
                ];
                $success = insert_details($data, 'transactions');
                if ($success) {
                    $response = [
                        'error' => false,
                        'message' => "Booking cancelled Successfully!",
                    ];
                    return $response;
                } else {
                    $response = [
                        'error' => false,
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'message' => "Booking can not be cancelled",
                    ];
                    return $response;
                }
            } else {
                $res = json_decode($payment, true);
                $response = [
                    'error' => true,
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'message' => $res['message'],
                ];
                return $response;
            }
        }
        if ($type == 'flutterwave' && $transaction[0]['status'] == "successfull") {
            $flutterwave = new Flutterwave();
            $payment = $flutterwave->refund_payment($txn_id, $amount);
            $payment = json_decode($payment);
            if (isset($payment->status) && $payment->status == 'success') {
                $data = [
                    'transaction_type' => 'refund',
                    'order_id' => $order_id,
                    'user_id' => $customer_id,
                    'type' => 'paystack',
                    'txn_id' => $payment['payment_id'],
                    'amount' => $amount,
                    'currency_code' => $currency,
                    'status' => $payment['status'],
                ];
                $success = insert_details($data, 'transactions');
                $response['error'] = false;
                $response['csrfName'] = csrf_token();
                $response['csrfHash'] = csrf_hash();
                $response['message'] = "Payment Refund Successfully";
                if ($success) {
                    update_details(['status' => $status], ['id' => $order_id], 'orders');
                    $response = [
                        'error' => false,
                        'message' => "Booking cancelled Successfully!",
                    ];
                    return $response;
                }
            } else {
                $message = json_decode($payment, true);
                $response['error'] = true;
                $response['csrfName'] = csrf_token();
                $response['csrfHash'] = csrf_hash();
                $response['message'] = $message['message'];
            }
        }
        if ($type == "paypal" && $transaction[0]['status'] == 'success') {
            $paypal = new Paypal();
            $payment = $paypal->refund($txn_id, $amount, $transaction[0]['currency_code']);
            $message = json_decode($payment, true);
            if (isset($message['status']) && $message['status'] == "COMPLETED") {
                $data = [
                    'transaction_type' => 'refund',
                    'order_id' => $order_id,
                    'user_id' => $customer_id,
                    'type' => 'paypal',
                    'txn_id' => $txn_id,
                    'amount' => $amount,
                    'currency_code' => $currency,
                    'status' => 'success',
                ];
                $success = insert_details($data, 'transactions');
                $response = [
                    'error' => false,
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'message' => "Payment Refund Successfully",
                ];
                if ($success) {
                    update_details(['status' => $status], ['id' => $order_id], 'orders');
                    $response = [
                        'error' => false,
                        'message' => "Booking cancelled Successfully!",
                    ];
                    return $response;
                }
                return $response;
            } else {
                $res = json_decode($payment['body']);
                $msg = $res->error->message;
                $response = [
                    'error' => true,
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'message' => $msg,
                ];
                return $response;
            }
        }
    } else {
        $response = [
            'error' => true,
            'csrfName' => csrf_token(),
            'csrfHash' => csrf_hash(),
            'message' => 'No transaction found of this order!',
        ];
        return $response;
    }
}
function curl($url, $method = 'GET', $header = ['Content-Type: application/x-www-form-urlencoded'], $data = [], $authorization = null)
{
    $ch = curl_init();
    $curl_options = array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_HTTPHEADER => $header,
    );
    if (strtolower($method) == 'post') {
        $curl_options[CURLOPT_POST] = 1;
        $curl_options[CURLOPT_POSTFIELDS] = http_build_query($data);
    } else {
        $curl_options[CURLOPT_CUSTOMREQUEST] = 'GET';
    }
    curl_setopt_array($ch, $curl_options);
    $result = array(
        'body' => curl_exec($ch),
        'http_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
    );
    return $result;
}
function generate_token()
{
    $jwt = new App\Libraries\JWT();
    $payload = [
        'iat' => time(), /* issued at time */
        'iss' => 'edemand',
        'exp' => time() + (30 * 60), /* expires after 1 minute */
        'sub' => 'edemand_authentication',
    ];
    $token = $jwt->encode($payload, "my_secret");
    return $token;
}
function verify_token()
{
    // to verify the token from admin pannel
    $responses = \Config\Services::response();
    $jwt = new App\Libraries\JWT;
    // verify_ip();
    try {
        $token = $jwt->getBearerToken();
    } catch (\Exception $e) {
        $response['error'] = true;
        $response['message'] = $e->getMessage();
        print_r(json_encode($response));
        return false;
    }
    if (!empty($token)) {
        $api_keys = API_SECRET;
        if (empty($api_keys)) {
            $response['error'] = true;
            $response['message'] = 'No Client(s) Data Found !';
            print_r(json_encode($response));
            return $response;
        }
        $flag = true; //For payload indication that it return some data or throws an expection.
        $error = true; //It will indicate that the payload had verified the signature and hash is valid or not.
        $message = '';
        $user_token = " ";
        try {
            $user_id = $jwt->decode_unsafe($token)->user_id;
            $user_token = fetch_details('users', ['id' => $user_id])[0]['api_key'];
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        try {
            $payload = $jwt->decode($token, $api_keys, ['HS256']);
            if (isset($payload->iss)) {
                $error = false;
                $flag = false;
            } else {
                $error = true;
                $flag = false;
                $message = 'Invalid Hash';
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        if ($flag) {
            $response['error'] = true;
            $response['message'] = $message;
            print_r(json_encode($response));
            return false;
        } else {
            if ($error == true) {
                $response['error'] = true;
                $response['message'] = $message;
                $responses->setStatusCode(401);
                print_r(json_encode($response));
                return false;
            } else {
                return $payload->user_id;
            }
        }
    } else {
        $response['error'] = true;
        $response['message'] = "Unauthorized access not allowed";
        print_r(json_encode($response));
        return false;
    }
}
function xss_clean($data)
{
    $data = trim($data);
    // Fix &entity\n;
    $data = str_replace(array('&amp;', '&lt;', '&gt;'), array('&amp;amp;', '&amp;lt;', '&amp;gt;'), $data);
    $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
    $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
    $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');
    // Remove any attribute starting with "on" or xmlns
    $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);
    // Remove javascript: and vbscript: protocols
    $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
    $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
    $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);
    // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
    $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
    $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
    $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);
    // Remove namespaced elements (we do not need them)
    $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);
    do {
        // Remove really unwanted tags
        $old_data = $data;
        $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
    } while ($old_data !== $data);
    // we are done...
    return $data;
}
function get_settings($type = 'system_settings', $is_json = false, $bool = false)
{
    $db = \Config\Database::connect();
    $builder = $db->table('settings');
    if ($type == 'all') {
        $res = $builder->select(' * ')->get()->getResultArray();
    } else {
        $res = $builder->select(' * ')->where('variable', $type)->get()->getResultArray();
    }
    if (!empty($res)) {
        if ($is_json) {
            return json_decode($res[0]['value'], true);
        } else {
            return $res[0]['value'];
        }
    } else {
        if ($bool) {
            return false;
        } else {
            return [];
        }
    }
}
// function escape_array($array)
// {
//     $db = \Config\Database::connect();
//     $posts = array();
//     if (!empty($array)) {
//         if (is_array($array)) {
//             foreach ($array as $key => $value) {
//                 $posts[$key] = $db->escapeString($value);
//             }
//         } else {
//             return $db->escapeString($array);
//         }
//     }
//     return $posts;
// }
function escape_array($array)
{
    $db = \Config\Database::connect();
    $posts = [];

    if (!empty($array)) {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                // Only escape strings, leave other types as is
                $posts[$key] = is_string($value) ? $db->escapeString($value) : $value;

                   
                // if( $value==0 || !empty($value) ){
                  
                //     $posts[$key] = $db->escapeString($value);
                // }
            }
        } else {
            // Escape only if it's a string
            return is_string($array) ? $db->escapeString($array) : $array;
        }
    }

    return $posts;
}

function update_details($set, $where, $table, $escape = true)
{
    $db = \Config\Database::connect();
    $db->transStart();
    if ($escape) {
        $set = escape_array($set);
    }
    $db->table($table)->update($set, $where);
    $db->transComplete();
    $response = false;
    if ($db->transStatus() === true) {
        $response = true;
    }
    return $response;
}
function fetch_details($table, $where = [], $fields = [], $limit = "", $offset = '0', $sort = 'id', $order = 'DESC', $where_in_key = '', $where_in_value = [], $or_like = [])
{
    $db = \Config\Database::connect();
    $builder = $db->table($table);
    if (!empty($fields)) {
        $builder = $builder->select($fields);
    }
    if (!empty($where)) {
        $builder = $builder->where($where)->select($fields);
    }
    if (!empty($where_in_key) && !empty($where_in_value)) {
        $builder = $builder->whereIn($where_in_key, $where_in_value);
    }
    if (isset($or_like) && !empty($or_like)) {
        $builder->groupStart();
        $builder->orLike($or_like);
        $builder->groupEnd();
    }
    if ($limit != null && $limit != "") {
        $builder = $builder->limit($limit, $offset);
    }
    $builder = $builder->orderBy($sort, $order);
    $res = $builder->get()->getResultArray();
    return $res;
}
function exists($where, $table)
{
    $db = \Config\Database::connect();
    $builder = $db->table($table);
    $builder = $builder->where($where);
    $res = count($builder->get()->getResultArray());
    if ($res > 0) {
        return true;
    } else {
        return false;
    }
}
function get_group($name = "")
{
    $db = \Config\Database::connect();
    $builder = $db->table("groups as g");
    $builder->select('ug.*,g.name');
    $builder->where('g.name', $name);
    $builder->join('users_groups as ug', 'g.id = ug.group_id ', "left");
    $group = $builder->get()->getResultArray();
    return $group;
}
function slugify($text, $divider = '-')
{
    $text = preg_replace('~[^\pL\d]+~u', $divider, $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, $divider);
    $text = preg_replace('~-+~', $divider, $text);
    $text = strtolower($text);
    if (empty($text)) {
        return 'n-a';
    }
    return $text;
}
function verify_payment_transaction($txn_id, $payment_method, $additional_data = [])
{
    $db = \Config\Database::connect();
    if (empty(trim($txn_id))) {
        $response['error'] = true;
        $response['message'] = "Transaction ID is required";
        return $response;
    }
    $razorpay = new Razorpay;
    switch ($payment_method) {
        case 'razorpay':
            $payment = $razorpay->fetch_payments($txn_id);
            if (!empty($payment) && isset($payment['status'])) {
                if ($payment['status'] == 'authorized') {
                    $capture_response = $razorpay->capture_payment($payment['amount'], $txn_id, $payment['currency']);
                    if ($capture_response['status'] == 'captured') {
                        $response['error'] = false;
                        $response['message'] = "Payment captured successfully";
                        $response['amount'] = $capture_response['amount'] / 100;
                        $response['data'] = $capture_response;
                        $response['status'] = $payment['status'];
                        return $response;
                    } else if ($capture_response['status'] == 'refunded') {
                        $response['error'] = true;
                        $response['message'] = "Payment is refunded.";
                        $response['amount'] = $capture_response['amount'] / 100;
                        $response['data'] = $capture_response;
                        $response['status'] = $payment['status'];
                        return $response;
                    } else {
                        $response['error'] = true;
                        $response['message'] = "Payment could not be captured.";
                        $response['amount'] = (isset($capture_response['amount'])) ? $capture_response['amount'] / 100 : 0;
                        $response['data'] = $capture_response;
                        $response['status'] = $payment['status'];
                        return $response;
                    }
                } else if ($payment['status'] == 'captured') {
                    $status = 'captured';
                    $response['error'] = false;
                    $response['message'] = "Payment captured successfully";
                    $response['amount'] = $payment['amount'] / 100;
                    $response['status'] = $payment['status'];
                    $response['data'] = $payment;
                    return $response;
                } else if ($payment['status'] == 'created') {
                    $status = 'created';
                    $response['error'] = true;
                    $response['message'] = "Payment is just created and yet not authorized / captured!";
                    $response['amount'] = $payment['amount'] / 100;
                    $response['data'] = $payment;
                    $response['status'] = $payment['status'];
                    return $response;
                } else {
                    $status = 'failed';
                    $response['error'] = true;
                    $response['message'] = "Payment is " . ucwords($payment['status']) . "! ";
                    $response['amount'] = (isset($payment['amount'])) ? $payment['amount'] / 100 : 0;
                    $response['status'] = $payment['status'];
                    $response['data'] = $payment;
                    return $response;
                }
            } else {
                $response['error'] = true;
                $response['message'] = "Payment not found by the transaction ID!";
                $response['amount'] = 0;
                $response['data'] = [];
                $response['status'] = 'failed';
                return $response;
            }
            break;
        case "paystack":
            $paystack = new Paystack;
            $payment = $paystack->verify_transation($txn_id);
            if (!empty($payment)) {
                $payment = json_decode($payment, true);
                if (isset($payment['data']['status']) && $payment['data']['status'] == 'success') {
                    $response['error'] = false;
                    $response['message'] = "Payment is successful";
                    $response['amount'] = (isset($payment['data']['amount'])) ? $payment['data']['amount'] / 100 : 0;
                    $response['data'] = $payment;
                    $response['status'] = $payment['data']['status'];
                    return $response;
                } elseif (isset($payment['data']['status']) && $payment['data']['status'] != 'success') {
                    $response['error'] = true;
                    $response['message'] = "Payment is " . ucwords($payment['data']['status']) . "! ";
                    $response['amount'] = (isset($payment['data']['amount'])) ? $payment['data']['amount'] / 100 : 0;
                    $response['data'] = $payment;
                    $response['status'] = $payment['data']['status'];
                    return $response;
                } else {
                    $response['error'] = true;
                    $response['message'] = "Payment is unsuccessful! ";
                    $response['amount'] = (isset($payment['data']['amount'])) ? $payment['data']['amount'] / 100 : 0;
                    $response['data'] = $payment;
                    return $response;
                }
            } else {
                $response['error'] = true;
                $response['message'] = "Payment not found by the transaction ID!";
                $response['amount'] = 0;
                $response['data'] = [];
                $response['status'] = 'failed';
                return $response;
            }
            break;
        case 'paytm':
            $paytm = new Paytm;
            $payment = $paytm->transaction_status($txn_id);
            if (!empty($payment)) {
                $payment = json_decode($payment, true);
                if (
                    isset($payment['body']['resultInfo']['resultCode'])
                    && ($payment['body']['resultInfo']['resultCode'] == '01' && $payment['body']['resultInfo']['resultStatus'] == 'TXN_SUCCESS')
                ) {
                    $response['error'] = false;
                    $response['message'] = "Payment is successful";
                    $response['amount'] = (isset($payment['body']['txnAmount'])) ? $payment['body']['txnAmount'] : 0;
                    $response['data'] = $payment;
                    return $response;
                } elseif (
                    isset($payment['body']['resultInfo']['resultCode'])
                    && ($payment['body']['resultInfo']['resultStatus'] == 'TXN_FAILURE')
                ) {
                    $response['error'] = true;
                    $response['message'] = $payment['body']['resultInfo']['resultMsg'];
                    $response['amount'] = (isset($payment['body']['txnAmount'])) ? $payment['body']['txnAmount'] : 0;
                    $response['data'] = $payment;
                    return $response;
                } else if (
                    isset($payment['body']['resultInfo']['resultCode'])
                    && ($payment['body']['resultInfo']['resultStatus'] == 'PENDING')
                ) {
                    $response['error'] = true;
                    $response['message'] = $payment['body']['resultInfo']['resultMsg'];
                    $response['amount'] = (isset($payment['body']['txnAmount'])) ? $payment['body']['txnAmount'] : 0;
                    $response['data'] = $payment;
                    return $response;
                } else {
                    $response['error'] = true;
                    $response['message'] = "Payment is unsuccessful!";
                    $response['amount'] = (isset($payment['body']['txnAmount'])) ? $payment['body']['txnAmount'] : 0;
                    $response['data'] = $payment;
                    return $response;
                }
            } else {
                $response['error'] = true;
                $response['message'] = "Payment not found by the Order ID!";
                $response['amount'] = 0;
                $response['data'] = [];
                return $response;
            }
            break;
    }
}
function add_transaction($transaction_details)
{
    $db = \Config\Database::connect();
    $insert = $db->table('transactions')->insert($transaction_details);
    if ($insert) {
        return $db->insertID();
    } else {
        return false;
    }
}
function valid_image($image)
{
    helper(['form', 'url']);
    $request = \Config\Services::request();
    if ($request->getFile($image)) {
        $file = $request->getFile($image);
        if (!$file->isValid()) {
            return false;
        }
        $type = $file->getMimeType();
        if ($type == 'image/jpeg' || $type == 'image/png' || $type == 'image/jpg' || $type == 'image/svg+xml' || $type = 'image/gif') {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}
function move_file($file, $path = 'public/uploads/images/', $name = '', $replace = false, $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/svg+xml', 'image/gif', 'application/json', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/pdf'])
{
    $type = $file->getMimeType();
    $p = FCPATH . $path;
    if (in_array($type, $allowed_types)) {
        if ($name == '') {
            $name = preg_replace('/\\.[^.\\s]{3,4}$/', '', $file->getName());
        }
        $ext = $file->guessExtension();
        if ($file->move($p, $name, $replace)) {
            $name = $file->getName();
            $response['error'] = false;
            $response['message'] = "File moved successfully";
            $response['file_name'] = $name;
            $response['extension'] = $ext;
            $response['file_size'] = $file->getSizeByUnit("kb");
            $response['path'] = $path;
            $response['full_path'] = $path . $name;
        } else {
            $response['error'] = true;
            $response['message'] = "File could not be moved!" . $file->getError();
            $response['file_name'] = $name;
            $response['extension'] = "";
            $response['file_size'] = "";
            $response['path'] = $path;
            $response['full_path'] = "";
        }
        return $response;
    } else {
        $response['error'] = true;
        $response['message'] = "File could not be moved! Invalid file type uploaded";
        return $response;
    }
}
function move_chat_file($file, $path = 'public/uploads/images/', $name = '', $replace = false, $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/svg+xml', 'image/gif', 'application/json', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/pdf', 'application/zip'])
{
    $type = $file->getMimeType();
    $p = FCPATH . $path;
    if (in_array($type, $allowed_types)) {
        if ($name == '') {
            $name = preg_replace('/\\.[^.\\s]{3,4}$/', '', $file->getName());
        }
        $ext = $file->guessExtension();
        if ($file->move($p, $name, $replace)) {
            $name = $file->getName();
            $response['error'] = false;
            $response['message'] = "File moved successfully";
            $response['file_name'] = $name;
            $response['extension'] = $ext;
            $response['file_size'] = $file->getSizeByUnit("kb");
            $response['path'] = $path;
            $response['full_path'] = $path . $name;
        } else {
            $response['error'] = true;
            $response['message'] = "File could not be moved!" . $file->getError();
            $response['file_name'] = $name;
            $response['extension'] = "";
            $response['file_size'] = "";
            $response['path'] = $path;
            $response['full_path'] = "";
        }
        return $response;
    } else {
        $response['error'] = true;
        $response['message'] = "File could not be moved! Invalid file type uploaded";
        return $response;
    }
}
function formatOffset($offset)
{
    $hours = $offset / 3600;
    $remainder = $offset % 3600;
    $sign = $hours > 0 ? '+' : '-';
    $hour = (int) abs($hours);
    $minutes = (int) abs($remainder / 60);
    if ($hour == 0 and $minutes == 0) {
        $sign = ' ';
    }
    return $sign . str_pad($hour, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minutes, 2, '0');
}
function get_timezone_array()
{
    $list = DateTimeZone::listAbbreviations();
    $idents = DateTimeZone::listIdentifiers();
    $data = $offset = $added = array();
    foreach ($list as $abbr => $info) {
        foreach ($info as $zone) {
            if (
                !empty($zone['timezone_id'])
                and
                !in_array($zone['timezone_id'], $added)
                and
                in_array($zone['timezone_id'], $idents)
            ) {
                $z = new DateTimeZone($zone['timezone_id']);
                $c = new DateTime("", $z);
                $zone['time'] = $c->format('h:i A');
                $offset[] = $zone['offset'] = $z->getOffset($c);
                $data[] = $zone;
                $added[] = $zone['timezone_id'];
            }
        }
    }
    array_multisort($offset, SORT_ASC, $data);
    $i = 0;
    $temp = array();
    foreach ($data as $key => $row) {
        $temp[0] = $row['time'];
        $temp[1] = formatOffset($row['offset']);
        $temp[2] = $row['timezone_id'];
        $options[$i++] = $temp;
    }
    return $options;
}
function check_exists($file)
{
    $target_path = FCPATH . $file;
    if (!file_exists($target_path)) {
        return true;
    } else {
        return false;
    }
}
function get_system_update_info()
{
    $check_query = false;
    $query_path = "";
    $data['previous_error'] = false;
    $sub_directory = (file_exists(UPDATE_PATH . "update/updater.json")) ? "update/" : "";
    if (file_exists(UPDATE_PATH . "updater.json") || file_exists(UPDATE_PATH . "update/updater.json")) {
        $lines_array = file_get_contents(UPDATE_PATH . $sub_directory . "updater.json");
        $lines_array = json_decode($lines_array, true);
        $file_version = $lines_array['version'];
        $file_previous = $lines_array['previous'];
        $check_query = $lines_array['manual_queries'];
        $query_path = $lines_array['query_path'];
    } else {
        print_r("no json exists");
        die();
    }
    $db_version_data = fetch_details("updates");
    if (!empty($db_version_data) && isset($db_version_data[0]['version'])) {
        $db_current_version = $db_version_data[0]['version'];
    }
    if (!empty($db_current_version)) {
        $data['db_current_version'] = $db_current_version;
    } else {
        $data['db_current_version'] = $db_current_version = 1.0;
    }
    if ($db_current_version == $file_previous) {
        $data['file_current_version'] = $file_current_version = $file_version;
    } else {
        $data['previous_error'] = true;
        $data['file_current_version'] = $file_current_version = false;
    }
    if ($file_current_version != false && $file_current_version > $db_current_version) {
        $data['is_updatable'] = true;
    } else {
        $data['is_updatable'] = false;
    }
    $data['query'] = $check_query;
    $data['query_path'] = $query_path;
    return $data;
}
function labels($label, $alt = '')
{
    //If label is array
    if (is_array($label)) {
        $translated = [];
        foreach ($label as $key => $value) {
            if (is_string($value)) {
                $value = trim($value);
                if (lang('Text.' . $value) != 'Text.' . $value) {
                    $translated[$key] = (lang('Text.' . $value) == '') ? trim($alt) : trim(lang('Text.' . $value));
                } else {
                    //as alt shall be an array as well
                    if(is_array($alt)) {
                        foreach($alt as $a) {
                            $translated[$key] = trim($a);

                        }
                    }
                }
            }
        }
        return $translated;
    }

    // If label is string
    $label = trim($label);
    if (lang('Text.' . $label) != 'Text.' . $label) {
        if (lang('Text.' . $label) == '') {
            return $alt;
        }
        return trim(lang('Text.' . $label));
    } else {
        return trim($alt);
    }
}
function get_currency()
{
    try {
        $currency = get_settings('general_settings', true)['currency'];
        if ($currency == '') {
            $currency = '₹';
        }
    } catch (Exception $e) {
        $currency = '₹';
    }
    return $currency;
}
function console_log($data)
{
    if (is_array($data)) {
        $data = json_encode($data);
    } elseif (is_object($data)) {
        $data = json_encode($data);
    }
    echo "<script>console.log('$data')</script>";
}
function delete_directory($dir)
{
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir . "/" . $object) == "dir") {
                    $dir_sec = $dir . "/" . $object;
                    if (is_dir($dir_sec)) {
                        $objects_sec = scandir($dir_sec);
                        foreach ($objects_sec as $object_sec) {
                            if ($object_sec != "." && $object_sec != "..") {
                                if (filetype($dir_sec . "/" . $object_sec) == "dir") {
                                    rmdir($dir_sec . "/" . $object_sec);
                                } else {
                                    unlink($dir_sec . "/" . $object_sec);
                                }
                            }
                        }
                        rmdir($dir_sec);
                    }
                } else {
                    unlink($dir . "/" . $object);
                }
            }
        }
        return rmdir($dir);
    }
}
function format_number($number, $decimals = 0, $decimal_separator = '.', $thousand_separator = ',', $currency_symbol = '', $type = 'prefix')
{
    $number = number_format($number, $decimals, $decimal_separator, $thousand_separator);
    $number = (!empty(trim($currency_symbol))) ? (($type == 'prefix') ? $currency_symbol . $number : $number . $currency_symbol) : $number;
    return $number;
}
function email_sender($user_email, $subject, $message)
{
    $email = \Config\Services::email();
    $email_settings = \get_settings('email_settings', true);
    $company_settings = \get_settings('general_settings', true);
    $smtpUsername = $email_settings['smtpUsername'];
    $company_name = $company_settings['company_title'];
    $email->setFrom($smtpUsername, $company_name);
    $email->setTo($user_email);
    $email->setSubject($subject);
    $email->setMessage($message);
    if ($email->send()) {
    } else {
        $data = $email->printDebugger(['headers']);
        return $data;
    }
}
function insert_details(array $data, string $table): array
{
    $db = \Config\Database::connect();
    $status = $db->table($table)->insert($data);
    $id = $db->insertID();
    if (!$status) {
        return [
            "error" => true,
            "message" => UNKNOWN_ERROR_MESSAGE,
            "data" => [],
        ];
    }
    return [
        "error" => false,
        "message" => "Data inserted",
        "id" => $id,
        "data" => [],
    ];
}
function remove_null_values(array $data)
{
    $integer = [
        'alternate_mobile' => 0,
        'range_wise_charges' => 0,
        'per_km_charge' => 0,
        'max_deliverable_distance' => 0,
        'fixed_charge' => 0,
        'discount' => 0,
    ];
    $array = [];
    foreach ($data as $key => $value) {
        if (is_array($value) || is_object($value)) {
            $data[$key] = remove_null_values($value);
        } else {
            if (is_null($value)) {
                if (isset($integer[$key])) {
                    $data[$key] = 0;
                } else if (isset($array[$key])) {
                    $data[$key] = [];
                } else {
                    $data[$key] = '';
                }
            }
        }
    }
    return $data;
}
if (!function_exists('response_helper')) {

    function response_helper(string $message = UNKNOWN_ERROR_MESSAGE, bool $error = true, $data = [], int $status_code = 200, $additional_data = [])
    {
        $response = \Config\Services::response();
        $send = [
            "error" => $error,
            "message" => labels($message, $message),
            "data" => $data,
        ];
        $send = array_merge($send, $additional_data);
        return $response->setJSON($send)->setStatusCode($status_code);
    }
}
function delete_details(array $data, string $table)
{
    $db = \Config\Database::connect();
    $builder = $db->table($table);
    if ($builder->delete($data)) {
        return true;
    }
    return false;
}
function validate_promo_code($user_id, $promo_code, $final_total)
{
    $db = \Config\Database::connect();
    $builder = $db->table('promo_codes pc');
    $promo_code = $builder->select('pc.*,count(o.id) as promo_used_counter ,( SELECT count(user_id) from orders where user_id =' . $user_id . ' and promocode_id ="' . $promo_code . '") as user_promo_usage_counter ')
        ->join('orders o', 'o.promocode_id=pc.id', 'left')
        ->where(['pc.id' => $promo_code, 'pc.status' => '1', ' start_date <= ' => date('Y-m-d'), '  end_date >= ' => date('Y-m-d')])
        ->get()->getResultArray();
    if (!empty($promo_code[0]['id'])) {
        if (intval($promo_code[0]['promo_used_counter']) < intval($promo_code[0]['no_of_users'])) {
            if ($final_total >= intval($promo_code[0]['minimum_order_amount'])) {
                if ($promo_code[0]['repeat_usage'] == 1 && ($promo_code[0]['user_promo_usage_counter'] <= $promo_code[0]['no_of_repeat_usage'])) {
                    if (intval($promo_code[0]['user_promo_usage_counter']) <= intval($promo_code[0]['no_of_repeat_usage'])) {
                        $response['error'] = false;
                        $response['message'] = 'The promo code is valid';
                        if ($promo_code[0]['discount_type'] == 'percentage') {
                            $promo_code_discount = floatval($final_total * $promo_code[0]['discount'] / 100);
                        } else {

                            $promo_code_discount = floatval($final_total - $promo_code[0]['discount']);
                        }
                        if ($promo_code[0]['discount_type'] == 'amount') {
                            if ($promo_code[0]['discount'] > $final_total) {
                                $promo_code_discount = $final_total;
                                $total = floatval($final_total);
                            }
                        }
                        if ($promo_code_discount > $final_total) {
                            if ($promo_code[0]['discount_type'] == 'amount') {

                                $promo_code_discount = $final_total;
                                $total = floatval($final_total);
                            }
                        } else {
                            if ($promo_code_discount <= $promo_code[0]['max_discount_amount']) {

                                $promo_code[0]['discount_type'] == 'amount' ? $total = floatval($final_total) - $promo_code[0]['discount']
                                    : $total = floatval($final_total) - $promo_code_discount;
                                // $total = floatval($final_total) - $promo_code_discount;
                            } else {
                                $total = floatval($final_total) - $promo_code[0]['max_discount_amount'];
                                $promo_code_discount = $promo_code[0]['max_discount_amount'];
                            }
                        }
                        $promo_code[0]['final_total'] = strval(floatval($total));
                        $promo_code[0]['final_discount'] = strval(floatval($promo_code_discount));
                        $response['data'] = $promo_code;
                        return $response;
                    } else {
                        $response['error'] = true;
                        $response['message'] = 'This promo code cannot be redeemed as it exceeds the usage limit';
                        $response['data']['final_total'] = strval(floatval($final_total));
                        return $response;
                    }
                } else if ($promo_code[0]['repeat_usage'] == 0 && ($promo_code[0]['user_promo_usage_counter'] <= 0)) {
                    if (intval($promo_code[0]['user_promo_usage_counter']) <= intval($promo_code[0]['no_of_repeat_usage'])) {
                        $response['error'] = false;
                        $response['message'] = 'The promo code is valid';
                        // if ($promo_code[0]['discount_type'] == 'percentage') {
                        //     $promo_code_discount = floatval($final_total * $promo_code[0]['discount'] / 100);
                        // } else {
                        //     $promo_code_discount = floatval($final_total - $promo_code[0]['discount']);
                        // }
                        // if ($promo_code_discount > $final_total) {
                        //     $promo_code_discount = $final_total;
                        //     $total = floatval($final_total);
                        // } else {
                        //     if ($promo_code_discount <= $promo_code[0]['max_discount_amount']) {
                        //         $total = floatval($final_total) - $promo_code_discount;
                        //     } else {
                        //         $total = floatval($final_total) - $promo_code[0]['max_discount_amount'];
                        //         $promo_code_discount = $promo_code[0]['max_discount_amount'];
                        //     }
                        // }
                        if ($promo_code[0]['discount_type'] == 'percentage') {
                            $promo_code_discount = floatval($final_total * $promo_code[0]['discount'] / 100);
                        } else {
                            $promo_code_discount = floatval($final_total - $promo_code[0]['discount']);
                        }
                        if ($promo_code[0]['discount'] > $final_total) {
                            $promo_code_discount = $final_total;
                            $total = floatval($final_total);
                        }
                        if ($promo_code_discount > $final_total) {
                            $promo_code_discount = $final_total;
                            $total = floatval($final_total);
                        } else {
                            if ($promo_code_discount <= $promo_code[0]['max_discount_amount']) {
                                $total = floatval($final_total) - $promo_code_discount;
                            } else {
                                $total = floatval($final_total) - $promo_code[0]['max_discount_amount'];
                                $promo_code_discount = $promo_code[0]['max_discount_amount'];
                            }
                        }
                        $promo_code[0]['final_total'] = strval(floatval($total));
                        $promo_code[0]['final_discount'] = strval(floatval($promo_code_discount));
                        $response['data'] = $promo_code;
                        return $response;
                    } else {
                        $response['error'] = true;
                        $response['message'] = 'This promo code cannot be redeemed as it exceeds the usage limit';
                        $response['data']['final_total'] = strval(floatval($final_total));
                        return $response;
                    }
                } else {
                    $response['error'] = true;
                    $response['message'] = 'The promo has already been redeemed. cannot be reused';
                    $response['data']['final_total'] = strval(floatval($final_total));
                    return $response;
                }
            } else {
                $response['error'] = true;
                $response['message'] = 'This promo code is applicable only for amount greater than or equal to ' . $promo_code[0]['minimum_order_amount'];
                $response['data']['final_total'] = strval(floatval($final_total));
                return $response;
            }
        } else {
            $response['error'] = true;
            $response['message'] = "promocode usage exceeded";
            $response['data']['final_total'] = strval(floatval($final_total));
            return $response;
        }
    } else {
        $response['error'] = true;
        $response['message'] = 'The promo code is not available or expired';
        $response['data']['final_total'] = strval(floatval($final_total));
        return $response;
    }
}

function validate_promo_code_new($user_id, $promo_code, $final_total)
{

    $db = \Config\Database::connect();
    $builder = $db->table('promo_codes pc');
    $promo_code = $builder->select('pc.*,count(o.id) as promo_used_counter ,( SELECT count(user_id) from orders where user_id =' . $user_id . ' and promocode_id ="' . $promo_code . '") as user_promo_usage_counter ')
        ->join('orders o', 'o.promocode_id=pc.id', 'left')
        ->where(['pc.id' => $promo_code, 'pc.status' => '1', ' start_date <= ' => date('Y-m-d'), '  end_date >= ' => date('Y-m-d')])
        ->get()->getResultArray();
    if (empty($promo_code_data)) {
        return generateErrorResponse('The promo code is not available or expired', $final_total);
    }

    if (intval($promo_code_data['promo_used_counter']) >= intval($promo_code_data['no_of_users'])) {
        return generateErrorResponse('Promocode usage exceeded', $final_total);
    }

    if ($final_total < intval($promo_code_data['minimum_order_amount'])) {
        return generateErrorResponse('This promo code is applicable only for amount greater than or equal to ' . $promo_code_data['minimum_order_amount'], $final_total);
    }

    if ($promo_code_data['repeat_usage'] == 1 && intval($promo_code_data['user_promo_usage_counter']) > intval($promo_code_data['no_of_repeat_usage'])) {
        return generateErrorResponse('This promo code cannot be redeemed as it exceeds the usage limit', $final_total);
    }

    if ($promo_code_data['repeat_usage'] == 0 && intval($promo_code_data['user_promo_usage_counter']) > 0) {
        return generateErrorResponse('The promo has already been redeemed. cannot be reused', $final_total);
    }

    $promo_code_discount = calculateDiscount($promo_code_data, $final_total);
    $total = $final_total - $promo_code_discount;

    $promo_code_data['final_total'] = strval(floatval($total));
    $promo_code_data['final_discount'] = strval(floatval($promo_code_discount));

    return [
        'error' => false,
        'message' => 'The promo code is valid',
        'data' => $promo_code_data
    ];
}

function generateErrorResponse($message, $final_total)
{
    return [
        'error' => true,
        'message' => $message,
        'data' => ['final_total' => strval(floatval($final_total))]
    ];
}

function calculateDiscount($promo_code_data, $final_total)
{
    if ($promo_code_data['discount_type'] == 'percentage') {
        $promo_code_discount = floatval($final_total * $promo_code_data['discount'] / 100);
    } else {
        $promo_code_discount = floatval($promo_code_data['discount']);
    }

    if ($promo_code_data['discount_type'] == 'amount' && $promo_code_discount > $final_total) {
        $promo_code_discount = $final_total;
    }

    if ($promo_code_discount > $promo_code_data['max_discount_amount']) {
        $promo_code_discount = $promo_code_data['max_discount_amount'];
    }

    return $promo_code_discount;
}
function get_near_partners($latitude, $longitude, $distance, $is_array = false)
{
    $max_deliverable_distance = $distance;
    $db = \Config\Database::connect();
    $point = ($latitude > -90 && $latitude < 90) ? "POINT($latitude" : "POINT($latitude > 90";
    $point .= ($longitude > -180 && $longitude < 180) ? " $longitude)" : " $longitude > 180)";
    $builder = $db->table('users u');
    $partners = $builder->Select("u.latitude,u.longitude,u.id,st_distance_sphere(POINT($longitude, $latitude), POINT(`longitude`, `latitude` ))/1000  as distance")
        ->join('users_groups ug', 'ug.user_id=u.id')
        ->where('ug.group_id', '3')
        // ->where('ABS((u.latitude)) > 180  or  ABS((u.longitude)) > 90')
        ->having('distance < ' . $max_deliverable_distance)
        ->orderBy('distance')
        ->get()->getResultArray();
    $ids = [];
    foreach ($partners as $key => $parnter) {
        $ids[] = $parnter['id'];
    }
    if ($is_array == false) {
        $ids = implode(',', $ids);
    }
    return $ids;
}
function fetch_cart($from_app = false, int $user_id = 0, string $search = '', $limit = 0, int $offset = 0, string $sort = 'c.id', string $order = 'Desc', $where = [], $additional_data = [], $reorder = null, $order_id = null)
{
    
    $disk = fetch_current_file_manager();

    $db = \Config\Database::connect();
    $builder = $db->table('cart c');
    $sortable_fields = [
        'c.id' => 'c.id',
    ];
    if ($search and $search != '') {
        $multipleWhere = [
            '`s.id`' => $search,
            '`s.title`' => $search,
            '`s.description`' => $search,
            '`s.status`' => $search,
            '`s.tags`' => $search,
            '`s.price`' => $search,
            '`s.discounted_price`' => $search,
            '`s.rating`' => $search,
            '`s.number_of_ratings`' => $search,
            '`s.max_quantity_allowed`' => $search,
        ];
    }
    $total = $builder->select(' COUNT(c.id) as `total` ')->where('c.user_id', $user_id);
    if (isset($multipleWhere) && !empty($multipleWhere)) {
        $builder->orWhere($multipleWhere);
    }
    if (isset($where) && !empty($where)) {
        $builder->where($where);
    }
    $service_count = $builder->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();
    $total = $service_count[0]['total'];
    if (isset($multipleWhere) && !empty($multipleWhere)) {
        $builder->orLike($multipleWhere);
    }
    if (isset($where) && !empty($where)) {
        $builder->where($where);
    }
    if ($reorder == 'yes' && !empty($order_id)) {
        $builder = $db->table('order_services os');
        $service_record = $builder
            ->select('os.id as cart_id,os.service_id,os.quantity as qty,s.image as service_image,s.*,s.title as service_name,p.username as partner_name,pd.visiting_charges as visiting_charges,cat.name as category_name')
            ->join('services s', 'os.service_id=s.id', 'left')
            ->join('orders o', 'o.id=os.order_id', 'left')
            ->join('users p', 'p.id=s.user_id', 'left')
            ->join('categories cat', 'cat.id=s.category_id', 'left')
            ->join('partner_details pd', 'pd.partner_id=s.user_id', 'left')
            ->where('os.order_id', $order_id)
            ->where('o.user_id', $user_id)->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();
    } else {
        $service_record = $builder
            ->select('c.id as cart_id,c.service_id,c.qty,c.is_saved_for_later,s.image as service_image,s.*,s.title as service_name,p.username as partner_name,pd.visiting_charges as visiting_charges,cat.name as category_name')
            ->join('services s', 'c.service_id=s.id', 'left')
            ->join('users p', 'p.id=s.user_id', 'left')
            ->join('categories cat', 'cat.id=s.category_id', 'left')
            ->join('partner_details pd', 'pd.partner_id=s.user_id', 'left')
            ->where('c.user_id', $user_id)->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();
    }


    $bulkData = $rows = $tempRow = array();
    $bulkData['total'] = $total;
    $tax = get_settings('system_tax_settings', true)['tax'];
    foreach ($service_record as $row) {
        if ($from_app) {
            if ($disk == "local_server") {
                if (check_exists(base_url('/public/uploads/services/' . $row['service_image']))) {
                    $images = base_url($row['image']);
                } else {
                    $images = 'nothing found';
                }
            } else if ($disk == "aws_s3") {
                $images = fetch_cloud_front_url('services', $row['service_image']);
            } else {
                $images = "";
            }
        } else {
            if ($disk == "local_server") {
                if (check_exists(base_url('/public/uploads/services/' . $row['service_image']))) {
                    $image_url = base_url('/public/uploads/services/' . $row['service_image']);
                } else {
                    $image_url = 'nothing found';
                }
            } else if ($disk == "aws_s3") {
                $image_url = fetch_cloud_front_url('services', $row['service_image']);
            } else {
                $image_url = "";
            }
            $images = '<a  href="' . $image_url . '" data-lightbox="image-1"><img height="80px" class="rounded-circle" src="' . $image_url . '" alt="image of the services multiple will be here"></a>';
        }
        $status = ($row['status'] == 1) ? 'Enable' : 'Disable';
        $site_allowed = ($row['on_site_allowed'] == 1) ? 'Allowed' : 'Not Allowed';
        $pay_later = ($row['is_pay_later_allowed'] == 1) ? 'Allowed' : 'Not Allowed';
        $rating = $row['rating'] . "/5";
        $tempRow['id'] = $row['cart_id'];
        $tempRow['order_id'] = $order_id ?? "";
        $tempRow['service_id'] = $row['service_id'];
        $tempRow['is_saved_for_later'] = isset($row['is_saved_for_later']) ? $row['is_saved_for_later'] : "";
        $tempRow['qty'] = isset($row['qty']) ? $row['qty'] : 0;
        $tempRow['visiting_charges'] = $row['visiting_charges'];
        $tempRow['price'] = $row['price'];
        $tempRow['discounted_price'] = $row['discounted_price'];
        $taxPercentageData = fetch_details('taxes', ['id' => $row['tax_id']], ['percentage']);
        if (!empty($taxPercentageData)) {
            $taxPercentage = $taxPercentageData[0]['percentage'];
        } else {
            $taxPercentage = 0;
        }
        $tempRow['servic_details']['id'] = $row['id'];
        $tempRow['servic_details']['partner_id'] = $row['user_id'];
        $tempRow['servic_details']['category_id'] = $row['category_id'];
        $tempRow['servic_details']['category_name'] = $row['category_name'];
        $tempRow['servic_details']['partner_name'] = $row['partner_name'];
        $tempRow['servic_details']['tax_type'] = $row['tax_type'];
        $tempRow['servic_details']['tax_id'] = $row['tax_id'];
        $tempRow['servic_details']['current_tax_percentage'] = $taxPercentage;
        $tempRow['servic_details']['tax'] = $row['tax'];
        $tempRow['servic_details']['title'] = $row['title'];
        $tempRow['servic_details']['slug'] = $row['slug'];
        $tempRow['servic_details']['description'] = $row['description'];
        $tempRow['servic_details']['tags'] = $row['tags'];
        $tempRow['servic_details']['image_of_the_service'] = $images;
        $tempRow['servic_details']['price'] = $row['price'];
        $tempRow['servic_details']['discounted_price'] = $row['discounted_price'];
        $tempRow['servic_details']['number_of_members_required'] = $row['number_of_members_required'];
        $tempRow['servic_details']['duration'] = $row['duration'];
        $tempRow['servic_details']['tags'] = json_decode((string) $row['tags'], true);
        $tempRow['servic_details']['rating'] = $rating;
        $tempRow['servic_details']['number_of_ratings'] = $row['number_of_ratings'];
        $tempRow['servic_details']['on_site_allowed'] = $site_allowed;
        $tempRow['servic_details']['max_quantity_allowed'] = $row['max_quantity_allowed'];
        $tempRow['servic_details']['is_pay_later_allowed'] = $pay_later;
        $tempRow['servic_details']['status'] = $status;
        $tempRow['servic_details']['created_at'] = $row['created_at'];
        if ($row['discounted_price'] == "0") {
            if ($row['tax_type'] == "excluded") {
                $tempRow['servic_details']['price_with_tax'] = strval(str_replace(',', '', number_format(strval($row['price'] + ($row['price'] * ($taxPercentage) / 100)), 2)));
                $tempRow['tax_value'] = number_format((intval(($row['price'] * ($taxPercentage) / 100))), 2);
                $tempRow['servic_details']['original_price_with_tax'] = strval(str_replace(',', '', number_format(strval($row['price'] + ($row['price'] * ($taxPercentage) / 100)), 2)));
            } else {
                $tempRow['servic_details']['price_with_tax'] = strval(str_replace(',', '', number_format(strval($row['price']), 2)));
                $tempRow['tax_value'] = "";
                $tempRow['servic_details']['original_price_with_tax'] = strval(str_replace(',', '', number_format(strval($row['price']), 2)));
            }
        } else {
            if ($row['tax_type'] == "excluded") {
                $tempRow['servic_details']['price_with_tax'] = strval(str_replace(',', '', number_format(strval($row['discounted_price'] + ($row['discounted_price'] * ($taxPercentage) / 100)), 2)));
                $tempRow['tax_value'] = number_format((intval(($row['discounted_price'] * ($taxPercentage) / 100))), 2);
                $tempRow['servic_details']['original_price_with_tax'] = strval(str_replace(',', '', number_format(strval($row['price'] + ($row['price'] * ($taxPercentage) / 100)), 2)));
            } else {
                $tempRow['servic_details']['price_with_tax'] = $row['discounted_price'];
                $tempRow['tax_value'] = "";
                $tempRow['servic_details']['original_price_with_tax'] = strval(str_replace(',', '', number_format(strval($row['price']), 2)));
            }
        }
        $rows[] = $tempRow;
    }
    if ($from_app) {
        $db      = \Config\Database::connect();
        $cart_builder = $db->table('cart');

        foreach ($service_record as $key => $s) {
            $detail = fetch_details('services', ['id' => $s['service_id']], ['id', 'user_id', 'approved_by_admin', 'at_store', 'at_doorstep'])[0];
            $p_detail = fetch_details('partner_details', ['partner_id' => $s['user_id']], ['id', 'at_store', 'at_doorstep', 'need_approval_for_the_service'])[0];
            if (($detail['at_store'] !=  $p_detail['at_store']) && ($detail['at_doorstep'] || $detail['at_doorstep'])) {
                unset($service_record[$key]);
                $cart_builder->delete(['service_id' => $detail['id']]);
            }
            $is_already_subscribe = fetch_details('partner_subscriptions', ['partner_id' => $detail['user_id'], 'status' => 'active']);
            if ($p_detail['need_approval_for_the_service'] == 1) {
                if ($detail['approved_by_admin'] != 1  || empty($is_already_subscribe)) {
                    unset($service_record[$key]);
                    $cart_builder->delete(['service_id' => $detail['id']]);
                }
            }
        }



        if (!empty($service_record)) {
            if (($reorder) == 'yes' && !empty($order_id)) {
                $builder = $db->table('order_services os');
                $order_record = $builder
                    ->select('os.id, os.service_id, os.quantity as qty')
                    ->join('orders o', 'o.id=os.order_id', 'left')
                    ->where('o.user_id', $user_id)
                    ->where('os.order_id', $order_id)
                    ->orderBy($sort, $order)
                    ->limit($limit, $offset)
                    ->get()
                    ->getResultArray();
                foreach ($order_record as $row) {
                    $array_ids[] = [
                        'service_id' => $row['service_id'],
                        'qty' => $row['qty'],
                    ];
                }
            } else {
                $array_ids = fetch_details('cart c', ['user_id' => $user_id], 'service_id,qty');
            }
            $s = [];
            $q = [];
            foreach ($array_ids as $ids) {
                array_push($s, $ids['service_id']);
                array_push($q, $ids['qty']);
            }
            $id = implode(',', $s);
            $qty = implode(',', $q);
            $builder = $db->table('services s');
            if (($reorder) == 'yes' && !empty($order_id)) {
                $builder = $db->table('order_services os');
                $extra_data = $builder
                    ->select('SUM(IF(s.discounted_price  > 0 , (s.discounted_price * os.quantity) , (s.price * os.quantity))) as subtotal,
                SUM(os.quantity) as total_quantity,pd.visiting_charges as visiting_charges,SUM(s.duration * os.quantity) as total_duration,pd.at_store,pd.at_doorstep,pd.advance_booking_days as advance_booking_days,pd.company_name as company_name')
                    ->join('services s', 'os.service_id=s.id', 'left')
                    ->join('partner_details pd', 'pd.partner_id=s.user_id')
                    ->where('os.order_id', $order_id)
                    ->whereIn('s.id', $s)->get()->getResultArray();
            } else {
                $builder = $db->table('services s');
                $extra_data = $builder
                    ->select('SUM(IF(s.discounted_price  > 0 , (s.discounted_price * c.qty) , (s.price * c.qty))) as subtotal,
               SUM(c.qty) as total_quantity,pd.visiting_charges as visiting_charges,SUM(s.duration * c.qty) as total_duration,pd.at_store,pd.at_doorstep,pd.advance_booking_days as advance_booking_days,pd.company_name as company_name')
                    ->join('cart c', 'c.service_id = s.id')
                    ->join('partner_details pd', 'pd.partner_id=s.user_id')
                    ->where('c.user_id', $user_id)
                    ->whereIn('s.id', $s)->get()->getResultArray();
            }
            $tax_value = 0;
            $sub_total = 0;
            foreach ($service_record as $s1) {
                $taxPercentageData = fetch_details('taxes', ['id' => $s1['tax_id']], ['percentage']);
                if (!empty($taxPercentageData)) {
                    $taxPercentage = $taxPercentageData[0]['percentage'];
                } else {
                    $taxPercentage = 0;
                }
                if ($s1['discounted_price'] == "0") {
                    $tax_value = ($s1['tax_type'] == "excluded") ? number_format(((($s1['price'] * ($taxPercentage) / 100))), 2) : 0;
                    $price = number_format($s1['price'], 2);
                } else {
                    $tax_value = ($s1['tax_type'] == "excluded") ? number_format(((($s1['discounted_price'] * ($taxPercentage) / 100))), 2) : 0;
                    $price = number_format($s1['discounted_price'], 2);
                }
                $sub_total = $sub_total + (floatval(str_replace(",", "", $price)) + floatval(str_replace(",", "", $tax_value))) * $s1['qty'];
            }
            $data['total'] = (empty($total)) ? (string) count($rows) : $total;
            $data['advance_booking_days'] = isset($extra_data[0]['advance_booking_days']) ? $extra_data[0]['advance_booking_days'] : "";
            $data['visiting_charges'] = $extra_data[0]['visiting_charges'];
            $data['company_name'] = isset($extra_data[0]['company_name']) ? $extra_data[0]['company_name'] : "";
            $data['at_store'] = isset($extra_data[0]['at_store']) ? $extra_data[0]['at_store'] : "0";
            $data['at_doorstep'] = isset($extra_data[0]['at_doorstep']) ? $extra_data[0]['at_doorstep'] : "0";
            $data['service_ids'] = $id;
            $data['qtys'] = isset($qty) ? $qty : 0;
            $data['total_quantity'] = $extra_data[0]['total_quantity'];
            $data['total_duration'] = $extra_data[0]['total_duration'];
            $data['sub_total'] = strval(str_replace(',', '', number_format(strval($sub_total), 2)));
            $data['overall_amount'] = strval(str_replace(',', '', number_format(strval($sub_total + $data['visiting_charges']), 2)));
            $data['data'] = $rows;
            $provider_data = $db->table('services s');
            $providers = $provider_data
                ->select('u.username as provider_names, u.id as provider_id')
                ->join('users u', 'u.id = s.user_id')
                ->whereIn('s.id', $s)->get()->getResultArray();
            $pds = [];
            $pid = [];
            foreach ($providers as $provider) {
                array_push($pds, $provider['provider_names']);
                array_push($pid, $provider['provider_id']);
            }
            $unique_name = array_unique($pds);
            $unique_id = array_unique($pid);
            $names = implode(',', $unique_name);
            $ids = implode(',', $unique_id);
            $data['provider_names'] = $names;
            $data['provider_id'] = $ids;
            $pay_later_array = [];
            foreach ($service_record as $service_row) {
                array_push($pay_later_array, $service_row['is_pay_later_allowed']);
            }
            $active_partner_subscription = fetch_details('partner_subscriptions', ['partner_id' => $providers[0]['provider_id'], 'status' => 'active']);
            $provider_details = fetch_details('users', ['id' => $providers[0]['provider_id']]);
            if (!empty($active_partner_subscription)) {
                if ($active_partner_subscription[0]['is_commision'] == "yes") {
                    $commission_threshold = $active_partner_subscription[0]['commission_threshold'];
                } else {
                    $commission_threshold = 0;
                }
            } else {
                $commission_threshold = 0;
            }
            $check_payment_gateway = get_settings('payment_gateways_settings', true);
            $data['is_online_payment_allowed'] =  $check_payment_gateway['payment_gateway_setting'];
            if ($check_payment_gateway['cod_setting'] == 1 && $check_payment_gateway['payment_gateway_setting'] == 0) {
                $data['is_pay_later_allowed'] = 1;
            } else if ($check_payment_gateway['cod_setting'] == 0) {
                $data['is_pay_later_allowed'] = 0;
            } else {
                $payable_commission_of_provider = $provider_details[0]['payable_commision'];
                if (($payable_commission_of_provider >= $commission_threshold) && $commission_threshold != 0) {
                    $data['is_pay_later_allowed'] = 0;
                } else {
                    if (in_array(0, $pay_later_array)) {
                        $data['is_pay_later_allowed'] = 0;
                    } else {
                        $data['is_pay_later_allowed'] = 1;
                    }
                }
            }
            return $data;
        } else {
            $data = [];
            return $data;
        }
    } else {
        $bulkData['rows'] = $rows;
        return json_encode($bulkData);
    }
}
function get_taxable_amount($service_id)
{
    $service_details = fetch_details('services', ['id' => $service_id])[0];
    if ($service_details['tax_id'] != 0) {
        $tax_details = fetch_details('taxes', ['id' => $service_details['tax_id']])[0];
        $tax_percentage = strval(str_replace(',', '', number_format(strval($tax_details['percentage']), 2)));
    } else {
        $tax_percentage = 0;
    }
    $taxable_amount = 0;
    if ($service_details['tax_type'] == "excluded") {
        if ($service_details['discounted_price'] == 0) {
            $tax_amount = (!empty($tax_percentage)) ? ($service_details['price'] * $tax_percentage) / 100 : 0;
            $taxable_amount = strval(str_replace(',', '', number_format(strval($service_details['price'] + ($tax_amount)), 2)));
        } else {
            $tax_amount = (!empty($tax_percentage)) ? ($service_details['discounted_price'] * $tax_percentage) / 100 : 0;
            $taxable_amount = strval(str_replace(',', '', number_format(strval($service_details['discounted_price'] + ($tax_amount)), 2)));
        }
    } else {
        if ($service_details['discounted_price'] == 0) {
            $tax_amount = (!empty($tax_percentage)) ? ($service_details['price'] * $tax_percentage) / 100 : 0;
            $taxable_amount = strval(str_replace(',', '', number_format(strval($service_details['price']), 2)));
        } else {
            $tax_amount = (!empty($tax_percentage)) ? ($service_details['discounted_price'] * $tax_percentage) / 100 : 0;
            $taxable_amount = strval(str_replace(',', '', number_format(strval($service_details['discounted_price']), 2)));
        }
    }
    $result = [
        'title' => $service_details['title'],
        'tax_percentage' => $tax_percentage,
        'tax_amount' => $tax_amount,
        'price' => $service_details['price'],
        'discounted_price' => $service_details['discounted_price'],
        'taxable_amount' => $taxable_amount ?? 0,
    ];
    return $result;
}
function get_partner_ids(string $type = '', string $column_name = 'id', array $ids = [], $is_array = false, array $fields_name = ['*'])
{
    $db = \Config\Database::connect();
    if ($type == 'service') {
        $builder = $db->table('services s');
        $partners = $builder->select('s.user_id as id')
            ->whereIn('s.' . $column_name, $ids)
            ->get()->getResultArray();
    } else if ($type == 'category') {
        $builder = $db->table('services s');
        $partners = $builder->select('s.user_id as id')
            ->whereIN('s.' . $column_name, $ids)
            ->get()->getResultArray();
    } else {
        $builder = $db->table('users u');
        $partners = $builder->select($fields_name)
            ->join('users_groups ug', 'ug.user_id=u.id')
            ->where('ug.group_id', '3')
            ->whereIn($column_name, $ids)
            ->get()->getResultArray();
    }
    $ids = [];
    foreach ($partners as $key => $parnter) {
        $ids[] = $parnter['id'];
    }
    $ids = array_unique($ids);
    if ($is_array == false) {
        $ids = implode(',', $ids);
    }
    return $ids;
}
function check_partner_availibility(int $partner_id)
{
    $days = [
        'Mon' => 'monday',
        'Tue' => 'tuesday',
        'Wed' => 'wednsday',
        'Thu' => 'thursday',
        'Fri' => 'friday',
        'Sat' => 'staturday',
        'Sun' => 'sunday',
    ];
    $partner_timing = fetch_details('partner_timings', ['partner_id' => $partner_id, 'day' => $days[date('D')]]);
    if (empty($partner_timing)) {
        return false;
    }
    $partner_timing = $partner_timing[0];
    $time = new DateTime($partner_timing['opening_time']);
    $opening_time = $time->format('H:i');
    $time = new DateTime($partner_timing['closing_time']);
    $closing_time = $time->format('H:i');
    $current_time = date('H:i');
    if (($opening_time <= $current_time) or ($current_time >= $closing_time)) {
        return $partner_timing;
    } else {
        return false;
    }
}
function get_time_slot()
{
    $days = [
        'Mon' => 'monday',
        'Tue' => 'tuesday',
        'Wed' => 'wednsday',
        'Thu' => 'thursday',
        'Fri' => 'friday',
        'Sat' => 'staturday',
        'Sun' => 'sunday',
    ];
    $service_id = 16;
    $partner_id = 50;
    $start_times = "5:00";
    $end_time = "6:00";
    $qty = 2;
    $date = date('Y-m-d');
    $day = $days[date('D', strtotime($date))];
    $partner_timing = fetch_details('partner_timings', ['partner_id' => $partner_id, 'day' => $day]);
    $service_details = fetch_details('services', ['id' => $service_id]);
    $service_duration = $service_details[0]['duration'];
    $parnter_opening_time = $partner_timing[0]['opening_time'];
    $parnter_closing_time = $partner_timing[0]['closing_time'];
    $time1 = strtotime($parnter_opening_time);
    $time2 = strtotime($parnter_closing_time);
    $total_hours = round(abs($time2 - $time1) / 3600, 2);
    $time_slotes = [];
    $increament_time = $service_duration;
    $slote_start_time = $parnter_opening_time;
    $i = 0;
    do {
        $slot_name = "time_slot_" . $i;
        $slote_end_time = date('H:i:s', strtotime('+' . $increament_time . ' minutes', strtotime($parnter_opening_time)));
        $time_slotes[$slot_name] = [
            'start_time' => date('H:i:s', strtotime($slote_start_time)),
            'end_time' => $slote_end_time,
        ];
        $increament_time += $service_duration;
        $slote_start_time = $slote_end_time;
        $i++;
    } while ($slote_end_time != $parnter_closing_time);
    return $time_slotes;
}
function check_partner_type($partner_id)
{
    $data = fetch_details('partner_details', ['partner_id' => $partner_id]);
    if (isset($data[0]['type']) && $data[0]['type'] == '1') {
        return 'organization';
    } else {
        return 'single';
    }
}
function check_available_employee($partner_id)
{
    $db = \Config\Database::connect();
    $data = $db->table('orders o')
        ->select('COUNT(o.id) AS order_count,
                    SUM(os.quantity) AS quantity,
                    (COUNT(o.id) * SUM(os.quantity)) AS order_members,
                    pd.number_of_members,
                    (pd.number_of_members -(COUNT(o.id) * SUM(os.quantity))) AS available_members')
        ->join('partner_details pd', 'pd.partner_id = o.partner_id', 'left')
        ->join('order_services os', 'o.id = os.order_id', 'left')
        ->where("o.partner_id = $partner_id AND o.status IN('confirmed', 'rescheduled')")
        ->get()->getResultArray();
    $type = check_partner_type($partner_id);
    if (!empty($type) && $type == 'organization' && !empty($data[0]['order_count']) && $data[0]['available_members'] != 0) {
        $response['error'] = false;
        $response['message'] = "Partner is available";
        $response['data'] = $data;
    } else {
        $response['error'] = true;
        $response['message'] = "Partner is not available";
        $response['data'] = $data;
    }
    return $response;
}
function is_bookmarked($user_id, $partner_id)
{
    $db = \Config\Database::connect();
    $builder = $db->table('bookmarks');
    $data = $builder
        ->select('COUNT(id) as total')
        ->where('user_id', $user_id)
        ->where('partner_id', $partner_id)->get()->getResultArray();
    return $data;
}
function delete_bookmark($user_id, $partner_id)
{
    $db = \Config\Database::connect();
    $builder = $db->table('bookmarks');
    $data = $builder->where(['user_id' => $user_id, 'partner_id' => $partner_id])
        ->delete();
    if ($data) {
        return true;
    } else {
        return false;
    }
}
function send_customer_web_notification($fcmMsg, $registrationIDs_chunks)
{
    $access_token = getAccessToken();
    $settings = fetch_details('settings', ['variable' => 'firebase_settings'])[0];
    $settings = $settings['value'];
    $settings = json_decode($settings, true);
    $url = 'https://fcm.googleapis.com/v1/projects/' . $settings['projectId'] . '/messages:send';
    foreach ($registrationIDs_chunks as $registrationIDs) {
        $message1 = [
            "message" => [
                "token" => $registrationIDs['web_fcm_id'],
                "data" => $fcmMsg
            ]
        ];
        $data1 = json_encode($message1);
        sendNotificationToFCM($url, $access_token, $data1);
    }
}
function send_notification($fcmMsg, $registrationIDs_chunks)
{
    $access_token = getAccessToken();
    $settings = fetch_details('settings', ['variable' => 'firebase_settings'])[0];
    $settings = $settings['value'];
    $settings = json_decode($settings, true);
    $message1 = [];
    $url = 'https://fcm.googleapis.com/v1/projects/' . $settings['projectId'] . '/messages:send';
    foreach ($registrationIDs_chunks[0] as $registrationIDs) {
        if ($registrationIDs['platform'] == "android") {
            $message1 = [
                "message" => [
                    "token" => $registrationIDs['fcm_id'],
                    "data" => $fcmMsg
                ]
            ];
            $data1 = json_encode($message1);
            sendNotificationToFCM($url, $access_token, $data1);
        } elseif ($registrationIDs['platform'] == "ios") {
            // $message1 = [
            //     "message" => [
            //         "token" => ($registrationIDs['fcm_id']),
            //         "data" => $fcmMsg,
            //         "notification" => array(
            //             "title" => $fcmMsg["title"],
            //             "body" => $fcmMsg["body"],
            //             "mutable_content" => true,
            //             "sound" => $fcmMsg["type"] == "order" || $fcmMsg["type"] == "new_order" ?  "order_sound.aiff" : "default"
            //         )
            //     ]
            // ];
            $message1 = [
                "message" => [
                    "token" => $registrationIDs['fcm_id'],
                    "notification" => [
                        "title" => $fcmMsg["title"],
                        "body" => $fcmMsg["body"]
                    ],
                    "apns" => [
                        "payload" => [
                            "aps" => [
                                "mutable-content" => 1,
                                "sound" => $fcmMsg["type"] == "order" || $fcmMsg["type"] == "new_order" ? "order_sound.aiff" : "default"
                            ]
                        ]
                    ],
                    "data" => $fcmMsg // Optional: Only if you need additional data
                ]
            ];

            $data1 = json_encode($message1);
            sendNotificationToFCM($url, $access_token, $data1);
        }
    }
}
function get_permission($user_id)
{
    $db = \Config\Database::connect();
    $builder = $db->table('user_permissions');
    $builder->select('role,permissions');
    $builder->where('user_id', $user_id);
    $permissions = $builder->get()->getResultArray();
    if (!empty($permissions[0]['permissions'])) {
        $permissions = json_decode($permissions[0]['permissions'], true);
    } else {
        $permissions = [
            'create' => [
                'order' => 0,
                'subscription' => 1,
                'categories' => 1,
                'sliders' => 1,
                'tax' => 1,
                'services' => 1,
                'promo_code' => 1,
                'featured_section' => 1,
                'partner' => 1,
                'customers' => 0,
                'send_notification' => 1,
                'faq' => 1,
                'settings' => 1,
                'system_user' => 1,
            ],
            'read' => [
                'orders' => 1,
                'subscription' => 1,
                'categories' => 1,
                'sliders' => 1,
                'tax' => 1,
                'services' => 1,
                'promo_code' => 1,
                'featured_section' => 1,
                'partner' => 1,
                'customers' => 1,
                'send_notification' => 1,
                'faq' => 1,
                'settings' => 1,
                'system_user' => 1,
            ],
            'update' => [
                'orders' => 1,
                'subscription' => 1,
                'categories' => 1,
                'sliders' => 1,
                'tax' => 1,
                'services' => 1,
                'promo_code' => 1,
                'featured_section' => 1,
                'partner' => 1,
                'customers' => 1,
                'city' => 1,
                'system_update' => 1,
                'settings' => 1,
                'system_user' => 1,
            ],
            'delete' => [
                'orders' => 1,
                'subscription' => 1,
                'categories' => 1,
                'offers' => 1,
                'sliders' => 1,
                'tax' => 1,
                'services' => 1,
                'promo_code' => 1,
                'featured_section' => 1,
                'partner' => 1,
                'customers' => 0, // Note: I added a default value here, adjust as needed
                'city' => 1,
                'faq' => 1,
                'send_notification' => 1,
                'support_tickets' => 1,
                'system_user' => 1,
            ],
        ];
    }
    return $permissions;
}
function is_permitted($user_id, $type_of_permission, $permit)
{
    $db = \Config\Database::connect();
    $builder = $db->table('user_permissions');
    $builder->select('role,permissions');
    $builder->where('user_id', $user_id);
    $permissions = $builder->get()->getResultArray();
    if ($permissions[0]['role'] == "1") {
        return true;
    } else {
        $permissions = json_decode($permissions[0]['permissions'], true);
        foreach ($permissions as $key => $val) {
            if ($key == $type_of_permission) {
                if ($val[$permit] == "yes" || $val[$permit] == "1" || $val[$permit] == 1) {
                    return true;
                } else {
                    return false;
                }
            }
        }
    }
}
function booked_timings($partner_id, $date_of_service)
{
    $db = \config\Database::connect();
    $table = $db->table('orders o');
    $day = date('l', strtotime($date_of_service));
    $response = $table->select('o.starting_time,o.ending_time')
        ->join('order_services os', 'o.id = os.order_id', 'left')
        ->join('services s', 'os.service_id = s.id', 'left')
        ->join('partner_timings pt', 'pt.partner_id = o.partner_id')
        ->where(['o.partner_id' => $partner_id, 'o.date_of_service' => $date_of_service, 'pt.day' => $day, 'pt.is_open' => '1'])
        ->whereIn('o.status', ['confirmed', 'rescheduled', 'awaiting'])
        ->groupBy('o.id')
        ->orderBy('o.starting_time')
        ->get()->getResultArray();
    return $response;
}
function check_availability($partner_id, $booking_date, $time)
{
    $today = date('Y-m-d');
    if ($booking_date < $today) {
        $response['error'] = true;
        $response['message'] = "please select upcoming date!";
        return $response;
    }
    $db = \config\Database::connect();
    $table = $db->table('orders a');
    $day = date('l', strtotime($booking_date));
    $timings = getTimingOfDay($partner_id, $day);
    if (isset($timings) && !empty($timings)) {
        $opening_time = $timings['opening_time'];
        $closing_time = $timings['closing_time'];
        $booked_slots = $table->select('a.starting_time AS free_before, (a.starting_time + INTERVAL a.duration HOUR_MINUTE) AS free_after')
            ->where("NOT EXISTS (
            SELECT 1
            FROM orders b
            WHERE b.starting_time BETWEEN (a.starting_time + INTERVAL a.duration HOUR_MINUTE)
                AND (a.starting_time + INTERVAL a.duration HOUR_MINUTE) + INTERVAL 15 SECOND - INTERVAL 1 MICROSECOND
        )")
            ->where("(a.starting_time + INTERVAL a.duration HOUR_MINUTE) BETWEEN '$booking_date $opening_time' AND '$booking_date $closing_time'")
            ->where('date_of_service', $booking_date)
            ->whereIn('status', ['awaiting', 'pending', 'confirmed', 'rescheduled'])
            ->where('partner_id', '50')
            ->groupBy('id')
            ->orderBy('starting_time', 'ASC')
            ->get()
            ->getResultArray();
        if (isset($booked_slots) && !empty($booked_slots)) {
            if ($time >= $opening_time && $time < $closing_time) {
                foreach ($booked_slots as $key => $val) {
                    $from = strtotime($val['free_before']);
                    $till = strtotime($val['free_after']);
                    $t = isBetween($from, $till, strtotime($time));
                    if (isset($t) && $t == true) {
                        $response['error'] = true;
                        $response['message'] = "provider is busy at this time select another slot";
                    } else {
                        if ($time >= $closing_time) {
                            $response['error'] = true;
                            $response['message'] = "Provider is closed at this time";
                        } else {
                            $response['error'] = false;
                            $response['message'] = "slot is available at this time";
                        }
                    }
                }
                return $response;
            } else {
                $response['error'] = true;
                $response['message'] = "Provider is closed at this time";
                return $response;
            }
        } else {
            $response['error'] = true;
            $response['message'] = "Provider is closed at this time";
            return $response;
        }
    } else {
        $response['error'] = true;
        $response['message'] = "provider is closed on this day";
        return $response;
    }
}
function isBetween($from, $till, $input)
{
    if ($input >= $from && $input <= $till) {
        return true;
    } else {
        return false;
    }
}
function getTimingOfDay($partner_id, $day)
{
    $timings = fetch_details('partner_timings', ['partner_id' => $partner_id, 'day' => $day], ['opening_time', 'closing_time', 'is_open']);
    if (!empty($timings) && isset($timings[0]) && $timings[0]['is_open'] == '1') {
        return $timings[0];
    } else {
        return false;
    }
}
function get_available_slots($partner_id, $booking_date, $required_duration = null, $next_day_order = null)
{
    $timezone = get_settings('general_settings', true);
    date_default_timezone_set($timezone['system_timezone']); // Added user timezone
    if (!empty($next_day_order)) {
        $today = date('Y-m-d');
        if ($booking_date < $today) {
            $response['error'] = true;
            $response['message'] = "please select upcoming date!";
            return $response;
        }
        $db = \config\Database::connect();
        $day = date('l', strtotime($booking_date));
        $timings = getTimingOfDay($partner_id, $day);
        if (isset($timings) && !empty($timings)) {
            $opening_time = $timings['opening_time'];
            $closing_time = $timings['closing_time'];
            $booked_slots = booked_timings($partner_id, $booking_date);
            $interval = 30 * 60;
            $start_time = strtotime($next_day_order);
            $current_time = time();
            $end_time = strtotime($closing_time);
            $count = count($booked_slots);
            $current_date = date('Y-m-d');
            $available_slots = [];
            $busy_slots = [];
            //if booked slot is not empty means that day no odrer no found
            while ($start_time < $end_time) {
                $array_of_time[] = date("H:i:s", $start_time);
                $start_time += $interval;
            }
            if (isset($booked_slots) && !empty($booked_slots)) {
                //here suggested time is created in gap of 30 minutes
                $count_suggestion_slots = count($array_of_time);
                //loop on total booked slots
                for ($i = 0; $i < $count; $i++) {
                    //loop on suggested time slots
                    for ($j = 0; $j < $count_suggestion_slots; $j++) {
                        //if suggested time slot is less than booked slot starting time or suggested time slot is greater than booked time slot starting time
                        if (strtotime($array_of_time[$j]) < strtotime($booked_slots[$i]['starting_time']) || strtotime($array_of_time[$j]) >= strtotime($booked_slots[$i]['ending_time'])) {
                            //check if suggested time slot is not  in array of avaialble slot
                            if (!in_array($array_of_time[$j], $available_slots)) {
                                //if suggested time slot is grater than current time and current date and booked date are not same then to available slot array otherwise busy slot array
                                if (strtotime($array_of_time[$j]) > $current_time || strtotime($booking_date) != strtotime($current_date)) {
                                    // echo $array_of_time[$j]." added to available time slot <br/>";
                                    $available_slots[] = $array_of_time[$j];
                                } else {
                                    // echo $array_of_time[$j]." added to busy time slot11<br/>";
                                    if (!in_array($array_of_time[$j], $busy_slots)) {
                                        $busy_slots[] = $array_of_time[$j];
                                    }
                                }
                                // die;
                            } else {
                            }
                        } else {
                            //  echo $array_of_time[$j]." added to busy time slot22<br/>";
                            if (!in_array($array_of_time[$j], $busy_slots)) {
                                $busy_slots[] = $array_of_time[$j];
                            }
                        }
                    }
                    $count_busy_slots = count($busy_slots);
                    for ($k = 0; $k < $count_busy_slots; $k++) {
                        if (($key = array_search($busy_slots[$k], $available_slots)) !== false) {
                            unset($available_slots[$key]);
                        }
                    }
                }
                $available_slots = array_values($available_slots);
                $ignore_last_slot = false;
                $all_continous_slot = calculate_continuous_slots($available_slots);
                $next_day_slots = get_next_days_slots($closing_time, $booking_date, $partner_id, $required_duration, $current_date);
                // if(!empty($next_day_available_slots)){
                $next_day_available_slots = $next_day_slots['continous_available_slots'];
                $required_slots = ceil($required_duration / 30);
                if (isset($next_day_available_slots[0][0]) && $next_day_available_slots[0][0] === $opening_time) {
                    // echo "if1";
                    $next_day_fullfilled_slots = count($next_day_available_slots[0]);
                    if ($next_day_fullfilled_slots >= $required_slots) {
                        // echo "if2";
                        $ignore_last_slot = true;
                        $required_duration_for_last_slot = $next_day_fullfilled_slots * 30;
                    } else {
                        // echo "else";
                        $expected_remaining_duration_for_today = $required_duration - ($next_day_fullfilled_slots * 30);
                        // echo $expected_remaining_duration_for_today."<br>";
                        $last_contious_slot_of_current_day = $all_continous_slot[count($all_continous_slot) - 1];
                        // print_R($last_contious_slot_of_current_day);
                        $last_element_of_current_day = $last_contious_slot_of_current_day[count($last_contious_slot_of_current_day) - 1];
                        $last_element_of_current_day = date("H:i:s", strtotime('+30 minutes', strtotime($last_element_of_current_day)));
                        if ($last_element_of_current_day == $closing_time) {
                            // echo "if3";
                            $required_duration_for_last_slot = count($last_contious_slot_of_current_day) * 30;
                            if ($expected_remaining_duration_for_today < $required_duration_for_last_slot) {
                                // echo "if5";
                                $ignore_last_slot = true;
                            }
                        } else {
                            // echo "else2";
                            //Don't do anything here
                        }
                    }
                } else {
                    // echo "else3";
                    //Don't do anything here as the next function will handle the last available slot and all
                }
                //Disable all the chunks that are not required enough
                $continous_slot_doration = 0; // Initialize the variable before the loop
                foreach ($all_continous_slot as $index => $row) {
                    $ignore_last_slot_local = false;
                    if ($index === (count($all_continous_slot) - 1)) {
                        $ignore_last_slot_local = ($ignore_last_slot == false) ? false : true;
                    }
                    if ($ignore_last_slot_local) {
                        $continous_slot_doration = sizeof($row) * 30;
                        if ($continous_slot_doration < $required_duration) {
                            foreach ($row as $child_slots) {
                                if (($key = array_search($child_slots, $available_slots)) !== false) {
                                    unset($available_slots[$key]);
                                    $busy_slots[] = $child_slots;
                                }
                            }
                        }
                    }
                }
                $available_slots = array_values($available_slots);
                $all_continous_slot = calculate_continuous_slots($available_slots);
                $required_slots = ceil($required_duration / 30);
                foreach ($all_continous_slot as $index => $row) {
                    if ($index == count($all_continous_slot) - 1 && $ignore_last_slot == true) {
                        $required_slots = $required_slots - $next_day_fullfilled_slots + 1;
                    }
                    $last_available_slot  = (count($row) - $required_slots) + 1;
                    for ($i = count($row) - 1; $i > $last_available_slot; $i--) {
                        if ($i >= 0 && (($key = array_search($row[$i], $available_slots)) !== false)) {
                            unset($available_slots[$key]);
                            $busy_slots[] = $row[$i];
                        }
                    }
                }
                //---------------------------------  START ----------------------------------------------------------
                // Fetch order data from the database for the requested partner
                $builder = $db->table('orders');
                $builder->select('starting_time, ending_time, date_of_service');
                $builder->where('partner_id', $partner_id);
                $builder->where('date_of_service', $booking_date);
                $builder->whereIn('status', ['awaiting', 'pending', 'confirmed', 'rescheduled']);
                $booked_slots = $builder->get()->getResultArray();
                $duration = $required_duration; // Duration of each service in minutes
                foreach ($available_slots as $slot) {
                    $slot_time = strtotime($slot);
                    $slot_end_time = strtotime("+$duration minutes", $slot_time);
                    $is_booked = false;
                    foreach ($booked_slots as $booked_slot) {
                        $booked_start_time = strtotime($booked_slot['starting_time']);
                        $booked_end_time = strtotime($booked_slot['ending_time']);
                        if (($slot_time >= $booked_start_time && $slot_time < $booked_end_time) ||
                            ($slot_end_time > $booked_start_time && $slot_end_time <= $booked_end_time)
                        ) {
                            $is_booked = true;
                            break;
                        }
                    }
                    if ($is_booked) {
                        $busy_slots[] = $slot;
                        $index = array_search($slot, $available_slots);
                        if ($index !== false) {
                            unset($available_slots[$index]);
                        }
                    }
                }
                // //------------------------------------------------------- END------------------------------------------------------------------
                $response['error'] = false;
                $response['available_slots'] = $available_slots;
                $response['busy_slots'] = $busy_slots;
                return $response;
            } else {
                if (strtotime($booking_date) == strtotime($current_date)) {
                    foreach ($array_of_time as $row) {
                        if (strtotime($row) < $current_time) {
                            if (($key = array_search($row, $array_of_time)) !== false) {
                                unset($array_of_time[$key]);
                                $busy_slots[] = $row;
                            }
                        }
                    }
                }
                //here to continue the index of available_slots
                $array_of_time = array_values($array_of_time);
                $ignore_last_slot = false;
                $all_continous_slot = calculate_continuous_slots($array_of_time);
                $next_day_slots = get_next_days_slots($closing_time, $booking_date, $partner_id, $required_duration, $current_date);
                // if(!empty($next_day_available_slots)){
                $next_day_available_slots = $next_day_slots['continous_available_slots'];
                $required_slots = ceil($required_duration / 30);
                if (isset($next_day_available_slots[0][0]) && $next_day_available_slots[0][0] === $opening_time) {
                    // echo "if1";
                    $next_day_fullfilled_slots = count($next_day_available_slots[0]);
                    if ($next_day_fullfilled_slots >= $required_slots) {
                        // echo "if2";
                        $ignore_last_slot = true;
                        $required_duration_for_last_slot = $next_day_fullfilled_slots * 30;
                    } else {
                        // echo "else";
                        $expected_remaining_duration_for_today = $required_duration - ($next_day_fullfilled_slots * 30);
                        // echo $expected_remaining_duration_for_today."<br>";
                        $last_contious_slot_of_current_day = $all_continous_slot[count($all_continous_slot) - 1];
                        // print_R($last_contious_slot_of_current_day);
                        $last_element_of_current_day = $last_contious_slot_of_current_day[count($last_contious_slot_of_current_day) - 1];
                        $last_element_of_current_day = date("H:i:s", strtotime('+30 minutes', strtotime($last_element_of_current_day)));
                        if ($last_element_of_current_day == $closing_time) {
                            // echo "if3";
                            $required_duration_for_last_slot = count($last_contious_slot_of_current_day) * 30;
                            if ($expected_remaining_duration_for_today < $required_duration_for_last_slot) {
                                // echo "if5";
                                $ignore_last_slot = true;
                            }
                        } else {
                            // echo "else2";
                            //Don't do anything here
                        }
                    }
                } else {
                    // echo "else3";
                    //Don't do anything here as the next function will handle the last available slot and all
                }
                //Disable all the chunks that are not required enough
                $continous_slot_doration = 0; // Initialize the variable before the loop
                foreach ($all_continous_slot as $index => $row) {
                    $ignore_last_slot_local = false;
                    if ($index === (count($all_continous_slot) - 1)) {
                        $ignore_last_slot_local = ($ignore_last_slot == false) ? false : true;
                    }
                    if ($ignore_last_slot_local) {
                        $continous_slot_doration = sizeof($row) * 30;
                        if ($continous_slot_doration < $required_duration) {
                            foreach ($row as $child_slots) {
                                if (($key = array_search($child_slots, $array_of_time)) !== false) {
                                    unset($array_of_time[$key]);
                                    $busy_slots[] = $child_slots;
                                }
                            }
                        }
                    }
                }
                $array_of_time = array_values($array_of_time);
                $all_continous_slot = calculate_continuous_slots($array_of_time);
                $required_slots = ceil($required_duration / 30);
                foreach ($all_continous_slot as $index => $row) {
                    if ($index == count($all_continous_slot) - 1 && $ignore_last_slot == true) {
                        $required_slots = $required_slots - $next_day_fullfilled_slots + 1;
                    }
                    $last_available_slot  = (count($row) - $required_slots) + 1;
                    for ($i = count($row) - 1; $i > $last_available_slot; $i--) {
                        if ($i >= 0 && (($key = array_search($row[$i], $array_of_time)) !== false)) {
                            unset($array_of_time[$key]);
                            $busy_slots[] = $row[$i];
                        }
                    }
                }
            }
            $response['error'] = false;
            $response['available_slots'] = $array_of_time;
            $response['busy_slots'] = $busy_slots;
            return $response;
        } else {
            $response['error'] = true;
            $response['message'] = "provider is closed on this day";
            return $response;
        }
    }
    //=====================================================================================================
    //=====================================================================================================
    //=====================================================================================================
    $today = date('Y-m-d');
    if ($booking_date < $today) {
        $response['error'] = true;
        $response['message'] = "please select upcoming date!";
        return $response;
    }
    $db = \config\Database::connect();
    $day = date('l', strtotime($booking_date));
    $timings = getTimingOfDay($partner_id, $day);
    if (isset($timings) && !empty($timings)) {
        $opening_time = $timings['opening_time'];
        $closing_time = $timings['closing_time'];
        $booked_slots = booked_timings($partner_id, $booking_date);
        $interval = 30 * 60;
        $start_time = strtotime($opening_time);
        $current_time = time();
        $end_time = strtotime($closing_time);
        $count = count($booked_slots);
        $current_date = date('Y-m-d');
        $available_slots = [];
        $busy_slots = [];
        //if booked slot is not empty means that day no odrer no found
        while ($start_time < $end_time) {
            $array_of_time[] = date("H:i:s", $start_time);
            $start_time += $interval;
        }
        if (isset($booked_slots) && !empty($booked_slots)) {
            //here suggested time is created in gap of 30 minutes
            $count_suggestion_slots = count($array_of_time);
            //loop on total booked slots
            for ($i = 0; $i < $count; $i++) {
                //loop on suggested time slots
                for ($j = 0; $j < $count_suggestion_slots; $j++) {
                    //if suggested time slot is less than booked slot starting time or suggested time slot is greater than booked time slot starting time
                    if (strtotime($array_of_time[$j]) < strtotime($booked_slots[$i]['starting_time']) || strtotime($array_of_time[$j]) >= strtotime($booked_slots[$i]['ending_time'])) {
                        //check if suggested time slot is not  in array of avaialble slot
                        if (!in_array($array_of_time[$j], $available_slots)) {
                            //if suggested time slot is grater than current time and current date and booked date are not same then to available slot array otherwise busy slot array
                            if (strtotime($array_of_time[$j]) > $current_time || strtotime($booking_date) != strtotime($current_date)) {
                                // echo $array_of_time[$j]." added to available time slot <br/>";
                                $available_slots[] = $array_of_time[$j];
                            } else {
                                // echo $array_of_time[$j]." added to busy time slot11<br/>";
                                if (!in_array($array_of_time[$j], $busy_slots)) {
                                    $busy_slots[] = $array_of_time[$j];
                                }
                            }
                            // die;
                        } else {
                        }
                    } else {
                        //  echo $array_of_time[$j]." added to busy time slot22<br/>";
                        if (!in_array($array_of_time[$j], $busy_slots)) {
                            $busy_slots[] = $array_of_time[$j];
                        }
                    }
                }
                $count_busy_slots = count($busy_slots);
                for ($k = 0; $k < $count_busy_slots; $k++) {
                    if (($key = array_search($busy_slots[$k], $available_slots)) !== false) {
                        unset($available_slots[$key]);
                    }
                }
            }
            //here to continue the index of available_slots
            $available_slots = array_values($available_slots);
            $ignore_last_slot = false;
            $all_continous_slot = calculate_continuous_slots($available_slots);
            $next_day_slots = get_next_days_slots($closing_time, $booking_date, $partner_id, $required_duration, $current_date);
            // if(!empty($next_day_available_slots)){
            $next_day_available_slots = $next_day_slots['continous_available_slots'];
            $required_slots = ceil($required_duration / 30);
            if (isset($next_day_available_slots[0][0]) && $next_day_available_slots[0][0] === $opening_time) {
                // echo "if1";
                $next_day_fullfilled_slots = count($next_day_available_slots[0]);
                if ($next_day_fullfilled_slots >= $required_slots) {
                    // echo "if2";
                    $ignore_last_slot = true;
                    $required_duration_for_last_slot = $next_day_fullfilled_slots * 30;
                } else {
                    // echo "else";
                    $expected_remaining_duration_for_today = $required_duration - ($next_day_fullfilled_slots * 30);
                    // echo $expected_remaining_duration_for_today."<br>";
                    $last_contious_slot_of_current_day = $all_continous_slot[count($all_continous_slot) - 1];
                    // print_R($last_contious_slot_of_current_day);
                    $last_element_of_current_day = $last_contious_slot_of_current_day[count($last_contious_slot_of_current_day) - 1];
                    $last_element_of_current_day = date("H:i:s", strtotime('+30 minutes', strtotime($last_element_of_current_day)));
                    if ($last_element_of_current_day == $closing_time) {
                        // echo "if3";
                        $required_duration_for_last_slot = count($last_contious_slot_of_current_day) * 30;
                        if ($expected_remaining_duration_for_today < $required_duration_for_last_slot) {
                            // echo "if5";
                            $ignore_last_slot = true;
                        }
                    } else {
                        // echo "else2";
                        //Don't do anything here
                    }
                }
            } else {
                // echo "else3";
                //Don't do anything here as the next function will handle the last available slot and all
            }
            //Disable all the chunks that are not required enough
            $continous_slot_doration = 0; // Initialize the variable before the loop
            foreach ($all_continous_slot as $index => $row) {
                $ignore_last_slot_local = false;
                if ($index === (count($all_continous_slot) - 1)) {
                    $ignore_last_slot_local = ($ignore_last_slot == false) ? false : true;
                }
                if ($ignore_last_slot_local) {
                    $continous_slot_doration = sizeof($row) * 30;
                    if ($continous_slot_doration < $required_duration) {
                        foreach ($row as $child_slots) {
                            if (($key = array_search($child_slots, $available_slots)) !== false) {
                                unset($available_slots[$key]);
                                $busy_slots[] = $child_slots;
                            }
                        }
                    }
                }
            }
            $available_slots = array_values($available_slots);
            $all_continous_slot = calculate_continuous_slots($available_slots);
            $required_slots = ceil($required_duration / 30);
            foreach ($all_continous_slot as $index => $row) {
                if ($index == count($all_continous_slot) - 1 && $ignore_last_slot == true) {
                    $required_slots = $required_slots - $next_day_fullfilled_slots + 1;
                }
                $last_available_slot  = (count($row) - $required_slots) + 1;
                for ($i = count($row) - 1; $i > $last_available_slot; $i--) {
                    if ($i >= 0 && (($key = array_search($row[$i], $available_slots)) !== false)) {
                        unset($available_slots[$key]);
                        $busy_slots[] = $row[$i];
                    }
                }
            }
            //---------------------------------  START ----------------------------------------------------------
            // Fetch order data from the database for the requested partner
            $builder = $db->table('orders');
            $builder->select('starting_time, ending_time, date_of_service');
            $builder->where('partner_id', $partner_id);
            $builder->where('date_of_service', $booking_date);
            $builder->whereIn('status', ['awaiting', 'pending', 'confirmed', 'rescheduled']);
            $booked_slots = $builder->get()->getResultArray();
            $duration = $required_duration; // Duration of each service in minutes
            foreach ($available_slots as $slot) {
                $slot_time = strtotime($slot);
                $slot_end_time = strtotime("+$duration minutes", $slot_time);
                $is_booked = false;
                foreach ($booked_slots as $booked_slot) {
                    $booked_start_time = strtotime($booked_slot['starting_time']);
                    $booked_end_time = strtotime($booked_slot['ending_time']);
                    if (($slot_time >= $booked_start_time && $slot_time < $booked_end_time) ||
                        ($slot_end_time > $booked_start_time && $slot_end_time <= $booked_end_time)
                    ) {
                        $is_booked = true;
                        break;
                    }
                }
                if ($is_booked) {
                    $busy_slots[] = $slot;
                    $index = array_search($slot, $available_slots);
                    if ($index !== false) {
                        unset($available_slots[$index]);
                    }
                }
            }
            // //------------------------------------------------------- END------------------------------------------------------------------
            $response['error'] = false;
            $response['available_slots'] = $available_slots;
            $response['busy_slots'] = $busy_slots;
            return $response;
        } else {
            // print_r($array_of_time);
            if (!isset($array_of_time) || empty($array_of_time)) {
                $array_of_time = [];
            }
            if (strtotime($booking_date) == strtotime($current_date)) {
                foreach ($array_of_time as $row) {
                    if (strtotime($row) < $current_time) {
                        if (($key = array_search($row, $array_of_time)) !== false) {
                            unset($array_of_time[$key]);
                            $busy_slots[] = $row;
                        }
                    }
                }
            }
            //here to continue the index of available_slots
            $array_of_time = array_values($array_of_time);
            $ignore_last_slot = false;
            $all_continous_slot = calculate_continuous_slots($array_of_time);
            if (!empty($array_of_time)) {
                $next_day_slots = get_next_days_slots($closing_time, $booking_date, $partner_id, $required_duration, $current_date);
                $next_day_available_slots = $next_day_slots['continous_available_slots'];
                $required_slots = ceil($required_duration / 30);
                if (isset($next_day_available_slots[0][0]) && $next_day_available_slots[0][0] === $opening_time) {
                    // echo "if1";
                    $next_day_fullfilled_slots = count($next_day_available_slots[0]);
                    if ($next_day_fullfilled_slots >= $required_slots) {
                        // echo "if2";
                        $ignore_last_slot = true;
                        $required_duration_for_last_slot = $next_day_fullfilled_slots * 30;
                    } else {
                        // echo "else";
                        $expected_remaining_duration_for_today = $required_duration - ($next_day_fullfilled_slots * 30);
                        // echo $expected_remaining_duration_for_today."<br>";
                        $last_contious_slot_of_current_day = $all_continous_slot[count($all_continous_slot) - 1];
                        // print_R($last_contious_slot_of_current_day);
                        $last_element_of_current_day = $last_contious_slot_of_current_day[count($last_contious_slot_of_current_day) - 1];
                        $last_element_of_current_day = date("H:i:s", strtotime('+30 minutes', strtotime($last_element_of_current_day)));
                        if ($last_element_of_current_day == $closing_time) {
                            // echo "if3";
                            $required_duration_for_last_slot = count($last_contious_slot_of_current_day) * 30;
                            if ($expected_remaining_duration_for_today < $required_duration_for_last_slot) {
                                // echo "if5";
                                $ignore_last_slot = true;
                            }
                        } else {
                            // echo "else2";
                            //Don't do anything here
                        }
                    }
                } else {
                    // echo "else3";
                    //Don't do anything here as the next function will handle the last available slot and all
                }
            }
            //Disable all the chunks that are not required enough
            $continous_slot_doration = 0; // Initialize the variable before the loop
            foreach ($all_continous_slot as $index => $row) {
                $ignore_last_slot_local = false;
                if ($index === (count($all_continous_slot) - 1)) {
                    $ignore_last_slot_local = ($ignore_last_slot == false) ? false : true;
                }
                if ($ignore_last_slot_local) {
                    $continous_slot_doration = sizeof($row) * 30;
                    if ($continous_slot_doration < $required_duration) {
                        foreach ($row as $child_slots) {
                            if (($key = array_search($child_slots, $array_of_time)) !== false) {
                                unset($array_of_time[$key]);
                                $busy_slots[] = $child_slots;
                            }
                        }
                    }
                }
            }
            $array_of_time = array_values($array_of_time);
            $all_continous_slot = calculate_continuous_slots($array_of_time);
            $required_slots = ceil($required_duration / 30);
            foreach ($all_continous_slot as $index => $row) {
                if ($index == count($all_continous_slot) - 1 && $ignore_last_slot == true) {
                    $required_slots = $required_slots - $next_day_fullfilled_slots + 1;
                }
                $last_available_slot  = ((count($row)) - $required_slots) + 1;
                $next_day_slots1 = get_next_days_slots($closing_time, $booking_date, $partner_id, $required_duration, $current_date);
                if (empty($next_day_slots1['available_slots'])) {
                    for ($i = count($row) - 1; $i >= $last_available_slot; $i--) {
                        if ($i >= 0 && (($key = array_search($row[$i], $array_of_time)) !== false)) {
                            unset($array_of_time[$key]);
                            $busy_slots[] = $row[$i];
                        }
                    }
                } else {
                    for ($i = count($row) - 1; $i > $last_available_slot; $i--) {
                        if ($i >= 0 && (($key = array_search($row[$i], $array_of_time)) !== false)) {
                            unset($array_of_time[$key]);
                            $busy_slots[] = $row[$i];
                        }
                    }
                }
            }
        }
        // die;
        $response['error'] = false;
        $response['available_slots'] = $array_of_time;
        $response['busy_slots'] = $busy_slots;
        return $response;
    } else {
        $response['error'] = true;
        $response['message'] = "provider is closed on this day";
        return $response;
    }
}
function get_available_slots_without_processing($partner_id, $booking_date, $required_duration = null, $next_day_order = null)
{
    $today = date('Y-m-d');
    if ($booking_date < $today) {
        $response['error'] = true;
        $response['message'] = "please select upcoming date!";
        return $response;
    }
    $db = \config\Database::connect();
    $day = date('l', strtotime($booking_date));
    $busy_slots = [];
    $timings = getTimingOfDay($partner_id, $day);
    if (isset($timings) && !empty($timings)) {
        $opening_time = $timings['opening_time'];
        $closing_time = $timings['closing_time'];
        $booked_slots = booked_timings($partner_id, $booking_date);
        $interval = 30 * 60;
        $start_time = strtotime($next_day_order);
        $current_time = time();
        $end_time = strtotime($closing_time);
        $count = count($booked_slots);
        $current_date = date('Y-m-d');
        $available_slots = [];
        $array_of_time = [];
        //here suggested time is created in gap of 30 minutes
        while ($start_time <= $end_time) {
            $array_of_time[] = date("H:i:s", $start_time);
            $start_time += $interval;
        }
        // addedd  start
        if (strtotime($booking_date) == strtotime($current_date)) {
            foreach ($array_of_time as $row) {
                if (strtotime($row) < $current_time) {
                    if (($key = array_search($row, $array_of_time)) !== false) {
                        unset($array_of_time[$key]);
                        $busy_slots[] = $row;
                    }
                }
            }
        }
        //addedd end
        //here to continue the index of available_slots
        $array_of_time = array_values($array_of_time);
        if (isset($booked_slots) && !empty($booked_slots)) {
            //here suggested time is created in gap of 30 minutes
            $count_suggestion_slots = count($array_of_time);
            //loop on total booked slots
            for ($i = 0; $i < $count; $i++) {
                //loop on suggested time slots
                for ($j = 0; $j < $count_suggestion_slots; $j++) {
                    //if suggested time slot is less than booked slot starting time or suggested time slot is greater than booked time slot starting time
                    if (strtotime($array_of_time[$j]) < strtotime($booked_slots[$i]['starting_time']) || strtotime($array_of_time[$j]) >= strtotime($booked_slots[$i]['ending_time'])) {
                        if (!in_array($array_of_time[$j], $available_slots)) {
                            //if suggested time slot is grater than current time and current date and booked date are not same then to available slot array otherwise busy slot array
                            if (strtotime($array_of_time[$j]) > $current_time || strtotime($booking_date) != strtotime($current_date)) {
                                // echo $array_of_time[$j]." added to available time slot <br/>";
                                $available_slots[] = $array_of_time[$j];
                            } else {
                                // echo $array_of_time[$j]." added to busy time slot11<br/>";
                                if (!in_array($array_of_time[$j], $busy_slots)) {
                                    $busy_slots[] = $array_of_time[$j];
                                }
                            }
                            // die;
                        } else {
                        }
                    } else {
                        //  echo $array_of_time[$j]." added to busy time slot22<br/>";
                        if (!in_array($array_of_time[$j], $busy_slots)) {
                            $busy_slots[] = $array_of_time[$j];
                        }
                    }
                }
                $count_busy_slots = count($busy_slots);
                for ($k = 0; $k < $count_busy_slots; $k++) {
                    if (($key = array_search($busy_slots[$k], $available_slots)) !== false) {
                        unset($available_slots[$key]);
                    }
                }
            }
        }
        $all_continous_slot = calculate_continuous_slots($array_of_time);
        $response['error'] = false;
        $response['available_slots'] = $all_continous_slot;
        return $response;
    } else {
        $response['error'] = true;
        $response['message'] = "provider is closed on this day";
        return $response;
    }
}
function get_service($service_id)
{
    if ($service_id != null) {
        return false;
    }
    $service = fetch_details('services', ['id' => $service_id]);
    if ($service != null && !empty($service)) {
        return response_helper('Found data', false, $service);
    } else {
        return response_helper('No Data Found', false, []);
    }
}
function has_ordered($user_id, $service_id, $custom_job_id = null)
{
    $db = \config\Database::connect();
    if ($custom_job_id) {
        $custom_job = fetch_details('custom_job_requests', ['id' => $custom_job_id]);
        if (empty($custom_job)) {
            $response['error'] = true;
            $response['message'] = "No Custom Service Found";
            return $response;
        }
        $builder = $db
            ->table('orders o')
            ->select(' o.id,o.user_id,os.service_id')
            ->join('order_services os', 'os.order_id = o.id')
            ->where('user_id', $user_id)
            ->where('o.status', 'completed')
            ->where('os.custom_job_request_id', $custom_job_id)->get()->getResultArray();
    } else {
        $services = fetch_details('services', ['id' => $service_id]);


        if (empty($services)) {
            $response['error'] = true;
            $response['message'] = "No Service Found";
            return $response;
        }
        $builder = $db
            ->table('orders o')
            ->select(' o.id,o.user_id,os.service_id')
            ->join('order_services os', 'os.order_id = o.id')
            ->where('user_id', $user_id)
            ->where('o.status', 'completed')
            ->where('os.service_id', $service_id)->get()->getResultArray();
    }
    if (!empty($builder)) {
        $response['error'] = false;
        $response['message'] = "Has ordered";
        return $response;
    } else {
        $response['error'] = true;
        $response['message'] = "Can not rate service  without Placing orders";
        return $response;
    }
}
function has_rated($user_id, $rate_id)
{
    $db = \config\Database::connect();
    $builder = $db
        ->table('services_ratings sr')
        ->select('sr.*')
        ->where('sr.id', $rate_id)
        ->where('user_id', $user_id);
    $old_data = $builder->get()->getResultArray();
    if (!empty($old_data)) {
        $response['error'] = false;
        $response['message'] = "Found Rating";
        $response['data'] = $old_data;
        return $response;
    } else {
        $response['error'] = true;
        $response['message'] = "No Rating Found";
        return $response;
    }
}
// function get_ratings($user_id)
// {
//     $db = \config\Database::connect();
//     $builder = $db
//         ->table('services s')
//         ->select("
//                 COUNT(sr.rating) as total_ratings,
//                 SUM( CASE WHEN sr.rating = ceil(5) THEN 1 ELSE 0 END) as rating_5,
//                 SUM( CASE WHEN sr.rating = ceil(4) THEN 1 ELSE 0 END) as rating_4,
//                 SUM( CASE WHEN sr.rating = ceil(3) THEN 1 ELSE 0 END) as rating_3,
//                 SUM( CASE WHEN sr.rating = ceil(2) THEN 1 ELSE 0 END) as rating_2,
//                 SUM( CASE WHEN sr.rating = ceil(1) THEN 1 ELSE 0 END) as rating_1
//             ")
//         ->join('services_ratings sr', 'sr.service_id = s.id')
//         ->where('s.user_id', $user_id)
//         ->join('users u', 'u.id = sr.user_id')
//         ->get()->getResultArray();
//     return $builder;
// }
function get_ratings($user_id)
{
    $db = \config\Database::connect();
    $builder = $db
        ->table('services_ratings sr')
        ->select("
            COUNT(sr.rating) as total_ratings,
            SUM(CASE WHEN sr.rating = ceil(5) THEN 1 ELSE 0 END) as rating_5,
            SUM(CASE WHEN sr.rating = ceil(4) THEN 1 ELSE 0 END) as rating_4,
            SUM(CASE WHEN sr.rating = ceil(3) THEN 1 ELSE 0 END) as rating_3,
            SUM(CASE WHEN sr.rating = ceil(2) THEN 1 ELSE 0 END) as rating_2,
            SUM(CASE WHEN sr.rating = ceil(1) THEN 1 ELSE 0 END) as rating_1
        ")
        ->join('services s', 'sr.service_id = s.id', 'left')
        ->join('custom_job_requests cj', 'sr.custom_job_request_id = cj.id', 'left')
        ->join('partner_bids pd', 'pd.custom_job_request_id = cj.id', 'left')
        ->join('users u', 'u.id = sr.user_id')
        ->where("(s.user_id = {$user_id}) OR (pd.partner_id = {$user_id})")
        ->get()->getResultArray();
    return $builder;
}
// function update_ratings($service_id, $rate)
// {
//     $db = \config\Database::connect();
//     $service_data = fetch_details('services', ['id' => $service_id]);
//     if (!empty($service_data)) {
//         $user_id = $service_data[0]['user_id'];
//     }
//     $partner_data = fetch_details('partner_details', ['partner_id' => $user_id]);
//     if (!empty($partner_data)) {
//         $partner_id = $partner_data[0]['partner_id'];
//     }
//     $service_ids = fetch_details('services', ['user_id' => $user_id], ['id']);
//     $ids = [];
//     foreach ($service_ids as $si) {
//         array_push($ids, $si['id']);
//     }
//     $data = $db
//         ->table('services_ratings sr')
//         ->select(
//             'count(sr.rating) as number_of_ratings,
//                 sum(sr.rating) as total_rating,
//                 (sum(sr.rating) /count(sr.rating)) as avg_rating'
//         )
//         ->whereIn('service_id', $ids)
//         ->get()->getResultArray();
//     if (!empty($data)) {
//         $data[0]['number_of_ratings'] = $data[0]['number_of_ratings'];
//         $data[0]['total_rating'] = $data[0]['total_rating'];
//         $data[0]['avg_rating'] = $data[0]['total_rating'] / $data[0]['number_of_ratings'];
//         $updated_data = update_details(['ratings' => $data[0]['avg_rating'], 'number_of_ratings' => $data[0]['number_of_ratings']], ['partner_id' => $partner_id], 'partner_details');
//         $updated_data = update_details(['rating' => $data[0]['avg_rating'], 'number_of_ratings' => $data[0]['number_of_ratings']], ['id' => $service_id], 'services');
//     } else {
//         $updated_data = update_details(
//             ['ratings' => $rate, 'number_of_ratings' => 1],
//             ['partner_id' => $partner_id],
//             'partner_details'
//         );
//         $updated_data = update_details(['rating' => $rate, 'number_of_ratings' => 1], ['id' => $service_id], 'services');
//     }
//     if ($updated_data != "") {
//         return $response['error'] = false;
//     } else {
//         return $response['error'] = true;
//     }
// }
function update_ratings($service_id, $rate)
{
    $db = \config\Database::connect();
    // Get service data
    $service_data = fetch_details('services', ['id' => $service_id]);
    if (empty($service_data)) {
        return ['error' => true];
    }
    $user_id = $service_data[0]['user_id'];
    // Get all ratings for this user's services in one query
    $ratings = $db->table('services_ratings sr')
        ->select('COUNT(sr.rating) as number_of_ratings, SUM(sr.rating) as total_rating')
        ->join('services s', 's.id = sr.service_id')
        ->where('s.user_id', $user_id)
        ->get()
        ->getRowArray();
    // Prepare update data
    if (!empty($ratings) && $ratings['number_of_ratings'] > 0) {
        $avg_rating = $ratings['total_rating'] / $ratings['number_of_ratings'];
        $num_ratings = $ratings['number_of_ratings'];
    } else {
        $avg_rating = $rate;
        $num_ratings = 1;
    }
    // Update partner details
    $updated = update_details(
        ['ratings' => $avg_rating, 'number_of_ratings' => $num_ratings],
        ['partner_id' => $user_id],
        'partner_details',false
    );
    // Update service
    $updated = update_details(
        ['rating' => $avg_rating, 'number_of_ratings' => $num_ratings],
        ['id' => $service_id],
        'services',false
    );
    return ['error' => ($updated === "") ? true : false];
}
function rating_images($rating_id, $from_app = false)
{
    $rating_data = fetch_details('services_ratings', ['id' => $rating_id]);
    $disk = fetch_current_file_manager();

    $d = ($from_app == false) ? 'for web' : 'for app';
    if (!empty($rating_data)) {
        $rating_images = json_decode($rating_data[0]['images'], true);
        $images_restored = [];
        foreach ($rating_images as $ri) {
            if ($from_app == false) {
                if ($disk == "local_server") {
                    $image_url =  base_url($ri);
                } else if ($disk == "aws_s3") {
                    $image_url =  fetch_cloud_front_url('ratings', $ri);
                }
                $image = '<a  href="' . base_url($ri) . '" data-lightbox="image-1"><img height="80px" class="rounded" src="' . $image_url . '" alt=""></a>';
                array_push($images_restored, $image);
            } else {
                if ($disk == "local_server") {
                    array_push($images_restored, base_url($ri));
                } else if ($disk == "aws_s3") {
                    array_push($images_restored, fetch_cloud_front_url('ratings', $ri));
                }
            }
        }
    }
    return $images_restored;
}
function is_favorite($user_id, $partner_id)
{
    $db = \config\Database::connect();
    $builder = $db
        ->table('bookmarks b')
        ->select('b.*')
        ->where('b.user_id', $user_id)
        ->where('b.partner_id', $partner_id);
    $data = $builder->get()->getResultArray();
    if (!empty($data)) {
        return true;
    } else {
        return false;
    }
}
function favorite_list($user_id)
{
    $db = \config\Database::connect();
    $builder = $db
        ->table('bookmarks b')
        ->select('b.partner_id')
        ->where('b.user_id', $user_id);
    $data = $builder->get()->getResultArray();
    $partner_ids = [];
    if (!empty($data)) {
        foreach ($data as $dt) {
            array_push($partner_ids, $dt['partner_id']);
        }
        return $partner_ids;
    } else {
        return false;
    }
}
function in_cart_qty($service_id, $user_id)
{
    $data = fetch_details('cart', ['user_id' => $user_id, 'service_id' => $service_id], ['qty']);
    $quantity = (!empty($data)) ? $data[0]['qty'] : '0';
    return $quantity;
}
function resize_image($image, $new_image, $thumbnail, $width = 300, $height = 300)
{
    if (file_exists(FCPATH . $image)) {
        if (!is_dir(base_url($thumbnail))) {
            mkdir(base_url($thumbnail), 0775, true);
        }
        \Config\Services::image('gd')
            ->withFile(FCPATH . $image)
            ->resize($width, $height, true, 'auto')
            ->save(FCPATH . $new_image);
        $response['error'] = false;
        $response['message'] = "File resizes successfully";
        return $response;
    } else {
        $response['error'] = true;
        $response['message'] = "File does not exist";
        return $response;
    }
}
function provider_total_earning_chart($partner_id = '')
{
    $amount = fetch_details('orders', ['partner_id' => $partner_id, 'is_commission_settled' => '0'], ['sum(final_total) as total']);
    $db = \config\Database::connect();
    $builder = $db
        ->table('orders')
        ->select('SUM(final_total) AS total, DATE_FORMAT(created_at,"%b") AS month_name')
        ->where('partner_id', $partner_id)
        ->where('status', 'completed')
        ->groupBy('created_at');
    $data = $builder->get()->getResultArray();
    $admin_commission_percentage = get_admin_commision($partner_id);
    $admin_commission_amount = intval($admin_commission_percentage) / 100;
    $month_wise_sales = ['total_sale' => [], 'month_name' => []];
    foreach ($data as $row) {
        $tempRow = $row['total'];
        $commission = intval($tempRow) * $admin_commission_amount;
        $total_after_commission = $tempRow - $commission;
        $month_wise_sales['total_sale'][] = $total_after_commission;
        $month_wise_sales['month_name'][] = $row['month_name'];
    }
    return $month_wise_sales;
}
function provider_already_withdraw_chart($partner_id = '')
{
    $db = \config\Database::connect();
    $builder = $db
        ->table('payment_request')
        ->select('sum(amount) as total')
        ->select('SUM(amount) AS total_withdraw,DATE_FORMAT(created_at,"%b") AS month_name')
        ->where('status', '1')
        ->where('user_id', $partner_id);
    $data = $builder->groupBy('created_at')->get()->getResultArray();
    $tempRow = array();
    $row1 = array();
    foreach ($data as $key => $row) {
        $tempRow = $row['total'];
        $row1[] = $tempRow;
    }
    $month_wise_sales['total_withdraw'] = array_map('intval', array_column($data, 'total_withdraw'));
    $month_wise_sales['month_name'] = array_column($data, 'month_name');
    $total_withdraw = $month_wise_sales;
    return $total_withdraw;
}
function provider_pending_withdraw_chart($partner_id = '')
{
    $db = \config\Database::connect();
    $builder = $db
        ->table('payment_request')
        ->select('sum(amount) as total')
        ->select('SUM(amount) AS pending_withdraw,DATE_FORMAT(created_at,"%b") AS month_name')
        ->where('status', '0')
        ->where('user_id', $partner_id);
    $data = $builder->groupBy('created_at')->get()->getResultArray();
    $month_wise_sales['pending_withdraw'] = array_map('floatval', array_column($data, 'pending_withdraw'));
    $month_wise_sales['month_name'] = array_column($data, 'month_name');
    $pending_withdraw = $month_wise_sales;
    return $pending_withdraw;
    // return $row1;
}
function provider_withdraw_chart($partner_id = '')
{
    $db = \config\Database::connect();
    $builder = $db
        ->table('payment_request')
        ->select('sum(amount) as total')
        ->select('SUM(amount) AS withdraw_request,DATE_FORMAT(created_at,"%b") AS month_name')
        ->where('user_id', $partner_id);
    $data = $builder->groupBy('created_at')->get()->getResultArray();
    $month_wise_sales['withdraw_request'] = array_map('intval', array_column($data, 'withdraw_request'));
    $month_wise_sales['month_name'] = array_column($data, 'month_name');
    $withdraw_request = $month_wise_sales;
    return $withdraw_request;
}
function income_revenue($partner_id = '')
{
    $db = \config\Database::connect();
    $builder = $db
        ->table('payment_request')
        ->select('sum(amount) as total')
        ->select('SUM(amount) AS income_revenue,DATE_FORMAT(date_of_service,"%b") AS month_name')
        ->where('status', '0');
    $data = $builder->groupBy('MONTH(created_at), YEAR(created_at)')->get()->getResultArray();
    $month_wise_sales['income_revenue'] = array_map('intval', array_column($data, 'income_revenue'));
    $month_wise_sales['month_name'] = array_column($data, 'month_name');
    $income_revenue = $month_wise_sales;
    return $income_revenue;
}
function admin_income_revenue($partner_id = '')
{
    $db = \config\Database::connect();
    $builder =  $db
        ->table('orders o')
        ->select('
            o.final_total, pd.admin_commission,pd.*,
            SUM(( o.final_total * pd.admin_commission)/100) as total_admin_earning,DATE_FORMAT(o.date_of_service,"%b") AS month_name
        ')
        ->where('o.status', 'completed')
        ->join('partner_details pd', 'pd.partner_id = o.partner_id', 'left')
        ->groupBy('month_name');
    $data = $builder->get()->getResultArray();
    $month_wise_sales['income_revenue'] = array_map('intval', array_column($data, 'total_admin_earning'));
    $month_wise_sales['month_name'] = array_column($data, 'month_name');
    $admin_income_revenue = $month_wise_sales;
    return $admin_income_revenue;
}
function provider_income_revenue($partner_id = '')
{
    $db = \config\Database::connect();
    $builder =  $db
        ->table('orders o')
        ->select('
        o.final_total, pd.admin_commission,pd.*,
        SUM(o.final_total - (( o.final_total * pd.admin_commission)/100)) as total_partner_earning,DATE_FORMAT(o.date_of_service,"%b") AS month_name
        ')
        ->where('o.status', 'completed')
        ->join('partner_details pd', 'pd.partner_id = o.partner_id', 'left')
        ->groupBy('month_name');
    $data = $builder->get()->getResultArray();
    $month_wise_sales['income_revenue'] = array_map('intval', array_column($data, 'total_partner_earning'));
    $month_wise_sales['month_name'] = array_column($data, 'month_name');
    $provider_income_revenue = $month_wise_sales;
    return $provider_income_revenue;
}
function total_income_revenue($partner_id = '')
{
    $db = \config\Database::connect();
    $builder = $db
        ->table('orders o')
        ->select('SUM(o.final_total) AS total_earning, DATE_FORMAT(o.date_of_service, "%b") AS month_name')
        ->where('o.status', 'completed')
        ->join('partner_details pd', 'pd.partner_id = o.partner_id', 'left')
        ->groupBy('month_name');
    $data = $builder->get()->getResultArray();
    $month_wise_sales['income_revenue'] = array_map('intval', array_column($data, 'total_earning'));
    $month_wise_sales['month_name'] = array_column($data, 'month_name');
    return $month_wise_sales;
}
function fetch_top_trending_services($category_id = 'null')
{
    $db = \config\Database::connect();
    $builder = $db->table('order_services');
    $builder->select('service_id, COUNT(*) as count');
    $builder->where('status', 'completed');
    $builder->groupBy('service_id');
    $builder->orderBy('count', 'desc');
    $builder->limit(10);
    $trending_services = $builder->get()->getResultArray();
    $top_trending_services = array();
    $total_service_orders = array();
    foreach ($trending_services as $key => $trending_service) {
        if ($category_id != "null") {
            $where = ['id' => $trending_service['service_id'], 'category_id' => $category_id];
        } else {
            $where = ['id' => $trending_service['service_id']];
        }
        $services = fetch_details("services", $where, ['id', 'title', 'image', 'price', 'discounted_price', 'category_id'], '10');
        foreach ($services as $key => $row) {
            $total_service_orders = $db->table('order_services o')->select('count(o.id) as `total`')->where('status', 'completed')->where('o.service_id', $row['id'])->get()->getResultArray();
            $services[$key]['order_data'] = $total_service_orders[0]['total'];
        }
        $top_trending_services[] = (!empty($services[0])) ? $services[0] : "";
    }
    return (array_filter($top_trending_services));
}
function order_encrypt($user_id, $amount, $order_id)
{
    $simple_string = $user_id . "-" . $amount . "-" . $order_id;
    // Store the cipher method
    $ciphering = "AES-128-CTR";
    // Use OpenSSl Encryption method
    $iv_length = openssl_cipher_iv_length($ciphering);
    $options = 0;
    // Non-NULL Initialization Vector for encryption
    $encryption_iv = '1234567891011121';
    // Store the encryption key
    $encryption_key = getenv('decryption_key');
    // Use openssl_encrypt() function to encrypt the data
    $encryption = openssl_encrypt(
        $simple_string,
        $ciphering,
        $encryption_key,
        $options,
        $encryption_iv
    );
    return $encryption;
}
function order_decrypt($order_id)
{
    $ciphering = "AES-128-CTR";
    $options = 0;
    // Use openssl_encrypt() function to encrypt the data
    $encryption = $order_id;
    // Non-NULL Initialization Vector for decryption
    $decryption_iv = '1234567891011121';
    // Store the decryption key
    $decryption_key = getenv('decryption_key');
    // Use openssl_decrypt() function to decrypt the data
    $decryption = openssl_decrypt(
        $encryption,
        $ciphering,
        $decryption_key,
        $options,
        $decryption_iv
    );
    $order_id = (explode("-", $decryption));
    return $order_id;
}
function is_file_uploaded($result = null)
{
    if ($result == true) {
        return true;
    } else {
        return false;
    }
}
function checkPartnerAvailability($partnerId, $requestedStartTime, $requestedDuration, $date_of_service, $starting_time)
{
    helper('date');
    $db = \Config\Database::connect();
    $builder = $db->table('orders');
    $builder->select('starting_time, ending_time, date_of_service');
    $builder->where('date_of_service', $date_of_service);
    $builder->where('partner_id', $partnerId);
    $builder->whereIn('status', ['awaiting', 'pending', 'confirmed', 'rescheduled']);
    $query = $builder->get()->getResultArray();
    $day = date('l', strtotime($requestedStartTime));
    $timings = getTimingOfDay($partnerId, $day);
    $date_of_service_timestamp = strtotime($date_of_service);
    $current_date_timestamp = time(); // Current date timestamp
    $date_of_service_date = date("Y-m-d", $date_of_service_timestamp);
    $current_date = date("Y-m-d", $current_date_timestamp);
    if ($date_of_service_date != $current_date && $date_of_service_timestamp < $current_date_timestamp) {
        $response['error'] = true;
        $response['message'] = "Please Select Upcoming date";
        return $response;
    }
    if (sizeof($query) > 0) {
        $orderTable = $query;
        $partnerClosingTime = $timings['closing_time']; // Replace with the actual closing time
        $requestedEndTime = date('Y-m-d H:i:s', strtotime($requestedStartTime) + $requestedDuration * 60);
        $provider_starting_time = date('H:i:s', strtotime($timings['opening_time']));
        $provider_closing_time = date('H:i:s', strtotime($partnerClosingTime));
        foreach ($orderTable as $order) {
            $orderStartTime = $order['date_of_service'] . ' ' . $order['starting_time'];
            $orderEndTime = $order['date_of_service'] . ' ' . $order['ending_time'];
            if ($requestedStartTime >= $orderStartTime && $requestedStartTime < $orderEndTime) {
                $response['error'] = true;
                $response['message'] = "The provider is currently unavailable during the requested time slot. Kindly propose an alternative time.";
                return $response;
            } elseif ($requestedEndTime > $orderStartTime && $requestedEndTime <= $orderEndTime) {
                $response['error'] = true;
                $response['message'] = "The provider is currently unavailable during the requested time slot. Kindly propose an alternative time.";
                return $response;
            } elseif ($requestedStartTime < $orderStartTime && $requestedEndTime > $orderEndTime) {
                $response['error'] = true;
                $response['message'] = "The provider is currently unavailable during the requested time slot. Kindly propose an alternative time.";
                return $response;
            }
        }
    }
    $time_slots = get_slot_for_place_order($partnerId, $date_of_service, $requestedDuration, $starting_time);
    if (isset($time_slots['closed']) && $time_slots['closed'] == "true") {
        $response['error'] = true;
        $response['message'] = "Provider is closed at this time";
        return $response;
    }
    $partnerClosingTime = $timings['closing_time'];
    $requestedEndTime = date('Y-m-d H:i:s', strtotime($requestedStartTime) + $requestedDuration * 60);
    $provider_starting_time = date('H:i:s', strtotime($timings['opening_time']));
    $provider_closing_time = date('H:i:s', strtotime($partnerClosingTime));
    if ($starting_time < $provider_starting_time || $starting_time >= $provider_closing_time) {
        $response['error'] = true;
        $response['message'] = "Provider is closed at this time";
    } elseif (!$time_slots['slot_avaialble'] && !$time_slots['suborder']) {
        $response['error'] = true;
        $response['message'] = "Slot is not available at this time ";
    } else {
        $response['error'] = false;
        $response['message'] = "Slot is available at this time";
    }
    return $response;
}
function next_day_available_slots($closing_time, $requestedDuration, $booking_date, $partner_id, $available_slots, $required_duration, $current_date, $busy_slots)
{
    // //-------------------------------------for next day order start--------------------------------------------------
    $before_end_time = date('H:i:s', strtotime($closing_time) - (30 * 60));
    $remaining_duration = $required_duration - 30;
    $next_day_date = date('Y-m-d', strtotime($booking_date . ' +1 day'));
    $next_day = date('l', strtotime($next_day_date));
    $next_day_timings = getTimingOfDay($partner_id, $next_day);
    $next_day_booked_slots = booked_timings($partner_id, $next_day_date);
    $interval = 30 * 60;
    $next_day_opening_time = $next_day_timings['opening_time'];
    $next_day_ending_time = $next_day_timings['closing_time'];
    $next_start_time = strtotime($next_day_opening_time);
    $time = $next_day_opening_time;
    $ending_time_for_next_day_slot = date('H:i:s', strtotime($time . ' +' . $remaining_duration . ' minutes'));
    $next_start_time = strtotime($next_day_opening_time);
    $next_day_available_slots = [];
    $next_day_busy_slots = [];
    $next_day_array_of_time = [];
    if (!empty($next_day_booked_slots)) {
        while ($next_start_time < strtotime($ending_time_for_next_day_slot)) {
            $next_day_array_of_time[] = date("H:i:s", $next_start_time);
            $next_start_time += $interval;
        }
        //check that main order date's last slot is available or not and remaining duration is grater than 30 min
        if (in_array($before_end_time, $available_slots) && $required_duration > 30) {
            //creating time slot for next day   
            //check that next day suggested slots are available or not
            //if next day has  orders
            if (count($next_day_booked_slots) > 0) {
                for ($i = 0; $i < count($next_day_booked_slots); $i++) {
                    //loop on suggested time slots
                    for ($j = 0; $j < count($next_day_array_of_time); $j++) {
                        //if suggested time slot is less than booked slot starting time or suggested time slot is greater than booked time slot starting time
                        if (strtotime($next_day_array_of_time[$j]) < strtotime($next_day_booked_slots[$i]['starting_time']) || strtotime($next_day_array_of_time[$j]) >= strtotime($next_day_booked_slots[$i]['ending_time'])) {
                            //check if suggested time slot is not  in array of avaialble slot
                            if (!in_array($next_day_array_of_time[$j], $next_day_available_slots)) {
                                // echo "suggested slot is not in avaiable slot<br/>";
                                $next_day_available_slots[] = $next_day_array_of_time[$j];
                            } else {
                                if (!in_array($next_day_array_of_time[$j], $next_day_busy_slots)) {
                                    $next_day_busy_slots[] = $next_day_array_of_time[$j];
                                }
                            }
                        } else {
                            if (!in_array($next_day_array_of_time[$j], $next_day_busy_slots)) {
                                $next_day_busy_slots[] = $next_day_array_of_time[$j];
                            }
                        }
                    }
                    $count_next_busy_slots = count($next_day_busy_slots);
                    for ($k = 0; $k < $count_next_busy_slots; $k++) {
                        if (($key = array_search($next_day_busy_slots[$k], $next_day_available_slots)) !== false) {
                            unset($next_day_available_slots[$key]);
                        }
                    }
                }
            } else {
                //loop on suggested time slots
                for ($j = 0; $j < count($next_day_array_of_time); $j++) {
                    //check if suggested time slot is not  in array of avaialble slot
                    if (!in_array($next_day_array_of_time[$j], $next_day_available_slots)) {
                        //if suggested time slot is grater than current time and current date and booked date are not same then to available slot array otherwise busy slot array
                        if (strtotime($next_day_date) != strtotime($current_date)) {
                            $next_day_available_slots[] = $next_day_array_of_time[$j];
                        } else {
                            if (!in_array($next_day_array_of_time[$j], $next_day_busy_slots)) {
                                $next_day_busy_slots[] = $next_day_array_of_time[$j];
                            }
                        }
                    }
                }
                $count_next_busy_slots = count($next_day_busy_slots);
                for ($k = 0; $k < $count_next_busy_slots; $k++) {
                    if (($key = array_search($next_day_busy_slots[$k], $next_day_available_slots)) !== false) {
                        unset($next_day_available_slots[$key]);
                    }
                }
            }
            $available_slots = array_values($available_slots);
            $all_continous_slot = calculate_continuous_slots($available_slots);
            $all_continous_slot_last_slots = $all_continous_slot[count($all_continous_slot) - 1];
            $continous_slot_last_slots = ($all_continous_slot_last_slots[count($all_continous_slot_last_slots) - 1]);
            // die;
            if ($before_end_time == $continous_slot_last_slots);
            // print_R('endind slot is avaialble </br>');
            // die;
            $next_day_all_continue_slot = calculate_continuous_slots($next_day_available_slots);
            // print_r( $next_day_all_continue_slot);
            // die;
            $next_day_available_duration = (count($next_day_all_continue_slot) * 30);
            $past_day_available_slot = count($all_continous_slot_last_slots) * 30;
            //  print_r( $next_day_all_continue_slot );
            $past_day_expected_available_duration = $required_duration - $next_day_available_duration;
            // print_R('past_day_expected_available_duration--' .$past_day_expected_available_duration."</br>");
            // print_R('past_day_available_slot--' .$past_day_available_slot."</br>");
            if ($past_day_expected_available_duration < 0 ||  $past_day_expected_available_duration < $past_day_available_slot) {
                if (count($next_day_available_slots) < count($next_day_array_of_time)) {
                    for ($k = 0; $k < count($available_slots); $k++) {
                        if (($key = array_search($before_end_time, $available_slots)) !== false) {
                            if (count($next_day_available_slots) < count($next_day_array_of_time)) {
                                // unset($available_slots[$key]);
                                // $busy_slots[] = $before_end_time;
                            }
                        }
                    }
                }
            }
        } else {
            for ($j = 0; $j < count($next_day_array_of_time); $j++) {
                //check if suggested time slot is not  in array of avaialble slot
                if (!in_array($next_day_array_of_time[$j], $next_day_available_slots)) {
                    //if suggested time slot is grater than current time and current date and booked date are not same then to available slot array otherwise busy slot array
                    if (strtotime($next_day_date) != strtotime($booking_date)) {
                        $next_day_available_slots[] = $next_day_array_of_time[$j];
                    } else {
                        if (!in_array($next_day_array_of_time[$j], $next_day_busy_slots)) {
                            $next_day_busy_slots[] = $next_day_array_of_time[$j];
                        }
                    }
                }
            }
            $count_next_busy_slots = count($next_day_busy_slots);
            for ($k = 0; $k < $count_next_busy_slots; $k++) {
                if (($key = array_search($next_day_busy_slots[$k], $next_day_available_slots)) !== false) {
                    unset($next_day_available_slots[$key]);
                }
            }
            $available_slots = array_values($available_slots);
            // if (count($next_day_available_slots) < count($next_day_array_of_time)) {
            //     for ($k = 0; $k < count($available_slots); $k++) {
            //         if (($key = array_search($before_end_time, $available_slots)) !== false) {
            //             if (count($next_day_available_slots) < count($next_day_array_of_time)) {
            //                 unset($available_slots[$key]);
            //                 $busy_slots[] = $before_end_time;
            //             }
            //         }
            //     }
            // }
            $all_continous_slot = calculate_continuous_slots($available_slots);
            $all_continous_slot_last_slots = $all_continous_slot[count($all_continous_slot) - 1];
            $continous_slot_last_slots = ($all_continous_slot_last_slots[count($all_continous_slot_last_slots) - 1]);
            // die;
            if ($before_end_time == $continous_slot_last_slots);
            $next_day_all_continue_slot = calculate_continuous_slots($next_day_available_slots);
            $next_day_available_duration = (count($next_day_all_continue_slot) * 30);
            $past_day_available_slot = count($all_continous_slot_last_slots) * 30;
            $past_day_expected_available_duration = $required_duration - $next_day_available_duration;
            if ($past_day_expected_available_duration < 0 || $past_day_available_slot < $past_day_expected_available_duration) {
                if (count($next_day_available_slots) < count($next_day_array_of_time)) {
                    for ($k = 0; $k < count($available_slots); $k++) {
                        if (($key = array_search($before_end_time, $available_slots)) !== false) {
                            if (count($next_day_available_slots) < count($next_day_array_of_time)) {
                                // unset($available_slots[$key]);
                                // $busy_slots[] = $before_end_time;
                            }
                        }
                    }
                }
            }
        }
        $response['error'] = false;
        $response['available_slots'] = $available_slots;
        $response['busy_slots'] = $busy_slots;
        return $response;
    }
}
function getTimingArray($start_time, $end_time, $interval)
{
    $timing_array = [];
    $current_time = strtotime($start_time);
    $end_time = strtotime($end_time);
    while ($current_time < $end_time) {
        $timing_array[] = date('H:i:s', $current_time);
        $current_time += $interval * 60;
    }
    return $timing_array;
}
function get_next_days_slots($closing_time, $booking_date, $partner_id, $required_duration, $current_date)
{
    $remaining_duration = $required_duration - 30;
    $next_day_date = date('Y-m-d', strtotime($booking_date . ' +1 day'));
    $next_day = date('l', strtotime($next_day_date));
    $next_day_timings = getTimingOfDay($partner_id, $next_day);
    $next_day_booked_slots = booked_timings($partner_id, $next_day_date);
    $interval = 30 * 60;
    if (!empty($next_day_timings)) {
        $next_day_opening_time = $next_day_timings['opening_time'];
        $next_day_ending_time = $next_day_timings['closing_time'];
        $next_start_time = strtotime($next_day_opening_time);
        $time = $next_day_opening_time;
        $ending_time_for_next_day_slot = date('H:i:s', strtotime($time . ' +' . $remaining_duration . ' minutes'));
        $next_start_time = strtotime($next_day_opening_time);
        $next_day_available_slots = [];
        $next_day_busy_slots = [];
        $next_day_array_of_time = [];
        while ($next_start_time < strtotime($ending_time_for_next_day_slot)) {
            $next_day_array_of_time[] = date("H:i:s", $next_start_time);
            $next_start_time += $interval;
        }
        if (!empty($next_day_booked_slots)) {
            //check that main order date's last slot is available or not and remaining duration is grater than 30 min
            //creating time slot for next day   
            //check that next day suggested slots are available or not
            //if next day has  orders
            if (count($next_day_booked_slots) > 0) {
                for ($i = 0; $i < count($next_day_booked_slots); $i++) {
                    // echo "-------------------------</br>";
                    //loop on suggested time slots
                    for ($j = 0; $j < count($next_day_array_of_time); $j++) {
                        //if suggested time slot is less than booked slot starting time or suggested time slot is greater than booked time slot starting time
                        if (strtotime($next_day_array_of_time[$j]) < strtotime($next_day_booked_slots[$i]['starting_time']) || strtotime($next_day_array_of_time[$j]) >= strtotime($next_day_booked_slots[$i]['ending_time'])) {
                            //check if suggested time slot is not  in array of avaialble slot
                            if (!in_array($next_day_array_of_time[$j], $next_day_available_slots)) {
                                // echo $next_day_array_of_time[$j]."--suggested slot is adding in avaiable slot<br/>";
                                $next_day_available_slots[] = $next_day_array_of_time[$j];
                            } else {
                                // echo $next_day_array_of_time[$j]."--suggested slot is adding in busy slot 1<br/>";
                                // if (!in_array($next_day_array_of_time[$j], $next_day_busy_slots)) {
                                //     $next_day_busy_slots[] = $next_day_array_of_time[$j];
                                // }
                            }
                        } else {
                            // echo $next_day_array_of_time[$j]."--suggested slot is adding in busy slot 2<br/>";
                            if (!in_array($next_day_array_of_time[$j], $next_day_busy_slots)) {
                                $next_day_busy_slots[] = $next_day_array_of_time[$j];
                            }
                        }
                    }
                    $count_next_busy_slots = count($next_day_busy_slots);
                    for ($k = 0; $k < $count_next_busy_slots; $k++) {
                        if (($key = array_search($next_day_busy_slots[$k], $next_day_available_slots)) !== false) {
                            unset($next_day_available_slots[$key]);
                        }
                    }
                }
            } else {
                //loop on suggested time slots
                for ($j = 0; $j < count($next_day_array_of_time); $j++) {
                    //check if suggested time slot is not  in array of avaialble slot
                    if (!in_array($next_day_array_of_time[$j], $next_day_available_slots)) {
                        //if suggested time slot is grater than current time and current date and booked date are not same then to available slot array otherwise busy slot array
                        if (strtotime($next_day_date) != strtotime($current_date)) {
                            $next_day_available_slots[] = $next_day_array_of_time[$j];
                        } else {
                            if (!in_array($next_day_array_of_time[$j], $next_day_busy_slots)) {
                                $next_day_busy_slots[] = $next_day_array_of_time[$j];
                            }
                        }
                    }
                }
                $count_next_busy_slots = count($next_day_busy_slots);
                for ($k = 0; $k < $count_next_busy_slots; $k++) {
                    if (($key = array_search($next_day_busy_slots[$k], $next_day_available_slots)) !== false) {
                        unset($next_day_available_slots[$key]);
                    }
                }
            }
            $next_day_available_slots = array_values($next_day_available_slots);
            $all_continuos_slot = calculate_continuous_slots($next_day_available_slots);
            $response['error'] = false;
            $response['available_slots'] = $next_day_available_slots;
            $response['busy_slots'] = $next_day_busy_slots;
            $response['continous_available_slots'] = $all_continuos_slot;
            return $response;
        } else {
            //loop on suggested time slots
            for ($j = 0; $j < count($next_day_array_of_time); $j++) {
                //check if suggested time slot is not  in array of avaialble slot
                if (!in_array($next_day_array_of_time[$j], $next_day_available_slots)) {
                    //if suggested time slot is grater than current time and current date and booked date are not same then to available slot array otherwise busy slot array
                    if (strtotime($next_day_date) != strtotime($current_date)) {
                        $next_day_available_slots[] = $next_day_array_of_time[$j];
                    } else {
                        if (!in_array($next_day_array_of_time[$j], $next_day_busy_slots)) {
                            $next_day_busy_slots[] = $next_day_array_of_time[$j];
                        }
                    }
                }
            }
            $count_next_busy_slots = count($next_day_busy_slots);
            for ($k = 0; $k < $count_next_busy_slots; $k++) {
                if (($key = array_search($next_day_busy_slots[$k], $next_day_available_slots)) !== false) {
                    unset($next_day_available_slots[$key]);
                }
            }
            $next_day_available_slots = array_values($next_day_available_slots);
            $all_continuos_slot = calculate_continuous_slots($next_day_available_slots);
            $response['error'] = false;
            $response['available_slots'] = $next_day_available_slots;
            $response['busy_slots'] = $next_day_busy_slots;
            $response['continous_available_slots'] = $all_continuos_slot;
            return $response;
        }
    } else {
        $response['error'] = false;
        $response['available_slots'] = [];
        $response['busy_slots'] = [];
        $response['continous_available_slots'] = [];
        return $response;
    }
}
function calculate_continuous_slots($array_of_time)
{
    $available_slots = array_values($array_of_time);
    // creating chunks of countinuos time slots from available time slots
    $all_continous_slot = [];
    $continous_slot_number = 0;
    for ($i = 0; $i <= count($available_slots) - 1; $i++) {
        //here we add 30 minutes to  available time slot 
        $next_expected_time_slot = date("H:i:s", strtotime('+30 minutes', strtotime($available_slots[$i])));
        //here we check avaialable slot + 1  means if avaialbe slot is 9:00 then available slot +1 is 9:30 is same as expected time slot if yes then add to continue slot 
        // if (($available_slots[$i + 1] == $next_expected_time_slot)) {
        if (isset($available_slots[$i + 1]) && ($available_slots[$i + 1] == $next_expected_time_slot)) {
            $all_continous_slot[$continous_slot_number][] = $available_slots[$i];
            if (count($available_slots) == $i) {
                $all_continous_slot[$continous_slot_number][] = $available_slots[$i];
            }
        } else {
            $all_continous_slot[$continous_slot_number][] = $available_slots[$i];
            $continous_slot_number++;
        }
    }
    return $all_continous_slot;
}
function get_slot_for_place_order($partnerId, $date_of_service, $required_duration, $starting_time)
{
    
    // $day = date('l', strtotime($starting_time));
    $day = date('l', strtotime($date_of_service));
    $current_date = date('Y-m-d');
    $timings = getTimingOfDay($partnerId, $day);
    $response = [];
    if (isset($timings) && !empty($timings)) {
        $provider_closing_time = date('H:i:s', strtotime($timings['closing_time']));
        $expoloed_start_time = explode(':', $starting_time);
        $remaining_duration = $required_duration;
        $extra_minutes = '';
        if (($expoloed_start_time[1] > 15 && $expoloed_start_time[1] <= 30) || ($expoloed_start_time[1] > 45 && $expoloed_start_time[1] > 30)) {
            $rounded = date('H:i:s', ceil(strtotime($starting_time) / 1800) * 1800);
            $differenceBetweenRoundedTime = round(abs(strtotime($rounded) - strtotime($starting_time)) / 60, 2);
            $extra_minutes = 'deduct';
        } else {
            $rounded = date('H:i:s', floor(strtotime($starting_time) / 1800) * 1800);
            $differenceBetweenRoundedTime = round(abs(strtotime($starting_time) -  strtotime($rounded)) / 60, 2);
            $extra_minutes = 'add';
        }
        $time_slots = get_available_slots_without_processing($partnerId, $date_of_service, $required_duration, $rounded); //working
        if (!isset($time_slots['available_slots'][0])) {
            $response['suborder'] = false;
            $response['slot_avaialble'] = false;
            return $response;
        }
        $array_of_time = $time_slots['available_slots'][0];
        $array_of_time = array_values($array_of_time);
        if ($array_of_time[0] == $rounded) {
            $next_expected_time_slot = $rounded;
            foreach ($array_of_time as $row) {
                if ($row == $next_expected_time_slot && ($row < $provider_closing_time)) {
                    // print_R("row-- ".$row."</br>");
                    $next_expected_time_slot = date("H:i:s", strtotime('+30 minutes', strtotime($row)));
                    //  print_R("next slot -- ".$next_expected_time_slot."</br>");
                    $remaining_duration = $remaining_duration - 30;
                    // print_R("remaining duration -- ".$remaining_duration."</br>");
                }
            }
            if ($extra_minutes == "add") {
                $remaining_duration += $differenceBetweenRoundedTime;
            } else if ($extra_minutes == "deduct") {
                $remaining_duration -= $differenceBetweenRoundedTime;
            }
            // die;
            if ($remaining_duration <= 0) {
                $response['suborder'] = false;
                $response['slot_avaialble'] = true;
                $response['order_data'] =  $time_slots['available_slots'][0];
            } else {
                $next_day_slots = get_next_days_slots($provider_closing_time, $date_of_service, $partnerId, $required_duration, $current_date);
                $next_day_available_slots = $next_day_slots['available_slots'];
                if ((sizeof($next_day_available_slots) * 30) >= $remaining_duration) {
                    $response['suborder'] = true;
                    $response['suborder_data'] = $next_day_available_slots;
                    $response['order_data'] =  $time_slots['available_slots'][0];
                    $response['slot_avaialble'] = true;
                } else {
                    $response['suborder'] = false;
                    $response['slot_avaialble'] = false;
                }
            }
        } else {
            $response['suborder'] = false;
            $response['slot_avaialble'] = false;
        }
    } else {
        $response['closed'] = "true";
        $response['suborder'] = false;
        $response['slot_avaialble'] = false;
    }
    return $response;
}
function get_service_ratings($service_id)
{
    $db = \config\Database::connect();
    $builder = $db
        ->table('services s')
        ->select("
                COUNT(sr.rating) as total_ratings,
                SUM( CASE WHEN sr.rating = ceil(5) THEN 1 ELSE 0 END) as rating_5,
                SUM( CASE WHEN sr.rating = ceil(4) THEN 1 ELSE 0 END) as rating_4,
                SUM( CASE WHEN sr.rating = ceil(3) THEN 1 ELSE 0 END) as rating_3,
                SUM( CASE WHEN sr.rating = ceil(2) THEN 1 ELSE 0 END) as rating_2,
                SUM( CASE WHEN sr.rating = ceil(1) THEN 1 ELSE 0 END) as rating_1
            ")
        ->join('services_ratings sr', 'sr.service_id = s.id')
        ->where('sr.service_id', $service_id)
        ->get()->getResultArray();
    // print_r($builder);
    return $builder;
}
function calculate_subscription_price($subcription_id)
{
    $subscription_details = fetch_details('subscriptions', ['id' => $subcription_id]);
    $taxPercentageData = fetch_details('taxes', ['id' => $subscription_details[0]['tax_id']], ['percentage']);
    if (!empty($taxPercentageData)) {
        $taxPercentage = $taxPercentageData[0]['percentage'];
    } else {
        $taxPercentage = 0;
    }
    $subscription_details[0]['tax_percentage'] = $taxPercentage;
    if ($subscription_details[0]['discount_price'] == "0") {
        if ($subscription_details[0]['tax_type'] == "excluded") {
            $subscription_details[0]['tax_value'] = number_format((intval(($subscription_details[0]['price'] * ($taxPercentage) / 100))), 2);
            $subscription_details[0]['price_with_tax']  = strval($subscription_details[0]['price'] + ($subscription_details[0]['price'] * ($taxPercentage) / 100));
            $subscription_details[0]['original_price_with_tax'] = strval($subscription_details[0]['price'] + ($subscription_details[0]['price'] * ($taxPercentage) / 100));
        } else {
            $subscription_details[0]['tax_value'] = "";
            $subscription_details[0]['price_with_tax']  = strval($subscription_details[0]['price']);
            $subscription_details[0]['original_price_with_tax'] = strval($subscription_details[0]['price']);
        }
    } else {
        if ($subscription_details[0]['tax_type'] == "excluded") {
            $subscription_details[0]['tax_value'] = number_format((intval(($subscription_details[0]['discount_price'] * ($taxPercentage) / 100))), 2);
            $subscription_details[0]['price_with_tax']  = strval($subscription_details[0]['discount_price'] + ($subscription_details[0]['discount_price'] * ($taxPercentage) / 100));
            $subscription_details[0]['original_price_with_tax'] = strval($subscription_details[0]['price'] + ($subscription_details[0]['discount_price'] * ($taxPercentage) / 100));
        } else {
            $subscription_details[0]['tax_value'] = "";
            $subscription_details[0]['price_with_tax']  = strval($subscription_details[0]['discount_price']);
            $subscription_details[0]['original_price_with_tax'] = strval($subscription_details[0]['price']);
        }
    }
    return $subscription_details;
}
function calculate_partner_subscription_price($partner_id, $subscription_id, $id)
{
    $partner_subscriptions = fetch_details('partner_subscriptions', ['partner_id' => $partner_id, 'subscription_id' => $subscription_id, 'id' => $id]);
    $taxPercentage = $partner_subscriptions[0]['tax_percentage'];
    $partner_subscriptions[0]['tax_percentage'] = $taxPercentage;
    if ($partner_subscriptions[0]['discount_price'] == "0") {
        if ($partner_subscriptions[0]['tax_type'] == "excluded") {
            $partner_subscriptions[0]['tax_value'] = number_format((intval(($partner_subscriptions[0]['price'] * ($taxPercentage) / 100))), 2);
            $partner_subscriptions[0]['price_with_tax']  = strval($partner_subscriptions[0]['price'] + ($partner_subscriptions[0]['price'] * ($taxPercentage) / 100));
            $partner_subscriptions[0]['original_price_with_tax'] = strval($partner_subscriptions[0]['price'] + ($partner_subscriptions[0]['price'] * ($taxPercentage) / 100));
        } else {
            $partner_subscriptions[0]['tax_value'] = "";
            $partner_subscriptions[0]['price_with_tax']  = strval($partner_subscriptions[0]['price']);
            $partner_subscriptions[0]['original_price_with_tax'] = strval($partner_subscriptions[0]['price']);
        }
    } else {
        if ($partner_subscriptions[0]['tax_type'] == "excluded") {
            $partner_subscriptions[0]['tax_value'] = number_format((intval(($partner_subscriptions[0]['discount_price'] * ($taxPercentage) / 100))), 2);
            $partner_subscriptions[0]['price_with_tax']  = strval($partner_subscriptions[0]['discount_price'] + ($partner_subscriptions[0]['discount_price'] * ($taxPercentage) / 100));
            $partner_subscriptions[0]['original_price_with_tax'] = strval($partner_subscriptions[0]['price'] + ($partner_subscriptions[0]['discount_price'] * ($taxPercentage) / 100));
        } else {
            $partner_subscriptions[0]['tax_value'] = "";
            $partner_subscriptions[0]['price_with_tax']  = strval($partner_subscriptions[0]['discount_price']);
            $partner_subscriptions[0]['original_price_with_tax'] = strval($partner_subscriptions[0]['price']);
        }
    }
    return $partner_subscriptions;
}
function add_subscription($subscription_id, $partner_id, $insert_id = null)
{
    $settings = get_settings('general_settings', true);
    date_default_timezone_set($settings['system_timezone']); // Added user timezone
    $subscription_details = fetch_details('subscriptions', ['id' => $subscription_id]);
    if ($subscription_details[0]['price'] == "0") {
        $price = calculate_subscription_price($subscription_details[0]['id']);;
        $purchaseDate = date('Y-m-d');
        $subscriptionDuration = $subscription_details[0]['duration'];
        if ($subscriptionDuration == "unlimited") {
            $subscriptionDuration = 0;
        }
        $expiryDate = date('Y-m-d', strtotime($purchaseDate . ' + ' . $subscriptionDuration . ' days'));
        $partner_subscriptions = [
            'partner_id' =>  $partner_id,
            'subscription_id' => $subscription_id,
            'is_payment' => "1",
            'status' => "active",
            'purchase_date' => date('Y-m-d'),
            'expiry_date' =>  $expiryDate,
            'name' => $subscription_details[0]['name'],
            'description' => $subscription_details[0]['description'],
            'duration' => $subscription_details[0]['duration'],
            'price' => $subscription_details[0]['price'],
            'discount_price' => $subscription_details[0]['discount_price'],
            'publish' => $subscription_details[0]['publish'],
            'order_type' => $subscription_details[0]['order_type'],
            'max_order_limit' => $subscription_details[0]['max_order_limit'],
            'service_type' => $subscription_details[0]['service_type'],
            'max_service_limit' => $subscription_details[0]['max_service_limit'],
            'tax_type' => $subscription_details[0]['tax_type'],
            'tax_id' => $subscription_details[0]['tax_id'],
            'is_commision' => $subscription_details[0]['is_commision'],
            'commission_threshold' => $subscription_details[0]['commission_threshold'],
            'commission_percentage' => $subscription_details[0]['commission_percentage'],
            'transaction_id' => '0',
            'tax_percentage' => $price[0]['tax_percentage'],
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ];
        $data = insert_details($partner_subscriptions, 'partner_subscriptions');
        $inserted_subscription = fetch_details('partner_subscriptions', ['id' => $data['id']]);
        if ($inserted_subscription[0]['is_commision'] == "yes") {
            $commission = $inserted_subscription[0]['commission_percentage'];
        } else {
            $commission = 0;
        }
        update_details(['admin_commission' => $commission], ['partner_id' => $partner_id], 'partner_details');
        return true;
    } else {
        if ($subscription_details[0]['is_commision'] == "yes") {
            $commission = $subscription_details[0]['commission_percentage'];
        } else {
            $commission = 0;
        }
        update_details(['admin_commission' => $commission], ['partner_id' => $partner_id], 'partner_details');
        $details_for_subscription = fetch_details('subscriptions', ['id' => $subscription_id]);
        $subscriptionDuration = $details_for_subscription[0]['duration'];
        // Calculate the expiry date based on the current date and subscription duration
        $purchaseDate = date('Y-m-d'); // Get the current date
        if ($subscriptionDuration == "unlimited") {
            $subscriptionDuration = 0;
        }
        $expiryDate = date('Y-m-d', strtotime($purchaseDate . ' + ' . $subscriptionDuration . ' days')); // Add the duration to the purchase date
        $taxPercentageData = fetch_details('taxes', ['id' => $details_for_subscription[0]['tax_id']], ['percentage']);
        if (!empty($taxPercentageData)) {
            $taxPercentage = $taxPercentageData[0]['percentage'];
        } else {
            $taxPercentage = 0;
        }
        $partner_subscriptions = [
            'partner_id' =>  $partner_id,
            'subscription_id' => $subscription_id,
            'is_payment' => "0",
            'status' => "pending",
            'purchase_date' => $purchaseDate,
            'expiry_date' => $expiryDate,
            'name' => $details_for_subscription[0]['name'],
            'description' => $details_for_subscription[0]['description'],
            'duration' => $details_for_subscription[0]['duration'],
            'price' => $details_for_subscription[0]['price'],
            'discount_price' => $details_for_subscription[0]['discount_price'],
            'publish' => $details_for_subscription[0]['publish'],
            'order_type' => $details_for_subscription[0]['order_type'],
            'max_order_limit' => $details_for_subscription[0]['max_order_limit'],
            'service_type' => $details_for_subscription[0]['service_type'],
            'max_service_limit' => $details_for_subscription[0]['max_service_limit'],
            'tax_type' => $details_for_subscription[0]['tax_type'],
            'tax_id' => $details_for_subscription[0]['tax_id'],
            'is_commision' => $details_for_subscription[0]['is_commision'],
            'commission_threshold' => $details_for_subscription[0]['commission_threshold'],
            'commission_percentage' => $details_for_subscription[0]['commission_percentage'],
            'transaction_id' => $insert_id,
            'tax_percentage' => $taxPercentage,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ];
        insert_details($partner_subscriptions, 'partner_subscriptions');
        return true;
    }
}
if (!function_exists('format_date')) {
    function format_date($dateString, $format = 'Y-m-d H:i:s')
    {
        $date = date_create($dateString);
        return date_format($date, $format);
    }
}
function uploadFile($request, $fieldName, $uploadPath, &$updatedData, $data)
{
    $file = $request->getFile($fieldName);
    if ($file->isValid()) {
        $newName = $file->getRandomName();
        $file->move($uploadPath, $newName);
        $updatedData[$fieldName] = $newName;
    } else {
        $updatedData[$fieldName] = isset($data[$fieldName]) ? $data[$fieldName] : "";
    }
}
function verify_transaction($order_id)
{
    $transaction = fetch_details('transactions', ['order_id' => $order_id]);
    if (!empty($transaction)) {
        if ($transaction[0]['type'] == "razorpay") {
            $razorpay = new Razorpay;
            $credentials = $razorpay->get_credentials();
            $secret = $credentials['secret'];
            $api = new Api($credentials['key'], $secret);
            $payment = $api->payment->fetch($transaction[0]['txn_id']);
            $status = $payment->status;
            if ($status != "captured") {
                update_details(['payment_status' => '1'], ['id' => $order_id], 'orders');
                $response['error'] = false;
                $response['message'] = 'Verified Successfully';
            } else if ($status != "captured") {
                update_details(['status' => 'cancelled'], ['id' => $order_id], 'orders');
                $response['error'] = true;
                $response['message'] = 'Booking is cancelled due to pending payment .';
            }
        } elseif ($transaction[0]['type'] == "stripe") {
            $settings = get_settings('payment_gateways_settings', true);
            $secret_key = isset($settings['stripe_secret_key']) ? $settings['stripe_secret_key'] : "sk_test_51LERZeSCiHzi4IW1hODcT6ngl88bSZzN4SHqH58CFKJ7eEQKSzniJTXgVNXFQPXuKfu9pAOYVMOe6UeE2q7hY5J400qllsvrye";
            $http = service('curlrequest');
            $http->setHeader('Authorization', 'Bearer ' . $secret_key);
            $http->setHeader('Content-Type', 'application/x-www-form-urlencoded');
            $response = $http->get("https://api.stripe.com/v1/payment_intents/{$transaction[0]['txn_id']}");
            $responseData = json_decode($response->getBody(), true);
            $statusOfTransaction = $responseData['status'];
            if ($statusOfTransaction == "succeeded") {
                update_details(['payment_status' => '1'], ['id' => $order_id], 'orders');
                $response['error'] = false;
                $response['message'] = 'Verified Successfully';
            } else if ($statusOfTransaction != "succeeded") {
                update_details(['status' => 'cancelled'], ['id' => $order_id], 'orders');
                $response['error'] = true;
                $response['message'] = 'Booking is cancelled due to pending payment .';
            }
        } else if ($transaction[0]['type'] = "paystack") {
            $paystack = new Paystack();
            $payment = $paystack->verify_transation($transaction[0]['reference']);
            $message = json_decode($payment, true);
            if ($message['status'] == "1" || $message['status'] == "success") {
                update_details(['payment_status' => '1'], ['id' => $order_id], 'orders');
                $response['error'] = false;
                $response['message'] = 'Verified Successfully';
            } else if ($message['status'] != "1" || $message['status'] != "success") {
                update_details(['status' => 'cancelled'], ['id' => $order_id], 'orders');
                $response['error'] = true;
                $response['message'] = 'Booking is cancelled due to pending payment .';
            }
        }
        return $response;
    }
}
function create_stripe_payment_intent()
{
    $settings = get_settings('payment_gateways_settings', true);
    $secret_key = $settings['stripe_secret_key'] ?? "sk_test_51LERZeSCiHzi4IW1hODcT6ngl88bSZzN4SHqH58CFKJ7eEQKSzniJTXgVNXFQPXuKfu9pAOYVMOe6UeE2q7hY5J400qllsvrye";
    $data = [
        'amount' => 100,
        'currency' => 'usd',
        'description' => 'Test',
        'payment_method_types' => ['card'],
        'metadata' => [
            'user_id' => 1,
            'competition_id' => 1,
        ],
        'shipping' => [
            'name' => 'TEST',
            'address' => [
                'country' => "in",
            ],
        ],
    ];
    $body = http_build_query($data);
    $response = \Config\Services::curlrequest()
        ->setHeader('Authorization', 'Bearer ' . $secret_key)
        ->setHeader('Content-Type', 'application/x-www-form-urlencoded')
        ->setBody($body)
        ->post('https://api.stripe.com/v1/payment_intents');
    $responseData = json_decode($response->getBody(), true);
    return $responseData;
}
function razorpay_create_order_for_place_order($order_id)
{
    $order_id = $order_id;
    if ($order_id && !empty($order_id)) {
        $where['o.id'] = $order_id;
    }
    $orders = new Orders_model();
    $order_detail = $orders->list(true, "", null, null, "", "", $where);
    $settings = get_settings('payment_gateways_settings', true);
    if (!empty($order_detail) && !empty($settings)) {
        $currency = $settings['razorpay_currency'];
        $price = $order_detail['data'][0]['final_total'];
        $amount = intval($price * 100);
        $razorpay = new Razorpay();
        $create_order = $razorpay->create_order($amount, $order_id, $currency);
        if (!empty($create_order)) {
            $response = [
                'error' => false,
                'message' => 'razorpay order created',
                'data' => $create_order,
            ];
        } else {
            $response = [
                'error' => true,
                'message' => 'razorpay order not created',
                'data' => [],
            ];
        }
    } else {
        $response = [
            'error' => true,
            'message' => 'details not found"',
            'data' => [],
        ];
    }
    return $response;
}
function create_order_paypal_for_place_order()
{
    $clientId = '123';
    $secret = '123';
    // Step 1: Generate a new access token
    $clientId = '123';
    $secret = '123';
    $client = new Client();
    try {
        $tokenResponse = $client->request('POST', 'https://api-m.sandbox.paypal.com/v1/oauth2/token', [
            'form_params' => [
                'grant_type' => 'client_credentials',
            ],
            'auth' => [$clientId, $secret],
        ]);
        $tokenData = json_decode($tokenResponse->getBody(), true);
        $accessToken = $tokenData['access_token'];
        // Step 2: Make the API request with the new access token
        $uri = 'https://api-m.sandbox.paypal.com/v2/checkout/orders';
        $payload = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => '100.00',
                    ]
                ]
            ],
        ];
        $response = $client->request('POST', $uri, [
            'json' => $payload,
            'headers' => [
                'Accept' => 'application/json',
                'Accept-Language' => 'en_US',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ]);
        $data = json_decode($response->getBody(), true);
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    } catch (ClientException $e) {
        // Handle 401 Unauthorized error
        echo 'Error Code: ' . $e->getCode() . "\n";
        echo 'Error Message: ' . $e->getMessage() . "\n";
        // You can also get detailed error response from $e->getResponse()
    }
}
function add_settlement_cashcollection_history($message, $type, $date, $time, $amount, $provider_id = null, $order_id = null, $payment_request_id = null, $commission_percentage = null, $total_amount = null, $commision_amount = null)
{
    $settlement_cashcollection_history = [
        'provider_id' => $provider_id,
        'order_id' => $order_id,
        'payment_request_id' => $payment_request_id,
        'commission_percentage' => $commission_percentage,
        'message' => $message,
        'type' => $type,
        'date' => $date,
        'time' => $time,
        'amount' => $amount,
        'total_amount' => $total_amount,
        'commission_amount' => $commision_amount,
    ];
    insert_details($settlement_cashcollection_history, 'settlement_cashcollection_history');
}
function partner_settlement_and_cash_collection_history_status($status, $panel_type)
{
    $value = '';
    if ($panel_type == "admin") {
        if ($status == "cash_collection_by_provider") {
            $value = "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-danger text-emerald-danger dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3 mx-5'>" . labels('debit', 'Debit') . "
            </div>";
        } else if ($status == "cash_collection_by_admin") {
            $value = "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-success text-emerald-success dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3 mx-5'>" . labels('credit', 'Credit') . "
            </div>";
        } else if ($status == "received_by_admin") {
            $value = "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-success text-emerald-success dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3 mx-5'>" . labels('credit', 'Credit') . "
            </div>";
        } else if ($status == "settled_by_settlement") {
            $value = "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-danger text-emerald-danger dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3 mx-5'>" . labels('debit', 'Debit') . "
        </div>";
        } else if ($status == "settled_by_payment_request") {
            $value = "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-danger text-emerald-danger dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3 mx-5'>" . labels('debit', 'Debit') . "
        </div>";
        }
    } else if ($panel_type == "provider") {
        if ($status == "cash_collection_by_provider") {
            $value = "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-success text-emerald-success dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3 mx-5'>" . labels('credit', 'Credit') . "
            </div>";
        } else if ($status == "cash_collection_by_admin") {
            $value = "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-danger text-emerald-danger dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3 mx-5'>" . labels('debit', 'Debit') . "
            </div>";
        } else if ($status == "received_by_admin") {
            $value = "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-danger text-emerald-danger dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3 mx-5'>" . labels('debit', 'Debit') . "
        </div>";
        } else if ($status == "settled_by_settlement") {
            $value = "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-success text-emerald-success dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3 mx-5'>" . labels('credit', 'Credit') . "
            </div>";
        } else if ($status == "settled_by_payment_request") {
            $value = "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-success text-emerald-success dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3 mx-5'>" . labels('credit', 'Credit') . "
            </div>";
        }
    }
    return $value;
}
function partner_settlement_and_cash_collection_history_type($type)
{
    if ($type == "cash_collection_by_provider") {
        $value = "Cash Collection By Provider";
    } else if ($type == "cash_collection_by_admin") {
        $value = "Cash Collection By Admin";
    } else if ($type == "received_by_admin") {
        $value = "Received By Admin";
    } else if ($type == "settled_by_settlement") {
        $value = "Settled By settlement";
    } else if ($type == "settled_by_payment_request") {
        $value = "Settled By Payment Request";
    }
    return $value;
}
function fetch_chat_ids($table, $type, $where = [], $fields = [], $limit = "", $offset = '0', $sort = 'id', $order = 'DESC', $or_like = [],)
{
    $db = \Config\Database::connect();
    $builder = $db->table($table);
    if (!empty($fields)) {
        $builder->select($fields);
    }
    if (!empty($where)) {
        $builder->where($where);
    }
    if (!empty($or_like)) {
        $builder->groupStart();
        foreach ($or_like as $field => $values) {
            $builder->whereIn($field, $values);
        }
        $builder->groupEnd();
    }
    $builder->orderBy($sort, $order);
    if (!empty($limit)) {
        $builder->limit($limit, $offset);
    }
    $query = $builder->get();
    if ($type == "customer") {
        $ids = [];
        foreach ($query->getResultArray() as $row) {
            $ids[] = $row['customer_id'];
        }
    } else if ($type == "provider") {
        $ids = [];
        foreach ($query->getResultArray() as $row) {
            $ids[] = $row['provider_id'];
        }
    }
    return $ids;
}
function add_enquiry_for_chat($user_type, $enquiry_user_id, $for_booking = false, $booking_id = null)
{
    $user_type = $user_type;
    if ($user_type == "provider") {
        $enquiry_field = 'provider_id';
    } else if ($user_type == "customer") {
        $enquiry_field = 'customer_id';
    }
    if ($for_booking && $user_type == "customer") {
        $is_already_exist_query = fetch_details('enquiries', [$enquiry_field => $enquiry_user_id, 'booking_id' => $booking_id]);
    } else {
        $is_already_exist_query = fetch_details('enquiries', [$enquiry_field => $enquiry_user_id, 'booking_id' => $booking_id]);
    }
    if (empty($is_already_exist_query)) {
        $user = fetch_details('users', ['id' => $enquiry_user_id])[0];
        $data['title'] =  $user['username'] . '_query';
        $data['status'] =  1;
        if ($user_type == "provider") {
            $data['userType'] =  1;
            $data['provider_id'] = $enquiry_user_id;
        } else if ($user_type == "customer") {
            $data['userType'] =  2;
            $data['customer_id'] = $enquiry_user_id;
        }
        if ($for_booking && $user_type == "customer") {
            $data['booking_id'] = $booking_id;
        }
        $data['date'] =  now();
        $store = insert_details($data, 'enquiries');
        $e_id = $store['id'];
    } else {
        $e_id = $is_already_exist_query[0]['id'];
    }
    return $e_id;
}
function insert_chat_message_for_chat($sender_id, $receiver_id, $message, $e_id, $sender_type, $receiver_type, $created_at, $upload_attachment = false, array $file = null, $booking_id = null)
{
    $data = [
        'sender_id' => $sender_id,
        'receiver_id' => $receiver_id,
        'message' => $message,
        'e_id' => $e_id,
        'sender_type' => $sender_type,
        'receiver_type' => $receiver_type,
        'created_at' => $created_at,
        'booking_id' => $booking_id
    ];
    $path = './public/uploads/chat_attachment/';
    $uploaded_files = [];
    if ($upload_attachment && !empty($file)) {
        if (!is_dir($path)) {
            mkdir($path, 0775, true);
        }
        foreach ($file['tmp_name'] as $key => $tmp_name) {
            $file_type = $file['type'][$key];
            $file_size = $file['size'][$key];
            $file_name = $file['name'][$key];
            $uploadedFile = new UploadedFile(
                $file['tmp_name'][$key],
                $file['name'][$key],
                $file['type'][$key],
                $file['size'][$key],
                $file['error'][$key]
            );
            $result = upload_file($uploadedFile, 'public/uploads/chat_attachment', "Error creating chat attachements", 'chat_attachment');
            if ($result['disk'] == "local_server") {
                $file_name = $path . $result['file_name'];
            } else if ($result['disk'] == "aws_s3") {
                $file_name = $result['file_name'];
            }
            $uploaded_files[] = ['file' => $file_name, 'file_type' => $file_type, 'file_size' => $file_size, 'file_name' => $result['file_name']];
        }
    }
    $disk = fetch_current_file_manager();
    $data['file'] = json_encode($uploaded_files);
    $chat_message = insert_details($data, 'chats');
    $db = \Config\Database::connect();
    $builder = $db->table('chats c');
    $builder->select('c.*,u.username,u.image,u.id as user_id')
        ->join('users u', 'u.id = c.sender_id')
        ->where(['c.id' =>  $chat_message['id']]);
    $chat = $builder->get()->getResultArray();
    if (!empty($chat)) {
        if (!empty($chat[0]['file'])) {
            $decodedFiles = json_decode($chat[0]['file'], true); // Decode the JSON string to an array
            $chat[0]['file'] = []; // Initialize the array to store the transformed data

            foreach ($decodedFiles as $data_file) {
                if ($disk == "local_server") {
                    $file = base_url('public/uploads/chat_attachment/' . $data_file['file']);
                } else if ($disk == "aws_s3") {
                    $file = fetch_cloud_front_url('chat_attachment', $data_file['file']);
                } else {
                    $file = base_url('public/uploads/chat_attachment/' . $data_file['file']);
                }
                $chat[0]['file'][] = [
                    'file' => $file,
                    'file_type' => $data_file['file_type'],
                    'file_size' => $data_file['file_size'],
                    'file_name' => $data_file['file_name']
                ];
            }
        } else {
            $chat[0]['file'] = is_array($chat[0]['file']) ? [] : "";
        }
        if (isset($chat[0]['image'])) {
            $imagePath = $chat[0]['image'];
            if ($disk == "local_server") {
                $chat[0]['profile_image'] = fix_provider_path($imagePath);
            } else if ($disk == "aws_s3") {
                $chat[0]['profile_image'] = fetch_cloud_front_url('profile', $chat[0]['image']);
            } else {
                $chat[0]['profile_image'] = fix_provider_path($imagePath);
            }
        }
        $chat_last_message_date = fetch_details('chats', ['e_id' => $chat[0]['e_id']], ['id', 'created_at'], 1, 0, 'created_at', 'DESC');
        if (!empty($chat_last_message_date)) {
            $last_date = $chat_last_message_date[0]['created_at'];
        } else {
            $last_date = now();
        }
        $chat[0]['last_message_date'] = $last_date;
    }
    return $chat[0];
}
function fix_provider_path($imagePath)
{
    $image = "";
    if (empty($imagePath) || $imagePath == NULL) {
        return $image;
    }
    if (strpos($imagePath, '/public/backend/assets/profiles/') === 0) {
        $image = $imagePath;
    } elseif (file_exists(FCPATH . 'public/backend/assets/profiles/' . $imagePath)) {
        $image = base_url('public/backend/assets/profiles/' . $imagePath);
    } else {
        $image = base_url($imagePath);
    }
    if (empty($image)) {
        $image  = base_url('public/backend/assets/profiles/default.png');
    }
    return $image;
}
function getSenderReceiverDataForChatNotification($sender_id, $receiver_id, $chat_id, $last_chat_date, $view_user_type, $when_customer_is_receiver = null)
{
    $db = \Config\Database::connect();
    $disk = fetch_current_file_manager();

    if ($view_user_type == "admin") {

        $receiver_details = $db->table('users u')->select('u.id,u.image,u.username')->where('u.id', $receiver_id)->get()->getResultArray()[0];
        if (isset($receiver_details['image'])) {
            if ($disk == "local_server") {

                $imagePath = $receiver_details['image'];
                $receiver_details['image'] = fix_provider_path($imagePath) ?? "";
            } else if ($disk == 'aws_s3') {
                $receiver_details['image'] = fetch_cloud_front_url('profile', $receiver_details['image']);
            } else {
                $imagePath = $receiver_details['image'];
                $receiver_details['image'] = fix_provider_path($imagePath) ?? "";
            }
        } else {
            $receiver_details['image'] = base_url("/public/backend/assets/profiles/default.png");
        }
    } else if ($view_user_type == "provider" || $view_user_type == "provider_booking") {


        if ($when_customer_is_receiver == "yes") {
            $receiver_details = $db->table('users u')->select('u.id,u.image,u.username')->where('u.id', $receiver_id)->get()->getResultArray()[0];
        } else {
            $receiver_details = $db->table('users u')->select('u.id,u.image,pd.company_name as username')->where('u.id', $receiver_id)->join('partner_details pd', 'pd.partner_id = u.id')->get()->getResultArray()[0];
        }


        if (isset($receiver_details['image'])) {
            if ($disk == "local_server") {

                $imagePath = $receiver_details['image'];
                $receiver_details['image'] = fix_provider_path($imagePath) ?? "";
            } else if ($disk == 'aws_s3') {
                $receiver_details['image'] = fetch_cloud_front_url('profile', $receiver_details['image']);
            } else {

                $imagePath = $receiver_details['image'];
                $receiver_details['image'] = fix_provider_path($imagePath) ?? "";
            }
        } else {
            $receiver_details['image'] = base_url("/public/backend/assets/profiles/default.png");
        }
    }
    $sender_details = fetch_details('users', ['id' => $sender_id], ['id', 'username', 'image'])[0];
    if (isset($sender_details['image'])) {
        if ($disk == "local_server") {

            $imagePath = $sender_details['image'];
            $sender_details['image'] = fix_provider_path($imagePath) ?? "";
        } else if ($disk == 'aws_s3') {
            $sender_details['image'] = fetch_cloud_front_url('profile', $sender_details['image']);
        } else {

            $sender_details['image'] = fix_provider_path($sender_details['image']) ?? "";
        }
    } else {
        $sender_details['image'] = base_url("/public/backend/assets/profiles/default.png");
    }
    $builder = $db->table('chats c');
    $builder->select('c.*,u.username,u.image,u.id as user_id')
        ->join('users u', 'u.id = c.sender_id')
        ->where(['c.id' =>  $chat_id]);
    $chat = $builder->get()->getResultArray();
    if (!empty($chat)) {
        if (!empty($chat[0]['file'])) {
            $decodedFiles = json_decode($chat[0]['file'], true); // Decode the JSON string into an array
            $chat[0]['file'] = []; // Initialize the array to store the formatted data

            foreach ($decodedFiles as $data) {
                if ($disk == "local_server") {
                    $file = base_url('public/uploads/chat_attachment/' . $data['file']);
                } else if ($disk == "aws_s3") {
                    $file = fetch_cloud_front_url('chat_attachment', $data['file']);
                } else {
                    $file = base_url('public/uploads/chat_attachment/' . $data['file']);
                }
                $chat[0]['file'][] = [
                    'file' => $file,
                    'file_type' => $data['file_type'],
                    'file_name' => $data['file_name'],
                    'file_size' => $data['file_size'],
                ];
            }
        } else {
            $chat[0]['file'] = is_array($chat[0]['file']) ? [] : "";
        }
        if (isset($chat[0]['image'])) {
            if ($disk == "local_server") {

                $imagePath = $chat[0]['image'];
                $chat[0]['profile_image'] = fix_provider_path($imagePath) ?? "";
            } else if ($disk == "aws_s3") {
                $chat[0]['profile_image'] = fetch_cloud_front_url('profile', $chat[0]['image']);
            } else {
                $imagePath = $chat[0]['image'];

                $chat[0]['profile_image'] = fix_provider_path($imagePath) ?? "";
            }
        } else {
            $chat[0]['profile_image'] = base_url("/public/backend/assets/profiles/default.png");
        }
        $chat[0]['last_message_date'] = $last_chat_date;
        $data = $chat[0];
    }
    $data['sender_details'] = $sender_details;
    $data['receiver_details'] = $receiver_details;
    $data['last_message_date'] = $last_chat_date;
    $data['viewer_type'] = $view_user_type;
    return $data;
}
function getLastMessageDateFromChat($e_id)
{
    $chat_last_message_date = fetch_details('chats', ['e_id' => $e_id], ['id', 'created_at'], 1, 0, 'created_at', 'DESC');
    if (!empty($chat_last_message_date)) {
        $last_date = $chat_last_message_date[0]['created_at'];
    } else {
        $last_date1 = new DateTime();
        $last_date = $last_date1->format('Y-m-d H:i:s');
    }
    return $last_date;
}
function checkModificationInDemoMode($superadminEmail)
{
    if ($superadminEmail == "superadmin@gmail.com") {
        return true;
    } else {
        if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1) {
            return true;
        } else {
            $response['error'] = true;
            $response['message'] = DEMO_MODE_ERROR;
            $response['csrfName'] = csrf_token();
            $response['csrfHash'] = csrf_hash();
            return $response;
        }
    }
}
function setPageInfo(&$data, $title, $mainPage)
{
    $data['title'] = $title;
    $data['main_page'] = $mainPage;
}
function getAccessToken()
{
    $filePath = (FCPATH . '/public/firebase_config.json');
    if (!file_exists($filePath)) {
        log_message('error', 'Firebase service account file not found at: ' . $filePath);
        return false;
    }
    $client = new Client();
    $client->setAuthConfig($filePath);
    $client->setScopes(['https://www.googleapis.com/auth/firebase.messaging']);
    $accessToken = $client->fetchAccessTokenWithAssertion()['access_token'];
    return $accessToken;
}
function sendNotificationToFCM($url, $access_token, $Data)
{
    $headers = [
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json',
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $Data);
    $result = curl_exec($ch);
    log_message('error',json_encode($result));
    if ($result == FALSE) {
        die('Curl failed: ' . curl_error($ch));
    }
    curl_close($ch);
    return false;
}
function send_web_notification1($fcmMsg, $registrationIDs_chunks)
{
    $access_token = getAccessToken();
    $settings = fetch_details('settings', ['variable' => 'firebase_settings'])[0];
    $settings = $settings['value'];
    $settings = json_decode($settings, true);
    $url = 'https://fcm.googleapis.com/v1/projects/' . $settings['projectId'] . '/messages:send';
    foreach ($registrationIDs_chunks[0] as $registrationIDs) {
        if ($registrationIDs['platform'] == "android") {
            $message1 = [
                "message" => [
                    "token" => $registrationIDs['fcm_id'],
                    "data" => $fcmMsg
                ]
            ];
            $data1 = json_encode($message1);
            sendNotificationToFCM($url, $access_token, $data1);
        } elseif ($registrationIDs['platform'] == "ios") {
            $message1 = [
                "message" => [
                    "token" => ($registrationIDs['fcm_id']),
                    "data" => $fcmMsg,
                    "notification" => array(
                        "title" => $fcmMsg["title"],
                        "body" => $fcmMsg["body"],
                        "mutable_content" => true,
                        "sound" => $fcmMsg["type"] == "order" || $fcmMsg["type"] == "new_order" ?  "order_sound.aiff" : "default"
                    )
                ]
            ];
            $data1 = json_encode($message1);
            sendNotificationToFCM($url, $access_token, $data1);
        }
    }
}
function send_web_notification($title, $message, $partner_id = null, $click_action = null)
{
    $access_token = getAccessToken();
    $settings = fetch_details('settings', ['variable' => 'firebase_settings'])[0];
    $settings = $settings['value'];
    $message1 = [];
    $settings = json_decode($settings, true);
    $url = 'https://fcm.googleapis.com/v1/projects/' . $settings['projectId'] . '/messages:send';
    $db      = \Config\Database::connect();
    $builder = $db->table('users u');
    $users = $builder->Select("u.id,u.web_fcm_id")
        ->join('users_groups ug', 'ug.user_id=u.id')
        ->where('ug.group_id', '1')
        ->get()->getResultArray();
    if (!empty($partner_id)) {
        $partner = fetch_details('users', ['id' => $partner_id], ['web_fcm_id']);
    }
    $settings = get_settings('general_settings', true);
    $icon = $settings['logo'];
    foreach ($users as $key => $users) {
        $fcm_tokens[] = $users['web_fcm_id'];
    }
    $fcm_tokens = array_filter(($fcm_tokens));
    // array_push($fcm_tokens,$partner[0]['fcm_id']);
    if (!empty($partner_id)) {
        array_push($fcm_tokens, $partner[0]['web_fcm_id']);
    }
    $fcm_tokens = (array_values($fcm_tokens));
    foreach ($fcm_tokens as $token) {
        $message1 = [
            "message" => [
                "token" => $token,
                'data' => ['type' => "new_order"],
                'notification' => [
                    'title' => $title,
                    'body' => $message,
                ],
            ]
        ];
    }
    $data1 = json_encode($message1);
    sendNotificationToFCM($url, $access_token, $data1);
    return false;
}
function send_panel_chat_notification($title, $message, $user_id = null, $click_action = null, $type = null, $payload = null)
{
    $db = \Config\Database::connect();
    $builder = $db->table('users u');
    $settings = get_settings('general_settings', true);
    $icon = $settings['logo'];
    $user_data = $builder->select("u.id,u.panel_fcm_id")
        ->join('users_groups ug', 'ug.user_id=u.id')
        ->where('ug.group_id', '3')
        ->get()->getResultArray();
    if (!empty($user_id)) {
        $user_data = fetch_details('users', ['id' => $user_id], ['panel_fcm_id', 'id', 'username', 'image']);
    }
    $settings = get_settings('general_settings', true);
    $fcm_tokens = [];
    foreach ($user_data as $key => $users) {
        $fcm_tokens[] = $users['panel_fcm_id'];
    }
    $fcm_tokens = array_filter($fcm_tokens);
    $payload = [
        "id" => (string) $payload['id'],
        "sender_id" => (string)$payload['sender_id'],
        "receiver_id" => (string)$payload['receiver_id'],
        "booking_id" => (string)$payload['booking_id'],
        "message" => (string)$payload['message'],
        // "file" => (string)$payload['file'],
        "file" => json_encode([
            $payload['file']
        ]),
        "file_type" => (string)$payload['file_type'],
        "created_at" => (string)$payload['created_at'],
        "updated_at" => (string)$payload['updated_at'],
        "e_id" => (string)$payload['e_id'],
        "sender_type" => (string)$payload['sender_type'],
        "receiver_type" => (string)$payload['receiver_type'],
        "username" => (string)$payload['username'],
        "image" => (string)$payload['image'],
        "user_id" => (string)$payload['user_id'],
        "profile_image" => (string)$payload['profile_image'] ?? "",
        "last_message_date" => (string)$payload['last_message_date'],
        "viewer_type" => (string)$payload['viewer_type'],
        "sender_details" => json_encode([
            $payload['sender_details']
        ]),
        "receiver_details" => json_encode([
            $payload['receiver_details']
        ]),
    ];
    if (!empty($fcm_tokens)) {
        $fcm_tokens1 = $fcm_tokens[0];
    } else {
        $fcm_tokens1 = [];
    }
    if (!empty($fcm_tokens1)) {
        $message1 = [
            "message" => [
                "token" => $fcm_tokens1,
                "data" => $payload,
                'notification' => [
                    'title' => $title,
                    'body' => $message,
                ],
            ]
        ];
        $access_token = getAccessToken();
        $settings = fetch_details('settings', ['variable' => 'firebase_settings'])[0];
        $settings = $settings['value'];
        $settings = json_decode($settings, true);
        $url = 'https://fcm.googleapis.com/v1/projects/' . $settings['projectId'] . '/messages:send';
        $data1 = json_encode($message1);
        sendNotificationToFCM($url, $access_token, $data1);
    } else {
        return "No fcm found";
    }
}
function send_app_chat_notification($title, $message, $user_id = null, $click_action = null, $type = null, $payload = null)
{
    $db = \Config\Database::connect();
    $builder = $db->table('users u');
    $settings = get_settings('general_settings', true);
    $icon = $settings['logo'];
    // $user_data = $builder->select("u.id,u.fcm_id,u.platform")
    //     ->join('users_groups ug', 'ug.user_id=u.id')
    //     ->where('ug.group_id', '3')
    //     ->get()->getResultArray();
    // if (!empty($user_id)) {
    //     $user_data = fetch_details('users', ['id' => $user_id], ['fcm_id', 'id', 'username', 'image', 'platform']);
    // }
    if ($payload['receiver_type'] == 1) {
        $user_data = $builder->select("u.id,u.fcm_id,u.platform")
            ->join('users_groups ug', 'ug.user_id=u.id')
            ->where('u.id', $user_id)
            ->where('ug.group_id', '3')
            ->get()->getResultArray();
    } else if ($payload['receiver_type'] == 2) {
        $user_data = $builder->select("u.id,u.fcm_id,u.platform,u.username,u.image,u.platform")
            ->join('users_groups ug', 'ug.user_id=u.id')
            ->where('u.id', $user_id)
            ->where('ug.group_id', '2')
            ->get()->getResultArray();
    }
    $settings = get_settings('general_settings', true);
    $fcm_tokens = [];
    foreach ($user_data as $key => $users) {
        $fcm_tokens['fcm_id'] = $users['fcm_id'];
        $fcm_tokens['platform'] = $users['platform'];
    }
    $fcm_tokens = array_filter($fcm_tokens);
    if (empty($message)) {
        $fileArray = isset($payload['file']) ? $payload['file'] : [];
        $fileCount = count($fileArray);
        $message = "Received " . $fileCount . " files";
    }
    $payload = [
        'title' => (string) $title,
        'body' => (string)  $message,
        "id" => (string) $payload['id'],
        "sender_id" => (string)$payload['sender_id'],
        "receiver_id" => (string)$payload['receiver_id'],
        "booking_id" => isset($payload['booking_id']) ? (string)$payload['booking_id'] : '0',
        "message" => (string)$payload['message'],
        "file" => json_encode([
            $payload['file']
        ]),
        "file_type" => (string)$payload['file_type'],
        "created_at" => (string)$payload['created_at'],
        "updated_at" => (string)$payload['updated_at'],
        "e_id" => (string)$payload['e_id'],
        "sender_type" => (string)$payload['sender_type'],
        "receiver_type" => (string)$payload['receiver_type'],
        "username" => (string)$payload['username'],
        "image" => (string)$payload['image'],
        "user_id" => (string)$payload['user_id'],
        "profile_image" => (string)$payload['profile_image'] ?? "",
        "last_message_date" => (string)$payload['last_message_date'],
        "viewer_type" => (string)$payload['viewer_type'],
        "sender_details" => json_encode([
            $payload['sender_details']
        ]),
        "receiver_details" => json_encode([
            $payload['receiver_details']
        ]),
        'type' => 'chat',
        'booking_status' => isset($payload['booking_status']) ? (string) $payload['booking_status'] : "",
        'provider_id' => isset($payload['provider_id']) ? (string) $payload['provider_id'] : "",
    ];
    if ($payload['sender_type'] == 1  && $payload['receiver_type'] == 2) {
        $payload['provider_id'] = $payload['sender_id'];
    } else if ($payload['sender_type'] == 2 && $payload['receiver_type'] == 1) {
        $payload['provider_id'] = $payload['receiver_id'];
    } else {
        $payload['provider_id'] = "";
    }
    if ($payload['booking_id'] != 0 || $payload['booking_id'] != "") {
        $booking_status = fetch_details('orders', ['id' => $payload['booking_id']], ['status']);
        $payload['booking_status'] = isset($booking_status[0]) ? $booking_status[0]['status'] : "";
    }
    $message1 = [
        "message" => [
            "data" => $payload,
        ]
    ];
    // Check if the platform is Android or iOS
    if (!empty($fcm_tokens) && isset($fcm_tokens['platform'])) {
        if ($fcm_tokens['platform'] === 'ios') {
            $message1["message"]["notification"] = [
                'title' => $title,
                'body' => $message,
            ];
        }
        $message1["message"]["token"] = $fcm_tokens['fcm_id'] ?? ""; // Include the token
    }
    $access_token = getAccessToken();
    $settings = fetch_details('settings', ['variable' => 'firebase_settings'])[0];
    $settings = $settings['value'];
    $settings = json_decode($settings, true);
    $url = 'https://fcm.googleapis.com/v1/projects/' . $settings['projectId'] . '/messages:send';
    $data1 = json_encode($message1);
    $headers = [
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json',
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data1);
    $result = curl_exec($ch);
    if ($result == FALSE) {
        die('Curl failed: ' . curl_error($ch));
    }
    curl_close($ch);
}
function send_customer_web_chat_notification($title, $message, $user_id = null, $click_action = null, $type = null, $payload = null)
{
    $db = \Config\Database::connect();
    $builder = $db->table('users u');
    $settings = get_settings('general_settings', true);
    $icon = $settings['logo'];
    $user_data = $builder->select("u.id,u.web_fcm_id")
        ->join('users_groups ug', 'ug.user_id=u.id')
        ->where('ug.group_id', '3')
        ->get()->getResultArray();
    if (!empty($user_id)) {
        $user_data = fetch_details('users', ['id' => $user_id], ['web_fcm_id', 'id', 'username', 'image']);
    }
    $settings = get_settings('general_settings', true);
    $fcm_tokens = [];
    foreach ($user_data as $key => $users) {
        $fcm_tokens[] = $users['web_fcm_id'];
    }
    $fcm_tokens = array_filter($fcm_tokens);
    $payload = [
        "id" => (string) $payload['id'],
        "sender_id" => (string)$payload['sender_id'],
        "receiver_id" => (string)$payload['receiver_id'],
        "booking_id" => (string)$payload['booking_id'],
        "message" => (string)$payload['message'],
        // "file" => (string)$payload['file'],
        "file" => json_encode([
            $payload['file']
        ]),
        "file_type" => (string)$payload['file_type'],
        "created_at" => (string)$payload['created_at'],
        "updated_at" => (string)$payload['updated_at'],
        "e_id" => (string)$payload['e_id'],
        "sender_type" => (string)$payload['sender_type'],
        "receiver_type" => (string)$payload['receiver_type'],
        "username" => (string)$payload['username'],
        "image" => (string)$payload['image'],
        "user_id" => (string)$payload['user_id'],
        "profile_image" => (string)$payload['profile_image'] ?? "",
        "last_message_date" => (string)$payload['last_message_date'],
        "viewer_type" => (string)$payload['viewer_type'],
        "sender_details" => json_encode([
            $payload['sender_details']
        ]),
        "receiver_details" => json_encode([
            $payload['receiver_details']
        ]),
    ];
    if (!empty($fcm_tokens)) {
        $fcm_tokens1 = $fcm_tokens[0];
    } else {
        $fcm_tokens1 = [];
    }
    if (!empty($fcm_tokens1)) {
        $message1 = [
            "message" => [
                "token" => $fcm_tokens1,
                "data" => $payload,
                'notification' => [
                    'title' => $title,
                    'body' => $message,
                ],
            ]
        ];
        $access_token = getAccessToken();
        $settings = fetch_details('settings', ['variable' => 'firebase_settings'])[0];
        $settings = $settings['value'];
        $settings = json_decode($settings, true);
        $url = 'https://fcm.googleapis.com/v1/projects/' . $settings['projectId'] . '/messages:send';
        $data1 = json_encode($message1);
        sendNotificationToFCM($url, $access_token, $data1);
    } else {
        return "No fcm found";
    }
}
function extractVariables($content)
{
    preg_match_all('/\[\[(.*?)\]\]/', $content, $matches);
    return array_map('trim', $matches[1]);
}
function custom_email_sender($to, $subject, $message, $from_email, $from_name, $bcc = null, $cc = null, $logo_attachment = null, $cid = null)
{
    $email_settings = get_settings('email_settings', true);

    // Initialize email service with proper configuration
    $config = [
        'protocol' => 'smtp',
        'SMTPHost' => $email_settings['smtpHost'] ?? '',
        'SMTPPort' => intval($email_settings['smtpPort']) ?? 587,
        'SMTPUser' => $email_settings['smtpUsername'] ?? '',
        'SMTPPass' => $email_settings['smtpPassword'] ?? '',
        'SMTPCrypto' => 'tls',
        'mailType' => 'html',
        'charset' => 'utf-8',
        'newline' => "\r\n"
    ];

    $email = \Config\Services::email($config);
    // $email = \Config\Services::email();
    $email->setTo($to);
    $email->setFrom($from_email, $from_name);
    $email->setSubject($subject);
    $email->setMailType('html');
    if (!empty($bcc)) {
        $email->setBCC($bcc);
    }
    if (!empty($cc)) {
        $email->setCC($cc);
    }
    $email->setMessage($message);

    return $email->send();
    // if (!$email->send()) {
    //     log_message('error', '$email header ' . var_export($email->printDebugger(['headers']), true));
    // } else {
    //     return true;
    //     echo "Email sent successfully.";
    // }
}
function send_custom_email($type, $provider_id = null, $email_to = null, $amount = null, $user_id = null, $booking_id = null, $booking_date = null, $booking_time = null, $booking_service_names = null, $booking_address = null)
{
    // Fetch settings
    $email_settings = \get_settings('email_settings', true);
    $company_settings = \get_settings('general_settings', true);
    $smtpUsername = $email_settings['smtpUsername'];
    $company_name = $company_settings['company_title'];
    // Fetch email template
    $template_data = fetch_details('email_templates', ['type' => $type]);
    if (!$template_data) {
        // echo "Email template not found.";
        return false;
    }
    $template = $template_data[0]['template'];
    $subject = $template_data[0]['subject'];
    // Check if template includes provider name placeholder
    if (strpos($template, '[[provider_name]]') !== false && $provider_id !== null) {
        $partner_data = fetch_details('partner_details', ['partner_id' => $provider_id]);
        if (!$partner_data) {
            // echo "Partner data not found.";
            // return "Partner data not found.";
            return false;
        }
        $provider_name = $partner_data[0]['company_name'];
        $template = str_replace("[[provider_name]]", $provider_name, $template);
    }
    if (strpos($template, '[[company_name]]') !== false  && $company_name !== null) {
        $template = str_replace("[[company_name]]", $company_name, $template);
    }
    if (strpos($template, '[[provider_id]]') !== false  && $provider_id !== null) {
        $template = str_replace("[[provider_id]]", $provider_id, $template);
    }
    if (strpos($template, '[[site_url]]') !== false) {
        $template = str_replace("[[site_url]]", base_url(), $template);
    }
    if (strpos($template, '[[company_contact_info]]') !== false) {
        $contact_us = get_settings('contact_us', true);
        $template = str_replace("[[company_contact_info]]", $contact_us['contact_us'], $template);
    }
    if (strpos($template, '[[amount]]') !== false && $amount !== null) {
        $template = str_replace("[[amount]]", $amount, $template);
    }
    $logo_attachment = "";
    $cid = "";
    if (strpos($template, '[[company_logo]]') !== false) {
        $settings = get_settings('general_settings', true);
        $logoPath = "public/uploads/site/" . $settings['logo'];
        if (file_exists($logoPath)) {
            $logo_attachment = $logoPath;
            $cid = basename($logoPath);
            $logo_img_tag = '<img src="cid:' . $cid . '" alt="Company Logo">';
            $template = str_replace("[[company_logo]]", $logo_img_tag, $template);
        } else {
            // If logo file doesn't exist, remove the placeholder from the template
            $template = str_replace("[[company_logo]]", '', $template);
        }
        preg_match_all('/<img[^>]+src=["\'](.*?)["\'][^>]*>/i', $template, $matches);
        $imagePaths = $matches[1];
        foreach ($imagePaths as $imagePath) {
            if (file_exists($imagePath)) {
                $template = str_replace($imagePath, "cid:$cid", $template);
            }
        }
    }
    if (strpos($template, '[[currency]]') !== false) {
        $currency = get_settings('general_settings', true);
        $currency = $currency['currency'];
        $template = str_replace("[[currency]]", $currency, $template);
    }
    if (strpos($template, '[[user_name]]') !== false && $user_id !== null) {
        $users = fetch_details('users', ['id' => $user_id]);
        if (!$users) {
            return false;
            // echo "User data not found.";
            // return "User data not found.";
        }
        $user_name = $users[0]['username'];
        $template = str_replace("[[user_name]]", $user_name, $template);
    }
    if (strpos($template, '[[user_id]]') !== false && $user_id !== null) {
        $template = str_replace("[[user_id]]", $user_id, $template);
    }
    if (strpos($template, '[[user_id]]') !== false && $user_id !== null) {
        $template = str_replace("[[user_id]]", $user_id, $template);
    }
    if ($booking_id !== null) {
        $booking = fetch_details('orders', ['id' => $booking_id]);
        if (empty($booking[0])) {
            return false;
        }
        $booking = $booking[0];
        $template = str_replace("[[booking_id]]", $booking['id'], $template);
        if (strpos($template, '[[booking_date]]') !== false && $booking_id !== null) {
            $template = str_replace("[[booking_date]]", $booking['date_of_service'], $template);
        }
        if (strpos($template, '[[booking_time]]') !== false && $booking_id !== null) {
            $template = str_replace("[[booking_time]]", $booking['starting_time'], $template);
        }
        if (strpos($template, '[[booking_status]]') !== false && $booking_id !== null) {
            $template = str_replace("[[booking_status]]", $booking['status'], $template);
        }
        
        if (strpos($template, '[[booking_service_names]]') !== false  && $booking_id !== null) {
            $services = fetch_details('order_services', ['order_id' => $booking_id]);
            $service_names = '';
            foreach ($services as $row) {
                $service_names .= $row['service_title'] . ', ';
            }
            $service_names = rtrim($service_names, ', ');
            $template = str_replace("[[booking_service_names]]", $service_names, $template);
        }
        if (strpos($template, '[[booking_address]]') !== false &&  $booking_id !== null) {
            $template = str_replace("[[booking_address]]", $booking['address'], $template);
        }
    }
    if (strpos($template, '[[booking_id]]') !== false && $booking_id !== null) {
        $booking = fetch_details('orders', ['id' => $booking_id]['id'])[0];
        $template = str_replace("[[booking_id]]", $booking['id'], $template);
    }
    if (strpos($template, '[[booking_date]]') !== false && $booking_date !== null && $booking_id !== null) {
        $booking = fetch_details('orders', ['id' => $booking_id]['booking_date'])[0];
        $template = str_replace("[[booking_date]]", $booking['date_of_service'], $template);
    }
    if (strpos($template, '[[booking_time]]') !== false && $booking_time !== null && $booking_id !== null) {
        $booking = fetch_details('orders', ['id' => $booking_id]['booking_time'])[0];
        $template = str_replace("[[booking_time]]", $booking['starting_time'], $template);
    }

    if (strpos($template, '[[booking_time]]') !== false && $booking_time !== null && $booking_id !== null) {
        $booking = fetch_details('orders', ['id' => $booking_id]['booking_time'])[0];
        $template = str_replace("[[booking_time]]", $booking['starting_time'], $template);
    }
    if (strpos($template, '[[booking_service_names]]') !== false && $booking_service_names !== null && $booking_id !== null) {
        $services = fetch_details('orders_services', ['order_id' => $booking_id]);
        $service_names = '';
        foreach ($services as $row) {
            $service_names .= $row['service_title'] . ', ';
        }
        $service_names = rtrim($service_names, ', ');
        $template = str_replace("[[booking_service_names]]", $service_names, $template);
    }
    if (strpos($template, '[[booking_address]]') !== false && $booking_address !== null && $booking_id !== null) {
        $booking = fetch_details('orders', ['id' => $booking_id]['address'])[0];
        $template = str_replace("[[booking_address]]", $booking['address'], $template);
    }
    $bcc = [];
    $cc = [];
    if ($template_data[0]['bcc']) {
        $base_tags = $template_data[0]['bcc'];
        $val = explode(',', $base_tags);
        $bcc = [];
        foreach ($val as $s) {
            $bcc[] = $s;
        }
    }
    if ($template_data[0]['cc']) {
        $base_tags = $template_data[0]['cc'];
        $val = explode(',', $base_tags);
        $cc = [];
        foreach ($val as $s) {
            $cc[] = $s;
        }
    }
    // Prepare email details
    $from_email = $smtpUsername;
    $from_name = $company_name;
    $message = htmlspecialchars_decode($template);
    // Send email
    if (custom_email_sender($email_to, $subject, $message, $from_email, $from_name, $bcc, $cc, $logo_attachment, $cid)) {
        // if (custom_email_sender($email_to, $subject, $message, $from_email, $from_name, $bcc, $cc, $logo_attachment, $cid)) {
        return true;
    } else {
        return "email not send";
    }
}
function unsubscribe_link_user_encrypt($user_id, $email)
{
    $simple_string = $user_id . "-" . $email;
    // Store the cipher method
    $ciphering = "AES-128-CTR";
    // Use OpenSSl Encryption method
    $iv_length = openssl_cipher_iv_length($ciphering);
    $options = 0;
    // Non-NULL Initialization Vector for encryption
    $encryption_iv = '1234567891011121';
    // Store the encryption key
    $encryption_key = getenv('decryption_key');
    // Use openssl_encrypt() function to encrypt the data
    $encryption = openssl_encrypt(
        $simple_string,
        $ciphering,
        $encryption_key,
        $options,
        $encryption_iv
    );
    return $encryption;
}
function unsubscribe_link_user_decrypt($user_id)
{
    $ciphering = "AES-128-CTR";
    $options = 0;
    // Use openssl_encrypt() function to encrypt the data
    $encryption = $user_id;
    // Non-NULL Initialization Vector for decryption
    $decryption_iv = '1234567891011121';
    // Store the decryption key
    $decryption_key = getenv('decryption_key');
    // Use openssl_decrypt() function to decrypt the data
    $decryption = openssl_decrypt(
        $encryption,
        $ciphering,
        $decryption_key,
        $options,
        $decryption_iv
    );
    $data = (explode("-", $decryption));
    return $data;
}
function is_unsubscribe_enabled($user_id)
{
    $user = fetch_details('users', ['id' => $user_id], ['id', 'unsubscribe_email'])[0];
    return $user['unsubscribe_email'];
}
// function compressImage($source, $destination, $quality)
// {

//     $settings = get_settings('general_settings', true);
//     // Check if image compression is enabled
//     if ($settings['image_compression_preference'] == 0) {
//         // If compression is not enabled, simply copy the file to the destination
//         // if (!copy($source, $destination)) {
//         //     die('Failed to copy the image.');
//         // }
//         // return;
//     }
//     $finfo = finfo_open(FILEINFO_MIME_TYPE);
//     $mime = finfo_file($finfo, $source);


//     finfo_close($finfo);
//     if ($mime === 'image/svg+xml') {
//         $svgContent = file_get_contents($source);
//         if (file_put_contents($destination, $svgContent) === false) {
//             die('Failed to save the SVG image.');
//         }
//         return;
//     }
//     switch ($mime) {
//         case 'image/jpeg':
//             $image = imagecreatefromjpeg($source);
//             break;
//         case 'image/png':

//             $image = imagecreatefrompng($source);
//             break;
//         case 'image/gif':
//             $image = imagecreatefromgif($source);
//             break;
//         default:
//             $image = null;
//     }
//     if ($image === null) {
//         $image=$source;

//         // die('Unsupported image type.');
//     }
//     // If a custom quality setting exists, use it
//     if (!empty($settings['image_compression_quality'])) {
//         $quality = $settings['image_compression_quality'];
//     }
//     if (!imagejpeg($image, $destination, $quality)) {
//         die('Failed to save the image.');
//     }
//     imagedestroy($image);
// }

function compressImage($source, $destination, $quality)
{
    $settings = get_settings('general_settings', true);

    // Check if image compression is disabled
    if ($settings['image_compression_preference'] == 0) {
        copy($source, $destination); // Directly copy the file if compression is disabled
        return;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $source);
    finfo_close($finfo);

    // Handle SVG images separately
    if ($mime === 'image/svg+xml') {
        $svgContent = file_get_contents($source);
        file_put_contents($destination, $svgContent); // Save the SVG without compression
        return;
    }

    $image = null;

    // Check for supported image types
    switch ($mime) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $image = imagecreatefrompng($source);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($source);
            break;
        default:
            // If image type is unsupported, simply copy it to the destination
            copy($source, $destination);
            return;
    }

    // Use custom quality setting if available
    if (!empty($settings['image_compression_quality'])) {
        $quality = $settings['image_compression_quality'];
    }

    // Compress and save the image
    if (!imagejpeg($image, $destination, $quality)) {
        copy($source, $destination); // If compression fails, copy the original file
    }

    imagedestroy($image);
}

function copy_image($number, $og_path)
{
    $sourceFilePath = FCPATH . $number;
    if (file_exists($sourceFilePath)) {
        $destinationDirectory = FCPATH . $og_path;
        $og_path = rtrim($og_path, '/');
        $fileName = basename($sourceFilePath);
        $destinationFilePath = $destinationDirectory . '/' . $fileName;
        if (copy($sourceFilePath, $destinationFilePath)) {
            $image = $og_path . '/' . $fileName;
        } else {
            $image = $og_path . '/' . $fileName;
        }
    } else {
        $image = "";
    }
    return $image;
}
function set_user_otp($mobile, $otp, $only_mobile_number = null, $country_code = null)
{
    $dateString = date('Y-m-d H:i:s');
    $time = strtotime($dateString);
    $data['otp'] = $otp;
    $data['created_at'] = $dateString;
    if (!empty($country_code)) {
        $mobile_for_sms = $country_code . $only_mobile_number;
    } else {
        $mobile_for_sms = $only_mobile_number;
    }
    $otps = fetch_details('otps', ['mobile' => $mobile_for_sms]);
    foreach ($otps as $user) {
        if (isset($user['mobile']) && !empty($user['mobile'])) {
            $message = send_sms($mobile_for_sms, "please don't share with anyone $otp");
            if ($message['http_code'] != 201) {
                return [
                    "error" => true,
                    "message" => "OTP Can not send.",
                    "data" => $data
                ];
            } else {
                update_details($data, ['id' => $user['id']], 'otps');
                return [
                    "error" => false,
                    "message" => "OTP send successfully.",
                    "data" => $data
                ];
            }
        }
        return [
            "error" => true,
            "message" => "No OTP Stored for this number."
        ];
    }
}

function send_sms($phone, $msg, $country_code = "+91")
{
    $data = get_settings('sms_gateway_setting', true);
    $current_sms_gateway = $data['current_sms_gateway']??"twilio";
    if ($current_sms_gateway == "vonage") {
    } else if ($current_sms_gateway == "twilio") {
        $account_sid = $data['twilio_account_sid'] ?? '';
        $auth_token = $data['twilio_auth_token'] ?? "";
        $from = $data['twilio_from'] ?? "";
        $body = [
            'To' => $country_code . $phone,
            'From' => $from,
            'Body' => $msg
        ];
        return curl_sms(
            "https://api.twilio.com/2010-04-01/Accounts/{$account_sid}/Messages.json",
            'POST',
            $body,
            [
                "Authorization: Basic " . base64_encode("{$account_sid}:{$auth_token}")
            ]
        );
    }
}
function parse_sms(string $string = "", string $mobile = "", string $sms = "", string $country_code = "")
{
    $parsedString = str_replace("{only_mobile_number}", $mobile, $string);
    $parsedString = str_replace("{message}", $sms, $parsedString); // Use $parsedString as the third argument
    return $parsedString;
}
function curl_sms($url, $method = 'GET', $data = [], $headers = [])
{
    $ch = curl_init();
    $curl_options = array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded',
        )
    );
    if (count($headers) != 0) {
        $curl_options[CURLOPT_HTTPHEADER] = $headers;
    }
    if (strtolower($method) == 'post') {
        $curl_options[CURLOPT_POST] = 1;
        $curl_options[CURLOPT_POSTFIELDS] = http_build_query($data);
    } else {
        $curl_options[CURLOPT_CUSTOMREQUEST] = 'GET';
    }
    curl_setopt_array($ch, $curl_options);
    $result = array(
        'body' => json_decode(curl_exec($ch), true),
        'http_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
    );
    return $result;
}
function check_notification_setting($setting, $type)
{
    $data = get_settings('notification_settings', true);
    if (isset($data[$setting . '_' . $type])) {
        return $data[$setting . '_' . $type];
    }
    return false;
}
function send_custom_sms($type, $provider_id = null, $email_to = null, $amount = null, $user_id = null, $booking_id = null, $booking_date = null, $booking_time = null, $booking_service_names = null, $booking_address = null)
{
    $msg = fetch_details('sms_templates', ['type' => $type]);
    $company_settings = \get_settings('general_settings', true);
    $company_name = $company_settings['company_title'];
    $template = $msg[0]['template'];
    if (strpos($template, '[[provider_name]]') !== false && $provider_id !== null) {
        $partner_data = fetch_details('partner_details', ['partner_id' => $provider_id]);
        if (!$partner_data) {
            return false;
        }
        $provider_name = $partner_data[0]['company_name'];
        $template = str_replace("[[provider_name]]", $provider_name, $template);
    }
    if (strpos($template, '[[company_name]]') !== false  && $company_name !== null) {
        $template = str_replace("[[company_name]]", $company_name, $template);
    }
    if (strpos($template, '[[provider_id]]') !== false  && $provider_id !== null) {
        $template = str_replace("[[provider_id]]", $provider_id, $template);
    }
    if (strpos($template, '[[site_url]]') !== false) {
        $template = str_replace("[[site_url]]", base_url(), $template);
    }
    if (strpos($template, '[[company_contact_info]]') !== false) {
        $contact_us = get_settings('contact_us', true)['contact_us'];
        $contact_us = htmlspecialchars_decode($contact_us);
        $contact_us = strip_tags($contact_us);
        $contact_us = html_entity_decode($contact_us);
        $template = str_replace("[[company_contact_info]]", $contact_us, $template);
    }
    if (strpos($template, '[[amount]]') !== false && $amount !== null) {
        $template = str_replace("[[amount]]", $amount, $template);
    }
    if (strpos($template, '[[currency]]') !== false) {
        $currency = get_settings('general_settings', true);
        $currency = $currency['currency'];
        $template = str_replace("[[currency]]", $currency, $template);
    }
    if (strpos($template, '[[user_name]]') !== false && $user_id !== null) {
        $users = fetch_details('users', ['id' => $user_id]);
        if (!$users) {
            return false;
        }
        $user_name = $users[0]['username'];
        $template = str_replace("[[user_name]]", $user_name, $template);
    }
    if (strpos($template, '[[user_id]]') !== false && $user_id !== null) {
        $template = str_replace("[[user_id]]", $user_id, $template);
    }
    if (strpos($template, '[[user_id]]') !== false && $user_id !== null) {
        $template = str_replace("[[user_id]]", $user_id, $template);
    }
    if ($booking_id !== null) {
        $booking = fetch_details('orders', ['id' => $booking_id]);
        if (empty($booking[0])) {
            return false;
        }
        $booking = $booking[0];
        $template = str_replace("[[booking_id]]", $booking['id'], $template);
        if (strpos($template, '[[booking_date]]') !== false && $booking_id !== null) {
            $template = str_replace("[[booking_date]]", $booking['date_of_service'], $template);
        }
        if (strpos($template, '[[booking_time]]') !== false && $booking_id !== null) {
            $template = str_replace("[[booking_time]]", $booking['starting_time'], $template);
        }
        if (strpos($template, '[[booking_service_names]]') !== false  && $booking_id !== null) {
            $services = fetch_details('order_services', ['order_id' => $booking_id]);
            $service_names = '';
            foreach ($services as $row) {
                $service_names .= $row['service_title'] . ', ';
            }
            $service_names = rtrim($service_names, ', ');
            $template = str_replace("[[booking_service_names]]", $service_names, $template);
        }
        if (strpos($template, '[[booking_address]]') !== false &&  $booking_id !== null) {
            $template = str_replace("[[booking_address]]", $booking['address'], $template);
        }
    }
    if (strpos($template, '[[booking_id]]') !== false && $booking_id !== null) {
        $booking = fetch_details('orders', ['id' => $booking_id]['id'])[0];
        $template = str_replace("[[booking_id]]", $booking['id'], $template);
    }
    if (strpos($template, '[[booking_date]]') !== false && $booking_date !== null && $booking_id !== null) {
        $booking = fetch_details('orders', ['id' => $booking_id]['booking_date'])[0];
        $template = str_replace("[[booking_date]]", $booking['date_of_service'], $template);
    }
    if (strpos($template, '[[booking_time]]') !== false && $booking_time !== null && $booking_id !== null) {
        $booking = fetch_details('orders', ['id' => $booking_id]['booking_time'])[0];
        $template = str_replace("[[booking_time]]", $booking['starting_time'], $template);
    }
    if (strpos($template, '[[booking_service_names]]') !== false && $booking_service_names !== null && $booking_id !== null) {
        $services = fetch_details('orders_services', ['order_id' => $booking_id]);
        $service_names = '';
        foreach ($services as $row) {
            $service_names .= $row['service_title'] . ', ';
        }
        $service_names = rtrim($service_names, ', ');
        $template = str_replace("[[booking_service_names]]", $service_names, $template);
    }
    if (strpos($template, '[[booking_address]]') !== false && $booking_address !== null && $booking_id !== null) {
        $booking = fetch_details('orders', ['id' => $booking_id]['address'])[0];
        $template = str_replace("[[booking_address]]", $booking['address'], $template);
    }
    $json_message_body = stripslashes($template);
    $json_message_body = str_replace(['rn', '\r', '\n', '\\'], '', $json_message_body);
    $mobile = fetch_details('users', ['email' => $email_to], ['phone']);
    if (empty($mobile)) {
        return [
            "error" => true,
            "message" => "OTP Can not send.",
        ];
    }
    $message = send_sms($mobile[0]['phone'], htmlspecialchars_decode($json_message_body));
    return [
        "error" => $message['http_code'] != 201,
        "message" => $message['http_code'] != 201 ? "OTP Can not send." : "OTP send successfully.",
    ];
}
function encrypt_data($key, $text)
{
    $iv = openssl_random_pseudo_bytes(16);
    $key .= "0000";
    $encrypted_data = openssl_encrypt($text, 'aes-256-cbc', $key, 0, $iv);
    $data = array("ciphertext" => $encrypted_data, "iv" => bin2hex($iv));
    return $data;
}
function checkOTPExpiration($otpTime)
{
    $currentTime = time();
    $otpTimestamp = strtotime($otpTime);
    if ($otpTimestamp === false) {
        return [
            "error" => true,
            "message" => "Invalid OTP time format."
        ];
    }
    $timeDifference = $currentTime - $otpTimestamp;
    if ($timeDifference <= 600) { // 10 minutes = 300 seconds
        return [
            "error" => false,
            "message" => "Success: OTP is valid."
        ];
    } else {
        return [
            "error" => true,
            "message" => "OTP has expired."
        ];
    }
}
function feature_section_type($type)
{
    $value = "";
    if ($type == "categories") {
        $value = "Categories";
    } else if ($type == "partners") {
        $value = "Partners";
    } else if ($type == "top_rated_partner") {
        $value = "Top Rated Partners";
    } else if ($type == "previous_order") {
        $value = "Previos Order";
    } else if ($type == "ongoing_order") {
        $value = "Ongoing Order";
    } else if ($type == "near_by_provider") {
        $value = "Near By Providers";
    } else if ($type == "banner") {
        $value = "Banner";
    } else {
        $value = "No Section Type Found";
    }
    return $value;
}
function banner_type($type)
{
    $value = "";
    if ($type == "banner_default") {
        $value = "Default";
    } else if ($type == "banner_category") {
        $value = "Category";
    } else if ($type == "banner_provider") {
        $value = "Provider";
    } else if ($type == "banner_url") {
        $value = "URL";
    } else {
        $value = "-";
    }
    return $value;
}
function create_folder($path)
{
    $fullPath = FCPATH . $path;
    if (is_dir($fullPath)) {
        return true;
    }
    if (mkdir($fullPath, 0775, true)) {
        return true;
    } else {
        return false;
    }
}
function sanitizeInput($data)
{
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
function updateEnv($key, $value)
{
    $envPath = ROOTPATH . '.env';
    if (file_exists($envPath)) {
        $envContent = file_get_contents($envPath);
        $pattern = "/^{$key}=.*/m";
        if (preg_match($pattern, $envContent)) {
            $envContent = preg_replace($pattern, "{$key}={$value}", $envContent);
        } else {
            $envContent .= "\n{$key}={$value}";
        }
        file_put_contents($envPath, $envContent);
    }
}
function diffForHumans($datetime)
{
    $time = strtotime($datetime);
    $current_time = time();
    $diff = $current_time - $time;
    // Define time intervals
    $intervals = [
        'year'   => 31536000,  // 365 days * 24 hours * 60 minutes * 60 seconds
        'month'  => 2592000,   // 30 days * 24 hours * 60 minutes * 60 seconds
        'week'   => 604800,    // 7 days * 24 hours * 60 minutes * 60 seconds
        'day'    => 86400,     // 24 hours * 60 minutes * 60 seconds
        'hour'   => 3600,      // 60 minutes * 60 seconds
        'minute' => 60,        // 60 seconds
        'second' => 1
    ];
    foreach ($intervals as $key => $value) {
        if ($diff >= $value) {
            $time_diff = floor($diff / $value);
            return $time_diff == 1 ? "1 $key ago" : "$time_diff {$key}s ago";
        }
    }
    return 'Just now';
}
function send_notification_to_related_providers($category_id, $custom_job_request_id, $latitude, $longitude)
{
    $partners = fetch_details('partner_details', ['is_accepting_custom_jobs' => 1], ['partner_id', 'custom_job_categories']);
    $category_name = fetch_details('categories', ['id' => $category_id], ['name']);
    // Prepare partner IDs for the specific category
    $partners_ids = [];
    foreach ($partners as $partner) {
        // Ensure custom_job_categories is a valid JSON string
        $category_ids = !empty($partner['custom_job_categories'])
            ? json_decode($partner['custom_job_categories'], true)
            : [];
        if (is_array($category_ids) && in_array($category_id, $category_ids)) {
            $partners_ids[] = $partner['partner_id'];
        }
    }


    if (!empty($partners_ids)) {
        $settings = get_settings('general_settings', true);
        $db = \Config\Database::connect();
        $builder = $db->table('partner_details pd');
        $builder->select("
        pd.*,
        u.username as partner_name, u.balance, u.longitude, u.latitude, 
        u.payable_commision,
        ps.id as partner_subscription_id, ps.status, ps.max_order_limit,
        st_distance_sphere(POINT('$longitude','$latitude'), POINT(u.longitude, u.latitude))/1000 as distance
    ")
            ->join('users u', 'pd.partner_id = u.id')
            ->join('partner_subscriptions ps', 'ps.partner_id = pd.partner_id', 'left')
            ->whereIn('pd.partner_id', $partners_ids)
            ->having('distance < ' . (float)$settings['max_serviceable_distance'])
            ->groupBy('pd.partner_id');
        $partners_for_notifiy = $builder->get()->getResultArray();

        foreach ($partners_for_notifiy as $key => $id) {

            $partner_subscription = fetch_details('partner_subscriptions', ['partner_id' => $id['partner_id'], 'status' => 'active']);
            if ($partner_subscription) {
                $subscription_purchase_date = $partner_subscription[0]['updated_at'];
                $partner_order_limit = fetch_details('orders', ['partner_id' => $id['partner_id'], 'parent_id' => null, 'created_at >' => $subscription_purchase_date]);
                $partners_subscription = $db->table('partner_subscriptions ps');
                $partners_subscription_data = $partners_subscription->select('ps.*')->where('ps.status', 'active')
                    ->get()
                    ->getResultArray();
                $subscription_order_limit = $partners_subscription_data[0]['max_order_limit'];
                if ($partners_subscription_data[0]['order_type'] == "limited") {
                    if (count($partner_order_limit) >= $subscription_order_limit) {
                        unset($ids[$key]);
                    }
                }
            } else {
                unset($partners_for_notifiy[$key]);
            }
        }
    }




    // Proceed only if there are matching partners
    if (!empty($partners_ids)) {
        $settings = get_settings('general_settings', true);
        $db = \Config\Database::connect();
        $builder = $db->table('partner_details pd');
        $builder->select("
        pd.*,
        u.username as partner_name, u.balance, u.longitude, u.latitude, 
        u.payable_commision,
        ps.id as partner_subscription_id, ps.status, ps.max_order_limit,
        st_distance_sphere(POINT('$longitude','$latitude'), POINT(u.longitude, u.latitude))/1000 as distance
    ")
            ->join('users u', 'pd.partner_id = u.id')
            ->join('partner_subscriptions ps', 'ps.partner_id = pd.partner_id', 'left')
            ->whereIn('pd.partner_id', $partners_ids) // Filter by partner IDs matching the category
            ->having('distance < ' . (float)$settings['max_serviceable_distance']) // Radius check
            ->groupBy('pd.partner_id');
         $partners_for_notifiy = $builder->get()->getResultArray();
  
        $access_token = getAccessToken();
        $settings = fetch_details('settings', ['variable' => 'firebase_settings'])[0]['value'];
        $firebase_settings = json_decode($settings, true);
        $url = 'https://fcm.googleapis.com/v1/projects/' . $firebase_settings['projectId'] . '/messages:send';
        $fcmMsg = [
            "title" => "New Custom Job Available",
            "body" => "A new job in " . $category_name[0]['name'] . " is available.",
            "type" => "job_notification",
        ];
        // Send notifications to partners and their panels
        foreach ($partners_for_notifiy as $partner_id) {
            insert_details(['custom_job_request_id' => $custom_job_request_id['id'], 'partner_id' => $partner_id['partner_id']], 'custom_job_provider');
            $user = fetch_details('users', ['id' => $partner_id['partner_id']], ['fcm_id', 'panel_fcm_id', 'platform']);
            if (!empty($user)) {
                // Send notification to the user's device
                if (!empty($user[0]['fcm_id'])) {
                    $fcm_id = $user[0]['fcm_id'];
                    $platform = $user[0]['platform'];
             log_the_responce(' FCM : ' . ($fcm_id) . " Issue => ",'');
                 
                    if ( $user[0]['platform'] == "android") {
                        $message1 = [
                            "message" => [
                                "token" => $user[0]['fcm_id'],
                                "data" => $fcmMsg
                            ]
                        ];
                        $data1 = json_encode($message1);
                        sendNotificationToFCM($url, $access_token, $data1);
                    } elseif ( $user[0]['platform'] == "ios") {
                     
                        $message1 = [
                            "message" => [
                                "token" => $user[0]['fcm_id'],
                                "notification" => [
                                    "title" => $fcmMsg["title"],
                                    "body" => $fcmMsg["body"]
                                ],
                                "apns" => [
                                    "payload" => [
                                        "aps" => [
                                            "mutable-content" => 1,
                                            "sound" => $fcmMsg["type"] == "order" || $fcmMsg["type"] == "new_order" ? "order_sound.aiff" : "default"
                                        ]
                                    ]
                                ],
                                "data" => $fcmMsg // Optional: Only if you need additional data
                            ]
                        ];
            
                        $data1 = json_encode($message1);
                        sendNotificationToFCM($url, $access_token, $data1);
                    }
                }
                // Send notification to the panel's FCM ID (if exists)
                if (!empty($user[0]['panel_fcm_id'])) {
                    $panel_fcm_id = $user[0]['panel_fcm_id'];
                    $panel_message = [
                        "message" => [
                            "token" => $panel_fcm_id,
                            "data" => $fcmMsg
                        ]
                    ];
                    $panel_data = json_encode($panel_message);
                    sendNotificationToFCM($url, $access_token, $panel_data);
                }
            }
        }
    }
}
// Functions for handling transactions
function handleAdditionalCharge($status, $transaction, $order, $order_id, $user_id)
{
    $data1['status'] = $status == "success" ? 'success' : 'failed';
    if (!empty($transaction)) {
        update_details($data1, [
            'order_id' => $order_id,
            'id' => $transaction['id'],
            'user_id' => $user_id
        ], 'transactions');
    } else {
        createTransaction($order, $order_id, 'failed', 'payment cancelled by customer', $user_id);
    }
}
function handleSuccessfulTransaction($transaction, $order, $order_id, $user_id, $is_redorder = false)
{
    if (!empty($transaction)) {
        $data1['status'] = 'success';
        update_details($data1, [
            'order_id' => $order_id,
            'user_id' => $user_id
        ], 'transactions');
    }
    $cart_data = fetch_cart(true, $user_id);
    if ($is_redorder == false && !empty($cart_data)) {
        foreach ($cart_data['data'] as $row) {
            delete_details(['id' => $row['id']], 'cart');
        }
    }
}
function handleFailedTransaction($transaction, $order, $order_id, $user_id)
{
    $data1['status'] = 'failed';
    if (!empty($transaction)) {
        update_details($data1, [
            'order_id' => $order_id,
            'user_id' => $user_id
        ], 'transactions');
        update_details(['status' => "cancelled"], [
            'id' => $order_id,
            'status' => 'awaiting',
            'user_id' => $user_id
        ], 'orders');
    } else {
        createTransaction($order, $order_id, 'failed', 'payment cancelled by customer', $user_id);
        update_details(['status' => "cancelled"], [
            'id' => $order_id,
            'status' => 'awaiting',
            'user_id' => $user_id
        ], 'orders');
    }
}
function createTransaction($order, $order_id, $status, $message, $user_id)
{
    $data = [
        'transaction_type' => 'transaction',
        'user_id' => $user_id,
        'partner_id' => "",
        'order_id' => $order_id,
        'type' => $order[0]['payment_method'],
        'txn_id' => "",
        'amount' => $order[0]['final_total'],
        'status' => $status,
        'currency_code' => "",
        'message' => $message,
    ];
    add_transaction($data);
}
function priceFormat($currencyCode, $price, $decimalDigits)
{
    $r =  number_to_currency($price, $currencyCode, locale_get_default(), $decimalDigits);

    // Check if price is empty or "null" (as string)
    if (empty($price) || $price === "null") {
        return $price;
    }
    // Convert price string to a float after removing commas
    $newPrice = (float)str_replace(",", "", $price);
    // Define the locale
    $locale = locale_get_default(); // or specify a default locale, e.g., "en_US"
    // Initialize formatter
    $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
    $formatter->setTextAttribute(NumberFormatter::CURRENCY_CODE, $currencyCode);
    $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $decimalDigits);
    // Format and return the price
    return $formatter->formatCurrency($newPrice, $currencyCode);
}
function update_custom_job_status($order_id, $status)
{
    $get_custom_job_data = fetch_details('orders', ['id' => $order_id]);
    if (!empty($get_custom_job_data)) {
        if ($get_custom_job_data[0]['custom_job_request_id'] != "" || $get_custom_job_data[0]['custom_job_request_id'] != NULL) {
            $update = update_details(['status' => $status], ['id' => $get_custom_job_data[0]['custom_job_request_id']], 'custom_job_requests');
        }
    }
}
function truncateWords($str, $limit = 20)
{
    $str = strip_tags($str); // Remove HTML tags
    if (mb_strlen($str) <= $limit) {
        return $str;
    }
    return mb_substr($str, 0, $limit) . '...';
}
if (!function_exists('image_url')) {
    function image_url($image_path)
    {
        $settings = get_settings('general_settings', true);
        $file_manager = $settings['file_manager'] ?? "local_server";
        if ($file_manager == "aws_s3") {
            return $image_path;
        } else if ($file_manager == "local_server") {
            return $image_path;
            // Trim the image path to remove any whitespace
            $image_path = trim($image_path);
            // Get settings for default logo
            $settings = get_settings('general_settings', true);
            $default_logo = base_url("public/uploads/site/" . $settings['logo']);
            // If empty path, return default logo
            if (empty($image_path)) {
                return $default_logo;
            }
            // Handle URLs
            if (filter_var($image_path, FILTER_VALIDATE_URL)) {
                // Parse the URL to get the path
                $parsed_url = parse_url($image_path);
                $url_path = isset($parsed_url['path']) ? urldecode($parsed_url['path']) : ''; // Check if 'path' exists
                // Extract the path after 'public/'
                if (strpos($url_path, '/public/') !== false) {
                    $relative_path = substr($url_path, strpos($url_path, '/public/') + 8);
                } else {
                    $relative_path = ltrim($url_path, '/');
                }
                // Define possible paths to check
                $possible_paths = [
                    FCPATH . $relative_path,
                    FCPATH . 'public/' . $relative_path
                ];
                foreach ($possible_paths as $path) {
                    if (is_file($path)) { // Use is_file instead of file_exists
                        return $image_path;
                    }
                }
                // Additional check for direct path
                $direct_path = FCPATH . str_replace('/public/', '', $url_path);
                if (is_file($direct_path)) {
                    return $image_path;
                }
                // If file not found, return default logo
                return $default_logo;
            }
            // Handle local paths
            // Remove 'public/' prefix if exists
            $clean_path = str_replace('public/', '', $image_path);
            $clean_path = urldecode($clean_path); // Decode URL-encoded characters
            // Define possible local paths to check
            $possible_paths = [
                FCPATH . $clean_path,
                FCPATH . 'public/' . $clean_path,
                FCPATH . 'backend/' . $clean_path
            ];
            foreach ($possible_paths as $path) {
                if (is_file($path)) {
                    $final_url = base_url(str_replace(FCPATH, '', $path));
                    return $final_url;
                }
            }
            // If no file found, return default logo
            return $default_logo;
        } else {
            return $image_path;
        }
    }
}
function upload_to_aws_s3($file_name, $file_tmp_name, $folder_name)
{
    try {
        $S3_settings = get_settings('general_settings', true);
        $aws_key = $S3_settings['aws_access_key_id'] ?? '';
        $aws_secret = $S3_settings['aws_secret_access_key'] ?? '';
        $bucket = $S3_settings['aws_bucket'] ?? '';
        $region = $S3_settings['aws_region'] ?? 'us-east-1';
        $directory = $folder_name ?? ''; // Add custom directory here
        if (!$aws_key || !$aws_secret || !$bucket || !$region) {
            return [
                "error" => true,
                "message" => "AWS configuration missing. Please check configuration variables."
            ];
        }
        $config = [
            'region' => $region,
            'version' => 'latest',
            'credentials' => [
                'key'    => $aws_key,
                'secret' => $aws_secret,
            ],
        ];
        $s3 = new S3Client($config);
        $file_open = fopen($file_tmp_name, 'r');
        // Construct the full file path with the directory
        $key = $directory ? rtrim($directory, '/') . '/' . $file_name : $file_name;
        $result = $s3->putObject([
            'Bucket' => $bucket,
            'Key'    => $key,
            'Body'   => $file_open,
            'ContentType' => mime_content_type($file_tmp_name) // Ensure the file has the correct MIME type
            // 'ACL'    => 'public-read'
        ]);
        fclose($file_open);
        if ($result && isset($result['ObjectURL'])) {
            $file_url = $result['ObjectURL'];
            return [
                "error" => false,
                "data" => $file_url,
                "message" => "File uploaded successfully"
            ];
        } else {
            return [
                "error" => true,
                "data" => "",
                "message" => "File upload failed"
            ];
        }
    } catch (Exception $e) {
        return [
            "error" => true,
            "message" => "An error occurred: " . $e->getMessage()
        ];
    }
}
function get_aws_s3_folder_info($folder_name = null)
{
    try {
        $S3_settings = get_settings('general_settings', true);
        $aws_key = $S3_settings['aws_access_key_id'] ?? '';
        $aws_secret = $S3_settings['aws_secret_access_key'] ?? '';
        $bucket = $S3_settings['aws_bucket'] ?? '';
        $region = $S3_settings['aws_default_region'] ?? 'us-east-1';

        if (!$aws_key || !$aws_secret || !$bucket || !$region) {
            return [
                "error" => true,
                "message" => "AWS configuration missing. Please check configuration variables."
            ];
        }

        $config = [
            'region' => $region,
            'version' => 'latest',
            'credentials' => [
                'key'    => $aws_key,
                'secret' => $aws_secret,
            ],
        ];
        $s3 = new S3Client($config);

        $params = [
            'Bucket' => $bucket,
            'Delimiter' => '/',
        ];

        if ($folder_name) {
            // If a specific folder name is provided, set it as the Prefix
            $params['Prefix'] = rtrim($folder_name, '/') . '/';
        }

        $result = $s3->listObjectsV2($params);
        $folderInfo = [];

        if (isset($result['CommonPrefixes']) || $folder_name) {
            $folders = $folder_name ? [['Prefix' => $params['Prefix']]] : $result['CommonPrefixes'];

            foreach ($folders as $prefix) {
                $folderPath = $prefix['Prefix'];
                $folderName = rtrim(basename($folderPath), '/');

                // Count files in folder
                $fileParams = [
                    'Bucket' => $bucket,
                    'Prefix' => $folderPath,
                ];
                $fileResult = $s3->listObjectsV2($fileParams);
                $fileCount = isset($fileResult['Contents']) ? count($fileResult['Contents']) : 0;

                $folderInfo[] = [
                    'name' => $folderName,
                    'path' => $folderPath,
                    'fileCount' => $fileCount
                ];
            }
        }

        return [
            "error" => false,
            "data" => $folderInfo,
            "message" => "Folder information retrieved successfully"
        ];
    } catch (AwsException $e) {
        return [
            "error" => true,
            "message" => "AWS Error: " . $e->getMessage()
        ];
    } catch (Exception $e) {
        return [
            "error" => true,
            "message" => "An error occurred: " . $e->getMessage()
        ];
    }
}


function get_provider_files_from_aws_s3_folder($segments)
{
    $S3_settings = get_settings('general_settings', true);
    $aws_key = $S3_settings['aws_access_key_id'] ?? '';
    $aws_secret = $S3_settings['aws_secret_access_key'] ?? '';
    $region = $S3_settings['aws_region'] ?? 'us-east-1';
    $aws_url = $S3_settings['aws_url'] ?? '';

    $bucket_name = $S3_settings['aws_bucket'] ?? '';

    // Validate AWS configuration
    if (!$aws_key || !$aws_secret || !$bucket_name || !$region) {
        return [
            "error" => true,
            "message" => "AWS configuration missing. Please check configuration variables."
        ];
    }

    $config = [
        'region' => $region,
        'version' => 'latest',
        'credentials' => [
            'key'    => $aws_key,
            'secret' => $aws_secret,
        ],
    ];

    $s3 = new S3Client($config);
    $new_path = implode('/', array_slice($segments, array_search('get-gallery-files', $segments) + 1));

    $result = $s3->listObjects([
        'Bucket' => $bucket_name,
        'Prefix' => $new_path,
    ]);

    $files = array_map(function ($object) use ($bucket_name, $s3, $new_path, $aws_url) {
        $fileName = basename($object['Key']);

        return [
            'name' => $fileName,
            'type' => $s3->headObject([
                'Bucket' => $bucket_name,
                'Key'    => $object['Key'],
            ])['ContentType'],
            'size' => $object['Size'],
            'full_path' => $aws_url . '/' .  $object['Key'],
            // 'full_path' => $s3->getObjectUrl($bucket_name, $object['Key']),
            'path' => $object['Key'],
            'disk' => 'aws_s3'
        ];
    }, $result['Contents']);

    return $files;
}

function get_aws_s3_file($file_path)
{
    try {
        $S3_settings = get_settings('general_settings', true);
        $aws_key = $S3_settings['aws_access_key_id'] ?? '';
        $aws_secret = $S3_settings['aws_secret_access_key'] ?? '';
        $aws_url = $S3_settings['aws_url'] ?? '';
        $region = $S3_settings['aws_region'] ?? 'us-east-1';
        $bucket_name = $S3_settings['aws_bucket'] ?? '';

        // Validate AWS configuration
        if (!$aws_key || !$aws_secret || !$bucket_name || !$region) {
            return [
                "error" => true,
                "message" => "AWS configuration missing. Please check configuration variables."
            ];
        }

        $config = [
            'region' => $region,
            'version' => 'latest',
            'credentials' => [
                'key'    => $aws_key,
                'secret' => $aws_secret,
            ],
        ];

        $s3 = new S3Client($config);

        // Check if file exists
        if (!$s3->doesObjectExist($bucket_name, $file_path)) {
            return [
                "error" => true,
                "message" => "File does not exist in S3"
            ];
        }

        // Get file metadata
        $result = $s3->headObject([
            'Bucket' => $bucket_name,
            'Key'    => $file_path
        ]);

        $fileName = basename($file_path);

        $fileData = [
            'name' => $fileName,
            'type' => getFileType($fileName),
            'size' => formatFileSize($result['ContentLength']),
            'full_path' => $aws_url . '/' . $file_path,
            'path' => $file_path,
            'lastModified' => $result['LastModified']->format('Y-m-d H:i:s'),
            'contentType' => $result['ContentType']
        ];

        return [
            "error" => false,
            "data" => $fileData,
            "message" => "File retrieved successfully"
        ];
    } catch (AwsException $e) {
        return [
            "error" => true,
            "message" => "AWS Error: " . $e->getMessage()
        ];
    } catch (Exception $e) {
        return [
            "error" => true,
            "message" => "An error occurred: " . $e->getMessage()
        ];
    }
}
function get_aws_s3_folder_files($folder_path)
{
    try {
        $S3_settings = get_settings('general_settings', true);
        $aws_key = $S3_settings['aws_access_key_id'] ?? '';
        $aws_secret = $S3_settings['aws_secret_access_key'] ?? '';
        $aws_url = $S3_settings['aws_url'] ?? '';
        $region = $S3_settings['aws_region'] ?? 'us-east-1';
        $bucket_name = $S3_settings['aws_bucket'] ?? '';

        if (!$aws_key || !$aws_secret || !$bucket_name || !$region) {
            return [
                "error" => true,
                "message" => "AWS configuration missing. Please check configuration variables."
            ];
        }

        $config = [
            'region' => $region,
            'version' => 'latest',
            'credentials' => [
                'key'    => $aws_key,
                'secret' => $aws_secret,
            ],
        ];
        $s3 = new S3Client($config);

        // Ensure folder path ends with '/'
        $folder_path = rtrim($folder_path, '/') . '/';

        $params = [
            'Bucket' => $bucket_name,
            'Prefix' => $folder_path,
        ];

        $result = $s3->listObjectsV2($params);
        $files = [];

        if (isset($result['Contents'])) {
            foreach ($result['Contents'] as $file) {
                // Skip the folder itself
                if ($file['Key'] !== $folder_path) {
                    $fileName = basename($file['Key']);
                    $files[] = [
                        'name' => $fileName,
                        'type' => getFileType($fileName),
                        'size' => formatFileSize($file['Size']),
                        'full_path' => $aws_url . '/' . $file['Key'],
                        'path' => $file['Key'],
                        'lastModified' => $file['LastModified']->format('Y-m-d H:i:s')
                    ];
                }
            }
        }

        return [
            "error" => false,
            "data" => $files,
            "message" => "Files retrieved successfully"
        ];
    } catch (AwsException $e) {
        return [
            "error" => true,
            "message" => "AWS Error: " . $e->getMessage()
        ];
    } catch (Exception $e) {
        return [
            "error" => true,
            "message" => "An error occurred: " . $e->getMessage()
        ];
    }
}
function download_aws_s3_file($file_path, $download_name = null)
{
    try {
        $S3_settings = get_settings('general_settings', true);
        $aws_key = $S3_settings['aws_access_key_id'] ?? '';
        $aws_secret = $S3_settings['aws_secret_access_key'] ?? '';
        $region = $S3_settings['aws_region'] ?? 'us-east-1';
        $bucket_name = $S3_settings['aws_bucket'] ?? '';

        if (!$aws_key || !$aws_secret || !$bucket_name || !$region) {
            return [
                "error" => true,
                "message" => "AWS configuration missing. Please check configuration variables."
            ];
        }

        $s3 = new S3Client([
            'version' => 'latest',
            'region'  => $region,
            'credentials' => [
                'key'    => $aws_key,
                'secret' => $aws_secret,
            ]
        ]);

        // Get file metadata
        $result = $s3->headObject([
            'Bucket' => $bucket_name,
            'Key'    => $file_path
        ]);

        // Set download name if not provided
        if (!$download_name) {
            $download_name = basename($file_path);
        }

        // Get the file content type
        $contentType = $result['ContentType'];

        // Get the file
        $file = $s3->getObject([
            'Bucket' => $bucket_name,
            'Key'    => $file_path
        ]);

        // Set headers for download
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $download_name . '"');
        header('Content-Length: ' . $file['ContentLength']);
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Output the file content
        echo $file['Body'];
        exit;
    } catch (S3Exception $e) {
        return [
            "error" => true,
            "message" => "S3 Error: " . $e->getMessage()
        ];
    } catch (Exception $e) {
        return [
            "error" => true,
            "message" => "An error occurred: " . $e->getMessage()
        ];
    }
}

function formatFileSize($bytes)
{
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

function getFileType($filename)
{
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    $fileTypes = [
        // Images
        'jpg' => 'image',
        'jpeg' => 'image',
        'png' => 'image',
        'gif' => 'image',
        'svg' => 'image',
        'webp' => 'image',

        // Documents
        'pdf' => 'document',
        'doc' => 'document',
        'docx' => 'document',
        'xls' => 'document',
        'xlsx' => 'document',
        'ppt' => 'document',
        'pptx' => 'document',
        'txt' => 'document',

        // Audio
        'mp3' => 'audio',
        'wav' => 'audio',
        'ogg' => 'audio',

        // Video
        'mp4' => 'video',
        'avi' => 'video',
        'mov' => 'video',
        'wmv' => 'video',

        // Archives
        'zip' => 'archive',
        'rar' => 'archive',
        '7z' => 'archive',
        'tar' => 'archive',
        'gz' => 'archive'
    ];

    return $fileTypes[$extension] ?? 'other';
}

function upload_file($file, $upload_path, $error_message, $folder_name, $is_login_image = null)
{


    $settings = get_settings('general_settings', true);
    $file_manager = $settings['file_manager'];
    if ($file_manager == "aws_s3") {
        $file_name = $file->getRandomName();
        $file_tmp_name = $file->getTempName();
        $result = upload_to_aws_s3($file_name, $file_tmp_name, $folder_name);
        if ($result['error']) {
            return ['error' => true, 'message' => "file not uploded"];
        } else {
            return ['error' => false, 'file_path' => $result, 'disk' => 'aws_s3', 'file_name' => $file_name];
        }
    } else if ($file_manager == "local_server") {
        if (!is_dir($upload_path)) {
            if (!mkdir($upload_path, 0755, true)) {
                return ['error' => false, 'message' => $error_message];
            }
        }

        if ($file->isValid() && !$file->hasMoved()) {


            if ($is_login_image == 'yes' && $upload_path == "public/frontend/retro/") {
                $file_name = "Login_BG.jpg";
            } else {

                $file_name = $file->getRandomName();
            }

            if ($file->isValid() && !$file->hasMoved()) {
                $tempPath = $file->getTempName();
                $full_path = $upload_path . '/' . $file_name;
                compressImage($tempPath, $full_path, 70);
                return ['error' => false, 'file_path' => $full_path, 'disk' => 'local_server', 'file_name' => $file_name];
            }
        }
    }
    return ['error' => true, 'message' => 'Failed to upload the file.'];
}
function get_top_rated_providers($latitude = null, $longitude = null)
{
    $db = \Config\Database::connect();
    $disk = fetch_current_file_manager();

    $rating_data = $db->table('partner_details pd')
        ->select('p.id, p.username, p.company, p.image, pd.banner, pd.company_name, pd.at_store, pd.at_doorstep,
                  COUNT(sr.rating) as number_of_rating, 
                  COALESCE(SUM(sr.rating), 0) as total_rating,
                  CASE 
                      WHEN COUNT(sr.rating) > 0 THEN SUM(sr.rating) / COUNT(sr.rating)
                      ELSE 0 
                  END as average_rating,
                  ps.status as subscription_status')
        ->join('users p', 'p.id = pd.partner_id')
        ->join('partner_subscriptions ps', 'ps.partner_id = pd.partner_id')
        ->join('services s', 's.user_id = pd.partner_id', 'left')
        ->join('services_ratings sr', 'sr.service_id = s.id', 'left')
        ->where('ps.status', 'active')
        ->groupBy('p.id')
        ->orderBy('average_rating', 'desc')
        ->get()
        ->getResultArray();
    // Filter out providers with exceeded order limits or inactive subscriptions
    foreach ($rating_data as $key => $row) {
        $partner_subscription = fetch_details('partner_subscriptions', ['partner_id' => $row['id'], 'status' => 'active']);
        if ($partner_subscription) {
            $subscription_purchase_date = $partner_subscription[0]['updated_at'];
            $partner_order_limit = fetch_details('orders', ['partner_id' => $row['id'], 'parent_id' => null, 'created_at >' => $subscription_purchase_date]);
            $subscription_data = fetch_details('partner_subscriptions', ['partner_id' => $row['id'], 'status' => 'active']);
            $subscription_order_limit = $subscription_data[0]['max_order_limit'];
            if ($subscription_data[0]['order_type'] == 'limited' && count($partner_order_limit) >= $subscription_order_limit) {
                unset($rating_data[$key]);
            }
        } else {
            unset($rating_data[$key]);
        }
    }
    $rating_data = array_values($rating_data);
    if (!empty($rating_data)) {
        foreach ($rating_data as &$provider) {
            if ($disk == 'local_server') {
                $provider['image'] = get_image_url($provider['image']);
            } else if ($disk) {
                $provider['image'] = fetch_cloud_front_url('profile', $provider['image']);
            } else {
                $provider['image'] = get_image_url($provider['banner']);
            }

            if ($disk == 'local_server') {
                $provider['banner_image'] = get_image_url($provider['banner']);
            } else if ($disk) {
                $provider['banner_image'] = fetch_cloud_front_url('banner', $provider['banner']);
            } else {
                $provider['banner_image'] = get_image_url($provider['banner']);
            }
            unset($provider['minimum_order_amount'], $provider['banner']);
            $total_services_of_providers = fetch_details(
                'services',
                [
                    'user_id' => $provider['id'],
                    'at_store' => $provider['at_store'],
                    'at_doorstep' => $provider['at_doorstep']
                ],
                ['id']
            );
            $provider['total_services'] = count($total_services_of_providers);
            // Safely calculate average rating
            $provider['average_rating'] = $provider['number_of_rating'] > 0
                ? ($provider['total_rating'] / $provider['number_of_rating'])
                : 0;
            $provider_services = fetch_details(
                'services',
                ['user_id' => $provider['id']],
                ['title']
            );
            $provider['services'] = $provider_services;
        }
    }
    return $rating_data;
}
function get_image_url($image_path)
{
    if (file_exists(FCPATH . 'public/backend/assets/profiles/' . $image_path)) {
        return base_url('public/backend/assets/profiles/' . $image_path);
    }
    if (file_exists(FCPATH . $image_path)) {
        return base_url($image_path);
    }
    if (file_exists(FCPATH . "public/uploads/users/partners/" . $image_path)) {
        return base_url("public/uploads/users/partners/" . $image_path);
    }
    return base_url("public/backend/assets/profiles/default.png");
}
function generate_unique_slug(string $slug, string $table, int|null $excludeID = null, int $count = 0): string
{
    // Create a URL-safe slug
    $slug = url_title($slug, '-', true);
    $newSlug = $count ? $slug . '-' . $count : $slug;
    // Get the CI4 database connection
    $db = \Config\Database::connect();
    $builder = $db->table($table); // Use the provided table name
    // Build query to check for existing slug
    $builder->where('slug', $newSlug);
    if ($excludeID !== null) {
        $builder->where('id !=', $excludeID);
    }
    // Check if there are any results
    $query = $builder->get();
    if ($query->getNumRows() > 0) {
        // If the slug exists, call the function recursively
        return generate_unique_slug($slug, $table, $excludeID, $count + 1);
    }
    return $newSlug;
}
function delete_file_based_on_server($folder_name, $file_name, $disk)
{

    $settings = get_settings('general_settings', true);
    if ($disk == "aws_s3") {
        $aws_key = $settings['aws_access_key_id'] ?? '';
        $aws_secret = $settings['aws_secret_access_key'] ?? '';
        $bucket = $settings['aws_bucket'] ?? '';
        $region = $settings['aws_region'] ?? 'us-east-1';
        if (!$aws_key || !$aws_secret || !$bucket || !$region) {
            return ['error' => true, 'message' => "AWS configuration missing. Please check configuration variables."];
        }
        $config = [
            'region' => $region,
            'version' => 'latest',
            'credentials' => [
                'key'    => $aws_key,
                'secret' => $aws_secret,
            ],
        ];
        $s3 = new S3Client($config);
        // The file key to delete on S3
        $file_key = $folder_name . '/' . $file_name; // Construct the full path
        try {
            $s3->deleteObject(['Bucket' => $bucket, 'Key'    => $file_key]);
            return ['error' => false, 'message' => "File deleted successfully from S3."];
        } catch (Aws\S3\Exception\S3Exception $e) {
            return ['error' => false, 'message' => "Failed to delete file from S3: " . $e->getMessage()];
        }
    } else {

        if ($folder_name == "categories") {
            $path = "public/uploads/categories";
        }
        if ($folder_name == "site") {
            $path = "public/uploads/site";
        }
        if ($folder_name == "profile") {
            $path = "public/backend/assets/profile/";
        }
        if ($folder_name == "banner") {
            $path = "public/backend/assets/banner/";
        }
        if ($folder_name == "national_id") {
            $path = "public/backend/assets/national_id/";
        }
        if ($folder_name == "address_id") {
            $path = "public/backend/assets/address_id//";
        }
        if ($folder_name == "passport") {
            $path = "public/backend/assets/passport/";
        }
        if ($folder_name == "partner") {
            $path = "public/uploads/partner";
        }
        if ($folder_name == "sliders") {
            $path = "public/uploads/sliders";
        }
        if ($folder_name == "services") {
            $path = "public/uploads/services";
        }
        if ($folder_name == "feature_section") {
            $path = "public/uploads/feature_section";
        }

        if ($folder_name == "ratings") {
            $path = "public/uploads/ratings/";
        }
        if($folder_name == "promocodes") {
            $path = "public/uploads/promocodes/";
        }
        if (!empty($file_name)) {
            $file_path = $path . '/' . $file_name;
            if (file_exists($file_path)) {
                if (unlink($file_path)) {
                    return [
                        "error" => false,
                        "message" => "File deleted successfully from the local server"
                    ];
                } else {
                    return [
                        "error" => true,
                        "message" => "Failed to delete file from the local server"
                    ];
                }
            } else {
                return [
                    "error" => true,
                    "message" => "File not found on the local server"
                ];
            }
        }
    }
}
// function fetch_cloud_front_url($folder_name, $file_name)
// {
//     $settings = get_settings('general_settings', true);
//     if (empty($settings['aws_url'])) {
//         return $file_name;
//     }
//     $aws_url = rtrim($settings['aws_url'], '/'); // Ensure no trailing slash
//     $folder_name = trim($folder_name, '/');     // Remove leading/trailing slashes
//     $file_name = ltrim($file_name, '/');        // Remove leading slashes
//     return $aws_url . '/' . $folder_name . '/' . $file_name;
// }
// function fetch_cloud_front_url($folder_name, $file_name)
// {
//     $settings = get_settings('general_settings', true);

//     if (empty($settings['aws_url'])) {
//         return $file_name ?? ''; // Return empty string if file_name is null
//     }

//     $aws_url = rtrim($settings['aws_url'] ?? '', '/'); // Ensure no trailing slash
//     $folder_name = trim($folder_name ?? '', '/');     // Remove leading/trailing slashes
//     $file_name = ltrim($file_name ?? '', '/');        // Ensure file_name is not null

//     return $aws_url . '/' . $folder_name . '/' . $file_name;
// }

function fetch_cloud_front_url($folder_name, $file_name)
{
    $settings = get_settings('general_settings', true);

    if (empty($settings['aws_url'])) {
        return $file_name ?? ''; // Return empty string if file_name is null
    }

    $aws_url = rtrim($settings['aws_url'] ?? '', '/'); // Ensure no trailing slash
    $folder_name = trim($folder_name ?? '', '/');     // Remove leading/trailing slashes
    $file_name = ltrim($file_name ?? '', '/');        // Ensure file_name is not null

    // Remove "public/uploads/", "backend/", or "assets/" from the file_name if present
    $file_name = preg_replace('#^(public|uploads|backend|assets|services|chat_attachment|provider_work_evidence|national_id|banner|address_id|passport|promocodes|ratings|profile|profiles)(/.*)?/#', '', $file_name);

    return $aws_url . '/' . $folder_name . '/' . $file_name;
}


function get_file_url($disk, $file_key, $default_path = '', $cloud_front_type = '')
{
    if ($disk === "local_server") {
        return ($file_key && file_exists(FCPATH . $file_key)) ? base_url($file_key) : base_url($default_path);
    } elseif ($disk === "aws_s3") {
        return fetch_cloud_front_url($cloud_front_type, $file_key);
    } else {
        return ($file_key) ? base_url($file_key) : base_url($default_path);
    }
}


function fetch_current_file_manager()
{
    $setting = get_settings('storage_disk');

    if (empty($setting) || !isset($setting)) {
        $setting = 'local_server';
    }
    return $setting;
}

function fetch_partner_formatted_data($user_id)
{
    $userdata = fetch_details('users', ['id' => $user_id], [
        'id',
        'username',
        'email',
        'balance',
        'active',
        'first_name',
        'last_name',
        'company',
        'phone',
        'country_code',
        'fcm_id',
        'image',
        'city_id',
        'city',
        'latitude',
        'longitude'
    ])[0];

    $partnerData = fetch_details('partner_details', ['partner_id' => $user_id])[0];
    $subscription = fetch_details('partner_subscriptions', ['partner_id' => $user_id], [], 1, 0, 'id', 'DESC');
    $disk = fetch_current_file_manager();

    if ($disk == 'local_server') {
        $userdata['image'] = (file_exists($userdata['image'])) ? base_url($userdata['image']) : "";
    } else if ($disk == 'aws_s3') {
        $userdata['image'] = fetch_cloud_front_url('profile', $userdata['image']);
    } else {
        $userdata['image'] = "";
    }
    if ($disk == 'local_server') {
        $partnerData['banner'] = (file_exists($partnerData['banner'])) ? base_url($partnerData['banner']) : "";
    } else if ($disk == 'aws_s3') {
        $partnerData['banner'] = fetch_cloud_front_url('banner', $partnerData['banner']);
    } else {
        $partnerData['banner'] = "";
    }
    if ($disk == 'local_server') {
        if (!empty($partnerData['address_id']) && is_string($partnerData['address_id'])) {
            $partnerData['address_id'] = base_url($partnerData['address_id']);
        } else {
            $partnerData['address_id'] = null;
        }
    } else if ($disk == 'aws_s3') {
        $partnerData['address_id'] = fetch_cloud_front_url('address_id', $partnerData['address_id']);
    } else {
        $partnerData['address_id'] = null;
    }
    if ($disk == 'local_server') {
        if (!empty($partnerData['passport']) && is_string($partnerData['passport'])) {
            $partnerData['passport'] = base_url($partnerData['passport']);
        } else {
            $partnerData['passport'] = null;
        }
    } else if ($disk == 'aws_s3') {
        $partnerData['passport'] = fetch_cloud_front_url('passport', $partnerData['passport']);
    } else {
        $partnerData['passport'] = "";
    }
    if ($disk == 'local_server') {
        $partnerData['national_id'] = base_url($partnerData['national_id']);
    } else if ($disk == 'aws_s3') {
        $partnerData['national_id'] = fetch_cloud_front_url('national_id', $partnerData['national_id']);
    } else {
        $partnerData['national_id'] = "";
    }

    $get_settings = get_settings('general_settings', true);



    $partnerData['pre_booking_chat'] = ($get_settings['allow_pre_booking_chat'] == 1) ? ($partnerData['pre_chat'] ?? "") : 0;
    $partnerData['post_booking_chat'] = ($get_settings['allow_post_booking_chat'] == 1) ? ($partnerData['chat'] ?? "") : 0;
    if (!empty($partnerData['other_images'])) {
        if (isset($partnerData['other_images'])) {
            $decodedImages = json_decode($partnerData['other_images'], true);
            $otherImages = [];
            if (is_array($decodedImages)) {
                foreach ($decodedImages as $image) {
                    if ($disk == "local_server") {
                        $otherImages[] = base_url($image);
                    } else if ($disk == "aws_s3") {
                        $otherImages[] = fetch_cloud_front_url('partner', $image);
                    } else {
                        $otherImages[] = base_url($image);
                    }
                }
            }
            $partnerData['other_images'] = $otherImages;
        }
    } else {
        $partnerData['other_images'] = [];
    }
    if (!empty($partnerData['custom_job_categories'])) {
        $partnerData['custom_job_categories'] =
            json_decode($partnerData['custom_job_categories'], true);
    } else {
        $partnerData['custom_job_categories'] = [];
    }

    $location_information = [
        'city' => $userdata['city'],
        'latitude' => $userdata['latitude'],
        'longitude' => $userdata['longitude'],
        'address' => $partnerData['address']
    ];

    $bank_information = [
        'tax_name' => $partnerData['tax_name'] == "null" ? "" : $partnerData['tax_name'],
        'tax_number' => $partnerData['tax_number'] == "null" ? "" : $partnerData['tax_number'],
        'account_number' => $partnerData['account_number'] == "null" ? "" : $partnerData['account_number'],
        'account_name' => $partnerData['account_name'] == "null" ? "" : $partnerData['account_name'],
        'bank_code' => $partnerData['bank_code'] == "null" ? "" : $partnerData['bank_code'],
        'swift_code' => $partnerData['swift_code'] == null ? "" : $partnerData['swift_code'],
        'bank_name' => $partnerData['bank_name'] == "null" ? "" : $partnerData['bank_name']
    ];

    $subscription_information = [
        'subscription_id' => $subscription[0]['subscription_id'] ?? "",
        'isSubscriptionActive' => $subscription[0]['status'] ?? "deactive",
        'created_at' => $subscription[0]['created_at'] ?? "",
        'updated_at' => $subscription[0]['updated_at'] ?? "",
        'is_payment' => $subscription[0]['is_payment'] ?? "",
        'id' => $subscription[0]['id'] ?? "",
        'partner_id' => $subscription[0]['partner_id'] ?? "",
        'purchase_date' => $subscription[0]['purchase_date'] ?? "",
        'expiry_date' => $subscription[0]['expiry_date'] ?? "",
        'name' => $subscription[0]['name'] ?? "",
        'description' => $subscription[0]['description'] ?? "",
        'duration' => $subscription[0]['duration'] ?? "",
        'price' => $subscription[0]['price'] ?? "",
        'discount_price' => $subscription[0]['discount_price'] ?? "",
        'order_type' => $subscription[0]['order_type'] ?? "",
        'max_order_limit' => $subscription[0]['max_order_limit'] ?? "",
        'is_commision' => $subscription[0]['is_commision'] ?? "",
        'commission_threshold' => $subscription[0]['commission_threshold'] ?? "",
        'commission_percentage' => $subscription[0]['commission_percentage'] ?? "",
        'publish' => $subscription[0]['publish'] ?? "",
        'tax_id' => $subscription[0]['tax_id'] ?? "",
        'tax_type' => $subscription[0]['tax_type'] ?? ""
    ];

    if (!empty($subscription[0])) {
        $price = calculate_partner_subscription_price($subscription[0]['partner_id'], $subscription[0]['subscription_id'], $subscription[0]['id']);
    }
    $subscription_information['tax_value'] = $price[0]['tax_value'] ?? "";
    $subscription_information['price_with_tax'] = $price[0]['price_with_tax'] ?? "";
    $subscription_information['original_price_with_tax'] = $price[0]['original_price_with_tax'] ?? "";
    $subscription_information['tax_percentage'] = $price[0]['tax_percentage'] ?? "";

    $data1 = [
        'subscription_information' => $subscription_information,
        'location_information' => $location_information,
        'user' => array_diff_key($userdata, array_flip(['city', 'latitude', 'longitude'])),
        'provder_information' => array_diff_key($partnerData, array_flip([
            'tax_name',
            'tax_number',
            'account_number',
            'account_name',
            'bank_code',
            'swift_code',
            'bank_name',
            'address',
            'chat',
            'pre_chat'
        ])),
        'bank_information' => $bank_information,
        'working_days' => array_map(function ($val) {
            return [
                'day' => $val['day'],
                'isOpen' => $val['is_open'],
                'start_time' => $val['opening_time'],
                'end_time' => $val['closing_time']
            ];
        }, fetch_details('partner_timings', ['partner_id' => $userdata['id']]))
    ];


    return $data1;
}


function get_cart_formatted_data($user_id, $search, $limit, $offset, $sort, $order, $where, $message, $error)
{
    $cart_details = fetch_cart(true, $user_id, $search, $limit, $offset, $sort, $order, $where);
    if (!empty($cart_details['data'])) {
        return response_helper(
            $message,
            $error,
            remove_null_values($cart_details['data']),
            200,
            remove_null_values(
                [
                    'provider_id' => $cart_details['provider_id'],
                    'provider_names' => $cart_details['provider_names'],
                    'service_ids' => $cart_details['service_ids'],
                    'qtys' => $cart_details['qtys'],
                    'visiting_charges' => $cart_details['visiting_charges'],
                    'advance_booking_days' => $cart_details['advance_booking_days'],
                    'company_name' => $cart_details['company_name'],
                    'total_duration' => $cart_details['total_duration'],
                    'is_pay_later_allowed' => $cart_details['is_pay_later_allowed'],
                    'total_quantity' => $cart_details['total_quantity'],
                    'sub_total' => $cart_details['sub_total'],
                    'overall_amount' => $cart_details['overall_amount'],
                    'total' => $cart_details['total'],
                    "at_store" => (!empty($cart_details) && isset($cart_details)) ? $cart_details['at_store'] : "0",
                    "at_doorstep" => (!empty($cart_details) && isset($cart_details)) ? $cart_details['at_doorstep'] : "0",
                    "is_online_payment_allowed" => (!empty($cart_details) && isset($cart_details)) ? $cart_details['is_online_payment_allowed'] : "0",
                ]
            )
        );
    } else {
        return response_helper('service not found');
    }
}
