<!-- Main Content -->

<?php
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
    <section class="section" id="pill-general_settings" role="tabpanel">
        <div class="section-header mt-2">
            <h1><?= labels('provider_details', 'Provider Details') ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item "><?= labels('provider_details', 'Provider Details') ?></div>
                <div class="breadcrumb-item "><?= labels('genral_outlook', 'General Outlook') ?></div>
                <div class="breadcrumb-item "><?= mb_strlen($partner['rows'][0]['company_name']) > 15 ? mb_substr($partner['rows'][0]['company_name'], 0, 15) . "..." : $partner['rows'][0]['company_name']; ?></div>
            </div>
        </div>
        <?php include "provider_details.php"; ?>
        <div class="row mt-4">
            <div class="d-flex h-auto col-xxl-6 col-xl-6 col-lg-12">
                <div class="card w-100 p-4">
                    <canvas id="sales"></canvas>
                </div>
            </div>
            <div class="m-0 p-0 px-2 col-xxl-6 col-lg-12 col-xl-6 ">
                <div class="row h-50">
                    <div class="col-md-6 ">
                        <div class="card card-statistic-1  " style=" display: flex;justify-content: center !important;">
                            <div class="card-wrap">
                                <div class="d-flex justify-content-center">
                                    <div class="provider_a ">
                                        <span class="material-symbols-outlined text-success">
                                            monetization_on
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body chart-height">
                                    <div class="d-flex justify-content-center dashboard_label">
                                        <?= $currency ?> <span class="counter"><?= $total_balance ?></span>
                                    </div>
                                    <div class="d-flex justify-content-center dashboard_small_label pb-2">
                                        <?= labels('total_earnings', 'Total Earnings') ?>
                                    </div>
                                </div>
                                <div id="total_earning_chart" style="background-color:#f8f8fa"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-statistic-1 " style=" display: flex;justify-content: center !important;">
                            <div class="card-wrap">
                                <div class="d-flex justify-content-center ">
                                    <div class="provider_a" style="box-shadow:0px 8px 26px #ffa4261a;background-color:#FFA42626">
                                        <span class="material-symbols-outlined text-warning">
                                            credit_score
                                        </span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-center ">
                                </div>
                                <div class="card-body chart-height">
                                    <div class="d-flex justify-content-center dashboard_label">
                                        <?= $currency ?><span class="counter"><?= $already_withdraw ?></span>
                                    </div>
                                    <div class="d-flex justify-content-center dashboard_small_label pb-2">
                                        <?= labels('already_withdraw', 'Already Withdraw') ?>
                                    </div>
                                </div>
                                <div id="already_withdraw_chart" style="background-color:#f8f8fa"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 ">
                        <div class="card card-statistic-1  " style=" display: flex;justify-content: center !important;">
                            <div class="card-wrap">
                                <div class="d-flex justify-content-center">
                                    <div class="provider_a " style="background-color: #cceffb;box-shadow: 0px 8px 26px #c7effa61;">
                                        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,700,0,200" />
                                        <span class="material-symbols-outlined" style="color: #00b9f0;">
                                            payments
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body chart-height">
                                    <div class="d-flex justify-content-center dashboard_label">
                                        <?= $currency ?><span class="counter"><?= $pending_withdraw ?></span>
                                    </div>
                                    <div class="d-flex justify-content-center dashboard_small_label pb-2">
                                        <?= labels('pending_withdraw', 'Pending Withdraw') ?>
                                    </div>
                                </div>
                                <div id="pending_withdraw_chart" style="background-color:#f8f8fa"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 ">
                        <div class="card card-statistic-1 " style=" display: flex;justify-content: center !important;">
                            <div class="card-wrap">
                                <div class="d-flex justify-content-center">
                                    <div class="provider_a " style="background-color: #ffdedc;box-shadow: 0px 8px 26px #ffdedd;">
                                        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,700,0,200" />
                                        <span class="material-symbols-outlined" style="color: #ff615d;">
                                            currency_exchange
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body chart-height">
                                    <div class="d-flex justify-content-center dashboard_label">
                                        <span class="counter"><?= $total_withdraw_request ?></span>
                                    </div>
                                    <div class="d-flex justify-content-center dashboard_small_label pb-2">
                                        <?= labels('withdraw_request', 'Withdraw request') ?>
                                    </div>
                                </div>
                                <div id="withdraw_request" style="background-color:#f8f8fa"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xxl-3 col-xl-3 col-lg-6 col-md-12">
                <div class="card card-statistic-1 ">
                    <div class="content d-flex">
                        <div class="provider_a  bg-danger text-light " style="box-shadow: 0px 8px 26px #fde1e2;">
                            <span class="material-symbols-outlined">
                                handyman
                            </span>
                        </div>
                        <div class="card-body my-3 p-0">
                            <span class="counter"><?= $total_services ?></span>
                            <h5 class="dashboard_small_label"><?= labels('total_services', 'Total Services') ?></h5>
                        </div>
                    </div>
                    <a href=<?= base_url('admin/partners/partner_service_details/' . $partner_id)  ?> class="text-dark">
                        <div class=" btn-lg m-3 p-2 dashboard_extra_small_label" style="background-color:#f8f8fa">
                            <?= labels('total_services', 'Total Services') ?>
                            <i class="fas fa-arrow-<?= ($is_rtl == 1) ? 'left' : 'right'; ?> mt-2" style="float: <?= ($is_rtl == 1) ? 'left' : 'right'; ?>;"></i>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-xxl-3 col-xl-3 col-lg-6 col-md-12">
                <div class="card card-statistic-1 ">
                    <div class="content d-flex">
                        <div class="provider_a  bg-info text-light" style="box-shadow: 0px 8px 26px #00b9f02e;">
                            <span class="material-symbols-outlined">
                                shopping_cart
                            </span>
                        </div>
                        <div class="card-body my-3 p-0">
                            <span class="counter"><?= '&nbsp;' . ($total_orders) ?></span>
                            <h5 class="dashboard_small_label"><?= labels('view_booking', 'View Booking') ?></h5>
                        </div>
                    </div>
                    <a href=<?= base_url('admin/partners/partner_order_details/' . $partner_id) ?> class="text-dark">
                        <div class=" btn-lg m-3 p-2 dashboard_extra_small_label" style="background-color:#f8f8fa">
                            <?= labels('view_booking', 'View Booking') ?>
                            <i class="fas fa-arrow-<?= ($is_rtl == 1) ? 'left' : 'right'; ?> mt-2" style="float: <?= ($is_rtl == 1) ? 'left' : 'right'; ?>;"></i>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-xxl-3 col-xl-3 col-lg-6 col-md-12">
                <div class="card card-statistic-1 ">
                    <div class="content d-flex">
                        <div class="provider_a bg-warning text-light " style="box-shadow: 0px 8px 26px #ffa53e24">
                            <span class="material-symbols-outlined">
                                shopping_cart
                            </span>
                        </div>
                        <div class="card-body my-3 p-0">
                            <span class="counter"><?= '&nbsp;' . ($total_promocodes) ?></span>
                            <h5 class="dashboard_small_label"><?= labels('total_promos', 'Total Promos') ?></h5>
                        </div>
                    </div>
                    <a href=<?= base_url('admin/partners/partner_promocode_details/' . $partner_id) ?> class="text-dark">
                        <div class=" btn-lg m-3 p-2 dashboard_extra_small_label" style="background-color:#f8f8fa">
                            <?= labels('total_promos', 'Total Promos') ?>
                            <i class="fas fa-arrow-<?= ($is_rtl == 1) ? 'left' : 'right'; ?> mt-2" style="float: <?= ($is_rtl == 1) ? 'left' : 'right'; ?>;"></i>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-xxl-3 col-xl-3 col-lg-6 col-md-12">
                <div class="card card-statistic-1 ">
                    <div class=" content d-flex">
                        <div class="provider_a  bg-success text-light">
                            <span class="material-symbols-outlined">
                                star_half
                            </span>
                        </div>
                        <div class="card-body my-3 p-0">
                            <span class="counter"><?= '&nbsp;' . ($total_review) ?></span>
                            <h5 class="dashboard_small_label"><?= labels('total_reviews', 'Total Reviews') ?></h5>
                        </div>
                    </div>
                    <a href=<?= base_url('admin/partners/partner_review_details/' . $partner_id) ?> class="text-dark">
                        <div class=" btn-lg m-3 p-2 dashboard_extra_small_label" style="background-color:#f8f8fa">
                            <?= labels('view_reviews', 'View Review') ?>
                            <i class="fas fa-arrow-<?= ($is_rtl == 1) ? 'left' : 'right'; ?> mt-2" style="float: <?= ($is_rtl == 1) ? 'left' : 'right'; ?>;"></i>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="section-body">
        </div>
    </section>
</div>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    $(document).ready(() => {
        if ($("#sales").length > 0) {
            var ctx = document.getElementById('sales').getContext('2d');
            var total_sale = [];
            var month_name;
            var data = [];
            $.ajax({
                type: "get",
                url: siteUrl + 'admin/partners/partner_fetch_sales/<?= $partner_id ?>',
                cache: false,
                dataType: 'json',
                success: function(result) {

                    total_sale = result.total_sale
                    month_name = result.month_name
                    var data = {
                        labels: month_name,
                        datasets: [{
                            label: 'total sale of month',
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.2)',
                                'rgba(255, 159, 64, 0.2)',
                                'rgba(255, 205, 86, 0.2)',
                                'rgba(75, 192, 192, 0.2)',
                                'rgba(54, 162, 235, 0.2)',
                                'rgba(153, 102, 255, 0.2)',
                                'rgba(201, 203, 207, 0.2)',
                                'rgba(153, 102, 255, 0.2)',
                                'rgba(201, 203, 207, 0.2)',
                                'rgba(201, 203, 207, 0.2)',
                                'rgba(201, 203, 207, 0.2)',
                                'rgba(201, 203, 207, 0.2)'
                            ],
                            borderColor: [
                                'rgb(255, 99, 132)',
                                'rgb(255, 159, 64)',
                                'rgb(255, 205, 86)',
                                'rgb(75, 192, 192)',
                                'rgb(54, 162, 235)',
                                'rgb(153, 102, 255)',
                                'rgb(201, 203, 207)',
                                'rgba(201, 203, 207, 0.2)',
                                'rgba(201, 203, 207, 0.2)',
                                'rgba(201, 203, 207, 0.2)'
                            ],
                            borderWidth: 1,
                            data: total_sale,
                        }]
                    };
                    var config = {
                        type: 'bar',
                        data: data,
                        options: {
                            scales: {
                                x: {
                                    grid: {
                                        display: false
                                    }
                                },
                                y: {
                                    grid: {
                                        display: false
                                    }
                                }
                            },
                            maintainAspectRatio: false,
                        }
                    };
                    var myChart = new Chart(
                        document.getElementById('sales'),
                        config
                    );
                }
            });
        }
    });
    jQuery(document).ready(function() {
        jQuery('#datepicker').datepicker({
            format: 'dd-mm-yyyy',
            startDate: '+1d'
        });
    });
    //total_earning_chart
    var provider_total_earning_chart = provider_total_earning_chart();
    var options = {
        series: [{
            data: provider_total_earning_chart.total_sale,
        }, ],
        labels: provider_total_earning_chart.month_name,
        yaxis: {
            labels: {
                show: false,
            },
            lines: {
                show: false,
            }
        },
        xaxis: {
            labels: {
                show: false,
            },
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            }
        },
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
                        offset: 0,
                        color: "#a7e2bd",
                        opacity: 1
                    },
                    {
                        offset: 50,
                        color: "#e9f5ee",
                        opacity: 1
                    },
                ]
            }
        },
        tooltip: {
            x: {},
            y: {
                formatter: function(val) {
                    return val.toFixed(2);
                },
                title: {
                    formatter: (seriesName) => "Earning"
                }
            }
        },
        chart: {
            height: 150,
            name: "FGM",
            type: "area",
            zoom: {
                enabled: false
            },
            toolbar: {
                show: true,
                tools: {
                    download: false
                }
            }
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
        colors: ["#1abc9c", "#2ecc71", "#3498db", "#9b59b6", "#34495e"],
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: "straight",
            colors: ['#1dc36a'],
            width: 1,
        },
        grid: {
            borderColor: " rgba(111,111,111,0.2) transparent",
        },
        markers: {
            size: 1,
            strokeColors: ['#1dc36a', '#1dc36a'],
            hover: {
                size: 1,
            }
        },
    };
    var chart1 = new ApexCharts(document.querySelector("#total_earning_chart"), options);
    chart1.render();
    var provider_already_withdraw_chart = provider_already_withdraw_chart();
    //already_withdraw_chart
    var options = {
        series: [{
            data: provider_already_withdraw_chart.total_withdraw,
        }, ],
        labels: provider_already_withdraw_chart.month_name,
        yaxis: {
            labels: {
                show: false,
            },
            lines: {
                show: false,
            }
        },
        xaxis: {
            labels: {
                show: false,
            },
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            }
        },
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
                        offset: 0,
                        color: "#ffd29f",
                        opacity: 1
                    },
                    {
                        offset: 80,
                        color: "#faf2e9",
                        opacity: 1
                    },
                ]
            }
        },
        chart: {
            height: 150,
            type: "area",
            zoom: {
                enabled: false
            },
            toolbar: {
                show: true,
                tools: {
                    download: false
                }
            }
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
        colors: ["#fdb685"],
        tooltip: {
            x: {},
            y: {
                formatter: function(val) {
                    return val.toFixed(2);
                },
                title: {
                    formatter: (seriesName) => "Withdraw Request"
                }
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: "straight",
            colors: ['#F48116'],
            width: 1,
        },
        grid: {
            borderColor: " rgba(111,111,111,0.2) transparent",
        },
        markers: {
            size: 1,
            strokeColors: ['#F48116', '#F48116'],
            hover: {
                size: 1,
            }
        },
    };
    var chart = new ApexCharts(document.querySelector("#already_withdraw_chart"), options);
    chart.render();
    var provider_pending_withdraw_chart = provider_pending_withdraw_chart();
    //pending_withdraw_chart
    var options = {
        series: [{
            data: provider_pending_withdraw_chart.pending_withdraw,
        }, ],
        labels: provider_pending_withdraw_chart.month_name,
        yaxis: {
            labels: {
                show: false,
            },
            lines: {
                show: false,
            }
        },
        xaxis: {
            labels: {
                show: false,
            },
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            }
        },
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
                        offset: 0,
                        color: "#96dbf5",
                        opacity: 1
                    },
                    {
                        offset: 100,
                        color: "#eef5fa",
                        opacity: 0.5
                    },
                    {
                        offset: 80,
                        color: "#eef5fa",
                        opacity: 1
                    },
                ]
            }
        },
        chart: {
            height: 150,
            type: "area",
            zoom: {
                enabled: false
            },
            toolbar: {
                show: true,
                tools: {
                    download: false
                }
            }
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
        colors: ["#00b9f0"],
        tooltip: {
            x: {},
            y: {
                formatter: function(val) {
                    return val.toFixed(2);
                },
                title: {
                    formatter: (seriesName) => "Pending Withdraw"
                }
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: "straight",
            colors: ['#00b9f0'],
            width: 1,
        },
        grid: {
            borderColor: " rgba(111,111,111,0.2) transparent",
        },
        markers: {
            size: 1,
            strokeColors: ['#00b9f0', '#00b9f0'],
            hover: {
                size: 1,
            }
        },
    };
    var chart = new ApexCharts(document.querySelector("#pending_withdraw_chart"), options);
    chart.render();
    //withdraw_request
    var provider_withdraw_chart = provider_withdraw_chart();
    var options = {
        series: [{
            data: provider_withdraw_chart.withdraw_request,
        }, ],
        labels: provider_withdraw_chart.month_name,
        yaxis: {
            labels: {
                show: false,
            },
            lines: {
                show: false,
            }
        },
        xaxis: {
            labels: {
                show: false,
            },
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            }
        },
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
                        color: "#ffaeac",
                        opacity: 1
                    },
                    {
                        offset: 100,
                        color: "#faedee",
                        opacity: 1
                    },
                ]
            }
        },
        chart: {
            height: 150,
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
        colors: ["#ff5552"],
        tooltip: {
            x: {},
            y: {
                formatter: function(val) {
                    return val.toFixed(2);
                },
                title: {
                    formatter: (seriesName) => "Withdraw Request"
                }
            }
        },
        markers: {
            size: 1,
            strokeColors: ['#ff5552', '#ff5552'],
            hover: {
                size: 1,
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: "straight",
            colors: ['#ff5552'],
            width: 1,
        },
        grid: {
            borderColor: " rgba(111,111,111,0.2) transparent",
        },
    };
    var chart = new ApexCharts(document.querySelector("#withdraw_request"), options);
    chart.render();

    function provider_total_earning_chart() {
        var get = <?= (json_encode($provider_total_earning_chart)); ?>;
        return get;
    }

    function provider_already_withdraw_chart() {
        var get = <?= (json_encode($provider_already_withdraw_chart)); ?>;
        return get;
    }

    function provider_pending_withdraw_chart() {
        var get = <?= (json_encode($provider_pending_withdraw_chart)); ?>;
        return get;
    }

    function provider_withdraw_chart() {
        var get = <?= (json_encode($provider_withdraw_chart)); ?>;
        return get;
    }
</script>