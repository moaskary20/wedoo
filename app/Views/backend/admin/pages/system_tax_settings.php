<!-- Main Content -->
<?php
$db      = \Config\Database::connect();
$builder = $db->table('users u');
$builder->select('u.*,ug.group_id')
    ->join('users_groups ug', 'ug.user_id = u.id')
    ->where('ug.group_id', 1)
    ->where(['phone' => $_SESSION['identity']]);
$user1 = $builder->get()->getResultArray();
$permissions = get_permission($user1[0]['id']);
?>
<div class="main-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('taxes', "Taxes") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item "><a href="<?= base_url('/admin/settings/system-settings') ?>"><?= labels('system_settings', "System Settings") ?></a></div>

                <div class="breadcrumb-item"><?= labels('manage_taxes', "Manage Taxes") ?></a></div>
            </div>
        </div>

        <div class="row">

            <div class="col-md-4">
                <div class=" card">
                    <?= helper('form'); ?>

                    <h1></h1>
                    <div class="row border_bottom_for_cards m-0">
                        <div class="col">
                            <div class="toggleButttonPostition"><?= labels('manage_taxes', "Manage Taxes") ?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <?= form_open('/admin/tax/add_tax', ['method' => "post", 'class' => 'form-submit-event', 'id' => 'add', 'enctype' => "multipart/form-data"]); ?>

                        <div class="form-group">
                            <label for="title"><?= labels('title', "Title") ?></label>
                            <input id="title" class="form-control" type="text" name="title" placeholder="<?= labels('enter_title_here', 'Enter the title here') ?>">
                        </div>
                        <div class="form-group">
                            <label for="percentage"><?= labels('percentage', "Percentage") ?></label>
                            <input id="percentage" class="form-control" type="number" name="percentage" placeholder="<?= labels('enter_percentage_here', 'Enter the percentage here') ?>" step="0.01">
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
                        <div class=" d-flex justify-content-end">

                            <button type="submit" class="btn btn-primary submit_btn"><?= labels('add_tax', "Add Tax") ?></button>
                        </div>

                        <?= form_close(); ?>
                    </div>
                </div>
            </div>
            <?php if ($permissions['read']['tax'] == 1) : ?>

                <div class="col-md-8">

                    <div class=" card">
                        <div class="row">
                            <div class="col-lg">

                                <div class="row border_bottom_for_cards m-0">
                                    <div class="col">
                                        <div class="toggleButttonPostition"><?= labels('taxes', "Taxes") ?></div>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <div class="row mt-2">
                                        <div class="col-md-12">


                                            <div class="row pb-3 ">
                                                <div class="col-12">
                                                    <div class="row mb-3 ">

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


                                                        <div class="dropdown d-inline ml-2">
                                                            <button class="btn export_download dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <?= labels('download', 'Download') ?>
                                                            </button>
                                                            <div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 28px, 0px); top: 0px; left: 0px; will-change: transform;">
                                                                <a class="dropdown-item" onclick="custome_export('pdf','Tax list','user_list');"> <?= labels('pdf', 'PDF') ?></a>
                                                                <a class="dropdown-item" onclick="custome_export('excel','Tax list','user_list');"> <?= labels('excel', 'Excel') ?></a>
                                                                <a class="dropdown-item" onclick="custome_export('csv','Tax list','user_list')"> <?= labels('csv', 'CSV') ?></a>
                                                            </div>
                                                        </div>


                                                    </div>

                                                </div>
                                            </div>
                                            <table class="table" data-pagination-successively-size="2" data-query-params="system_tax_query_params" id="user_list" data-detail-formatter="user_formater" data-auto-refresh="true" data-toggle="table" data-url="<?= base_url("admin/tax/list") ?>" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-search="false" data-show-columns="false" data-show-columns-search="true" data-show-refresh="false" data-sort-name="id" data-sort-order="desc">
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

                                            <!-- </div> -->
                                        </div>
                                        <!-- </div> -->
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            <?php endif; ?>
        </div>

    </section>
    <!-- update modal -->
    <div class="modal fade" id="update_modal" tabindex="-1" aria-labelledby="update_modal_thing" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"><?= labels('edit', 'Edit') ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- <form action="" method="post"> -->
                    <?= form_open('admin/tax/edit_taxes', ['method' => "post", 'class' => 'form-submit-event', 'id' => 'edit_taxes', 'enctype' => "multipart/form-data"]); ?>
                    <input type="hidden" name="id" id="id">
                    <div class="form-group">
                        <label for="title"><?= labels('title', "Title") ?></label>
                        <input id="edit_title" class="form-control" type="text" name="title" placeholder="Enter the title here">
                    </div>
                    <div class="form-group">
                        <label for="title"><?= labels('percentage', "Percentage") ?></label>
                        <input id="edit_percentage" class="form-control" type="number" name="percentage" step="0.01" placeholder="Enter the percentage here">
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input id="status_edit" class="custom-control-input" type="checkbox" name="tax_status_edit" checked>
                            <label for="status_edit" class="custom-control-label">
                                <span id="tax_status_edit">
                                    <?= labels('enable', 'Enable') ?>
                                </span>
                            </label>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary submit_btn" name="submit"><?= labels('save_changes', 'Save Changes') ?></button>
                    <?php form_close() ?>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= labels('close', 'Close') ?></button>
                </div>
            </div>
        </div>
    </div>


</div>


<script>
    $("#customSearch").on('keydown', function() {
        $('#user_list').bootstrapTable('refresh');
    });
</script>