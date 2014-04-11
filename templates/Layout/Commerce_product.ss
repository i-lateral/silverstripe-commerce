<% require css('commerce/css/Commerce.css') %>

<div class="commerce-content-container typography">
    <div class="commerce-product">
        <p>$Breadcrumbs</p>

        <h1>$Title</h1>

        <div class="units-row">
            <div class="unit-50 commerce-product-images">
                <div id="commerce-product-image">
                    $ProductImage.PaddedImage(550,550)
                </div>

                <div class="units-row-end">
                    <% if $Images.Count > 1 %>
                        <div class="thumbs">
                            <% loop $SortedImages %>
                                <% if not $First %>
                                    <a href="{$Top.Link('image')}/$ID#commerce-product-image">
                                        $PaddedImage(75,75)
                                    </a>
                                <% end_if %>
                            <% end_loop %>
                        </div>
                    <% end_if %>
                </div>
            </div>

            <div class="unit-50 commerce-product-summary">
                <p>
                    <span class="price label big label-green">
                        <span class="title"><% _t('Commerce.PRICE','Price') %>:</span>
                        <span class="value">
                            {$SiteConfig.Currency.HTMLNotation.RAW}
                            {$FrontPrice}
                            {$SiteConfig.TaxString}
                        </span>
                    </span>

                    <% if $PackSize %>
                        <span class="packsize label big">
                            <span class="title bold"><% _t('Commerce.PACKSIZE','Pack Size') %>:</span>
                            <span class="value">{$PackSize}</span>
                        </span>
                    <% end_if %>

                    <% if $Weight %>
                        <span class="weight label big">
                            <span class="title bold"><% _t('Commerce.WEIGHT','Weight') %>:</span>
                            <span class="value">{$Weight}{$SiteConfig.Weight.Unit}</span>
                        </span>
                    <% end_if %>
                </p>

                <% if $Description %>
                    <div class="description">
                        <p>
                            $Description.Summary(50)
                            <a href="{$Top.Link()}#commerce-product-description" title="<% _t('Commerce.READMORE','Read More') %>: {$Title}">
                                <% _t('Commerce.READMORE','Read More') %>
                            </a>
                        </p>
                    </div>
                <% end_if %>

                $AddItemForm
            </div>
        </div>

        <div class="commerce-clear"></div>


        <%-- Description & Attributes: Only loaded when added through the CMS --%>
        <div class="units-row">
            <div class="commerce-product-details">
                <% if $Description %>
                    <div id="commerce-product-description" class="description">
                        <h2><% _t('Commerce.DESCRIPTION','Description') %></h2>
                        $Description
                    </div>
                <% end_if %>

                <% if $Attributes %>
                    <div id="commerce-product-attributes" class="attributes">
                        <h2><% _t('Commerce.ATTRIBUTES','Attributes') %></h2>
                        <ul>
                            <% loop $Attributes %><li class="feature">
                                <strong>$Title:</strong> $Content
                            </li><% end_loop %>
                        </ul>
                    </div>
                <% end_if %>
            </div>
        </div>

        <%-- Related Products: Only loaded when added through the CMS --%>
        <% if $RelatedProducts.exists %>
            <hr/>

            <h2><% _t('Commerce.RELATEDPRODUCTS','Related Products') %></h2>

            <div class="units-row commerce-related-products">
                <% loop $RelatedProducts %>
                    <div class="unit-20 commerce-list-child">
                        <h3><a href="$Link">$Title</a></h3>

                        <p>
                            <a href="$Link">
                                $SortedImages.First.CroppedImage(200,200)
                            </a>

                            <span class="price label label-green big">
                                {$Top.SiteConfig.Currency.HTMLNotation.RAW}{$Price}
                            </span>
                        </p>
                    </div>

                    <% if $MultipleOf(5) && not $Last %>
                        </div><div class="units-row commerce-related-products">
                    <% end_if %>


                <% end_loop %>
            </div>
        <% end_if %>

    </div>
</div>

<% include SideBar %>
