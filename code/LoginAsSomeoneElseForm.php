<?php
namespace MaximeRainville\Auth0;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\FormAction;
use SilverStripe\Security\Security;
use SilverStripe\Control\Director;

class LoginAsSomeoneElseForm extends Form
{

    public function __construct($controller, $name, $authenticator_class)
    {

        $this->setController($controller);
        $fields = FieldList::create(
            HiddenField::create('BackURL', null, $_SERVER['REQUEST_URI']),
            HiddenField::create('forceRedirect', null, 1)
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
