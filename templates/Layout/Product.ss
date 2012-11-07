<div class="commerce-content-container typography">	
	<div class="commerce-product">
	
	    <h1>$Product.Title</h1>
	
	    <div class="commerce-product-images">
	        <% if Product.Images %>
	            $Product.Images.First.CroppedImage(500,500)
            <% else_if Category.Thumbnail %>
                Category.Thumbnail.CroppedImage(500,500)
            <% else %>
                <div class="commerce-noimage">>$Top.CommerceNoImage.CroppedImage(500,500)</div>
            <% end_if %>
	    </div>
	    
	    <p class="commerce-product-price">{$SiteConfig.Currency.HTMLNotation.RAW}{$Product.Price}</p>
	    
	    <% if $Product.PackSize %><p class="commerce-product-packsize">Per: {$Product.PackSize}</p><% end_if %>
	    
	    <% if $Product.Weight %><p class="commerce-product-weight">Weight: {$Product.Weight}</p><% end_if %>
	    
	    <% if $Product.Description %><div class="commerce-product-description">
	        $Product.Description
        </div><% end_if %>
        
        <div class="commerce-product-addform">
	        $AddItemForm
        </div>
	    
	</div>
</div>

<% include SideBar %>
