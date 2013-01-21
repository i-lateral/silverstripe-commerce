<div class="commerce-content-container typography">	
	<div class="commerce-product">
	
	    <h1>$Title</h1>
	    
	    <% loop $Product %>
	
	        <div class="commerce-product-images">
	            <div class="firstimage">
	                <% if $Images %>
	                    <a href="$Images.First.SetRatioSize(900,550).Link">$Images.First.PaddedImage(400,400)</a>
                    <% else_if $Top.Category.Thumbnail %>
                        $Top.Category.Thumbnail.PaddedImage(400,400)
                    <% else %>
                        <div class="commerce-noimage">$Top.CommerceNoImage.PaddedImage(500,500)</div>
                    <% end_if %>
                </div>
                
                <% if $HasMultipleImages %>
                    <div class="thumbs">
                        <% loop $Images %>
                            <% if not $First %><a href="$SetRatioSize(900,550).Link">$PaddedImage(75,75)</a><% end_if %>
                        <% end_loop %>
                    </div>
                <% end_if %>
	        </div>

            <div class="commerce-product-summary">
	            <p class="price"><strong>Price:</strong> {$Top.SiteConfig.Currency.HTMLNotation.RAW}{$Price}</p>
                <% if $PackSize %><p class="packsize"><strong>Pack Size:</strong> {$PackSize}</p><% end_if %>
                <% if $Weight %><p class="weight"><strong>Weight:</strong> {$Weight}</p><% end_if %>
                
                <% if $Description %>
	                <div class="description">
	                    <p>
	                        $Description.Summary(50)
	                        <a href="#commerce-product-description" title="Read more about {$Title}">Read More</a>
                        </p>
                    </div>
                <% end_if %>

	            $Top.AddItemForm
            </div>
            
            <div class="commerce-clear"></div>

	        <div class="commerce-product-details">
	            <% if $Description %>
	                <div id="commerce-product-description" class="description">
                        <h2>Description</h2>
	                    $Description
                    </div>
                <% end_if %>
                
                <% if $Attributes %>
	                <div class="attributes">
                        <h2>Attributes</h2>
                        <ul>
                            <% loop $Attributes %><li class="feature">
                                <strong>$Title:</strong> $Content
                            </li><% end_loop %>
                        </ul>
                    </div>
                <% end_if %>
            </div>
        <% end_loop %>
	    
	</div>
</div>

<% include SideBar %>
