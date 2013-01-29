<tr>
    <td><% if $Image %>$Image.CroppedImage(75,50)<% end_if %></td>
    <td>
        <strong>$Title</strong><br/>
        <% if $Description %>$Description.Summary(10)<br/><% end_if %>
        <% if $Customised %><strong>Custom Options:</strong> 
            <% loop $Customised %>$Value<% if not $Last %>, <% end_if %><% end_loop %>
        <% end_if %>
    </td>
    <td class="quantity"><input type="text" name="Quantity_{$Key}" value="{$Quantity}" /></td>
    <td class="total">{$CurrencySymbol}{$Price}</td>
    <td class="remove"><a href="{$Top.Link}/remove/{$Key}"><img src="commerce/images/delete_medium.png" alt="remove" /></a></td>
</tr>
