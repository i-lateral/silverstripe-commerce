<WPDISPLAY FILE="header.html">

    <script type="text/javascript">
        function leave() {
            window.location.replace("{$RedirectURL}");
        }
        setTimeout("leave()", 5000);
    </script>

    <h1><% _t("Commerce.PROCESSING","Processing") %></h1>

    <p>
        <% _t("Commerce.REDIRECTINGTOSTORE","We are now redirecting you, if you are not redirected automatically then click the link below.") %>
    </p>

    <p>
        <a href="{$RedirectURL}">$SiteConfig.Title</a>
    </p>

<WPDISPLAY ITEM="banner">
<WPDISPLAY FILE="footer.html">
