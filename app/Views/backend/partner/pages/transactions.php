<div class="main-content">
    <section class="section">

        <div class="container-fluid card">
            <h2 class='section-title'><?= labels('transactions', "Transactions") ?></h2>
            <div class="row">
                <div class="col-md">
                    <div class="card-body">
                        <table class="table " id="tts_table" data-pagination-successively-size="2" data-detail-view="true" data-detail-formatter="detailFormatter" data-auto-refresh="true" data-toggle="table" data-url="<?= base_url("partner/transactions/list") ?>" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-search="true" data-show-columns="true" data-show-columns-search="true" data-show-refresh="true" data-sort-name="t.id" data-sort-order="DESC">
                            <thead>
                                <tr>
                                    <th data-field="id"><?= labels('id', 'ID') ?></th>
                                    <th data-field="type"><?= labels('payment_method', 'Payment Method') ?></th>
                                    <th data-field="txn_id"><?= labels('transaction_id', 'Transaction ID') ?></th>
                                    <th data-field="transaction_type"><?= labels('transaction_type', 'Transaction Type') ?></th>
                                    <th data-field="amount" data-sortable="true"><?= labels('amount', "Amount") ?></th>
                                    <th data-field="message" data-visible="false"><?= labels('message', "Message") ?></th>
                                    <th data-field="status"><?= labels('status', "Status") ?></th>
                                    <th data-field="created_at"><?= labels('created_on', 'Created on') ?></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>