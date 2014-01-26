<div class="commerce-content-container typography">
    <h1><% _t('Commerce.SUMMARY','Summary') %></h1>
    <p><% _t('Commerce.SUMMARYCOPY','Please review your personal information before proceeding and entering your payment details') %>.</p>

    <% with $Order %>
        <div class="commerce-summary units-row">
            <div class="unit-50">
                <h2><% _t('Commerce.BILLINGDETAILS','Billing Details') %></h2>
                <p>
                    <strong><% _t('Commerce.FULLNAME','Name') %>:</strong> $FirstName $Surname<br/>
                    <strong><% _t('Commerce.EMAIL','Email') %>:</strong> $Email<br/>
                    <strong><% _t('Commerce.PHONE','Phone Number') %>:</strong> $PhoneNumber<br/>
                    <strong><% _t('Commerce.ADDRESS','Address') %>:</strong><br/>
                    $Address1<br/>
                    $Address2<br/>
                    $City<br/>
                    <strong><% _t('Commerce.POSTCODE','Post Code') %>:</strong> $PostCode<br/>
                    <strong><% _t('Commerce.COUNTRY','Country') %>:</strong> $Country
                </p>
            </div>

            <div class="unit-50">
                <h2><% _t('Commerce.DELIVERYDETAILS','Delivery Details') %></h2>
                <p>
                    <strong><% _t('Commerce.FULLNAME','Name') %>:</strong> $DeliveryFirstnames $DeliverySurname<br/>
                    <strong><% _t('Commerce.ADDRESS','Address') %></strong><br/>
                    $DeliveryAddress1<br/>
                    $DeliveryAddress2<br/>
                    $DeliveryCity<br/>
                    <strong><% _t('Commerce.POSTCODE','Post Code') %>:</strong> $DeliveryPostCode<br/>
                    <strong><% _t('Commerce.COUNTRY','Country') %>:</strong> $DeliveryCountry
                </p>
                <p>
                    <strong><%  _t('Commerce.POSTAGE', 'Postage') %>:</strong>
                    $PostageType $PostageCost.Nice
                </p>
            </div>
        </div>
    <% end_with %>
    $GatewayForm
</div>
