<?php

namespace MaximeRainville\Auth0\Tests;

use MaximeRainville\Auth0\Client;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Environment;
use SilverStripe\Dev\SapphireTest;


class ClientTest extends SapphireTest
{

    function setUp() {
        // Pretend to start a session and suppress the error to shut up the orginal auth0 client that we extend.
        @session_start();
        parent::setUp();
    }

    function testGetSettings()
    {
        $expected = [
            "domain" => "example.au.auth0.com",
            "client_id" => "myclientid",
            "client_secret" => "myclientsecret",
            "redirect_uri" => "http://localhost/Security/login/auth0/callback?BackURL=",
            "audience" => "https://example.au.auth0.com/userinfo",
            "scope" => "openid profile email",
            "persist_id_token" => true,
            "persist_access_token" => true,
            "persist_refresh_token" => true,
            "store" => false
        ];

        // Using env variable
        Environment::setEnv('AUTH0_CLIENT_ID', 'myclientid');
        Environment::setEnv('AUTH0_CLIENT_SECRET', 'myclientsecret');
        Environment::setEnv('AUTH0_DOMAIN', 'example.au');

        $client = new Client('auth0');

        $this->assertEquals(
            $expected,
            $client->getSettings()
        );

        // Using constructor
        $client = new Client(
            'auth0',
            ['client_id' => 'myclientid', 'client_secret' => 'myclientsecret'],
            'example.au'
        );

        $this->assertEquals(
            $expected,
            $client->getSettings()
        );


    }
}
