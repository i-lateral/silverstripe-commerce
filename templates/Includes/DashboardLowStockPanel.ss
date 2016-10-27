<div class="dashboard-commerce-panel dashboard-top-products">
    <table>
        <thead>
            <tr>
                <th><%t Commerce.StockID "StockID" %></th>
                <th><%t Commerce.Title "Title" %></th>
                <th><%t Commerce.QTY "QTY" %></th>
            </tr>
        </thead>
        <tbody>
            <% loop Products %>
                <tr>
                    <td>$StockID</td>
                    <td>$Title</td>
                    <td>$StockLevel</td>
                <tr>
            <% end_loop %>
        </tbody>
    </table>

    <p><a class="ss-ui-button ss-ui-button-block" href="{$CatalogueLink}">
        <%t Commerce.ViewAll "View All" %>
    </a></p>
</div>