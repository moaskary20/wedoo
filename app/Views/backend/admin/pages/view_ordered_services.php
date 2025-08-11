<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1><?= labels('orders', 'Orders') ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('admin/dashboard') ?>"><?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item"> <a href="<?= base_url('/admin/orders'); ?>">Orders </a></div>
                <div class="breadcrumb-item disabled"> Ordered Services</div>
            </div>
        </div>
        <div class="container-fluid card">
            <h2 class="section-title">Ordered services Details</h2>

            <div class="row">
                <div class="col-md">
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <table class="table " id="ordered_services_list" data-detail-formatter="user_formater" data-auto-refresh="true" data-toggle="table" data-url="<?= base_url("admin/orders/view_ordered_services_list") ?>" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-search="true" data-show-columns="true" data-show-columns-search="true" data-show-refresh="true" data-sort-name="id" data-sort-order="desc">
                                        <thead>
                                            <tr>
                                                <th data-field="id" class="text-center" data-visible="true" data-sortable="true"><?= labels('id', 'ID') ?></th>

                                                <th data-field="order_id" class="text-center" data-visible="false" data-sortable="true"><?= labels('order_id', 'Order ID') ?></th>

                                                <th data-field="service_id" class="text-center" data-visible="false" data-sortable="true"><?= labels('service_id', 'Service ID') ?></th>

                                                <th data-field="service_title" class="text-center" data-visible="true" data-sortable="true"><?= labels('service_title', 'Service Title') ?></th>

                                                <th data-field="is_cancelable" class="text-center" data-visible="true" data-sortable="true"><?= labels('is_cancelable', 'Is Cancelable') ?></th>

                                                <th data-field="cancelable_till" class="text-center" data-visible="true" data-sortable="true"><?= labels('cancelable_till', 'cancelable Till') ?></th>

                                                <th data-field="status" class="text-center" data-sortable="true"><?= labels('order_status', 'Order Status') ?></th>

                                                <th data-field="operations" class="text-center" data-sortable="true" data-events="order_service_events">
                                                    <?= labels('operations', 'Operations') ?></th>

                                                <!--  -->
                                                <th data-field="tax_percentage" class="text-center" data-visible="false" data-sortable="true"><?= labels('tax_percentage', 'Tax Percentage') ?></th>

                                                <th data-field="tax_amount" class="text-center" data-visible="false" data-sortable="true"><?= labels('tax_amount', 'Tax Amount') ?></th>

                                                <th data-field="price" class="text-center" data-visible="false" data-sortable="true"><?= labels('price', 'Price') ?></th>

                                                <th data-field="quantity" class="text-center" data-visible="false" data-sortable="true"><?= labels('quantity', 'Quantity') ?></th>

                                                <th data-field="sub_total" class="text-center" data-visible="false" data-sortable="true"><?= labels('sub_total', 'Sub Total') ?></th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="change_order_status" tabindex="-1" role="dialog" aria-labelledby="change_order_status" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Rescheduled service</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="rescheduled_form">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="rescheduled_date">
                                <?= labels('reschedule_to', 'Reschedule To:') ?>
                            </label>
                            <input id="rescheduled_date" class="form-control" type="datetime-local" name="rescheduled_date">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success"> <?= labels('submit', 'Submit') ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>