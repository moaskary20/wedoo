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
      <h1>System Updater</h1> &ensp; &ensp;
      <span class="badge badge-primary">
        <?php foreach ($version as $ver) : ?>
          <?= $ver->version ?>
        <?php endforeach; ?>
      </span>
      <div class="section-header-breadcrumb">
        <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
        <div class="breadcrumb-item "><a href="<?= base_url('/admin/settings/system-settings') ?>"><?= labels('system_settings', "System Settings") ?></a></div>
        <div class="breadcrumb-item"><?= labels('system_updater', 'System Updater') ?></div>
      </div>
    </div>

  </section>
  <section class="content">
    <div class="container-fluid">

      <div class="alert alert-danger col-md-8">
        <div class="alert-title"><?= labels('note', 'Note') ?>:</div>
        <?= labels('note_system_updater', 'Make sure you update system in sequence. Like if you have current version 1.0 and you want to update this version to 1.5 then you can\'t update it directly. You must have to update in sequence like first update version 1.2 then 1.3 and 1.4 so on.') ?>
        <div class="mt-2">
          <div class="alert-title"><?= labels('instructions', 'Instructions') ?>:</div>
          <ul class="pl-3 d-flex flex-column gap-2 instructions-list">
            <li class="list-unstyled">

              1. <?= labels('system_update_instruction_one', 'Please make sure, Your server php \"upload_max_filesize\" Value is greater
              or equal to 80MB. Current value is') ?>
              - <?= print_r(ini_get("upload_max_filesize")); ?>
            </li>
            <li class="list-unstyled">
              2. <?= labels('system_update_instruction_two', 'Please make sure, Your server php \"post_max_size\" Value is greater or equal to 80MB. Current value is') ?> - <?= print_r(ini_get("post_max_size")); ?>
            </li>
          </ul>
        </div>
      </div>



      <div class="row">
        <div class="col-md-8">
          <div class="card card-info">

            <div class="col-md">

              <form class="form-horizontal form-submit-event" action="<?= base_url('admin/upload_update_file') ?>" method="POST" enctype="multipart/form-data">




                <div class="card-body">

                  <div class="form-group" style="padding-left: 0;">
                    <label for="purchase_code"><?= labels('purchase_code', 'Purchase Code') ?></label>
                    <input class="form-control" type="text" name="purchase_code" id="purchase_code">
                  </div>


                  <div class="dropzone dz-clickable" id="system-update-dropzone">
                  </div>

                  <?php if ($permissions['update']['system_update'] == 1) : ?>

                    <div class="form-group justify-content-end d-flex" style="margin-top: 25px;margin-bottom: 25px">
                      <button class="btn btn-primary" id="system_update_btn"><?= labels('update_the_system', 'Update The System') ?></button>
                    </div>
                  <?php endif; ?>

                </div>

                <div class="d-flex justify-content-center">
                  <div class="form-group" id="error_box">
                  </div>
                </div>
            </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<script>
  var purchase_code = document.getElementById('purchase_code');
</script>