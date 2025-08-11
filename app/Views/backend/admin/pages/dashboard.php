<!-- Main Content -->
<?php

$db      = \Config\Database::connect();
$builder = $db->table('users u');
$builder->select('u.*,ug.group_id')
    ->join('users_groups ug', 'ug.user_id = u.id')
    ->where('ug.group_id', 1)
    ->where(['phone' => $_SESSION['identity']]);
$user1 = $builder->get()->getResultArray();
$permissions = get_permission($user1[0]['id']);
$current_url = current_url();
$session = \Config\Services::session();
$is_rtl = $session->get('is_rtl');
$language = $session->get('language');
$default_language = fetch_details('languages', ['is_default' => '1']);

// Only check default language's RTL status if no language is set in session
// Otherwise, use the explicit is_rtl value from session
if (empty($language) && !isset($is_rtl)) {
    $is_rtl = $default_language[0]['is_rtl'];
} elseif ($is_rtl === null) {
    // Fallback if is_rtl is not set but language is
    $is_rtl = 0;
}

// Convert to integer value for consistency
$is_rtl = (int)$is_rtl;
?>


<div class="main-content">
    <div id="filterBackdrop"></div>

    <section class="section mt-4">
        <div class="row mb-2">
            <div class="m-0 col-xxl-6 col-lg-12 col-xl-6 ">
                <div class="row ">
                    <div class="col-lg-6 col-md-12 col-xl-6 col-xxl-3 mb-30 ">
                        <div class="card h-100 mb-30 p-2 bg-new-primary">
                            <div class="business-summary-earning mt-1 mx-2">
                                <div class="mb-2" id="greeting"></div>
                                <h5 class=" pt-1"><?= labels('hello', "Hello ") ?>, <?= $user1[0]['username'] ?></h5>
                                <div class="pt-4"><?= labels('view_current_sale', "View Your Current Sales & Summary.") ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-12">
                        <div class="card card-statistic-1 " style="padding: 20px;">
                            <div class="content d-flex">
                                <div class="provider_a mr-2  bg-emerald-success text-light " style="box-shadow: 0px 8px 26px #47C36326;margin: 0;padding: 0;">
                                    <i class="fas fa-user text-emerald-success" style="    font-size: 24px;"></i>
                                </div>
                                <div class="card-body my-3 p-0" style="margin-left: 20px!important;">
                                    <span class="counter"><?= $total_customers ?></span>
                                    <h5 class="dashboard_small_label">
                                        <?= labels('total_customer', "Total Customer") ?></h5>
                                </div>
                            </div>
                            <a href=<?= base_url("admin/users") ?> class="text-dark">
                                <div class=" btn-lg  dashboard_extra_small_label" style="background-color:#f8f8fa">
                                    <?= labels('total_customer', "Total Customer") ?>
                                    <i class="fas fa-arrow-<?= ($is_rtl == 1) ? 'left' : 'right'; ?> mt-2" style="float: <?= ($is_rtl == 1) ? 'left' : 'right'; ?>;"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-12">
                        <div class="card card-statistic-1 " style="padding: 20px;">
                            <div class="content d-flex">
                                <div class="provider_a mr-2   bg-emerald-blue text-light " style="box-shadow: 0px 8px 26px #3ABAF426;margin: 0;padding: 0;">
                                    <i class="fas fa-shopping-bag text-emerald-blue " style="    font-size: 24px;"> </i>
                                </div>
                                <div class="card-body my-3 p-0" style="margin-left: 20px!important;">
                                    <span class="counter"><?= $total_on_sale_service ?></span>
                                    <h5 class="dashboard_small_label">
                                        <?= labels('total_services', "Total Services") ?></h5>
                                </div>
                            </div>
                            <a href=<?= base_url("admin/services") ?> class="text-dark">
                                <div class=" btn-lg  dashboard_extra_small_label" style="background-color:#f8f8fa">
                                    <?= labels('total_services', "Total Services") ?></h5>
                                    <i class="fas fa-arrow-<?= ($is_rtl == 1) ? 'left' : 'right'; ?> mt-2" style="float: <?= ($is_rtl == 1) ? 'left' : 'right'; ?>;"></i>

                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-12">
                        <div class="card card-statistic-1 " style="padding: 20px;">
                            <div class="content d-flex">
                                <div class="provider_a mr-2   bg-emerald-warning text-warning " style="box-shadow: 0px 8px 26px #FFA42626;margin: 0;padding: 0;">
                                    <i class="fas fa-shopping-bag text-emerald-warning " style="    font-size: 24px;"> </i>
                                </div>
                                <div class="card-body my-3 p-0" style="margin-left: 20px!important;">
                                    <span class="counter"><?= $total_orders ?></span>
                                    <h5 class="dashboard_small_label">
                                        <?= labels('order_statistic', "Order Statistics") ?></h5>
                                </div>
                            </div>
                            <a href=<?= base_url("admin/orders") ?> class="text-dark">
                                <div class=" btn-lg  dashboard_extra_small_label" style="background-color:#f8f8fa">
                                    <?= labels('order_statistic', "Order Statistics") ?></h5>
                                    <i class="fas fa-arrow-<?= ($is_rtl == 1) ? 'left' : 'right'; ?> mt-2" style="float: <?= ($is_rtl == 1) ? 'left' : 'right'; ?>;"></i>

                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex h-auto col-xxl-6 col-xl-6 col-lg-12">
                <div class="card w-100  ">
                    <div class="row pl-3" id="total">
                        <div class="col  ">
                            <div class="toggleButttonPostition_dashboard" id="income_revenue"></div>
                        </div>
                        <div class="col-md-2  p-30 d-flex justify-content-end">
                            <div class='btn  tag tag_custome  mr-2 ' id="income_revenue_filter_total" name="income_revenue_filter" value="income_revenue_filter"> <?= labels('total', "Total") ?></div>
                            <div class='btn  tag tag_custome mr-2' id="income_revenue_filter_admin" name="income_revenue_filter" value="income_revenue_filter"> <?= labels('admin', "Admin") ?></div>
                            <div class='btn  tag tag_custome mr-2' id="income_revenue_filter_provider" name="income_revenue_filter" value="income_revenue_filter"> <?= labels('provider', "Provider") ?></div>
                        </div>
                    </div>
                    <div id="chart" style="background-color: rgb(248, 248, 250);">
                    </div>
                    <div id="admin_income_revenue_chart" style="background-color: rgb(248, 248, 250);">
                    </div>
                    <div id="provider_income_revenue_chart" style="background-color: rgb(248, 248, 250);">
                    </div>
                </div>
            </div>
        </div>
        <div class="row ">
            <div class="col-lg-8 col-md-12 col-sm-12">
                <div class="col-md-12 p-0">
                    <?php if (empty($rating_wise_rating_data) && empty($rating_data)) { ?>
                        <div class="card px-4">
                            <div class="row">
                                <div class="col-md-12 d-flex justify-content-center">

                                    <div class="empty-state" data-height="400" style="height: 400px;">
                                        <div class="empty-state-icon bg-primary">
                                            <i class="fas fa-question text-white "></i>
                                        </div>
                                        <h2><?= labels('we_couldnt_find_any_providers', "We couldn't find any Providers") ?></h2>
                                        <p class="lead">
                                            <?= labels("sorry_we_cant_find_any_data", "Sorry we can't find any data, to get rid of this message, make at least 1 entry") ?>.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="card px-4">
                            <div class="row  mt-3">
                                <div class="col mb-3 ">
                                    <div class="toggleButttonPostition_dashboard m-0"><?= labels('top_providers', 'Top  Providers') ?></div>
                                </div>
                                <div class="col-md-2 mb-3 d-flex justify-content-end">
                                    <select name="filter_top_providers" id="filter_top_providers" class="form-control dashboard_design select2">
                                        <option value="orders"><?= labels('orders', 'Orders') ?></option>
                                        <option value="rating"><?= labels('rating', 'Ratings') ?></option>
                                    </select>
                                </div>
                            </div>

                            <?php
                            if (count($rating_data['data']) > 0 && count($rating_wise_rating_data['data']) > 0) { ?>

                                <div class="swiper mySwiper m-5 mt-1" id="order_wise_provider" style="padding:0!important ;margin:0!important">
                                    <div class="swiper-wrapper mt-3 ">
                                        <?php
                                        foreach ($rating_data['data'] as $row) {
                                        ?>
                                            <div class="swiper-slide">
                                                <a href="<?= base_url('/admin/partners/general_outlook/' . $row['partner_id'])  ?>" class=" my-5 provider_card" style="text-decoration: none;">

                                                    <?php
                                                    if (!empty($row['image'])) { ?>
                                                        <img src="<?= ((!empty(($row['image'])))) ? $row['image'] :  base_url('public/backend/assets/images/no-pictures.png') ?>" height="100px" width="100px" alt="">
                                                    <?php } else { ?>
                                                        <img src="<?= base_url('public/backend/assets/images/no-pictures.png') ?>" height="100px" width="100px" alt="">
                                                    <?php    }
                                                    ?>
                                                    <h6 class="m-3" style="word-break:break-word;"><?= mb_strlen($row['company_name']) > 15 ? mb_substr($row['company_name'], 0, 15) . "..." :  $row['company_name']; ?></h6>
                                                    <div class="row">
                                                        <div class="partner-rating" id="<?= $row['id'] ?>" data-value="<?= $row['ratings'] ?>"></div>
                                                        <span class="small">
                                                            (<?= $row['ratings'] ?>)
                                                        </span>
                                                    </div>
                                                    <button type="button" class="btn bg-new-primary  left-icon-holder m-4" onclick="this.classList.add('active')" style="border-radius:8px;" style="word-break:break-word;">


                                                        <i class="fa fa-check-circle mr-3"></i>
                                                        &nbsp;&nbsp;&nbsp;
                                                        <?= $row['number_of_orders'] ?> <?= labels('order_completed', 'Order Completed') ?>
                                                    </button>
                                                </a>
                                            </div>
                                        <?php  }
                                        ?>
                                    </div>
                                </div>

                                <div class="swiper mySwiper m-5 mt-1" id="rating_wise_provider" style="padding:0!important ;margin:0!important">
                                    <div class="swiper-wrapper mt-3 ">
                                        <?php
                                        foreach ($rating_wise_rating_data['data'] as $row) { ?>
                                            <div class="swiper-slide">

                                                <a href="<?= base_url('/admin/partners/general_outlook/' . $row['partner_id'])  ?>" class=" my-5 provider_card" style="text-decoration: none;">
                                                    <?php
                                                    if (!empty($row['image'])) { ?>
                                                        <img src="<?= (!empty(($row['image']))) ? $row['image'] : base_url('public/backend/assets/images/no_image_avaialble.jpg')  ?>" height="100px" width="100px" alt="">
                                                    <?php } else { ?>
                                                        <img src="<?= base_url('public/backend/assets/images/no_image_avaialble.jpg') ?>" height="100px" width="100px" alt="">
                                                    <?php    }
                                                    ?>
                                                    <h6 class="m-3" style="word-break:break-word;"><?= mb_strlen($row['company_name']) > 15 ? mb_substr($row['company_name'], 0, 15) . "..." :  $row['company_name']; ?></h6>
                                                    <div class="row">
                                                        <div class="partner-rating" id="order_<?= $row['id'] ?>" data-value="<?= $row['ratings'] ?>"></div>
                                                        <span class="small">
                                                            (<?= $row['ratings'] ?>)
                                                        </span>
                                                    </div>
                                                    <button type="button" class="btn bg-new-primary  left-icon-holder m-4" style="word-break:break-word;">
                                                        <i class="fa fa-check-circle mr-3"></i>
                                                        &nbsp;&nbsp;&nbsp;
                                                        <?= $row['number_of_orders'] ?> <?= labels('order_completed', 'Order Completed') ?>
                                                    </button>
                                                </a>
                                            </div>
                                        <?php  }
                                        ?>
                                    </div>
                                </div>


                            <?php } else { ?>

                                <div class="row">
                                    <div class="col-md-12 d-flex justify-content-center">

                                        <div class="empty-state" data-height="400" style="height: 400px;">
                                            <div class="empty-state-icon bg-primary">
                                                <i class="fas fa-question text-white "></i>
                                            </div>
                                            <h2><?= labels('we_couldnt_find_any_data', "We couldn't find any data") ?></h2>
                                            <p class="lead">
                                                <?= labels('sorry_we_cant_find_any_data', "Sorry we can't find any data, to get rid of this message, make at least 1 entry.") ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>


                            <?php    } ?>
                        </div>
                    <?php } ?>
                </div>
                <div class=" col-md-12 p-0">
                    <div class="card p-3">
                        <div class="row">
                            <div class="col mb-3">
                                <div class="toggleButttonPostition m-0"> <span class="text-dark"><?= labels('recent_booking', 'Recent Booking') ?> :
                                        <span class="" id="recent_booking"></span></span>
                                </div>
                            </div>
                        </div>
                        <table class="table " id="user_list" width="100%" data-detail-formatter="user_formater" data-trim-on-search="false" data-click-to-select="true" data-toggle="table" data-url="<?= base_url("admin/dashboard/recent_booking") ?>" data-side-pagination="server" d data-sort-name="id" data-sort-order="DESC" data-query-params="orders_query" data-mobile-responsive="true" data-responsive="true">
                            <thead>
                                <tr>
                                    <th data-field="customer" class="text-center"><?= labels('customer', 'Customer') ?></th>
                                    <th data-field="partner" class="text-center"><?= labels('provider', 'Provider') ?></th>
                                    <th data-field="new_start_time_with_date" class="text-center"><?= labels('starting_time', 'Starting time') ?></th>
                                    <th data-field="new_end_time_with_date" class="text-center"><?= labels('ending_time', 'Ending time') ?></th>
                                    <th data-field="status" class="text-center"><?= labels('status', 'Status') ?></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-12 col-sm-12 mb-30">
                <div class="card w-100 h-100">
                    <div class="row pl-3 m-0" style="border-bottom: solid 1px #e5e6e9;">
                        <div class="col mb-3 ">
                            <div class="toggleButttonPostition_dashboard"><?= labels('top_trending_services', "Top Trending Services") ?></div>
                        </div>
                        <div class="col-md-4 mt-3 mb-3 mr-3 d-flex justify-content-end">
                            <select name="filter_trending_services" id="filter_trending_services" class="form-control dashboard_design select2">
                                <option value=""><?= labels('all', 'All') ?></option>
                                <?php foreach ($categories as $row) { ?>
                                    <option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
                                <?php       } ?>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php
                        $disk = fetch_current_file_manager();

                        if (empty($top_trending_services)) { ?>
                            <div class="row">
                                <div class="col-md-12 d-flex justify-content-center">

                                    <div class="empty-state" data-height="400" style="height: 400px;">
                                        <div class="empty-state-icon bg-primary">
                                            <i class="fas fa-question text-white "></i>
                                        </div>
                                        <h2><?= labels('we_couldnt_find_any_data', "We couldn't find any data") ?></h2>
                                        <p class="lead">
                                            <?= labels('sorry_we_cant_find_any_data', "Sorry we can't find any data, to get rid of this message, make at least 1 entry.") ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php  } else { ?>
                            <ul class="common-list m-0" id="trending_service">
                                <?php
                                foreach ($top_trending_services as $service) {   ?>
                                    <li class="d-flex flex-wrap gap-2 align-items-center justify-content-between" style="cursor: pointer">
                                        <div class="media align-items-center gap-3">
                                            <div class="avatar avatar-lg " style="border-radius:8px!important">
                                                <?php
                                                if (!empty($service['image'])) {
                                                ?>

                                                    <?php

                                                    if ($disk == "local_server") {
                                                        $image_url = base_url($service['image']);
                                                    } else if ($disk == "aws_s3") {
                                                        $image_url = fetch_cloud_front_url('services', $service['image']);
                                                    }
                                                    ?>
                                                    <img src="<?= $image_url  ?>" height="100px" width="100px" alt="" class="avatar-img rounded">
                                                <?php } else { ?>
                                                    <img src="<?= base_url('public/backend/assets/images/no-pictures.png') ?>" height="100px" width="100px" alt="" class="avatar-img rounded">
                                                <?php    }
                                                ?>

                                            </div>
                                            <div class="media-body mt-3 ml-3">
                                                <b> <?= $service['title'] ?></b>
                                                <p>
                                                    <?php
                                                    if ($service['discounted_price'] != 0) {
                                                        echo $currency . $service['discounted_price'];
                                                        echo '<s class="ml-2 mr-2">' . $currency . $service['price'] . '</s>';
                                                    } else {
                                                        echo '<span class="ml-2 mr-2 mt-0">' . $currency . $service['price'] . '</span>';
                                                    }
                                                    ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class='tag border-0 rounded-md bg-new-primary'><i class="fa fa-check-circle mr-2"></i>
                                            <?= $service['order_data'] ?>
                                        </div>
                                    </li>
                            <?php    }
                            }
                            ?>
                            </ul>
                    </div>
                </div>
            </div>
        </div>
</div>
</section>
</div>
<script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    var swiper = new Swiper(".mySwiper", {
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        slidesPerView: 3,
        spaceBetween: 20,
        breakpoints: {
            320: {
                slidesPerView: 1,
                spaceBetween: 30,
            },
            640: {
                slidesPerView: 1,
                spaceBetween: 30,
            },
            768: {
                slidesPerView: 3,
                spaceBetween: 30,
            },
            769: {
                slidesPerView: 2,
                spaceBetween: 30,
            },
            1020: {
                slidesPerView: 2.1,
                spaceBetween: 30,
            },
            1200: {
                slidesPerView: 2,
                spaceBetween: 30,
            },
            1564: {
                slidesPerView: 3,
                spaceBetween: 40,
            },
        }
    });
    //total revenue
    var income_revenue = income_revenue();
    var options = {
        series: [{
            data: income_revenue.income_revenue,
        }, ],
        labels: income_revenue.month_name,
        dropShadow: {
            enabled: true,
            color: "#000"
        },
        fill: {
            type: "gradient",
            gradient: {
                shadeIntensity: 5,
                type: "vertical",
                colorStops: [{
                        offset: 20,
                        color: "#a0def6",
                        opacity: 1
                    },
                    {
                        offset: 100,
                        color: "#e4f2f9",
                        opacity: 1
                    },
                ]
            }
        },
        chart: {
            height: 250,

            type: "area",
            zoom: {
                enabled: false
            },
            toolbar: {
                show: true,
                tools: {
                    download: false
                }
            },
        },
        noData: {
            text: "No data",
            align: "center",
            verticalAlign: "middle",
        },
        responsive: [{
            breakpoint: 400,
            options: {
                chart: {
                    width: 200,
                },
                legend: {
                    position: 'left',
                    horizontalAlign: 'right',
                }
            }
        }],
        colors: ["#a0def6"],
        tooltip: {
            x: {

            },
            y: {
                formatter: function(val) {
                    return val.toFixed(2);
                },
                title: {
                    formatter: (seriesName) => "<?= labels('income_revenue', 'Income Revenue') ?>"
                }
            }
        },
        markers: {
            size: 1,
            strokeColors: ['#a0def6', '#a0def6'],
            hover: {
                size: 1,
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: "straight",
            colors: ['#a0def6'],
            width: 1,
        },
        grid: {
            borderColor: " rgba(111,111,111,0.2) transparent",
        },
    };
    var chart = new ApexCharts(document.querySelector("#chart"), options);
    chart.render();

    function income_revenue() {
        var get = <?= (json_encode($income_revenue)); ?>;
        return get;
    }
    //admin income revenue chart start -----------------------------------------
    var admin_income_revenue = admin_income_revenue();
    var options = {
        series: [{
            data: admin_income_revenue.income_revenue,
        }, ],
        labels: admin_income_revenue.month_name,
        dropShadow: {
            enabled: true,
            color: "#000"
        },
        fill: {
            type: "gradient",
            gradient: {
                shadeIntensity: 5,
                type: "vertical",
                colorStops: [{
                        offset: 20,
                        color: "#a0def6",
                        opacity: 1
                    },
                    {
                        offset: 100,
                        color: "#e4f2f9",
                        opacity: 1
                    },
                ]
            }
        },
        chart: {
            height: 250,

            type: "area",
            zoom: {
                enabled: false
            },
            toolbar: {
                show: true,
                tools: {
                    download: false
                }
            },
        },
        noData: {
            text: "No data",
            align: "center",
            verticalAlign: "middle",
        },
        responsive: [{
            breakpoint: 400,
            options: {
                chart: {
                    width: 200,
                },
                legend: {
                    position: 'left',
                    horizontalAlign: 'right',
                }
            }
        }],
        colors: ["#a0def6"],
        tooltip: {
            x: {

            },
            y: {
                formatter: function(val) {
                    return val.toFixed(2);
                },
                title: {
                    formatter: (seriesName) => "<?= labels('admin_income_revenue', 'Admin Income Revenue') ?>"
                }
            }
        },
        markers: {
            size: 1,
            strokeColors: ['#a0def6', '#a0def6'],
            hover: {
                size: 1,
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: "straight",
            colors: ['#a0def6'],
            width: 1,
        },
        grid: {
            borderColor: " rgba(111,111,111,0.2) transparent",
        },
    };
    var admin_chart = new ApexCharts(document.querySelector("#admin_income_revenue_chart"), options);
    admin_chart.render();

    function admin_income_revenue() {
        var get = <?= (json_encode($admin_income_revenue)); ?>;
        return get;
    }
    //admin income revenue chart end----------------------------------------------
    //provider income revenue chart start -----------------------------------------
    var provider_income_revenue = provider_income_revenue();
    var options = {
        series: [{
            data: provider_income_revenue.income_revenue,
        }, ],
        labels: provider_income_revenue.month_name,
        dropShadow: {
            enabled: true,
            color: "#000"
        },
        fill: {
            type: "gradient",
            gradient: {
                shadeIntensity: 5,
                type: "vertical",
                colorStops: [{
                        offset: 20,
                        color: "#a0def6",
                        opacity: 1
                    },
                    {
                        offset: 100,
                        color: "#e4f2f9",
                        opacity: 1
                    },
                ]
            }
        },
        chart: {
            height: 250,

            type: "area",
            zoom: {
                enabled: false
            },
            toolbar: {
                show: true,
                tools: {
                    download: false
                }
            },
        },
        noData: {
            text: "No data",
            align: "center",
            verticalAlign: "middle",
        },
        responsive: [{
            breakpoint: 400,
            options: {
                chart: {
                    width: 200,
                },
                legend: {
                    position: 'left',
                    horizontalAlign: 'right',
                }
            }
        }],
        colors: ["#a0def6"],
        tooltip: {
            x: {

            },
            y: {
                formatter: function(val) {
                    return val.toFixed(2);
                },
                title: {
                    formatter: (seriesName) => "<?= labels('provider_income_revenue', 'Provider Income Revenue') ?>"
                }
            }
        },
        markers: {
            size: 1,
            strokeColors: ['#a0def6', '#a0def6'],
            hover: {
                size: 1,
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: "straight",
            colors: ['#a0def6'],
            width: 1,
        },
        grid: {
            borderColor: " rgba(111,111,111,0.2) transparent",
        },
    };
    var provider_income_revenue_chart = new ApexCharts(document.querySelector("#provider_income_revenue_chart"), options);
    provider_income_revenue_chart.render();

    function provider_income_revenue() {
        var get = <?= (json_encode($provider_income_revenue)); ?>;
        return get;
    }
    //provider income revenue chart end----------------------------------------------
    const greeting = document.getElementById('greeting');
    const hour = new Date().getHours();
    const welcomeTypes = ['<?= labels('good_morning', 'Good morning') ?>', '<?= labels('good_afternoon', 'Good afternoon') ?>', '<?= labels('good_evening', 'Good evening') ?>'];
    let welcomeText = '';
    if (hour < 12) welcomeText = welcomeTypes[0];
    else if (hour < 18) welcomeText = welcomeTypes[1];
    else welcomeText = welcomeTypes[2];
    greeting.innerHTML = welcomeText;
    $('#user_list').on('load-success.bs.table', function(data) {
        // ...
        var numRecords = $('#user_list').bootstrapTable('getData').length;
        $("#recent_booking").text(numRecords);
    })
    $(function() {
        $('#order_wise_provider').show();
        $('#rating_wise_provider').hide();
        $('#filter_top_providers').change(function() {
            if ($('#filter_top_providers').val() == 'orders') {
                $('#order_wise_provider').show();
                $('#rating_wise_provider').hide();
            } else {
                $('#rating_wise_provider').show();
                $('#order_wise_provider').hide();
            }
        });
    });
    $(function() {
        $('#provider_income_revenue_chart').hide();
        $('#admin_income_revenue_chart').hide();
        $('#income_revenue_filter_total').addClass("tag_active");
        $('#admin_income_revenue').hide();
        $('#provider_income_revenue').hide();
        const income_revenue = document.getElementById('income_revenue');
        const income_revenue_types = ['<?= labels('income_revenue', 'Income Revenue') ?>', ' <?= labels('admin_income_revenue', 'Admin Income Revenue') ?>', '<?= labels('provider_income_revenue', 'Provider Income Revenue') ?>'];
        let welcoincome_revenue_types_Text = '';
        income_revenue.innerHTML = income_revenue_types[0];

        $("#income_revenue_filter_admin").click(function() {
            $('#admin_income_revenue_chart').show();
            $('#provider_income_revenue_chart').hide();
            $('#chart').hide();
            $('#income_revenue_filter_admin').addClass("tag_active");
            $('#income_revenue_filter_provider').removeClass("tag_active");
            $('#income_revenue_filter_total').removeClass("tag_active");
            income_revenue.innerHTML = income_revenue_types[1];
        });
        $("#income_revenue_filter_provider").click(function() {
            $('#admin_income_revenue_chart').hide();
            $('#provider_income_revenue_chart').show();
            $('#chart').hide();
            $('#income_revenue_filter_provider').addClass("tag_active");
            $('#income_revenue_filter_admin').removeClass("tag_active");
            $('#income_revenue_filter_total').removeClass("tag_active");
            income_revenue.innerHTML = income_revenue_types[2];
        });
        $("#income_revenue_filter_total").click(function() {
            $('#admin_income_revenue_chart').hide();
            $('#provider_income_revenue_chart').hide();
            $('#chart').show();
            $('#income_revenue_filter_total').addClass("tag_active");
            $('#income_revenue_filter_admin').removeClass("tag_active");
            $('#income_revenue_filter_provider').removeClass("tag_active");
            income_revenue.innerHTML = income_revenue_types[0];
        });
    });
    $('#filter_trending_services').change(function() {
        var trending_filter = $('#filter_trending_services').val();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url: baseUrl + '/admin/dashboard/top_trending_services',
            type: 'POST',
            data: {
                data_trending_filter: trending_filter,
            },
            dataType: 'JSON',
            success: function(response) {

                let html = "";
                $.each(response.data, function(index, item) {
                    html += ' <li class="d-flex flex-wrap gap-2 align-items-center justify-content-between" style="cursor: pointer">' +
                        '<div class="media align-items-center gap-3">' +
                        ' <div class="avatar avatar-lg">' +
                        '<img class="avatar-img rounded" src="' + baseUrl + '/' + item.image + '" alt="">' +
                        '</div>' +
                        '<div class="media-body mt-3 ml-3">' +
                        '<b>' + item.title + '</b>' +
                        '<p>' + item.discounted_price + '<s class="ml-2">' + item.price + '</s></p>' +
                        ' </div>' +
                        ' </div>' +
                        '<div class="tag border-0 rounded-md bg-new-primary"><i class="fa fa-check-circle mr-2"></i>' + item.order_data +
                        '</div>' +
                        '</li>';
                });
                $('#trending_service').html(html);
            },
            error: function(err) {

            },
        });
    });
</script>