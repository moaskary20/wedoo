<?php
/*
==================================
        Parnter Panel Routes
==================================
*/
// Login
$routes->get('/partner/login', 'Auth::login');



$routes->group('', ['filter' => 'admin_sanitizer'], function ($routes) {



        // Dashbord
        $routes->get('partner/', 'partner\Dashboard::index');
        $routes->get('/partner/dashboard', 'partner\Dashboard::index');
        $routes->get('/partner/dashboard/fetch_sales', 'partner\Dashboard::fetch_sales');
        $routes->get('/partner/dashboard/fetch_data', 'partner\Dashboard::fetch_data');
        $routes->get('/partner/stripe', 'partner\StripePaymentController::index');
        // Services For Partners 
        $routes->add('partner/services', 'partner\Services::index');
        $routes->add('partner/services/list', 'partner\Services::list');
        $routes->add('partner/services/add', 'partner\Services::add');
        $routes->add('partner/services/add_service', 'partner\Services::add_service');
        $routes->add('partner/services/update_service', 'partner\Services::update_service');
        $routes->add('partner/services/delete_service', 'partner\Services::delete');
        $routes->add('partner/services/edit_service/(:any)', 'partner\Services::edit_service');
        $routes->add('partner/services/duplicate/(:any)', 'partner\Services::duplicate');
        $routes->add('partner/services/bulk_import_services/', 'partner\Services::bulk_import_services');
        $routes->add('partner/services/bulk_import_service_upload/', 'partner\Services::bulk_import_service_upload');
        $routes->add('partner/services/download-sample-for-insert/', 'partner\Services::downloadSampleForInsert');
        $routes->add('partner/services/download-sample-for-update/', 'partner\Services::downloadSampleForUpdate');
        $routes->add('partner/services/Service-Add-Instructions/', 'partner\Services::ServiceAddInstructions');
        $routes->add('partner/services/Service-Update-Instructions/', 'partner\Services::ServiceUpdateInstructions');

        // for profile
        $routes->add('partner/profile', 'partner\Profile::index');
        $routes->add('partner/update-profile', 'partner\Profile::update');
        $routes->add('partner/update_profile', 'partner\Profile::update_profile');
        // KYC for Partner
        $routes->add('partner/kyc', 'partner\KYC::index');
        // Categories
        $routes->add('partner/categories', 'partner\Categories::index');
        //check number
        $routes->add('auth/check_number', 'Auth::check_number');
        $routes->add('auth/check_number_for_forgot_password', 'Auth::check_number_for_forgot_password');
        $routes->add('auth/reset_password_otp', 'Auth::reset_password_otp');
        $routes->add('auth/send_sms_otp', 'Auth::send_sms_otp');
        $routes->add('auth/verify_sms_otp', 'Auth::verify_sms_otp');



        // orders
        $routes->add('partner/orders', 'partner\Orders::index');
        $routes->add('partner/orders/list', 'partner\Orders::list');
        $routes->add('partner/orders/veiw_orders/(:any)', 'partner\Orders::view_orders');
        $routes->add('partner/orders/invoice/(:any)', 'partner\Orders::invoice');
        $routes->add('partner/orders/invoice_table/(:any)', 'partner\Orders::invoice_table');
        $routes->add('partner/orders/order_summary_table/(:any)', 'partner\Orders::order_summary_table');
        $routes->add('partner/orders/update_order_status', 'partner\Orders::update_order_status');
        $routes->add('partner/orders/get_slots', 'partner\Orders::get_slots');
        $routes->add('partner/orders/change_order_status', 'partner\Orders::change_order_status');
        $routes->add('partner/orders/newList', 'partner\Orders::newList');
        $routes->add('partner/orders/test', 'partner\Orders::test');
        // promot codes
        $routes->add('partner/promo_codes', 'partner\Promo_codes::index');
        $routes->add('partner/promo_codes/add', 'partner\Promo_codes::add');
        $routes->add('partner/promo_codes/save', 'partner\Promo_codes::save');
        $routes->add('partner/promo_codes/list', 'partner\Promo_codes::list');
        $routes->add('partner/promo_codes/delete', 'partner\Promo_codes::delete');

        $routes->add('partner/promo_codes/duplicate/(:any)', 'partner\Promo_codes::duplicate');



        $routes->add('partner/withdrawal_requests', 'partner\Withdrawal_requests::index');
        $routes->add('partner/withdrawal_requests/save', 'partner\Withdrawal_requests::save');
        $routes->add('partner/withdrawal_requests/send', 'partner\Withdrawal_requests::send');
        $routes->add('partner/withdrawal_requests/delete', 'partner\Withdrawal_requests::delete');
        $routes->add('partner/withdrawal_requests/list', 'partner\Withdrawal_requests::list');
        $routes->add('partner/review', 'partner\Partner::review');
        $routes->add('partner/review_list', 'partner\Partner::review_list');
        $routes->add('partner/cash_collection', 'partner\Partner::cash_collection');
        $routes->add('partner/cash_collection_list', 'partner\Partner::cash_collection_history_list');
        $routes->add('partner/settlement', 'partner\Partner::settlement');
        $routes->add('partner/settlement_list', 'partner\Partner::settlement_list');
        $routes->add('partner/transactions', 'partner\Transactions::index');
        $routes->add('partner/transactions/list', 'partner\Transactions::list');
        $routes->add('partner/update_partner', 'admin\Partners::update_partner');
        $routes->add('partner/subscription', 'partner\Partner::subscription_list');
        $routes->add('partner/subscription_history', 'partner\Partner::subscription_history');
        $routes->add('partner/subscription-payment', 'partner\Subscription::subscription_payment');
        $routes->add('partner/subscription_history_list', 'partner\Partner::subscription_history_list');
        $routes->add('partner/subscription/pre-payment-setup', 'partner\Subscription::pre_payment_setup');
        $routes->post('partner/make_payment_for_subscription', 'partner\Partner::make_payment_for_subscription');
        $routes->get('partner/stripe_success', 'partner\Partner::success');
        $routes->get('partner/cancel', 'partner\Partner::cancel');

        $routes->get('partner/flutterwave_callback', 'partner\Partner::flutterwave_callback');


        $routes->get('partner/payment/checkout/(:any)', 'partner\Partner::checkout');
        $routes->get('payment/intent/(:any)', 'partner\Partner::createPaymentIntent/');
        $routes->get('razorpay-payment-form', 'partner\Partner::payWithRazorpay');
        $routes->post('razorpay-payment', 'partner\Partner::processPayment');
        $routes->add('partner/settlement_cashcollection_history', 'partner\Partner::settlement_cashcollection_history');
        $routes->add('partner/settlement_cashcollection_history_list', 'partner\Partner::settlement_cashcollection_history_list');
        //routes for chat
        $routes->add('partner/admin-support', 'partner\Chats::admin_support_index');
        $routes->add('partner/provider-chats', 'partner\Chats::provider_chats_index');

        $routes->add('partner/provider-booking-chats/(:any)', 'partner\Chats::provider_chats_index');

        $routes->add('partner/store_admin_chat', 'partner\Chats::store_admin_chat');
        $routes->add('partner/store_booking_chat', 'partner\Chats::store_booking_chat');
        $routes->add('partner/chat_get_all_messages', 'partner\Chats::getAllMessage');
        $routes->add('partner/save_web_token', 'partner\Partner::save_web_token');
        $routes->add('partner/provider_booking_chat_list', 'partner\Chats::provider_booking_chat_list');
        $routes->add('partner/check_booking_status', 'partner\Chats::check_booking_status');

        $routes->add('partner/get_customer', 'partner\Chats::get_customer');





        $routes->add('partner/gallery-view', 'partner\Gallery::index');
        $routes->add('partner/gallery/get-gallery-files/(:any)', 'partner\Gallery::GetGallaryFiles');
        $routes->add('partner/gallery/download-all', 'admin\partner::downloadAll');
        $routes->add('partner/JobRequests/', 'partner\JobRequests::index');
        $routes->add('partner/manage_category_preference', 'partner\JobRequests::manage_category_preference');
        $routes->add('partner/make_bid', 'partner\JobRequests::make_bid');
        $routes->add('partner/manage_accepting_custom_jobs', 'partner\JobRequests::manage_accepting_custom_jobs');
});
