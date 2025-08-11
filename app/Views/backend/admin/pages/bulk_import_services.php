<!-- Main Content -->
<div class="main-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('bulk_service_update', 'Bulk Service Update') ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item"><?= labels('bulk_service_update', 'Bulk Service Update') ?></div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="card card-primary h-100">
                    <div class="card-header">
                        <h4><?= labels('step_1', 'Step 1') ?></h4>
                        <div class="card-header-action">
                            <img height="50" width="50" src="<?= base_url("public/uploads/site/file.png")  ?>" class="" alt="">
                        </div>
                    </div>
                    <div class="card-body">
                        <h6 class="text-dark"><?= labels('download_excel_file', 'Download Excel File') ?></h6>
                        <ul class="p-3">
                            <li>
                                <?= labels('download_format_instruction', 'Download the format file and fill it with proper data.') ?>
                            </li>
                            <li>
                                <?= labels('download_review_example', 'You can download the example file to understand how the data must be filled.') ?>
                            </li>
                            <li>
                                <?= labels('upload_excel', ' Have to upload excel file.') ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-primary h-100">
                    <div class="card-header">
                        <h4><?= labels('step_2', 'Step 2') ?></h4>
                        <div class="card-header-action">
                            <img height="50" width="50" src="<?= base_url("public/uploads/site/data-transfer.png") ?>" class="" alt="">
                        </div>
                    </div>
                    <div class="card-body">
                        <h6 class="text-dark"><?= labels('match_data_instruction', ' Match Spread sheet data according to instruction') ?></h6>
                        <ul class="p-3">
                            <li><?= labels('validate_spreadsheet', 'Ensure that all data in the spreadsheet adheres to the specified formats and values.') ?></li>
                            <li><?= labels('download_review_example', 'Download and review the example file provided to understand the required structure and format for the data. This file serves as a template to help you fill in your data correctly.') ?></li>
                            <li><?= labels('upload_excel_instruction', 'You need to upload an Excel file (<code>.xlsx</code>) for the bulk import process.') ?></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-primary h-100">
                    <div class="card-header">
                        <h4><?= labels('step_3', 'Step 3') ?></h4>
                        <div class="card-header-action">
                            <img height="50" width="50" src="<?= base_url("public/uploads/site/file_upload.png")  ?>" class="" alt="">
                        </div>
                    </div>
                    <div class="card-body">
                        <h6 class="text-dark"><?= labels('upload_excel', 'Upload Excel File') ?></h6>
                        <ul class="p-3">
                            <li>
                                <?= labels('ensure_correct_headers', 'Ensure the first row contains the correct headers matching the template.') ?>
                            </li>
                            <li>
                                <?= labels('validate_data', ' Review and validate your data thoroughly before uploading to avoid errors during the
                                import process.') ?>
                            </li>
                            <li>
                                <?= labels('fill_mandatory_fields', 'Ensure all mandatory fields are filled and follow the specified formats strictly.') ?>
                            </li>
                            <li>
                                <?= labels('upload_excel',  'Have to upload excel file.') ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-8">
                <div class="card h-100">
                    <div class="card-header">
                        <h4><?= labels('download_files', 'Download Files') ?></h4>
                    </div>
                    <div class="card-body">
                        <div class="row mt-4">
                            <div class="col-md-6 mb-3">
                                <a href="<?= base_url("/admin/services/download-sample-for-insert/") ?>" class="btn  btn-lg btn-outline-primary w-100" id="downloadInsert">
                                    <i class="fas fa-arrow-circle-down mr-2"></i>
                                    <?= labels('add_service_data', 'Add Service Data') ?> </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="<?= base_url("/admin/services/Service-Add-Instructions/") ?>" class="btn  btn-lg btn-outline-primary w-100" id="addInstructions">
                                    <i class="fas fa-arrow-circle-down mr-2"></i>
                                    <?= labels('add_service_instructions', 'Add Service Instructions') ?> </a>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <a href="<?= base_url("/admin/services/Service-Update-Instructions/") ?>" class="btn  btn-lg btn-outline-primary w-100" id="updateInstructions">
                                    <i class="fas fa-arrow-circle-down mr-2"></i>
                                    <?= labels('update_service_instructions', 'Update Service Instructions') ?> </a>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <form action="<?= base_url("/admin/services/download-sample-for-update") ?>" method="post" id="downloadForm">
                                        <select id="service_partner_ids" class="form-control select2" multiple name="partners[]">
                                            <option></option>
                                            <?php foreach ($partner_name as $pn) : ?>
                                                <option value="<?= $pn['id'] ?>">
                                                    <?= $pn['company_name'] . ' - ' . $pn['username'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <button type="button" class="btn btn-lg btn-outline-primary w-100" id="downloadUpdate">
                                        <i class="fas fa-arrow-circle-down mr-2"></i>
                                        <?= labels('update_service_data', 'Update Service Data') ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h4><?= labels('upload_file', 'Upload File') ?></h4>
                    </div>
                    <div class="card-body">
                        <?= form_open(
                            '/admin/services/bulk_import_service_upload',
                            ['method' => "post", 'class' => 'form-submit-event', 'id' => 'update_service', 'enctype' => "multipart/form-data"]
                        ); ?>
                        <div class="row align-items-center">
                            <div class="col-md-12">
                                <input type="file" class="filepond-excel" name="file" id="file" required>
                            </div>
                            <div class="col-md-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-lg bg-new-primary submit_btn"><?= labels('submit', 'Submit') ?></button>
                            </div>
                        </div>
                        <?= form_close() ?>
                    </div>
                </div>
            </div>
        </div>
</div>
</div>
</section>
</div>
<script>
    $(document).ready(function() {
        setTimeout(() => {
            $("#service_partner_ids").select2({
                placeholder: "<?= labels('select_provider(s)', 'Select Provider(s)') ?>",
                allowClear: true,
                width: '100%'
            });
        });
    });
    $(document).ready(function() {
        $('#downloadUpdate').click(function() {

            const formData = $('#service_partner_ids').val();
            const url = $('#downloadForm').attr('action')

            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    partners: formData
                },
                cache: false,
                success: function(response) {
                    var blob = new Blob([response], {
                        type: 'text/csv'
                    });
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = 'service_sample_with_data.csv';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                },
                error: function(xhr, status, error) {
                    console.error("Full Server Response:", xhr.responseText);
                    alert('AJAX Error: ' + error);
                }
            });
        });
    });
</script>