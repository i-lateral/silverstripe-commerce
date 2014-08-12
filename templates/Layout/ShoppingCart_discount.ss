<div class="commerce-content-container typography commerce-cart">

    <% if $Discount %>
        <h1><%t Commerce.DiscountAdded 'Discount Added' %></h1>

        <% if $Discount.Type == 'Percentage' %>
            <p><%t Commerce.DiscountFixedText "{title} will be deducted from your next order" title=$Discount.Title  %></p>
        <% else_if $Discount.Type == 'Fixed' %>
            <p><%t Commerce.DiscountPercentText "A credit of £{amount} will be applied to your order" title=$Discount.Amount  %></p>
        <% end_if %>

        <p>
            <a class="btn" href="$BaseHref">
                <%t Commerce.StartShopping "Start shopping" %>
            </a>
        </p>
    <% else %>
        <h1><%t Commerce.DiscountNotValid 'Discount Not Valid' %></h1>

        <p><%t Commerce.DiscountNotValidText "This discount is either not valid or has expired"  %>.</p>

        <p>
            <a class="btn" href="{$BaseHref}">
                <%t Commerce.StartShopping "Start shopping" %>
            </a>
        </p>
    <% end_if %>
</div>
