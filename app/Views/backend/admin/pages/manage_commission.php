<div class="main-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('settlement', "Settlement") ?>
                <span class="breadcrumb-item p-3 pt-2 text-primary"><i data-content="<?= labels('data_content_manage_commission', 'The admin will be able to view the prepaid booking amount that is to be sent to the provider. This amount represents the total payment made by the customer in advance for the provider’s services. The admin is responsible for  sending the remaining payment to the provider.') ?>" class="fa fa-question-circle"></i></span>
            </h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/partners') ?>"><i class="fas fa-handshake text-warning"></i> </i> <?= labels('provider', 'Provider') ?></a></div>
                <div class="breadcrumb-item"></i> <?= labels('settlement', 'Settlement') ?></div>
            </div>
        </div>
        <div class="section-body">
            <div class="container-fluid card">
                <div class="">
                    <div class="row">
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
                                        <a class="dropdown-item" onclick="custome_export('pdf','Settlement  list','commission_list');"><?= labels('pdf', 'PDF') ?></a>
                                        <a class="dropdown-item" onclick="custome_export('excel','Settlement list','commission_list');"><?= labels('excel', 'Excel') ?></a>
                                        <a class="dropdown-item" onclick="custome_export('csv','Settlement list','commission_list')"><?= labels('csv', 'CSV') ?></a>
                                    </div>
                                </div>
                                <div class="col-md-2" id="update_bulk">
                                    <div class="form-group">
                                        <button class="btn bg-primary text-white" type="submit" id="bulk_order_update" disabled><?= labels('bulk_settlement', 'Bulk Settlement') ?></button>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover table-borderd" id="commission_list" data-show-export="true" data-export-types="['txt','excel','csv']"
                                    data-export-options='{"fileName": "invoice-order-list","ignoreColumn": ["action"]}' data-auto-refresh="true" data-show-columns="false"
                                    data-search="false" data-show-refresh="false" data-toggle="table" data-page-list="[5, 10, 25, 50, 100, 200, All]"
                                    data-side-pagination="server" data-pagination="true" data-url="<?= base_url('admin/partners/commission_list'); ?>" data-sort-name="id" data-sort-order="desc" data-query-params="manage_commission_query_params">
                                    <thead>
                                        <tr>
                                            <th class="text-center multi-check" data-checkbox="true">
                                            <th data-field="partner_id" data-visible="true" data-sortable="true">
                                                <?= labels('provider_id', 'Provider ID')  ?>
                                            </th>
                                            <th data-field="company_name" data-visible="true">
                                                <?= labels('company_name', 'Company Name')  ?>
                                            </th>
                                            <th data-field="partner_name" data-visible="true">
                                                <?= labels('provider_name', 'Provider Name')  ?>
                                            </th>
                                            <th data-field="balance" data-visible="true" data-sortable="true"><?= labels('balance', 'Balance') ?></th>
                                            <th data-field="operations" data-visible="true" data-events="commission_events"><?= labels('operations', 'Operations') ?></th>
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
    <!-- model for commission settlement -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"><?= labels('commission_settlement', "Commission Settlement") ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="<?= base_url('admin/partners/commission_pay_out') ?>" method="post" class="form-submit-event" id="pay-out-form">
                        <input type="hidden" name="partner_id" id="partner_id">
                        <div class="row">
                            <div class="col-md">
                                <div class="form-group">
                                    <label for="amount" class="required"><?= labels('amount', "Amount") ?></label>
                                    <input id="amount" class="form-control" type="number" name="amount" min="1">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md">
                                <div class="form-group">
                                    <label for="amount" class="required"><?= labels('message', "Message") ?></label>
                                    <input id="message" class="form-control" type="text" name="message" min="0">
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary submit_button">
                            <?= labels('update_balance', "Update Balance") ?>
                        </button>
                    </form>
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
<script>
    $(document).on('change', '.multi-check', (event) => {
        selected = $('#commission_list').bootstrapTable('getSelections');
        if (selected.length === 0) {
            $("#bulk_order_update").attr('disabled', 'disabled');
        } else {
            $("#bulk_order_update").removeAttr('disabled');
        }
    });
    $('#bulk_order_update').click(function() {
        var request_ids = [];
        selected = $('#commission_list').bootstrapTable('getSelections');
        var arr = Object.values(selected);
        var i;
        var final_selection = [];
        var request_ids = arr.map(({
            partner_id
        }) => (partner_id));
        Swal.fire({
            title: "<?= labels('are_your_sure', 'Are you sure?') ?>",
            text: you_wont_be_able_to_revert_this,
            icon: 'error',
            input: 'text',
            inputPlaceholder: '<?= labels('enter_message_here', 'Enter Message Here') ?>',
            inputClass: 'custom-input-class',
            inputAttributes: {
                autocapitalize: 'off',
                required: 'true',
            },
            showCancelButton: true,
            cancelButtonText: "<?= labels('cancel', 'Cancel') ?>",
            confirmButtonText: yes_proceed
        }).then((result) => {

            if (result.value) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: baseUrl + "/admin/partners/bulk_commission_settelement",
                    data: {
                        request_ids: request_ids,
                        message: result.value
                    },
                    type: 'post',
                    success: function(response) {
                        if (response.error == false) {
                            showToastMessage(response.message, "success");
                            setTimeout(() => {
                                $('#commission_list').bootstrapTable('refresh')
                            }, 2000)
                        } else {
                            return showToastMessage(response.message, "error");
                        }
                        $('#update_bulk').addClass('d-none');
                        $('#update_bulk').removeClass('d-flex');
                        $('#update_bulk').removeClass('justify-content-end');
                        return;
                    },
                    error: function(response) {
                        return showToastMessage(response.message, "error");
                    }
                });
            }
        })
    });
</script>
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
                field: 'partner_id',
                label: '<?= labels('provider_id', 'Provider ID')  ?>',
            },
            {
                field: 'company_name',
                label: '<?= labels('company_name', 'Company Name')  ?>'
            },
            {
                field: 'partner_name',
                label: '<?= labels('provider_name', 'Provider Name')  ?>'
            },
            {
                field: 'balance',
                label: '<?= labels('balance', 'Balance') ?>',
            },
            {
                field: 'operations',
                label: '<?= labels('operations', 'Operations') ?>',
            },
        ];
        setupColumnToggle('commission_list', columns, 'columnToggleContainer');
    });
</script>
<script>
    $(document).on('click', '.pay-out', function() {
        $('#pay-out-form')[0].reset();
    });
</script>