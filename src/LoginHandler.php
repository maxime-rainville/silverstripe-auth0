<?php

namespace MaximeRainville\Auth0;

use Auth0\SDK\Exception\CoreException;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Authenticator as SSAuthenticator;
use SilverStripe\Security\IdentityStore;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use PageController;

/**
 * Handle login requests from MaximeRainville\Auth0\Authenticator.
 *
 * @internal Mostly copied from the regular LoginHandler. Unfortunately, the regular login handler is tightly coupled
 * with the MemberLoginForm, which makes our work a bit more difficult here.
 */
class LoginHandler extends RequestHandler
{
    /**
     * @var SSAuthenticator
     */
    protected $authenticator;

    /**
     * @var array
     * @config
     */
    private static $url_handlers = [
        '' => 'login',
    ];

    /**
     * @var array
     * @config
     */
    private static $allowed_actions = [
        'login',
        'logout',
        'callback'
    ];

    /**
     * Whatever we allow registration without any pre-condition. Defaults to `FALSE`.
     * If set to `TRUE` anyone can use the Auth0 authentication request to create an account.
     * @var bool
     */
    private static $allow_public_registration = false;

    /**
     * @var string Called link on this handler
     */
    private $link;

    /**
     * @param string $link The URL to recreate this request handler
     * @param SSAuthenticator $authenticator The authenticator to use
     */
    public function __construct($link, SSAuthenticator $authenticator)
    {
        $this->link = $link;
        $this->authenticator = $authenticator;
        parent::__construct();
    }

    /**
     * Return a link to this request handler.
     * The link returned is supplied in the constructor
     * @param null|string $action
     * @return string
     */
    public function link($action = null)
    {
        if ($action) {
            return Controller::join_links($this->link, $action);
        }

        return $this->link;
    }

    /**
     * URL handler for the log-in screen
     *
     * @return array
     */
    public function login()
    {
        // Make sure we don't have a user already logged in.
        if (Security::getCurrentUser()) {
            return [
                'Form' => $this->LoginAsSomeoneElseForm()
            ];
        }

        // This will redirect the user to the Auth0 Form
        $auth0 = Injector::inst()->get(Client::class);
        $auth0->login();
        return [];
    }

    public function LoginAsSomeoneElseForm()
    {
        return LoginAsSomeoneElseForm::create(
            $this,
            'LoginAsSomeoneElseForm',
            get_class($this->authenticator)
        );
    }

    /**
     * Login form handler method
     *
     * This method is called when the user finishes the login flow
     *
     * @param HTTPRequest $request
     * @return HTTPResponse
     */
    public function callback(HTTPRequest $request)
    {
        Security::setCurrentUser(null);

        $failureMessage = null;
        $this->extend('beforeLogin');
        // Successful login
        if ($member = $this->checkLogin()) {
            $this->performLogin($member, [], $request);

            // Allow operations on the member after successful login
            $this->extend('afterLogin', $member);
            return $this->redirectAfterSuccessfulLogin();
        }

        $this->extend('failedLogin');

        $this->httpError(
            401,
            'Could not log you in'
        );
    }

    public function getReturnReferer()
    {
        return $this->link();
    }

    /**
     * Login in the user and figure out where to redirect the browser.
     *
     * The $data has this format
     * array(
     *   'AuthenticationMethod' => 'MemberAuthenticator',
     *   'Email' => 'sam@silverstripe.com',
     *   'Password' => '1nitialPassword',
     *   'BackURL' => 'test/link',
     *   [Optional: 'Remember' => 1 ]
     * )
     *
     * @return HTTPResponse
     */
    protected function redirectAfterSuccessfulLogin()
    {
        $member = Security::getCurrentUser();

        // Absolute redirection URLs may cause spoofing
        $backURL = $this->getBackURL();
        if ($backURL) {
            return $this->redirect($backURL);
        }

        // If a default login dest has been set, redirect to that.
        $defaultLoginDest = Security::config()->get('default_login_dest');
        if ($defaultLoginDest) {
            return $this->redirect($defaultLoginDest);
        }

        // Redirect the user to the page where they came from
        if ($member) {
            // Welcome message
            $message = _t(
                'SilverStripe\\Security\\Member.WELCOMEBACK',
                'Welcome Back, {firstname}',
                ['firstname' => $member->FirstName]
            );
            Security::singleton()->setSessionMessage($message, ValidationResult::TYPE_GOOD);
        }

        // Redirect back
        return $this->redirectBack();
    }

    /**
     * Try to authenticate the user
     *
     * @param ValidationResult $result
     * @return Member Returns the member object on successful authentication
     *                or NULL on failure.
     */
    public function checkLogin(ValidationResult &$result = null)
    {
        $auth0 = Injector::inst()->get(Client::class);

        try {
            $userData = $auth0->getUser();
        } catch (CoreException $ex) {
            return null;
        }

        $member = $this->updateMember($userData);

        return $member ? $member : null;
    }

    /**
     * Try to authenticate the user
     *
     * @param Member $member
     * @param array $data Submitted data
     * @param HTTPRequest $request
     * @return Member Returns the member object on successful authentication
     *                or NULL on failure.
     */
    public function performLogin($member, $data, HTTPRequest $request)
    {
        $identityStore = Injector::inst()->get(IdentityStore::class);
        $identityStore->logIn($member, false, $request);

        return $member;
    }

    /**
     * Given a user info from Auth0, will attempt to retrive a matching Member based on its email address.
     *
     * If no Member is found, will check if it the userData allows registration. If registration is allowed, a new
     * Member will be created. Otherwise, `FALSE` will be returned.
     *
     * If we have a Member to work with, it will be updated with the information provided by Auth0.
     *
     * @param  array  $userInfo
     * @return Member|false
     */
    protected function updateMember(array $userInfo)
    {
        $member = $this->findMatchingMember($userInfo);

        // Couldn't find a member. Let's see if we can create it.
        if (!$member) {
            if ($this->allowUserToRegister($userInfo)) {
                $member = Member::create();
                $member->Email = $userInfo['email'];
            } else {
                return false;
            }
        }

        // Fill out some common field
        $member->FirstName = isset($userInfo['given_name']) ? $userInfo['given_name']: '';
        $member->Surname = isset($userInfo['family_name']) ? $userInfo['family_name']: '';

        // Give a chance to our Extension to handle custom fields.
        $this->extend('updateMember', $member, $userInfo);

        // Check if the Member has EnrichAuth0Profile that expect some Auth0 data
        if ($member->hasMethod('EnrichAuth0Profile')) {
            $member->EnrichAuth0Profile($userInfo);
        }

        $member->write();

        return $member;
    }

    /**
     * Finds a matching member by using the $userInfo array. Default is to use Email address to find
     * a match, but since Auth0 allows empty email fields, we need to be able to overwrite this behaviour
     * and match with other fields
     *
     * Extension can hook into this method if they want.
     *
     * @param  array  $userInfo [description]
     * @return Member|false
     */
    protected function findMatchingMember(array $userInfo) {
        $answers = $this->extend('findMatchingMember', $userInfo);
        foreach ($answers as $member) {
            if ($member) {
                return $member;
            }
        }

        if (strlen($userInfo['email']) > 0) {
            return Member::get()->filter(['Email' => $userInfo['email']])->First();
        } else {
            return Member::get()->filter(['Auth0Sub' => $userInfo['sub']])->First();
        }
    }

    /**
     * Determines if users not already in the system are allowed to register.
     *
     * If the `allow_public_registration` config flag is set to true, user will be allowed to register.
     *
     * Extension can hook into this method if they want.
     *
     * @param  array  $userInfo [description]
     * @return bool
     */
    public function allowUserToRegister(array $userInfo)
    {
        if (self::config()->allow_public_registration) {
            return true;
        };

        // Check if one of our extension allows registrations.
        $answers = $this->extend('allowUserToRegister', $userInfo);
        foreach ($answers as $answer) {
            if ($answer) {
                return true;
            }
        }

        // Disallow registration.
        return false;
    }

    /**
     * @inheritdoc
     * @internal We want to display pretty error pages so we will relay errors to the PageController.
     * @param int $errorCode
     * @param null $errorMessage
     */
    public function httpError($errorCode, $errorMessage = null)
    {
        return PageController::singleton()->httpError($errorCode, $errorMessage);
    }

}
