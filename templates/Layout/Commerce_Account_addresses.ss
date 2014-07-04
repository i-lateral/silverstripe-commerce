<% include Users_Profile_SideBar %>

<div class="commerce-content-container typography commerce-account unit-75 unit size3of4 lastUnit">
    <h1><%t CommerceAccount.YourAddresses "Your Addresses" %></h1>

    <% with $CurrentMember %>
        <div class="units-row line">
            <% loop $Addresses %>
                <div class="unit-33 unit size1of3<% if $MultipleOf(3) %> lastUnit<% end_if %>">
                    <p>
                        <strong>$FirstName $Surname</strong><br/>
                        $Address1<br/>
                        <% if $Address2 %>$Address2<br/><% end_if %>
                        $City<br/>
                        $PostCode<br/>
                        $Country<br/>
                        <% if not $Address2 %><br/><% end_if %>
                    </p>
                    <p>
                        <a href="{$Top.Link('editaddress')}/{$ID}" class="btn btn-green">
                            <%t CommerceAccount.Edit "Edit" %>
                        </a>
                        <a href="{$Top.Link('removeaddress')}/{$ID}" class="btn btn-red">
                            <%t CommerceAccount.Remove "Remove" %>
                        </a>
                    </p>
                </div>
                <% if MultipleOf(3) %></div><div class="units-row line"><% end_if %>
            <% end_loop %>
        </div>

        <% if $Addresses.exists %>
            <hr/>
        <% else %>
            <p>
                <%t CommerceAccount.NoAddrsses "You have no saved addresses." %>
            </p>
        <% end_if %>

    <% end_with %>

    <p>
        <a href="{$Link('addaddress')}" class="btn btn-green">
            <%t CommerceAccount.AddAddress "Add Address" %>
        </a>
    </p>

</div>
