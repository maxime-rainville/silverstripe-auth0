<div class="cms-login-status">
	<% with $CurrentMember %>
        <a href="{$AdminURL}myprofile" class="cms-login-status__profile-link">
            <% if $CurrentMember.Auth0Picture %>
            <img src="$CurrentMember.Auth0Picture" class="rounded-circle" style="width: auto; height: 25px; position:absolute;">
            <% else %>
            <i class="font-icon-torso"></i>
            <% end_if %>

            <span <% if $CurrentMember.Auth0Picture %> <% end_if %>>
                <% if $FirstName && $Surname %>$FirstName $Surname<% else_if $FirstName %>$FirstName<% else %>$Email<% end_if %>
            </span>


        </a>
	<% end_with %>

	<a href="$LogoutURL" class="cms-login-status__logout-link font-icon-logout" title="<%t SilverStripe\Admin\LeftAndMain.LOGOUT 'Log out' %>"></a>
</div>
