<!-- Start Breadcrumbs -->

<!-- End Breadcrumbs -->

<section class="container" data-aos='fade-up'>
    <div class="row">
        <div id="app">
            <section class="section">
                <div class="container mt-5">
                    <div class="row">
                        <?php
                        if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                        ?>
                            <div class="col-sm-12">
                                <div class="alert alert-warning mb-0">
                                    <b>Note:</b> If you cannot login here, please close the codecanyon frame by clicking on <b>x Remove Frame</b> button from top right corner on the page or <a href="https://espeech.in" target="_blank">&gt;&gt; Click here &lt;&lt;</a>
                                </div>
                            </div>
                        <?php } ?>

                        <div class="col-12 col-sm-8 offset-sm-2 col-md-6 offset-md-3 col-lg-6 offset-lg-3 col-xl-4 offset-xl-4">
                            <?php
                            $data = get_settings('general_settings', true);
                            ?>


                            <?php echo $message; ?>
                            <?php
                            if (isset($_SESSION['logout_msg'])) {
                            ?>
                                <div class="alert alert-primary" id="logout_msg">
                                    <?= $_SESSION['logout_msg'] ?>
                                </div>

                            <?php } ?>

                            <div class="card card-primary shadow-lg bg-white rounded">
                                <img src=" <?= isset($data['partner_logo']) && $data['partner_logo'] != "" ? base_url("public/uploads/site/" . $data['partner_logo']) : base_url('public/backend/assets/img/news/img01.jpg') ?>" class="" alt="">
                                <div class="card-header ">
                                    <h3 class="text-primary">Login</h3>
                                </div>

                                <div class="card-body">
                                    <?= form_open('auth/login', ['method' => "post", "class" => ""]); ?>
                                    <div class="form-group">
                                        <label class="form-label d-none" for="identity"><?= lang('Auth.login_identity_label') ?></label>
                                        <label for="email">Mobile number</label>
                                        <input id="identity" type="number" class="form-control" name="identity" tabindex="1" placeholder="Please enter registered mobile number" required autofocus>
                                        <div class="invalid-feedback">
                                            Please fill in your mobile number
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="d-block">
                                            <label class="form-label  d-none" for="password"><?= lang('Auth.login_password_label') ?></label>
                                            <label for="password" class="control-label">Password</label>
                                            <div class="float-end">
                                                <a href="<?= base_url('auth/forgot-password') ?>" class="text-small text-primary">
                                                    Forgot Password?
                                                </a>
                                            </div>
                                        </div>
                                        <input id="password" type="password" class="form-control" name="password" tabindex="2" required placeholder="Enter your password">

                                        <div class="invalid-feedback">
                                            please fill in your password
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" id="remember" name='remember' value=1 class="form-check-input" />
                                            <label class="form-check-label" for="remember">Remember me</label>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary btn-lg w-100" tabindex="4">
                                            Login
                                        </button>
                                    </div>
                                    <?= form_close() ?>

                                    <div class=" text-muted text-center">
                                        Don't have an account? <a href="<?= base_url('auth/create_user') ?>">Create One</a>
                                    </div>
                                    <div class="simple-footer mb-1">
                                        Copyright &copy; eDemand <?php echo date("Y"); ?>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

</section>