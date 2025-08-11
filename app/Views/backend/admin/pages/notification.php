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
            <h1><?= labels('notification', "Notification") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item"><?= labels('notification', 'Notification') ?></div>
            </div>
        </div>
        <div class="row">
            <?php
            if ($permissions['create']['send_notification'] == 1) { ?>
                <div class="col-md-4">
                    <div class="card">
                        <div class="row pl-3 border_bottom_for_cards m-0">
                            <div class="col ">
                                <div class="toggleButttonPostition"><?= labels('add_notifications', 'Add Notification') ?></div>
                            </div>
                        </div>
                        <?= helper('form'); ?>
                        <div class="row">
                            <div class="col-md">
                                <div class="card-body">
                                    <?= form_open('/admin/notification/add_notification', [
                                        'method' => "post",
                                        'class' => 'form-submit-event',
                                        'id' => 'add_notification',
                                        'enctype' => "multipart/form-data"
                                    ]); ?>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="type" class="required"><?= labels('send', "Send") ?> <?= labels('to', "To") ?> </label>
                                                <select id="user_type" class="form-control select2" name="user_type">
                                                    <option value="all_users" selected><?= labels('all_users', "All Users") ?> </option>
                                                    <option value="specific_user"><?= labels('specific_user', "Specific User") ?></option>
                                                    <option value="provider"><?= labels('provider', "Provider") ?></option>
                                                    <option value="customer"><?= labels('customer', "Customer") ?></option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row" id="user_select">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <div class="personal">
                                                    <label for="Category_item" class="required"><?= labels('personal', "Personal") ?></label>
                                                    <select id="users" class="form-control" name="user_ids[]" multiple>
                                                        <?php foreach ($users as $user) : ?>
                                                            <option value="<?= $user['id'] ?>"><?= $user['username'] ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="type" class="required"><?= labels('type', "Type") ?> <?= labels('notification', "Notification") ?> </label>
                                                <select id="type1" class="form-control select2" name="type">
                                                    <option value=""><?= labels('select_type', "Select Type") ?> </option>
                                                    <option value="general"><?= labels('general', "General") ?></option>
                                                    <option value="provider"><?= labels('provider', "Provider") ?></option>
                                                    <option value="category"><?= labels('category', "Category") ?></option>
                                                    <option value="url"><?= labels('url', "URL") ?></option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row" id="provider_select">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <div class="per">
                                                    <label for="Category_item" class="required"><?= labels('provider', "Provider") ?></label>
                                                    <select id="providers" class="form-control select2 select2-hidden-accessible" name="partner_id">
                                                        <?php foreach ($partners as $partner) : ?>
                                                            <option value="<?= $partner['partner_id'] ?>"><?= $partner['company_name'] ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row" id="category_select">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <div class="per">
                                                    <label for="Category_item" class="required"><?= labels('category', "Category") ?></label>
                                                    <select id="categories" class="form-control select2 select2-hidden-accessible" name="category_id">
                                                        <?php foreach ($categories_name as $categorie) : ?>
                                                            <option value="<?= $categorie['id'] ?>"><?= $categorie['name'] ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row" id="url">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <div class="per">
                                                    <label for="Category_item" class="required"><?= labels('url', "URL") ?></label>
                                                    <input id="url" class="form-control" type="url" name="url" placeholder="<?= labels('enter_url_here', 'Enter the URL here') ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="title" class="required"><?= labels('title', "Title") ?></label>
                                                <input id="title" class="form-control" type="title" name="title" placeholder="<?= labels('enter_title_here', 'Enter the title here') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="message" class="required"><?= labels('message', "Message") ?></label>
                                                <textarea id="messgae" style="min-height:60px" class="form-control col-md-12" name="message" placeholder="<?= labels('enter_message_here', 'Enter Message Here') ?>"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3 form-group">
                                            <label class=" mt-2" class="required">
                                                <input type="checkbox" id="image_checkbox" name="image_checkbox" class="custom-switch-input">
                                                <span class="custom-switch-indicator"></span>
                                                <span class="custom-switch-description"><?= labels('image', 'Image') ?></span>
                                            </label>
                                        </div>
                                        <div class="col-md-9 d-none include_image">
                                            <input type="file" name="image" id="image" class="filepond" accept="image/*">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md d-flex justify-content-end">
                                            <button type="submit" class="btn bg-new-primary submit_btn" id="add_slider"><?= labels('send_notifications', "Send Notifications") ?></button>
                                        </div>
                                        <?= form_close() ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <div class="col-md-8">
                <div class="container-fluid card">
                    <div class="row  border_bottom_for_cards ">
                        <div class="col-auto">
                            <div class="toggleButttonPostition "><?= labels('notification_list', 'Notification List') ?></div>
                        </div>
                    </div>
                    <div class="row mt-4 mb-3 ml-1">
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
                                <a class="dropdown-item" onclick="custome_export('pdf','Notification list','user_list');"><?= labels('pdf', 'PDF') ?></a>
                                <a class="dropdown-item" onclick="custome_export('excel','Notification list','user_list');"><?= labels('excel', 'Excel') ?></a>
                                <a class="dropdown-item" onclick="custome_export('csv','Notification list','user_list')"><?= labels('csv', 'CSV') ?></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg">
                        <table class="table " data-fixed-columns="true" id="user_list" data-pagination-successively-size="2" data-detail-formatter="user_formater" data-auto-refresh="true" data-toggle="table" data-url="<?= base_url("admin/notification/list") ?>"
                            data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-search="false" data-show-columns="false" data-show-columns-search="true"
                            data-show-refresh="false" data-sort-name="id" data-sort-order="desc" data-query-params="notification_query_params">
                            <thead>
                                <tr>
                                    <th data-field="id" class="text-center" data-visible="true" data-sortable="true"><?= labels('id', 'ID') ?></th>
                                    <th data-field="image" class="text-center"><?= labels('image', 'Image') ?></th>
                                    <th data-field="title" class="text-center" data-visible="true"><?= labels('title', 'Title') ?></th>
                                    <th data-field="message" class="text-center" data-visible="true"><?= labels('message', 'Message') ?></th>
                                    <th data-field="type" class="text-center" data-visible="true" data-sortable="true"><?= labels('type', 'Type') ?></th>
                                    <th data-field="notification_type" class="text-center" data-visible="true" data-sortable="true"><?= labels('notification_type', 'Notification Type') ?></th>
                                    <th data-field="operations" class="text-center" data-events="notification_event"><?= labels('operations', 'Operations') ?></th>
                                </tr>
                            </thead>
                        </table>
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
</div>
<script>
    $(document).ready(function() {
        for_drawer("#filterButton", "#filterDrawer", "#filterBackdrop", "#cancelButton");
        var dynamicColumns = fetchColumns('user_list');
        setupColumnToggle('user_list', dynamicColumns, 'columnToggleContainer');
    });
</script>