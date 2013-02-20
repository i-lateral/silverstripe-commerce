Thank you for ordering from {$SiteConfig.Title}.

Your order ({$Order.OrderNumber}) has been marked as: {$Order.Status}

ITEMS
------------------------------------
<% loop $Order.Items() %>{$Title} x{$Quantity}
<% end_loop %>

<% if $Order.Status = 'dispatched' %>DELIVERY ADDRESS
------------------------------------
Your order will be dispatched to:
{$Order.BillingFirstnames} {$Order.BillingSurname}
{$Order.DeliveryAddress1},
{$Order.DeliveryAddress2},
{$Order.DeliveryCity},
{$Order.DeliveryPostCode},
{$Order.DeliveryCountry}<% end_if %>

<% if $SiteConfig.ContactPhone || $SiteConfig.ContactEmail %>If you have any queries, please contact us by:<% end_if %>

<% if $SiteConfig.ContactPhone %>Phone: {$SiteConfig.ContactPhone}<% end_if %>
<% if $SiteConfig.ContactEmail %>Email: {$SiteConfig.ContactEmail}<% end_if %>

<% if $Order.Status = 'dispatched' %>Please check your order carefully when it arrives, and contact us as soon as
possible if there are any problems.<% end_if %>

Many Thanks,

{$SiteConfig.Title}

