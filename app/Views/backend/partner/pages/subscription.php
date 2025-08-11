<?php
$check_payment_gateway = get_settings('payment_gateways_settings', true);
$cod_setting =  $check_payment_gateway['cod_setting'];
$payment_gateway_setting =  $check_payment_gateway['payment_gateway_setting'];
?>
<div class="main-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('subscription', " Subscription") ?><span class="breadcrumb-item p-3 pt-2 text-primary"></span></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('partner/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item"></i> <?= labels('subscription', 'Subscription') ?></div>
            </div>
        </div>
        <?= helper('form'); ?>
        <div class="section-body">
            <?php if (session()->has('error')) : ?>
                <script>
                    $(document).ready(function() {
                        iziToast.error({
                            title: "Error",
                            message: "<?= session('error') ?>",
                            position: "topRight",
                        });
                    });
                </script>
            <?php endif; ?>
            <?php if (session()->has('success')) : ?>
                <script>
                    $(document).ready(function() {
                        iziToast.success({
                            title: "Success",
                            message: "<?= session('success') ?>",
                            position: "topRight",
                        });
                    });
                </script>
            <?php endif; ?>
            <?php
            if (!empty($active_subscription_details)) { ?>
                <div class="tickets-container">
                    <div class="col-md-12 m-0 p-0">
                        <div class="item">
                            <div class="item-right">
                                <button class="buy-button my-2"> <?= $active_subscription_details[0]['name'] ?></button>
                                <div class="buy">
                                    <span class="up-border"></span>
                                    <span class="down-border"></span>
                                </div>
                                <?php
                                $price = calculate_partner_subscription_price($active_subscription_details[0]['partner_id'], $active_subscription_details[0]['subscription_id'], $active_subscription_details[0]['id']);
                                ?>
                                <h4 class="active_subscription_plan_price"><?= $currency ?> <?= $price[0]['price_with_tax'] ?></h4>
                                <?php
                                if ($active_subscription_details[0]['expiry_date'] != $active_subscription_details[0]['purchase_date']) { ?>
                                    <div class="active_subscription_plan_expiry_date mt-5">
                                        <div class="form-group m-0 p-0">
                                            <?php
                                            echo labels('yourSubscriptionWillBeValidFor', "Your subscription will be valid for " . $active_subscription_details[0]['expiry_date']);
                                            ?>
                                        </div>
                                    </div>
                                <?php  } else { ?>
                                    <div class="active_subscription_plan_expiry_date mt-5">
                                        <div class="form-group m-0 p-0">
                                            <?php echo labels('enjoySubscriptionForUnlimitedDays', "Lifetime Subscription – seize success without limits!") ?>;
                                        </div>
                                    </div>
                                <?php      } ?>
                            </div>
                            <div class="item-left w-100">
                                <div class="row">
                                    <div class="col-md-10">
                                        <div class="active_plan_title "><?= labels('features', 'Features') ?></div>
                                    </div>
                                    <div class="col-md-2 text-right" style="white-space:nowrap;">
                                        <div class="tag border-0 rounded-md bg-emerald-grey ">
                                            <?php
                                            if ($active_subscription_details[0]['is_payment'] == 1) {
                                                $status = "Success";
                                            } elseif ($active_subscription_details[0]['is_payment'] == 0) {
                                                $status = "Pending";
                                            } else {
                                                $status = "Failed";
                                            }
                                            ?>
                                            <?= $status ?>
                                        </div>
                                    </div>
                                </div>
                                <ul class="active_subscription_feature_list mb-3 mt-3" style="margin:28px">
                                    <li>
                                        <span class="icon">
                                            <svg height="24" width="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M0 0h24v24H0z" fill="none"></path>
                                                <path fill="currentColor" d="M10 15.172l9.192-9.193 1.415 1.414L10 18l-6.364-6.364 1.414-1.414z"></path>
                                            </svg>
                                        </span>
                                        <span>
                                            <?php
                                            if (isset($active_subscription_details[0]['max_order_limit'])) {
                                                if ($active_subscription_details[0]['order_type'] == "unlimited") {
                                                    echo labels('enjoyUnlimitedOrders', "Unlimited Orders: No limits, just success.");
                                                } else {
                                                    echo labels('enjoyGenerousOrderLimitOf', "Enjoy a generous order limit of") . " " . $active_subscription_details[0]['max_order_limit'] . " " . labels('ordersDuringYourSubscriptionPeriod', "orders during your subscription period");
                                                }
                                            }
                                            ?>
                                        </span>
                                    </li>
                                    <li>
                                        <span class="icon">
                                            <svg height="24" width="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M0 0h24v24H0z" fill="none"></path>
                                                <path fill="currentColor" d="M10 15.172l9.192-9.193 1.415 1.414L10 18l-6.364-6.364 1.414-1.414z"></path>
                                            </svg>
                                        </span>
                                        <?php
                                        if ($active_subscription_details[0]['duration'] == "unlimited") {
                                            echo labels('enjoySubscriptionForUnlimitedDays', "Lifetime Subscription – seize success without limits!");
                                        } else {
                                            echo labels('yourSubscriptionWillBeValidFor', "Your subscription will be valid for") . " " . $active_subscription_details[0]['duration'] . " " . labels('days', "Days");
                                        }
                                        ?>
                                    </li>
                                    <li>
                                        <span class="icon">
                                            <svg height="24" width="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M0 0h24v24H0z" fill="none"></path>
                                                <path fill="currentColor" d="M10 15.172l9.192-9.193 1.415 1.414L10 18l-6.364-6.364 1.414-1.414z"></path>
                                            </svg>
                                        </span>
                                        <?php
                                        if ($active_subscription_details[0]['is_commision'] == "yes") {
                                            echo labels('commissionWillBeAppliedToYourEarnings', "Commission will be applied to your earnings");
                                        } else {
                                            echo labels('noNeedToPayExtraCommission', "Your income, your rules – no hidden commission charges on your profits");
                                        }
                                        ?>
                                    </li>
                                    <li>
                                        <span class="icon">
                                            <svg height="24" width="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M0 0h24v24H0z" fill="none"></path>
                                                <path fill="currentColor" d="M10 15.172l9.192-9.193 1.415 1.414L10 18l-6.364-6.364 1.414-1.414z"></path>
                                            </svg>
                                        </span>
                                        <?php
                                        if ($active_subscription_details[0]['is_commision'] == "yes"  &&  ($cod_setting == 1) && ($payment_gateway_setting == 1)) {
                                            echo labels('commissionThreshold', "Pay on Delivery threshold: The Pay on Service option will be closed, once the cash of the " . $currency . $active_subscription_details[0]['commission_threshold']) . " " . labels('AmountIsReached', " amount is reached");
                                        } else {
                                            echo labels('noThresholdOnPayOnDeliveryAmount', "There is no threshold on the Pay on Service amount.");
                                        }
                                        ?>
                                    </li>
                                    <li>
                                        <span class="icon">
                                            <svg height="24" width="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M0 0h24v24H0z" fill="none"></path>
                                                <path fill="currentColor" d="M10 15.172l9.192-9.193 1.415 1.414L10 18l-6.364-6.364 1.414-1.414z"></path>
                                            </svg>
                                        </span>
                                        <span>
                                            <?php
                                            if ($active_subscription_details[0]['is_commision'] == "yes") {
                                                echo $active_subscription_details[0]['commission_percentage'] . "% " . labels('commissionWillBeAppliedToYourEarnings', "commission will be applied to your earnings.");
                                            } else {
                                                echo labels('noNeedToPayExtraCommission', "Your income, your rules – no hidden commission charges on your profits");
                                            }
                                            ?></span>
                                    </li>
                                    <?php if ($price[0]['tax_percentage'] != "0") { ?>
                                        <li>
                                            <span class="icon">
                                                <svg height="24" width="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M0 0h24v24H0z" fill="none"></path>
                                                    <path fill="currentColor" d="M10 15.172l9.192-9.193 1.415 1.414L10 18l-6.364-6.364 1.414-1.414z"></path>
                                                </svg>
                                            </span>
                                            <span>
                                                <?php
                                                echo labels('tax_included', $price[0]['tax_percentage'] . "% tax included");
                                                ?></span>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } else { ?>
                <div class="row d-flex">
                    <?php foreach ($subscription_details as $row) { ?>
                        <div class="col-md-4 mb-md-3">
                            <div class="plan d-flex flex-column h-100">
                                <div class="inner  h-100">
                                    <div class="plan_title">
                                        <b><?= $row['name'] ?></b>
                                    </div>
                                    <?php
                                    $price = calculate_subscription_price($row['id']);;
                                    ?>
                                    <h5>
                                        <p class="plan_price"><b><?= $currency ?><?= $price[0]['price_with_tax'] ?></b></p>
                                    </h5>
                                    <ul class="features mb-3">
                                        <li>
                                            <span class="icon">
                                                <svg height="24" width="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M0 0h24v24H0z" fill="none"></path>
                                                    <path fill="currentColor" d="M10 15.172l9.192-9.193 1.415 1.414L10 18l-6.364-6.364 1.414-1.414z"></path>
                                                </svg>
                                            </span>
                                            <span><strong>
                                                    <?php
                                                    if ($row['order_type'] == "unlimited") {
                                                        echo labels('enjoyUnlimitedOrders', "Unlimited Orders: No limits, just success.");
                                                    } else {
                                                        echo labels('enjoyGenerousOrderLimitOf', "Enjoy a generous order limit of") . " " . $row['max_order_limit'] . " " . labels('ordersDuringYourSubscriptionPeriod', "orders during your subscription period");
                                                    }
                                                    ?>
                                                </strong></span>
                                        </li>
                                        <li>
                                            <span class="icon">
                                                <svg height="24" width="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M0 0h24v24H0z" fill="none"></path>
                                                    <path fill="currentColor" d="M10 15.172l9.192-9.193 1.415 1.414L10 18l-6.364-6.364 1.414-1.414z"></path>
                                                </svg>
                                            </span>
                                            <span><strong>
                                                    <?php
                                                    if ($row['duration'] == "unlimited") {
                                                        echo labels('enjoySubscriptionForUnlimitedDays', "Lifetime Subscription – seize success without limits!");
                                                    } else {
                                                        echo labels('yourSubscriptionWillBeValidFor', "Your subscription will be valid for") . " " . $row['duration'] . " " . labels('days', "Days");
                                                    }
                                                    ?>
                                                </strong>
                                        </li>
                                        <li>
                                            <span class="icon">
                                                <svg height="24" width="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill="currentColor" d="M10 15.172l9.192-9.193 1.415 1.414L10 18l-6.364-6.364 1.414-1.414z"></path>
                                                </svg>
                                            </span>
                                            <strong>
                                                <?php
                                                if ($row['is_commision'] == "yes") {
                                                    echo labels('commissionWillBeAppliedToYourEarnings', "Commission will be applied to your earnings");
                                                } else {
                                                    echo labels('noNeedToPayExtraCommission', "Your income, your rules – no hidden commission charges on your profits");
                                                }
                                                ?>
                                            </strong>
                                        </li>
                                        <li>
                                            <span class="icon">
                                                <svg height="24" width="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M0 0h24v24H0z" fill="none"></path>
                                                    <path fill="currentColor" d="M10 15.172l9.192-9.193 1.415 1.414L10 18l-6.364-6.364 1.414-1.414z"></path>
                                                </svg>
                                            </span>
                                            <strong>
                                                <?php
                                                if ($row['is_commision'] == "yes" && ($cod_setting == 1) && ($payment_gateway_setting == 1)) {
                                                    echo labels('commissionThreshold', "Pay on Delivery threshold: The Pay on Service option will be closed, once the cash of the " . $currency . $row['commission_threshold']) . " " . labels('AmountIsReached', " amount is reached");
                                                } else {
                                                    echo labels('noThresholdOnPayOnDeliveryAmount', "There is no threshold on the Pay on Service amount.");
                                                }
                                                ?>
                                            </strong>
                                        </li>
                                        <li>
                                            <span class="icon">
                                                <svg height="24" width="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M0 0h24v24H0z" fill="none"></path>
                                                    <path fill="currentColor" d="M10 15.172l9.192-9.193 1.415 1.414L10 18l-6.364-6.364 1.414-1.414z"></path>
                                                </svg>
                                            </span>
                                            <span>
                                                <strong>
                                                    <?php
                                                    if ($row['is_commision'] == "yes") {
                                                        echo $row['commission_percentage'] . "% " . labels('commissionWillBeAppliedToYourEarnings', "commission will be applied to your earnings.");
                                                    } else {
                                                        echo labels('noNeedToPayExtraCommission', "Your income, your rules – no hidden commission charges on your profits");
                                                    }
                                                    ?>
                                                </strong>
                                        </li>
                                        <?php if ($price[0]['tax_percentage'] != "0") { ?>
                                            <li>
                                                <span class="icon">
                                                    <svg height="24" width="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M0 0h24v24H0z" fill="none"></path>
                                                        <path fill="currentColor" d="M10 15.172l9.192-9.193 1.415 1.414L10 18l-6.364-6.364 1.414-1.414z"></path>
                                                    </svg>
                                                </span>
                                                <strong>
                                                    <?php
                                                    echo labels('tax_included', $price[0]['tax_percentage'] . "% tax included");
                                                    ?>
                                                </strong>
                                            </li>
                                        <?php     } ?>
                                        <a href="javascript:void(0);" class="toggle-description">
                                            <span class="icon" style="font-size: 11px;">
                                                <i class="fa-solid fa-eye fa-sm"></i>
                                                <i class="fa-solid fa-eye-slash fa-sm"></i>
                                            </span>
                                            <span class="text">View Description</span>
                                        </a>
                                        <div class="description">
                                            <?= $row['description'] ?>
                                        </div>
                                    </ul>
                                </div>
                                <form class="needs-validation make_payment_form" id="make_payment_for_subscription1" method="POST" action="<?= base_url('partner/make_payment_for_subscription') ?>">
                                    <input type="hidden" name="stripe_key_id" id="stripe_key_id" value="<?= $stripe_credentials['publishable_key'] ?>" />
                                    <input id="subscription_id" name="subscription_id" class="form-control" value="<?= $row['id'] ?>" type="hidden" name="">
                                    <input type="hidden" name="stripe_client_secret" id="stripe_client_secret" value="" />
                                    <input type="hidden" name="stripe_payment_id" id="stripe_payment_id" value="" />
                                    <input type="hidden" id="payment_gateway_count" value="<?= count($payment_gateway) ?>" />
                                    <input type="hidden" id="payment_gateway_amount" value="<?= $price[0]['price_with_tax'] ?>" />
                                    <?php if (count($payment_gateway) == 1) : ?>
                                        <input id="payment_method" name="payment_method" class="form-control" value="<?= $payment_gateway[0] ?>" type="hidden">
                                    <?php else : ?>
                                        <input id="payment_method" name="payment_method" class="form-control" value="" type="hidden">
                                    <?php endif; ?>
                                    <?php if (count($payment_gateway) > 1 &&   ($price[0]['price_with_tax'] != 0)) : ?>
                                        <div class="card card-primary paymentGatewaySelectionCard" style="display: none;background-color:#f4f6f9;">

                                            <div class="card-header">
                                                <h4>Select Payment Gateway</h4>

                                            </div>
                                            <div class="card-body">
                                                <ul>
                                                    <?php foreach ($payment_gateway as $gateway) : ?>
                                                        <li style="list-style: none; cursor: pointer;" class="mb-3"id="<?= $gateway ?>">
                                                            <div class="row text-dark">
                                                                <div class="col-md-1 w-auto m-0">
                                                                    <?php $icon_path = base_url("public/uploads/site/" .$gateway . "_icon.svg"); ?>
                                                                    
                                                                    <img src="<?= $icon_path; ?>" alt="<?= $gateway ?> icon" />
                                                                </div>
                                                                <div class="col-md-4 w-auto m-0">
                                                                    <div class="form-check">
                                                                        <label class="form-check-label mt-2">
                                                                            <?= ucfirst($gateway) ?>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6 w-auto d-flex justify-content-end">
                                                                    <input class="form-check-input payment_gateway_radio" type="radio" name="payment_gateway_<?= $row['id'] ?>" id="payment_gateway_<?= $gateway ?>" value="<?= $gateway ?>" required>
                                                                </div>
                                                            </div>
                                                        </li>
                                                     
                                                    <?php endforeach; ?>
                                                </ul>

                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-footer mt-auto">
                                        <button type="submit" class="btn btn-block text-white bg-primary">Buy</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php            } ?>
                <?php } ?>
                </div>
        </div>
    </section>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleDescriptionLinks = document.querySelectorAll('.toggle-description');
        toggleDescriptionLinks.forEach(function(link) {
            link.addEventListener('click', function() {
                const description = link.nextElementSibling;
                description.classList.toggle('show');
                const icon = link.querySelector('.icon');
                const eyeIcon = icon.querySelector('.fa-eye');
                const eyeSlashIcon = icon.querySelector('.fa-eye-slash');
                if (description.classList.contains('show')) {
                    link.querySelector('.text').textContent = 'Hide Description';
                    eyeIcon.style.display = 'none';
                    eyeSlashIcon.style.display = 'inline-block';
                } else {
                    link.querySelector('.text').textContent = 'View Description';
                    eyeIcon.style.display = 'inline-block';
                    eyeSlashIcon.style.display = 'none';
                }
            });
        });
    });
</script>
<style>
    .description {
        display: none;
    }

    .description.show {
        display: block;
    }

    .fa-eye-slash {
        display: none;
    }
</style>

<script>
    $(document).ready(function() {
        // Initially hide all payment gateway selection cards
        $('.make_payment_form').each(function() {
            $(this).find('.paymentGatewaySelectionCard').hide();
        });

        // Handle card click and selection
        $('li').on('click', function() {
            const selectedGateway = $(this).attr('id');
            selectGateway(selectedGateway);
        });

        const gateways = ["paypal", "paystack", "stripe", "razorpay", "flutterwave"];

        function selectGateway(selectedGateway) {
            gateways.forEach(gateway => {
                if (gateway === selectedGateway) {
                    $(`#${gateway}`).addClass('selected_payment_method');
                    $(`#payment_gateway_${gateway}`).prop('checked', true);
                } else {
                    $(`#${gateway}`).removeClass('selected_payment_method');
                }
            });

            // Update hidden input with selected gateway value
            $('.make_payment_form').find('#payment_method').val(selectedGateway);
            if (selectedGateway) {
                $('.make_payment_form').find('button[type=submit]').prop('disabled', false);
            } else {
                $('.make_payment_form').find('button[type=submit]').prop('disabled', true);
            }
        }

        // Handle radio button change
        $('.make_payment_form .payment_gateway_radio').change(function() {
            var form = $(this).closest('form');
            var selectedGateway = form.find('input[name^=payment_gateway_]:checked').val();
            form.find('#payment_method').val(selectedGateway);

            if (selectedGateway) {
                form.find('button[type=submit]').prop('disabled', false);
            } else {
                form.find('button[type=submit]').prop('disabled', true);
            }
        });

        // Handle form submission
        $('.make_payment_form').submit(function() {
            var form = $(this);
            var selectedGateway = form.find('input[name^=payment_gateway_]:checked').val();
            var paymentGatewayCount = form.find('#payment_gateway_count').val();
            var paymentGatewayAmount = form.find('#payment_gateway_amount').val();

            if (paymentGatewayCount > 1 && !selectedGateway && paymentGatewayAmount != 0) {
                alert('Please select a payment gateway.');
                return false;
            }
            return true;
        });

        // Toggle payment gateway selection card visibility
        function togglePaymentGatewaySelectionCard(form) {
            var paymentGatewayCard = form.find('.paymentGatewaySelectionCard');
            if (form.find('.payment_gateway_radio').length > 1) {
                paymentGatewayCard.show();
            } else {
                paymentGatewayCard.hide();
            }
        }

        $('.make_payment_form button[type=submit]').click(function() {
            var form = $(this).closest('form');
            togglePaymentGatewaySelectionCard(form);
        });
    });
</script>
