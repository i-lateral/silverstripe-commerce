<% _t('CommerceEmail.THANKYOU','Thank you for ordering from') %> {$SiteConfig.Title}.

<% _t('CommerceEmail.ORDER','Order') %> ({$Order.OrderNumber}) <% _t('CommerceEmail.MARKEDAS','has been marked as') %>: {$Order.TranslatedStatus}

<% _t('CommerceEmail.ITEMS','Items Ordered') %>
------------------------------------
<% loop $Order.Items() %>{$Title} x{$Quantity}
<% end_loop %>

<% if $Order.Status = 'dispatched' %><% _t('Commerce.DELIVERYDETAILS','Delivery Details') %>
------------------------------------
<% _t('CommerceEmail.ORDERDISPATCHEDTO','Your order will be dispatched to') %>:
{$Order.BillingFirstnames} {$Order.BillingSurname}
{$Order.DeliveryAddress1},
{$Order.DeliveryAddress2},
{$Order.DeliveryCity},
{$Order.DeliveryPostCode},
{$Order.DeliveryCountry}<% end_if %>

<% if $SiteConfig.VendorEmailFooter %>$SiteConfig.VendorEmailFooter<% end_if %>

<% if $SiteConfig.ContactPhone || $SiteConfig.ContactEmail %><% _t('CommerceEmail.CONTACTQUERIES','If you have any queries, please') %>:<% end_if %>

<% if $SiteConfig.ContactPhone %><% _t('CommerceEmail.PHONE','Phone') %>: {$SiteConfig.ContactPhone}<% end_if %>
<% if $SiteConfig.ContactEmail %><% _t('CommerceEmail.EMAIL','Email') %>: {$SiteConfig.ContactEmail}<% end_if %>

<% if $Order.Status = 'dispatched' %><% _t('CommerceEmail.CHECKORDER','Please check your order carefully when it arrives and contact us as soon as
possible if there are any problems') %>.<% end_if %>

<% _t('CommerceEmail.FINALTHANKS','Many thanks') %>,

{$SiteConfig.Title}
