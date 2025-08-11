<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1><?= labels('provider', 'Provider') ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item active"><a href="<?= base_url('admin/partners') ?>"><i class="fas fa-handshake text-warning"></i> Provider</a></div>
                <div class="breadcrumb-item">Provider details</div>
            </div>
        </div>
        <section>
            <?= helper('form'); ?>
            <div class="container-fluid card">
                <div class="row">
                    <div class="col-md-12 mb-4">
                        <div class="section-title">Provider Details</div>
                        <!-- <div class="row">
                            <div class="col-md-10">
                                <span class="h5 font-weight-bolder">
                                    <?= $personal_details['username'] ?>
                                </span>
                            </div>
                        </div> -->

                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-2">
                                        <?php if ($personal_details['image'] != "") : ?>
                                            <?php if (check_exists(base_url('public/backend/assets/profiles/' . $personal_details['image'])) || check_exists($personal_details['image'])) : ?>
                                                <?php if (filter_var($personal_details['image'], FILTER_VALIDATE_URL) === true) : ?>
                                                    <a href="<?= $personal_details['image'] ?>" data-lightbox="image-1">
                                                        <img height="80px" class="rounded" src="<?= $personal_details['image'] ?>" alt="">
                                                    </a>
                                                <?php else :
                                                    $image = (file_exists(FCPATH . 'public/backend/assets/profiles/' . $personal_details['image'])) ? base_url('public/backend/assets/profiles/' . $personal_details['image']) : ((file_exists(FCPATH . $personal_details['image'])) ? base_url($personal_details['image']) : ((!file_exists(FCPATH . "public/uploads/users/partners/" . $personal_details['image'])) ? base_url("public/backend/assets/profiles/default.png") : base_url("public/uploads/users/partners/" . $personal_details['image'])));
                                                ?>
                                                    <a href="<?= $image ?>" data-lightbox="image-1">
                                                        <img height="80px" class="rounded" src="<?= $image ?>" alt="">
                                                    </a>
                                                <?php endif; ?>
                                            <?php else : ?>
                                                <a href="#" id="pop">
                                                    <img id="profile_picture" src="<?= base_url('public/backend/assets/profiles/default.png') ?>" height="100px" class="rounded">
                                                </a>
                                            <?php endif; ?>
                                        <?php else : ?>
                                            <a href="#" id="pop">
                                                <img id="profile_picture" src="<?= base_url('public/backend/assets/profiles/default.png') ?>" height="100px" class="rounded">
                                            </a>
                                        <?php endif; ?>
                                    </div>


                                </div>











                                <div class="row mt-4">
                                    <div class="col-md-12">

                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>Company Name : </th>
                                                    <td class="col-10"><?= $partner_details['company_name'] ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Address : </th>
                                                    <td class="col-10"><?= $partner_details['address'] ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Company Type : </th>
                                                    <?php $partner_details['type'] =  $partner_details['type'] == 0 ? "Individual" : "Organization"; ?>
                                                    <td class="col-10"><?= $partner_details['type'] ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Visiting Charges : </th>
                                                    <td class="col-10"><?= $partner_details['visiting_charges'] ?></td>
                                                </tr>
                                                <tr>
                                                    <th>About :</th>
                                                    <td class="col-10"><?= $partner_details['about'] ?></td>
                                                </tr>
                                            </thead>
                                        </table>

                                    </div>

                                </div>
                            </div>
                            <div class="col-md-6">
                                <div id="map_wrapper_div">
                                    <div id="map_tuts" class="h-100"></div>
                                    <input type="hidden" name="latitude" id="lat" value="<?= ($personal_details['latitude'] != '') ? $personal_details['latitude'] : ''  ?> " readonly>
                                    <input type="hidden" name="longitude" id="lon" value="<?= ($personal_details['longitude'] != '') ? $personal_details['longitude'] : '' ?> " readonly>
                                    <input type="hidden" name="city_name" id="city_name" value="<?= (isset($city_data) && $city_data['name'] != '') ? $city_data['name'] : '' ?> " readonly>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 d-none">
                            <table class="table table-hover table-bordered" id="payment_table" data-show-export="true" data-export-types="[' txt','excel','csv']" data-export-options='{"fileName": "invoice-order-list","ignoreColumn": ["action"]}' data-auto-refresh="true" data-show-columns="true" data-show-toggle="true" data-show-refresh="true" data-toggle="table" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-url="<?= base_url('admin/partners/partner_details/' . $personal_details['id']); ?>">
                                <thead>
                                    <tr>
                                        <th data-field="partner_profile" data-visible="true">Provider Image</th>
                                        <th data-field="company_name" data-visible="true">Company Name</th>
                                        <th data-field="about" data-visible="true">About</th>
                                        <th data-field="contact_detail" data-visible="true">Contact Detail</th>
                                        <th data-field="type" class="text-center" data-sortable="true"><?= labels('type', 'Type') ?></th>
                                        <th data-field="number_of_members" class="text-center" data-sortable="false"><?= labels('number_of_members', 'Number Of Members') ?></th>
                                        <th data-field="status" class="text-center" data-sortable="false"><?= labels('status', 'Status') ?></th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- banking details and residential details -->

            <div class="container-fluid card">
                <div class="row ">
                    <div class="col-md-12">
                        <div class="section-title">Banking & Address Details</div>
                        <div class="table-responsive mb-3">
                            <table class="table table-hover table-bordered" id="payment_table" data-show-export="true" data-export-types="['txt','excel','csv']" data-export-options='{"fileName": "invoice-order-list","ignoreColumn": ["action"]}' data-auto-refresh="true" data-show-columns="true" data-show-toggle="true" data-show-refresh="true" data-toggle="table" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-url="<?= base_url('admin/partners/banking_details/' . $personal_details['id']); ?>">
                                <thead>
                                    <tr>
                                        <th data-field="partner_id" data-visible="true">Provider ID</th>
                                        <th data-field="name" data-visible="false">City Name</th>
                                        <th data-field="passport" data-visible="false">Passport</th>
                                        <th data-field="tax_name" data-visible="true">Tax Name</th>
                                        <th data-field="tax_number" data-visible="true">Tax Number</th>
                                        <th data-field="bank_name" data-visible="true">Bank Name</th>
                                        <th data-field="account_number" data-visible="true">Account Number</th>
                                        <th data-field="account_name" data-visible="true">Account Name</th>
                                        <th data-field="bank_code" data-visible="true">Bank Code</th>
                                        <th data-field="swift_code" data-visible="true">Swift Code</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- time related details -->
            <div class="container-fluid card">
                <div class="row pb-3">
                    <div class="col-md-6">
                        <div class="section-title">Timing Details</div>
                        <div class="mb-3">
                            <table class="table table-hover table-bordered " id="payment_table" data-show-export="true" data-export-types="['txt','excel','csv']" data-export-options='{"fileName": "invoice-order-list","ignoreColumn": ["action"]}' data-auto-refresh="true" data-show-columns="true" data-sort-name="day" data-sort-order="desc" data-show-toggle="true" data-show-refresh="true" data-toggle="table" data-pagination="true" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-url="<?= base_url('admin/partners/timing_details/' . $personal_details['id']); ?>">
                                <thead>
                                    <tr>
                                        <th data-field="partner_id" data-visible="false">Provider ID</th>
                                        <th data-field="day" data-visible="true" data-sortable="true">Day</th>
                                        <th data-field="opening_time" data-visible="true">Opening Time</th>
                                        <th data-field="closing_time" data-visible="true">Closing Time</th>
                                        <th data-field="is_open" data-visible="true" data-sortable="true">Open / Close</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <!-- Current Available Services -->
                    <div class="col-md-6">
                        <div class="section-title">Services Details</div>
                        <div class="">
                            <table class="table table-hover table-bordered " id="payment_table" data-show-export="true" data-export-types="['txt','excel','csv']" data-export-options='{"fileName": "invoice-order-list","ignoreColumn": ["action"]}' data-auto-refresh="true" data-show-columns="true" data-sort-name="day" data-sort-order="desc" data-show-toggle="true" data-show-refresh="true" data-toggle="table" data-pagination="true" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-page-size="5" data-url="<?= base_url('admin/partners/service_details/' . $personal_details['id']); ?>">
                                <thead>
                                    <tr>
                                        <th data-field="service_title" data-visible="true" data-sortable="true">Title</th>
                                        <th data-field="category_name" data-visible="true">Category</th>
                                        <th data-field="description" data-visible="false">Description</th>
                                        <th data-field="image" data-visible="true">Image</th>
                                        <th data-field="duration" data-visible="true" data-sortable="true">Duration</th>
                                        <th data-field="on_site_allowed" data-visible="false" data-sortable="true">on_site_allowed</th>
                                        <th data-field="is_cancelable" data-visible="false" data-sortable="true">is_cancelable</th>
                                        <th data-field="cancelable_till" data-visible="false">cancelable_till</th>
                                        <th data-field="max_quantity_allowed" data-visible="false">max_quantity_allowed</th>
                                        <th data-field="is_pay_later_allowed" data-visible="false">is_pay_later_allowed</th>
                                        <th data-field="status" data-visible="true" data-sortable="true">status</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- do not touch partner store address is here -->
            <!-- <div class="row">
                <div class="col-md">
                    <div class="card">
                        <div class="card-header">
                            <div class="section-title">Provider Current Location</div>
                        </div>
                        <div class="card-body">
                            <div id="map_wrapper_div">
                                <div id="map_tuts" class="h-100"></div>
                                <input type="hidden" name="latitude" id="lat" value="<?= ($personal_details['latitude'] != '') ? $personal_details['latitude'] : ''  ?> " readonly>
                                <input type="hidden" name="longitude" id="lon" value="<?= ($personal_details['longitude'] != '') ? $personal_details['longitude'] : '' ?> " readonly>
                                <input type="hidden" name="city_name" id="city_name" value="<?= (isset($city_data) && $city_data['name'] != '') ? $city_data['name'] : '' ?> " readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
        </section>
    </section>
</div>