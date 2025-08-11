<div class="main-content">
    <section class="section">
        <div class="section-header mt-3">
            <h1><?= labels('add_system_user', 'Add System Users') ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item"> <a href="<?= base_url('admin/system_users') ?>"> <?= labels('system_user', 'System Users') ?></a></div>
                <div class="breadcrumb-item"><?= labels('add_system_user', 'Add System Users') ?></a></div>
            </div>
        </div>

        <div class="section-body">
            <div class="container-fluid card">
                <h2 class='section-title'><?= labels('add_system_user', "Add System Users") ?></h2>

                <form action="<?= base_url('admin/system_users/permit') ?>" method="post" class="form-submit-event" id="system_user_form">
                    <div class="row">
                        <div class="col-md-3">

                            <div class="row" id="">
                                <div class="col-md">
                                    <div class="row">
                                        <div class="col-md">
                                            <div class="form-group">
                                                <label for="new_user_name"  class="required"><?= labels('user_name', 'User Name') ?></label>
                                                <input id="name" class="form-control" type="text" name="new_user_name" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md">
                                            <div class="form-group">
                                                <label for="mobile"  class="required"><?= labels('phone_number', 'Phone Number') ?></label>
                                                <input id="mobile" class="form-control" type="number" name="phone" min="0" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md">
                                            <div class="form-group">
                                                <label for="email"  class="required"><?= labels('email', 'E-mail') ?></label>
                                                <input id="email" class="form-control" type="email" name="mail" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md">
                                            <div class="form-group" >
                                                <label for="password"  class="required"><?= labels('password', 'Password') ?></label>
                                                <input id="password" class="form-control" type="password" name="password" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md">
                                            <div class="form-group">
                                                <label for="confirm_password"  class="required"><?= labels('confirm_password', 'Confirm Password') ?></label>
                                                <input id="confirm_password" class="form-control" type="password" name="confirm_password" required>
                                            </div>
                                        </div>

                                    </div>


                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="role"  class="required"><?= labels('role', 'Role') ?></label>
                                        <select id="role" class="form-control" name="role" required>

                                            <option value="3"><?= labels('editor', 'Editor') ?></option>
                                            <option value="1"><?= labels('super_admin', 'Super Admin') ?> </option>
                                            <option value="2"><?= labels('admin', 'Admin') ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 ">
                                    <div class="form-group" >
                                    <label for="role"  class="required"><?= labels('status', 'Status') ?></label><br>
                                        <input type="checkbox" class="status-switch" name="is_approved" checked>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md">
                                    <div class="form-group">
                                        <input type="submit" value="<?= labels('submit', 'Submit') ?>" class="btn btn-primary">
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-9" id="permissions">
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
                                                    <?= ucfirst($perms) ?>
                                                </td>

                                                <td class="align-baseline">
                                                    <?php
                                                    $i;
                                                    $cust_id =  $perms . "_create"
                                                    ?>
                                                    <?php for ($i = 0; $i < count($value); $i++) : ?>
                                                        <?php if ($value[$i] == "create") : ?>
                                                            <div class="custom-control custom-switch">
                                                                <input id="<?= $cust_id ?>" class="custom-control-input" type="checkbox" name="<?= $cust_id ?>" value="true" checked>
                                                                <label for="<?= $cust_id ?>" class="custom-control-label">
                                                                </label>
                                                            </div>
                                                        <?php endif; ?>
                                                    <?php endfor; ?>
                                                </td>
                                                <td class="align-baseline">
                                                    <?php
                                                    $cust_id_read =  $perms . "_read";
                                                    ?>

                                                    <?php for ($i = 0; $i < count($value); $i++) : ?>
                                                        <?php if ($value[$i] == "read") : ?>
                                                            <div class="custom-control custom-switch">
                                                                <input id="<?= $cust_id_read ?>" class="custom-control-input" type="checkbox" name="<?= $cust_id_read ?>" value="true" checked>
                                                                <label for="<?= $cust_id_read ?>" class="custom-control-label">
                                                                </label>
                                                            </div>
                                                        <?php endif; ?>
                                                    <?php endfor; ?>
                                                </td>
                                                <td class="align-baseline">

                                                    <?php $cust_id_update =  $perms . "_update" ?>
                                                    <?php for ($i = 0; $i < count($value); $i++) : ?>
                                                        <?php if ($value[$i] == "update") : ?>
                                                            <div class="custom-control custom-switch">
                                                                <input id="<?= $cust_id_update ?>" class="custom-control-input" type="checkbox" name="<?= $cust_id_update ?>" value="true" checked>
                                                                <label for="<?= $cust_id_update ?>" class="custom-control-label">
                                                                </label>
                                                            </div>
                                                        <?php else : ?>
                                                        <?php endif ?>
                                                        <?php ?>
                                                    <?php endfor ?>

                                                </td>

                                                <td class="align-baseline">
                                                    <?php $cust_id_delete =  $perms . "_delete"  ?>
                                                    <?php for ($i = 0; $i < count($value); $i++) : ?>
                                                        <?php if ($value[$i] == "delete") : ?>
                                                            <div class="custom-control custom-switch">
                                                                <input id="<?= $cust_id_delete ?>" class="custom-control-input" type="checkbox" name="<?= $cust_id_delete ?>" value="true" checked>
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

                </form>
            </div>
        </div>
    </section>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('[name*="_read"]').change(function() {
            var readCheckbox = $(this);
            var prefix = readCheckbox.attr('name').replace('_read', '');

            var createCheckbox = $('#' + prefix + '_create');
            var updateCheckbox = $('#' + prefix + '_update');
            var deleteCheckbox = $('#' + prefix + '_delete');

            if (!readCheckbox.is(':checked')) {
                createCheckbox.prop('checked', false).prop('disabled', true);
                updateCheckbox.prop('checked', false).prop('disabled', true);
                deleteCheckbox.prop('checked', false).prop('disabled', true);
            } else {
                createCheckbox.prop('disabled', false);
                updateCheckbox.prop('disabled', false);
                deleteCheckbox.prop('disabled', false);
            }
        });
    });
</script>