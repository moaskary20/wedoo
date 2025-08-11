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
    <section class="section" id="pill-about_us" role="tabpanel">
        <div class="section-header mt-2">
            <h1> <?= labels('about_us', "About Us") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item "><a href="<?= base_url('/admin/settings/system-settings') ?>"><?= labels('system_settings', "System Settings") ?></a></div>
                <div class="breadcrumb-item"><?= labels('about_us', 'About us') ?></div>
            </div>
        </div>
        <div class="">
            <!-- tab section -->
            <ul class="justify-content-start nav nav-fill nav-pills pl-3 py-2 setting" id="gen-list">
                <div class="row">
                    <li class="nav-item">
                        <a class="nav-link " aria-current="page" href="<?= base_url('admin/settings/general-settings') ?>" id="pills-general_settings-tab" aria-selected="true">
                            <?= labels('general_settings', "General Settings") ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="<?= base_url('admin/settings/about-us') ?>" id="pills-about_us" aria-selected="false">
                            <?= labels('about_us', "About Us") ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('admin/settings/contact-us') ?>" id="pills-about_us" aria-selected="false">
                            <?= labels('support_details', "Support Details") ?></a>
                    </li>
                </div>
            </ul>
            <!-- tab section ends here -->
        </div>
        <form action="<?= base_url('admin/settings/about-us') ?>" method="post">
            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
            <div class="container-fluid card p-3">
                <div class="row">
                    <div class="col-lg">
                        <textarea rows=50 class='form-control h-50 summernotes' name="about_us"><?= isset($about_us) ? $about_us : 'Enter About Us.' ?></textarea>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6 mt-3 mb-4">
                        <a href="<?= base_url('admin/settings/about-us-preview'); ?>" class="btn btn-primary"><i class="fa fa-eye"></i> <?= labels('preview', 'Preview') ?></a>
                    </div>
                    <?php if ($permissions['update']['settings'] == 1) : ?>

                        <div class="col-md-6 mt-3  justify-content-end d-flex">
                            <div class="form-group">
                                <input type='submit' name='update' id='update' value='<?= labels('save_changes', "Update") ?>' class='btn btn-primary' />
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </section>
</div>