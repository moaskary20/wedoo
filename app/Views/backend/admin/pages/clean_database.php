<div class="main-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('clean_database', 'Clean Database') ?>
                <span class="breadcrumb-item p-3 pt-2 text-primary"><i data-content="The admin will be able to view the prepaid booking amount that is to be sent to the provider. This amount represents the total payment made by the customer in advance for the provider’s services. The admin is responsible for  sending the remaining payment to the provider." class="fa fa-question-circle"></i></span>
            </h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item "><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item "><a href="<?= base_url('/admin/settings/system-settings') ?>"><?= labels('system_settings', "System Settings") ?></a></div>
                <div class="breadcrumb-item active"><?= labels('clean_database', "Clean Database") ?></div>
            </div>
        </div>
        <div class="section-body">
            <div class="card">
                <div class="card-body">
                    <?= form_open('/admin/clean_database_tables', ['method' => 'post', 'class' => 'form-submit-event', 'id' => 'add_partner', 'enctype' => 'multipart/form-data']); ?>
                    <div class="row">
                        <?php foreach ($tables as $table) : ?>
                            <div class="col-md-3 my-1">
                                <input type="checkbox" class="clean-table-checkbox" name="tables_to_clean[]" id="<?= "id-" . $table['table'] ?>" value="<?= $table['table'] ?>">
                                <label for="<?= "id-" . $table['table'] ?>"><?= $table['table'] ?></label>
                                <span class="badge badge-light"><?= $table['total_records'] ?></span>
                            </div>
                        <?php endforeach; ?>


                    </div>
                    <div class="row">
                        <div class="col-md d-flex justify-content-end">
                            <button type="submit" class="btn btn-lg bg-new-primary submit_btn"><?= labels('submit', 'Submit') ?></button>
                        </div>
                    </div>
                    <?= form_close(); ?>
                </div>
            </div>
        </div>
</div>
</section>
</div>