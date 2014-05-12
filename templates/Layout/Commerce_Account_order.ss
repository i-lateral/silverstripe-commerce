<% include Users_Profile_SideBar %>

<div class="commerce-content-container typography commerce-account unit-75">
    <h1>$Title</h1>

    $Content

    <% if $Order %><% with $Order %>
        <div class="units-row-end">
            <p class="unit-50">
                <strong><% _t("Commerce.DATE","Date") %>:</strong> $Created.Nice <br/>
                <strong><% _t("Commerce.STATUS","Status") %>:</strong> $TranslatedStatus<br/>
                <strong><% _t("Commerce.FIRSTNAMES","First Name(s)") %>:</strong> $FirstName <br/>
                <strong><% _t("Commerce.SURNAME","Surname") %>:</strong> $Surname <br/>
                <strong><% _t("Commerce.EMAIL","Email") %>:</strong> $Email <br/>
                <strong><% _t("Commerce.PHONE","Phone Number") %>:</strong> $PhoneNumber <br/>
            </p>

            <p class="unit-50">
                <strong><% _t("Commerce.DELIVERYDETAILS","Delivery Details") %></strong><br/>
                <strong><% _t("Commerce.ADDRESS1","Address Line 1") %>:</strong> $DeliveryAddress1 <br/>
                <strong><% _t("Commerce.ADDRESS2","Address Line 2") %>:</strong> $DeliveryAddress1 <br/>
                <strong><% _t("Commerce.CITY","City") %>:</strong> $DeliveryCity <br/>
                <strong><% _t("Commerce.POSTCODE","Post Code") %>:</strong> $DeliveryPostCode <br/>
                <strong><% _t("Commerce.COUNTRY","Country") %>:</strong> $DeliveryCountry
            </p>
        </div>

        <hr/>

        <% if $Items.exists %>
            <table class="width-100">
                <thead>
                    <tr>
                        <th class="width-50"><% _t("Commerce.ITEM","Item") %></th>
                        <th><% _t("Commerce.QTY","Qty") %></th>
                        <th><% _t("Commerce.PRICE","Price") %></th>
                        <% if $Top.SiteConfig.TaxRate > 0 %>
                            <th class="tax">
                                <% if $Top.SiteConfig.TaxName %>{$Top.SiteConfig.TaxName}
                                <% else %><% _t('Commerce.Tax','Tax') %><% end_if %>
                            </th>
                        <% end_if %>
                        <th><% _t("Commerce.REORDER","Reorder") %></th>
                    </tr>
                </thead>
                <tbody>
                    <% loop $Items %>
                        <tr>
                            <td>$Title</td>
                            <td>$Quantity</td>
                            <td>$Price.Nice</td>
                            <% if $Top.SiteConfig.TaxRate > 0 %>
                                <td class="total">
                                    {$TaxTotal.Nice}
                                </td>
                            <% end_if %>
                            <td><% if $MatchProduct %>
                                <a href="$MatchProduct.Link">
                                    <% _t("Commerce.ADDTOCART","Add to cart") %>
                                </a>
                            <% end_if %></td>
                        </tr>
                    <% end_loop %>

                    <tr>
                        <td colspan="<% if $Top.SiteConfig.TaxRate > 0 %>5<% else %>4<% end_if %>">&nbsp;</td>
                    </tr>

                    <% if $Top.SiteConfig.TaxRate > 0 %>
                        <tr>
                            <td colspan="3" class="text-right">
                                <% _t("Commerce.SubTotal","Sub Total") %>
                            </td>
                            <td class="text-right">$SubTotal.Nice</td>
                            <td></td>
                        </tr>

                        <tr>
                            <td colspan="<% if $Top.SiteConfig.TaxRate > 0 %>3<% else %>2<% end_if %>" class="text-right">
                                <% _t("Commerce.POSTAGE","Postage") %>
                            </td>
                            <td class="text-right">$PostageCost.Nice</td>
                            <td></td>
                        </tr>

                        <tr>
                            <td colspan="3" class="text-right">
                                <% if $Top.SiteConfig.TaxName %>
                                    {$Top.SiteConfig.TaxName}
                                <% else %>
                                    <% _t('Commerce.Tax','Tax') %>
                                <% end_if %>
                            </td>
                            <td class="text-right">$TaxTotal.Nice</td>
                            <td></td>
                        </tr>
                    <% else %>
                        <tr>
                            <td colspan="<% if $Top.SiteConfig.TaxRate > 0 %>3<% else %>2<% end_if %>" class="text-right">
                                <% _t("Commerce.POSTAGE","Postage") %>
                            </td>
                            <td class="text-right">$PostageCost.Nice</td>
                            <td></td>
                        </tr>
                    <% end_if %>

                    <tr>
                        <td colspan="<% if $Top.SiteConfig.TaxRate > 0 %>5<% else %>4<% end_if %>">&nbsp;</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="<% if $Top.SiteConfig.TaxRate > 0 %>3<% else %>2<% end_if %>" class="text-right bold">
                            <% _t("Commerce.TOTAL","Total") %>
                        </td>
                        <td class="text-right">$Total.Nice</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        <% end_if %>

    <% end_with %><% end_if %>
</div>
