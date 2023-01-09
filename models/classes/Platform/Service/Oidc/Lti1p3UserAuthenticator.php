<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoLti\models\classes\Platform\Service\Oidc;

use ErrorException;
use oat\generis\model\user\UserRdf;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use OAT\Library\Lti1p3Core\Security\User\Result\UserAuthenticationResult;
use OAT\Library\Lti1p3Core\Security\User\Result\UserAuthenticationResultInterface;
use OAT\Library\Lti1p3Core\Security\User\UserAuthenticatorInterface;
use OAT\Library\Lti1p3Core\User\UserIdentity;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\user\User;
use oat\oatbox\user\UserService;
use Throwable;

class Lti1p3UserAuthenticator extends ConfigurableService implements UserAuthenticatorInterface
{
    public function authenticate(
        RegistrationInterface $registration,
        string $loginHint
    ): UserAuthenticationResultInterface {
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

        if (!$user instanceof User) {
            throw new ErrorException(sprintf('User [%s] not found', $userId));
        }

        $login = $this->getPropertyValue($user, UserRdf::PROPERTY_LOGIN);
        $login = empty($login) ? $userId : $login;

        $fullName = $this->getPropertyValue($user, UserRdf::PROPERTY_FIRSTNAME)
            . ' ' . $this->getPropertyValue($user, UserRdf::PROPERTY_LASTNAME);

        $email = $this->getPropertyValue($user, UserRdf::PROPERTY_MAIL);

        return new UserIdentity($login, trim($fullName), $email);
    }

    private function getPropertyValue(User $user, string $propertyName): string
    {
        return (string)($user->getPropertyValues($propertyName)[0] ?? null);
    }

    private function getUserService(): UserService
    {
        return $this->getServiceLocator()->get(UserService::SERVICE_ID);
    }
}
