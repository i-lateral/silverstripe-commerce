<% loop $Items %>
    <tr>
        <td>
            <strong>$Title</strong><br/>
            $Description.FirstParagraph
        </td>
        <td class="quantity"><input name="Quantity_{$ID}" value="{$Quantity}" /></td>
        <td class="total">{$Top.CurrencySymbol}{$Price}</td>
        <td class="remove"><a href="{$Top.Link}/remove/{$ID}"><img src="commerce/images/delete_medium.png" alt="remove" /></a></td>
    </tr>
<% end_loop %>
