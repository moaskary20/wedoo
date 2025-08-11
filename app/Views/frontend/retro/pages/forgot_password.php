<?php
$data = get_settings('general_settings', true);
isset($data['primary_color']) && $data['primary_color'] != "" ?  $primary_color = $data['primary_color'] : $primary_color =  '#05a6e8';
isset($data['secondary_color']) && $data['secondary_color'] != "" ?  $secondary_color = $data['secondary_color'] : $secondary_color =  '#003e64';
isset($data['primary_shadow']) && $data['primary_shadow'] != "" ?  $primary_shadow = $data['primary_shadow'] : $primary_shadow =  '#05A6E8';
$authentication_mode = $data['authentication_mode'];

$session = \Config\Services::session();
$is_rtl = $session->get('is_rtl');
$language = $session->get('language');
$default_language = fetch_details('languages', ['is_default' => '1']);

// Only check default language's RTL status if no language is set in session
// Otherwise, use the explicit is_rtl value from session
if (empty($language) && !isset($is_rtl)) {
    $is_rtl = $default_language[0]['is_rtl'];
} elseif ($is_rtl === null) {
    // Fallback if is_rtl is not set but language is
    $is_rtl = 0;
}

// Convert to integer value for consistency
$is_rtl = (int)$is_rtl;

?>
<style>
    body {
        --primary-color: <?= $primary_color ?>;
        --secondary-color: <?= $secondary_color ?>;

    }

    .bg-primary {
        background-color: <?= $primary_color ?> !important;
        color: white;
    }
</style>
<div class="auth " style="overflow: hidden;">
    <div class="join_us_as_provider">
        <section class="section">
            <section class=" " data-aos='fade-up'>



                <div class="d-flex justify-content-<?= $is_rtl == 1 ? 'start' : 'end' ?> m-3 row mt-5">
                    <div class="col-12 col-sm-10 col-md-8 col-lg-8  col-xl-4 mt-5 <?= $is_rtl == 1 ? 'ms-md-5' : 'me-md-5' ?> ">
                        <?php
                        $data = get_settings('general_settings', true);
                        ?>
                        <div class="">
                            <div id="" class='alert'><?php echo $message; ?></div>
                        </div>
                        <div class="card  p-3 mb-5   " style="border-radius:8px">

                            <div class="row">

                                <div class="col-md-3 d-flex justify-content-end w-100"><?= labels('step', 'Step') ?>&nbsp;<span class="step">1&nbsp;</span>&nbsp;<?= labels('of', 'of') ?>&nbsp;3</div>
                            </div>
                            <div class="row" style="text-align:center;margin-bottom:50px;margin-top:30px;width:100%;padding:0!important;display:block;">
                                <img style="width:60%" src=" <?= isset($data['logo']) && $data['logo'] != "" ? base_url("public/uploads/site/" . $data['logo']) : base_url('public/backend/assets/img/news/img01.jpg') ?>" class="" alt="">
                            </div>
                            <div class="col-md">


                                <div class="card-body" id="step_1">
                                    <div id="send">

                                        <div class="form-group">
                                            <label for="number"><?= labels('phone_number', 'Phone Number') ?></label>

                                            <?php


                                            $country_codes =  fetch_details('country_codes');

                                            $system_country_code = fetch_details('country_codes', ['is_default' => 1])[0];

                                            if (!empty($system_country_code['code'])) {
                                                $system_country_code = $system_country_code['code'];
                                            } else {
                                                $system_country_code['code'] = +91;
                                                $system_country_code['name'] = 'IN';
                                            }


                                            ?>
                                            <div class="row">
                                                <div class="col-4 p-0">
                                                    <select class="form-control col-4" name="country_code" id="country_code">
                                                        <?php
                                                        foreach ($country_codes as $key => $country_code) {

                                                            $code = $country_code['code'];
                                                            $name = $country_code['name'];
                                                            $selected = ($system_country_code == $country_code['code']) ? "selected" : "";

                                                            echo "<option $selected value='$code'>$code || $name</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="col-8 p-0">
                                                    <input id="number" class="form-control" type="number" name="number" placeholder="<?= labels('enter_mobile_number', 'Enter Mobile Number') ?>">
                                                </div>

                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div id="rec"></div>
                                        </div>
                                        <div class="form-group">
                                            <button type="button" class="btn bg-primary btn-lg w-100 mt-2" id="sender_forgot_password"><?= labels('submit_number', 'Submit Number') ?></button>
                                        </div>
                                    </div>

                                    <div class="otp_show">
                                        <div class="form-group">
                                            <label for="otp"><?= labels('received_otp', 'Received OTP') ?></label>
                                            <input id="otp" class="form-control" type="number" name="otp">
                                        </div>
                                        <div class="form-group">
                                            <?php
                                            if ($authentication_mode == "sms_gateway") {
                                                $function_name = "sms_codeverify()";
                                            } else {
                                                $function_name = "codeverify()";
                                            }

                                            ?>
                                            <button type="button" class="btn bg-primary  text-white btn-lg w-100 mt-2" id="check" onclick="<?= $function_name; ?>"><?= labels('verify_otp', 'Verify Otp') ?></button>
                                        </div>
                                    </div>
                                </div>


                                <div class="card-body" id="step_2">
                                    <?= form_open('/auth/reset_password_otp', ['id' => 'forgot_password']); ?>


                                    <div class="row">
                                        <div class="form-group ">
                                            <input type="hidden" id="phone" class="form-control" name='phone' placeholder="Mobile Number" required min="0" readOnly />
                                            <input id="store_country_code" class="form-control" type="hidden" name="store_country_code">

                                            <label for='password' class="mb-2"><?= labels('password', 'Password') ?></label>
                                            <input type="password" id="password" class="form-control" name='password' placeholder="<?= labels('password', 'Password') ?>" required />
                                        </div>

                                        <div class="form-group " class="">
                                            <label for='password_confirm' class="mb-2"><?= labels('confirm_password', 'Confirm Password') ?></label>
                                            <input type="password" id="password_confirm" class="form-control" name='password_confirm' placeholder="<?= labels('confirm_password', 'Confirm Password') ?>" required />
                                        </div>
                                        <input type="hidden" id="userType" name="userType" value="">

                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn bg-primary btn-lg w-100 mt-2">
                                            <?= labels('submit', 'Submit') ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md d-flex justify-content-center">
                                    <?= labels('back_to', 'Back To') ?> &nbsp; <a href="<?= base_url('partner/login') ?>" class=""><b><?= labels('login', 'Login') ?></b></a><br>
                                </div>
                                <?php
                                if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                                ?>
                                    <div class="col-sm-12 mt-3">
                                        <div class="alert alert-warning mb-0">
                                            <b><?= labels('note', 'Note') ?>:</b> <?= labels('cannot_register_close_codecanyon_frame', 'If you cannot Register here, please close the codecanyon frame by clicking on') ?> <b>x <?= labels('remove_frame', 'Remove Frame') ?></b> <?= labels('button_top_right_corner', 'button from top right corner on the page or') ?> <a href="https://edemand.erestro.me/auth/create_user" target="_blank">&gt;&gt; <?= labels('click_here', 'Click here') ?> &lt;&lt;</a>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
            </section>

        </section>
    </div>
</div>
<!-- Start Signup Section-->

<!-- Signup Section End -->
<script>
    $(document).ready(function() {
        // Function to get query parameters from the URL
        function getQueryParam(name) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(name);
        }

        // Get the value of the "partner" query parameter
        const userType = getQueryParam("userType");
        if (userType === "partner") {
            $('#userType').val("partner");
        } else {
            $('#userType').val("admin");
        }
    });
</script>