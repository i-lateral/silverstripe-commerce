<div class="commerce-content-container typography">
    <h1><% _t('Commerce.Summary','Summary') %></h1>
    <p><% _t('Commerce.SummaryCopy','Please review your personal information before proceeding and entering your payment details') %>.</p>

    <% with $Order %>
        <div class="commerce-summary">
            <h2><% _t('Commerce.Order','Order') %></h2>
            <p>
                <strong><%  _t('Commerce.SubTotal', 'Sub Total') %>:</strong>
                $SubTotal.Nice
                <br/>

                <strong><%  _t('Commerce.Postage', 'Postage') %>:</strong>
                $PostageType $PostageCost.Nice
                <br/>

                <% if $Top.SiteConfig.TaxRate > 0 %>
                    <strong><%  _t('Commerce.Tax', 'Tax') %>:</strong>
                    $TaxTotal.Nice
                    <br/>
                <% end_if %>

                <strong><%  _t('Commerce.Total', 'Total') %>:</strong>
                $Total.Nice
            </p>
        </div>

        <hr/>

        <div class="commerce-summary units-row">
            <div class="unit-50">
                <h2><% _t('Commerce.BillingDetails','Billing Details') %></h2>
                <p>
                    <strong><% _t('Commerce.Name','Name') %>:</strong> $FirstName $Surname<br/>
                    <strong><% _t('Commerce.Email','Email') %>:</strong> $Email<br/>
                    <strong><% _t('Commerce.Company','Company') %>:</strong> $Company<br/>
                    <strong><% _t('Commerce.Phone','Phone Number') %>:</strong> $PhoneNumber<br/>
                    <strong><% _t('Commerce.Address','Address') %>:</strong><br/>
                    $Address1<br/>
                    $Address2<br/>
                    $City<br/>
                    <strong><% _t('Commerce.PostCode','Post Code') %>:</strong> $PostCode<br/>
                    <strong><% _t('Commerce.Country','Country') %>:</strong> $Country
                </p>
            </div>

            <div class="unit-50">
                <h2><% _t('Commerce.DeliveryDetails','Delivery Details') %></h2>
                <p>
                    <strong><% _t('Commerce.Name','Name') %>:</strong> $DeliveryFirstnames $DeliverySurname<br/>
                    <strong><% _t('Commerce.Address','Address') %></strong><br/>
                    $DeliveryAddress1<br/>
                    $DeliveryAddress2<br/>
                    $DeliveryCity<br/>
                    <strong><% _t('Commerce.PostCode','Post Code') %>:</strong> $DeliveryPostCode<br/>
                    <strong><% _t('Commerce.Country','Country') %>:</strong> $DeliveryCountry
                </p>
            </div>
        </div>
    <% end_with %>

    <% if $PaymentInfo %>
        <hr/>
        $PaymentInfo
    <% end_if %>

    $Form
</div>
