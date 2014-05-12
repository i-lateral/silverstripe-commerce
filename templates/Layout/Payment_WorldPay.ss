
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<!-- layout.templ $Revision$ -->
<html lang="en">
<head>
    <meta http-equiv="refresh" content="3;url={$RedirectURL}" />

    <title>$SiteConfig.Title <% _t("Commerce.PROCESSING","Processing") %></title>

    <link rel="stylesheet" href="/i/<wpdisplay item=instId>/stylesheet.css" type="text/css" />

</head>

<WPDISPLAY FILE="header.html">

    <div class="commerce-payment-status">
        <h1><% _t("Commerce.PROCESSING","Processing") %></h1>

        <p>
            <% _t("Commerce.REDIRECTINGTOSTORE","We are now redirecting you, if you are not redirected automatically then click the link below.") %>
        </p>

        <p>
            <a href="{$RedirectURL}">$SiteConfig.Title</a>
        </p>
    </div>

    <WPDISPLAY ITEM="banner">
<WPDISPLAY FILE="footer.html">

</html>
