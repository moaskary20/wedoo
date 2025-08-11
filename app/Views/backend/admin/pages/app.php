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
            <h1><?= labels('app_settings', "App settings") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item "><a href="<?= base_url('/admin/settings/system-settings') ?>"><?= labels('system_settings', "System Settings") ?></a></div>
                <div class="breadcrumb-item"><?= labels('app_settings', "App settings") ?></div>
            </div>
        </div>
        <?= form_open_multipart(base_url('admin/settings/app_settings')) ?>
        <div class="row mb-3">
            <!-- Country Currency -->
            <div class="col-md-6 col-sm-12 col-xl-4">
                <div class="card h-100">
                    <div class="col mb-3" style="border-bottom: solid 1px #e5e6e9;">
                        <div class="toggleButttonPostition"><?= labels('country_currency', "Country Currency") ?></div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label><?= labels('country_currency', "Country Currency Code") ?></label>
                                    <select class="form-control" name="country_currency_code">
                                        <option value=AFN <?php echo  isset($country_currency_code)  && $country_currency_code == 'AFN' ? 'selected' : '' ?>> AFN - Afghanistan Afghani” </option>
                                        <option value=AED <?php echo  isset($country_currency_code) && $country_currency_code == 'AED' ? 'selected' : '' ?>> AED - United Arab Emirates Dirham </option>
                                        <option value=ALL <?php echo  isset($country_currency_code) && $country_currency_code == 'ALL' ? 'selected' : '' ?>> ALL - Albania Lek </option>
                                        <option value=AMD <?php echo  isset($country_currency_code) && $country_currency_code == 'AMD' ? 'selected' : '' ?>> AMD - Armenia Dram </option>
                                        <option value=ANG <?php echo  isset($country_currency_code) && $country_currency_code == 'ANG' ? 'selected' : '' ?>> ANG - Netherlands Antilles Guilder </option>
                                        <option value=AOA <?php echo  isset($country_currency_code) && $country_currency_code == 'AOA' ? 'selected' : '' ?>> AOA - Angola Kwanza </option>
                                        <option value=ARS <?php echo  isset($country_currency_code) && $country_currency_code == 'ARS' ? 'selected' : '' ?>> ARS - Argentina Peso </option>
                                        <option value=AUD <?php echo  isset($country_currency_code) && $country_currency_code == 'AUD' ? 'selected' : '' ?>> AUD - Australia Dollar </option>
                                        <option value=AWG <?php echo  isset($country_currency_code) && $country_currency_code == 'AWG' ? 'selected' : '' ?>> AWG - Aruba Guilder </option>
                                        <option value=AZN <?php echo  isset($country_currency_code) && $country_currency_code == 'AZN' ? 'selected' : '' ?>> AZN - Azerbaijan Manat </option>
                                        <option value=BAM <?php echo  isset($country_currency_code) && $country_currency_code == 'BAM' ? 'selected' : '' ?>> BAM - Bosnia and Herzegovina Convertible Mark </option>
                                        <option value=BBD <?php echo  isset($country_currency_code) && $country_currency_code == 'BBD' ? "selected" : '' ?>> BBD - Barbados Dollar </option>
                                        <option value=BDT <?php echo  isset($country_currency_code) && $country_currency_code == 'BDT' ? 'selected' : '' ?>> BDT - Bangladesh Taka </option>
                                        <option value=BGN <?php echo  isset($country_currency_code) && $country_currency_code == 'BGN' ? 'selected' : '' ?>> BGN - Bulgaria Lev </option>
                                        <option value=BHD <?php echo  isset($country_currency_code) && $country_currency_code == 'BHD' ? 'selected' : '' ?>> BHD - Bahrain Dinar </option>
                                        <option value=BIF <?php echo  isset($country_currency_code) && $country_currency_code == 'BIF' ? 'selected' : '' ?>> BIF - Burundi Franc </option>
                                        <option value=BMD <?php echo  isset($country_currency_code) && $country_currency_code == 'BMD' ? 'selected' : '' ?>> BMD - Bermuda Dollar </option>
                                        <option value=BND <?php echo  isset($country_currency_code) && $country_currency_code == 'BND' ? 'selected' : '' ?>> BND - Brunei Darussalam Dollar </option>
                                        <option value=BOB <?php echo  isset($country_currency_code) && $country_currency_code == 'BOB' ? 'selected' : '' ?>> BOB - Bolivia Bolíviano </option>
                                        <option value=BRL <?php echo  isset($country_currency_code) && $country_currency_code == 'BRL' ? 'selected' : '' ?>> BRL - Brazil Real </option>
                                        <option value=BSD <?php echo  isset($country_currency_code) && $country_currency_code == 'BSD' ? 'selected' : '' ?>> BSD - Bahamas Dollar </option>
                                        <option value=BTN <?php echo  isset($country_currency_code) && $country_currency_code == 'BTN' ? 'selected' : '' ?>> BTN - Bhutan Ngultrum </option>
                                        <option value=BWP <?php echo  isset($country_currency_code) && $country_currency_code == 'BWP' ? 'selected' : '' ?>> BWP - Botswana Pula </option>
                                        <option value=BYN <?php echo  isset($country_currency_code) && $country_currency_code == 'BYN' ? 'selected' : '' ?>> BYN - Belarus Ruble </option>
                                        <option value=BZD <?php echo  isset($country_currency_code) && $country_currency_code == 'BZD' ? 'selected' : '' ?>> BZD - Belize Dollar </option>
                                        <option value=CAD <?php echo  isset($country_currency_code) && $country_currency_code == 'CAD' ? 'selected' : '' ?>> CAD - Canada Dollar </option>
                                        <option value=CDF <?php echo  isset($country_currency_code) && $country_currency_code == 'CDF' ? 'selected' : '' ?>> CDF - Congo/Kinshasa Franc” </option>
                                        <option value=CHF <?php echo  isset($country_currency_code) && $country_currency_code == 'CHF' ? 'selected' : '' ?>> CHF - Switzerland Franc </option>
                                        <option value=CLP <?php echo  isset($country_currency_code) && $country_currency_code == 'CLP' ? 'selected' : '' ?>> CLP - Chile Peso </option>
                                        <option value=CNY <?php echo  isset($country_currency_code) && $country_currency_code == 'CNY' ? 'selected' : '' ?>> CNY - China Yuan Renminbi </option>
                                        <option value=COP <?php echo  isset($country_currency_code) && $country_currency_code == 'COP' ? 'selected' : '' ?>> COP - Colombia Peso </option>
                                        <option value=CRC <?php echo  isset($country_currency_code) && $country_currency_code == 'CRC' ? 'selected' : '' ?>> CRC - Costa Rica Colon </option>
                                        <option value=CUC <?php echo  isset($country_currency_code) && $country_currency_code == 'CUC' ? 'selected' : '' ?>> CUC - Cuba Convertible Peso </option>
                                        <option value=CUP <?php echo  isset($country_currency_code) && $country_currency_code == 'CUP' ? 'selected' : '' ?>> CUP - Cuba Peso </option>
                                        <option value=CVE <?php echo  isset($country_currency_code) && $country_currency_code == 'CVE' ? 'selected' : '' ?>> CVE - Cape Verde Escudo </option>
                                        <option value=CZK <?php echo  isset($country_currency_code) && $country_currency_code == 'CZK' ? 'selected' : '' ?>> CZK - Czech Republic Koruna </option>
                                        <option value=DJF <?php echo  isset($country_currency_code) && $country_currency_code == 'DJF' ? 'selected' : '' ?>> DJF - Djibouti Franc </option>
                                        <option value=DKK <?php echo  isset($country_currency_code) && $country_currency_code == 'DKK' ? 'selected' : '' ?>> DKK - Denmark Krone </option>
                                        <option value=DOP <?php echo  isset($country_currency_code) && $country_currency_code == 'DOP' ? 'selected' : '' ?>> DOP - Dominican Republic Peso </option>
                                        <option value=DZD <?php echo  isset($country_currency_code) && $country_currency_code == 'DZD' ? 'selected' : '' ?>> DZD - Algeria Dinar </option>
                                        <option value=EGP <?php echo  isset($country_currency_code) && $country_currency_code == 'EGP' ? 'selected' : '' ?>> EGP - Egypt Pound </option>
                                        <option value=ERN <?php echo  isset($country_currency_code) && $country_currency_code == 'ERN' ? 'selected' : '' ?>> ERN - Eritrea Nakfa </option>
                                        <option value=ETB <?php echo  isset($country_currency_code) && $country_currency_code == 'ETB' ? 'selected' : '' ?>> ETB - Ethiopia Birr </option>
                                        <option value=EUR <?php echo  isset($country_currency_code) && $country_currency_code == 'EUR' ? 'selected' : '' ?>> EUR - Euro Member Countries </option>
                                        <option value=FJD <?php echo  isset($country_currency_code) && $country_currency_code == 'FJD' ? 'selected' : '' ?>> FJD - Fiji Dollar </option>
                                        <option value=FKP <?php echo  isset($country_currency_code) && $country_currency_code == 'FKP' ? 'selected' : '' ?>> FKP - Falkland Islands (Malvinas) Pound” </option>
                                        <option value=GBP <?php echo  isset($country_currency_code) && $country_currency_code == 'GBP' ? 'selected' : '' ?>> GBP - United Kingdom Pound </option>
                                        <option value=GEL <?php echo  isset($country_currency_code) && $country_currency_code == 'GEL' ? 'selected' : '' ?>> GEL - Georgia Lari </option>
                                        <option value=GGP <?php echo  isset($country_currency_code) && $country_currency_code == 'GGP' ? 'selected' : '' ?>> GGP - Guernsey Pound </option>
                                        <option value=GHS <?php echo  isset($country_currency_code) && $country_currency_code == 'GHS' ? 'selected' : '' ?>> GHS - Ghana Cedi </option>
                                        <option value=GIP <?php echo  isset($country_currency_code) && $country_currency_code == 'GIP' ? 'selected' : '' ?>> GIP - Gibraltar Pound </option>
                                        <option value=GMD <?php echo  isset($country_currency_code) && $country_currency_code == 'GMD' ? 'selected' : '' ?>> GMD - Gambia Dalasi </option>
                                        <option value=GNF <?php echo  isset($country_currency_code) && $country_currency_code == 'GNF' ? 'selected' : '' ?>> GNF - Guinea Franc </option>
                                        <option value=GTQ <?php echo  isset($country_currency_code) && $country_currency_code == 'GTQ' ? 'selected' : '' ?>> GTQ - Guatemala Quetzal </option>
                                        <option value=GYD <?php echo  isset($country_currency_code) && $country_currency_code == 'GYD' ? 'selected' : '' ?>> GYD - Guyana Dollar </option>
                                        <option value=HKD <?php echo  isset($country_currency_code) && $country_currency_code == 'HKD' ? 'selected' : '' ?>> HKD - Hong Kong Dollar </option>
                                        <option value=HNL <?php echo  isset($country_currency_code) && $country_currency_code == 'HNL' ? 'selected' : '' ?>> HNL - Honduras Lempira </option>
                                        <option value=HRK <?php echo  isset($country_currency_code) && $country_currency_code == 'HRK' ? 'selected' : '' ?>> HRK - Croatia Kuna </option>
                                        <option value=HTG <?php echo  isset($country_currency_code) && $country_currency_code == 'HTG' ? 'selected' : '' ?>> HTG - Haiti Gourde </option>
                                        <option value=HUF <?php echo  isset($country_currency_code) && $country_currency_code == 'HUF' ? 'selected' : '' ?>> HUF - Hungary Forint </option>
                                        <option value=IDR <?php echo  isset($country_currency_code) && $country_currency_code == 'IDR' ? 'selected' : '' ?>> IDR - Indonesia Rupiah </option>
                                        <option value=ILS <?php echo  isset($country_currency_code) && $country_currency_code == 'ILS' ? 'selected' : '' ?>> ILS - Israel Shekel </option>
                                        <option value=IMP <?php echo  isset($country_currency_code) && $country_currency_code == 'IMP' ? 'selected' : '' ?>> IMP - Isle of Man Pound </option>
                                        <option value=INR <?php echo  isset($country_currency_code) && $country_currency_code == 'INR' ? 'selected' : '' ?>> INR - India Rupee </option>
                                        <option value=IQD <?php echo  isset($country_currency_code) && $country_currency_code == 'IQD' ? 'selected' : '' ?>> IQD - Iraq Dinar </option>
                                        <option value=IRR <?php echo  isset($country_currency_code) && $country_currency_code == 'IRR' ? 'selected' : '' ?>> IRR - Iran Rial </option>
                                        <option value=ISK <?php echo  isset($country_currency_code) && $country_currency_code == 'ISK' ? 'selected' : '' ?>> ISK - Iceland Krona </option>
                                        <option value=JEP <?php echo  isset($country_currency_code) && $country_currency_code == 'JEP' ? 'selected' : '' ?>> JEP - Jersey Pound </option>
                                        <option value=JMD <?php echo  isset($country_currency_code) && $country_currency_code == 'JMD' ? 'selected' : '' ?>> JMD - Jamaica Dollar </option>
                                        <option value=JOD <?php echo  isset($country_currency_code) && $country_currency_code == 'JOD' ? 'selected' : '' ?>> JOD - Jordan Dinar </option>
                                        <option value=JPY <?php echo  isset($country_currency_code) && $country_currency_code == 'JPY' ? 'selected' : '' ?>> JPY - Japan Yen </option>
                                        <option value=KES <?php echo  isset($country_currency_code) && $country_currency_code == 'KES' ? 'selected' : '' ?>> KES - Kenya Shilling </option>
                                        <option value=KGS <?php echo  isset($country_currency_code) && $country_currency_code == 'KGS' ? 'selected' : '' ?>> KGS - Kyrgyzstan Som </option>
                                        <option value=KHR <?php echo  isset($country_currency_code) && $country_currency_code == 'KHR' ? 'selected' : '' ?>> KHR - Cambodia Riel </option>
                                        <option value=KMF <?php echo  isset($country_currency_code) && $country_currency_code == 'KMF' ? 'selected' : '' ?>> KMF - Comorian Franc </option>
                                        <option value=KPW <?php echo  isset($country_currency_code) && $country_currency_code == 'KPW' ? 'selected' : '' ?>> KPW - Korea (North) Won </option>
                                        <option value=KRW <?php echo  isset($country_currency_code) && $country_currency_code == 'KRW' ? 'selected' : '' ?>> KRW - Korea (South) Won </option>
                                        <option value=KWD <?php echo  isset($country_currency_code) && $country_currency_code == 'KWD' ? 'selected' : '' ?>> KWD - Kuwait Dinar </option>
                                        <option value=KYD <?php echo  isset($country_currency_code) && $country_currency_code == 'KYD' ? 'selected' : '' ?>> KYD - Cayman Islands Dollar </option>
                                        <option value=KZT <?php echo  isset($country_currency_code) && $country_currency_code == 'KZT' ? 'selected' : '' ?>> KZT - Kazakhstan Tenge </option>
                                        <option value=LAK <?php echo  isset($country_currency_code) && $country_currency_code == 'LAK' ? 'selected' : '' ?>> LAK - Laos Kip </option>
                                        <option value=LBP <?php echo  isset($country_currency_code) && $country_currency_code == 'LBP' ? 'selected' : '' ?>> LBP - Lebanon Pound </option>
                                        <option value=LKR <?php echo  isset($country_currency_code) && $country_currency_code == 'LKR' ? 'selected' : '' ?>> LKR - Sri Lanka Rupee </option>
                                        <option value=LRD <?php echo  isset($country_currency_code) && $country_currency_code == 'LRD' ? 'selected' : '' ?>> LRD - Liberia Dollar </option>
                                        <option value=LSL <?php echo  isset($country_currency_code) && $country_currency_code == 'LSL' ? 'selected' : '' ?>> LSL - Lesotho Loti </option>
                                        <option value=LYD <?php echo  isset($country_currency_code) && $country_currency_code == 'LYD' ? 'selected' : '' ?>> LYD - Libya Dinar </option>
                                        <option value=MAD <?php echo  isset($country_currency_code) && $country_currency_code == 'MAD' ? 'selected' : '' ?>> MAD - Morocco Dirham </option>
                                        <option value=MDL <?php echo  isset($country_currency_code) && $country_currency_code == 'MDL' ? 'selected' : '' ?>> MDL - Moldova Leu </option>
                                        <option value=MGA <?php echo  isset($country_currency_code) && $country_currency_code == 'MGA' ? 'selected' : '' ?>> MGA - Madagascar Ariary </option>
                                        <option value=MKD <?php echo  isset($country_currency_code) && $country_currency_code == 'MKD' ? 'selected' : '' ?>> MKD - Macedonia Denar” </option>
                                        <option value=MMK <?php echo  isset($country_currency_code) && $country_currency_code == 'MMK' ? 'selected' : '' ?>> MMK - Myanmar (Burma) Kyat” </option>
                                        <option value=MNT <?php echo  isset($country_currency_code) && $country_currency_code == 'MNT' ? 'selected' : '' ?>> MNT - Mongolia Tughrik” </option>
                                        <option value=MOP <?php echo  isset($country_currency_code) && $country_currency_code == 'MOP' ? 'selected' : '' ?>> MOP - Macau Pataca” </option>
                                        <option value=MRU <?php echo  isset($country_currency_code) && $country_currency_code == 'MRU' ? 'selected' : '' ?>> MRU - Mauritania Ouguiya” </option>
                                        <option value=MUR <?php echo  isset($country_currency_code) && $country_currency_code == 'MUR' ? 'selected' : '' ?>> MUR - Mauritius Rupee” </option>
                                        <option value=MVR <?php echo  isset($country_currency_code) && $country_currency_code == 'MVR' ? 'selected' : '' ?>> MVR - Maldives (Maldive Islands) Rufiyaa” </option>
                                        <option value=MWK <?php echo  isset($country_currency_code) && $country_currency_code == 'MWK' ? 'selected' : '' ?>> MWK - Malawi Kwacha” </option>
                                        <option value=MXN <?php echo  isset($country_currency_code) && $country_currency_code == 'MXN' ? 'selected' : '' ?>> MXN - Mexico Peso” </option>
                                        <option value=MYR <?php echo  isset($country_currency_code) && $country_currency_code == 'MYR' ? 'selected' : '' ?>> MYR - Malaysia Ringgit” </option>
                                        <option value=MZN <?php echo  isset($country_currency_code) && $country_currency_code == 'MZN' ? 'selected' : '' ?>> MZN - Mozambique Metical” </option>
                                        <option value=NAD <?php echo  isset($country_currency_code) && $country_currency_code == 'NAD' ? 'selected' : '' ?>> NAD - Namibia Dollar </option>
                                        <option value=NGN <?php echo  isset($country_currency_code) && $country_currency_code == 'NGN' ? 'selected' : '' ?>> NGN - Nigeria Naira </option>
                                        <option value=NIO <?php echo  isset($country_currency_code) && $country_currency_code == 'NIO' ? 'selected' : '' ?>> NIO - Nicaragua Cordoba </option>
                                        <option value=NOK <?php echo  isset($country_currency_code) && $country_currency_code == 'NOK' ? 'selected' : '' ?>> NOK - Norway Krone </option>
                                        <option value=NPR <?php echo  isset($country_currency_code) && $country_currency_code == 'NPR' ? 'selected' : '' ?>> NPR - Nepal Rupee </option>
                                        <option value=NZD <?php echo  isset($country_currency_code) && $country_currency_code == 'NZD' ? 'selected' : '' ?>> NZD - New Zealand Dollar </option>
                                        <option value=OMR <?php echo  isset($country_currency_code) && $country_currency_code == 'OMR' ? 'selected' : '' ?>> OMR - Oman Rial </option>
                                        <option value=PAB <?php echo  isset($country_currency_code) && $country_currency_code == 'PAB' ? 'selected' : '' ?>> PAB - Panama Balboa </option>
                                        <option value=PEN <?php echo  isset($country_currency_code) && $country_currency_code == 'PEN' ? 'selected' : '' ?>> PEN - Peru Sol </option>
                                        <option value=PGK <?php echo  isset($country_currency_code) && $country_currency_code == 'PGK' ? 'selected' : '' ?>> PGK - Papua New Guinea Kina </option>
                                        <option value=PHP <?php echo  isset($country_currency_code) && $country_currency_code == 'PHP' ? 'selected' : '' ?>> PHP - Philippines Peso </option>
                                        <option value=PKR <?php echo  isset($country_currency_code) && $country_currency_code == 'PKR' ? 'selected' : '' ?>> PKR - Pakistan Rupee </option>
                                        <option value=PLN <?php echo  isset($country_currency_code) && $country_currency_code == 'PLN' ? 'selected' : '' ?>> PLN - Poland Zloty </option>
                                        <option value=PYG <?php echo  isset($country_currency_code) && $country_currency_code == 'PYG' ? 'selected' : '' ?>> PYG - Paraguay Guarani </option>
                                        <option value=QAR <?php echo  isset($country_currency_code) && $country_currency_code == 'QAR' ? 'selected' : '' ?>> QAR - Qatar Riyal </option>
                                        <option value=RON <?php echo  isset($country_currency_code) && $country_currency_code == 'RON' ? 'selected' : '' ?>> RON - Romania Leu </option>
                                        <option value=RSD <?php echo  isset($country_currency_code) && $country_currency_code == 'RSD' ? 'selected' : '' ?>> RSD - Serbia Dinar </option>
                                        <option value=RUB <?php echo  isset($country_currency_code) && $country_currency_code == 'RUB' ? 'selected' : '' ?>> RUB - Russia Ruble </option>
                                        <option value=RWF <?php echo  isset($country_currency_code) && $country_currency_code == 'RWF' ? 'selected' : '' ?>> RWF - Rwanda Franc </option>
                                        <option value=SAR <?php echo  isset($country_currency_code) && $country_currency_code == 'SAR' ? 'selected' : '' ?>> SAR - Saudi Arabia Riyal </option>
                                        <option value=SBD <?php echo  isset($country_currency_code) && $country_currency_code == 'SBD' ? 'selected' : '' ?>> SBD - Solomon Islands Dollar </option>
                                        <option value=SCR <?php echo  isset($country_currency_code) && $country_currency_code == 'SCR' ? 'selected' : '' ?>> SCR - Seychelles Rupee </option>
                                        <option value=SDG <?php echo  isset($country_currency_code) && $country_currency_code == 'SDG' ? 'selected' : '' ?>> SDG - Sudan Pound </option>
                                        <option value=SEK <?php echo  isset($country_currency_code) && $country_currency_code == 'SEK' ? 'selected' : '' ?>> SEK - Sweden Krona </option>
                                        <option value=SGD <?php echo  isset($country_currency_code) && $country_currency_code == 'SGD' ? 'selected' : '' ?>> SGD - Singapore Dollar </option>
                                        <option value=SHP <?php echo  isset($country_currency_code) && $country_currency_code == 'SHP' ? 'selected' : '' ?>> SHP - Saint Helena Pound </option>
                                        <option value=SLL <?php echo  isset($country_currency_code) && $country_currency_code == 'SLL' ? 'selected' : '' ?>> SLL - Sierra Leone Leone </option>
                                        <option value=SOS <?php echo  isset($country_currency_code) && $country_currency_code == 'SOS' ? 'selected' : '' ?>> SOS - Somalia Shilling </option>
                                        <option value=SPL <?php echo  isset($country_currency_code) && $country_currency_code == '“SP' ? 'selected' : '' ?>”> SPL - Seborga Luigino </option>
                                        <option value=SRD <?php echo  isset($country_currency_code) && $country_currency_code == 'SRD' ? 'selected' : '' ?>> SRD - Suriname Dollar </option>
                                        <option value=STN <?php echo  isset($country_currency_code) && $country_currency_code == 'STN' ? 'selected' : '' ?>> STN - São Tomé and Príncipe Dobra </option>
                                        <option value=SVC <?php echo  isset($country_currency_code) && $country_currency_code == 'SVC' ? 'selected' : '' ?>> SVC - El Salvador Colon </option>
                                        <option value=SYP <?php echo  isset($country_currency_code) && $country_currency_code == 'SYP' ? 'selected' : '' ?>> SYP - Syria Pound </option>
                                        <option value=SZL <?php echo  isset($country_currency_code) && $country_currency_code == 'SZL' ? 'selected' : '' ?>> SZL - eSwatini Lilangeni </option>
                                        <option value=THB <?php echo  isset($country_currency_code) && $country_currency_code == 'THB' ? 'selected' : '' ?>> THB - Thailand Baht </option>
                                        <option value=TJS <?php echo  isset($country_currency_code) && $country_currency_code == 'TJS' ? 'selected' : '' ?>> TJS - Tajikistan Somoni </option>
                                        <option value=TMT <?php echo  isset($country_currency_code) && $country_currency_code == 'TMT' ? 'selected' : '' ?>> TMT - Turkmenistan Manat </option>
                                        <option value=TND <?php echo  isset($country_currency_code) && $country_currency_code == 'TND' ? 'selected' : '' ?>> TND - Tunisia Dinar
                                        <option value=TOP <?php echo  isset($country_currency_code) && $country_currency_code == 'TOP' ? 'selected' : '' ?>> TOP - Tonga Pa’anga </option>
                                        <option value=TRY <?php echo  isset($country_currency_code) && $country_currency_code == 'TRY' ? 'selected' : '' ?>> TRY - Turkey Lira </option>
                                        <option value=TTD <?php echo  isset($country_currency_code) && $country_currency_code == 'TTD' ? 'selected' : '' ?>> TTD - Trinidad and Tobago Dollar </option>
                                        <option value=TVD <?php echo  isset($country_currency_code) && $country_currency_code == 'TVD' ? 'selected' : '' ?>> TVD - Tuvalu Dollar </option>
                                        <option value=TWD <?php echo  isset($country_currency_code) && $country_currency_code == 'TWD' ? 'selected' : '' ?>> TWD - Taiwan New Dollar </option>
                                        <option value=TZS <?php echo  isset($country_currency_code) && $country_currency_code == 'TZS' ? 'selected' : '' ?>> TZS - Tanzania Shilling </option>
                                        <option value=UAH <?php echo  isset($country_currency_code) && $country_currency_code == 'UAH' ? 'selected' : '' ?>> UAH - Ukraine Hryvnia </option>
                                        <option value=UGX <?php echo  isset($country_currency_code) && $country_currency_code == 'UGX' ? 'selected' : '' ?>> UGX - Uganda Shilling </option>
                                        <option value=USD <?php echo  isset($country_currency_code) && $country_currency_code == 'USD' ? 'selected' : '' ?>> USD - United States Dollar </option>
                                        <option value=UYU <?php echo  isset($country_currency_code) && $country_currency_code == 'UYU' ? 'selected' : '' ?>> UYU - Uruguay Peso” </option>
                                        <option value=UZS <?php echo  isset($country_currency_code) && $country_currency_code == 'UZS' ? 'selected' : '' ?>> UZS - Uzbekistan Som </option>
                                        <option value=VEF <?php echo  isset($country_currency_code) && $country_currency_code == 'VEF' ? 'selected' : '' ?>> VEF - Venezuela Bolívar </option>
                                        <option value=VND <?php echo  isset($country_currency_code) && $country_currency_code == 'VND' ? 'selected' : '' ?>> VND - Viet Nam Dong” </option>
                                        <option value=VUV <?php echo  isset($country_currency_code) && $country_currency_code == 'VUV' ? 'selected' : '' ?>> VUV - Vanuatu Vatu </option>
                                        <option value=WST <?php echo  isset($country_currency_code) && $country_currency_code == 'WST' ? 'selected' : '' ?>> WST - Samoa Tala </option>
                                        <option value=XAF <?php echo  isset($country_currency_code) && $country_currency_code == 'XAF' ? 'selected' : '' ?>> XAF - Communauté Financière Africaine (BEAC) CFA Franc BEAC </option>
                                        <option value=XCD <?php echo  isset($country_currency_code) && $country_currency_code == 'XCD' ? 'selected' : '' ?>> XCD - East Caribbean Dollar </option>
                                        <option value=XDR <?php echo  isset($country_currency_code) && $country_currency_code == 'XDR' ? 'selected' : '' ?>> XDR - International Monetary Fund (IMF) Special Drawing Rights </option>
                                        <option value=XOF <?php echo  isset($country_currency_code) && $country_currency_code == 'XOF' ? 'selected' : '' ?>> XOF - Communauté Financière Africaine (BCEAO) Franc </option>
                                        <option value=XPF <?php echo  isset($country_currency_code) && $country_currency_code == 'XPF' ? 'selected' : '' ?>> XPF - Comptoirs Français du Pacifique (CFP) Franc </option>
                                        <option value=YER <?php echo  isset($country_currency_code) && $country_currency_code == 'YER' ? 'selected' : '' ?>> YER - Yemen Rial </option>
                                        <option value=ZAR <?php echo  isset($country_currency_code) && $country_currency_code == 'ZAR' ? 'selected' : '' ?>> ZAR - South Africa Rand </option>
                                        <option value=ZMW <?php echo  isset($country_currency_code) && $country_currency_code == 'ZMW' ? 'selected' : '' ?>> ZMW - Zambia Kwacha </option>
                                        <option value=ZWD <?php echo  isset($country_currency_code) && $country_currency_code == 'ZWD' ? 'selected' : '' ?>> ZWD - Zimbabwe Dollar </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for='currency'><?= labels('currency_symbol', "Currency Symbol") ?></label>
                                    <input type='text' class='form-control' name='currency' id='currency' value="<?= isset($currency) ? $currency : '' ?>" />
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label><?= labels('decimal_point', "Decimal Point") ?></label>
                                <select class="form-control" name="decimal_point">
                                    <option value="0" <?php echo  isset($decimal_point)  && $decimal_point == '0' ? 'selected' : '' ?>>0</option>
                                    <option value="1" <?php echo  isset($decimal_point)  && $decimal_point == '1' ? 'selected' : '' ?>>1</option>
                                    <option value="2" <?php echo  isset($decimal_point)  && $decimal_point == '2' ? 'selected' : '' ?>>2</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Customer Version Settings -->
            <div class="col-md-6 col-sm-12 col-xl-4">
                <div class="card h-100">
                    <div class="row pl-3 m-0" style="border-bottom: solid 1px #e5e6e9;">
                        <div class="col ">
                            <div class="toggleButttonPostition"><?= labels('version_settings', "Version Settings") ?></div>
                        </div>
                        <div class="mr-4 mt-4">
                            <input type="" class="btn btn-primary" readonly value="<?= labels('customer_application', "Customer Application") ?>" style="cursor: default;">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="customer_current_version_android_app"><?= labels('current_version_of_android_app', "Current Version Of Android App") ?></label>
                                    <input type="tel" class="form-control" name="customer_current_version_android_app" id="customer_current_version_android_app" value="<?= isset($customer_current_version_android_app) ? $customer_current_version_android_app : '' ?>" required />
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="current_version_ios_app"><?= labels('current_version_of_IOS_app', "Current Version Of IOS App") ?></label>
                                    <input type="tel" class="form-control" name="customer_current_version_ios_app" id="customer_current_version_ios_app" value="<?= isset($customer_current_version_ios_app) ? $customer_current_version_ios_app : '' ?>" required />
                                </div>
                            </div>
                            <div class="col-md-12 mt-2">
                                <div class="form-group">
                                    <div class="control-label"><?= labels('compulsory_update_force_update', "Compulsory Update/Force Update") ?></div>
                                    <label class=" mt-2">
                                        <input type="hidden" name="customer_compulsary_update_force_update" value='0' id="customer_compulsary_update_force_update_value">
                                        <!-- <input type="checkbox" name="customer_compulsary_update_force_update" id="customer_compulsary_update_force_update" <?php echo isset($customer_compulsary_update_force_update) && $customer_compulsary_update_force_update == 1 ? "checked" : "" ?> class="custom-switch-input" value=<?= isset($compulsary_update_force_update) ? $compulsary_update_force_update : "0"; ?>>
                                        <span class="custom-switch-indicator"></span> -->
                                        <?php
                                        $customer_compulsary_update_force_update = isset($customer_compulsary_update_force_update) ? $customer_compulsary_update_force_update : 0;
                                        $isMaintenanceMode = $customer_compulsary_update_force_update == "1";
                                        $isChecked = isset($customer_compulsary_update_force_update) && $customer_compulsary_update_force_update == 1;
                                        $defaultValue = isset($compulsary_update_force_update) ? $compulsary_update_force_update : "0";
                                        ?>
                                        <input type="checkbox" class="status-switch" name="customer_compulsary_update_force_update" id="customer_compulsary_update_force_update" <?php echo $isChecked ? "checked" : "" ?> value="<?= $defaultValue; ?>">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Provider Version Settings -->
            <div class="col-md-6 col-sm-12 col-xl-4">
                <div class="card h-100">
                    <div class="row pl-3 m-0" style="border-bottom: solid 1px #e5e6e9;">
                        <div class="col ">
                            <div class="toggleButttonPostition"><?= labels('version_settings', "Version Settings") ?></div>
                        </div>
                        <div class="mr-4 mt-4">
                            <input type="" class="btn btn-primary" readonly value="<?= labels('provider_application', "Provider Application") ?> " style="cursor: default;">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="current_version_android_app"><?= labels('current_version_of_android_app', "Current Version Of Android App") ?></label>
                                    <input type="tel" class="form-control" name="provider_current_version_android_app" id="provider_current_version_android_app" value="<?= isset($provider_current_version_android_app)   ? $provider_current_version_android_app : '' ?>" required />
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="current_version_ios_app"><?= labels('current_version_of_IOS_app', "Current Version Of IOS App") ?></label>
                                    <input type="tel" class="form-control" name="provider_current_version_ios_app" id="current_version_ios_app" value="<?= isset($provider_current_version_ios_app) ? $provider_current_version_ios_app : '' ?>" required />
                                </div>
                            </div>
                            <div class="col-md-12 mt-2">
                                <div class="form-group">
                                    <div class="control-label"><?= labels('compulsory_update_force_update', "Compulsory Update/Force Update") ?></div>
                                    <label class=" mt-2">
                                        <input type="hidden" id="provider_compulsary_update_force_value" name="provider_compulsary_update_force_update" value='0'>
                                        <!-- <input type="checkbox" id="provider_compulsary_update_force_update" <?php echo isset($provider_compulsary_update_force_update) && $provider_compulsary_update_force_update == 1 ? "checked" : "" ?> name="provider_compulsary_update_force_update" class="custom-switch-input" value=<?= isset($compulsary_update_force_update) ? "checked" : "0"; ?>>
                                        <span class="custom-switch-indicator"></span> -->
                                        <?php
                                        $provider_compulsary_update_force_update = isset($provider_compulsary_update_force_update) ? $provider_compulsary_update_force_update : 0;
                                        $isMaintenanceMode = $provider_compulsary_update_force_update == "1";
                                        $isChecked = isset($provider_compulsary_update_force_update) && $provider_compulsary_update_force_update == 1;
                                        $defaultValue = isset($compulsary_update_force_update) ? $compulsary_update_force_update : "0";
                                        ?>
                                        <input type="checkbox" class="status-switch" name="provider_compulsary_update_force_update" id="provider_compulsary_update_force_update" <?php echo $isChecked ? "checked" : "" ?> value="<?= $defaultValue; ?>">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <!-- Customer App urls  -->
            <div class="col-md-4 col-sm-12 col-xl-4">
                <div class="card h-100">
                    <div class="row pl-3 m-0" style="border-bottom: solid 1px #e5e6e9;">
                        <div class="col ">
                            <div class="toggleButttonPostition"><?= labels('app_url', "App URL") ?></div>
                        </div>
                        <div class="mr-4 mt-4">
                            <input type="" class="btn btn-primary" readonly value="<?= labels('customer_application', "Customer Application") ?>" style="cursor: default;">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="form-group mb-0">
                                    <label><?= labels('playstore_url', "Playstore URL") ?></label>
                                    <input id="customer_playstore_url" class="form-control" value="<?= isset($customer_playstore_url) ? $customer_playstore_url : "" ?>" type="text" name="customer_playstore_url" placeholder="<?= labels('enter_customer_playstore_url', 'Enter customer playstore application url') ?>">
                                </div>
                            </div>
                            <div class="col-md-12 mt-2">
                                <div class="form-group mb-0">
                                    <label><?= labels('appstore_url', "Appstore URL") ?></label>
                                    <input id="customer_appstore_url" value="<?= isset($customer_appstore_url) ? $customer_appstore_url : "" ?>" class="form-control" type="text" name="customer_appstore_url" placeholder="<?= labels('enter_customer_appstore_url', 'Enter customer appstore application url') ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Provider App urls  -->
            <div class="col-md-6 col-sm-12 col-xl-4">
                <div class="card h-100">
                    <div class="row pl-3 m-0" style="border-bottom: solid 1px #e5e6e9;">
                        <div class="col ">
                            <div class="toggleButttonPostition"><?= labels('app_url', "App URL") ?></div>
                        </div>
                        <div class="mr-4 mt-4">
                            <input type="" class="btn btn-primary" readonly value="<?= labels('provider_application', "Provider Application") ?>" style="cursor: default;">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="form-group mb-0">
                                    <label><?= labels('playstore_url', "Playstore URL") ?></label>
                                    <input id="provider_playstore_url" class="form-control" value="<?= isset($provider_playstore_url) ? $provider_playstore_url : "" ?>" type="text" name="provider_playstore_url" placeholder="<?= labels('enter_provider_playstore_app_url', 'Enter provider playstore application url') ?>">
                                </div>
                            </div>
                            <div class="col-md-12 mt-2">
                                <div class="form-group mb-0">
                                    <label><?= labels('appstore_url', "Appstore URL") ?></label>
                                    <input id="provider_appstore_url" class="form-control" type="text" value="<?= isset($provider_appstore_url) ? $provider_appstore_url : "" ?>" name="provider_appstore_url" placeholder="<?= labels('enter_provider_appstore_app_url', 'Enter provider appstore application url') ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Customer Setting -->
            <div class="col-md-6 col-sm-12 col-xl-4">
                <div class="card h-100">
                    <div class="row pl-3 m-0" style="border-bottom: solid 1px #e5e6e9;">
                        <div class="col ">
                            <div class="toggleButttonPostition"><?= labels('customer_setting', "Customer Setting") ?></div>
                        </div>
                        <div class="mr-4 mt-4">
                            <input type="" readonly class="btn btn-primary" value="<?= labels('customer_application', "Customer Application") ?>" style="cursor: default;">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label><?= labels('provider_location_in_provider_details', "show provider location on provider details") ?></label>
                                    <br>
                                    <label class="custom-switch mt-2 " style="padding-top:0">
                                        <?php
                                        $provider_location_in_provider_details = isset($provider_location_in_provider_details) ? $provider_location_in_provider_details : 0;
                                        $isMaintenanceMode = $provider_location_in_provider_details == "1";
                                        $isChecked = isset($provider_location_in_provider_details) && $provider_location_in_provider_details == 1;
                                        $defaultValue = isset($provider_location_in_provider_details) ? $provider_location_in_provider_details : "0";
                                        ?>
                                        <input type="checkbox" class="status-switch" name="provider_location_in_provider_details" id="provider_location_in_provider_details" <?php echo $isChecked ? "checked" : "" ?> value="<?= $defaultValue; ?>">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <!-- Android Google Admob ads -->
            <div class="col-md-6 col-sm-12 col-xl-6">
                <div class="card h-100">
                    <div class="row pl-3 m-0" style="border-bottom: solid 1px #e5e6e9;">
                        <div class="col ">
                            <div class="toggleButttonPostition"><?= labels('android_google_admob_ads', "Android Google Admob Ads") ?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="android_google_interstitial_id"><?= labels('google_interstitial_id', "Google Interstitial Id") ?></label>
                                    <input type="text" class="form-control" name="android_google_interstitial_id" id="android_google_interstitial_id" value="<?= isset($android_google_interstitial_id) ? $android_google_interstitial_id : '' ?>" required />
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="android_google_banner_id"><?= labels('google_banner_id', "Google Banner Id") ?></label>
                                    <input type="text" class="form-control" name="android_google_banner_id" id="android_google_banner_id" value="<?= isset($android_google_banner_id) ? $android_google_banner_id : '' ?>" required />
                                </div>
                            </div>
                            <div class="col-md-12 mt-2">
                                <div class="form-group">
                                    <div class="control-label"><?= labels('android_google_ads_status', "Android Google Ads Status") ?></div>
                                    <label class=" mt-2">
                                        <input type="hidden" name="android_google_ads_status" value='0' id="android_google_ads_status_value">
                                        <?php
                                        $android_google_ads_status = isset($android_google_ads_status) ? $android_google_ads_status : 0;
                                        $isMaintenanceMode = $android_google_ads_status == "1";
                                        $isChecked = isset($android_google_ads_status) && $android_google_ads_status == 1;
                                        $defaultValue = isset($android_google_ads_status) ? $android_google_ads_status : "0";
                                        ?>
                                        <input type="checkbox" class="status-switch" name="android_google_ads_status" id="android_google_ads_status" <?php echo $isChecked ? "checked" : "" ?> value="<?= $defaultValue; ?>">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-12 col-xl-6">
                <div class="card h-100">
                    <div class="row pl-3 m-0" style="border-bottom: solid 1px #e5e6e9;">
                        <div class="col ">
                            <div class="toggleButttonPostition"><?= labels('ios_google_admob_ads', "IOS Google Admob Ads") ?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="ios_google_interstitial_id"><?= labels('google_interstitial_id', "Google Interstitial Id") ?></label>
                                    <input type="text" class="form-control" name="ios_google_interstitial_id" id="ios_google_interstitial_id" value="<?= isset($ios_google_interstitial_id) ? $ios_google_interstitial_id : '' ?>" required />
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="ios_google_banner_id"><?= labels('google_banner_id', "Google Banner Id") ?></label>
                                    <input type="text" class="form-control" name="ios_google_banner_id" id="ios_google_banner_id" value="<?= isset($ios_google_banner_id) ? $ios_google_banner_id : '' ?>" required />
                                </div>
                            </div>
                            <div class="col-md-12 mt-2">
                                <div class="form-group">
                                    <div class="control-label"><?= labels('ios_google_ads_status', "IOS Google Ads Status") ?></div>
                                    <label class=" mt-2">
                                        <input type="hidden" name="ios_google_ads_status" value='0' id="android_google_ads_status_value">
                                        <?php
                                        $ios_google_ads_status = isset($ios_google_ads_status) ? $ios_google_ads_status : 0;
                                        $isMaintenanceMode = $ios_google_ads_status == "1";
                                        $isChecked = isset($ios_google_ads_status) && $ios_google_ads_status == 1;
                                        $defaultValue = isset($ios_google_ads_status) ? $ios_google_ads_status : "0";
                                        ?>
                                        <input type="checkbox" class="status-switch" name="ios_google_ads_status" id="ios_google_ads_status" <?php echo $isChecked ? "checked" : "" ?> value="<?= $defaultValue; ?>">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <!-- Customer Maintenance Mode -->
            <div class="col-md-6 col-sm-12 col-xl-6">
                <div class="card h-100">
                    <div class="row pl-3 m-0" style="border-bottom: solid 1px #e5e6e9;">
                        <div class="col ">
                            <div class="toggleButttonPostition"><?= labels('maintenance_mode', "Maintenance Mode") ?></div>
                        </div>
                        <div class="mr-4 mt-4">
                            <input type="" class="btn btn-primary" readonly value="<?= labels('customer_application', "Customer Application") ?>" style="cursor: default;">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label><?= labels('start_and_end_date', "Start And End Date") ?></label>
                                    <input type="text" name="customer_app_maintenance_schedule_date" id="customer_app_maintenance_schedule_date" class="form-control daterange-cus " value="<?php echo $customer_app_maintenance_schedule_date ?? ""  ?>">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mb-0">
                                    <label><?= labels('message_for_customer_application', "Message for Customer Application") ?></label>
                                    <textarea class="form-control" style="min-height:60px" name="message_for_customer_application" style="min-height:60px" rows="1"><?php echo $message_for_customer_application ?? "" ?></textarea>
                                </div>
                            </div>
                            <div class="col-md-12  mt-2">
                                <div class="form-group">
                                    <label><?= labels('maintenance_mode', "Maintenance Mode") ?></label>
                                    <br>
                                    <label class=" mt-1 " style="padding-top:0">
                                        <?php
                                        $customer_app_maintenance_mode = isset($customer_app_maintenance_mode) ? $customer_app_maintenance_mode : 0;
                                        $isMaintenanceMode = $customer_app_maintenance_mode == "1";
                                        $isChecked = isset($customer_app_maintenance_mode) && $customer_app_maintenance_mode == 1;
                                        $defaultValue = isset($compulsary_update_force_update) ? $compulsary_update_force_update : "0";
                                        ?>
                                        <input type="checkbox" class="status-switch" name="customer_app_maintenance_mode" id="customer_maintenance_mode" <?php echo $isChecked ? "checked" : "" ?> value="<?= $defaultValue; ?>">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Provider Maintenance Mode -->
            <div class="col-md-6 col-sm-12 col-xl-6">
                <div class="card h-100">
                    <div class="row pl-3 m-0" style="border-bottom: solid 1px #e5e6e9;">
                        <div class="col ">
                            <div class="toggleButttonPostition"><?= labels('maintenance_mode', "Maintenance Mode") ?></div>
                        </div>
                        <div class="mr-4 mt-4">
                            <input type="" readonly class="btn btn-primary" value="<?= labels('provider_application', "Provider Application") ?>" style="cursor: default;">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label><?= labels('start_and_end_date', "Start And End Date") ?></label>
                                    <input type="text" name="provider_app_maintenance_schedule_date" id="provider_app_maintenance_schedule_date" class="form-control  daterange-cus" value="<?php echo $provider_app_maintenance_schedule_date ?? ""  ?>">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mb-0">
                                    <label><?= labels('message_for_provider_application', "Message for Provider Application") ?></label>
                                    <textarea class="form-control" style="min-height:60px" name="message_for_provider_application" rows="1"><?php echo $message_for_provider_application ?? "" ?></textarea>
                                </div>
                            </div>
                            <div class="col-md-12 mt-2">
                                <div class="form-group">
                                    <label><?= labels('maintenance_mode', "Maintenance Mode") ?></label>
                                    <br>
                                    <label class=" mt-1 " style="padding-top:0">
                                        <input type="hidden" name="provider_app_maintenance_mode" value='0' id="provider_maintenance_mode_value">
                                        <?php
                                        $provider_app_maintenance_mode = isset($provider_app_maintenance_mode) ? $provider_app_maintenance_mode : 0;
                                        $isMaintenanceMode = $provider_app_maintenance_mode == "1";
                                        $isChecked = isset($provider_app_maintenance_mode) && $provider_app_maintenance_mode == 1;
                                        $defaultValue = isset($compulsary_update_force_update) ? $compulsary_update_force_update : "0";
                                        ?>
                                        <input type="checkbox" class="status-switch" name="provider_app_maintenance_mode" id="provider_maintenance_mode" <?php echo $isChecked ? "checked" : "" ?> value="<?= $defaultValue; ?>">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php if ($permissions['update']['settings'] == 1) : ?>

            <div class="row mb-3">
                <div class="col-md d-flex justify-content-end">
                    <input type='submit' name='update' id='update' value='<?= labels('save_changes', "Save") ?>' class='btn btn-lg bg-new-primary' />
                    <?= form_close() ?>
                </div>
            </div>
        <?php endif; ?>

    </section>
</div>
<script>
    $('#provider_location_in_provider_details').on('change', function() {
        var value = this.checked ? "1" : "0";
        this.value = this.checked ? "1" : "0";
    }).change();
    $('#otp_system').on('change', function() {
        this.value = this.checked ? 1 : 0;
    }).change();
</script>
<script>
    $(function() {
        $('.fa').popover({
            trigger: "hover"
        });
    })
    $('#customer_compulsary_update_force_update').on('change', function() {
        this.value = this.checked ? 1 : 0;
    }).change();
    $('#provider_compulsary_update_force_update').on('change', function() {
        this.value = this.checked ? 1 : 0;
    }).change();
    $('#customer_maintenance_mode').on('change', function() {
        this.value = this.checked ? 1 : 0;
    }).change();
    $('#provider_maintenance_mode').on('change', function() {
        this.value = this.checked ? 1 : 0;
    }).change();
    $('#android_google_ads_status').on('change', function() {
        this.value = this.checked ? 1 : 0;
    }).change();
    $('#ios_google_ads_status').on('change', function() {
        this.value = this.checked ? 1 : 0;
    }).change();
    $(document).ready(function() {
        var isMaintenanceMode = <?= json_encode($isChecked) ?>;

        function handleSwitchChange(checkbox) {
            var switchery = $(checkbox).siblings('.switchery');
            if (checkbox.checked) {
                switchery.addClass('yes-content').removeClass('no-content');
            } else {
                switchery.addClass('no-content').removeClass('yes-content');
            }
        }
        handleSwitchChange($('#customer_maintenance_mode')[0]);
        handleSwitchChange($('#provider_maintenance_mode')[0]);
        handleSwitchChange($('#customer_compulsary_update_force_update')[0]);
        handleSwitchChange($('#provider_compulsary_update_force_update')[0]);
        handleSwitchChange($('#provider_location_in_provider_details')[0]);
        handleSwitchChange($('#android_google_ads_status')[0]);
        handleSwitchChange($('#ios_google_ads_status')[0]);
        $('#customer_maintenance_mode').on('change', function() {
            handleSwitchChange(this);
            this.value = this.checked ? 1 : 0;
        });
        $('#provider_maintenance_mode').on('change', function() {
            handleSwitchChange(this);
            this.value = this.checked ? 1 : 0;
        });
        $('#customer_compulsary_update_force_update').on('change', function() {
            handleSwitchChange(this);
            this.value = this.checked ? 1 : 0;
        });
        $('#provider_compulsary_update_force_update').on('change', function() {
            handleSwitchChange(this);
            this.value = this.checked ? 1 : 0;
        });
        $('#provider_location_in_provider_details').on('change', function() {
            handleSwitchChange(this);
            this.value = this.checked ? 1 : 0;
        });
        $('#android_google_ads_status').on('change', function() {
            handleSwitchChange(this);
            this.value = this.checked ? 1 : 0;
        });
        $('#ios_google_ads_status').on('change', function() {
            handleSwitchChange(this);
            this.value = this.checked ? 1 : 0;
        });
    });
</script>