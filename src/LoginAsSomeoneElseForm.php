<?php
namespace MaximeRainville\Auth0;

use SilverStripe\Control\Controller;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\FormAction;
use SilverStripe\Security\Security;

/**
 * Simple form to get served up when an authenticated user tries to access an area they are not authorises for. Invites
 * them to logout and log back in as someone else.
 */
class LoginAsSomeoneElseForm extends Form
{

    /**
     * Instanciate a new LoginAsSomeoneElseForm.
     * @param Controller $controller
     * @param string $name
     * @param string $authenticator_class Name of the autenticator class associated to this form.
     */
    public function __construct($controller, $name, $authenticator_class)
    {

        $this->setController($controller);
        $fields = FieldList::create(
            HiddenField::create('BackURL', null, $_SERVER['REQUEST_URI']),
            HiddenField::create('forceRedirect', null, 1),
            HiddenField::create('AuthenticationMethod', null, $authenticator_class, $this)
        );
        $actions = FieldList::create(
            FormAction::create('forceLogin', _t(
                'SilverStripe\\Security\\Member.BUTTONLOGINOTHER',
                'Log in as someone else'
            ))
        );

        $this->setFormMethod('GET', true);

        parent::__construct(
            $controller,
            $name,
            $fields,
            $actions
        );

        $this->setFormAction(Security::logout_url());
    }
}
