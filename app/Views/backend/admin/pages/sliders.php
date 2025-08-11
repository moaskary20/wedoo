@auth
<?php
// $user1 = fetch_details('users', ["phone" => $_SESSION['identity']],);
$db      = \Config\Database::connect();
$builder = $db->table('users u');
$builder->select('u.*,ug.group_id')
    ->join('users_groups ug', 'ug.user_id = u.id')
    ->where('ug.group_id', 1)
    ->where(['phone' => $_SESSION['identity']]);
$user1 = $builder->get()->getResultArray();
$permissions = get_permission($user1[0]['id']);
?>
<div class="main-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('sliders', "Sliders") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item"><?= labels('sliders', 'Sliders') ?></div>
            </div>
        </div>
        <div class="row">
            <?php
            if ($permissions['create']['sliders'] == 1) { ?>
                <div class="col-md-4">
                    <?= form_open('/admin/sliders/add_slider', ['method' => "post", 'class' => 'form-submit-event', 'id' => 'add', 'enctype' => "multipart/form-data"]); ?>
                    <div class="">
                        <div class="card ">
                            <div class="row m-0 border_bottom_for_cards m-0">
                                <div class="col-auto">
                                    <div class="toggleButttonPostition"><?= labels('add_new_slider', 'Add New Slider') ?></div>
                                </div>
                                <div class="col d-flex justify-content-end mr-3 mt-4">
                                    <input type="checkbox" id="slider_switch" name="slider_switch" class="status-switch" checked>
                                </div>
                            </div>


                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="type" class="required"><?= labels('type', 'Type') ?> <small> <?= labels('sliders', 'Sliders') ?></small> </label>
                                            <select id="type" class="form-control select2" name="type">
                                                <option value=""><?= labels('select_type', 'Select Type') ?> </option>
                                                <option value="default"><?= labels('default', 'Default') ?> </option>
                                                <option value="Category"><?= labels('category', 'Category') ?> </option>
                                                <option value="provider"><?= labels('provider', 'Provider') ?> </option>
                                                <option value="url"><?= labels('url', 'URL') ?> </option>

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <div class="categories" id="categories_select">
                                                <label class="required" for="Category_item"><?= labels('category', 'Category') ?></label>
                                                <select id="Category_item" class="form-control select2" name="Category_item">
                                                    <option value=""><?= labels('select', 'Select') ?> <?= labels('category', 'Category') ?></option>
                                                    <?php foreach ($categories_name as $Category) : ?>
                                                        <option value="<?= $Category['id'] ?>"><?= $Category['name'] ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="services" id="services_select">
                                                <label for="service_item" class="required"><?= labels('provider', 'Provider') ?></label>
                                                <select id="service_item" class="form-control select2" name="service_item">
                                                    <option value=""><?= labels('select', 'Select') ?> <?= labels('provider', 'Provider') ?></option>
                                                    <?php foreach ($provider_title as $provider) : ?>
                                                        <option value="<?= $provider['partner_id'] ?>"><?= $provider['company_name'] ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="" id="url_section">
                                                <label for="url" class="required"><?= labels('url', 'URL') ?></label>
                                                <input type="url" class="form-control" id="slider_url" name="url">

                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="service_item" class="required"><?= labels('app_image', 'App Image') ?></label>

                                            <small>(<?= labels('app_size_slider_recommend', 'We recommend to use 345 X 163') ?>)</small>
                                            <input type="file" name="app_image" class="filepond" id="file" accept="image/*">
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="web_image" class="required"><?= labels('web_image', 'Web Image') ?></label>
                                            <small>(<?= labels('web_size_slider_recommend', 'We recommend to use 1920 x 500') ?>)</small>
                                            <input type="file" name="web_image" class="filepond" id="file" accept="image/*">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md d-flex justify-content-end">
                                        <button type="submit" class="btn bg-new-primary submit_btn" id="add_slider"><?= labels('add_new_slider', 'Add New Slider') ?></button>
                                    </div>
                                    <?= form_close() ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <div class="col-md-8">
                <?php if ($permissions['read']['sliders'] == 1) { ?>
                    <div class="row">
                        <div class="col d-flex w-100">
                            <div class="card w-100 h-100">
                                <div class="col mb-3" style="border-bottom: solid 1px #e5e6e9;">
                                    <div class="toggleButttonPostition"><?= labels('all_sliders', 'All Sliders') ?></div>
                                </div>
                                <div class="row ml-4 px-5">
                                    <!-- <div class='btn bg-emerald-blue tag text-emerald-blue mr-2 ml-3 mb-2 filters_table' id="slider_filter" name="service_filter_all" value="">All</div> -->
                                    <div class='btn mb-2 bg-emerald-success tag text-emerald-success mr-2 filters_table' id="slider_filter_active" name="slider_filter_active" value="slider_filter"><?= labels('active', 'Active') ?></div>
                                    <div class='btn bg-emerald-danger tag text-emerald-danger mr-2 filters_table' id="slider_filter_deactive" name="slider_filter_deactive" value="slider_filter"><?= labels('deactive', 'Deactive') ?></div>
                                    <div class=" mb-2">
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="customSearch" placeholder="<?= labels('search_here', 'Search here!') ?>" aria-label="Search" aria-describedby="customSearchBtn">
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
                                    <div class="dropdown d-inline ml-2">
                                        <button class="btn export_download dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <?= labels('download', 'Download') ?>
                                        </button>
                                        <div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 28px, 0px); top: 0px; left: 0px; will-change: transform;">
                                            <a class="dropdown-item" onclick="custome_export('pdf','Slider list','slider_list');"><?= labels('pdf', 'PDF') ?></a>
                                            <a class="dropdown-item" onclick="custome_export('excel','Slider list','slider_list');"><?= labels('excel', 'Excel') ?></a>
                                            <a class="dropdown-item" onclick="custome_export('csv','Slider list','slider_list')"><?= labels('csv', 'CSV') ?></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="col-12">
                                        <table class="table " data-pagination-successively-size="2" id="slider_list" data-detail-formatter="user_formater" data-auto-refresh="true" data-toggle="table" data-url="<?= base_url("admin/sliders/list") ?>" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-search="false" data-show-columns-search="true" data-sort-name="id" data-sort-order="desc" data-query-params="slider_query_params">
                                            <thead>
                                                <tr>
                                                    <th data-field="id" class="text-center" data-visible="true" data-sortable="true"><?= labels('id', 'ID') ?></th>
                                                    <th data-field="slider_app_image" class="text-center" data-visible="true"><?= labels('app_image', 'App Image') ?></th>
                                                    <th data-field="slider_web_image" class="text-center" data-visible="true"><?= labels('web_image', 'Web Image') ?></th>
                                                    <th data-field="type" class="text-center" data-visible="true" data-sortable="true"><?= labels('type', 'type') ?></th>
                                                    <th data-field="status" class="text-center" data-sortable="true"><?= labels('status', 'Status') ?></th>
                                                    <th data-field="created_at" class="text-center" data-sortable="true"><?= labels('created_at', 'Created At') ?></th>
                                                    <th data-field="url" class="text-center" data-visible="false"><?= labels('url', 'URL') ?></th>
                                                    <th data-field="operations" class="text-center" data-events="slider_events"><?= labels('operations', 'Operations') ?></th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php  } ?>
            </div>
        </div>
</div>
</div>
<div id="filterBackdrop"></div>
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
                        <h3 class="mb-0" style="display: inline-block; font-size: 16px; margin-left: 10px;"> <?= labels('filters', 'Filters') ?> </h3>
                    </div>
                    <div id="cancelButton" style="cursor: pointer;">
                        <span class="material-symbols-outlined mr-2">
                            cancel
                        </span>
                    </div>
                </div>
                <div class="row mt-4 mx-2">
                    <div class="col-md-12">
                        <div class="form-group ">
                            <label for="table_filters"><?= labels('table_filters', 'Table filters') ?></label>
                            <div id="columnToggleContainer">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
</section>
<!-- update Modal -->
<div class="modal fade" id="update_modal" tabindex="-1" aria-labelledby="update_modal_thing" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <?= form_open('/admin/sliders/update_slider', ['method' => "post", 'class' => 'update-form-submit-event', 'id' => 'update_slider', 'enctype' => "multipart/form-data"]); ?>
            <div class="modal-header m-0 p-0" style="border-bottom: solid 1px #e5e6e9;">
                <div class="row pl-3">
                    <div class="col ">
                        <div class="toggleButttonPostition"><?= labels('edit_slider', 'Edit Slider') ?></div>
                    </div>
                </div>
                <div class="col d-flex justify-content-end mr-3 mt-4">
                    <input type="checkbox" id="edit_slider_switch" name="edit_slider_switch" class="status-switch editInModel">
                </div>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="id">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="required"><?= labels('select_type', 'Select Type') ?></label>
                            <select id="type_1" class="form-control" name="type_1">
                                <option value=""><?= labels('select_type', 'Select Type') ?> </option>
                                <option value="default"><?= labels('default', 'Default') ?> </option>
                                <option value="Category"><?= labels('category', 'Category') ?> </option>
                                <option value="provider"><?= labels('provider', 'Provider') ?> </option>
                                <option value="url"><?= labels('url', 'URL') ?> </option>

                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group ">
                            <div class="categories" id="categories_select_1">
                                <label for="Category_item" class="required"><?= labels('choose_category', 'Choose a Category') ?></label>
                                <select id="Category_item_1" class="form-control select2 " name="Category_item_1">
                                    <option value=""><?= labels('select_category', 'Select Category') ?> </option>
                                    <?php foreach ($categories_name as $Category) : ?>
                                        <option value="<?= $Category['id'] ?>"><?= $Category['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="services" id="services_select_1">
                                <label class="required" for="service_item"><?= labels('provider', 'Choose a Provider') ?></label>
                                <select id="service_item_1" class="form-control select2 select2-hidden-accessible" name="service_item_1">
                                    <option value=""><?= labels('select', 'Select') ?> <?= labels('provider', 'Provider') ?> </option>
                                    <?php foreach ($provider_title as $service) : ?>
                                        <option value="<?= $service['partner_id'] ?>"><?= $service['company_name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="" id="edit_url_section">
                                <label for="url" class="required"><?= labels('url', 'URL') ?></label>
                                <input type="url" class="form-control" id="edit_slider_url" name="url">

                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="service_item" class="required"><?= labels('app_image', 'App Image') ?>
                            </label>
                            <small>(<?= labels('app_size_recommend', 'We recommend to use 345 X 163') ?>)</small>
                            <input type="file" name="app_image" class="filepond" id="file" accept="image/*">

                        </div>
                    </div>

                    <div class="col-md-4">
                        <img src="" style="border-radius: 8px;height: 100px;width: 100px!important;" alt="old_image" id="offer_image" class="w-50">
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="web_image" class="required"><?= labels('web_image', 'Web Image') ?></label>
                            <small>(<?= labels('web_size_recommend', 'We recommend to use 1920 x 500') ?>)</small>
                            <input type="file" name="web_image" class="filepond" id="file" accept="image/*">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <img src="" style="border-radius: 8px;height: 100px;width: 100px!important;" alt="old_image" id="offer_web_image" class="w-50">
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="submit" class="btn bg-new-primary submit_btn" id=""><?= labels('edit_slider', 'Edit Slider') ?></button>
                <?php form_close() ?>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= labels('close', 'Close') ?></button>
            </div>
        </div>
    </div>
</div>
</div>
<script>
    $(document).ready(function() {
        for_drawer("#filterButton", "#filterDrawer", "#filterBackdrop", "#cancelButton");
        var dynamicColumns = fetchColumns('slider_list');
        setupColumnToggle('slider_list', dynamicColumns, 'columnToggleContainer');
    });
</script>
<script>
    var slider_filter = "";
    $("#slider_filter").on("click", function() {
        slider_filter = "";
        $("#slider_list").bootstrapTable("refresh");
    });
    $("#slider_filter_active").on("click", function() {
        slider_filter = "1";
        $("#slider_list").bootstrapTable("refresh");
    });
    $("#slider_filter_deactive").on("click", function() {
        slider_filter = "0";
        $("#slider_list").bootstrapTable("refresh");
    });
    $("#customSearch").on('keydown', function() {
        $('#slider_list').bootstrapTable('refresh');
    });

    function slider_query_params(p) {
        return {
            search: $("#customSearch").val() ? $("#customSearch").val() : p.search,
            limit: p.limit,
            sort: p.sort,
            order: p.order,
            offset: p.offset,
            slider_filter: slider_filter,
        };
    }
    $(document).ready(function() {
        $('#slider_switch').siblings('.switchery').addClass('active-content').removeClass('deactive-content');
    });

    function handleSwitchChange(checkbox) {
        var switchery = checkbox.nextElementSibling;
        if (checkbox.checked) {
            switchery.classList.add('active-content');
            switchery.classList.remove('deactive-content');
        } else {
            switchery.classList.add('deactive-content');
            switchery.classList.remove('active-content');
        }
    }
    var slider_switch = document.querySelector('#slider_switch');
    slider_switch.addEventListener('change', function() {
        handleSwitchChange(slider_switch);
    });
    var edit_slider_switch = document.querySelector('#edit_slider_switch');
    edit_slider_switch.addEventListener('change', function() {
        handleSwitchChange(edit_slider_switch);
    });
</script>
@endauth