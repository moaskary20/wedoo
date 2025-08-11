<!-- Start Signup Section-->
<section class="section">
    <section class="container mt-1" data-aos='fade-up'>

        <div class="row card mt-5">
            <!-- <div class="col">
                <div class="card-body">
                    <div class="container">
                        <div class="error-box">
                            <p class="error danger-text center-align">

                            </p>
                        </div>
                        <div class="form-group">
                            <label for="number">number</label>
                            <input type="number" name="number" id="number" class="form-group">
                        </div>
                    </div>
                    <div id="rec"></div>
                    <div class="container">
                        <input type="submit" value="try" onclick="phoneauth()">
                    </div>
                </div>
            </div> -->

        </div>

        <div class="row">
            <div class="col-12 col-sm-10 offset-sm-1 col-md-8 offset-md-2 col-lg-8 offset-lg-2 col-xl-8 offset-xl-2">
                <?php
                $data = get_settings('general_settings', true);
                ?>
                <div class="login-brand ">

                    <!-- TODO  fireBase to complete with otp send functions-->

                    <!-- TODO add new column for firebase as well -->

                    <img src=" <?= isset($data['logo']) && $data['logo'] != "" ? base_url("public/uploads/site/" . $data['logo']) : base_url('public/backend/assets/img/news/img01.jpg') ?>" class="shadow-light rounded-circle w-25 shadow-lg" alt="">
                </div>
                <div class="card card-primary shadow-lg p-3 mb-5 bg-white rounded">
                    <div class="row">
                        <div class="card-body">
                            <div class="card-header">
                                <h4>Number Verification</h4>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="number">Phone Number</label>
                                    <input id="number" class="form-control" type="text" name="number"  placeholder="+91 ***********">
                                </div>
                                <div class="form-group">
                                    <div id="rec"></div>
                                </div>
                                <div class="form-group">
                                    <button type="button" class="btn btn-primary" id="sender" onclick="phoneAuth()">Submit Number</button>
                                </div>

                                <div class="form-group">
                                    <label for="otp">Phone Number</label>
                                    <input id="otp" class="form-control" type="text" name="number"  placeholder="+91 ***********">
                                </div>

                                <div class="form-group">
                                    <button type="button" class="btn btn-primary" id="check" onclick="codeverify()">check</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
    </section>
</section>


<!-- Signup Section End -->