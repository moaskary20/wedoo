<?php
$session = \Config\Services::session();
$is_rtl = $session->get('is_rtl');
$language = $session->get('language');
$default_language = fetch_details('languages', ['is_default' => '1']);

// Only check default language's RTL status if no language is set in session
// Otherwise, use the explicit is_rtl value from session
if (empty($language) && !isset($is_rtl)) {
    $is_rtl = $default_language[0]['is_rtl'];
} elseif ($is_rtl === null) {
    // Fallback if is_rtl is not set but language is
    $is_rtl = 0;
}

// Convert to integer value for consistency
$is_rtl = (int)$is_rtl;
?>



<?php
if ($is_rtl == 1) {  ?>
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/rtl_css/rtl_bootstrap.min.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/rtl_css/rtl_bootstrap-table.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/rtl_css/rtl_iziToast.min.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/rtl_css/rtl_daterangepicker.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/rtl_css/rtl_select2_min_css.min.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/rtl_css/rtl_dropzone.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/rtl_css/rtl_style.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/googleMap.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/rtl_css/rtl_components.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/vendor/cropper.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/rtl_css/rtl_custom.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/rtl_css/rtl_switchery.min.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/rtl_css/rtl_chat.css') ?>" />
<?php } else { ?>
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/vendor/bootstrap.min.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/vendor/bootstrap-table.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/vendor/iziToast.min.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/vendor/daterangepicker.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/vendor/select2.min.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/vendor/dropzone.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/style.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/googleMap.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/components.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/vendor/cropper.css') ?>" />
    <link rel="stylesheet" href="<?= base_url("public/backend/assets/css/custom.css") ?>">
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/switchery.min.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/chat.css') ?>" />
<?php } ?>
<link rel="stylesheet" href="<?= base_url('public/fontawesome/css/all.css') ?>" />
<?php $data = get_settings('general_settings', true); ?>
<link href="<?= isset($data['partner_favicon']) && $data['partner_favicon'] != "" ? base_url("public/uploads/site/" . $data['partner_favicon']) : base_url('public/backend/assets/img/news/img01.jpg') ?>" rel="icon" />
<link href="<?= base_url("public/frontend/retro/img/site/apple-touch-icon.png") ?>" rel="apple-touch-icon" />
<link rel="stylesheet" href="https://unpkg.com/@yaireo/tagify/dist/tagify.css" />
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">
<link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet">
<!-- filepond Css -->
<link href="<?= base_url('public/backend/assets/js/filepond/dist/filepond.css') ?>" rel="stylesheet" type="text/css" />
<link href="<?= base_url('public/backend/assets/js/filepond/dist/filepond-plugin-image-preview.css') ?>" rel="stylesheet" type="text/css" />
<link href="<?= base_url('public/backend/assets/js/filepond/dist/filepond-plugin-pdf-preview.min.css') ?>" rel="stylesheet" type="text/css" />
<link href="<?= base_url('public/backend/assets/js/filepond/dist/filepond-plugin-media-preview.css') ?>" rel="stylesheet" type="text/css" />
<link href="<?= base_url('public/backend/assets/js/filepond/dist/filepond-plugin-media-preview.min.css') ?>" rel="stylesheet" type="text/css" />
<!-- filepond Css -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
<!-- switchery css -->
<!-- <link href="http://abpetkov.github.io/switchery/dist/switchery.min.css" rel="stylesheet" /> -->
<!-- switchery css -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.3/dist/extensions/fixed-columns/bootstrap-table-fixed-columns.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/rateYo/2.3.2/jquery.rateyo.min.css">
<script src="<?= base_url('public/backend/assets/js/vendor/jquery.min.js') ?>"></script>
<style>
    .tagify {
        width: 100%;
        max-width: 700px;
    }
</style>
<script>
    var baseUrl = '<?= base_url() ?>';
    var csrfName = '<?= csrf_token() ?>';
    var csrfHash = '<?= csrf_hash() ?>';
</script>