<?php

namespace MaximeRainville\Auth0;

use SilverStripe\Security\MemberAuthenticator\LogoutHandler as SSLogoutHandler;

/**
 * Handle log out when using Auth0 LoginHandler. It just redirects you to your homepage, but this can be overriden by
 * setting the `logout_url` in your YML config.
 */
class LogoutHandler extends SSLogoutHandler
{

    private static $logout_url = '';

    /**
     * @return HTTPResponse
     */
    protected function redirectAfterLogout()
    {
        if ($this->request->getVar('forceRedirect')) {
            return parent::redirectAfterLogout();
        }

        return $this->redirect(self::config()->logout_url);
    }
}
