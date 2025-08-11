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
        <div class="section-header mt-3">
            <h1><?= labels('notification_settings', 'Notification Settings') ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item "><a href="<?= base_url('/admin/settings/system-settings') ?>"><?= labels('system_settings', "System Settings") ?></a></div>
                <div class="breadcrumb-item"><?= labels('notification_settings', 'Notification Settings') ?></a></div>
            </div>
        </div>
        <div class="section-body">
            <div class=" card">
                <div class="card-body p-0">
                    <div class="row">
                        <div class="col-md-12" id="permissions">
                            <form action="<?= base_url('admin/settings/notification_setting_update') ?>" method="post" class="" id="notification_setting_update">
                                <div class="table-responsive">
                                    <div class="table-responsive">
                                        <div class="table-responsive">
                                            <table class="table permission-table">
                                                <thead>
                                                    <tr>
                                                        <th class="p-3"><?= labels('module', 'Module') ?></th>
                                                        <th class="p-3"><?= labels('email', 'Email') ?></th>
                                                        <th class="p-3"><?= labels('sms', 'SMS') ?></th>
                                                        <th class="p-3"><?= labels('notification', 'Notification') ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($notification_settings as $module) : ?>
                                                        <tr>
                                                            <td><a href="<?= base_url('admin/settings/sms-email-preview/'.$module); ?>"><?= ucwords(str_replace('_', ' ', $module)) ?></td></a>
                                                            <td class="align-baseline">
                                                                <div class="custom-control custom-switch">
                                                                    <input id="<?= $module ?>_email" class="custom-control-input" type="checkbox" name="<?= $module ?>_email" value="true">
                                                                    <label for="<?= $module ?>_email" class="custom-control-label"></label>
                                                                </div>
                                                            </td>
                                                            <td class="align-baseline">
                                                                <div class="custom-control custom-switch">
                                                                    <input id="<?= $module ?>_sms" class="custom-control-input" type="checkbox" name="<?= $module ?>_sms" value="true">
                                                                    <label for="<?= $module ?>_sms" class="custom-control-label"></label>
                                                                </div>
                                                            </td>
                                                            <td class="align-baseline">
                                                                <div class="custom-control custom-switch">
                                                                    <input id="<?= $module ?>_notification" class="custom-control-input" type="checkbox" name="<?= $module ?>_notification" value="true">
                                                                    <label for="<?= $module ?>_notification" class="custom-control-label"></label>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($permissions['update']['settings'] == 1) : ?>
                                <div class="row p-3">
                                    <div class="col-md d-flex justify-content-lg-end m-1">
                                        <div class="form-group">
                                            <input type='submit' name='update' id='update' value='<?= labels('save_changes', "Save Changes") ?>' class='btn btn-primary' />
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<script>
    $(document).ready(function() {
        var current_settings = <?= json_encode($current_settings); ?>;
        $.each(current_settings, function(key, value) {
            if (value) {
                $("#" + key).prop("checked", true);
            }
        });
    });
</script>