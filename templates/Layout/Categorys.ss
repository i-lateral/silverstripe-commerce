<div class="commerce-content-container typography">	
	<h1>$Title</h1>
	
	<div class="commerce-list commerce-list-children">
	
        <% if Category.Children %>
            <% loop Category.Children %>
                <% include CategoryItem %>
            <% end_loop %>
        <% else_if Category.Products %>
            <% loop Category.Products %>    
                <% include CategoryItem %>
            <% end_loop %>
        <% else %>
            <p>Unable to find any products.</p>
        <% end_if %>
	
	</div>
</div>

<% include SideBar %>
