<% require css('commerce/css/Commerce.css') %>

<div class="commerce-content-container typography commerce-cart">
    <h1><% _t('Commerce.CartName', 'Shopping Cart') %></h1>

    <% if $Items.exists %>

        $SiteConfig.CartCopy

        <div class="form">
            $CartForm
        </div>

        <div class="units-row commerce-cart-postage">
            <div class="unit-66">
                <h2><% _t('Commerce.EstimateShipping','Estimate Shipping') %></h2>
                $PostageForm
            </div>

            <div class="unit-33">
                <h2><% _t("Commerce.Total","Total") %></h2>

                <table class="commerce-tax-table width-100">
                    <tr class="subtotal">
                        <td class="right">
                            <strong>
                                <% _t('Commerce.SubTotal','Sub Total') %>
                            </strong>
                        </td>
                        <td class="right">
                            {$SiteConfig.Currency.HTMLNotation.RAW}{$CommerceCart.SubTotalCost}
                        </td>
                    </tr>
                    <tr class="shipping">
                        <td class="right">
                            <strong>
                                <% _t('Commerce.Shipping','Shipping') %>
                            </strong>
                        </td>
                        <td class="right">
                            {$SiteConfig.Currency.HTMLNotation.RAW}{$CommerceCart.PostageCost}
                        </td>
                    </tr>
                    <% if $SiteConfig.TaxRate > 0 %>
                        <tr class="tax">
                            <td class="right">
                                <strong>
                                    <% if $SiteConfig.TaxName %>{$SiteConfig.TaxName}
                                    <% else %><% _t('Commerce.Tax','Tax') %><% end_if %>
                                </strong>
                            </td>
                            <td class="right">
                                {$SiteConfig.Currency.HTMLNotation.RAW}{$CommerceCart.TaxCost}
                            </td>
                        </tr>
                    <% end_if %>
                </table>

                <p class="commerce-cart-total">
                    <strong class="uppercase bold">
                        <% _t('Commerce.CartTotal','Total') %>:
                    </strong>
                    {$SiteConfig.Currency.HTMLNotation.RAW}{$CommerceCart.TotalCost}
                </p>
            </div>
        </div>

        <div class="units-row-end">
            <div class="unit-push-right">
                <a href="{$BaseHref}commerce/checkout" class="btn btn-green btn-big">
                    <% _t('Commerce.CartProceed','Proceed to Checkout') %>
                </a>
            </div>
        </div>
    <% else %>
        <p>
            <strong>
                <% _t('Commerce.CartIsEmpty','Your cart is currently empty') %>
            </strong>
        </p>
    <% end_if %>
</div>
