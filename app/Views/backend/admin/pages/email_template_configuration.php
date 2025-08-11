<!-- Main Content -->
<div class="main-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('email_configuration', "Email Configuration") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item "><a href="<?= base_url('/admin/settings/system-settings') ?>"><?= labels('system_settings', "System Settings") ?></a></div>
                <div class="breadcrumb-item"><?= labels('email_configuration', "Email Configuration") ?></div>
            </div>
        </div>
        <?= form_open_multipart(base_url('admin/settings/email_template_configuration_update'), array('class' => 'form-submit-event')) ?>
        <div class="row mb-3">
            <div class="col-md-12 col-sm-12 col-xl-12">
                <div class="card h-100">
                    <div class="col mb-3" style="border-bottom: solid 1px #e5e6e9;">
                        <div class="toggleButttonPostition"><?= labels('email_configuration', "Email Configuration") ?></div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><?= labels('type', "Type") ?></label>
                                    <select class="form-control select2" name="email_type" id="email_type">
                                        <option value="provider_approved"><?= labels('provider_approved', "Provider Approved") ?> </option>
                                        <option value="provider_disapproved"> <?= labels('provider_disapproved', "Provider Disapproved") ?></option>
                                        <option value="withdraw_request_approved"><?= labels('approved_withdraw_request', "Approved Withdrawal Request") ?></option>
                                        <option value="withdraw_request_disapproved"><?= labels('disapproved_withdraw_request', "Disapproved Withdrawal Request") ?> </option>
                                        <option value="payment_settlement"><?= labels('payment_settled', "Payment Settled") ?> </option>
                                        <option value="service_approved"> <?= labels('service_approved', "Service Approved") ?></option>
                                        <option value="service_disapproved"><?= labels('service_disapproved', "Service Disapproved") ?> </option>
                                        <option value="user_account_active"><?= labels('user_account_activated', "User Account Activated") ?> </option>
                                        <option value="user_account_deactive"> <?= labels('user_account_deactivated', "User Account Deactivated") ?> </option>
                                        <option value="provider_update_information"> <?= labels('provider_information_updated', "Provider Information Updated") ?> </option>
                                        <option value="new_provider_registerd"> <?= labels('new_provider_registered', "New Provider Registered") ?> </option>
                                        <option value="withdraw_request_received"> <?= labels('withdrawal_request_received', "Withdrawal Request Received") ?> </option>
                                        <option value="booking_status_updated"> <?= labels('booking_status_updated', "Booking Status Updated") ?> </option>
                                        <option value="new_booking_confirmation_to_customer"> <?= labels('new_booking_confirmation_to_customer', "Booking Confirmation for customer") ?> </option>
                                        <option value="new_booking_received_for_provider"> <?= labels('new_booking_received_for_provider', "New Booking Received For provider") ?> </option>
                                        <option value="withdraw_request_send"><?= labels('withdraw_request_by_provider', "Withdraw Request By Provider") ?> </option>
                                        <option value="new_rating_given_by_customer" <?= ($sms_template['type'] == "new_rating_given_by_customer") ? 'selected' : '' ?>><?= labels('new_rating_given_by_customer', "New Rating Given By Customer") ?> </option>
                                        <option value="rating_request_to_customer" <?= ($sms_template['type'] == "rating_request_to_customer") ? 'selected' : '' ?>><?= labels('rating_request_to_customer', "Rating Request to Customer") ?> </option>

                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><?= labels('subject', "Subject") ?></label>
                                    <input class="form-control" type="text" name="subject">
                                </div>
                            </div>
                            <div class="col-md-12 provider_registration_request parameters">
                                <label><?= labels('parameters', "Parameters") ?></label>
                                <div class="form-group">
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="provider_name"><?= labels('provider_name', "Provider name") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="provider_id"><?= labels('provider_id', "Provider ID") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="company_name"><?= labels('company_name', "Company name") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="site_url"><?= labels('site_url', "Site URL") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="company_contact_info"><?= labels('company_contact_info', "Company Contact Info") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="company_logo"><?= labels('company_logo', "Company Logo") ?></button>
                                </div>
                            </div>
                            <div class="col-md-12 withdraw_request parameters">
                                <label><?= labels('parameters', "Parameters") ?></label>
                                <div class="form-group">
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="provider_name"><?= labels('provider_name', "Provider name") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="provider_id"><?= labels('provider_id', "Provider ID") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="company_name"><?= labels('company_name', "Company name") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="site_url"><?= labels('site_url', "Site URL") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="company_contact_info"><?= labels('company_contact_info', "Company Contact Info") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="amount"><?= labels('amount', "Amount") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="currency"><?= labels('currency', "Currency") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="company_logo"><?= labels('company_logo', "Company Logo") ?></button>
                                </div>
                            </div>
                            <div class="col-md-12 payment_settlement parameters">
                                <label><?= labels('parameters', "Parameters") ?></label>
                                <div class="form-group">
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="provider_name"><?= labels('provider_name', "Provider name") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="provider_id"><?= labels('provider_id', "Provider ID") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="company_name"><?= labels('company_name', "Company name") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="site_url"><?= labels('site_url', "Site URL") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="company_contact_info"><?= labels('company_contact_info', "Company Contact Info") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="amount"><?= labels('amount', "Amount") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="currency"><?= labels('currency', "Currency") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="company_logo"><?= labels('company_logo', "Company Logo") ?></button>
                                </div>
                            </div>
                            <div class="col-md-12 service_request parameters">
                                <label><?= labels('parameters', "Parameters") ?></label>
                                <div class="form-group">
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="provider_name"><?= labels('provider_name', "Provider name") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="provider_id"><?= labels('provider_id', "Provider ID") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="company_name"><?= labels('company_name', "Company name") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="site_url"><?= labels('site_url', "Site URL") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="company_contact_info"><?= labels('company_contact_info', "Company Contact Info") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="amount"><?= labels('amount', "Amount") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="currency"><?= labels('currency', "Currency") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="company_logo"><?= labels('company_logo', "Company Logo") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="service_id"><?= labels('service_id', "Service ID") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="service_name"><?= labels('service_name', "Service Name") ?></button>
                                </div>
                            </div>
                            <div class="col-md-12 user_account parameters">
                                <label><?= labels('parameters', "Parameters") ?></label>
                                <div class="form-group">
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="user_id"><?= labels('user_id', "User ID") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="user_name"><?= labels('user_name', "User name") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="company_name"><?= labels('company_name', "Company name") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="site_url"><?= labels('site_url', "Site URL") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="company_contact_info"><?= labels('company_contact_info', "Company Contact Info") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="company_logo"><?= labels('company_logo', "Company Logo") ?></button>
                                </div>
                            </div>
                            <div class="col-md-12 booking_status parameters">
                                <label><?= labels('parameters', "Parameters") ?></label>
                                <div class="form-group">
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="user_id"><?= labels('user_id', "User ID") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="user_name"><?= labels('user_name', "User Name") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="company_name"><?= labels('company_name', "Company name") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="site_url"><?= labels('site_url', "Site URL") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="company_contact_info"><?= labels('company_contact_info', "Company Contact Info") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="company_logo"><?= labels('company_logo', "Company Logo") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="booking_id"><?= labels('booking_id', "Booking ID") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="booking_date"><?= labels('booking_date', "Booking Date") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="booking_time"><?= labels('booking_time', "Booking Time") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="booking_service_names"><?= labels('booking_service_names', "Booking Service names") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="booking_address"><?= labels('booking_address', "Booking Address") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="amount"><?= labels('amount', "Amount") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="currency"><?= labels('currency', "Currency") ?></button>
                                </div>
                            </div>
                            <div class="col-md-12 new_booking parameters">
                                <label><?= labels('parameters', "Parameters") ?></label>
                                <div class="form-group">
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="user_id"><?= labels('user_id', "User ID") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="user_name"><?= labels('user_name', "User Name") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="company_name"><?= labels('company_name', "Company name") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="site_url"><?= labels('site_url', "Site URL") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="company_contact_info"><?= labels('company_contact_info', "Company Contact Info") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="company_logo"><?= labels('company_logo', "Company Logo") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="booking_id"><?= labels('booking_id', "Booking ID") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="booking_date"><?= labels('booking_date', "Booking Date") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="booking_time"><?= labels('booking_time', "Booking Time") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="booking_service_names"><?= labels('booking_service_names', "Booking Service names") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="booking_address"><?= labels('booking_address', "Booking Address") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="amount"><?= labels('amount', "Amount") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="currency"><?= labels('currency', "Currency") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="provider_name"><?= labels('provider_name', "Provider name") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="provider_id"><?= labels('provider_id', "Provider ID") ?></button>
                                </div>
                            </div>
                            <div class="col-md-12 rating_module parameters">
                                <label><?= labels('parameters', "Parameters") ?></label>
                                <div class="form-group">
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="user_id"><?= labels('user_id', "User ID") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="user_name"><?= labels('user_name', "User Name") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="company_name"><?= labels('company_name', "Company name") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="site_url"><?= labels('site_url', "Site URL") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="company_contact_info"><?= labels('company_contact_info', "Company Contact Info") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="company_logo"><?= labels('company_logo', "Company Logo") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="provider_name"><?= labels('provider_name', "Provider name") ?></button>
                                    <button type="button" class="btn btn-primary btn-icon icon-left" data-variable="provider_id"><?= labels('provider_id', "Provider ID") ?></button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label><?= labels('bcc', "BCC") ?></label>
                                <input class="form-control" type="text" id="bcc" name="bcc">
                            </div>
                            <div class="col-md-4">
                                <label><?= labels('cc', "CC") ?></label>
                                <input class="form-control" type="text" id="cc" name="cc">
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="template" class="required"><?= labels('template', 'Template') ?></label>
                                    <textarea rows="10" class="form-control h-50 summernotes custome_reset" name="template"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3 mb-3">
                            <div class="col-md d-flex justify-content-end">
                                <button type="submit" class="btn btn-lg bg-new-primary submit_btn"><?= labels('save_changes', 'Save') ?></button>
                                <?= form_close() ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<script>
    $(document).ready(function() {
        $('.provider_registration_request,.withdraw_request,.payment_settlement,.service_request,.user_account,.booking_status,.new_booking,.rating_module').hide();
        setTimeout(() => {
            $('#email_to').select2();
        });
        $('.parameters .btn').click(function() {
            let variableName = $(this).data('variable');
            let formattedText = `[[${variableName}]]`;
            tinymce.activeEditor.execCommand('mceInsertContent', false, formattedText);
        });
        $('#email_type').change(function() {
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
    });

    if (document.getElementById("bcc") != null) {
        $(document).ready(function() {
            var input = document.querySelector('input[id=bcc]');
            new Tagify(input)
        });
    }

    if (document.getElementById("cc") != null) {
        $(document).ready(function() {
            var input = document.querySelector('input[id=cc]');
            new Tagify(input)
        });
    }
</script>