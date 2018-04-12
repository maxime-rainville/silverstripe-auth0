<?php
namespace MaximeRainville\Auth0;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;

use SilverStripe\Security\LoginForm as SSLoginForm;

class LoginForm extends SSLoginForm {


    /**
     * Constructor
     *
     * @skipUpgrade
     * @param RequestHandler $controller The parent controller, necessary to
     *                               create the appropriate form action tag.
     * @param string $authenticatorClass Authenticator for this LoginForm
     * @param string $name The method on the controller that will return this
     *                     form object.
     * @param FieldList $fields All of the fields in the form - a
     *                                   {@link FieldList} of {@link FormField}
     *                                   objects.
     * @param FieldList|FormAction $actions All of the action buttons in the
     *                                     form - a {@link FieldList} of
     *                                     {@link FormAction} objects
     * @param bool $checkCurrentUser If set to TRUE, it will be checked if a
     *                               the user is currently logged in, and if
     *                               so, only a logout button will be rendered
     */
    public function __construct(
        $controller,
        $authenticatorClass,
        $name,
        $fields = null,
        $actions = null,
        $checkCurrentUser = true
    ) {
        $this->setController($controller);
        $this->authenticator_class = $authenticatorClass;

        if (!$fields) {
            $fields = $this->getFormFields();
        }
        if (!$actions) {
            $actions = $this->getFormActions();
        }

        // Reduce attack surface by enforcing POST requests
        $this->setFormMethod('POST', true);

        parent::__construct($controller, $name, $fields, $actions);
    }

    /**
     * Return the title of the form for use in the frontend
     * For tabs with multiple login methods, for example.
     * This replaces the old `get_name` method
     * @return string
     */
    public function getAuthenticatorName() {
        return 'Auth0 Authenticator';
    }

    /**
     * Required FieldList creation on a LoginForm
     *
     * @return FieldList
     */
    protected function getFormFields() {
        return FieldList::create(

        );
    }

    /**
     * Required FieldList creation for the login actions on this LoginForm
     *
     * @return FieldList
     */
    protected function getFormActions() {
        return FieldList::create(
            FormAction::create('Auth0', 'Auth0')
        );
    }

}
