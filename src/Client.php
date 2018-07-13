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
 * You need to specify the following keys in your Environment file:
 * * AUTH0_DOMAIN
 * * AUTH0_CLIENT_ID
 * * AUTH0_CLIENT_SECRET
 *
 * Those values can be retrieved from your Auth0 dashboard. Alternatively, you can pass them via the `$baseSettings`
 * parameter on the constructor.
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
 * @property string AuthenticatorKey
 */
class Client extends Auth0
{

    use Injectable;

    /**
     * @var string
     */
    public $AuthenticatorKey;

    /**
     * Base settings, will be set via the environment settings most of the time.
     * @var array
     */
    protected $baseSettings = [];

    /**
     * Base settings, will be set via the environment settings most of the time.
     * @var string
     */
    protected $baseDomain = '';

    /**
     * Instantiate a new Auth0 client.
     * @param string $AuthenticatorKey Key used to add our AUth0 Authenticator to Security.
     * @param array $baseSetting Optional list of argument to pass to the aut0 client. Can be provided via
     * environment variables as well.
     * @param string $domain Auth0 domain where we will connect. Can be provided via the AUTH0_DOMAIN environment
     * variable as well.
     */
    public function __construct($AuthenticatorKey = '', $baseSettings = [], $baseDomain = '')
    {
        $this->AuthenticatorKey = $AuthenticatorKey;
        $this->baseSettings = $baseSettings;
        $this->baseDomain = $baseDomain ?: Environment::getEnv('AUTH0_DOMAIN');
        return parent::__construct($this->getSettings());
    }

    /**
     * Get the default Auth0 settings to pass to the base Auth0 client.
     *
     * @return array
     */
    public function getSettings()
    {
        $domain = $this->getDomain();
        return array_merge([
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
        ], $this->baseSettings);
    }

    /**
     * Get the AUTH0 domain to use from the `AUTH0_DOMAIN` environement key.
     * @return string
     */
    protected function getDomain()
    {
        $domain = $this->baseDomain;

        // Append auth0.com to domain
        if (!preg_match('/\.auth0\.com$/i', $domain)) {
            $domain .= '.auth0.com';
        }

        return $domain;
    }

    /**
     * Generate the URL that where the user needs to be redirected to after a successful authentication in Auth0.
     * @return string
     */
    protected function getRedirectUri()
    {
        $authenticatorSegment = $this->AuthenticatorKey ? '/' . $this->AuthenticatorKey : '';
        return Director::absoluteURL(
            Security::login_url() . $authenticatorSegment . '/callback'
        ) . '?BackURL=' . urlencode(Controller::curr()->getBackURL());
    }
}
