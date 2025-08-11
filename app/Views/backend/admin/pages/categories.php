<?php
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
            <h1><?= labels('categories', "Categories") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item"> <?= labels('category', 'Categories') ?></a></div>
            </div>
        </div>
        <div class="row">
            <?php
            if ($permissions['create']['categories'] == 1) { ?>
                <div class="col-md-6 ">
                    <div class="card">
                        <?= helper('form'); ?>
                        <?= form_open('/admin/category/add_category', ['method' => "post", 'class' => 'form-submit-event', 'id' => 'add_Category', 'enctype' => "multipart/form-data"]); ?>


                        <div class="row m-0">
                            <div class="col mb-3 " style="border-bottom: solid 1px #e5e6e9;">
                                <div class="toggleButttonPostition"><?= labels('category', 'Category') ?></div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="category_name" class="required"><?= labels('name', 'Name') ?></label>
                                        <input id="category_name" class="form-control" type="text" name="name" placeholder="<?= labels('enter_name_of_category', 'Enter the name of the Category here') ?>">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="category_slug" class="required"><?= labels('slug', 'Slug') ?></label>
                                        <input id="category_slug" class="form-control" type="text" name="category_slug" placeholder="<?= labels('enter_category_slug_here', 'Enter the slug of the Category here') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="make_parent" class="required"><?= labels('type', 'Type') ?></label><br>
                                        <select name="make_parent" id="make_parent" class="form-control">
                                            <option value="0"><?= labels('category', 'Category') ?></option>
                                            <option value="1"><?= labels('sub_category', 'Sub Category') ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row" id="parent">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="category_ids" class="required"> <?= labels('select_parent_category', 'Select Parent Category') ?></label><br>
                                        <select name="parent_id" id="category_ids" class="form-control">
                                            <option value=""><?= labels('select_parent_category', 'Select Parent Category') ?></option>
                                            <?php foreach ($categories as $category) : ?>
                                                <option value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group"> <label for="image" class="required"><?= labels('image', 'Image') ?></label>
                                        <input type="file" class="filepond" name="image" id="image" accept="image/*">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="color" class="required"><?= labels('dark_theme_color', 'Dark Theme Color') ?></label>
                                        <br>
                                        <input type="color" name="dark_theme_color" id="dark_theme_color" title="Choose Color" value="#000000" />
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="color" class="required"><?= labels('light_theme_color', 'Light Theme Color') ?></label>
                                        <br>
                                        <input type="color" name="light_theme_color" id="light_theme_color" title="Choose Color" value="#FFFFFF" />
                                    </div>
                                </div>
                            </div>
                            <div class="row ">
                                <div class="col-md d-flex justify-content-end">
                                    <div>
                                        <button type="submit" class="btn bg-new-primary submit_btn"><?= labels('add_category', 'Add Category') ?></button>
                                        <?= form_close(); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php }
            ?>
            <?php
            if ($permissions['read']['categories'] == 1) { ?>
                <div class="col-md-6 ">
                    <div class="card ">

                        <div class="row m-0">
                            <div class="col mb-3 " style="border-bottom: solid 1px #e5e6e9;">
                                <div class="toggleButttonPostition"><?= labels('category_list', 'Category List') ?></div>
                            </div>
                        </div>
                        <div class="row pb-3 pl-3">
                            <div class="col-12">
                                <div class="row mb-3 mt-3">
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
                                            <a class="dropdown-item" onclick="custome_export('pdf','Category list','category_list');"> <?= labels('pdf', 'PDF') ?></a>
                                            <a class="dropdown-item" onclick="custome_export('excel','Category list','category_list');"> <?= labels('excel', 'Excel') ?></a>
                                            <a class="dropdown-item" onclick="custome_export('csv','Category list','category_list')"> <?= labels('csv', 'CSV') ?></a>
                                        </div>
                                    </div>
                                </div>
                                <table class="table " data-fixed-columns="true" id="category_list" data-pagination-successively-size="2"
                                    data-detail-formatter="category_formater" data-query-params="category_query_params" data-auto-refresh="true"
                                    data-toggle="table" data-url="<?= base_url("admin/categories/list") ?>" data-side-pagination="server" data-pagination="true"
                                    data-page-list="[5, 10, 25, 50, 100, 200, All]" data-search="false" data-show-columns="false" data-show-columns-search="true"
                                    data-show-refresh="false" data-sort-name="id" data-sort-order="desc">
                                    <thead>
                                        <tr>
                                            <th data-field="id" data-visible="true" class="text-center" data-sortable="true"><?= labels('id', 'ID') ?></th>
                                            <th data-field="category_image" class="text-center"><?= labels('image', 'Image') ?></th>
                                            <th data-field="parent_id" data-visible="false" class="text-center" data-sortable="true"><?= labels('parent_Id', 'Parent Id') ?></th>
                                            <th data-field="parent_category_name" class="text-center" data-visible="false"><?= labels('parent_category_name', 'Parent Category Name') ?></th>
                                            <th data-field="name" class="text-center"><?= labels('name', 'Name') ?></th>
                                            <th data-field="slug" class="text-center"><?= labels('slug', 'Slug') ?></th>

                                            <th data-field="dark_color_format" class="text-center" data-visible="false"><?= labels('dark_theme_color', 'Dark Color') ?></th>
                                            <th data-field="light_color_format" class="text-center" data-visible="false"><?= labels('light_theme_color', 'Light Color') ?></th>
                                            <th data-field="created_at" data-visible="false" class="text-center" data-sortable="true"><?= labels('created_at', 'Created At') ?></th>
                                            <th data-field="operations" class="text-center" data-events="Category_events"><?= labels('operations', 'Operations') ?></th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </section>

    <div class="modal fade" id="update_modal" tabindex="-1" aria-labelledby="update_modal_thing" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header m-0 p-0">
                    <div class="row pl-3 w-100">
                        <div class="col border_bottom_for_cards">
                            <div class="toggleButttonPostition"><?= labels('update_category', 'Update Category') ?></div>
                        </div>
                        <div class="col d-flex justify-content-end  mt-4 border_bottom_for_cards">
                        </div>
                    </div>
                </div>
                <div class="modal-body">
                    <?= form_open('admin/category/update_category', ['method' => "post", 'class' => 'form-submit-event', 'id' => 'add_Category', 'enctype' => "multipart/form-data"]); ?>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_make_parent"><?= labels('type', 'Type') ?></label><br>
                                <select name="edit_make_parent" id="edit_make_parent" class="form-control">
                                    <option value="0"><?= labels('category', 'Category') ?></option>
                                    <option value="1"><?= labels('sub_category', 'Sub Category') ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name"><?= labels('name', 'Name') ?></label>
                                <input id="edit_name" class="form-control" type="text" name="name" placeholder="Enter the name of the Category here">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category_slug" class="required"><?= labels('slug', 'Slug') ?></label>
                                <input id="edit_category_slug" class="form-control" type="text" name="category_slug" placeholder="<?= labels('enter_category_slug_here', 'Enter the slug of the Category here') ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row" id="edit_parent">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category_ids"><?= labels('select_parent_category', 'Select Parent Category') ?></label><br>
                                <select name="edit_parent_id" id="edit_category_ids" class="form-control">
                                    <option value=""><?= labels('select_parent_category', 'Select Parent Category') ?></option>
                                    <?php foreach ($categories as $category) : ?>
                                        <option value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="id" id="id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="mb-3">
                                    <?= labels('image', "Image") ?>
                                    <input type="file" name="image" class="filepond" id="formFile" accept="image/*">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="edit_dark_theme_color"><?= labels('dark_theme_color', 'Dark Theme Color') ?></label>
                                <input type="color" name="edit_dark_theme_color" id="edit_dark_theme_color" class="form-control" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="edit_light_theme_color"><?= labels('light_theme_color', 'Light Theme Color') ?></label>
                                <input type="color" name="edit_light_theme_color" id="edit_light_theme_color" class="form-control" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div id="edit_categoryImage" style="width: 200px; height: 150px; border: 1px solid ;border-color: #e4e6fc;border-radius: 0.35rem;margin-bottom:25px ">
                                <img src="" alt="old_image" style="display: block;margin-left: auto;margin-top: 25px;margin-right: auto;width: 80%;" width="50%" height="100px" id="category_image" id="update_service_image">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn bg-new-primary submit_btn"><?= labels('update_category', 'Update Category') ?></button>
                    <?php form_close() ?>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= labels('close', "Close") ?></button>
                </div>
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
                        <h3 class="mb-0" style="display: inline-block; font-size: 16px; margin-left: 10px;"><?= labels('filters', "Filters") ?></h3>
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
<script>
    var picker1 = document.getElementById('dark_theme_color');
    var box1 = document.getElementById('categoryImage');
    picker1.addEventListener('change', function() {
        box1.style.backgroundColor = this.value;
    })
    var picker2 = document.getElementById('light_theme_color');
    var box2 = document.getElementById('categoryImage');
    picker2.addEventListener('change', function() {
        box2.style.backgroundColor = this.value;
    })
    var picker3 = document.getElementById('edit_light_theme_color');
    var box3 = document.getElementById('edit_categoryImage');
    picker3.addEventListener('change', function() {
        box3.style.backgroundColor = this.value;
    })
    var picker4 = document.getElementById('edit_dark_theme_color');
    var box4 = document.getElementById('edit_categoryImage');
    picker4.addEventListener('change', function() {
        box4.style.backgroundColor = this.value;
    })
</script>
<script>
    $(document).ready(function() {
        for_drawer("#filterButton", "#filterDrawer", "#filterBackdrop", "#cancelButton");
        var dynamicColumns = fetchColumns('category_list');
        setupColumnToggle('category_list', dynamicColumns, 'columnToggleContainer');
    });
</script>
<script>
    $("#customSearch").on('keydown', function() {
        $('#category_list').bootstrapTable('refresh');
    });
</script>