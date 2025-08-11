<div class="main-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('cash_collection', "Cash Collection") ?>
                <span class="breadcrumb-item p-3 pt-2 text-primary"><i data-content="<?= labels('data_content_cash_collection', 'Admin commission amounts for COD bookings will be managed here. The amount will be credited once the booking status is completed. For example, if a customer books a service for $100 as COD, the provider will receive the total booking amount of $100 in cash. The provider will then need to pay the admin their commission amount of 10%, which in this case is $10. Admin can check the commission amount received from the provider here in their account, and they will need to collect this amount from the provider directly.') ?>" class="fa fa-question-circle"></i></span>
            </h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/partners') ?>"><i class="fas fa-handshake text-warning"></i> </i> <?= labels('provider', 'Provider') ?></a></div>
                <div class="breadcrumb-item"></i> <?= labels('cash_collection', 'Cash Collection') ?></div>
            </div>
        </div>
        <div class="section-body">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row  mb-3">
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
                                        <a class="dropdown-item" onclick="custome_export('pdf','Cash Collection  list','cash_collection');"><?= labels('pdf', 'PDF') ?></a>
                                        <a class="dropdown-item" onclick="custome_export('excel','Cash Collection list','cash_collection');"><?= labels('excel', 'Excel') ?></a>
                                        <a class="dropdown-item" onclick="custome_export('csv','Cash Collection list','cash_collection')"><?= labels('csv', 'CSV') ?></a>
                                    </div>
                                </div>
                                <div class="col-md-2" id="update_bulk">
                                    <div class="form-group">
                                        <button class="btn bg-primary text-white" type="submit" id="bulk_order_update" disabled><?= labels('bulk_cash_collection', "Bulk Cash Collection") ?></button>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover table-borderd" data-pagination-successively-size="2" id="cash_collection" data-show-export="false" data-export-types="['txt','excel','csv']" data-export-options='{"fileName": "invoice-order-list","ignoreColumn": ["action"]}' data-auto-refresh="true" data-show-columns="false" data-search="false" data-show-refresh="false" data-toggle="table" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-side-pagination="server" data-pagination="true" data-url="<?= base_url('admin/partners/cash_collection_list'); ?>" data-sort-name="id" data-sort-order="desc" data-query-params="cash_collection_query_paramas">
                                    <thead>
                                        <tr>
                                            <th class="text-center multi-check" data-checkbox="true">
                                            <th data-field="partner_id" data-visible="false" data-sortable="true"> <?= labels('partner_id', 'Partner Id')  ?> </th>
                                            <th data-field="partner_name" data-visible="true"><?= labels('partner_name', 'Provider')  ?></th>
                                            <!-- <th data-field="admin_commission" data-visible="true"><?= labels('commison_percentage', 'Commison Percentage')  ?></th> -->
                                            <th data-field="payable_commision" data-visible="true"><?= labels('commission', 'Commission')  ?></th>
                                            <th data-field="cash_collection_button" data-visible="true" data-events="cash_collection_events"><?= labels('operations', 'Operations') ?></th>
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
    <div class="modal fade" id="update_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"><?= labels('cash_collection', "Cash Collection") ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="<?= base_url('admin/partners/cash_collection_deduct') ?>" method="post" class="form-submit-event" id="pay-out-form">
                        <input type="hidden" name="partner_id" id="partner_id">
                        <div class="row">
                            <div class="col-md">
                                <div class="form-group">
                                    <label for="amount" class="required"><?= labels('amount_to_be_collected', 'Amount to be collected') ?></label>
                                    <input id="amount" class="form-control" type="number" name="amount" min="1">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md">
                                <div class="form-group">
                                    <label for="amount" class="required"><?= labels('amount_to_be_collected', 'Amount to be collected') ?> <?= labels('message', 'Message') ?></label>
                                    <input id="message" class="form-control" type="text" name="message">
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary submit_button">
                            <?= labels('collect', 'Collect') ?>
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
        selected = $('#cash_collection').bootstrapTable('getSelections');
        if (selected.length === 0) {
            $("#bulk_order_update").attr('disabled', 'disabled');
        } else {
            $("#bulk_order_update").removeAttr('disabled');
        }
    });
    $('#bulk_order_update').click(function() {
        var request_ids = [];
        selected = $('#cash_collection').bootstrapTable('getSelections');
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
            confirmButtonText: yes_proceed
        }).then((result) => {
            if (result.value) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: baseUrl + "/admin/partners/bulk_cash_collection",
                    data: {
                        request_ids: request_ids,
                        message: result.value
                    },
                    type: 'post',
                    success: function(response) {
                        if (response.error == false) {
                            showToastMessage(response.message, "success");
                            setTimeout(() => {
                                $('#cash_collection').bootstrapTable('refresh')
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
        $('#cash_collection').bootstrapTable('refresh');
    });
    $(document).ready(function() {
        for_drawer("#filterButton", "#filterDrawer", "#filterBackdrop", "#cancelButton");
        var columns = [{
                field: 'partner_id',
                label: ' <?= labels('partner_id', 'Partner Id')  ?>',
            },
            {
                field: 'partner_name',
                label: '<?= labels('partner_name', 'Provider')  ?>'
            },
            {
                field: 'payable_commision',
                label: '<?= labels('commison', 'Commison')  ?>',
            },
            {
                field: 'cash_collection_button',
                label: '<?= labels('operations', 'Operations') ?>',
            },
        ];
        setupColumnToggle('cash_collection', columns, 'columnToggleContainer');
    });
</script>