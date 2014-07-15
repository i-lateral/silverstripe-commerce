<div class="units-row users-account line">
    <% include Users_Profile_SideBar %>

    <div class="commerce-content-container typography commerce-account unit-75">
        <h1>$Title</h1>

        $Content

        <% if $Order %><% with $Order %>
            <div class="units-row-end">
                <p class="unit-50">
                    <strong><% _t("Commerce.Date","Date") %>:</strong> $Created.Nice <br/>
                    <strong><% _t("Commerce.Status","Status") %>:</strong> $TranslatedStatus<br/>
                    <strong><% _t("Commerce.FirstNames","First Name(s)") %>:</strong> $FirstName <br/>
                    <strong><% _t("Commerce.Surname","Surname") %>:</strong> $Surname <br/>
                    <strong><% _t("Commerce.Email","Email") %>:</strong> $Email <br/>
                    <strong><% _t("Commerce.Phone","Phone Number") %>:</strong> $PhoneNumber <br/>
                </p>

                <p class="unit-50">
                    <strong><% _t("Commerce.DeliveryDetails","Delivery Details") %></strong><br/>
                    <strong><% _t("Commerce.Address1","Address Line 1") %>:</strong> $DeliveryAddress1 <br/>
                    <strong><% _t("Commerce.Address2","Address Line 2") %>:</strong> $DeliveryAddress1 <br/>
                    <strong><% _t("Commerce.City","City") %>:</strong> $DeliveryCity <br/>
                    <strong><% _t("Commerce.PostCode","Post Code") %>:</strong> $DeliveryPostCode <br/>
                    <strong><% _t("Commerce.Country","Country") %>:</strong> $DeliveryCountry
                </p>
            </div>

            <hr/>

            <% if $Items.exists %>
                <table class="width-100">
                    <thead>
                        <tr>
                            <th class="width-50"><% _t("Commerce.Item","Item") %></th>
                            <th><% _t("Commerce.Qty","Qty") %></th>
                            <th><% _t("Commerce.Price","Price") %></th>
                            <% if $Top.SiteConfig.TaxRate > 0 %>
                                <th class="tax">
                                    <% if $Top.SiteConfig.TaxName %>{$Top.SiteConfig.TaxName}
                                    <% else %><% _t('Commerce.Tax','Tax') %><% end_if %>
                                </th>
                            <% end_if %>
                            <th><% _t("Commerce.Reorder","Reorder") %></th>
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
                                        <% _t("Commerce.AddToCart","Add to cart") %>
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
                                    <% _t("Commerce.Postage","Postage") %>
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
                                    <% _t("Commerce.Postage","Postage") %>
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
                                <% _t("Commerce.Total","Total") %>
                            </td>
                            <td class="text-right">$Total.Nice</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            <% end_if %>

        <% end_with %><% end_if %>
    </div>
</div>
