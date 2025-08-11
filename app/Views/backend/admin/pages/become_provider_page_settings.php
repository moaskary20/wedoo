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
                    <a class="nav-link" href="<?= base_url('admin/settings/web-landing-page-settings') ?>" id="pills-about_us">
                        <?= labels('landing_page_settings', "Landing Page Settings") ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="<?= base_url('admin/settings/become-provider-setting') ?>" id="pills-about_us">
                        <?= labels('become_provider_page_settings', "Become Provider Page Settings") ?>
                    </a>
                </li>
            </div>
        </ul>

        <form id="become_provider_form"
            action="<?= base_url('admin/settings/become-provider-setting-update') ?>"
            method="post"
            enctype="multipart/form-data">
            <div class="row mb-4">
                <?php
                $hero_section = isset($hero_section) ? json_decode($hero_section, true) : [];
                $how_it_work_section = isset($how_it_work_section) ? json_decode($how_it_work_section, true) : [];
                $category_section = isset($category_section) ? json_decode($category_section, true) : [];
                $subscription_section = isset($subscription_section) ? json_decode($subscription_section, true) : [];
                $top_providers_section = isset($top_providers_section) ? json_decode($top_providers_section, true) : [];
                $review_section = isset($review_section) ? json_decode($review_section, true) : [];
                $faq_section = isset($faq_section) ? json_decode($faq_section, true) : [];
                $feature_section = isset($feature_section) ? json_decode($feature_section, true) : [];
                ?>
                <!-- Hero Section -->
                <div class="col-md-12 col-sm-12 col-xl-12 mb-3">
                    <div class="card h-100">
                        <div class="row pl-3 m-0 border_bottom_for_cards">
                            <div class="col-auto">
                                <div class="toggleButttonPostition"><?= labels('hero_section', 'Hero Section') ?></div>
                            </div>
                            <div class="col d-flex justify-content-end mt-4">
                                <input type="checkbox" id="hero_section_status" class="status-switch" name="hero_section_status"
                                    <?= (isset($hero_section['status']) && $hero_section['status'] == '1') ? 'checked' : '' ?>>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="hero_section_short_headline" class=""><?= labels('short_headline', 'Short Headline') ?></label>
                                        <input id="hero_section_short_headline" value="<?= isset($hero_section['short_headline']) ? $hero_section['short_headline'] : "" ?>" class="form-control" type="text" name="hero_section_short_headline" placeholder="<?= labels('enter', 'Enter ') ?> <?= labels('short_headline', 'the short headline ') ?> <?= labels('here', ' Here ') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="hero_section_title" class=""><?= labels('title', 'Title') ?></label>
                                        <input id="hero_section_title" class="form-control" value="<?= isset($hero_section['title']) ? $hero_section['title'] : "" ?>" type="text" name="hero_section_title" placeholder="<?= labels('enter', 'Enter ') ?> <?= labels('title', 'the title') ?> <?= labels('here', ' Here ') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="hero_section_description" class=""><?= labels('description', 'Description') ?></label>
                                        <textarea name="hero_section_description" id="hero_section_description" class="form-control" placeholder="<?= labels('description', 'the description') ?> <?= labels('here', ' Here ') ?>"><?= isset($hero_section['description']) ? $hero_section['description'] : "" ?></textarea>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for='hero_section_images'><?= labels('images', "Images") ?></label>
                                        <input type="file" name="hero_section_images[]" multiple class="filepond logo" id="hero_section_images" accept="image/*">

                                        <?php
                                        if (!empty($hero_section['images'])) {
                                            $other_images = ($hero_section['images']);
                                            $disk = fetch_current_file_manager();

                                        ?>
                                            <div class="row">
                                                <?php foreach ($other_images as $key => $row) { ?>
                                                    <?php
                                                    if ($disk == "aws_s3") {
                                                        $image_url = fetch_cloud_front_url('become_provider', $row['image']);
                                                    } else if ($disk == "local_server") {
                                                        $image_url = base_url('public/uploads/become_provider/' . $row['image']);
                                                    } else {
                                                        $image_url = base_url('public/uploads/become_provider/' . $row['image']);
                                                    }
                                                    ?>
                                                    <div class="col-xl-4 col-md-12">
                                                        <img alt="no image found" width="130px" style="border: solid 1; border-radius: 12px;" height="100px" class="mt-2" id="image_preview" src="<?= isset($image_url) ? ($image_url) : "" ?>">
                                                        <input type="hidden" name="hero_section_images_existing[<?= $key ?>][image]" value="<?= $row['image'] ?>">
                                                        <input type="hidden" name="hero_section_images_existing[<?= $key ?>][disk]" value="<?= $disk ?>">
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- How It work  Section -->
                <div class="col-md-12 col-sm-12 col-xl-12  mb-3">
                    <div class="card h-100">
                        <div class="row pl-3 m-0 border_bottom_for_cards">
                            <div class="col-auto">
                                <div class="toggleButttonPostition"><?= labels('how_it_work_section', 'How It Work Section') ?></div>
                            </div>
                            <div class="col d-flex justify-content-end  mt-4 ">
                                <input type="checkbox" id="how_it_work_section_status" class="status-switch" name="how_it_work_section_status"
                                    <?= (isset($how_it_work_section['status']) && $how_it_work_section['status'] == '1') ? 'checked' : '' ?>>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="how_it_work_section_short_headline" class=""><?= labels('short_headline', 'Short Headline') ?></label>
                                        <input id="how_it_work_section_short_headline" value="<?= isset($how_it_work_section['short_headline']) ? $how_it_work_section['short_headline'] : "" ?>" class="form-control" type="text" name="how_it_work_section_short_headline" placeholder="<?= labels('enter', 'Enter ') ?> <?= labels('short_headline', 'the short headline ') ?> <?= labels('here', ' Here ') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="how_it_work_section_title" class=""><?= labels('title', 'Title') ?></label>
                                        <input id="how_it_work_section_title" class="form-control" value="<?= isset($how_it_work_section['title']) ? $how_it_work_section['title'] : "" ?>" type="text" name="how_it_work_section_title" placeholder="<?= labels('enter', 'Enter ') ?> <?= labels('title', 'the title') ?> <?= labels('here', ' Here ') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="how_it_work_section_description" class=""><?= labels('description', 'Description') ?></label>
                                        <textarea name="how_it_work_section_description" id="how_it_work_section_description" class="form-control" placeholder="<?= labels('description', 'the description') ?> <?= labels('here', ' Here ') ?>"><?= isset($how_it_work_section['description']) ? $how_it_work_section['description'] : "" ?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label for="steps" class=""> Steps </label>
                                    <?php
                                    $steps = (isset($how_it_work_section['steps'])) ? json_decode($how_it_work_section['steps'], true) : [];
                                    ?>
                                    <?php if (count($steps) == 0): ?>
                                        <div id="how_it_work_section_steps_container">
                                            <div class="row input-group mb-2">
                                                <div class="col-md-5">
                                                    <input id="how_it_work_section_steps" class="form-control" placeholder="Enter title" type="text" name="how_it_work_section_steps[0][title]">
                                                </div>
                                                <div class="col-md-5">
                                                    <input id="how_it_work_section_steps" class="form-control" placeholder="Enter description" type="text" name="how_it_work_section_steps[0][description]">
                                                </div>
                                                <div class="col-md-2 ">
                                                    <button type="button" class="btn btn-outline-primary add-how-it-work-steps">
                                                        <i class="fa fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else : ?>
                                        <div id="how_it_work_section_steps_container">
                                            <?php foreach ($steps as $step_index => $step) : ?>
                                                <div class="row input-group mb-2">
                                                    <div class="col-md-5">
                                                        <input id="how_it_work_section_steps" value="<?= isset($step['title']) ? $step['title'] : '' ?>" class="form-control" placeholder="Enter title" type="text" name="how_it_work_section_steps[<?= $step_index; ?>][title]">
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input id="how_it_work_section_steps" value="<?= isset($step['description']) ? $step['description'] : '' ?>" class="form-control" placeholder="Enter description" type="text" name="how_it_work_section_steps[<?= $step_index; ?>][description]">
                                                    </div>
                                                    <?php if ($step_index == 0) : ?>
                                                        <div class="col-md-2 ">
                                                            <button type="button" class="btn btn-outline-primary add-how-it-work-steps">
                                                                <i class="fa fa-plus"></i>
                                                            </button>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Feature section -->
                <div class="col-md-12 col-sm-12 col-xl-12 mb-3">
                    <?php

                    $feature = isset($feature_section['features']) ? ($feature_section['features']) : [];

                    ?>
                    <div class="card h-100">
                        <div class="row pl-3 m-0 border_bottom_for_cards">
                            <div class="col-auto">
                                <div class="toggleButttonPostition"><?= labels('feature_section', 'Feature Section') ?></div>
                            </div>
                            <div class="col d-flex justify-content-end mt-4">
                                <input type="checkbox" id="feature_section_status" class="status-switch" name="feature_section_status"
                                    <?= (isset($feature_section['status']) && $feature_section['status'] == '1') ? 'checked' : '' ?>>
                            </div>
                        </div>
                        <?php if (count($feature) == 0) : ?>
                            <div class="card-body" id="feature_section_features_container">
                                <div class="card">
                                    <div class="d-flex justify-content-between align-items-center m-0 border_bottom_for_cards">
                                        <div class="col-auto">
                                            <div class="my-3 toggleButttonPostition">Feature 1</div>
                                        </div>
                                        <div class="col d-flex justify-content-end ">
                                            <button type="button" class="btn btn-outline-primary add-feature-section">
                                                <i class="fa fa-plus"></i>
                                                <label for='add_feature' class="m-0"><?= labels('add_feature', "Add Feature") ?></label>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row  feature-section-card card-body  mb-2" data-section-index="0">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="feature_section_short_headline" class="">
                                                    <?= labels('short_headline', 'Short Headline') ?></label>
                                                <input id="feature_section_short_headline" class="form-control" type="text" name="feature_section_feature[0][short_headline]" placeholder="<?= labels('enter', 'Enter ') ?> <?= labels('short_headline', 'the short headline ') ?> <?= labels('here', ' Here ') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="feature_section_title" class=""><?= labels('title', 'Title') ?></label>
                                                <input id="feature_section_title" class="form-control" type="text" name="feature_section_feature[0][title]" placeholder="<?= labels('enter', 'Enter ') ?> <?= labels('title', 'the title') ?> <?= labels('here', ' Here ') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="feature_section_description" class=""><?= labels('description', 'Description') ?></label>
                                                <textarea name="feature_section_feature[0][description]" id="feature_section_description" class="form-control" placeholder="<?= labels('description', 'the description') ?> <?= labels('here', ' Here ') ?>"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="feature_section_position" class=""><?= labels('position', 'Position') ?></label>
                                                <select class="form-control" name="feature_section_feature[0][position]" id="feature_section_position">
                                                    <option disabled selected> <?= labels('select_position', 'Select Position') ?></option>
                                                    <option value="right">
                                                        <?= labels('right', 'Right') ?>
                                                    </option>
                                                    <option value="left">
                                                        <?= labels('left', 'Left') ?>
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for='feature_section_image'><?= labels('image', "Image") ?></label>
                                                <input type="file" name="feature_section_feature[0][image]" class="filepond logo" id="feature_section_images" accept="image/*">
                                                <input type="hidden" name="feature_section_feature[0][exist_image]" value="new">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php else : ?>
                            <div class="card-body" id="feature_section_features_container">
                                <?php foreach ($feature as $index => $value) : ?>
                                    <div class="card">
                                        <div class="d-flex justify-content-between align-items-center m-0 border_bottom_for_cards">
                                            <div class="col-auto">
                                                <div class="my-3 toggleButttonPostition">Feature <?= $index + 1; ?></div>
                                            </div>
                                            <?php if ($index == 0) : ?>
                                                <div class="col d-flex justify-content-end ">
                                                    <button type="button" class="btn btn-outline-primary add-feature-section">
                                                        <i class="fa fa-plus"></i>
                                                        <label for='add_feature' class="m-0"><?= labels('add_feature', "Add Feature") ?></label>
                                                    </button>
                                                </div>
                                            <?php else: ?>
                                                <div class="col d-flex justify-content-end">
                                                    <button type="button" class="btn btn-outline-danger remove-feature-section">
                                                        <i class="fa fa-minus"></i>
                                                        <label class="m-0"><?= labels('remove_feature', "Remove Feature") ?></label>
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="row  feature-section-card card-body  mb-2" data-section-index="<?= $index ?>">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="feature_section_short_headline" class="">
                                                        <?= labels('short_headline', 'Short Headline') ?></label>
                                                    <input id="feature_section_short_headline" class="form-control" value="<?= isset($value['short_headline']) ? $value['short_headline'] : "" ?>" type="text" name="feature_section_feature[<?= $index ?>][short_headline]" placeholder="<?= labels('enter', 'Enter ') ?> <?= labels('short_headline', 'the short headline ') ?> <?= labels('here', ' Here ') ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="feature_section_title" class=""><?= labels('title', 'Title') ?></label>
                                                    <input id="feature_section_title" value="<?= isset($value['title']) ? $value['title'] : "" ?>" class="form-control" type="text" name="feature_section_feature[<?= $index ?>][title]" placeholder="<?= labels('enter', 'Enter ') ?> <?= labels('title', 'the title') ?> <?= labels('here', ' Here ') ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="feature_section_description" class=""><?= labels('description', 'Description') ?></label>
                                                    <textarea name="feature_section_feature[<?= $index ?>][description]" id="feature_section_description" class="form-control" placeholder="<?= labels('description', 'the description') ?> <?= labels('here', ' Here ') ?>"><?= isset($value['description']) ? $value['description'] : "" ?> </textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="feature_section_position" class=""><?= labels('position', 'Position') ?></label>
                                                    <select class="form-control" name="feature_section_feature[<?= $index ?>][position]" id="feature_section_position">
                                                        <option disabled><?= labels('select_position', 'Select Position') ?></option>
                                                        <option value="right" <?= isset($value['position']) && $value['position'] == "right" ? 'selected="selected"' : "" ?>>
                                                            <?= labels('right', 'Right') ?>
                                                        </option>
                                                        <option value="left" <?= isset($value['position']) && $value['position'] == "left" ? 'selected="selected"' : "" ?>>
                                                            <?= labels('left', 'Left') ?>
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for='feature_section_image'><?= labels('image', "Image") ?></label>

                                                    <?php
                                                    $disk = fetch_current_file_manager();

                                                    ?>
                                                    <input type="file" name="feature_section_feature[<?= $index ?>][image]" class="filepond logo" id="feature_section_images" accept="image/*">
                                                    <input type="hidden" name="feature_section_feature[<?= $index ?>][exist_image]" value="<?= $value['image']; ?>">
                                                    <input type="hidden" name="feature_section_feature[<?= $index ?>][exist_disk]" value="<?= $disk; ?>">

                                                    <?php

                                                    if ($disk== "aws_s3") {

                                                        $image_url = fetch_cloud_front_url('become_provider', $value['image']);
                                                    } else if ($disk== "local_server") {
                                                        $image_url = base_url('public/uploads/become_provider/' . $value['image']);
                                                    } else {
                                                        $image_url = base_url('public/backend/assets/img/news/img01.jpg');
                                                    }
                                                    ?>

                                                    <img class="settings_logo" src="<?= $image_url; ?>">

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- Categroy section -->
                <div class="col-md-12 col-sm-12 col-xl-12 mb-3">
                    <div class="card h-100">
                        <div class="row pl-3 m-0 border_bottom_for_cards">
                            <div class="col-auto">
                                <div class="toggleButttonPostition"><?= labels('category_section', 'Category Section') ?></div>
                            </div>
                            <div class="col d-flex justify-content-end  mt-4 ">
                                <input type="checkbox" id="category_section_status" class="status-switch" name="category_section_status"
                                    <?= (isset($category_section['status']) && $category_section['status'] == '1') ? 'checked' : '' ?>>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="category_section_short_headline" class=""><?= labels('short_headline', 'Short Headline') ?></label>
                                        <input id="category_section_short_headline" value="<?= isset($category_section['short_headline']) ? $category_section['short_headline'] : "" ?>" class="form-control" type="text" name="category_section_short_headline" placeholder="<?= labels('enter', 'Enter ') ?> <?= labels('short_headline', 'the short headline ') ?> <?= labels('here', ' Here ') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="category_section_title" class=""><?= labels('title', 'Title') ?></label>
                                        <input id="category_section_title" value="<?= isset($category_section['title']) ? $category_section['title'] : "" ?>" class="form-control" type="text" name="category_section_title" placeholder="<?= labels('enter', 'Enter ') ?> <?= labels('title', 'the title') ?> <?= labels('here', ' Here ') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="category_section_description" class=""><?= labels('description', 'Description') ?></label>
                                        <textarea name="category_section_description" id="category_section_description" class="form-control" placeholder="<?= labels('description', 'the description') ?> <?= labels('here', ' Here ') ?>"><?= isset($category_section['description']) ? $category_section['description'] : "" ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Subscription section -->
                <div class="col-md-12 col-sm-12 col-xl-12 mb-3">
                    <div class="card h-100">
                        <div class="row pl-3 m-0 border_bottom_for_cards">
                            <div class="col-auto">
                                <div class="toggleButttonPostition"><?= labels('subscription_section', 'Subscription Section') ?></div>
                            </div>
                            <div class="col d-flex justify-content-end  mt-4 ">
                                <input type="checkbox" id="subscription_section_status" class="status-switch" name="subscription_section_status"
                                    <?= (isset($subscription_section['status']) && $subscription_section['status'] == '1') ? 'checked' : '' ?>>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="subscription_section_short_headline" class=""><?= labels('short_headline', 'Short Headline') ?></label>
                                        <input id="subscription_section_short_headline" value="<?= isset($subscription_section['short_headline']) ? $subscription_section['short_headline'] : "" ?>" class="form-control" type="text" name="subscription_section_short_headline" placeholder="<?= labels('enter', 'Enter ') ?> <?= labels('short_headline', 'the short headline ') ?> <?= labels('here', ' Here ') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="subscription_section_title" class=""><?= labels('title', 'Title') ?></label>
                                        <input id="subscription_section_title" value="<?= isset($subscription_section['title']) ? $subscription_section['title'] : "" ?>" class="form-control" type="text" name="subscription_section_title" placeholder="<?= labels('enter', 'Enter ') ?> <?= labels('title', 'the title') ?> <?= labels('here', ' Here ') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="subscription_section_description" class=""><?= labels('description', 'Description') ?></label>
                                        <textarea name="subscription_section_description" id="subscription_section_description" class="form-control" placeholder="<?= labels('description', 'the description') ?> <?= labels('here', ' Here ') ?>"> <?= isset($subscription_section['description']) ? $subscription_section['description'] : "" ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Top Providers section -->
                <div class="col-md-12 col-sm-12 col-xl-12 mb-3">
                    <div class="card h-100">
                        <div class="row pl-3 m-0 border_bottom_for_cards">
                            <div class="col-auto">
                                <div class="toggleButttonPostition"><?= labels('top_providers_section', 'Top Providers Section') ?></div>
                            </div>
                            <div class="col d-flex justify-content-end  mt-4 ">
                                <input type="checkbox" id="top_providers_section_status" class="status-switch" name="top_providers_section_status"
                                    <?= (isset($top_providers_section['status']) && $top_providers_section['status'] == '1') ? 'checked' : '' ?>>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="top_providers_section_short_headline" class=""><?= labels('short_headline', 'Short Headline') ?></label>
                                        <input id="top_providers_section_short_headline" value="<?= isset($top_providers_section['short_headline']) ? $top_providers_section['short_headline'] : "" ?>" class="form-control" type="text" name="top_providers_section_short_headline" placeholder="<?= labels('enter', 'Enter ') ?> <?= labels('short_headline', 'the short headline ') ?> <?= labels('here', ' Here ') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="top_providers_section_title" class=""><?= labels('title', 'Title') ?></label>
                                        <input id="top_providers_section_title" value="<?= isset($top_providers_section['title']) ? $top_providers_section['title'] : "" ?>" class="form-control" type="text" name="top_providers_section_title" placeholder="<?= labels('enter', 'Enter ') ?> <?= labels('title', 'the title') ?> <?= labels('here', ' Here ') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="top_providers_section_description" class=""><?= labels('description', 'Description') ?></label>
                                        <textarea name="top_providers_section_description" id="top_providers_section_description" class="form-control" placeholder="<?= labels('description', 'the description') ?> <?= labels('here', ' Here ') ?>"><?= isset($top_providers_section['description']) ? $top_providers_section['description'] : "" ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Review section -->
                <div class="col-md-12 col-sm-12 col-xl-12 mb-3">
                    <div class="card h-100">
                        <div class="row pl-3 m-0 border_bottom_for_cards">
                            <div class="col-auto">
                                <div class="toggleButttonPostition"><?= labels('review_section', 'Review Section') ?></div>
                            </div>
                            <div class="col d-flex justify-content-end  mt-4 ">
                                <input type="checkbox" id="review_section_status" class="status-switch" name="review_section_status"
                                    <?= (isset($review_section['status']) && $review_section['status'] == '1') ? 'checked' : '' ?>>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="review_section_short_headline" class=""><?= labels('short_headline', 'Short Headline') ?></label>
                                        <input id="review_section_short_headline" value="<?= isset($review_section['short_headline']) ? $review_section['short_headline'] : "" ?>" class="form-control" type="text" name="review_section_short_headline" placeholder="<?= labels('enter', 'Enter ') ?> <?= labels('short_headline', 'the short headline ') ?> <?= labels('here', ' Here ') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="review_section_title" class=""><?= labels('title', 'Title') ?></label>
                                        <input id="review_section_title" value="<?= isset($review_section['title']) ? $review_section['title'] : "" ?>" class="form-control" type="text" name="review_section_title" placeholder="<?= labels('enter', 'Enter ') ?> <?= labels('title', 'the title') ?> <?= labels('here', ' Here ') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="review_section_description" class=""><?= labels('description', 'Description') ?></label>
                                        <textarea name="review_section_description" id="review_section_description" class="form-control" placeholder="<?= labels('description', 'the description') ?> <?= labels('here', ' Here ') ?>"><?= isset($review_section['description']) ? $review_section['description'] : "" ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- FAQs section -->
                <div class="col-md-12 col-sm-12 col-xl-12 mb-3">
                    <div class="card h-100">
                        <div class="row pl-3 m-0 border_bottom_for_cards">
                            <div class="col-auto">
                                <div class="toggleButttonPostition"><?= labels('faq_section', 'FAQ Section') ?></div>
                            </div>
                            <div class="col d-flex justify-content-end  mt-4 ">
                                <input type="checkbox" id="faq_section_status" class="status-switch" name="faq_section_status"
                                    <?= (isset($faq_section['status']) && $faq_section['status'] == '1') ? 'checked' : '' ?>>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="faq_section_short_headline" class=""><?= labels('short_headline', 'Short Headline') ?></label>
                                        <input id="faq_section_short_headline" value="<?= isset($faq_section['short_headline']) ? $faq_section['short_headline'] : "" ?>" class="form-control" type="text" name="faq_section_short_headline" placeholder="<?= labels('enter', 'Enter ') ?> <?= labels('short_headline', 'the short headline ') ?> <?= labels('here', ' Here ') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="faq_section_title" class=""><?= labels('title', 'Title') ?></label>
                                        <input id="faq_section_title" value="<?= isset($faq_section['title']) ? $faq_section['title'] : "" ?>" class="form-control" type="text" name="faq_section_title" placeholder="<?= labels('enter', 'Enter ') ?> <?= labels('title', 'the title') ?> <?= labels('here', ' Here ') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="faq_section_description" class=""><?= labels('description', 'Description') ?></label>
                                        <textarea name="faq_section_description" id="faq_section_description" class="form-control" placeholder="<?= labels('description', 'the description') ?> <?= labels('here', ' Here ') ?>"><?= isset($faq_section['description']) ? $faq_section['description'] : "" ?></textarea>
                                    </div>
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
        </form>
    </section>
</div>
<script>
    $(document).ready(function() {
        const sections = [{
                id: 'hero_section_status',
                status: <?= isset($hero_section['status']) ? $hero_section['status'] : 0 ?>
            },
            {
                id: 'how_it_work_section_status',
                status: <?= isset($how_it_work_section['status']) ? $how_it_work_section['status'] : 0 ?>
            },
            {
                id: 'feature_section_status',
                status: <?= isset($feature_section['status']) ? $feature_section['status'] : 0 ?>
            },
            {
                id: 'category_section_status',
                status: <?= isset($category_section['status']) ? $category_section['status'] : 0 ?>
            },
            {
                id: 'subscription_section_status',
                status: <?= isset($subscription_section['status']) ? $subscription_section['status'] : 0 ?>
            },
            {
                id: 'top_providers_section_status',
                status: <?= isset($top_providers_section['status']) ? $top_providers_section['status'] : 0 ?>
            },
            {
                id: 'review_section_status',
                status: <?= isset($review_section['status']) ? $review_section['status'] : 0 ?>
            },
            {
                id: 'faq_section_status',
                status: <?= isset($faq_section['status']) ? $faq_section['status'] : 0 ?>
            },
        ];
        sections.forEach(function(section) {
            if (section.status == 1) {
                $('#' + section.id).siblings('.switchery').addClass('active-content').removeClass('deactive-content');
            } else {
                $('#' + section.id).siblings('.switchery').addClass('deactive-content').removeClass('active-content');
            }
        });
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
        $(document).ready(function() {
            const sectionIds = [
                'hero_section_status',
                'how_it_work_section_status',
                'feature_section_status',
                'category_section_status',
                'subscription_section_status',
                'top_providers_section_status',
                'review_section_status',
                'faq_section_status'
            ];
            sectionIds.forEach(function(id) {
                var sectionStatus = document.querySelector('#' + id);
                sectionStatus.addEventListener('change', function() {
                    handleSwitchChange(sectionStatus);
                });
            });
        });
    });
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('how_it_work_section_steps_container');
        container.addEventListener('click', function(event) {
            // Add new step
            if (event.target.closest('.add-how-it-work-steps')) {
                // Determine the next index based on current elements
                const currentCount = container.querySelectorAll('.input-group').length;
                const newTagInput = `
                <div class="row input-group mb-2"> 
                    <div class="col-md-5"> 
                        <input class="form-control" placeholder="Enter title" type="text" name="how_it_work_section_steps[${currentCount}][title]">
                    </div> 
                    <div class="col-md-5"> 
                        <input class="form-control" placeholder="Enter description" type="text" name="how_it_work_section_steps[${currentCount}][description]"> 
                    </div> 
                    <div class="col-md-2"> 
                        <button type="button" class="btn btn-outline-danger remove-how-it-work-steps"> 
                            <i class="fa fa-minus"></i>
                        </button>
                    </div> 
                </div>`;
                container.insertAdjacentHTML('beforeend', newTagInput);
            }
            // Remove step
            if (event.target.closest('.remove-how-it-work-steps')) {
                event.target.closest('.input-group').remove();
                // Re-index remaining fields
                const rows = container.querySelectorAll('.input-group');
                rows.forEach((row, index) => {
                    row.querySelectorAll('input').forEach(input => {
                        const nameAttr = input.name;
                        const newName = nameAttr.replace(/\[\d+\]/, `[${index}]`);
                        input.name = newName;
                    });
                });
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('feature_section_features_container');
        let sectionIndex = container.querySelectorAll('.card').length;

        // Handle all click events in the container
        container.addEventListener('click', function(event) {
            // Handle Add Feature button
            if (event.target.closest('.add-feature-section')) {
                const newSection = `
                <div class="card">
                    <div class="d-flex justify-content-between align-items-center m-0 border_bottom_for_cards">
                        <div class="col-auto">
                            <div class="my-3 toggleButttonPostition">Feature ${sectionIndex + 1}</div>
                        </div>
                        <div class="col d-flex justify-content-end">
                            <button type="button" class="btn btn-outline-danger remove-feature-section">
                                <i class="fa fa-minus"></i>
                                <label class="m-0">Remove Feature</label>
                            </button>
                        </div>
                    </div>
                    <div class="row feature-section-card card-body mb-3" data-section-index="${sectionIndex}">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="">Short Headline</label>
                                <input class="form-control" type="text" 
                                    name="feature_section_feature[${sectionIndex}][short_headline]" 
                                    placeholder="the short headline">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="">Title</label>
                                <input class="form-control" type="text" 
                                    name="feature_section_feature[${sectionIndex}][title]" 
                                    placeholder="title">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="">Description</label>
                                <textarea name="feature_section_feature[${sectionIndex}][description]" 
                                    class="form-control" 
                                    placeholder="the description"></textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="">Position</label>
                                <select class="form-control" name="feature_section_feature[${sectionIndex}][position]">
                                    <option disabled selected>Select Position</option>
                                    <option value="right">Right</option>
                                    <option value="left">Left</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Image</label>
                                <input name="feature_section_feature[${sectionIndex}][image]" type="file" class="filepond logo" accept="image/*">
                                <input type="hidden" name="feature_section_feature[${sectionIndex}][exist_image]" value="new">
                            </div>
                        </div>
                    </div>
                </div>`;

                container.insertAdjacentHTML('beforeend', newSection);



                const fileInput = container.querySelector(`.feature-section-card[data-section-index="${sectionIndex}"] .filepond.logo`);

                if (fileInput) {
                    FilePond.create(fileInput, {
                        credits: null,
                        allowFileSizeValidation: true,
                        maxFileSize: "25MB",
                        labelMaxFileSizeExceeded: "File is too large",
                        labelMaxFileSize: "Maximum file size is {filesize}",
                        allowFileTypeValidation: true,
                        acceptedFileTypes: ["image/*"],
                        labelFileTypeNotAllowed: "File of invalid type",
                        fileValidateTypeLabelExpectedTypes: "Expects {allButLastType} or {lastType}",
                        storeAsFile: true,
                        allowPdfPreview: true,
                        pdfPreviewHeight: 320,
                        pdfComponentExtraParams: "toolbar=0&navpanes=0&scrollbar=0&view=fitH",
                        allowVideoPreview: true,
                        allowAudioPreview: true,
                    });
                   
                } else {
                    console.error("File input not found for section index:", sectionIndex);
                }

                sectionIndex++;

                // Update all section indices
                const sections = container.querySelectorAll('.card');
                sections.forEach((section, index) => {
                    const label = section.querySelector('.toggleButttonPostition');
                    if (label) {
                        label.textContent = `Feature ${index + 1}`;
                    }
                    const featureSection = section.querySelector('.feature-section-card');
                    if (featureSection) {
                        featureSection.setAttribute('data-section-index', index);
                    }
                });
            }

            // Handle Remove Feature button
            if (event.target.closest('.remove-feature-section')) {
                const section = event.target.closest('.card');
                if (section && container.querySelectorAll('.card').length > 1) {
                    section.remove();

                    // Update remaining section indices
                    const sections = container.querySelectorAll('.card');
                    sections.forEach((section, index) => {
                        const label = section.querySelector('.toggleButttonPostition');
                        if (label) {
                            label.textContent = `Feature ${index + 1}`;
                        }
                        const featureSection = section.querySelector('.feature-section-card');
                        if (featureSection) {
                            featureSection.setAttribute('data-section-index', index);
                        }
                    });
                    sectionIndex = sections.length;
                }
            }
        });

    });
</script>