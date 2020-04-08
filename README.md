# Auth0/Silverstripe Integration
This module provides a mean to authenticate your SilverStripe user against Auth0.

## What's Auth0?
[Auth0](https://auth0.com/) is an Identity Service provider. It allows you to easily set up things like Single Sign On 
and Social Login.

## Why use Auth0 with SilverStripe?
Let's say you want to quickly allow your users to register/login with Facebook or Google. With this module you can do 
this minutes.

Let's say you have dozens of websites you manage. With this module you can manage permissions for all your sites in 
Auth0 and have a single source of truth.

## Requirements
* silverstripe/framework: ^4.0
* auth0/auth0-php: ^5.1

## Installation and basic set up
The exact set up to get the module working can very quite a bit depending on what your use case. However, the following
steps will be starting point for all use case.

### Prerequisite
You'll need a functional SilverStripe site and an active administrator account with a valid email address.

In this set up, Auth0 completely takes over user identification. So if you don't have a pre-existing administrator 
account, you won't be able to log into your CMS afterwards.

If you disable this module, your original credentials will still work however.  

### Installing 
```bash
composer require maxime-rainville/silverstripe-auth0
```

### Setting up an Auth0 account and application
Follow the steps in the 
[SSO for Regular Web Apps: Auth0 Configuration](https://auth0.com/docs/architecture-scenarios/application/web-app-sso/part-2)
tutorial. Make sure you configure at least one connection type (e.g.: Facebook, Google, etc.)

When asked to provide a callback URL, enter `http://example.com/Security/login/callback`, replacing
`http://example.com/` with your SilverStripe web root URL.

Include all domains and protocol variations that will be using this Auth0 account for authentication.

On your Auth0 application settings page, you will be provided:
* a Auth0 domain ;
* a client id ;
* a client secret.

You'll need those in the next step.

### Configure your SilverStripe website 
Add the following keys to your SilverStripe `.env` file and enter the values from the previous step:
* AUTH0_DOMAIN ;
* AUTH0_CLIENT_ID ;
* AUTH0_CLIENT_SECRET.

Run a `dev/build` of your site and you should be all set up.

### Trying it out
If you try to access any page that requires authentication, rather than be redirected to the SilverStripe login screen,
you will be redirected to your Auth0 authentication screen. On their first login, user will be ask to grant your Auth0
application access to their account if they are using a social media provider.

Once users have agreed, they will be redirected to your site. If they have a pre-existing account on your
website (based on their email address), they will be logged in.

If the user doesn't have a pre-existing account, the default behavior is to deny access.

If you want to grant access to additional user, you just need to create Members in the CMS like you normally would and 
as long as their email matches the identity they provide to Auth0, they will be allowed in.

Note that the permissions for each user are still completely managed by SilverStripe in this set up. Auth0 is only 
acting as an identity provider.

## Alternative set up
The basic example is meant to be simple so you can build on top of it. To module is designed to be flexible so you can
easily personalise its behavior to your needs.

Here are a few sample use case and instruction on how to implement them. They are ordered in increasing level of
complexity.

1. Allow registration for anyone.
1. Allow registration if certain criteria are met.
1. Basic Authorisation provider via Auth0 rule.
1. Advanced authorisation provider via the 
[Auth0 Authorization Extension](https://auth0.com/docs/extensions/authorization-extension/v2)
