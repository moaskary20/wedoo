<!-- Main Content -->
<div class="main-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('bookings', 'Bookings') ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('partner/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item"><?= labels('bookings', 'Bookings') ?></div>
            </div>
        </div>
        <div class="container-fluid card">
            <div class="row">
                <div class="col-md col-lg col-sm">
                    <div class="row mt-4 mb-3">
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
                                <a class="dropdown-item" onclick="custome_export('pdf','odrer list','user_list');"><?= labels('pdf', 'PDF') ?></a>
                                <a class="dropdown-item" onclick="custome_export('excel','odrer list','user_list');"><?= labels('excel', 'Excel') ?></a>
                                <a class="dropdown-item" onclick="custome_export('csv','odrer list','user_list')"><?= labels('csv', 'CSV') ?></a>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-borderd" data-fixed-columns="true" id="user_list"
                            data-pagination-successively-size="2" data-show-export="false" data-export-types="['txt','excel','csv']"
                            data-export-options='{"fileName": "invoice-order-list","ignoreColumn": ["action"]}' data-auto-refresh="true"
                            data-show-columns="false" data-search="false" data-show-refresh="false" data-toggle="table" data-page-list="[5, 10, 25, 50, 100, 200, All]"
                            data-side-pagination="server" data-pagination="true" data-url="<?= base_url("partner/orders/list") ?>" data-sort-name="id" data-sort-order="desc"
                            data-query-params="orders_query1">
                            <thead>
                                <tr>
                                    <th data-field="id" class="text-center" data-sortable="true"><?= labels('id', 'ID') ?></th>
                                    <th data-field="date_of_service" class="text-center"><?= labels('date_of_service', 'Date of Service') ?></th>
                                    <th data-field="invoice_no" class="text-center" data-visible="false" data-sortable="true"><?= labels('invoice_no', 'Invoice No') ?></th>
                                    <th data-field="user_id" class="text-center" data-visible="false" data-sortable="true"><?= labels('user_id', 'User id') ?></th>
                                    <th data-field="customer" class="text-center"><?= labels('customer', 'Customer') ?></th>
                                    <th data-field="final_total" class="text-center" data-visible="true"><?= labels('final_total', 'Final total') ?></th>
                                    <th data-field="partner" class="text-center" data-sortable="true" data-visible="false"><?= labels('provider', 'Provider') ?></th>
                                    <th data-field="city_id" class="text-center" data-visible="false" data-sortable="true"><?= labels('city_id', 'city_id') ?></th>
                                    <th data-field="total" class="text-center" data-visible="false"><?= labels('total', 'Total') ?></th>
                                    <th data-field="promo_code" class="text-center" data-visible="false"><?= labels('promo_code', 'Promo code') ?></th>
                                    <th data-field="promo_discount" class="text-center" data-visible="false"><?= labels('promo_discount', 'Promo discount') ?></th>
                                    <th data-field="admin_earnings" class="text-center" data-visible="false"><?= labels('admin_earning', 'Admin Earning') ?></th>
                                    <th data-field="partner_earnings" class="text-center" data-visible="false"><?= labels('provider_earning', 'Provider earnings') ?></th>
                                    <th data-field="address_id" class="text-center" data-visible="false"><?= labels('address_id', 'Address id') ?></th>
                                    <th data-field="address" class="text-center" data-visible="false"><?= labels('address', 'Address') ?></th>
                                    <th data-field="visiting_charges" class="text-center" data-visible="false"><?= labels('visiting_charges', 'Visiting Charges') ?></th>
                                    <th data-field="new_start_time_with_date" class="text-center"><?= labels('starting_time', 'Starting time') ?></th>
                                    <th data-field="new_end_time_with_date" class="text-center"><?= labels('ending_time', 'Ending time') ?></th>
                                    <th data-field="duration" class="text-center" data-visible="false"><?= labels('duration', 'Duration') ?></th>
                                    <th data-field="status" class="text-center"><?= labels('status', 'Status') ?></th>
                                    <th data-field="remarks" class="text-center" data-visible="false"><?= labels('remarks', 'Remarks') ?></th>
                                    <th data-field="operations" class="text-center" data-events="orders_events"><?= labels('operations', 'Operations') ?></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
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
                        <h3 class="mb-0" style="display: inline-block; font-size: 16px; margin-left: 10px;">Filters</h3>
                    </div>
                    <div id="cancelButton" style="cursor: pointer;">
                        <span class="material-symbols-outlined mr-2">
                            cancel
                        </span>
                    </div>
                </div>
                <div class="row mt-4 mx-2">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="order_status_filter"><?= labels('filter_booking_by_status', 'Filter Bookings by Status') ?></label>
                            <select name="order_status_filter" id="order_status_filter" class="form-control select2">
                                <option value=""><?= labels('select', 'Select') ?>-</option>
                                <option value="awaiting"><?= labels('awaiting', 'Awaiting') ?></option>
                                <option value="confirmed"><?= labels('confirmed', 'Confirmed') ?></option>
                                <option value="rescheduled"><?= labels('rescheduled', 'Rescheduled') ?></option>
                                <option value="cancelled"><?= labels('cancelled', 'Cancelled') ?></option>
                                <option value="completed"><?= labels('completed', 'Completed') ?></option>
                                <option value="started"><?= labels('started', 'Started') ?></option>
                                <option value="booking_ended"><?= labels('booking_ended', 'Booking Ended') ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group ">
                            <label for="table_filters"><?= labels('table_filters', 'Table filters') ?></label>
                            <div id="columnToggleContainer">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-4 ">
                    <div class="col-md-4 ml-2">
                        <button class="btn bg-new-primary d-block" id="filter">
                            <?= labels('apply_filter', 'Apply Filter') ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<script>
    $(document).ready(function() {
        for_drawer("#filterButton", "#filterDrawer", "#filterBackdrop", "#cancelButton");
        var columns = [{
                field: 'id',
                label: '<?= labels('id', 'ID') ?>',
                visible: false
            },
            {
                field: 'date_of_service',
                label: '<?= labels('date_of_service', 'Date of Service') ?>',
                visible: false
            },
            {
                field: 'invoice_no',
                label: '<?= labels('invoice_no', 'Invoice No') ?>'
            },
            {
                field: 'user_id',
                label: '<?= labels('user_id', 'User id') ?>'
            },
            {
                field: 'customer',
                label: '<?= labels('customer', 'Customer') ?>'
            },
            {
                field: 'final_total',
                label: '<?= labels('final_total', 'Final total') ?>',
               
            },
            {
                field: 'partner',
                label: '<?= labels('provider', 'Provider') ?>'
            },
            {
                field: 'city_id',
                label: '<?= labels('city_id', 'city_id') ?>',
                visible: false
            },
            {
                field: 'total',
                label: '<?= labels('total', 'Total') ?>',
            },

            {
                field: 'promo_code',
                label: '<?= labels('promo_code', 'Promo code') ?>',
                visible: false
            },
            {
                field: 'promo_discount',
                label: '<?= labels('promo_discount', 'Promo discount') ?>',
                visible: false
            },
            {
                field: 'admin_earnings',
                label: '<?= labels('admin_earning', 'Admin Earning') ?>',
                visible: false
            },
            {
                field: 'partner_earnings',
                label: '<?= labels('provider_earning', 'Provider earnings') ?>',
                visible: false
            },
            {
                field: 'address_id',
                label: '<?= labels('address_id', 'Address id') ?>',
                visible: false
            },
            {
                field: 'address',
                label: '<?= labels('address', 'Address') ?>',
                visible: false
            },
            {
                field: 'visiting_charges',
                label: '<?= labels('visiting_charges', 'Visiting Charges') ?>',
                visible: false
            },
            {
                field: 'new_start_time_with_date',
                label: '<?= labels('starting_time', 'Starting time') ?>',
                visible: false
            },
            {
                field: 'new_end_time_with_date',
                label: '<?= labels('ending_time', 'Ending time') ?>',
                visible: false
            },
            {
                field: 'duration',
                label: '<?= labels('duration', 'Duration') ?>',
                visible: false
            },
            {
                field: 'status',
                label: '<?= labels('status', 'Status') ?>',
            },
            {
                field: 'remarks',
                label: '<?= labels('remarks', 'Remarks') ?>',
                visible: false
            },
            {
                field: 'operations',
                label: '<?= labels('operations', 'Operations') ?>'
            }
        ];
        setupColumnToggle('user_list', columns, 'columnToggleContainer');
    });
</script>
<script>
    $("#customSearch").on('keydown', function() {
        $('#user_list').bootstrapTable('refresh');
    });

    function orders_query1(p) {
        return {
            search: $("#customSearch").val() ? $("#customSearch").val() : p.search,
            limit: p.limit,
            sort: p.sort,
            order: p.order,
            offset: p.offset,
            order_status_filter: $("#order_status_filter").val()
        };
    }
</script>