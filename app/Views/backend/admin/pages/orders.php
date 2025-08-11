<!-- Main Content -->
<div class="main-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1> <?= labels('bookings', 'Bookings') ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item"> <?= labels('bookings', 'Bookings') ?></div>
            </div>
        </div>
        <div class="container-fluid card">
            <div class="row ">
                <div class="col-12">
                    <div class="row mt-4 mb-3">
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
                                <a class="dropdown-item" onclick="custome_export('pdf','odrer list','user_list');"><?= labels('pdf', 'PDF') ?></a>
                                <a class="dropdown-item" onclick="custome_export('excel','odrer list','user_list');"><?= labels('excel', 'Excel') ?>s</a>
                                <a class="dropdown-item" onclick="custome_export('csv','odrer list','user_list')"><?= labels('csv', 'CSV') ?></a>
                            </div>
                        </div>
                    </div>
                    <table class="table " data-fixed-columns="true" id="user_list" width="100%"
                        data-detail-formatter="user_formater" data-toolbar="#toolbar" data-auto-refresh="true" data-fixed-columns="true" data-fixed-number="2"
                        data-trim-on-search="false" data-click-to-select="true" data-toggle="table" data-url="<?= base_url("admin/orders/list") ?>"
                        data-pagination-successively-size="2" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-search="false"
                        data-show-columns-search="true" data-sort-name="id" data-sort-order="DESC" data-query-params="orders_query" data-mobile-responsive="true" data-responsive="true">
                        <thead>
                            <tr>
                                <th data-field="id" class="text-center" data-sortable="true" data-visible="false"><?= labels('id', 'ID') ?></th>
                                <th data-field="user_id" class="text-center" data-visible="true" data-sortable="true"><?= labels('user_id', 'User id') ?></th>
                                <th data-field="customer" class="text-center"><?= labels('customer', 'Customer') ?></th>
                                <th data-field="partner" class="text-center"><?= labels('provider', 'Provider') ?></th>
                                <th data-field="city_id" class="text-center" data-visible="false" data-sortable="true"><?= labels('city_id', 'city_id') ?></th>
                                <th data-field="total" class="text-center" data-sortable="true" data-visible="false"><?= labels('total', 'Total') ?></th>
                                <th data-field="promo_code" class="text-center" data-sortable="true" data-visible="false"><?= labels('promo_code', 'Promo code') ?></th>
                                <th data-field="promo_discount" class="text-center" data-sortable="true" data-visible="false"><?= labels('promo_discount', 'Promo discount') ?></th>
                                <th data-field="final_total" class="text-center" data-sortable="true"><?= labels('final_total', 'Final total') ?></th>
                                <th data-field="admin_earnings" class="text-center" data-sortable="true" data-visible="false"><?= labels('admin_earning', 'admin_earnings') ?></th>
                                <th data-field="partner_earnings" class="text-center" data-sortable="true" data-visible="false"><?= labels('provider_earning', 'provider_earnings') ?></th>
                                <th data-field="address_id" class="text-center" data-visible="false"><?= labels('address_id', 'Address id') ?></th>
                                <th data-field="address" class="text-center" data-visible="false"><?= labels('address', 'Address') ?></th>
                                <th data-field="date_of_service" data-sortable="true" class="text-center" data-visible="false"><?= labels('date_of_service', 'Date of Service') ?></th>
                                <th data-field="new_start_time_with_date" data-sortable="true" class="text-center" data-visible="false"><?= labels('starting_time', 'Starting time') ?></th>
                                <th data-field="new_end_time_with_date" data-sortable="true" class="text-center" data-visible="false"><?= labels('ending_time', 'Ending time') ?></th>
                                <th data-field="duration" data-sortable="true" class="text-center" data-visible="false"><?= labels('duration', 'Duration') ?></th>
                                <th data-field="status" data-sortable="true" class="text-center"><?= labels('status', 'Status') ?></th>
                                <th data-field="remarks" class="text-center" data-visible="false"><?= labels('remarks', 'Remarks') ?></th>
                                <th data-field="operations" class="text-center" data-events="orders_events"><?= labels('operations', 'Operations') ?></th>
                            </tr>
                        </thead>
                    </table>
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
                        <div class="jquery-script-clear"></div>
                        <div class="categories" id="categories">
                            <div class="form-group ">
                                <label for="table_filters"><?= labels('select_provider', 'Select Provider') ?></label>
                                <select id="order_provider_filter" class="form-control w-100 select2" name="partner">
                                    <option value=""><?= labels('select_provider', 'Select Provider') ?></option>
                                    <?php foreach ($partner_name as $pn) : ?>
                                        <option value="<?= $pn['id'] ?>" data-members="<?= $pn['number_of_members'] ?>">
                                            <?= $pn['company_name'] . ' - ' . $pn['username'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="order_status_filter"><?= labels('filter_booking_by_status', 'Filter Booking by Status') ?></label>
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
        var dynamicColumns = fetchColumns('user_list');
        setupColumnToggle('user_list', dynamicColumns, 'columnToggleContainer');
    });
</script>