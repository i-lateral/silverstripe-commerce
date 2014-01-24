<div class="commerce-content-container typography">
    <h1>$Title</h1>

    <div class="units-row commerce-list commerce-list-children">
        <% if Children.exists %>
            <nav class="nav-g">
                <ul>
                    <% loop Children %>
                        <li><a href="$Link">$Title</a></li>
                    <% end_loop %>
                </ul>
            </nav>
        <% end_if %>
    </div>

    <% if $AllProducts.exists %>
        <div class="units-row commerce-list commerce-list-products">
            <% loop $AllProducts %>
                <div class="unit-25 commerce-list-child">
                    <h2><a href="$Link">$Title</a></h2>

                    <p>
                        <a href="$Link">$Images.First.CroppedImage(250,250)</a>

                        <span class="price label label-green big">
                            {$Top.SiteConfig.Currency.HTMLNotation.RAW}{$Price}
                        </span>
                    </p>
                </div>
            <% end_loop %>
        </div>
    <% end_if %>
</div>

<% include SideBar %>
