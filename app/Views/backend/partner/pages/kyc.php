<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1><?= labels('kyc', "KYC") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/partner/Dashboard') ?>">Partner</a></div>
                <div class="breadcrumb-item">KYC</div>
            </div>
        </div>
        <?= helper('form'); ?>
        <div class="section-body">
            <div class="row">
                <div class="col">
                    <div class="card shadow">
                        <div class="card-header pb-1">
                            <h2 class='section-title '><?= labels('add_kyc_detail', "Add KYC Details") ?></h2>
                        </div>
                        <div class="card-body">
                            <?= form_open('/partner/kyc/add_kyc', ['method' => "post", 'class' => 'form-submit-event', 'id' => 'update_service', 'enctype' => "multipart/form-data"]); ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="company_name">Company Name</label>
                                        <input id="company_name" class="form-control" type="text" name="company_name" readonly value="
                                        <?= $users[0]['company'] ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="national_id">national ID</label>
                                        <input id="national_id" class="form-control" type="text" name="national_id">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                            </div>
                            <?= form_close() ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>