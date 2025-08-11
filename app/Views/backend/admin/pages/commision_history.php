<div class="main-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('settlement_history', ' Settlement History') ?>
                <span class="breadcrumb-item p-3 pt-2 text-primary"><i data-content="<?= labels('data_content_settlement_history', 'The admin will be able to view the prepaid booking amount that is to be sent to the provider. This amount represents the total payment made by the customer in advance for the provider’s services. The admin is responsible for  sending the remaining payment to the provider.') ?>" class="fa fa-question-circle"></i></span>
            </h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/partners') ?>"><i class="fas fa-handshake text-warning"></i> </i> <?= labels('provider', 'Provider') ?></a></div>
                <div class="breadcrumb-item"></i> <?= labels('settlement_history', ' Settlement History') ?></div>
            </div>
        </div>
        <div class="section-body">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
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
                                        <a class="dropdown-item" onclick="custome_export('pdf','Settlement history  list','commission_list');"><?= labels('pdf', 'PDF') ?></a>
                                        <a class="dropdown-item" onclick="custome_export('excel','Settlement history list','commission_list');"><?= labels('excel', 'Excel') ?></a>
                                        <a class="dropdown-item" onclick="custome_export('csv','Settlement history list','commission_list')"><?= labels('csv', 'CSV') ?></a>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover table-borderd" id="commission_list" data-show-export="true" data-export-types="['txt','excel','csv']"
                                    data-export-options='{"fileName": "invoice-order-list","ignoreColumn": ["action"]}'
                                    data-auto-refresh="true" data-show-columns="false" data-search="false" data-show-refresh="false"
                                    data-toggle="table" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-side-pagination="server" data-pagination="true"
                                    data-url="<?= base_url('admin/partners/manage_commission_history_list'); ?>" data-sort-name="id" data-sort-order="desc"
                                    data-query-params="comission_history_query_params">
                                    <thead>
                                        <tr>
                                            <th data-field="id" data-visible="false" data-sortable="true">
                                                <?= labels('id', 'ID')  ?>
                                            </th>
                                            <th data-field="provider_id" data-visible="false" data-sortable="true">
                                                <?= labels('provider_id', 'Provider ID')  ?>
                                            </th>
                                            <th data-field="partner_name" data-visible="true" data-sortable="true">
                                                <?= labels('partner_name', 'Provider Name')  ?>
                                            </th>
                                            <th data-field="message" data-visible="true" data-sortable="false">
                                                <?= labels('message', 'Message')  ?>
                                            </th>
                                            <th data-field="status" data-visible="true" data-sortable="true">
                                                <?= labels('status', 'Status')  ?>
                                            </th>
                                            <th data-field="amount" data-visible="true" data-sortable="true">
                                                <?= labels('amount', 'Amount')  ?>
                                            </th>
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
    });
    $("#customSearch").on('keydown', function() {
        $('#commission_list').bootstrapTable('refresh');
    });
    $(document).ready(function() {
        for_drawer("#filterButton", "#filterDrawer", "#filterBackdrop", "#cancelButton");
        var columns = [{
                field: 'provider_id',
                label: '<?= labels('provider_id', 'Provider ID')  ?>',
            },
            {
                field: 'partner_name',
                label: '<?= labels('partner_name', 'Provider Name')  ?>'
            },
            {
                field: 'message',
                label: '<?= labels('message', 'Message')  ?>',
            },
            {
                field: 'status',
                label: '<?= labels('status', 'Status')  ?>',
            },
            {
                field: 'amount',
                label: ' <?= labels('amount', 'Amount')  ?>',
            },
        ];
        setupColumnToggle('commission_list', columns, 'columnToggleContainer');
    });
</script>