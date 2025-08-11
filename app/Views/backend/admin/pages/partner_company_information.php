<!-- Main Content -->
<div class="main-content">
    <section class="section" id="pill-general_settings" role="tabpanel">
        <div class="section-header mt-2">
            <h1><?= labels('partner_details', 'Partner Details') ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item "><?= labels('partner_details', 'Partner Details') ?></div>
                <div class="breadcrumb-item "><?= labels('company_information', 'Company Information') ?></div>
                <div class="breadcrumb-item "><?= $partner['rows'][0]['company_name'] ?></div>
            </div>
        </div>
        <?php include "provider_details.php"; ?>
        <div class="section-body">
            <div id="output-status"></div>
            <div class="row mt-3">
                <!-- Company Details start -->
                <div class="col-md-12 col-sm-12 col-xl-8 mb-3">
                    <div class="card h-100">
                        <div class="row pl-3">
                            <div class="col ">
                                <div class="toggleButttonPostition"><?= labels('company_details', 'Company Details') ?></div>
                            </div>
                            <div class="col d-flex justify-content-end mr-3 mt-4">
                                <?php
                                $label = ($partner['rows'][0]['is_approved_edit'] == 1) ?
                                    "<div class='tag border-0 rounded-md  bg-emerald-success text-emerald-success mx-2'>Approved</div>" :
                                    "<div class='tag border-0 rounded-md  bg-emerald-danger text-emerald-danger mx-2'>Disapproved</div>";
                                echo $label;
                                ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-xl-4 col-md-4 col-sm-6 mb-sm-2 d-flex d-flex">
                                    <div class="icon_box">
                                        <i class="fas fa-building fa-lg text-white"></i>
                                    </div>
                                    <div class="service_info">
                                        <span class="title"><?= labels('company_name', 'Company Name') ?></span>
                                        <p class="m-0"><?= $partner['rows']['0']['company_name'] ?></p>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-md-4 col-sm-6 mb-sm-2 d-flex d-flex">
                                    <div class="icon_box">
                                        <i class="fa-solid fa-t text-white"></i>
                                    </div>
                                    <div class="service_info">
                                        <span class="title"><?= labels('type', 'Type') ?></span>
                                        <p class="m-0"><?= $partner['rows']['0']['type'] ?></p>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-md-4 col-sm-6 mb-sm-2 d-flex d-flex">
                                    <div class="icon_box">
                                        <i class="fa-thin fa-dollar text-white"></i>
                                    </div>
                                    <div class="service_info ">
                                        <span class="title"><?= labels('visiting_charges', 'Visiting Charges') ?></span>
                                        <p class="m-0"><?= $partner['rows']['0']['visiting_charges'] ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-xl-4 col-md-4 col-sm-6 mb-sm-2 d-flex ">
                                    <div class="icon_box">
                                        <i class="fas fa-map-marker-alt text-white"></i>
                                    </div>
                                    <div class="service_info">
                                        <span class="title"><?= labels('company_address', 'Company Address') ?></span>
                                        <p class="m-0"><?= $partner['rows']['0']['address'] ?></p>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-md-4 col-sm-6 mb-sm-2 d-flex">
                                    <div class="icon_box">
                                        <i class="fas fa-users text-white"></i>
                                    </div>
                                    <div class="service_info ">
                                        <span class="title"><?= labels('number_Of_members', 'Number Of Members') ?></span>
                                        <p class="m-0"><?= $partner['rows']['0']['number_of_members'] ?></p>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-md-4 col-sm-6 mb-sm-2 d-flex">
                                    <div class="icon_box">
                                        <i class="far fa-calendar-check text-white"></i>
                                    </div>
                                    <div class="service_info ">
                                        <span class="title"><?= labels('advance_booking_days', 'Advance Booking Days') ?></span>
                                        <p class="m-0"><?= $partner['rows']['0']['advance_booking_days'] ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-xl-4 col-md-4 col-sm-6 mb-sm-2 d-flex">
                                    <div class="icon_box">
                                        <i class="fas fa-city text-white"></i>
                                    </div>
                                    <div class="service_info">
                                        <span class="title"><?= labels('city', 'City') ?></span>
                                        <p class="m-0" style="white-space:nowrap;"><?= $partner['rows']['0']['city'] ?></p>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-md-4 col-sm-6 mb-sm-2 d-flex">
                                    <div class="icon_box">
                                        <i class="fas fa-location text-white"></i>
                                    </div>
                                    <div class="service_info ">
                                        <span class="title"><?= labels('latitude', 'Latitude') ?></span>
                                        <p class="m-0"><?= $partner['rows']['0']['latitude'] ?></p>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-4 col-sm-6 mb-sm-2 d-flex">
                                    <div class="icon_box">
                                        <i class="fas fa-location text-white"></i>
                                    </div>
                                    <div class="service_info ">
                                        <span class="title"><?= labels('longitude', 'Longitude') ?></span>
                                        <p class="m-0"><?= $partner['rows']['0']['longitude'] ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row ">
                                <div class="col-xl-4 col-md-4 col-sm-6 mb-sm-2 d-flex">
                                    <div class="icon_box">
                                        <i class="fas fa-info text-white"></i>
                                    </div>
                                    <div class="service_info ">
                                        <span class="title"><?= labels('at_store_avilable', 'At Store Available') ?></span>
                                        <p class="m-0"><?= ($partner['rows']['0']['at_store'] == "1")  ? "Yes" : "No" ?></p>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-md-4 col-sm-6 mb-sm-2 d-flex">
                                    <div class="icon_box">
                                        <i class="fas fa-info text-white"></i>
                                    </div>
                                    <div class="service_info">
                                        <span class="title"><?= labels('at_doorstep_avilable', 'At Doorstep Available') ?></span>
                                        <p class="m-0"><?= ($partner['rows']['0']['at_doorstep'] == "1")  ? "Yes" : "No" ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-xl-12 col-md-12 col-sm-12 mb-sm-2 d-flex">
                                    <div class="icon_box">
                                        <i class="fas fa-city text-white p-3"></i>
                                    </div>
                                    <div class="service_info">
                                        <span class="title"><?= labels('about_company', 'About Company') ?></span>
                                        <p class="m-0">
                                            <span id="shortDescription1"><?= substr($partner['rows']['0']['about'], 0, 100) ?></span>
                                            <span id="fullDescription1" style="display: none;"><?= substr($partner['rows']['0']['about'], 100) ?></span>
                                            <span id="dots1">...</span>
                                            <a href="javascript:void(0)" id="readMoreLink1" onclick="toggleDescription(1)"><?= labels('read_more', 'Read more') ?></a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-xl-12 col-md-12 col-sm-12 mb-sm-2 d-flex">
                                    <div class="icon_box">
                                        <i class="fas fa-city text-white p-3"></i>
                                    </div>
                                    <div class="service_info">
                                        <span class="title"><?= labels('long_description', 'Long Description') ?></span>
                                        <p class="m-0">
                                            <span id="shortDescription2"><?= substr($partner['rows']['0']['long_description'], 0, 100) ?></span>
                                            <span id="fullDescription2" style="display: none;"><?= substr($partner['rows']['0']['long_description'], 100) ?></span>
                                            <span id="dots2">...</span>
                                            <a href="javascript:void(0)" id="readMoreLink2" onclick="toggleDescription(2)"><?= labels('read_more', 'Read more') ?></a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-xl-6 col-md-6 col-sm-6 mb-sm-2">
                                    <div class="col-xl-12 col-md-12">
                                        <span class="title"><?= labels('logo', 'Logo') ?></span>
                                    </div>
                                    <div class="col-xl-12 col-md-12">
                                        <div class="col-md-6 col-xl-6 p-0 ">
                                            <?php
                                            if (!empty($partner['rows']['0']['image'])) { ?>
                                                <img src="<?= ($partner['rows']['0']['image'])  ?>" style="border-radius:8px;" height="100px" width="100px" alt="">
                                            <?php } else { ?>
                                                <img src="<?= base_url('public/backend/assets/images/no_image_avaialble.jpg') ?>" height="100px" width="100px" alt="">
                                            <?php    }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-md-6 col-sm-6 mb-sm-2">
                                    <div class="col-xl-12 col-md-12">
                                        <span class="title"><?= labels('banner_image', 'Banner Image') ?></span>
                                    </div>
                                    <div class="col-xl-12 col-md-12">
                                        <div class="col-md-6 col-xl-6 p-0">
                                            <?php
                                            if (!empty($partner['rows']['0']['banner_image'])) { ?>
                                                <img src="<?= $partner['rows']['0']['banner_image'] ?>" style="border-radius:8px;" height="100px" width="100px" alt="">
                                            <?php } else { ?>
                                                <img src="<?= base_url('public/backend/assets/images/no_image_avaialble.jpg') ?>" height="100px" width="100px" alt="">
                                            <?php    }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Company Details end -->
                <!-- Personal Information start -->
                <div class="col-md-12 col-sm-12 col-xl-4 mb-3">
                    <div class="card h-100">
                        <div class="row pl-3">
                            <div class="col ">
                                <div class="toggleButttonPostition"><?= labels('personal_information', ' Personal Information') ?></div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-xl-6 col-md-4 col-sm-6 mb-sm-2 d-flex">
                                    <div class="icon_box">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                    <div class="service_info">
                                        <span class="title"><?= labels('name', 'Name') ?></span>
                                        <p class="m-0"><?= $partner['rows']['0']['partner_name'] ?></p>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-md-4 col-sm-6 mb-sm-2 d-flex">
                                    <div class="icon_box">
                                        <i class="fas fa-envelope text-white"></i>
                                    </div>
                                    <div class="service_info">
                                        <span class="title"><?= labels('email', 'Email') ?></span>
                                        <p class="m-0"><?= $partner['rows']['0']['email'] ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-xl-6 col-md-4 col-sm-6 mb-sm-2 d-flex">
                                    <div class="icon_box">
                                        <i class="fas fa-phone-alt text-white"></i>
                                    </div>
                                    <div class="service_info">
                                        <span class="title"><?= labels('phone', 'Phone') ?></span>
                                        <p class="m-0"><?= $partner['rows']['0']['mobile'] ?></p>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-md-4 col-sm-6 mb-sm-2 d-flex">
                                    <div class="icon_box">
                                        <i class="fas fa-percent text-white"></i>
                                    </div>
                                    <div class="service_info">
                                        <span class="title"><?= labels('commision', 'Commision') ?></span>
                                        <p class="m-0"><?= $partner['rows']['0']['admin_commission'] ?></p>
                                    </div>
                                </div>
                            </div>


                            <?php $disk = fetch_current_file_manager(); ?>
                            <div class="row mb-3">
                                <div class="col-xl-6 col-md-6 col-sm-6 mb-sm-2">
                                    <div class="col-xl-12 col-md-12">
                                        <span class="title"><b class="text-dark"><?= labels('passport', 'Passport') ?></b></span>
                                    </div>
                                    <div class="col-xl-12 col-md-12">
                                        <div class="col-md-2 p-0">
                                            <?php

                                            if (!empty($partner['rows']['0']['passport'])) { ?>
                                                <img src="<?= $partner['rows']['0']['passport'] ?>" style="border-radius:8px;" height="100px" width="100px" alt="">
                                            <?php } else { ?>
                                                <img src="<?= base_url('public/backend/assets/images/no_image_avaialble.jpg') ?>" height="100px" width="100px" alt="">
                                            <?php    }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-md-6 col-sm-6 mb-sm-2">
                                    <div class="col-xl-12 col-md-12">
                                        <span class="title"><b class="text-dark"><?= labels('national_id', 'National ID') ?></b></span>
                                    </div>
                                    <div class="col-xl-12 col-md-12">
                                        <div class="col-md-2 p-0">
                                            <?php



                                            if (!empty($partner['rows']['0']['national_id'])) { ?>
                                                <img src="<?= $partner['rows']['0']['national_id'] ?>" style="border-radius:8px;" height="100px" width="100px" alt="">
                                            <?php } else { ?>
                                                <img src="<?= base_url('public/backend/assets/images/no_image_avaialble.jpg')  ?>" height="100px" width="100px" alt="">
                                            <?php    }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-md-6 col-sm-6 mb-sm-2">
                                    <div class="col-xl-12 col-md-12">
                                        <span class="title"><b class="text-dark"><?= labels('address_id', 'Address ID') ?></b></span>
                                    </div>
                                    <div class="col-xl-12 col-md-12">
                                        <div class="col-md-2 p-0">
                                            <?php


                                            if (!empty($partner['rows']['0']['address_id'])) { ?>
                                                <img src="<?= $partner['rows']['0']['address_id'] ?>" style="border-radius:8px;" height="100px" width="100px" alt="">
                                            <?php } else { ?>
                                                <img src="<?= base_url('public/backend/assets/images/no_image_avaialble.jpg')  ?>" height="100px" width="100px" alt="">
                                            <?php    }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12 ">
                                    <div class=" ">
                                        <div class="col-xl-12 col-md-12">
                                            <span class="title"><b class="text-dark"><?= labels('other_images', 'other Images') ?></b></span>
                                        </div>
                                        <div class=" m-3 row ">
                                            <?php
                                            if (!empty($partner['rows']['0']['other_images'])) {
                                                $partner_details['other_images'] = array_map(function ($data) {
                                                    return ($data);
                                                }, json_decode(json_encode($partner['rows']['0']['other_images']), true));
                                            } else {
                                                $partner_details['other_images'] = [];
                                            }
                                            foreach ($partner_details['other_images'] as $image) { ?>
                                                <div class="col-md-6">
                                                    <img alt="" width="170px" style="border: solid  #d6d6dd 1px;background-color:#f4f6f9; border-radius: 4px;margin-bottom:8px;padding:5px;" height="100px" class="mt-2" id="image_preview" src="<?= isset($image) ? ($image) : "" ?>">
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Personal Information end -->
            </div>
            <div class="row mt-3 ">
                <!-- Bank Details start -->
                <div class="col-md-12 col-sm-12 col-xl-4 mb-3">
                    <div class="card h-100">
                        <div class="row pl-3">
                            <div class="col ">
                                <div class="toggleButttonPostition"><?= labels('bank_details', 'Bank Details') ?></div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-xl-6 col-md-4 col-sm-6 mb-sm-2 d-flex">
                                    <div class="icon_box">
                                        <i class="fas fa-hashtag text-white"></i>
                                    </div>
                                    <div class="service_info">
                                        <span class="title"><?= labels('provider_id', 'Provider ID ') ?></span>
                                        <p class="m-0"><?= $partner['rows']['0']['partner_id'] ?></p>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-md-4 col-sm-6 mb-sm-2 d-flex">
                                    <div class="icon_box">
                                        <i class="fas fa-city text-white"></i>
                                    </div>
                                    <div class="service_info">
                                        <span class="title"><?= labels('city', 'City Name ') ?></span>
                                        <p class="m-0"><?= $partner['rows']['0']['city'] ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-xl-6 col-md-4 col-sm-6 mb-sm-2 d-flex">
                                    <div class="icon_box">
                                        <i class="fas fa-scroll text-white"></i>
                                    </div>
                                    <div class="service_info">
                                        <span class="title"><?= labels('tax_name', 'Tax Name ') ?></span>
                                        <p class="m-0"><?= $partner['rows']['0']['tax_name'] ?></p>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-md-4 col-sm-6 mb-sm-2 d-flex">
                                    <div class="icon_box">
                                        <i class="fas fa-scroll text-white"></i>
                                    </div>
                                    <div class="service_info">
                                        <span class="title"><?= labels('tax_number', 'Tax Number') ?></span>
                                        <p class="m-0"><?= $partner['rows']['0']['tax_number'] ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-xl-6 col-md-4 col-sm-6 mb-sm-2 d-flex">
                                    <div class="icon_box">
                                        <i class="fas fa-university text-white"></i>
                                    </div>
                                    <div class="service_info">
                                        <span class="title"><?= labels('bank_name', 'Bank Name') ?></span>
                                        <p class="m-0"><?= $partner['rows']['0']['bank_name'] ?></p>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-md-4 col-sm-6 mb-sm-2 d-flex">
                                    <div class="icon_box">
                                        <i class="fas fa-list-ol text-white"></i>
                                    </div>
                                    <div class="service_info">
                                        <span class="title"><?= labels('account_number', 'Account Number') ?></span>
                                        <p class="m-0"><?= $partner['rows']['0']['account_number'] ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-xl-6 col-md-4 col-sm-6 mb-sm-2 d-flex">
                                    <div class="icon_box">
                                        <i class="fas fa-university text-white"></i>
                                    </div>
                                    <div class="service_info">
                                        <span class="title"><?= labels('account_name', 'Account Name') ?></span>
                                        <p class="m-0"><?= $partner['rows']['0']['account_name'] ?></p>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-md-4 col-sm-6 mb-sm-2 d-flex">
                                    <div class="icon_box">
                                        <i class="fas fa-university text-white"></i>
                                    </div>
                                    <div class="service_info">
                                        <span class="title"><?= labels('bank_code', 'Bank Code') ?></span>
                                        <p class="m-0"><?= $partner['rows']['0']['bank_code'] ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-xl-6 col-md-4 col-sm-6 mb-sm-2 d-flex">
                                    <div class="icon_box">
                                        <i class="fas fa-university text-white"></i>
                                    </div>
                                    <div class="service_info">
                                        <span class="title"><?= labels('swift_code', 'Swift Code') ?></span>
                                        <p class="m-0"><?= $partner['rows']['0']['swift_code'] ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Bank Details end -->
                <!-- Timing Details start -->
                <div class="col-md-12 col-sm-12 col-xl-8 mb-3">
                    <div class="card h-100">
                        <div class="row pl-3">
                            <div class="col ">
                                <div class="toggleButttonPostition"><?= labels('timing_details', 'Timing Details') ?></div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-xl-12 col-md-12 col-sm-6 mb-sm-2">
                                    <table class="table table-hover table-bordered " id="payment_table" data-sort-name="day" data-sort-order="desc" data-toggle="table" data-url="<?= base_url('admin/partners/timing_details/' . $partner['rows'][0]['partner_id']); ?>">
                                        <thead>
                                            <tr>
                                                <th data-field="day" data-visible="true" data-sortable="true">Day</th>
                                                <th data-field="opening_time" data-visible="true">Opening Time</th>
                                                <th data-field="closing_time" data-visible="true">Closing Time</th>
                                                <th data-field="is_open_new" data-visible="true">Open / Close</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Timing Details end -->
            </div>
        </div>
</div>
</section>
</div>
<script>
    function toggleDescription(section) {
        var shortDescription = $("#shortDescription" + section);
        var fullDescription = $("#fullDescription" + section);
        var dots = $("#dots" + section);
        var readMoreLink = $("#readMoreLink" + section);
        if (fullDescription.is(":visible")) {
            fullDescription.hide();
            dots.show();
            readMoreLink.text("<?= labels('read_more', 'Read more') ?>");
        } else {
            fullDescription.show();
            dots.hide();
            readMoreLink.text("<?= labels('read_less', 'Read less') ?>");
        }
    }
</script>