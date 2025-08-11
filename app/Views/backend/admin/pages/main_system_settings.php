<!-- Main Content -->
<div class="main-content">
    <section class="section" id="pill-general_settings" role="tabpanel">
        <div class="row mt-3">
            <!-- //general Settings -->
            <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-12 mb-3">
                <a href="<?= base_url("admin/settings/general-settings") ?>" class="card setting_active_tab h-100 " style="text-decoration: none;">
                    <div class="content d-flex h-100">
                        <div class="row mx-2 ">
                            <div class="provider_a test bg-new-primary text-white" style="box-shadow: 0px 8px 26px #00b9f02e;">
                                <span class="material-symbols-outlined material-symbols-outlined-new">
                                    settings
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class=""><?= labels('general_setting', 'General Setting') ?></h5>
                        <p style="line-height: 18px;"><?= labels('general_setting_description', 'Includes company settings, logos, support hours, etc.') ?></p>
                        <div class=""><?= labels('go_to_settings', 'Go to settings') ?> <i class="fas fa-arrow-right mt-2 arrow_icon"></i></div>
                    </div>
                </a>
            </div>
            <!-- app settings -->
            <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-12 mb-3">
                <a href="<?= base_url('admin/settings/app_settings') ?>" class="card setting_active_tab h-100" style="text-decoration: none;">
                    <div class="content d-flex h-100">
                        <div class="row mx-2">
                            <div class="provider_a bg-new-primary text-white" style="box-shadow: 0px 8px 26px ;">
                                <span class="material-symbols-outlined material-symbols-outlined-new">
                                    developer_mode
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class=""><?= labels('app_settings', 'App Setting') ?></h5>
                        <p style="line-height: 18px;"><?= labels('application_setting_description', 'Includes Country Currency, Version Settings, Maintenance Mode Settings.') ?></p>
                        <div><?= labels('go_to_settings', 'Go to settings') ?> <i class="fas fa-arrow-right mt-2 arrow_icon"></i></div>
                    </div>
                </a>
            </div>
            <!-- web settings -->
            <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-12 mb-3">
                <a href="<?= base_url('admin/settings/web_setting') ?>" class="card setting_active_tab h-100" style="text-decoration: none;">
                    <div class="content d-flex h-100">
                        <div class="row mx-2">
                            <div class="provider_a bg-new-primary text-white" style="box-shadow: 0px 8px 26px #00b9f02e;">
                                <span class="material-symbols-outlined material-symbols-outlined-new">
                                    language
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class=""><?= labels('web_settings', 'Web Setting') ?></h5>
                        <p style="line-height: 18px;"><?= labels('web_setting_description', 'Includes web logos, other settings.') ?></p>
                        <div><?= labels('go_to_settings', 'Go to settings') ?> <i class="fas fa-arrow-right mt-2 arrow_icon"></i></div>
                    </div>
                </a>
            </div>
            <!-- SMTP mail settings -->
            <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-12 mb-3">
                <a href="<?= base_url('admin/settings/email-settings') ?>" class="card setting_active_tab h-100" style="text-decoration: none;">
                    <div class="content d-flex h-100">
                        <div class="row mx-2">
                            <div class="provider_a bg-new-primary text-white" style="box-shadow: 0px 8px 26px #00b9f02e;">
                                <span class="material-symbols-outlined material-symbols-outlined-new">
                                    mail
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class=""><?= labels('SMTP_email', 'SMTP (Email)') ?></h5>
                        <p style="line-height: 18px;"><?= labels('mail_description', 'Includes Email Settings.') ?></p>
                        <div><?= labels('go_to_settings', 'Go to settings') ?> <i class="fas fa-arrow-right mt-2 arrow_icon"></i></div>
                    </div>
                </a>
            </div>
            <!-- Payment gateway settings -->
            <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-12 mb-3">
                <a href="<?= base_url('admin/settings/pg-settings') ?>" class="card setting_active_tab h-100" style="text-decoration: none;">
                    <div class="content d-flex h-100">
                        <div class="row mx-2">
                            <div class="provider_a bg-new-primary text-white" style="box-shadow: 0px 8px 26px #00b9f02e;">
                                <span class="material-symbols-outlined material-symbols-outlined-new">
                                    monetization_on
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class=""><?= labels('payment_gateways', 'Payment Gateways') ?></h5>
                        <p style="line-height: 18px;"><?= labels('payment_gateway_description', 'Includes Paypal, RazorPay, Paystack, Stripe Settings.') ?></p>
                        <div><?= labels('go_to_settings', 'Go to settings') ?> <i class="fas fa-arrow-right mt-2 arrow_icon"></i></div>
                    </div>
                </a>
            </div>
            <!-- API key settings -->
            <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-12 mb-3">
                <a href="<?= base_url('admin/settings/api_key_settings') ?>" class="card setting_active_tab h-100" style="text-decoration: none;">
                    <div class="content d-flex h-100">
                        <div class="row mx-2">
                            <div class="provider_a bg-new-primary text-white" style="box-shadow: 0px 8px 26px #00b9f02e;">
                                <span class="material-symbols-outlined material-symbols-outlined-new">
                                    api
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class=""><?= labels('api_key_settings', 'API Key Setting') ?></h5>
                        <p style="line-height: 18px;"><?= labels('api_key_description', 'Includes Client API Keys, Google API key for map, FCM Server Key.') ?></p>
                        <div><?= labels('go_to_settings', 'Go to settings') ?> <i class="fas fa-arrow-right mt-2 arrow_icon"></i></div>
                    </div>
                </a>
            </div>
            <!-- Firebase settings -->
            <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-12 mb-3">
                <a href="<?= base_url('admin/settings/firebase_settings') ?>" class="card setting_active_tab h-100" style="text-decoration: none;">
                    <div class="content d-flex h-100">
                        <div class="row mx-2">
                            <div class="provider_a bg-new-primary text-white" style="box-shadow: 0px 8px 26px #00b9f02e;">
                                <span class="material-symbols-outlined material-symbols-outlined-new">
                                    storage
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class=""><?= labels('firebase_settings', 'Firebase Settings') ?></h5>
                        <p style="line-height: 18px;"><?= labels('firebase_settings_description', 'Includes apiKey, authDomain, projectId, storageBucket, appId, etc.') ?></p>
                        <div><?= labels('go_to_settings', 'Go to settings') ?> <i class="fas fa-arrow-right mt-2 arrow_icon"></i></div>
                    </div>
                </a>
            </div>
            <!-- Tax settings -->
            <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-12 mb-3">
                <a href="<?= base_url('admin/settings/system_tax_settings') ?>" class="card setting_active_tab h-100" style="text-decoration: none;">
                    <div class="content d-flex h-100">
                        <div class="row mx-2">
                            <div class="provider_a bg-new-primary text-white" style="box-shadow: 0px 8px 26px #00b9f02e;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="33.999" height="34" viewBox="0 0 33.999 34" class="apple">
                                    <g id="tax" transform="translate(-528.75 -528.75)">
                                        <path id="Path_12941" data-name="Path 12941" d="M562.749,696.357v11.9a1.185,1.185,0,0,1-1.205.717q-15.795-.014-31.589,0a1.185,1.185,0,0,1-1.205-.717v-11.9a1.182,1.182,0,0,1,1.2-.717q15.8.014,31.59,0A1.185,1.185,0,0,1,562.749,696.357Zm-17.031,9.14h.73a1.292,1.292,0,0,1,1.3.9.968.968,0,0,0,1.275.649,1,1,0,0,0,.522-1.308q-1.353-3.749-2.73-7.487a1.113,1.113,0,0,0-2.1-.013c-.13.327-.249.659-.37.989q-1.181,3.212-2.358,6.426a1.014,1.014,0,0,0,.5,1.4.979.979,0,0,0,1.3-.716c.068-.181.143-.359.2-.544a.366.366,0,0,1,.414-.3c.442.014.885,0,1.328,0Zm5.879,1.63a1.084,1.084,0,0,0,.858-.532c.51-.745,1.027-1.486,1.543-2.227.242-.347.261-.347.5,0q.784,1.126,1.563,2.254a.944.944,0,0,0,.957.483.985.985,0,0,0,.623-1.589c-.632-.936-1.268-1.87-1.934-2.78a.613.613,0,0,1,0-.86c.667-.909,1.3-1.843,1.933-2.78a.981.981,0,0,0-.624-1.588.953.953,0,0,0-.957.484q-.789,1.142-1.582,2.282c-.216.31-.244.309-.466-.007-.523-.75-1.039-1.5-1.565-2.253a.96.96,0,0,0-1.409-.337,1.03,1.03,0,0,0-.147,1.481c.511.745,1.032,1.481,1.54,2.229.2.3.552.561.556.907,0,.363-.358.632-.564.938-.53.786-1.077,1.561-1.611,2.345a.936.936,0,0,0-.049,1.032.957.957,0,0,0,.831.515Zm-15.317-4.185v2.873c0,.806.359,1.3.954,1.315s.986-.5.986-1.316c0-1.972.007-3.945-.006-5.917,0-.33.1-.445.416-.426.386.024.775.012,1.162,0a1,1,0,1,0,0-1.994q-2.539-.011-5.08,0a1,1,0,1,0,0,1.994c.4.008.8.017,1.2,0,.291-.015.385.093.381.392-.014,1.026-.005,2.052-.005,3.078Z" transform="translate(0 -152.387)" fill="#fff" />
                                        <path id="Path_12942" data-name="Path 12942" d="M598.928,528.75c.009.159.024.317.025.476q0,3.435,0,6.871c0,.938.355,1.312,1.246,1.313h6.526q.494,0,.5.531c0,1.2-.01,2.393.006,3.589,0,.331-.074.455-.418.454q-11.624-.014-23.248,0c-.314,0-.411-.1-.41-.426.01-3.567,0-7.133.01-10.7a2.139,2.139,0,0,1,1.53-2.108h14.238Zm-8.211,6.471c.764,0,1.528.01,2.292,0a1,1,0,1,0,0-1.989q-2.293-.018-4.584,0a.951.951,0,0,0-.932.63.989.989,0,0,0,.962,1.36C589.211,535.233,589.964,535.222,590.716,535.221Zm.009,4.379c.742,0,1.485.005,2.227,0a1,1,0,1,0,.017-1.994c-1.5-.008-2.99,0-4.486,0a1.084,1.084,0,0,0-.623.177,1,1,0,0,0,.574,1.818c.764.013,1.528,0,2.292,0Z" transform="translate(-49.438)" fill="#fff" />
                                        <path id="Path_12943" data-name="Path 12943" d="M584.642,868.177a2.148,2.148,0,0,1-1.538-2.328c.024-.375,0-.752,0-1.128,0-.239.009-.479,0-.718-.008-.222.085-.313.292-.3.087.006.175,0,.262,0h22.956c.088,0,.175.006.262,0,.207-.015.3.077.293.3-.007.775.031,1.552-.015,2.324a1.967,1.967,0,0,1-1.185,1.713c-.109.054-.223.092-.335.138h-21Z" transform="translate(-49.391 -305.428)" fill="#fff" />
                                        <path id="Path_12944" data-name="Path 12944" d="M785.613,539.858c0-.866,0-1.732,0-2.6,0-.106-.054-.254.071-.306.1-.043.173.085.243.154q2.723,2.716,5.44,5.435c.068.068.192.134.151.244s-.18.068-.275.068c-1.754,0-3.509,0-5.263.006-.281,0-.378-.092-.374-.374.013-.877,0-1.754,0-2.631Z" transform="translate(-234.252 -7.448)" fill="#fff" />
                                        <path id="Path_12945" data-name="Path 12945" d="M712.006,758.238c-.228,0-.456,0-.683,0-.184,0-.252-.075-.183-.254.251-.66.5-1.321.751-1.98.062-.161.154-.173.219-.006.257.669.506,1.343.759,2.014.065.173-.017.228-.178.227-.228,0-.456,0-.683,0Z" transform="translate(-166.243 -207.062)" fill="#fff" />
                                    </g>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class=""><?= labels('system_tax_settings', 'Tax Settings') ?></h5>
                        <p style="line-height: 18px;"><?= labels('system_tax_description', 'Includes Taxes Settings.') ?></p>
                        <div><?= labels('go_to_settings', 'Go to settings') ?> <i class="fas fa-arrow-right mt-2 arrow_icon"></i></div>
                    </div>
                </a>
            </div>
            <!-- Language settings -->
            <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-12 mb-3">
                <a href="<?= base_url('admin/languages') ?>" class="card setting_active_tab h-100" style="text-decoration: none;">
                    <div class="content d-flex h-100">
                        <div class="row mx-2">
                            <div class="provider_a bg-new-primary text-white" style="box-shadow: 0px 8px 26px #00b9f02e;">
                                <span class="material-symbols-outlined material-symbols-outlined-new">
                                    g_translate
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class=""><?= labels('language_setting', 'Language Setting') ?></h5>
                        <p style="line-height: 18px;"><?= labels('language_setting_description', 'Includes Language Settings.') ?></p>
                        <div><?= labels('go_to_settings', 'Go to settings') ?> <i class="fas fa-arrow-right mt-2 arrow_icon"></i></div>
                    </div>
                </a>
            </div>
            <!-- Country Codes settings -->
            <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-12 mb-3">
                <a href="<?= base_url('admin/settings/country_codes') ?>" class="card setting_active_tab h-100" style="text-decoration: none;">
                    <div class="content d-flex h-100">
                        <div class="row mx-2">
                            <div class="provider_a bg-new-primary text-white" style="box-shadow: 0px 8px 26px #00b9f02e;">
                                <span class="material-symbols-outlined material-symbols-outlined-new">
                                    call
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class=""><?= labels('country_codes', 'Country Codes') ?></h5>
                        <p style="line-height: 18px;"><?= labels('include_contry_codes', 'Includes Contry Codes.') ?></p>
                        <div><?= labels('go_to_settings', 'Go to settings') ?> <i class="fas fa-arrow-right mt-2 arrow_icon"></i></div>
                    </div>
                </a>
            </div>
            <!-- Terms & Privacy Settings settings -->
            <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-12 mb-3">
                <a href="<?= base_url('admin/settings/customer-terms-and-conditions') ?>" class="card setting_active_tab h-100" style="text-decoration: none;">
                    <div class="content d-flex h-100">
                        <div class="row mx-2">
                            <div class="provider_a bg-new-primary text-white" style="box-shadow: 0px 8px 26px #00b9f02e;">
                                <span class="material-symbols-outlined material-symbols-outlined-new">
                                    description
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class=""><?= labels('terms_and_privacy_settings', 'Terms & Privacy Settings') ?></h5>
                        <p style="line-height: 18px;"><?= labels('terms_and_condition_description', 'Includes Terms and Conditions, Privacy Policies, etc.') ?></p>
                        <div><?= labels('go_to_settings', 'Go to settings') ?> <i class="fas fa-arrow-right mt-2 arrow_icon"></i></div>
                    </div>
                </a>
            </div>
            <!-- System Updater settings -->
            <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-12 mb-3">
                <a href="<?= base_url('admin/settings/updater') ?>" class="card setting_active_tab h-100" style="text-decoration: none;">
                    <div class="content d-flex h-100">
                        <div class="row mx-2">
                            <div class="provider_a bg-new-primary text-white" style="box-shadow: 0px 8px 26px #00b9f02e;">
                                <span class="material-symbols-outlined material-symbols-outlined-new">
                                    change_circle
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class=""><?= labels('system_updater', 'System Updater') ?></h5>
                        <p style="line-height: 18px;"><?= labels('system_update_description', 'In System Updater, you can update the admin panel.') ?></p>
                        <div><?= labels('go_to_settings', 'Go to settings') ?> <i class="fas fa-arrow-right mt-2 arrow_icon"></i></div>
                    </div>
                </a>
            </div>
            <!-- Email Settings -->
            <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-12 mb-3">
                <a href="<?= base_url('admin/settings/email_template_list') ?>" class="card setting_active_tab h-100" style="text-decoration: none;">
                    <div class="content d-flex h-100">
                        <div class="row mx-2">
                            <div class="provider_a bg-new-primary text-white" style="box-shadow: 0px 8px 26px #00b9f02e;">
                                <span class="material-symbols-outlined material-symbols-outlined-new">
                                    change_circle
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class=""><?= labels('email_configuration', 'Email Configuration') ?></h5>
                        <p style="line-height: 18px;"><?= labels('system_email_configuration_description', 'Manages email type, subject, BCC, CC, and template settings.') ?></p>
                        <div><?= labels('go_to_settings', 'Go to settings') ?> <i class="fas fa-arrow-right mt-2 arrow_icon"></i></div>
                    </div>
                </a>
            </div>


            <!-- SMS Gateway Settings -->
            <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-12 mb-3">
                <a href="<?= base_url('admin/settings/sms-gateways') ?>" class="card setting_active_tab h-100" style="text-decoration: none;">
                    <div class="content d-flex h-100">
                        <div class="row mx-2">
                            <div class="provider_a bg-new-primary text-white" style="box-shadow: 0px 8px 26px #00b9f02e;">
                                <span class="material-symbols-outlined material-symbols-outlined-new">
                                    sms
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class=""><?= labels('sms_gateway_settings', 'SMS Gateway Settings') ?></h5>
                        <p style="line-height: 18px;"><?= labels('system_sms_gateway_settings', 'Manages sms gateway and  template settings.') ?></p>
                        <div><?= labels('go_to_settings', 'Go to settings') ?> <i class="fas fa-arrow-right mt-2 arrow_icon"></i></div>
                    </div>
                </a>
            </div>

            <!-- Notification Settings -->
            <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-12 mb-3">
                <a href="<?= base_url('admin/settings/notification-settings') ?>" class="card setting_active_tab h-100" style="text-decoration: none;">
                    <div class="content d-flex h-100">
                        <div class="row mx-2">
                            <div class="provider_a bg-new-primary text-white" style="box-shadow: 0px 8px 26px #00b9f02e;">
                                <span class="material-symbols-outlined material-symbols-outlined-new">
                                    notifications
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class=""><?= labels('notification_settings', 'Notification Settings') ?></h5>
                        <p style="line-height: 18px;"><?= labels('system_notification_settings_description', 'Manages SMS ,email and notificatioo settings.') ?></p>
                        <div><?= labels('go_to_settings', 'Go to settings') ?> <i class="fas fa-arrow-right mt-2 arrow_icon"></i></div>
                    </div>
                </a>
            </div>



        </div>
    </section>
</div>