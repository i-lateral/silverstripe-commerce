<div class="commerce-content-container typography">	
	<h1>$Title</h1>
	
	<div class="commerce-list commerce-list-children">
	
    <% if Category.Children %>
        <% loop Category.Children %><div class="commerce-list-child">
            <h2><a href="$Link">$Title</a></h2>
            <a href="$Link">$Thumbnail.CroppedImage(225,225)</a>
        </div><% end_loop %>
    <% else_if Category.Products %>
        <% loop Category.Products %><div class="commerce-list-child">
            <h2><a href="$Link">$Title</a></h2>
            <a href="$Link">$Thumbnail.CroppedImage(225,225)</a>
        </div><% end_loop %>
    <% else %>
        <p>Unable to find any products.</p>
    <% end_if %>
	
	</div>
</div>

<% include SideBar %>
