<div class="main-content profile-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('my_profile', 'My Profile') ?></h1>
        </div>
        <div class="section-body">

            <div class="row">
                <div class="col-lg-6 col-md-12  col-sm-12 col-xxs-6 mb-sm-3 mb-lg-0 mb-xs-3 mb-xs-6  ">
                    <div class="card h-100">
                        <form action="<?= base_url('admin/profile/update') ?>" method="post" accept-charset="utf-8" class="form-submit-event">
                            <div class="col mb-3" style="border-bottom: solid 1px #e5e6e9;">
                                <div class="toggleButttonPostition"><?= labels('edit_profile', 'Edit Profile') ?></div>

                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">

                                        <div class="form-group">
                                            <label><?= labels('username', "User Name") ?></label>
                                            <input type="text" class="form-control" name="username" id="username" value="<?= $data['username']  ?>" required="">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group ">
                                            <label><?= labels('phone_number', 'Phone Number') ?></label>
                                            <input type="tel" name='phone' id="phone" name="phone" class="form-control" value="<?= $data['phone']  ?>">

                                        </div>
                                    </div>

                                </div>
                                <div class="row">



                                    <div class="col-md-8    ">
                                        <div class="form-group mt-4">
                                            <label for=""><?= labels('change_profile_picture', "Change Profile Picture") ?></label>
                                            <input type="file" name="profile" class="filepond" id="file" accept="image/*">
                                        </div>
                                    </div>

                                    <div class=" col-md-4 mt-5">
                                        <?php
                                        if ($data['image'] != '') {
                                        ?>
                                            <a href="<?= base_url('public/backend/assets/profiles/' . $data['image'])  ?>" data-lightbox="image-1">
                                                <img class="" height="80px" src="<?= base_url('public/backend/assets/profiles/' . $data['image'])  ?>" alt="" style="border-radius: 8px;">
                                            </a>
                                        <?php
                                        } else {
                                        ?>
                                            <figure class="avatar mb-2 avatar-xl" data-initial="<?= strtoupper($data['username'][0])  ?>"></figure>
                                        <?php }
                                        ?>
                                    </div>

                                </div>
                            </div>

                    </div>
                </div>


                <div class="col-lg-6 col-md-12 col-sm-12  mt-sm-3 mt-lg-0 mt-xs-3 mt-3">
                    <div class="card h-100">

                        <div class="row pl-3">
                            <div class="col mb-3" style="border-bottom: solid 1px #e5e6e9;">
                                <div class="toggleButttonPostition"><?= labels('change_password', 'Change Password') ?></div>

                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">

                                    <div class="form-group ">
                                        <label><?= labels('old_password', "Old Password") ?> ( <?= labels('leave_blank', "Leave it blank to disable it") ?> )</label>
                                        <input type="text" class="form-control" name="old">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group ">
                                        <label><?= labels('new_password', "New Password") ?> ( <?= labels('leave_blank', "Leave it blank to disable it") ?> )</label>
                                        <input type="password" class="form-control" name="new">
                                    </div>
                                </div>

                            </div>


                        </div>
                    </div>
                </div>
            </div>



        </div>
        <div class="row mt-4">
            <div class="col-md d-flex justify-content-end">

                <button type="submit" class="btn bg-new-primary"><?= labels('save_changes', 'Save') ?></button>
                <?= form_close() ?>

            </div>
        </div>

</div>
</div>
</section>

</div>