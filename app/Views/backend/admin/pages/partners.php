<?php
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
            <h1><?= labels('providers', "Providers") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item"><i class="fas fa-handshake text-warning"></i> <?= labels('providers', 'Provider') ?></a></div>
            </div>
        </div>
        <div class="container-fluid card">
            <?php if ($permissions['read']['partner'] == 1) { ?>
                <div class="row mt-4 mb-3">
                    <div class='btn bg-emerald-blue tag text-emerald-blue mr-2 ml-3 mb-2 filters_table' id="partner_filter_all" name="partner_filter" value="partner_filter"><?= labels('all', 'All') ?></div>
                    <div class='btn bg-emerald-success tag text-emerald-success mr-2 filters_table' id="partner_filter_active" name="partner_filter_active" value="partner_filter"><?= labels('approved', 'Approved') ?></div>
                    <div class='btn bg-emerald-danger tag text-emerald-danger mr-2 filters_table' id="partner_filter_deactivate" name="partner_filter_deactivate" value="partner_filter"><?= labels('disapproved', 'Disapproved') ?></div>
                    <div class="col-md-4 col-sm-2 mb-2">
                        <div class="input-group">
                            <input type="text" class="form-control" id="customSearch" placeholder="<?= labels('search_here', 'Search here!')?>" aria-label="Search" aria-describedby="customSearchBtn">
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
                            <a class="dropdown-item" onclick="custome_export('pdf', 'Partner list', 'partner_list', [ 'Status','Operations']);"><?= labels('pdf', 'PDF') ?></a>
                            <a class="dropdown-item" onclick="custome_export('excel','Partner list','partner_list',[ 'Status','Operations']);"><?= labels('excel', 'Excel') ?></a>
                            <a class="dropdown-item" onclick="custome_export('csv','Partner list','partner_list',[ 'Status','Operations'])"><?= labels('csv', 'CSV') ?></a>
                        </div>
                    </div>
                    <div class="col col d-flex justify-content-end">
                        <?php if ($permissions['create']['partner'] == 1) { ?>
                            <div class="text-center">
                                <a href="<?= base_url("admin/partners/add_partner"); ?>" class="btn btn-primary" style="height: 39px;font-size:14px">
                                    <i class="fa fa-plus-circle mr-1 mt-2"></i><?= labels('add_providers', 'Add Provider') ?>
                                </a>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-lg">
                        <table class="table" data-fixed-columns="true" id="partner_list" data-detail-formatter="user_formater" data-auto-refresh="true" data-toggle="table" data-url="<?= base_url("admin/partners/list") ?>" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-show-columns-search="true" data-sort-name="pd.id" data-sort-order="desc" data-toolbar="#toolbar" data-query-params="partner_list_query_params1" data-pagination-successively-size="1">
                            <thead>
                                <tr>
                                    <th data-field="partner_id" class="text-center" data-visible="false" data-sortable="false"><?= labels('provider_id', 'Provider Id') ?></th>
                                    <th data-field="partner_profile" name="partner_profile" class="text-center"><?= labels('profile', 'Provider Profile') ?></th>
                                    <th data-field="partner_name" class="text-center" data-visible="true" data-sortable="true"><?= labels('provider_name', 'Provider Name') ?></th>
                                    <th data-field="company_name" class="text-center"><?= labels('company_name', 'Company Name') ?></th>
                                    <th data-field="slug" class="text-center"><?= labels('slug', 'Slug') ?></th>
                                    <th data-field="mobile" class="text-center"><?= labels('mobile', 'Mobile') ?></th>
                                    <th data-field="balance" class="text-center" data-visible="false"><?= labels('balance', 'Balance') ?></th>
                                    <th data-field="address" class="text-center" data-visible="false"><?= labels('address', 'Address') ?></th>
                                    <th data-field="tax_name" class="text-center" data-visible="false"><?= labels('tax_name', 'Tax Name') ?></th>
                                    <th data-field="tax_number" class="text-center" data-visible="false"><?= labels('tax_number', 'Tax Number') ?></th>
                                    <th data-field="bank_name" class="text-center" data-visible="false"><?= labels('bank_name', 'Bank Name') ?></th>
                                    <th data-field="account_number" class="text-center" data-visible="false"><?= labels('account_number', 'Account Number') ?></th>
                                    <th data-field="account_name" class="text-center" data-visible="false"><?= labels('account_name', 'Account Name') ?></th>
                                    <th data-field="bank_code" class="text-center" data-visible="false"><?= labels('bank_code', 'Bank Code') ?></th>
                                    <th data-field="ratings" class="text-center" data-visible="true"><?= labels('stars', 'Stars') ?></th>
                                    <th data-field="swift_code" class="text-center" data-visible="false"><?= labels('swift_code', 'Swift Code') ?></th>
                                    <th data-field="advance_booking_days" class="text-center" data-visible="false"><?= labels('advance_booking_details', 'Advance Booking Details') ?></th>
                                    <th data-field="type" class="text-center" data-sortable="true"><?= labels('type', 'Type') ?></th>
                                    <th data-field="number_of_members" class="text-center" data-visible="false"><?= labels('number_Of_members', 'Number Of Members') ?></th>
                                    <th data-field="number_of_ratings" class="text-center" data-sortable="true" data-visible="false"><?= labels('number_Of_rating', 'Number Of Rating') ?></th>
                                    <th data-field="status" class="text-center"><?= labels('status', 'Status') ?></th>
                                    <th data-field="created_at" class="text-center" data-visible="false"><?= labels('created_at', 'Created At') ?></th>
                                    <th data-field="is_approved" class="text-center" data-events="partner_events"><?= labels('operations', 'Operation') ?></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            <?php } ?>
        </div>
        <!-- Filter Drawer Container -->
    </section>
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
</div>
<script>
    $(document).ready(function() {
        for_drawer("#filterButton", "#filterDrawer", "#filterBackdrop", "#cancelButton");
        // Get columns dynamically
        var dynamicColumns = fetchColumns('partner_list');
        setupColumnToggle('partner_list', dynamicColumns, 'columnToggleContainer');
    });
    $("#customSearch").on('keydown', function() {
        $('#partner_list').bootstrapTable('refresh');
    });
    var partner_filter = "";
    // partner list params
    function partner_list_query_params1(p) {
        return {
            search: $('#customSearch').val() ? $('#customSearch').val() : p.search,
            limit: p.limit,
            sort: p.sort,
            order: p.order,
            offset: p.offset,
            partner_filter: partner_filter,
        };
    }
</script>