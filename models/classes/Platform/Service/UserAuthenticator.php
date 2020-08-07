<?php

namespace oat\taoLti\models\classes\Platform\Service;

use ErrorException;
use oat\generis\model\GenerisRdf;
use OAT\Library\Lti1p3Core\Security\User\UserAuthenticationResult;
use OAT\Library\Lti1p3Core\Security\User\UserAuthenticationResultInterface;
use OAT\Library\Lti1p3Core\Security\User\UserAuthenticatorInterface;
use OAT\Library\Lti1p3Core\User\UserIdentity;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\user\User;
use oat\oatbox\user\UserService;
use Throwable;

class UserAuthenticator extends ConfigurableService implements UserAuthenticatorInterface
{
    private const ANONYMOUS = '';

    public function authenticate(string $loginHint): UserAuthenticationResultInterface
    {
        if ($loginHint === self::ANONYMOUS) {
            return new UserAuthenticationResult(true);
        }

        try {
            return new UserAuthenticationResult(true, $this->getUserIdentity($loginHint));
        } catch (Throwable $exception) {
            return new UserAuthenticationResult(false);
        }
    }

    /**
     * @throws ErrorException
     */
    private function getUserIdentity(string $userId): UserIdentity
    {
        $user = $this->getUserService()
            ->getUser($userId);

        if (!$user instanceof User || empty($user->getRoles())) {
            throw new ErrorException(sprintf('User [%s] not found', $userId));
        }

        $fullName = (string)($user->getPropertyValues(GenerisRdf::PROPERTY_USER_FIRSTNAME)[0] ?? null)
            . ' ' . (string)($user->getPropertyValues(GenerisRdf::PROPERTY_USER_LASTNAME)[0] ?? null);

        $email = (string)($user->getPropertyValues(GenerisRdf::PROPERTY_USER_MAIL)[0] ?? null);

        return new UserIdentity($userId, $fullName, $email);
    }

    private function getUserService(): UserService
    {
        return $this->getServiceLocator()->get(UserService::SERVICE_ID);
    }
}
