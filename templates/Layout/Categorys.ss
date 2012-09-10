<div class="commerce-content-container typography">	
	<h1>$Title</h1>
	
	<div class="commerce-list commerce-list-children">
	
        <% if Category.Children %>
            <% loop Category.Children %>
                <div class="commerce-list-child<% if FirstLast %> commerce-list-$FirstLast<% end_if %>"><% include CategoryItem %></div>
            <% end_loop %>
        <% else_if Category.Products %>
            <% loop Category.Products %>
                <div class="commerce-list-child<% if FirstLast %> commerce-list-$FirstLast<% end_if %>"><% include CategoryItem %></div>
            <% end_loop %>
        <% else %>
            <p>Unable to find any products.</p>
        <% end_if %>
	
	</div>
</div>

<% include SideBar %>
