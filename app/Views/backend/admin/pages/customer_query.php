<div class="main-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('user_queries', "User Queries") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item"><i class="fas fa-newspaper text-warning"></i>     <?= labels('customer_queries', "Customer Queries") ?></a></div>
            </div>
        </div>
        <div class="container-fluid card">
            <div class="row mb-3">
                <div class="col-lg">
                    <table class="table " id="customer_query" data-detail-formatter="user_formater" data-auto-refresh="true" data-toggle="table" data-url="<?= base_url("admin/customer_queris_list") ?>" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-search="true" data-show-columns="true" data-show-columns-search="true" data-show-refresh="true" data-sort-name="id" data-sort-order="desc" data-toolbar="#toolbar" data-query-params="partner_list_query_params">
                        <thead>
                            <tr>
                                <th data-field="id" data-visible="false" class="text-center" data-sortable="true"><?= labels('id', 'ID') ?></th>
                                <th data-field="username" class="text-center" data-sortable="true"><?= labels('name', 'Name') ?></th>
                                <th data-field="email" class="text-center" data-sortable="true"><?= labels('email', 'Email') ?></th>
                                <th data-field="message" class="text-center" data-sortable="true"><?= labels('message', 'Message') ?></th>
                                <th data-field="subject" class="text-center" data-sortable="true"><?= labels('subject', 'Subject') ?></th>
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