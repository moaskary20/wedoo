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
            <h1><?= labels('web_settings', "Web settings") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item "><a href="<?= base_url('/admin/settings/system-settings') ?>"><?= labels('system_settings', "System Settings") ?></a></div>
                <div class="breadcrumb-item"><?= labels('web_settings', "Web settings") ?></div>
            </div>
        </div>


        <ul class="justify-content-start nav nav-fill nav-pills pl-3 py-2 setting" id="gen-list">
            <div class="row">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="<?= base_url('admin/settings/web_setting') ?>" id="pills-general_settings-tab" aria-selected="true">
                        <?= labels('web_settings', "Web Settings") ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('admin/settings/web-landing-page-settings') ?>" id="pills-about_us" aria-selected="false">
                        <?= labels('landing_page_settings', "Landing Page Settings") ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('admin/settings/become-provider-setting') ?>" id="pills-about_us">
                        <?= labels('become_provider_page_settings', "Become Provider Page Settings") ?>
                    </a>
                </li>
            </div>
        </ul>

        <?= form_open_multipart(base_url('admin/settings/web_setting_update')) ?>
        <div class="row mb-4">
            <!-- App download Section -->

            <div class="col-md-12 col-sm-12 col-xl-12 mb-3">
                <div class="card h-100">
                    <div class="row m-0 border_bottom_for_cards">
                        <div class="col ">
                            <div class="toggleButttonPostition"><?= labels('general_web_settings', "General Web Settings") ?></div>
                        </div>

                    </div>

                    <div class="card-body">





                        <div class="form-group">
                            <div class="control-label"><?= labels('web_maintenance_mode', "Web Maintenance Mode") ?></div>


                            <?php
                            $web_maintenance_mode = isset($web_maintenance_mode) ? $web_maintenance_mode : 0;
                            $label = ($web_maintenance_mode == 1) ?
                                '<input type="checkbox" id="web_maintenance_mode" name="web_maintenance_mode" class="status-switch" checked>' :
                                '<input type="checkbox" id="web_maintenance_mode" name="web_maintenance_mode" class="status-switch">';
                            echo $label;
                            ?>

                            
                        </div>
                    </div>

                </div>

            </div>

            <div class="col-md-6 col-sm-12 col-xl-6 mb-3">
                <div class="card h-100">
                    <div class="row m-0 border_bottom_for_cards">
                        <div class="col ">
                            <div class="toggleButttonPostition"><?= labels('app_download_section', "App download Section") ?></div>
                        </div>
                        <div class="col d-flex justify-content-end mr-3 mt-4">
                            <?php
                            $app_section_status = isset($app_section_status) ? $app_section_status : 0;
                            $label = ($app_section_status == 1) ?
                                '<input type="checkbox" id="app_section_status" name="app_section_status" class="status-switch" checked>' :
                                '<input type="checkbox" id="app_section_status" name="app_section_status" class="status-switch">';
                            echo $label;
                            ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for='web_title'><?= labels('title', "Title") ?></label>
                                    <input type='text' class="form-control custome_reset" name='web_title' id='web_title' value="<?= isset($web_title) ? $web_title : '' ?>" />
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="form-group">
                                    <label for='playstore_url'><?= labels('playstore_url', "Playstore URL ") ?></label>
                                    <div class="input-group">
                                        <input type='text' class="form-control custome_reset" name='playstore_url' id='playstore_url' value="<?= isset($playstore_url) ? $playstore_url : '' ?>" />
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('playstore_url')">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for='applestore_url'><?= labels('applestore_url', "Applestore URL") ?></label>
                                    <div class="input-group">
                                        <input type='text' class="form-control custome_reset" name='applestore_url' id='applestore_url' value="<?= isset($applestore_url) ? $applestore_url : '' ?>" />
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('applestore_url')">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-12 col-xl-6 mb-3">
                <div class="card h-100">
                    <div class="row border_bottom_for_cards m-0">
                        <div class="col">
                            <div class="toggleButttonPostition"><?= labels('logos', "Logos") ?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for='logo'><?= labels('logo', "Logo") ?></label>
                                    <input type="file" name="web_logo" class="filepond logo" id="web_logo" accept="image/*">

                                    <?php
                                    $disk = fetch_current_file_manager();

                                    if (isset($disk) && $disk === "aws_s3") {
                                        $web_logo = fetch_cloud_front_url('web_settings', $web_logo);
                                    } elseif (isset($disk) && $disk === "local_server") {
                                        $web_logo = base_url("public/uploads/web_settings/" . $web_logo);
                                    } else {
                                        $web_logo = base_url('public/backend/assets/img/news/img01.jpg');
                                    }
                                    ?>
                                    <img class="settings_logo" src="<?= $web_logo; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for='favicon'><?= labels('favicon', "Favicon") ?></label>
                                    <input type="file" name="web_favicon" class="filepond logo" id="web_favicon" accept="image/*">

                                    <?php
                                    $disk = fetch_current_file_manager();

                                    if (isset($disk) && $disk === "aws_s3") {
                                        $web_favicon = fetch_cloud_front_url('web_settings', $web_favicon);
                                    } elseif (isset($disk) && $disk === "local_server") {
                                        $web_favicon = base_url("public/uploads/web_settings/" . $web_favicon);
                                    } else {
                                        $web_favicon = base_url('public/backend/assets/img/news/img01.jpg');
                                    }
                                    ?>
                                    <img class="settings_logo" src="<?= $web_favicon; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for='halfLogo'><?= labels('half_logo', "Half Logo") ?></label>
                                    <input type="file" name="web_half_logo" class="filepond logo" id="web_half_logo" accept="image/*">



                                    <?php
                                    $disk = fetch_current_file_manager();

                                    if (isset($disk) && $disk === "aws_s3") {
                                        $web_half_logo = fetch_cloud_front_url('web_settings', $web_half_logo);
                                    } elseif (isset($disk) && $disk === "local_server") {
                                        $web_half_logo = base_url("public/uploads/web_settings/" . $web_half_logo);
                                    } else {
                                        $web_half_logo = base_url('public/backend/assets/img/news/img01.jpg');
                                    }
                                    ?>
                                    <img class="settings_logo" src="<?= $web_half_logo; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for='halfLogo'><?= labels('footer_logo', "Footer Logo") ?></label>
                                    <input type="file" name="footer_logo" class="filepond logo" id="footer_logo" accept="image/*">
                                    <?php
                                    $disk = fetch_current_file_manager();

                                    if (isset($disk) && $disk === "aws_s3") {
                                        $footer_logo = fetch_cloud_front_url('web_settings', $footer_logo);
                                    } elseif (isset($disk) && $disk === "local_server") {
                                        $footer_logo = base_url("public/uploads/web_settings/" . $footer_logo);
                                    } else {
                                        $footer_logo = base_url('public/backend/assets/img/news/img01.jpg');
                                    }
                                    ?>
                                    <img class="settings_logo" src="<?= $footer_logo; ?>">
                                </div>

                            </div>
                        </div>


                    </div>
                </div>
            </div>

        </div>



        <div class="row mb-4">
            <!-- social media links -->
            <div class="col-md-12 col-sm-12 col-xl-12">
                <div class="card h-100">
                    <div class="row border_bottom_for_cards m-0">
                        <div class="col">
                            <div class="toggleButttonPostition"><?= labels('social_media_links', "Social Media Links") ?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="list_wrapper">
                            <div class="row">

                                <div class="col-xs-4 col-sm-4 col-md-5">
                                    <div class="form-group m-0">
                                        <label for="title"><?= labels('url', "URL") ?></label>
                                        <input name="social_media[0][url]" type="text" placeholder="<?= labels('enter_url_here', 'Enter the URL here') ?>" class="form-control social_media_url_change" />
                                        <input type="hidden" name="social_media[0][exist_url]" value="new">
                                    </div>
                                </div>
                                <div class="col-xs-7 col-sm-7 col-md-5">
                                    <div class="form-group m-0">
                                        <label for="image"><?= labels('image', "Image") ?></label>
                                        <input name="social_media[0][file]" type="file" class="filepond logo " data-your-attribute="social_media_file" accept="image/*">
                                        <input type="hidden" name="social_media[0][exist_file]" value="new">
                                        <img class="settings_logo" src="">
                                    </div>
                                </div>
                                <div class="col-xs-1 col-sm-1 col-md-2 mt-4">
                                    <button class="btn btn-primary list_add_button" type="button">+</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($permissions['update']['settings'] == 1) : ?>

            <div class="row mt-3">
                <div class="col-md d-flex justify-content-end">
                    <input type='submit' name='update' id='update' value='<?= labels('save_changes', "Save") ?>' class='btn btn-lg bg-new-primary' />
                    <?= form_close() ?>
                </div>
            </div>

        <?php endif; ?>

</div>
</div>
</section>
</div>
<script>
    $(document).ready(function() {
        var x = 0; // Initial field counter
        var list_maxField = 10; // Input fields increment limitation
        // Once add button is clicked
        $('.list_add_button').click(function() {
            // Check maximum number of input fields
            if (x < list_maxField) {
                x++; // Increment field counter
                var list_fieldHTML = `
                <div class="row">
                    <div class="col-xs-4 col-sm-4 col-md-5">
                        <div class="form-group">
                            <label for="title"><?= labels('url', "URL") ?></label>
                            <input name="social_media[${x}][url]" type="text" placeholder="<?= labels('enter_url_here', 'Enter the URL here') ?>" class="form-control social_media_url_change"/>
                            <input type="hidden" name="social_media[${x}][exist_url]" value="social_media[${x}][url]">

                        </div>
                    </div>
                    <div class="col-xs-7 col-sm-7 col-md-5">
                        <div class="form-group">
                            <label for="image"><?= labels('image', "Image") ?></label>
                            <input name="social_media[${x}][file]" type="file" class="filepond logo new-row " accept="image/*" data-your-attribute="social_media_file" required>
                            <input type="hidden" name="social_media[${x}][exist_file]" value="social_media[${x}][file]">
                            <input type="hidden" name="social_media[${x}][exist_disk]" value="social_media[${x}][disk]">

                        </div>
                    </div>
                    <div class="col-xs-1 col-sm-1 col-md-2 mt-4">
                        <button class="list_remove_button btn btn-danger" type="button">-</button>
                    </div>
                </div>`;
                // Append field HTML
                $('.list_wrapper').append(list_fieldHTML);
                // Initialize FilePond only for the new row
                $('.list_wrapper .new-row').last().filepond({
                    credits: null,
                    allowFileSizeValidation: true,
                    maxFileSize: "25MB",
                    labelMaxFileSizeExceeded: "File is too large",
                    labelMaxFileSize: "Maximum file size is {filesize}",
                    allowFileTypeValidation: true,
                    acceptedFileTypes: ["image/*"],
                    labelFileTypeNotAllowed: "File of invalid type",
                    fileValidateTypeLabelExpectedTypes: "Expects {allButLastType} or {lastType}",
                    storeAsFile: true,
                    allowPdfPreview: true,
                    pdfPreviewHeight: 320,
                    pdfComponentExtraParams: "toolbar=0&navpanes=0&scrollbar=0&view=fitH",
                    allowVideoPreview: true,
                    allowAudioPreview: true,
                });
            }
        });
        // Once remove button is clicked
        $('.list_wrapper').on('click', '.list_remove_button', function() {
            $(this).closest('div.row').remove(); // Remove field HTML
            x--; // Decrement field counter
        });
    });
</script>
<script>
    var baseUrl = "<?= base_url() ?>"; // Define the base URL
    $(document).ready(function() {
        var app_section_status = document.querySelector('#app_section_status');
        app_section_status.addEventListener('change', function() {
            handleSwitchChange(app_section_status);
        });

        var web_maintenance_mode = document.querySelector('#web_maintenance_mode');

        web_maintenance_mode.addEventListener('change', function() {
            handleSwitchChange(web_maintenance_mode);
        });
        //for status
        <?php
        $app_section_status = isset($app_section_status) ? $app_section_status : 0;
        if ($app_section_status == 1) { ?>
            $('#app_section_status').siblings('.switchery').addClass('active-content').removeClass('deactive-content');
        <?php   } else { ?>
            $('#app_section_status').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        <?php  }


        $web_maintenance_mode = isset($web_maintenance_mode) ? $web_maintenance_mode : 0;
        if ($web_maintenance_mode == 1) { ?>
            $('#web_maintenance_mode').siblings('.switchery').addClass('active-content').removeClass('deactive-content');
        <?php   } else { ?>
            $('#web_maintenance_mode').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        <?php  }
        ?>



        function handleSwitchChange(checkbox) {
            var switchery = checkbox.nextElementSibling;
            if (checkbox.checked) {
                switchery.classList.add('active-content');
                switchery.classList.remove('deactive-content');
            } else {
                switchery.classList.add('deactive-content');
                switchery.classList.remove('active-content');
            }
        }
    });
</script>
<script>
    $(document).ready(function() {
        <?php
        $social_media = isset($social_media) && is_array($social_media) ? $social_media : [];
        $social_media = array_values($social_media); // Ensure the array keys are sequential
        ?>
        var x = <?= count($social_media) ?>; // Initial field counter
        // Function to add a new row with input fields
        function addRow(url, file, exist_url, disk) {

            let fileUrl = '';

            // Corrected PHP logic outside of JavaScript
            <?php
            $fileUrlAws = fetch_cloud_front_url("web_settings", ""); // Corrected for concatenation in JavaScript
            $fileUrlLocal = base_url("public/uploads/web_settings/");
            ?>

            if (disk === "aws_s3") {
                fileUrl = '<?= $fileUrlAws ?>' + file;
            } else if (disk === "local_server") {
                fileUrl = '<?= $fileUrlLocal ?>' + file;
            }


            var newRow = `
                <div class="row">
                    <div class="col-xs-4 col-sm-4 col-md-5">
                        <div class="form-group">
                            <label for="title"><?= labels('url', "URL") ?></label>
                            <input name="social_media[${x}][url]" type="text" value="${url}" class="form-control social_media_url_change" />
                            <input type="hidden" name="social_media[${x}][exist_url]" value="${exist_url}">
                            </div>
                            </div>
                            <div class="col-xs-7 col-sm-7 col-md-5">
                            <div class="form-group">
                            <label for="image"><?= labels('image', "Image") ?></label>
                            <input name="social_media[${x}][file]" type="file" class="filepond logo " accept="image/*" >
                            <img class="settings_logo" src="${fileUrl}" >
                            <input type="hidden" name="social_media[${x}][exist_file]" value="${file}">
                            <input type="hidden" name="social_media[${x}][exist_disk]" value="${disk}">
                            
                        </div>
                    </div>
                    <div class="col-xs-1 col-sm-1 col-md-2 mt-4">
                        <button class="btn btn-danger list_remove_button" type="button">-</button>
                    </div>
                </div>
            `;
            $(".list_wrapper").append(newRow);
            x++; // Increment the field counter
        }
        // Loop through the social_media array and add rows for each entry
        <?php foreach ($social_media as $entry) : ?>
            addRow("<?= $entry['url'] ?>", "<?= $entry['file'] ?>", "<?= $entry['url'] ?>", "<?= isset($entry['disk']) ? $entry['disk'] : "local_server" ?>");
        <?php endforeach; ?>
        $(".social_media_url_change").change(function() {
            if ($(this).val() == "") {
                $(this).parent().parent().next().find('.filepond--browser').prop('required', false);
            } else {
                $(this).parent().parent().next().find('.filepond--browser').prop('required', true);
            }
        });

    });
   
</script>