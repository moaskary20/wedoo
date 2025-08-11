<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1><?= labels('invoice', 'Invoice') ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('admin/dashboard') ?>"><?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item"><a href="<?= base_url('admin/orders') ?>"><?= labels('booking', 'Booking') ?></a></div>
                <div class="breadcrumb-item"><?= labels('invoice', 'Invoice') ?></div>
            </div>
        </div>
        <div class="section-body">
            <div class="container-fluid card" id="print_invoice">
                <div class="card-body p-5">
                    <div class="row">
                        <div class="col-md-5">
                            <img height="100px" width="200px" class="mb-4" src="<?= isset($data['logo']) && $data['logo'] != "" ? base_url("public/uploads/site/" . $data['logo']) : base_url('public/backend/assets/img/news/img01.jpg') ?>" class="sidebar_logo w-max-90 h-max-60px" alt="">
                            <div class="row mt-5">
                                <div class="col-md partner_details">
                                    <h4 class="h4 font-weight-bolder text-body">
                                    <?= labels('service_by', 'SERVICE BY') ?>
                                    </h4>
                                    <div class="row">
                                        <div class="col-2">
                                            <div class="text-datk mr-3"><?= labels('name', 'Name') ?>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <?= $partner_details['company_name'] ?>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-2">
                                            <div class="text-datk mr-3"><?= labels('email', 'Email') ?>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <?= $partner_details['email'] ?>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-2">
                                            <div class="text-datk mr-3"><?= labels('phone', 'Phone') ?>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <?= $partner_details['phone'] ?>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-2">
                                            <div class="text-datk mr-3"><?= labels('address', 'Address') ?>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <?= $partner_details['address'] ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="col-md-1"></div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md text-sm-right left-row">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h2 class="h2 font-weight-bolder text-sm-right text-primary invoice-text"><?= labels('invoice', 'INVOICE') ?></h2>
                                        </div>
                                    </div>
                                    <div class="row text-sm-right ">
                                        <div class="col-md-9">
                                            <h6 class="h6 text-sm-right font-weight-bold invoice-text">
                                                <?= labels('invoice_no', 'Invoice no') ?>:
                                            </h6>
                                        </div>
                                        <div class="col-md">
                                            <h6 class="h6 text-sm-right invoice-text">
                                                #INVO-<?= $order['id'] ?>
                                            </h6>
                                        </div>
                                    </div>
                                    <div class="row text-sm-right ">
                                        <div class="col-md-9">
                                            <p class="h6 text-sm-right font-weight-bold invoice-text">
                                                <?= labels('invoice_date', 'Invoice Date') ?>:
                                            </p>
                                        </div>
                                        <?php
                                        $date1 =  $order['date_of_service'];
                                        $dt = new DateTime($date1);
                                        $date = $dt->format('d-m-Y');
                                        ?>
                                        <div class="col-md">
                                            <h6 class="h6 text-sm-right invoice-text">
                                                <?= $date ?>
                                            </h6>
                                        </div>
                                    </div>

                                    <div class="row text-sm-right">
                                        <div class="col-md-9">
                                            <p class="h6 text-sm-right font-weight-bold invoice-text">
                                                <?= labels('service_time', 'Service Time') ?>:
                                            </p>
                                        </div>
                                        <?php
                                        $date1 = $order['starting_time'];
                                        $dt = new DateTime($date1);
                                        $formatted_date = $dt->format('h:i A');
                                        ?>
                                        <div class="col-md">
                                            <h6 class="h6 text-sm-right invoice-text">
                                                <?= $formatted_date ?>
                                            </h6>
                                        </div>
                                    </div>




                                    <div class="row text-sm-right  invoice-text">
                                        <div class="col-md-9">
                                            <h6 class="h6 text-sm-right font-weight-bold">
                                                <?= labels('booking_status', 'Booking status') ?>:
                                            </h6>
                                        </div>
                                        <div class="col-md">
                                            <h6 class="h6 text-sm-right font-weight-bold invoice-text">
                                                <?= $order['status'] ?>
                                            </h6>
                                        </div>
                                    </div>



                                    <div class=" text-sm-right mt-5">
                                        <div class="col-md-9">

                                        </div>
                                        <div class="col-md">
                                            <h4 class="h4 font-weight-bolder text-body">
                                            <?= labels('billing_address', 'BILLING ADDRESS') ?>
                                            </h4>
                                        </div>
                                    </div>


                                    <div class="row text-sm-right ">
                                        <div class="col-md-9">
                                            <h6 class="h6 text-sm-right font-weight-bold invoice-text">
                                                <?= labels('Name', 'Name') ?>:
                                            </h6>
                                        </div>
                                        <div class="col-md">
                                            <h6 class="h6 text-sm-right invoice-text">
                                                <?= $user_details['username'] ?>
                                            </h6>
                                        </div>
                                    </div>


                                    <div class="row text-sm-right ">
                                        <div class="col-md-9">
                                            <h6 class="h6 text-sm-right font-weight-bold invoice-text">
                                                <?= labels('email', 'Email') ?>:
                                            </h6>
                                        </div>
                                        <div class="col-md">
                                            <h6 class="h6 text-sm-right invoice-text">
                                                <?= $user_details['email'] ?>
                                            </h6>
                                        </div>
                                    </div>


                                    <div class="row text-sm-right ">
                                        <div class="col-md-9">
                                            <h6 class="h6 text-sm-right font-weight-bold invoice-text">
                                                <?= labels('phone', 'Phone') ?>:
                                            </h6>
                                        </div>
                                        <div class="col-md">
                                            <h6 class="h6 text-sm-right invoice-text">
                                                <?= $user_details['phone'] ?>
                                            </h6>
                                        </div>
                                    </div>




                                </div>

                            </div>


                        </div>
                    </div>
                    <hr class="w-100 border-secondary">
                    <div class="row">
                        <div class="col-md-12 mt-3">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered table-sm invoice-table" id="invoice_table" data-show-export="true" data-export-types="['txt','excel','csv']" data-export-options='{"fileName": "invoice-order-list","ignoreColumn": ["action"]}' data-auto-refresh="true" data-toggle="table" data-search-highlight="true" data-url="<?= base_url('admin/orders/invoice_table/' . $order['id']); ?>" data-side-pagination="server">
                                    <thead class="bg-primary text-light">
                                        <tr>
                                            <th class=" border-0" data-field="service_title" data-visible="true"><?= labels('services', 'Service') ?></th>
                                            <th class=" border-0" data-field="price" data-visible="true"><?= labels('price', 'Price') ?></th>
                                            <th class=" border-0" data-field="discount" data-visible="true"><?= labels('discount', 'Discount Price') ?></th>
                                            <th class=" border-0" data-field="net_amount" data-visible="true"><?= labels('net_amount', 'Net Amount') ?></th>
                                            <th class=" border-0" data-field="tax" data-visible="true"><?= labels('tax', 'Tax') ?></th>
                                            <th class=" border-0" data-field="tax_amount" data-visible="true"><?= labels('tax_amount', 'Tax Amount') ?></th>
                                            <th class=" border-0" data-field="quantity" data-visible="true"><?= labels('quantity', 'Quantity') ?></th>
                                            <th class=" border-0" data-field="subtotal" data-visible="true"><?= labels('sub_total_including_tax', 'Sub total (Including Tax)') ?></th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                            <div class="col-md"></div>
                        </div>
                    </div>
                    <div class="row">

                        <div class="col-md ">
                            <?php $partner_details['image'] = (file_exists(FCPATH . 'public/backend/assets/profiles/' . $partner_details['image'])) ? base_url('public/backend/assets/profiles/' . $partner_details['image']) : ((file_exists(FCPATH . $partner_details['image'])) ? base_url($partner_details['image']) : ((!file_exists(FCPATH . "public/uploads/users/partners/" . $partner_details['image'])) ? base_url("public/backend/assets/profiles/default.png") : base_url("public/uploads/users/partners/" . $partner_details['image'])));
                            ?>
                            <img style="border-radius:15px;border: 0px solid" src="<?= $partner_details['image'] ?>" alt="Partner LOGO" class="mt-4 media-80">
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-s">
                            <p class="h5 mt-2  ml-1 text-dark">
                                <?= labels('thank_you_for_your_business', ' Thank you for your Business') ?>
                            </p>
                        </div>
                    </div>
                    <div class="row">
                        <div id="section-not-to-print float-right">
                            <button type="button" value="Print this page" onclick="printDiv('print_invoice')" class="btn btn-default"><i class="fa fa-print"></i> Print</button>
                        </div>
                    </div>
                </div>
            </div>
    </section>
</div>