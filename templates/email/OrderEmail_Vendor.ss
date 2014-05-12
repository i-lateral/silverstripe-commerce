<% if $Order.Status = 'paid' %>A new order has been placed with order number: {$Order.OrderNumber}<% end_if %>

Order ({$Order.OrderNumber}) has been marked as: {$Order.Status}

ORDER ITEMS
------------------------------------
<% loop $Order.Items() %>{$Title} x{$Quantity}
<% if $CustomDetails %>Customisation: {$CustomDetails}<% end_if %><% end_loop %>


CUSTOMER DETAILS
------------------------------------
Order was made by: {$Order.BillingFirstnames} {$Order.BillingSurname}
<% if $Order.BillingPhone %>Phone: {$Order.BillingPhone}<% end_if %>
<% if $Order.BillingEmail %>Email: {$Order.BillingEmail}<% end_if %>


DELIVERY ADDRESS
------------------------------------
Order is to be dispatched to the following address:
{$Order.BillingFirstnames} {$Order.BillingSurname}
{$Order.DeliveryAddress1},
{$Order.DeliveryAddress2},
{$Order.DeliveryCity},
{$Order.DeliveryPostCode},
{$Order.DeliveryCountry}

