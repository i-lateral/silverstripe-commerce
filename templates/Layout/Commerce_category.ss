<% require css('commerce/css/Commerce.css') %>

<% include SideBar %>

<div class="commerce-content-container typography <% if $Menu(2) %>unit size3of4 lastUnit unit-75<% end_if %>">
    <% if Level(2) %><p>$Breadcrumbs</p><% end_if %>

    <h1>$Title</h1>

    <div class="units-row commerce-list">
        <% if Children.exists %>
            <nav class="nav-g">
                <ul class="line">
                    <li class="unit">Filter By:</li>
                    <% loop Children %>
                        <li class="unit"><a href="$Link">$Title</a></li>
                    <% end_loop %>
                </ul>
            </nav>
        <% end_if %>
    </div>

    <% if $PaginatedAllProducts(8).exists %>
        <div class="units-row line commerce-list">
            <% loop $PaginatedAllProducts(8) %>
                <div class="unit-25 unit size1of4 commerce-list-child">
                    <h2><a href="$Link">$Title</a></h2>

                    <p>
                        <a href="$Link">$SortedImages.First.CroppedImage(180,180)</a>

                        <span class="price label label-green big">
                            {$Top.SiteConfig.Currency.HTMLNotation.RAW}{$FrontPrice}
                        </span>

                        <% if not $Quantity %>
                            <span class="label label-red">
                                <strong>
                                    <%t Commerce.OutOfStock "Out of stock" %>
                                </strong>
                            </span>
                        <% end_if %>
                    </p>
                </div>

                <% if $MultipleOf(4) %></div><div class="units-row line commerce-list"><% end_if %>
            <% end_loop %>
        </div>

        <% with $PaginatedAllProducts(8) %>
            <% if $MoreThanOnePage %>
                <ul class="pagination line">
                    <% if $NotFirstPage %>
                        <li class="prev unit">
                            <a class="prev" href="$PrevLink">Prev</a>
                        </li>
                    <% end_if %>

                    <% loop $Pages %>
                        <% if $CurrentBool %>
                            <li class="unit"><span>$PageNum</span></li>
                        <% else %>
                            <% if $Link %>
                                <li class="unit"><a href="$Link">$PageNum</a></li>
                            <% else %>
                                <li class="unit">...</li>
                            <% end_if %>
                        <% end_if %>
                    <% end_loop %>

                    <% if $NotLastPage %>
                        <li class="unit next">
                            <a class="next" href="$NextLink">Next</a>
                        </li>
                    <% end_if %>
                </ul>
            <% end_if %>
        <% end_with %>

    <% end_if %>
</div>
