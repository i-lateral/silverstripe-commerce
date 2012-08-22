<% if Cart %>
<form $FormAttributes>
    <fieldset>
        $dataFieldByName(SecurityID)
        <table border="0" class="cart" cellpadding="0" cellspacing="0">
            <tr>
                <th colspan="2"></th>
                <th class="quantity"><% _t('Commerce.CARTQTY','Qty') %></th>
                <th class="total"><% _t('Commerce.CARTCOST','Cost') %></th>
            </tr>
            <% control Cart %><tr>
                <td><div class="tag tagOne">
                    <pre style="background-image: url(<% control Silencer.Preview %>$CroppedImage(191,110).url<% end_control %>)">$TagOne</pre>
                </div></td>
                <td><% if TagTwo %><div class="tag tagTwo">
                    <pre style="background-image: url(<% control Silencer.Preview %>$CroppedImage(191,110).url<% end_control %>)">$TagTwo</pre>
                </div><% end_if %></td>
                <td class="quantity"><input name="Quantity_{$ID}" value="$Quantity" /></td>
                <td class="total">{$Top.CurrencySymbol}{$Total.RAW}</td>
            </tr><% end_control %>
            
            <tr class="postage">
                <td colspan="2"><label for="{$FormName}_Postage"><% _t('Commerce.CARTLOCATION','Please choose the location to post to') %></label></td>
                <td>$dataFieldByName(Postage)</td>
                <td>
                    <% if PostageCost %>{$Top.CurrencySymbol}{$PostageCost}<% end_if %>
                </td>
            </tr>
            
            <tr class="total">
                <td colspan="2"></td>
                <td><strong><% _t('Commerce.CARTTOTAL','Total') %></strong></td>
                <td>{$Top.CurrencySymbol}$CartTotal</td>
            </tr>
            
        </table>
    </fieldset>
    
    <% if Actions %>
      <fieldset class="Actions">
         <input class="action button" id="CartForm_CartForm_action_doEmpty" type="submit" name="action_doEmpty" value="<% _t('Commerce.CARTEMPTY','Empty Cart') %>" title="<% _t('Commerce.CARTEMPTY','Empty Cart') %>" />
         <input class="action button" id="CartForm_CartForm_action_doUpdate" type="submit" name="action_doUpdate" value="<% _t('Commerce.CARTUPDATE','Update Cart') %>" title="<% _t('Commerce.CARTUPDATE','Update Cart') %>" />
         <input class="action highlight" id="CartForm_CartForm_action_doCheckout" type="submit" name="action_doCheckout" value="<% _t('Commerce.CARTPROCEED','Proceed to Checkout') %>" title="<% _t('Commerce.CARTPROCEED','Proceed to Checkout') %>" /> 
      </fieldset>
   <% end_if %>
</form>

<% else %>
    <p><strong><% _t('Commerce.CARTISEMPTY','Your cart is currently empty') %></strong></p>
<% end_if %>