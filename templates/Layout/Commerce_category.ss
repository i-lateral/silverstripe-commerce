<% require css('commerce/css/Commerce.css') %>

<% include SideBar %>

<div class="commerce-content-container typography <% if $Menu(2) %>unit-75<% end_if %>">
    <% if Level(2) %><p>$Breadcrumbs</p><% end_if %>

    <h1>$Title</h1>

    <div class="units-row commerce-list commerce-list-children">
        <% if Children.exists %>
            <nav class="nav-g">
                <ul>
                    <li>Filter By:</li>
                    <% loop Children %>
                        <li><a href="$Link">$Title</a></li>
                    <% end_loop %>
                </ul>
            </nav>
        <% end_if %>
    </div>

    <% if $PaginatedAllProducts(8).exists %>
        <div class="units-row commerce-list commerce-list-products">
            <% loop $PaginatedAllProducts(8) %>
                <div class="unit-25 commerce-list-child">
                    <h2><a href="$Link">$Title</a></h2>

                    <p>
                        <a href="$Link">$SortedImages.First.CroppedImage(350,350)</a>

                        <span class="price label label-green big">
                            {$Top.SiteConfig.Currency.HTMLNotation.RAW}{$FrontPrice}
                        </span>
                    </p>
                </div>

                <% if $MultipleOf(4) %></div><div class="units-row commerce-list commerce-list-products"><% end_if %>
            <% end_loop %>
        </div>

        <% with $PaginatedAllProducts(8) %>
            <% if $MoreThanOnePage %>
                <ul class="pagination">
                    <% if $NotFirstPage %>
                        <li class="prev">
                            <a class="prev" href="$PrevLink">Prev</a>
                        </li>
                    <% end_if %>

                    <% loop $Pages %>
                        <% if $CurrentBool %>
                            <li><span>$PageNum</span></li>
                        <% else %>
                            <% if $Link %>
                                <li><a href="$Link">$PageNum</a></li>
                            <% else %>
                                <li>...</li>
                            <% end_if %>
                        <% end_if %>
                    <% end_loop %>

                    <% if $NotLastPage %>
                        <li class="next">
                            <a class="next" href="$NextLink">Next</a>
                        </li>
                    <% end_if %>
                </ul>
            <% end_if %>
        <% end_with %>

    <% end_if %>
</div>
