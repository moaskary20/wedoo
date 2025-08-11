<div class="main-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('subscription_history', "Subscription History") ?><span class="breadcrumb-item p-3 pt-2 text-primary"></span></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('partner/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item"></i> <?= labels('subscription_history', 'Subscription History') ?></div>
            </div>
        </div>
        <div class="section-body">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mt-4 mb-3">
                                <div class='btn bg-emerald-blue tag text-emerald-blue mr-2 ml-3 mb-2 filters_table' id="subscription_filter_all" name="subscription_filter" value="subscription_filter"><?= labels('all', 'All') ?></div>
                                <div class='btn bg-emerald-success tag text-emerald-success mr-2 filters_table' id="subscription_filter_active" name="subscription_filter_active" value="subscription_filter"><?= labels('active', 'Active') ?></div>
                                <div class='btn bg-emerald-danger tag text-emerald-danger mr-2 filters_table' id="subscription_filter_deactive" name="subscription_filter_deactivate" value="subscription_filter"><?= labels('deactive', 'Deactive') ?></div>
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
                                        <?= labels('download', "Download") ?>
                                    </button>
                                    <div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 28px, 0px); top: 0px; left: 0px; will-change: transform;">
                                        <a class="dropdown-item" onclick="custome_export('pdf','Withdraw request list','user_list');"> <?= labels('pdf', "PDF") ?> </a>
                                        <a class="dropdown-item" onclick="custome_export('excel','Withdraw request list','user_list');"> <?= labels('excel', "Excel") ?> </a>
                                        <a class="dropdown-item" onclick="custome_export('csv','Withdraw request list','user_list')"> <?= labels('csv', "CSV") ?> </a>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover table-borderd" id="subscription_list" data-show-export="false" data-export-types="['txt','excel','csv']" data-export-options='{"fileName": "invoice-order-list","ignoreColumn": ["action"]}' data-auto-refresh="true" data-show-columns="false" data-search="false" data-show-refresh="false" data-toggle="table" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-side-pagination="server" data-pagination="true" data-url="<?= base_url('partner/subscription_history_list'); ?>" data-sort-name="id" data-sort-order="desc" data-query-params="subscription_query_paramas" data-pagination-successively-size="2">
                                    <thead>
                                        <tr>
                                            <th data-field="id" data-visible="false" data-sortable="true"><?= labels('id', 'ID')  ?></th>
                                            <th data-field="name" data-sortable="true"><?= labels('name', 'Name')  ?></th>
                                            <th data-field="description"><?= labels('description', 'Description')  ?></th>
                                            <th data-field="duration"><?= labels('duration', 'Duration')  ?></th>
                                            <th data-field="max_order_limit"><?= labels('order_limit', 'Order Limit')  ?></th>
                                            <th data-field="status_badge"><?= labels('status', 'Status')  ?></th>
                                            <th data-field="is_commision_badge"><?= labels('commission', 'Commission')  ?></th>
                                            <th data-field="commission_threshold"><?= labels('commission_threshold', 'Commission Thresold')  ?></th>
                                            <th data-field="commission_percentage"><?= labels('commission_percentage', 'Commission Percentage')  ?></th>
                                            <th data-field="price_with_tax"><?= labels('price', 'Price')  ?></th>
                                            <th data-field="tax_percentage"><?= labels('tax_percentage', 'Tax Percentage')  ?></th>
                                            <th data-field="purchase_date" data-visible="false"><?= labels('purchase_date', 'Purchase Date')  ?></th>
                                            <th data-field="expiry_date" data-visible="false"><?= labels('expiry_date', 'Expiry Date')  ?></th>
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
    })
    $("#customSearch").on('keydown', function() {
        $('#subscription_list').bootstrapTable('refresh');
    });
    var subscription_filter = "";
    $("#subscription_filter_all").on("click", function() {
        subscription_filter = "";
        $("#subscription_list").bootstrapTable("refresh");
    });
    $("#subscription_filter_active").on("click", function() {
        subscription_filter = "active";
        $("#subscription_list").bootstrapTable("refresh");
    });
    $("#subscription_filter_deactive").on("click", function() {
        subscription_filter = "deactive";
        $("#subscription_list").bootstrapTable("refresh");
    });

    function subscription_query_paramas(p) {
        return {
            search: $("#customSearch").val() ? $("#customSearch").val() : p.search,
            limit: p.limit,
            sort: p.sort,
            order: p.order,
            offset: p.offset,
            subscription_filter: subscription_filter,
        };
    }
    $(document).ready(function() {
        for_drawer("#filterButton", "#filterDrawer", "#filterBackdrop", "#cancelButton");
        var columns = [{
                field: 'id',
                label: '<?= labels('id', 'ID')  ?>',
                visible: false
            },
            {
                field: 'name',
                label: '<?= labels('name', 'Name')  ?>'
            },
            {
                field: 'description',
                label: '<?= labels('description', 'Description')  ?>'
            },
            {
                field: 'duration',
                label: '<?= labels('duration', 'Duration')  ?>',
            },
            {
                field: 'max_order_limit',
                label: '<?= labels('order_limit', 'Order Limit')  ?>',
                visible: false
            },
            {
                field: 'status_badge',
                label: '<?= labels('status', 'Status')  ?>',
            },
            {
                field: 'is_commision_badge',
                label: '<?= labels('commission', 'Commission')  ?>',
            },
            {
                field: 'commission_threshold',
                label: '<?= labels('commission_threshold', 'Commission Thresold')  ?>',
                visible: false
            },
            {
                field: 'commission_percentage',
                label: '<?= labels('commission_percentage', 'Commission Percentage')  ?>',
                visible: false
            },
            {
                field: 'price_with_tax',
                label: '<?= labels('price', 'Price')  ?>',
            },
            {
                field: 'tax_percentage',
                label: '<?= labels('tax_percentage', 'Tax Percentage')  ?>',
                visible: false
            },
            {
                field: 'purchase_date',
                label: '<?= labels('purchase_date', 'purchase Date')  ?>',
                visible: false
            },
            {
                field: 'expiry_date',
                label: '<?= labels('expiry_date', 'Expiry Date')  ?>',
                visible: false
            },
        ];
        setupColumnToggle('subscription_list', columns, 'columnToggleContainer');
    });
</script>