<?php
// $session = \Config\Services::session();
// $is_rtl = isset($_SESSION['is_rtl']) ? $_SESSION['is_rtl'] : null;
// $language = isset($_SESSION['language']) ? $_SESSION['language'] : null;
?>

<!-- Main Content -->
<?php
$session = \Config\Services::session();
$is_rtl = $session->get('is_rtl');
$language = $session->get('language');
$default_language = fetch_details('languages', ['is_default' => '1']);

if (($is_rtl != 0 && (empty($language) || $language == "")) || $default_language[0]['is_rtl'] == "1") {
    $is_rtl = 1;
} else {
    $is_rtl = 0;
}
?>
<?php if ($is_rtl === null) : ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            var isRtl = localStorage.getItem("is_rtl");
            var language = localStorage.getItem("language");
            if (isRtl !== null) {
                isRtl = JSON.parse(isRtl);
                language = JSON.parse(language);
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/lang/updateIsRtl",
                    data: {
                        is_rtl: isRtl,
                        language: language
                    },
                    success: function(response) {
                        location.reload();
                    },
                    error: function() {
                        // console.error("An error occurred while updating the session.");
                    }
                });
            } else {
                // console.log("Session update already processed or values not found in localStorage.");
            }
        });
    </script>
<?php endif; ?>
<?php
$data = get_settings('general_settings', true);
isset($data['primary_color']) && $data['primary_color'] != "" ?  $primary_color = $data['primary_color'] : $primary_color =  '#05a6e8';
isset($data['secondary_color']) && $data['secondary_color'] != "" ?  $secondary_color = $data['secondary_color'] : $secondary_color =  '#003e64';
isset($data['primary_shadow']) && $data['primary_shadow'] != "" ?  $primary_shadow = $data['primary_shadow'] : $primary_shadow =  '#05A6E8';
?>
<style>
    body {
        --primary-color: <?= $primary_color ?>;
        --secondary-color: <?= $secondary_color ?>;
    }

    .bg-primary {
        background-color: <?= $primary_color ?> !important;
    }
</style>
<div class="auth " style="overflow: hidden;">
    <div class="login-wrapper">
        <section class="container-fluid" data-aos='fade-up'>
            <div class="">
                <div id="app">
                    <section class="section">
                        <div class="container-fluid ">
                            <div class=" row d-flex justify-content-<?= $is_rtl == 1 ? 'start' : 'end' ?>">
                                <div class="col-12 col-lg-6 col-md-8 col-sm-8 col-xl-4 <?= $is_rtl == 1 ? 'ms-md-5' : 'me-md-5' ?>   <?= $is_rtl == 1 ? 'mx-lg-3' : 'my-lg-3' ?>  -<?= $is_rtl == 1 ? '' : 'offset-sm-2' ?>">
                                    <div class="card" style="border-radius: 8px;padding:30px">
                                        <div class="row" style="text-align:center;margin-bottom:65px;margin-top:30px;width:100%;padding:0!important;display:block;">
                                            <?php if (current_url() == base_url() . 'admin/login') { ?>
                                                <img style="width: 60%;" src=" <?= isset($data['logo']) && $data['logo'] != "" ? base_url("public/uploads/site/" . $data['logo']) : base_url('public/backend/assets/img/news/img01.jpg') ?>" class="" alt="">
                                            <?php } else { ?>
                                                <img style="width: 60%;" src=" <?= isset($data['partner_logo']) && $data['partner_logo'] != "" ? base_url("public/uploads/site/" . $data['partner_logo']) : base_url('public/backend/assets/img/news/img01.jpg') ?>" class="" alt="">
                                            <?php } ?>
                                        </div>
                                        <div class="row">
                                            <div class="col-md">
                                                <div class=" ">
                                                    <div class="card-body" style="padding: 0!important;">
                                                        <div class="row">
                                                            <div class="col-md">
                                                                <?php
                                                                $country_codes =  fetch_details('country_codes');
                                                                $system_country_code = fetch_details('country_codes', ['is_default' => 1])[0];
                                                                if (!empty($system_country_code['code'])) {
                                                                    $system_country_code = $system_country_code['code'];
                                                                } else {
                                                                    $system_country_code['code'] = +91;
                                                                    $system_country_code['name'] = 'IN';
                                                                }
                                                                // Check if the current URL is for the admin login
                                                                $isAdminLogin = (current_url() == base_url() . 'admin/login');
                                                                $isPartnerLogin = (current_url() == base_url() . 'partner/login');

                                                                ?>
                                                                <?= form_open('auth/login', ['method' => "post", "class" => ""]); ?>
                                                                <div class="form-group">
                                                                    <label class="form-label d-none" for="identity"><?= lang('Auth.login_identity_label') ?></label>
                                                                    <label for="email" class="mb-2"><?= labels('phone_number', 'Phone Number') ?></label>
                                                                    <div class="input-group">
                                                                        <div class="col-md-4 country_code">
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
                                                                        <div id="identityInputDiv" class="<?php echo ($isAdminLogin) ? 'col-12' : 'col-md-8'; ?>">
                                                                            <input id="identity" type="number" class="form-control form-control-new-border" min="0" name="identity" tabindex="1" placeholder="Please enter registered phone number" required autofocus>
                                                                        </div>
                                                                    </div>
                                                                    <div class="invalid-feedback">
                                                                        Please fill in your <?= labels('phone_number', 'Phone Number') ?>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group mb-0">
                                                                    <label class="form-label d-none" for="identity"><?= lang('Auth.login_identity_label') ?></label>
                                                                    <label for="email" class="mb-2"><?= labels('password', 'Password') ?></label>
                                                                    <div class="input-group mb-2">
                                                                        <input id="password" type="password" class="form-control form-control-new-border" name="password" tabindex="2" required placeholder="Enter your password">
                                                                        <div class="input-group-append">
                                                                            <span class="input-group-text form-control-new-border"><i class="fa-sharp fa-solid fa-eye-slash toggle-password "></i></span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="form-group float-start" style="width: 50%;">
                                                                        <div class="custom-control custom-checkbox">
                                                                            <input type="checkbox" id="remember" name='remember' value=1 class="form-check-input" />
                                                                            <label class="form-check-label" for="remember"><?= labels('remember_me', 'Remember me') ?></label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group" style="width: 50%;">
                                                                        <div class="float-end">
                                                                            <a href="#" class="text-small text-new-primary" id="forgot-password-link">
                                                                                <?= labels('forgot_password', 'Forgot Password?') ?>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class=" text-muted text-center mb-4">
                                                                    <?= labels('dont_have_account', 'Don\'t have an account ?') ?> <a class="text-new-primary" href="<?= base_url('auth/create_user') ?>"><b>
                                                                            <?= labels('join_us_as_provider', 'Join us as provider') ?></b></a>
                                                                </div>
                                                                <div class="form-group ">
                                                                    <button type="submit" class="btn bg-primary text-white btn-lg w-100" tabindex="4">
                                                                        <?= labels('login', 'Login') ?>
                                                                    </button>
                                                                </div>
                                                                <?php
                                                                if (isset($_SESSION['logout_msg'])) {
                                                                ?>
                                                                    <div class="alert alert-primary" id="logout_msg">
                                                                        <?= $_SESSION['logout_msg'] ?>
                                                                    </div>
                                                                <?php }
                                                                if (isset($message) && !empty($message)) {
                                                                ?>
                                                                    <div class="alert alert-danger" id="logout_msg">
                                                                        <div class="mt-2"> <?= $message ?></div>
                                                                    </div>
                                                                <?php
                                                                }
                                                                ?>
                                                                <?= form_close() ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!--  -->
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                        if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                                        ?>
                                            <div class="row ">
                                                <div class="col-md-6">
                                                    <div class="card bg-new-primary " style="border-radius: 8px; ">
                                                        <div class="flex-container">
                                                            <div class="flex-left">
                                                                <div class="">
                                                                    <h6 style="font-size:14px ;">ADMIN LOGIN</h6>
                                                                    <span style="white-space:nowrap;font-size:12px ;">Mobile : 9876543210</span>
                                                                    <span style="white-space:nowrap;font-size:12px ;">Password : 12345678</span>
                                                                </div>
                                                            </div>
                                                            <div class="flex-right ml-5Note: If you cannot login here, please close the codecanyon frame by clicking on x Remove Frame button from top right corner on the page or >> Click here <<">
                                                                <div class="card-icon bg-white copy_credentials" onclick="copy_admin_cred()">
                                                                    <i class="fa-solid fa-pen-to-square text-new-primary"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="card bg-new-secondary " style="border-radius: 8px; ">
                                                        <div class="flex-container">
                                                            <div class="flex-left">
                                                                <div class="">
                                                                    <h6 style="font-size:14px ;">PROVIDER LOGIN</h6>
                                                                    <span style="white-space:nowrap;font-size:12px ;">Mobile : 1234567890</span>
                                                                    <span style="white-space:nowrap;font-size:12px ;">Password : 12345678</span>
                                                                </div>
                                                            </div>
                                                            <div class="flex-right">
                                                                <div class="card-icon bg-white copy_credentials" onclick="copy_provider_cred()">
                                                                    <i class="fa-solid fa-pen-to-square text-new-secondary"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <div class="simple-footer " style="margin-bottom: 0!important;margin-top: 0!important;">
                                            <?php $data = get_settings('general_settings', true); ?>
                                            <?= (isset($data['copyright_details']) && $data['copyright_details'] != "") ? $data['copyright_details']  : "edemand copyright" ?>
                                        </div>
                                        <?php
                                        if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                                        ?>
                                            <div class="col-md-12">
                                                <div class="alert bg-warning  mb-0" style="font-size:12px ;">
                                                    <b>Note:</b> If you cannot login here, please close the codecanyon frame by clicking on <b>x Remove Frame</b> button from top right corner on the page or <a href="<?= current_url(); ?>" target="_blank">&gt;&gt; Click here &lt;&lt;</a>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </section>
    </div>
    </body>
    <script>
        $(document).ready(function() {
            var currentURL = window.location.href;
            var isAdminLogin = (window.location.href.indexOf('/admin/login') !== -1);
            var isPartnerLogin = (window.location.href.indexOf('/partner/login') !== -1);
            var forgotPasswordLink = $('#forgot-password-link');
            if (isAdminLogin) {
                $('.country_code').hide();
                $('#country_code').val("");
                // Update the href attribute for admin login
                forgotPasswordLink.attr('href', '<?= base_url() ?>/auth/forgot_password?userType=admin');
            } else if (isPartnerLogin) {
                var selectedCountryCode = "<?php echo isset($system_country_code) ? $system_country_code : "+9125   "; ?>";
                var selectedCountryCode1 = "<?php $system_country_code; ?>";
                $('#country_code').val(selectedCountryCode).trigger('change');
                // Update the href attribute for partner login
                forgotPasswordLink.attr('href', '<?= base_url() ?>/auth/forgot_password?userType=partner');
            }
        });
        $(document).on('click', '.toggle-password', function() {
            $(this).toggleClass("fa-eye fa-eye-slash");
            var input = $("#password");
            input.attr('type') === 'password' ? input.attr('type', 'text') : input.attr('type', 'password')
        });

        function copy_admin_cred() {
            $('.country_code').hide();
            $('#country_code').val("");
            $('.country_code').closest('div').removeClass('col-8').addClass('col-md-4');
            // Change the class of the identity input div
            $('#identityInputDiv').removeClass('col-md-8').addClass('col-md-12');
            $('#identity').val('9876543210');
            $('#password').val('12345678');
            iziToast.success({
                title: "Success",
                message: "Admin Credentials Copied successfully!",
                position: "topRight",
            });
        }

        function copy_provider_cred() {
            $('.country_code').show();
            var selectedCountryCode = "<?php echo isset($system_country_code['code']) ? $system_country_code['code'] : "+91"; ?>";
            $('#country_code').val(selectedCountryCode).trigger('change');
            $('#identityInputDiv').removeClass('col-md-12').addClass('col-md-8');
            $('#identity').val('1234567890');
            $('#password').val('12345678');
            iziToast.success({
                title: "Success",
                message: "Provider Credentials Copied successfully!",
                position: "topRight",
            });
        }
    </script>