<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link rel="icon" href="<?= base_url() . 'public/uploads/site/' . $settings['partner_favicon'] ?>" type="image/gif" sizes="16x16">
</head>

<body><?php
    print_R($privacy_policy['privacy_policy']);?>
</body>

</html>