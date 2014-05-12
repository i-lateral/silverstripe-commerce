<form $FormAttributes>
    <fieldset class="commerce-cart-items">
        $Fields.dataFieldByName(SecurityID)

        <table>
            <tr>
                <th class="image"></th>
                <th class="description">
                    <% _t('Commerce.ProductDescription','Product Description') %>
                </th>
                <th class="quantity">
                    <% _t('Commerce.Qty','Qty') %>
                </th>
                <th class="price">
                    <% _t('Commerce.ItemCost','Item Cost') %>
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
                        {$Price.Nice}
                    </td>
                    <td class="remove">
                        <a href="{$Top.Controller.Link('remove')}/{$Key}">
                            <img src="commerce/images/delete_medium.png" alt="remove" />
                        </a>
                    </td>
                </tr>
            <% end_loop %>

            <tr class="subtotal">
                <td class="right" colspan="3">
                    <strong>
                        <% _t('Commerce.SubTotal','Sub Total') %>
                    </strong>
                </td>
                <td colspan="2">
                    {$Controller.SiteConfig.Currency.HTMLNotation.RAW}{$Controller.CommerceCart.SubTotalCost}
                </td>
            </tr>
        </table>
    </fieldset>

    <fieldset class="commerce-cart-actions Actions">
        $Actions.dataFieldByName(action_doEmpty)
        $Actions.dataFieldByName(action_doUpdate)
    </fieldset>
</form>
