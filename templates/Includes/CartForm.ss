<% if $Cart %>
<form $FormAttributes>
    <fieldset>
        $Fields.FieldByName(SecurityID)
        <table border="0" class="cart" cellpadding="0" cellspacing="0">
            <tr>
                <th></th>
                <th class="description"><% _t('Commerce.CARTPRODUCTDESCRIPTION','Product Description') %></th>
                <th class="quantity"><% _t('Commerce.CARTQTY','Qty') %></th>
                <th class="price"><% _t('Commerce.CARTCOST','Cost') %></th>
                <th></th>
            </tr>
            
            <% loop $Cart %><tr>
                <td></td>
                <td>
                    <strong>$Product.Title</strong><br/>
                    $Product.Description.FirstParagraph
                </td>
                <td class="quantity"><input name="Quantity_{$Product.ID}" value="{$Quantity}" /></td>
                <td class="total">{$Top.CurrencySymbol}{$Product.Price}</td>
                <td class="remove"><a href="{$Top.Link}/remove/{$Product.ID}"><img src="commerce/images/delete_large.png" alt="remove" /></a></td>
            </tr><% end_loop %>
            
            <tr class="subtotal">
                <td colspan="2"></td>
                <td><strong><% _t('Commerce.CARTSUBTOTAL','Subtotal') %></strong></td>
                <td colspan="2">{$Top.CurrencySymbol}$CartSubTotal</td>
            </tr>
            
        </table>
    </fieldset>
    
    <fieldset class="commerce-cart-postage">
        <h2>Postage</h2>
        <div class="Field postage">
            <label class="left" for="{$FormName}_Postage"><% _t('Commerce.CARTLOCATION','Please choose the location to post to') %></label>
            $Fields.FieldByName(Postage)
            <% if PostageCost %><label for="{$FormName}_Postage">{$Top.CurrencySymbol}{$PostageCost}</label><% end_if %>
        </div>
        
        <div class="commerce-cart-total">
            <strong><% _t('Commerce.CARTTOTAL','Total') %></strong>
            {$Top.CurrencySymbol}{$CartTotal}
        </div>
    </fieldset>
    
    <fieldset class="commerce-cart-actions Actions">
        $Actions 
    </fieldset>
</form>

<% else %>
    <p><strong><% _t('Commerce.CARTISEMPTY','Your cart is currently empty') %></strong></p>
<% end_if %>
