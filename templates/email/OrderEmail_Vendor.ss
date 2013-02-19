<% if $Order.Status = 'paid' %>A new order has been placed with order number: {$Order.OrderNumber}<% end_if %>

Order ({$Order.OrderNumber}) has been marked as: {$Order.Status}

ORDER ITEMS
------------------------------------
<% loop $Order.Items() %>{$Title} x{$Quantity}
<% if $CustomDetails %>Customisation: {$CustomDetails}<% end_if %><% end_loop %>

DELIVERY ADDRESS
------------------------------------
Order is to be dispatched to the following address:
{$Order.DeliveryAddress1},
{$Order.DeliveryAddress2},
{$Order.DeliveryCity},
{$Order.DeliveryPostCode},
{$Order.DeliveryCountry}

