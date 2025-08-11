<?php
/*
=======================
    Customer APIs
=======================
*/
$routes->post('api/v1/index', 'api\V1::index');
$routes->post('/api/v1/manage_user', 'api\V1::manage_user');
$routes->post('/api/v1/update_user', 'api\V1::update_user');
$routes->post('/api/v1/update_fcm', 'api\V1::update_fcm');
$routes->post('/api/v1/get_settings', 'api\V1::get_settings');
$routes->post('/api/v1/add_transaction', 'api\V1::add_transaction');
$routes->post('/api/v1/get_transactions', 'api\V1::get_transactions');
$routes->post('/api/v1/add_address', 'api\V1::add_address');
$routes->post('/api/v1/delete_address', 'api\V1::delete_address');
$routes->post('/api/v1/get_address', 'api\V1::get_address');
$routes->post('/api/v1/validate_promo_code', 'api\V1::validate_promo_code');
$routes->post('/api/v1/get_promo_codes', 'api\V1::get_promo_codes');
$routes->post('/api/v1/get_categories', 'api\V1::get_categories');
$routes->post('/api/v1/get_sub_categories', 'api\V1::get_sub_categories');
$routes->post('/api/v1/get_sliders', 'api\V1::get_sliders');
$routes->post('/api/v1/get_providers', 'api\V1::get_providers');
$routes->post('/api/v1/get_services', 'api\V1::get_services');
$routes->post('/api/v1/manage_cart', 'api\V1::manage_cart');
$routes->post('/api/v1/remove_from_cart', 'api\V1::remove_from_cart');
$routes->post('/api/v1/get_cart', 'api\V1::get_cart');
$routes->post('/api/v1/place_order', 'api\V1::place_order'); 
$routes->post('/api/v1/get_orders', 'api\V1::get_orders');
$routes->post('/api/v1/manage_notification', 'api\V1::manage_notification');
$routes->post('/api/v1/get_notifications', 'api\V1::get_notifications');
$routes->post('/api/v1/book_mark', 'api\V1::book_mark');
$routes->post('/api/v1/update_order_status', 'api\V1::update_order_status');
$routes->post('/api/v1/get_available_slots', 'api\V1::get_available_slots');
$routes->post('/api/v1/check_available_slot', 'api\V1::check_available_slot');
$routes->post('/api/v1/razorpay_create_order', 'api\V1::razorpay_create_order');
$routes->post('/api/v1/update_service_status', 'api\V1::update_service_status');
$routes->post('/api/v1/get_faqs', 'api\V1::get_faqs');
$routes->post('/api/v1/verify_user', 'api\V1::verify_user');
$routes->post('/api/v1/get_ratings', 'api\V1::get_ratings');
$routes->post('/api/v1/add_rating', 'api\V1::add_rating');
$routes->post('/api/v1/update_rating', 'api\V1::update_rating');
$routes->post('/api/v1/manage_service', 'api\V1::manage_service');
$routes->post('/api/v1/delete_user_account', 'api\V1::delete_user_account');
$routes->post('/api/v1/logout', 'api\V1::logout');
$routes->post('/api/v1/get_home_screen_data', 'api\V1::get_home_screen_data');
$routes->post('/api/v1/provider_check_availability', 'api\V1::provider_check_availability');
$routes->post('/api/v1/get_paypal_link', 'api\V1::get_paypal_link');
$routes->get('/api/v1/paypal_transaction_webview', 'api\V1::paypal_transaction_webview');
$routes->get('/api/v1/app_payment_status', 'api\V1::app_payment_status');
$routes->post('/api/v1/ipn', 'api\V1::ipn');
$routes->post('/api/v1/invoice-download', 'api\V1::invoice_download');
$routes->post('/api/v1/verify-transaction', 'api\V1::verify_transaction');
$routes->post('/api/v1/contact_us_api', 'api\V1::contact_us_api');
$routes->post('/api/v1/search', 'api\V1::search');
$routes->post('/api/v1/search_services_providers', 'api\V1::search_services_providers');
$routes->get('/api/v1/capturePayment', 'api\V1::capturePayment');
$routes->post('/api/v1/send_chat_message', 'api\V1::send_chat_message');
$routes->post('/api/v1/get_chat_history', 'api\V1::get_chat_history');
$routes->post('/api/v1/get_chat_providers_list', 'api\V1::get_chat_providers_list');
$routes->post('/api/v1/get_user_info', 'api\V1::get_user_info');
$routes->post('/api/v1/verify_otp', 'api\V1::verify_otp');
$routes->get('/api/v1/paystack_transaction_webview', 'api\V1::paystack_transaction_webview');
$routes->get('/api/v1/app_paystack_payment_status', 'api\V1::app_paystack_payment_status');
$routes->get('/api/v1/flutterwave_webview', 'api\V1::flutterwave_webview');
$routes->get('/api/v1/flutterwave_payment_status', 'api\V1::flutterwave_payment_status');
$routes->post('/api/v1/resend_otp', 'api\V1::resend_otp');
$routes->post('/api/v1/get_web_landing_page_settings', 'api\V1::get_web_landing_page_settings');
$routes->post('/api/v1/make_custom_job_request', 'api\V1::make_custom_job_request');
$routes->post('/api/v1/fetch_my_custom_job_requests', 'api\V1::fetch_my_custom_job_requests');

$routes->post('/api/v1/fetch_custom_job_bidders', 'api\V1::fetch_custom_job_bidders');
$routes->post('/api/v1/cancle_custom_job_request', 'api\V1::cancle_custom_job_request');
$routes->get('/api/v1/get_places_for_app', 'api\V1::get_places_for_app');
$routes->get('/api/v1/get_place_details_for_app', 'api\V1::get_place_details_for_app');
$routes->get('/api/v1/get_places_for_web', 'api\V1::get_places_for_web');
$routes->get('/api/v1/get_place_details_for_web', 'api\V1::get_place_details_for_web');
$routes->post('/api/v1/get_become_provider_settings', 'api\V1::get_become_provider_settings');
$routes->post('/api/v1/get_parent_categories', 'api\V1::get_parent_categories');


$routes->post('/api/v1/get_all_categories', 'api\V1::get_all_categories');


