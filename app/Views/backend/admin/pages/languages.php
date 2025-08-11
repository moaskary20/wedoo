<?php
helper('form');
$db      = \Config\Database::connect();
$builder = $db->table('users u');
$builder->select('u.*,ug.group_id')
    ->join('users_groups ug', 'ug.user_id = u.id')
    ->where('ug.group_id', 1)
    ->where(['phone' => $_SESSION['identity']]);
$user1 = $builder->get()->getResultArray();
$permissions = get_permission($user1[0]['id']);
?>
<div class="main-wrapper ">
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-header mt-2">
                <h1><?= labels("languages", "Languages") ?></h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                    <div class="breadcrumb-item "><a href="<?= base_url('/admin/settings/system-settings') ?>"><?= labels('system_settings', "System Settings") ?></a></div>
                    <div class="breadcrumb-item"><?= labels("languages", "Languages") ?></a></div>
                </div>
            </div>
            <div class="section-body">
                <div id="output-status"></div>
                <div class="row">
                    <?php if ($permissions['create']['settings'] == 1) : ?>
                        <div class="col-md-4">
                            <?= form_open('/admin/languages/insert', ['method' => "post", 'class' => 'form-submit-event', 'id' => 'add', 'enctype' => "multipart/form-data"]); ?>
                            <div class="card">
                                <div class="row m-0">
                                    <div class="col border_bottom_for_cards">
                                        <div class="toggleButttonPostition"><?= labels('add', 'Add') ?></div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="name"><?= labels('language', 'Language') ?></label>
                                                <input id="name" required class="form-control" type="text" name="language_name" placeholder="<?= labels('enter_name_of_language', 'Enter the name of the langauge here') ?>">
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="Code"><?= labels('language_code', 'Code') ?></label>
                                                <input id="name" required class="form-control" type="text" name="language_code" placeholder="<?= labels('enter_name_of_code', 'Enter the name of the code here') ?>">
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="custom-switch p-0 m-0">
                                                <div class="form-group">
                                                    <label for=""><?= labels('is_rtl', 'Is RTL ') ?></label>
                                                    <input id="is_rtl" class="custom-control-input " type="checkbox" name="is_rtl" value="true">
                                                    <label for="is_rtl" class="custom-control-label mt-3">
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group"> <label for="json"><?= labels('language_file', 'Language File') ?></label>
                                                <div class="file-upload">
                                                    <div class="file-select" style="border-radius: 0.25rem;">
                                                        <div class="file-select-button" id="fileName"><?= labels('choose_file', 'Choose File') ?></div>
                                                        <div class="file-select-name" id="noFile"><?= labels('no_file_chosen', 'No file chosen...') ?></div>
                                                        <input type="file" name="language_json" id="language_json" accept="application/json">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="row">
                                                <div class="col text-dark">
                                                    <?= labels('download_sample_json_file', 'Download sample json File') ?>
                                                </div>
                                                <div class="col d-flex justify-content-end">
                                                    <a class="" href="<?= APP_URL ?>download_sample_file">
                                                        <span class="material-symbols-outlined text-new-primary">
                                                            download_for_offline
                                                        </span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md d-flex justify-content-end">
                                            <button type="submit" class="btn btn-lg bg-new-primary submit_btn"><?= labels('submit', 'Submit') ?></button>
                                            <?= form_close() ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($permissions['read']['settings'] == 1) : ?>
                        <div class="col d-flex w-100">
                            <div class="card w-100">
                                <div class="col mb-3" style="border-bottom: solid 1px #e5e6e9;">
                                    <div class="toggleButttonPostition"><?= labels('language_settings', 'Language Settings') ?></div>
                                </div>
                                <div class="card-body">
                                    <div class="col-md-12">
                                        <table class="table " id="language_list" data-pagination="true" data-pagination-successively-size="2" data-detail-formatter="user_formater" data-auto-refresh="true" data-toggle="table" data-url="<?= base_url("admin/language/list") ?>" data-side-pagination="server" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-sort-name="id" data-sort-order="DESC" data-query-params="orders_query">
                                            <thead>
                                                <tr>
                                                    <th data-field="id" class="text-center" data-sortable="true"><?= labels('id', 'ID') ?></th>
                                                    <th data-field="language" class="text-center"><?= labels('name', 'Name') ?></th>
                                                    <th data-field="code" class="text-center"><?= labels('language_code', 'Code') ?></th>
                                                    <th data-field="is_rtl" class="text-center"><?= labels('is_rtl', 'Is RTL') ?></th>
                                                    <th data-field="default" class="text-center" data-sortable="true"><?= labels('default', 'Default') ?></th>
                                                    <th data-field="operations" class="text-center" data-events="language_events"><?= labels('operations', 'Operations') ?></th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</div>
<!-- update modal -->
<div class="modal fade" id="update_modal" tabindex="-1" aria-labelledby="update_modal_thing" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"><?= labels('update_language', 'Update Language') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?= form_open('admin/language/update', ['method' => "post", 'id' => 'edit_language', 'enctype' => "multipart/form-data"]); ?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="edit_name"><?= labels('name', 'Name') ?></label>
                            <input id="edit_name" required class="form-control" type="text" name="edit_name" placeholder="Enter the name of the language here">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="edit_code"><?= labels('language_code', 'Code') ?></label>
                            <input id="edit_code" class="form-control" type="text" name="edit_code" placeholder="Enter the name of the code here">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="custom-switch p-0 m-0">
                            <div class="form-group">
                                <label for=""><?= labels('is_rtl', 'Is RTL ') ?></label>
                                <input id="is_rtl_edit" class="custom-control-input " type="checkbox" name="is_rtl" value="true">
                                <label for="is_rtl_edit" class="custom-control-label mt-3">
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="id" id="id">
                <div class="row">
                    <div class="col-md-6">
                        <div class="file-upload">
                            <div class="file-select" style="border-radius: 0.25rem;">
                                <div class="file-select-button" id="update_fileName"><?= labels('choose_file', 'Choose File') ?></div>
                                <div class="file-select-name" id="update_noFile"><?= labels('no_file_chosen', 'No file chosen...') ?></div>
                                <input type="file" name="update_language_json" id="update_language_json" accept="application/json">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <a class="btn btn-primary" id="download_old_file"><?= labels('download_old_file', 'Download Old File') ?></a>
                        </div>
                    </div>
                </div>
                <div class="row">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><?= labels('update_language', 'Update Language') ?></button>
                <?php form_close() ?>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= labels('close', 'Close') ?></button>
            </div>
        </div>
    </div>
</div>
<script>
    $('#language_json').bind('change', function() {
        var filename = $("#language_json").val();

        if (/^\s*$/.test(filename)) {
            $(".file-upload").removeClass('active');
            $("#noFile").text("No file chosen...");
        } else {
            $(".file-upload").addClass('active');
            $("#noFile").text(filename.replace("C:\\fakepath\\", ""));
        }
    });
    $('#update_language_json').bind('change', function() {
        var filename = $("#update_language_json").val();

        if (/^\s*$/.test(filename)) {
            $(".file-upload").removeClass('active');
            $("#update_noFile").text("No file chosen...");
        } else {
            $(".file-upload").addClass('active');
            $("#update_noFile").text(filename.replace("C:\\fakepath\\", ""));
        }
    });
    window.language_events = {
        'click .delete-language': function(e, value, row, index) {

            var id = row.id;
            Swal.fire({
                title: "<?= labels('are_your_sure', 'Are you sure?') ?>",
                text: you_wont_be_able_to_revert_this,
                icon: 'error',
                showCancelButton: true,
                confirmButtonText: 'Yes, Proceed!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post(
                        baseUrl + "/admin/language/remove_langauge", {
                            [csrfName]: csrfHash,
                            id: id,
                        },
                        function(data) {
                            csrfName = data.csrfName;
                            csrfHash = data.csrfHash;

                            if (data.error == false) {
                                showToastMessage(data.message, "success");
                                setTimeout(() => {
                                    $('#language_list').bootstrapTable('refresh')
                                }, 2000)
                                return;
                            } else {
                                return showToastMessage(data.message, "error");
                            }
                        }
                    )
                }
            });
        },
        'click .edit-language': function(e, value, row, index) {

            $('#id').val(row.id);
            $('#edit_id').val(row.id);
            $("#edit_name").val(row.language);
            $("#edit_code").val(row.code);
            if (row.is_rtl_og == "1") {
                $("#is_rtl_edit").attr("checked", true);
            } else {
                $("#is_rtl_edit").attr("checked", false);
            }
            document.getElementById(id = "download_old_file").href = "<?= APP_URL ?>download_old_file/" + row.code;
        },
    };
</script>
<script type="text/javascript">
    $(document).on('click', '.store_default_language', function() {
        var id = $(this).data("id");
        var base_url = baseUrl;
        $.ajax({
            url: baseUrl + "/admin/language/store_default_language",
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
                    $("#language_list").bootstrapTable("refresh");
                    location.reload();
                }
            }
        });
    });
</script>