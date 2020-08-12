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

namespace oat\taoLti\test\unit\models\classes\Tool;

use oat\oatbox\user\User;
use oat\taoLti\models\classes\LtiProvider\LtiProvider;
use oat\taoLti\models\classes\Tool\LtiLaunchCommand;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LtiLaunchCommandTest extends TestCase
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

    /** @var User|MockObject */
    private $user;

    /** @var LtiProvider|MockObject */
    private $ltiProvider;

    /** @var LtiLaunchCommand */
    private $subject;

    public function setUp(): void
    {
        $this->user = $this->createMock(User::class);
        $this->ltiProvider = $this->createMock(LtiProvider::class);
        $this->subject = new LtiLaunchCommand(
            $this->ltiProvider,
            self::ROLES,
            self::CLAIMS,
            self::RESOURCE_IDENTIFIER,
            $this->user,
            self::LOGIN_HINT,
            self::LAUNCH_URL
        );
    }

    public function testGetters(): void
    {
        $this->assertSame(self::RESOURCE_IDENTIFIER, $this->subject->getResourceIdentifier());
        $this->assertSame(self::ROLES, $this->subject->getRoles());
        $this->assertSame(self::CLAIMS, $this->subject->getClaims());
        $this->assertSame(self::LAUNCH_URL, $this->subject->getLaunchUrl());
        $this->assertSame(self::LOGIN_HINT, $this->subject->getOpenIdLoginHint());
        $this->assertSame($this->ltiProvider, $this->subject->getLtiProvider());
        $this->assertSame($this->user, $this->subject->getUser());
        $this->assertSame(false, $this->subject->isAnonymousLaunch());
        $this->assertSame(true, $this->subject->isOpenIdConnectLaunch());
    }
}
