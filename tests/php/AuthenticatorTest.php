<?php

namespace MaximeRainville\Auth0\Tests;

use MaximeRainville\Auth0\LoginHandler;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Dev\SapphireTest;
use MaximeRainville\Auth0\Authenticator;
use SilverStripe\Security\Authenticator as SSAuthenticator;
use SilverStripe\Security\Member;

class AuthenticatorTest extends SapphireTest
{

    protected static $fixture_file = 'fixtures.yml';

    public function testSupportedServices()
    {
        $auth = new Authenticator();
        $this->assertEquals(
            SSAuthenticator::LOGIN | SSAuthenticator::LOGOUT,
            $auth->supportedServices(),
            "Auth0 Authenticator should only allow Login and Logout"
        );
    }

    public function testAuthenticate()
    {
        $auth = new Authenticator();
        $request = new HTTPRequest(
            'POST',
            'Security/auth0/login',
            array(),
            array()
        );
        $this->assertNull(
            $auth->authenticate([], $request),
            "Auth0 Authenticator null allow you to autenticate with a password"
        );
    }

    public function testCheckPassword()
    {
        $member = $this->objFromFixture(Member::class, 'member1');
        $auth = new Authenticator();
        $results = $auth->checkPassword($member, 'nonsense');

        $this->assertFalse(
            $results->isValid(),
            "Auth0 Authenticator should never be able to check a password."
        );
    }

    public function testLostPasswordHandler()
    {
        $auth = new Authenticator();
        $this->assertNull(
            $auth->getLostPasswordHandler('Security'),
            'Auth0 Authenticator should not provide a loss password handler'
        );
    }

    public function testChangePasswordHandler()
    {
        $auth = new Authenticator();
        $this->assertNull(
            $auth->getChangePasswordHandler('Security'),
            'Auth0 Authenticator should not provide a change password handler'
        );
    }

    public function testLoginHandler()
    {
        $auth = new Authenticator();
        $this->assertInstanceOf(
            RequestHandler::class,
            $auth->getLoginHandler('Security/auth0')
        );
    }

    public function testLogoutHandler()
    {
        $auth = new Authenticator();
        $this->assertInstanceOf(
            RequestHandler::class,
            $auth->getLogoutHandler('Security/auth0')
        );
    }
}
