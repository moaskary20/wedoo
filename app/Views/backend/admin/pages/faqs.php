<div class="main-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('faqs', "FAQs") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item"><?= labels('FAQs', 'FAQs') ?></a></div>
            </div>
        </div>
        <?= helper('form'); ?>
        <div class="row">
            <div class="col-md-4">
                <div class="container-fluid card">
                    <div class="row ">
                        <div class="col mb-12" style="border-bottom: solid 1px #e5e6e9;">
                            <div class="toggleButttonPostition"><?= labels('manage_FAQs', 'Manage FAQs') ?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <?= form_open('admin/faqs/add_faqs', ['method' => "post", 'class' => 'form-submit-event', 'id' => 'add_faqs', 'enctype' => "multipart/form-data"]); ?>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="question" class="required"><?= labels('question', "Question") ?></label>
                                    <input id="question" class="form-control" type="text" name="question" placeholder="<?= labels('enter_question', 'Enter the question here') ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="answer" class="required"><?= labels('answer', "Answer") ?></label>
                                    <textarea id="answer" style="min-height:60px" class="form-control" name="answer" placeholder="<?= labels('enter_answer', 'Enter the answer here') ?>"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary submit_btn"><?= labels('add_FAQS', "Add FAQs") ?></button>
                            </div>
                        </div>
                        <?= form_close(); ?>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="container-fluid card">
                    <div class="row ">
                        <div class="col mb-12" style="border-bottom: solid 1px #e5e6e9;">
                            <div class="toggleButttonPostition"><?= labels('FAQs', "FAQs") ?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="row mt-4 mb-3 ">
                                    <div class="col-md-4 col-sm-2 mb-2">
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="customSearch" placeholder="<?= labels('search', 'Search') ?>" aria-label="Search" aria-describedby="customSearchBtn">
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
                                            <a class="dropdown-item" onclick="custome_export('pdf','FAQs list','user_list');"><?= labels('pdf', "PDF") ?></a>
                                            <a class="dropdown-item" onclick="custome_export('excel','FAQs list','user_list');"><?= labels('excel', "Excel") ?></a>
                                            <a class="dropdown-item" onclick="custome_export('csv','FAQs list','user_list')"><?= labels('csv', "CSV") ?></a>
                                        </div>
                                    </div>
                                </div>
                                <table class="table " data-fixed-columns="true" id="user_list" data-detail-formatter="user_formater"
                                    data-auto-refresh="true" data-toggle="table"
                                    data-url="<?= base_url("admin/faqs/list") ?>" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 25, 50, 100, 200, All]"
                                    data-search="false" data-show-columns="false" data-show-columns-search="true" data-show-refresh="false" data-sort-name="id" data-sort-order="DESC"
                                    data-query-params="faqs_query_params" data-pagination-successively-size="2">
                                    <thead>
                                        <tr>
                                            <th data-field="id" class="text-center" data-sortable="true"><?= labels('id', 'ID') ?></th>
                                            <th data-field="question" class="text-center" data-sortable="true"><?= labels('question', 'Question') ?></th>
                                            <th data-field="answer" class="text-center" data-sortable="true"><?= labels('answer', 'Answer') ?></th>
                                            <th data-field="created_at" class="text-center" data-visible="false" data-sortable="true"><?= labels('created_at', 'Created At') ?></th>
                                            <th data-field="operations" class="text-center" data-events="faqs_events"><?= labels('operations', 'Operations') ?></th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- update modal -->
    <div class="modal fade" id="update_modal" tabindex="-1" aria-labelledby="update_modal_thing" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"><?= labels('edit_FAQs', 'Edit FAQs') ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?= form_open('admin/faqs/edit_faqs', ['method' => "post", 'class' => 'form-submit-event', 'id' => 'edit_faqs', 'enctype' => "multipart/form-data"]); ?>
                    <input type="hidden" name="id" id="id">
                    <div class="form-group">
                        <label for="title" class="required"><?= labels('question', "Question") ?></label>
                        <input id="edit_question" class="form-control" type="question" name="question" placeholder="Enter the title here">
                    </div>
                    <div class="form-group">
                        <label for="title" class="required"><?= labels('answer', "Answer") ?></label>
                        <textarea id="edit_answer" style="min-height:60px" class="form-control col-md-12" name="answer" placeholder="Enter The Answer Here"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" name="submit"><?= labels('save_changes', "Save changes") ?></button>
                    <?php form_close() ?>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= labels('close', "Close") ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $("#customSearch").on('keydown', function() {
        $('#user_list').bootstrapTable('refresh');
    });
</script>