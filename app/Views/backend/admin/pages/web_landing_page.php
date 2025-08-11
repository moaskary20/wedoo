<!-- Main Content -->
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
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('web_settings', "Web settings") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active">
                    <a href="<?= base_url('/admin/dashboard') ?>">
                        <i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?>
                    </a>
                </div>
                <div class="breadcrumb-item">
                    <a href="<?= base_url('/admin/settings/system-settings') ?>">
                        <?= labels('system_settings', "System Settings") ?>
                    </a>
                </div>
                <div class="breadcrumb-item"><?= labels('web_settings', "Web settings") ?></div>
            </div>
        </div>
        <ul class="justify-content-start nav nav-fill nav-pills pl-3 py-2 setting" id="gen-list">
            <div class="row">
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('admin/settings/web_setting') ?>" id="pills-general_settings-tab">
                        <?= labels('web_settings', "Web Settings") ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="<?= base_url('admin/settings/web-landing-page-settings') ?>" id="pills-about_us">
                        <?= labels('landing_page_settings', "Landing Page Settings") ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('admin/settings/become-provider-setting') ?>" id="pills-about_us">
                        <?= labels('become_provider_page_settings', "Become Provider Page Settings") ?>
                    </a>
                </li>
            </div>
        </ul>
        <?= form_open_multipart(base_url('admin/settings/web-landing-page-settings-update')) ?>
        <div class="row mb-4">
            <!-- Logos Section -->
            <div class="col-md-6 col-sm-12 col-xl-6">
                <div class="card h-100">
                    <div class="row border_bottom_for_cards m-0">
                        <div class="col">
                            <div class="toggleButttonPostition"><?= labels('logos', "Logos") ?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for='landing_page_logo'><?= labels('landing_page_logo', "Landing Page Logo") ?></label>
                                    <input type="file" name="landing_page_logo" class="filepond logo" id="landing_page_logo" accept="image/*">
                                    <?php

                                    $disk = fetch_current_file_manager();

                                    if (isset($disk)) {
                                        if ($disk === "aws_s3") {
                                            $landing_page_logo = fetch_cloud_front_url('web_settings', $landing_page_logo);
                                        } elseif ($disk === "local_server") {
                                            $landing_page_logo = base_url("public/uploads/web_settings/" . $landing_page_logo);
                                        }
                                    } else {
                                        $landing_page_logo = base_url('public/backend/assets/img/news/img01.jpg'); // Default logo
                                    }
                                    if (isset($disk)) {
                                        if ($disk === "aws_s3") {
                                            $landing_page_backgroud_image = fetch_cloud_front_url('web_settings', $landing_page_backgroud_image);
                                        } elseif ($disk === "local_server") {
                                            $landing_page_backgroud_image = base_url("public/uploads/web_settings/" . $landing_page_backgroud_image);
                                        }
                                    } else {
                                        $landing_page_backgroud_image = base_url('public/backend/assets/img/news/img01.jpg'); // Default logo
                                    }
                                    ?>
                                    <img class="settings_logo" src="<?= $landing_page_logo; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for='landing_page_backgroud_image'><?= labels('landing_page_image', "Landing Page Image") ?></label>
                                    <input type="file" name="landing_page_backgroud_image" class="filepond logo" id="landing_page_backgroud_image" accept="image/*">
                                    <img class="settings_logo" src="<?= $landing_page_backgroud_image; ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- FAQ Section -->
            <div class="col-md-6 col-sm-12 col-xl-6">
                <div class="card h-100">
                    <div class="row border_bottom_for_cards m-0">
                        <div class="col-auto">
                            <div class="toggleButttonPostition"><?= labels('faq_section', "FAQ Section") ?></div>
                        </div>
                        <div class="col d-flex justify-content-end mr-3 mt-4">
                            <input type="checkbox" class="status-switch" id="faq_section_status" name="faq_section_status" <?= isset($faq_section_status) && $faq_section_status == "1" ? 'checked' : '' ?>>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for='faq_section_title'><?= labels('faq_section_title', "FAQ Section Title") ?></label>
                            <input type='text' class="form-control custome_reset" name='faq_section_title' id='faq_section_title' value="<?= isset($faq_section_title) ? $faq_section_title : '' ?>">
                        </div>
                        <div class="form-group">
                            <label for='faq_section_description'><?= labels('faq_section_description', "FAQ Section Description") ?></label>
                            <input type='text' class="form-control custome_reset" name='faq_section_description' id='faq_section_description' value="<?= isset($faq_section_description) ? $faq_section_description : '' ?>">
                        </div>
                        <div id="notification_div" class="alert alert-primary alert-has-icon">
                            <div class="alert-icon"><i class="fa-solid fa-circle-exclamation mr-2"></i></div>
                            <div class="alert-body">
                                <div id="status" class="">You can add data from <a href="<?= base_url('admin/faqs') ?>"><?= labels('faqs', "FAQs") ?></a> </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-4">
            <!-- Rating Section -->
            <div class="col-md-6 col-sm-12 col-xl-6">
                <div class="card h-100">
                    <div class="row border_bottom_for_cards m-0">
                        <div class="col-auto">
                            <div class="toggleButttonPostition"><?= labels('rating_section', "Rating Section") ?></div>
                        </div>
                        <div class="col d-flex justify-content-end mr-3 mt-4">
                            <input type="checkbox" class="status-switch" id="rating_section_status" name="rating_section_status" <?= isset($rating_section_status) && $rating_section_status == "1" ? 'checked' : '' ?>>
                        </div>
                    </div>
                    <div class="card-body">
                        <input type="hidden" name="new_rating_ids[]" id="new_rating_ids" value=<?= isset($rating_ids[0]) ? $rating_ids[0] : "" ?>>
                        <div class="form-group">
                            <label for='rating_section_title'><?= labels('rating_section_title', "Rating Section Title") ?></label>
                            <input type='text' class="form-control custome_reset" name='rating_section_title' id='rating_section_title' value="<?= isset($rating_section_title) ? $rating_section_title : '' ?>">
                        </div>
                        <div class="form-group">
                            <label for='rating_section_description'><?= labels('rating_section_description', "Rating Section Description") ?></label>
                            <input type='text' class="form-control custome_reset" name='rating_section_description' id='rating_section_description' value="<?= isset($rating_section_description) ? $rating_section_description : '' ?>">
                        </div>
                        <button id="select-ratings" type="button" class="btn btn-primary"><?= labels('select_ratings', "Select ratings") ?></button>
                        <div class="col-12 mt-2">
                            <div class="form-group mb-0">
                                <label for='rating_section_id'><?= labels('selected_ratings', "Selected Ratings") ?></label>
                            </div>
                            <div id="selected-ratings">
                                <?php
                                if (isset($rating_ids) && is_array($rating_ids) && isset($rating_ids[0])) {
                                    $rating_ids = explode(',', $rating_ids[0]);
                                } else {
                                    $rating_ids = [];
                                }
                                $rating_map = array_column($services_ratings, null, 'id');
                                foreach ($rating_ids as $index => $id) :
                                    if (isset($rating_map[$id])):
                                        $rating = $rating_map[$id];
                                ?>
                                        <div class="card author-box card-primary <?= $index >= 2 ? 'd-none more-ratings' : '' ?>">
                                            <div class="card-body">
                                                <div class="author-box-left">
                                                    <img alt="image" src="<?= $rating['profile_image'] ?>" class="rounded-circle author-box-picture">
                                                </div>
                                                <div class="author-box-details">
                                                    <div class="author-box-name"><?= $rating['username'] ?></div>
                                                    <?php
                                                    $created_at = isset($rating['created_at']) ? $rating['created_at'] : '';
                                                    $formatted_date = $created_at ? (new DateTime($created_at))->format('j M Y, g:i A') : '';
                                                    ?>
                                                    <div class="author-box-job"><?= htmlspecialchars($formatted_date, ENT_QUOTES, 'UTF-8') ?></div>
                                                    <div class="author-box-description">
                                                        <p class="p-0 m-0"><?= htmlspecialchars($rating['comment'], ENT_QUOTES, 'UTF-8') ?></p>
                                                    </div>
                                                    <div class="float-right mt-sm-0">
                                                        <i class="fa-solid fa-star text-warning mr-1"></i><?= $rating['rating'] ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                <?php
                                    endif;
                                endforeach;
                                ?>
                                <?php if (count($rating_ids) > 2): ?>
                                    <div class="row">
                                        <div class="col-md-12 d-flex justify-content-end">
                                            <button id="view-more-ratings" type="button" class="btn btn-primary"><?= labels('view_more', "View More") ?></button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Category Section -->
            <div class="col-md-6 col-sm-12 col-xl-6">
                <div class="card h-100">
                    <div class="row border_bottom_for_cards m-0">
                        <div class="col-auto">
                            <div class="toggleButttonPostition"><?= labels('category_section', "Category Section") ?></div>
                        </div>
                        <div class="col d-flex justify-content-end mr-3 mt-4">
                            <input type="checkbox" class="status-switch" id="category_section_status" name="category_section_status" <?= isset($category_section_status) && $category_section_status == "1" ? 'checked' : '' ?>>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for='category_section_title'><?= labels('category_section_title', "Category Section Title") ?></label>
                            <input type='text' class="form-control custome_reset" name='category_section_title' id='category_section_title' value="<?= isset($category_section_title) ? $category_section_title : '' ?>">
                        </div>
                        <div class="form-group">
                            <label for='category_section_description'><?= labels('category_section_description', "Category Section Description") ?></label>
                            <input type='text' class="form-control custome_reset" name='category_section_description' id='category_section_description' value="<?= isset($category_section_description) ? $category_section_description : '' ?>">
                        </div>
                        <?php $category_ids = isset($category_ids) ? $category_ids : [] ?>
                        <div class="col-md-6">
                            <div class="categories form-group" id="categories">
                                <label for="category_item" class="required"><?= labels('choose_a_category', 'Choose a Category') ?></label>
                                <select id="category_item" class="form-control select2" name="categories[]" multiple style="margin-bottom: 20px;">
                                    <option value=""> <?= labels('select', 'Select') ?> <?= labels('category', 'Category') ?> </option>
                                    <?php foreach ($categories_name as $category) : ?>
                                        <?php
                                        if (is_string($category_ids)) {
                                            $category_ids = explode(',', $category_ids);
                                        }
                                        $selected = in_array($category['id'], $category_ids) ? 'selected' : '';
                                        ?>
                                        <option value="<?= $category['id'] ?>" <?= $selected ?>><?= $category['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-md-12 col-sm-12 col-xl-12">
                <div class="card h-100">
                    <div class="row border_bottom_for_cards m-0">
                        <div class="col-auto">
                            <div class="toggleButttonPostition"><?= labels('process_flow', "Process Flow") ?></div>
                        </div>
                        <div class="col d-flex justify-content-end mr-3 mt-4">
                            <input type="checkbox" class="status-switch" id="process_flow_status" name="process_flow_status" <?= isset($process_flow_status) && $process_flow_status == "1" ? 'checked' : '' ?>>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for='web_tagline'><?= labels('landing_page_title', "Landing Page Title") ?></label>
                                    <input type='text' class="form-control custome_reset" name='landing_page_title' id='landing_page_title' value="<?= isset($landing_page_title) ? $landing_page_title : '' ?>" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for='process_flow_title'><?= labels('process_flow_title', "Process Flow Title") ?></label>
                                    <input type='text' class="form-control custome_reset" name='process_flow_title' id='process_flow_title' value="<?= isset($process_flow_title) ? $process_flow_title : '' ?>" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for='step_1_title'><?= labels('process_flow_description', "Process Flow Description") ?></label>
                                    <textarea rows=3 class='form-control h-50 ' name="process_flow_description"><?= isset($process_flow_description) ? $process_flow_description : '' ?></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for='step_1_title'><?= labels('footer_Description', "Footer Description") ?></label>
                                    <textarea rows=3 class='form-control h-50 ' name="footer_description"><?= isset($footer_description) ? $footer_description : '' ?></textarea>
                                </div>
                            </div>
                        </div>
                        <!-- </div> -->
                        <!-- Step 1 -->
                        <div class="row">
                            <div class="col-12 mb-3">
                                <h5><?= labels('step_1', "Step 1") ?></h5>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for='step_1_title'><?= labels('title', "Title") ?></label>
                                    <input type='text' class="form-control custome_reset" name='step_1_title' id='step_1_title' value="<?= isset($step_1_title) ? $step_1_title : '' ?>" />
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for='step_1_description'><?= labels('description', "Description") ?></label>
                                    <input type='text' class="form-control custome_reset" name='step_1_description' id='step_1_description' value="<?= isset($step_1_description) ? $step_1_description : '' ?>" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for='step_1_image'><?= labels('image', "Image") ?></label>
                                    <input type="file" id="step_1_image" name="step_1_image" accept="image/*" class="filepond logo">
                                </div>
                            </div>
                            <?php
                                if (isset($disk)) {
                                    if ($disk === "aws_s3") {
                                        $step_1_image = fetch_cloud_front_url('web_settings', $step_1_image);
                                        $step_2_image = fetch_cloud_front_url('web_settings', $step_2_image);
                                        $step_3_image = fetch_cloud_front_url('web_settings', $step_3_image);
                                        $step_4_image = fetch_cloud_front_url('web_settings', $step_4_image);

                                    } elseif ($disk === "local_server") {
                                        $step_1_image = base_url("public/uploads/web_settings/" . $step_1_image);
                                        $step_2_image = base_url("public/uploads/web_settings/" . $step_2_image);
                                        $step_3_image = base_url("public/uploads/web_settings/" . $step_3_image);
                                        $step_4_image = base_url("public/uploads/web_settings/" . $step_4_image);

                                    }
                                } else {
                                    $step_1_image = base_url('public/backend/assets/img/news/img01.jpg'); // Default logo
                                    $step_2_image = base_url('public/backend/assets/img/news/img01.jpg'); // Default logo
                                    $step_3_image = base_url('public/backend/assets/img/news/img01.jpg'); // Default logo
                                    $step_4_image = base_url('public/backend/assets/img/news/img01.jpg'); // Default logo

                                }

                                ?>
                            <div class="col-md-3">
                                


                                <img class="settings_logo" src="<?= $step_1_image; ?>">
                            </div>
                        </div>
                        <!-- Step 2 -->
                        <div class="row">
                            <div class="col-12 mb-3">
                                <h5><?= labels('step_2', "Step 2") ?></h5>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for='step_2_title'><?= labels('title', "Title") ?></label>
                                    <input type='text' class="form-control custome_reset" name='step_2_title' id='step_2_title' value="<?= isset($step_2_title) ? $step_2_title : '' ?>" />
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for='step_2_description'><?= labels('description', "Description") ?></label>
                                    <input type='text' class="form-control custome_reset" name='step_2_description' id='step_2_description' value="<?= isset($step_2_description) ? $step_2_description : '' ?>" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for='step_2_image'><?= labels('image', "Image") ?></label>
                                    <input type="file" id="step_2_image" name="step_2_image" accept="image/*" class="filepond logo">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <img class="settings_logo" src="<?= $step_2_image; ?>">
                            </div>
                        </div>
                        <!-- Step 3 -->
                        <div class="row">
                            <div class="col-12 mb-3">
                                <h5><?= labels('step_3', "Step 3") ?></h5>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for='step_3_title'><?= labels('title', "Title") ?></label>
                                    <input type='text' class="form-control custome_reset" name='step_3_title' id='step_3_title' value="<?= isset($step_3_title) ? $step_3_title : '' ?>" />
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for='step_3_description'><?= labels('description', "Description") ?></label>
                                    <input type='text' class="form-control custome_reset" name='step_3_description' id='step_3_description' value="<?= isset($step_3_description) ? $step_3_description : '' ?>" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for='step_3_image'><?= labels('image', "Image") ?></label>
                                    <input type="file" id="step_3_image" name="step_3_image" accept="image/*" class="filepond logo">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <img class="settings_logo" src="<?= $step_3_image; ?>">
                            </div>
                        </div>
                        <!-- Step 4 -->
                        <div class="row">
                            <div class="col-12 mb-3">
                                <h5><?= labels('step_4', "Step 4") ?></h5>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for='step_4_title'><?= labels('title', "Title") ?></label>
                                    <input type='text' class="form-control custome_reset" name='step_4_title' id='step_4_title' value="<?= isset($step_4_title) ? $step_4_title : '' ?>" />
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for='step_4_description'><?= labels('description', "Description") ?></label>
                                    <input type='text' class="form-control custome_reset" name='step_4_description' id='step_4_description' value="<?= isset($step_4_description) ? $step_4_description : '' ?>" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for='step_4_image'><?= labels('image', "Image") ?></label>
                                    <input type="file" id="step_4_image" name="step_4_image" accept="image/*" class="filepond logo">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <img class="settings_logo" src="<?= $step_4_image; ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php if ($permissions['update']['settings'] == 1) : ?>
            <div class="row mt-3">
                <div class="col-md d-flex justify-content-end">
                    <input type="submit" name="update" id="update" value="<?= labels('save_changes', "Save") ?>" class="btn btn-lg bg-new-primary">
                </div>
            </div>
        <?php endif; ?>
        <?= form_close() ?>
    </section>
</div>
<div id="ratingsModal" class="modal fade" tabindex="-1" aria-labelledby="ratingsModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= labels('select_rating', 'Select Rating') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php
                $rating_ids = isset($rating_ids) ? $rating_ids : [];
                ?>
                <table class="table table-bordered table-hover" id="slider_list" data-fixed-columns="true"
                    data-pagination-successively-size="2" data-detail-formatter="user_formater" data-auto-refresh="true" data-toggle="table"
                    data-url="<?= base_url("admin/settings/review-list") ?>" data-side-pagination="server" data-pagination="true"
                    data-page-list="[5, 10, 25, 50, 100, 200, All]" data-search="false" data-show-columns="false" data-show-columns-search="true"
                    data-show-refresh="false" data-sort-name="id" data-sort-order="desc" data-query-params="review_query_param">
                    <thead>
                        <tr>
                            <th class="text-center multi-check" data-checkbox="true"></th>
                            <th data-field="id" class="text-center" data-visible="false" data-sortable="true"><?= labels('id', 'ID') ?></th>
                            <th data-field="comment" class="text-center"><?= labels('comment', 'Comment') ?></th>
                            <th data-field="partner_name" class="text-center"><?= labels('provider_name', 'Provider Name') ?></th>
                            <th data-field="profile_image" class="text-center"><?= labels('image', 'Image') ?></th>
                            <th data-field="rated_on" class="text-center"><?= labels('rated_on', 'Rated On') ?></th>
                            <th data-field="stars" class="text-center"><?= labels('rating', 'Rating') ?></th>
                            <th data-field="service_name" class="text-center"><?= labels('service', 'Service') ?></th>
                            <th data-field="user_name" class="text-center"><?= labels('username', 'User Name') ?></th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="saveRatings"><?= labels('save', 'Save') ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= labels('close', 'Close') ?></button>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        <?php
        if (isset($rating_section_status) && $rating_section_status == 1) { ?>
            $('#rating_section_status').siblings('.switchery').addClass('active-content').removeClass('deactive-content');
        <?php   } else { ?>
            $('#rating_section_status').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        <?php  }
        ?>
        <?php
        if (isset($faq_section_status) && $faq_section_status == 1) { ?>
            $('#faq_section_status').siblings('.switchery').addClass('active-content').removeClass('deactive-content');
        <?php   } else { ?>
            $('#faq_section_status').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        <?php  }
        ?>
        <?php
        if (isset($category_section_status) && $category_section_status == 1) { ?>
            $('#category_section_status').siblings('.switchery').addClass('active-content').removeClass('deactive-content');
        <?php   } else { ?>
            $('#category_section_status').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        <?php  }
        ?>
        <?php
        if (isset($process_flow_status) && $process_flow_status == 1) { ?>
            $('#process_flow_status').siblings('.switchery').addClass('active-content').removeClass('deactive-content');
        <?php   } else { ?>
            $('#process_flow_status').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        <?php  }
        ?>
    });
    $(document).ready(function() {
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
        var rating_section_status = document.querySelector('#rating_section_status');
        var faq_section_status = document.querySelector('#faq_section_status');
        var category_section_status = document.querySelector('#category_section_status');
        var process_flow_status = document.querySelector('#process_flow_status');
        rating_section_status.addEventListener('change', function() {
            handleSwitchChange(rating_section_status);
        });
        faq_section_status.addEventListener('change', function() {
            handleSwitchChange(faq_section_status);
        });
        category_section_status.addEventListener('change', function() {
            handleSwitchChange(category_section_status);
        });
        process_flow_status.addEventListener('change', function() {
            handleSwitchChange(process_flow_status);
        });
    });

    function review_query_param(p) {
        return {
            limit: p.limit,
            sort: p.sort,
            order: p.order,
            offset: p.offset,
        };
    }
</script>
<?php if (count($rating_ids) > 2): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var viewMoreButton = document.getElementById('view-more-ratings');
            if (viewMoreButton) {
                viewMoreButton.addEventListener('click', function(event) {
                    event.preventDefault();
                    document.querySelectorAll('.more-ratings').forEach(rating => rating.classList.remove('d-none'));
                    this.style.display = 'none';
                });
            }
        });
    </script>
<?php endif; ?>
<script>
    $(document).ready(function() {
        var ratingIds = <?= json_encode($rating_ids); ?>;
        $('#slider_list').bootstrapTable({
            onLoadSuccess: function(data) {
                setTimeout(function() {
                    ratingIds.forEach(function(id) {
                        $('#slider_list').bootstrapTable('checkBy', {
                            field: 'id',
                            values: [id]
                        });
                    });
                }, 0);
            },
            onCheck: function(row) {
                if (!ratingIds.includes(row.id)) {
                    ratingIds.push(row.id);
                }
            },
            onUncheck: function(row) {
                ratingIds = ratingIds.filter(id => id !== row.id);
            }
        });
        $('#saveRatings').click(function() {
            $('#new_rating_ids').val(ratingIds);
            $('#ratingsModal').modal('hide');
        });
        document.getElementById('select-ratings').addEventListener('click', function() {
            $('#ratingsModal').modal('show');
        });
    });
</script>