<?= helper('form'); ?>
<div class="main-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('withdrawal_requests', 'Withdrawal Requests') ?><span class="breadcrumb-item p-3 pt-2 text-primary"><i data-content="<?= labels('data_content_withdrawal_requests', 'Admin will receive the money for prepaid bookings, and their commission amount will be deducted before displaying the provider’s amount here. For instance, if a customer makes a prepaid booking of $100, the admin will deduct their commission of 10%, which is $10, and the remaining amount of $90 will be displayed as the provider’s amount here. If the provider needs the money for any circumstances, they can send a withdraw request from this page.') ?>" class="fa fa-question-circle"></i></span></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('partner/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
            </div>
        </div>
        <div class="container-fluid card">
            <div class="row">
                <div class="col-md-12">
                    <div class="row mt-4 mb-3">
                        <div class='btn bg-emerald-blue tag text-emerald-blue mr-2 ml-3 mb-2 filters_table' id="withdraw_request_filter" name="withdraw_request_all" value=""><?= labels('all', 'All') ?></div>
                        <div class='btn bg-emerald-success tag text-emerald-success mr-2 filters_table' id="withdraw_request_approved" name="withdraw_request_approved" value="withdraw_request"><?= labels('approved', 'Approved') ?></div>
                        <div class='btn bg-emerald-grey tag text-emerald-grey mr-2 filters_table' id="withdraw_request_pending" name="withdraw_request_pending" value="withdraw_request"><?= labels('pending', 'Pending') ?></div>
                        <div class='btn bg-emerald-warning tag text-emerald-warning mr-2 filters_table' id="withdraw_request_settled" name="withdraw_request_settled" value="withdraw_request"><?= labels('settled', 'Settled') ?></div>
                        <div class='btn bg-emerald-danger tag text-emerald-danger mr-2 filters_table' id="withdraw_request_rejected" name="withdraw_request_rejected" value="withdraw_request"><?= labels('rejected', 'Rejected') ?></div>
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
                                <a class="dropdown-item" onclick="custome_export('pdf','Withdraw request list','user_list');"><?= labels('pdf', 'PDF') ?></a>
                                <a class="dropdown-item" onclick="custome_export('excel','Withdraw request list','user_list');"><?= labels('excel', 'Excel') ?></a>
                                <a class="dropdown-item" onclick="custome_export('csv','Withdraw request list','user_list')"><?= labels('csv', 'CSV') ?></a>
                            </div>
                        </div>
                        <div class="col col d-flex justify-content-end">
                            <div class="text-center">
                                <a class="btn btn-primary text-white" id="add_promo" href="<?= base_url('partner/withdrawal_requests/send'); ?>"><i class="fas fa-plus"></i> <?= labels('send_withdrawal_request', 'Send Request') ?></a>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-borderd" id="user_list" data-show-export="false" data-export-types="['txt','excel','csv']"
                            data-export-options='{"fileName": "invoice-order-list","ignoreColumn": ["action"]}' data-auto-refresh="true" data-show-columns="false" data-search="false"
                            data-show-refresh="false" data-toggle="table" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-side-pagination="server" data-pagination="true"
                            data-url="<?= base_url("partner/withdrawal_requests/list") ?>" data-pagination-successively-size="2" data-sort-name="p.id" data-sort-order="desc" data-query-params="withdraw_request_query1">
                            <thead>
                                <tr>
                                    <th data-field="id" class="text-center" data-sortable="true"><?= labels('id', 'ID') ?></th>
                                    <th data-field="payment_address" class="text-center" data-visible="true"><?= labels('payment_address', 'Payment Address') ?></th>
                                    <th data-field="amount" class="text-center" data-visible="true"><?= labels('amount', 'Amount') ?></th>
                                    <th data-field="remarks" class="text-center" data-visible="false"><?= labels('remarks', 'Remarks') ?></th>
                                    <th data-field="status" class="text-center" data-visible="true"><?= labels('status', 'Status') ?></th>
                                    <th data-field="created_at" class="text-center" data-visible="false"><?= labels('created_at', 'Created at') ?></th>
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
    $(function() {
        $('.fa').popover({
            trigger: "hover"
        });
    });
    var withdraw_request_filter = "";
    $("#withdraw_request_filter").on("click", function() {
        withdraw_request_filter = "";
        $("#user_list").bootstrapTable("refresh");
    });
    $("#withdraw_request_pending").on("click", function() {
        withdraw_request_filter = "0";
        $("#user_list").bootstrapTable("refresh");
    });
    $("#withdraw_request_approved").on("click", function() {
        withdraw_request_filter = "1";
        $("#user_list").bootstrapTable("refresh");
    });
    $("#withdraw_request_rejected").on("click", function() {
        withdraw_request_filter = "2";
        $("#user_list").bootstrapTable("refresh");
    });
    $("#withdraw_request_settled").on("click", function() {
        withdraw_request_filter = "3";
        $("#user_list").bootstrapTable("refresh");
    });
    $("#customSearch").on("keydown", function() {
        $("#user_list").bootstrapTable("refresh");
    });

    function withdraw_request_query1(p) {
        return {
            search: $("#customSearch").val() ? $("#customSearch").val() : p.search,
            limit: p.limit,
            sort: p.sort,
            order: p.order,
            offset: p.offset,
            withdraw_request_filter: withdraw_request_filter,
        };
    }
    $(document).ready(function() {
        for_drawer("#filterButton", "#filterDrawer", "#filterBackdrop", "#cancelButton");
        var columns = [{
                field: 'id',
                label: '<?= labels('id', 'ID') ?>',
                visible: false
            },
            {
                field: 'payment_address',
                label: '<?= labels('payment_address', 'Payment Address') ?>'
            },
            {
                field: 'amount',
                label: '<?= labels('amount', 'Amount') ?>'
            },
            {
                field: 'remarks',
                label: '<?= labels('remarks', 'Remarks') ?>',
                visible: false
            },
            {
                field: 'status',
                label: '<?= labels('status', 'Status') ?>',
            },
            {
                field: 'created_at',
                label: '<?= labels('created_at', 'Created at') ?>',
            },
        ];
        setupColumnToggle('user_list', columns, 'columnToggleContainer');
    });
</script>