<!-- Main Content -->
<div class="main-content">
    <section class="section">

        <div class="section-header mt-2">
            <h1><?= labels('transactions', 'Transactions') ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item active"><?= labels('transactions', 'Transactions') ?></div>

            </div>
        </div>


        <div class="container-fluid card">

            <div class="card-body">




                <div class="row mb-3">
                    <div class="col-lg">


                        <div class="row mt-4 mb-3 ">


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
                                    <a class="dropdown-item" onclick="custome_export('pdf','Transaction list','transaction_table');"> <?= labels('pdf', 'PDF') ?> </a>
                                    <a class="dropdown-item" onclick="custome_export('excel','Transaction list','transaction_table');"> <?= labels('excel', 'Excel') ?> </a>
                                    <a class="dropdown-item" onclick="custome_export('csv','Transaction list','transaction_table')"> <?= labels('csv', 'CSV') ?> </a>
                                </div>
                            </div>


                        </div>
                        <table class="table " data-fixed-columns="true" id="transaction_table" data-pagination-successively-size="2" data-detail-formatter="transaction_table_formatter" data-auto-refresh="true" data-toggle="table" data-url="<?= base_url("admin/transactions/list-transactions") ?>" data-toolbar="#toolbar" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-search="false" data-show-columns="false" data-show-columns-search="true" data-show-refresh="false" data-sort-name="id" data-sort-order="DESC" data-query-params="txn_table">

                            <thead>
                                <tr>
                                    <th data-field="id" data-sortable="true"><?= labels('id', 'ID') ?></th>
                                    <th data-field="user_id" data-sortable="true"><?= labels('user_id', 'User ID') ?></th>
                                    <th data-field="name"><?= labels('user_name', 'User Name') ?></th>
                                    <th data-field="type"><?= labels('payment_method', 'Payment Method') ?></th>
                                    <th data-field="txn_id"><?= labels('transaction_id', 'Transaction ID') ?></th>
                                    <th data-field="transaction_type"><?= labels('transaction_type', 'Transaction Type') ?></th>
                                    <th data-field="amount" data-sortable="true"><?= labels('amount', "Amount") ?></th>
                                    <th data-field="message" data-visible="false"><?= labels('message', "Message") ?></th>
                                    <th data-field="status"><?= labels('status', "Status") ?></th>
                                    <th data-field="created_at"><?= labels('created_on', 'Created on') ?></th>
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

                        <div class="form-group mb-0">
                            <label for="date"><?= labels('payment_method') ?></label>
                            <select name="payment_method" id="payment_method" class="form-control selectric">
                                <option value=""><?= labels('all', "All") ?></option>
                                <option value="Stripe"><?= labels('stripe', "Stripe") ?></option>
                                <option value="razorpay"><?= labels('razorPay', "Razorpay") ?></option>
                                <option value="paystack"><?= labels('paystack', "Paystack") ?></option>
                                <option value="paypal"><?= labels('paypal', "Paypal") ?></option>

                            </select>
                        </div>
                    </div>


                    <div class="col-md-12 mt-3">

                        <div class="form-group mb-0">
                            <label for="date"><?= labels('transaction_date', "Transaction Date") ?></label>
                            <input type="text" name="date_range" id="txn_date" class="form-control">
                        </div>
                    </div>

                    <div class="col-md-12 mt-3">

                        <div class="form-group mb-0">
                            <label for="date"><?= labels('filter_by_status', "Filter by Status") ?></label>
                            <select name="subscription_type" class="form-control selectric" id="transaction_status">
                                <option value=""><?= labels('all', 'All') ?></option>
                                <option value="success"><?= labels('success', "Success") ?></option>
                                <option value="failed"><?= labels('failed', "Failed") ?></option>
                                <option value="pending"><?= labels('pending', "Pending") ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12 mt-3">
                        <div class="form-group ">
                            <label for="table_filters"><?= labels('table_filters', 'Table filters') ?></label>
                            <div id="columnToggleContainer">
                            </div>
                        </div>
                    </div>

                </div>
                <div class="d-flex justify-content-end mt-4 ">
                    <div class="col-md-4 ml-2">
                        <!-- <button class="btn bg-new-primary d-block" id="filter">
                            <?= labels('apply_filter', 'Apply Filter') ?>
                        </button> -->

                        <button class="btn bg-new-primary d-block" onclick="refresh_table('transaction_table')">
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


        var dynamicColumns = fetchColumns('transaction_table');

        setupColumnToggle('transaction_table', dynamicColumns, 'columnToggleContainer');
    });
</script>