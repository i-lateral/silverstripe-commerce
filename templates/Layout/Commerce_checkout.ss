<div class="commerce-content-container typography">
    <h1>$Title</h1>

    <% if $ClassName == "CheckoutLogin" %>
        <div class="units-row">
            <div class="unit-33">
                $Form
            </div>
            <div class="unit-33">
                $Content
            </div>
            <div class="unit-33">
                $Content
            </div>
        </div>


    <% else %>
        $CheckoutForm
    <% end_if %>
</div>
