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
            <h1> <?= labels('api_key_settings', 'API Key Settings') ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item "><a href="<?= base_url('/admin/settings/system-settings') ?>"><?= labels('system_settings', "System Settings") ?></a></div>
                <div class="breadcrumb-item"> <?= labels('api_key_settings', 'API Key Settings') ?></div>
            </div>
        </div>
        <form action="<?= base_url('admin/settings/api_key_settings') ?>" method="post">
            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
            <div class="container-fluid card ">
                <div class="row">
                    <div class="col " style="border-bottom: solid 1px #e5e6e9;">
                        <div class='toggleButttonPostition '><?= labels('api_key_settings', 'Api Key Settings') ?></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col mb-3">
                        <div class='toggleButttonPostition text-new-primary'><?= labels('client_api_key_settings', ' Client API Keys') ?></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="google_map_api"><?= labels('API_link_for_customer_app', 'API link for Customer App') ?> </label>
                            <div class="input-group">
                                <input id="API_link_for_customer_app" class="form-control" type="text" name="API_link_for_customer_app" value="<?= base_url('api/v1/'); ?>" readonly>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('API_link_for_customer_app')">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="text-danger">( <?= labels('use_this_link_as_your_API_link_in_apps_code', 'Use this link as your API link in App\'s code') ?> )</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="google_map_api"><?= labels('API_link_for_provider_app', 'API link for Provider App ') ?> </label>
                            <div class="input-group">
                                <input id="API_link_for_provider_app" class="form-control" type="text" name="API_link_for_provider_app" value="<?= base_url('/partner/api/v1/') ; ?>" readonly>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('API_link_for_provider_app')">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="text-danger">( <?= labels('use_this_link_as_your_API_link_in_providers_app_code', 'Use this link as your API link in Provider\'s App code') ?> )</small>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class='toggleButttonPostition text-new-primary'><?= labels('google_API_key_for_map', 'Google API key for map') ?></div>
                        <div class="form-group">
                            <label for="google_map_api"><?= labels('google_API_key_for_map', 'Google API key for map') ?></label>
                            <div class="input-group">
                                <input id="google_map_api" class="form-control" type="text" name="google_map_api" value="<?= isset($google_map_api) ? trim($google_map_api) : 'Enter API key' ?>">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('google_map_api')">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                </div>
                <div class="row mt-3">
                    <?php if ($permissions['update']['settings'] == 1) : ?>

                        <div class="col-md d-flex justify-content-end">
                            <div class="form-group">
                                <input type='submit' name='update' id='update' value='<?= labels('save_changes', "Save Changes") ?>' class='btn bg-new-primary' />
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </form>
    </section>
</div>