<!-- Main Content -->
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
            <h1><?= labels('payment_gateway', "Payment Gateways") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item "><a href="<?= base_url('/admin/settings/system-settings') ?>"><?= labels('system_settings', "System Settings") ?></a></div>
                <div class="breadcrumb-item"></i> <?= labels('payment_gateways', 'Payment Gateways Settings') ?></div>
            </div>
        </div>
        <form method="POST" action="<?= base_url('admin/settings/pg-settings') ?>">
            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="card h-100">
                        <div class="row pl-3">
                            <div class="col border_bottom_for_cards">
                                <div class="toggleButttonPostition"><?= labels('payment_settings', 'Payment Settings') ?></div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mt-2">
                                    <div class="form-group">
                                        <label for="cod_setting" class="required"><?= labels('is_pay_later_allowed', 'Is Pay Later Allowed') ?>?</label>
                                        <i data-content=" <?= labels('data_content_for__is_pay_later_allowed', 'If you enable the Pay Later option, customers can book services using this feature if it\'s enabled for the service. If you choose to allow only the Pay Later option without enabling online payment, customers will automatically see the Pay Later option by default, regardless of its availability for the service. In that case Providers won\'t have the option to enable or disable Pay Later when adding or editing a service.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                                        <?php if (isset($cod_setting) && $cod_setting == "1") { ?>
                                            <input type="checkbox" id="cod_setting" name="cod_setting" class="status-switch" checked>
                                        <?php } else { ?>
                                            <input type="checkbox" id="cod_setting" name="cod_setting" class="status-switch">
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-2">
                                    <div class="form-group">
                                        <label for="payment_gateway_setting" class="required"><?= labels('is_online_payment_allowed', ' Is Online Payment Allowed') ?>?</label>
                                        <i data-content=" <?= labels('data_content_for_isonline_payment_allowed', 'If you enable online payment, customers will have the option to book their service as prepaid. If you prefer not to allow this, please disable this setting.') ?>" class="fa fa-question-circle" data-original-title="" title=""></i>
                                        <?php if (isset($payment_gateway_setting) && $payment_gateway_setting == "1") { ?>
                                            <input type="checkbox" id="payment_gateway_setting" name="payment_gateway_setting" class="status-switch" checked>
                                        <?php } else { ?>
                                            <input type="checkbox" id="payment_gateway_setting" name="payment_gateway_setting" class="status-switch">
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" id="all_payment_gateways">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class=" card  px-3">
                        <div class="row border_bottom_for_cards mb-3">
                            <div class="col">
                                <div class='toggleButttonPostition'><?= labels('paypal', 'Paypal') ?></div>
                            </div>
                            <div class="col d-flex justify-content-end  mt-4 ">
                                <input type="checkbox" id="paypal_status" class="status-switch" name="paypal_status" <?= isset($paypal_status) && $paypal_status === 'enable' ? 'checked' : '' ?>>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-4">
                                <label for=""><?= labels('payment_mode', 'Payment Mode') ?> <small>[ <?= labels('sanbox_or_live', 'sandbox / live') ?> ]</small>
                                </label>
                                <select name="paypal_mode" class="form-control" required>
                                    <option value="">Select Mode</option>
                                    <option value="sandbox" <?= (isset($paypal_mode) && $paypal_mode == 'sandbox') ? 'selected' : '' ?>>Sandbox ( Testing )</option>
                                    <option value="production" <?= (isset($paypal_mode) && $paypal_mode == 'production') ? 'selected' : '' ?>>Production ( Live )</option>
                                </select>
                            </div>
                            <div class="form-group col-4">
                                <label for="paypal_business_email"><?= labels('paypal_business_email', 'Paypal Business Email') ?></label>
                                <input type="text" class="form-control" name="paypal_business_email"

                                    value="<?= isset($paypal_business_email) ? ((defined('ALLOW_VIEW_KEYS') && ALLOW_VIEW_KEYS == 0) ? "asc****************adaca" : $paypal_business_email) : '' ?>" />
                            </div>
                            <div class="form-group col-4">
                                <label for="currency_code"><?= labels('currency_code', 'Currency Code') ?></label>
                                <select class="form-control" name="paypal_currency_code" ?>">
                                    <option value="AUD" <?= (isset($paypal_currency_code) && $paypal_currency_code == "AUD") ? "selected" : '' ?>>AUD</option>
                                    <option value="BRL" <?= (isset($paypal_currency_code) && $paypal_currency_code == "BRL") ? "selected" : '' ?>>BRL</option>
                                    <option value="CAD" <?= (isset($paypal_currency_code) && $paypal_currency_code == "CAD") ? "selected" : '' ?>>CAD</option>
                                    <option value="CNY" <?= (isset($paypal_currency_code) && $paypal_currency_code == "CNY") ? "selected" : '' ?>>CNY</option>
                                    <option value="CZK" <?= (isset($paypal_currency_code) && $paypal_currency_code == "CZK") ? "selected" : '' ?>>CZK</option>
                                    <option value="DKK" <?= (isset($paypal_currency_code) && $paypal_currency_code == "DKK") ? "selected" : '' ?>>DKK</option>
                                    <option value="EUR" <?= (isset($paypal_currency_code) && $paypal_currency_code == "EUR") ? "selected" : '' ?>>EUR</option>
                                    <option value="HKD" <?= (isset($paypal_currency_code) && $paypal_currency_code == "HKD") ? "selected" : '' ?>>HKD</option>
                                    <option value="HUF" <?= (isset($paypal_currency_code) && $paypal_currency_code == "HUF") ? "selected" : '' ?>>HUF</option>
                                    <option value="INR" <?= (isset($paypal_currency_code) && $paypal_currency_code == "INR") ? "selected" : '' ?>>INR</option>
                                    <option value="ILS" <?= (isset($paypal_currency_code) && $paypal_currency_code == "ILS") ? "selected" : '' ?>>ILS</option>
                                    <option value="JPY" <?= (isset($paypal_currency_code) && $paypal_currency_code == "JPY") ? "selected" : '' ?>>JPY</option>
                                    <option value="MYR" <?= (isset($paypal_currency_code) && $paypal_currency_code == "MYR") ? "selected" : '' ?>>MYR</option>
                                    <option value="MXN" <?= (isset($paypal_currency_code) && $paypal_currency_code == "MXN") ? "selected" : '' ?>>MXN</option>
                                    <option value="TWD" <?= (isset($paypal_currency_code) && $paypal_currency_code == "TWD") ? "selected" : '' ?>>TWD</option>
                                    <option value="NZD" <?= (isset($paypal_currency_code) && $paypal_currency_code == "NZD") ? "selected" : '' ?>>NZD</option>
                                    <option value="NOK" <?= (isset($paypal_currency_code) && $paypal_currency_code == "NOK") ? "selected" : '' ?>>NOK</option>
                                    <option value="PHP" <?= (isset($paypal_currency_code) && $paypal_currency_code == "PHP") ? "selected" : '' ?>>PHP</option>
                                    <option value="PLN" <?= (isset($paypal_currency_code) && $paypal_currency_code == "PLN") ? "selected" : '' ?>>PLN</option>
                                    <option value="GBP" <?= (isset($paypal_currency_code) && $paypal_currency_code == "GBP") ? "selected" : '' ?>>GBP</option>
                                    <option value="RUB" <?= (isset($paypal_currency_code) && $paypal_currency_code == "RUB") ? "selected" : '' ?>>RUB</option>
                                    <option value="SGD" <?= (isset($paypal_currency_code) && $paypal_currency_code == "SGD") ? "selected" : '' ?>>SGD</option>
                                    <option value="SEK" <?= (isset($paypal_currency_code) && $paypal_currency_code == "SEK") ? "selected" : '' ?>>SEK</option>
                                    <option value="CHF" <?= (isset($paypal_currency_code) && $paypal_currency_code == "CHF") ? "selected" : '' ?>>CHF</option>
                                    <option value="THB" <?= (isset($paypal_currency_code) && $paypal_currency_code == "THB") ? "selected" : '' ?>>THB</option>
                                    <option value="USD" <?= (isset($paypal_currency_code) && $paypal_currency_code == "USD") ? "selected" : '' ?>>USD</option>
                                </select>
                            </div>
                            <div class="form-group col-4 ">
                                <label><?= labels('notification_url', 'Notification URL') ?> <small>(<?= labels('set_ipn_notification_url', 'Set this as IPN notification URL in you PayPal account') ?>)</small></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="paypal_webhook_url" readonly value="<?= base_url('api/webhooks/paypal') ?>" />
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('paypal_webhook_url')">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="endpoint"><?= labels('website_url', 'Website URL') ?></label>
                                    <div class="input-group">
                                        <input type="text" value="<?= isset($paypal_website_url) ? ((defined('ALLOW_VIEW_KEYS') && ALLOW_VIEW_KEYS == 0) ? "asc****************adaca" : $paypal_website_url) : '' ?>" name='paypal_website_url' id='paypal_website_url' class="form-control"  />
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('paypal_website_url')">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-4 ">
                                <label><?= labels('client_key', 'Client Key') ?></label>
                                <input type="text" class="form-control" name="paypal_client_key"
                                    value="<?= isset($paypal_client_key) ? ((defined('ALLOW_VIEW_KEYS') && ALLOW_VIEW_KEYS == 0) ? "asc****************adaca" : $paypal_client_key) : '' ?>" />
                            </div>
                            <div class="form-group col-4 ">
                                <label><?= labels('secret_key', 'Secret Key') ?></label>
                                <input type="text" class="form-control" name="paypal_secret_key"
                                    value="<?= isset($paypal_secret_key) ? ((defined('ALLOW_VIEW_KEYS') && ALLOW_VIEW_KEYS == 0) ? "asc****************adaca" : $paypal_secret_key) : '' ?>" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 ">
                    <div class="card px-3">
                        <div class="row border_bottom_for_cards mb-3">
                            <div class="col ">
                                <div class='toggleButttonPostition'><?= labels('razorpay', 'RazorPay') ?></div>
                            </div>
                            <div class="col d-flex justify-content-end  mt-4 ">
                                <input type="checkbox" id="razorpayApiStatus" class="status-switch" name="razorpayApiStatus" <?= isset($razorpayApiStatus) && $razorpayApiStatus === 'enable' ? 'checked' : '' ?>>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="razorpayMode"><?= labels('mode', 'Mode') ?></label>
                                    <select class='form-control selectric' name='razorpay_mode' id='razorpay_mode'>
                                        <option value='test' <?= isset($razorpay_mode) && $razorpay_mode === 'test' ? 'selected' : '' ?>>Test</option>
                                        <option value='live' <?= isset($razorpay_mode) && $razorpay_mode === 'live' ? 'selected' : '' ?>>Live</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="razorpayMode"><?= labels('currency_code', 'Currency Code') ?></label>
                                    <!-- <input type="text" value="<?= isset($razorpay_currency) ? $razorpay_currency : '' ?>" name='razorpay_currency' id='razorpay_currency' placeholder='Enter Razorpay currency' class="form-control" /> -->
                                    <select class="form-control" name="razorpay_currency" id="">
                                        <?php
                                        $currencies = [
                                            'AED' => 'United Arab Emirates Dirham',
                                            'ALL' => 'Albanian lek',
                                            'AMD' => 'Armenian dram',
                                            'ARS' => 'Argentine peso',
                                            'AUD' => 'Australian dollar',
                                            'AWG' => 'Aruban florin',
                                            'BBD' => 'Barbadian dollar',
                                            'BDT' => 'Bangladeshi taka',
                                            'BMD' => 'Bermudian dollar',
                                            'BND' => 'Brunei dollar',
                                            'BOB' => 'Bolivian boliviano',
                                            'BSD' => 'Bahamian dollar',
                                            'BWP' => 'Botswana pula',
                                            'BZD' => 'Belize dollar',
                                            'CAD' => 'Canadian dollar',
                                            'CHF' => 'Swiss franc',
                                            'CNY' => 'Chinese yuan renminbi',
                                            'COP' => 'Colombian peso',
                                            'CRC' => 'Costa Rican colon',
                                            'CUP' => 'Cuban peso',
                                            'CZK' => 'Czech koruna',
                                            'DKK' => 'Danish krone',
                                            'DOP' => 'Dominican peso',
                                            'DZD' => 'Algerian dinar',
                                            'EGP' => 'Egyptian pound',
                                            'ETB' => 'Ethiopian birr',
                                            'EUR' => 'European euro',
                                            'FJD' => 'Fijian dollar',
                                            'GBP' => 'Pound sterling',
                                            'GHS' => 'Ghanian Cedi',
                                            'GIP' => 'Gibraltar pound',
                                            'GMD' => 'Gambian dalasi',
                                            'GTQ' => 'Guatemalan quetzal',
                                            'GYD' => 'Guyanese dollar',
                                            'HKD' => 'Hong Kong dollar',
                                            'HNL' => 'Honduran lempira',
                                            'HRK' => 'Croatian kuna',
                                            'HTG' => 'Haitian gourde',
                                            'HUF' => 'Hungarian forint',
                                            'IDR' => 'Indonesian rupiah',
                                            'ILS' => 'Israeli new shekel',
                                            'INR' => 'Indian rupee',
                                            'JMD' => 'Jamaican dollar',
                                            'KES' => 'Kenyan shilling',
                                            'KGS' => 'Kyrgyzstani som',
                                            'KHR' => 'Cambodian riel',
                                            'KYD' => 'Cayman Islands dollar',
                                            'KZT' => 'Kazakhstani tenge',
                                            'LAK' => 'Lao kip',
                                            'LKR' => 'Sri Lankan rupee',
                                            'LRD' => 'Liberian dollar',
                                            'LSL' => 'Lesotho loti',
                                            'MAD' => 'Moroccan dirham',
                                            'MDL' => 'Moldovan leu',
                                            'MKD' => 'Macedonian denar',
                                            'MMK' => 'Myanmar kyat',
                                            'MNT' => 'Mongolian tugrik',
                                            'MOP' => 'Macanese pataca',
                                            'MUR' => 'Mauritian rupee',
                                            'MVR' => 'Maldivian rufiyaa',
                                            'MWK' => 'Malawian kwacha',
                                            'MXN' => 'Mexican peso',
                                            'MYR' => 'Malaysian ringgit',
                                            'NAD' => 'Namibian dollar',
                                            'NGN' => 'Nigerian naira',
                                            'NIO' => 'Nicaraguan cordoba',
                                            'NOK' => 'Norwegian krone',
                                            'NPR' => 'Nepalese rupee',
                                            'NZD' => 'New Zealand dollar',
                                            'PEN' => 'Peruvian sol',
                                            'PGK' => 'Papua New Guinean kina',
                                            'PHP' => 'Philippine peso',
                                            'PKR' => 'Pakistani rupee',
                                            'QAR' => 'Qatari riyal',
                                            'RUB' => 'Russian ruble',
                                            'SAR' => 'Saudi Arabian riyal',
                                            'SCR' => 'Seychellois rupee',
                                            'SEK' => 'Swedish krona',
                                            'SGD' => 'Singapore dollar',
                                            'SLL' => 'Sierra Leonean leone',
                                            'SOS' => 'Somali shilling',
                                            'SSP' => 'South Sudanese pound',
                                            'SVC' => 'Salvadoran colón',
                                            'SZL' => 'Swazi lilangeni',
                                            'THB' => 'Thai baht',
                                            'TTD' => 'Trinidad and Tobago dollar',
                                            'TZS' => 'Tanzanian shilling',
                                            'USD' => 'United States dollar',
                                            'UYU' => 'Uruguayan peso',
                                            'UZS' => 'Uzbekistani so\'m',
                                            'YER' => 'Yemeni rial',
                                            'ZAR' => 'South African rand',
                                            'TRY' => 'Turkish Lira'
                                        ];

                                        foreach ($currencies as $value => $label) {
                                            $selected = (isset($razorpay_currency) && $razorpay_currency == $value) ? "selected" : "";
                                            echo "<option value=\"$value\" $selected>$label</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="razorpay_secret"><?= labels('secret_key', 'Secret Key') ?></label>
                                    <input type="text" value="<?= isset($razorpay_secret) ? ((defined('ALLOW_VIEW_KEYS') && ALLOW_VIEW_KEYS == 0) ? "asc****************adaca" : $razorpay_secret) : '' ?>" name='razorpay_secret' id='razorpay_secret' placeholder='<?= labels('enter_razorpay_secret_key', 'Enter Razor Pay secret key') ?>' class="form-control" />
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="razorpay_key"><?= labels('API_key', 'API Key') ?></label>
                                    <input type="text" value="<?= isset($razorpay_key) ? ((defined('ALLOW_VIEW_KEYS') && ALLOW_VIEW_KEYS == 0) ? "asc****************adaca" : $razorpay_key) : '' ?>" name='razorpay_key' id='razorpay_key' placeholder='<?= labels('enter_razorpay_api_key', 'Enter Razor Pay API key') ?>' class="form-control" />
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="endpoint"><?= labels('Payment_endpoint_URL', 'Payment Endpoint URL') ?><small>(<?= labels('set_this_as_endpoint_URL_in_your_razorpay_account', ' Set this as Endpoint URL in your razorpay account') ?>)</small></label>
                                    <div class="input-group">
                                        <input type="text" value="<?= base_url("/api/webhooks/razorpay") ?>" name='endpoint' id='endpoint' class="form-control" readonly />
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('endpoint')">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 ">
                    <div class=" card px-3">
                        <div class="row border_bottom_for_cards mb-3">
                            <div class="col">
                                <div class='toggleButttonPostition'><?= labels('paystack', 'Paystack') ?></div>
                            </div>
                            <div class="col d-flex justify-content-end  mt-4 ">
                                <input type="checkbox" id="paystack_status" class="status-switch" name="paystack_status" <?= isset($paystack_status) && $paystack_status === 'enable' ? 'checked' : '' ?>>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="paystack_mode"><?= labels('mode', 'Mode') ?></label>
                                    <select class='form-control selectric' name='paystack_mode' id='paystack_mode'>
                                        <option value='test' <?= isset($paystack_mode) && $paystack_mode === 'test' ? 'selected' : '' ?>>Test</option>
                                        <option value='live' <?= isset($paystack_mode) && $paystack_mode === 'live' ? 'selected' : '' ?>>Live</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="razorpayMode"><?= labels('currency_code', 'Currency Code') ?></label>
                                    <!-- <input type="text" value="<?= isset($paystack_currency) ? $paystack_currency : '' ?>" name='paystack_currency' id='paystack_currency' placeholder='Enter Paystack currency' class="form-control" /> -->
                                    <select class="form-control" name="paystack_currency" id="">
                                        <option value="GHS" <?= (isset($paystack_currency) && $paystack_currency == "GHS") ? "selected" : '' ?>>Ghana (GHS)</option>
                                        <option value="NGN" <?= (isset($paystack_currency) && $paystack_currency == "NGN") ? "selected" : '' ?>>Nigeria (NGN)</option>
                                        <option value="USD" <?= (isset($paystack_currency) && $paystack_currency == "USD") ? "selected" : '' ?>>Nigeria (USD)</option>
                                        <option value="ZAR" <?= (isset($paystack_currency) && $paystack_currency == "ZAR") ? "selected" : '' ?>>South Africa (ZAR)</option>
                                        <option value="KES" <?= (isset($paystack_currency) && $paystack_currency == "KES") ? "selected" : '' ?>>Kenya (KES)</option>
                                        <option value="USD" <?= (isset($paystack_currency) && $paystack_currency == "USD") ? "selected" : '' ?>>Kenya (USD)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="paystack_secret"><?= labels('secret_key', 'Secret Key') ?></label>
                                    <input type="text" value="<?= isset($paystack_secret) ? ((defined('ALLOW_VIEW_KEYS') && ALLOW_VIEW_KEYS == 0) ? "asc****************adaca" : $paystack_secret) : '' ?>" name='paystack_secret' id='paystack_secret' placeholder='<?= labels('enter_paystack_secret_key', 'Enter Paystack secret key') ?>' class="form-control" />
                                </div>
                            </div>
                        </div>
                        <div class="row">

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="paystack_key"><?= labels('public_key', 'Public Key') ?></label>
                                    <input type="text" value="<?= isset($paystack_key) ? ((defined('ALLOW_VIEW_KEYS') && ALLOW_VIEW_KEYS == 0) ? "asc****************adaca" : $paystack_key) : '' ?>" name='paystack_key' id='paystack_key' placeholder='<?= labels('enter_paystack_api_key', 'Enter Paystack API key') ?>' class="form-control" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="endpoint"><?= labels('Payment_endpoint_URL', 'Payment Endpoint URL') ?><small> (<?= labels('set_this_as_endpoint_URL_in_your_paystack_account', 'Set this as Endpoint URL in your paystack account') ?>)</small></label>
                                    <div class="input-group">
                                        <input type="text" value="<?= base_url("api/webhooks/paystack") ?>" name='paystack_endpoint' id='endpoint' class="form-control" readonly />
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('paystack_endpoint')">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 ">
                    <div class="card px-3">
                        <div class="row border_bottom_for_cards mb-3">
                            <div class="col ">
                                <div class='toggleButttonPostition'><?= labels('stripe', 'Stripe') ?></div>
                            </div>
                            <div class="col d-flex justify-content-end  mt-4 ">
                                <input type="checkbox" id="stripe_status" class="status-switch" name="stripe_status" <?= isset($stripe_status) && $stripe_status === 'enable' ? 'checked' : '' ?>>
                                <div></div>

                            </div>
                        </div>
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="razorpayMode"><?= labels('mode', 'Mode') ?></label>
                                    <select class='form-control selectric' name='stripe_mode' id='stripe_mode'>
                                        <option value='test' <?= isset($stripe_mode) && $stripe_mode === 'test' ? 'selected' : '' ?>>Test</option>
                                        <option value='live' <?= isset($stripe_mode) && $stripe_mode === 'live' ? 'selected' : '' ?>>Live</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-4">
                                <div class="form-group">
                                    <label for="razorpayMode"><?= labels('currency_code', 'Currency Code') ?></label>
                                    <select name="stripe_currency" class="form-control mt-2">
                                        <option value="">Select Currency Code </option>
                                        <option value="INR" <?= (isset($stripe_currency) && $stripe_currency == "INR") ? "selected" : '' ?>>Indian rupee </option>
                                        <option value="USD" <?= (isset($stripe_currency) && $stripe_currency == "USD") ? "selected" : '' ?>>United States dollar </option>
                                        <option value="AED" <?= (isset($stripe_currency) && $stripe_currency == "AED") ? "selected" : '' ?>>United Arab Emirates Dirham </option>
                                        <option value="AFN" <?= (isset($stripe_currency) && $stripe_currency == "AFN") ? "selected" : '' ?>>Afghan Afghani </option>
                                        <option value="ALL" <?= (isset($stripe_currency) && $stripe_currency == "ALL") ? "selected" : '' ?>>Albanian Lek </option>
                                        <option value="AMD" <?= (isset($stripe_currency) && $stripe_currency == "AMD") ? "selected" : '' ?>>Armenian Dram </option>
                                        <option value="ANG" <?= (isset($stripe_currency) && $stripe_currency == "ANG") ? "selected" : '' ?>>Netherlands Antillean Guilder </option>
                                        <option value="AOA" <?= (isset($stripe_currency) && $stripe_currency == "AOA") ? "selected" : '' ?>>Angolan Kwanza </option>
                                        <option value="ARS" <?= (isset($stripe_currency) && $stripe_currency == "ARS") ? "selected" : '' ?>>Argentine Peso</option>
                                        <option value="AUD" <?= (isset($stripe_currency) && $stripe_currency == "AUD") ? "selected" : '' ?>> Australian Dollar</option>
                                        <option value="AWG" <?= (isset($stripe_currency) && $stripe_currency == "AWG") ? "selected" : '' ?>> Aruban Florin</option>
                                        <option value="AZN" <?= (isset($stripe_currency) && $stripe_currency == "AZN") ? "selected" : '' ?>> Azerbaijani Manat </option>
                                        <option value="BAM" <?= (isset($stripe_currency) && $stripe_currency == "BAM") ? "selected" : '' ?>> Bosnia-Herzegovina Convertible Mark </option>
                                        <option value="BBD" <?= (isset($stripe_currency) && $stripe_currency == "BBD") ? "selected" : '' ?>> Bajan dollar </option>
                                        <option value="BDT" <?= (isset($stripe_currency) && $stripe_currency == "BDT") ? "selected" : '' ?>> Bangladeshi Taka</option>
                                        <option value="BGN" <?= (isset($stripe_currency) && $stripe_currency == "BGN") ? "selected" : '' ?>> Bulgarian Lev </option>
                                        <option value="BIF" <?= (isset($stripe_currency) && $stripe_currency == "BIF") ? "selected" : '' ?>>Burundian Franc</option>
                                        <option value="BMD" <?= (isset($stripe_currency) && $stripe_currency == "BMD") ? "selected" : '' ?>> Bermudan Dollar</option>
                                        <option value="BND" <?= (isset($stripe_currency) && $stripe_currency == "BND") ? "selected" : '' ?>> Brunei Dollar </option>
                                        <option value="BOB" <?= (isset($stripe_currency) && $stripe_currency == "BOB") ? "selected" : '' ?>> Bolivian Boliviano </option>
                                        <option value="BRL" <?= (isset($stripe_currency) && $stripe_currency == "BRL") ? "selected" : '' ?>> Brazilian Real </option>
                                        <option value="BSD" <?= (isset($stripe_currency) && $stripe_currency == "BSD") ? "selected" : '' ?>> Bahamian Dollar </option>
                                        <option value="BWP" <?= (isset($stripe_currency) && $stripe_currency == "BWP") ? "selected" : '' ?>> Botswanan Pula </option>
                                        <option value="BZD" <?= (isset($stripe_currency) && $stripe_currency == "BZD") ? "selected" : '' ?>> Belize Dollar </option>
                                        <option value="CAD" <?= (isset($stripe_currency) && $stripe_currency == "CAD") ? "selected" : '' ?>> Canadian Dollar </option>
                                        <option value="CDF" <?= (isset($stripe_currency) && $stripe_currency == "CDF") ? "selected" : '' ?>> Congolese Franc </option>
                                        <option value="CHF" <?= (isset($stripe_currency) && $stripe_currency == "CHF") ? "selected" : '' ?>> Swiss Franc </option>
                                        <option value="CLP" <?= (isset($stripe_currency) && $stripe_currency == "CLP") ? "selected" : '' ?>> Chilean Peso </option>
                                        <option value="CNY" <?= (isset($stripe_currency) && $stripe_currency == "CNY") ? "selected" : '' ?>> Chinese Yuan </option>
                                        <option value="COP" <?= (isset($stripe_currency) && $stripe_currency == "COP") ? "selected" : '' ?>> Colombian Peso </option>
                                        <option value="CRC" <?= (isset($stripe_currency) && $stripe_currency == "CRC") ? "selected" : '' ?>> Costa Rican Colón </option>
                                        <option value="CVE" <?= (isset($stripe_currency) && $stripe_currency == "CVE") ? "selected" : '' ?>> Cape Verdean Escudo </option>
                                        <option value="CZK" <?= (isset($stripe_currency) && $stripe_currency == "CZK") ? "selected" : '' ?>> Czech Koruna </option>
                                        <option value="DJF" <?= (isset($stripe_currency) && $stripe_currency == "DJF") ? "selected" : '' ?>> Djiboutian Franc </option>
                                        <option value="DKK" <?= (isset($stripe_currency) && $stripe_currency == "DKK") ? "selected" : '' ?>> Danish Krone </option>
                                        <option value="DOP" <?= (isset($stripe_currency) && $stripe_currency == "DOP") ? "selected" : '' ?>> Dominican Peso </option>
                                        <option value="DZD" <?= (isset($stripe_currency) && $stripe_currency == "DZD") ? "selected" : '' ?>> Algerian Dinar </option>
                                        <option value="EGP" <?= (isset($stripe_currency) && $stripe_currency == "EGP") ? "selected" : '' ?>> Egyptian Pound </option>
                                        <option value="ETB" <?= (isset($stripe_currency) && $stripe_currency == "ETB") ? "selected" : '' ?>> Ethiopian Birr </option>
                                        <option value="EUR" <?= (isset($stripe_currency) && $stripe_currency == "EUR") ? "selected" : '' ?>> Euro </option>
                                        <option value="FJD" <?= (isset($stripe_currency) && $stripe_currency == "FJD") ? "selected" : '' ?>> Fijian Dollar </option>
                                        <option value="FKP" <?= (isset($stripe_currency) && $stripe_currency == "FKP") ? "selected" : '' ?>> Falkland Island Pound </option>
                                        <option value="GBP" <?= (isset($stripe_currency) && $stripe_currency == "GBP") ? "selected" : '' ?>> Pound sterling </option>
                                        <option value="GEL" <?= (isset($stripe_currency) && $stripe_currency == "GEL") ? "selected" : '' ?>> Georgian Lari </option>
                                        <option value="GIP" <?= (isset($stripe_currency) && $stripe_currency == "GIP") ? "selected" : '' ?>> Gibraltar Pound </option>
                                        <option value="GMD" <?= (isset($stripe_currency) && $stripe_currency == "GMD") ? "selected" : '' ?>> Gambian dalasi </option>
                                        <option value="GNF" <?= (isset($stripe_currency) && $stripe_currency == "GNF") ? "selected" : '' ?>> Guinean Franc </option>
                                        <option value="GTQ" <?= (isset($stripe_currency) && $stripe_currency == "GTQ") ? "selected" : '' ?>> Guatemalan Quetzal </option>
                                        <option value="GYD" <?= (isset($stripe_currency) && $stripe_currency == "GYD") ? "selected" : '' ?>> Guyanaese Dollar </option>
                                        <option value="HKD" <?= (isset($stripe_currency) && $stripe_currency == "HKD") ? "selected" : '' ?>> Hong Kong Dollar </option>
                                        <option value="HNL" <?= (isset($stripe_currency) && $stripe_currency == "HNL") ? "selected" : '' ?>> Honduran Lempira </option>
                                        <option value="HRK" <?= (isset($stripe_currency) && $stripe_currency == "HRK") ? "selected" : '' ?>> Croatian Kuna </option>
                                        <option value="HTG" <?= (isset($stripe_currency) && $stripe_currency == "HTG") ? "selected" : '' ?>> Haitian Gourde </option>
                                        <option value="HUF" <?= (isset($stripe_currency) && $stripe_currency == "HUF") ? "selected" : '' ?>> Hungarian Forint </option>
                                        <option value="IDR" <?= (isset($stripe_currency) && $stripe_currency == "IDR") ? "selected" : '' ?>> Indonesian Rupiah </option>
                                        <option value="ILS" <?= (isset($stripe_currency) && $stripe_currency == "ILS") ? "selected" : '' ?>> Israeli New Shekel </option>
                                        <option value="ISK" <?= (isset($stripe_currency) && $stripe_currency == "ISK") ? "selected" : '' ?>> Icelandic Króna </option>
                                        <option value="JMD" <?= (isset($stripe_currency) && $stripe_currency == "JMD") ? "selected" : '' ?>> Jamaican Dollar </option>
                                        <option value="JPY" <?= (isset($stripe_currency) && $stripe_currency == "JPY") ? "selected" : '' ?>> Japanese Yen </option>
                                        <option value="KES" <?= (isset($stripe_currency) && $stripe_currency == "KES") ? "selected" : '' ?>> Kenyan Shilling </option>
                                        <option value="KGS" <?= (isset($stripe_currency) && $stripe_currency == "KGS") ? "selected" : '' ?>> Kyrgystani Som </option>
                                        <option value="KHR" <?= (isset($stripe_currency) && $stripe_currency == "KHR") ? "selected" : '' ?>> Cambodian riel </option>
                                        <option value="KMF" <?= (isset($stripe_currency) && $stripe_currency == "KMF") ? "selected" : '' ?>> Comorian franc </option>
                                        <option value="KRW" <?= (isset($stripe_currency) && $stripe_currency == "KRW") ? "selected" : '' ?>> South Korean won </option>
                                        <option value="KYD" <?= (isset($stripe_currency) && $stripe_currency == "KYD") ? "selected" : '' ?>> Cayman Islands Dollar </option>
                                        <option value="KZT" <?= (isset($stripe_currency) && $stripe_currency == "KZT") ? "selected" : '' ?>> Kazakhstani Tenge </option>
                                        <option value="LAK" <?= (isset($stripe_currency) && $stripe_currency == "LAK") ? "selected" : '' ?>> Laotian Kip </option>
                                        <option value="LBP" <?= (isset($stripe_currency) && $stripe_currency == "LBP") ? "selected" : '' ?>> Lebanese pound </option>
                                        <option value="LKR" <?= (isset($stripe_currency) && $stripe_currency == "LKR") ? "selected" : '' ?>> Sri Lankan Rupee </option>
                                        <option value="LRD" <?= (isset($stripe_currency) && $stripe_currency == "LRD") ? "selected" : '' ?>> Liberian Dollar </option>
                                        <option value="LSL" <?= (isset($stripe_currency) && $stripe_currency == "LSL") ? "selected" : '' ?>>Lesotho loti </option>
                                        <option value="MAD" <?= (isset($stripe_currency) && $stripe_currency == "MAD") ? "selected" : '' ?>> Moroccan Dirham </option>
                                        <option value="MDL" <?= (isset($stripe_currency) && $stripe_currency == "MDL") ? "selected" : '' ?>> Moldovan Leu </option>
                                        <option value="MGA" <?= (isset($stripe_currency) && $stripe_currency == "MGA") ? "selected" : '' ?>> Malagasy Ariary </option>
                                        <option value="MKD" <?= (isset($stripe_currency) && $stripe_currency == "MKD") ? "selected" : '' ?>> Macedonian Denar </option>
                                        <option value="MMK" <?= (isset($stripe_currency) && $stripe_currency == "MMK") ? "selected" : '' ?>> Myanmar Kyat </option>
                                        <option value="MNT" <?= (isset($stripe_currency) && $stripe_currency == "MNT") ? "selected" : '' ?>> Mongolian Tugrik </option>
                                        <option value="MOP" <?= (isset($stripe_currency) && $stripe_currency == "MOP") ? "selected" : '' ?>> Macanese Pataca </option>
                                        <option value="MRO" <?= (isset($stripe_currency) && $stripe_currency == "MRO") ? "selected" : '' ?>> Mauritanian Ouguiya </option>
                                        <option value="MUR" <?= (isset($stripe_currency) && $stripe_currency == "MUR") ? "selected" : '' ?>> Mauritian Rupee</option>
                                        <option value="MVR" <?= (isset($stripe_currency) && $stripe_currency == "MVR") ? "selected" : '' ?>> Maldivian Rufiyaa </option>
                                        <option value="MWK" <?= (isset($stripe_currency) && $stripe_currency == "MWK") ? "selected" : '' ?>> Malawian Kwacha </option>
                                        <option value="MXN" <?= (isset($stripe_currency) && $stripe_currency == "MXN") ? "selected" : '' ?>> Mexican Peso </option>
                                        <option value="MYR" <?= (isset($stripe_currency) && $stripe_currency == "MYR") ? "selected" : '' ?>> Malaysian Ringgit </option>
                                        <option value="MZN" <?= (isset($stripe_currency) && $stripe_currency == "MZN") ? "selected" : '' ?>> Mozambican metical </option>
                                        <option value="NAD" <?= (isset($stripe_currency) && $stripe_currency == "NAD") ? "selected" : '' ?>> Namibian dollar </option>
                                        <option value="NGN" <?= (isset($stripe_currency) && $stripe_currency == "NGN") ? "selected" : '' ?>> Nigerian Naira </option>
                                        <option value="NIO" <?= (isset($stripe_currency) && $stripe_currency == "NIO") ? "selected" : '' ?>>Nicaraguan Córdoba </option>
                                        <option value="NOK" <?= (isset($stripe_currency) && $stripe_currency == "NOK") ? "selected" : '' ?>> Norwegian Krone </option>
                                        <option value="NPR" <?= (isset($stripe_currency) && $stripe_currency == "NPR") ? "selected" : '' ?>> Nepalese Rupee </option>
                                        <option value="NZD" <?= (isset($stripe_currency) && $stripe_currency == "NZD") ? "selected" : '' ?>> New Zealand Dollar </option>
                                        <option value="PAB" <?= (isset($stripe_currency) && $stripe_currency == "PAB") ? "selected" : '' ?>> Panamanian Balboa </option>
                                        <option value="PEN" <?= (isset($stripe_currency) && $stripe_currency == "PEN") ? "selected" : '' ?>> Sol </option>
                                        <option value="PGK" <?= (isset($stripe_currency) && $stripe_currency == "PGK") ? "selected" : '' ?>> Papua New Guinean Kina </option>
                                        <option value="PHP" <?= (isset($stripe_currency) && $stripe_currency == "PHP") ? "selected" : '' ?>>Philippine peso </option>
                                        <option value="PKR" <?= (isset($stripe_currency) && $stripe_currency == "PKR") ? "selected" : '' ?>> Pakistani Rupee </option>
                                        <option value="PLN" <?= (isset($stripe_currency) && $stripe_currency == "PLN") ? "selected" : '' ?>> Poland złoty </option>
                                        <option value="PYG" <?= (isset($stripe_currency) && $stripe_currency == "PYG") ? "selected" : '' ?>> Paraguayan Guarani </option>
                                        <option value="QAR" <?= (isset($stripe_currency) && $stripe_currency == "QAR") ? "selected" : '' ?>> Qatari Rial </option>
                                        <option value="RON" <?= (isset($stripe_currency) && $stripe_currency == "RON") ? "selected" : '' ?>>Romanian Leu </option>
                                        <option value="RSD" <?= (isset($stripe_currency) && $stripe_currency == "RSD") ? "selected" : '' ?>> Serbian Dinar </option>
                                        <option value="RUB" <?= (isset($stripe_currency) && $stripe_currency == "RUB") ? "selected" : '' ?>> Russian Ruble </option>
                                        <option value="RWF" <?= (isset($stripe_currency) && $stripe_currency == "RWF") ? "selected" : '' ?>> Rwandan franc </option>
                                        <option value="SAR" <?= (isset($stripe_currency) && $stripe_currency == "SAR") ? "selected" : '' ?>> Saudi Riyal </option>
                                        <option value="SBD" <?= (isset($stripe_currency) && $stripe_currency == "SBD") ? "selected" : '' ?>> Solomon Islands Dollar </option>
                                        <option value="SCR" <?= (isset($stripe_currency) && $stripe_currency == "SCR") ? "selected" : '' ?>>Seychellois Rupee </option>
                                        <option value="SEK" <?= (isset($stripe_currency) && $stripe_currency == "SEK") ? "selected" : '' ?>> Swedish Krona </option>
                                        <option value="SGD" <?= (isset($stripe_currency) && $stripe_currency == "SGD") ? "selected" : '' ?>> Singapore Dollar </option>
                                        <option value="SHP" <?= (isset($stripe_currency) && $stripe_currency == "SHP") ? "selected" : '' ?>> Saint Helenian Pound </option>
                                        <option value="SLL" <?= (isset($stripe_currency) && $stripe_currency == "SLL") ? "selected" : '' ?>> Sierra Leonean Leone </option>
                                        <option value="SOS" <?= (isset($stripe_currency) && $stripe_currency == "SOS") ? "selected" : '' ?>>Somali Shilling </option>
                                        <option value="SRD" <?= (isset($stripe_currency) && $stripe_currency == "SRD") ? "selected" : '' ?>> Surinamese Dollar </option>
                                        <option value="STD" <?= (isset($stripe_currency) && $stripe_currency == "STD") ? "selected" : '' ?>> Sao Tome Dobra </option>
                                        <option value="SZL" <?= (isset($stripe_currency) && $stripe_currency == "SZL") ? "selected" : '' ?>> Swazi Lilangeni </option>
                                        <option value="THB" <?= (isset($stripe_currency) && $stripe_currency == "THB") ? "selected" : '' ?>> Thai Baht </option>
                                        <option value="TJS" <?= (isset($stripe_currency) && $stripe_currency == "TJS") ? "selected" : '' ?>> Tajikistani Somoni </option>
                                        <option value="TOP" <?= (isset($stripe_currency) && $stripe_currency == "TOP") ? "selected" : '' ?>> Tongan Paʻanga </option>
                                        <option value="TRY" <?= (isset($stripe_currency) && $stripe_currency == "TRY") ? "selected" : '' ?>> Turkish lira </option>
                                        <option value="TTD" <?= (isset($stripe_currency) && $stripe_currency == "TTD") ? "selected" : '' ?>> Trinidad &amp; Tobago Dollar </option>
                                        <option value="TWD" <?= (isset($stripe_currency) && $stripe_currency == "TWD") ? "selected" : '' ?>> New Taiwan dollar </option>
                                        <option value="TZS" <?= (isset($stripe_currency) && $stripe_currency == "TZS") ? "selected" : '' ?>> Tanzanian Shilling </option>
                                        <option value="UAH" <?= (isset($stripe_currency) && $stripe_currency == "UAH") ? "selected" : '' ?>> Ukrainian hryvnia </option>
                                        <option value="UGX" <?= (isset($stripe_currency) && $stripe_currency == "UGX") ? "selected" : '' ?>> Ugandan Shilling </option>
                                        <option value="UYU" <?= (isset($stripe_currency) && $stripe_currency == "UYU") ? "selected" : '' ?>> Uruguayan Peso </option>
                                        <option value="UZS" <?= (isset($stripe_currency) && $stripe_currency == "UZS") ? "selected" : '' ?>> Uzbekistani Som </option>
                                        <option value="VND" <?= (isset($stripe_currency) && $stripe_currency == "VND") ? "selected" : '' ?>> Vietnamese dong </option>
                                        <option value="VUV" <?= (isset($stripe_currency) && $stripe_currency == "VUV") ? "selected" : '' ?>> Vanuatu Vatu </option>
                                        <option value="WST" <?= (isset($stripe_currency) && $stripe_currency == "WST") ? "selected" : '' ?>> Samoa Tala</option>
                                        <option value="XAF" <?= (isset($stripe_currency) && $stripe_currency == "XAF") ? "selected" : '' ?>> Central African CFA franc </option>
                                        <option value="XCD" <?= (isset($stripe_currency) && $stripe_currency == "XCD") ? "selected" : '' ?>> East Caribbean Dollar </option>
                                        <option value="XOF" <?= (isset($stripe_currency) && $stripe_currency == "XOF") ? "selected" : '' ?>> West African CFA franc </option>
                                        <option value="XPF" <?= (isset($stripe_currency) && $stripe_currency == "XPF") ? "selected" : '' ?>> CFP Franc </option>
                                        <option value="YER" <?= (isset($stripe_currency) && $stripe_currency == "YER") ? "selected" : '' ?>> Yemeni Rial </option>
                                        <option value="ZAR" <?= (isset($stripe_currency) && $stripe_currency == "ZAR") ? "selected" : '' ?>> South African Rand </option>
                                        <option value="ZMW" <?= (isset($stripe_currency) && $stripe_currency == "ZMW") ? "selected" : '' ?>> Zambian Kwacha </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="publishable_key"><?= labels('stripe_publishable_key', 'Stripe Publishable key') ?></label>
                                    <input type="text" value="<?= isset($stripe_publishable_key) ? ((defined('ALLOW_VIEW_KEYS') && ALLOW_VIEW_KEYS == 0) ? "asc****************adaca" : $stripe_publishable_key) : '' ?>" name='stripe_publishable_key' id='stripe_publishable_key' placeholder='<?= labels('enter_stripe_publishable_key', 'Enter Stripe Publishable key') ?>' class="form-control" />
                                </div>
                            </div>
                        </div>
                        <div class="row">

                            <div class="col-4">
                                <div class="form-group">
                                    <label for="publishable_key"><?= labels('stripe_webhook_secret', 'Stripe Webhook secret') ?></label>
                                    <input type="text" value="<?= isset($stripe_webhook_secret_key) ? ((defined('ALLOW_VIEW_KEYS') && ALLOW_VIEW_KEYS == 0) ? "asc****************adaca" : $stripe_webhook_secret_key) : '' ?>" name='stripe_webhook_secret_key' id='stripe_webhook_secret_key' placeholder='<?= labels('enter_stripe_webhook_secret', 'Enter Stripe webhook secret') ?>' class="form-control" />
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="razorpaySecretKey"><?= labels('stripe_secret_key', 'Stripe Secret key') ?></label>
                                    <input type="text" value="<?= isset($stripe_secret_key) ? ((defined('ALLOW_VIEW_KEYS') && ALLOW_VIEW_KEYS == 0) ? "asc****************adaca" : $stripe_secret_key) : '' ?>" name='stripe_secret_key' id='stripe_secret_key' placeholder='<?= labels('enter_stripe_secret_key', 'Enter Stripe secret key') ?>' class="form-control" />
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="endpoint"><?= labels('Payment_endpoint_URL', 'Payment Endpoint URL') ?><small> (<?= labels('set_this_as_endpoint_URL_in_your_stripe_account', 'Set this as Endpoint URL in your stripe account') ?>)</small></label>
                                    <div class="input-group">
                                        <input type="text" value="<?= site_url("api/webhooks/stripe") ?>" name='stripe_endpoint' id='endpoint' class="form-control" readonly />
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('stripe_endpoint')">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 ">
                    <div class=" card px-3">
                        <div class="row border_bottom_for_cards mb-3">
                            <div class="col">
                                <div class='toggleButttonPostition'><?= labels('flutterwave', 'Flutterwave') ?></div>
                            </div>
                            <div class="col d-flex justify-content-end  mt-4 ">
                                <input type="checkbox" id="flutterwave_status" class="status-switch" name="flutterwave_status" <?= isset($flutterwave_status) && $flutterwave_status === 'enable' ? 'checked' : '' ?>>
                            </div>
                        </div>
                        <div class="row">

                            <div class="col-4">
                                <div class="form-group">
                                    <label for="razorpayMode"><?= labels('currency_code', 'Currency Code') ?></label>
                                    <select name="flutterwave_currency_code" class="form-control">
                                        <option value="">Select Currency Code </option>
                                        <option value="NGN" <?= (isset($flutterwave_currency_code) && $flutterwave_currency_code == 'NGN') ? "selected" : "" ?>>Nigerian Naira</option>
                                        <option value="USD" <?= (isset($flutterwave_currency_code) && $flutterwave_currency_code == 'USD') ? "selected" : "" ?>>United States dollar</option>
                                        <option value="TZS" <?= (isset($flutterwave_currency_code) && $flutterwave_currency_code == 'TZS') ? "selected" : "" ?>>Tanzanian Shilling</option>
                                        <option value="SLL" <?= (isset($flutterwave_currency_code) && $flutterwave_currency_code == 'SLL') ? "selected" : "" ?>>Sierra Leonean Leone</option>
                                        <option value="MUR" <?= (isset($flutterwave_currency_code) && $flutterwave_currency_code == 'MUR') ? "selected" : "" ?>>Mauritian Rupee</option>
                                        <option value="MWK" <?= (isset($flutterwave_currency_code) && $flutterwave_currency_code == 'MWK') ? "selected" : "" ?>>Malawian Kwacha </option>
                                        <option value="GBP" <?= (isset($flutterwave_currency_code) && $flutterwave_currency_code == 'GBP') ? "selected" : "" ?>>UK Bank Accounts</option>
                                        <option value="GHS" <?= (isset($flutterwave_currency_code) && $flutterwave_currency_code == 'GHS') ? "selected" : "" ?>>Ghanaian Cedi</option>
                                        <option value="RWF" <?= (isset($flutterwave_currency_code) && $flutterwave_currency_code == 'RWF') ? "selected" : "" ?>>Rwandan franc</option>
                                        <option value="UGX" <?= (isset($flutterwave_currency_code) && $flutterwave_currency_code == 'UGX') ? "selected" : "" ?>>Ugandan Shilling</option>
                                        <option value="ZMW" <?= (isset($flutterwave_currency_code) && $flutterwave_currency_code == 'ZMW') ? "selected" : "" ?>>Zambian Kwacha</option>
                                        <option value="KES" <?= (isset($flutterwave_currency_code) && $flutterwave_currency_code == 'KES') ? "selected" : "" ?>>Mpesa</option>
                                        <option value="ZAR" <?= (isset($flutterwave_currency_code) && $flutterwave_currency_code == 'ZAR') ? "selected" : "" ?>>South African Rand</option>
                                        <option value="XAF" <?= (isset($flutterwave_currency_code) && $flutterwave_currency_code == 'XAF') ? "selected" : "" ?>>Central African CFA franc</option>
                                        <option value="XOF" <?= (isset($flutterwave_currency_code) && $flutterwave_currency_code == 'XOF') ? "selected" : "" ?>>West African CFA franc</option>
                                        <option value="AUD" <?= (isset($flutterwave_currency_code) && $flutterwave_currency_code == 'AUD') ? "selected" : "" ?>>Australian Dollar</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="flutterwave_public_key"><?= labels('flutterwave_public_key', 'Flutterwave Public Key') ?></label>
                                    <input type="text" value="<?= isset($flutterwave_public_key) ? ((defined('ALLOW_VIEW_KEYS') && ALLOW_VIEW_KEYS == 0) ? "asc****************adaca" : $flutterwave_public_key) : '' ?>" name='flutterwave_public_key' id='flutterwave_public_key' placeholder='<?= labels('enter_flutterwave_public_key', 'Enter Flutterwave public key') ?>' class="form-control" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="flutterwave_secret_key"><?= labels('flutterwave_secret_key', 'Flutterwave Secret Key') ?></label>
                                    <input type="text" value="<?= isset($flutterwave_secret_key) ? ((defined('ALLOW_VIEW_KEYS') && ALLOW_VIEW_KEYS == 0) ? "asc****************adaca" : $flutterwave_secret_key) : '' ?>" name='flutterwave_secret_key' id='flutterwave_secret_key' placeholder='<?= labels('enter_fluterwave_secret_key', 'Enter Flutterwave Secret key') ?>' class="form-control" />
                                </div>
                            </div>
                        </div>

                        <div class="row">

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="flutterwave_encryption_key"><?= labels('flutterwave_encryption_key', 'Flutterwave Encryption key') ?></label>
                                    <input type="text" value="<?= isset($flutterwave_encryption_key) ? ((defined('ALLOW_VIEW_KEYS') && ALLOW_VIEW_KEYS == 0) ? "asc****************adaca" : $flutterwave_encryption_key) : '' ?>" name='flutterwave_encryption_key' id='flutterwave_encryption_key' placeholder='<?= labels('enter_flutterwave_encryption_key', 'Enter Flutterwave encryption key') ?>' class="form-control" />
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="flutterwave_webhook_secret_key"><?= labels('flutterwave_webhook_secret_key', 'Flutterwave Webhook Secret Key') ?></label>
                                    <input type="text" value="<?= isset($flutterwave_webhook_secret_key) ? ((defined('ALLOW_VIEW_KEYS') && ALLOW_VIEW_KEYS == 0) ? "asc****************adaca" : $flutterwave_webhook_secret_key) : '' ?>" name='flutterwave_webhook_secret_key' id='flutterwave_webhook_secret_key' placeholder='<?= labels('enter_flutterwave_webhook_secret', 'Enter Flutterwave webhook secret key') ?>' class="form-control" />
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="endpoint"><?= labels('Payment_endpoint_URL', 'Payment Endpoint URL') ?><small> (<?= labels('set_this_as_endpoint_URL_in_your_flutterwave_account', 'Set this as Endpoint URL in your flutterwave account') ?>)</small></label>
                                    <div class="input-group">
                                        <input type="text" value="<?= base_url("api/webhooks/flutterwave") ?>" name='flutterwave_endpoint' id='endpoint' class="form-control" readonly />
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('flutterwave_endpoint')">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="endpoint"><?= labels('website_url', 'Website URL') ?></label>
                                    <div class="input-group">
                                        <input type="text" value="<?= isset($flutterwave_website_url) ? ((defined('ALLOW_VIEW_KEYS') && ALLOW_VIEW_KEYS == 0) ? "asc****************adaca" : $flutterwave_website_url) : '' ?>" name='flutterwave_website_url' id='flutterwave_website_url' class="form-control"  />
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('flutterwave_website_url')">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            
            <?php if ($permissions['update']['settings'] == 1) : ?>

                <div class="row">
                    <div class="col-md d-flex justify-content-lg-end m-1">
                        <div class="form-group">
                            <input type='submit' name='update' id='update' value='<?= labels('save_changes', "Save Changes") ?>' class='btn btn-primary' />
                        </div>
                    </div>
                </div>
                <?php endif; ?>

        </form>


    </section>
</div>
<script>
    $(document).ready(function() {
        <?php if (isset($cod_setting) && $cod_setting == "1") { ?>
            $('#cod_setting').siblings('.switchery').addClass('active-content').removeClass('deactive-content');
        <?php } else { ?>
            $('#cod_setting').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        <?php } ?>
        <?php if (isset($payment_gateway_setting) && $payment_gateway_setting == "1") { ?>
            $('#payment_gateway_setting').siblings('.switchery').addClass('active-content').removeClass('deactive-content');
            $('#all_payment_gateways').show();
        <?php } else { ?>
            $('#payment_gateway_setting').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
            $('#all_payment_gateways').hide();
        <?php } ?>
        <?php if (isset($paypal_status) && $paypal_status == "enable") { ?>
            $('#paypal_status').siblings('.switchery').addClass('active-content').removeClass('deactive-content');
        <?php } else { ?>
            $('#paypal_status').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        <?php } ?>
        <?php if (isset($razorpayApiStatus) && $razorpayApiStatus == "enable") { ?>
            $('#razorpayApiStatus').siblings('.switchery').addClass('active-content').removeClass('deactive-content');
        <?php } else { ?>
            $('#razorpayApiStatus').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        <?php } ?>
        <?php if (isset($paystack_status) && $paystack_status == "enable") { ?>
            $('#paystack_status').siblings('.switchery').addClass('active-content').removeClass('deactive-content');
        <?php } else { ?>
            $('#paystack_status').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        <?php } ?>
        <?php if (isset($stripe_status) && $stripe_status == "enable") { ?>
            $('#stripe_status').siblings('.switchery').addClass('active-content').removeClass('deactive-content');
        <?php } else { ?>
            $('#stripe_status').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        <?php } ?>
        <?php if (isset($flutterwave_status) && $flutterwave_status == "enable") { ?>
            $('#flutterwave_status').siblings('.switchery').addClass('active-content').removeClass('deactive-content');
        <?php } else { ?>
            $('#flutterwave_status').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        <?php } ?>


    });

    function handleSwitchChange(checkbox) {
        var switchery = checkbox.nextElementSibling;
        if (checkbox.checked) {
            switchery.classList.add('active-content');
            switchery.classList.remove('deactive-content');
            if (checkbox.id === 'payment_gateway_setting') {
                $('#all_payment_gateways').show();
            }
        } else {
            switchery.classList.add('deactive-content');
            switchery.classList.remove('active-content');
            if (checkbox.id === 'payment_gateway_setting') {
                $('#all_payment_gateways').hide();
            }
        }
    }
    var cod_setting = document.querySelector('#cod_setting');
    var payment_gateway_setting = document.querySelector('#payment_gateway_setting');
    var paypal_status = document.querySelector('#paypal_status');
    var razorpayApiStatus = document.querySelector('#razorpayApiStatus');
    var paystack_status = document.querySelector('#paystack_status');
    var stripe_status = document.querySelector('#stripe_status');
    var flutterwave_status = document.querySelector('#flutterwave_status');

    cod_setting.addEventListener('change', function() {
        if (!cod_setting.checked && !payment_gateway_setting.checked) {
            $('#payment_gateway_setting').siblings('.switchery').addClass('active-content').removeClass('deactive-content');
            payment_gateway_setting.click();
        } else {
            handleSwitchChange(cod_setting);
        }
    });
    payment_gateway_setting.addEventListener('change', function() {
        if (!payment_gateway_setting.checked && !cod_setting.checked) {
            $('#cod_setting').siblings('.switchery').addClass('active-content').removeClass('deactive-content');
            cod_setting.click();
        } else {
            handleSwitchChange(payment_gateway_setting);
        }
    });

    function handlePaymentSTatusSwitchChange(checkbox) {
        var switchery = checkbox.nextElementSibling;
        if (checkbox.checked) {
            switchery.classList.add('active-content');
            switchery.classList.remove('deactive-content');
        } else {
            switchery.classList.add('deactive-content');
            switchery.classList.remove('active-content');
        }
    }


    paypal_status.onchange = function() {
        handlePaymentSTatusSwitchChange(paypal_status);
    };



    razorpayApiStatus.onchange = function() {
        handlePaymentSTatusSwitchChange(razorpayApiStatus);
    };



    paystack_status.onchange = function() {
        handlePaymentSTatusSwitchChange(paystack_status);
    };




    stripe_status.onchange = function() {
        handlePaymentSTatusSwitchChange(stripe_status);
    };



    flutterwave_status.onchange = function() {
        handlePaymentSTatusSwitchChange(flutterwave_status);
    };
</script>
<script>
    $(function() {
        $('.fa').popover({
            trigger: "hover"
        });
    });


    // Validate on form submission
    $('form').submit(function(event) {
        $('input[name="paypal_status"]').val($('#paypal_status').is(':checked') ? 'enable' : 'disable');
        $('input[name="razorpayApiStatus"]').val($('#razorpayApiStatus').is(':checked') ? 'enable' : 'disable');
        $('input[name="paystack_status"]').val($('#paystack_status').is(':checked') ? 'enable' : 'disable');
        $('input[name="stripe_status"]').val($('#stripe_status').is(':checked') ? 'enable' : 'disable');
        $('input[name="flutterwave_status"]').val($('#flutterwave_status').is(':checked') ? 'enable' : 'disable');

    });
</script>