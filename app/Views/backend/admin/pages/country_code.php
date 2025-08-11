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
?>
<div class="main-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('country_codes', "Country Codes") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item "><a href="<?= base_url('/admin/settings/system-settings') ?>"><?= labels('system_settings', "System Settings") ?></a></div>
                <div class="breadcrumb-item"><?= labels('country_codes', "Country Codes") ?></a></div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class=" card">
                    <?= helper('form'); ?>
                    <div class="row m-0">
                        <div class="col border_bottom_for_cards">
                            <div class="toggleButttonPostition"><?= labels('country_codes', "Country Codes") ?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <?= form_open('admin/settings/add_contry_code', ['method' => "post", 'class' => 'form-submit-event', 'id' => 'add_faqs', 'enctype' => "multipart/form-data"]); ?>
                        <div class="form-group">
                            <label for="code"><?= labels('code', "Code") ?></label>
                            <input id="code" class="form-control" type="text" name="code" placeholder="<?= labels('enter_code_here', 'Enter the code here') ?>">
                        </div>
                        <div class="form-group">
                            <label for="name"><?= labels('name', "Name") ?></label>
                            <input id="name" class="form-control" type="text" name="name" placeholder="<?= labels('enter_name_here', 'Enter the name here') ?>">
                        </div>
                        <?php if ($permissions['create']['settings'] == 1) : ?>

                            <div class=" d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary"><?= labels('add_country_code', "Add Country code") ?></button>
                            </div>
                        <?php endif; ?>

                        <?= form_close(); ?>
                    </div>
                </div>
            </div>

            <?php if ($permissions['read']['settings'] == 1) : ?>

                <div class="col-md-8">
                    <div class=" card">
                        <div class="row">
                            <div class="col-lg">
                                <div class="row m-0">
                                    <div class="col border_bottom_for_cards">
                                        <div class="toggleButttonPostition"><?= labels('contry_codes', "Country codes") ?></div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row mt-2">
                                        <div class="col-md-12">
                                            <div class="row pb-3 ">
                                                <div class="col-12">
                                                    <div class="row mb-3 ">
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
                                                                <?= labels('download', 'Download') ?>
                                                            </button>
                                                            <div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 28px, 0px); top: 0px; left: 0px; will-change: transform;">
                                                                <a class="dropdown-item" onclick="custome_export('pdf','Tax list','tax_list');"> <?= labels('pdf', 'PDF') ?></a>
                                                                <a class="dropdown-item" onclick="custome_export('excel','Tax list','tax_list');"> <?= labels('excel', 'Excel') ?></a>
                                                                <a class="dropdown-item" onclick="custome_export('csv','Tax list','tax_list')"> <?= labels('csv', 'CSV') ?></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <table class="table" data-pagination-successively-size="2" data-query-params="country_code_query_params" id="country_code_list" data-detail-formatter="user_formater" data-auto-refresh="true" data-toggle="table" data-url="<?= base_url("admin/settings/fetch_contry_code") ?>" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-search="false" data-show-columns="false" data-show-columns-search="true" data-show-refresh="false" data-sort-name="id" data-sort-order="desc">
                                                <thead>
                                                    <tr>
                                                        <th data-field="id" class="text-center" data-sortable="true"><?= labels('id', 'ID') ?></th>
                                                        <th data-field="name" class="text-center" data-sortable="true"><?= labels('name', 'Name') ?></th>
                                                        <th data-field="code" class="text-center" data-sortable="true"><?= labels('code', 'Code') ?></th>
                                                        <th data-field="default" class="text-center" data-sortable="true"><?= labels('default', 'Default') ?></th>
                                                        <th data-field="created_at" class="text-center" data-visible="false" data-sortable="true"><?= labels('created_at', 'Created At') ?></th>
                                                        <th data-field="operations" class="text-center" data-events="Countr_code_events"><?= labels('operations', 'Operations') ?></th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endif; ?>

        </div>
    </section>
    <!-- update modal -->
    <div class="modal fade" id="update_modal" tabindex="-1" aria-labelledby="update_modal_thing" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header m-0 p-0">
                    <div class="row pl-3 w-100">
                        <div class="col-12 " style="border-bottom: solid 1px #e5e6e9;">
                            <div class="toggleButttonPostition"><?= labels('update_country_code', 'Update Country Code') ?></div>
                        </div>
                    </div>
                </div>
                <div class="modal-body">
                    <?= form_open('admin/settings/update_country_codes', ['method' => "post", 'class' => 'form-submit-event', 'id' => 'add_Category', 'enctype' => "multipart/form-data"]); ?>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="code"><?= labels('code', "Code") ?></label>
                                <input id="edit_code" class="form-control" type="text" name="code" placeholder="<?= labels('enter_code_here', 'Enter the code here') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name"><?= labels('name', "Name") ?></label>
                                <input id="edit_name" class="form-control" type="text" name="name" placeholder="<?= labels('enter_name_here', 'Enter the name here') ?>">
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="id" id="id">
                    <div class="modal-footer">
                        <button type="submit" class="btn bg-new-primary submit_btn"><?= labels('update_country_code', 'Update Country Code') ?></button>
                        <?php form_close() ?>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= labels('close', "Close") ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $("#customSearch").on('keydown', function() {
        $('#country_code_list').bootstrapTable('refresh');
    });
    $(document).ready(function() {
        window.Countr_code_events = {
            "click .delete-country_code": function(e, value, row, index) {

                var id = row.id;
                Swal.fire({
                    title: "<?= labels('are_your_sure', 'Are you sure?') ?>",
                    text: you_wont_be_able_to_revert_this,
                    icon: "error",
                    showCancelButton: true,
                    confirmButtonText: yes_proceed,
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post(
                            baseUrl + "/admin/settings/delete_contry_code", {
                                [csrfName]: csrfHash,
                                id: id,
                            },
                            function(data) {
                                csrfName = data.csrfName;
                                csrfHash = data.csrfHash;

                                if (data.error == false) {
                                    showToastMessage(data.message, "success");
                                    setTimeout(() => {
                                        $("#country_code_list").bootstrapTable("refresh");
                                    }, 2000);
                                    return;
                                } else {
                                    return showToastMessage(data.message, "error");
                                }
                            }
                        );
                    }
                });
            },
            "click .edit_country_code": function(e, value, row, index) {
                $("#id").val(row.id);
                $("#edit_name").val(row.name);
                $("#edit_code").val(row.code);
            },
        };
    });
</script>
<script type="text/javascript">
    $(document).on('click', '.store_default_country_code', function() {
        var id = $(this).data("id");
        var base_url = baseUrl;
        $.ajax({
            url: baseUrl + "/admin/settings/store_default_country_code",
            type: "POST",
            dataType: "json",
            data: {
                id: id
            },
            success: function(result) {
                if (result) {
                    iziToast.success({
                        title: "Success",
                        message: result.message,
                        position: "topRight",
                    })
                    $("#country_code_list").bootstrapTable("refresh");
                }
            }
        });
    });
</script>