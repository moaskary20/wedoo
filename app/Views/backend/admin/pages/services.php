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
            <h1><?= labels('services', "Services") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item"><?= labels('services', 'Services') ?></a></div>
            </div>
        </div>
        <div class="container-fluid card">
            <div class="row mt-4 mb-3">
                <div class='btn bg-emerald-blue tag text-emerald-blue mr-2 ml-3 mb-2 filters_table' id="service_filter" name="service_filter_all" value=""><?= labels('all', 'All') ?></div>
                <div class='btn bg-emerald-success tag text-emerald-success mr-2 filters_table' id="service_filter_active" name="service_filter_active" value="service_filter"><?= labels('active', 'Active') ?></div>
                <div class='btn bg-emerald-danger tag text-emerald-danger mr-2 filters_table' id="service_filter_deactive" name="service_filter_deactive" value="service_filter"><?= labels('deactive', 'Deactive') ?></div>
                <div class='btn bg-emerald-info tag text-emerald-info mr-2 filters_table' id="service_filter_approve" name="service_filter_approve" value="service_filter_approve"><?= labels('approved', 'Approved') ?></div>
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
                        <a class="dropdown-item" onclick="custome_export('pdf','service list','service_list');"><?= labels('pdf', 'PDF') ?></a>
                        <a class="dropdown-item" onclick="custome_export('excel','service list','service_list');"><?= labels('excel', 'Excel') ?></a>
                        <a class="dropdown-item" onclick="custome_export('csv','service list','service_list')"><?= labels('csv', 'CSV') ?></a>
                    </div>
                </div>
                <div class="col col d-flex justify-content-end">
                    <?php if ($permissions['create']['services'] == 1) { ?>
                        <div class="text-center">
                            <a href="<?= base_url("admin/services/add_service"); ?>" class="btn btn-primary" style="height: 39px;font-size:14px">
                                <i class="fa fa-plus-circle mr-1 mt-2"></i><?= labels('add_service', 'Add Service') ?>
                            </a>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <?php if ($permissions['read']['services'] == 1) { ?>
                <div class="row ">
                    <div class="col-md-12">
                        <table class="table " data-fixed-columns="true" id="service_list" data-detail-formatter="user_formater" data-auto-refresh="true" data-toggle="table" data-url="<?= base_url("admin/services/list") ?>" data-query-params="service_list_query_params1" data-side-pagination="server" data-pagination="true" data-pagination-successively-size="2" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-search="false" data-show-columns-search="true" data-sort-name="id" data-sort-order="desc">
                            <thead>
                                <tr>
                                    <th data-field="id" class="text-center" data-sortable="true" data-visible="false"><?= labels('id', 'ID') ?></th>
                                    <th data-field="image_of_the_service" class="text-center"><?= labels('image ', 'Image') ?></th>
                                    <th data-field="title" class="text-center"><?= labels('title', 'Title') ?></th>
                                    <!-- <th data-field="slug" class="text-center"><?= labels('slug', 'Slug') ?></th> -->

                                    <th data-field="tags" class="text-center" data-visible="false"><?= labels('tags ', 'Tags') ?></th>
                                    <th data-field="price" class="text-center" data-sortable="true"><?= labels('price ', 'Price') ?></th>
                                    <th data-field="discounted_price" class="text-center" data-sortable="true"><?= labels('discounted_price ', 'Discounted price') ?></th>
                                    <th data-field="average_rating" class="text-center" data-sortable="true" data-visible="false"><?= labels('rating ', 'Rating') ?></th>
                                    <th data-field="status_badge" class="text-center"><?= labels('status ', 'Status') ?></th>
                                    <th data-field="category_id" class="text-center" data-sortable="true" data-visible="false"><?= labels('category_id', 'Category ID') ?></th>
                                    <th data-field="tax_type" class="text-center" data-sortable="true" data-visible="false"><?= labels('tax_type', 'Tax Type') ?></th>
                                    <th data-field="number_of_members_required" class="text-center" data-sortable="true" data-visible="false"><?= labels('members_required ', 'Members required') ?></th>
                                    <th data-field="duration" class="text-center" data-sortable="true" data-visible="false"><?= labels('duration ', 'Duration') ?></th>
                                    <!-- <th data-field="number_of_ratings" class="text-center" data-sortable="true" data-visible="false"><?= labels('numbers_of_rating ', 'Numbers of Rating') ?></th> -->
                                    <th data-field="max_quantity_allowed" class="text-center" data-sortable="true" data-visible="false"><?= labels('max_quantity_allowed ', 'Max Quantity Allowed') ?></th>
                                    <th data-field="is_pay_later_allowed_badge" class="text-center" data-visible="false"><?= labels('pay_later_allowed ', 'Pay Later Allowed') ?></th>
                                    <th data-field="cancelable_badge" class="text-center" data-visible="false"><?= labels('is_cancelable_?', 'is Cancelable') ?></th>
                                    <th data-field="approved_by_admin_badge" class="text-center" data-visible="true"><?= labels('approved_by_admin ', 'Approved By Admin') ?></th>
                                    <th data-field="created_at" class="text-center" data-sortable="true" data-visible="false"><?= labels('created_at', 'Created At') ?></th>
                                    <th data-field="operations" class="text-center" data-events="services_events_admin"><?= labels('operations', 'Operations') ?></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            <?php } ?>
        </div>
    </section>
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
                            <div class="jquery-script-clear"></div>
                            <div class="categories" id="categories">
                                <div class="form-group ">
                                    <label for="table_filters"><?= labels('select_provider', 'Select Provider') ?></label>
                                    <select id="service_custom_provider_filter" class="form-control w-100 select2" name="partner">
                                        <option value=""><?= labels('select_provider', 'Select Provider') ?></option>
                                        <?php foreach ($partner_name as $pn) : ?>
                                            <option value="<?= $pn['id'] ?>" data-members="<?= $pn['number_of_members'] ?>">
                                                <?= $pn['company_name'] . ' - ' . $pn['username'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 mb-2">
                            <div class="form-group ">
                                <label for="category_item" class=""><?= labels('choose_a_category', 'Choose a Category') ?></label>
                                <select id="service_category_custom_filter" class="form-control select2" name="categories" style="margin-bottom: 20px;">
                                    <option value=""> <?= labels('select', 'Select') ?> <?= labels('category', 'Category') ?> </option>
                                    <?php foreach ($categories_name as $category) : ?>
                                        <option value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
                                    <?php endforeach; ?>
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
                            <button class="btn bg-new-primary d-block" id="service_filter_all">
                                <?= labels('apply_filter', 'Apply Filter') ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<script>
    $(document).ready(function() {
        for_drawer("#filterButton", "#filterDrawer", "#filterBackdrop", "#cancelButton");
        var dynamicColumns = fetchColumns('service_list');
        setupColumnToggle('service_list', dynamicColumns, 'columnToggleContainer');
    });
</script>
<script>
    $("#service_filter").on("click", function() {

        service_filter = "";
        service_filter_approve = "";
        $("#service_list").bootstrapTable("refresh");
    });
    $("#service_filter_active").on("click", function() {
        service_filter = "1";
        service_filter_approve = "";
        $("#service_list").bootstrapTable("refresh");
    });
    $("#service_filter_deactive").on("click", function() {
        service_filter = "0";
        service_filter_approve = "";
        $("#service_list").bootstrapTable("refresh");
    });
    $("#service_filter_approve").on("click", function() {
        service_filter = "";
        service_filter_approve = 1;

        $("#service_list").bootstrapTable("refresh");
    });
    $(document).ready(function() {
        $('#is_cancelable').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        $('#pay_later').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        $('#status').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        $('#at_store').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        $('#at_doorstep').siblings('.switchery').addClass('deactive-content').removeClass('active-content');

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
        var isCancelable = document.querySelector('#is_cancelable');
        isCancelable.addEventListener('change', function() {
            handleSwitchChange(isCancelable);
        });
        var payLater = document.querySelector('#pay_later');
        payLater.addEventListener('change', function() {
            handleSwitchChange(payLater);
        });
        var status = document.querySelector('#status');
        status.addEventListener('change', function() {
            handleSwitchChange(status);
        });
        var atStore = document.querySelector('#at_store');
        atStore.addEventListener('change', function() {
            handleSwitchChange(atStore);
        });
        var atDoorstep = document.querySelector('#at_doorstep');
        atDoorstep.addEventListener('change', function() {
            handleSwitchChange(atDoorstep);
        });
    });

    function test() {
        var tax = document.getElementById("edit_tax").value;
        document.getElementById("update_service").reset();
        document.getElementById("edit_tax").value = tax;
        document.getElementById('edit_service_image').removeAttribute('src');
    }
    $('#service_image_selector').bind('change', function() {
        var filename = $("#service_image_selector").val();
        if (/^\s*$/.test(filename)) {
            $(".file-upload").removeClass('active');
            $("#noFile").text("No file chosen...");
        } else {
            $(".file-upload").addClass('active');
            $("#noFile").text(filename.replace("C:\\fakepath\\", ""));
        }
    });
</script>
<script>
    $(document).ready(function() {
        var x = 0; //Initial field counter
        var list_maxField = 10; //Input fields increment limitation
        //Once add button is clicked
        $('.list_add_button').click(function() {
            //Check maximum number of input fields
            if (x < list_maxField) {
                x++; //Increment field counter
                var list_fieldHTML = '<div class="row"><div class="col-xs-4 col-sm-4 col-md-4"><div class="form-group"> <label for="question"><?= labels('question', "Quetion") ?></label><input name="faqs[' + x + '][]" type="text" placeholder="Enter the question here" class="form-control"/></div></div><div class="col-xs-7 col-sm-7 col-md-4"><div class="form-group">    <label for="question"><?= labels('answer', "Answer") ?></label><input name="faqs[' + x + '][]" type="text" placeholder="Enter the answer here" class="form-control"/></div></div><div class="col-xs-1 col-sm-7 col-md-1 mt-4"><a href="javascript:void(0);" class="list_remove_button btn btn-danger">-</a></div></div>'; //New input field html 
                $('.list_wrapper').append(list_fieldHTML); //Add field html
            }
        });
        //Once remove button is clicked
        $('.list_wrapper').on('click', '.list_remove_button', function() {
            $(this).closest('div.row').remove(); //Remove field html
            x--; //Decrement field counter
        });
    });
</script>