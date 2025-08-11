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
            <h1><?= labels('firebase_settings', "Firebase Settings") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item "><a href="<?= base_url('/admin/settings/system-settings') ?>"><?= labels('system_settings', "System Settings") ?></a></div>

                <div class="breadcrumb-item"> <?= labels('firebase_settings', 'Firebase Settings') ?></div>
            </div>
        </div>
        <form action="<?= base_url('admin/settings/firebase_settings') ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
            <div class="container-fluid card pt-3">

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="google_map_api"><?= labels('apiKey', 'apiKey') ?><span class="text-danger text-xs">*</span> </label>
                            <input id="apiKey" class="form-control" type="text" name="apiKey" value="<?= isset($apiKey) ? $apiKey : '0' ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="google_map_api"><?= labels('authDomain', 'authDomain') ?><span class="text-danger text-xs">*</span> </label>
                            <input id="authDomain" class="form-control" type="text" name="authDomain" value="<?= isset($authDomain) ? $authDomain : 0 ?>">
                        </div>
                    </div>

                </div>


                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="google_map_api"><?= labels('projectId', 'projectId ') ?><span class="text-danger text-xs">*</span> </label>
                            <input id="projectId" class="form-control" type="text" name="projectId" value="<?= isset($projectId) ? $projectId : 0 ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="google_map_api"><?= labels('storageBucket', 'storageBucket ') ?><span class="text-danger text-xs">*</span> </label>
                            <input id="storageBucket" class="form-control" type="text" name="storageBucket" value="<?= isset($storageBucket) ? $storageBucket : 0 ?>">
                        </div>
                    </div>
                </div>


                <div class="row">

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="google_map_api"><?= labels('messagingSenderId', 'messagingSenderId') ?><span class="text-danger text-xs">*</span> </label>
                            <input id="messagingSenderId" class="form-control" type="text" name="messagingSenderId" value="<?= isset($messagingSenderId) ? $messagingSenderId : 0 ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="google_map_api"><?= labels('appId', 'appId') ?><span class="text-danger text-xs">*</span> </label>
                            <input id="appId" class="form-control" type="text" name="appId" value="<?= isset($appId) ? $appId : 0 ?>">
                        </div>
                    </div>

                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="google_map_api"><?= labels('measurementId', 'measurementId ') ?><span class="text-danger text-xs">*</span> </label>
                            <input id="measurementId" class="form-control" type="text" name="measurementId" value="<?= isset($measurementId) ? $measurementId : 0 ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="google_map_api"><?= labels('vapidKey', 'vapidKey ') ?><span class="text-danger text-xs">*</span> </label>
                            <input id="vapidKey" class="form-control" type="text" name="vapidKey" value="<?= isset($vapidKey) ? $vapidKey : 0 ?>">
                        </div>
                    </div>

                </div>



                <div class="col-md-6">
                    <div class="form-group row">
                        <div class="col-md-12 col-sm-12">
                            <?php
                            $target_path = FCPATH . 'public';
                            $file_path = $target_path . '/firebase_config.json';
                            $is_file = file_exists($file_path) ? 1 : 0;
                            ?>



                            <label class="control-label"><?= labels('current_file_status', 'Current File Status') ?> : <?= ($is_file) ? '<small class="badge badge-success">File Exists</small>' : '<small class="badge badge-danger">File Not Exists, Please Upload</small>' ?></label>
                        </div>
                    </div>
                    <div class="form-group"> <label for="image" class="required"><?= labels('upload_file', 'Upload File') ?></label>
                        <input type="file" name="json_file" class="filepond logo" id="json_file" accept="application/json">
                    </div>
                </div>



                <div class="row mt-3">
                <?php if ($permissions['update']['settings'] == 1) : ?>

                    <div class="col-md d-flex justify-content-end ">
                        <div class="form-group">
                            <input type='submit' name='update' id='update' value='<?= labels('save_changes', "Save Changes") ?>' class='btn bg-new-primary' />
                        </div>
                    </div>
                </div>

                <?php endif; ?>

            </div>
        </form>

        <div class="card">

            <div class="card-body">
                <ol>
                    <li><?= labels('open', 'Open') ?> <a href="https://console.firebase.google.com/project/_/settings/serviceaccounts/adminsdk" target="_blank">https://console.firebase.google.com/project/_/settings/serviceaccounts/adminsdk</a><?= labels('select_project_generate_private_key', 'and select the project you want to generate a private key file for.') ?> </li>
                    <li><?= labels('generate_new_private_key_confirm_upload_json', 'Click Generate New Private Key, then confirm by clicking Generate Key, and finally <b>upload the generated .json file.') ?></b>
                        <img src="../../public/backend/assets/images/generate-key.png" alt="Image" width="100%">
                    </li>
                </ol>

            </div>

        </div>
    </section>
</div>