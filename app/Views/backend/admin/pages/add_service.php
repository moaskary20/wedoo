<?php
$check_payment_gateway = get_settings('payment_gateways_settings', true);
$cod_setting =  $check_payment_gateway['cod_setting'];
?>
<div class="main-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('services', "Services") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/services') ?>"><i class="	fas fa-tools text-warning"></i> <?= labels('service', 'Service') ?></a></div>
                <div class="breadcrumb-item"><?= labels('add_services', 'Add Service') ?></a></div>
            </div>
        </div>
        <?= form_open('/admin/services/insert_service', ['method' => "post", 'class' => 'normal-form-submit', 'enctype' => "multipart/form-data"]); ?>
        <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
        
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="row  border_bottom_for_cards m-0">
                        <div class="col-auto">
                            <div class="toggleButttonPostition"><?= labels('add_service_details', 'Add Service Details') ?></div>
                        </div>
                        <div class="col d-flex justify-content-end mr-3 mt-4 ">
                            <input type="checkbox" id="status" name="status" class="status-switch" checked>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <div class="jquery-script-clear"></div>
                                <div class="categories form-group" id="categories">
                                    <label for="partner" class="required"><?= labels('select_provider', 'Select Provider') ?></label> <br>
                                    <select id="partner" class="form-control w-100 select2" name="partner">
                                        <option value=""><?= labels('select_provider', 'Select Provider') ?></option>
                                        <?php foreach ($partner_name as $pn) : ?>
                                            <option value="<?= $pn['id'] ?>" data-members="<?= $pn['number_of_members'] ?>" data-at_store="<?= $pn['at_store'] ?>" data-at_doorstep="<?= $pn['at_doorstep'] ?>" data-need_approval_for_the_service="<?= $pn['need_approval_for_the_service'] ?>">
                                                <?= $pn['company_name'] . ' - ' . $pn['username'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="categories form-group" id="categories">
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

                                                    // Recursive call with the full category list
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
                                    <input class="form-control" type="text" name="title" id="service_title">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="service_slug" class="required"><?= labels('slug', 'Slug') ?></label>
                                    <input id="service_slug" class="form-control" type="text" name="service_slug" placeholder="<?= labels('enter_the_slug', 'Enter the slug') ?> ">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="tags" class="required"><?= labels('tags', 'Tags') ?></label>
                                    <i data-content=" <?= labels('data_content_for_tags', 'These tags will help find the services while users search for the services.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                                    <input id="tags" style="border-radius: 0.25rem" class="w-100" type="text" name="tags[]" placeholder="<?= labels('press_enter_to_add_tag', 'press enter to add tag') ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description" class="required"><?= labels('short_description', "Short Description") ?></label>
                                    <textarea rows=4 class='form-control' style="min-height:60px" name="description"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="row  m-0">
                        <div class="col mb-3 " style="border-bottom: solid 1px #e5e6e9;">
                            <div class="toggleButttonPostition"><?= labels('perform_task', 'Perform Task') ?></div>
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
                                        <input type="number" style="height: 42px;" class="form-control" name="duration" id="duration" min="0" oninput="this.value = Math.abs(this.value)" placeholder="<?= labels('duration_to_perform_task', 'Duration to Perform service') ?>" value="">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="members" class="required"><?= labels('members_required_to_perform_task', 'Members Required to Perform Task') ?></label>
                                    <i data-content=" <?= labels('data_content_for_member_required', 'We\'re just collecting the number of team members who will be doing the service. This helps us show customers how many people will be working on their service.') ?> " class="fa fa-question-circle" data-original-title="" title=""></i>
                                    <input id="members" class="form-control" type="number" name="members" min="0" oninput="this.value = Math.abs(this.value)" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('members_required_to_perform_task', 'Members Required to Perform Task') ?> <?= labels('here', ' Here ') ?>" min="0">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="max_qty" class="required"><?= labels('max_quantity_allowed_for_services', 'Max Quantity allowed for services') ?></label>
                                    <i data-content=" <?= labels('data_content_for_max_quality_allowed', 'Users can add up to a maximum of X quantity of a specific service when adding services to the cart.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                                    <input id="max_qty" class="form-control" type="number" min="0" oninput="this.value = Math.abs(this.value)" name="max_qty" placeholder="<?= labels('max_quantity_allowed_for_services', 'Max Quantity allowed for services') ?>">
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
                    <div class="row m-0">
                        <div class="col mb-3 " style="border-bottom: solid 1px #e5e6e9;">
                            <div class="toggleButttonPostition"><?= labels('files', 'Files') ?>
                                <i data-content="<?= labels('data_content_for_files', 'You can add images, other images, or any files like brochures or PDFs so users can see more details about the service.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group"> <label for="service_image_selector" class="required"><?= labels('image', 'Image') ?></label>
                                    <input type="file" name="service_image_selector" class="filepond logo" id="service_image_selector" accept="image/*">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group"> <label for="other_service_image_selector" class=""><?= labels('other_images', 'Other Image') ?></label>
                                    <input type="file" name="other_service_image_selector[]" class="filepond logo" id="other_service_image_selector" accept="image/*" multiple>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group"> <label for="files"><?= labels('files', 'Files') ?></label>
                                    <input type="file" name="files[]" class="filepond-docs" id="files" multiple>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card h-100 ">
                    <div class="row m-0">
                        <div class="col mb-3 " style="border-bottom: solid 1px #e5e6e9;">
                            <div class="toggleButttonPostition"><?= labels('description', 'Description') ?>
                                <i data-content="<?= labels('data_content_for_service_description', 'You can add an extra description so users can see more details about the service.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <label for="long_description" class="required"><?= labels('description', 'Description') ?></label>
                                <textarea rows=10 class='form-control h-50 summernotes custome_reset' name="long_description"><?= isset($short_description) ? $short_description : '' ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card h-100">
                    <div class="row m-0">
                        <div class="col mb-3 " style="border-bottom: solid 1px #e5e6e9;">
                            <div class="toggleButttonPostition"><?= labels('price_details', 'Price Details') ?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="tax_type" class="required"><?= labels('price', 'Price') ?> <?= labels('type', 'Type') ?></label>
                                    <select name="tax_type" id="tax_type" required class="form-control">
                                        <option value="excluded"><?= labels('tax_excluded_in_price', 'Tax Excluded In Price') ?></option>
                                        <option value="included"><?= labels('tax_included_in_price', 'Tax Included In Price') ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="jquery-script-clear"></div>
                                <div class="" id="">
                                    <label for="tax_id" class="required"><?= labels('select_tax', 'Select Tax') ?></label> <br>
                                    <select id="tax" name="tax_id" required class="form-control w-100" name="tax">
                                        <option value=""><?= labels('select_tax', 'Select Tax') ?></option>
                                        <?php foreach ($tax_data as $pn) : ?>
                                            <option value="<?= $pn['id'] ?>"><?= $pn['title'] ?>(<?= $pn['percentage'] ?>%)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="price" class="required"><?= labels('price', 'Price') ?></label>
                                    <input id="price" class="form-control" type="number" name="price" placeholder="<?= labels('price', 'Price') ?>" min="1" oninput="this.value = Math.abs(this.value)">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="discounted_price" class="required"><?= labels('discounted_price', 'Discounted Price') ?></label>
                                    <input id="discounted_price" class="form-control" type="number" name="discounted_price" min="0" oninput="this.value = Math.abs(this.value)" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('discounted_price', 'Discounted Price') ?> <?= labels('here', ' Here ') ?>">
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
                    <div class="row m-0">
                        <div class="col mb-3 " style="border-bottom: solid 1px #e5e6e9;">
                            <div class="toggleButttonPostition"><?= labels('faqs', 'Faqs') ?>
                                <i data-content=" <?= labels('data_content_for_faqs', 'You can include some general questions and answers to help users understand the service better. This will make it clearer for everyone.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="list_wrapper">
                                    <div class="row">
                                        <div class="col-xs-4 col-sm-4 col-md-4">
                                            <div class="form-group">
                                                <label for="question"><?= labels('question', "Quetion") ?></label>
                                                <input name="faqs[0][]" type="text" placeholder="<?= labels('enter_question', 'Enter the question here') ?>" class="form-control" />
                                            </div>
                                        </div>
                                        <div class="col-xs-7 col-sm-7 col-md-4">
                                            <div class="form-group">
                                                <label for="answer"><?= labels('answer', "Answer") ?></label>
                                                <input name="faqs[0][]" type="text" placeholder="<?= labels('enter_answer', 'Enter the answer here') ?>" class="form-control" />
                                            </div>
                                        </div>
                                        <div class="col-xs-1 col-sm-1 col-md-2 mt-4">
                                            <button class="btn btn-primary list_add_button" type="button">+</button>
                                        </div>
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
                    <div class="row m-0">
                        <div class="col mb-3 " style="border-bottom: solid 1px #e5e6e9;">
                            <div class="toggleButttonPostition"><?= labels('service_option', 'Service Options') ?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="" for="is_cancelable" class="required"><?= labels('is_cancelable_?', 'Is Cancelable ')  ?></label>
                                    <i data-content="<?= labels('data_content_for_is_cancellable', 'Can customers cancel their booking if they\'ve already booked this service?') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                                    <input type="checkbox" id="is_cancelable" name="is_cancelable" class="status-switch">
                                </div>
                            </div>
                            <div class="col-md-3 <?php if ($cod_setting != 1) echo 'd-none'; ?>">
                                <div class="form-group">
                                    <label class="" for="pay_later" class="required"><?= labels('pay_later_allowed', 'Pay Later Allowed') ?></label>
                                    <i data-content="<?= labels('data_content_for_paylater_allowed', 'If this option is enabled, customers can book the service and pay after the booking is completed. Generally, this is known as the Cash On Delivery option.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                                    <input type="checkbox" id="pay_later" name="pay_later" class="status-switch">
                                </div>
                            </div>
                            <div class="col-md-3" id="service_at_store">
                                <div class="form-group">
                                    <label class="" for="at_store" class="required"><?= labels('at_store', 'At Store') ?></label>
                                    <i data-content=" <?= labels('data_content_for_service_at_store', 'If this feature is enabled, customers can book the service at the provider\'s location. The customer needs to go to the provider\'s location on the chosen date and time.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                                    <input type="checkbox" id="at_store" name="at_store" class="status-switch">
                                </div>
                            </div>
                            <div class="col-md-3" id="service_at_doorstep">
                                <div class="form-group"><?= labels('at_doorstep', 'At Doorstep') ?></label>
                                    <i data-content="<?= labels('data_content_for_service_at_doorstep', 'If this feature is enabled, customers can book the service at their location. The provider needs to go to the customer’s location on the chosen date and time.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                                    <input type="checkbox" id="at_doorstep" name="at_doorstep" class="status-switch">
                                </div>
                            </div>
                            <div class="col-md-3" id="service_approve_service">
                                <div class="form-group">
                                    <label class="" for="approve_service" class="required"> <?= labels('approve_service', 'Approve Service') ?></label></label>
                                    <input type="hidden" name="approve_service_value" value='0' id="approve_service_value">
                                    <input type="checkbox" id="approve_service" name="approve_service" class="status-switch">
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
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md d-flex justify-content-end">
                <button type="submit" class="btn btn-lg btn-primary" name="submit_form" value="1"><?= labels('add_services', 'Add Service') ?></button>
                <?= form_close() ?>
            </div>
        </div>
</div>
</section>
</div>
<script>
    $(document).ready(function() {
        $('#is_cancelable').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        $('#pay_later').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        $('#status').siblings('.switchery').addClass('active-content').removeClass('deactive-content');
        $('#at_store').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        $('#at_doorstep').siblings('.switchery').addClass('deactive-content').removeClass('active-content');

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
        isCancelable.addEventListener('change', function() {
            handleSwitchChange(isCancelable);
        });
        var payLater = document.querySelector('#pay_later');
        payLater.addEventListener('change', function() {
            handleSwitchChange(payLater);
        });
        var status = document.querySelector('#status');
        status.addEventListener('change', function() {
            handleSwitchChange(status);
        });
        var atStore = document.querySelector('#at_store');
        atStore.addEventListener('change', function() {
            handleSwitchChange(atStore);
        });
        var atDoorstep = document.querySelector('#at_doorstep');
        atDoorstep.addEventListener('change', function() {
            handleSwitchChange(atDoorstep);
        });
    });

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
</script>
<script>
    $(document).ready(function() {
        var x = 0;
        var list_maxField = 10000000000;
        $('.list_add_button').click(function() {
            if (x < list_maxField) {
                x++;
                var list_fieldHTML = '<div class="row"><div class="col-xs-4 col-sm-4 col-md-4"><div class="form-group"> <label for="question"><?= labels('question', "Quetion") ?></label><input name="faqs[' + x + '][]" type="text" placeholder="<?= labels('enter_question', 'Enter the question here') ?>" class="form-control"/></div></div><div class="col-xs-7 col-sm-7 col-md-4"><div class="form-group">    <label for="question"><?= labels('answer', "Answer") ?></label><input name="faqs[' + x + '][]" type="text" placeholder="<?= labels('enter_answer', 'Enter the answer here') ?>" class="form-control"/></div></div><div class="col-xs-1 col-sm-7 col-md-1 mt-4"><a href="javascript:void(0);" class="list_remove_button btn btn-danger">-</a></div></div>'; //New input field html 
                $('.list_wrapper').append(list_fieldHTML);
            }
        });
        $('.list_wrapper').on('click', '.list_remove_button', function() {
            $(this).closest('div.row').remove();
            x--;
        });
    });
</script>
<script>
    var partnerSelect = document.getElementById("partner");
    var membersInput = document.getElementById("members");
    partnerSelect.addEventListener("change", function() {
        var selectedOption = partnerSelect.options[partnerSelect.selectedIndex];
        var numberOfMembers = parseInt(selectedOption.getAttribute("data-members"), 10);
        membersInput.value = numberOfMembers;
        if (numberOfMembers === 1) {
            membersInput.readOnly = true;
        } else {
            membersInput.readOnly = false;
        }
        var at_store = parseInt(selectedOption.getAttribute("data-at_store"));
        var at_doorstep = parseInt(selectedOption.getAttribute("data-at_doorstep"));
        $("#service_at_store").toggle(at_store === 1);
        $("#service_at_doorstep").toggle(at_doorstep === 1);
    });
    $('#approve_service').on('change', function() {
        this.value = this.checked ? 1 : 0;
        $('#approve_service_value').val(this.value);
    });
    $('#partner').change(function() {
        // var partnerSelect = document.getElementById("partner");
        // var selectedOption = partnerSelect.options[partnerSelect.selectedIndex];

        var selectedOption = $("#partner option:selected");
        var selectedPartnerId = $(this).val();


        // // var selectedPartnerId = $(this).val();
        // var at_store = parseInt(selectedOption.getAttribute("data-at_store"));
        // var at_doorstep = parseInt(selectedOption.getAttribute("data-at_doorstep"));
        // var need_approval_for_the_service = parseInt(selectedOption.getAttribute("data-need_approval_for_the_service"));
        var atStore = parseInt(selectedOption.data("at_store"));
        var atDoorstep = parseInt(selectedOption.data("at_doorstep"));
        var need_approval_for_the_service = parseInt(selectedOption.data("need_approval_for_the_service"));

        if (at_store == 0) {
            $("#service_at_store").hide();
        } else {
            $("#service_at_store").show();
        }
        if (at_doorstep == 0) {
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
    
    // COMPLETELY DISABLE ALL AJAX FORM HANDLERS
    $(document).ready(function() {
        // Override jQuery's AJAX submit completely
        $.fn.submit = function() {
            return this.each(function() {
                if (this.submit) {
                    this.submit();
                }
            });
        };
        
        // Remove ALL event handlers from document that handle form submission
        $(document).off('submit');
        
        // Disable preventDefault on forms
        $('form').off('submit').on('submit', function(e) {
            // Allow only normal HTML submission
            return true;
        });
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