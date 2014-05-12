<% if $Order.Status = 'failed' %>
<%t CommerceEmail.ORDER 'Order' %> ({$Order.OrderNumber}) <%t CommerceEmail.MARKEDAS 'has been marked as' %>: {$Order.TranslatedStatus}

<%t CommerceEmail.FAILEDNOTICE 'Unfortunately we could not process your order and payment. Please get in touch with us if you have any questions.' %>

<% if $SiteConfig.ContactPhone %><%t CommerceEmail.PHONE 'Phone' %>: {$SiteConfig.ContactPhone}<% end_if %>
<% if $SiteConfig.ContactEmail %><%t CommerceEmail.EMAIL 'Email' %>: {$SiteConfig.ContactEmail}<% end_if %>

{$SiteConfig.Title}

<% else %>
<%t CommerceEmail.THANKYOU 'Thank you for ordering from {title}' title=$SiteConfig.Title %> .

<%t CommerceEmail.ORDER 'Order' %> ({$Order.OrderNumber}) <%t CommerceEmail.MARKEDAS 'has been marked as' %>: {$Order.TranslatedStatus}

<%t CommerceEmail.ITEMS 'Items Ordered' %>
------------------------------------
<% loop $Order.Items() %>{$Title} x{$Quantity}
<% end_loop %>

<% if $Order.Status = 'dispatched' %><%t Commerce.DELIVERYDETAILS 'Delivery Details' %>
------------------------------------
<%t CommerceEmail.ORDERDISPATCHEDTO 'Your order will be dispatched to' %>:
{$Order.BillingFirstnames} {$Order.BillingSurname}
{$Order.DeliveryAddress1},
{$Order.DeliveryAddress2},
{$Order.DeliveryCity},
{$Order.DeliveryPostCode},
{$Order.DeliveryCountry}<% end_if %>

<% if $SiteConfig.VendorEmailFooter %>$SiteConfig.VendorEmailFooter<% end_if %>
<% if $SiteConfig.ContactPhone || $SiteConfig.ContactEmail %><%t CommerceEmail.CONTACTQUERIES 'If you have any queries, please' %>:
<% if $SiteConfig.ContactPhone %><%t CommerceEmail.PHONE 'Phone' %>: {$SiteConfig.ContactPhone}<% end_if %>
<% if $SiteConfig.ContactEmail %><%t CommerceEmail.EMAIL 'Email' %>: {$SiteConfig.ContactEmail}<% end_if %>
<% end_if %>

<% if $Order.Status = 'dispatched' %><%t CommerceEmail.CHECKORDER 'Please check your order carefully when it arrives and contact us as soon as
possible if there are any problems' %>.<% end_if %>

<%t CommerceEmail.FINALTHANKS 'Many thanks' %>,

{$SiteConfig.Title}
<% end_if %>
