<!DOCTYPE HTML>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="format-detection" content="date=no">
    <meta name="format-detection" content="telephone=no">
    <style type="text/CSS"></style>


</head>

<body>






    <div role="article" aria-roledescription="email" lang="en">
        <table role="presentation">
            <tr>
                <td align="center" style="padding:0;">

                    <table role="presentation" align="center">
                        <tr>
                            <td>
                                <table role="presentation">
                                    <tr>
                                        <td>
                                            <img src=" https://edemand-test.thewrteam.in/public/uploads/site/1655699574_7fd61254c6132ebfd8ce.svg" alt="">
                                            <h1 style="margin-top:0;margin-bottom:1.38em;font-size:1.953em;line-height:1.3;font-weight:bold;letter-spacing:-0.02em;">
                                                <p><?= $title ?></p>
                                            </h1>
                                            <br>
                                            <p style="margin:0;">Dear <?php echo $name ?>,</p>
                                            <br>
                                            <p><?= $first_paragraph ?></p>

                                            <p><?php
                                                if (!empty($provider_name)) {
                                                    echo ("Company Name : " . $provider_name);
                                                } ?>
                                            </p>
                                            <p>
                                                <?php
                                                if (!empty($provider_email)) {
                                                    echo ("Email : " . $provider_email);
                                                } ?>
                                            </p>
                                            <p>
                                                <?php
                                                if (!empty($provoder_phone)) {
                                                    echo ("Phone : " . $provoder_phone);
                                                } ?>
                                            </p>
                                            <p>
                                                <?php
                                                if (!empty($order_id)) {
                                                    echo ("Order Id : " . $order_id);
                                                } ?>
                                            </p>
                                            <p><?= $second_paragraph ?></p>
                                            <p><?= $third_paragraph ?></p>

                                            <p>Thanks.</p>
                                            <!-- <p>Best regards,</p>
                                            <p><?= $company_name ?></p> -->

                                        </td>
                                    </tr>

                                </table>

                            </td>
                        </tr>
                    </table>

                </td>
            </tr>
        </table>
    </div>
</body>

</html>