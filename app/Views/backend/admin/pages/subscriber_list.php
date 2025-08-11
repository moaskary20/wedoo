<!-- Main Content -->

<div class="main-content">
    <section class="section">


        <div class="section-header mt-2">
            <h1><?= labels('subscriber_list', "Subscriber List") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <!-- <div class="breadcrumb-item"> <?= labels('subscrition', 'Subscription') ?></a></div> -->
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/subscription') ?>"><i class="fas fa-newspaper text-warning"></i> <?= labels('subscription', 'Subscription') ?></a></div>

            </div>
        </div>

        <div class="row mb-2">
            <div class="m-0 col-xxl-12 col-lg-12 col-xl-12 ">
                <div class="row ">


                    <div class="col-xxl-3 col-xl-3 col-lg-6 col-md-6">
                        <div class="card card-statistic-1 " style="padding: 20px;">
                            <div class="content d-flex">

                                <div class="provider_a   bg-emerald-success text-light " style="box-shadow: 0px 8px 26px #47C36326;margin: 0;padding: 0;">
                                    <i class="fas fa-user text-emerald-success" style="    font-size: 24px;"></i>
                                </div>

                                <div class="card-body my-3 p-0" style="margin-left: 20px!important;margin-right: 20px!important;">
                                    <span class="counter"><?= $totalSubscriptionCount ?></span>
                                    <h5 class="dashboard_small_label">
                                        <?= labels('total_subscription', "Total Subscription") ?></h5>
                                </div>
                            </div>

                        </div>
                    </div>


                    <div class="col-xxl-3 col-xl-3 col-lg-6 col-md-6">
                        <div class="card card-statistic-1 " style="padding: 20px;">
                            <div class="content d-flex">

                                <div class="provider_a    bg-emerald-blue text-light " style="box-shadow: 0px 8px 26px #47C36326;margin: 0;padding: 0;">
                                    <i class="fas fa-check-circle text-emerald-blue" style="    font-size: 24px;"></i>
                                </div>

                                <div class="card-body my-3 p-0" style="margin-left: 20px!important;margin-right: 20px!important;">
                                    <span class="counter"><?= $activeSubscriptionCount ?></span>
                                    <h5 class="dashboard_small_label">
                                        <?= labels('active_subscription', "Active Subscription") ?></h5>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="col-xxl-3 col-xl-3 col-lg-6 col-md-6">
                        <div class="card card-statistic-1 " style="padding: 20px;">
                            <div class="content d-flex">

                                <div class="provider_a   bg-emerald-danger text-light " style="box-shadow: 0px 8px 26px #47C36326;margin: 0;padding: 0;">
                                    <i class="fas fa-times-circle text-emerald-danger" style="    font-size: 24px;"></i>
                                </div>

                                <div class="card-body my-3 p-0" style="margin-left: 20px!important;margin-right: 20px!important;">
                                    <span class="counter"><?= $expiredSubscriptionCount ?></span>
                                    <h5 class="dashboard_small_label">
                                        <?= labels('expired_subscription', "Expired Subscription") ?></h5>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="col-xxl-3 col-xl-3 col-lg-6 col-md-6">
                        <div class="card card-statistic-1 " style="padding: 20px;">
                            <div class="content d-flex">

                                <div class="provider_a   bg-emerald-warning text-light " style="box-shadow: 0px 8px 26px #47C36326;margin: 0;padding: 0;">
                                    <i class="fas fa-clock text-emerald-warning" style="    font-size: 24px;"></i>
                                </div>

                                <div class="card-body my-3 p-0" style="margin-left: 20px!important;margin-right: 20px!important;">
                                    <span class="counter"><?= $expiringSoonSubscriptionCount ?></span>
                                    <h5 class="dashboard_small_label">
                                        <?= labels('expiring_soon', "Expiring soon") ?></h5>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>

            </div>


        </div>


        <div class="row">

            <div class="col d-flex w-100">
                <div class="card w-100 h-100">
                    <div class="col mb-3" style="border-bottom: solid 1px #e5e6e9;">
                        <div class="toggleButttonPostition"><?= labels('subscriber_list', "Subscriber List") ?></div>

                    </div>
                    <div class="card-body">
                        <div class="col-12">

                            <div class="row mb-3 ">

                                <div class='btn bg-emerald-blue tag text-emerald-blue mr-2 ml-3 mb-2 filters_table' id="subscription_filter_all" name="subscription_filter" value="subscription_filter"> <?= labels('all', 'All') ?> </div>
                                <div class='btn bg-emerald-success tag text-emerald-success mr-2 filters_table' id="subscription_filter_active" name="subscription_filter_active" value="subscription_filter"> <?= labels('active', 'Active') ?> </div>
                                <div class='btn bg-emerald-danger tag text-emerald-danger mr-2 filters_table' id="subscription_filter_deactive" name="subscription_filter_deactivate" value="subscription_filter"> <?= labels('deactive', 'Deactive') ?> </div>

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

                                <div class="dropdown d-inline ml-2">
                                    <button class="btn export_download dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <?= labels('download', 'Download') ?>
                                    </button>
                                    <div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 28px, 0px); top: 0px; left: 0px; will-change: transform;">
                                        <a class="dropdown-item" onclick="custome_export('pdf','Subscriber list','slider_list');"><?= labels('pdf', 'PDF') ?></a>
                                        <a class="dropdown-item" onclick="custome_export('excel','Subscriber list','slider_list');"><?= labels('excel', 'Excel') ?></a>
                                        <a class="dropdown-item" onclick="custome_export('csv','Subscriber list','slider_list')"><?= labels('csv', 'CSV') ?></a>
                                    </div>
                                </div>


                            </div>


                            <table class="table " id="slider_list" data-fixed-columns="true" 
                            data-pagination-successively-size="2" data-detail-formatter="user_formater" data-auto-refresh="true" data-toggle="table"
                             data-url="<?= base_url("admin/subscription/partner_subscriber_list") ?>" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-search="false" data-show-columns="false" data-show-columns-search="true" data-show-refresh="false" data-sort-name="id" data-sort-order="desc" data-query-params="subscriber_query_params">
                                <thead>
                                    <tr>
                                        <th data-field="id" class="text-center" data-visible="false" data-sortable="true"><?= labels('id', 'ID') ?></th>
                                        <th data-field="banner_image" class="text-center"><?= labels('image', 'Image') ?></th>
                                        <th data-field="company_name" class="text-center"><?= labels('provider', 'Provider') ?></th>
                                        <th data-field="name" class="text-center"><?= labels('subscrition', 'Subscription') ?></th>
                                        <th data-field="purchase_date" class="text-center"><?= labels('purchase_date', 'Purchase Date') ?></th>
                                        <th data-field="expiry_date" class="text-center"><?= labels('expiry_date', 'Expiry Date') ?></th>
                                        <th data-field="duration" class="text-center" data-visible="false"><?= labels('duration', 'Duration') ?>(Days)</th>
                                        <th data-field="price_with_tax" class="text-center"><?= labels('price', 'Prize') ?></th>
                                        <th data-field="is_payment" class="text-center"><?= labels('payment', 'Payment') ?></th>
                                        <th data-field="status_badge" class="text-center"><?= labels('status', 'Status') ?></th>
                                        <th data-field="operations" class="text-center"><?= labels('operations', 'Operations') ?></th>

                                    </tr>
                                </thead>
                            </table>
                        </div>

                    </div>
                </div>

            </div>

        </div>


</div>






</section>
</div>
<script>
    $("#customSearch").on('keydown', function() {
        $('#slider_list').bootstrapTable('refresh');
    });


    var subscription_filter = "";
    $("#subscription_filter_all").on("click", function() {
        subscription_filter = "";

        $("#slider_list").bootstrapTable("refresh");
    });
    $("#subscription_filter_active").on("click", function() {
        subscription_filter = "active";
        
        $("#slider_list").bootstrapTable("refresh");
        
    });
    $("#subscription_filter_deactive").on("click", function() {
        subscription_filter = "deactive";

        $("#slider_list").bootstrapTable("refresh");
    });
    $("#customSearch").on('keydown', function() {
        $('#slider_list').bootstrapTable('refresh');
    });


    function subscriber_query_params(p) {
        return {
            search: $("#customSearch").val() ? $("#customSearch").val() : p.search,
            limit: p.limit,
            sort: p.sort,
            order: p.order,
            offset: p.offset,
            subscription_filter: subscription_filter,

        };
    }
</script>