<?php
namespace MaximeRainville\Auth0\Extensions;

use MaximeRainville\Auth0\Utilities\UserMetaData;
use SilverStripe\Core\Extension;
use SilverStripe\Security\Group;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;

/**
 * Extension to the Auth0 Login Handler to read and update permission for the provided user.
 *
 * This is designed to work with the
 * [Auth0 Authorization Extension](https://auth0.com/docs/extensions/authorization-extension/v2)
 *
 */
class LoginHandlerAuthorizationExtension extends Extension
{

    /**
     * @var string
     */
    protected $namespace;

    /**
     * LoginHandlerAuthorizationExtension constructor.
     * Namespace where our special properties will be stored.
     * @param string $namespace Namespace you use to append permissions, roles and groups to the user metadata.
     */
    public function __construct($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * We let Auth0 handle everything so we'll allow registration, if Auth0 is happy.
     * @param $userInfo
     * @return bool
     */
    public function allowUserToRegister($userInfo)
    {
        $acl = new UserMetaData($userInfo, $this->namespace);
        return $acl->hasAclDefined();
    }

    /**
     * Update the user's permission after they log in.
     * @param Member $member
     * @param array $userInfo
     */
    public function updateMember(Member $member, $userInfo)
    {
        $acl = new UserMetaData($userInfo, $this->namespace);

        // Set up a a group to old this user's individual permsission.
        $code = 'auth-zero-group-' . $member->ID;
        $group = Group::get()->filter('Code', $code)->first();

        if (!$group) {
            $group = Group::create();
        }

        $group->update([
            'Title' => 'Individual Group for ' . $member->Title,
            'Code' => $code,
        ])->write();

        $group->DirectMembers()->add($member);
        $parent = $this->getParentGroup();
        $parent->Groups()->add($group);

        // Apply new permission to user
        $currentPermissions = $group->Permissions()->column('Code');
        $auth0Permissions = $acl->getPermissions();
        $toDeny = array_diff($currentPermissions, $auth0Permissions);
        $toAllow = array_diff($auth0Permissions, $currentPermissions);

        foreach ($toDeny as $code) {
            Permission::deny($group->ID, $code);
        }

        foreach ($toAllow as $code) {
            Permission::grant($group->ID, $code);
        }

    }

    /**
     * Return a group that encapsulate all the individual groups.
     * @return Group
     */
    protected function getParentGroup()
    {
        $code = 'auth-zero-master-group';
        $group = Group::get()->filter('Code', $code)->first();

        if (!$group) {
            $group = Group::create();
        }

        $group->update([
            'Title' => 'Master Auth0 Group',
            'Code' => $code,
        ])->write();

        return $group;
    }


}

