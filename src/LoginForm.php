<?php

namespace MaximeRainville\Auth0;

use SilverStripe\Control\RequestHandler;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Security\LoginForm as SSLoginForm;

/**
 * Handle login requests from MaximeRainville\Auth0\Authenticator.
 *
 * @internal Mostly copied from the regular LoginHandler. Unfortunately, the regular login handler is tightly coupled
 * with the MemberLoginForm, which makes our work a bit more difficult here.
 */
class LoginForm extends SSLoginForm
{

    /**
     * @var URL where the user should be redirected to after a successfull login.
     */
    protected $backURL;

    public function __construct(
        RequestHandler $controller = null,
        $name,
        $backURL
    )
    {
        $this->backURL = $backURL;
        parent::__construct($controller, $name, $this->getFormFields(), $this->getFormActions());
    }

    public function FormName()
    {
        return 'Auth0LoginForm';
    }

    /**
     * Return the title of the form for use in the frontend
     * For tabs with multiple login methods, for example.
     * This replaces the old `get_name` method
     * @return string
     */
    public function getAuthenticatorName()
    {
        return _t(self::class . '.AuthenticatorName', 'Auth0');
    }

    /**
     * Required FieldList creation on a LoginForm
     *
     * @return FieldList
     */
    protected function getFormFields()
    {
        return  FieldList::create([
            HiddenField::create('BackURL')->setValue($this->backURL)
        ]);
    }

    /**
     * Required FieldList creation for the login actions on this LoginForm
     *
     * @return FieldList
     */
    protected function getFormActions()
    {
        return FieldList::create([
            FormAction::create(
                'doLogin',
                _t(self::class . '.Action', 'Login with Auth0')
            )]
        );
    }

}
