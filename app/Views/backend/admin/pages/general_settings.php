<!-- Main Content new-->
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
    <section class="section" id="pill-general_settings" role="tabpanel">
        <div class="section-header mt-2">
            <h1><?= labels('general_settings', 'General Settings') ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item "><a href="<?= base_url('/admin/settings/system-settings') ?>"><?= labels('system_settings', "System Settings") ?></a></div>
                <div class="breadcrumb-item "><a href="<?= base_url('admin/settings/general-settings') ?>"><?= labels('general_settings', "General Settings") ?></a></div>
            </div>
        </div>
        <ul class="justify-content-start nav nav-fill nav-pills pl-3 py-2 setting" id="gen-list">
            <div class="row">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="<?= base_url('admin/settings/general-settings') ?>" id="pills-general_settings-tab" aria-selected="true">
                        <?= labels('general_settings', "General Settings") ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('admin/settings/about-us') ?>" id="pills-about_us" aria-selected="false">
                        <?= labels('about_us', "About Us") ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('admin/settings/contact-us') ?>" id="pills-about_us" aria-selected="false">
                        <?= labels('support_details', "Support Details") ?></a>
                </li>
            </div>
        </ul>
        <?= form_open_multipart(base_url('admin/settings/general-settings')) ?>
        <div class="row mb-3 mb-sm-3 mb-md-3 mb-xxs-12">
            <div class="col-lg-4 col-md-12 col-sm-12 col-xl-4 mb-md-3 mb-sm-3  mb-3">
                <div class="card h-100 ">
                    <div class="row m-0 border_bottom_for_cards">
                        <div class="col  ">
                            <div class="toggleButttonPostition"><?= labels('business_settings', 'Business settings') ?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <input type="hidden" id="set" value="<?= isset($system_timezone) ? $system_timezone : 'Asia/Kolkata' ?>">
                                    <input type="hidden" name="system_timezone_gmt" value="<?= isset($system_timezone_gmt) ? $system_timezone_gmt : '' ?>" id="system_timezone_gmt" value="<?= isset($system_timezone_gmt) ? $system_timezone_gmt : '+05:30' ?>" />
                                    <label for='timezone'><?= labels('select_time_zone', "Select Time Zone") ?></label>
                                    <select class='form-control selectric ' name='system_timezone' id='timezone' value="">
                                        <option value="">-- <?= labels('select_time_zone', "Select Time Zone") ?> --</option>
                                        <?php foreach ($timezones as $row) { ?>
                                            <option value="<?= $row[2] ?>" data-gmt="<?= $row[1] ?>"><?= $row[1] ?> - <?= $row[0] ?> - <?= $row[2] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="max_serviceable_distance"><?= labels('max_Serviceable_distance_in_kms', "Max Serviceable Distance (in Kms)") ?></label>
                                    <i data-content=" <?= labels('data_content_for_max_serviceable_distance', 'The system will use the distance values (KM) you provide to find providers in Xkms within the location chosen by the customer. For instance, if you set it to 100 KM, customers will see providers within 100 KM of their chosen location. If there are no providers within 100 KM, it\'ll say, We are not available here.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                                    <div class="input-group">
                                        <input type="number" class="form-control custome_reset" name="max_serviceable_distance" id="max_serviceable_distance" value="<?= isset($max_serviceable_distance) ? $max_serviceable_distance : '' ?>" />
                                        <div class="input-group-append">
                                            <select class="form-control" name="distance_unit" id="distance_unit">
                                                <option value="km" <?= isset($distance_unit) && $distance_unit == 'km' ? 'selected' : '' ?>>Kms</option>
                                                <option value="miles" <?= isset($distance_unit) && $distance_unit == 'miles' ? 'selected' : '' ?>>Miles</option>
                                            </select>
                                        </div>
                                    </div>
                                    <label for="max_serviceable_distance" class="text-danger"><?= labels('note_this_distance_is_used_while_search_nearby_partner_for_customer', " This distance is used while search nearby partner for customer") ?></label>
                                </div>
                            </div>
                            <div class="col-md-12 ">
                                <div class="form-group">
                                    <label for='logo'><?= labels('login_image', "Login Image") ?></label>
                                    <i data-content="<?= labels('data_content_for_login_image', "This picture will appear as the background on the login pages for the admin and provider panels.") ?>" class="fa fa-question-circle" data-original-title="" title=""></i></span>
                                </div>
                                <input type="file" name="login_image" class="filepond logo" id="login_image" accept="image/*">
                                <img class="settings_logo" style="border-radius: 8px" src="<?= base_url('public/frontend/retro/Login_BG.jpg') ?>">
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="primary_color"><?= labels('primary_color', "Primary Color") ?></label>
                                    <input type="text" onkeyup="change_color('change_color',this)" oninput="change_color('change_color',this)" class=" form-control" name="primary_color" id="primary_color" value="<?= isset($primary_color) ? $primary_color : '' ?>" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="secondary_color"><?= labels('secondary_color', "Secondary Color") ?></label>
                                    <input type="text" class=" form-control" name="secondary_color" id="secondary_color" value="<?= isset($secondary_color) ? $secondary_color : '' ?>" />
                                </div>
                            </div>
                            <div class="col-md-6 ">
                                <div class="form-group">
                                    <div class="control-label"><?= labels('booking_auto_cancel', "Booking auto cancel Duration") ?> <span class="breadcrumb-item p-3 pt-2 text-primary">
                                            <i data-content=" If the booking is not accepted by the provider before the added cancelable duration from the actual booking time, the booking will be automatically canceled. If the booking is pre-paid, the amount will be credited to the customer’s bank account.For example, if a customer books a service at 4:00 PM, and the cancelable duration is 30 minutes, if the provider does not accept the booking by 3:30 PM, the booking will be canceled." class="fa fa-question-circle" data-original-title="" title=""></i></span></div>
                                    <input type="number" class="form-control" name="booking_auto_cancle_duration" id="booking_auto_cancle_duration" value="<?= isset($booking_auto_cancle_duration) ? $booking_auto_cancle_duration : '30' ?>" />
                                </div>
                            </div>
                            <div class="col-md-6 ">
                                <div class="form-group">
                                    <div class="control-label"><?= labels('image_compression_preference', "Image Compression Preference") ?> <span class="breadcrumb-item p-3 pt-2 text-primary">
                                            <i data-content="<?= labels('data_content_image_compression_preference', 'If enabled, This high-quality image has been compressed to a lower quality, as per the quality provided in Image Compression Quality.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i></span></div>
                                    <select name="image_compression_preference" class="form-control" id="image_compression_preference">
                                        <option value="0" <?php echo  isset($image_compression_preference) && $image_compression_preference == '0' ? 'selected' : '' ?>><?= labels('disable', 'Disable') ?></option>
                                        <option value="1" <?php echo  isset($image_compression_preference) && $image_compression_preference == '1' ? 'selected' : '' ?>><?= labels('enable', 'Enable') ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12 mt-2" id="image_compression_quality_input">
                                <div class="form-group">
                                    <div class="control-label"><?= labels('image_compression_quality', "Image Compression Quality") ?> <span class="breadcrumb-item p-3 pt-2 text-primary">
                                            <i data-content="<?= labels('data_content_image_compression_quality', 'This high-quality image has been compressed to a lower quality, as per the quality provided here.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i></span></div>
                                    <input type="number" max=100 min=0 class="form-control" name="image_compression_quality" id="image_compression_quality" value="<?= isset($image_compression_quality) ? $image_compression_quality : '70' ?>" />
                                </div>
                            </div>
                            <!-- <div class="col-md-6 mt-2">
                                <div class="form-group">
                                    <div class="control-label"><= labels('prepaid_booking_cancellation_time', "Prepaid Booking auto cancle Duration") ?>
                                        <span class="breadcrumb-item p-3 pt-2 text-primary"><i data-content=" If you don't complete the payment for a prepaid booking before the cancellation deadline, the system will cancel the booking automatically. For instance, if you book a service at 4:00 PM with a 30-minute cancellation window, and the payment is still pending by 3:30 PM, the booking will be canceled automatically.." class="fa fa-question-circle" data-original-title="" title=""></i></span>
                                    </div>
                                    <input type="number" class="form-control" name="prepaid_booking_cancellation_time" id="prepaid_booking_cancellation_time" value="<= isset($prepaid_booking_cancellation_time) ? $prepaid_booking_cancellation_time : '30' ?>" />
                                </div>
                            </div> -->
                        </div>
                    </div>
                </div>
            </div>
            <!-- admin logos  -->
            <div class="col-lg-4 col-md-12 col-sm-12 col-xl-4 mb-md-3 mb-sm-3 mb-3">
                <div class="card h-100">
                    <div class="row border_bottom_for_cards m-0">
                        <div class="col">
                            <div class="toggleButttonPostition"><?= labels('admin_logos', "Admin Logos") ?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 ">
                                <div class="form-group">
                                    <label for='logo'><?= labels('logo', "Logo") ?></label>
                                    <input type="file" name="logo" class="filepond logo" id="file" accept="image/*">
                                    <img class="settings_logo" src="<?= isset($logo) && $logo != "" ? $logo : base_url('public/backend/assets/img/news/img01.jpg') ?>">
                                </div>
                            </div>
                            <div class="col-md-12 ">
                                <div class="form-group">
                                    <label for='favicon'><?= labels('favicon', "Favicon") ?></label>
                                    <input type="file" name="favicon" class="filepond logo" id="favicon" accept="image/*">
                                    <img class="settings_logo" src="<?= isset($favicon) && $favicon != "" ? $favicon : base_url('public/backend/assets/img/news/img01.jpg') ?>">
                                </div>
                            </div>
                            <div class="col-md-12 ">
                                <div class="form-group">
                                    <label for='half_logo'><?= labels('half_logo', "Half Logo") ?></label>
                                    <input type="file" name="half_logo" class="filepond logo" id="half_logo" accept="image/*">
                                    <img class="settings_logo" src="<?= isset($half_logo) && $half_logo != "" ? $half_logo : base_url('public/backend/assets/img/news/img01.jpg') ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- provider logos  -->
            <div class="col-lg-4 col-md-12 col-sm-12 col-xl-4 mb-md-3 mb-sm-3 mb-3">
                <div class="card h-100">
                    <div class="row border_bottom_for_cards m-0">
                        <div class="col ">
                            <div class="toggleButttonPostition"><?= labels('provider_logos', "Provider Logos") ?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 ">
                                <div class="form-group">
                                    <label for='logo'><?= labels('logo', "Logo") ?></label>
                                    <input type="file" name="partner_logo" class="filepond logo" id="partner_logo" accept="image/*">
                                    <img class="settings_logo" src="<?= isset($partner_logo) && $partner_logo != "" ? $partner_logo : base_url('public/backend/assets/img/news/img01.jpg') ?>">
                                </div>
                            </div>
                            <div class="col-md-12 ">
                                <label for='favicon'><?= labels('favicon', "Favicon") ?></label>
                                <input type="file" name="partner_favicon" class="filepond logo" id="partner_favicon" accept="image/*">
                                <img class="settings_logo" src="<?= isset($partner_favicon) && $partner_favicon != "" ? $partner_favicon : base_url('public/backend/assets/img/news/img01.jpg') ?>">
                            </div>
                        </div>
                        <div class="col-md-12 ">
                            <div class="form-group">
                                <label for='halfLogo'><?= labels('half_logo', "Half Logo") ?></label>
                                <input type="file" name="partner_half_logo" class="filepond logo" id="partner_half_logo" accept="image/*">
                                <img class="settings_logo" src="<?= isset($partner_half_logo) && $partner_half_logo != "" ? $partner_half_logo : base_url('public/backend/assets/img/news/img01.jpg') ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xl-12 mb-md-3 mb-sm-3 mb-3">
                <div class="card h-100">
                    <div class="row border_bottom_for_cards m-0">
                        <div class="col ">
                            <div class="toggleButttonPostition"><?= labels('company_setting', "Company Settings") ?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-3">
                                <div class="form-group">
                                    <label for='company_title'><?= labels('company_title', "Company Title") ?></label>
                                    <input type='text' class="form-control custome_reset" name='company_title' id='company_title' value="<?= isset($company_title) ? $company_title : '' ?>" />
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for='support_email'><?= labels('support_email', "support Email") ?></label>
                                    <input type='email' class="form-control custome_reset" name='support_email' id='support_email' value="<?= isset($support_email) ? $support_email : '' ?>" />
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="phone"><?= labels('mobile', "Phone") ?></label>
                                    <input type="number" min="0" class="form-control custome_reset" name="phone" id="phone" value="<?= isset($phone) ? $phone : '' ?>" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="support_hours"><?= labels('support_hours', "Support Hours") ?></label>
                                <input type="text" class="form-control custome_reset" name="support_hours" id="support_hours" value="<?= isset($support_hours) ? $support_hours : '09:00 to 18:00' ?>" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <label for="copyright_details"><?= labels('copyright_details', "Copyright Details") ?></label>
                                <input type="text" class="form-control " name="copyright_details" id="copyright_details" value="<?= isset($copyright_details) ? $copyright_details : 'Enter Copyright details' ?>" />
                            </div>
                            <div class="col-md-3">
                                <label for="copyright_details"><?= labels('company_map_location', "Company Map Location") ?></label>
                                <input type="text" class="form-control" name="company_map_location" id="company_map_location" value="<?= htmlentities(isset($company_map_location) ? $company_map_location : '') ?>" />
                            </div>
                            <div class="col-md-3">
                                <label for="address"><?= labels('address', "Address") ?></label>
                                <textarea rows=1 class='form-control  custome_reset' name="address"><?= isset($address) ? $address : 'Enter Address' ?></textarea>
                            </div>
                            <div class="col-md-3">
                                <label for="short_description"><?= labels('short_description', "Short Description") ?></label>
                                <textarea rows=1 class='form-control  custome_reset' name="short_description"><?= isset($short_description) ? $short_description : 'Enter Short Description' ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-lg-8 col-md-8 col-sm-12 col-xl-8 mb-md-3 mb-sm-3 mb-3">
                <div class="card h-100">
                    <div class="row border_bottom_for_cards m-0">
                        <div class="col ">
                            <div class="toggleButttonPostition"><?= labels('chat_settings', "Chat Settings") ?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label for='maxFilesOrImagesInOneMessage'><?= labels('maxFilesOrImagesInOneMessage', "Max File Or Images In One message") ?></label>
                                    <br>
                                    <small class="text-grey"><?= labels('note_max_file_or_image_allowed_in_one_message', 'Note: Maximum File or image allowed in one message') ?></small>
                                    <input type='text' class="form-control custome_reset" name='maxFilesOrImagesInOneMessage' id='maxFilesOrImagesInOneMessage' value="<?= isset($maxFilesOrImagesInOneMessage) ? $maxFilesOrImagesInOneMessage : '' ?>" />
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for='maxFileSizeInMBCanBeSent'><?= labels('maxFileSizeInMBCanBeSent', "Max File Size In MB Can be sent") ?></label>
                                    <br>
                                    <small class="text-grey"><?= labels('note_max_size', 'Note: The maximum size') ?> (
                                        <?php
                                        $maxFileSizeStr = ini_get("upload_max_filesize");
                                        $maxFileSizeBytes = return_bytes($maxFileSizeStr);
                                        $maxFileSizeMB = $maxFileSizeBytes / (1024 * 1024); // Convert bytes to megabytes
                                        echo round($maxFileSizeMB, 2) . ' MB'; // Round to 2 decimal places for MB
                                        function return_bytes($size_str)
                                        {
                                            switch (substr($size_str, -1)) {
                                                case 'M':
                                                case 'm':
                                                    return (int)$size_str * 1048576;
                                                case 'K':
                                                case 'k':
                                                    return (int)$size_str * 1024;
                                                case 'G':
                                                case 'g':
                                                    return (int)$size_str * 1073741824;
                                                default:
                                                    return $size_str;
                                            }
                                        }
                                        ?>
                                        ) <?= labels('allowed_sending_files', 'allowed for sending files') ?></small>
                                    <input type='number' class="form-control custome_reset" max="<?= round($maxFileSizeMB, 2) ?>" name='maxFileSizeInMBCanBeSent' id='maxFileSizeInMBCanBeSent' value="<?= isset($maxFileSizeInMBCanBeSent) ? $maxFileSizeInMBCanBeSent : '' ?>" />
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="phone"><?= labels('maxCharactersInATextMessage', "Max Characters in a text message") ?></label>
                                    <br>
                                    <small class="text-grey"><?= labels('note_max_characters_allowed_in_text_message', 'Note: The maximum number of characters allowed in a text message') ?></small>
                                    <input type="number" min="0" class="form-control custome_reset" name="maxCharactersInATextMessage" id="maxCharactersInATextMessage" value="<?= isset($maxCharactersInATextMessage) ? $maxCharactersInATextMessage : '' ?>" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="control-label"><?= labels('allow_pre_booking_chat', 'Allow Pre Booking Chat') ?></div>
                                    <select name="allow_pre_booking_chat" class="form-control">
                                        <option value="0" <?php echo  isset($allow_pre_booking_chat) && $allow_pre_booking_chat == '0' ? 'selected' : '' ?>><?= labels('disable', 'Disable') ?></option>
                                        <option value="1" <?php echo  isset($allow_pre_booking_chat) && $allow_pre_booking_chat == '1' ? 'selected' : '' ?>><?= labels('enable', 'Enable') ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="control-label"><?= labels('allow_post_booking_chat', 'Allow Post Booking Chat') ?></label> </div>
                                    <select name="allow_post_booking_chat" class="form-control">
                                        <option value="0" <?php echo  isset($allow_post_booking_chat) && $allow_post_booking_chat == '0' ? 'selected' : '' ?>><?= labels('disable', 'Disable') ?></option>
                                        <option value="1" <?php echo  isset($allow_post_booking_chat) && $allow_post_booking_chat == '1' ? 'selected' : '' ?>><?= labels('enable', 'Enable') ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-12 col-xl-4 mb-md-3 mb-sm-3 mb-3">
                <div class="card h-100">
                    <div class="row border_bottom_for_cards m-0">
                        <div class="col ">
                            <div class="toggleButttonPostition"><?= labels('otp_settings', "OTP Settings") ?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 ">
                                <div class="form-group">
                                    <div class="control-label"><?= labels('otp_system', "OTP System") ?> <span class="breadcrumb-item p-3 pt-2 text-primary">
                                            <i data-content="<?= labels('data_content_otp_system', 'If enabled, both the provider and admin need to obtain an OTP from the customer in order to mark the booking as completed. Otherwise, if no OTP verification is required, the booking can be directly marked as completed.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i></span></div>
                                    <select name="otp_system" class="form-control">
                                        <option value="0" <?php echo  isset($otp_system) && $otp_system == '0' ? 'selected' : '' ?>><?= labels('disable', 'Disable') ?></option>
                                        <option value="1" <?php echo  isset($otp_system) && $otp_system == '1' ? 'selected' : '' ?>><?= labels('enable', 'Enable') ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12 ">
                                <div class="form-group">
                                    <div class="control-label"><?= labels('authentication_mode', "Authentication Mode") ?> </div>
                                    <select name="authentication_mode" class="form-control">
                                        <option value="firebase" <?php echo  isset($authentication_mode) && $authentication_mode == 'firebase' ? 'selected' : '' ?>>Firebase</option>
                                        <option value="sms_gateway" <?php echo  isset($authentication_mode) && $authentication_mode == 'sms_gateway' ? 'selected' : '' ?>>SMS Gateway</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xl-12 mb-md-3 mb-sm-3 mb-3">
                <div class="card h-100">
                    <div class="row border_bottom_for_cards m-0">
                        <div class="col ">
                            <div class="toggleButttonPostition"><?= labels('file_manager_settings', "File Manager Settings") ?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="type" class="required"><?= labels('file_manager', 'File Manager') ?></label>
                                    <select class="select2" name="file_manager" id="file_manager" required>
                                        <option value="local_server" <?php echo  isset($file_manager) && $file_manager == 'local_server' ? 'selected' : '' ?>><?= labels('local_server', 'Local Server') ?></option>
                                        <option value="aws_s3" <?php echo  isset($file_manager) && $file_manager == 'aws_s3' ? 'selected' : '' ?>><?= labels('aws_s3', 'AWS S3') ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="control-label"><?= labels('file_transfer_process', "File Transfer Process") ?></div>
                                    <label class="mt-2">
                                        <input type="hidden" name="file_transfer_process" value="0" id="file_transfer_process_value">
                                        <input type="checkbox" class="status-switch" id="file_transfer_process" value="0">
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-12" id="file_transfer_note">
                                <div class="alert alert-light alert-has-icon">
                                    <div class="alert-icon"><i class="far fa-lightbulb"></i></div>
                                    <div class="alert-body">
                                        <div class="alert-title"><?= labels('note', 'Note') ?></div>
                                        <?= labels('enable_file_transfer_need_to_set_below_command_cron_job', 'If you enable file transfer process then you need to set below command to your cron job') ?> ::
                                        <br>
                                        <p class="danger">* * * * * cd /path/to/your/project && php spark queue:work --queue=default --sleep=3 --tries=3 >> /dev/null 2>&1</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row aws_s3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="aws_access_key_id"><?= labels('aws_access_key_id', "AWS Access Key ID") ?></label>
                                    <input type="text" class=" form-control" name="aws_access_key_id" id="aws_access_key_id" value="<?= (isset($aws_access_key_id) && (ALLOW_VIEW_KEYS == 1)) ? $aws_access_key_id : 'your aws access key id' ?>" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="aws_access_key_id"><?= labels('aws_secret_access_key', "AWS Secret Access Key") ?></label>
                                    <input type="text" class=" form-control" name="aws_secret_access_key" id="aws_secret_access_key" value="<?= (isset($aws_secret_access_key) && (ALLOW_VIEW_KEYS == 1)) ? $aws_secret_access_key : 'you aws secret access key' ?>" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="aws_access_key_id"><?= labels('aws_default_region', "AWS Default Region") ?></label>
                                    <select name="aws_default_region" class="select2" id="aws_default_region">
                                        <option value="us-east-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'us-east-1' ? 'selected' : '' ?>>US East (N. Virginia) - us-east-1</option>
                                        <option value="us-east-2" <?php echo  isset($aws_default_region) && $aws_default_region == 'us-east-2' ? 'selected' : '' ?>>US East (Ohio) - us-east-2</option>
                                        <option value="us-west-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'us-west-1' ? 'selected' : '' ?>>US West (N. California) - us-west-1</option>
                                        <option value="us-west-2" <?php echo  isset($aws_default_region) && $aws_default_region == 'us-west-2' ? 'selected' : '' ?>>US West (Oregon) - us-west-2</option>
                                        <option value="ca-central-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'ca-central-1' ? 'selected' : '' ?>>Canada (Central) - ca-central-1</option>
                                        <option value="ca-central-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'ca-central-1' ? 'selected' : '' ?>>Canada (West) - ca-central-1</option>
                                        <option value="us-gov-west-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'us-gov-west-1' ? 'selected' : '' ?>>GovCloud (US-West) - us-gov-west-1</option>
                                        <option value="us-gov-east-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'us-gov-east-1' ? 'selected' : '' ?>>GovCloud (US-East) - us-gov-east-1</option>
                                        <option value="mx-central-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'mx-central-1' ? 'selected' : '' ?>>Mexico (Central) - mx-central-1</option>
                                        <option value="sa-east-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'sa-east-1' ? 'selected' : '' ?>>Sao Paulo, Brazil - sa-east-1</option>
                                        <option value="eu-west-2" <?php echo  isset($aws_default_region) && $aws_default_region == 'eu-west-2' ? 'selected' : '' ?>>London, UK - eu-west-2</option>
                                        <option value="eu-central-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'eu-central-1' ? 'selected' : '' ?>>Frankfurt, Germany - eu-central-1</option>
                                        <option value="eu-west-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'eu-west-1' ? 'selected' : '' ?>>Ireland - eu-west-1</option>
                                        <option value="eu-west-3" <?php echo  isset($aws_default_region) && $aws_default_region == 'eu-west-3' ? 'selected' : '' ?>>Paris, France - eu-west-3</option>
                                        <option value="eu-north-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'eu-north-1' ? 'selected' : '' ?>>Stockholm, Sweden - eu-north-1</option>
                                        <option value="eu-south-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'eu-south-1' ? 'selected' : '' ?>>Milan, Italy - eu-south-1</option>
                                        <option value="eu-south-2" <?php echo  isset($aws_default_region) && $aws_default_region == 'eu-south-2' ? 'selected' : '' ?>>Spain - eu-south-2</option>
                                        <option value="me-south-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'me-south-1' ? 'selected' : '' ?>>Bahrain - me-south-1</option>
                                        <option value="af-south-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'ap-east-1' ? 'selected' : '' ?>>Cape Town, South Africa - af-south-1</option>
                                        <option value="ap-east-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'ap-east-1' ? 'selected' : '' ?>>Hong Kong SAR, China - ap-east-1</option>
                                        <option value="ap-northeast-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'ap-northeast-1' ? 'selected' : '' ?>>Tokyo, Japan - ap-northeast-1</option>
                                        <option value="ap-northeast-2" <?php echo  isset($aws_default_region) && $aws_default_region == 'ap-northeast-2' ? 'selected' : '' ?>>Seoul, South Korea - ap-northeast-2</option>
                                        <option value="ap-southeast-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'ap-southeast-2' ? 'selected' : '' ?>>Singapore - ap-southeast-1</option>
                                        <option value="ap-southeast-2" <?php echo  isset($aws_default_region) && $aws_default_region == 'us-east-1' ? 'selected' : '' ?>>Sydney, Australia - ap-southeast-2</option>
                                        <option value="ap-south-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'ap-south-1' ? 'selected' : '' ?>>Mumbai, India - ap-south-1</option>
                                        <option value="ap-southeast-3" <?php echo  isset($aws_default_region) && $aws_default_region == 'ap-southeast-3' ? 'selected' : '' ?>>Jakarta, Indonesia - ap-southeast-3</option>
                                        <option value="cn-north-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'cn-north-1' ? 'selected' : '' ?>>Beijing, China - cn-north-1</option>
                                        <option value="cn-northwest-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'cn-northwest-1' ? 'selected' : '' ?>>Ningxia, China - cn-northwest-1</option>
                                        <option value="ap-northeast-3" <?php echo  isset($aws_default_region) && $aws_default_region == 'ap-northeast-3' ? 'selected' : '' ?>>Osaka-Local, Japan - ap-northeast-3</option>
                                        <option value="ap-southeast-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'ap-southeast-1' ? 'selected' : '' ?>>Singapore - ap-southeast-1</option>
                                        <option value="ap-southeast-2" <?php echo  isset($aws_default_region) && $aws_default_region == 'ap-southeast-2' ? 'selected' : '' ?>>Sydney, Australia - ap-southeast-2</option>
                                        <option value="ap-southeast-3" <?php echo  isset($aws_default_region) && $aws_default_region == 'ap-southeast-3' ? 'selected' : '' ?>>Jakarta, Indonesia - ap-southeast-3</option>
                                        <option value="ap-northeast-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'ap-northeast-1' ? 'selected' : '' ?>>Tokyo, Japan - ap-northeast-1</option>
                                        <option value="ap-northeast-2" <?php echo  isset($aws_default_region) && $aws_default_region == 'ap-northeast-2' ? 'selected' : '' ?>>Seoul, South Korea - ap-northeast-2</option>
                                        <option value="ap-northeast-3" <?php echo  isset($aws_default_region) && $aws_default_region == 'ap-northeast-3' ? 'selected' : '' ?>>Osaka-Local, Japan - ap-northeast-3</option>
                                        <option value="ap-south-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'ap-south-1' ? 'selected' : '' ?>>Mumbai, India - ap-south-1</option>
                                        <option value="ap-east-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'ap-east-1' ? 'selected' : '' ?>>Hong Kong SAR, China - ap-east-1</option>
                                        <option value="cn-north-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'cn-north-1' ? 'selected' : '' ?>>Beijing, China - cn-north-1</option>
                                        <option value="cn-northwest-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'cn-northwest-1' ? 'selected' : '' ?>>Ningxia, China - cn-northwest-1</option>
                                        <option value="eu-central-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'eu-central-1' ? 'selected' : '' ?>>Frankfurt, Germany - eu-central-1</option>
                                        <option value="eu-west-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'eu-west-1' ? 'selected' : '' ?>>Ireland - eu-west-1</option>
                                        <option value="eu-west-2" <?php echo  isset($aws_default_region) && $aws_default_region == 'eu-west-2' ? 'selected' : '' ?>>London, UK - eu-west-2</option>
                                        <option value="eu-west-3" <?php echo  isset($aws_default_region) && $aws_default_region == 'eu-west-3' ? 'selected' : '' ?>>Paris, France - eu-west-3</option>
                                        <option value="eu-north-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'eu-north-1' ? 'selected' : '' ?>>Stockholm, Sweden - eu-north-1</option>
                                        <option value="eu-south-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'eu-south-1' ? 'selected' : '' ?>>Milan, Italy - eu-south-1</option>
                                        <option value="eu-south-2" <?php echo  isset($aws_default_region) && $aws_default_region == 'eu-south-2' ? 'selected' : '' ?>>Spain - eu-south-2</option>
                                        <option value="me-south-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'me-south-1' ? 'selected' : '' ?>>Bahrain - me-south-1</option>
                                        <option value="af-south-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'af-south-1' ? 'selected' : '' ?>>Cape Town, South Africa - af-south-1</option>
                                        <option value="sa-east-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'sa-east-1' ? 'selected' : '' ?>>Sao Paulo, Brazil - sa-east-1</option>
                                        <option value="ca-central-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'ca-central-1' ? 'selected' : '' ?>>Canada (Central) - ca-central-1</option>
                                        <option value="us-gov-west-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'us-gov-west-1' ? 'selected' : '' ?>>GovCloud (US-West) - us-gov-west-1</option>
                                        <option value="us-gov-east-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'us-gov-east-1' ? 'selected' : '' ?>>GovCloud (US-East) - us-gov-east-1</option>
                                        <option value="us-east-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'us-east-1' ? 'selected' : '' ?>>US East (N. Virginia) - us-east-1</option>
                                        <option value="us-east-2" <?php echo  isset($aws_default_region) && $aws_default_region == 'us-east-2' ? 'selected' : '' ?>>US East (Ohio) - us-east-2</option>
                                        <option value="us-west-1" <?php echo  isset($aws_default_region) && $aws_default_region == 'us-west-1' ? 'selected' : '' ?>>US West (N. California) - us-west-1</option>
                                        <option value="us-west-2" <?php echo  isset($aws_default_region) && $aws_default_region == 'us-west-2' ? 'selected' : '' ?>>US West (Oregon) - us-west-2</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="aws_access_key_id"><?= labels('aws_bucket', "AWS Bucket") ?></label>
                                    <input type="text" class=" form-control" name="aws_bucket" id="aws_bucket" value="<?= (isset($aws_bucket) && (ALLOW_VIEW_KEYS == 1)) ? $aws_bucket : 'your aws bucket' ?>" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="aws_access_key_id"><?= labels('aws_url', "AWS URL") ?></label>
                                    <input type="text" class=" form-control" name="aws_url" id="aws_url" value="<?= (isset($aws_url) && (ALLOW_VIEW_KEYS == 1)) ? $aws_url : 'your_aws_url' ?>" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xl-12 mb-md-3 mb-sm-3 mb-3">
                <div class="card h-100">
                    <div class="row border_bottom_for_cards m-0">
                        <div class="col ">
                            <div class="toggleButttonPostition"><?= labels('deep_link_setting', "Deep Link Settings") ?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        
                        <div class="row">
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="schema"><?= labels('schema', "Schema") ?></label>
                                    <small class="text-grey"><?= labels('not_for_deeplink', 'Note: Please add your scheme here using a single word in lowercase (e.g., edemand).') ?></small>

                                    <input type="text" class=" form-control" name="schema_for_deeplink" id="schema" value="<?= (isset($schema_for_deeplink) && (ALLOW_VIEW_KEYS == 1)) ? $schema_for_deeplink : 'your schema' ?>" />
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php if ($permissions['update']['settings'] == 1) : ?>
            <div class="row mb-3">
                <div class="col-md d-flex justify-content-end">
                    <input type='submit' name='update' id='update' value='<?= labels('save_changes', "Save") ?>' class='btn btn-lg bg-new-primary' />
                </div>
            </div>
        <?php endif; ?>
        <?= form_close() ?>
    </section>
</div>
<script>
    function test() {
        $('.custome_reset').attr('value', '');
    }
    $('#otp_system').on('change', function() {
        this.value = this.checked ? 1 : 0;
    }).change();
</script>
<script>
    $(document).ready(function() {
        $('#file_transfer_process').siblings('.switchery').addClass('no-content').removeClass('yes-content');
    });
    $(function() {
        $('.fa').popover({
            trigger: "hover"
        });
    })
    if (<?= isset($image_compression_preference) && $image_compression_preference == 1 ? 'true' : 'false' ?>) {
        $("#image_compression_quality_input").show();
    } else {
        $("#image_compression_quality_input").hide();
    }
    $("#image_compression_preference").change(function() {
        if (this.value == 1) {
            $("#image_compression_quality_input").show();
        } else {
            $("#image_compression_quality_input").hide();
        }
    });
    $(document).ready(function() {
        // Assuming the PHP variable `$file_manager` is passed to the JavaScript as a global variable
        var fileManager = '<?php echo  isset($file_manager) ? $file_manager : 'local_server' ?>';
        // Check if `fileManager` is defined and equals 'aws_s3'
        if (typeof fileManager !== 'undefined' && fileManager === 'aws_s3') {
            $('.aws_s3').show();
        } else {
            $('.aws_s3').hide();
        }
        // Handle changes to the file_manager select element
        $('#file_manager').change(function() {
            var selectedValue = $(this).val();
            $('.aws_s3').toggle(selectedValue === 'aws_s3');
        });
    });
</script>
<script>
    function handleSwitchChange(checkbox) {
        var isChecked = checkbox.checked;
        var hiddenInput = document.getElementById('file_transfer_process_value');
        hiddenInput.value = isChecked ? "1" : "0";
        var switchery = $(checkbox).closest('.form-group').find('.switchery');
        if (isChecked) {
            $('#file_transfer_note').show();
            switchery.addClass('yes-content').removeClass('no-content');
        } else {
            $('#file_transfer_note').hide();
            switchery.addClass('no-content').removeClass('yes-content');
        }
    }
    $(document).ready(function() {
        // var checkbox = $('#file_transfer_process')[0];
        // handleSwitchChange(checkbox); // Initialize state
        $('#file_transfer_process').on('change', function() {
            handleSwitchChange(this);
        });
    });
</script>