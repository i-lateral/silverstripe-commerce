<% if $Cart.Items %>
	<form $FormAttributes>
		<fieldset class="commerce-cart-items">
			$Fields.dataFieldByName(SecurityID)
			
			<table border="0" cellpadding="0" cellspacing="0">
				<tr>
					<th class="image"></th>
					<th class="description"><% _t('Commerce.CARTPRODUCTDESCRIPTION','Product Description') %></th>
					<th class="quantity"><% _t('Commerce.CARTQTY','Qty') %></th>
					<th class="price"><% _t('Commerce.CARTCOST','Item Cost') %></th>
					<th class="actions"></th>
				</tr>
				
				<% loop $Items %>
					<tr>
						<td><% if $Image %>$Image.CroppedImage(75,75)<% end_if %></td>
						<td>
							<strong>$Title</strong><br/>
							<% if $Description %>$Description.Summary(10)<br/><% end_if %>
							<% if $Customised %><span class="small">
								<% loop $Customised %>
									<strong>{$Title}:</strong> {$Value}
									<% if not $Last %></br><% end_if %>
								<% end_loop %>
							</span><% end_if %>
						</td>
						<td class="quantity"><input type="text" name="Quantity_{$Key}" value="{$Quantity}" /></td>
						<td class="total">{$Top.CurrencySymbol}{$Price}</td>
						<td class="remove"><a href="{$Top.Link}/remove/{$Key}"><img src="commerce/images/delete_medium.png" alt="remove" /></a></td>
					</tr>
				<% end_loop %> 
				
				<tr class="subtotal">
					<td class="right" colspan="3"><strong><% _t('Commerce.CARTSUBTOTAL','Subtotal') %></strong></td>
					<td colspan="2">{$Top.CurrencySymbol}$Cart.TotalPrice</td>
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
			$Fields.dataFieldByName(PaymentHeading)
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
