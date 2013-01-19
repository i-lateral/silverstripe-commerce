<% if $Cart.Items %>
<form class="commerce-cart-form" $FormAttributes>
    <fieldset class="commerce-cart-items">
        $Fields.FieldByName(SecurityID)
        <table border="0" cellpadding="0" cellspacing="0">
            <tr>
                <th class="image"></th>
                <th class="description"><% _t('Commerce.CARTPRODUCTDESCRIPTION','Product Description') %></th>
                <th class="quantity"><% _t('Commerce.CARTQTY','Qty') %></th>
                <th class="price"><% _t('Commerce.CARTCOST','Item Cost') %></th>
                <th class="actions"></th>
            </tr>
            
            $CartItems
            
            <tr class="subtotal">
                <th colspan="3"><strong><% _t('Commerce.CARTSUBTOTAL','Subtotal') %></strong></th>
                <td colspan="2">{$Top.CurrencySymbol}$Cart.TotalPrice</td>
            </tr>
        </table>
    </fieldset>
    
    <fieldset class="commerce-cart-actions Actions">
        $Actions.FieldByName(action_doEmpty)
        $Actions.FieldByName(action_doUpdate)
    </fieldset>
    
    <fieldset class="commerce-cart-postage">
        $Fields.FieldByName(PostageHeading)
        <div class="Field postage">
            <label class="left" for="{$FormName}_Postage"><% _t('Commerce.CARTLOCATION','Please choose the location to post to') %></label>
            $Fields.FieldByName(Postage)
            <% if PostageCost %><label class="right" for="{$FormName}_Postage"><strong>{$Top.CurrencySymbol}{$PostageCost}</strong></label><% end_if %>
        </div>
        
        <p class="commerce-cart-total">
            <strong><% _t('Commerce.CARTTOTAL','Total') %></strong>
            {$Top.CurrencySymbol}{$CartTotal}
        </p>
    </fieldset>
    
    <fieldset class="commerce-cart-payment">
        $Fields.FieldByName(PaymentHeading)
        <div class="Field payment">
            <label for="{$FormName}_PaymentMethod"><% _t('Commerce.PAYMENTSELECTION', 'Please choose how you would like to pay') %></label>
            $Fields.FieldByName(PaymentMethod)
        </div>
    </fieldset>
    
    <fieldset class="commerce-cart-actions Actions">
        $Actions.FieldByName(action_doCheckout)
    </fieldset>
</form>

<% else %>
    <p><strong><% _t('Commerce.CARTISEMPTY','Your cart is currently empty') %></strong></p>
<% end_if %>
