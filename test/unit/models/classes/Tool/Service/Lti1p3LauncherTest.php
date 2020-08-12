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

use oat\generis\test\TestCase;
use OAT\Library\Lti1p3Core\Launch\Request\LtiLaunchRequest;
use oat\taoLti\models\classes\Tool\Factory\Lti1p3LaunchRequestFactory;
use oat\taoLti\models\classes\Tool\LtiLaunch;
use oat\taoLti\models\classes\Tool\LtiLaunchCommand;
use oat\taoLti\models\classes\Tool\Service\Lti1p3Launcher;
use PHPUnit\Framework\MockObject\MockObject;

class Lti1p3LauncherTest extends TestCase
{
    private const LAUNCH_URL = 'launchUrl';
    private const LAUNCH_PARAMS = [
        'some' => 'thing'
    ];

    /** @var Lti1p3LaunchRequestFactory|MockObject */
    private $launchRequestFactory;

    /** @var Lti1p3Launcher */
    private $subject;

    public function setUp(): void
    {
        $this->launchRequestFactory = $this->createMock(Lti1p3LaunchRequestFactory::class);
        $this->subject = new Lti1p3Launcher();
        $this->subject->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    Lti1p3LaunchRequestFactory::class => $this->launchRequestFactory
                ]
            )
        );
    }

    public function testLaunch(): void
    {
        $command = $this->createMock(LtiLaunchCommand::class);
        $launchRequest = new LtiLaunchRequest(self::LAUNCH_URL, self::LAUNCH_PARAMS);

        $this->launchRequestFactory
            ->method('create')
            ->willReturn($launchRequest);

        $this->assertEquals(
            new LtiLaunch(self::LAUNCH_URL, self::LAUNCH_PARAMS),
            $this->subject->launch($command)
        );
    }
}
