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
use oat\oatbox\user\User;
use oat\taoLti\models\classes\LtiProvider\LtiProvider;
use oat\taoLti\models\classes\Tool\Factory\Lti1p3LaunchRequestFactory;
use oat\taoLti\models\classes\Tool\LtiLaunchCommand;
use oat\taoLti\models\classes\Tool\Service\Lti1p1Launcher;
use PHPUnit\Framework\MockObject\MockObject;

class Lti1p1LauncherTest extends TestCase
{
    private const LAUNCH_URL = 'launchUrl';
    private const LOGIN_HINT = 'loginHint';
    private const RESOURCE_IDENTIFIER = 'resourceIdentifier';
    private const CLAIMS = [
        'claim' => 'value'
    ];
    private const ROLES = [
        'Learner'
    ];

    /** @var Lti1p3LaunchRequestFactory|MockObject */
    private $launchRequestFactory;

    /** @var Lti1p1Launcher */
    private $subject;

    public function setUp(): void
    {
        $this->launchRequestFactory = $this->createMock(Lti1p3LaunchRequestFactory::class);
        $this->subject = new Lti1p1Launcher();
    }

    public function testLaunch(): void
    {
        $ltiProvider = $this->createMock(LtiProvider::class);

        $ltiProvider->method('getKey')
            ->willReturn('key');

        $ltiProvider->method('getSecret')
            ->willReturn('secret');

        $user = $this->createMock(User::class);

        $command = new LtiLaunchCommand(
            $ltiProvider,
            self::ROLES,
            self::CLAIMS,
            self::RESOURCE_IDENTIFIER,
            $user,
            self::LOGIN_HINT,
            self::LAUNCH_URL
        );

        $launch = $this->subject->launch($command);
        $launchParams = $launch->getToolLaunchParams();

        $this->assertSame(self::LAUNCH_URL, $launch->getToolLaunchUrl());
        $this->assertSame('1.0', $launchParams['oauth_version']);
        $this->assertSame('HMAC-SHA1', $launchParams['oauth_signature_method']);
        $this->assertSame('key', $launchParams['oauth_consumer_key']);
        $this->assertSame('LTI-1p0', $launchParams['lti_version']);
        $this->assertSame(null, $launchParams['user_id']);
        $this->assertSame('value', $launchParams['claim']);
        $this->assertSame('Learner', $launchParams['roles']);
    }
}
