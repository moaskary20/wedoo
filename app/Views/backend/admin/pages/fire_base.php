<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1><?= labels('firebase_settings', "Firebase Settings") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-cog  text-info"></i> <?= labels('settings', 'Settings') ?></a></div>
                <div class="breadcrumb-item"> <?= labels('firebase_settings', 'Firebase Settings') ?></div>
            </div>
        </div>
        <form action="<?= base_url('admin/settings/firebase_settings') ?>" method="post">
            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
            <div class="container-fluid card pt-3">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="google_map_api"><?= labels('apiKey', 'apiKey') ?><span class="text-danger text-xs">*</span> </label>
                            <input id="apiKey" class="form-control" type="text" name="apiKey" value="<?= isset($apiKey) ? $apiKey : '0' ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="google_map_api"><?= labels('authDomain', 'authDomain') ?><span class="text-danger text-xs">*</span> </label>
                            <input id="authDomain" class="form-control" type="text" name="authDomain" value="<?= isset($authDomain) ? $authDomain : 0 ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="google_map_api"><?= labels('projectId', 'projectId ') ?><span class="text-danger text-xs">*</span> </label>
                            <input id="projectId" class="form-control" type="text" name="projectId" value="<?= isset($projectId) ? $projectId : 0 ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="google_map_api"><?= labels('storageBucket', 'storageBucket ') ?><span class="text-danger text-xs">*</span> </label>
                            <input id="storageBucket" class="form-control" type="text" name="storageBucket" value="<?= isset($storageBucket) ? $storageBucket : 0 ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="google_map_api"><?= labels('messagingSenderId', 'messagingSenderId') ?><span class="text-danger text-xs">*</span> </label>
                            <input id="messagingSenderId" class="form-control" type="text" name="messagingSenderId" value="<?= isset($messagingSenderId) ? $messagingSenderId : 0 ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="google_map_api"><?= labels('appId', 'appId') ?><span class="text-danger text-xs">*</span> </label>
                            <input id="appId" class="form-control" type="text" name="appId" value="<?= isset($appId) ? $appId : 0 ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="google_map_api"><?= labels('measurementId', 'measurementId ') ?><span class="text-danger text-xs">*</span> </label>
                            <input id="measurementId" class="form-control" type="text" name="measurementId" value="<?= isset($measurementId) ? $measurementId : 0 ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="google_map_api"><?= labels('vapidKey', 'vapidKey ') ?><span class="text-danger text-xs">*</span> </label>
                            <input id="vapidKey" class="form-control" type="text" name="vapidKey" value="<?= isset($vapidKey) ? $vapidKey : 0 ?>">
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md">
                        <div class="form-group">
                            <input type='submit' name='update' id='update' value='<?= labels('update', "Update") ?>' class='btn btn-success' />
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>
</div>