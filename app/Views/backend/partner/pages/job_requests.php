<?php

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Calculation\Category;

?>
<div class="main-content">
    <section class="section">
        <div class="section-header mt-2 ">
            <h1><?= labels('job_requests', "Job Request's") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="text-center mr-3">
                    <button class="btn job_request_apply_now_btn"
                        data-toggle="modal"
                        data-target="#manage_custom_job_setting">
                        <?php if ($is_accepting_custom_jobs == 1) : ?>
                            <i class="fas fa-times-circle mr-1 mt-2"></i><?= labels('disable_custom_job_request', 'Disable Custom Job Request') ?>
                        <?php else : ?>
                            <i class="fas fa-check-circle mr-1 mt-2"></i><?= labels('enable_custom_job_request', 'Enable Custom Job Request') ?>
                        <?php endif; ?>
                    </button>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-between">
            <div>
                <!-- Tabs -->
                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="pills-open-jobs-tab" data-toggle="pill" href="#pills-open-jobs" role="tab" aria-controls="pills-open-jobs" aria-selected="true">
                            <?= labels('open_jobs', "Open Jobs") ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="pills-applied-jobs-tab" data-toggle="pill" href="#pills-applied-jobs" role="tab" aria-controls="pills-applied-jobs" aria-selected="false">
                            <?= labels('applied_jobs', "Applied Jobs") ?>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="d-flex">
                <div class="text-center">
                    <button class="btn job_request_apply_now_btn"
                        data-toggle="modal"
                        data-target="#manage_categories">
                        <i class="fas fa-tasks mr-1 mt-2"></i><?= labels('manage_category_preference', 'Manage Category Preference') ?>
                    </button>
                </div>
            </div>
        </div>
        <!-- Tab Content -->
        <div class="tab-content" id="pills-tabContent">
            <!-- Open Jobs Content -->
            <div class="tab-pane fade show active" id="pills-open-jobs" role="tabpanel" aria-labelledby="pills-open-jobs-tab">
                <div class="row">
                    <?php if (empty($custom_job_requests)) : ?>
                        <div class="row w-100">
                            <div class="col-md-12 d-flex justify-content-center">
                                <div class="empty-state" data-height="400" style="height: 400px;">
                                    <img src="<?= base_url('public/uploads/site/design.png'); ?>" alt="" srcset="">
                                    <h2><?= labels('no_jobs_available', "Sahh...! No Job’s Available") ?></h2>
                                    <p class="lead">
                                    <h2><?= labels('no_jobs_message', 'There is no jobs available at the moment please visit us again after some time') ?></h2>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php else : ?>
                        <?php foreach ($custom_job_requests as $key => $request) { ?>
                            <div class="col-md-4">
                                <div class="card mt-2">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="card-body">
                                                <div class="job_request_title">
                                                    <?= $request['service_title']; ?>
                                                </div>

                                                <div class="job_request_desc">
                                                    <?php
                                                    $content = $request['service_short_description'];
                                                    $unique_id = 'read-more-' . uniqid();
                                                    $char_limit = 100; // Character limit
                                                    $needs_read_more = strlen(strip_tags($content)) > $char_limit;
                                                    ?>
                                                    <div class="read-more-container" id="<?= $unique_id ?>">
                                                        <div class="content-wrapper">
                                                            <div class="short-text" style="<?= !$needs_read_more ? 'display: none;' : '' ?>">
                                                                <?= htmlspecialchars(mb_substr(strip_tags($content), 0, $char_limit)) ?>
                                                                <?= $needs_read_more ? '...' : '' ?>
                                                            </div>
                                                            <div class="full-text" style="<?= !$needs_read_more ? 'display: block;' : 'display: none;' ?>">
                                                                <?= htmlspecialchars($content) ?>
                                                            </div>
                                                        </div>
                                                        <?php if ($needs_read_more): ?>
                                                            <button class="read-more-btn" onclick="toggleReadMore('<?= $unique_id ?>')"><?= labels('read_more', 'Read more') ?></button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>



                                                <div class="budget mt-2">
                                                    <div><?= labels('budget', 'Budget') ?></div>
                                                    <div class="align-items-center d-flex">
                                                        <div class="mr-2 text-dark"><?= $currency . $request['min_price']; ?></div>
                                                        <div class="mr-2">To</div>
                                                        <div class="mr-2 text-dark"><?= $currency . $request['max_price']; ?></div>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="align-items-center d-flex justify-content-between">
                                                    <div class="o-media o-media--middle">
                                                        <a href="<?= base_url('public/backend/assets/profiles/' . $request['image']); ?>" data-lightbox="image-1">
                                                            <img class="o-media__img job_request_image" src="<?= base_url('public/backend/assets/profiles/' . $request['image']); ?>" alt="">
                                                        </a>
                                                        <div class="o-media__body">
                                                            <div class="provider_name_table"><?= $request['username'] ?></div>
                                                            <div class="provider_email_table"><?= diffForHumans($request['created_at']); ?></div>
                                                        </div>
                                                    </div>
                                                    <div>

                                                        <?php
                                                        $disk = fetch_current_file_manager();

                                                        if ($disk == 'local_server') {

                                                            $localPath = base_url('/public/uploads/categories/' . $request['category_image']);

                                                            if (check_exists($localPath)) {
                                                                $category_image = $localPath; // Use the local server image URL
                                                            } else {
                                                                $category_image = ''; // File not found, return an empty string
                                                            }
                                                        } else if ($disk == "aws_s3") {
                                                            $category_image = fetch_cloud_front_url('categories', $request['category_image']); // Construct the CloudFront URL
                                                        } else {
                                                            $category_image = $request['category_image'];
                                                        }

                                                        ?>
                                                        <button class="btn job_request_apply_now_btn"
                                                            data-toggle="modal"
                                                            data-target="#applyNowModal"
                                                            data-id="<?= $request['id']; ?>"
                                                            data-title="<?= $request['service_title']; ?>"
                                                            data-min-price="<?= $request['min_price']; ?>"
                                                            data-max-price="<?= $request['max_price']; ?>"
                                                            data-username="<?= $request['username']; ?>"
                                                            data-desc="<?= $request['service_short_description']; ?>"
                                                            data-category_name="<?= $request['category_name']; ?>"
                                                            data-category_image="<?= $category_image; ?>"
                                                            data-user_image="<?= base_url('public/backend/assets/profiles/' . $request['image']); ?>"
                                                            data-expiresat="<?= $request['requested_end_date'] . ' ' . $request['requested_end_time']; ?>"

                                                            data-created-at="<?= $request['created_at'] ?>">
                                                            <?= labels('apply_now', 'Apply Now') ?>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Applied Jobs Content -->
            <div class="tab-pane fade" id="pills-applied-jobs" role="tabpanel" aria-labelledby="pills-applied-jobs-tab">
                <div class="">
                    <?php if (empty($applied_jobs)): ?>
                        <div class="row w-100">
                            <div class="col-md-12 d-flex justify-content-center">
                                <div class="empty-state" data-height="400" style="height: 400px;">
                                    <img src="<?= base_url('public/uploads/site/design.png'); ?>" alt="" srcset="">
                                    <h2><?= labels('no_jobs_available', 'Sahh...! No Job’s Available') ?></h2>
                                    <p class="lead">
                                        <?= labels('no_custom_jobs_applied', 'You have not applied for any custom jobs yet. Please explore available jobs and apply to get started.') ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php else : ?>
                        <div class="row">
                            <?php foreach ($applied_jobs as $key => $request) { ?>
                                <div class="col-md-4">
                                    <div class="card mt-2">
                                        <div class="">
                                            <div class="col-md-12">
                                                <div class="card-body">
                                                    <div class="job_request_title">
                                                        <?= $request['service_title']; ?>
                                                    </div>

                                                    <div class="job_request_desc">
                                                        <?php
                                                        $content = $request['service_short_description'];
                                                        $unique_id = 'read-more-' . uniqid();
                                                        $char_limit = 100; // Character limit
                                                        $needs_read_more = strlen(strip_tags($content)) > $char_limit;
                                                        ?>
                                                        <div class="read-more-container" id="<?= $unique_id ?>">
                                                            <div class="content-wrapper">
                                                                <div class="short-text" style="<?= !$needs_read_more ? 'display: none;' : '' ?>">
                                                                    <?= htmlspecialchars(mb_substr(strip_tags($content), 0, $char_limit)) ?>
                                                                    <?= $needs_read_more ? '...' : '' ?>
                                                                </div>
                                                                <div class="full-text" style="<?= !$needs_read_more ? 'display: block;' : 'display: none;' ?>">
                                                                    <?= htmlspecialchars($content) ?>
                                                                </div>
                                                            </div>
                                                            <?php if ($needs_read_more): ?>
                                                                <button class="read-more-btn" onclick="toggleReadMore('<?= $unique_id ?>')"><?= labels('read_more', 'Read more') ?></button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>


                                                    <hr>

                                                    <?php
                                                    $disk = fetch_current_file_manager();

                                                    if ($disk == 'local_server') {

                                                        $localPath = base_url('/public/uploads/categories/' . $request['category_image']);

                                                        if (check_exists($localPath)) {
                                                            $category_image = $localPath; // Use the local server image URL
                                                        } else {
                                                            $category_image = ''; // File not found, return an empty string
                                                        }
                                                    } else if ($disk == "aws_s3") {
                                                        $category_image = fetch_cloud_front_url('categories', $request['category_image']); // Construct the CloudFront URL
                                                    } else {
                                                        $category_image = $request['category_image'];
                                                    }
                                                    ?>

                                                    <div class="clickable-section align-items-center d-flex justify-content-between clickableSection" id="clickableSection" data-id="<?= $request['id']; ?>"
                                                        data-title="<?= $request['service_title']; ?>"
                                                        data-min-price="<?= $request['min_price']; ?>"
                                                        data-max-price="<?= $request['max_price']; ?>"
                                                        data-username="<?= $request['username']; ?>"
                                                        data-desc="<?= $request['service_short_description']; ?>"
                                                        data-category_name="<?= $request['category_name']; ?>"
                                                        data-counter_price="<?= $request['counter_price']; ?>"
                                                        data-cover_note="<?= $request['note']; ?>"
                                                        data-duration="<?= $request['duration']; ?>"
                                                        data-tax_id="<?= $request['tax_id']; ?>"
                                                        data-tax_amount="<?= $request['tax_amount']; ?>"
                                                        data-tax_percentage="<?= $request['tax_percentage']; ?>"
                                                        data-category_image="<?= $category_image; ?>"
                                                        data-user_image="<?= base_url('public/backend/assets/profiles/' . $request['image']); ?>"
                                                        data-created-at="<?= $request['created_at']; ?>"
                                                        data-expiresat="<?= $request['requested_end_date']; ?>">

                                                        <div class="o-media o-media--middle">
                                                            <img class="o-media__img job_request_image" src="<?= base_url('public/backend/assets/profiles/' . $request['image']); ?>" alt="">
                                                            <div class="o-media__body">
                                                                <div class="provider_name_table"><?= $request['username'] ?></div>
                                                                <div class="provider_email_table"><?= diffForHumans($request['created_at']); ?></div>
                                                            </div>
                                                        </div>
                                                        <div class="budget mt-2">
                                                            <div><?= labels('budget', 'Budget') ?></div>
                                                            <div class="align-items-center d-flex">
                                                                <div class="mr-2 text-dark"><?= $currency . $request['min_price']; ?></div>
                                                                <div class="mr-2">-</div>
                                                                <div class="mr-2 text-dark"><?= $currency . $request['max_price']; ?></div>
                                                                <div class="mr-2 text-dark"> <i class="fas fa-angle-right"></i> </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                </div>
            <?php endif; ?>
            </div>
        </div>
</div>
</section>
</div>
<!-- Applied Job Modal -->
<div class="modal fade" id="appliedJobsModal" tabindex="-1" aria-labelledby="appliedJobsModalLable" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark"><?= labels('your_bid', 'Your Bid') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <hr>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="job-title-view" class="font-weight-bold text-dark"></div>
                    </div>
                    <div class="col-md-12">
                        <div id="job-desc-view">
                            <span class="job-desc-short-view"></span>
                            <span class="job-desc-full-view d-none"></span>
                            <a href="#" class="read-more-less-view text-primary"></a>
                        </div>
                    </div>
                </div>
                <div class="row d-flex p-3 gap-4">
                    <div class="align-content-center">
                        <?= labels('category', 'Category') ?>
                    </div>
                    <div class=" job_request_category_name d-flex">
                        <img class="job_request_category_image" id="job-category-image-view" src="" alt="" srcset="">
                        <div class="category" id="job-category-view"></div>
                    </div>
                </div>
                <hr class="mt-0">
                <div class="row w-100">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <img class="rounded-circle" id="job-user-image-view" src="" alt="" style="width: 50px; height: 50px;">
                            <div class="ml-3">
                                <div id="job-user-profile" class="text-muted"><?= labels('customer', 'Customer') ?></div>
                                <div id="job-username-view" class="font-weight-bold"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-left">
                            <div class="text-muted"><?= labels('budget', 'Budget') ?></div>
                            <div id="job-budget-view" class="font-weight-bold"></div>
                        </div>
                    </div>
                </div>
                <div class="row w-100 mt-3">
                    <div class="col-md-6 ">
                        <div class="">
                            <div class="text-muted"><?= labels('posted_at', 'Posted At') ?></div>
                            <div id="job-created-at-view" class="font-weight-bold"></div>
                        </div>
                    </div>
                    <div class="col-md-6 ">
                        <div>
                            <div class="text-muted"><?= labels('expires_on', 'Expires On') ?></div>
                            <div id="job-expires-on-view" class="font-weight-bold"></div>
                        </div>
                    </div>
                </div>
                <h6 class="mt-4 text-dark"><?= labels('your_bid', 'Your Bid') ?></h6>
                <div class="d-flex justify-content-end">
                    <div class="col-md-6 p-0">
                        <label for="counter_price" class="text-muted"><?= labels('counter_price', 'Counter Price') ?></label>
                    </div>
                    <div class="col-md-6 p-0">
                        <div id="counter_price_view" class="text-dark"></div>
                    </div>
                </div>
                <div class="d-flex justify-content-end tax_info">
                    <div class="col-md-6 p-0">
                        <label for="tax_percentage_view" class="text-muted"><?= labels('tax_percentage', 'Tax Percentage') ?></label>
                    </div>
                    <div class="col-md-6 p-0">
                        <div id="tax_percentage_view" class="text-dark"></div>
                    </div>
                </div>
                <div class="d-flex justify-content-end tax_info">
                    <div class="col-md-6 p-0 ">
                        <label for="tax_amount" class="text-muted"><?= labels('tax_amount', 'Tax Amount') ?></label>
                    </div>
                    <div class="col-md-6 p-0 ">
                        <div id="tax_amount_view" class="text-dark"></div>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <div class="col-md-6 p-0">
                        <label for="duration_view" class="text-muted"><?= labels('duration', 'Duration') ?></label>
                    </div>
                    <div class="col-md-6 p-0">
                        <div id="duration_view" class="text-dark"></div>
                    </div>
                </div>
                <!-- <div class="">
                    <label for="cover_note_view" class="text-dark"><?= labels('cover_note', 'Cover Note') ?></label>
                    <div id="cover_note_view" class="text-muted"></div>
                </div> -->
                <div>
                    <label for="cover_note_view" class="text-dark"><?= labels('cover_note', 'Cover Note') ?></label>
                    <div id="cover_note_view" class="text-muted">
                        <span class="cover-note-short"></span>
                        <span class="cover-note-full d-none"></span>
                        <a href="#" class="cover-read-more-less text-primary"></a>
                    </div>
                </div>
                <div class="modal-footer px-0">
                    <button type="submit" class="btn bg-new-primary submit_btn" disabled><?= labels('submitted', 'Submitted') ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Apply Now Modal -->
<div class="modal fade" id="applyNowModal" tabindex="-1" aria-labelledby="applyNowModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark"><?= labels('submit_your_bid', 'Submit Your Bid') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <hr>
            <div class="modal-body">
                <?= form_open('partner/make_bid', ['method' => "post", 'class' => 'form-submit-event', 'id' => 'add_Category', 'enctype' => "multipart/form-data"]); ?>
                <input type="hidden" name="id" id="job-id">
                <div class="row">
                    <div class="col-md-12">
                        <div id="job-title" class="font-weight-bold text-dark"></div>
                    </div>
                    <div class="col-md-12">
                        <!-- <div id="job-desc"></div>


                        <a href="#" class="read-more-less text-primary"></a> -->

                        <div id="job-desc-view">
                            <span class="job-desc-short"></span>
                            <span class="job-desc-full d-none"></span>
                            <a href="#" class="read-more-less text-primary"></a>
                        </div>
                    </div>
                </div>
                <div class="row d-flex p-3 gap-4">
                    <div class="align-content-center">
                        <?= labels('category', 'Category') ?>
                    </div>
                    <div class=" job_request_category_name d-flex">
                        <img class="job_request_category_image" id="job-category-image" src="" alt="" srcset="">
                        <div class="category" id="job-category"></div>
                    </div>
                </div>
                <hr class="mt-0">
                <div class="row w-100">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <img class="rounded-circle" id="job-user-image" src="" alt="" style="width: 50px; height: 50px;">
                            <div class="ml-3">
                                <div id="job-user-profile" class="text-muted"><?= labels('customer', 'Customer') ?></div>
                                <div id="job-username" class="font-weight-bold"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-left">
                            <div class="text-muted"><?= labels('budget', 'Budget') ?></div>
                            <div id="job-budget" class="font-weight-bold"></div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row w-100 mt-3">
                    <div class="col-md-6">
                        <div class="">
                            <div class="text-muted"><?= labels('posted_at', 'Posted At') ?></div>
                            <div id="job-created-at" class="font-weight-bold"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div>
                            <div class="text-muted"><?= labels('expires_on', 'Expires On') ?></div>
                            <div id="job-expires-on" class="font-weight-bold"></div>
                        </div>
                    </div>
                </div>
                <hr>
                <h6 class="mt-4 text-dark"><?= labels('apply_bid', 'Apply Bid') ?></h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="counter_price"><?= labels('select_tax', 'Select Tax') ?></label>
                            <select id="tax" name="tax_id" class="form-control w-100" name="tax">
                                <option value=""><?= labels('select_tax', 'Select Tax') ?></option>
                                <?php foreach ($tax_data as $pn) : ?>
                                    <option value="<?= $pn['id'] ?>"><?= $pn['title'] ?>(<?= $pn['percentage'] ?>%)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="counter_price" class="required"><?= labels('counter_price', 'Counter Price') ?></label>
                            <input type="number" min=0 class="form-control" id="counter_price" name="counter_price">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="cover_note" class="required"><?= labels('write_cover_note', 'Write Cover note') ?></label>
                            <textarea class="form-control" name="cover_note" id="cover_note" placeholder="Write Cover note"></textarea>
                        </div>
                    </div>
                </div>
                <label for="duration"><?= labels('how_much_time_you_need_to_perform_this_service', 'How Much time you need to perform this service') ?></label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <div class="input-group-text myDivClass" style="height: 42px;">
                            <span class="mySpanClass"><?= labels('minutes', 'Minutes') ?></span>
                        </div>
                    </div>
                    <input type="number" style="height: 42px;" class="form-control" name="duration" id="duration" min="0" placeholder="<?= labels('duration_to_perform_task', 'Duration to Perform service') ?>" value="">
                </div>
                <div class="modal-footer px-0">
                    <button type="submit" class="btn bg-new-primary submit_btn"><?= labels('submit_bid', 'Submit Bid') ?></button>
                    <?= form_close(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- manage categories modal -->
<div class="modal fade" id="manage_categories" tabindex="-1" aria-labelledby="manage_categoriesLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="row pl-3 m-0 border_bottom_for_cards">
                <div class="col-auto">
                    <div class="toggleButttonPostition"><?= labels('manage_category_preference', 'Manage Category Preference') ?></div>
                </div>
            </div>
            <div class="modal-body">
                <?= form_open('partner/manage_category_preference', ['method' => "post", 'class' => 'form-submit-event', 'id' => 'add_Category', 'enctype' => "multipart/form-data"]); ?>
                <div class="d-flex justify-content-between row">
                    <?php foreach ($categories_name as $d): ?>
                        <div class="col-md-3 m-3">
                            <div class="category_preference_card">
                                <input type="checkbox" name="category_id[]" <?= (in_array($d['id'], $custom_job_categories) == true)  ? 'checked' : "" ?> value="<?= $d['id'] ?>">


                                <img class="category_preference_image" src="<?= base_url('public/uploads/categories/' . $d['image']); ?>" alt="">
                                <p class="text-center"><?= $d['name'] ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="modal-footer px-0">
                    <button type="submit" class="btn bg-new-primary submit_btn"><?= labels('submit', 'Submit') ?></button>
                    <?= form_close(); ?> <!-- Move this line inside the modal-body -->
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= labels('close', 'Close') ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Disable Custom Job modal -->
<div class="modal fade" id="manage_custom_job_setting" tabindex="-1" aria-labelledby="manage_custom_job_settingLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <?= form_open('partner/manage_accepting_custom_jobs', ['method' => "post", 'class' => 'form-submit-event', 'id' => 'add_Category', 'enctype' => "multipart/form-data"]); ?>
                <!-- Center the image -->
                <div class="row d-flex justify-content-center">
                    <div class="col-auto">
                        <img src="<?= base_url('public/uploads/site/think.png'); ?>" alt="Think Image" class="img-fluid">
                    </div>
                </div>
                <p class="text-center text-dark font-weight-bold"><?= labels('are_you_sure', 'Are You Sure..?') ?></p>
                <?php if ($is_accepting_custom_jobs == 1) : ?>
                    <p class="text-center text-muted"><?= labels('disable_job_service_warning', 'You are going to disable open job service request’s from customers across the world. that’s means you are not eligible to provide customized service.') ?></p>
                    <input type="hidden" name="custom_job_value" value="0">
                <?php else : ?>
                    <input type="hidden" name="custom_job_value" value="1">
                    <p class="text-center text-muted"><?= labels('custom_service_eligibility', ' You are now eligible to provide customized service. Open job service requests from customers across the world are enabled.') ?>
                    </p>
                <?php endif; ?>
                <div class="row justify-content-center mt-4">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-secondary btn-lg  submit_btn w-100"><?= labels('continue', 'Continue') ?></button>
                    </div>
                    <div class="col-md-6">
                        <button type="button" class="btn bg-new-primary btn-lg  w-100" data-dismiss="modal"><?= labels('not_yet', 'Not Yet..!') ?></button>
                    </div>
                </div>
                <?= form_close(); ?>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#category_status').siblings('.switchery').addClass('active-content').removeClass('deactive-content');
        $('.job_request_apply_now_btn').on('click', function() {


            <?php
            $settings = get_settings('general_settings', true);
            $default_logo = base_url("public/uploads/site/" . $settings['logo']);
            ?>

            var data = $(this).data();


            $('#job-id').val(data.id);
            $('#job-title').text(data.title);
            $('#job-desc').text(data.desc);
            $('#job-budget').text('$' + data.minPrice + ' - $' + data.maxPrice);
            $('#job-category').text(data.category_name);
            // $('#job-category-image').attr('src', data.category_image);


            // User image with error handling
            $('#job-category-image')
                .attr('src', data.category_image)
                .on('error', function() {
                    $(this).attr('src', '<?= $default_logo; ?>')
                        .attr('onerror', null) // Prevent infinite loop
                        .addClass('fallback-image');
                });




            $('#job-username').text(data.username);
            // $('#job-user-image').attr('src', data.user_image);
            // User image with error handling
            $('#job-user-image')
                .attr('src', data.user_image)
                .on('error', function() {
                    $(this).attr('src', '<?= $default_logo; ?>')
                        .attr('onerror', null) // Prevent infinite loop
                        .addClass('fallback-image');
                });



            // Format and display the created date
            var createdDate = new Date(data.createdAt);

            $('#job-created-at').text(formatDate(createdDate));
            var expiresat = new Date(data.expiresat);
            $('#job-expires-on').text(formatDate(expiresat));

            // Handle job description
            var fullDesc = data.desc;
            var shortDesc = fullDesc.length > 20 ? fullDesc.substring(0, 20) + '...' : fullDesc;

            $('.job-desc-short').text(shortDesc);
            $('.job-desc-full').text(fullDesc);
            $('.read-more-less').text(fullDesc.length > 20 ? '<?= labels('read_more', 'Read more') ?>' : '').off('click').on('click', function(e) {
                e.preventDefault();
                var isExpanded = $('.job-desc-full').hasClass('d-none');
                if (isExpanded) {
                    $('.job-desc-short').addClass('d-none');
                    $('.job-desc-full').removeClass('d-none');
                    $(this).text('<?= labels('read_less', 'Read less') ?>');
                } else {
                    $('.job-desc-short').removeClass('d-none');
                    $('.job-desc-full').addClass('d-none');
                    $(this).text('<?= labels('read_more', 'Read more') ?>');
                }
            });
        });
    });
    // Helper function to format date as DD/MM/YYYY HH:MM
    function formatDate(date) {
        if (isNaN(date)) return "Invalid Date"; // Handle invalid dates gracefully

        const day = String(date.getDate()).padStart(2, '0'); // Ensure 2-digit day
        const month = String(date.getMonth() + 1).padStart(2, '0'); // Ensure 2-digit month
        const year = date.getFullYear();
        const time = date.toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit'
        });

        return `${day}/${month}/${year} - ${time}`;
    }


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
    var category_status = document.querySelector('#category_status');
    category_status.addEventListener('change', function() {
        handleSwitchChange(category_status);
    });
</script>
<script>
    document.querySelectorAll('.job_request_categories_container_card').forEach(card => {
        card.addEventListener('click', () => {
            card.classList.toggle('selected');
        });
    });
</script>
<script>
    function toggleReadMore(containerId) {
        const container = document.getElementById(containerId);
        const btn = container.querySelector('.read-more-btn');
        const shortText = container.querySelector('.short-text');
        const fullText = container.querySelector('.full-text');
        if (shortText.style.display === 'none') {
            // Show short text (collapse)
            shortText.style.display = 'block';
            fullText.style.display = 'none';
            btn.textContent = '<?= labels('read_more', 'Read more') ?>';
        } else {
            // Show full text (expand)
            shortText.style.display = 'none';
            fullText.style.display = 'block';
            btn.textContent = '<?= labels('read_less', 'Read less') ?>';
        }
    }
</script>
<script>
    document.addEventListener('click', function(event) {




        if (event.target.closest('.clickableSection')) {



            <?php
            $settings = get_settings('general_settings', true);
            $default_logo = base_url("public/uploads/site/" . $settings['logo']);
            ?>

            const element = event.target.closest('.clickableSection');
            $('#appliedJobsModal').modal('show');
            const data = $(element).data();

            $('#job-title-view').text(data.title);
            $('#job-category-view').text(data.category_name);

            // Category image with error handling
            $('#job-category-image-view')
                .attr('src', data.category_image)
                .on('error', function() {
                    $(this).attr('src', '<?= $default_logo; ?>')
                        .attr('onerror', null) // Prevent infinite loop
                        .addClass('fallback-image');
                });

            $('#job-username-view').text(data.username);

            // User image with error handling
            $('#job-user-image-view')
                .attr('src', data.user_image)
                .on('error', function() {
                    $(this).attr('src', '<?= $default_logo; ?>')
                        .attr('onerror', null) // Prevent infinite loop
                        .addClass('fallback-image');
                });

            $('#job-budget-view').text('$' + data.minPrice + ' - $' + data.maxPrice);

            const createdDate = new Date(data.createdAt);
            $('#job-created-at-view').text(formatDate(createdDate));

            const expireDate = new Date(data.expiresat);
            $('#job-expires-on-view').text(formatDate(expireDate));

            $('#counter_price_view').text(data.counter_price);
            $('#duration_view').text(data.duration);


            if (data.tax_id == "0" || data.tax_id == "") {
                $('#tax_amount_view').text('-');
                $('#tax_percentage_view').text('-');
            } else {
                $('#tax_amount_view').text(data.tax_amount);
                $('#tax_percentage_view').text(data.tax_percentage + '%');
            }

            // Handle job description
            var fullDesc = data.desc;
            var shortDesc = fullDesc.length > 20 ? fullDesc.substring(0, 20) + '...' : fullDesc;

            $('.job-desc-short-view').text(shortDesc);
            $('.job-desc-full-view').text(fullDesc);
            $('.read-more-less-view')
                .text(fullDesc.length > 20 ? '<?= labels('read_more', 'Read more') ?>' : '')
                .off('click')
                .on('click', function(e) {
                    e.preventDefault();
                    var isExpanded = $('.job-desc-full-view').hasClass('d-none');
                    if (isExpanded) {
                        $('.job-desc-short-view').addClass('d-none');
                        $('.job-desc-full-view').removeClass('d-none');
                        $(this).text('<?= labels('read_less', 'Read less') ?>');
                    } else {
                        $('.job-desc-short-view').removeClass('d-none');
                        $('.job-desc-full-view').addClass('d-none');
                        $(this).text('<?= labels('read_more', 'Read more') ?>');
                    }
                });

            // Handle cover note
            var fullCoverNote = data.cover_note || '';
            var shortCoverNote = fullCoverNote.length > 20 ? fullCoverNote.substring(0, 20) + '...' : fullCoverNote;

            $('.cover-note-short').text(shortCoverNote);
            $('.cover-note-full').text(fullCoverNote);
            $('.cover-read-more-less')
                .text(fullCoverNote.length > 20 ? '<?= labels('read_more', 'Read more') ?>' : '')
                .off('click')
                .on('click', function(e) {
                    e.preventDefault();
                    var isExpanded = $('.cover-note-full').hasClass('d-none');
                    if (isExpanded) {
                        $('.cover-note-short').addClass('d-none');
                        $('.cover-note-full').removeClass('d-none');
                        $(this).text('<?= labels('read_less', 'Read less') ?>');
                    } else {
                        $('.cover-note-short').removeClass('d-none');
                        $('.cover-note-full').addClass('d-none');
                        $(this).text('<?= labels('read_more', 'Read more') ?>');
                    }
                });
        }
    });
</script>