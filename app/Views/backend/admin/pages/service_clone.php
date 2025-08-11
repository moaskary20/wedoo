<?php
$check_payment_gateway = get_settings('payment_gateways_settings', true);
$cod_setting =  $check_payment_gateway['cod_setting'];
?>
<div class="main-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('add_services', "Add Service") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item"><?= labels('services', 'Services') ?></a></div>
            </div>
        </div>
        <?= form_open('/admin/services/insert_service', ['method' => "post", 'class' => 'form-submit-event', 'id' => 'add_service', 'enctype' => "multipart/form-data"]); ?>

        <input type="hidden" name="service_id" id="service_id" value=<?= $service['id'] ?>>
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="row pl-3">
                        <div class="col border_bottom_for_cards">
                            <div class="toggleButttonPostition"><?= labels('service_details', 'Service Details') ?></div>
                        </div>
                        <div class="col d-flex justify-content-end mr-3 mt-4 border_bottom_for_cards">
                            <div class="form-group">
                                <label class="required"><?= labels('status', 'Status') ?></label>
                                <?php
                                if ($service['status'] == "1") { ?>
                                    <input type="checkbox" id="status" name="status" class="status-switch" checked>
                                <?php   } else { ?> <input type="checkbox" id="status" name="status" class="status-switch">
                                <?php  }
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <div class="jquery-script-clear"></div>
                                <div class="categories" id="categories">
                                    <label for="partner" class="required"><?= labels('select_provider', 'Select Provider') ?></label> <br>
                                    <select id="partner" class="form-control w-100 select2" name="partner">
                                        <option value=""><?= labels('select_provider', 'Select Provider') ?></option>
                                        <?php foreach ($partner_name as $pn) : ?>
                                            <option value="<?= $pn['id'] ?>" <?php echo  isset($service['user_id'])  && $service['user_id'] ==  $pn['id'] ? 'selected' : '' ?> data-at_store="<?= $pn['at_store'] ?>" data-at_doorstep="<?= $pn['at_doorstep'] ?>" data-need_approval_for_the_service="<?= $pn['need_approval_for_the_service'] ?>">
                                                <?= $pn['company_name'] . ' - ' . $pn['username'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <div class="categories" id="categories">
                                    <label for="category_item" class="required"><?= labels('choose_a_category_for_your_service', 'Choose a Category for your service') ?></label>
                                    <select id="category_item" class="form-control select2" name="categories" style="margin-bottom: 20px;">
                                        <option value=""><?= labels('select', 'Select') ?> <?= labels('category', 'Category') ?></option>
                                        <?php
                                        function renderCategories($categories_name, $parent_id = 0, $depth = 0, $selected_id = null)
                                        {
                                            $html = '';
                                            foreach ($categories_name as $category) {
                                                if ($category['parent_id'] == $parent_id) {
                                                    $is_selected = ($category['id'] == $selected_id) ? 'selected' : '';
                                                    $padding = str_repeat('&nbsp;', $depth * 4);
                                                    $html .= sprintf(
                                                        '<option value="%s" %s style="padding-left: %spx;">%s%s</option>',
                                                        htmlspecialchars($category['id']),
                                                        $is_selected,
                                                        $depth * 20,
                                                        $padding,
                                                        htmlspecialchars($category['name'])
                                                    );
                                                    $html .= renderCategories($categories_name, $category['id'], $depth + 1, $selected_id);
                                                }
                                            }
                                            return $html;
                                        }

                                        $selected_category_id = isset($service['category_id']) ? $service['category_id'] : null;
                                        echo renderCategories($categories_name, 0, 0, $selected_category_id);
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title" class="required"><?= labels('title_of_the_service', 'Title of the service') ?> </label>
                                    <input class="form-control" type="text" name="title" value="<?= isset($service['title']) ? $service['title'] : "" ?>" id="service_title">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="service_slug" class="required"><?= labels('slug', 'Slug') ?></label>
                                    <input id="service_slug" class="form-control" type="text" value="<?= isset($service['slug']) ? $service['slug'] : "" ?>" name="service_slug" placeholder="<?= labels('enter_the_slug', 'Enter the slug') ?> ">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="tags" class="required"><?= labels('tags', 'Tags') ?></label>
                                    <i data-content=" <?= labels('data_content_for_tags', 'These tags will help find the services while users search for the services.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                                    <input id="tags" style="border-radius: 0.25rem" class="w-100" type="text" name="tags[]" value="<?= isset($service['tags']) ? $service['tags'] : "" ?>" placeholder="press enter to add tag">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="short_description" class="required"><?= labels('short_description', "Short Description") ?></label>
                                    <textarea rows=4 style="min-height:60px" class='form-control' style="min-height:60px" name="description"><?= isset($service['description']) ? $service['description'] : "" ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card h-100 ">
                    <div class="row pl-3">
                        <div class="col border_bottom_for_cards">
                            <div class="toggleButttonPostition"><?= labels('perform_task', 'Perform Task') ?></div>
                        </div>
                        <div class="col d-flex justify-content-end mr-3 mt-4 border_bottom_for_cards">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="duration" class="required"><?= labels('duration_to_perform_task', 'Duration to Perform Task') ?></label>
                                    <i data-content="  <?= labels('data_content_for_duration_perform_task', 'The duration will be used to figure out how long the service will take and to determine available timeslots when the customer book their services.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text myDivClass" style="height: 42px;">
                                                <span class="mySpanClass"><?= labels('minutes', 'Minutes') ?></span>
                                            </div>
                                        </div>
                                        <input type="number" style="height: 42px;" class="form-control" name="duration" id="duration" min="0" oninput="this.value = Math.abs(this.value)" placeholder="<?= labels('duration_to_perform_task', 'Duration to Perform service') ?>" value="<?= isset($service['duration']) ? $service['duration'] : "" ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="members" class="required"><?= labels('members_required_to_perform_task', 'Members Required to Perform Task') ?></label>
                                    <i data-content=" <?= labels('data_content_for_member_required', 'We\'re just collecting the number of team members who will be doing the service. This helps us show customers how many people will be working on their service.') ?> " class="fa fa-question-circle" data-original-title="" title=""></i>
                                    <input id="members" class="form-control" type="number" name="members" min="0" oninput="this.value = Math.abs(this.value)" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('members_required_to_perform_task', 'Members Required to Perform Task') ?> <?= labels('here', ' Here ') ?>" min="0" value="<?= isset($service['number_of_members_required']) ? $service['number_of_members_required'] : "" ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="max_qty" class="required"><?= labels('max_quantity_allowed_for_services', 'Max Quantity allowed for services') ?></label>
                                    <i data-content=" <?= labels('data_content_for_max_quality_allowed', 'Users can add up to a maximum of X quantity of a specific service when adding services to the cart.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                                    <input id="max_qty" class="form-control" type="number" min="0" oninput="this.value = Math.abs(this.value)" name="max_qty" placeholder="<?= labels('max_quantity_allowed_for_services', 'Max Quantity allowed for services') ?>" value="<?= isset($service['max_quantity_allowed']) ? $service['max_quantity_allowed'] : "" ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="card card h-100 ">
                    <div class="row pl-3">
                        <div class="col border_bottom_for_cards">
                            <div class="toggleButttonPostition"><?= labels('files', 'Files') ?>
                                <i data-content="<?= labels('data_content_for_files', 'You can add images, other images, or any files like brochures or PDFs so users can see more details about the service.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                            </div>
                        </div>
                        <div class="col d-flex justify-content-end mr-3 mt-4 border_bottom_for_cards">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group"> <label for="image" class="required"><?= labels('image', 'Image') ?></label>
                                    <input type="file" name="service_image_selector" class="filepond logo" id="service_image_selector" accept="image/*" onchange="loadServiceImage(event)">
                                    <img alt="no image found" width="130px" style="border: solid 1; border-radius: 12px;" height="100px" class="mt-2" id="image_preview" src="<?= isset($service['image']) ? base_url($service['image']) : "" ?>">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group"> <label for="image" class=""><?= labels('other_images', 'Other Image') ?></label>
                                    <input type="file" name="other_service_image_selector[]" class="filepond logo" id="other_service_image_selector" accept="image/*" multiple>
                                    <?php
                                    if (!empty($service['other_images'])) {
                                        $service['other_images'] = array_map(function ($data) {
                                            return base_url($data);
                                        }, json_decode($service['other_images'], true));
                                    } else {
                                        $service['other_images'] = []; // Return an empty array
                                    }
                                    foreach ($service['other_images'] as $image) { ?>
                                        <img alt="no image found" width="130px" style="border: solid 1; border-radius: 12px;" height="100px" class="mt-2" id="image_preview" src="<?= isset($image) ? ($image) : "" ?>">
                                    <?php }
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="image"><?= labels('files', 'Files') ?></label>
                                    <input type="file" name="files[]" class="filepond-docs logo" id="files" multiple>
                                    <?php
                                    if (!empty($service['files'])) {
                                        $service['files'] = array_map(function ($data) {
                                            return base_url($data);
                                        }, json_decode($service['files'], true));
                                    } else {
                                        $service['files'] = []; // Return an empty array
                                    } ?>
                                    <div class="row ">
                                        <?php
                                        foreach ($service['files'] as $file) { ?>
                                            <div class=" col-md-3 m-2 p-2" style="border-radius: 8px;background-color:#f2f1f6">
                                                <a href="<?= $file ?>">View uploaded File</a>
                                            </div>
                                        <?php }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card h-100 ">
                    <div class="row pl-3">
                        <div class="col border_bottom_for_cards">
                            <div class="toggleButttonPostition"><?= labels('description', 'Description') ?>
                                <i data-content="<?= labels('data_content_for_service_description', 'You can add an extra description so users can see more details about the service.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                            </div>
                        </div>
                        <div class="col d-flex justify-content-end mr-3 mt-4 border_bottom_for_cards">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <label for="Description" class="required"> <?= labels('description', 'Description') ?></label>
                                <textarea rows=10 class='form-control h-50 summernotes custome_reset' name="long_description"><?= isset($service['long_description']) ? $service['long_description'] : '' ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card h-100">
                    <div class="row pl-3">
                        <div class="col border_bottom_for_cards">
                            <div class="toggleButttonPostition"><?= labels('Faqs', 'Faqs') ?>
                                <i data-content=" <?= labels('data_content_for_faqs', 'You can include some general questions and answers to help users understand the service better. This will make it clearer for everyone.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                            </div>
                        </div>
                        <div class="col d-flex justify-content-end mr-3 mt-4 border_bottom_for_cards">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="list_wrapper">
                                    <?php
                                    if (!empty($service['faqs'])) {
                                        $faqsData = json_decode($service['faqs'], true); // Decode the string into an array
                                        if (is_array($faqsData)) {
                                            $faqs = [];
                                            foreach ($faqsData as $pair) {
                                                $faq = [
                                                    'question' => $pair[0],
                                                    'answer' => $pair[1]
                                                ];
                                                $faqs[] = $faq;
                                            }
                                            $service['faqs'] = $faqs;
                                        } else {
                                            // Handle the case when decoding fails
                                            // You can display an error message or set a default value for $data['faqs']
                                        }
                                        foreach ($service['faqs'] as $index => $faq) {
                                    ?>
                                            <div class="row">
                                                <div class="col-xs-4 col-sm-4 col-md-4">
                                                    <div class="form-group">
                                                        <label for="question">Question</label>
                                                        <input name="faqs[<?= $index ?>][]" type="text" placeholder="Enter the question here" class="form-control" value="<?= $faq['question'] ?>" />
                                                    </div>
                                                </div>
                                                <div class="col-xs-7 col-sm-7 col-md-4">
                                                    <div class="form-group">
                                                        <label for="answer">Answer</label>
                                                        <input autocomplete="off" name="faqs[<?= $index ?>][]" type="text" placeholder="Enter the answer here" class="form-control" value="<?= $faq['answer'] ?>" />
                                                    </div>
                                                </div>
                                                <div class="col-xs-1 col-sm-1 col-md-2 mt-4">
                                                    <a href="javascript:void(0);" class="existing_faq_delete_button btn btn-danger">-</a> <!-- Add the FAQ ID to the button using data-faq-id attribute -->
                                                </div>
                                            </div>
                                    <?php
                                        }
                                    }
                                    ?>
                                </div>
                                <div class="row">
                                    <div class="col-xs-1 col-sm-1 col-md-2 mt-4">
                                        <a href="javascript:void(0);" class="list_add_button btn btn-primary">+</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card h-100">
                    <div class="row pl-3">
                        <div class="col border_bottom_for_cards">
                            <div class="toggleButttonPostition"><?= labels('price_details', 'Price Details') ?></div>
                        </div>
                        <div class="col d-flex justify-content-end mr-3 mt-4 border_bottom_for_cards">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="tax_type" class="required"><?= labels('price', 'Price') ?> <?= labels('type', 'Type') ?></label>
                                    <select name="tax_type" id="tax_type" class="form-control">
                                        <option value="excluded" <?php echo  isset($service['tax_type'])  && $service['tax_type'] == "excluded"  ? 'selected' : '' ?>><?= labels('tax_excluded_in_price', 'Tax Excluded In Price') ?></option>
                                        <option value="included" <?php echo  isset($service['tax_type'])  && $service['tax_type'] == "included"  ? 'selected' : '' ?>><?= labels('tax_included_in_price', 'Tax Included In Price') ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="jquery-script-clear"></div>
                                <div class="" id="">
                                    <label for="partner" class="required"><?= labels('select_tax', 'Select Tax') ?></label> <br>
                                    <select id="tax" name="tax_id" required class="form-control w-100" name="tax">
                                        <option value=""><?= labels('select_tax', 'Select Tax') ?></option>
                                        <?php foreach ($tax_data as $pn) : ?>
                                            <option value="<?= $pn['id'] ?>" <?php echo  isset($service['tax_id'])  && $service['tax_id'] ==  $pn['id'] ? 'selected' : '' ?>> <?= $pn['title'] ?>(<?= $pn['percentage'] ?>%)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="price" class="required"><?= labels('price', 'Price') ?></label>
                                    <input id="price" class="form-control" type="number" name="price" placeholder="price" min="1" oninput="this.value = Math.abs(this.value)" value="<?= isset($service['price']) ? $service['price'] : "" ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="discounted_price" class="required"><?= labels('discounted_price', 'Discounted Price') ?></label>
                                    <input id="discounted_price" class="form-control" type="number" name="discounted_price" value="<?= isset($service['discounted_price']) ? $service['discounted_price'] : "" ?>" min="0" oninput="this.value = Math.abs(this.value)" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('discounted_price', 'Discounted Price') ?> <?= labels('here', ' Here ') ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card h-100">
                    <div class="row pl-3">
                        <div class="col border_bottom_for_cards">
                            <div class="toggleButttonPostition"><?= labels('service_option', 'Service Options') ?></div>
                        </div>
                        <div class="col d-flex justify-content-end mr-3 mt-4 border_bottom_for_cards">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="required" for="is_cancelable"><?= labels('is_cancelable_?', 'Is Cancelable ')  ?></label>
                                <i data-content="<?= labels('data_content_for_is_cancellable', 'Can customers cancel their booking if they\'ve already booked this service?') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                                <?php
                                if ($service['is_cancelable'] == "1") { ?>
                                    <input type="checkbox" id="is_cancelable" name="is_cancelable" class="status-switch" checked>
                                <?php   } else { ?>
                                    <input type="checkbox" id="is_cancelable" name="is_cancelable" class="status-switch">
                                <?php  }
                                ?>
                            </div>
                            <div class="col-md-3  <?php if ($cod_setting != 1) echo 'd-none'; ?>">
                                <label class="required"><?= labels('pay_later_allowed', 'Pay Later Allowed') ?></label>
                                <i data-content="<?= labels('data_content_for_paylater_allowed', 'If this option is enabled, customers can book the service and pay after the booking is completed. Generally, this is known as the Cash On Delivery option.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                                <?php
                                if ($service['is_pay_later_allowed'] == "1") { ?>
                                    <input type="checkbox" id="pay_later" name="pay_later" class="status-switch" checked>
                                <?php   } else { ?>
                                    <input type="checkbox" id="pay_later" name="pay_later" class="status-switch">
                                <?php  }
                                ?>
                            </div>
                            <div class="col-md-3" id="service_at_store">
                                <div class="form-group">
                                    <label class="required"><?= labels('at_store', 'At Store') ?></label>
                                    <i data-content=" <?= labels('data_content_for_service_at_store', 'If this feature is enabled, customers can book the service at the provider\'s location. The customer needs to go to the provider\'s location on the chosen date and time.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                                    <input type="checkbox" id="at_store" name="at_store" class="status-switch" <?= $service['at_store'] == "1" ? 'checked' : ''; ?>>
                                </div>
                            </div>
                            <div class="col-md-3" id="service_at_doorstep">
                                <div class="form-group">
                                    <label class="required"><?= labels('at_doorstep', 'At Doorstep') ?></label>
                                    <i data-content="<?= labels('data_content_for_service_at_doorstep', 'If this feature is enabled, customers can book the service at their location. The provider needs to go to the customer’s location on the chosen date and time.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                                    <input type="checkbox" id="at_doorstep" name="at_doorstep" class="status-switch" <?= $service['at_doorstep'] == "1" ? 'checked' : ''; ?>>
                                </div>
                            </div>
                            <div class="col-md-3" id="service_approve_service">
                                <div class="form-group">
                                    <label class="" for="approve_service" class="required"> <?= labels('approve_service', 'Approve Service') ?></label></label>
                                    <input type="hidden" name="approve_service_value" value=' <?= $service['approved_by_admin'] ?>' id="approve_service_value">
                                    <input type="checkbox" id="approve_service" name="approve_service" class="status-switch" <?= $service['approved_by_admin'] == "1" ? 'checked' : ''; ?>>
                                </div>
                            </div>
                        </div>
                        <div class="row" id="edit_cancel">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="cancelable_till" class="required"><?= labels('cancelable_before', 'Cancelable before') ?></label>
                                    <i data-content="<?= labels('data_content_for_cancellable_before', 'If customer can cancel the service, they can cancel their booking X minutes before it starts. For example, if their booking is at 11:00 AM, they can cancel it up to X minutes before 11:00 AM.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text myDivClass" style="height: 42px;">
                                                <span class="mySpanClass"><?= labels('minutes', 'Minutes') ?></span>
                                            </div>
                                        </div>
                                        <input type="number" style="height: 42px;" class="form-control" name="cancelable_till" id="cancelable_till" placeholder="Ex. 30" min="0" value="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md d-flex justify-content-end">
                <button type="submit" class="btn btn-lg bg-new-primary submit_btn"><?= labels('edit_services', "Edit Service") ?></button>
                <!-- <input type="reset" class="btn btn-lg bg-danger text-white ml-2" value="<?= labels('Reset', 'Reset') ?>" class="btn btn-danger btn-block"> -->
                <?= form_close() ?>
            </div>
        </div>
    </section>
</div>
<script>
    $(document).ready(function() {
        let cancle = <?= (isset($service['cancelable_till']) && !empty($service['cancelable_till'])) ? $service['cancelable_till'] : "0" ?>;
        let is_cancelable = <?= $service['is_cancelable'] ?>;
        <?php if ($service['is_cancelable'] == "0") { ?>
            $('#edit_cancel').hide();
        <?php } else { ?>
            $("#edit_cancel").show()
        <?php  }
        ?>
        $("#cancelable_till").val(cancle);
        //for is_cancellable
        <?php
        if ($service['is_cancelable'] == 1) { ?>
            $('#is_cancelable').siblings('.switchery').addClass('active-content').removeClass('deactive-content');
        <?php   } else { ?>
            $('#is_cancelable').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        <?php  }
        ?>
        //for pay later
        <?php
        if ($service['is_pay_later_allowed'] == 1) { ?>
            $('#pay_later').siblings('.switchery').addClass('active-content').removeClass('deactive-content');
        <?php   } else { ?>
            $('#pay_later').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        <?php  }
        ?>
        //for status
        <?php
        if ($service['status'] == 1) { ?>
            $('#status').siblings('.switchery').addClass('active-content').removeClass('deactive-content');
        <?php   } else { ?>
            $('#status').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        <?php  }
        ?>
        //for status
        <?php
        if ($service['status'] == 1) { ?>
            $('#status').siblings('.switchery').addClass('active-content').removeClass('deactive-content');
        <?php   } else { ?>
            $('#status').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        <?php  }
        ?>
        //for at_store
        <?php
        if ($service['at_store'] == 1) { ?>
            $('#at_store').siblings('.switchery').addClass('active-content').removeClass('deactive-content');
        <?php   } else { ?>
            $('#at_store').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        <?php  }
        ?>
        //for doorstep
        <?php
        if ($service['at_doorstep'] == 1) { ?>
            $('#at_doorstep').siblings('.switchery').addClass('active-content').removeClass('deactive-content');
        <?php   } else { ?>
            $('#at_doorstep').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        <?php  }
        ?>
        $('#approve_service').on('change', function() {
            this.value = this.checked ? 1 : 0;
            $('#approve_service_value').val(this.value);
        });

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
        var isCancelable = document.querySelector('#is_cancelable');
        isCancelable.onchange = function() {
            handleSwitchChange(isCancelable);
        };
        var payLater = document.querySelector('#pay_later');
        payLater.onchange = function() {
            handleSwitchChange(payLater);
        };
        var status = document.querySelector('#status');
        status.onchange = function() {
            handleSwitchChange(status);
        };
        var atStore = document.querySelector('#at_store');
        atStore.onchange = function() {
            handleSwitchChange(atStore);
        };
        var atDoorstep = document.querySelector('#at_doorstep');
        atDoorstep.onchange = function() {
            handleSwitchChange(atDoorstep);
        };

        function loadServiceImage(event) {
            var image = document.getElementById('image_preview');
            image.src = URL.createObjectURL(event.target.files[0]);
        };

        function test() {
            var tax = document.getElementById("edit_tax").value;
            document.getElementById("update_service").reset();
            document.getElementById("edit_tax").value = tax;
            document.getElementById('edit_service_image').removeAttribute('src');
        }
        $('#service_image_selector').bind('change', function() {
            var filename = $("#service_image_selector").val();
            if (/^\s*$/.test(filename)) {
                $(".file-upload").removeClass('active');
                $("#noFile").text("No file chosen...");
            } else {
                $(".file-upload").addClass('active');
                $("#noFile").text(filename.replace("C:\\fakepath\\", ""));
            }
        });
        $('#is_cancelable').on('change', function() {
            if (this.checked) {
                $("#edit_cancel").show()
            } else {
                $('#edit_cancel').hide();
            }
        }).change();
    });
</script>
<script>
    $(document).ready(function() {
        <?php
        $faqsData = $service['faqs']; // Assign the array directly without decoding
        $service['faqs'] = is_array($faqsData) ? $faqsData : []; // Set an empty array if the value is not an array
        ?>
        var x = <?= count($service['faqs']) ?>; // Initial field counter
        var list_maxField = 1000000; // Input fields increment limitation
        // Once add button is clicked
        $('.list_add_button').click(function() {
            // Check maximum number of input fields
            if (x < list_maxField) {
                x++; // Increment field counter
                var list_fieldHTML = '<div class="row"><div class="col-xs-4 col-sm-4 col-md-4"><div class="form-group"> <label for="question">Question</label><input name="faqs[' + x + '][]" type="text" placeholder="Enter the question here" class="form-control"/></div></div><div class="col-xs-7 col-sm-7 col-md-4"><div class="form-group">    <label for="question">Answer</label><input name="faqs[' + x + '][]" type="text" placeholder="Enter the answer here" class="form-control"/></div></div><div class="col-xs-1 col-sm-7 col-md-1 mt-4">  <a href="javascript:void(0);" class="list_remove_button btn btn-danger">-</a></div></div>'; // New input field HTML
                $('.list_wrapper').append(list_fieldHTML); // Add field HTML
            }
        });
        // Once remove button is clicked
        $('.list_wrapper').on('click', '.list_remove_button', function() {
            $(this).closest('div.row').remove(); // Remove field HTML
            x--; // Decrement field counter
        });
        // Update existing delete buttons
        $('.list_wrapper').on('click', '.existing_faq_delete_button', function() {
            var faqId = $(this).data('faq-id');
            // Perform an AJAX request to delete the FAQ using the faqId
            // You can use the faqId to send a request to the server to delete the corresponding FAQ record
            $(this).closest('div.row').remove(); // Remove field HTML
        });
    });
</script>
<script>
    $(document).ready(function() {
        // Initial setup based on the selected partner
        updateCheckboxDisplay();
        // Listen for changes in the selected partner
        $("#partner").change(function() {
            updateCheckboxDisplay();
        });

        function updateCheckboxDisplay() {
            var selectedOption = $("#partner option:selected");
            var atStore = parseInt(selectedOption.data("at_store"));
            var atDoorstep = parseInt(selectedOption.data("at_doorstep"));
            // Show/hide the checkboxes based on the selected partner's attributes
            $("#service_at_store").toggle(atStore === 1);
            $("#service_at_doorstep").toggle(atDoorstep === 1);
            // Uncheck the checkboxes if the conditions are not met
            if (atStore !== 1) {
                $("#at_store").prop("checked", false);
            }
            if (atDoorstep !== 1) {
                $("#at_doorstep").prop("checked", false);
            }
        }
    });
    $('#partner').change(function() {
        var selectedOption = $("#partner option:selected");
        var selectedPartnerId = $(this).val();
        var atStore = parseInt(selectedOption.data("at_store"));
        var atDoorstep = parseInt(selectedOption.data("at_doorstep"));
        var need_approval_for_the_service = parseInt(selectedOption.data("need_approval_for_the_service"));
        if (atStore == 0) {
            $("#service_at_store").hide();
        } else {
            $("#service_at_store").show();
        }
        if (atDoorstep == 0) {
            $("#service_at_doorstep").hide();
        } else {
            $("#service_at_doorstep").show();
        }
        if (need_approval_for_the_service == 0) {
            $("#service_approve_service").hide();
            $('#approve_service_value').val(1);
        } else {
            $("#service_approve_service").show();
        }
    });
</script>
<script>
    $(function() {
        $('.fa').popover({
            trigger: "hover"
        });
    })

    $("#service_title").on("input", function() {
        let slug = generateSlug($(this).val());
        $("#service_slug").val(slug);
    });
</script>