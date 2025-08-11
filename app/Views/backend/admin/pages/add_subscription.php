<?php
$check_payment_gateway = get_settings('payment_gateways_settings', true);
$cod_setting =  $check_payment_gateway['cod_setting'];
$payment_gateway_setting =  $check_payment_gateway['payment_gateway_setting'];
?>
<div class="main-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('add_subscription', "Add Subscription") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/subscription') ?>"><i class="fas fa-newspaper text-warning"></i> <?= labels('subscription', 'Subscription') ?></a></div>
                <div class="breadcrumb-item"><?= labels('add_subscription', " Add Subscription") ?></a></div>
            </div>
        </div>
        <?= form_open('/admin/subscription/add_store_subscription', ['method' => "post", 'class' => 'form-submit-event', 'id' => 'add_subscription', 'enctype' => "multipart/form-data"]); ?>
        <div class="container-fluid">
            <div class="alert alert-danger col-md-12">
                <div class="alert-title"><?= labels('note', 'Note') ?>:</div>
                <div class="mt-2">
                    <ul class="pl-3 d-flex flex-column gap-2 instructions-list">
                        <li class="list-unstyled">
                            1.<?= labels('to_create_a_free_subscription', ' To create a free subscription, set both the price and discount price to zero.') ?>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-lg-8 col-md-12 col-sm-12">
                    <div class="card m-0 p-0">
                        <div class="row  m-0 border_bottom_for_cards">
                            <div class="col-auto">
                                <div class="toggleButttonPostition"><?= labels('subscription_information', 'Subscription Information') ?></div>
                            </div>
                            <div class="col d-flex justify-content-end mr-3 mt-4 ">
                                <input type="checkbox" class="status-switch" name="status" id="status">
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="company" class="required"> <?= labels('name', ' Name') ?></label>
                                        <input id="name" class="form-control" type="text" name="name" placeholder="<?= labels('enter', 'Enter ') ?> <?= labels('name', 'the name ') ?> <?= labels('here', ' Here ') ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group m-0 p-0">
                                        <label for="commission" class="required"><?= labels('duration', 'Duration') ?></label>
                                        <div class="radio-buttons">
                                            <label class="radio-inline">
                                                <input type="radio" name="duration_type" value="limited" checked> <?= labels('limited', 'Limited') ?>
                                                <i data-content="<?= labels('data_content_for_subscription_limited_duration', ' The subscription will be valid for X number of days. After that period ends, the provider needs to renew it. For example, if you set it for 15 days, the provider must renew the subscription after 15 days.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="duration_type" value="unlimited"> <?= labels('unlimited', 'Unlimited') ?>
                                                <i data-content="<?= labels('data_content_for_subscription_unlimited_duration', ' The subscription will last for a lifetime.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                                            </label>
                                        </div>
                                    </div>
                                    <div id="duration_fields">
                                        <div class="col-md-12 m-0 p-0">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <div class="input-group-text myDivClass">
                                                        <span class="mySpanClass"><?= labels('days', 'Days') ?></span>
                                                    </div>
                                                </div>
                                                <input id="duration" class="form-control" min="0" oninput="this.value = Math.abs(this.value)" type="number" name="duration" placeholder="<?= labels('enter', 'Enter ') ?> <?= labels('duration', 'the duration in day  ') ?> <?= labels('here', ' Here ') ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="description" class="required"><?= labels('description', ' Description') ?></label>
                                        <textarea rows="5" style="min-height:60px" class="form-control" name="description"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="required"><?= labels('publish', 'Publish') ?></label>
                                        <i data-content="<?= labels('data_content_for_publish', ' If you allow this, the subscription will appear on the provider panel and provider app when they buy any subscription.If you don\'t allow it, only the admin will see it when they manually assign it to the provider from the provider details page in the admin panel.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                                        <input type="checkbox" id="publish" name="publish" class="status-switch">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12 col-md-4">
                    <div class="card h-100 m-0 p-0 ">
                        <div class="row border_bottom_for_cards m-0">
                            <div class="col-auto">
                                <div class="toggleButttonPostition"><?= labels('price_details', 'Price Details') ?></div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="price" class="required"><?= labels('price', 'Price') ?></label>
                                        <input id="price" class="form-control" type="number" name="price" min="0" placeholder="<?= labels('enter', 'Enter ') ?> <?= labels('price', 'the price   ') ?> <?= labels('here', ' Here ') ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="price" class="required"><?= labels('discount_price', 'Discount price') ?></label>
                                        <input id="discount_price" class="form-control" type="number" min="0" name="discount_price" placeholder="<?= labels('enter', 'Enter ') ?> <?= labels('discount_price', 'the Discount price     ') ?> <?= labels('here', ' Here ') ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tax_type" class="required"><?= labels('tax', 'Tax') ?> <?= labels('type', 'Type') ?></label>
                                        <select name="tax_type" id="tax_type" class="form-control">
                                            <option value="included"><?= labels('tax_included_in_price', 'Tax Included In Price') ?></option>
                                            <option value="excluded"><?= labels('tax_excluded_in_price', 'Tax Excluded In Price') ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6" id="percentage_field">
                                    <div class="form-group">
                                        <label for="partner" class="required"><?= labels('select_tax', 'Select Tax') ?></label> <br>
                                        <select id="" name="tax_id" class="form-control w-100">
                                            <option value=""><?= labels('select_tax', 'Select Tax') ?></option>
                                            <?php foreach ($tax_data as $pn) : ?>
                                                <option value="<?= $pn['id'] ?>"><?= $pn['title'] ?>(<?= $pn['percentage'] ?>%)</option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="card">
                        <div class="row m-0 border_bottom_for_cards">
                            <div class="col-auto">
                                <div class="toggleButttonPostition"><?= labels('set_limit', 'Set Limit') ?></div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="Order" class="required"><?= labels('order', ' Order') ?></label>
                                        <div class="radio-buttons">
                                            <label class="radio-inline">
                                                <input type="radio" name="order_type" value="limited"> <?= labels('limited', 'Limited') ?>
                                                <i data-content="<?= labels('data_content_for_order_limited', 'Providers must renew their subscription after they receive X number of orders from customers. If they don\'t renew their subscription, their services won\'t be visible in the Customer app and website.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="order_type" value="unlimited" checked> <?= labels('unlimited', 'Unlimited') ?>
                                                <i data-content="<?= labels('data_content_for_order_unlimited', ' There won\'t be any limit on orders. If a provider has bought this subscription, they can get as many orders as they want until their subscription ends.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                                            </label>
                                        </div>
                                        <div class="col-md-12">
                                            <div id="max_order">
                                                <div class="form-group">
                                                    <label for="cancelable_till"><?= labels('max_order', 'Maximum Order Number ') ?></label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                        </div>
                                                        <input type="number" style="height: 42px;" class="form-control" name="max_order" id="1" placeholder="Ex. 30" min="0" value="">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="commission" class="required"><?= labels('commission', 'Commission') ?></label>
                                        <div class="radio-buttons">
                                            <label class="radio-inline">
                                                <input type="radio" name="commission_type" value="no" checked> <?= labels('no', 'No') ?>
                                                <i data-content="<?= labels('data_content_for_commission_no', 'The provider doesn\'t need to pay any commission for the bookings they receive.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="commission_type" value="yes"> <?= labels('yes', 'Yes') ?>
                                                <i data-content="<?= labels('data_content_for_commission_yes', 'The provider needs to pay a commission for the bookings they receive.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                                            </label>
                                        </div>
                                        <div id="commission_fields">
                                            <div class="row">
                                                <?php
                                                if ($cod_setting == 1) { ?>
                                                    <div class="col-md-6 ">
                                                        <div class="form-group">
                                                            <label for="threshold"><?= labels('threshold', 'Threshold') ?></label>
                                                            <i data-content=" <?= labels('data_content_for_commission_threshold', "Providers will not receive Pay Later (COD) bookings if they've already collected a certain amount of cash (the COD commission payable to the admin) and reached the limit. To receive more bookings, providers must first pay that collected amount to the admin. For example, if the threshold is $500 and the collected cash reaches or exceeds $500, the provider won't receive any more COD bookings until they've paid that amount to the admin.") ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                                                            <div class="input-group">
                                                                <input type="number" min="0" class="form-control" min="0" name="threshold" id="threshold" placeholder="Threshold" min="0" value="">
                                                            </div>
                                                        </div>
                                                    </div>
                                               
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="percentage"><?= labels('percentage', 'Percentage') ?></label>
                                                        <i data-content=" <?= labels('data_content_for_commission_commission', "The provider needs to pay X% of the commission to the admin for each booking they complete. For example, if the commission rate is set at 10% and the provider receives $500 for a booking, they have to give 10% of $500, which equals $50, to the admin as commission.") ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                                                        <div class="input-group">
                                                            <input type="number" min="0" max="100" class="form-control" name="percentage" id="percentage" placeholder="Percentage" min="0" max="100" value="">
                                                        </div>
                                                    </div>
                                                </div>

                                                <?php  }
                                                ?>
                                            </div>
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
                    <button type="submit" id="redirectButton" class="btn btn-lg bg-new-primary submit_btn"><?= labels('add_subscription', " Add Subscription") ?></button>
                    <?= form_close() ?>
                </div>
            </div>
    </section>
</div>
<style>
</style>
<script>
    $(document).ready(function() {
        $("#max_order").hide();
        $("#max_service").hide();
        $("#commission_fields").hide();
        $("#duration_type").hide();
        $('#status').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        var status = document.querySelector('#status');
        status.onchange = function(e) {
            if (status.checked) {
                $(this).siblings('.switchery').addClass('active-content').removeClass('deactive-content');
            } else {
                $(this).siblings('.switchery').addClass('deactive-content').removeClass('active-content');
            }
        };
        $('input[name="order_type"]').change(function() {
            if ($(this).val() === "limited") {
                $("#max_order").show();
            } else {
                $("#max_order").hide();
            }
        });
        $('input[name="service_type"]').change(function() {
            if ($(this).val() === "limited") {
                $("#max_service").show();
            } else {
                $("#max_service").hide();
            }
        });
        $('input[name="commission_type"]').change(function() {
            if ($(this).val() === "yes") {
                $("#commission_fields").show();
            } else {
                $("#commission_fields").hide();
            }
        });
        $('input[name="duration_type"]').change(function() {
            if ($(this).val() === "limited") {
                $("#duration_fields").show();
            } else {
                $("#duration_fields").hide();
            }
        });
        $('#publish').siblings('.switchery').addClass('no-content').removeClass('yes-content');
        var publish = document.querySelector('#publish');
        publish.onchange = function(e) {
            if (publish.checked) {
                $(this).siblings('.switchery').addClass('yes-content').removeClass('no-content');
            } else {
                $(this).siblings('.switchery').addClass('no-content').removeClass('yes-content');
            }
        };
    });
</script>
<script>
    $(function() {
        $('.fa').popover({
            trigger: "hover"
        });
    })
</script>