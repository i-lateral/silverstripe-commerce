<div class="commerce-content-container typography">	
	<div class="commerce-product">
	
	    <h1>$Product.Title</h1>
	
	    <div class="commerce-product-images">
	        <% if Product.Images %>
	            $Product.Images.First.CroppedImage(500,500)
            <% else_if Category.Thumbnail %>
                Category.Thumbnail.CroppedImage(500,500)
            <% else %>
                <div class="commerce-noimage"></div>
            <% end_if %>
	    </div>
	    
	    <p class="commerce-product-price">{$SiteConfig.Currency.HTMLNotation.RAW}{$Product.Price}</p>
	    
	    <div class="commerce-product-description">
	        $Product.Description
        </div>
        
        <div class="commerce-product-addform">
	        $AddItemForm
        </div>
	    
	</div>
</div>

<% include SideBar %>
