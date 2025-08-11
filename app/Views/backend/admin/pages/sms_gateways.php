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
            <h1><?= labels('sms_gateways', "SMS Gateways") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active">
                    <a href="<?= base_url('/admin/dashboard') ?>">
                        <i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?>
                    </a>
                </div>
                <div class="breadcrumb-item">
                    <a href="<?= base_url('/admin/settings/system-settings') ?>">
                        <?= labels('system_settings', "System Settings") ?>
                    </a>
                </div>
                <div class="breadcrumb-item"><?= labels('sms_gateways', "SMS Gateways") ?></div>
            </div>
        </div>
        <?php
        $settings = get_settings('system_settings', true);
        $sms_gateway_setting = get_settings('sms_gateway_setting');
        $sms_gateway_data = is_string($sms_gateway_setting) ? json_decode($sms_gateway_setting, true) : [];
        ?>
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link active" id="twilio-tab" data-toggle="tab" href="#twilio" role="tab"><?= labels('sms_gateways_configuration', "SMS Gateways Configuration") ?></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="sms-template-tab" data-toggle="tab" href="#sms_template" role="tab"><?= labels('sms_templates', "SMS Templates") ?></a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="twilio" role="tabpanel">
                <input type="hidden" id="sms_gateway_data" value='<?= json_encode($sms_gateway_data) ?>' />
                <form method="POST" action="<?= base_url('admin/settings/sms-gateway-settings') ?>">
                    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
                    <div class="row" id="">
                        <div class="col-md-6">
                            <div class="card px-3">
                                <div class="row border_bottom_for_cards mb-3">
                                    <div class="col ">
                                        <div class='toggleButttonPostition'><?= labels('twilio', 'Twilio') ?></div>
                                    </div>
                                    <div class="col d-flex justify-content-end mt-4">
                                        <div class="custom-control custom-switch">
                                            <?php
                                            $twilio_status = (isset($twilio['twilio_status']) && ($twilio['twilio_status']=="1")) ? 1 : 0;
                                            ?>

                                            <input id="twilio_status" class="custom-control-input toggle-switch" type="checkbox" name="twilio_status" <?=($twilio_status)==1 ?'checked':''?>>
                                            <label for="twilio_status" class="custom-control-label"><?= labels('twilio', 'Twilio') ?> <?= labels('status', 'Status') ?></label>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="twilio_account_sid"><?= labels('account_sid', 'Account SID') ?></label>
                                                <input type="text" value="<?= isset($twilio['twilio_account_sid']) ? (ALLOW_VIEW_KEYS == 0 ? "asc****************adaca" : $twilio['twilio_account_sid']) : '' ?>" name='twilio_account_sid' id='twilio_account_sid' placeholder='<?= labels('enter_account_sid', 'Enter Account SID') ?>' class="form-control" />
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="twilio_auth_token"><?= labels('auth_token', 'Auth Token') ?></label>
                                                <input type="text" value="<?= isset($twilio['twilio_auth_token']) ? (ALLOW_VIEW_KEYS == 0 ? "asc****************adaca" : $twilio['twilio_auth_token']) : '' ?>" name='twilio_auth_token' id='twilio_auth_token' placeholder='<?= labels('enter_auth_token', 'Enter Auth Token') ?>' class="form-control" />
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="twilio_from"><?= labels('from', 'From') ?></label>
                                                <input type="text" value="<?= isset($twilio['twilio_from']) ? (ALLOW_VIEW_KEYS == 0 ? "asc****************adaca" : $twilio['twilio_from']) : '' ?>" name='twilio_from' id='twilio_from' placeholder='<?= labels('enter_from', 'Enter From') ?>' class="form-control" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- <div class="col-md-6">
                            <div class="card px-3">
                                <div class="row border_bottom_for_cards mb-3">
                                    <div class="col ">
                                        <div class='toggleButttonPostition'><?= labels('vonage', 'Vonage') ?></div>
                                    </div>

                                    <div class="col d-flex justify-content-end mt-4">
                                        <div class="custom-control custom-switch">
                                            <php
                                            $vonage_status = (isset($vonage['vonage_status'])&& $vonage['vonage_status']=="1") ? 1 : 0;
                                            ?>

                                            <input id="vonage_status" class="custom-control-input toggle-switch" type="checkbox" name="vonage_status" <=($vonage_status)==1 ?'checked':''?>>
                                            <label for="vonage_status" class="custom-control-label">Vonage Status</label>
                                        </div>
                                    </div>

                                </div>
                                <div class="card-body p-0">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">

                                                <label for="vonage_api_key"><= labels('api_key', 'API KEY') ?></label>
                                                <div class="input-group">
                                                    <input type="text" value="<= isset($vonage['vonage_api_key']) ? (ALLOW_VIEW_KEYS == 0 ? "asc****************adaca" : $vonage['vonage_api_key']) : '' ?>" name='vonage_api_key' id='vonage_api_key' class="form-control" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="vonage_api_secret"><= labels('api_secret', 'API SECRET') ?></label>
                                                <div class="input-group">
                                                    <input type="text" value="<= isset($vonage['vonage_api_secret']) ? (ALLOW_VIEW_KEYS == 0 ? "asc****************adaca" : $vonage['vonage_api_secret']) : '' ?>" name='vonage_api_secret' id='vonage_api_secret' class="form-control" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> -->

                    </div>
                    <div class="row">
                        <?php if ($permissions['update']['settings'] == 1) : ?>
                            <div class="col-md d-flex justify-content-lg-end m-1">
                                <div class="form-group">
                                    <input type='submit' name='update' id='update' value='<?= labels('save_changes', "Save Changes") ?>' class='btn btn-primary' />
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            <div class="tab-pane fade show " id="sms_template" role="tabpanel">
                <div class="card">
                    <div class="col mb-3" style="border-bottom: solid 1px #e5e6e9;">
                        <div class="toggleButttonPostition"><?= labels('sms_templates', "SMS Templates") ?></div>
                    </div>
                    <div class="card-body">

                        <?php if ($permissions['read']['settings'] == 1) : ?>
                            <div class="col-md-12">
                                <table class="table " data-fixed-columns="true" id="user_list" data-pagination-successively-size="2" data-detail-formatter="user_formater" data-auto-refresh="true" data-toggle="table" data-url="<?= base_url("admin/settings/sms-templates-list") ?>" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-search="false" data-show-columns="false" data-show-columns-search="true" data-show-refresh="false" data-sort-name="id" data-sort-order="desc" data-query-params="sms_query_params">
                                    <thead>
                                        <tr>
                                            <th data-field="id" class="text-center" data-visible="true" data-sortable="true"><?= labels('id', 'ID') ?></th>
                                            <th data-field="title" class="text-center"><?= labels('title', 'Title') ?></th>
                                            <th data-field="type" class="text-center"><?= labels('type', 'Type') ?></th>
                                            <th data-field="truncatedtemplate" class="text-center" data-visible="true"><?= labels('template', 'template') ?></th>
                                            <th data-field="parameters" class="text-center" data-visible="true"><?= labels('parameters', 'Parameters') ?></th>
                                            <th data-field="operations" class="text-center" data-events="sms_gateway_events"><?= labels('operations', 'Operations') ?></th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<script>
    $('.parameters .btn').click(function() {
        let variableName = $(this).data('variable');
        let formattedText = `[[${variableName}]]`;
        let textarea = document.getElementById('template');
        if (textarea.selectionStart || textarea.selectionStart === 0) {
            // For modern browsers
            let startPos = textarea.selectionStart;
            let endPos = textarea.selectionEnd;
            let scrollTop = textarea.scrollTop;
            textarea.value = textarea.value.substring(0, startPos) + formattedText + textarea.value.substring(endPos, textarea.value.length);
            textarea.focus();
            textarea.selectionStart = startPos + formattedText.length;
            textarea.selectionEnd = startPos + formattedText.length;
            textarea.scrollTop = scrollTop;
        } else {
            // For IE < 9
            textarea.value += formattedText;
            textarea.focus();
        }
    });
    document.addEventListener('DOMContentLoaded', function() {
        const smsData = JSON.parse(document.getElementById('sms_gateway_data').value || '{}');

        function createInputRow(containerId, keyName, valueName, keyPlaceholder, valuePlaceholder, removeClass, key = '', value = '') {
            const container = document.getElementById(containerId);
            const row = document.createElement('div');
            row.classList.add('form-group', 'row');
            row.innerHTML = `
            <div class="col-md-5">
                <input type="text" name="${keyName}" class="form-control" placeholder="${keyPlaceholder}" value="${key}">
            </div>
            <div class="col-md-5">
                <input type="text" name="${valueName}" class="form-control" placeholder="${valuePlaceholder}" value="${value}">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger ${removeClass}"><i class="fas fa-minus-circle"></i></button>
            </div>
        `;
            container.appendChild(row);
        }
        document.querySelector('.add-header').addEventListener('click', function() {
            createInputRow('header-container', 'header_key[]', 'header_value[]', 'Enter Key', 'Enter Value', 'remove-header');
        });
        document.querySelector('.add-body').addEventListener('click', function() {
            createInputRow('body-container', 'body_key[]', 'body_value[]', 'Enter Key', 'Enter Value', 'remove-body');
        });
        document.querySelector('.add-param').addEventListener('click', function() {
            createInputRow('param-container', 'params_key[]', 'params_value[]', 'Enter Key', 'Enter Value', 'remove-param');
        });

        function loadSmsHeaderSection() {
            if (smsData.header_key && smsData.header_value) {
                for (let i = 0; i < smsData.header_key.length; i++) {
                    createInputRow('header-container', 'header_key[]', 'header_value[]', 'Enter Key', 'Enter Value', 'remove-header', smsData.header_key[i], smsData.header_value[i]);
                }
            }
            if (smsData.body_key && smsData.body_value) {
                for (let i = 0; i < smsData.body_key.length; i++) {
                    createInputRow('body-container', 'body_key[]', 'body_value[]', 'Enter Key', 'Enter Value', 'remove-body', smsData.body_key[i], smsData.body_value[i]);
                }
            }
            if (smsData.params_key && smsData.params_value) {
                for (let i = 0; i < smsData.params_key.length; i++) {
                    createInputRow('param-container', 'params_key[]', 'params_value[]', 'Enter Key', 'Enter Value', 'remove-param', smsData.params_key[i], smsData.params_value[i]);
                }
            }
        }
        document.addEventListener('click', function(event) {
            if (event.target.closest('.remove-header')) {
                event.target.closest('.row').remove();
            }
            if (event.target.closest('.remove-body')) {
                event.target.closest('.row').remove();
            }
            if (event.target.closest('.remove-param')) {
                event.target.closest('.row').remove();
            }
        });
        window.createHeader = function() {
            const accountSID = document.getElementById('twilio_account_sid').value;
            const authToken = document.getElementById('twilio_auth_token').value;
            const base64Encoded = btoa(`${accountSID}:${authToken}`);
            document.getElementById('basicToken').innerText = `Authorization: Basic ${base64Encoded}`;
        };
        loadSmsHeaderSection();
    });
    $('.provider_registration_request,.withdraw_request,.payment_settlement,.service_request,.user_account,.booking_status,.new_booking,.rating_module').hide();
    $('#type').change(function() {
        let email_type = this.value;
        if (email_type == "provider_approved" || email_type == "provider_disapproved" || email_type == "provider_update_information" || email_type == "new_provider_registerd") {
            $('.provider_registration_request').show();
        } else {
            $('.provider_registration_request').hide();
        }
        if (email_type == "withdraw_request_approved" || email_type == "withdraw_request_disapproved" || email_type == "withdraw_request_received" || email_type == "withdraw_request_send") {
            $('.withdraw_request').show();
        } else {
            $('.withdraw_request').hide();
        }
        if (email_type == "payment_settlement") {
            $('.payment_settlement').show();
        } else {
            $('.payment_settlement').hide();
        }
        if (email_type == "service_approved" || email_type == "service_disapproved") {
            $('.service_request').show();
        } else {
            $('.service_request').hide();
        }
        if (email_type == "user_account_active" || email_type == "user_account_deactive") {
            $('.user_account').show();
        } else {
            $('.user_account').hide();
        }
        if (email_type == "booking_status_updated") {
            $('.booking_status').show();
        } else {
            $('.booking_status').hide();
        }
        if (email_type == "new_booking_confirmation_to_customer" || email_type == "new_booking_received_for_provider") {
            $('.new_booking').show();
        } else {
            $('.new_booking').hide();
        }
        if (email_type == "new_rating_given_by_customer" || email_type == "rating_request_to_customer") {
            $('.rating_module').show();
        } else {
            $('.rating_module').hide();
        }
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const switches = document.querySelectorAll('.toggle-switch');

        switches.forEach(switchInput => {
            // Set initial value based on checked status
            switchInput.value = switchInput.checked ? 'true' : 'false';

            // Add event listener to update value dynamically
            switchInput.addEventListener('change', function() {
                // Update the value of the current switch
                this.value = this.checked ? 'true' : 'false';

                // Uncheck other switches and update their values
                if (this.checked) {
                    switches.forEach(input => {
                        if (input !== this) {
                            input.checked = false;
                            input.value = 'false';
                        }
                    });
                }
            });
        });
    });
</script>