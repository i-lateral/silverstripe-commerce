<p class="units-row line">
    <% _t('CommerceAccount.NoAccount',"Don't have an account") %>?
</p>

<p class="units-row line">
    <a href="{$BaseHref}users/register?BackURL={$Link}" class="btn text-centered unit-push-right width-100">
        <% _t("CommerceAccount.Register", "Register") %>
    </a>
</p>

<p class="units-row line text-centered"><strong><% _t('CommerceAccount.Or',"Or") %></strong></p>

<p class="units-row line">
    <a href="{$Link('billing')}" class="btn text-centered unit-push-right width-100">
        <% _t('CommerceAccount.ContinueGuest',"Continue as a Guest") %>
    </a>
</p>
