<div class="dashboard-commerce-panel dashboard-new-customers">
    <table>
        <thead>
            <tr>
                <th><%t Commerce.Name "Name" %></th>
                <th><%t Commerce.Joined "Joined" %></th>
            </tr>
        </thead>
        <tbody>
            <% loop Customers %>
                <tr>
                    <td>$FirstName $Surname</td>
                    <td>$Created.Nice</td>
                <tr>
            <% end_loop %>
        </tbody>
    </table>

    <p><a class="ss-ui-button ss-ui-button-block" href="{$SecurityLink}">
        <%t Commerce.ViewAll "View All" %>
    </a></p>
</div>