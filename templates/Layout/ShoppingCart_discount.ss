<div class="commerce-content-container typography commerce-cart">
    <h1><%t Commerce.DiscountAdded 'Discount Added' %></h1>

    <p><%t Commerce.DiscountTitleAdded "{title} has been added to your shopping cart" title=$Discount.Title  %></p>

    <p>
        <a href="$BaseHref">
            <%t Commerce.ContinueShopping "Continue shopping" %>
        </a>
    </p>
</div>
