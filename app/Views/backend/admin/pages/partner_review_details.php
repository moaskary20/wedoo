<!-- Main Content -->
<div class="main-content">
    <section class="section" id="pill-general_settings" role="tabpanel">
        <div class="section-header mt-2">
            <h1><?= labels('partner_details', 'Partner Details') ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item "><?= labels('partner_details', 'Partner Details') ?></div>
                <div class="breadcrumb-item "><?= labels('reviews', 'Reviews') ?></div>
                <div class="breadcrumb-item "><?= $partner['rows'][0]['company_name'] ?></div>
            </div>
        </div>
        <?php include "provider_details.php"; ?>
        <div class="section-body">
            <div class="card card_progress_bar col h-100">
                <div class="row grid-divider">
                    <div class="col-md-2 my-3">
                        <div class="align-items-center card h-100 p-2 d-flex justify-content-center align-items-center">
                            <div class="rating_round">
                                <span class="text-white" style="font-size: 24px;"><?= $ratingData[0]['average_rating'] ?></span>
                            </div>
                            <div class="partner-rating mt-3" id="<?= rand() ?>" data-value="<?= $ratingData[0]['average_rating'] ?>"></div>
                            <div class="mt-3 " style="font-size: 16px;"><strong><?= $ratingData[0]['total_ratings'] ?> <?= labels('reviews', 'Reviews') ?></strong></div>
                        </div>
                    </div>
                    <div class="col-md-10">
                        <div class="mt-4">
                            <div class="mb-2 mb-3 mx-2 row">
                                <div class="">
                                    <i class="fa-solid fa-star text-white"></i>
                                    <span class="ml-2 mr-2 text-white">5</span>
                                </div>
                                <div class="col mt-2">
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" aria-valuenow="<?= $ratingData[0]['rating_5_percentage'] ?>" aria-valuemin="0" aria-valuemax="100" style="width:<?= $ratingData[0]['rating_5_percentage'] ?>%">
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-0 text-white">
                                    <?= $ratingData[0]['rating_5'] ?>
                                </div>
                            </div>
                            <div class="mb-2 mb-3 mx-2 row">
                                <div class="">
                                    <i class="fa-solid fa-star text-white"></i>
                                    <span class="ml-2 mr-2 text-white">4</span>
                                </div>
                                <div class="col mt-2">
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" aria-valuenow="<?= $ratingData[0]['rating_4_percentage'] ?>" aria-valuemin="0" aria-valuemax="100" style="width:<?= $ratingData[0]['rating_4_percentage'] ?>%">
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-0 text-white">
                                    <?= $ratingData[0]['rating_4'] ?>
                                </div>
                            </div>
                            <div class="mb-2 mb-3 mx-2 row">
                                <div class="">
                                    <i class="fa-solid fa-star text-white"></i>
                                    <span class="ml-2 mr-2 text-white">3</span>
                                </div>
                                <div class="col mt-2">
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" aria-valuenow="<?= $ratingData[0]['rating_3_percentage'] ?>" aria-valuemin="0" aria-valuemax="100" style="width:<?= $ratingData[0]['rating_3_percentage'] ?>%">
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-0 text-white">
                                    <?= $ratingData[0]['rating_3'] ?>
                                </div>
                            </div>
                            <div class="mb-2 mb-3 mx-2 row">
                                <div class="">
                                    <i class="fa-solid fa-star text-white"></i>
                                    <span class="ml-2 mr-2 text-white">2</span>
                                </div>
                                <div class="col mt-2">
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" aria-valuenow="<?= $ratingData[0]['rating_2_percentage'] ?>" aria-valuemin="0" aria-valuemax="100" style="width:<?= $ratingData[0]['rating_2_percentage'] ?>%">
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-0 text-white">
                                    <?= $ratingData[0]['rating_2'] ?>
                                </div>
                            </div>
                            <div class="mb-2 mb-3 mx-2 row">
                                <div class="">
                                    <i class="fa-solid fa-star text-white"></i>
                                    <span class="ml-2 mr-2 text-white">1</span>
                                </div>
                                <div class="col mt-2">
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" aria-valuenow="<?= $ratingData[0]['rating_1_percentage'] ?>" aria-valuemin="0" aria-valuemax="100" style="width:<?= $ratingData[0]['rating_1_percentage'] ?>%">
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-0 text-white">
                                    <?= $ratingData[0]['rating_1'] ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="output-status"></div>
            <div class="row mt-3">
                <div class="col-md-12 col-sm-12 col-xl-12   ">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="row mt-4 mb-3">
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
                                <button class="btn btn-secondary  ml-2 filter_button" id="filterButton">
                                    <span class="material-symbols-outlined mt-1">
                                        filter_alt
                                    </span>
                                </button>
                                <div class="d-inline dropdown ml-2 mr-3">
                                    <button class="btn export_download dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <?= labels('download', 'Download') ?>
                                    </button>
                                    <div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 28px, 0px); top: 0px; left: 0px; will-change: transform;">
                                        <a class="dropdown-item" onclick="custome_export('pdf','Rating list','rating_table');"> <?= labels('pdf', 'PDF') ?> </a>
                                        <a class="dropdown-item" onclick="custome_export('excel','Rating list','rating_table');"> <?= labels('excel', 'Excel') ?> </a>
                                        <a class="dropdown-item" onclick="custome_export('csv','Rating list','rating_table')"> <?= labels('csv', 'CSV') ?> </a>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover table-borderd" data-pagination-successively-size="2" id="rating_table" data-show-export="true" data-query-params="rating_query_params" data-export-types="['txt','excel','csv']" data-export-options='{"fileName": "invoice-order-list","ignoreColumn": ["action"]}' data-auto-refresh="true" data-show-columns="false" data-search="false" data-show-refresh="false" data-toggle="table" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-side-pagination="server" data-pagination="true" data-url="<?= base_url("admin/partners/partner_review_details_list/" . $partner['rows'][0]['partner_id']) ?>" data-sort-name="id" data-sort-order="desc">
                                    <thead>
                                        <tr>
                                            <th data-field="id" class="text-center" data-visible="false" data-sortable="true"><?= labels('id', 'ID') ?></th>
                                            <th data-field="service_name" class="text-center" data-visible="true" data-sortable="true"><?= labels('service_name', 'Service') ?></th>
                                            <th data-field="user_name" class="text-center" data-visible="true"><?= labels('user_name', 'User') ?></th>
                                            <th data-field="comment" class="text-center" data-visible="true"><?= labels('comment', 'Comment') ?></th>
                                            <th data-field="rated_on" class="text-center" data-visible="true"><?= labels('rated_on', 'Rated On') ?></th>
                                            <th data-field="view_images" class="text-center" data-visible="true"><?= labels('images', 'Images') ?></th>
                                            <th data-field="stars"><?= labels('rating', 'Rating') ?></th>
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
</div>
<div class="drawer" id="filterDrawer">
    <section class="section">
        <div class="row">
            <div class="col-md-12">
                <div class="bg-new-primary" style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center;">
                        <div class="bg-white m-3 text-new-primary" style="box-shadow: 0px 8px 26px #00b9f02e; display: inline-block; padding: 10px; height: 45px; width: 45px; border-radius: 15px;">
                            <span class="material-symbols-outlined">
                                filter_alt
                            </span>
                        </div>
                        <h3 class="mb-0" style="display: inline-block; font-size: 16px; margin-left: 10px;"><?= labels('filters', 'Filters') ?></h3>
                    </div>
                    <div id="cancelButton" style="cursor: pointer;">
                        <span class="material-symbols-outlined mr-2">
                            cancel
                        </span>
                    </div>
                </div>
                <div class="row mt-4 mx-2">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="rating_star_filter"><?= labels('rating_star_filter', 'Filter Rating by Star') ?></label>
                            <select name="rating_star_filter" id="rating_star_filter" class="form-control select2">
                                <option value=""><?= labels('select', 'Select') ?>-</option>
                                <option value="1"><?= labels('1', '1') ?></option>
                                <option value="2"><?= labels('2', '2') ?></option>
                                <option value="3"><?= labels('3', '3') ?></option>
                                <option value="4"><?= labels('4', '4') ?></option>
                                <option value="5"><?= labels('5', '5') ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group ">
                            <label for="table_filters"><?= labels('table_filters', 'Table filters') ?></label>
                            <div id="columnToggleContainer">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-4 ">
                    <div class="col-md-4 ml-2">
                        <button class="btn bg-new-primary d-block" id="filter">
                            <?= labels('apply_filter', 'Apply Filter') ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<div id="viewImages" class="modal fade" tabindex="-1" aria-labelledby="viewImagesLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewImagesLabel"><?= labels('images', 'Images') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Images will be injected here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= labels('close', 'Close') ?></button>
            </div>
        </div>
    </div>
</div>
<script>
    $("#customSearch").on('keydown', function() {
        $('#rating_table').bootstrapTable('refresh');
    });
    var rating_star_filter = "";
    $("#rating_star_filter").on("change", function() {
        rating_star_filter = $(this).find("option:selected").val();
    });

    function rating_query_params(p) {
        return {
            search: $("#customSearch").val() ? $("#customSearch").val() : p.search,
            limit: p.limit,
            sort: p.sort,
            order: p.order,
            offset: p.offset,
            rating_star_filter: rating_star_filter,
        };
    }
    $("#filter").on("click", function(e) {
        $("#rating_table").bootstrapTable("refresh");
    });
    $(document).ready(function() {
        for_drawer("#filterButton", "#filterDrawer", "#filterBackdrop", "#cancelButton");
        var columns = [{
                field: 'id',
                label: '<?= labels('id', 'ID') ?>',
                visible: false
            },
            {
                field: 'service_name',
                label: '<?= labels('service_name', 'Service') ?>'
            },
            {
                field: 'user_name',
                label: '<?= labels('user_name', 'User') ?>'
            },
            {
                field: 'comment',
                label: '<?= labels('comment', 'Comment') ?>',
                visible: false
            },
            {
                field: 'rated_on',
                label: '<?= labels('category_name', 'Category Name') ?>',
            },
        ];
        setupColumnToggle('rating_table', columns, 'columnToggleContainer');
    });
    $(document).ready(function() {
    $(document).on('click', '.view-rating-images', function() {
        var imagesData = $(this).data('images');
        displayImages(imagesData);
    });
});

function displayImages(imagesData) {
    var imagesHtml = '';
    if (Array.isArray(imagesData) && imagesData.length > 0) {
        imagesData.forEach(function(url) {
            imagesHtml += '<a href="' + url + '" data-lightbox="image-1"><img height="80px" class="rounded m-1" src="' + url + '" alt="Rating Image"></a>';
        });
    } else {
        imagesHtml = '<p>No images available.</p>';
    }
    $('#modalBody').html(imagesHtml);
    $('#viewImages').modal('show');
    
    // Reinitialize lightbox if you're using one
    if (typeof lightbox !== 'undefined') {
        lightbox.option({
            'resizeDuration': 200,
            'wrapAround': true
        });
    }
}
    function imageFormatter(value, row) {
        if (value && value.length > 0) {
            return '<button class="btn btn-sm btn-primary view-rating-images" data-images=\'' + JSON.stringify(value) + '\'>View Images</button>';
        }
        return 'No Images';
    }
</script>