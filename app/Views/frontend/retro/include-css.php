
<?php
if(!isset($meta_description))
    $meta_description="";
if(!isset($meta_keywords))
    $meta_keywords="";
 
?>


<meta content="<?= $meta_description; ?>" name="description" />



<meta content="<?= $meta_keywords; ?>" name="keywords" />
<!-- Favicons -->
<?php
$data = [];
try {
    helper('function');
    $data = get_settings('general_settings', true);
} catch (Exception $e) {
    echo "<script>console.log('$e')</script>";
}
?>
<link href="<?= isset($data['favicon']) && $data['favicon'] != "" ? base_url("public/uploads/site/" . $data['favicon']) : base_url('public/backend/assets/img/news/img01.jpg') ?>" rel="icon" />
<link href="<?= base_url("public/frontend/retro/img/site/apple-touch-icon.png") ?>" rel="apple-touch-icon" />
<link rel="stylesheet" href="<?= base_url('public/backend/assets/css/vendor/iziToast.min.css') ?>" />


<link rel="stylesheet" href="<?= base_url('public/backend/assets/css/vendor/iziToast.min.css') ?>" />


<!-- Vendor CSS Files -->
<link href="<?= base_url('public/frontend/retro/vendor/bootstrap/css/bootstrap.min.css') ?>" rel="stylesheet" />
<link href="<?= base_url("public/frontend/retro/vendor/bootstrap-icons/bootstrap-icons.css") ?>" rel="stylesheet" />
<link href="<?= base_url("public/frontend/retro/vendor/aos/aos.css") ?>" rel="stylesheet" />
<link href="<?= base_url("public/frontend/retro/vendor/swiper/swiper-bundle.min.css") ?>" rel="stylesheet" />

<!-- Template Main CSS File -->
<link rel="stylesheet" href="<?= base_url('public/backend/assets/css/vendor/select2.min.css') ?>" />
<script src="<?= base_url('public/backend/assets/js/vendor/jquery.min.js') ?>"></script>
<link rel="stylesheet" href="<?= base_url('public/fontawesome/css/all.css') ?>" />
<link href="<?= base_url("public/frontend/retro/css/style.css") ?>" rel="stylesheet" />

<!-- new styles -->
<link rel="stylesheet" href="<?= base_url('public/frontend/retro/css/style.css') ?>" />
<link rel="stylesheet" href="<?= base_url('public/frontend/retro/css/components.css') ?>" />
<link rel="stylesheet" href="<?= base_url('public/frontend/retro/css/custom.css') ?>" />

<script>
    var baseUrl = '<?= base_url() ?>';
    var csrfName = '<?= csrf_token() ?>';
    var csrfHash = '<?= csrf_hash() ?>';
</script>