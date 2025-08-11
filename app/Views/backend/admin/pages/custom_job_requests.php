<div class="main-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('custom_job_requests', "Custom Job Requests") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item"><?= labels('custom_job_requests', "Custom Job Requests") ?></a></div>
            </div>
        </div>
        <?= helper('form'); ?>
        <div class="row">
            <div class="col-md-12">
                <div class="container-fluid card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="row mt-4 mb-3 ">
                                    <div class="col-md-4 col-sm-2 mb-2">
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="customSearch" placeholder="<?= labels('search_here', 'Search here!') ?>" aria-label="Search" aria-describedby="customSearchBtn">
                                            <div class="input-group-append">
                                                <button class="btn btn-primary" type="button">
                                                    <i class="fa fa-search d-inline"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="dropdown d-inline ml-2">
                                        <button class="btn export_download dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <?= labels('download', 'Download') ?>
                                        </button>
                                        <div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 28px, 0px); top: 0px; left: 0px; will-change: transform;">
                                            <a class="dropdown-item" onclick="custome_export('pdf','FAQs list','user_list');"><?= labels('pdf', 'PDF') ?></a>
                                            <a class="dropdown-item" onclick="custome_export('excel','FAQs list','user_list');"><?= labels('excel', 'Excel') ?></a>
                                            <a class="dropdown-item" onclick="custome_export('csv','FAQs list','user_list')"><?= labels('csv', 'CSV') ?></a>
                                        </div>
                                    </div>
                                </div>
                                <table class="table " data-fixed-columns="true" id="user_list" data-detail-formatter="user_formater"
                                    data-auto-refresh="true" data-toggle="table"
                                    data-url="<?= base_url("admin/custom-job-requests-list") ?>" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 25, 50, 100, 200, All]"
                                    data-search="false" data-show-columns="false" data-show-columns-search="true" data-show-refresh="false" data-sort-name="id" data-sort-order="DESC"
                                    data-query-params="faqs_query_params" data-pagination-successively-size="2">
                                    <thead>
                                        <tr>
                                            <th data-field="id" class="text-center" data-sortable="true"><?= labels('id', 'ID') ?></th>
                                            <th data-field="username" class="text-center"><?= labels('username', 'Username') ?></th>
                                            <th data-field="service_title" class="text-center"><?= labels('title', 'Title') ?></th>
                                            <th data-field="truncateWords_service_short_description" class="text-center"><?= labels('short_description', 'Short Description') ?></th>
                                            <th data-field="category_name" class="text-center"><?= labels('category', 'Category') ?></th>
                                            <th data-field="total_bids" class="text-center"><?= labels('total_bids', 'Total Bids') ?></th>
                                            <th data-field="status" class="text-center"><?= labels('status', 'Status') ?></th>
                                            <th data-field="operation" class="text-center"><?= labels('view_more', 'View More') ?></th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<script>
    $("#customSearch").on('keydown', function() {
        $('#user_list').bootstrapTable('refresh');
    });
</script>