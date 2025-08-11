<div class="main-content">
    <section class="section" id="pill-general_settings" role="tabpanel">
        <div class="section-header mt-2">
            <h1><?= labels('booking_payment_management', 'Booking Payment Management') ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/partner/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item "><?= labels('booking_payment_management', "Booking Payment Management") ?></div>

            </div>
        </div>
        <div class="section-body">
            <div id="output-status"></div>
            <div class="row mt-3">
                <div class="col-md-12 col-sm-12 col-xl-12   ">
                    <div class="container-fluid card h-100">
                        <div class="">
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
                                        <?= labels('download', "Download") ?>
                                    </button>
                                    <div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 28px, 0px); top: 0px; left: 0px; will-change: transform;">
                                        <a class="dropdown-item" onclick="custome_export('pdf','history list','partner_settlement_and_cash_collection_history_list');"><?= labels('pdf', 'PDF') ?></a>
                                        <a class="dropdown-item" onclick="custome_export('excel','history list','partner_settlement_and_cash_collection_history_list');"><?= labels('excel', 'Excel') ?></a>
                                        <a class="dropdown-item" onclick="custome_export('csv','history list','partner_settlement_and_cash_collection_history_list')"><?= labels('csv', 'CSV') ?></a>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover" id="partner_settlement_and_cash_collection_history" data-show-export="true" data-export-types="['txt','excel','csv']" data-export-options='{"fileName": "invoice-order-list","ignoreColumn": ["action"]}' . data-auto-refresh="true" data-show-columns="false" data-search="false" data-show-refresh="false" data-toggle="table" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-side-pagination="server" data-pagination="true" data-url="<?= base_url("admin/all_settlement_cashcollection_history_list") ?>" data-sort-name="id" data-sort-order="desc" data-pagination-successively-size="2" data-query-params="all_settlement_cashcollection_queryParam">
                                    <thead>
                                        <tr>
                                            <th data-field="id" class="text-center" data-sortable="true"><?= labels('id', 'ID') ?></th>
                                            <th data-field="message" class="text-center" data-visible="true"><?= labels('message ', 'Message') ?></th>
                                            <th data-field="partner_name" class="text-center" data-visible="true"><?= labels('provider ', 'Provider') ?></th>

                                            <th data-field="order_id" class="text-center" data-visible="false"><?= labels('order_id', 'Order id') ?></th>
                                            <th data-field="payment_request_id" class="text-center" data-visible="false"><?= labels('payment_request_id ', 'Payment Request Id') ?></th>
                                            <th data-field="commission_percentage" class="text-center" data-visible="false"><?= labels('commission_percentage ', 'Commission Percentage') ?></th>
                                            <th data-field="type_badge" class="text-center" data-visible="true"><?= labels('type ', 'Type') ?></th>
                                            <th data-field="date" class="text-center" data-sortable="true" data-visible="true"><?= labels('date', 'Date') ?></th>
                                            <th data-field="time" class="text-center" data-visible="true"><?= labels('time ', 'Time') ?></th>
                                            <th data-field="total_amount" class="text-center" data-visible="true"><?= labels('total_amount ', 'Total amount') ?></th>
                                            <th data-field="amount" class="text-center" data-visible="true"><?= labels('amount ', 'Amount') ?></th>
                                            <th data-field="commission_amount" class="text-center" data-visible="true"><?= labels('commission_amount ', 'Commission amount') ?></th>
                                            <th data-field="status_badge" class="text-center" data-visible="true"><?= labels('status ', 'Status') ?></th>
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
    $("#customSearch").on('keydown', function() {
        $('#partner_settlement_and_cash_collection_history').bootstrapTable('refresh');
    });


    $("#filter").on("click", function(e) {
        $("#partner_settlement_and_cash_collection_history").bootstrapTable("refresh");
    });
    $(document).ready(function() {
        for_drawer("#filterButton", "#filterDrawer", "#filterBackdrop", "#cancelButton");
        var dynamicColumns = fetchColumns('partner_settlement_and_cash_collection_history');
        setupColumnToggle('partner_settlement_and_cash_collection_history', dynamicColumns, 'columnToggleContainer');
    });
</script>