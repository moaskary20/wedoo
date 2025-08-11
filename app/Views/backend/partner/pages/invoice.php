<div class="main-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('invoice', 'Invoice') ?></h1>
        </div>
        <?= session("message"); ?>
        <?php if (!empty($order)) { ?>
            <div class="section">
                <div class="section-body">
                    <div class="card card-flush invoice section-to-print">
                        <div class="col-md-12 text-right">
                            <div id="section-not-to-print">
                                <button type="button" value="Print this page" onclick="{window.print()};" class="btn btn-default"><i class="fa fa-print"></i> Print</button>
                            </div>
                            <div class="invoice-title d-flex justify-content-between">
                                <div class="invoice-number"><?= "#INVOC-" . $order['id'] ?></div>
                            </div>
                        </div>
                        <div class="invoice-print">
                            <div class="col-md-12 col-sm-12 d-flex justify-content-between">
                                <div>
                                    <strong><?= labels('billed_from', 'Billed From') ?>:</strong><br>
                                    <address>
                                        <?= labels('company_name', 'Company') ?> : <?= $order['company_name'] ?><br>
                                        <?= labels('addresses', 'Address') ?> : <?= $order['address'] ?><br>
                                        <?= labels('partner_no', 'Partner No') ?> : <?= $order['partner_no'] ?><br>
                                    </address>
                                </div>
                                <hr>
                                <address>
                                    <strong><?= labels('billed_to', 'Billed To') ?>:</strong><br>
                                    <?= labels('name', 'Name') ?> : <?= $order['customer'] ?><br>
                                    <?= labels('contact_no', 'Contact') ?> : <?= $order['customer_no'] ?><br>
                                    <?= labels('email', 'Email') ?> : <?= $order['customer_email'] ?><br>
                                    <div class="mt-2">
                                        <strong><?= labels('booking_date', 'Booking Date') ?>:</strong><br>
                                        <?= $order['date_of_service'] ?><br>
                                    </div>
                                    <div class="mt-2">
                                        <strong><?= labels('time', 'Time') ?>:</strong><br>
                                        <?php
                                        $date1 = $order['starting_time'];
                                        $dt = new DateTime($date1);
                                        $formatted_time = $dt->format('h:i A');
                                        ?>
                                        <?= $formatted_time ?><br>
                                    </div>
                                    <div class="mt-2">
                                        <strong><?= labels('bookings_status', 'Bookings Status') ?>:</strong><br>
                                        <?= $order['status'] ?><br><br>
                                    </div>
                                </address>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-hover table-borderd" id="invoice_table" data-show-export="true" data-export-types="['txt','excel','csv']" data-export-options='{"fileName": "invoice-order-list","ignoreColumn": ["action"]}' data-auto-refresh="true" data-toggle="table" data-search-highlight="true" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-url="<?= base_url('partner/orders/invoice_table/' . $order['id']); ?>" data-sort-order="DESC">
                                        <thead>
                                            <tr>
                                                <th data-field="service_title" data-sortable="true" data-visible="true"> <?= labels('service', 'Service') ?></th>
                                                <th data-field="price" data-visible="true"> <?= labels('price', 'Price') ?></th>
                                                <th data-field="discount" data-visible="true"> <?= labels('discount', 'discount') ?></th>
                                                <th data-field="net_amount" data-visible="true"> <?= labels('net_amount', 'New Amount') ?></th>
                                                <th data-field="tax" data-visible="true"> <?= labels('tax', 'Tax') ?></th>
                                                <th data-field="tax_amount" data-visible="true"> <?= labels('tax_amount', 'Tax Amount') ?></th>
                                                <th data-field="quantity" data-visible="true"> <?= labels('quantity', 'Quantity') ?></th>
                                                <th data-field="subtotal" data-visible="true"> <?= labels('sub_total_including_tax', 'Sub total (Including Tax)') ?></th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                </div>
            </div>
        <?php } else { ?>
            <div class="section">
                <div class="section-body">
                    <div class="invoice">
                        <div class="invoice-print">
                            <div class="row">
                                <div class="col-md-12 col-sm-12 d-flex justify-content-between">
                                    <h2 class="text-left invoice-logo">
                                        <img class="d-block img-fluid">
                                    </h2>
                                </div>
                                <h6 class="text-left">
                                    </h2>
                                    <address>
                                        <?= labels('address', 'Address') ?>: <br>
                                        <?= labels('contact_no', 'Contact') ?>: <br>
                                        <strong></strong>
                                    </address>
                                    <div class="invoice-title col-md-12 col-sm-12 d-flex justify-content-between">
                                        <h2><?= labels('invoice', 'Invoice') ?></h2>
                                    </div>
                                    <div class="invoice-number"> "#INVOC-"</div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <address>
                                                <strong><?= labels('billed_to', 'Billed To') ?>:</strong><br>
                                            </address>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <address>
                                                <strong><?= labels('payment_method', 'Payment Method') ?>:</strong><br>
                                                <br>
                                            </address>
                                        </div>
                                        <div class="col-md-6 text-md-right">
                                            <address>
                                                <strong><?= labels('order_date', 'Order Date') ?>:</strong><br>
                                                <br><br>
                                            </address>
                                        </div>
                                    </div>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="section-title"><?= labels('order_summary', 'Order Summary') ?></div>
                                <p class="section-lead">All items here cannot be deleted.</p>
                                <div class="table-responsive">
                                    <table class="table  table-hover table-md">
                                    </table>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-lg-8">
                                        <div class="section-title"><?= labels('payment_method', 'Payment Method') ?></div>
                                        <p class="section-lead">The payment method that we provide is to make it easier for you to pay invoices.</p>
                                        <div class="images">
                                        </div>
                                    </div>
                                    <div class="col-lg-4 text-right">
                                        <div class="invoice-detail-item">
                                            <div class="invoice-detail-name"><?= labels('sub_total', 'Sub total') ?></div>
                                            <div class="invoice-detail-value"></div>
                                        </div>
                                        <div class="invoice-detail-item">
                                            <div class="invoice-detail-name"><?= labels('shipping', 'Shipping') ?></div>
                                            <div class="invoice-detail-value"></div>
                                        </div>
                                        <hr class="mt-2 mb-2">
                                        <div class="invoice-detail-item">
                                            <div class="invoice-detail-name"><?= labels('total', 'Total') ?></div>
                                            <div class="invoice-detail-value invoice-detail-value-lg"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                </div>
            </div>
        <?php } ?>
    </section>
</div>