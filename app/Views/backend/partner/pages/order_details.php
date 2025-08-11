<head>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
</head>
<div class="main-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('view_booking', 'View Booking') ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('partner/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item"><?= labels('booking_details', 'Booking details') ?></a></div>
            </div>
        </div>
        <?= helper('form'); ?>
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card border border-grey">
                        <div class="card-body">
                            <div class="row ml-0">
                                <div class="col h-full">
                                    <div class="mb-6">
                                        <div class=" d-flex items-center justify-content-between mb-2 text-dark">
                                            <div>
                                                <h3 class="d-md-inline d-block">
                                                    <span><?= labels('booking', 'Booking') ?></span>
                                                    <span class="ltr:ml-2 rtl:mr-2 text-primary"> <?= !empty($order_details['invoice_no']) ? $order_details['invoice_no'] : "" ?></span>
                                                </h3>
                                                <!-- <div class="tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3 m-2"><?= $order_details['status']; ?>
                                                </div>
                                                <div class="tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-cyan-100 text-cyan-600 dark:bg-cyan-500/20 dark:text-cyan-100"><?= (!empty($order_details['payment_status'])) ? ($order_details['payment_status']) : 'pending'; ?></div> -->
                                            </div>
                                            <div>
                                                <?php
                                                if ($order_details['payment_status_of_additional_charge'] == '0'  && $order_details['total_additional_charge'] > 0) { ?>
                                                    <div class="tag text-danger"> <?= labels('pending_payment', 'Pending payment : ') ?><?= $currency . $order_details['total_additional_charge'] ?>
                                                    </div>
                                                <?php }
                                                ?>
                                            </div>
                                        </div>

                                        <span class="flex items-center" style="width: 100%; display: flex;margin: 16px 0 16px 0;">
                                            <span class="material-symbols-outlined">
                                                info
                                            </span>
                                            <?= labels('booking_status', 'Booking status : ') ?> :
                                            <span class="ltr:ml-1 rtl:mr-1"> &nbsp;<?= str_replace('_',' ',ucfirst($order_details['status'])); ?> </span>
                                        </span>


                                        <span class="flex items-center" style="width: 100%;display: flex;margin: 16px 0 16px 0;">
                                            <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20,200,0,-25" />
                                            <span class="material-symbols-outlined">
                                                event_available
                                            </span>
                                            <?= labels('date_of_service', 'Date Of Service : ') ?> :
                                            <span class="ltr:ml-1 rtl:mr-1">&nbsp; <?= !empty($order_details['date_of_service']) ? $order_details['date_of_service'] : "No data found" ?></span>
                                        </span>
                                        <span class="flex items-center" style="width: 100%;display: flex;margin: 16px 0 16px 0;">
                                            <span class="material-symbols-outlined">
                                                monetization_on
                                            </span>
                                            <?= labels('payment_method', 'Payment Method : ') ?> :
                                            <span class="ltr:ml-1 rtl:mr-1"> &nbsp;<?= !empty($order_details['payment_method']) ? (($order_details['payment_method'] == "cod") ? "Pay On Service" : $order_details['payment_method']) : "No data found" ?></span>
                                        </span>

                                        <?php if ($order_details['total_additional_charge'] != 0 || $order_details['total_additional_charge'] != NULL) : ?>
                                            <div>
                                                <span class="flex items-center" style="width: 100%; display: flex;margin: 16px 0 16px 0;">
                                                    <span class="material-symbols-outlined">
                                                        monetization_on
                                                    </span>
                                                    <?= labels('payment_method_of_additional_charge', 'Payment Method Of Additional Charges: ') ?> :
                                                    <span class="ltr:ml-1 rtl:mr-1"> &nbsp;<?= !empty($order_details['payment_method_of_additional_charge']) ? (($order_details['payment_method_of_additional_charge'] == "cod") ? "Pay On Service" : $order_details['payment_method_of_additional_charge']) : "No data found" ?></span>
                                                </span>

                                            </div>


                                            <div class="">
                                                <span class="flex items-center" style="width: 100%;display: flex;margin: 16px 0 16px 0;">
                                                    <span class="material-symbols-outlined">
                                                        note
                                                    </span>
                                                    <?= labels('payment_status_of_additional_charges', 'Payment Status of Additional Charges : ') ?>

                                                    <?php

                                                    if ($order_details['payment_status_of_additional_charge'] == "") {
                                                        $status = "Pending";
                                                    } else if ($order_details['payment_status_of_additional_charge'] == "0") {
                                                        $status = "Pending";
                                                    } else if ($order_details['payment_status_of_additional_charge'] == "1") {
                                                        $status = "Success";
                                                    } else if ($order_details['payment_status_of_additional_charge'] == "2") {
                                                        $status == "Failed";
                                                    }

                                                    ?>

                                                     <?= (!empty($status)) ? ($status) : 'pending'; ?>
                                                </span>
                                            </div>


                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-8 col-md-12 gap-4 xl:flex">
                                    <div class="w-full">
                                        <div class="mb-4 border-0 card-border ">
                                            <div class=" card-gutterless">
                                                <div class="overflow-x-auto">
                                                    <table class="table-default table-hover" id="invoice_table" data-show-export="true" data-export-types="['txt','excel','csv']" data-export-options='{"fileName": "invoice-order-list","ignoreColumn": ["action"]}' data-auto-refresh="true" data-toggle="table" data-search-highlight="true" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-url="<?= base_url('partner/orders/order_summary_table/' . $order_details['id']); ?>" data-sort-order="DESC">
                                                        <thead>
                                                            <tr class="brd text-dark-all">
                                                                <th data-field="service_title" data-sortable="true" data-visible="true"><?= labels('service', 'Services') ?></th>
                                                                <th data-field="price" data-sortable="true" data-visible="true"><?= labels('price', 'Price') ?></th>
                                                                <th data-field="discount" data-visible="true"> <?= labels('discount', 'discount') ?></th>
                                                                <th data-field="net_amount" data-visible="true"> <?= labels('net_amount', 'New Amount') ?></th>
                                                                <th data-field="tax" data-visible="false"><?= labels('tax_percentage', 'Tax Percentage') ?> </th>
                                                                <th data-field="tax_amount" data-visible="false"><?= labels('Tax Amount', 'tax Amount') ?> </th>
                                                                <th data-field="quantity" data-visible="true"><?= labels('quantity', ' Quantity') ?></th>
                                                                <th data-field="subtotal" data-visible="true"><?= labels('sub_total_including_tax', 'Sub total (Including Tax)') ?> </th>
                                                            </tr>
                                                        </thead>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="">
                                        <div class="row">
                                            <div class="col-lg-8 col-sm-6">
                                            </div>
                                            <div class="col-lg-4 col-sm-6">
                                                <div class=" mb-4 card-border">
                                                    <div class="" style="background-color:#f2f1f6;border-radius:0.3rem">
                                                        <?php
                                                        $total = isset($order_details['total']) ? str_replace(',', '', $order_details['total']) : 0;
                                                        $total_additional_charge = isset($order_details['total_additional_charge']) ? str_replace(',', '', $order_details['total_additional_charge']) : 0;
                                                        $visiting_charges = isset($order_details['visiting_charges']) ? str_replace(',', '', $order_details['visiting_charges']) : 0;
                                                        $promo_discount = isset($order_details['promo_discount']) ? str_replace(',', '', $order_details['promo_discount']) : 0;
                                                        $final_total = $total + $total_additional_charge + $visiting_charges - $promo_discount;
                                                        ?> <ul class="text-dark" style="padding: 0;" class="mb-0">
                                                            <li class="flex items-center justify-between ml-2 mr-2 mt-1 pl-1 pt-3"><span>Total</span><span class="font-semibold"><span><?= !empty($order_details['total']) ? $currency . ($subtotal - $tax_amount)  : "No data found" ?></span></span></li>
                                                            <hr class="flex items-center justify-between   " style="border-color: #dde2e6;">
                                                            <li class="flex items-center justify-between  ml-2 mt-1  mr-2"><span><?= labels('tax_amount', "Tax Amount") ?></span><span class="font-semibold"><span> <label for="" class="bold"> <?= $tax_amount; ?></label></span></span></li>
                                                            <hr class="flex items-center justify-between   " style="border-color: #dde2e6;">
                                                            <?php
                                                            if ($order_details['visiting_charges'] != "0") { ?>
                                                                <li class="flex items-center justify-between   ml-2 mt-1 mr-2"><span><?= labels('service_charge', "Service Charge") ?></span><span class="font-semibold"><span><?= !empty($order_details['visiting_charges']) ? $currency . $order_details['visiting_charges']  : 0 ?></span></span></li>
                                                                <hr class="flex items-center justify-between   " style="border-color: #dde2e6;">
                                                            <?php }
                                                            ?>
                                                            <?php
                                                            if (!empty($order_details['additional_charges'])) {
                                                                foreach (($order_details['additional_charges']) as $key => $charge) {
                                                            ?>
                                                                    <li class="flex items-center justify-between ml-2 mt-1 mr-2">
                                                                        <span><?= !empty($charge['name']) ? $charge['name'] : 'N/A' ?></span>
                                                                        <span class="font-semibold">
                                                                            <?= !empty($charge['charge']) ? $currency . $charge['charge'] : $currency . '0' ?>
                                                                        </span>
                                                                    </li>
                                                                    <hr class="flex items-center justify-between   " style="border-color: #dde2e6;">
                                                            <?php
                                                                }
                                                            } ?>
                                                            <li class="flex items-center justify-between ml-2 mt-1  mr-2"><span><?= labels('promo_code', "Promo Code") ?></span><span class="font-semibold"><span><?= !empty($order_details['promo_discount']) ? $order_details['promo_discount'] : "No data found" ?></span></span></li>
                                                            <hr class="flex items-center justify-between mb-0  " style="border-color: #dde2e6;">
                                                            <li class="fbg-dark  flex items-center justify-between mb-lg-n5  p-3 " style="background-color: #DFDDEB;"><span> <?= labels('Payable Total', "Payable Total") ?></span><span class="font-semibold"><span><?= !empty($final_total) ?  $currency . $final_total : "" ?></span></span></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4  border-left">
                                    <div class="card text-dark border-bottom" style="border: none!important;">
                                        <form id="myForm" method="post" enctype="multipart/form-data">
                                            <div class="order_detail_headings mb-4">
                                                <h5 class="m-0 p-2"><?= labels('booking_status', "Booking Status") ?></h5>
                                            </div>
                                            <div class="form-group  text-dark col-md">
                                                <select name="status" id="status" class="form-control update_order_status select2">
                                                    <option data-customer_id="<?= $order_details["customer_id"] ?>" data-order_id="<?= $order_details["id"] ?>" value="pending" <?= (isset($order_details['status']) && !empty($order_details['status']) && $order_details['status'] == "pending") ? "selected" : "" ?>><?= labels('pending', 'Pending') ?></option>
                                                    <option data-customer_id="<?= $order_details["customer_id"] ?>" data-order_id="<?= $order_details["id"] ?>" value="awaiting" <?= (isset($order_details['status']) && !empty($order_details['status']) && $order_details['status'] == "awaiting") ? "selected" : "" ?>><?= labels('awaiting', 'Awaiting') ?></option>
                                                    <option data-customer_id="<?= $order_details["customer_id"] ?>" data-order_id="<?= $order_details["id"] ?>" value="confirmed" <?= (isset($order_details['status']) && !empty($order_details['status']) && $order_details['status'] == "confirmed") ? "selected" : "" ?>><?= labels('confirmed', 'Confirmed') ?></option>
                                                    <option data-customer_id="<?= $order_details["customer_id"] ?>" data-order_id="<?= $order_details["id"] ?>" value="started" <?= (isset($order_details['status']) && !empty($order_details['status']) && $order_details['status'] == "started") ? "selected" : "" ?>><?= labels('started', 'Started') ?></option>
                                                    <option data-customer_id="<?= $order_details["customer_id"] ?>" data-order_id="<?= $order_details["id"] ?>" value="booking_ended" <?= (isset($order_details['status']) && !empty($order_details['status']) && $order_details['status'] == "booking_ended") ? "selected" : "" ?>><?= labels('booking_ended', 'Booking ended') ?></option>
                                                    <option data-customer_id="<?= $order_details["customer_id"] ?>" data-order_id="<?= $order_details["id"] ?>" value="rescheduled" <?= (isset($order_details['status']) && !empty($order_details['status']) && $order_details['status'] == "rescheduled") ? "selected" : "" ?>><?= labels('rescheduled', 'Rescheduled') ?></option>
                                                    <option data-customer_id="<?= $order_details["customer_id"] ?>" data-order_id="<?= $order_details["id"] ?>" value="cancelled" <?= (isset($order_details['status']) && !empty($order_details['status']) && $order_details['status'] == "cancelled") ? "selected" : "" ?>><?= labels('cancelled', 'Cancelled') ?></option>
                                                    <option data-customer_id="<?= $order_details["customer_id"] ?>" data-order_id="<?= $order_details["id"] ?>" value="completed" <?= (isset($order_details['status']) && !empty($order_details['status']) && $order_details['status'] == "completed") ? "selected" : "" ?>><?= labels('completed', 'Completed') ?></option>
                                                </select>
                                            </div>
                                            <div class="row m-0">
                                                <div class="col-md">
                                                    <div class="form-group rescheduled_date">
                                                        <label for="rescheduled_date"><?= labels('rescheduled_date', "Rescheduled Date") ?></label>
                                                        <input id="rescheduled_date" class="form-control" type="date" name="rescheduled_date">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row m-0 work_started_proof">
                                                <div class="col-md mt-2">
                                                    <div class="form-group">
                                                        <label for=""><?= labels('work_started_proof', "Work Started Proof") ?></label>
                                                        <!-- <input type="file" class="filepond-only-images-and-videos" id="filepond2" name="work_started_files[]" multiple> -->

                                                        <input type="file" class="filepond-only-images-and-videos" id="work_started_files" name="work_started_files[]" multiple>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row m-0 work_completed_proof">
                                                <div class="col-md mt-2">
                                                    <div class="form-group">
                                                        <label for=""><?= labels('work_completed_proof', "Work Completed Proof") ?></label>

                                                        <input type="file" name="work_complete_files[]" class="filepond-only-images-and-videos" id="file[]" multiple>

                                                        <!-- <input type="file" name="work_complete_files[]" class="filepond-only-images-and-videos" id="work_complete_files" multiple> -->
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="row m-0">
                                                <div class="col-md">
                                                    <div id="additional-charges-container">
                                                        <div class="booking_ended_additional_charge">
                                                            <div class="form-group">
                                                                <label for=""><?= labels('additional_charge', "Additional Charge") ?></label>
                                                                <div class="row  mb-2">
                                                                    <div class="col-md-6 pr-0">
                                                                        <input class="form-control" type="text" name="booking_ended_additional_charges[0][name]" placeholder="<?= labels('enter', 'Enter ') ?> <?= labels('charge_name', 'Charge Name') ?> <?= labels('here', ' Here ') ?>" required>
                                                                    </div>
                                                                    <div class="col-md-4 pr-0">
                                                                        <input class="form-control" type="number" min=1 name="booking_ended_additional_charges[0][charge]" placeholder="<?= labels('amount', 'Amount') ?> <?= labels('here', ' Here ') ?>" required>
                                                                    </div>
                                                                    <div class="col-md-2 d-flex align-items-end">
                                                                        <button type="button" class="btn btn-primary add-button">+</button>
                                                                        <button type="button" class="btn btn-danger remove-button" style="display:none;">-</button>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row m-0">
                                                <div class="col-sm mt-2">
                                                    <div class="row" id="available-slots">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row m-0">
                                                <div class="col-md mt-3">
                                                    <input type="hidden" name="order_id" id="order_id" value="<?= $order_details['id'] ?>">
                                                    <input type="hidden" name="is_otp_enable" id="is_otp_enable" value="<?= $order_details['is_otp_enalble'] ?>">
                                                    <button class="btn btn-md btn-primary text-white" id="change_status"> <?= labels('update', "Update") ?> <?= labels('status', "Status") ?> </button>
                                                </div>
                                            </div>
                                            <p class="order-detail-p reschedulable"></p>
                                        </form>
                                    </div>
                                    <div class="card text-dark" style="border: none!important;">
                                        <div class="order_detail_headings ">
                                            <h5 class="m-0 p-2"><?= labels('customer_details', "Customer Details") ?></h5>
                                        </div>
                                        <div class="border-bottom">
                                            <div class="widget-user-header bg-info">
                                                <input type="hidden" name="hidden" id="order_id" value="id">
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <ul class="nav flex-column">
                                                        <li class="nav-item">
                                                            <label for="" class="bold"><?= labels('name', "Name") ?>:</label>
                                                        </li>
                                                        <li class="nav-item">
                                                            <label for="" class="bold"><?= labels('contact_no', "Contact") ?>:</label>
                                                        </li>
                                                        <li class="nav-item">
                                                            <label for="" class="bold"><?= labels('email', "Email") ?></label>
                                                        </li>
                                                        <?php
                                                        if ($order_details['address_id'] != "0") { ?>
                                                            <li class="nav-item">
                                                                <label for="" class="bold"><?= labels('address', "Address") ?></label>
                                                            </li>
                                                        <?php } ?>
                                                    </ul>
                                                </div>
                                                <div class="col-md-6">
                                                    <ul class="nav flex-column">
                                                        <li class="nav-item">
                                                            <label for="" class="bold"> <?= !empty($order_details['customer']) ? $order_details['customer'] : "No data found" ?> </label>
                                                        </li>
                                                        <li class="nav-item">
                                                            <label for="" class="bold"> <?= !empty($order_details['customer_no']) ? $order_details['customer_no'] : "No data found" ?> </label>
                                                        </li>
                                                        <li class="nav-item">
                                                            <label for="" class="bold"> <?= !empty($order_details['customer_email']) ? $order_details['customer_email'] : "No data found" ?></label>
                                                        </li>
                                                        <?php
                                                        if ($order_details['address'] != "0") { ?>
                                                            <li class="nav-item">
                                                                <label for="" class="bold" style="word-break: break-all;  "><?= !empty($order_details['address']) ? $order_details['address'] : "No data found" ?> </label>
                                                            </li>
                                                        <?php } ?>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card text-dark" style="border: none!important;">
                                        <div class="order_detail_headings ">
                                            <h5 class="m-0 p-2"><?= labels('service_info', "Service Info") ?></h5>
                                        </div>
                                        <div class="border-bottom">
                                            <div class="widget-user-header bg-info">
                                                <input type="hidden" name="hidden" id="order_id" value="id">
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <ul class="nav flex-column">
                                                        <li class="nav-item">
                                                            <label for="" class="bold"><?= labels('start_time', "Start Time") ?>:</label>
                                                        </li>
                                                        <li class="nav-item">
                                                            <label for="" class="bold"><?= labels('end_time', "End Time") ?>:</label>
                                                        </li>
                                                        <li class="nav-item">
                                                            <label for="" class="bold"><?= labels('service_duration', "Service Duration") ?></label>
                                                        </li>
                                                        <?php
                                                        if ($order_details['address_id'] != "0") { ?>
                                                            <li class="nav-item">
                                                                <label for="" class="bold"><?= labels('visiting_charge', "Visiting Charge") ?></label>
                                                            </li>
                                                        <?php } ?>
                                                        <li class="nav-item">
                                                            <label for="" class="bold"><?= labels('service_type', "Service Type") ?></label>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="col-md-6">
                                                    <ul class="nav flex-column">
                                                        <li class="nav-item">
                                                            <label for="" class="bold"> <?= !empty($order_details['starting_time']) ? $order_details['starting_time'] : "No data found" ?> </label>
                                                        </li>
                                                        <li class="nav-item">
                                                            <label for="" class="bold"> <?= !empty($order_details['ending_time']) ? $order_details['ending_time'] : "No data found" ?> </label>
                                                        </li>
                                                        <li class="nav-item">
                                                            <label for="" class="bold"> <?= !empty($order_details['duration']) ? $order_details['duration'] . " Minutes" : "No data found" ?></label>
                                                        </li>
                                                        <?php
                                                        if ($order_details['visiting_charges'] != "0") { ?>
                                                            <li class="nav-item">
                                                                <label for="" class="bold"><?= !empty($order_details['visiting_charges']) ? $currency . $order_details['visiting_charges'] : "No data found" ?></label>
                                                            </li>
                                                        <?php } ?>
                                                        <li class="nav-item">
                                                            <div class="bg-emerald-100 dark:text-emerald-100 tag text-emerald-600"><?= $order_details['address_id'] == "0" ? "At store" : "At Doorstep"  ?></div>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <?php
                                                if (!empty($sub_order)) { ?>
                                                    <div class="row ml-0">
                                                        <strong class="ml-3">
                                                            <h6><?= labels('Order scheduled for the multiple days', "Order scheduled for the multiple days") ?></h6>
                                                        </strong>
                                                        <div class="col-md-6 ">
                                                            <ul class="nav flex-column">
                                                                <li class="nav-item">
                                                                    <label for="" class="bold"><?= labels('date', "Date") ?>:</label>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <label for="" class="bold"><?= labels('start_time', "Start Time") ?>:</label>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <label for="" class="bold"><?= labels('end_time', "End Time") ?>:</label>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <label for="" class="bold"><?= labels('duration', "Duration") ?>:</label>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <ul class="nav flex-column">
                                                                <li class="nav-item">
                                                                    <label for="" class="bold"> <?= !empty($sub_order[0]['date_of_service']) ? $sub_order[0]['date_of_service'] : "No data found" ?> </label>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <label for="" class="bold"> <?= !empty($sub_order[0]['starting_time']) ? $sub_order[0]['starting_time'] : "No data found" ?> </label>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <label for="" class="bold"> <?= !empty($sub_order[0]['ending_time']) ? $sub_order[0]['ending_time'] : "No data found" ?> </label>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <label for="" class="bold"> <?= !empty($sub_order[0]['duration']) ? $sub_order[0]['duration'] : "No data found" ?> </label>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                <?php }    ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card text-dark" style="margin-bottom: 0;border: none!important;">
                                        <div class="order_detail_headings ">
                                            <h5 class="m-0 p-2"><?= labels('provider_info', "Provider Info") ?></h5>
                                        </div>
                                        <div class="border-bottom">
                                            <div class="widget-user-header bg-info">
                                                <input type="hidden" name="hidden" id="order_id" value="id">
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <ul class="nav flex-column">
                                                        <li class="nav-item">
                                                            <label for="" class="bold"><?= labels('provider', "Provider") ?>:</label>
                                                        </li>
                                                        <li class="nav-item">
                                                            <label for="" class="bold"><?= labels('compnay_name', "Company Name") ?>:</label>
                                                        </li>
                                                        <li class="nav-item">
                                                            <label for="" class="bold"><?= labels('email', "Email") ?></label>
                                                        </li>
                                                        <li class="nav-item">
                                                            <label for="" class="bold"><?= labels('phone', "Phone") ?></label>
                                                        </li>
                                                        <li class="nav-item">
                                                            <label for="" class="bold"><?= labels('address', "Address") ?></label>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="col-md-6">
                                                    <ul class="nav flex-column">
                                                        <li class="nav-item">
                                                            <label for="" class="bold"> <?= !empty($order_details['partner']) ? $order_details['partner'] : "No data found" ?> </label>
                                                        </li>
                                                        <li class="nav-item">
                                                            <label for="" class="bold"> <?= !empty($order_details['company_name']) ? $order_details['company_name'] : "No data found" ?> </label>
                                                        </li>
                                                        <li class="nav-item">
                                                            <label for="" class="bold"> <?= !empty($personal_data['email']) ? $personal_data['email'] : "No data found" ?></label>
                                                        </li>
                                                        <li class="nav-item">
                                                            <label for="" class="bold"><?= !empty($order_details['partner_no']) ?  $order_details['partner_no'] : "No data found" ?></label>
                                                        </li>
                                                        <li class="nav-item">
                                                            <label for="" class="bold"><?= !empty($order_details['partner_address']) ? $order_details['partner_address'] : "No data found" ?></label>
                                                        </li>
                                                    </ul>
                                                </div>
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
                <?php
                $has_work_started_proof = !empty($order_details['work_started_proof']);
                $has_work_completed_proof = !empty($order_details['work_completed_proof']);
                $col_class = ($has_work_started_proof && $has_work_completed_proof) ? 'col-md-6' : 'col-md-12';
                ?>
                <?php if (!empty($order_details['work_started_proof'])) : ?>
                    <div class="<?=$col_class?> ">
                        <div class="card border border-grey  h-100">
                            <div class="card-body">
                                <div class=" text-dark" style="border: none!important;">
                                    <div class="order_detail_headings ">
                                        <h5 class="m-0 p-2"><?= labels('work_started_proof', "Work Started Proof    ") ?></h5>
                                    </div>
                                    <div class="d-flex justify-content-center m-3 row ">
                                        <?php
                                        if (empty($order_details['work_started_proof'])) { ?>
                                            <h5><?= labels('no_data_found', "No Data Found") ?></h5>
                                            <?php   } else
                                            $video_exytension = ['mov', 'mp4', 'm3u8', 'ts', '3gp', 'mov', 'avi', 'wmv'];
                                        foreach ($order_details['work_started_proof'] as $row) {
                                            $fileNameParts = explode('.', $row);
                                            $ext = end($fileNameParts);
                                            if ((in_array($ext, $video_exytension))) { ?>
                                                <div class="col-md-3 image_preview mr-4">
                                                    <button type="button" class="btn btn-primary  myBtn" id="myBtn" data-analystId=<?= $row ?>><?= labels('open_video', 'Open Video') ?></button>
                                                </div>
                                            <?php } else { ?>
                                                <div class="col-lg-3 col-md-3 col-sm-5 col-xxs-12 ">
                                                    <a href="<?php echo $row ?>" data-lightbox="image-1" class="image_preview h-100">
                                                        <img height="150px" width="120px" src="<?php echo $row ?>" alt="" style="max-width: 100%;  ">
                                                    </a>
                                                </div>
                                            <?php    } ?>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                    <div class="modal fade" id="view-video" role="dialog" aria-labelledby="view-video" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLongTitle"><?= labels('watch_video', 'Watch Video') ?></h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row ">
                                                        <div class="col-md ">
                                                            <video id="video-10" height="300px" class="w-100" controls>
                                                                <source src="movie.mp4" type="video/mp4">
                                                            </video>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endif; ?>

                <?php if (!empty($order_details['work_completed_proof'])) : ?>

                    <div class="<?=$col_class?>">
                        <div class="card border border-grey  h-100">
                            <div class="card-body">
                                <div class=" text-dark" style="border: none!important;">
                                    <div class="order_detail_headings ">
                                        <h5 class="m-0 p-2"><?= labels('work_completed_proof', "Work Completed Proof    ") ?></h5>
                                    </div>
                                    <div class="row d-flex justify-content-center m-3 ">
                                        <?php
                                        if (empty($order_details['work_completed_proof'])) { ?>
                                            <h5><?= labels('no_data_found', "No Data Found") ?></h5>
                                            <?php   } else
                                            $video_exytension = ['mov', 'mp4', 'm3u8', 'ts', '3gp', 'mov', 'avi', 'wmv'];
                                        foreach ($order_details['work_completed_proof'] as $row) {
                                            $fileNameParts = explode('.', $row);
                                            $ext = end($fileNameParts);
                                            if ((in_array($ext, $video_exytension))) { ?>
                                                <div class=" col-md-3 image_preview ">
                                                    <button type="button" class="btn btn-primary  myBtn_completed" id="myBtn_completed" data-analystId=<?= $row ?>><?= labels('open_video', 'Open Video') ?></button>
                                                </div>
                                            <?php } else { ?>
                                                <div class="col-lg-3 col-md-3 col-sm-5 col-xxs-12">
                                                    <a href="<?php echo $row ?>" data-lightbox="image-1" class="image_preview h-100">
                                                        <img height="150px" width="120px" src="<?php echo $row ?>" alt="" style="max-width: 100%;  ">
                                                    </a>
                                                </div>
                                            <?php    } ?>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                    <div class="modal fade" id="view-video" role="dialog" aria-labelledby="view-video" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLongTitle"><?= labels('watch_video', 'Watch Video') ?></h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row ">
                                                        <div class="col-md">
                                                            <video id="video-10" class="w-100" height="300px" controls>
                                                                <source src="movie.mp4" type="video/mp4">
                                                            </video>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
    </section>
</div>
<script>
    $(document).ready(function() {
        $(".myBtn").click(function() {
            var analystID = $(this).attr('data-analystId');
            var video = $("#video-10");
            const videoSource = $(this).attr('data-analystId')
            $('video source').attr('src', videoSource)
            $('video')[0].load()
            $("#view-video").modal({
                backdrop: false
            });
        });
        $(".myBtn_completed").click(function() {
            var analystID = $(this).attr('data-analystId');
            var video = $("#video-10");
            const videoSource = $(this).attr('data-analystId')
            $('video source').attr('src', videoSource)
            $('video')[0].load()
            $("#view-video").modal({
                backdrop: false
            });
        });
    });
</script>
<script>
    document.querySelector('#additional-charges-container').addEventListener('click', function(e) {
        if (e.target.classList.contains('add-button')) {
            const chargeEntry = e.target.closest('.booking_ended_additional_charge');
            const clone = chargeEntry.cloneNode(true);
            const inputs = clone.querySelectorAll('input');
            inputs.forEach(input => input.value = '');
            const currentIndex = document.querySelectorAll('.booking_ended_additional_charge').length;
            clone.querySelectorAll('input').forEach(input => {
                input.name = input.name.replace(/\d+/, currentIndex);
            });
            clone.querySelector('.add-button').style.display = 'none';
            clone.querySelector('.remove-button').style.display = 'inline-block';
            chargeEntry.parentNode.appendChild(clone);
        }
        if (e.target.classList.contains('remove-button')) {
            const chargeEntry = e.target.closest('.booking_ended_additional_charge');
            chargeEntry.remove();
        }
    });
</script>