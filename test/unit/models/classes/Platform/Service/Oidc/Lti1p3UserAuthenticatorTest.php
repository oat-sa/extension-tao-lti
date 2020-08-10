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

use oat\generis\test\TestCase;
use OAT\Library\Lti1p3Core\Security\User\UserAuthenticationResult;
use OAT\Library\Lti1p3Core\User\UserIdentity;
use oat\oatbox\user\User;
use oat\oatbox\user\UserService;
use oat\taoLti\models\classes\Platform\Service\Oidc\Lti1p3UserAuthenticator;
use PHPUnit\Framework\MockObject\MockObject;

class Lti1p3UserAuthenticatorTest extends TestCase
{
    /** @var Lti1p3UserAuthenticator */
    private $subject;

    /** @var UserService|MockObject */
    private $userService;

    public function setUp(): void
    {
        $this->userService = $this->createMock(UserService::class);
        $this->subject = new Lti1p3UserAuthenticator();
        $this->subject->setServiceLocator(
            $this->getServiceLocatorMock(
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
            'first name',
            'last name',
            'email'
        );

        $this->assertEquals(
            new UserAuthenticationResult(
                true,
                new UserIdentity(
                    'login',
                    'first name last name',
                    'email'
                )
            ),
            $this->subject->authenticate('login')
        );
    }

    public function testAuthenticateUserAnonymous(): void
    {
        $this->assertEquals(
            new UserAuthenticationResult(true),
            $this->subject->authenticate('')
        );
    }

    public function testAuthenticateUserWithoutRolesIsNotAllowed(): void
    {
        $this->expectUser(
            [],
            'first name',
            'last name',
            'email'
        );

        $this->assertEquals(
            new UserAuthenticationResult(false),
            $this->subject->authenticate('login')
        );
    }

    private function expectUser(array $roles, string $firstName, string $lastName, string $email): void
    {
        $user = $this->createMock(User::class);

        $user->method('getRoles')
            ->willReturn($roles);

        $user->method('getPropertyValues')
            ->willReturnOnConsecutiveCalls(
                [$firstName],
                [$lastName],
                [$email]
            );

        $this->userService
            ->method('getUser')
            ->willReturn($user);
    }
}
