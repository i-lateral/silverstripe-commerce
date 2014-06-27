<div class="commerce-content-container typography">
    <h1>$Title</h1>

    <% if $Content %>$Content<% end_if %>

    <div class="commerce-list-children line">
        <% if $Display = 'Categories' %>
            <% if RootCategories %>
                <% loop RootCategories %>
                    <div class="commerce-list-child unit size1of4">
                        <h2><a href="$Link">$Title</a></h2>
                        <% if Images %>
                            <div class="commerce-list-image"><a href="$Link">$Images.First.PaddedImage(190,190)</a></div>
                        <% else %>
                            <div class="commerce-noimage"><a href="$Link">$Top.CommerceNoImage.PaddedImage(190,190)</a></div>
                        <% end_if %>

                        <% if ClassName = "Product" %><p class="commerce-list-price">{$Top.SiteConfig.Currency.HTMLNotation.RAW}{$Price}</p><% end_if %>
                    </div>

                    <% if $MultipleOf(4) && not $Last %></div><div class="commerce-list-children line"><% end_if %>
                <% end_loop %>
            <% else %>
                <p>Unable to find any products.</p>
            <% end_if %>


        <% else_if $Display = 'Category' %>
            <% if CategoryChildren %>
                <% loop CategoryChildren %>
                    <div class="commerce-list-child unit size1of4">
                        <% if Images %>
                            <div class="commerce-list-image"><a href="$Link">$Images.First.PaddedImage(190,190)</a></div>
                        <% else %>
                            <div class="commerce-noimage"><a href="$Link">$Top.CommerceNoImage.PaddedImage(190,190)</a></div>
                        <% end_if %>

                        <h2><a href="$Link">$Title</a></h2>
                        <% if ClassName = "Product" %><p class="commerce-list-price">{$Top.SiteConfig.Currency.HTMLNotation.RAW}{$Price}</p><% end_if %>
                    </div>

                    <% if $MultipleOf(4) && not $Last %></div><div class="commerce-list-children line"><% end_if %>
                <% end_loop %>
            <% else %>
                <p>Unable to find any products.</p>
            <% end_if %>

        <% else_if $Display = 'Products' %>
            <% if AllProducts %>
                <% loop AllProducts %>
                    <div class="commerce-list-child unit size1of4">
                        <h2><a href="$Link">$Title</a></h2>

                        <a href="$Link">$SortedImages.First.CroppedImage(225,225)</a>

                        <% if ClassName = "Product" %><p class="commerce-list-price">{$Top.SiteConfig.Currency.HTMLNotation.RAW}{$Price}</p><% end_if %>
                    </div>

                    <% if $MultipleOf(4) && not $Last %></div><div class="commerce-list-children line"><% end_if %>
                <% end_loop %>
            <% end_if %>
        <% end_if %>

    </div>
</div>

<% include SideBar %>
