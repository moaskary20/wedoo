<div class="main-content">
    <!-- ------------------------------------------------------------------- -->
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('add_provider', "Add Provider") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/partners') ?>"><i class="fas fa-handshake text-warning"></i> <?= labels('provider', 'Provider') ?></a></div>
                <div class="breadcrumb-item"><?= labels('add_provider', " Add Provider") ?></a></div>
            </div>
        </div>
        <?= form_open('/admin/partner/insert_partner', ['method' => "post", 'class' => 'add-provider-with-subscription', 'id' => 'add_partner', 'enctype' => "multipart/form-data"]); ?>
        <div class="row">
            <div class="col-lg-8 col-md-12 col-sm-12">
                <div class="card">
                    <div class="row pl-3">
                        <div class="col border_bottom_for_cards">
                            <div class="toggleButttonPostition"><?= labels('provider_information', 'Provider Information') ?></div>
                        </div>
                        <div class="col d-flex justify-content-end mr-3 mt-4 border_bottom_for_cards">
                            <input type="checkbox" class="status-switch" name="is_approved" checked>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="company " class="required"><?= labels('company_name', 'Company Name') ?></label>
                                    <input id="company_name" value="<?= isset($partner_details['company_name']) ? $partner_details['company_name'] : "" ?> " placeholder="<?= labels('enter', 'Enter ') ?> <?= labels('company_name', 'the company name ') ?> <?= labels('here', ' Here ') ?>" class="form-control" type="text" name="company_name" placeholder="<?= labels('enter', 'Enter ') ?> <?= labels('company_name', 'the company name ') ?> <?= labels('here', ' Here ') ?>" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="provider_slug" class="required"><?= labels('slug', 'Slug') ?></label>
                                    <input id="provider_slug" class="form-control" value="<?= isset($partner_details['slug']) ? $partner_details['slug'] : "" ?>" type="text" name="provider_slug" placeholder="<?= labels('enter_the_slug', 'Enter the slug') ?> ">
                                </div>
                            </div>



                        </div>
                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="type" class="required"><?= labels('type', 'Type') ?></label>
                                    <select class="select2" name="type" id="type" required>
                                        <option disabled selected><?= labels('select_type', 'Select Type') ?></option>
                                        <option value="0" <?php echo  isset($partner_details['type']) && $partner_details['type'] == '0' ? 'selected' : '' ?>><?= labels('individual', 'Individual') ?></option>
                                        <option value="1" <?php echo  isset($partner_details['type']) && $partner_details['type'] == '1' ? 'selected' : '' ?>><?= labels('organization', 'Organization') ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="visiting_charges " class="required"><?= labels('visiting_charges', 'Visiting Charges') ?><strong>( <?= $currency ?> )</strong>
                                    </label>
                                    <i data-content="<?= labels('data_content_for_visiting_charge', 'The customer will pay these fixed charges for every booking made at their doorstep.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                                    <input id="visiting_charges" class="form-control" type="number" name="visiting_charges" min="0" value=<?= isset($partner_details['visiting_charges']) ? $partner_details['visiting_charges'] : "" ?> oninput="this.value = Math.abs(this.value)" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('visiting_charges', 'Visiting Charges') ?> <?= labels('here', ' Here ') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="advance_booking_days" class="required"><?= labels('advance_booking_days', 'Advance Booking Days') ?></label>
                                    <i data-content="<?= labels('data_content_for_advance_booking_day', 'Customers can book a service in advance for up to X days. For example, if you set it to 5 days, customers can book a service starting from today up to the next 5 days. During this period, only the available dates and time slots will be visible for booking.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                                    <input id="advance_booking_days" class="form-control" type="number" name="advance_booking_days" min="1" oninput="this.value = Math.abs(this.value)" value=<?= isset($partner_details['advance_booking_days']) ? $partner_details['advance_booking_days'] : "" ?> placeholder="<?= labels('enter', 'Enter') ?> <?= labels('advance_booking_days', 'Advance Booking Days') ?> <?= labels('here', ' Here ') ?>" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="number_of_members" class="required"><?= labels('number_Of_members', 'Number of Members') ?></label>
                                    <i data-content="<?= labels('data_content_for_number_of_member', 'Currently, we\'re only gathering the total number of providers members for reference. Later on, we intend to use this information for future updates.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                                    <input id="number_of_members" class="form-control" type="number" name="number_of_members" min="0" value=<?= isset($partner_details['number_of_members']) ? $partner_details['number_of_members'] : "" ?> oninput="this.value = Math.abs(this.value)" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('number_Of_members', 'Number of Members') ?> <?= labels('here', ' Here ') ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="number_of_members" class="required"><?= labels('at_store', 'At Store') ?></label>
                                    <i data-content=" <?= labels('data_content_for_at_store', 'The provider needs to perform the service at their store. The customer will arrive at the store on a specific date and time.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                                    <input type="checkbox" id="at_store" name="at_store" class="status-switch" <?= $partner_details['at_store'] == "1" ? 'checked' : '' ?>>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="at_doorstep" class="required"><?= labels('at_doorstep', 'At Doorstep') ?></label>
                                    <i data-content="<?= labels('data_content_for_at_doorstep', 'The provider has to go to the customer\'s place to do the job. They must arrive at the customer\'s place on a set date and time.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                                    <input type="checkbox" id="at_doorstep" name="at_doorstep" class="status-switch" <?= $partner_details['at_doorstep'] == "1" ? 'checked' : '' ?>>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="need_approval_for_the_service" class="required"><?= labels('need_approval_for_the_service', 'Need approval for the service ?') ?></label>
                                    <i data-content="<?= labels('data_content_need_approval_for_the_service', 'If enabled, the admin must approve services added by the provider. After approval, the services will be visible to the customer. If disabled, services will instantly appear in the customer app.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                                    <input type="checkbox" id="need_approval_for_the_service" name="need_approval_for_the_service" class="status-switch" <?= $partner_details['need_approval_for_the_service'] == "1" ? 'checked' : '' ?>>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="about" class="required"><?= labels('about_provider', 'About Provider') ?></label>
                                    <textarea id="about" style="min-height:60px" class="form-control" required type="text" name="about" rowspan="10" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('about_provider', 'About Provider') ?> <?= labels('here', ' Here ') ?>"> <?= isset($partner_details['about']) ? $partner_details['about'] : "" ?></textarea>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label for="Description" class=""><?= labels('description', 'Description') ?></label>
                                <textarea rows=10 class='form-control h-50 summernotes custome_reset' name="long_description"><?= isset($service['long_description']) ? $service['long_description'] : '' ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-12 d-flex w-100">
                <div class="card w-100 ">
                    <div class="row pl-3">
                        <div class="col mb-3 " style="border-bottom: solid 1px #e5e6e9;">
                            <div class="toggleButttonPostition"><?= labels('images', 'Images') ?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="name" class="required"><?= labels('image', 'Image') ?> </label><br>
                                    <input type="file" class="filepond" name="image" id="image" accept="image/*" required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="banner_image" class="required"><?= labels('banner_image', 'Banner Image') ?></label><br>
                                    <input type="file" class="filepond" name="banner_image" id="banner_image" accept="image/*" required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group"> <label for="image" class=""><?= labels('other_images', 'Other Image') ?></label>
                                    <input type="file" name="other_service_image_selector[]" class="filepond logo" id="other_service_image_selector" accept="image/*" multiple>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8 col-md-12 col-sm-12">
                <div class="col-md-12 p-0">
                    <div class="card">
                        <div class="col mb-3" style="border-bottom: solid 1px #e5e6e9;">
                            <div class="toggleButttonPostition"><?= labels('working_days', 'Working Days') ?>
                                <i data-content=" <?= labels('data_content_for_working_days', "Please include the opening and closing times of the service provider and make it On. When customers book services, they'll receive a 30-minute time slot based on the available times for each day.") ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <div class="row mb-3">
                                            <div class="col-md-2">
                                                <label for="0"><?= labels('monday', 'Monday') ?></label>
                                            </div>
                                            <div class="col-md-3 col-sm-3 col-4 ">
                                                <input type="time" required id="0" class="form-control start_time" name="start_time[]" value="<?php echo (isset($partner_timings[6]['opening_time']) ? $partner_timings[6]['opening_time'] : '00:00'); ?>">
                                            </div>
                                            <div class="col-md-1 col-sm-2 mt-2 col-4 text-center">
                                                <?= labels('to', 'To') ?>
                                            </div>
                                            <div class="col-md-3 col-sm-3 col-4 endTime">
                                                <input type="time" id="0" required class="form-control end_time" name="end_time[]" value="<?php echo (isset($partner_timings[6]['closing_time']) ? $partner_timings[6]['closing_time'] : '00:00') ?>">
                                            </div>
                                            <div class="col-md-2 col-sm-3 m-sm-1 mt-3">
                                                <div class="form-check mt-3">
                                                    <div class="button b2 working-days_checkbox" id="button-11">
                                                        <input type="checkbox" class="checkbox check_box" name="monday" id="flexCheckDefault" <?php echo ($partner_timings[6]['is_open'] == "1") ? 'checked' : ''; ?> />
                                                        <div class="knobs">
                                                            <span></span>
                                                        </div>
                                                        <div class="layer"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-2">
                                            <label for="1"> <?= labels('tuesday', 'Tuesday') ?></label>
                                        </div>
                                        <div class="col-md-3 col-sm-3 col-4">
                                            <input type="time" id="1" class="form-control start_time" name="start_time[]" value="<?php echo (isset($partner_timings[5]['opening_time']) ? $partner_timings[5]['opening_time'] : '00:00') ?>">
                                        </div>
                                        <div class="col-md-1 col-sm-2 mt-2 col-4 text-center">
                                            <?= labels('to', 'To') ?>
                                        </div>
                                        <div class="col-md-3 col-sm-3 col-4 endTime">
                                            <input type="time" id="01" class="form-control end_time" name="end_time[]" value="<?php echo (isset($partner_timings[5]['closing_time']) ? $partner_timings[5]['closing_time'] : '00:00') ?>">
                                        </div>
                                        <div class="col-md-2 col-sm-3 m-sm-1 mt-3">
                                            <div class="form-check mt-3">
                                                <div class="button b2 working-days_checkbox" id="button-11">
                                                    <input type="checkbox" class="checkbox check_box" name="tuesday" id="flexCheckDefault" <?php echo ($partner_timings[5]['is_open'] == "1") ? 'checked' : ''; ?> />
                                                    <div class="knobs">
                                                        <span></span>
                                                    </div>
                                                    <div class="layer"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-2">
                                            <label for="2"> <?= labels('wednesday', 'Wednesday') ?></label>
                                        </div>
                                        <div class="col-md-3 col-sm-3 col-4">
                                            <input type="time" id="2" class="form-control start_time" name="start_time[]" value="<?php echo (isset($partner_timings[4]['opening_time']) ? $partner_timings[4]['opening_time'] : '00:00') ?>">
                                        </div>
                                        <div class="col-md-1 col-sm-2 mt-2 col-4 text-center">
                                            <?= labels('to', 'To') ?>
                                        </div>
                                        <div class="col-md-3 col-sm-3 col-4 endTime">
                                            <input type="time" id="02" class="form-control end_time" name="end_time[]" value="<?php echo (isset($partner_timings[4]['closing_time']) ? $partner_timings[4]['closing_time'] : '00:00') ?>">
                                        </div>
                                        <div class="col-md-2 col-sm-3 m-sm-1 mt-3">
                                            <div class="form-check mt-3">
                                                <div class="button b2 working-days_checkbox" id="button-11">
                                                    <input type="checkbox" class="checkbox check_box" name="wednesday" id="flexCheckDefault" <?php echo ($partner_timings[4]['is_open'] == "1") ? 'checked' : ''; ?> />
                                                    <div class="knobs">
                                                        <span></span>
                                                    </div>
                                                    <div class="layer"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-2">
                                            <label for="3"> <?= labels('thursday', 'Thursday') ?></label>
                                        </div>
                                        <div class="col-md-3 col-sm-3 col-4">
                                            <input type="time" id="3" class="form-control start_time" name="start_time[]" value="<?php echo (isset($partner_timings[3]['opening_time']) ? $partner_timings[3]['opening_time'] : '00:00') ?>">
                                        </div>
                                        <div class="col-md-1 col-sm-2 mt-2 col-4 text-center">
                                            <?= labels('to', 'To') ?>
                                        </div>
                                        <div class="col-md-3 col-sm-3 col-4 endTime">
                                            <input type="time" class="form-control end_time" name="end_time[]" value="<?php echo (isset($partner_timings[3]['closing_time']) ? $partner_timings[3]['closing_time'] : '00:00') ?>">
                                        </div>
                                        <div class="col-md-2 col-sm-3 m-sm-1 mt-4">
                                            <div class="form-check mt-3">
                                                <div class="button b2 working-days_checkbox" id="button-11">
                                                    <input type="checkbox" class="checkbox check_box" name="thursday" id="flexCheckDefault" <?php echo ($partner_timings[3]['is_open'] == "1") ? 'checked' : ''; ?> />
                                                    <div class="knobs">
                                                        <span></span>
                                                    </div>
                                                    <div class="layer"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-2">
                                            <label for="4"> <?= labels('friday', 'Friday') ?></label>
                                        </div>
                                        <div class="col-md-3 col-sm-3 col-4">
                                            <input type="time" id="4" class="form-control start_time" name="start_time[]" value="<?php echo (isset($partner_timings[2]['opening_time']) ? $partner_timings[2]['opening_time'] : '00:00') ?>">
                                        </div>
                                        <div class="col-md-1 col-sm-2 mt-2 col-4 text-center">
                                            <?= labels('to', 'To') ?>
                                        </div>
                                        <div class="col-md-3 col-sm-3 col-4 endTime">
                                            <input type="time" class="form-control end_time" name="end_time[]" value="<?php echo (isset($partner_timings[2]['closing_time']) ? $partner_timings[2]['closing_time'] : '00:00') ?>">
                                        </div>
                                        <div class="col-md-2 col-sm-3 m-sm-1 mt-3">
                                            <div class="form-check mt-3">
                                                <div class="button b2 working-days_checkbox" id="button-11">
                                                    <input type="checkbox" class="checkbox check_box" name="friday" id="flexCheckDefault" <?php echo ($partner_timings[2]['is_open'] == "1") ? 'checked' : ''; ?> />
                                                    <div class="knobs">
                                                        <span></span>
                                                    </div>
                                                    <div class="layer"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-2">
                                            <label for="5"> <?= labels('saturday', 'Saturday') ?></label>
                                        </div>
                                        <div class="col-md-3 col-sm-3 col-4">
                                            <input type="time" id="5" class="form-control start_time" name="start_time[]" value="<?php echo (isset($partner_timings[1]['opening_time']) ? $partner_timings[1]['opening_time'] : '00:00') ?>">
                                        </div>
                                        <div class="col-md-1 col-sm-2 mt-2 col-4 text-center">
                                            <?= labels('to', 'To') ?>
                                        </div>
                                        <div class="col-md-3 col-sm-3 col-4 endTime">
                                            <input type="time" class="form-control end_time" name="end_time[]" value="<?php echo (isset($partner_timings[1]['closing_time']) ? $partner_timings[1]['closing_time'] : '00:00') ?>">
                                        </div>
                                        <div class="col-md-2 col-sm-3 m-sm-1 mt-3">
                                            <div class="form-check mt-3">
                                                <div class="button b2 working-days_checkbox" id="button-11">
                                                    <input type="checkbox" class="checkbox check_box" name="saturday" id="flexCheckDefault" <?php echo ($partner_timings[1]['is_open'] == "1") ? 'checked' : ''; ?> />
                                                    <div class="knobs">
                                                        <span></span>
                                                    </div>
                                                    <div class="layer"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-2">
                                            <label for="6"> <?= labels('sunday', 'Sunday') ?></label>
                                        </div>
                                        <div class="col-md-3 col-sm-3 col-4">
                                            <input type="time" id="6" class="form-control start_time" name="start_time[]" value="<?php echo (isset($partner_timings[0]['opening_time']) ? $partner_timings[0]['opening_time'] : '00:00') ?>">
                                        </div>
                                        <div class="col-md-1 col-sm-2 mt-2 col-4 text-center">
                                            <?= labels('to', 'To') ?>
                                        </div>
                                        <div class="col-md-3 col-sm-3 col-4 endTime">
                                            <input type="time" class="form-control end_time" name="end_time[]" value="<?php echo (isset($partner_timings[0]['closing_time']) ? $partner_timings[0]['closing_time'] : '00:00') ?>">
                                        </div>
                                        <div class="col-md-2 col-sm-3 m-sm-1 mt-3">
                                            <div class="form-check mt-3">
                                                <div class="button b2 working-days_checkbox" id="button-11">
                                                    <input type="checkbox" class="checkbox check_box" name="sunday" id="flexCheckDefault" <?php echo ($partner_timings[0]['is_open'] == "1") ? 'checked' : ''; ?> />
                                                    <div class="knobs">
                                                        <span></span>
                                                    </div>
                                                    <div class="layer"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 p-0">
                    <div class="card">
                        <div class="col mb-3" style="border-bottom: solid 1px #e5e6e9;">
                            <div class="toggleButttonPostition"><?= labels('personal_details', 'Personal Details') ?> </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="name" class="required"><?= labels('name', 'Name') ?></label>
                                        <input id="username" class="form-control" type="text" name="username" value="<?= isset($personal_details['username']) ? $personal_details['username'] : "" ?>" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('name', 'Name') ?> <?= labels('here', ' Here ') ?>" required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="email" class="required"><?= labels('email', 'Email') ?></label>
                                        <input id="email" class="form-control" type="email" name="email" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('email', 'Email') ?> <?= labels('here', ' Here ') ?>" value="<?= ((defined('ALLOW_VIEW_KEYS') && ALLOW_VIEW_KEYS == 0)) ? "XXXX@gmail.com" : (isset($personal_details['email']) ? $personal_details['email'] : "") ?>" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone" class="required"><?= labels('phone_number', 'Phone Number') ?></label>
                                        <?php
                                        $country_codes =  fetch_details('country_codes');
                                        $system_country_code = fetch_details('country_codes', ['is_default' => 1])[0];
                                        $default_country_code = isset($system_country_code['code']) ? $system_country_code['code'] : "+91";
                                        ?>
                                        <div class="input-group">
                                            <select class=" col-md-3 form-control" name="country_code" id="country_code">
                                                <?php
                                                foreach ($country_codes as $key => $country_code) {
                                                    $code = $country_code['code'];
                                                    $name = $country_code['name'];
                                                    $selected = ($default_country_code == $country_code['code']) ? "selected" : "";
                                                    echo "<option $selected value='$code'>$code || $name</option>";
                                                }
                                                ?>
                                            </select>
                                            <input id="phone" class="form-control" type="text" min="4" value="<?= $personal_details['phone'] ?>" maxlength="16" name="phone" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('phone_number', 'Phone Number') ?> <?= labels('here', ' Here ') ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password" class="required"><?= labels('password', 'Password') ?></label>
                                        <input id="password" class="form-control" type="password" name="password" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('password', 'Password') ?> <?= labels('here', ' Here ') ?>" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="passport" class="required"><?= labels('passport', 'Passport') ?></label><br>
                                        <input type="file" class="filepond" name="passport" id="passport" accept="image/*" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="national_id" class="required"><?= labels('national_identity', 'National Identity') ?></label><br>
                                        <input type="file" class="filepond" name="national_id" id="national_id" accept="image/*" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="national_id" class="required"><?= labels('address_id', 'Address Identity') ?></label><br>
                                        <input type="file" class="filepond" name="address_id" id="address_id" accept="image/*" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-12 col-sm-12 mb-30">
                <div class="card w-100 h-100">
                    <div class="row pl-3">
                        <div class="col mb-3 " style="border-bottom: solid 1px #e5e6e9;">
                            <div class="toggleButttonPostition"><?= labels('provider_location_information', "Location Information") ?>
                                <i data-content=" <?= labels('data_content_for_location', "Customers will see providers near them based on the providers' locations.") ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div id="map_wrapper_div_partner">
                                    <div id="partner_map">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12  mt-3">
                                <div class="form-group">
                                    <label for="partner_location" class="required"><?= labels('current_location', 'Current Location') ?></label>
                                    <input id="partner_location" class="form-control" type="text" name="partner_location">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <div class="cities" id="cities_select">
                                        <label for="city" class="required"><?= labels('city', 'City') ?></label>
                                        <input type="text" name="city" class="form-control" placeholder="<?= labels('enter_your_providers_city_name', 'Enter your provider\'s city name') ?>" value=<?= isset($personal_details['city']) ? $personal_details['city'] : "" ?> required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="partner_latitude" class="required"> <?= labels('latitude', 'Latitude') ?></label>
                                    <input id="partner_latitude" class="form-control" type="text" name="partner_latitude" placeholder="<?= labels('latitude', 'Latitude') ?>" value=<?= isset($personal_details['latitude']) ? $personal_details['latitude'] : "" ?> required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="partner_longitude" class="required"><?= labels('longitude', 'Longitude') ?></label>
                                    <input id="partner_longitude" class="form-control" type="text" name="partner_longitude" placeholder="<?= labels('longitude', 'Longitude') ?>" value=<?= isset($personal_details['longitude']) ? $personal_details['longitude'] : "" ?> required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="address" class="required"><?= labels('address', 'Address') ?></label>
                                    <textarea id="address" class="form-control" style="min-height:60px" name="address" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('address', 'Address') ?> <?= labels('here', ' Here ') ?>" required> <?= isset($partner_details['address']) ? $partner_details['address'] : "" ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row ">
            <div class="col-md-12">
                <div class="card">
                    <div class="col mb-3" style="border-bottom: solid 1px #e5e6e9;">
                        <div class="toggleButttonPostition"><?= labels('bank_details', 'Bank Details') ?></div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tax_name" class="required"><?= labels('tax_name', 'Tax Name') ?></label>
                                    <input id="tax_name" class="form-control" type="text" name="tax_name" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('tax_name', 'Tax Name') ?> <?= labels('here', ' Here ') ?>" value=<?= isset($partner_details['tax_name']) ? $partner_details['tax_name'] : "" ?> required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tax_number" class="required"> <?= labels('tax_number', 'Tax Number') ?></label>
                                    <input id="tax_number" class="form-control" type="text" name="tax_number" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('tax_number', 'Tax Number') ?> <?= labels('here', ' Here ') ?>" value=<?= isset($partner_details['tax_number']) ? $partner_details['tax_number'] : "" ?> required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="account_number" class="required"><?= labels('account_number', 'Account Number') ?></label>
                                    <input id="account_number" class="form-control" type="number" name="account_number" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('account_number', 'Account Number') ?> <?= labels('here', ' Here ') ?>" value=<?= isset($partner_details['account_number']) ? $partner_details['account_number'] : "" ?> required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="account_name" class="required"><?= labels('account_name', 'Account Name') ?></label>
                                    <input id="account_name" class="form-control" type="text" name="account_name" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('account_name', 'Account Name') ?> <?= labels('here', ' Here ') ?>" value=<?= isset($partner_details['account_name']) ? $partner_details['account_name'] : "" ?> required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="bank_code" class="required"><?= labels('bank_code', 'Bank Code') ?></label>
                                    <input id="bank_code" class="form-control" type="text" name="bank_code" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('bank_code', 'Bank Code') ?> <?= labels('here', ' Here ') ?>" value=<?= isset($partner_details['bank_code']) ? $partner_details['bank_code'] : "" ?> required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="bank_name" class="required"><?= labels('bank_name', 'Bank Name') ?></label>
                                    <input id="bank_name" class="form-control" type="text" name="bank_name" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('bank_name', 'Bank Name') ?> <?= labels('here', ' Here ') ?>" value=<?= isset($partner_details['bank_name']) ? $partner_details['bank_name'] : "" ?> required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="swift_code" class="required"><?= labels('swift_code', 'Swift Code') ?></label>
                                    <input id="swift_code" class="form-control" type="text" name="swift_code" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('swift_code', 'Swift Code') ?> <?= labels('here', ' Here ') ?>" value=<?= isset($partner_details['swift_code']) ? $partner_details['swift_code'] : "" ?> required>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="partner_id_for_sub_bar" id="partner_id_for_sub_bar" value="">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-info alert-has-icon">
                            <div class="alert-icon"><i class="far fa-lightbulb"></i></div>
                            <div class="alert-body">
                                <div class="alert-title"><?= labels('note', 'Note') ?></div>
                                <?= labels('provider_must_have_active_subscription', ' Provider must have active subscription for listing in app and web.') ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md d-flex justify-content-end">
                <button type="submit" class="btn btn-lg bg-new-primary submit_btn"><?= labels('add_provider', 'Add Provider') ?></button>
                <?= form_close() ?>
            </div>
        </div>
    </section>
</div>
<div class="modal fade" id="partner_subscriptions_add" tabindex="-1" role="dialog" aria-labelledby="partner_subscriptions_add" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl " role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle"><?= labels('change_renew_plan', 'Change / Renew Subscription Plan') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="background-color: #f4f6f9;">
                <div class="row">
                    <?php foreach ($subscription_details as $row) { ?>
                        <div class="col-md-6 mb-md-3">
                            <div class="plan d-flex flex-column h-100">
                                <div class="inner  h-100">
                                    <div class="plan_title">
                                        <b><?= $row['name'] ?></b>
                                    </div>
                                    <?php
                                    $price = calculate_subscription_price($row['id']);;
                                    ?>
                                    <h5>
                                        <p class="plan_price"><b><?= $currency ?><?= $price[0]['price_with_tax'] ?></b></p>
                                    </h5>
                                    <ul class="features mb-3">
                                        <li>
                                            <span class="icon">
                                                <svg height="24" width="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M0 0h24v24H0z" fill="none"></path>
                                                    <path fill="currentColor" d="M10 15.172l9.192-9.193 1.415 1.414L10 18l-6.364-6.364 1.414-1.414z"></path>
                                                </svg>
                                            </span>
                                            <span><strong>
                                                    <?php
                                                    if ($row['order_type'] == "unlimited") {
                                                        echo labels('enjoyUnlimitedOrders', "Unlimited Orders: No limits, just success.");
                                                    } else {
                                                        echo labels('enjoyGenerousOrderLimitOf', "Enjoy a generous order limit of") . " " . $row['max_order_limit'] . " " . labels('ordersDuringYourSubscriptionPeriod', "orders during your subscription period");
                                                    }
                                                    ?>
                                                </strong></span>
                                        </li>
                                        <li>
                                            <span class="icon">
                                                <svg height="24" width="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M0 0h24v24H0z" fill="none"></path>
                                                    <path fill="currentColor" d="M10 15.172l9.192-9.193 1.415 1.414L10 18l-6.364-6.364 1.414-1.414z"></path>
                                                </svg>
                                            </span>
                                            <span><strong>
                                                    <?php
                                                    if ($row['duration'] == "unlimited") {
                                                        echo labels('enjoySubscriptionForUnlimitedDays', "Lifetime Subscription – seize success without limits!");
                                                    } else {
                                                        echo labels('yourSubscriptionWillBeValidFor', "Your subscription will be valid for") . " " . $row['duration'] . " " . labels('days', "Days");
                                                    }
                                                    ?>
                                                </strong>
                                        </li>
                                        <li>
                                            <span class="icon">
                                                <svg height="24" width="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill="currentColor" d="M10 15.172l9.192-9.193 1.415 1.414L10 18l-6.364-6.364 1.414-1.414z"></path>
                                                </svg>
                                            </span>
                                            <strong>
                                                <?php
                                                if ($row['is_commision'] == "yes") {
                                                    echo labels('commissionWillBeAppliedToYourEarnings', "Commission will be applied to your earnings");
                                                } else {
                                                    echo labels('noNeedToPayExtraCommission', "Your income, your rules – no hidden commission charges on your profits");
                                                }
                                                ?>
                                            </strong>
                                        </li>
                                        <li>
                                            <span class="icon">
                                                <svg height="24" width="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M0 0h24v24H0z" fill="none"></path>
                                                    <path fill="currentColor" d="M10 15.172l9.192-9.193 1.415 1.414L10 18l-6.364-6.364 1.414-1.414z"></path>
                                                </svg>
                                            </span>
                                            <strong>
                                                <?php
                                                if ($row['is_commision'] == "yes") {
                                                    echo labels('commissionThreshold', "Pay on Delivery threshold: The Pay on Service option will be closed, once the cash of the " . $currency . $row['commission_threshold']) . " " . labels('AmountIsReached', " amount is reached");
                                                } else {
                                                    echo labels('noThresholdOnPayOnDeliveryAmount', "There is no threshold on the Pay on Service amount.");
                                                }
                                                ?>
                                            </strong>
                                        </li>
                                        <li>
                                            <span class="icon">
                                                <svg height="24" width="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M0 0h24v24H0z" fill="none"></path>
                                                    <path fill="currentColor" d="M10 15.172l9.192-9.193 1.415 1.414L10 18l-6.364-6.364 1.414-1.414z"></path>
                                                </svg>
                                            </span>
                                            <span>
                                                <strong>
                                                    <?php
                                                    if ($row['is_commision'] == "yes") {
                                                        echo $row['commission_percentage'] . "% " . labels('commissionWillBeAppliedToYourEarnings', "commission will be applied to your earnings.");
                                                    } else {
                                                        echo labels('noNeedToPayExtraCommission', "Your income, your rules – no hidden commission charges on your profits");
                                                    }
                                                    ?>
                                                </strong>
                                        </li>
                                        <?php if ($price[0]['tax_percentage'] != "0") { ?>
                                            <li>
                                                <span class="icon">
                                                    <svg height="24" width="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M0 0h24v24H0z" fill="none"></path>
                                                        <path fill="currentColor" d="M10 15.172l9.192-9.193 1.415 1.414L10 18l-6.364-6.364 1.414-1.414z"></path>
                                                    </svg>
                                                </span>
                                                <strong>
                                                    <?php
                                                    echo labels('tax_included', $price[0]['tax_percentage'] . "% tax included");
                                                    ?>
                                                </strong>
                                            </li>
                                        <?php     } ?>
                                        <a href="javascript:void(0);" class="toggle-description">
                                            <span class="icon" style="font-size: 11px;">
                                                <i class="fa-solid fa-eye fa-sm"></i>
                                                <i class="fa-solid fa-eye-slash fa-sm"></i>
                                            </span>
                                            <span class="text">View Description</span>
                                        </a>
                                        <div class="description">
                                            <?= $row['description'] ?>
                                        </div>
                                    </ul>
                                </div>
                                <form class="needs-validation" id="make_payment_for_subscription1" method="POST" action="<?= base_url('admin/assign_subscription_to_partner') ?>">
                                    <input type="hidden" name="stripe_key_id" id="stripe_key_id" value="pk_test_51Hh90WLYfObhNTTwooBHwynrlfiPo2uwxyCVqGNNCWGmpdOHuaW4rYS9cDldKJ1hxV5ik52UXUDSYgEM66OX45550065US7tRX" />
                                    <input id="subscription_id" name="subscription_id" class="form-control" value="<?= $row['id'] ?>" type="hidden" name="">
                                    <input id="payment_method" name="payment_method" class="form-control" value="stripe" type="hidden" name="">
                                    <input type="hidden" name="stripe_client_secret" id="stripe_client_secret" value="" />
                                    <input type="hidden" name="partner_id" id="partner_id" value="">
                                    <input type="hidden" name="stripe_payment_id" id="stripe_payment_id" value="" />
                                    <div class="card-footer mt-auto">
                                        <div class="form-group m-0 p-0">
                                            <button type="button" class="btn btn-block text-white" style="background-color:#344052;" onclick="confirmAssign(<?= $row['id'] ?>)">Assign</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"> <?= labels('close', 'Close') ?> </button>
                <button type="button" class="btn btn-primary"> <?= labels('save_changes', 'Close') ?></button>
            </div>
        </div>
    </div>
</div>
<style>
</style>
<script>
</script>
<script>
    $(document).ready(function() {
        $('#at_store').siblings('.switchery').addClass('active-content').removeClass('deactive-content');
        $('#at_doorstep').siblings('.switchery').addClass('active-content').removeClass('deactive-content');
        $('#need_approval_for_the_service').siblings('.switchery').addClass('deactive-content').removeClass('active-content');

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
        var need_approval_for_the_service = document.querySelector('#need_approval_for_the_service');
        need_approval_for_the_service.addEventListener('change', function() {
            handleSwitchChange(need_approval_for_the_service);
        });
        var atStore = document.querySelector('#at_store');
        var atDoorstep = document.querySelector('#at_doorstep');
        atDoorstep.addEventListener('change', function() {
            if (!atStore.checked && !atDoorstep.checked) {
                var switchery = atStore.nextElementSibling;
                switchery.classList.add('active-content');
                switchery.classList.remove('deactive-content');
                atStore.click();
                var switchery1 = atDoorstep.nextElementSibling;
                switchery1.classList.add('deactive-content');
                switchery1.classList.remove('active-content');
            } else {
                handleSwitchChange(atDoorstep);
            }
        });
        atStore.addEventListener('change', function() {
            if (!atStore.checked && !atDoorstep.checked) {
                var switchery = atDoorstep.nextElementSibling;
                switchery.classList.add('active-content');
                switchery.classList.remove('deactive-content');
                atDoorstep.click();
            } else {
                handleSwitchChange(atStore);
            }
        });
    });
    $('#type').change(function() {
        var doc = document.getElementById("type");
        if (doc.options[doc.selectedIndex].value == 0) {
            $("#number_of_members").val('1');
            $("#number_of_members").attr("readOnly", "readOnly");
        } else if (doc.options[doc.selectedIndex].value == 1) {
            $("#number_of_members").val('');
            $("#number_of_members").removeAttr("readOnly");
        }
    });
    $('.start_time').change(function() {
        var doc = $(this).val();

        $(this).parent().siblings(".endTime").children().attr('min', doc);
    });
</script>
<script>
    $(function() {
        $('.fa').popover({
            trigger: "hover"
        });
    })
</script>
<script>
    function confirmAssign(subscriptionId) {
        event.preventDefault();
        Swal.fire({
            title: "<?= labels('are_your_sure', 'Are you sure?') ?>",
            text: "Once you assign this subscription plan, you cannot assign again until the current plan expires. Choose wisely!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, proceed!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('subscription_id').value = subscriptionId;
                document.getElementById('make_payment_for_subscription1').submit();
            }
        });
    }

    $("#company_name").on("input", function() {
        let slug = generateSlug($(this).val());
        $("#provider_slug").val(slug);
    });
</script>