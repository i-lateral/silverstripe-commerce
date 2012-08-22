<% if ShowSitesMenu %><ul class="siteselect">
    <li>
        <span class="title"><% _t('Commerce.CHANGELOCATION','Change Location') %></span>
        <ul>
            <% control AllSites %><li><a href="$absoluteBaseURL">$Title</a></li><% end_control %>
        </ul>
    </li>
</ul><% end_if %>