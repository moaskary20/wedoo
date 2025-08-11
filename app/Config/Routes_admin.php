<?php


$routes->get('/admin/login', 'Auth::login');
$routes->get('/', 'admin\Dashboard::index');

$routes->group('', ['filter' => 'admin_sanitizer'], function($routes) {




//Admin login
$routes->get('payment-form', 'RazorpayController::payWithRazorpay');
$routes->post('payment', 'RazorpayController::processPayment');
$routes->get('update_subscription_status', 'admin\Dashboard::update_subscription_status');
$routes->get('cancle_elapsed_time_order', 'admin\Dashboard::cancle_elapsed_time_order');
$routes->get('/customer_privacy_policy', 'Auth::customer_privacy_policy');
$routes->get('/provider-details/(:any)', 'Auth::providerDetails');


// Route::get('/product-details/{slug}', [SettingController::class, 'webPageURL'])->name('deep-link');
$routes->add('admin/forgot-password', 'admin\Dashboard::forgot_password');
//...

$routes->add('admin/dashboard', 'admin\Dashboard::index');
$routes->add('admin/dashboard/recent_booking', 'admin\Dashboard::recent_orders');
$routes->add('admin/dashboard/top_trending_services', 'admin\Dashboard::top_trending_services');
$routes->add('admin/profile', 'admin\Profile::index');
$routes->add('admin/profile/update', 'admin\Profile::update');
//LANGUAGE ROUTES

// $routes->get('lang/(:alpha)', 'Language::switchLang/$1');
// $routes->get('lang/(:segment)', 'Language::index/$1');
// $routes->get('lang/(:any)', 'Language::index\$1');
$routes->get('lang/(:any)', 'Language::index/$1');

$routes->post('lang/updateIsRtl', 'Language::updateIsRtl');
$routes->get('admin/languages/', "admin\Languages::index");
$routes->post('admin/languages/create', "admin\Languages::create");
$routes->post('admin/languages/set_labels', "admin\Languages::set_labels");
$routes->get('admin/languages/change/(:any)', "admin\Languages::change/$1");
$routes->add('admin/languages/remove', 'admin\Languages::remove');
$routes->add('admin/upload_update_file', 'admin\Updater::upload_update_file');
$routes->post('admin/languages/insert', "admin\Languages::insert");
$routes->get('download_sample_file', 'admin\Languages::language_sample');
$routes->add('download_old_file/(:any)', 'admin\Languages::language_old');
$routes->add('admin/language/list', 'admin\Languages::list');
$routes->add('admin/language/update', 'admin\Languages::update');
$routes->add('admin/language/remove_langauge', 'admin\Languages::remove');
$routes->add('admin/language/store_default_language', 'admin\Languages::store_default_language');
//SETTINGS ROUTES
$routes->add('admin/settings', 'admin\Settings::index');
$routes->add('admin/settings/themes', 'admin\Settings::themes');
$routes->add('admin/settings/general-settings', 'admin\Settings::general_settings');
$routes->add('admin/settings/email-settings', 'admin\Settings::email_settings');
$routes->add('admin/settings/pg-settings', 'admin\Settings::pg_settings');
$routes->add('admin/settings/api_key_settings', 'admin\Settings::api_key_settings');
$routes->add('admin/settings/system_tax_settings', 'admin\Settings::system_tax_settings');
$routes->add('admin/settings/app_settings', 'admin\Settings::app_settings');
$routes->add('admin/settings/customer_privacy_policy_page', 'admin\Settings::customer_privacy_policy_page');
$routes->add('admin/settings/partner_privacy_policy_page', 'admin\Settings::partner_privacy_policy_page');
$routes->add('admin/settings/firebase_settings', 'admin\Settings::firebase_settings');
$routes->add('admin/settings/customer_terms_and_condition', 'admin\Settings::customer_tearms_and_condition');
$routes->add('admin/settings/provider_terms_and_condition', 'admin\Settings::provider_terms_and_condition');
$routes->add('admin/settings/refund_policy_page', 'admin\Settings::refund_policy_page');
$routes->add('admin/settings/system-settings', 'admin\Settings::main_system_setting_page');
$routes->add('admin/settings/about-us', 'admin\Settings::about_us');
$routes->add('admin/settings/contact-us', 'admin\Settings::contact_us');
$routes->add('admin/settings/app', 'admin\Settings::app_settings');
$routes->add('admin/settings/country_codes', 'admin\Settings::contry_codes');
$routes->add('admin/settings/add_contry_code', 'admin\Settings::add_contry_code');
$routes->add('admin/settings/fetch_contry_code', 'admin\Settings::fetch_contry_code');
$routes->add('admin/settings/delete_contry_code', 'admin\Settings::delete_contry_code');
$routes->add('admin/settings/store_default_country_code', 'admin\Settings::store_default_country_code');
$routes->add('admin/settings/update_country_codes', 'admin\Settings::update_country_codes');
$routes->add('admin/settings/web_setting', 'admin\Settings::web_setting_page');
$routes->add('admin/settings/web_setting_update', 'admin\Settings::web_setting_update');
$routes->add('admin/settings/terms-and-conditions', 'admin\Settings::terms_and_conditions');
$routes->add('admin/settings/privacy-policy', 'admin\Settings::privacy_policy');
$routes->add('admin/settings/refund-policy', 'admin\Settings::refund_policy');
$routes->add('admin/settings/customer-terms-and-conditions', 'admin\Settings::customer_terms_and_conditions');
$routes->add('admin/settings/customer-privacy-policy', 'admin\Settings::customer_privacy_policy');
$routes->add('admin/settings/about-us-preview', 'admin\Settings::about_us_page_preview');
$routes->add('admin/settings/contact-us-preview', 'admin\Settings::contact_us_page_preview');
$routes->add('admin/settings/sms-gateways', 'admin\Settings::sms_gateway_setting_index');
$routes->add('admin/settings/sms-gateway-settings', 'admin\Settings::sms_gateway_setting_update');
$routes->add('admin/settings/sms-templates', 'admin\Settings::sms_templates');
$routes->add('admin/settings/sms-templates-list', 'admin\Settings::sms_template_list');
$routes->add('admin/settings/edit_sms_template/(:any)', 'admin\Settings::edit_sms_template');
$routes->add('admin/settings/edit-sms-templates', 'admin\Settings::edit_sms_template_update');
$routes->add('admin/settings/notification-settings', 'admin\Settings::notification_settings');
$routes->add('admin/settings/notification_setting_update', 'admin\Settings::notification_setting_update');
$routes->add('admin/settings/sms-email-preview/(:any)', 'admin\Settings::sms_email_preview');
$routes->add('admin/settings/updater', 'admin\Updater::index');
$routes->add('admin/settings/web-landing-page-settings', 'admin\Settings::web_landing_page_settings');
$routes->add('admin/settings/web-landing-page-settings-update', 'admin\Settings::web_setting_landing_page_update');
$routes->add('admin/settings/review-list', 'admin\Settings::review_list');
$routes->add('admin/settings/become-provider-setting', 'admin\Settings::become_provider_setting_page');
$routes->add('admin/settings/become-provider-setting-update', 'admin\Settings::become_provider_setting_page_update');

//CATEGORY ROUTES
$routes->add('admin/categories/', 'admin\Categories::index');
$routes->add('admin/category/add_category', 'admin\Categories::add_category');
$routes->add('admin/category/remove_category', 'admin\Categories::remove_category');
$routes->add('admin/category/update_category', 'admin\Categories::update_category');
$routes->add('admin/categories/list', 'admin\Categories::list');
//FEATURE SECTION ROUTES
$routes->add('admin/Featured_sections', 'admin\Featured_sections::index');
$routes->add('admin/featured_sections/add_featured_section', 'admin\Featured_sections::add_featured_section');
$routes->add('admin/featured_sections/get_custom_services', 'admi\/Featured_sections::get_custom_services');
$routes->add('admin/featured_sections/list', 'admin\Featured_sections::list');
$routes->add('admin/featured_sections/update_featured_section', 'admin\Featured_sections::update_featured_section');
$routes->add('admin/featured_sections/delete_featured_section', 'admin\Featured_sections::delete_featured_section');
$routes->add('admin/featured-section/change-order', 'admin\Featured_sections::change_order');
// PROMOCODE ROUTES
$routes->add('admin/promo_codes', 'admin\Promo_codes::index');
$routes->add('admin/promo_codes/list', 'admin\Promo_codes::list');
$routes->add('admin/promo_codes/delete', 'admin\Promo_codes::delete_promo_code');
$routes->add('admin/promo_codes/add', 'admin\Promo_codes::add');
$routes->add('admin/promo_codes/save', 'admin\Promo_codes::save');
$routes->add('admin/promo_codes/update', 'admin\Promo_codes::update');
$routes->add('admin/promo_codes/duplicate/(:any)', 'admin\Promo_codes::duplicate');
// SLIDER ROUTES 
$routes->add('admin/sliders', 'admin\Sliders::index');
$routes->add('admin/sliders/list', 'admin\Sliders::list');
$routes->add('admin/sliders/add_slider', 'admin\Sliders::add_slider');
$routes->add('admin/sliders/update_slider', 'admin\Sliders::update_slider');
$routes->add('admin/sliders/delete_sliders', 'admin\Sliders::delete_sliders');
//PARTNER ROUTES 
$routes->add('admin/partners', 'admin\Partners::index');
$routes->add('admin/partners/list', 'admin\Partners::list');
$routes->add('admin/partners/add_partner', 'admin\Partners::add_partner');
$routes->add('admin/partner/insert_partner', 'admin\Partners::insert_partner');
$routes->add('admin/partners/edit_partner/(:any)', 'admin\Partners::edit_partner');
$routes->add('admin/partners/general_outlook/(:any)', 'admin\Partners::general_outlook');
$routes->add('admin/partners/partner_company_information/(:any)', 'admin\Partners::partner_company_information');
$routes->add('admin/partners/partner_service_details/(:any)', 'admin\Partners::partner_service_details');
$routes->add('admin/partners/partner_order_details/(:any)', 'admin\Partners::partner_order_details');
$routes->add('admin/partners/partner_order_details_list/(:any)', 'admin\Partners::partner_order_details_list');
$routes->add('admin/partners/partner_promocode_details/(:any)', 'admin\Partners::partner_promocode_details');
$routes->add('admin/partners/partner_promocode_details_list/(:any)', 'admin\Partners::partner_promocode_details_list');
$routes->add('admin/partners/partner_review_details/(:any)', 'admin\Partners::partner_review_details');
$routes->add('admin/partners/partner_review_details_list/(:any)', 'admin\Partners::partner_review_details_list');
$routes->add('admin/partners/partner_fetch_sales/(:any)', 'admin\Partners::partner_fetch_sales');
$routes->add('admin/partners/partner_subscription/(:any)', 'admin\Partners::partner_subscription');
$routes->get('admin/partners/all_subscription/(:any)', 'admin\Partners::all_subscription_list');
$routes->add('admin/partners/partner_settlement_and_cash_collection_history/(:any)', 'admin\Partners::partner_settlement_and_cash_collection_history');
$routes->add('admin/partners/partner_settlement_and_cash_collection_history_list/(:any)', 'admin\Partners::partner_settlement_and_cash_collection_history_list');
$routes->add('admin/partners/view_partner/(:any)', 'admin\Partners::view_partner');
$routes->add('admin/partners/partner_details/(:any)', 'admin\Partners::partner_details');
$routes->add('admin/partners/banking_details/(:any)', 'admin\Partners::banking_details');
$routes->add('admin/partners/timing_details/(:any)', 'admin\Partners::timing_details');
$routes->add('admin/partners/service_details/(:any)', 'admin\Partners::service_details');
$routes->post('admin/partner/deactivate_partner', 'admin\Partners::deactivate_partner');
$routes->post('admin/partner/activate_partner', 'admin\Partners::activate_partner');
$routes->post('admin/partner/approve_partner', 'admin\Partners::approve_partner');
$routes->post('admin/partner/disapprove_partner', 'admin\Partners::disapprove_partner');
$routes->post('admin/partner/delete_partner', 'admin\Partners::delete_partner');
$routes->add('admin/partners/payment_request', 'admin\Partners::payment_request');
$routes->add('admin/partners/payment_request_list', 'admin\Partners::payment_request_list');
$routes->add('admin/partners/payment_request_multiple_update', 'admin\Partners::payment_request_multiple_update');
$routes->add('admin/partners/payment_request_settement_status', 'admin\Partners::payment_request_settement_status');
$routes->add('admin/partners/edit_request', 'admin\Partners::payment_request_list');
$routes->add('admin/partners/pay_partner', 'admin\Partners::pay_partner');
$routes->add('admin/partners/delete_request', 'admin\Partners::delete_request');
$routes->add('admin/partners/settle_commission', 'admin\Partners::settle_commission');
$routes->add('admin/partners/commission_list', 'admin\Partners::commission_list');
$routes->add('admin/partners/bulk_commission_settelement', 'admin\Partners::bulk_commission_settelement');
$routes->add('admin/partners/commission_pay_out', 'admin\Partners::commission_pay_out');
$routes->add('admin/partners/view_ratings/(:any)', 'admin\Partners::view_ratings');
$routes->add('admin/partners/delete_rating', 'admin\Partners::delete_rating');
$routes->add('admin/partners/cash_collection', 'admin\Partners::cash_collection');
$routes->add('admin/partners/cash_collection_list', 'admin\Partners::cash_collection_list');
$routes->add('admin/partners/cash_collection_deduct', 'admin\Partners::cash_collection_deduct');
$routes->add('admin/partners/cash_collection_history', 'admin\Partners::cash_collection_history');
$routes->add('admin/partners/manage_commission_history', 'admin\Partners::settle_commission_history');
$routes->add('admin/partners/manage_commission_history_list', 'admin\Partners::manage_commission_history_list');
$routes->add('admin/partners/cash_collection_history_list', 'admin\Partners::cash_collection_history_list');
$routes->add('admin/partners/bulk_cash_collection', 'admin\Partners::bulk_cash_collection');
$routes->add('admin/partners/duplicate/(:any)', 'admin\Partners::duplicate');
//USER ROUTES  
$routes->add('admin/users', 'admin\Users::index');
$routes->add('admin/users/deactivate', 'admin\Users::deactivate');
$routes->add('admin/users/activate', 'admin\Users::activate');
$routes->add('admin/list-user', 'admin\Users::list_user');
//ADDRESS ROUTES
$routes->add('admin/addresses', 'admin\Addresses::index');
$routes->add('admin/addresses/list', 'admin\Addresses::list');
//SERVIES ROUTES
$routes->add('admin/services', 'admin\Services::index');
$routes->add('admin/services/list', 'admin\Services::list');
$routes->add('admin/services/simple_add_service', 'admin\Services::simple_add_service');
$routes->add('admin/services/test_add_simple', 'admin\Services::test_add_simple');
$routes->add('admin/services/test_simple_form', 'admin\Services::test_simple_form');
$routes->add('admin/services/simple_add_service_page', 'admin\Services::simple_add_service_page');
$routes->add('admin/services/add_service', 'admin\Services::add_service_view');
$routes->add('admin/services/insert_service', 'admin\Services::add_service');
$routes->add('admin/services/delete_service', 'admin\Services::delete_service');
$routes->add('admin/services/edit_service/(:any)', 'admin\Services::edit_service');
$routes->add('admin/services/update_service', 'admin\Services::update_service');
$routes->add('admin/services/service_detail/(:any)', 'admin\Services::service_detail');
$routes->post('admin/services/disapprove_service', 'admin\Services::disapprove_service');
$routes->post('admin/services/approve_service', 'admin\Services::approve_service');
$routes->add('admin/services/duplicate/(:any)', 'admin\Services::duplicate');
$routes->add('admin/services/bulk_import_services/', 'admin\Services::bulk_import_services');
$routes->add('admin/services/bulk_import_service_upload/', 'admin\Services::bulk_import_service_upload');
$routes->add('admin/services/download-sample-for-insert/', 'admin\Services::downloadSampleForInsert');
$routes->add('admin/services/download-sample-for-update/', 'admin\Services::downloadSampleForUpdate');
$routes->add('admin/services/Service-Add-Instructions/', 'admin\Services::ServiceAddInstructions');
$routes->add('admin/services/Service-Update-Instructions/', 'admin\Services::ServiceUpdateInstructions');




//ORDERS ROUTE
$routes->add('admin/orders', 'admin\Orders::index');
$routes->add('admin/orders/list', 'admin\Orders::list');
$routes->add('admin/orders/veiw_orders/(:any)', 'admin\Orders::view_orders');
$routes->add('admin/orders/view_user/(:any)', 'admin\Orders::view_user');
$routes->add('admin/orders/view_payment_details/(:any)', 'admin\Orders::view_payment_details');
$routes->add('admin/orders/change_order_status', 'admin\Orders::change_order_status');
$routes->add('admin/orders/upload_file', 'admin\Orders::upload_file');
$routes->add('admin/orders', 'admin\Orders::index');
$routes->add('admin/orders/list', 'admin\Orders::list');
$routes->add('admin/Orders/delete_orders', 'admin\Orders::delete_orders');
$routes->add('admin/orders/veiw_orders/(:any)', 'admin\Orders::view_orders');
$routes->add('admin/orders/invoice/(:any)', 'admin\Orders::invoice');
$routes->add('admin/orders/invoice_table/(:any)', 'admin\Orders::invoice_table');
$routes->add('admin/orders/customer_details/(:any)', 'admin\Orders::customer_details');
$routes->add('admin/orders/payment_details/(:any)', 'admin\Orders::payment_details');
$routes->add('admin/orders/partner_details/(:any)', 'admin\Orders::partner_details');
$routes->add('admin/orders/view_ordered_services', 'admin\Orders::view_ordered_services');
$routes->add('admin/orders/view_ordered_services_list', 'admin\Orders::view_ordered_services_list');
$routes->add('admin/orders/cancel_order_service', 'admin\Orders::cancel_order_service');
$routes->add('admin/orders/get_slots', 'admin\Orders::get_slots');
//FAQS ROUTES
$routes->add('admin/faqs', 'admin\Faqs::index');
$routes->add('admin/faqs/add_faqs', 'admin\Faqs::add_faqs');
$routes->add('admin/faqs/list', 'admin\Faqs::list');
$routes->add('admin/faqs/remove_faqs', 'admin\Faqs::remove_faqs');
$routes->add('admin/faqs/edit_faqs', 'admin\Faqs::edit_faqs');
//NOTIFICATION ROUTES
$routes->add('admin/notification', 'admin\Notification::index');
$routes->add('admin/notification/add_notification', 'admin\Notification::add_notification');
$routes->add('admin/notification/delete_notification', 'admin\Notification::delete_notification');
$routes->add('admin/notification/list', 'admin\Notification::list');
//TAX ROUTES
$routes->add('admin/taxes', 'admin\Tax::index');
$routes->add('admin/tax/add_tax', 'admin\Tax::add_tax');
$routes->add('admin/tax/list', 'admin\Tax::list');
$routes->add('admin/tax/edit_taxes', 'admin\Tax::edit_taxes');
$routes->add('admin/tax/remove_taxes', 'admin\Tax::remove_taxes');
// SUBSCRIPTION ROUTES
$routes->add('admin/subscription/', 'admin\Subscription::index', ['as' => 'admin_subscription']);
$routes->add('admin/subscription/add_subscription', 'admin\Subscription::add_subscription');
$routes->add('admin/subscription/add_store_subscription', 'admin\Subscription::add_store_subscription');
$routes->add('admin/subscription/edit_subscription_page/(:any)', 'admin\Subscription::edit_subscription_page');
$routes->add('admin/subscription/edit_subscription', 'admin\Subscription::edit_subscription');
$routes->add('admin/subscription/delete_subscription', 'admin\Subscription::delete_subscription');
$routes->add('admin/subscription/list', 'admin\Subscription::list');
$routes->add('admin/add_ons/', 'admin\Subscription::add_ons_index');
$routes->add('admin/add_ons/create_add_ons', 'admin\Subscription::add_on_create_page');
$routes->add('admin/subscription/subscriber_list', 'admin\Subscription::subscriber_list');
$routes->add('admin/subscription/partner_subscriber_list', 'admin\Subscription::partner_subscription_list');
$routes->post('admin/assign_subscription_to_partner', 'admin\Partners::assign_subscription_to_partner');
$routes->post('admin/assign_subscription_to_partner_from_edit_provider', 'admin\Partners::assign_subscription_to_partner_from_edit_provider');
$routes->post('admin/cancle_subscription_plan', 'admin\Partners::cancel_subscription_plan');
$routes->post('admin/cancel_subscription_plan_from_edit_partner', 'admin\Partners::cancel_subscription_plan_from_edit_partner');
$routes->add('admin/transactions', 'admin\Transactions::index');
$routes->add('admin/transactions/list-transactions', 'admin\Transactions::list_transactions');
//comman routes
$routes->add('admin/delete_details', 'admin\Admin::delete_details');
//SYSTEM USER ROUTE 
$routes->add('admin/system_users', 'admin\System_users::index');
$routes->add('admin/system_users/list', 'admin\System_users::list');
$routes->add('admin/system_users/deactivate_user', 'admin\System_users::deactivate_user');
$routes->add('admin/system_users/activate_user', 'admin\System_users::activate_user');
$routes->add('admin/system_users/delete_user', 'admin\System_users::delete_user');
$routes->add('admin/system_users/add_user', 'admin\System_users::add_user');
$routes->add('admin/system_users/permit', 'admin\System_users::permit');
$routes->add('admin/system_users/edit_permit', 'admin\System_users::edit_permit');
$routes->add('save-web-token', 'admin\Dashboard::save_web_token');
$routes->add('admin/all_settlement_cashcollection_history', 'admin\Partners::all_settlement_cashcollection_history');
$routes->add('admin/all_settlement_cashcollection_history_list', 'admin\Partners::all_settlement_cashcollection_history_list');
$routes->add('admin/customer_queris', 'admin\Dashboard::customer_queris');
$routes->add('admin/customer_queris_list', 'admin\Dashboard::customer_queris_list');
//CHAT ROUTES
$routes->add('admin/chat', 'admin\Chats::index');
$routes->add('admin/store_chat', 'admin\Chats::store_chat');
$routes->add('admin/chat_get_all_messages', 'admin\Chats::getAllMessage');
$routes->add('admin/get_customers', 'admin\Chats::get_customers');
$routes->add('admin/get_providers', 'admin\Chats::get_providers');
$routes->add('admin/settings/email-configuration', 'admin\Settings::email_template_configuration');
$routes->add('admin/settings/email_template_configuration_update', 'admin\Settings::email_template_configuration_update');
$routes->add('admin/settings/email_template_list', 'admin\Settings::email_template_list');
$routes->add('admin/settings/email_template_list_fetch', 'admin\Settings::email_template_list_fetch');
$routes->add('admin/settings/edit_email_template/(:any)', 'admin\Settings::edit_email_template');
$routes->add('admin/settings/edit_email_template_operation', 'admin\Settings::edit_email_template_operation');
$routes->add('admin/settings/delete_email_template', 'admin\Settings::delete_email_template');
// Email routes
//NOTIFICATION ROUTES
$routes->add('admin/send_email_page', 'admin\SendEmail::index');
$routes->add('admin/send_email', 'admin\SendEmail::send_email');
$routes->add('admin/email_list', 'admin\SendEmail::list');
$routes->add('admin/delete_email', 'admin\SendEmail::delete_email');
$routes->add('unsubscribe_link/(:any)', 'admin\SendEmail::unsubscribe_link_view');
$routes->add('admin/unsubscribe_email_op', 'admin\SendEmail::unsubscription_email_operation');
$routes->add('admin/media/upload', 'admin\Dashboard::upload_media');
$routes->add('admin/database_backup', 'admin\DatabaseOperations::index');
$routes->add('admin/clean_database', 'admin\DatabaseOperations::clean_database_index');
$routes->add('admin/clean_database_tables', 'admin\DatabaseOperations::clean_database_tables');
$routes->add('admin/logs', "admin\LogViewerController::index");
$routes->add('admin/partners/bulk_import/', 'admin\Partners::bulk_import');
$routes->add('admin/partners/download-sample-for-insert/', 'admin\Partners::downloadSampleForInsert');
$routes->add('admin/partners/download-sample-for-update/', 'admin\Partners::downloadSampleForUpdate');
$routes->add('admin/partners/bulk_import_provider_upload/', 'admin\Partners::bulk_import_provider_upload');
$routes->add('admin/partners/Provider-Add-Instructions/', 'admin\Partners::ProviderAddInstructions');
$routes->add('admin/partners/Provider-Update-Instructions/', 'admin\Partners::ProviderUpdateInstructions');


$routes->add('admin/gallery-view', 'admin\Gallery::index');
$routes->add('admin/gallery/get-gallery-files/(:any)', 'admin\Gallery::GetGallaryFiles');
$routes->add('admin/gallery/download-all', 'admin\Gallery::downloadAll');

$routes->add('admin/custom-job-requests', 'admin\CustomJobRequest::index');
$routes->add('admin/custom-job-requests-list', 'admin\CustomJobRequest::list');

$routes->add('admin/custom-job/bidders-list/(:any)', 'admin\CustomJobRequest::bidders_list');
$routes->add('admin/custom-job/bidders/(:any)', 'admin\CustomJobRequest::bidders_list_page');


// $routes->get('admin/queue/add', 'admin\QueueController::addToQueue');
// $routes->get('admin/queue/process', 'admin\QueueController::processQueue');

$routes->get('admin/queue/queue1', 'admin\QueueController::queueNumbers');

$routes->get('admin/queue/work', 'admin\QueueController::work');
$routes->get('admin/queue/stop', 'admin\QueueController::stopWorker');
$routes->get('admin/queue/flush', 'admin\QueueController::flushQueue');

});

$routes->get('admin/slug/category', 'admin\SlugController::category');
$routes->get('admin/slug/partner', 'admin\SlugController::partner');
$routes->get('admin/slug/service', 'admin\SlugController::service');
