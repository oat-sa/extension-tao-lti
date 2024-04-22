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

namespace oat\taoLti\test\unit\models\classes\Platform\Service\Oidc;

use core_kernel_users_GenerisUser;
use oat\generis\model\user\UserRdf;
use oat\generis\test\ServiceManagerMockTrait;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use OAT\Library\Lti1p3Core\Security\User\Result\UserAuthenticationResult;
use OAT\Library\Lti1p3Core\User\UserIdentity;
use oat\oatbox\user\UserService;
use oat\taoLti\models\classes\Platform\Service\Oidc\Lti1p3UserAuthenticator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class Lti1p3UserAuthenticatorTest extends TestCase
{
    use ServiceManagerMockTrait;

    private const LOGIN_HINT = 'userId#123456';

    /** @var Lti1p3UserAuthenticator */
    private $subject;

    /** @var UserService|MockObject */
    private $userService;

    public function setUp(): void
    {
        $this->userService = $this->createMock(UserService::class);
        $this->subject = new Lti1p3UserAuthenticator();
        $this->subject->setServiceLocator(
            $this->getServiceManagerMock(
                [
                    UserService::SERVICE_ID => $this->userService
                ]
            )
        );
    }

    public function testAuthenticateUser(): void
    {
        $this->expectUser(
            [
                'role'
            ],
            'login',
            'first name',
            'last name',
            'email',
            'en-US'
        );

        /** @var RegistrationInterface|MockObject $registration */
        $registration = $this->createMock(RegistrationInterface::class);

        $this->assertEquals(
            new UserAuthenticationResult(
                true,
                new UserIdentity(
                    'login',
                    'first name last name',
                    'email',
                    'first name',
                    'last name',
                    null,
                    'en-US'
                )
            ),
            $this->subject->authenticate($registration, self::LOGIN_HINT)
        );
    }

    public function testAnonymousOrGuestUser(): void
    {
        $this->expectAnonymousUser(
            [
                'role'
            ]
        );

        /** @var RegistrationInterface|MockObject $registration */
        $registration = $this->createMock(RegistrationInterface::class);

        $this->assertEquals(
            new UserAuthenticationResult(
                true,
                new UserIdentity(
                    self::LOGIN_HINT,
                    '',
                    ''
                )
            ),
            $this->subject->authenticate($registration, self::LOGIN_HINT)
        );
    }

    public function testAnonymousWithoutLoginHintData(): void
    {
        $this->expectAnonymousUser(
            [
                'role'
            ]
        );

        /** @var RegistrationInterface|MockObject $registration */
        $registration = $this->createMock(RegistrationInterface::class);

        $this->assertEquals(
            new UserAuthenticationResult(
                true,
                null
            ),
            $this->subject->authenticate($registration, '')
        );
    }

    private function expectUser(
        array $roles,
        string $login,
        string $firstName,
        string $lastName,
        string $email,
        string $locale
    ): void {
        $user = $this->createMock(core_kernel_users_GenerisUser::class);

        $user->method('getRoles')
            ->willReturn($roles);

        $user->method('getPropertyValues')
            ->withConsecutive(
                [UserRdf::PROPERTY_LOGIN],
                [UserRdf::PROPERTY_FIRSTNAME],
                [UserRdf::PROPERTY_LASTNAME],
                [UserRdf::PROPERTY_MAIL],
                [UserRdf::PROPERTY_DEFLG]
            )
            ->willReturnOnConsecutiveCalls(
                [$login],
                [$firstName],
                [$lastName],
                [$email],
                [$locale],
            );

        $this->userService
            ->method('getUser')
            ->willReturn($user);
    }

    private function expectAnonymousUser(array $roles): void
    {
        $user = $this->createMock(core_kernel_users_GenerisUser::class);

        $user->method('getRoles')
            ->willReturn($roles);

        $user->method('getPropertyValues')
            ->willReturn([]);

        $this->userService
            ->method('getUser')
            ->willReturn($user);
    }
}
