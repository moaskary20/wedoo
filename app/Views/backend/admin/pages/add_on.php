<div class="main-content">

    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('add_ons', "Add on") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item"><i class="fas fa-newspaper text-warning"></i> <?= labels('add_ons', 'Add Ons') ?></a></div>
            </div>
        </div>

        <div class="container-fluid card">
            <div class="align-items-center bg-navy card-header d-flex justify-content-between">
                <div class="bg-navy">
                    <!-- <h4><?= labels('add_ons', "Add Ons") ?></h4> -->
                </div>
                <div>
                    <a href="<?= base_url('/admin/add_ons/create_add_ons'); ?>" class="btn btn-primary"><i class="fa fa-plus"></i> <?= labels('add_ons', 'Add Ons') ?></a>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-lg">
                    <div id="toolbar" class="mt-2">
                        <div class='btn bg-emerald-blue tag text-emerald-blue mr-2' id="subscription_filter_all" name="subscription_filter" value="subscription_filter">All</div>
                        <div class='btn bg-emerald-success tag text-emerald-success mr-2' id="subscription_filter_active" name="subscription_filter_active" value="subscription_filter">Active</div>
                        <div class='btn bg-emerald-danger tag text-emerald-danger mr-2' id="subscription_filter_deactivate" name="subscription_filter_deactivate" value="subscription_filter">Deactive</div>
                    </div>
                    <table class="table " id="subscription_list" data-detail-formatter="user_formater" data-auto-refresh="true" data-toggle="table" data-url="<?= base_url("admin/subscription/list") ?>" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-search="true" data-show-columns="true" data-show-columns-search="true" data-show-refresh="true" data-sort-name="id" data-sort-order="desc" data-toolbar="#toolbar" data-query-params="partner_list_query_params">
                        <thead>
                            <tr>
                                <th data-field="id" data-visible="false" class="text-center" data-sortable="true"><?= labels('id', 'ID') ?></th>
                                <th data-field="name" class="text-center" data-sortable="true"><?= labels('name', 'Name') ?></th>
                                <th data-field="description" class="text-center" data-sortable="true"><?= labels('description', 'Description') ?></th>
                                <th data-field="duration" class="text-center" data-sortable="true"><?= labels('duration', 'Duration') ?></th>
                                <th data-field="price" class="text-center" data-sortable="true"><?= labels('price', 'Price') ?></th>
                                <th data-field="discount_price" class="text-center" data-sortable="true"><?= labels('discount_price', 'Disocunt price') ?></th>
                                <th data-field="order_type" class="text-center" data-sortable="true"><?= labels('order_type', 'Order Type') ?></th>
                                <th data-field="max_order_limit" data-visible="false" class="text-center" data-sortable="true"><?= labels('max_order_limit', 'Max Order Limit') ?></th>
                                <th data-field="service_type" class="text-center" data-sortable="true"><?= labels('service_type', 'Service Type') ?></th>
                                <th data-field="max_service_limit" data-visible="false" class="text-center" data-sortable="true"><?= labels('max_service_limit', 'Max Service Limit') ?></th>
                                <th data-field="tax_type" data-visible="false" class="text-center" data-sortable="true"><?= labels('tax_type', 'Tax Type') ?></th>
                                <th data-field="tax_id" data-visible="false" class="text-center" data-sortable="true"><?= labels('tax_id', 'Tax ID') ?></th>
                                <th data-field="is_commision_badge" class="text-center" data-sortable="true"><?= labels('is_commision', 'Commision') ?></th>
                                <th data-field="commission_threshold" data-visible="false" class="text-center" data-sortable="true"><?= labels('commission_threshold', 'Commission Threshold') ?></th>
                                <th data-field="commission_percentage" data-visible="false" class="text-center" data-sortable="true"><?= labels('commission_percentage', 'Commission Percentage') ?></th>
                                <th data-field="publish_badge" class="text-center" data-sortable="true"><?= labels('publish', 'Publish') ?></th>
                                <th data-field="status_badge" class="text-center" data-sortable="true"><?= labels('status', 'Status') ?></th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
<script>

</script>