<div class="main-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1> <?= labels('refund_policy', "Refund Policy") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item "><a href="<?= base_url('/admin/settings/system-settings') ?>"><?= labels('system_settings', "System Settings") ?></a></div>

                <div class="breadcrumb-item"><?= labels('refund_policy', "Refund Policy") ?> </div>
            </div>
        </div>
        <div class="">
            <!-- tab section -->
            <ul class="nav nav-pills justify-content-center py-2 nav-fill" id="gen-list">


                <li class="nav-item">
                    <a class="nav-link " href="<?= base_url('admin/settings/customer-terms-and-conditions') ?>" id="pills-customer_terms_and_conditions" aria-selected="false">
                        <?= labels('customer_terms_and_conditions', "Customer Terms and Conditions") ?></a>
                </li>

                <li class="nav-item">
                    <a class="nav-link " href="<?= base_url('admin/settings/terms-and-conditions') ?>" id="pills-partner_terms_and_conditions" aria-selected="false">
                        <?= labels('partner_terms_and_conditions', "Partner Terms and Conditions") ?></a>

                </li>

                <li class="nav-item">
                    <a class="nav-link " href="<?= base_url('admin/settings/customer-privacy-policy') ?>" id="pills-customer_privacy_policy" aria-selected="false">
                        <?= labels('customer_privacy_policy', "Customer Privacy Policy") ?></a>
                </li>

                <li class="nav-item">
                    <a class="nav-link " href="<?= base_url('admin/settings/privacy-policy') ?>" id="pills-partner_privacy_policy" aria-selected="false">
                        <?= labels('partner_privacy_policy', "Partner Privacy Policy") ?></a>
                </li>
                <!-- <li class="nav-item">
                    <a class="nav-link active" href="<?= base_url('admin/settings/refund-policy') ?>" id="pills-partner_privacy_policy" aria-selected="false">
                        <?= labels('refund_policy', "Refund Policy") ?></a>
                </li> -->
            </ul>

        </div>
        <form action="<?= base_url('admin/settings/refund-policy') ?>" method="post">
            <div class="col-md-6 mt-3 mb-4">
                <a href="<?= base_url('admin/settings/refund_policy_page'); ?>" class="btn btn-primary"><i class="fa fa-eye"></i> <?= labels('preview', 'Preview') ?></a>
            </div>
            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
            <div class="container-fluid card p-3">
                <div class="row">
                    <div class="col-lg">
                        <textarea rows=50 class='form-control h-50 summernotes' name="refund_policy"><?= isset($refund_policy) ? $refund_policy : 'Enter Refund policy.' ?></textarea>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md">
                        <div class="form-group">
                            <input type='submit' name='update' id='update' value='<?= labels('save_changes', "Update") ?>' class='btn btn-success' />
                            <input type='reset' name='clear' id='clear' value='<?= labels('Reset', "Clear") ?>' class='btn btn-danger' />
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>
</div>