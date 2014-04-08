<WPDISPLAY FILE="header.html">

    <meta http-equiv="refresh" content="5;url={$RedirectURL}" />

    <h1><% _t("Commerce.PROCESSING","Processing") %></h1>

    <p>
        <% _t("Commerce.REDIRECTINGTOSTORE","We are now redirecting you, if you are not redirected automatically then click the link below.") %>
    </p>

    <p>
        <a href="{$RedirectURL}">$SiteConfig.Title</a>
    </p>

<WPDISPLAY ITEM="banner">
<WPDISPLAY FILE="footer.html">
