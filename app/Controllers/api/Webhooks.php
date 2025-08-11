<?php

namespace App\Controllers\api;

use App\Controllers\BaseController;
use App\Libraries\Flutterwave;
use App\Libraries\Paystack;
use App\Libraries\Razorpay;
use App\Libraries\Stripe;
use App\Libraries\Paypal;
use Config\ApiResponseAndNotificationStrings;

class Webhooks extends BaseController
{
    private $stripe,$paypal_lib,$trans;
    public function __construct()
    {
        $this->stripe = new Stripe;
        $this->paypal_lib = new Paypal();
        helper('api');
        helper("function");
        $this->settings = get_settings('general_settings', true);
        date_default_timezone_set($this->settings['system_timezone']);
        $this->trans = new ApiResponseAndNotificationStrings();
    }
    public function stripe()
    {
        $credentials = $this->stripe->get_credentials();
        $request_body = file_get_contents('php://input');
        $event = json_decode($request_body, FALSE);
        if (!empty($event->data->object->payment_intent)) {
            $txn_id = (isset($event->data->object->payment_intent)) ? $event->data->object->payment_intent : "";
            if (!empty($txn_id)) {
                if (isset($event->data->object->metadata) && !empty($event->data->object->metadata->order_id)) {
                    // Process the metadata and retrieve order details
                    $amount = ($event->data->object->amount / 100);
                    $currency = $event->data->object->currency;
                    $order_id = $event->data->object->metadata->order_id;
                    $order_data = fetch_details('orders', ["id" => $order_id]);
                    $user_id = $order_data[0]['user_id'];
                    $partner_id = $order_data[0]['partner_id'];
                    // Continue with your code logic
                }
            }
        } else {
            $order_id = 0;
            $amount = 0;
            $currency = (isset($event->data->object->currency)) ? $event->data->object->currency : "";
        }
        $http_stripe_signature = isset($_SERVER['HTTP_STRIPE_SIGNATURE']) ? $_SERVER['HTTP_STRIPE_SIGNATURE'] : "";
        $result = $this->stripe->construct_event($request_body, $http_stripe_signature, $credentials['webhook_key']);
        if ($result == "Matched") {
            log_message('error', '$event ' . var_export($event, true));
            if ($event->type == 'charge.succeeded') {
                //for subscription
                if (isset($event->data->object->metadata->transaction_id) && !empty($event->data->object->metadata->transaction_id)) {
                    $transaction_details_for_subscription = fetch_details('transactions', ['id' => $event->data->object->metadata->transaction_id]);
                    $details_for_subscription = fetch_details('subscriptions', ['id' => $transaction_details_for_subscription[0]['subscription_id']]);
                    if (!empty($transaction_details_for_subscription)) {
                        if (isset($transaction_details_for_subscription[0])) {
                            log_message('error', 'FOR SUBSCRIPTION');
                            update_details(['status' => 'success', 'txn_id' => $event->data->object->payment_intent], ['id' => $event->data->object->metadata->transaction_id], 'transactions');
                            // update_details(['status' => 'active'], ['subscription_id' => $transaction_details_for_subscription[0]['subscription_id'],'partner_id'=>$transaction_details_for_subscription[0]['user_id'],'status'=>'pending'], 'partner_subscriptions');
                            $purchaseDate = date('Y-m-d');
                            $subscriptionDuration = $details_for_subscription[0]['duration'];
                            $expiryDate = date('Y-m-d', strtotime($purchaseDate . ' + ' . $subscriptionDuration . ' days')); // Add the duration to the purchase date
                            if ($subscriptionDuration == "unlimited") {
                                $subscriptionDuration = 0;
                            }
                            update_details(['status' => 'active', 'is_payment' => '1', 'purchase_date' => $purchaseDate, 'expiry_date' => $expiryDate, 'updated_at' => date('Y-m-d h:i:s')], [
                                'subscription_id' => $transaction_details_for_subscription[0]['subscription_id'],
                                'partner_id' => $transaction_details_for_subscription[0]['user_id'],
                                'transaction_id' => $event->data->object->metadata->transaction_id,
                            ], 'partner_subscriptions');
                        }
                    }
                }
                //for additional charges
                else if (!empty($event->data->object->metadata->additional_charges_transaction_id)) {
                    log_message('error', 'FOR ADDITIONAL CHARGES');
                    update_details(['status' => 'success', 'txn_id' => $txn_id], ['id' => $event->data->object->metadata->additional_charges_transaction_id], 'transactions');
                    $order_data = fetch_details('orders', ["id" => $event->data->object->metadata->order_id]);
                    update_details(['payment_status_of_additional_charge' => '1'], ['id' => $order_id], 'orders');
                }
                //for booking
                else {



                    $data = [
                        'transaction_type' => 'transaction',
                        'user_id' => $user_id,
                        'partner_id' => $partner_id,
                        'order_id' => $order_id,
                        'type' => 'stripe',
                        'txn_id' => $txn_id,
                        'amount' => $amount,
                        'status' => 'success',
                        'currency_code' => $currency,
                        'message' => 'Order placed successfully',
                    ];
                    $insert_id = add_transaction($data);
                    send_web_notification('New Booking Notification', 'We are pleased to inform you that you have received a new Booking.');


                    update_custom_job_status($order_id, 'booked');

                    //customer email
                    $userdata = fetch_details('users', ['id' => $user_id], ['email', 'username']);
                    if (!empty($userdata[0]['email']) && check_notification_setting('new_booking_confirmation_to_customer', 'email') && is_unsubscribe_enabled($user_id) == 1) {
                        send_custom_email('new_booking_confirmation_to_customer', $partner_id, $userdata[0]['email'], null, $user_id, $order_id);
                    }
                    if (check_notification_setting('new_booking_confirmation_to_customer', 'sms')) {
                        send_custom_sms('new_booking_confirmation_to_customer', $partner_id, $userdata[0]['email'], null, $user_id, $order_id);
                    }
                    //for provider
                    $user_partner_data = fetch_details('users', ['id' => $partner_id], ['email', 'username']);
                    if (!empty($user_partner_data[0]['email']) && check_notification_setting('new_booking_received_for_provider', 'email') && is_unsubscribe_enabled($partner_id) == 1) {
                        send_custom_email('new_booking_received_for_provider', $partner_id, $user_partner_data[0]['email'], null, $user_id, $order_id);
                    }
                    if (check_notification_setting('new_booking_received_for_provider', 'sms')) {
                        send_custom_sms('new_booking_received_for_provider', $partner_id, $user_partner_data[0]['email'], null, $user_id, $order_id);
                    }
                    //for app notification
                    $db      = \Config\Database::connect();
                    $to_send_id = $partner_id;
                    $builder = $db->table('users')->select('fcm_id,email,username,platform');
                    $users_fcm = $builder->where('id', $to_send_id)->get()->getResultArray();
                    foreach ($users_fcm as $ids) {
                        if ($ids['fcm_id'] != "") {
                            $fcm_ids['fcm_id'] = $ids['fcm_id'];
                            $fcm_ids['platform'] = $ids['platform'];
                            $email = $ids['email'];
                        }
                    }
                    if (!empty($fcm_ids)) {
                        $registrationIDs = $fcm_ids;
                        $registrationIDs_chunks = array_chunk($users_fcm, 1000);
                        $fcmMsg = array(
                            'content_available' => "true",
                            'title' => $this->trans->newBookingNotification,
                            'body' => $this->trans->newBookingReceivedMessage,
                            'type' => 'order',
                            'order_id' => "$order_id",
                            'type_id' => "$to_send_id",
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        );
                        send_notification($fcmMsg, $registrationIDs_chunks);
                    }
                    if ($insert_id) {
                        //  log_message('error', 'Transaction successfully done ' . var_export($event, true));
                        $response['error'] = false;
                        $response['transaction_status'] = $event->type;
                        $response['message'] = "Transaction successfully done";
                        return $this->response->setJSON($response);
                    } else {
                        $response['error'] = true;
                        $response['message'] = "something went wrong";
                        return $this->response->setJSON($response);
                    }
                }
            } elseif ($event->type == 'charge.failed') {


                if (!empty($event->data->object->metadata->additional_charges_transaction_id)) {
                    log_message('error', 'FOR ADDITIONAL CHARGES');
                    update_details(['status' => 'failed', 'txn_id' => $txn_id], ['id' => $event->data->object->metadata->additional_charges_transaction_id], 'transactions');
                    $order_data = fetch_details('orders', ["id" => $event->data->object->metadata->order_id]);
                    update_details(['payment_status_of_additional_charge' => '2'], ['id' => $order_id], 'orders');
                } else {
                    log_message('error', 'Stripe Webhook | charge.failed ');
                    $data = [
                        'transaction_type' => 'transaction',
                        'user_id' => $user_id,
                        'partner_id' => $partner_id,
                        'order_id' => $order_id,
                        'type' => 'stripe',
                        'txn_id' => $txn_id,
                        'amount' => $amount,
                        'status' => 'failed',
                        'currency_code' => $currency,
                        'message' => 'Booking is cancelled',
                    ];
                    $insert_id = add_transaction($data);
                    update_details(['payment_status' => 2], ['id' => $order_id], 'orders');
                    update_details(['status' => 'cancelled'], ['id' => $order_id], 'orders');
                    update_custom_job_status($order_id, 'cancelled');
                }
            } elseif ($event->type == 'charge.pending') {
                if (!empty($event->data->object->metadata->additional_charges_transaction_id)) {
                    log_message('error', 'FOR ADDITIONAL CHARGES');
                    update_details(['status' => 'pending', 'txn_id' => $txn_id], ['id' => $event->data->object->metadata->additional_charges_transaction_id], 'transactions');
                    $order_data = fetch_details('orders', ["id" => $event->data->object->metadata->order_id]);
                    update_details(['payment_status_of_additional_charge' => '0'], ['id' => $order_id], 'orders');
                }else{
                    $data = [
                        'transaction_type' => 'transaction',
                        'user_id' => $user_id,
                        'partner_id' => $partner_id,
                        'order_id' => $order_id,
                        'type' => 'stripe',
                        'txn_id' => $txn_id,
                        'amount' => $amount,
                        'status' => 'pending',
                        'currency_code' => $currency,
                        'message' => 'Order placed successfully',
                    ];
                    $insert_id = add_transaction($data);
                    update_details(['payment_status' => 0], ['id' => $order_id], 'orders');
                    update_custom_job_status($order_id, 'pending');
                    return false;
                }

                
            } elseif ($event->type == 'charge.expired') {
                $data = [
                    'transaction_type' => 'transaction',
                    'user_id' => $user_id,
                    'partner_id' => $partner_id,
                    'order_id' => $order_id,
                    'type' => 'stripe',
                    'txn_id' => $txn_id,
                    'amount' => $amount,
                    'status' => 'failed',
                    'currency_code' => $currency,
                    'message' => 'Order placed successfully',
                ];
                $insert_id = add_transaction($data);
                update_custom_job_status($order_id, 'cancelled');
                return false;
            } elseif ($event->type == 'charge.refunded') {
                log_message('error', 'Stripe Webhook | REFUND CALLED  --> ');
                log_message('error', 'Transaction_id | ' . var_export($txn_id, true));
                $success_transaction = fetch_details('transactions', ['transaction_type' => 'transaction', 'type' => 'stripe', 'status' => 'success', 'txn_id' => $txn_id]);
                if (!empty($success_transaction)) {
                    $already_exist_refund_transaction = fetch_details('transactions', ['transaction_type' => 'refund', 'type' => 'stripe', 'message' => 'stripe_refund', 'txn_id' => $txn_id]);
                    if (!empty($already_exist_refund_transaction)) {
                        $refund_data = [
                            'status' => 'succeeded',
                        ];
                        update_details($refund_data, ['id' =>  $already_exist_refund_transaction[0]['id']], 'transactions');
                    } else {
                        $data = [
                            'transaction_type' => 'refund',
                            'user_id' => $user_id,
                            'partner_id' => $partner_id,
                            'order_id' => $order_id,
                            'type' => 'stripe',
                            'txn_id' => $txn_id,
                            'amount' => $amount,
                            'status' => 'succeeded',
                            'currency_code' => $currency,
                            'message' => 'stripe_refund',
                        ];
                        $insert_id = add_transaction($data);
                        update_custom_job_status($order_id, 'refunded');
                    }
                }
            } else {
                $response['error'] = true;
                $response['transaction_status'] = $event->type;
                $response['message'] = "Transaction could not be detected.";
                echo json_encode($response);
                return false;
            }
        } else {
            log_message('error', 'Stripe Webhook | Invalid Server Signature  --> ');
            return false;
        }
    }
    public function paystack()
    {
        log_message('error', 'paystack Webhook Called');
        $system_settings = get_settings('system_settings', true);
        $paystack = new Paystack;
        $credentials = $paystack->get_credentials();
        $secret_key = $credentials['secret'];
        $request_body = file_get_contents('php://input');
        $event = json_decode($request_body, true);
        log_message('error', 'paystack Webhook --> ' . var_export($event, true));
        if (!empty($event['data'])) {
            // $txn_id = (isset($event['data']['reference'])) ? $event['data']['reference'] : "";
            $txn_id = (isset($event['data']['id'])) ? $event['data']['id'] : "";
            // log_message('error', 'paystack Webhook SERVER Variable --> ' . var_export($txn_id, true));
            if (isset($txn_id) && !empty($txn_id)) {
                $transaction = fetch_details('transactions', ['txn_id' => $txn_id]);
                if (!empty($transaction)) {
                    $order_id = $transaction[0]['order_id'];
                    $user_id = $transaction[0]['user_id'];
                } else {
                    if (!empty($event['data']['metadata']['transaction_id'])) {
                    } else {
                        if (isset($event['data']['metadata']['order_id']) && !empty($event['data']['metadata']['order_id'])) {
                            $order_id = 0;
                            $order_id = $event['data']['metadata']['order_id'];
                            $order_data = fetch_details('orders', ["id" => $order_id]);
                            $user_id = $order_data[0]['user_id'];
                            $partner_id = $order_data[0]['partner_id'];
                        }
                    }
                }
            }
            $amount = $event['data']['amount'];
            $currency = $event['data']['currency'];
        } else {
            $order_id = 0;
            $amount = 0;
            $currency = (isset($event['data']['currency'])) ? $event['data']['currency'] : "";
        }
        if ($event['event'] == 'charge.success') {
            //for subscription
            if (!empty($event['data']['metadata']['transaction_id'])) {
                $transaction_details_for_subscription = fetch_details('transactions', ['id' => $event['data']['metadata']['transaction_id']]);
                $details_for_subscription = fetch_details('subscriptions', ['id' => $transaction_details_for_subscription[0]['subscription_id']]);
                log_message('error', 'FOR SUBSCRIPTION');
                update_details(['status' => 'success', 'txn_id' => $txn_id], ['id' => $event['data']['metadata']['transaction_id']], 'transactions');
                $purchaseDate = date('Y-m-d');
                $subscriptionDuration = $details_for_subscription[0]['duration'];
                $expiryDate = date('Y-m-d', strtotime($purchaseDate . ' + ' . $subscriptionDuration . ' days')); // Add the duration to the purchase date
                if ($subscriptionDuration == "unlimited") {
                    $subscriptionDuration = 0;
                }
                update_details(['status' => 'active', 'is_payment' => '1', 'purchase_date' => $purchaseDate, 'expiry_date' => $expiryDate, 'updated_at' => date('Y-m-d h:i:s')], [
                    'subscription_id' => $transaction_details_for_subscription[0]['subscription_id'],
                    'partner_id' => $transaction_details_for_subscription[0]['user_id'],
                    'status !=' => 'active',
                    'transaction_id' => $event['data']['metadata']['transaction_id'],
                ], 'partner_subscriptions');
                // log_message('error', 'METAFDATA --> ' . var_export($event['data']['metadata']['transaction_id'], true));
            }
            //for additional charges
            else if (!empty($event['data']['metadata']['additional_charges_transaction_id'])) {
                // $transaction_details_for_additional_charges = fetch_details('transactions', ['id' => $event['data']['metadata']['additional_charges_transaction_id']]);
                log_message('error', 'FOR ADDITIONAL CHARGES');
                update_details(['status' => 'success', 'txn_id' => $txn_id, 'reference' => $event['data']['reference']], ['id' => $event['data']['metadata']['additional_charges_transaction_id']], 'transactions');
                $order_data = fetch_details('orders', ["id" => $order_id]);
                update_details(['payment_status_of_additional_charge' => '1'], ['id' => $order_id], 'orders');
            }
            //for order
            else {
                if (!empty($order_id)) {     /* To do the wallet recharge if the order id is set in the pattern */
                    /* process the order and mark it as received */
                    $order = fetch_details('orders', ['id' => $order_id]);
                    log_message('error', 'Paystack Webhook | order --> ' . var_export($order, true));
                    /* No need to add because the transaction is already added just update the transaction status */
                    if (!empty($transaction)) {
                        $transaction_id = $transaction[0]['id'];
                        update_details(['status' => 'success'], ['id' => $transaction_id], 'transactions');
                    } else {
                        /* add transaction of the payment */
                        // $amount = ($event['data']['amount'] / 100);
                        $amount = ($event['data']['amount']);
                        $data = [
                            'transaction_type' => 'transaction',
                            'user_id' => $user_id,
                            'partner_id' => $partner_id,
                            'order_id' => $order_id,
                            'type' => 'paystack',
                            'txn_id' => $txn_id,
                            'amount' => $amount,
                            'status' => 'success',
                            'currency_code' => $currency,
                            'message' => 'Order placed successfully',
                            'reference' => (isset($event['data']['reference'])) ? $event['data']['reference'] : "",
                        ];
                        $insert_id = add_transaction($data);
                        if ($insert_id) {
                            update_details(['payment_status' => 1], ['id' => $order_id], 'orders');
                            update_custom_job_status($order_id, 'booked');
                            send_web_notification('New Booking Notification', 'We are pleased to inform you that you have received a new Booking.');
                            //customer email
                            $userdata = fetch_details('users', ['id' => $user_id], ['email', 'username']);
                            if (!empty($userdata[0]['email']) && check_notification_setting('new_booking_confirmation_to_customer', 'email') && is_unsubscribe_enabled($user_id) == 1) {
                                send_custom_email('new_booking_confirmation_to_customer', $partner_id, $userdata[0]['email'], null, $user_id, $order_id);
                            }
                            if (check_notification_setting('new_booking_confirmation_to_customer', 'sms')) {
                                send_custom_sms('new_booking_confirmation_to_customer', $partner_id, $userdata[0]['email'], null, $user_id, $order_id);
                            }
                            //for provider
                            $user_partner_data = fetch_details('users', ['id' => $partner_id], ['email', 'username']);
                            if (!empty($user_partner_data[0]['email']) && check_notification_setting('new_booking_received_for_provider', 'email') && is_unsubscribe_enabled($partner_id) == 1) {
                                send_custom_email('new_booking_received_for_provider', $partner_id, $user_partner_data[0]['email'], null, $user_id, $order_id);
                            }
                            if (check_notification_setting('new_booking_received_for_provider', 'sms')) {
                                send_custom_sms('new_booking_received_for_provider', $partner_id, $user_partner_data[0]['email'], null, $user_id, $order_id);
                            }
                            
                            //for app notification
                            $db      = \Config\Database::connect();
                            $to_send_id = $partner_id;
                            $builder = $db->table('users')->select('fcm_id,email,username,platform');
                            $users_fcm = $builder->where('id', $to_send_id)->get()->getResultArray();
                            foreach ($users_fcm as $ids) {
                                if ($ids['fcm_id'] != "") {
                                    $fcm_ids['fcm_id'] = $ids['fcm_id'];
                                    $fcm_ids['platform'] = $ids['platform'];
                                    $email = $ids['email'];
                                }
                            }
                            if (!empty($fcm_ids)  && check_notification_setting('new_booking_received_for_provider', 'notification')) {
                                $registrationIDs = $fcm_ids;
                                $registrationIDs_chunks = array_chunk($users_fcm, 1000);
                                $fcmMsg = array(
                                    'content_available' => "true",
                                    'title' => $this->trans->newBookingNotification,
                                    'body' => $this->trans->newBookingReceivedMessage,
                                    'type' => 'order',
                                    'order_id' => "$order_id",
                                    'type_id' => "$to_send_id",
                                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                                );
                                send_notification($fcmMsg, $registrationIDs_chunks);
                            }
                            $response['error'] = false;
                            $response['transaction_status'] = "paystack";
                            $response['message'] = "Transaction successfully done";
                            return $this->response->setJSON($response);
                        } else {
                            $response['error'] = true;
                            $response['message'] = "something went wrong";
                            return $this->response->setJSON($response);
                        }
                    }
                    log_message('error', 'Paystack Webhook inner Success --> ' . var_export($event, true));
                    log_message('error', 'Paystack Webhook order Success --> ' . var_export($event, true));
                } else {
                    /* No order ID found / sending 304 error to payment gateway so it retries wenhook after sometime*/
                    log_message('error', 'Paystack Webhook | Order id not found --> ' . var_export($event, true));
                    return $this->output
                        ->set_content_type('application/json')
                        ->set_status_header(304)
                        ->set_output(json_encode(array(
                            'message' => '304 Not Modified - order/transaction id not found',
                            'error' => true
                        )));
                }
            }
        } else if ($event['event'] == 'charge.dispute.create') {
            if (!empty($order_id) && is_numeric($order_id)) {
                $order = fetch_details('orders', ['id' => $order_id]);
                if ($order['order_data']['0']['active_status'] == 'received' || $order['order_data']['0']['active_status'] == 'processed') {
                    update_details(['status' => 'awaiting'], ['id' => $order_id], 'orders');
                    update_custom_job_status($order_id, 'pending');
                }
                if (!empty($transaction)) {
                    $transaction_id = $transaction[0]['id'];
                    update_details(['status' => 'pending'], ['id' => $transaction_id], 'transactions');
                }
                log_message('error', 'Paystack Transaction is Pending --> ' . var_export($event, true));
            }
        } else if ($event['event'] == 'refund.processed') {
            log_message('error', 'Paystack Webhook | REFUND ');
            $success_transaction = fetch_details('transactions', ['transaction_type' => 'transaction', 'type' => 'paystack', 'status' => 'success', 'txn_id' => $txn_id]);
            if (!empty($success_transaction)) {
                $already_exist_refund_transaction = fetch_details('transactions', ['transaction_type' => 'refund', 'type' => 'paystack', 'message' => 'paystack_refund', 'txn_id' => $txn_id]);
                if (!empty($already_exist_refund_transaction)) {
                    $refund_data = [
                        'status' => 'processed',
                    ];
                    update_details($refund_data, ['id' =>  $already_exist_refund_transaction[0]['id']], 'transactions');
                } else {
                    $data = [
                        'transaction_type' => 'refund',
                        'user_id' => $user_id,
                        'partner_id' => $partner_id,
                        'order_id' => $order_id,
                        'type' => 'paystack',
                        'txn_id' => $txn_id,
                        'amount' => $amount,
                        'status' => 'processed',
                        'currency_code' => $currency,
                        'message' => 'paystack_refund',
                    ];
                    $insert_id = add_transaction($data);
                    update_custom_job_status($order_id, 'refunded');
                }
            }
        } else {
            log_message('error', 'Paystack Webhook | IN ELSE');
            // if (!empty($order_id) && is_numeric($order_id)) {
            //     update_details(['status' => 'cancelled'], ['id' => $order_id], 'orders');
            // }
            // /* No need to add because the transaction is already added just update the transaction status */
            // if (!empty($transaction)) {
            //     $transaction_id = $transaction[0]['id'];
            //     update_details(['status' => 'failed'], ['id' => $transaction_id], 'transactions');
            //     update_details(['payment_status' => 2], ['id' => $order_id], 'orders');
            // }
            // $response['error'] = true;
            // $response['transaction_status'] = $event['event'];
            // $response['message'] = "Transaction could not be detected.";
            // // log_message('error', 'Paystack Webhook | Transaction could not be detected --> ' . var_export($event, true));
            // echo json_encode($response);
            // return false;
            if (!empty($event['data']['metadata']['additional_charges_transaction_id'])) {
                log_message('error', 'FOR ADDITIONAL CHARGES');
                update_details(['status' => 'failed', 'txn_id' => $txn_id, 'reference' => $event['data']['reference']], ['id' => $event['data']['metadata']['additional_charges_transaction_id']], 'transactions');
                $order_data = fetch_details('orders', ["id" => $order_id]);
                update_details(['payment_status_of_additional_charge' => '2'], ['id' => $order_id], 'orders');
            }
        }
    }
    public function razorpay()
    {
        //Debug in server first
        if ((strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') || !array_key_exists('HTTP_X_RAZORPAY_SIGNATURE', $_SERVER))
            exit();
        $razorpay = new Razorpay;
        $system_settings = get_settings('system_settings', true);
        $credentials = $razorpay->get_credentials();
        $request = file_get_contents('php://input');
        $request = json_decode($request, true);
        define('RAZORPAY_SECRET_KEY', $credentials['secret']);
        $http_razorpay_signature = isset($_SERVER['HTTP_X_RAZORPAY_SIGNATURE']) ? $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] : "";
        // log_message('error', 'Razorpay --> ' . var_export($request, true));
        log_message('error', 'Razorpay --> ' . var_export($request, true));
        $txn_id = (isset($request['payload']['payment']['entity']['id'])) ? $request['payload']['payment']['entity']['id'] : "";
        if (!empty($request['payload']['payment']['entity']['id'])) {
            if (!empty($txn_id)) {
                $transaction = fetch_details('transactions', ['txn_id' => $txn_id]);
            }
            $amount = $request['payload']['payment']['entity']['amount'];
            $amount = ($amount / 100);
            $currency = (isset($request['payload']['payment']['entity']['currency'])) ? $request['payload']['payment']['entity']['currency'] : "";
        } else {
            $amount = 0;
            $currency = (isset($request['payload']['payment']['entity']['currency'])) ? $request['payload']['payment']['entity']['currency'] : "";
        }
        $is_for_additional_charge = isset($request['payload']['payment']['entity']['notes']['additional_charges_transaction_id']) ? $request['payload']['payment']['entity']['notes']['additional_charges_transaction_id'] : "";
        if (!empty($transaction)) {
            $order_id = $transaction[0]['order_id'];
            $user_id = $transaction[0]['user_id'];
            $order_data = fetch_details('orders', ["id" => $order_id]);
            $user_id = $order_data[0]['user_id'];
            $partner_id = $order_data[0]['partner_id'];
        } else if (!empty($request['payload']['payment']['entity']['notes']['transaction_id'])) {
            $transaction_id_actual = isset($request['payload']['payment']['entity']['notes']['transaction_id']) ? $request['payload']['payment']['entity']['notes']['transaction_id'] : "";
            //  log_message('error', 'transaction_id ID ********* ' . $request['payload']['payment']['entity']['notes']['transaction_id']);
        } else {
            $order_id = 0;
            $order_id = (isset($request['payload']['order']['entity']['notes']['order_id'])) ? $request['payload']['order']['entity']['notes']['order_id'] : $request['payload']['payment']['entity']['notes']['order_id'];
            $order_data = fetch_details('orders', ["id" => $order_id]);
            $user_id = $order_data[0]['user_id'];
            $partner_id = $order_data[0]['partner_id'];
        }
        if ($http_razorpay_signature) {
            if ($request['event'] == 'payment.authorized') {
                $currency = (isset($request['payload']['payment']['entity']['currency'])) ? $request['payload']['payment']['entity']['currency'] : "INR";
                $response = $razorpay->capture_payment($amount * 100, $txn_id, $currency);
                return;
            }
            if ($request['event'] == 'payment.captured' || $request['event'] == 'order.paid') {
                if (!empty($transaction_id_actual)) {
                    log_message('error', 'FOR SUBSCRIPTION');
                    log_message('error', ' ID ********* ' . $request['payload']['payment']['entity']['notes']['transaction_id']);
                    log_message('error', 'transaction_id  ********* ' . $txn_id);
                    $transaction_details_for_subscription = fetch_details('transactions', ['id' => $request['payload']['payment']['entity']['notes']['transaction_id']]);
                    $details_for_subscription = fetch_details('subscriptions', ['id' => $transaction_details_for_subscription[0]['subscription_id']]);
                    update_details(['status' => 'success', 'txn_id' => $txn_id], ['id' => $request['payload']['payment']['entity']['notes']['transaction_id']], 'transactions');
                    // update_details(['status' => 'active'], ['subscription_id' => $transaction_details_for_subscription[0]['subscription_id'],'partner_id'=>$transaction_details_for_subscription[0]['user_id'],'status'=>'pending'], 'partner_subscriptions');
                    $purchaseDate = date('Y-m-d');
                    $subscriptionDuration = $details_for_subscription[0]['duration'];
                    $expiryDate = date('Y-m-d', strtotime($purchaseDate . ' + ' . $subscriptionDuration . ' days')); // Add the duration to the purchase date
                    if ($subscriptionDuration == "unlimited") {
                        $subscriptionDuration = 0;
                    }
                    update_details(['status' => 'active', 'is_payment' => '1', 'purchase_date' => $purchaseDate, 'expiry_date' => $expiryDate, 'updated_at' => date('Y-m-d h:i:s')], [
                        'subscription_id' => $transaction_details_for_subscription[0]['subscription_id'],
                        'partner_id' => $transaction_details_for_subscription[0]['user_id'],
                        'status !=' => 'active',
                        'transaction_id' => $request['payload']['payment']['entity']['notes']['transaction_id'],
                    ], 'partner_subscriptions');
                } else if (!empty($is_for_additional_charge)) {
                    log_message('error', 'FOR ADDITIONAL CHARGES');
                    update_details(['status' => 'success', 'txn_id' => $txn_id], ['id' => $is_for_additional_charge], 'transactions');
                    $order_data = fetch_details('orders', ["id" => $order_id]);
                    update_details(['payment_status_of_additional_charge' => '1'], ['id' => $order_id], 'orders');
                }
                if ($request['event'] == 'order.paid') {
                    $order_id = $request['payload']['order']['entity']['receipt'];
                    $order_data = fetch_details('orders', ["id" => $order_id]);
                    $user_id = $order_data[0]['user_id'];
                    $partner_id = $order_data[0]['partner_id'];
                }
                if (!empty($order_id)) {
                    /* No need to add because the transaction is already added just update the transaction status */
                    if (!empty($transaction)) {
                        $transaction_id = $transaction[0]['id'];
                        update_details(['status' => 'success'], ['id' => $transaction_id], 'transactions');
                    } else {
                        /* add transaction of the payment */
                        $currency = (isset($request['payload']['payment']['entity']['currency'])) ? $request['payload']['payment']['entity']['currency'] : "";
                        $data = [
                            'transaction_type' => 'transaction',
                            'user_id' => $user_id,
                            'partner_id' => $partner_id,
                            'order_id' => $order_id,
                            'type' => 'razorpay',
                            'txn_id' => $txn_id,
                            'amount' => $amount,
                            'status' => 'success',
                            'currency_code' => $currency,
                            'message' => 'Order placed successfully',
                        ];
                        $insert_id = add_transaction($data);
                        if ($insert_id) {
                            update_details(['payment_status' => 1], ['id' => $order_id], 'orders');
                            update_custom_job_status($order_id, 'booked');

                            send_web_notification('New Booking', 'Please check new Booking ' . $order_id, $partner_id);
                            //customer email
                            $userdata = fetch_details('users', ['id' => $user_id], ['email', 'username']);
                            if (!empty($userdata[0]['email']) && check_notification_setting('new_booking_confirmation_to_customer', 'email') && is_unsubscribe_enabled($user_id) == 1) {
                                send_custom_email('new_booking_confirmation_to_customer', $partner_id, $userdata[0]['email'], null, $user_id, $order_id);
                            }
                            if (check_notification_setting('new_booking_confirmation_to_customer', 'sms')) {
                                send_custom_sms('new_booking_confirmation_to_customer', $partner_id, $userdata[0]['email'], null, $user_id, $order_id);
                            }
                            //for provider
                            $user_partner_data = fetch_details('users', ['id' => $partner_id], ['email', 'username']);
                            if (!empty($user_partner_data[0]['email']) && check_notification_setting('new_booking_received_for_provider', 'email') && is_unsubscribe_enabled($partner_id) == 1) {
                                send_custom_email('new_booking_received_for_provider', $partner_id, $user_partner_data[0]['email'], null, $user_id, $order_id);
                            }
                            if (check_notification_setting('new_booking_received_for_provider', 'sms')) {
                                send_custom_sms('new_booking_received_for_provider', $partner_id, $user_partner_data[0]['email'], null, $user_id, $order_id);
                            }
                            //for app notification
                            $db      = \Config\Database::connect();
                            $to_send_id = $partner_id;
                            $builder = $db->table('users')->select('fcm_id,email,username,platform');
                            $users_fcm = $builder->where('id', $to_send_id)->get()->getResultArray();
                            foreach ($users_fcm as $ids) {
                                if ($ids['fcm_id'] != "") {
                                    $fcm_ids['fcm_id'] = $ids['fcm_id'];
                                    $fcm_ids['platform'] = $ids['platform'];
                                    $email = $ids['email'];
                                }
                            }
                            if (!empty($fcm_ids) && check_notification_setting('new_booking_received_for_provider', 'notification')) {
                                $registrationIDs = $fcm_ids;
                                $registrationIDs_chunks = array_chunk($users_fcm, 1000);
                                $fcmMsg = array(
                                    'content_available' => "true",
                                    'title' => $this->trans->newBookingNotification,
                                    'body' => $this->trans->newBookingReceivedMessage,
                                    'type' => 'order',
                                    'order_id' => "$order_id",
                                    'type_id' => "$to_send_id",
                                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                                );
                                send_notification($fcmMsg, $registrationIDs_chunks);
                            }
                        }
                    }
                    // update_details(['active' => 'confirmed'], ['id' => $order_id], 'orders');
                } else {
                    log_message('error', 'Razorpay Order id not found --> ' . var_export($request, true));
                    /* No order ID found */
                }
            } elseif ($request['event'] == 'payment.failed') {

                if (!empty($is_for_additional_charge)) {
                    log_message('error', 'FOR ADDITIONAL CHARGES');
                    update_details(['status' => 'failed', 'txn_id' => $txn_id], ['id' => $is_for_additional_charge], 'transactions');
                    $order_data = fetch_details('orders', ["id" => $order_id]);
                    update_details(['payment_status_of_additional_charge' => '2'], ['id' => $order_id], 'orders');
                } else {
                    update_details(['payment_status' => 2], ['id' => $order_id], 'orders');
                    update_details(['status' => 'cancelled'], ['id' => $order_id], 'orders');
                    if (!empty($transaction)) {
                        $transaction_id = $transaction[0]['id'];
                        update_details(['status' => 'failed'], ['id' => $transaction_id], 'transactions');
                    } else {
                        /* add transaction of the payment */
                        $currency = (isset($request['payload']['payment']['entity']['currency'])) ? $request['payload']['payment']['entity']['currency'] : "";
                        $data = [
                            'transaction_type' => 'transaction',
                            'user_id' => $user_id,
                            'partner_id' => $partner_id,
                            'order_id' => $order_id,
                            'type' => 'razorpay',
                            'txn_id' => $txn_id,
                            'amount' => $amount,
                            'status' => 'failed',
                            'currency_code' => $currency,
                            'message' => 'Booking is cancelled',
                        ];
                        $insert_id = add_transaction($data);
                        update_custom_job_status($order_id, 'cancelled');
                    }
                    log_message('error', 'Razorpay Webhook | Transaction is failed --> ' . var_export($request['event'], true));
                }
            } elseif ($request['event'] == 'payment.authorized') {
                if (!empty($order_id)) {
                    update_details(['active_status' => 'awaiting'], ['id' => $order_id], 'orders');
                    update_details(['active_status' => 'awaiting'], ['order_id' => $order_id], 'order_items');
                    update_custom_job_status($order_id, 'requested');
                }
            } elseif ($request['event'] == "refund.processed") {
                log_message('error', 'Razorpay REFUND ');
                log_message('error', 'Razorpay TXN ID --> ' . $txn_id);
                $success_transaction = fetch_details('transactions', ['transaction_type' => 'transaction', 'type' => 'razorpay', 'status' => 'success', 'txn_id' => $txn_id]);
                if (!empty($success_transaction)) {
                    $already_exist_refund_transaction = fetch_details('transactions', ['transaction_type' => 'refund', 'type' => 'razorpay', 'message' => 'razorpay_refund', 'txn_id' => $txn_id]);
                    if (!empty($already_exist_refund_transaction)) {
                        $refund_data = [
                            'status' => 'processed',
                        ];
                        update_details($refund_data, ['id' =>  $already_exist_refund_transaction[0]['id']], 'transactions');
                    } else {
                        $data = [
                            'transaction_type' => 'refund',
                            'user_id' => $user_id,
                            'partner_id' => $partner_id,
                            'order_id' => $order_id,
                            'type' => 'razorpay',
                            'txn_id' => $txn_id,
                            'amount' => $amount,
                            'status' => 'processed',
                            'currency_code' => $currency,
                            'message' => 'razorpay_refund',
                        ];
                        $insert_id = add_transaction($data);
                        update_custom_job_status($order_id, 'refunded');
                    }
                }
            } elseif ($request['event'] == "refund.failed") {


                $response['error'] = true;
                $response['transaction_status'] = $request['event'];
                $response['message'] = "Refund is failed. ";
                log_message('error', 'Razorpay Webhook | Payment refund failed --> ' . var_export($request['event'], true));
                echo json_encode($response);
                return false;
            }
            //  else {
            //     $response['error'] = true;
            //     $response['transaction_status'] = $request['event'];
            //     $response['message'] = "Transaction could not be detected.";
            //     log_message('error', 'Razorpay Webhook | Transaction could not be detected --> ' . var_export($request['event'], true));
            //     echo json_encode($response);
            //     return false;
            // }
        } else {
            log_message('error', 'razorpay Webhook | Invalid Server Signature  --> ' . var_export($request['event'], true));
            return false;
        }
    }
    public function edie($error_msg)
    {
        global $debug_email;
        $report =  "ERROR : " . $error_msg . "\n\n";
        $report .= "POST DATA\n\n";
        foreach ($_POST as $key => $value) {
            $report .= "|$key| = |$value| \n";
        }
        log_message('error', $report);
        die($error_msg);
    }
    public function paypal()
    {
        $req = 'cmd=_notify-validate';
        $request_body = file_get_contents('php://input');
        parse_str($request_body, $event);
        log_message('error', 'paypal------' . var_export($event, true));
        $txn_id = (isset($event['txn_id'])) ? $event['txn_id'] : "";
        if (!empty($request_body)) {
            $ipnCheck = $this->paypal_lib->validate_ipn($event);
            if ($ipnCheck) {
                if (!empty($event['txn_id'])) {
                    if (!empty($txn_id)) {
                        $transaction = fetch_details('transactions', ['txn_id' => $txn_id]);
                    }
                    $amount = $event['payment_gross'];
                    $amount = ($amount);
                    $currency = (isset($event['mc_currency'])) ? $event['mc_currency'] : "";
                } else {
                    $amount = 0;
                    $currency = (isset($event['mc_currency'])) ? $event['mc_currency'] : "";
                }
                $custom_data = explode('|', $event['custom']); // Split the invoice string
                $is_subscripition = $custom_data[2] ?? null;
                $is_for_additional_charge = $custom_data[2] ?? null;
                if (!empty($transaction)) {
                    $order_id = $transaction[0]['order_id'];
                    $order_data = fetch_details('orders', ["id" => $order_id]);
                    $user_id = $order_data[0]['user_id'];
                    $partner_id = $order_data[0]['partner_id'];
                } else {
                    $order_id = 0;
                    $order_id = (isset($event['item_number'])) ? $event['item_number'] : $event['item_number'];
                    $order_data = fetch_details('orders', ["id" => $order_id]);
                    if (!empty($order_data)) {
                        $user_id = $order_data[0]['user_id'];
                        $partner_id = $order_data[0]['partner_id'];
                    }
                }
                // log_message('error', var_export($transaction, true));
                if ($event['payment_status'] == "Completed") {
                    if ($is_subscripition == "subscription") {
                        if (isset($event['custom']) && !empty($event['custom'])) {
                            $subsciption_data = explode('|', $event['custom']); // Split the invoice string
                            $transaction_id = $subsciption_data[0] ?? null;
                            if (!empty($transaction_id) && $transaction_id != null) {
                                $transaction_details_for_subscription = fetch_details('transactions', ['id' => $transaction_id]);
                                if (!empty($transaction_details_for_subscription)) {
                                    $details_for_subscription = fetch_details('subscriptions', ['id' => $transaction_details_for_subscription[0]['subscription_id']]);
                                    log_message('error', 'FOR SUBSCRIPTION');
                                    update_details(['status' => 'success', 'txn_id' => $txn_id], ['id' => $transaction_id], 'transactions');
                                    $purchaseDate = date('Y-m-d');
                                    $subscriptionDuration = $details_for_subscription[0]['duration'];
                                    $expiryDate = date('Y-m-d', strtotime($purchaseDate . ' + ' . $subscriptionDuration . ' days')); // Add the duration to the purchase date
                                    if ($subscriptionDuration == "unlimited") {
                                        $subscriptionDuration = 0;
                                    }
                                    update_details(['status' => 'active', 'is_payment' => '1', 'purchase_date' => $purchaseDate, 'expiry_date' => $expiryDate, 'updated_at' => date('Y-m-d h:i:s')], [
                                        'subscription_id' => $transaction_details_for_subscription[0]['subscription_id'],
                                        'partner_id' => $transaction_details_for_subscription[0]['user_id'],
                                        'status !=' => 'active',
                                        'transaction_id' => $transaction_id,
                                    ], 'partner_subscriptions');
                                }
                                // log_message('error', 'METAFDATA --> ' . var_export($event['data']['metadata']['transaction_id'], true));
                            }
                        }
                    } else if (isset($is_for_additional_charge)) {
                        log_message('error', 'FOR ADDITIONAL CHARGES');
                        update_details(['status' => 'success', 'txn_id' => $txn_id], ['id' => $custom_data[2]], 'transactions');
                        $order_data = fetch_details('orders', ["id" => $order_id]);
                        update_details(['payment_status_of_additional_charge' => '1'], ['id' => $order_id], 'orders');
                    } else {
                        if (!empty($order_id)) {
                            /* No need to add because the transaction is already added just update the transaction status */
                            if (!empty($transaction)) {
                                $transaction_id = $transaction[0]['id'];
                                update_details(['status' => 'success'], ['id' => $transaction_id], 'transactions');
                            } else {
                                log_message('error', 'add transaction of the payment');
                                /* add transaction of the payment */
                                $currency = (isset($event['mc_currency'])) ? $event['mc_currency'] : "";
                                $data = [
                                    'transaction_type' => 'transaction',
                                    'user_id' => $user_id,
                                    'partner_id' => $partner_id,
                                    'order_id' => $order_id,
                                    'type' => 'paypal',
                                    'txn_id' => $txn_id,
                                    'amount' => $amount,
                                    'status' => 'success',
                                    'currency_code' => $currency,
                                    'message' => 'Order placed successfully',
                                ];
                                $insert_id = add_transaction($data);
                            }
                            if ($insert_id) {
                                update_details(['payment_status' => 1], ['id' => $order_id], 'orders');
                                update_custom_job_status($order_id, 'booked');

                                // send_web_notification('New Order', 'Please check new order ' . $order_id, $partner_id);
                                send_web_notification('New Booking Notification', 'We are pleased to inform you that you have received a new Booking.');
                                $settings = get_settings('general_settings', true);
                                $icon = $settings['logo'];
                                //customer email
                                $userdata = fetch_details('users', ['id' => $user_id], ['email', 'username']);
                                if (!empty($userdata[0]['email']) && check_notification_setting('new_booking_confirmation_to_customer', 'email') && is_unsubscribe_enabled($user_id) == 1) {
                                    send_custom_email('new_booking_confirmation_to_customer', $partner_id, $userdata[0]['email'], null, $user_id, $order_id);
                                }
                                if (check_notification_setting('new_booking_confirmation_to_customer', 'sms')) {
                                    send_custom_sms('new_booking_confirmation_to_customer', $partner_id, $userdata[0]['email'], null, $user_id, $order_id);
                                }
                                //for provider
                                $user_partner_data = fetch_details('users', ['id' => $partner_id], ['email', 'username']);
                                if (!empty($user_partner_data[0]['email']) && check_notification_setting('new_booking_received_for_provider', 'email') && is_unsubscribe_enabled($partner_id) == 1) {
                                    send_custom_email('new_booking_received_for_provider', $partner_id, $user_partner_data[0]['email'], null, $user_id, $order_id);
                                }
                                if (check_notification_setting('new_booking_received_for_provider', 'sms')) {
                                    send_custom_sms('new_booking_received_for_provider', $partner_id, $user_partner_data[0]['email'], null, $user_id, $order_id);
                                }
                                //for app notification
                                $db      = \Config\Database::connect();
                                $to_send_id = $partner_id;
                                $builder = $db->table('users')->select('fcm_id,email,username,platform');
                                $users_fcm = $builder->where('id', $to_send_id)->get()->getResultArray();
                                foreach ($users_fcm as $ids) {
                                    if ($ids['fcm_id'] != "") {
                                        $fcm_ids['fcm_id'] = $ids['fcm_id'];
                                        $fcm_ids['platform'] = $ids['platform'];
                                        $email = $ids['email'];
                                    }
                                }
                                if (!empty($fcm_ids) && check_notification_setting('new_booking_received_for_provider', 'notification')) {
                                    $registrationIDs = $fcm_ids;
                                    $registrationIDs_chunks = array_chunk($users_fcm, 1000);
                                    $fcmMsg = array(
                                        'content_available' => "true",
                                        'title' => $this->trans->newBookingNotification,
                                        'body' => $this->trans->newBookingReceivedMessage,
                                        'type' => 'order',
                                        'order_id' => "$order_id",
                                        'type_id' => "$to_send_id",
                                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                                    );
                                    send_notification($fcmMsg, $registrationIDs_chunks);
                                }
                                $response['error'] = false;
                                $response['transaction_status'] = $event['payment_status'];
                                $response['message'] = "Transaction successfully done";
                                log_message('error', 'Transaction successfully done');
                            } else {
                                $response['error'] = true;
                                $response['message'] = "something went wrong";
                                log_message('error', 'something went wrong');
                            }
                            // update_details(['status' => 'confirmed'], ['id' => $order_id], 'orders');
                            $response['error'] = false;
                            $response['transaction_status'] = $event['payment_status'];
                            $response['message'] = "Transaction successfully done";
                            echo json_encode($response);
                            log_message('error', 'Transaction successfully done ');
                        }
                    }
                } else if ($event['payment_status'] == "Refunded") {
                    log_message('error', 'Paypal Webhook | REFUND ');
                    $success_transaction = fetch_details('transactions', ['transaction_type' => 'transaction', 'type' => 'paypal', 'status' => 'success', 'txn_id' => $txn_id]);
                    if (!empty($success_transaction)) {
                        $already_exist_refund_transaction = fetch_details('transactions', ['transaction_type' => 'refund', 'type' => 'paypal', 'message' => 'paypal_refund', 'txn_id' => $txn_id]);
                        if (!empty($already_exist_refund_transaction)) {
                            $refund_data = [
                                'status' => 'COMPLETED',
                            ];
                            update_details($refund_data, ['id' =>  $already_exist_refund_transaction[0]['id']], 'transactions');
                        } else {
                            $data = [
                                'transaction_type' => 'refund',
                                'user_id' => $user_id,
                                'partner_id' => $partner_id,
                                'order_id' => $order_id,
                                'type' => 'paypal',
                                'txn_id' => $txn_id,
                                'amount' => $amount,
                                'status' => 'COMPLETED',
                                'currency_code' => $currency,
                                'message' => 'paypal_refund',
                            ];
                            $insert_id = add_transaction($data);
                            update_custom_job_status($order_id, 'refunded');
                        }
                    }
                } else {
                    log_message('error', 'Something went wrong1111');
                }
                log_message('error', 'SUCCESS');
            } else {
                log_message('error', 'IPN failed');
            }
        }
    }
    public function flutterwave()
    {
        log_message('error', " flutterwave Webhook called");
        $request_body = file_get_contents('php://input');
        $event = json_decode($request_body, FALSE);
        log_message('error', 'Flutterwave Webhook --> ' . var_export($event, true));
        $flutterwave = new Flutterwave();
        $verifiy = $flutterwave->verify_transaction($event->data->id);
        $credentials = $flutterwave->get_credentials();
        $local_secret_hash = $credentials['secret_hash'];
        $from_env = env('FLUTTERWAVE_SECRET_KEY');
        $signature = (isset($_SERVER['FLUTTERWAVE_SECRET_KEY'])) ? $_SERVER['FLUTTERWAVE_SECRET_KEY'] : '';
        log_message('error', 'FlutterWave Webhook - header signature --> ' . var_export($signature, true));
        /* comparing our local signature with received signature */
        if (empty($signature) || $signature != $local_secret_hash) {
            log_message('error', 'FlutterWave Webhook - Invalid Signature - JSON DATA --> ' . var_export($event, true));
            // log_message('error', 'FlutterWave Server Variable invalid --> ' . var_export($_SERVER, true));
            return false;
        }
        $response = json_decode($verifiy);
        log_message('error', 'verified response : ' . var_export($response, true));
        $status = $response->status;
        if (!empty($event->data)) {
            $txn_id = (isset($event->data->id)) ? $event->data->id : "";
            if (isset($txn_id) && !empty($txn_id)) {
                $transaction = fetch_details('transactions', ['txn_id' => $txn_id]);
                if (!empty($transaction)) {
                    $order_id = $transaction[0]['order_id'];
                    $user_id = $transaction[0]['user_id'];
                } else {
                    if (!empty($response->data->meta->transaction_id)) {
                    } else {
                        if (isset($response->data->meta->order_id) && !empty($response->data->meta->order_id)) {
                            $order_id = 0;
                            $order_id = $response->data->meta->order_id;
                            $order_data = fetch_details('orders', ["id" => $order_id]);
                            $user_id = $order_data[0]['user_id'];
                            $partner_id = $order_data[0]['partner_id'];
                        }
                    }
                }
            }
            $amount = $event->data->amount;
            $currency = $event->data->currency;
        } else {
            $order_id = 0;
            $amount = 0;
            $currency = (isset($event->data->currency)) ? $event->data->currency : "";
        }
        if ($event->event == 'charge.completed' && $event->data->status == 'successful') {
            if (!empty($response->data->meta->transaction_id)) {
                $transaction_details_for_subscription = fetch_details('transactions', ['id' => $response->data->meta->transaction_id]);
                $details_for_subscription = fetch_details('subscriptions', ['id' => $transaction_details_for_subscription[0]['subscription_id']]);
                log_message('error', 'FOR SUBSCRIPTION');
                update_details(['status' => 'success', 'txn_id' => $txn_id, 'reference' => $event->data->tx_ref], ['id' => $response->data->meta->transaction_id], 'transactions');
                $purchaseDate = date('Y-m-d');
                $subscriptionDuration = $details_for_subscription[0]['duration'];
                $expiryDate = date('Y-m-d', strtotime($purchaseDate . ' + ' . $subscriptionDuration . ' days')); // Add the duration to the purchase date
                if ($subscriptionDuration == "unlimited") {
                    $subscriptionDuration = 0;
                }
                log_message('error', 'transaction id from metadata : ' . var_export($response->data->meta->transaction_id, true));
                update_details(['status' => 'active', 'is_payment' => '1', 'purchase_date' => $purchaseDate, 'expiry_date' => $expiryDate, 'updated_at' => date('Y-m-d h:i:s')], [
                    'subscription_id' => $transaction_details_for_subscription[0]['subscription_id'],
                    'partner_id' => $transaction_details_for_subscription[0]['user_id'],
                    'status !=' => 'active',
                    'transaction_id' => $response->data->meta->transaction_id,
                ], 'partner_subscriptions');
            } else if (!empty($response->data->meta->additional_charges_transaction_id)) {
                log_message('error', 'FOR ADDITIONAL CHARGES');
                update_details(['status' => 'success', 'txn_id' => $txn_id], ['id' => $response->data->meta->additional_charges_transaction_id], 'transactions');
                $order_data = fetch_details('orders', ["id" => $order_id]);
                update_details(['payment_status_of_additional_charge' => '1'], ['id' => $order_id], 'orders');
            } else {
                if (!empty($order_id)) {
                    $order = fetch_details('orders', ['id' => $order_id]);
                    log_message('error', 'Flutterwave Webhook | order --> ' . var_export($order, true));
                    /* No need to add because the transaction is already added just update the transaction status */
                    if (!empty($transaction)) {
                        $transaction_id = $transaction[0]['id'];
                        update_details(['status' => 'success'], ['id' => $transaction_id], 'transactions');
                    } else {
                        /* add transaction of the payment */
                        $amount = ($event->data->amount / 100);
                        $data = [
                            'transaction_type' => 'transaction',
                            'user_id' => $user_id,
                            'partner_id' => $partner_id,
                            'order_id' => $order_id,
                            'type' => 'flutterwave',
                            'txn_id' => $txn_id,
                            'amount' => $amount,
                            'status' => 'success',
                            'currency_code' => $currency,
                            'message' => 'Order placed successfully',
                            'reference' => (isset($event->data->tx_ref)) ? $event->data->tx_ref : "",
                        ];
                        $insert_id = add_transaction($data);
                        if ($insert_id) {
                            update_details(['payment_status' => 1], ['id' => $order_id], 'orders');
                            update_custom_job_status($order_id, 'booked');

                            send_web_notification('New Booking Notification', 'We are pleased to inform you that you have received a new Booking.');
                            //customer email
                            $userdata = fetch_details('users', ['id' => $user_id], ['email', 'username']);
                            if (!empty($userdata[0]['email']) && check_notification_setting('new_booking_confirmation_to_customer', 'email') && is_unsubscribe_enabled($user_id) == 1) {
                                send_custom_email('new_booking_confirmation_to_customer', $partner_id, $userdata[0]['email'], null, $user_id, $order_id);
                            }
                            if (check_notification_setting('new_booking_confirmation_to_customer', 'sms')) {
                                send_custom_sms('new_booking_confirmation_to_customer', $partner_id, $userdata[0]['email'], null, $user_id, $order_id);
                            }
                            //for provider
                            $user_partner_data = fetch_details('users', ['id' => $partner_id], ['email', 'username']);
                            if (!empty($user_partner_data[0]['email']) && check_notification_setting('new_booking_received_for_provider', 'email') && is_unsubscribe_enabled($partner_id) == 1) {
                                send_custom_email('new_booking_received_for_provider', $partner_id, $user_partner_data[0]['email'], null, $user_id, $order_id);
                            }
                            if (check_notification_setting('new_booking_received_for_provider', 'sms')) {
                                send_custom_sms('new_booking_received_for_provider', $partner_id, $user_partner_data[0]['email'], null, $user_id, $order_id);
                            }
                            //for app notification
                            $db      = \Config\Database::connect();
                            $to_send_id = $partner_id;
                            $builder = $db->table('users')->select('fcm_id,email,username,platform');
                            $users_fcm = $builder->where('id', $to_send_id)->get()->getResultArray();
                            foreach ($users_fcm as $ids) {
                                if ($ids['fcm_id'] != "") {
                                    $fcm_ids['fcm_id'] = $ids['fcm_id'];
                                    $fcm_ids['platform'] = $ids['platform'];
                                    $email = $ids['email'];
                                }
                            }
                            if (!empty($fcm_ids)  && check_notification_setting('new_booking_received_for_provider', 'notification')) {
                                $registrationIDs = $fcm_ids;
                                $registrationIDs_chunks = array_chunk($users_fcm, 1000);
                                $fcmMsg = array(
                                    'content_available' => "true",
                                    'title' => $this->trans->newBookingNotification,
                                    'body' => $this->trans->newBookingReceivedMessage,
                                    'type' => 'order',
                                    'order_id' => "$order_id",
                                    'type_id' => "$to_send_id",
                                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                                );
                                send_notification($fcmMsg, $registrationIDs_chunks);
                            }
                            $response['error'] = false;
                            $response['transaction_status'] = "flutterwave";
                            $response['message'] = "Transaction successfully done";
                            return $this->response->setJSON($response);
                        } else {
                            $response['error'] = true;
                            $response['message'] = "something went wrong";
                            return $this->response->setJSON($response);
                        }
                    }
                    log_message('error', 'Flutterwave Webhook inner Success --> ' . var_export($event, true));
                    log_message('error', 'Flutterwave Webhook order Success --> ' . var_export($event, true));
                } else {
                    /* No order ID found / sending 304 error to payment gateway so it retries wenhook after sometime*/
                    log_message('error', 'Flutterwave Webhook | Order id not found --> ' . var_export($event, true));
                }
            }
        } else {
            if (!empty($response->data->meta->additional_charges_transaction_id)) {
                log_message('error', 'FOR ADDITIONAL CHARGES');
                update_details(['status' => 'failed', 'txn_id' => $txn_id], ['id' => $response->data->meta->additional_charges_transaction_id], 'transactions');
                $order_data = fetch_details('orders', ["id" => $order_id]);
                update_details(['payment_status_of_additional_charge' => '2'], ['id' => $order_id], 'orders');
            } else {
                if (!empty($order_id) && is_numeric($order_id)) {
                    update_details(['status' => 'cancelled'], ['id' => $order_id], 'orders');
                }
                update_custom_job_status($order_id, 'cancelled');
                /* No need to add because the transaction is already added just update the transaction status */
                if (!empty($transaction)) {
                    $transaction_id = $transaction[0]['id'];
                    update_details(['status' => 'failed'], ['id' => $transaction_id], 'transactions');
                    update_details(['payment_status' => 2], ['id' => $order_id], 'orders');
                }
                $response['error'] = true;
                $response['transaction_status'] = $event['event'];
                $response['message'] = "Transaction could not be detected.";
                echo json_encode($response);
                return false;
            }
        }
    }
}
