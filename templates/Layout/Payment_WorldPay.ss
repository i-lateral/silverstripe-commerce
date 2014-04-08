<WPDISPLAY FILE="header.html">

<script type="text/javascript">
    window.location.replace("{$RedirectURL}");
</script>

<h1><% _t("Commerce.PROCESSING","Processing") %></h1>

<p>
    <% _t("Commerce.REDIRECTINGTOSTORE","We are now redirecting you, if you are not redirected automatically then click the link below.") %>
    <a href="{$RedirectURL}">$SiteConfig.Title</a>
</p>

<WPDISPLAY ITEM="banner">
<WPDISPLAY FILE="footer.html">
