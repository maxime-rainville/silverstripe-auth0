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

    public function supportedServices()
    {
        // Bitwise-OR of all the supported services in this Authenticator, to make a bitmask
        return Authenticator::LOGIN | Authenticator::LOGOUT;
    }

    public function authenticate(array $data, HTTPRequest $request, ValidationResult &$result = null)
    {
        // Find authenticated member
        $member = $this->authenticateMember($data, $result);

        // Optionally record every login attempt as a {@link LoginAttempt} object
        $this->recordLoginAttempt($data, $request, $member, $result->isValid());

        if ($member) {
            $request->getSession()->clear('BackURL');
        }

        return $result->isValid() ? $member : null;
    }

    /**
     * Attempt to find and authenticate member if possible from the given data
     *
     * @skipUpgrade
     * @param array $data Form submitted data
     * @param ValidationResult $result
     * @param Member $member This third parameter is used in the CMSAuthenticator(s)
     * @return Member Found member, regardless of successful login
     */
    protected function authenticateMember($data, ValidationResult &$result = null, Member $member = null)
    {

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
        // Will always fail
    }

    /**
     * @param string $link
     * @return LostPasswordHandler
     */
    public function getLostPasswordHandler($link)
    {
        // Should always fail
        return LostPasswordHandler::create($link, $this);
    }

    /**
     * @param string $link
     * @return ChangePasswordHandler
     */
    public function getChangePasswordHandler($link)
    {
        // Should always fail
        return ChangePasswordHandler::create($link, $this);
    }

    /**
     * @param string $link
     * @return LoginHandler
     */
    public function getLoginHandler($link)
    {
        return LoginHandler::create($link, $this);
    }

    /**
     * @param string $link
     * @return LogoutHandler
     */
    public function getLogoutHandler($link)
    {
        return LogoutHandler::create($link, $this);
    }

}
