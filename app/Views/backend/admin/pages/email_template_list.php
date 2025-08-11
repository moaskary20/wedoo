<!-- Main Content -->
<div class="main-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('email_configuration', "Email Configuration") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item "><a href="<?= base_url('/admin/settings/system-settings') ?>"><?= labels('system_settings', "System Settings") ?></a></div>
                <div class="breadcrumb-item"><?= labels('email_configuration', "Email Configuration") ?></div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12 col-sm-12 col-xl-12">
                <div class="card h-100">
                    <div class="card-body">
                        <table class="table" data-fixed-columns="true"  id="category_list" data-pagination-successively-size="2" data-detail-formatter="category_formater" data-query-params="category_query_params" data-auto-refresh="true" data-toggle="table" data-url="<?= base_url("admin/settings/email_template_list_fetch") ?>" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-search="false" data-show-columns="false" data-show-columns-search="true" data-show-refresh="false" data-sort-name="id" data-sort-order="desc">
                            <thead>
                                <tr>
                                    <th data-field="id" data-visible="true" class="text-center" data-sortable="true"><?= labels('id', 'ID') ?></th>
                                    <th data-field="type" class="text-center"><?= labels('type', 'Type') ?></th>
                                    <th data-field="subject" class="text-center" data-sortable="true"><?= labels('template', 'Template') ?></th>
                                    <th data-field="operations" class="text-center" data-events="email_template_actions_events"><?= labels('operations', 'Operations') ?></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>