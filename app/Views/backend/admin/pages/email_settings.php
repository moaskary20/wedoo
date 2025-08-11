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
            <h1><?= labels('email_settings', 'Email Settings') ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>

                <div class="breadcrumb-item "><a href="<?= base_url('/admin/settings/system-settings') ?>"><?= labels('system_settings', "System Settings") ?></a></div>

                <div class="breadcrumb-item"><?= labels('email_settings', 'Email Settings') ?></div>
            </div>
        </div>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="google-map">
                <div class="col-sm-6">
                    <div class="card">

                        <div class="row border_bottom_for_cards m-0">
                            <div class="col">
                                <div class="toggleButttonPostition"><?= labels('email_settings', "Email Settings") ?></div>
                            </div>
                        </div>
                        <div class="container-fluid  pt-3">

                            <form name='email_settings' id='ESForm' action="<?= base_url('admin/settings/email-settings') ?>" method='get'>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for='mailProtocol'><?= labels('mail_protocol', "Mail Protocol") ?></label>
                                            <input type='text' class="form-control" name='mailProtocol' id='mailProtocol' value='SMTP' readonly />
                                        </div>
                                    </div>
                                    <div class="col-md-6 ">
                                        <div class="form-group">
                                            <label for='smtpPort'><?= labels('SMTP_port_number', "SMTP Port Number") ?></label>
                                            <input type='text' class="form-control" name='smtpPort' id='smtpPort' placeholder="Port number of your SMTP host" value="<?= isset($smtpPort) ? $smtpPort : '' ?>" />
                                        </div>
                                    </div>

                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for='smtpHost'><?= labels('SMTP_host', "SMTP Host") ?></label>
                                            <input type='text' class="form-control" name='smtpHost' id='smtpHost' placeholder="eg. smtp.google.com" value="<?= isset($smtpHost) ? $smtpHost : '' ?>" />
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for='smtpEncryption'><?= labels('mail_encryption', "Mail Encryption") ?></label>
                                            <select class='form-control selectric' name='smtpEncryption' id='smtpEncryption'>
                                                <option value='off' <?= isset($smtpEncryption) && $smtpEncryption === 'off' ? 'selected' : '' ?>>Off</option>
                                                <option value='ssl' <?= isset($smtpEncryption) && $smtpEncryption === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                                <option value='tls' <?= isset($smtpEncryption) && $smtpEncryption === 'tls' ? 'selected' : '' ?>>TLS</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for='smtpUsername'><?= labels('SMTP_username', "SMTP Username") ?></label>
                                            <input type='email' class="form-control" name='smtpUsername' id='smtpUsername' placeholder="eg. example@gmail.com" value="<?= isset($smtpUsername) ? $smtpUsername : '' ?>" />
                                        </div>
                                    </div>
                                    <div class="col-md-6 ">
                                        <div class="form-group">
                                            <label for='mailType'><?= labels('choose_mail_type', "Choose Mail Type") ?></label>
                                            <select class='form-control selectric' name='mailType' id='mailType'>
                                                <option value='text' <?= isset($mailType) && $mailType === 'text' ? 'selected' : '' ?>>Text</option>
                                                <option value='html' <?= isset($mailType) && $mailType === 'html' ? 'selected' : '' ?>>HTMl</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for='smtpPassword'><?= labels('SMTP_password', "SMTP Password") ?></label>
                                            <input type='password' class="form-control" name='smtpPassword' id='smtpPassword' placeholder="Mail account password" value="<?= isset($smtpPassword) ? $smtpPassword : '' ?>" />
                                        </div>
                                    </div>

                                    <?php if ($permissions['update']['settings'] == 1) : ?>

                                        <div class="col-md-6 d-flex justify-content-end mt-5">
                                            <div class="form-group">

                                                <input type='submit' name='update' id='update' value='<?= labels('save_changes', "Update") ?>' class='btn btn-primary' />
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