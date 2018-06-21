<?php
namespace MaximeRainville\Auth0;

use SilverStripe\Security\Authenticator as SSAuthenticator;
use SilverStripe\Security\Member;
use SilverStripe\Core\Extensible;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\ORM\ValidationResult;


class Authenticator implements SSAuthenticator
{

    use Extensible;

    /**
     * @inheritdoc
     * @return int
     */
    public function supportedServices()
    {
        // Bitwise-OR of all the supported services in this Authenticator, to make a bitmask
        return SSAuthenticator::LOGIN | SSAuthenticator::LOGOUT;
    }


    /**
     * @inheritdoc
     * @param array $data
     * @param HTTPRequest $request
     * @param ValidationResult|null $result
     * @return null|Member
     */
    public function authenticate(array $data, HTTPRequest $request, ValidationResult &$result = null)
    {
        // Not compatible with auth0 authentication.
        return null;
    }

    /**
     * Check if the passed password matches the stored one (if the member is not locked out).
     *
     * Note, we don't return early, to prevent differences in timings to give away if a member
     * password is invalid.
     *
     * @param Member $member
     * @param string $password
     * @param ValidationResult $result
     * @return ValidationResult
     */
    public function checkPassword(Member $member, $password, ValidationResult &$result = null)
    {
        if ($result === null) {
            $result = new ValidationResult();
        }

        $result->addError(_t(
            static::class . '.CHECK_PASSWORD',
            'Can not check password with Auth0 authenticator.'
        ));

        return $result;
    }

    /**
     * @inheritdoc
     * @param string $link
     * @return mixed
     */
    public function getLostPasswordHandler($link)
    {
        // We don't support lost password
        return null;
    }

    /**
     * @inheritdoc
     * @param string $link
     * @return mixed
     */
    public function getChangePasswordHandler($link)
    {
        // Not needed
        return null;
    }

    /**
     * @inheritdoc
     * @param string $link
     * @return LoginHandler
     */
    public function getLoginHandler($link)
    {
        return LoginHandler::create($link, $this);
    }

    /**
     * @inheritdoc
     * @param string $link
     * @return LogoutHandler
     */
    public function getLogoutHandler($link)
    {
        return LogoutHandler::create($link, $this);
    }

}
