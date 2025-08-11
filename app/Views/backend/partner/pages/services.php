<!-- Main Content -->
<?= helper('form'); ?>
<div class="main-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('services', "Services") ?></h1>
            <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="<?= base_url('partner/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
            </div>
        </div>
        <div class="container-fluid card">
            <div class="row">
                <div class="col-md-12">
                    <div class="row mt-4 mb-3">
                        <div class='btn bg-emerald-blue tag text-emerald-blue mr-2 ml-3 mb-2 filters_table' id="service_filter" name="service_filter_all" value=""><?= labels('all', 'All') ?> </div>
                        <div class='btn bg-emerald-success tag text-emerald-success mr-2 filters_table' id="service_filter_active" name="service_filter_active" value="service_filter"><?= labels('active', 'Active') ?> </div>
                        <div class='btn bg-emerald-danger tag text-emerald-danger mr-2 filters_table' id="service_filter_deactive" name="service_filter_deactive" value="service_filter"><?= labels('deactive', 'Deactive') ?> </div>
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
                        <div class="dropdown d-inline ml-2">
                            <button class="btn export_download dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <?= labels('download', 'Download') ?> 
                            </button>
                            <div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 28px, 0px); top: 0px; left: 0px; will-change: transform;">
                                <a class="dropdown-item" onclick="custome_export('pdf','service list','cash_collection');"> <?= labels('pdf', 'PDF') ?> </a>
                                <a class="dropdown-item" onclick="custome_export('excel','service list','cash_collection');"> <?= labels('excel', 'Excel') ?> </a>
                                <a class="dropdown-item" onclick="custome_export('csv','service list','cash_collection')"> <?= labels('csv', 'CSV') ?> </a>
                            </div>
                        </div>
                        <div class="col col d-flex justify-content-end">
                            <div class="text-center">
                                <a class="btn btn-primary text-white" id="add_promo" href="<?= base_url('partner/services/add'); ?>"><i class="fas fa-plus"></i> <?= labels('add_services', 'Add Services') ?></a>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-borderd" data-fixed-columns="true"  id="cash_collection" data-show-export="false"
                         data-export-types="['txt','excel','csv']" data-export-options='{"fileName": "invoice-order-list","ignoreColumn": ["action"]}'
                          data-auto-refresh="true" data-show-columns="false" data-search="false" data-show-refresh="false" data-toggle="table" data-page-list="[5, 10, 25, 50, 100, 200, All]" 
                          data-side-pagination="server" data-pagination="true" data-url="<?= base_url("partner/services/list") ?>" data-sort-name="id" data-sort-order="desc"
                          data-query-params="service_list_query_params1" data-pagination-successively-size="2">
                            <thead>
                                <tr>
                                    <th data-field="id" class="text-center" data-visible="false" data-sortable="true"><?= labels('id', 'ID') ?></th>
                                    <th data-field="image_of_the_service" class="text-center"><?= labels('image', 'Image') ?></th>
                                    <th data-field="category_id" class="text-center" data-visible="false" data-sortable="true"><?= labels('category_id', 'Category ID') ?></th>
                                    <th data-field="category_name" class="text-center" data-visible="false" data-sortable="true"><?= labels('category_name', 'Category Name') ?></th>
                                    <th data-field="parent_id" class="text-center" data-visible="false" data-sortable="true"><?= labels('parent_id', 'Parent ID') ?></th>
                                    <th data-field="user_id" class="text-center" data-visible="false" data-sortable="true"><?= labels('user_id', 'User ID') ?></th>
                                    <th data-field="title" class="text-center" data-sortable="true"><?= labels('title', 'Title') ?></th>
                                    <th data-field="slug" class="text-center" data-visible="false" data-sortable="true"><?= labels('slug', 'Slug') ?></th>
                                    <th data-field="tags" class="text-center" data-visible="false" data-sortable="true"><?= labels('tags', 'Tags') ?></th>
                                    <th data-field="price" class="text-center" data-sortable="true"><?= labels('price', 'Price') ?></th>
                                    <th data-field="discounted_price" class="text-center" data-sortable="true"><?= labels('discounted_price', 'Discounted Price') ?></th>
                                    <th data-field="duration" class="text-center" data-sortable="true"><?= labels('duration', 'Duration') . '(min)' ?></th>
                                    <th data-field="cancelable_badge" class="text-center" data-sortable="true"><?= labels('is_cancelable_?', 'is service cancelable?') ?></th>
                                    <th data-field="cancelable_till" class="text-center" data-sortable="true"><?= labels('cancelable_till', 'Cancelable before') . '(min)' ?></th>
                                    <th data-field="tax_type" class="text-center" data-visible="false" data-sortable="true"><?= labels('tax_type', 'Tax Type') ?></th>
                                    <th data-field="status_badge" class="text-center"><?= labels('status ', 'Status') ?></th>
                                    <th data-field="description" class="text-center" data-visible="false" data-sortable="true"><?= labels('description', 'Description') ?></th>
                                    <th data-field="number_of_members_required" class="text-center" data-visible="false" data-sortable="true"><?= labels('members_required_to_perform_task', 'Members required to perform Tasks') ?></th>
                                    <th data-field="tax_id" class="text-center" data-visible="false" data-sortable="true"><?= labels('tax_id', 'Tax ID') ?></th>
                                    <th data-field="max_quantity_allowed" class="text-center" data-visible="false" data-sortable="true"><?= labels('max_quantity_allowed', 'Max Quantity allowed for services') ?></th>
                                    <th data-field="is_pay_later_allowed" class="text-center" data-visible="false" data-sortable="true"><?= labels('pay_later_allowed', 'Pay Later Allowed') ?></th>
                                    <th data-field="created_at" class="text-center" data-visible="false" data-sortable="true"><?= labels('created_at', 'Created At') ?></th>
                                    <th data-field="updated_at" class="text-center" data-visible="false" data-sortable="true"><?= labels('updated_at', 'Updated At') ?></th>
                                    <th data-field="operations" class="text-center" data-events="services_events"><?= labels('operations', 'Operations') ?></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<div class="modal fade" id="update_modal" tabindex="-1" role="dialog" aria-labelledby="update_modal_ser" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"><?= labels('update_service', 'Update Service') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?= form_open('/partner/services/update_service', ['method' => "post", 'class' => 'form-submit-event', 'id' => 'update_service', 'enctype' => "multipart/form-data"]); ?>
                <input type="hidden" name="service_id" id="service_id">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="title"><?= labels('title', 'Title') ?></label>
                            <input class="form-control" type="text" name="title">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="jquery-script-clear"></div>
                        <div class="categories" id="categories">
                            <label for="category_item"><?= labels('choose_a_category_for_your_service', 'Choose a Category for your service') ?></label>
                            <select id="category_item" class="form-control" name="categories">
                                <option value=""> <?= labels('select_category', 'Select Category') ?></option>
                                <?php foreach ($categories as $category) : ?>
                                    <option value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mt-4">
                            <label for="tags"><?= labels('tags', 'Tags') ?></label>
                            <input id="service_tags" class="" type="text" name="tags[]">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tax_type"><?= labels('tax_type', 'Tax Type') ?></label>
                            <select name="tax_type" id="tax_type" class="form-control">
                                <option value="excluded"><?= labels('tax_excluded_in_price', 'Tax Excluded In Price') ?></option>
                                <option value="included"><?= labels('tax_included_in_price', 'Tax Included In Price') ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="jquery-script-clear"></div>
                        <div class="" id="">
                            <label for="partner"><?= labels('select_tax', 'Select Tax') ?></label> <br>
                            <select id="edit_tax" name="edit_tax_id" class="form-control w-100">
                                <option value=""><?= labels('select_tax', 'Select Tax') ?></option>
                                <?php foreach ($tax_data as $pn) : ?>
                                    <option value="<?= $pn['id'] ?>"><?= $pn['title'] ?>(<?= $pn['percentage'] ?>%)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="custom-file">
                            <div class="form-group">
                                <?= labels('service_image', "Service Image") ?>
                                <input type="file" class="custom-file-input" id="image" name="image" accept='image/*' onchange="readURL(this)">
                                <input type="hidden" class="form-control" name="old_icon" id="old_icon">
                                <label class="custom-file-label mt-4" for="image"><?= labels('choose_file', 'Choose file') ?></label>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 mt-3">
                        <div class="form-group image">
                            <img src="<?= base_url('public/backend/assets/img/news/img01.jpg') ?>" alt="Service Image" width="30%" id="service_image">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="price"><?= labels('price', 'Price') ?></label>
                            <input id="price" class="form-control" type="number" name="price" placeholder="price" min="0" oninput="this.value = Math.abs(this.value)">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="discounted_price"><?= labels('discounted_price', 'Discounted Price') ?></label>
                            <input id="discounted_price" class="form-control" type="number" name="discounted_price" placeholder="Discounted Price" min="0" oninput="this.value = Math.abs(this.value)">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-3">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="pay_later" name="pay_later">
                            <label class="custom-control-label" for="pay_later"><?= labels('pay_later_allowed', 'Pay Later Allowed') ?></label>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_cancelable" name="is_cancelable">
                            <label class="custom-control-label" for="is_cancelable"><?= labels('is_cancelable_?', 'is service cancelable') ?>?</label>
                        </div>
                    </div>
                    <div class="col-lg-3 cancelable-till">
                        <div class="form-group">
                            <label for="cancelable_till"><?= labels('cancelable_till', 'Cancelable before') ?></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <span><?= labels('minutes', 'Minutes') ?></span>
                                    </div>
                                </div>
                                <input type="number" style="height: 42px;" class="form-control" name="cancelable_till" id="cancelable_till" placeholder="Ex. 30" min="0" oninput="this.value = Math.abs(this.value)">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="members"><?= labels('members_required_to_perform_task', 'Members required to perform Tasks') ?></label>
                            <input id="members" class="form-control" type="number" name="members" placeholder="Members Required" min="0" oninput="this.value = Math.abs(this.value)">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="duration"><?= labels('duration_to_perform_task', 'Duration to Perform Task') ?></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <span><?= labels('minutes', 'Minutes') ?></span>
                                    </div>
                                </div>
                                <input type="number" style="height: 42px;" class="form-control" name="duration" id="duration" placeholder="Duration of the Service" value="" min="0" oninput="this.value = Math.abs(this.value)">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg">
                        <div class="form-group">
                            <label for="max_qty"><?= labels('max_quantity_allowed', 'Max Quantity allowed for services') ?></label>
                            <input id="max_qty" class="form-control" type="number" name="max_qty" placeholder="Max Quantity allowed for services" min="0" oninput="this.value = Math.abs(this.value)">
                        </div>
                    </div>
                    <div class="col-lg">
                        <div class="form-group">
                            <label for="Description"><?= labels('description', 'Description') ?></label>
                            <textarea style="min-height:60px" rows='2' cols='30' class='form-control h-50' name="description"></textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Status<span class="text-danger text-sm">*</span></label>
                            <br>
                            <div id="edit_status" class="btn-group col-sm-8" style="margin-left: -11px;">
                                <label class="btn btn-primary" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                    <input type="radio" name="edit_status" id="edit_status_active" value="1">Active </label>
                                <label class="btn btn-danger" data-toggle-class="btn-danger" data-toggle-passive-class="btn-default">
                                    <input type="radio" name="edit_status" id="edit_status_deactive" value="0" checked=""> Deactive </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-2">
                        <div class="form-group">
                            <input type="submit" value="<?= labels('update_service', 'Update Service') ?>" id="service_submit" class="btn btn-success btn-block">
                        </div>
                    </div>
                    <div class="col-2">
                        <div class="form-group">
                            <input type="reset" value="<?= labels('Reset', 'Reset') ?>" class="btn btn-danger btn-block">
                        </div>
                    </div>
                </div>
                <?= form_close() ?>
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
<script>
    var service_filter="";
    $("#service_filter").on("click", function() {
        service_filter = "";
        $("#cash_collection").bootstrapTable("refresh");
    });
    $("#service_filter_active").on("click", function() {
        service_filter = "1";
        $("#cash_collection").bootstrapTable("refresh");
    });
    $("#service_filter_deactive").on("click", function() {
        service_filter = "0";
        $("#cash_collection").bootstrapTable("refresh");
    });
    $("#customSearch").on("keydown", function() {
        $("#cash_collection").bootstrapTable("refresh");
    });
    function service_list_query_params1(p) {
        return {
            search: $("#customSearch").val() ? $("#customSearch").val() : p.search,
            limit: p.limit,
            sort: p.sort,
            order: p.order,
            offset: p.offset,
            service_filter: service_filter,
        };
    }
        $(document).ready(function() {
            for_drawer("#filterButton", "#filterDrawer", "#filterBackdrop", "#cancelButton");
            var columns = [{
                    field: 'id',
                    label: '<?= labels('id', 'ID') ?>',
                    visible: false
                },
                {
                    field: 'image_of_the_service',
                    label: '<?= labels('image', 'Image') ?>'
                },
                {
                    field: 'title',
                    label: '<?= labels('title', 'Title') ?>'
                },
                {
                    field: 'category_id',
                    label: '<?= labels('category_id', 'Category ID') ?>',
                    visible: false
                },
                {
                    field: 'category_name',
                    label: '<?= labels('category_name', 'Category Name') ?>',
                    visible: false
                },
                {
                    field: 'tags',
                    label: '<?= labels('tags', 'Tags') ?>',
                    visible: false
                },
                {
                    field: 'price',
                    label: '<?= labels('price', 'Price') ?>',
                },
                {
                    field: 'discounted_price',
                    label: '<?= labels('discounted_price', 'Discounted Price') ?>',
                    visible: false
                },
                {
                    field: 'status_badge',
                    label: 'Status',
                },
                {
                    field: 'parent_id',
                    label: '<?= labels('parent_id', 'Parent ID') ?>',
                    visible: false
                },
                {
                    field: 'user_id',
                    label: '<?= labels('user_id', 'User ID') ?>',
                    visible: false
                },
                {
                    field: 'tax_type',
                    label: '<?= labels('tax_type', 'Tax Type') ?>'
                },
                {
                    field: 'number_of_members_required',
                    label: 'Member Required',
                    visible: false
                },
                {
                    field: 'duration',
                    label: '<?= labels('duration', 'Duration') . '(min)' ?>'
                },
                {
                    field: 'max_quantity_allowed',
                    label: 'Maximum Quantity',
                    visible: false
                },
                {
                    field: 'cancelable_badge',
                    label: '<?= labels('is_cancelable_?', 'is service cancelable?') ?>'
                },
                {
                    field: 'created_at',
                    label: 'Created At',
                    visible: false
                },
                {
                    field: 'cancelable_till',
                    label: '<?= labels('cancelable_till', 'Cancelable before') . '(min)' ?>',
                    visible: false
                },
                {
                    field: 'status_badge',
                    label: '<?= labels('status ', 'Status') ?>',
                },
                {
                    field: 'description',
                    label: '<?= labels('description', 'Description') ?>',
                },
                {
                    field: 'number_of_members_required',
                    label: '<?= labels('members_required_to_perform_task', 'Members required to perform Tasks') ?>',
                    visible: false
                },
                {
                    field: 'operations',
                    label: '<?= labels('operations', 'Operations') ?>'
                },
                {
                    field: 'is_pay_later_allowed',
                    label: '<?= labels('pay_later_allowed', 'Pay Later Allowed') ?>',
                    visible:false,
                },
                {
                    field: 'created_at',
                    label: '<?= labels('created_at', 'Created At') ?>',
                    visible:false,
                }
            ];
            setupColumnToggle('cash_collection', columns, 'columnToggleContainer');
        });
</script>