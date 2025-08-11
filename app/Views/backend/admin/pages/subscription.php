<?php
// $user1 = fetch_details('users', ["phone" => $_SESSION['identity']],);
$db      = \Config\Database::connect();
$builder = $db->table('users u');
$builder->select('u.*,ug.group_id')
    ->join('users_groups ug', 'ug.user_id = u.id')
    ->where('ug.group_id', 1)
    ->where(['phone' => $_SESSION['identity']]);
$user1 = $builder->get()->getResultArray();
$permissions = get_permission($user1[0]['id']);
?>
<div class="main-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('subscription', "Subscription") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item"><i class="fas fa-newspaper text-warning"></i> <?= labels('subscription', 'Subscription') ?></a></div>
            </div>
        </div>
        <div class="container-fluid card">
            <?php if ($permissions['read']['partner'] == 1) { ?>
                <div class="row mb-3">
                    <div class="col-lg">
                        <div class="row mt-4 mb-3 ml-1">
                            <div class='btn bg-emerald-blue tag text-emerald-blue mr-2  mb-2 filters_table' id="subscription_filter_all" name="subscription_filter" value="subscription_filter"><?= labels('all', 'All') ?></div>
                            <div class='btn bg-emerald-success tag text-emerald-success mr-2 filters_table' id="subscription_filter_active" name="subscription_filter_active" value="subscription_filter"><?= labels('active', 'Active') ?></div>
                            <div class='btn bg-emerald-danger tag text-emerald-danger mr-2 filters_table' id="subscription_filter_deactive" name="subscription_filter_deactivate" value="subscription_filter"><?= labels('deactive', 'Deactive') ?></div>
                            <div class="col-md-4 col-sm-2 mb-2">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="customSearch" placeholder="<?= labels('search_here', 'Search here!') ?>" aria-label="Search" aria-describedby="customSearchBtn">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="button">
                                            <i class="fa fa-search d-inline"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-secondary  ml-2 filter_button" id="filterButton">
                                <span class="material-symbols-outlined mt-1">
                                    filter_alt
                                </span>
                            </button>
                            <div class="dropdown d-inline ml-2">
                                <button class="btn export_download dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <?= labels('download', 'Download') ?>
                                </button>
                                <div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 28px, 0px); top: 0px; left: 0px; will-change: transform;">
                                    <a class="dropdown-item" onclick="custome_export('pdf','Subscription list','subscription_list');"> <?= labels('pdf', 'PDF') ?></a>
                                    <a class="dropdown-item" onclick="custome_export('excel','Subscription list','subscription_list');"> <?= labels('excel', 'Excel') ?></a>
                                    <a class="dropdown-item" onclick="custome_export('csv','Subscription list','subscription_list')"> <?= labels('csv', 'CSV') ?></a>
                                </div>
                            </div>
                            <div class="col col d-flex justify-content-end mt-1">
                                <div class="mr-2 " id="myCol">
                                    <button type="button" class="btn btn-primary " style="height:40px;white-space:nowrap;" data-toggle="modal" data-target="#myModal">
                                        <?= labels('set_cron_job', 'Set Cron Job') ?>
                                    </button>
                                </div>
                                <?php if ($permissions['create']['subscription'] == 1) { ?>
                                    <div class="text-center">
                                        <a href="<?= base_url('admin/subscription/add_subscription'); ?>" class="btn btn-primary" style="height: 39px;font-size:14px;white-space:nowrap;">
                                            <i class="fa fa-plus-circle mr-1 mt-2"></i> <?= labels('add_subscription', 'Add Subscription') ?>
                                        </a>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <table class="table " data-fixed-columns="true" id="subscription_list" data-pagination-successively-size="2" data-detail-formatter="user_formater" data-auto-refresh="true" data-toggle="table" data-url="<?= base_url("admin/subscription/list") ?>" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-search="false" data-show-columns="false" data-show-columns-search="true" data-show-refresh="false" data-sort-name="id" data-sort-order="desc" data-toolbar="#toolbar" data-query-params="subscription_query_params">
                            <thead>
                                <tr>
                                    <th data-field="id" data-visible="false" class="text-center" data-sortable="true"><?= labels('id', 'ID') ?></th>
                                    <th data-field="name" class="text-center" data-sortable="true"><?= labels('name', 'Name') ?></th>
                                    <th data-field="description" class="text-center" data-sortable="true"><?= labels('description', 'Description') ?></th>
                                    <th data-field="duration" class="text-center"><?= labels('duration', 'Duration') ?> (Days)</th>
                                    <th data-field="price" class="text-center"><?= labels('price', 'Price') ?></th>
                                    <th data-field="discount_price" class="text-center"><?= labels('discount_price', 'Discount price') ?></th>
                                    <th data-field="order_type" class="text-center"><?= labels('order_type', 'Order Type') ?></th>
                                    <th data-field="max_order_limit" data-visible="false" class="text-center"><?= labels('max_order_limit', 'Max Order Limit') ?></th>
                                    <!-- <th data-field="service_type" class="text-center"><?= labels('service_type', 'Service Type') ?></th> -->
                                    <!-- <th data-field="max_service_limit" data-visible="false" class="text-center"><?= labels('max_service_limit', 'Max Service Limit') ?></th> -->
                                    <th data-field="tax_type" data-visible="false" class="text-center"><?= labels('tax_type', 'Tax Type') ?></th>
                                    <th data-field="tax_id" data-visible="false" class="text-center"><?= labels('tax_id', 'Tax ID') ?></th>
                                    <th data-field="is_commision_badge" class="text-center"><?= labels('commission', 'Commission') ?></th>
                                    <th data-field="commission_threshold" data-visible="false" class="text-center"><?= labels('commission_threshold', 'Commission Threshold') ?></th>
                                    <th data-field="commission_percentage" data-visible="false" class="text-center"><?= labels('commission_percentage', 'Commission Percentage') ?></th>
                                    <th data-field="publish_badge" class="text-center"><?= labels('publish', 'Publish') ?></th>
                                    <th data-field="status_badge" class="text-center"><?= labels('status', 'Status') ?></th>
                                    <th data-field="operations" class="text-center" data-events="subscription_events_admin"><?= labels('operation', 'Operation') ?></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            <?php } ?>
        </div>
    </section>
    <div class="modal fade" id="myModal" role="dialog" aria-labelledby="view-video" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle"><?= labels('set_cron_job', 'Set Cron Job') ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md">
                            <div class="form-group col-12 ">
                                <label>Cron Job URL </label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="cron_job_url" readonly value="<?= base_url('update_subscription_status') ?>" />
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('cron_job_url')">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <b>
                                <?= labels('why_use_a_cron_job', 'Why Use a Cron Job') ?>
                                :
                                <?= labels('why_use_a_cron_job', 'Why Use a Cron Job') ?>
                                <?= labels('why_use_cron_job_desc', 'Imagine a task that needs to be done every day, like changing subscription statuses. A cron job is like a helpful robot that does this task automatically at the same time each day. This makes sure things are accurate and saves you time.
                                How to Set Up a Cron Job') ?>
                                :
                            </b>
                            <li>
                                <?= labels('step_1_cron_job', 'Step 1: Log into cPanel:Open your web browser and enter your cPanel URL. Log in using your credentials') ?>
                            </li>
                            <li>
                                <?= labels('step_2_cron_job', 'Step 2: Navigate to Cron Jobs: Search for “Cron Jobs” in the cPanel search bar, or scroll down to the “Advanced” section and find “Cron Jobs.” Click on it.') ?>
                            </li>
                            <li>
                                <?= labels('step_3_cron_job', 'Step 3: Choose Add New Cron Job:You’ll see a list of current cron jobs. Scroll down to the “Add New Cron Job” section.') ?>
                            </li>
                            <li>
                                <?= labels('step_4_cron_job', 'Step 4: Set the Timing:For our specific case, since we want to change the subscription status at midnight, select “Every day” and set the time to 12:00 AM (midnight).') ?>
                            </li>
                            <li>
                                <?= labels('step_5_cron_job', 'Step 5: Add the Command:In the “Command” field, enter the full URL of the script or file you want to run as the cron job. This is the URL of the task you want to automate.') ?>
                                <b>(Ex : <?= base_url('update_subscription_status') ?>)</b>
                            </li>
                            <li>
                                <?= labels('step_6_cron_job', 'Step 6: Save the Cron Job:Click the “Add New Cron Job” button to save your settings.') ?>
                            </li>
                            <li>
                                <?= labels('step_7_cron_job', 'Step 7: Confirm Cron Job:You’ll see a confirmation message that your cron job has been added. Double-check the details to make sure everything is correct.') ?>
                            </li>
                            <li>
                                <?= labels('step_8_cron_job', 'Step 8: Test the Cron Job:To make sure the cron job is working as expected, you can test it. You might need to wait until the scheduled time for the test.') ?>


                            </li>
                            <li>
                                <?= labels('step_9_cron_job', 'Step 9: Edit or Delete Cron Jobs:If you need to change or remove a cron job, you can do so from the same “Cron Jobs” section in cPanel.') ?>


                            </li>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
<div id="filterBackdrop"></div>
<div class="drawer" id="filterDrawer">
    <section class="section">
        <div class="row">
            <div class="col-md-12">
                <div class="bg-new-primary" style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center;">
                        <div class="bg-white m-3 text-new-primary" style="box-shadow: 0px 8px 26px #00b9f02e; display: inline-block; padding: 10px; height: 45px; width: 45px; border-radius: 15px;">
                            <span class="material-symbols-outlined">
                                filter_alt
                            </span>
                        </div>
                        <h3 class="mb-0" style="display: inline-block; font-size: 16px; margin-left: 10px;"> <?= labels('filters', 'Filters') ?></h3>
                    </div>
                    <div id="cancelButton" style="cursor: pointer;">
                        <span class="material-symbols-outlined mr-2">
                            cancel
                        </span>
                    </div>
                </div>
                <div class="row mt-4 mx-2">
                    <div class="col-md-12">
                        <div class="form-group ">
                            <label for="table_filters"><?= labels('table_filters', 'Table filters') ?></label>
                            <div id="columnToggleContainer">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<script>
    $(document).ready(function() {
        // Function to update justify-content class based on window width
        function updateJustifyContent() {
            var $col = $("#myCol");
            if ($(window).width() < 768) { // col-sm-12 applies for screens < 768px wide
                $col.removeClass("justify-content-start").addClass("justify-content-end");
                $col.removeClass("justify-content-start").addClass("mb-2");
                $col.removeClass("justify-content-start").addClass("mr-4");
            } else {
                $col.removeClass("justify-content-end").addClass("justify-content-start");
                $col.removeClass("mb-2")
                $col.removeClass("mr-4")
            }
        }
        // Call the function on page load
        updateJustifyContent();
        // Call the function whenever the window is resized
        $(window).resize(function() {
            updateJustifyContent();
        });
    });
    var subscription_filter = "";
    $("#subscription_filter_all").on("click", function() {
        subscription_filter = "";
        $("#subscription_list").bootstrapTable("refresh");
    });
    $("#subscription_filter_active").on("click", function() {
        subscription_filter = "1";
        $("#subscription_list").bootstrapTable("refresh");
    });
    $("#subscription_filter_deactive").on("click", function() {
        subscription_filter = "0";
        $("#subscription_list").bootstrapTable("refresh");
    });
    $("#customSearch").on('keydown', function() {
        $('#subscription_list').bootstrapTable('refresh');
    });

    function subscription_query_params(p) {
        return {
            search: $("#customSearch").val() ? $("#customSearch").val() : p.search,
            limit: p.limit,
            sort: p.sort,
            order: p.order,
            offset: p.offset,
            subscription_filter: subscription_filter,
        };
    }
    $(document).ready(function() {
        for_drawer("#filterButton", "#filterDrawer", "#filterBackdrop", "#cancelButton");
        var dynamicColumns = fetchColumns('subscription_list');
        setupColumnToggle('subscription_list', dynamicColumns, 'columnToggleContainer');
    });

    function handleSwitchChange(checkbox) {
        var switchery = checkbox.nextElementSibling;
        if (checkbox.checked) {
            switchery.classList.add('active-content');
            switchery.classList.remove('deactive-content');
        } else {
            switchery.classList.add('deactive-content');
            switchery.classList.remove('active-content');
        }
    }
    var enable_payment_gateways = document.querySelector('#enable_payment_gateways');
    enable_payment_gateways.addEventListener('change', function() {
        handleSwitchChange(enable_payment_gateways);
    });
</script>