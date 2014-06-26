<% if $Order.Status = 'failed' %>
<%t CommerceEmail.Order 'Order' %> ({$Order.OrderNumber}) <%t CommerceEmail.MarkedAs 'has been marked as' %>: {$Order.TranslatedStatus}

<%t CommerceEmail.FailedNotice 'Unfortunately we could not process your order and payment. Please get in touch with us if you have any questions.' %>

<% if $SiteConfig.ContactPhone %><%t CommerceEmail.Phone 'Phone' %>: {$SiteConfig.ContactPhone}<% end_if %>
<% if $SiteConfig.ContactEmail %><%t CommerceEmail.Email 'Email' %>: {$SiteConfig.ContactEmail}<% end_if %>

{$SiteConfig.Title}

<% else %>
<%t CommerceEmail.ThankYou 'Thank you for ordering from {title}' title=$SiteConfig.Title %> .

<%t CommerceEmail.Order 'Order' %> ({$Order.OrderNumber}) <%t CommerceEmail.MarkedAs 'has been marked as' %>: {$Order.TranslatedStatus}

<%t CommerceEmail.Items 'Items Ordered' %>
------------------------------------
<% loop $Order.Items() %>{$Title} x{$Quantity}
<% end_loop %>

<% if $Order.Status = 'dispatched' %><%t Commerce.DeliveryDetails 'Delivery Details' %>
------------------------------------
<%t CommerceEmail.OrderDispatchedTo 'Your order will be dispatched to' %>:
{$Order.BillingFirstnames} {$Order.BillingSurname}
{$Order.DeliveryAddress1},
{$Order.DeliveryAddress2},
{$Order.DeliveryCity},
{$Order.DeliveryPostCode},
{$Order.DeliveryCountry}<% end_if %>

<% if $SiteConfig.VendorEmailFooter %>$SiteConfig.VendorEmailFooter<% end_if %>
<% if $SiteConfig.ContactPhone || $SiteConfig.ContactEmail %><%t CommerceEmail.ContactQueries 'If you have any queries, please' %>:
<% if $SiteConfig.ContactPhone %><%t CommerceEmail.Phone 'Phone' %>: {$SiteConfig.ContactPhone}<% end_if %>
<% if $SiteConfig.ContactEmail %><%t CommerceEmail.Email 'Email' %>: {$SiteConfig.ContactEmail}<% end_if %>
<% end_if %>

<% if $Order.Status = 'dispatched' %><%t CommerceEmail.CheckOrder 'Please check your order carefully when it arrives and contact us as soon as possible if there are any problems' %>.<% end_if %>

<%t CommerceEmail.FinalThanks 'Many thanks' %>,

{$SiteConfig.Title}
<% end_if %>
