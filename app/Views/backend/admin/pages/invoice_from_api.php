<html>

<head>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10pt;
        }

        p {
            margin: 0pt;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }

        .items td {
            vertical-align: top;
        }

        table thead td {
            background-color: #2560FC;
            color: #ffffff;
            font-size: larger;
            text-align: left;
        }

        .items td.totals,
        .items td.subtotal,
        .items td.tax,
        .items td.amount {
            text-align: right;
        }

        .text-primary {
            color: #2560FC;
        }

        .provider-image {
            height: 80px;
            width: 80px;
        }
    </style>
</head>

<body>
    <table width="100%" cellpadding="10">
        <tr>
            <td width="45%">
                <img height="100px" width="200px" src="<?= isset($data['logo']) && $data['logo'] != "" ? base_url('public/uploads/site/' . $data['logo']) : base_url('public/backend/assets/img/news/img01.jpg') ?>" alt="">
            </td>
            <td width="10%">&nbsp;</td>
            <td width="45%" style="text-align: right">
                <h2 class="text-primary">INVOICE</h2>
                <p>Invoice no: #INVO-<?= $order['id'] ?></p>
                <p>Invoice Date: <?= (new DateTime($order['created_at']))->format('d-m-Y') ?></p>
                <p>Status: <?= $order['status'] ?></p>
            </td>
        </tr>
    </table>

    <table width="100%" cellpadding="10">
        <tr>
            <td width="45%">
                <strong>SERVICE BY:</strong>
                <p>Name: <?= $partner_details['company_name'] ?></p>
                <p>Email: <?= $partner_details['email'] ?></p>
                <p>Phone: <?= $partner_details['phone'] ?></p>
                <p>Address: <?= $partner_details['address'] ?></p>
            </td>
            <td width="10%">&nbsp;</td>
            <td width="45%" style="text-align: right">
                <strong>BILLING ADDRESS:</strong>
                <p>Name: <?= $user_details['username'] ?></p>
                <p>Email: <?= $user_details['email'] ?></p>
                <p>Phone: <?= $user_details['phone'] ?></p>
            </td>
        </tr>
    </table>

    <br />

    <table class="items" cellpadding="8">
        <thead>
            <tr>
                <td>Services</td>
                <td>Price</td>
                <td>Discount</td>
                <td>Net Amount</td>
                <td>Tax</td>
                <td>Tax Amount</td>
                <td>Quantity</td>
                <td>Sub total (Including Tax)</td>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= $r['service_title']; ?></td>
                    <td><?= $r['price'] ?></td>
                    <td><?= $r['discount'] ?></td>
                    <td><?= $r['net_amount'] ?></td>
                    <td><?= $r['tax'] ?></td>
                    <td><?= $r['tax_amount'] ?></td>
                    <td><?= $r['quantity'] ?></td>
                    <td><?= $r['subtotal'] ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="6"></td>
                <td>Total</td>
                <td><?= $currency . $order['total'] ?></td>
            </tr>
            <tr>
                <td colspan="6"></td>
                <td>Visiting Charges</td>
                <td><?= $currency . $order['visiting_charges'] ?></td>
            </tr>
            <tr>
                <td colspan="6"></td>
                <td>Promo Code Discount</td>
                <td><?= $currency . $order['promo_discount'] ?></td>
            </tr>



            <?php
            if (!empty($order['additional_charges']) && !empty(json_decode($order['additional_charges'], true))) {


                foreach ((json_decode($order['additional_charges'], true)) as $key => $charge) { ?>
                    <tr>

                        <td colspan="6"></td>
                        <td>
                            <?= !empty($charge['name']) ? $charge['name'] : 'N/A' ?>
                        </td>
                        <td> <?= !empty($charge['charge']) ? $currency . $charge['charge'] : $currency . '0' ?></td>
                    </tr>

            <?php }
            } ?>
            <tr>
                <td colspan="6"></td>
                <td>
                    <h4>Final Total</h4>
                </td>
                <td><strong><?= $currency . $order['final_total'] ?></strong></td>
            </tr>


        </tbody>
    </table>

    <img class="provider-image" src="<?= $partner_details['image'] ?>" alt="Partner Logo">
    <p>Thank you for your business!</p>
</body>

</html>