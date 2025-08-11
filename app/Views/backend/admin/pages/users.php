<!-- Main Content -->
<div class="main-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('customers', 'Customers') ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item"><?= labels('customers', 'Customers') ?></div>
            </div>
        </div>
        <section>
            <div class="row mt-4">
                <div class="col-md-12 col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row mt-4 mb-3">
                                <div class='btn bg-emerald-blue tag text-emerald-blue mr-2 ml-3 mb-2 filters_table' id="customer_filter" name="service_filter_all" value=""> <?= labels('all', 'All') ?></div>
                                <div class='btn bg-emerald-success tag text-emerald-success mr-2 filters_table' id="customer_filter_active" name="customer_filter_filter_active" value="customer_filter"> <?= labels('active', 'Active') ?></div>
                                <div class='btn bg-emerald-danger tag text-emerald-danger mr-2 filters_table' id="customer_filter_deactive" name="customer_filter_filter_deactive" value="customer_filter"> <?= labels('deactive', 'Deactive') ?></div>
                                <div class="col-md-6 col-sm-2 mb-2">
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
                                        <a class="dropdown-item" onclick="custome_export('pdf','Customer list','user_list');"> <?= labels('pdf', 'PDF') ?> </a>
                                        <a class="dropdown-item" onclick="custome_export('excel','Customer list','user_list');"> <?= labels('excel', 'Excel') ?> </a>
                                        <a class="dropdown-item" onclick="custome_export('csv','Customer list','user_list',['Profile', 'User Status','Operations']);"> <?= labels('csv', 'CSV') ?> </a>
                                    </div>
                                </div>
                            </div>
                            <table class="table " data-fixed-columns="true" id="user_list" data-pagination-successively-size="2" data-detail-formatter="user_formater" data-auto-refresh="true" data-toggle="table" data-url="<?= base_url("admin/list-user") ?>" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-search="false" data-show-columns="false" data-show-columns-search="true" data-show-refresh="false" data-sort-name="id" data-sort-order="desc" data-query-params="customer_query_params">
                                <thead>
                                    <tr>
                                        <th data-field="id" class="text-center" data-visible="false" data-sortable="true"><?= labels('id', 'ID') ?></th>
                                        <th data-field="image" class="text-center"><?= labels('profile', 'Profile') ?></th>
                                        <!-- <th data-field="profile" data-visible="false" class="text-center"><?= labels('profile', 'Profile') ?></th> -->
                                        <th data-field="username" class="text-center" data-sortable="true"><?= labels('user_name', 'User Name') ?></th>
                                        <th data-field="phone" class="text-center"><?= labels('mobile', 'Mobile') ?></th>
                                        <th data-field="email" class="text-center"><?= labels('email', 'Email') ?></th>
                                        <th data-field="active" class="text-center" data-sortable="true"><?= labels('user_status', 'User Status') ?></th>


                                        <th data-field="operations" class="text-center" data-events="user_events"><?= labels('operations', 'Operations') ?></th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </section>
    <!-- To deactivate given selected user -->
    <div class="modal fade" id="deactivate_user_modal" tabindex="-1" role="dialog" aria-labelledby="deactivate_user_modal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle"><?= labels('deactivate_user', 'Deactivate user') ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="<?= base_url('admin/users/deactivate') ?>" method="post" id="deactivate_user_form">
                        <input type="hidden" name="user_id" id="user_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= labels('close', 'Close') ?></button>
                    <button type="submit" class="btn btn-primary" id="deactive_btn"><?= labels('deactivate_user', 'Deactivate user') ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- To activate given selected user -->
    <div class="modal fade" id="activate_user_modal" tabindex="-1" role="dialog" aria-labelledby="activate_user_modal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle"><?= labels('activate_user', 'Activate User') ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="<?= base_url('admin/users/activate') ?>" method="post" id="activate_user_form">
                        <input type="hidden" name="user_id" id="user_id_active">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= labels('close', 'Close') ?></button>
                    <button type="submit" class="btn btn-primary" id="activate_btn"><?= labels('activate_user', 'Activate User') ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $("#customSearch").on('keydown', function() {
        $('#user_list').bootstrapTable('refresh');
    });
    var customer_filter = "";
    $("#customer_filter").on("click", function() {
        customer_filter = "";
        $("#user_list").bootstrapTable("refresh");
    });
    $("#customer_filter_active").on("click", function() {
        customer_filter = "1";
        $("#user_list").bootstrapTable("refresh");
    });
    $("#customer_filter_deactive").on("click", function() {
        customer_filter = "0";
        $("#user_list").bootstrapTable("refresh");
    });
    $("#customSearch").on('keydown', function() {
        $('#user_list').bootstrapTable('refresh');
    });

    function customer_query_params(p) {
        return {
            search: $("#customSearch").val() ? $("#customSearch").val() : p.search,
            limit: p.limit,
            sort: p.sort,
            order: p.order,
            offset: p.offset,
            customer_filter: customer_filter,
        };
    }
</script>