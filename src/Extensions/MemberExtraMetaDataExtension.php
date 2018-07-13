<?php
namespace MaximeRainville\Auth0\Extensions;

use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;

/**
 * Extension to Member to adapt it to support additional Auth0 field.
 *
 * @property string $Auth0Sub Identity string provided by Auth0.
 * @property string $Auth0Picture URL to an image provided by Auth0.
 */
class MemberExtraMetaDataExtension extends DataExtension
{

    private static $db = [
        'Auth0Sub' => 'Varchar(255)',
        'Auth0Picture' => 'Varchar(255)',
    ];


    /**
     * Receives auth0 data and apply them to this Member.
     * @param array $userinfo
     * @return void
     */
    public function EnrichAuth0Profile(array $userinfo)
    {
        /**
         * var Member
         */
        $owner = $this->getOwner();
        $owner->Auth0Sub = $userinfo['sub'];
        $owner->Auth0Picture = $userinfo['picture'];
    }

    /**
     * Remove and disabled fields that are no longer relevant when using Auth0.
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName('Auth0Sub');
        $fields->removeByName('Auth0Picture');

        $owner = $this->getOwner();
        if ($owner->Auth0Sub) {
            $fields->removeByName('Password');
            $fields->removeByName('DirectGroups');
            $fields->fieldByName('Root.Main.Email')
                ->setReadonly(true)
                ->setDisabled(true);
            $fields->fieldByName('Root.Main.FirstName')
                ->setReadonly(true)
                ->setDisabled(true);
            $fields->fieldByName('Root.Main.Surname')
                ->setReadonly(true)
                ->setDisabled(true);
        }
    }
}
