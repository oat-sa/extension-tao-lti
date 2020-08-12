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

namespace oat\taoLti\test\unit\models\classes\Tool;

use LogicException;
use oat\generis\test\TestCase;
use oat\taoLti\models\classes\LtiProvider\LtiProvider;
use oat\taoLti\models\classes\Tool\LtiLaunch;
use oat\taoLti\models\classes\Tool\LtiLaunchCommand;
use oat\taoLti\models\classes\Tool\LtiLaunchCommandInterface;
use oat\taoLti\models\classes\Tool\Service\Lti1p1Launcher;
use oat\taoLti\models\classes\Tool\Service\Lti1p3Launcher;
use oat\taoLti\models\classes\Tool\Service\LtiLauncherInterface;
use oat\taoLti\models\classes\Tool\Service\LtiLauncherProxy;
use PHPUnit\Framework\MockObject\MockObject;

class LtiLauncherProxyTest extends TestCase
{
    private const LAUNCH_URL = 'launchUrl';
    private const LAUNCH_PARAMS = [
        'some' => 'thing'
    ];

    /** @var LtiLauncherInterface|MockObject */
    private $lti1p1Launcher;

    /** @var LtiLauncherInterface|MockObject */
    private $lti1p3Launcher;

    /** @var Lti1p3Launcher */
    private $subject;

    public function setUp(): void
    {
        $this->lti1p1Launcher = $this->createMock(Lti1p3Launcher::class);
        $this->lti1p3Launcher = $this->createMock(Lti1p1Launcher::class);
        $this->subject = new LtiLauncherProxy();
        $this->subject->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    Lti1p3Launcher::class => $this->lti1p3Launcher,
                    Lti1p1Launcher::class => $this->lti1p1Launcher
                ]
            )
        );
    }

    public function testLti1p1Launch(): void
    {
        $command = $this->createCommand('1.1');

        $launch = new LtiLaunch(self::LAUNCH_URL, self::LAUNCH_PARAMS);

        $this->lti1p1Launcher
            ->method('launch')
            ->willReturn($launch);

        $this->assertEquals($launch, $this->subject->launch($command));
    }

    public function testLti1p3Launch(): void
    {
        $command = $this->createCommand('1.3');

        $launch = new LtiLaunch(self::LAUNCH_URL, self::LAUNCH_PARAMS);

        $this->lti1p3Launcher
            ->method('launch')
            ->willReturn($launch);

        $this->assertEquals($launch, $this->subject->launch($command));
    }


    public function testLtiInvalidLaunchWillThrowException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('LTI version 1.4 is not supported');

        $this->subject->launch($this->createCommand('1.4'));
    }

    private function createCommand(string $ltiVersion): LtiLaunchCommandInterface
    {
        $command = $this->createMock(LtiLaunchCommand::class);
        $ltiProvider = $this->createMock(LtiProvider::class);

        $command->method('getLtiProvider')
            ->willReturn($ltiProvider);

        $ltiProvider->method('getLtiVersion')
            ->willReturn($ltiVersion);

        return $command;
    }
}
