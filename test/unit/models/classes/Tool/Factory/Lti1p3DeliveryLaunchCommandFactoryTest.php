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

use oat\generis\test\TestCase;
use oat\oatbox\session\SessionService;
use oat\oatbox\user\User;
use oat\taoDelivery\model\execution\DeliveryExecution;
use oat\taoLti\models\classes\LtiProvider\LtiProvider;
use oat\taoLti\models\classes\Tool\LtiLaunchCommand;
use oat\taoLtiConsumer\model\Tool\Factory\Lti1p3DeliveryLaunchCommandFactory;
use PHPUnit\Framework\MockObject\MockObject;

class Lti1p3DeliveryLaunchCommandFactoryTest extends TestCase
{
    /** @var SessionService|MockObject */
    private $sessionService;

    /** @var Lti1p3DeliveryLaunchCommandFactory */
    private $subject;

    public function setUp(): void
    {
        $this->sessionService = $this->createMock(SessionService::class);
        $this->subject = new Lti1p3DeliveryLaunchCommandFactory();
        $this->subject->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    SessionService::SERVICE_ID => $this->sessionService
                ]
            )
        );
    }

    public function testCreate(): void
    {
        $execution = $this->createMock(DeliveryExecution::class);

        $execution->method('getIdentifier')
            ->willReturn('deliveryExecutionIdentifier');

        $ltiProvider = $this->createMock(LtiProvider::class);

        $user = $this->createMock(User::class);

        $user->method('getIdentifier')
            ->willReturn('userIdentifier');

        $this->sessionService
            ->method('getCurrentUser')
            ->willReturn($user);

        $expectedCommand = new LtiLaunchCommand(
            $ltiProvider,
            [
                'Learner'
            ],
            [
                'deliveryExecutionId' => 'deliveryExecutionIdentifier'
            ],
            'deliveryExecutionIdentifier',
            $user,
            'userIdentifier',
            'launchUrl'
        );

        $config = [
            'launchUrl' => 'launchUrl',
            'ltiProvider' => $ltiProvider,
            'deliveryExecution' => $execution
        ];

        $this->assertEquals($expectedCommand, $this->subject->create($config));
    }
}
