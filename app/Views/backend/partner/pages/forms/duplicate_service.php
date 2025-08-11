<?= helper('form'); ?>
<?php
$db      = \Config\Database::connect();
$builder = $db->table('users u');
$builder->select('u.*,ug.group_id')
    ->join('users_groups ug', 'ug.user_id = u.id')
    ->where('ug.group_id', 3)
    ->where(['phone' => $_SESSION['identity']]);
$user1 = $builder->get()->getResultArray();
$partner = fetch_details('partner_details', ["partner_id" => $user1[0]['id']],);
$at_store = ($partner[0]['at_store']);
$at_doorstep = ($partner[0]['at_doorstep']);
?>
<div class="main-content">
    <div class="section">
        <div class="section-header mt-2">
            <h1><?= labels('edit_service', 'Edit Service') ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('partner/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item"><a href="<?= base_url('/partner/services') ?>"><?= labels('service', "Service") ?></a></div>
            </div>
        </div>
        <?= form_open('/partner/services/add_service', ['method' => "post", 'class' => 'form-submit-event', 'id' => 'add_service', 'enctype' => "multipart/form-data"]); ?>
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="row pl-3">
                        <div class="col border_bottom_for_cards">
                            <div class="toggleButttonPostition"><?= labels('service', 'Service Details') ?></div>
                        </div>
                        <div class="col d-flex justify-content-end mr-3 mt-4 border_bottom_for_cards">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title" class="required"><?= labels('title_of_the_service', 'Title of the service') ?> </label>
                                    <input class="form-control" type="text" name="title" value="<?= isset($service['title']) ? $service['title'] : "" ?>">
                                </div>
                            </div>
                          

                            <div class="col-md-6 form-group">
                                <div class="categories" id="categories">
                                    <label for="category_item" class="required"><?= labels('choose_a_category_for_your_service', 'Choose a Category for your service') ?></label>
                                    <select id="category_item" class="form-control select2" name="categories" style="margin-bottom: 20px;">
                                        <option value=""><?= labels('select', 'Select') ?> <?= labels('category', 'Category') ?></option>
                                        <?php
                                        function renderCategories($categories, $parent_id = 0, $depth = 0, $selected_id = null)
                                        {
                                            $html = '';
                                            foreach ($categories as $category) {
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
                                                    $html .= renderCategories($categories, $category['id'], $depth + 1, $selected_id);
                                                }
                                            }
                                            return $html;
                                        }

                                        $selected_category_id = isset($service['category_id']) ? $service['category_id'] : null;
                                        echo renderCategories($categories, 0, 0, $selected_category_id);
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="tags" class="required"><?= labels('tags', 'Tags') ?></label>
                                    <i data-content=" <?= labels('data_content_for_tags', 'These tags will help find the services while users search for the services.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                                    <input id="service_tags" class="" type="text" name="tags[]" value="<?= isset($service['tags']) ? $service['tags'] : "" ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="Description" class="required"><?= labels('short_description', 'Short Description') ?></label>
                                    <textarea rows=4 style="min-height:60px" class='form-control' name="description"><?= isset($service['description']) ? $service['description'] : "" ?></textarea>
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
                                        <input type="number" style="height: 42px;" class="form-control" name="duration" id="duration" value="<?= isset($service['duration']) ? $service['duration'] : "" ?>" placeholder="Duration of the Service" min="0" oninput="this.value = Math.abs(this.value)">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="members" class="required"><?= labels('members_required_to_perform_task', 'Members required to perform Tasks') ?></label>
                                    <i data-content=" <?= labels('data_content_for_member_required', 'We\'re just collecting the number of team members who will be doing the service. This helps us show customers how many people will be working on their service.') ?> " class="fa fa-question-circle" data-original-title="" title=""></i>
                                    <input id="members" class="form-control" type="number" name="members" placeholder="Members Required" min="0" oninput="this.value = Math.abs(this.value)" value="<?= isset($service['number_of_members_required']) ? $service['number_of_members_required'] : "" ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="max_qty" class="required"><?= labels('max_quantity_allowed', 'Max Quantity allowed for services') ?></label>
                                    <i data-content=" <?= labels('data_content_for_max_quality_allowed', 'Users can add up to a maximum of X quantity of a specific service when adding services to the cart.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                                    <input id="max_qty" class="form-control" type="number" name="max_qty" placeholder="Max Quantity allowed for services" min="0" oninput="this.value = Math.abs(this.value)" value="<?= isset($service['max_quantity_allowed']) ? $service['max_quantity_allowed'] : "" ?>">
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
                                <div class="form-group"> <label for="image" class="required"> <?= labels('image', 'Image') ?></label>
                                    <input type="file" name="image" class="filepond logo" id="service_image_selector" accept="image/*" onchange="loadServiceImage(event)">
                                    <img alt="no image found" width="130px" style="border: solid 1; border-radius: 12px;" height="100px" class="mt-2" id="image_preview" src="<?= isset($service['image']) ? base_url($service['image']) : "" ?>">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group"> <label for="image"><?= labels('other_images', 'Other Image') ?></label>
                                    <input type="file" name="other_service_image_selector[]" class="filepond logo" id="other_service_image_selector" accept="image/*" multiple>
                                    <?php
                                    if (!empty($service['other_images'])) {
                                        $service['other_images'] = array_map(function ($data) {
                                            return base_url($data);
                                        }, json_decode($service['other_images'], true));
                                    } else {
                                        $service['other_images'] = []; 
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
                                        $service['files'] = [];
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
                                <label for="Description" class="required"><?= labels('description', 'Description') ?></label>
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
                            <div class="toggleButttonPostition"><?= labels('FAQs', 'Faqs') ?>
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
                                        $faqsData = json_decode($service['faqs'], true); 
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
                                                    <a href="javascript:void(0);" class="existing_faq_delete_button btn btn-danger">-</a> 
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
                                    <label for="tax_type" class="required"><?= labels('tax_type', 'Tax Type') ?></label>
                                    <select name="tax_type" id="tax_type" class="form-control">
                                        <option value="excluded" <?php echo  isset($service['tax_type'])  && $service['tax_type'] == "excluded"  ? 'selected' : '' ?>><?= labels('tax_excluded_in_price', 'Tax Excluded In Price') ?></option>
                                        <option value="included" <?php echo  isset($service['tax_type'])  && $service['tax_type'] == "included"  ? 'selected' : '' ?>> <?= labels('tax_included_in_price', 'Tax Included In Price') ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="jquery-script-clear"></div>
                                <div class="" id="">
                                    <label for="partner" class="required"><?= labels('select_tax', 'Select Tax') ?></label> <br>
                                    <select id="tax" name="tax_id" class="form-control w-100" name="tax">
                                        <option value=""><?= labels('select_tax', 'Select Tax') ?></option>
                                        <?php foreach ($tax_data as $pn) : ?>
                                            <option value="<?= $pn['id'] ?>" <?php echo  isset($service['tax_id'])  && $service['tax_id'] ==  $pn['id'] ? 'selected' : '' ?>><?= $pn['title'] ?>(<?= $pn['percentage'] ?>%)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="price" class="required"><?= labels('price', 'Price') ?></label>
                                    <input id="price" class="form-control" type="number" name="price" placeholder="price" value="<?= isset($service['price']) ? $service['price'] : "" ?>" min="0" oninput="this.value = Math.abs(this.value)">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="discounted_price" class="required"><?= labels('discounted_price', 'Discounted Price') ?></label>
                                    <input id="discounted_price" class="form-control" type="number" name="discounted_price" value="<?= isset($service['discounted_price']) ? $service['discounted_price'] : "" ?>" placeholder="Discounted Price" min="0" oninput="this.value = Math.abs(this.value)">
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
                            <div class="toggleButttonPostition"><?= labels('service_options', 'Service Options') ?></div>
                        </div>
                        <div class="col d-flex justify-content-end mr-3 mt-4 border_bottom_for_cards">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="" for="is_cancelable" class="required"><?= labels('is_cancelable_?', 'Is Cancelable ')  ?></label>
                                <i data-content="<?= labels('data_content_for_is_cancellable', 'Can customers cancel their booking if they\'ve already booked this service?') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                                <?php
                                if ($service['is_cancelable'] == "1") { ?>
                                    <input type="checkbox" id="is_cancelable" name="is_cancelable" class="status-switch" checked>
                                <?php   } else { ?>
                                    <input type="checkbox" id="is_cancelable" name="is_cancelable" class="status-switch">
                                <?php  }
                                ?>
                            </div>
                            <div class="col-md-4">
                                <label class="" for="pay_later" class="required"><?= labels('pay_later_allowed', 'Pay Later Allowed') ?></label>
                                <i data-content="<?= labels('data_content_for_paylater_allowed', 'If this option is enabled, customers can book the service and pay after the booking is completed. Generally, this is known as the Cash On Delivery option.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                                <?php
                                if ($service['is_pay_later_allowed'] == "1") { ?>
                                    <input type="checkbox" id="pay_later" name="pay_later" class="status-switch" checked>
                                <?php   } else { ?>
                                    <input type="checkbox" id="pay_later" name="pay_later" class="status-switch">
                                <?php  }
                                ?>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="required">Status</span></label>
                                    <?php
                                    if ($service['status'] == "1") { ?>
                                        <input type="checkbox" id="status" name="status" class="status-switch" checked>
                                    <?php   } else { ?> <input type="checkbox" id="status" name="status" class="status-switch">
                                    <?php  }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="row" id="cancel_order">
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
                        <div class="row">
                            <?php
                            if (isset($service['at_store']) && $service['at_store'] == 1) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="" for="at_store"><?= labels('at_store', 'At Store') ?></label>
                                        <input type="checkbox" id="at_store" name="at_store" class="status-switch" checked>
                                    </div>
                                </div>
                            <?php } else {  ?>
                                <label class="" for="at_store"><?= labels('at_store', 'At Store') ?></label>
                                <input type="checkbox" id="at_store" name="at_store" class="status-switch">
                            <?php } ?>
                            <?php
                            if (isset($service['at_doorstep']) && $service['at_doorstep'] == 1) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="" for="at_doorstep"><?= labels('at_doorstep', 'At Doorstep') ?></label>
                                        <input type="checkbox" id="at_doorstep" name="at_doorstep" class="status-switch" checked>
                                    </div>
                                </div>
                            <?php } else {  ?>
                                <label class="" for="at_doorstep"><?= labels('at_doorstep', 'At Doorstep') ?></label>
                                <input type="checkbox" id="at_doorstep" name="at_doorstep" class="status-switch">
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md d-flex justify-content-end">
                <button type="submit" class="btn btn-lg bg-new-primary"><?= labels('edit_service', 'Edit Service') ?></button>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#at_store').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        $('#at_doorstep').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        <?php
        if ($service['is_cancelable'] == 1) { ?>
            $('#is_cancelable').siblings('.switchery').addClass('active-content').removeClass('deactive-content');
        <?php   } else { ?>
            $('#is_cancelable').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        <?php  }
        ?>
        <?php
        if ($service['is_pay_later_allowed'] == 1) { ?>
            $('#pay_later').siblings('.switchery').addClass('active-content').removeClass('deactive-content');
        <?php   } else { ?>
            $('#pay_later').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        <?php  }
        ?>
        <?php
        if ($service['status'] == 1) { ?>
            $('#status').siblings('.switchery').addClass('active-content').removeClass('deactive-content');
        <?php   } else { ?>
            $('#status').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        <?php  }
        ?>
        <?php
        if ($service['at_store'] == 1) { ?>
        
            $('#at_store').siblings('.switchery').addClass('active-content').removeClass('deactive-content');
        <?php   } else { ?>
            
            $('#at_store').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        <?php  }
        ?>
        <?php
        if ($service['at_doorstep'] == 1) { ?>
            $('#at_doorstep').siblings('.switchery').addClass('active-content').removeClass('deactive-content');
        <?php   } else { ?>
            $('#at_doorstep').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        <?php  }
        ?>
        var is_cancelable = document.querySelector('#is_cancelable');
        is_cancelable.onchange = function(e) {
            if (is_cancelable.checked) {
                $(this).siblings('.switchery').addClass('active-content').removeClass('deactive-content');
            } else {
                $(this).siblings('.switchery').addClass('deactive-content').removeClass('active-content');
            }
        };
        var pay_later = document.querySelector('#pay_later');
        pay_later.onchange = function(e) {
            if (pay_later.checked) {
                $(this).siblings('.switchery').addClass('active-content').removeClass('deactive-content');
            } else {
                $(this).siblings('.switchery').addClass('deactive-content').removeClass('active-content');
            }
        };
        var status = document.querySelector('#status');
        status.onchange = function(e) {
            if (status.checked) {
                $(this).siblings('.switchery').addClass('active-content').removeClass('deactive-content');
            } else {
                $(this).siblings('.switchery').addClass('deactive-content').removeClass('active-content');
            }
        };
        if (<?= $service['is_cancelable'] ?> == "1") {
            $("#edit_cancel").show()
            $('#cancelable_till').val(<?= $service['cancelable_till'] ?>);
        } else {
            $("#edit_cancel").hide();
            $('#cancelable_till').val('');
        }
    });
    var atStore = document.querySelector('#at_store');
    atStore.onchange = function(e) {
        if (atStore.checked) {
            $(this).siblings('.switchery').addClass('active-content').removeClass('deactive-content');
        } else {
            $(this).siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        }
    };
    var atDoorstep = document.querySelector('#at_doorstep');
    atDoorstep.onchange = function(e) {
        if (atDoorstep.checked) {
            $(this).siblings('.switchery').addClass('active-content').removeClass('deactive-content');
        } else {
            $(this).siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        }
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
            $("#cancel_order").show()
        } else {
            $('#cancel_order').hide();
        }
    }).change();
</script>
<script>
    $(document).ready(function() {
        <?php
        $faqsData = $service['faqs']; 
        $service['faqs'] = is_array($faqsData) ? $faqsData : []; 
        ?>
        var x = <?= count($service['faqs']) ?>;
        var list_maxField = 10000000; 
        $('.list_add_button').click(function() {
            if (x < list_maxField) {
                x++; 
                var list_fieldHTML = '<div class="row"><div class="col-xs-4 col-sm-4 col-md-4"><div class="form-group"> <label for="question">Question</label><input name="faqs[' + x + '][]" type="text" placeholder="Enter the question here" class="form-control"/></div></div><div class="col-xs-7 col-sm-7 col-md-4"><div class="form-group">    <label for="question">Answer</label><input name="faqs[' + x + '][]" type="text" placeholder="Enter the answer here" class="form-control"/></div></div><div class="col-xs-1 col-sm-7 col-md-1 mt-4">  <a href="javascript:void(0);" class="list_remove_button btn btn-danger">-</a></div></div>'; // New input field HTML
                $('.list_wrapper').append(list_fieldHTML);
            }
        });
        $('.list_wrapper').on('click', '.list_remove_button', function() {
            $(this).closest('div.row').remove(); 
            x--; 
        });
        $('.list_wrapper').on('click', '.existing_faq_delete_button', function() {
            var faqId = $(this).data('faq-id');
            $(this).closest('div.row').remove(); 
        });
    });
</script>
<script>
    $(function() {
        $('.fa').popover({
            trigger: "hover"
        });
    })
</script>