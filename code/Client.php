<?php

namespace MaximeRainville\Auth0;

use SilverStripe\Core\Environment;
use SilverStripe\Control\Director;
use SilverStripe\Core\Injector\Injectable;
use Auth0\SDK\Auth0;
use SilverStripe\Security\Security;
use SilverStripe\Control\Controller;

/**
 * Simple extension of the base `Auth0\SDK\Auth0` client that can be called via the Injector.
 *
 * You need to specify the following keys in your Environement file:
 * * AUTH0_DOMAIN
 * * AUTH0_CLIENT_ID
 * * AUTH0_CLIENT_SECRET
 *
 * Those values can be retrieve from your Auth0 dashboard.
 *
 * If you want to implement your own Auth0 client, you can add this to your YML config to reference your own class.
 * ```YML
 * SilverStripe\Core\Injector\Injector:
 *   MaximeRainville\Auth0\Client:
 *     class:
 *       YourOwn\Auth0\Client
 * ```
 *
 * Or if you need get an instance of the Auth0 client:
 * ```php
 * \SilverStripe\Core\Injector\Injector::inst()->get(\MaximeRainville\Auth0\Client::class)
 * ```
 */
class Client extends Auth0
{

    use Injectable;

    public function __construct()
    {
        return parent::__construct($this->getDefaultSettings());
    }

    /**
     * Get the default Auth0 settings to pass to the base Auth0 client.
     *
     * @return array
     */
    protected function getDefaultSettings()
    {
        $domain = $this->getDomain();
        return [
            'domain' => $domain,
            'client_id' => Environment::getEnv('AUTH0_CLIENT_ID'),
            'client_secret' => Environment::getEnv('AUTH0_CLIENT_SECRET'),
            'redirect_uri' => $this->getRedirectUri(),
            'audience' => sprintf('https://%s/userinfo', $domain),
            'scope' => 'openid profile email',
            'persist_id_token' => true,
            'persist_access_token' => true,
            'persist_refresh_token' => true,
            'store' => false,
        ];
    }

    /**
     * Get the AUTH0 domain to use from the `AUTH0_DOMAIN` environement key.
     * @return string
     */
    protected function getDomain()
    {
        $domain = Environment::getEnv('AUTH0_DOMAIN');

        // Append auth0.com to domain
        if (!preg_match('/\.auth0\.com$/i', $domain)) {
            $domain .= '.auth0.com';
        }

        return $domain;
    }

    protected function getRedirectUri()
    {
        return Director::absoluteURL(Security::login_url() . '/callback') .
            '?BackURL=' . urlencode(Controller::curr()->getBackURL());
    }

}
