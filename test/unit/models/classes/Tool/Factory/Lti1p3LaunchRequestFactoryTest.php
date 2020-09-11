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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

declare(strict_types=1);

namespace oat\taoLti\test\unit\models\classes\Tool\Factory;

use ErrorException;
use oat\generis\test\TestCase;
use OAT\Library\Lti1p3Core\Launch\Builder\LtiLaunchRequestBuilder;
use OAT\Library\Lti1p3Core\Launch\Builder\OidcLaunchRequestBuilder;
use OAT\Library\Lti1p3Core\Launch\Request\LtiLaunchRequest;
use OAT\Library\Lti1p3Core\Launch\Request\OidcLaunchRequest;
use OAT\Library\Lti1p3Core\Link\ResourceLink\ResourceLink;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use OAT\Library\Lti1p3Core\Registration\RegistrationRepositoryInterface;
use OAT\Library\Lti1p3Core\User\UserIdentity;
use oat\oatbox\user\User;
use oat\taoLti\models\classes\LtiProvider\LtiProvider;
use oat\taoLti\models\classes\Platform\Repository\Lti1p3RegistrationRepository;
use oat\taoLti\models\classes\Tool\Factory\Lti1p3LaunchRequestFactory;
use oat\taoLti\models\classes\Tool\LtiLaunchCommand;
use PHPUnit\Framework\MockObject\MockObject;

class Lti1p3LaunchRequestFactoryTest extends TestCase
{
    /** @var RegistrationRepositoryInterface|MockObject */
    private $registrationRepository;

    /** @var Lti1p3LaunchRequestFactory */
    private $subject;

    /** @var OidcLaunchRequestBuilder|MockObject */
    private $oidcLaunchRequestBuilder;

    /** @var LtiLaunchRequestBuilder|MockObject */
    private $ltiLaunchRequestBuilder;

    public function setUp(): void
    {
        $this->registrationRepository = $this->createMock(RegistrationRepositoryInterface::class);
        $this->oidcLaunchRequestBuilder = $this->createMock(OidcLaunchRequestBuilder::class);
        $this->ltiLaunchRequestBuilder = $this->createMock(LtiLaunchRequestBuilder::class);
        $this->subject = new Lti1p3LaunchRequestFactory();
        $this->subject->withLtiLaunchRequestBuilder($this->ltiLaunchRequestBuilder);
        $this->subject->withOidcLaunchRequestBuilder($this->oidcLaunchRequestBuilder);
        $this->subject->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    Lti1p3RegistrationRepository::SERVICE_ID => $this->registrationRepository
                ]
            )
        );
    }

    public function testWillThrowExceptionIfRegistrationNotFound(): void
    {
        $this->registrationRepository
            ->method('find')
            ->willReturn(null);

        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('Registration for provider ltiId not found');

        $this->subject->create($this->createCommand($this->expectUser()));
    }

    public function testCreateDirectRequest(): void
    {
        $registration = $this->expectRegistration();
        $command = $this->createCommand($this->expectUser());

        $launchRequest = $this->createMock(LtiLaunchRequest::class);

        $this->ltiLaunchRequestBuilder
            ->method('buildUserResourceLinkLtiLaunchRequest')
            ->with(
                new ResourceLink('deliveryExecutionIdentifier', $command->getLaunchUrl()),
                $registration,
                new UserIdentity(
                    'userIdentifier',
                    'user name',
                    'user@email.com'
                ),
                '1',
                [
                    'Learner'
                ],
                [
                    'deliveryExecutionId' => 'deliveryExecutionIdentifier'
                ]
            )
            ->willReturn($launchRequest);

        $this->assertEquals($launchRequest, $this->subject->create($command));
    }

    public function testCreateAnonymousRequest(): void
    {
        $registration = $this->expectRegistration();
        $command = $this->createCommand();

        $launchRequest = $this->createMock(LtiLaunchRequest::class);

        $this->ltiLaunchRequestBuilder
            ->method('buildResourceLinkLtiLaunchRequest')
            ->with(
                new ResourceLink('deliveryExecutionIdentifier', $command->getLaunchUrl()),
                $registration,
                '1',
                [
                    'Learner'
                ],
                [
                    'deliveryExecutionId' => 'deliveryExecutionIdentifier'
                ]
            )
            ->willReturn($launchRequest);

        $this->assertEquals($launchRequest, $this->subject->create($command));
    }

    public function testCreateOidcRequest(): void
    {
        $registration = $this->expectRegistration();
        $command = $this->createCommand($this->expectUser(), 'userIdentifier');

        $launchRequest = $this->createMock(OidcLaunchRequest::class);

        $this->oidcLaunchRequestBuilder
            ->method('buildResourceLinkOidcLaunchRequest')
            ->with(
                new ResourceLink('deliveryExecutionIdentifier', $command->getLaunchUrl()),
                $registration,
                'userIdentifier',
                '1',
                [
                    'Learner'
                ],
                [
                    'deliveryExecutionId' => 'deliveryExecutionIdentifier'
                ]
            )
            ->willReturn($launchRequest);

        $this->assertEquals($launchRequest, $this->subject->create($command));
    }

    private function createCommand(User $user = null, string $openIdLoginHint = null): LtiLaunchCommand
    {
        $ltiProvider = $this->createMock(LtiProvider::class);

        $ltiProvider->method('getId')
            ->willReturn('ltiId');

        return new LtiLaunchCommand(
            $ltiProvider,
            [
                'Learner'
            ],
            [
                'deliveryExecutionId' => 'deliveryExecutionIdentifier'
            ],
            'deliveryExecutionIdentifier',
            $user,
            $openIdLoginHint,
            'launchUrl'
        );
    }

    private function expectRegistration(): RegistrationInterface
    {
        $registration = $this->createMock(RegistrationInterface::class);

        $registration->method('getDefaultDeploymentId')
            ->willReturn('1');

        $this->registrationRepository
            ->method('find')
            ->willReturn($registration);

        return $registration;
    }

    private function expectUser(): User
    {
        $user = $this->createMock(User::class);

        $user->method('getIdentifier')
            ->willReturn('userIdentifier');

        $user->method('getPropertyValues')
            ->willReturnOnConsecutiveCalls(
                ['user'],
                ['name'],
                ['user@email.com']
            );

        return $user;
    }
}
