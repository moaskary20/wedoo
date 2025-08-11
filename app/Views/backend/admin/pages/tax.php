<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1><?= labels('manage_taxes', "Manage Taxes") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item "><a href="<?= base_url('/admin/settings/system-settings') ?>"><?= labels('system_settings', "System Settings") ?></a></div>

                <div class="breadcrumb-item"><?= labels('manage_taxes', "Manage Taxes") ?></a></div>
            </div>
        </div>

        <div class="row">

            <div class="col-md-6">
                <div class=" card">
                    <?= helper('form'); ?>
                    <div class="row">
                        <h2 class='section-title'><?= labels('manage_taxes', "Manage Taxes") ?></h2>
                        <div class="card-body">
                            <?= form_open('admin/tax/add_tax', ['method' => "post", 'class' => 'form-submit-event', 'id' => 'add_faqs', 'enctype' => "multipart/form-data"]); ?>
                            <div class="form-group">
                                <label for="title"><?= labels('title', "Title") ?></label>
                                <input id="title" class="form-control" type="text" name="title" placeholder="Enter the Title here">
                            </div>
                            <div class="form-group">
                                <label for="percentage"><?= labels('percentage', "Percentage") ?></label>
                                <input id="percentage" class="form-control" type="number" name="percentage" placeholder="Enter the percentage here">
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input id="status" class="custom-control-input" type="checkbox" name="tax_status" checked>
                                    <label for="status" class="custom-control-label">
                                        <span id="tax_status">
                                            <?= labels('enable', "Enable") ?>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <button type="reset" class="btn btn-warning"> <?= labels('reset', "Reset") ?></button>
                            <button type="submit" class="btn btn-success"><?= labels('submit', "Submit") ?></button>
                            <?= form_close(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid card">
            <div class="row">
                <div class="col-lg">
                    <h2 class='section-title'><?= labels('taxes', "Taxes") ?></h2>
                    <div class="card-body">
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <div class="card">
                                    <table class="table " data-pagination-successively-size="2" id="tax_list" data-detail-formatter="user_formater" data-auto-refresh="true" data-toggle="table" data-url="<?= base_url("admin/tax/list") ?>" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-search="true" data-show-columns="true" data-show-columns-search="true" data-show-refresh="true" data-sort-name="id" data-sort-order="desc">
                                        <thead>
                                            <tr>
                                                <th data-field="id" class="text-center" data-sortable="true"><?= labels('id', 'ID') ?></th>
                                                <th data-field="title" class="text-center" data-sortable="true"><?= labels('title', 'Title') ?></th>
                                                <th data-field="percentage" class="text-center" data-sortable="true"><?= labels('percentage', 'Percentage') ?></th>
                                                <th data-field="status" class="text-center" data-sortable="true"><?= labels('status', 'Status') ?></th>
                                                <th data-field="created_at" class="text-center" data-visible="false" data-sortable="true"><?= labels('created_at', 'Created At') ?></th>
                                                <th data-field="operations" class="text-center" data-events="taxes_events"><?= labels('operations', 'Operations') ?></th>
                                            </tr>
                                        </thead>
                                    </table>

                                </div>
                            </div>
                            <!-- </div> -->
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
    <!-- update modal -->
    <div class="modal fade" id="update_modal" tabindex="-1" aria-labelledby="update_modal_thing" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- <form action="" method="post"> -->
                    <?= form_open('admin/tax/edit_taxes', ['method' => "post", 'class' => 'form-submit-event', 'id' => 'edit_taxes', 'enctype' => "multipart/form-data"]); ?>
                    <input type="hidden" name="id" id="id">
                    <div class="form-group">
                        <label for="title">title</label>
                        <input id="edit_title" class="form-control" type="text" name="title" placeholder="Enter the title here">
                    </div>
                    <div class="form-group">
                        <label for="title">Percentage</label>
                        <input id="edit_percentage" class="form-control" type="number" name="percentage" placeholder="Enter the percentage here">
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input id="status_edit" class="custom-control-input" type="checkbox" name="tax_status_edit" checked>
                            <label for="status_edit" class="custom-control-label">
                                <span id="tax_status_edit">
                                    Enable
                                </span>
                            </label>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" name="submit">Save changes</button>
                    <?php form_close() ?>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


</div>