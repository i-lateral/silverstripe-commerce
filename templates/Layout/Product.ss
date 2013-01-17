<div class="commerce-content-container typography">	
	<div class="commerce-product">
	
	    <h1>$Product.Title</h1>
	
	    <div class="commerce-product-images">
	        <div class="commerce-product-firstimage">
	            <% if $Product.Images %>
	                <a href="$Product.Images.First.Link">$Product.Images.First.CroppedImage(400,400)</a>
                <% else_if $Category.Thumbnail %>
                    $Category.Thumbnail.CroppedImage(400,400)
                <% else %>
                    <div class="commerce-noimage">$Top.CommerceNoImage.CroppedImage(500,500)</div>
                <% end_if %>
            </div>
            
            <% if $Product.HasMultipleImages %>
                <div class="commerce-product-thumbs">
                    <% loop $Product.Images %>
                        <% if not $First %><a href="$Link">$CroppedImage(75,75)</a><% end_if %>
                    <% end_loop %>
                </div>
            <% end_if %>
	    </div>

        <div class="commerce-product-summary">
	        <p class="commerce-product-price">
	            <span>Price:</span> {$SiteConfig.Currency.HTMLNotation.RAW}{$Product.Price}
	            <% if $Product.PackSize %><br/><span>Per:</span> {$Product.PackSize}<% end_if %>
	            <% if $Product.Weight %><br/><span>Weight:</span> {$Product.Weight}<% end_if %>
            </p>

	        $AddItemForm
        </div>
        
        <div class="commerce-clear"></div>

	    <div class="commerce-product-details">
	        <% if $Product.Description %>
	            <div class="commerce-product-description">
                    <h2>Description</h2>
	                $Product.Description
                </div>
            <% end_if %>
            
            <% if $Product.Attributes %>
	            <div class="commerce-product-attributes">
                    <h2>Attributes</h2>
                    <ul>
                        <% loop $Product.Attributes %><li class="commerce-product-feature">
                            <span class="commerce-strong">$Title:</span> $Content
                        </li><% end_loop %>
                    </ul>
                </div>
            <% end_if %>
        </div>
	    
	</div>
</div>

<% include SideBar %>
