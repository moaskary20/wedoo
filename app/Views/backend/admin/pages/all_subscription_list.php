<div class="main-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('subscription', " Subscription") ?><span class="breadcrumb-item p-3 pt-2 text-primary"></span></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
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
                                            if ($row['is_commision'] == "yes") {
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
                            <form class="needs-validation" id="make_payment_for_subscription1" method="POST" action="<?= base_url('admin/assign_subscription_to_partner') ?>">
                                <input type="hidden" name="stripe_key_id" id="stripe_key_id" value="pk_test_51Hh90WLYfObhNTTwooBHwynrlfiPo2uwxyCVqGNNCWGmpdOHuaW4rYS9cDldKJ1hxV5ik52UXUDSYgEM66OX45550065US7tRX" />
                                <input id="subscription_id" name="subscription_id" class="form-control" value="<?= $row['id'] ?>" type="hidden" name="">
                                <input id="payment_method" name="payment_method" class="form-control" value="stripe" type="hidden" name="">
                                <input type="hidden" name="stripe_client_secret" id="stripe_client_secret" value="" />
                                <input type="hidden" name="partner_id" id="partner_id" value=<?= $partner_id ?>>
                                <input type="hidden" name="stripe_payment_id" id="stripe_payment_id" value="" />
                                <div class="card-footer mt-auto">
                                    <div class="form-group m-0 p-0">
                                        <button type="button" class="btn btn-block text-white" style="background-color:#344052;" onclick="confirmAssign(<?= $row['id'] ?>)">Assign</button>
                            </form>
                        </div>
                    </div>
            </div>
        </div>
    <?php            } ?>
</div>
</div>
</section>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const submitButton = document.querySelector('#make_payment'); // Get the submit button
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
<script>
    function confirmAssign(subscriptionId) {
        event.preventDefault();
        Swal.fire({
            title: "<?= labels('are_your_sure', 'Are you sure?') ?>",
            text: "Once you assign this subscription plan, you cannot assign again until the current plan expires. Choose wisely!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, proceed!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('subscription_id').value = subscriptionId;
                document.getElementById('make_payment_for_subscription1').submit();
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