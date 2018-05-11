<?php
namespace MaximeRainville\Auth0\Utilities;

/**
 * Simple wrapper object around the user ACL info Auth0 is sending back to us.
 */
class UserMetaData
{

    private $permissions = [];

    private $groups = [];

    private $roles = [];

    /**
     * UserMetaData constructor.
     * @param array $userinfo Info comming back from Auth0.
     * @param string $namespace Namespace where we should look for ACL info.
     */
    public function __construct(array $userinfo, $namespace)
    {
        if (isset($userinfo[$namespace . 'permissions'])) {
            $this->permissions = $userinfo[$namespace . 'permissions'];
        }

        if (isset($userinfo[$namespace . 'groups'])) {
            $this->groups = $userinfo[$namespace . 'groups'];
        }

        if (isset($userinfo[$namespace . 'roles'])) {
            $this->roles = $userinfo[$namespace . 'roles'];
        }
    }

    /**
     * @return string[]
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @return string[]
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @return string[]
     */
    public function getRoles()
    {
        $this->roles;
    }


    /**
     * Whatever the userinfo has any ACL information attached to it.
     * @return bool
     */
    public function hasAclDefined()
    {
        return ! (
            empty($this->permissions) &&
            empty($this->groups) &&
            empty($this->roles)
        );
    }

}
