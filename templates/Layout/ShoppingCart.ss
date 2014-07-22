<% require css('commerce/css/Commerce.css') %>

<div class="commerce-content-container typography commerce-cart">
    <h1><%t Commerce.CartName 'Shopping Cart' %></h1>

    <% if $Items.exists %>

        $SiteConfig.CartCopy

        <div class="commerce-cart-form">
            $CartForm
        </div>

        <hr/>

        <div class="units-row line">
            <div class="unit-66 unit size2of3">
                <div class="commerce-cart-discounts line units-row-end">
                    <% if $Discount || $ShowDiscountForm %>
                        <h2><%t Commerce.Discount "Discount" %></h2>
                    <% end_if %>

                    <% if $Discount %>
                        <p>
                            <%t Commerce.CurrentDiscount "Current discount" %>
                            $Discount.Title
                        </p>
                    <% end_if %>

                    <% if $ShowDiscountForm %>
                        $DiscountForm
                    <% end_if %>
                </div>

                <hr/>

                <div class="commerce-cart-postage">
                    <h2><%t Commerce.EstimateShipping "Estimate Shipping" %></h2>
                    $PostageForm
                </div>
            </div>

            <div class="unit-33 unit size1of3">
                <h2><%t Commerce.Total "Total" %></h2>

                <table class="commerce-tax-table width-100">
                    <tr class="subtotal">
                        <td class="right">
                            <strong>
                                <%t Commerce.SubTotal 'Sub Total' %>
                            </strong>
                        </td>
                        <td class="right">
                            {$SiteConfig.Currency.HTMLNotation.RAW}{$SubTotalCost}
                        </td>
                    </tr>

                    <% if $Discount %>
                        <tr class="discounts">
                            <td class="right">
                                <strong>
                                    <%t Commerce.Discount 'Discount' %>
                                </strong>
                            </td>
                            <td class="right">
                                {$SiteConfig.Currency.HTMLNotation.RAW}{$DiscountAmount}
                            </td>
                        </tr>
                    <% end_if %>

                    <tr class="shipping">
                        <td class="right">
                            <strong>
                                <%t Commerce.Shipping 'Shipping' %>
                            </strong>
                        </td>
                        <td class="right">
                            {$SiteConfig.Currency.HTMLNotation.RAW}{$PostageCost}
                        </td>
                    </tr>
                    <% if $SiteConfig.TaxRate > 0 %>
                        <tr class="tax">
                            <td class="right">
                                <strong>
                                    <% if $SiteConfig.TaxName %>{$SiteConfig.TaxName}
                                    <% else %><%t Commerce.Tax 'Tax' %><% end_if %>
                                </strong>
                            </td>
                            <td class="right">
                                {$SiteConfig.Currency.HTMLNotation.RAW}{$TaxCost}
                            </td>
                        </tr>
                    <% end_if %>
                </table>

                <p class="commerce-cart-total">
                    <strong class="uppercase bold">
                        <%t Commerce.CartTotal 'Total' %>:
                    </strong>
                    {$SiteConfig.Currency.HTMLNotation.RAW}{$TotalCost}
                </p>
            </div>
        </div>

        <hr/>

        <div class="commerce-cart-proceed line units-row-end">
            <div class="unit-push-right">
                <a href="{$BaseHref}commerce/checkout" class="btn btn-green btn-big">
                    <%t Commerce.CartProceed 'Proceed to Checkout' %>
                </a>
            </div>
        </div>
    <% else %>
        <p>
            <strong>
                <%t Commerce.CartIsEmpty 'Your cart is currently empty' %>
            </strong>
        </p>
    <% end_if %>
</div>
