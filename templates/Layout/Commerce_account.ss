<div class="units-row">
    <% include Commerce_Profile_SideBar %>

    <div class="commerce-content-container typography commerce-account unit-75">
        <h1>$Title</h1>

        $Content

        <% if $Orders.exists %>
            <table class="width-100 table-hovered">
                <thead>
                    <tr>
                        <th><% _t("Commerce.ORDER","Order") %></th>
                        <th><% _t("Commerce.DATE","Date") %></th>
                        <th><% _t("Commerce.Price","Price") %></th>
                        <th><% _t("Commerce.Status","Status") %></th>
                    </tr>
                </thead>
                <tbody>
                    <% loop $Orders %>
                        <tr>
                            <td><a href="{$Top.Link('order')}/{$ID}">$OrderNumber</a></td>
                            <td><a href="{$Top.Link('order')}/{$ID}">$Created.Nice</a></td>
                            <td><a href="{$Top.Link('order')}/{$ID}">$Total.Nice</a></td>
                            <td><a href="{$Top.Link('order')}/{$ID}">$TranslatedStatus</a></td>
                        </tr>
                    <% end_loop %>
                </tbody>
            </table>
        <% end_if %>

        $Form
    </div>
</div>
