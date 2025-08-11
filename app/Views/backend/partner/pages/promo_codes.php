<!-- Main Content -->
<?= helper('form'); ?>
<div class="main-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('promocode', 'Promo codes') ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/partner/dashboard') ?>"> <i class="fas fa-home-alt text-primary"></i><?= labels('Dashboard', 'Dashboard') ?></a></div>
            </div>
        </div>
        <div class="container-fluid card">
            <div class="row">
                <div class="col-md-12">
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
                                <a class="dropdown-item" onclick="custome_export('pdf','Promo code list','promocode_table');"><?= labels('pdf', 'PDF') ?></a>
                                <a class="dropdown-item" onclick="custome_export('excel','Promo code list','promocode_table');"><?= labels('excel', 'Excel') ?></a>
                                <a class="dropdown-item" onclick="custome_export('csv','Promo code list','promocode_table')"><?= labels('csv', 'CSV') ?></a>
                            </div>
                        </div>
                        <div class="col col d-flex justify-content-end">
                            <div class="text-center">
                                <a class="btn btn-primary text-white" id="add_promo" href="<?= base_url('partner/promo_codes/add'); ?>"><i class="fas fa-plus"></i> <?= labels('add_promocodes', 'Add Promocodes') ?></a>
                            </div>
                        </div>
                    </div>
                    <table class="table table-bordered table-hover" data-fixed-columns="true"  data-pagination-successively-size="2" data-detail-formatter="detailFormatter" id="promocode_table" data-auto-refresh="true" data-show-columns="false" data-show-toggle="false" data-show-refresh="false" data-toggle="table" data-search-highlight="true" data-server-sort="true" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-url="<?= base_url("partner/promo_codes/list") ?>" data-side-pagination="server" data-pagination="true" data-search="false" data-sort-name="id" data-sort-order="DESC" data-query-params="promocode_query_params">
                        <thead>
                            <tr>
                                <th data-field="id" class="text-center" data-sortable="true"><?= labels('id', 'ID') ?></th>
                                <th data-field="image" class="text-center" data-sortable="true"><?= labels('image', 'Image') ?></th>
                                <th data-field="promo_code" class="text-center" data-sortable="true"><?= labels('promo_code', 'Promo code') ?></th>
                                <th data-field="message" class="text-center" data-visible="false"><?= labels('message', 'Message') ?></th>
                                <th data-field="start_date" class="text-center"><?= labels('start_date', 'Start date') ?></th>
                                <th data-field="end_date" class="text-center"><?= labels('end_date', 'end_date') ?></th>
                                <th data-field="no_of_users" class="text-center" data-visible="false" data-sortable="true"><?= labels('	no_of_users', '	no_of_users') ?></th>
                                <th data-field="minimum_order_amount" class="text-center" data-visible="false" data-sortable="true"><?= labels('minimum_order_amount', 'minimum_order_amount') ?></th>
                                <th data-field="discount" class="text-center" data-visible="false"><?= labels('discount', 'discount') ?></th>
                                <th data-field="discount_type" class="text-center" data-visible="false"><?= labels('discount_type', 'discount_type') ?></th>
                                <th data-field="max_discount_amount" class="text-center" data-visible="false" data-sortable="true"><?= labels('max_discount_amount	', 'max_discount_amount	') ?></th>
                                <th data-field="repeat_usage_badge" class="text-center"><?= labels('repeat_usage', 'repeat_usage') ?></th>
                                <th data-field="no_of_repeat_usage" class="text-center" data-visible="false"><?= labels('no_of_repeat_usage', 'no_of_repeat_usage') ?></th>
                                <th data-field="status_badge" class="text-center"><?= labels('status', 'status') ?></th>
                                <th data-field="operations" class="text-center" data-events="promo_codes_events"><?= labels('operations', 'Operations') ?></th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
<div class="modal fade" id="update_modal" tabindex="-1" aria-labelledby="update_modal_thing" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
        <form method="post" action="<?= base_url('partner/promo_codes/save') ?>" id="promo_code_form" class="form-submit-event">
            <div class="modal-header m-0 p-0" style="border-bottom: solid 1px #e5e6e9;">
                <div class="row pl-3">
                    <div class="col ">
                        <div class="toggleButttonPostition"> <?= labels('update_promo_code', 'Update Promo Code') ?></div>
                    </div>
                </div>
                <div class="col d-flex justify-content-end mr-3 mt-4">
                    <input type="checkbox" id="promocode_status" name="status" class="status-switch editInModel">
                </div>
            </div>
            <div class="modal-body">
                <input type="hidden" name="promo_id" id="id">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="promo_code" class="required"><?= labels('promocode', 'Promocode') ?></label>
                            <input type="text" class="form-control" id="promo_code" name="promo_code">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="start_date" class="required"><?= labels('start_date', 'Start Date') ?></label>
                            <input type="text" class="form-control datepicker" id="start_date" name="start_date">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="end_date" class="required"><?= labels('end_date', 'End Date') ?></label>
                            <input type="text" class="form-control datepicker" id="end_date" name="end_date">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="no_of_users" class="required"><?= labels('no_of_users', 'No. of users') ?></label>
                            <i data-content=" <?= labels('data_content_for_no_of_user', "Only the first X number of users can apply it. For example, if you have allowed 10, then the first 10 users can use this promo code.") ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                            <input type="number" class="form-control" id="no_of_users" name="no_of_users" min="0" oninput="this.value = !!this.value && Math.abs(this.value) >= 0 ? Math.abs(this.value) : null">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="minimum_order_amount" class="required"><?= labels('minimum_order_amount', 'Minimum order amount') ?></label>
                            <i data-content=" <?= labels('data_content_for_minimum_booking_amount', "Customers can apply a promo code if the subtotal of their service is higher than the Minimum Booking amount.") ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                            <input type="number" class="form-control" id="minimum_order_amount" name="minimum_order_amount" min="0" oninput="this.value = !!this.value && Math.abs(this.value) >= 0 ? Math.abs(this.value) : null">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="discount" class="required"><?= labels('discount', 'Discount') ?></label>
                            <input type="number" class="form-control" id="discount" name="discount" min="0" oninput="this.value = !!this.value && Math.abs(this.value) >= 0 ? Math.abs(this.value) : null">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="discount_type" class="required"><?= labels('discount_type', 'Discount Type') ?></label>
                            <i data-content=" <?= labels('data_content_for_max_discount_amount', "You want to offer a discount based on a percentage or a fixed amount of the total cost of the services.") ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
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
                            <label for="message" class=""><?= labels('message', 'Message') ?></label>
                            <textarea class="form-control" name="message" id="message" cols="50" rows="2" style="min-height:60px"></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="max_discount_amount" class="required"><?= labels('max_discount_amount', 'Max Discount Amount') ?></label>
                            <i data-content=" <?= labels('data_content_for_discount_type', "This promo code gives customers a maximum discount of X amount.") ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                            <input type="number" class="form-control" id="max_discount_amount" name="max_discount_amount" min="0" oninput="this.value = !!this.value && Math.abs(this.value) >= 0 ? Math.abs(this.value) : null">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="custom-switch mt-2" class="required">
                            <span class="custom-switch-description"><?= labels('repeat_usage', 'Repeat Usage ?') ?></span>
                            <i data-content=" <?= labels('data_content_for_repeat_usage', "If it's allowed, customers can use this promo code many times.") ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                            <input type="checkbox" id="repeat_usage" name="repeat_usage" class="status-switch editRepeatUsageInModel">
                        </label>
                    </div>
                    <div class="col-md-6 repeat_usage">
                        <div class="form-group">
                            <label for="no_of_repeat_usage" class="required"><?= labels('no_of_repeat_usage', 'No. of repeat usage') ?></label>
                            <i data-content=" <?= labels('data_content_for_no_of_repeat_usage', "customers can use the promo code a certain number of times. For example, if you set it to 10, customers can use the promo code up to 10 times when booking the services, as long as the conditions are met.") ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                            <input type="number" class="form-control" id="no_of_repeat_usage" name="no_of_repeat_usage" min="0" oninput="this.value = !!this.value && Math.abs(this.value) >= 0 ? Math.abs(this.value) : null">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="image" class="required"><?= labels('image', 'Image') ?></label>
                            <input type="file" class="filepond" id="image" name="image" accept="image/*">
                            <div id="image_edit">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><?= labels('update_promo_code', 'Update Promo Code') ?></button>
                </form>
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
        for_drawer("#filterButton", "#filterDrawer", "#filterBackdrop", "#cancelButton");
        var columns = [{
                field: 'id',
                label: '<?= labels('id', 'ID') ?>',
                visible: false
            },
            {
                field: 'image',
                label: '<?= labels('image', 'Image') ?>',
            },
            {
                field: 'promo_code',
                label: '<?= labels('promo_code', 'Promo code') ?>',
            },
            {
                field: 'message',
                label: '<?= labels('message', 'Message') ?>',
            },
            {
                field: 'start_date',
                label: '<?= labels('start_date', 'Start date') ?>',
            },
            {
                field: 'end_date',
                label: '<?= labels('end_date', 'end_date') ?>',
            },
            {
                field: 'no_of_users',
                label: '<?= labels('	no_of_users', '	no_of_users') ?>',
                visible: false
            },
            {
                field: 'status_badge',
                label: '<?= labels('status', 'status') ?>',
            },
            {
                field: 'minimum_order_amount',
                label: '<?= labels('minimum_order_amount', 'minimum_order_amount') ?>',
                visible: false
            },
            {
                field: 'max_discount_amount',
                label: '<?= labels('max_discount_amount	', 'max_discount_amount	') ?>',
                visible: false
            },
            {
                field: 'discount',
                label: '<?= labels('discount', 'discount') ?>',
                visible: false
            },
            {
                field: 'discount_type',
                label: '<?= labels('discount_type', 'discount_type') ?>',
                visible: false
            },
            {
                field: 'repeat_usage_badge',
                label: '<?= labels('repeat_usage', 'repeat_usage') ?>',
                visible: false
            },
            {
                field: 'no_of_repeat_usage',
                label: '<?= labels('no_of_repeat_usage', 'no_of_repeat_usage') ?>',
                visible: false
            },
            {
                field: 'operations',
                label: '<?= labels('operations', 'Operations') ?>',
            },
        ];
        setupColumnToggle('promocode_table', columns, 'columnToggleContainer');
    });
</script>
<section class="script">
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
        var edit_promocode_switch = document.querySelector('#promocode_status');
        edit_promocode_switch.addEventListener('change', function() {
            handleSwitchChange(edit_promocode_switch);
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
        function handleRepeatSwitchChange(checkbox) {
            var switchery1 = checkbox.nextElementSibling;
            if (checkbox.checked) {
                switchery1.classList.add('allowed-content');
                switchery1.classList.remove('not_allowed-content');
            } else {
                switchery1.classList.add('not_allowed-content');
                switchery1.classList.remove('allowed-content');
            }
        }
        var repeat_usage = document.querySelector('#repeat_usage');
        repeat_usage.addEventListener('change', function() {
            handleRepeatSwitchChange(repeat_usage);
        });
    </script>
</section>
<script>
    $(function() {
        $('.fa').popover({
            trigger: "hover"
        });
    })
</script>