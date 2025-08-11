<?php
// $user1 = fetch_details('users', ["phone" => $_SESSION['identity']],);

$db      = \Config\Database::connect();
$builder = $db->table('users u');
$builder->select('u.*,ug.group_id')
    ->join('users_groups ug', 'ug.user_id = u.id')
    ->where('ug.group_id', 1)
    ->where(['phone' => $_SESSION['identity']]);
$user1 = $builder->get()->getResultArray();

$permissions1 = get_permission($user1[0]['id']);
?>
<div class="main-content">
    <section class="section">
        <div class="section-header mt-3">
            <h1><?= labels('system_user', "System Users") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item"><?= labels('add_system_user', 'Add System Users') ?></a></div>
            </div>
        </div>

        <div class="section-body">
            <div class="container-fluid card">

                <div class="row">
                    <div class="col-md">



                        <div class="row">

                            <div class="col-lg">
                                <div class="row ">
                                    <div class="col-12">
                                        <div class="col-md">

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

                                                <div class="dropdown d-inline ml-2">
                                                    <button class="btn export_download dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <?= labels('download', 'Download') ?>
                                                    </button>
                                                    <div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 28px, 0px); top: 0px; left: 0px; will-change: transform;">
                                                        <a class="dropdown-item" onclick="custome_export('pdf','System User list','system_user_list');"> <?= labels('pdf', 'PDF') ?></a>
                                                        <a class="dropdown-item" onclick="custome_export('excel','System User list','system_user_list');"> <?= labels('excel', 'Excel') ?></a>
                                                        <a class="dropdown-item" onclick="custome_export('csv','System User list','system_user_list')"> <?= labels('csv', 'CSV') ?></a>
                                                    </div>
                                                </div>


                                                <?php if ($permissions1['create']['system_user'] == 1) { ?>
                                                    <div class="col col d-flex justify-content-end">
                                                        <div>
                                                            <a href="<?= base_url('/admin/system_users/add_user') ?>" class="btn btn-primary float-right"><?= labels('add_system_user', 'Add System Users') ?></a>

                                                        </div>
                                                    </div>
                                                <?php } ?>
                                            </div>



                                            <table class="table " data-fixed-columns="true" data-pagination-successively-size="2" id="system_user_list" data-auto-refresh="true" data-toggle="table" data-url="<?= base_url("admin/system_users/list") ?>" data-query-params="system_user_query_params" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-search="false" data-show-columns="false" data-show-columns-search="true" data-show-refresh="false" data-sort-name="id" data-sort-order="desc">
                                                <thead>
                                                    <tr>
                                                        <th data-field="id" class="text-center" data-visible="true" data-sortable="true"><?= labels('id', 'ID') ?></th>
                                                        <th data-field="username" class="text-center" data-visible="true" data-sortable="true"><?= labels('user_name', 'User Name') ?></th>
                                                        <th data-field="email" class="text-center" data-visible="true"><?= labels('email', 'E-Mail') ?></th>
                                                        <th data-field="role" class="text-center" data-visible="true" data-sortable="true"><?= labels('role', 'Role') ?></th>

                                                        <th data-field="operations" class="text-center" data-visible="true" data-events="system_user_events">
                                                            <?= labels('operations', 'Operations') ?>
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
                </div>
            </div>
        </div>


    </section>


</div>


<div class="modal fade" id="edit_permission" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle"><?= labels('edit_permission_model', 'Edit permission Model') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="<?= base_url('admin/system_users/edit_permit') ?>" method="post" class="form-submit-event" id="edit-permit">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md">
                                <input type="hidden" name="id" id="id">
                                <div class="form-group">
                                    <label for="edit_role"><?= labels('edit_role', 'Edit Role') ?></label>
                                    <select id="edit_role" class="form-control" name="edit_role">
                                        <option value="default"><?= labels('select_role', 'Select Role') ?> </option>
                                        <option value="1"><?= labels('super_admin', 'Super Admin') ?> </option>
                                        <option value="2"><?= labels('admin', 'Admin') ?></option>
                                        <option value="3"><?= labels('editor', 'Editor') ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row" id="permissions">
                        <div class="col-md">
                            <div class="table-responsive">
                                <table class="table permission-table">
                                    <tbody>
                                        <tr>
                                            <th><?= labels('module_permissions', 'Module/Permissions') ?></th>
                                            <th><?= labels('create', 'Create') ?></th>
                                            <th><?= labels('read', 'Read') ?></th>
                                            <th><?= labels('update', 'Update') ?></th>
                                            <th><?= labels('delete', 'Delete') ?></th>
                                        </tr>
                                    </tbody>
                                    <tbody>
                                        <?php foreach ($permissions as $perms => $value) : ?>


                                            <tr>
                                                <td>

                                                    <?= labels($perms, ucfirst($perms)) ?>

                                            
                                                </td>

                                                <td class="align-baseline">
                                                    <?php
                                                    $i;
                                                    $cust_id =  $perms . "_create_edit"
                                                    ?>
                                                    <!-- <?= $cust_id ?> -->
                                                    <?php for ($i = 0; $i < count($value); $i++) : ?>
                                                        <?php if ($value[$i] == "create") : ?>
                                                            <div class="custom-control custom-switch">
                                                                <input id="<?= $cust_id ?>" class="custom-control-input" type="checkbox" name="<?= $cust_id ?>" value="true">
                                                                <label for="<?= $cust_id ?>" class="custom-control-label">
                                                                </label>
                                                            </div>
                                                        <?php endif; ?>
                                                    <?php endfor; ?>
                                                </td>
                                                <td class="align-baseline">
                                                    <?php
                                                    $cust_id_read =  $perms . "_read_edit";
                                                    ?>
                                                    <!-- <?= $cust_id_read ?> -->
                                                    <?php for ($i = 0; $i < count($value); $i++) : ?>
                                                        <?php if ($value[$i] == "read") : ?>
                                                            <div class="custom-control custom-switch">
                                                                <input id="<?= $cust_id_read ?>" class="custom-control-input" type="checkbox" name="<?= $cust_id_read ?>" value="true">
                                                                <label for="<?= $cust_id_read ?>" class="custom-control-label">
                                                                </label>
                                                            </div>
                                                        <?php endif; ?>
                                                    <?php endfor; ?>
                                                </td>
                                                <td class="align-baseline">

                                                    <?php $cust_id_update =  $perms . "_update_edit" ?>
                                                    <!-- <?= $cust_id_update ?> -->
                                                    <?php for ($i = 0; $i < count($value); $i++) : ?>
                                                        <?php if ($value[$i] == "update") : ?>
                                                            <div class="custom-control custom-switch">
                                                                <input id="<?= $cust_id_update ?>" class="custom-control-input" type="checkbox" name="<?= $cust_id_update ?>" value="true">
                                                                <label for="<?= $cust_id_update ?>" class="custom-control-label">
                                                                </label>
                                                            </div>
                                                        <?php else : ?>
                                                        <?php endif ?>
                                                        <?php ?>
                                                    <?php endfor ?>

                                                </td>

                                                <td class="align-baseline">
                                                    <?php $cust_id_delete =  $perms . "_delete_edit"  ?>
                                                    <!-- <?= $cust_id_delete ?> -->
                                                    <?php for ($i = 0; $i < count($value); $i++) : ?>
                                                        <?php if ($value[$i] == "delete") : ?>
                                                            <div class="custom-control custom-switch">
                                                                <input id="<?= $cust_id_delete ?>" class="custom-control-input" type="checkbox" name="<?= $cust_id_delete ?>" value="true">
                                                                <label for="<?= $cust_id_delete ?>" class="custom-control-label">
                                                                </label>
                                                            </div>
                                                        <?php else : ?>
                                                        <?php endif ?>
                                                        <?php ?>
                                                    <?php endfor ?>
                                                    <!--  -->
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><?= labels('save_changes', 'Save Changes') ?></button>
                </form>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= labels('close', 'Close') ?></button>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Change event handler for the read permission checkboxes
        $('[name*="_read_edit"]').change(function() {


            var readCheckbox = $(this);
            var prefix = readCheckbox.attr('name').replace('_read_edit', '');

            // Find corresponding create, update, and delete checkboxes
            var createCheckbox = $('#' + prefix + '_create_edit');
            var updateCheckbox = $('#' + prefix + '_update_edit');
            var deleteCheckbox = $('#' + prefix + '_delete_edit');

            // If read permission is turned off, disable other checkboxes
            if (!readCheckbox.is(':checked')) {
                createCheckbox.prop('checked', false).prop('disabled', true);
                updateCheckbox.prop('checked', false).prop('disabled', true);
                deleteCheckbox.prop('checked', false).prop('disabled', true);
            } else {
                // If read permission is turned on, enable other checkboxes
                createCheckbox.prop('disabled', false);
                updateCheckbox.prop('disabled', false);
                deleteCheckbox.prop('disabled', false);
            }
        });
    });
</script>

<script>
    $("#customSearch").on('keydown', function() {
        $('#system_user_list').bootstrapTable('refresh');
    });
</script>