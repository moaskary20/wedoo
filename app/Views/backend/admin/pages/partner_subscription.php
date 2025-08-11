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
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item "><?= labels('partner_details', 'Partner Details') ?></div>
                <div class="breadcrumb-item "><?= labels('subscription', 'Subscription') ?></div>
            </div>
        </div>
        <?= helper('form'); ?>
        <?php include "provider_details.php"; ?>
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
                                            echo labels('yourSubscriptionWillBeValidFor', "Your subscription will be valid for ") . " " . $active_subscription_details[0]['expiry_date'];
                                            ?>
                                        </div>
                                    </div>
                                <?php  } else { ?>
                                    <div class="active_subscription_plan_expiry_date mt-5">
                                        <div class="form-group m-0 p-0">
                                            <?php echo labels('enjoySubscriptionForUnlimitedDays', "Lifetime Subscription - seize success without limits!") ?>;
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
                                                $status = labels('success', 'Success');
                                            } elseif ($active_subscription_details[0]['is_payment'] == 0) {
                                                $status = labels('pending', 'Pending');
                                            } else {
                                                $status = labels('failed', 'Failed');
                                            }
                                            ?>
                                            <?= $status ?>
                                        </div>
                                    </div>
                                </div>
                                <ul class="active_subscription_feature_list mb-3 mt-3" style="margin:28px">
                                    <!-- Feature list -->
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
                                        if ($active_subscription_details[0]['is_commision'] == "yes" &&  ($cod_setting == 1) && ($payment_gateway_setting == 1)) {
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
                        </div> <!-- end item-left -->
                    </div>
                </div>
                <?php
                $uri = service('uri');
                $partner_id = $uri->getSegments()[3];
                ?>
                <div class="__btn-container btn--container justify-content-end d-flex">
                    <!-- <a href="<?= base_url("admin/partners/all_subscription/" . $partner_id); ?>" class="btn btn-grey mr-3" >
                        <i class="fas fa-sync-alt"></i>
                        <span class="ml-1"><?= labels('change_renew_plan', 'Change / Renew Subscription Plan') ?></span>
                    </a> -->
                    <button class="btn bg-new-primary mr-3 view_rating  ml-1" title="<?= labels('change_renew_plan', 'Change / Renew Subscription Plan') ?>" data-toggle="modal" data-target="#view_rating_model">
                        <i class="fas fa-sync-alt"></i><span class="ml-1"><?= labels('change_renew_plan', 'Change / Renew Subscription Plan') ?></span>
                    </button>
                    <form class="needs-validation" id="cancel_subscription_plan" method="POST" action="<?= base_url('admin/cancle_subscription_plan') ?>">
                        <input type="hidden" name="partner_id" id="partner_id" value=<?= $partner_id ?>>
                        <button type="submit" class="btn bg-new-primary mr-3" onclick="cancleplan(<?= $partner_id ?>)">
                            <i class="far fa-times-circle"></i>
                            <span class="ml-1"><?= labels('cancel_plan', 'Cancel Subscription Plan') ?></span>
                </div>
                </form>
        </div>
    <?php } else { ?>
        <div class="row d-flex">
            <?php foreach ($subscription_details as $row) { ?>
                <div class="col-md-4 mb-md-3">
                    <div class="plan d-flex flex-column h-100">
                        <div class="inner  h-100">
                            <!-- Plan details -->
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
                                <!-- Feature list -->
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
                                <!-- Add more features as needed -->
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
                                <!-- Toggle Description Link -->
                                <a href="javascript:void(0);" class="toggle-description">
                                    <span class="icon" style="font-size: 11px;">
                                        <i class="fa-solid fa-eye fa-sm"></i>
                                        <i class="fa-solid fa-eye-slash fa-sm"></i>
                                    </span>
                                    <span class="text">View Description</span>
                                </a>
                                <!-- Description -->
                                <div class="description">
                                    <?= $row['description'] ?>
                                </div>
                            </ul>
                        </div>
                        <form class="needs-validation" id="make_payment_for_subscription1" method="POST" action="<?= base_url('admin/assign_subscription_to_partner') ?>">
                            <input type="hidden" name="stripe_key_id" id="stripe_key_id" value="sdfesr" />
                            <input id="subscription_id" name="subscription_id" class="form-control" value="<?= $row['id'] ?>" type="hidden" name="">
                            <input id="payment_method" name="payment_method" class="form-control" value="stripe" type="hidden" name="">
                            <input type="hidden" name="stripe_client_secret" id="stripe_client_secret" value="" />
                            <input type="hidden" name="partner_id" id="partner_id" value=<?= $partner_id ?>>
                            <input type="hidden" name="stripe_payment_id" id="stripe_payment_id" value="" />
                            <!-- Buy button -->
                            <div class="card-footer mt-auto">
                                <div class="form-group m-0 p-0">
                                    <button type="button" class="btn btn-block text-white bg-primary" style="" onclick="confirmAssign(<?= $row['id'] ?>)">Assign</button>
                        </form>
                    </div>
                </div>
        </div>
</div>
<?php            } ?>
<?php            } ?>
</div>
</div>
<!-- model for view rating -->
<div class="modal fade" id="view_rating_model" tabindex="-1" role="dialog" aria-labelledby="view_rating_model" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl " role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle"><?= labels('change_renew_plan', 'Change / Renew Subscription Plan') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="background-color: #f4f6f9;">
                <div class="row">
                    <?php foreach ($subscription_details as $row) { ?>
                        <div class="col-md-6 mb-md-3">
                            <div class="plan d-flex flex-column h-100">
                                <div class="inner  h-100">
                                    <!-- Plan details -->
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
                                        <!-- Feature list -->
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
                                        <!-- Add more features as needed -->
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
                                        <!-- Toggle Description Link -->
                                        <a href="javascript:void(0);" class="toggle-description">
                                            <span class="icon" style="font-size: 11px;">
                                                <i class="fa-solid fa-eye fa-sm"></i>
                                                <i class="fa-solid fa-eye-slash fa-sm"></i>
                                            </span>
                                            <span class="text">View Description</span>
                                        </a>
                                        <!-- Description -->
                                        <div class="description">
                                            <?= $row['description'] ?>
                                        </div>
                                    </ul>
                                </div>
                                <form class="needs-validation" id="make_payment_for_subscription1" method="POST" action="<?= base_url('admin/assign_subscription_to_partner') ?>">
                                    <input type="hidden" name="stripe_key_id" id="stripe_key_id" value="pk_test_51Hh90WLYfObhNTTwooBHwynrlfiPo2uwxyCVqGNNCWGmpdOHuaW4rYS9cDldKJ1hxV5ik52UXUDSYgEM66OX45550065US7tRX" />
                                    <input id="subscription_id" name="subscription_id" class="form-control" value="<?= $row['id'] ?>" type="hidden" name="">
                                    <input id="payment_method" name="payment_method" class="form-control" value="stripe" type="hidden" name="">
                                    <input type="hidden" name="stripe_client_secret" id="stripe_client_secret" value="" />
                                    <input type="hidden" name="partner_id" id="partner_id" value=<?= $partner_id ?>>
                                    <input type="hidden" name="stripe_payment_id" id="stripe_payment_id" value="" />
                                    <!-- Buy button -->
                                    <div class="card-footer mt-auto">
                                        <div class="form-group m-0 p-0">
                                            <button type="button" class="btn btn-block text-white" style="background-color:#344052;" onclick="confirmAssign(<?= $row['id'] ?>)">Assign</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"> <?= labels('close', 'Close') ?> </button>
                <button type="button" class="btn btn-primary"> <?= labels('save_changes', 'Close') ?></button>
            </div>
        </div>
    </div>
</div>
</div>
</div>
</div>
</section>
</div>
<script>
    // JavaScript code
    document.addEventListener('DOMContentLoaded', function() {
        const submitButton = document.querySelector('#make_payment'); // Get the submit button
        const toggleDescriptionLinks = document.querySelectorAll('.toggle-description');
        toggleDescriptionLinks.forEach(function(link) {
            link.addEventListener('click', function() {
                const description = link.nextElementSibling;
                description.classList.toggle('show');
                // Get the icon elements
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
<script>
    function confirmAssign(subscriptionId) {
        event.preventDefault(); // Prevent the default form submission
        Swal.fire({
            title:"<?= labels('are_your_sure', 'Are you sure?') ?>",
            text: "Once you assign this subscription plan, you cannot assign again until the current plan expires. Choose wisely!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, proceed!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Set the subscription ID in a hidden input field
                document.getElementById('subscription_id').value = subscriptionId;
                // Submit the form
                document.getElementById('make_payment_for_subscription1').submit();
            }
        });
    }

    function cancleplan(partner_id) {
        event.preventDefault(); // Prevent the default form submission
        Swal.fire({
            title: "<?= labels('are_your_sure', 'Are you sure?') ?>",
            text: "The result of this will be the subscription of the provider getting deactivated..",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, proceed!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Set the subscription ID in a hidden input field
                document.getElementById('partner_id').value = partner_id;
                // Submit the form
                document.getElementById('cancel_subscription_plan').submit();
            }
        });
    }
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