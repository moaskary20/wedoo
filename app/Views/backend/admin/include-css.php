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
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/rtl_css/rtl_admin_css.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/rtl_css/rtl_bootstrap-table.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/rtl_css/rtl_bootstrap.min.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/rtl_css/rtl_summernote.min.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/fontawesome/css/all.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/rtl_css/rtl_style.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/rtl_css/rtl_iziToast.min.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/rtl_css/rtl_daterangepicker.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/rtl_css/rtl_switchery.min.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/rtl_css/rtl_select2_min_css.min.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/rtl_css/rtl_components.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/rtl_css/rtl_dropzone.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/rtl_css/rtl_chat.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/js/filepond/dist/filepond.css') ?>" rel="stylesheet" type="text/css" />
    <!-- <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/rtl_css/rtl_filepond.css') ?>" rel="stylesheet" type="text/css" /> -->
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/js/filepond/dist/filepond-plugin-image-preview.css') ?>" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/js/filepond/dist/filepond-plugin-pdf-preview.min.css') ?>" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/js/filepond/dist/filepond-plugin-media-preview.css') ?>" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/js/filepond/dist/filepond-plugin-media-preview.min.css') ?>" rel="stylesheet" type="text/css" />
<?php } else { ?>
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/admin_css.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/vendor/bootstrap-table.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/vendor/bootstrap.min.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/vendor/summernote.min.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/fontawesome/css/all.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/style.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/vendor/iziToast.min.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/vendor/daterangepicker.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/switchery.min.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/vendor/select2.min.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/components.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/vendor/dropzone.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/chat.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/js/filepond/dist/filepond.css') ?>" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/js/filepond/dist/filepond-plugin-image-preview.css') ?>" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/js/filepond/dist/filepond-plugin-pdf-preview.min.css') ?>" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/js/filepond/dist/filepond-plugin-media-preview.css') ?>" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/js/filepond/dist/filepond-plugin-media-preview.min.css') ?>" rel="stylesheet" type="text/css" />
<?php }
?>
<script src="<?= base_url('public/backend/assets/js/vendor/jquery.min.js') ?>"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tagify/4.15.2/tagify.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.3/css/intlTelInput.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/rateYo/2.3.2/jquery.rateyo.min.js"></script>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
<link href="https://unpkg.com/bootstrap-table@1.21.4/dist/extensions/reorder-rows/bootstrap-table-reorder-rows.css" rel="stylesheet">
<?php $data = get_settings('general_settings', true); ?>
<link href="<?= isset($data['favicon']) && $data['favicon'] != "" ? base_url("public/uploads/site/" . $data['favicon']) : base_url('public/backend/assets/img/news/img01.jpg') ?>" rel="icon" />
<link href="<?= base_url("public/frontend/retro/img/site/apple-touch-icon.png") ?>" rel="apple-touch-icon" />
<script>
    var baseUrl = '<?= base_url() ?>';
    var csrfName = '<?= csrf_token() ?>';
    var csrfHash = '<?= csrf_hash() ?>';
</script>
<script>
    <?php $firebase_setting = get_settings('firebase_settings', true); ?>
    let apiKey = "<?= isset($firebase_setting['apiKey']) ? $firebase_setting['apiKey'] : '1' ?>"
    let authDomain = "<?= isset($firebase_setting['authDomain']) ? ($firebase_setting['authDomain']) : 0 ?>"
    let projectId = "<?= isset($firebase_setting['projectId']) ? $firebase_setting['projectId'] : 0 ?>"
    let storageBucke = "<?= isset($firebase_setting['storageBucket']) ? $firebase_setting['storageBucket'] : 0 ?>"
    let messagingSenderId = "<?= isset($firebase_setting['messagingSenderId']) ? $firebase_setting['messagingSenderId'] : 0 ?>"
    let appId = "<?= isset($firebase_setting['appId']) ? $firebase_setting['appId'] : 0 ?>"
    let measurementId = "<?= isset($firebase_setting['measurementId']) ? $firebase_setting['measurementId'] : 0 ?>"
</script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.3/dist/extensions/fixed-columns/bootstrap-table-fixed-columns.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/uikit@3.9.4/dist/js/uikit.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/uikit@3.9.4/dist/js/uikit-icons.min.js"></script>