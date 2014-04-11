<% if $Items.exists %>
    <form $FormAttributes>
        <fieldset class="commerce-cart-items">
            $Fields.dataFieldByName(SecurityID)

            <table>
                <tr>
                    <th class="image"></th>
                    <th class="description">
                        <% _t('Commerce.CARTPRODUCTDESCRIPTION','Product Description') %>
                    </th>
                    <th class="quantity">
                        <% _t('Commerce.CARTQTY','Qty') %>
                    </th>
                    <th class="price">
                        <% _t('Commerce.CARTCOST','Item Cost') %>
                    </th>
                    <th class="actions"></th>
                </tr>

                <% loop $Items %>
                    <tr>
                        <td>
                            <% if $Image %>$Image.CroppedImage(75,75)<% end_if %>
                        </td>
                        <td>
                            <strong>$Title</strong><br/>
                            <% if $Description %>$Description.Summary(10)<br/><% end_if %>
                            <% if $Customised %><div class="small">
                                <% loop $Customised %><div class="{$ClassName}">
                                    <strong>{$Title}:</strong> {$Value}
                                    <% if not $Last %></br><% end_if %>
                                </div><% end_loop %>
                            </div><% end_if %>
                        </td>
                        <td class="quantity">
                            <input type="text" name="Quantity_{$Key}" value="{$Quantity}" />
                        </td>
                        <td class="total">
                            {$Top.CurrencySymbol}{$Price}
                        </td>
                        <td class="remove">
                            <a href="{$Top.Controller.Link('remove')}/{$Key}">
                                <img src="commerce/images/delete_medium.png" alt="remove" />
                            </a>
                        </td>
                    </tr>
                <% end_loop %>

                <% if $Controller.SiteConfig.TaxRate > 0 %>
                    <tr class="subtotal">
                        <td class="right" colspan="3">
                            <strong>
                                <% _t('Commerce.SubTotal','Sub Total') %>
                            </strong>
                        </td>
                        <td colspan="2">
                            {$Top.CurrencySymbol}$Cart.SubTotalPrice
                        </td>
                    </tr>

                    <tr class="subtotal">
                        <td class="right" colspan="3">
                            <strong>
                                <% if $Controller.SiteConfig.TaxName %>{$Controller.SiteConfig.TaxName}
                                <% else %><% _t('Commerce.Tax','Tax') %><% end_if %>
                            </strong>
                        </td>
                        <td colspan="2">
                            {$CurrencySymbol}$Cart.TaxTotalPrice
                        </td>
                    </tr>
                <% end_if %>

                <tr class="subtotal">
                    <td class="right" colspan="3">
                        <strong>
                            <% _t('Commerce.Total','Total') %>
                        </strong>
                    </td>
                    <td colspan="2">
                        <strong>
                            {$Top.CurrencySymbol}$Cart.TotalPrice
                        </strong>
                    </td>
                </tr>
            </table>
        </fieldset>

        <fieldset class="commerce-cart-actions Actions">
            $Actions.dataFieldByName(action_doEmpty)
            $Actions.dataFieldByName(action_doUpdate)
        </fieldset>

        <fieldset class="commerce-cart-postage">
            $Fields.FieldByName(PostageHeading)

            <div class="Field postage">
                <% if $Fields.dataFieldByName(Postage).Message %><div class="message $Fields.dataFieldByName(Postage).MessageType">$Fields.dataFieldByName(Postage).Message</div><% end_if %>
                <label class="left" for="{$FormName}_Postage"><% _t('Commerce.CARTLOCATION','Please choose the location to post to') %></label>
                $Fields.dataFieldByName(Postage)
                <% if PostageCost %><label class="right" for="{$FormName}_Postage"><strong>{$Top.CurrencySymbol}{$PostageCost}</strong></label><% end_if %>
            </div>

            <p class="commerce-cart-total">
                <strong class="uppercase bold"><% _t('Commerce.CARTTOTAL','Total') %></strong>
                {$Top.CurrencySymbol}{$CartTotal}
            </p>
        </fieldset>

        <fieldset class="commerce-cart-payment">
            $Fields.FieldByName(PaymentHeading)

            <div class="Field payment">
                <% if $Fields.dataFieldByName(PaymentMethod).Message %><div class="message $Fields.dataFieldByName(PaymentMethod).MessageType">$Fields.dataFieldByName(PaymentMethod).Message</div><% end_if %>
                <label for="{$FormName}_PaymentMethod"><% _t('Commerce.PAYMENTSELECTION', 'Please choose how you would like to pay') %></label>
                $Fields.dataFieldByName(PaymentMethod)
            </div>
        </fieldset>

        <fieldset class="commerce-cart-actions Actions">
            $Actions.dataFieldByName(action_doCheckout)
        </fieldset>
    </form>

<% else %>
    <p><strong><% _t('Commerce.CARTISEMPTY','Your cart is currently empty') %></strong></p>
<% end_if %>
