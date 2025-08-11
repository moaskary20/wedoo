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
        <section class="section">
            <div class="row mt-3">
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
                                        <div class="provider_a mr-2">
                                            <i class="material-icons text-success">monetization_on</i>
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
                                        <div class="provider_a mr-2" style="box-shadow:0px 8px 26px #ffa4261a;background-color:#FFA42626">
                                            <i class="material-icons text-warning">credit_score</i>
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
                                        <div class="provider_a mr-2" style="background-color: #cceffb;box-shadow: 0px 8px 26px #c7effa61;">
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
                                        <div class="provider_a mr-2" style="background-color: #ffdedc;box-shadow: 0px 8px 26px #ffdedd;">
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
                <div class="m-0 p-0 px-2 col-xxl-6 col-lg-12 col-xl-6 ">
                    <div class="row h-50">
                        <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12">
                            <div class="card card-statistic-1 ">
                                <div class="content d-flex">
                                    <div class="provider_a  mr-2 bg-danger text-light " style="box-shadow: 0px 8px 26px #fde1e2;">
                                        <i class="material-icons ">handyman</i>
                                    </div>
                                    <div class="card-body my-3 p-0">
                                        <span class="counter"><?= $total_services ?></span>
                                        <h5 class="dashboard_small_label"><?= labels('total_services', 'Total Services') ?></h5>
                                    </div>
                                </div>
                                <a href=<?= base_url("partner/services") ?> class="text-dark">
                                    <div class=" btn-lg m-3 p-2 dashboard_extra_small_label" style="background-color:#f8f8fa">
                                        <?= labels('total_services', 'Total Services') ?>
                                        <i class="fas fa-arrow-<?= ($is_rtl == 1) ? 'left' : 'right'; ?> mt-2" style="float: <?= ($is_rtl == 1) ? 'left' : 'right'; ?>;"></i>

                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12">
                            <div class="card card-statistic-1 ">
                                <div class="content d-flex">
                                    <div class="provider_a  mr-2  bg-info text-light" style="box-shadow: 0px 8px 26px #00b9f02e;">
                                        <i class="material-icons ">shopping_cart</i>
                                    </div>
                                    <div class="card-body my-3 p-0">
                                        <span class="counter"><?= '&nbsp;' . ($total_orders) ?></span>
                                        <h5 class="dashboard_small_label"><?= labels('view_booking', 'View Booking') ?></h5>
                                    </div>
                                </div>
                                <a href=<?= base_url("partner/orders") ?> class="text-dark">
                                    <div class=" btn-lg m-3 p-2 dashboard_extra_small_label" style="background-color:#f8f8fa">
                                        <?= labels('view_booking', 'View Booking') ?>
                                        <i class="fas fa-arrow-<?= ($is_rtl == 1) ? 'left' : 'right'; ?> mt-2" style="float: <?= ($is_rtl == 1) ? 'left' : 'right'; ?>;"></i>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12">
                            <div class="card card-statistic-1 ">
                                <div class="content d-flex">
                                    <div class="provider_a mr-2   bg-warning text-light " style="box-shadow: 0px 8px 26px #ffa53e24">
                                        <i class="material-icons ">percent</i>
                                    </div>
                                    <div class="card-body my-3 p-0">
                                        <span class="counter"><?= '&nbsp;' . ($total_promocodes) ?></span>
                                        <h5 class="dashboard_small_label"><?= labels('total_promos', 'Total Promos') ?></h5>
                                    </div>
                                </div>
                                <a href=<?= base_url("partner/promo_codes") ?> class="text-dark">
                                    <div class=" btn-lg m-3 p-2 dashboard_extra_small_label" style="background-color:#f8f8fa">
                                        <?= labels('total_promos', 'Total Promos') ?>
                                        <i class="fas fa-arrow-<?= ($is_rtl == 1) ? 'left' : 'right'; ?> mt-2" style="float: <?= ($is_rtl == 1) ? 'left' : 'right'; ?>;"></i>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12">
                            <div class="card card-statistic-1 ">
                                <div class=" content d-flex">
                                    <div class="provider_a mr-2  bg-success text-light">
                                        <i class="material-icons ">star_half</i>
                                    </div>
                                    <div class="card-body my-3 p-0">
                                        <span class="counter"><?= '&nbsp;' . ($total_review) ?></span>
                                        <h5 class="dashboard_small_label"><?= labels('total_reviews', 'Total Reviews') ?></h5>
                                    </div>
                                </div>
                                <a href=<?= base_url("partner/review") ?> class="text-dark">
                                    <div class=" btn-lg m-3 p-2 dashboard_extra_small_label" style="background-color:#f8f8fa">
                                        <?= labels('view_reviews', 'View Review') ?>
                                        <i class="fas fa-arrow-<?= ($is_rtl == 1) ? 'left' : 'right'; ?> mt-2" style="float: <?= ($is_rtl == 1) ? 'left' : 'right'; ?>;"></i>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex h-auto col-xxl-6 col-xl-6 col-lg-12">
                    <div class="card w-100 ">
                        <div class="card-header gap-10 d-flex justify-content-between align-items-center p-0">
                            <div class="d-flex gap-4 align-items-center">
                                <div>
                                    <div class="provider_a  mr-2  bg-primary text-light">
                                        <i class="material-icons ">work</i>
                                    </div>
                                    <!-- <i class="fas fa-briefcase text-primary" style="font-size: 40px;"></i> Adjust the color if necessary -->
                                </div>
                                <div>
                                    <h5 class="m-0">
                                        <span class="dashboard_large_label"><?= labels('job_requests_for_you', "Job request’s for you") ?></span>
                                    </h5>
                                    <span class="small"><?= ($total_custom_job_requests) ?> <?= labels('requests', 'Requests') ?></span>
                                </div>
                            </div>
                            <div>
                                <a href=<?= base_url('partner/JobRequests/') ?> class="text-dark">
                                    <div class=" btn-lg m-3 p-2 dashboard_extra_small_label" style="background-color:#f8f8fa">
                                        <?= labels('view_all', 'View All') ?>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php foreach ($custom_job_requests as $request) : ?>
                                <div>
                                    <div class="title text-dark font-weight-bold">
                                        <?= $request['service_title'] ?>
                                    </div>
                                    <div class="desc text-muted">
                                        <?= $request['service_short_description'] ?>
                                    </div>
                                    <div class="d-flex">
                                        <img src="<?= base_url('public/backend/assets/profiles/' . $request['image']); ?>" class="job_request_category_image" alt="">
                                        <p> <?= $request['username'] ?>
                                        </p>
                                    </div>
                                </div>
                                <hr class="mt-0">
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 col-sm-12">
                    <div class="card d-flex h-100  pb-0 recent-activities">
                        <div class="card-header d-flex justify-content-between gap-10">
                            <h5>
                                <span class="dashboard_large_label"><?php
                                                                    if (!empty($promocode_dates)) {
                                                                        echo  labels('upcoming_promos', 'Upcoming Promo');
                                                                    } else {
                                                                        echo labels('no_promocode_found ', 'No Promocode Found');
                                                                    }
                                                                    ?></span>
                            </h5>
                        </div>
                        <ul class="common-list  " style="padding: 18px;">
                            <?php
                            if (!empty($promocode_dates)) {
                                $count = 0;
                                $colors = ['bg-danger bg-danger-boxshadow', 'bg-info bg-info-boxshadow', 'bg-warning bg-warning-boxshadow', 'bg-success bg-success-boxshadow'];
                                foreach ($promocode_dates as $key => $promocode) {
                                    if ($count >= count($colors)) {
                                        $count = 0;
                                    }
                            ?>
                                    <li class=" d-flex  gap-2 align-items-center justify-content-between pb-3">
                                        <div class="media align-items-center gap-3">
                                            <div class="provider_b <?= $colors[$count] ?> text-light p-1" style="display:block;">
                                                <?php
                                                $date = explode('-', $promocode['start_date']);
                                                ($date[0] == '01') ? $date[0] = "Jan" : "";
                                                ($date[0] == '02') ? $date[0] = "Feb" : "";
                                                ($date[0] == '03') ? $date[0] = "Mar" : "";
                                                ($date[0] == '04') ? $date[0] = "April" : "";
                                                ($date[0] == '05') ? $date[0] = "May" : "";
                                                ($date[0] == '06') ? $date[0] = "Jun" : "";
                                                ($date[0] == '07') ? $date[0] = "Jul" : "";
                                                ($date[0] == '08') ? $date[0] = "Aug" : "";
                                                ($date[0] == '09') ? $date[0] = "Sep" : "";
                                                ($date[0] == '10') ? $date[0] = "Oct" : "";
                                                ($date[0] == '11') ? $date[0] = "Nov" : "";
                                                ($date[0] == '12') ? $date[0] = "Dec" : "";
                                                ?>
                                                <p class="view_promocode"><?php echo $date[1] ?> </p>
                                                <p class="view_promocode "> <?php echo $date[0] ?></p>
                                            </div>
                                            <div class="media-body  ">
                                                <div class="promocode-h5"><?php echo $promocode['promo_code'] ?></div>
                                                <p class="m-0"><?php echo $promocode['end_date'] ?></p>
                                            </div>
                                        </div>
                                        <a href="<?= base_url("partner/promo_codes") ?>" class="btn  btn-sm action-button  bg-new-primary text-white p-2" title="view the promocode">
                                            <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,300,1,0" />
                                            <i class="material-symbols-outlined">
                                                visibility
                                            </i>
                                        </a>
                                    </li>
                            <?php
                                    $count = $count + 1;
                                }
                            } else {
                            }
                            ?>
                    </div>
                    </ul>
                </div>
                <div class="col-md-8 col-sm-12 ">
                    <div class="card h-100 p-3">
                        <div class="row">
                            <div class="col-md-12 ">
                                <div class="ml-0">
                                    <div class=" h-full">
                                        <div class="mb-6">
                                            <div class="flex items-center mb-2 text-dark">
                                                <h5>
                                                    <span class="dashboard_large_label"><?= labels('recent_booking', 'Recent Booking') ?></span>
                                                </h5>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-3">
                                                <input type="date" id="daily_quiz_date" name="daily_quiz_date" placeholder="<?= labels('select_date', 'Select date') ?>" class="form-control">
                                            </div>
                                            <div class="col-sm-3">
                                            </div>
                                            <div class="col-6">
                                                <h5>
                                                    <span class="dashboard_large_label d-flex dashboard_small_label justify-content-end text-dark"><?= labels('recent_booking', 'Recent Booking') ?> :
                                                        <span class="" id="recent_booking"></span></span>
                                                </h5>
                                            </div>
                                        </div>
                                        <table class="table  " id="new-order" data-detail-formatter="user_formater" data-auto-refresh="true" data-toggle="table" data-url="<?= base_url("partner/orders/newList") ?>" data-side-pagination="server" data-sort-name="id" data-sort-order="DESC" data-query-params="new_order_query_params" data-show-export="true" data-toggle="table" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-side-pagination="server">
                                            <thead>
                                                <tr>
                                                    <th data-field="customer" class="text-center" data-sortable="true"><?= labels('customer', 'Customer') ?></th>
                                                    <th data-field="partner" class="text-center" data-sortable="true" data-visible="false"><?= labels('provider', 'Provider') ?></th>
                                                    <th data-field="date_of_service" class="text-center"><?= labels('date_of_service', 'Date of Service') ?></th>
                                                    <th data-field="final_total" class="text-center" data-visible="true"><?= labels('final_total', 'Final total') ?></th>
                                                    <th data-field="status" class="text-center"><?= labels('status', 'Status') ?></th>
                                                    <th data-field="operations" class="text-center" data-events="orders_events"><?= labels('operations', 'Operations') ?></th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
        </section>
    </div>
    <script>
        filter_date = "";
        $('#daily_quiz_date').on('change', function(e) {
            filter_date = $('#daily_quiz_date').val();
            $('#new-order').bootstrapTable('refresh');
        });

        function new_order_query_params(p) {
            return {
                filter_date: filter_date,
                limit: 5,
                sort: p.sort,
                order: p.order,
                offset: p.offset,
                search: p.search
            };
        }
        $("#daily_quiz_date").on("input", function() {
            if ($(this).val().length > 0) {
                $(this).addClass("full");
            } else {
                $(this).removeClass("full");
            }
        });
    </script>
    <script>
        $(document).ready(() => {
            if ($("#sales").length > 0) {
                var ctx = document.getElementById('sales').getContext('2d');
                var total_sale = [];
                var month_name;
                var data = [];
                $.ajax({
                    type: "get",
                    url: siteUrl + '/partner/dashboard/fetch_sales',
                    cache: false,
                    dataType: 'json',
                    success: function(result) {

                        total_sale = result.total_sale
                        month_name = result.month_name
                        var data = {
                            labels: month_name,
                            datasets: [{
                                label: '<?= labels('total_sale_of_month', 'total sale of month') ?>',
                                backgroundColor: [
                                    'rgba(255, 99, 132, 0.2)',
                                    'rgba(255, 159, 64, 0.2)',
                                    'rgba(255, 205, 86, 0.2)',
                                    'rgba(75, 192, 192, 0.2)',
                                    'rgba(54, 162, 235, 0.2)',
                                    'rgba(153, 102, 255, 0.2)',
                                    'rgba(201, 203, 207, 0.2)',
                                    //---
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
                                    //---
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
            if ($("#pieChart").length > 0) {
                var ctx = document.getElementById('pieChart').getContext('2d');
                $.ajax({
                    type: "get",
                    url: siteUrl + '/partner/dashboard/fetch_data',
                    cache: false,
                    dataType: 'json',
                    success: function(result) {
                        var category = ''
                        Object.keys(result.category).map((key) => {
                            labels = result.category[key];
                            category = labels.split(",")
                        });
                        const data = {
                            labels: result.category,
                            datasets: [{
                                label: 'sale',
                                data: result.counter,
                                backgroundColor: [
                                    'rgb(255, 99, 132)',
                                    'rgb(255, 159, 64)',
                                    'rgb(255, 205, 86)',
                                    'rgb(75, 192, 192)',
                                    'rgb(54, 162, 235)',
                                    'rgb(153, 102, 255)',
                                    'rgb(201, 203, 207)'
                                ],
                                hoverOffset: 4
                            }]
                        };
                        const config = {
                            type: 'doughnut',
                            data: data,
                        };
                        const myChart = new Chart(
                            document.getElementById('pieChart'),
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
    </script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        $('#new-order').on('load-success.bs.table', function(data) {
            var numRecords = $('#new-order').bootstrapTable('getData').length;
            $("#recent_booking").text(numRecords);
        })
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