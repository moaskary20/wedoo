<div class="main-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= $custom_job['service_title'] ?> <?= labels('bids', "Bids") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item"><?= $custom_job['service_title'] ?> <?= labels('bids', "Bids") ?></a></div>
            </div>
        </div>
        <?= helper('form'); ?>
        <div class="row">
            <div class="col-md-12">
                <div class="container-fluid card">
                    <div class="col-xl-12 col-md-12 col-sm-12 mb-sm-2 mt-4">
                        <div class="service_info">
                            <span class="title"><?= labels('description', 'Description') ?></span>
                            <p class="m-0">
                                <span id="shortDescription1"><?= substr($custom_job['service_short_description'], 0, 100) ?></span>
                                <span id="fullDescription1" style="display: none;"><?= substr($custom_job['service_short_description'], 100) ?></span>
                                <span id="dots1">...</span>
                                <a href="javascript:void(0)" id="readMoreLink1" onclick="toggleDescription(1)"><?= labels('read_more', 'Read more') ?></a>
                            </p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="row mt-4 mb-3 ">
                                    <div class="col-md-4 col-sm-2 mb-2">
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="customSearch" placeholder="Search here!" aria-label="Search" aria-describedby="customSearchBtn">
                                            <div class="input-group-append">
                                                <button class="btn btn-primary" type="button">
                                                    <i class="fa fa-search d-inline"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="dropdown d-inline ml-2">
                                        <button class="btn export_download dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            Download
                                        </button>
                                        <div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 28px, 0px); top: 0px; left: 0px; will-change: transform;">
                                            <a class="dropdown-item" onclick="custome_export('pdf','FAQs list','user_list');">PDF</a>
                                            <a class="dropdown-item" onclick="custome_export('excel','FAQs list','user_list');">Excel</a>
                                            <a class="dropdown-item" onclick="custome_export('csv','FAQs list','user_list')">CSV</a>
                                        </div>
                                    </div>
                                </div>
                                <table class="table " data-fixed-columns="true" id="user_list" data-detail-formatter="user_formater"
                                    data-auto-refresh="true" data-toggle="table"
                                    data-url="<?= base_url("admin/custom-job/bidders-list/" . $custom_job['id']) ?>" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 25, 50, 100, 200, All]"
                                    data-search="false" data-show-columns="false" data-show-columns-search="true" data-show-refresh="false" data-sort-name="id" data-sort-order="DESC"
                                    data-query-params="faqs_query_params" data-pagination-successively-size="2">
                                    <thead>
                                        <tr>
                                            <th data-field="id" class="text-center" data-sortable="true"><?= labels('id', 'ID') ?></th>
                                            <th data-field="provider_name" class="text-center"><?= labels('provider', 'Provider') ?></th>
                                            <th data-field="counter_price" class="text-center"><?= labels('counter_price', 'Counter Price') ?></th>
                                            <th data-field="duration" class="text-center"><?= labels('duration', 'Duration') ?></th>
                                            <th data-field="truncateWords_note" class="text-center"><?= labels('note', 'Note') ?></th>
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
<script>
    function toggleDescription(section) {
        var shortDescription = $("#shortDescription" + section);
        var fullDescription = $("#fullDescription" + section);
        var dots = $("#dots" + section);
        var readMoreLink = $("#readMoreLink" + section);
        if (fullDescription.is(":visible")) {
            fullDescription.hide();
            dots.show();
            readMoreLink.text("<?= labels('read_more', 'Read more') ?>");
        } else {
            fullDescription.show();
            dots.hide();
            readMoreLink.text("<?= labels('read_less', 'Read less') ?>");
        }
    }
</script>