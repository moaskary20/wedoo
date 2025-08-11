<div class="main-content">
    <!-- ------------------------------------------------------------------- -->
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('add_ons', "Add Ons") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/add_on') ?>"><i class="fas fa-plus text-warning"></i> <?= labels('add_on', 'Add Ons') ?></a></div>
                <div class="breadcrumb-item"><?= labels('create_add_ons', "Create Add Ons") ?></a></div>
            </div>
        </div>
        <?= form_open('/admin/add_ons/store_create_add_ons', ['method' => "post", 'class' => 'form-submit-event', 'id' => 'add_ons', 'enctype' => "multipart/form-data"]); ?>
        <div class="row mb-4">
            <div class="col-lg-8 col-md-12 col-sm-12">
                <div class="card m-0 p-0">
                    <div class="row pl-3" style="border-bottom: solid 1px #e5e6e9;">
                        <div class="col ">
                            <div class="toggleButttonPostition"><?= labels('add_ons_information', 'Add On Information') ?></div>
                        </div>
                        <div class="col d-flex justify-content-end mr-3 mt-4">
                            <input type="checkbox" class="status-switch" name="status" id="status">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="company"><?= labels('name', ' Name') ?></label>
                                    <input id="name" class="form-control" type="text" name="name" placeholder="<?= labels('enter', 'Enter ') ?> <?= labels('name', 'the name ') ?> <?= labels('here', ' Here ') ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Publish</label>
                                    <input type="checkbox" id="publish" name="publish" class="status-switch">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md d-flex justify-content-end m-2">
                            <button type="submit" class="btn btn-lg bg-new-primary"><?= labels('save', 'Save') ?></button>
                            <?= form_close() ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>    
    </section>
</div>
<style>
</style>
<script>
    $(document).ready(function() {
        $("#max_order").hide();
        $("#max_service").hide();
        $("#commission_fields").hide();
        $('#percentage_field').hide();
        $('#status').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        var status = document.querySelector('#status');
        status.onchange = function(e) {
            if (status.checked) {
                $(this).siblings('.switchery').addClass('active-content').removeClass('deactive-content');
            } else {
                $(this).siblings('.switchery').addClass('deactive-content').removeClass('active-content');
            }
        };
        $('#publish').siblings('.switchery').addClass('no-content').removeClass('yes-content');
        var publish = document.querySelector('#publish');
        publish.onchange = function(e) {
            if (publish.checked) {
                $(this).siblings('.switchery').addClass('yes-content').removeClass('no-content');
            } else {
                $(this).siblings('.switchery').addClass('no-content').removeClass('yes-content');
            }
        };
        $('input[name="order_type"]').change(function() {
            if ($(this).val() === "limited") {
                $("#max_order").show();
            } else {
                $("#max_order").hide();
            }
        });
        $('input[name="service_type"]').change(function() {
            if ($(this).val() === "limited") {
                $("#max_service").show();
            } else {
                $("#max_service").hide();
            }
        });
        $('input[name="commission_type"]').change(function() {
            if ($(this).val() === "yes") {
                $("#commission_fields").show();
            } else {
                $("#commission_fields").hide();
            }
        });
        $('#tax_type').change(function() {
            if ($(this).val() === "excluded") {
                $('#percentage_field').show();
            } else {
                $('#percentage_field').hide();
            }
        });
    });
</script>