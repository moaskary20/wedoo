<?php
$uri = service('uri');
$partner_id = $uri->getSegments()[3];
?>

<ul class="justify-content-start nav nav-fill nav-pills pl-3 py-2 setting" id="gen-list">
    <div class="row">
        <li class="nav-item">


            <a class="nav-link <?=(service('uri')->getSegments()[2]=="general_outlook"?"active":"")?>"   aria-current="page" href="<?= base_url('admin/partners/general_outlook/' . $partner_id) ?>" id="pills-general_settings-tab" aria-selected="true">
                <?= labels('general_outlook', "General Outlook") ?></a>
        </li>
        <li class="nav-item">
            <a class="nav-link  <?=(service('uri')->getSegments()[2]=="partner_company_information"?"active":"")?>" href="<?= base_url('admin/partners/partner_company_information/'. $partner_id) ?>" id="pills-about_us" aria-selected="false">
                <?= labels('company_information', "Company Information") ?></a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?=(service('uri')->getSegments()[2]=="partner_service_details"?"active":"")?>" href="<?= base_url('admin/partners/partner_service_details/'. $partner_id) ?>" id="pills-about_us" aria-selected="false">
                <?= labels('service_list', "Service List") ?></a>
        </li>

        <li class="nav-item">
            <a class="nav-link  <?=(service('uri')->getSegments()[2]=="partner_order_details"?"active":"")?>" href="<?= base_url('admin/partners/partner_order_details/'. $partner_id) ?>" id="pills-about_us" aria-selected="false">
                <?= labels('booking_list', "Booking List") ?></a>
        </li>
        <li class="nav-item">
            <a class="nav-link   <?=(service('uri')->getSegments()[2]=="partner_promocode_details"?"active":"")?>" href="<?= base_url('admin/partners/partner_promocode_details/'. $partner_id) ?>" id="pills-about_us" aria-selected="false">
                <?= labels('offer_promo', "Offer Promo") ?></a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?=(service('uri')->getSegments()[2]=="partner_review_details"?"active":"")?>" href="<?= base_url('admin/partners/partner_review_details/'. $partner_id) ?>" id="pills-about_us" aria-selected="false">
                <?= labels('reviews', "Reviews") ?></a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?=(service('uri')->getSegments()[2]=="partner_subscription"?"active":"")?>" href="<?= base_url('admin/partners/partner_subscription/'. $partner_id) ?>" id="pills-about_us" aria-selected="false">
                <?= labels('subscription', "Subscription") ?></a>
        </li>


        <li class="nav-item">
            <a class="nav-link <?=(service('uri')->getSegments()[2]=="partner_settlement_and_cash_collection_history"?"active":"")?>" href="<?= base_url('admin/partners/partner_settlement_and_cash_collection_history/'. $partner_id) ?>" id="pills-about_us" aria-selected="false">
            <?= labels('booking_payment_management', 'Booking Payment Management') ?></a>
        </li>
    </div>
 

</ul>