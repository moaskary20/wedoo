<?php $data = get_settings('general_settings', true);
// $user1 = fetch_details('users', ["phone" => $_SESSION['identity']],);
$db      = \Config\Database::connect();
$builder = $db->table('users u');
$builder->select('u.*,ug.group_id')
    ->join('users_groups ug', 'ug.user_id = u.id')
    ->where('ug.group_id', 1)
    ->where(['phone' => $_SESSION['identity']]);
$user1 = $builder->get()->getResultArray();
$permissions = get_permission($user1[0]['id']);
$current_url = current_url();
$version = $db->table('updates')->select('*')->orderBy('id', 'DESC')->get(1)->getResult();
?>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
<!-- <div class="navbar-bg"></div> -->
<nav class="navbar new_nav_bar navbar-expand-lg main-navbar">
    <form class="form-inline mr-auto">
        <ul class="navbar-nav mr-3">
            <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars text-new-primary"></i></a></li>
            <?php
            if ($_SESSION['email'] == "superadmin@gmail.com") {
                defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
            } else if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) { ?>
                <li class="nav-item my-auto ml-2 mr-2">
                    <span class="badge badge-danger" style="border-radius: 8px!important">Demo mode</span>
                </li>
            <?php  } ?>
            <li class="nav-item my-auto ml-2 mr-2">
                <span class="badge badge-primary" style="border-radius: 8px!important"> <?php foreach ($version as $ver) : ?>
                        <?= $ver->version ?>
                    <?php endforeach; ?></span>
            </li>
            <li><a href="#" data-toggle="search" class="nav-link nav-link-lg d-sm-none"><i class="fas fa-search"></i></a></li>
            <div class=" nav-item search-element">
                <input class="form-control" type="search" id="menu-search" oninput="filterMenuItems()" onclick="showAllMenuItems()" placeholder="<?= labels('search', 'Search') ?>" aria-label="Search">
                <button class="btn " type="button">
                    <i class="fa fa-search d-inline text-dark"></i>
                </button>
                <div class="search-backdrop"></div>
                <div class="search-result">
                </div>
            </div>
        </ul>
    </form>
    <ul class="navbar-nav navbar-right">
        <?php
        // Fetch the default language
        $default_language = fetch_details('languages', ['is_default' => '1']);
                    // print_R($default_language);
                    // die;

        $default_language_id = (!empty($default_language)) ? $default_language[0]['id'] : null;
        ?>


        <?php

        if (count($languages_locale) > 1) { ?>
            <li class="dropdown navbar_dropdown mr-2 mt-2">
                <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                    <?php
                    $session = session();
                    $lang = $session->get('lang');
                    
                    if (!empty($lang)) {
                        $default_language = $lang;
                    } else {
                        $default_language = $default_language[0]['code'];
                    }
                    ?>
                    <div class="d-inline-block"><?= strtoupper($default_language) ?>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <?php foreach ($languages_locale as $language) { ?>
                        <?php
                        $is_default = ($language['id'] == $default_language_id);
                        ?>
                        <span onclick="set_locale('<?= $language['code'] ?>')" class="dropdown-item has-icon <?= ($language['code'] == $default_language) ? 'text-primary' : '' ?>" <?= ($is_default) ? 'selected' : '' ?>>
                            <?= strtoupper($language['code']) . " - " . ucwords($language['language']) ?>
                        </span>
                    <?php } ?>
                </div>
            </li>

        <?php   } else { ?>

            <li class="nav-item my-auto ml-2 mr-2">
                <span class="badge badge-primary mt-2" style="border-radius: 8px!important;"> <?php foreach ($languages_locale as $language) : ?>
                        <p class="p-0 m-0">
                            <?= strtoupper($language['code']) ?>
                        </p>
                    <?php endforeach; ?>
                </span>
            </li>

        <?php } ?>



        <li class="dropdown navbar_dropdown mt-2">
            <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                <img src="<?= base_url("/public/backend/assets/profiles/" . $user1[0]['image']) ?>" class="sidebar_logo h-max-60px navbar_image" alt="no image">
                <div class="d-inline-block"><?= labels('hello', 'Hi') ?> , <?= $user1[0]['username'] ?>
                </div>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <a href="<?= base_url('admin/profile') ?>" class="dropdown-item has-icon">
                    <i class="far fa-user"></i> <?= labels('profile', "Profile") ?>
                </a>
                <div class="dropdown-divider"></div>
                <a href="<?= base_url('auth/logout') ?>" class="dropdown-item has-icon text-danger">
                    <i class="fas fa-sign-out-alt"></i> <?= labels('logout', "Logout") ?>
                </a>
            </div>
        </li>
    </ul>
</nav>


<div class="main-sidebar">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <a href="<?= base_url('/') ?>">
                
                <?php
            $disk =fetch_current_file_manager();

                if (isset($disk) && $disk == "local_server") {
                    $logo = $data['logo'] != "" ? base_url("public/uploads/site/" . $data['logo']) : base_url('public/backend/assets/img/news/img01.jpg');
                } elseif (isset($disk) && $disk == "aws_s3") {
                    $logo = fetch_cloud_front_url('site', $data['logo']);
                } else {
                    $logo = $data['logo'] != "" ? base_url("public/uploads/site/" . $data['logo']) : base_url('public/backend/assets/img/news/img01.jpg');
                }


                if (isset($disk) && $disk == "local_server") {
                    $half_logo = $data['half_logo'] != "" ? base_url("public/uploads/site/" . $data['half_logo']) : base_url('public/backend/assets/img/news/img01.jpg');
                } elseif (isset($disk) && $disk == "aws_s3") {
                    $half_logo = fetch_cloud_front_url('site', $data['logo']);
                } else {
                    $half_logo = $data['half_logo'] != "" ? base_url("public/uploads/site/" . $data['half_logo']) : base_url('public/backend/assets/img/news/img01.jpg');
                }
                ?>
                <img src=" <?= $logo; ?>" class="sidebar_logo h-max-60px" alt="">
            </a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="<?= base_url('admin/') ?>">
                <img src="<?= $half_logo; ?>" height="40px" alt="">
            </a>
        </div>
        <ul class="sidebar-menu">
            <li class="nav-item">
                <a class="nav-link" href="<?= base_url('/admin/dashboard') ?>">
                    <span class="material-symbols-outlined mr-1 ">
                        home
                    </span>
                    <span class="span"><?= labels('Dashboard', 'Dashboard') ?></span>
                </a>
            </li>
            <?php if ($permissions['read']['partner'] == 1) { ?>
                <label for="provider management" class="heading_lable"><?= labels('provider_management', 'PROVIDER MANAGEMENT') ?></label>
                <li class="dropdown <?= ($current_url == base_url('/admin/partners') || $current_url == base_url('/admin/partners/add_partner')) ? 'active' : '' ?>">
                    <a href="#" class="nav-link has-dropdown " data-toggle="dropdown">
                        <span class="material-symbols-outlined ">
                            engineering
                        </span>
                        <span class="span hide-on-mini"><?= labels('providers', 'Providers') ?></span>
                    </a>
                    <ul class="dropdown-menu <?= ($current_url == base_url('/admin/partners') || $current_url == base_url('/admin/partners/add_partner') || $current_url == base_url('/admin/partners/bulk_import')) ? 'dropdown-active-open-menu' : '' ?>">
                        <?php if ($permissions['read']['partner'] == 1) { ?>
                            <li><a class="nav-link" href="<?= base_url('/admin/partners'); ?>">- <span><?= labels('provider_list', 'Provider List') ?></span></a></li>
                        <?php } ?>
                        <?php if ($permissions['create']['partner'] == 1) { ?>
                            <li><a class="nav-link" href="<?= base_url('/admin/partners/add_partner'); ?>">- <span><?= labels('add_new_provider', 'Add New Providers') ?></span></a></li>
                        <?php } ?>
                        <li><a class="nav-link" href="<?= base_url('/admin/partners/bulk_import'); ?>">- <span><?= labels('bulk_provider_update', ' Bulk Provider Update') ?></span></a></li>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/partners/payment_request'); ?>">
                        <span class="material-symbols-outlined">
                            payments
                        </span><span class="span"><?= labels('payment_request', "Payment Request") ?></span></a>
                </li>
            <?php }     ?>
            <li class="dropdown <?= ($current_url ==  base_url('admin/partners/settle_commission') || $current_url ==  base_url('admin/partners/manage_commission_history')) ? 'active' : '' ?>">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                    <span class="material-symbols-outlined">
                        receipt_long
                    </span><span class="span"><?= labels('manage_commission', "Settlements") ?></span>
                </a>
                <ul class="dropdown-menu <?= ($current_url ==  base_url('admin/partners/settle_commission') || $current_url ==  base_url('admin/partners/manage_commission_history')) ? 'dropdown-active-open-menu' : '' ?>">
                    <li>
                        <a class="nav-link" href="<?= base_url('admin/partners/settle_commission'); ?>">
                            <span class="span">- <?= labels('manage_commission', "Settlements") ?></span></a>
                    </li>
                    <li>
                        <a class="nav-link" href="<?= base_url('admin/partners/manage_commission_history') ?>">
                            <span class="span">- <?= labels('settlement_history', ' Settlement History') ?></span></a>
                    </li>
                </ul>
            </li>
            <li class="dropdown <?= ($current_url ==  base_url('admin/partners/cash_collection') || $current_url == base_url('admin/partners/cash_collection_history')) ? 'active' : '' ?>">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                    <span class="material-symbols-outlined">
                        universal_currency_alt</span>
                    <span class="span"><?= labels('cash_collection', "Cash Collection") ?></span>
                </a>
                <ul class="dropdown-menu <?= ($current_url ==  base_url('admin/partners/cash_collection') || $current_url == base_url('admin/partners/cash_collection_history')) ? 'dropdown-active-open-menu' : '' ?>" style="display: none;">
                    <li>
                        <a class="nav-link" href="<?= base_url('admin/partners/cash_collection') ?>">
                            <span class="span">- <?= labels('cash_collection', "Cash Collection") ?></span></a>
                    </li>
                    <li>
                        <a class="nav-link" href="<?= base_url('admin/partners/cash_collection_history') ?>">
                            <span class="span">- <?= labels('cash_collection_list', "Cash Collection List") ?></span></a>
                    </li>
                </ul>
            </li>
            <?php if ($permissions['read']['orders'] == 1) { ?>
                <label for="provider management" class="heading_lable"><?= labels('booking_management', 'BOOKING MANAGEMENT') ?></label>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('/admin/orders') ?>"><span class="material-symbols-outlined">
                            list_alt
                        </span><span class="span"> <?= labels('bookings', 'Bookings') ?></span></span></a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/all_settlement_cashcollection_history'); ?>">
                        <span class="material-symbols-outlined">
                            monetization_on
                        </span><span class="span"><?= labels('booking_payment', "Booking's Payment") ?></span></a>
                </li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/custom-job-requests'); ?>">
                        <span class="material-symbols-outlined">
                            work
                        </span><span class="span"><?= labels('custom_job_requests', "Custom Job Requests") ?></span></a>
                </li>

            <?php } ?>
            <?php if ($permissions['read']['services'] == 1) { ?>
                <label for="provider management" class="heading_lable"><?= labels('service_management', 'SERVICE MANAGEMENT') ?></label>
                <li class="dropdown <?= ($current_url ==   base_url('/admin/services/add_service') || $current_url == base_url("admin/services")) ? 'active' : '' ?>">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                        <span class="material-symbols-outlined">
                            list
                        </span><span class="span"><?= labels('service', 'Service') ?></span>
                    </a>
                    <ul class="dropdown-menu <?= ($current_url ==   base_url('/admin/services/add_service') || $current_url == base_url("admin/services") || $current_url == base_url('/admin/services/bulk_import_services')) ? 'dropdown-active-open-menu' : '' ?>">
                        <li class="nav-item"><a class="nav-link" href="<?= base_url("admin/services"); ?>">- <span><?= labels('service_list', 'Services List') ?></span></a></li>
                        <?php if ($permissions['create']['services'] == 1) { ?>
                            <li class="nav-item"><a class="nav-link" href="<?= base_url('/admin/services/add_service'); ?>">- <span><?= labels('add_new_service', 'Add New Service') ?></span></a></li>
                        <?php } ?>

                        <?php if ($permissions['create']['services'] == 1) { ?>
                            <li class="nav-item"><a class="nav-link" href="<?= base_url('/admin/services/bulk_import_services'); ?>">- <span><?= labels('bulk_service_update', 'Bulk Service Update') ?></span></a></li>
                        <?php } ?>
                    </ul>
                </li>
                <?php if ($permissions['read']['categories'] == 1) { ?>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url("admin/categories"); ?>">
                            <span class="material-symbols-outlined">
                                category
                            </span><span class="span"><?= labels('service_categories', 'Service Categories') ?></span></a>
                    </li>
                <?php } ?>
            <?php } ?>
            <label for="provider management" class="heading_lable"><?= labels('home_screen_management', 'HOME SCREEN MANAGEMENT') ?></label>
            <?php if ($permissions['read']['sliders'] == 1) { ?>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('/admin/sliders'); ?>"><span class="material-symbols-outlined">
                            view_day
                        </span><span class="span"><?= labels('sliders', 'Sliders') ?></span></span></a></li>
            <?php } ?>
            <?php if ($permissions['read']['featured_section'] == 1) { ?>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('/admin/Featured_sections') ?>"><span class="material-symbols-outlined">
                            view_comfy
                        </span> <span class="span"><?= labels('featured', 'Featured Section') ?></span></span></a></li>
            <?php } ?>
            <label for="provider management" class="heading_lable"><?= labels('customer_management', 'CUSTOMER MANAGEMENT') ?></label>
            <?php if ($permissions['read']['customers'] == 1) { ?>
                <li><a class="nav-link" href="<?= base_url('/admin/users'); ?>"><span class="material-symbols-outlined">
                            tv_signin
                        </span><span class="span"><?= labels('customers', "Customers") ?></span></span></a></li>
                <li><a class="nav-link" href="<?= base_url('/admin/transactions'); ?>"><span class="material-symbols-outlined">
                            receipt
                        </span><span class="span"><?= labels('transactions', "Transactions") ?></span></span></a></li>
                <li><a class="nav-link" href="<?= base_url('/admin/addresses'); ?>"><span class="material-symbols-outlined">
                            pin_drop
                        </span><span class="span"><?= labels('addresses', 'Addresses') ?></span></a></li>
            <?php } ?>
            <label for="support management" class="heading_lable"><?= labels('support_management', 'SUPPORT MANAGEMENT') ?></label>
            <li><a class="nav-link" href="<?= base_url('/admin/customer_queris'); ?>"><span class="material-symbols-outlined">
                        info
                    </span><span class="span"><?= labels('user_queries', 'User Queries') ?></span></a></li>
            <li>
                <a class="nav-link" href="<?= base_url('/admin/chat'); ?>"><span class="material-symbols-outlined">
                        chat_bubble
                    </span><span class="span"><?= labels('chat', "Chat") ?></span></span></a>
            </li>
            <label for="provider management" class="heading_lable"><?= labels('promotional_management', 'PROMOTIONAL MANAGEMENT') ?></label>
            <?php if ($permissions['read']['promo_code'] == 1) { ?>
                <li class="dropdown <?= ($current_url == base_url('/admin/promo_codes/add') || $current_url == base_url('/admin/promo_codes')) ? 'active' : '' ?>">
                    <a href="#" class="nav-link has-dropdown " data-toggle="dropdown">
                        <span class="material-symbols-outlined ">
                            sell
                        </span>
                        <span class="span hide-on-mini"><?= labels('promocode', 'Promo codes') ?></span>
                    </a>
                    <ul class="dropdown-menu <?= ($current_url ==   base_url('/admin/promo_codes/add') || $current_url == base_url("admin/promo_codes")) ? 'dropdown-active-open-menu' : '' ?>">
                        <li class="nav-item"><a class="nav-link" href="<?= base_url("admin/promo_codes"); ?>">- <span><?= labels('promocode', 'Promo codes') ?></span></a></li>
                        <?php if ($permissions['create']['promo_code'] == 1) { ?>
                            <li class="nav-item"><a class="nav-link" href="<?= base_url('/admin/promo_codes/add'); ?>">- <span><?= labels('add_promocodes', 'Add Promo Codes') ?></span></a></li>
                        <?php } ?>
                    </ul>
                </li>
            <?php } ?>
            <?php if ($permissions['read']['send_notification'] == 1) { ?>
                <li>
                    <a class="nav-link" href="<?= base_url('/admin/notification'); ?>"><span class="material-symbols-outlined">
                            phone_iphone
                        </span><span class="span"><?= labels('send_notifications', "Send Notifications") ?></span></span></a>
                </li>
                <li>
                    <a class="nav-link" href="<?= base_url('/admin/send_email_page'); ?>"><span class="material-symbols-outlined">
                            mail
                        </span><span class="span"><?= labels('send_email', "Send Email") ?></span></span></a>
                </li>
            <?php } ?>
            <label for="provider management" class="heading_lable"><?= labels('subscription_management', 'SUBSCRIPTION MANAGEMENT') ?></label>
            <?php if ($permissions['read']['subscription'] == 1) { ?>
                <li class="dropdown  <?= ($current_url ==   base_url('admin/subscription') || $current_url == base_url('admin/subscription/subscriber_list') || $current_url == base_url('admin/subscription/add_subscription')) ? 'active' : '' ?>">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><span class="material-symbols-outlined">
                            package_2
                        </span> <span class="span"><?= labels('subscription', "Subscription") ?></span></a>
                    <ul class="dropdown-menu <?= ($current_url ==   base_url('admin/subscription') || $current_url == base_url('admin/subscription/subscriber_list') || $current_url == base_url('admin/subscription/add_subscription')) ? 'dropdown-active-open-menu' : '' ?>" style="display: none;">
                        <li><a class="nav-link" href="<?= base_url('admin/subscription') ?>"><span>-<?= labels('list_subscription', "List Subscription") ?></span></span></a></li>
                        <li><a class="nav-link" href="<?= base_url('admin/subscription/subscriber_list'); ?>">-<span><?= labels('subscriber_list', "Subscriber List") ?></span></span></a></li>
                        <?php if ($permissions['create']['subscription'] == 1) { ?>
                            <li><a class="nav-link" href="<?= base_url('admin/subscription/add_subscription'); ?>">-<span><?= labels('add_subscription', "Add Subscription") ?></span></span></a></li>
                        <?php } ?>
                    </ul>
                </li>
            <?php } ?>
            <label for="provider management" class="heading_lable"><?= labels('media_section_management', 'MEDIA SECTION MANAGEMENT') ?></label>
            <li>
                <a class="nav-link" href="<?= base_url('admin/gallery-view') ?>"><span class="material-symbols-outlined">
                        gallery_thumbnail
                    </span><span class="span"> <?= labels('gallery', "Gallery") ?></span></span></a>
            </li>
            <label for="provider management" class="heading_lable"><?= labels('system_management', 'SYSTEM MANAGEMENT') ?></label>
            <?php if ($permissions['read']['settings'] == 1) { ?>
                <li>
                    <a class="nav-link" href="<?= base_url('admin/settings/system-settings') ?>"><span class="material-symbols-outlined">
                            settings
                        </span><span class="span"><?= labels('system_settings', "System Settings") ?></span></span></a>
                </li>
            <?php } ?>
            <?php if ($permissions['read']['faq'] == 1) { ?>
                <li>
                    <a class="nav-link" href="<?= base_url('admin/faqs') ?>"><span class="material-symbols-outlined">
                            help
                        </span><span class="span"><?= labels('faqs', "FAQs") ?></span></span></a>
                </li>
            <?php } ?>
            <?php if ($permissions['read']['system_user'] == 1) { ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('/admin/system_users'); ?>"><span class="material-symbols-outlined">
                            contact_emergency
                        </span><span class="span"><?= labels('system_user', 'System Users') ?></span></span></a>
                </li>
            <?php } ?>
            <li>
                <a class="nav-link" href="<?= base_url('/admin/database_backup'); ?>"><span class="material-symbols-outlined">
                        cloud_download
                    </span><span class="span"> <?= labels('database_backup', 'Database backup') ?></span></span></a>
            </li>
            <li>
        </ul>
    </aside>
</div>
<script>
    function filterMenuItems() {
        var searchInput = document.getElementById('menu-search').value.toLowerCase().trim();
        var staticMenuItems = [
            "<div class='heading_lable'><?= labels('Dashboard', 'Dashboard') ?></div>",
            "<a class='nav-link' href='<?= base_url('/admin/dashboard') ?>'><span class='material-symbols-outlined'>home</span><?= labels('Dashboard', 'Dashboard') ?></a>",
            "<div class='heading_lable'><?= labels('provider_management', 'PROVIDER MANAGEMENT') ?></div>",
            "<a class='nav-link' href='<?= base_url('/admin/partners'); ?>'><span class='material-symbols-outlined'>list</span> <?= labels('provider_list', 'Provider List') ?></a>",
            "<a class='nav-link' href='<?= base_url('/admin/partners/add_partner'); ?>'><span class='material-symbols-outlined'>person_add</span> <?= labels('add_new_provider', 'Add New Providers') ?></a>",
            "<a class='nav-link' href='<?= base_url('/admin/partners/bulk_import'); ?>'><span class='material-symbols-outlined'>upload</span><?= labels('bulk_provider_update', ' Bulk Provider Update') ?></a>",
            "<a class='nav-link' href='<?= base_url('admin/partners/payment_request'); ?>'><span class='material-symbols-outlined'>payments</span><?= labels('payment_request', 'Payment Request') ?></a>",
            "<a class='nav-link' href='<?= base_url('admin/partners/settle_commission'); ?>'><span class='material-symbols-outlined'>receipt_long</span> <?= labels('manage_commission', 'Settlements') ?></a>",
            "<a class='nav-link' href='<?= base_url('admin/partners/manage_commission_history'); ?>'><span class='material-symbols-outlined'>history</span> <?= labels('settlement_history', 'Settlement History') ?></a>",
            "<a class='nav-link' href='<?= base_url('admin/partners/cash_collection') ?>'><span class='material-symbols-outlined'>universal_currency_alt</span> <?= labels('cash_collection', "Cash Collection") ?></a>",
            "<a class='nav-link' href='<?= base_url('admin/partners/cash_collection_history') ?>'><span class='material-symbols-outlined'>history</span><?= labels('cash_collection_list', "Cash Collection List") ?></a>",
            "<div class='heading_lable'><?= labels('booking_management', 'BOOKING MANAGEMENT') ?></div>",
            "<a class='nav-link' href='<?= base_url('/admin/orders') ?>'><span class='material-symbols-outlined'>list_alt</span> <?= labels('bookings', 'Bookings') ?></a>",
            "<a class='nav-link' href='<?= base_url('admin/all_settlement_cashcollection_history'); ?>'><span class='material-symbols-outlined'>monetization_on</span> <?= labels('booking_payment', "Booking's Payment") ?></a>",
            "<a class='nav-link' href='<?= base_url('admin/custom-job-requests'); ?>'><span class='material-symbols-outlined'>work</span> <?= labels('custom_job_requests', "Custom Job Requests") ?></a>",
            "<div class='heading_lable'><?= labels('service_management', 'SERVICE MANAGEMENT') ?></div>",
            "<a class='nav-link' href='<?= base_url("admin/services"); ?>'><span class='material-symbols-outlined'>list_alt</span> <?= labels('service_list', 'Services List') ?></a>",
            "<a class='nav-link' href='<?= base_url('/admin/services/add_service'); ?>'><span class='material-symbols-outlined'>add</span> <?= labels('add_new_service', 'Add New Service') ?></a>",
            "<a class='nav-link' href='<?= base_url("admin/categories"); ?>'><span class='material-symbols-outlined'>grid_view</span><?= labels('service_categories', 'Service Categories') ?></a>",
            "<a class='nav-link' href='<?= base_url('/admin/services/bulk_import_services'); ?>'><span class='material-symbols-outlined'>upload</span><?= labels('bulk_service_update', 'Bulk Service Update') ?></a>",
            "<div class='heading_lable'><?= labels('home_screen_management', 'HOME SCREEN MANAGEMENT') ?></div>",
            "<a class='nav-link' href='<?= base_url('/admin/sliders'); ?>'><span class='material-symbols-outlined'>view_day</span><?= labels('sliders', 'Sliders') ?></a>",
            "<a class='nav-link' href='<?= base_url('/admin/Featured_sections') ?>'><span class='material-symbols-outlined'>view_comfy</span><?= labels('featured', 'Featured Section') ?></a>",
            "<div class='heading_lable'><?= labels('customer_management', 'CUSTOMER MANAGEMENT') ?></div>",
            "<a class='nav-link' href='<?= base_url('/admin/users/'); ?>'><span class='material-symbols-outlined'>tv_signin</span><?= labels('customers', "Customers") ?></a>",
            "<a class='nav-link' href='<?= base_url('/admin/transactions'); ?>'><span class='material-symbols-outlined'>receipt</span><?= labels('transactions', "Transactions") ?></a>",
            "<a class='nav-link' href='<?= base_url('/admin/addresses'); ?>'><span class='material-symbols-outlined'>pin_drop</span><?= labels('addresses', 'Addresses') ?></a>",
            "<div class='heading_lable'><?= labels('support_management', 'SUPPORT MANAGEMENT') ?></div>",
            "<a class='nav-link' href='<?= base_url('/admin/customer_queris'); ?>'><span class='material-symbols-outlined'>info</span><?= labels('user_queries', 'User Queries') ?></a>",
            "<a class='nav-link' href='<?= base_url('/admin/chat'); ?>'><span class='material-symbols-outlined'>chat_bubble</span><?= labels('chat', "Chat") ?></a>",
            "<div class='heading_lable'><?= labels('promotional_management', 'PROMOTIONAL MANAGEMENT') ?></div>",
            "<a class='nav-link' href='<?= base_url('/admin/promo_codes'); ?>'><span class='material-symbols-outlined'>sell</span><?= labels('promocode', 'Promo codes') ?></a>",
            "<a class='nav-link' href='<?= base_url('/admin/promo_codes/add'); ?>'><span class='material-symbols-outlined'>add</span><?= labels('add_promocodes', 'Add Promo Codes') ?></a>",
            "<a class='nav-link' href='<?= base_url('/admin/notification'); ?>'><span class='material-symbols-outlined'>phone_iphone</span><?= labels('send_notifications', "Send Notifications") ?></a>",
            "<a class='nav-link' href='<?= base_url('/admin/send_email_page'); ?>'><span class='material-symbols-outlined'>mail</span><?= labels('send_email', "Send Email") ?></a>",
            "<div class='heading_lable'><?= labels('subscription_management', 'SUBSCRIPTION MANAGEMENT') ?></div>",
            "<a class='nav-link' href='<?= base_url('admin/subscription') ?>'><span class='material-symbols-outlined'>package_2</span><?= labels('list_subscription', "List Subscription") ?></a>",
            "<a class='nav-link' href='<?= base_url('admin/subscription/subscriber_list'); ?>'><span class='material-symbols-outlined'>groups</span><?= labels('subscriber_list', "Subscriber List") ?></a>",
            "<a class='nav-link' href='<?= base_url('admin/subscription/add_subscription'); ?>'><span class='material-symbols-outlined'>add</span><?= labels('add_subscription', "Add Subscription") ?></a>",
            "<div class='heading_lable'><?= labels('media_section_management', 'MEDIA SECTION MANAGEMENT') ?></div>",
            "<a class='nav-link' href='<?= base_url('admin/gallery-view') ?>'><span class='material-symbols-outlined'>gallery_thumbnail</span><?= labels('gallery', "Gallery") ?></a>",
            "<div class='heading_lable'><?= labels('system_management', 'SYSTEM MANAGEMENT') ?></div>",
            "<a class='nav-link' href='<?= base_url('admin/settings/system-settings') ?>'><span class='material-symbols-outlined'>settings</span><?= labels('system_settings', "System Settings") ?></a>",
            "<a class='nav-link' href='<?= base_url('admin/faqs') ?>'><span class='material-symbols-outlined'>help</span><?= labels('faqs', "FAQs") ?></a>",
            "<a class='nav-link' href='<?= base_url('/admin/system_users'); ?>'><span class='material-symbols-outlined'>contact_emergency</span><?= labels('system_user', 'System Users') ?></a>",
            "<a class='nav-link' href='<?= base_url('/admin/database_backup'); ?>'><span class='material-symbols-outlined'>cloud_download</span><?= labels('database_backup', 'Database Backup') ?></a>",
        ];
        var searchResultContainer = document.querySelector('.search-result');
        searchResultContainer.innerHTML = ''; // Clear previous results
        if (searchInput === '') {
            // Show all menu items when search input is empty
            staticMenuItems.forEach(item => {
                // if (!item.includes('heading_lable')) {
                var searchItem = document.createElement('div');
                searchItem.classList.add('search-item');
                searchItem.innerHTML = item;
                searchResultContainer.appendChild(searchItem);
                // }
            });
            // Reset the height to the default value
            searchResultContainer.style.height = '500px';
        } else {
            // Filter menu items based on the search input
            var matchingItems = staticMenuItems.filter(item => {
                if (!item.includes('heading_lable')) {
                    return item.toLowerCase().includes(searchInput);
                }
                return false;
            });
            if (matchingItems.length > 0) {
                // Display matching menu items
                matchingItems.forEach(item => {
                    var searchItem = document.createElement('div');
                    searchItem.classList.add('search-item');
                    searchItem.innerHTML = item;
                    searchResultContainer.appendChild(searchItem);
                });
                // Calculate and set the height based on the number of results
                var resultHeight = matchingItems.length * 40; // Adjust 40 based on your styling
                searchResultContainer.style.height = resultHeight + 'px';
            } else {
                // If no results, set a default height
                searchResultContainer.style.height = '500px';
            }
        }
        // Show or hide the search results container
        searchResultContainer.style.display = matchingItems.length > 0 ? 'block' : 'none';
    }

    function showAllMenuItems() {
        // Display the entire menu when the input box is clicked for the first time
        var searchInput = document.getElementById('menu-search').value.trim();
        if (searchInput === '') {
            filterMenuItems();
        }
    }
    document.getElementById('menu-search').addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
        }
    });
</script>