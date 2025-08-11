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
                <div class="breadcrumb-item "><?= labels('promocode_list', 'Promo Code List') ?></div>
                <div class="breadcrumb-item "><?= $partner['rows'][0]['company_name'] ?></div>
            </div>
        </div>
        <?php include "provider_details.php"; ?>
        <div class="section-body">
            <div id="output-status"></div>
            <div class="row mt-3">
                <div class="col-md-12 col-sm-12 col-xl-12   ">
                    <div class=" container-fluid card h-100">
                        <div class="">
                            <div class="row mt-4 mb-3">
                                <div class='btn bg-emerald-blue tag text-emerald-blue mr-2 ml-3 mb-2 filters_table' id="promocode_filter_all" name="promocode_filter" value="promocode_filter"><?= labels('all', 'All') ?></div>
                                <div class='btn bg-emerald-success tag text-emerald-success mr-2 filters_table' id="promocode_filter_active" name="promocode_filter_active" value="promocode_filter"><?= labels('active', 'Active') ?></div>
                                <div class='btn bg-emerald-danger tag text-emerald-danger mr-2 filters_table' id="promocode_filter_deactive" name="promocode_filter_deactive" value="promocode_filter"><?= labels('deactive', 'Deactive') ?></div>
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
                                        <?= labels('download', 'Download') ?>
                                    </button>
                                    <div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 28px, 0px); top: 0px; left: 0px; will-change: transform;">
                                        <a class="dropdown-item" onclick="custome_export('pdf','Promo code list','promocode_table');"> <?= labels('pdf', 'PDF') ?></a>
                                        <a class="dropdown-item" onclick="custome_export('excel','Promo code list','promocode_table');"> <?= labels('excel', 'Excel') ?></a>
                                        <a class="dropdown-item" onclick="custome_export('csv','Promo code list','promocode_table')"> <?= labels('csv', 'CSV') ?></a>
                                    </div>
                                </div>
                                <div class="col col d-flex justify-content-end">
                                    <?php if ($permissions['create']['promo_code'] == 1) { ?>
                                        <div class="text-center">
                                            <a href="<?= base_url('admin/promo_codes/add'); ?>" class="btn btn-primary" style="height: 39px;font-size:14px">
                                                <i class="fa fa-plus-circle mr-1 mt-2"></i><?= labels('add_promocodes', 'Add Promo Codes') ?>
                                            </a>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" data-detail-formatter="detailFormatter" id="promocode_table" data-auto-refresh="true" data-show-columns="false" data-pagination-successively-size="2" data-query-params="promocode_query_params" data-show-toggle="false" data-show-refresh="false" data-toggle="table" data-search-highlight="true" data-server-sort="true" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-url="<?= base_url("admin/partners/partner_promocode_details_list/" . $partner['rows'][0]['partner_id']) ?>" data-side-pagination="server" data-pagination="true" data-search="false" data-sort-name="id" data-sort-order="DESC">
                                    <thead>
                                        <tr>
                                            <th data-field="id" class="text-center" data-sortable="true"><?= labels('id', 'ID') ?></th>
                                            <th data-field="image" class="text-center"><?= labels('image', 'Image') ?></th>
                                            <th data-field="partner_id" class="text-center" data-visible="false" data-sortable="true"><?= labels('provider_id', 'Provider Id') ?></th>
                                            <th data-field="partner_name" class="text-center" data-search-highlight-formatter="customSearchFormatter" data-sortable="true"><?= labels('provider_name', 'Provider name') ?></th>
                                            <th data-field="promo_code" class="text-center" data-sortable="true"><?= labels('promo_code', 'Promo Code') ?></th>
                                            <th data-field="message" data-search-highlight-formatter="customSearchFormatter" class="text-center" data-visible="false"><?= labels('message', 'Message') ?></th>
                                            <th data-field="start_date" data-search-highlight-formatter="customSearchFormatter" class="text-center"><?= labels('start_date', 'Start Date') ?></th>
                                            <th data-field="end_date" data-search-highlight-formatter="customSearchFormatter" class="text-center"><?= labels('end_date', 'End Date') ?></th>
                                            <th data-field="no_of_users" class="text-center" data-visible="false" data-sortable="true"><?= labels('no_of_users', 'No. of Users') ?></th>
                                            <th data-field="minimum_order_amount" data-search-highlight-formatter="customSearchFormatter" class="text-center" data-visible="false" data-sortable="true"><?= labels('minimum_order_amount', 'Minimum Order Amount') ?></th>
                                            <th data-field="max_discount_amount" data-search-highlight-formatter="customSearchFormatter" class="text-center" data-visible="false" data-sortable="true"><?= labels('max_discount_amount', 'Max Discount Amount') ?></th>
                                            <th data-field="discount" class="text-center" data-search-highlight-formatter="customSearchFormatter" data-visible="false"><?= labels('discount', 'Discount') ?></th>
                                            <th data-field="discount_type" class="text-center" data-search-highlight-formatter="customSearchFormatter" data-visible="false"><?= labels('discount_type', 'Discount Type') ?></th>
                                            <th data-field="repeat_usage" class="text-center" data-search-highlight-formatter="customSearchFormatter" data-visible="false"><?= labels('repeat_usage', 'Repeat Usage') ?></th>
                                            <th data-field="no_of_repeat_usage" class="text-center" data-search-highlight-formatter="customSearchFormatter" data-visible="false"><?= labels('no_of_repeat_usage', 'No. of Repeat Usage') ?></th>
                                            <th data-field="status_badge" class="text-center" data-search-highlight-formatter="customSearchFormatter" data-sortable="true"><?= labels('status', 'Status') ?></th>
                                            <th data-field="operations" class="text-center" data-events="promo_codes_events"><?= labels('operations', 'Operations') ?></th>
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
</section>
</div>
<div class="modal fade" id="update_modal" tabindex="-1" aria-labelledby="update_modal_thing" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"><?= labels('update_promo_code', 'Update Promo Code') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?= form_open('admin/promo_codes/update', ['method' => "post", 'class' => 'form-submit-event', 'id' => 'promo_code_form', 'enctype' => "multipart/form-data"]); ?>
                <input type="hidden" name="promo_id" id="id">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="promo_code"><?= labels('promocode', 'Promocode') ?></label>
                            <input type="text" class="form-control" id="promo_code" name="promo_code">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="jquery-script-clear"></div>
                        <div class="categories" id="categories">
                            <label for="partner"><?= labels('select_provider', 'Select Provider') ?></label> <br>
                            <select id="partner" class="form-control w-100" name="partner">
                                <option value=""><?= labels('select_provider', 'Select Provider') ?></option>
                                <?php foreach ($partner_name as $pn) : ?>
                                    <option value="<?= $pn['id'] ?>"><?= $pn['company_name'] . ' - ' . $pn['username'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="start_date"><?= labels('start_date', 'Start Date') ?></label>
                            <input type="text" class="form-control datepicker" id="start_date" name="start_date">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="end_date">End Date</label>
                            <input type="text" class="form-control datepicker" id="end_date" name="end_date">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="no_of_users"><?= labels('no_of_users', 'No. of users') ?></label>
                            <input type="number" class="form-control" id="no_of_users" name="no_of_users" min="0" oninput="this.value = 
 !!this.value && Math.abs(this.value) >= 0 ? Math.abs(this.value) : null">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="minimum_order_amount"><?= labels('minimum_order_amount', 'Minimum order amount') ?></label>
                            <input type="number" class="form-control" id="minimum_order_amount" name="minimum_order_amount" min="0" oninput="this.value = 
 !!this.value && Math.abs(this.value) >= 0 ? Math.abs(this.value) : null">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="discount"><?= labels('discount', 'Discount') ?></label>
                            <input type="number" class="form-control" id="discount" name="discount" min="0" oninput="this.value = 
 !!this.value && Math.abs(this.value) >= 0 ? Math.abs(this.value) : null">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="discount_type"><?= labels('discount_type', 'Discount Type') ?></label>
                            <select name="discount_type" id="discount_type" class="form-control">
                                <option value="amount"><?= labels('amount', 'Amount') ?></option>
                                <option value="percentage"><?= labels('percentage', 'Percentage') ?></option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="message"><?= labels('message', 'Message') ?></label>
                            <textarea style="min-height:60px" class="form-control" name="message" id="message" cols="50" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="max_discount_amount"><?= labels('max_discount_amount', 'Max Discount Amount') ?></label>
                            <input type="number" class="form-control" id="max_discount_amount" name="max_discount_amount" min="0" oninput="this.value = 
 !!this.value && Math.abs(this.value) >= 0 ? Math.abs(this.value) : null">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="custom-switch mt-2">
                            <input type="checkbox" id="repeat_usage" name="repeat_usage" class="custom-switch-input">
                            <span class="custom-switch-indicator"></span>
                            <span class="custom-switch-description"><?= labels('repeat_usage', 'Repeat Usage ?') ?></span>
                        </label>
                    </div>
                    <div class="col-md-6 repeat_usage">
                        <div class="form-group">
                            <label for="no_of_repeat_usage"><?= labels('no_of_repeat_usage', 'No. of repeat usage') ?></label>
                            <input type="number" class="form-control" id="no_of_repeat_usage" name="no_of_repeat_usage" min="0" oninput="this.value = 
 !!this.value && Math.abs(this.value) >= 0 ? Math.abs(this.value) : null">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="image"><?= labels('image', 'Image') ?></label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <div id="image_edit">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="custom-switch mt-2">
                        <input type="checkbox" name="status" class="custom-switch-input">
                        <span class="custom-switch-indicator"></span>
                        <span class="custom-switch-description"><?= labels('status', 'Status') ?></span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><?= labels('save_changes', 'Save changes') ?></button>
                <?php form_close() ?>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= labels('close', 'Close') ?></button>
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
<script>
    $(document).ready(function() {
        if ($(".datepicker").length) {
            var startDatePicker = $('#start_date');
            var endDatePicker = $('#end_date');
            startDatePicker.daterangepicker({
                locale: {
                    format: 'YYYY-MM-DD'
                },
                singleDatePicker: true,
                autoUpdateInput: false 
            });
            endDatePicker.daterangepicker({
                locale: {
                    format: 'YYYY-MM-DD'
                },
                singleDatePicker: true,
                autoUpdateInput: false 
            });
            startDatePicker.on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD'));
                endDatePicker.data('daterangepicker').setMinDate(picker.startDate);
            });
            endDatePicker.on('change', function() {
                if (startDatePicker.val() !== '' && endDatePicker.val() !== '') {
                    var startDate = moment(startDatePicker.val(), 'YYYY-MM-DD');
                    var endDate = moment(endDatePicker.val(), 'YYYY-MM-DD');
                    if (endDate.isBefore(startDate)) {
                        alert('End date must be greater than or equal to the start date.');
                        $(this).val('');
                    }
                }
            });
        }
    });
</script>
<script>
    var promocode_filter = "";
    $("#promocode_filter_all").on("click", function() {
        promocode_filter = "";
        $("#promocode_table").bootstrapTable("refresh");
    });
    $("#promocode_filter_active").on("click", function() {
        promocode_filter = "1";
        $("#promocode_table").bootstrapTable("refresh");
    });
    $("#promocode_filter_deactive").on("click", function() {
        promocode_filter = "0";
        $("#promocode_table").bootstrapTable("refresh");
    });
    $("#customSearch").on('keydown', function() {
        $('#promocode_table').bootstrapTable('refresh');
    });
    function promocode_query_params(p) {
        return {
            search: $("#customSearch").val() ? $("#customSearch").val() : p.search,
            limit: p.limit,
            sort: p.sort,
            order: p.order,
            offset: p.offset,
            promocode_filter: promocode_filter,
        };
    }
</script>
<script>
    $(document).ready(function() {
        for_drawer("#filterButton", "#filterDrawer", "#filterBackdrop", "#cancelButton");
        var columns = [{
                field: 'id',
                label: '<?= labels('id', 'ID') ?>',
                visible: false
            },
            {
                field: 'partner_name',
                label: '<?= labels('provider_name', 'Provider name') ?>',
            },
            {
                field: 'start_date',
                label: '<?= labels('start_date', 'Start Date') ?>',
            },
            {
                field: 'end_date',
                label: '<?= labels('end_date', 'End Date') ?>',
            },
            {
                field: 'no_of_users',
                label: '<?= labels('no_of_users', 'No. of Users') ?>',
                visible: false
            },
            {
                field: 'minimum_order_amount',
                label: '<?= labels('minimum_order_amount', 'Minimum Order Amount') ?>',
                visible: false
            },
            {
                field: 'max_discount_amount',
                label: '<?= labels('max_discount_amount', 'Max Discount Amount') ?>',
                visible: false
            },
            {
                field: 'discount',
                label: '<?= labels('discount', 'Discount') ?>',
                visible: false
            },
            {
                field: 'discount_type',
                label: '<?= labels('discount_type', 'Discount Type') ?>',
                visible: false
            },
            {
                field: 'repeat_usage',
                label: '<?= labels('repeat_usage', 'Repeat Usage') ?>',
                visible: false
            },
            {
                field: 'no_of_repeat_usage',
                label: '<?= labels('no_of_repeat_usage', 'No. of Repeat Usage') ?>',
                visible: false
            },
            {
                field: 'status_badge',
                label: '<?= labels('status', 'Status') ?>',
            },
            {
                field: 'operations',
                label: '<?= labels('operations', 'Operations') ?>',
            },
        ];
        setupColumnToggle('promocode_table', columns, 'columnToggleContainer');
    });
</script>