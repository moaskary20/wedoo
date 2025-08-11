<div class="main-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('send_withdrawal_request', 'Send Withdrawal Request') ?></h1>
            <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="<?= base_url('partner/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item"><a href="<?= base_url('partner/withdrawal_requests') ?>"><?= labels('withdrawal_request', 'Withdrawal Request') ?></a></div>
            </div>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <form method="post" action="<?= base_url('partner/withdrawal_requests/save') ?>" id="withdrawal_request_form">
                                <div class="row>">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-primary"><?= labels('balance', 'Balance') ?></label>
                                            <p class="text-primary"> <strong><?= $currency . number_format($balance) ?></strong></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="payment_address" class="required"><?= labels('bank_details', 'Bank Details') ?></label>
                                            <textarea style="min-height:60px" class="form-control" aria-rowspan="10" name="payment_address" id="payment_address" rows="10" placeholder="Ex. BOB , Acc no 05454545454"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 ">
                                        <div class="form-group">
                                            <label for="amount" class="required"><?= labels('amount', 'Amount') ?></label>
                                            <input type="text" class="form-control" id="amount" name="amount" placeholder="100.00">
                                            <input type="hidden" class="form-control" id="user_id" name="user_id" value="<?= !empty($partnerId) ? $partnerId : "" ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md d-flex justify-content-end">
                                        <button class="btn btn-primary" type="submit"><?= labels('send_withdrawal_request', 'Send Request') ?></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>
</section>
</div>