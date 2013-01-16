<div class="commerce-content-container typography">	
	<h1>$Title</h1>
	
	<% if $Content %>$Content<% end_if %>
	
	<div class="commerce-list commerce-list-children">
	
        <% if CategoriesOrProducts %>
            <% loop CategoriesOrProducts %>
                <div class="commerce-list-child site-float-left">
                    <h2><a href="$Link">$Title</a></h2>

                    <% if Thumbnail %>
                        <div class="commerce-list-image"><a href="$Link">$Thumbnail.CroppedImage(230,190)</a></div>
                    <% else_if Images %>
                        <div class="commerce-list-image"><a href="$Link">$Images.First.CroppedImage(230,190)</a></div>
                    <% else %>
                        <div class="commerce-noimage"><a href="$Link">$Top.CommerceNoImage.CroppedImage(230,190)</a></div>
                    <% end_if %>
                    
                    <% if ClassName = "Product" %><p class="commerce-list-price">{$Top.SiteConfig.Currency.HTMLNotation.RAW}{$Price}</p><% end_if %>
                </div>
            <% end_loop %>
        <% else %>
            <p>Unable to find any products.</p>
        <% end_if %>
	
	</div>
</div>

<% include SideBar %>
