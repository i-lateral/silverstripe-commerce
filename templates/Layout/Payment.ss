<div class="commerce-content-container typography">
    <h1><% _t('Commerce.SUMMARY','Summary') %></h1>
    <p><% _t('Commerce.SUMMARYCOPY','Please review your personal information before proceeding and entering your payment details') %>.</p>

    <div class="commerce-summary">
        <% with $Order %>
            <div class="commerce-summary-billing">
                <h2><% _t('Commerce.BILLINGDETAILS','Billing Details') %></h2>
                <p>
                    <strong><% _t('Commerce.FULLNAME','Name') %>:</strong> $BillingFirstnames $BillingSurname<br/>
                    <strong><% _t('Commerce.EMAIL','Email') %>:</strong> $BillingEmail<br/>
                    <strong><% _t('Commerce.PHONE','Phone Number') %>:</strong> $BillingPhone<br/>
                    <strong><% _t('Commerce.ADDRESS','Address') %>:</strong><br/>
                    $BillingAddress1<br/>
                    $BillingAddress2<br/>
                    $BillingCity<br/>
                    <strong><% _t('Commerce.POSTCODE','Post Code') %>:</strong> $BillingPostCode<br/>
                    <strong><% _t('Commerce.COUNTRY','Country') %>:</strong> $NiceBillingCountry
                </p>
            </div>

            <div class="commerce-summary-delivery">
                <h2><% _t('Commerce.DELIVERYDETAILS','Delivery Details') %></h2>
                <p>
                    <strong><% _t('Commerce.FULLNAME','Name') %>:</strong> $DeliveryFirstnames $DeliverySurname<br/>
                    <strong><% _t('Commerce.ADDRESS','Address') %></strong><br/>
                    $DeliveryAddress1<br/>
                    $DeliveryAddress2<br/>
                    $DeliveryCity<br/>
                    <strong><% _t('Commerce.POSTCODE','Post Code') %>:</strong> $DeliveryPostCode<br/>
                    <strong><% _t('Commerce.COUNTRY','Country') %>:</strong> $NiceDeliveryCountry
                </p>
            </div>
        <% end_with %>
    </div>

    <div class="commerce-clear"></div>

    $GatewayForm
</div>
