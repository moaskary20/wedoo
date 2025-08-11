<!-- Main Content -->
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
    <section class="section" id="pill-general_settings" role="tabpanel">
        <div class="section-header mt-2">
            <h1><?= labels('partner_details', 'Partner Details') ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item "><?= labels('partner_details', 'Partner Details') ?></div>
                <div class="breadcrumb-item "><?= labels('service_list', 'Service List') ?></div>
                <div class="breadcrumb-item "><?= $partner['rows'][0]['company_name'] ?></div>
            </div>
        </div>
        <?php include "provider_details.php"; ?>
        <div class="section-body">
            <div id="output-status"></div>
            <div class="row mt-3">
                <div class="col-md-12 col-sm-12 col-xl-12   ">
                    <div class="container-fluid card h-100">
                        <div class="">
                            <div class="row mt-4 mb-3">
                                <div class='btn bg-emerald-blue tag text-emerald-blue mr-2 ml-3 mb-2 filters_table' id="service_filter" name="service_filter_all" value=""><?= labels('all', 'All') ?></div>
                                <div class='btn bg-emerald-success tag text-emerald-success mr-2 filters_table' id="service_filter_active" name="service_filter_active" value="service_filter"><?= labels('active', 'Active') ?></div>
                                <div class='btn bg-emerald-danger tag text-emerald-danger mr-2 filters_table' id="service_filter_deactive" name="service_filter_deactive" value="service_filter"><?= labels('deactive', 'Deactive') ?></div>
                                <div class="col-md-4 col-sm-2 mb-2">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="customSearch" placeholder="Search here!" aria-label="Search" aria-describedby="customSearchBtn">
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
                                        Download
                                    </button>
                                    <div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 28px, 0px); top: 0px; left: 0px; will-change: transform;">
                                        <a class="dropdown-item" onclick="custome_export('pdf','service list','cash_collection');"><?= labels('pdf', 'PDF') ?></a>
                                        <a class="dropdown-item" onclick="custome_export('excel','service list','cash_collection');"><?= labels('excel', 'Excel') ?></a>
                                        <a class="dropdown-item" onclick="custome_export('csv','service list','cash_collection')"><?= labels('csv', 'CSV') ?></a>
                                    </div>
                                </div>
                                <div class="col col d-flex justify-content-end">
                                    <?php if ($permissions['create']['services'] == 1) { ?>
                                        <div class="text-center">
                                            <a href="<?= base_url("admin/services/add_service"); ?>" class="btn btn-primary" style="height: 39px;font-size:14px">
                                                <i class="fa fa-plus-circle mr-1 mt-2"></i><?= labels('add_service', 'Add Service') ?>
                                            </a>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover" id="cash_collection" data-show-export="true" data-export-types="['txt','excel','csv']" 
                                data-export-options='{"fileName": "invoice-order-list","ignoreColumn": ["action"]}' . data-auto-refresh="true"
                                 data-show-columns="false" data-search="false" data-show-refresh="false" data-toggle="table" 
                                 data-page-list="[5, 10, 25, 50, 100, 200, All]" data-side-pagination="server" data-pagination="true" data-url="<?= base_url("admin/partners/service_details/" . $partner['rows'][0]['partner_id']) ?>" data-sort-name="id" data-sort-order="desc" data-pagination-successively-size="2" data-query-params="service_list_query_params2">
                                    <thead>
                                        <tr>
                                            <th data-field="id" class="text-center" data-sortable="true"><?= labels('id', 'ID') ?></th>
                                            <th data-field="image_of_the_service" class="text-center"><?= labels('image ', 'Image') ?></th>
                                            <th data-field="title" class="text-center"><?= labels('title', 'Title') ?></th>
                                            <th data-field="tags" class="text-center" data-visible="false"><?= labels('tags ', 'Tags') ?></th>
                                            <th data-field="price" class="text-center" data-sortable="true"><?= labels('price ', 'Price') ?></th>
                                            <th data-field="discounted_price" class="text-center" data-sortable="true"><?= labels('discounted_price ', 'Discounted price') ?></th>
                                            <th data-field="rating" class="text-center" data-sortable="true"><?= labels('rating ', 'Rating') ?></th>
                                            <th data-field="status_badge" class="text-center"><?= labels('status ', 'Status') ?></th>
                                            <th data-field="category_id" class="text-center" data-sortable="true" data-visible="false"><?= labels('category_id', 'Category ID') ?></th>
                                            <th data-field="tax_type" class="text-center" data-sortable="true" data-visible="false"><?= labels('taxe_type', 'Tax Type') ?></th>
                                            <th data-field="number_of_members_required" class="text-center" data-sortable="true" data-visible="false"><?= labels('members_required ', 'Members required') ?></th>
                                            <th data-field="duration" class="text-center" data-sortable="true" data-visible="false"><?= labels('duration ', 'Duration') ?></th>
                                            <th data-field="number_of_ratings" class="text-center" data-sortable="true" data-visible="false"><?= labels('numbers_of_rating ', 'Numbers of Rating') ?></th>
                                            <th data-field="max_quantity_allowed" class="text-center" data-sortable="true" data-visible="false"><?= labels('max_quantity_allowed ', 'Max Quantity Allowed') ?></th>
                                            <th data-field="is_pay_later_allowed_badge" class="text-center"><?= labels('pay_later_allowed ', 'Pay Later Allowed') ?></th>
                                            <th data-field="cancelable_badge" class="text-center"><?= labels('is_cancelable ', 'is Cancelable') ?></th>
                                            <th data-field="created_at" class="text-center" data-sortable="true" data-visible="false"><?= labels('created_at', 'Created At') ?></th>
                                            <th data-field="operations" class="text-center" data-events="services_events_admin"><?= labels('operations', 'Operations') ?></th>
                                        </tr>
                                    </thead>
                                </table>
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
                        <h3 class="mb-0" style="display: inline-block; font-size: 16px; margin-left: 10px;"><?= labels('filters', 'Filters') ?></h3>
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
</section>
</div>
<script>
    $(document).ready(function() {
        $(document).ready(function() {
            for_drawer("#filterButton", "#filterDrawer", "#filterBackdrop", "#cancelButton");
            var columns = [{
                    field: 'id',
                    label: '<?= labels('id', 'ID') ?>',
                    visible: false
                },
                {
                    field: 'image_of_the_service',
                    label: '<?= labels('image ', 'Image') ?>'
                },
                {
                    field: 'title',
                    label: '<?= labels('title', 'Title') ?>'
                },
                {
                    field: 'tags',
                    label: '<?= labels('tags ', 'Tags') ?>',
                    visible: false
                },
                {
                    field: 'price',
                    label: '<?= labels('price ', 'Price') ?>',
                },
                {
                    field: 'discounted_price',
                    label: '<?= labels('discounted_price ', 'Discounted price') ?>',
                },
                {
                    field: 'rating',
                    label: '<?= labels('rating ', 'Rating') ?>',
                    visible: false,
                },
                {
                    field: 'status_badge',
                    label: '<?= labels('status ', 'Status') ?>',
                },
                {
                    field: 'category_id',
                    label: '<?= labels('category_id', 'Category ID') ?>',
                    visible: false
                },
                {
                    field: 'tax_type',
                    label: '<?= labels('taxe_type', 'Tax Type') ?>',
                    visible: false
                },
                {
                    field: 'number_of_members_required',
                    label: '<?= labels('members_required ', 'Members required') ?>',
                    visible: false
                },
                {
                    field: 'duration',
                    label: '<?= labels('duration ', 'Duration') ?>'
                },
                {
                    field: 'max_quantity_allowed',
                    label: '<?= labels('max_quantity_allowed ', 'Max Quantity Allowed') ?>',
                    visible: false
                },
                {
                    field: 'is_pay_later_allowed_badge',
                    label: '<?= labels('pay_later_allowed ', 'Pay Later Allowed') ?>'
                },
                {
                    field: 'cancelable_badge',
                    label: '<?= labels('is_cancelable ', 'is Cancelable') ?>'
                },
                {
                    field: 'created_at',
                    label: '<?= labels('created_at', 'Created At') ?>',
                    visible: false
                },
                {
                    field: 'operations',
                    label: '<?= labels('operations', 'Operations') ?>'
                }
            ];
            setupColumnToggle('cash_collection', columns, 'columnToggleContainer');
        });
    });
    var service_filter = "";
    $("#service_filter_all").on("click", function() {
        service_filter = "";
        $("#cash_collection").bootstrapTable("refresh");
    });
    $("#service_filter_active").on("click", function() {
        service_filter = "1";
        $("#cash_collection").bootstrapTable("refresh");
    });
    $("#service_filter_deactive").on("click", function() {
        service_filter = "0";
        $("#cash_collection").bootstrapTable("refresh");
    });
    $("#service_filter").on("click", function(e) {
        $("#cash_collection").bootstrapTable("refresh");
    });
    $("#customSearch").on("keydown", function() {
        $("#cash_collection").bootstrapTable("refresh");
    });
    function service_list_query_params2(p) {
        return {
            search: $("#customSearch").val() ? $("#customSearch").val() : p.search,
            limit: p.limit,
            sort: p.sort,
            order: p.order,
            offset: p.offset,
            service_filter: service_filter,
        };
    }
</script>