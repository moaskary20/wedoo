<div class="main-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('addresses', "Addresses") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item"><?= labels('addresses', "Addresses") ?></a></div>
            </div>
        </div>
        <div class="row">
            <div class="container-fluid ">
                <div class="">
                    <div class="card">
                        <div class="card-body">
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
                                        <?= labels('download', "Download") ?></button>
                                    <div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 28px, 0px); top: 0px; left: 0px; will-change: transform;">
                                        <a class="dropdown-item" onclick="custome_export('pdf','Address list','list');"><?= labels('pdf', "PDF") ?></a>
                                        <a class="dropdown-item" onclick="custome_export('excel','Address list','list');"><?= labels('excel', "Excel") ?></a>
                                        <a class="dropdown-item" onclick="custome_export('csv','Address list','list')"><?= labels('csv', "CSV") ?></a>
                                    </div>
                                </div>
                            </div>
                            <table class="table " data-fixed-columns="true" id="list" data-detail-formatter="user_formater" data-auto-refresh="true" data-toggle="table" data-url="<?= base_url("admin/addresses/list") ?>" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-search="false" data-show-columns="false" data-show-columns-search="true" data-pagination-successively-size="2" data-query-params="address_query_params" data-show-refresh="false" data-sort-name="id" data-sort-order="DESC">
                                <thead>
                                    <tr>
                                        <th data-field="id" class="text-center" data-sortable="true"><?= labels('id', 'ID') ?></th>
                                        <th data-field="user_id" class="text-center" data-visible="false" data-sortable="true"><?= labels('user_id', 'User Id') ?></th>
                                        <th data-field="username" class="text-center"><?= labels('username', 'Username') ?></th>
                                        <th data-field="type" class="text-center"><?= labels('type', 'Type') ?></th>
                                        <th data-field="mobile" class="text-center"><?= labels('mobile', 'Mobile') ?></th>
                                        <th data-field="alternate_mobile" class="text-center" data-visible="false"><?= labels('alternate_mobile', 'Alternate Mobile') ?></th>
                                        <th data-field="city_name" class="text-center"><?= labels('city', 'City') ?></th>
                                        <th data-field="address" class="text-center"><?= labels('address', 'Address') ?></th>
                                        <th data-field="area" class="text-center"><?= labels('area', 'Area') ?></th>
                                        <th data-field="pincode" class="text-center" data-sortable="true"><?= labels('pincode', 'Pincode') ?></th>
                                        <th data-field="landmark" class="text-center" data-visible="false"><?= labels('landmark', 'Landmark') ?></th>
                                        <th data-field="state" class="text-center"><?= labels('state', 'State') ?></th>
                                        <th data-field="country" class="text-center" data-sortable="true"><?= labels('country', 'Country') ?></th>
                                        <th data-field="lattitude" class="text-center" data-visible="false" data-sortable="true"><?= labels('latitude', 'Lattitude') ?></th>
                                        <th data-field="longitude" class="text-center" data-visible="false" data-sortable="true"><?= labels('longitude', 'Longitude') ?></th>
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
    $("#customSearch").on('keydown', function() {
        $('#list').bootstrapTable('refresh');
    });
</script>