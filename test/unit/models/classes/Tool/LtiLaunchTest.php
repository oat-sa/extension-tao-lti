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

use oat\taoLti\models\classes\Tool\LtiLaunch;
use PHPUnit\Framework\TestCase;

class LtiLaunchTest extends TestCase
{
    private const LAUNCH_URL = 'launchUrl';
    private const LAUNCH_PARAMS = [
        'some' => 'thing'
    ];

    /** @var LtiLaunch */
    private $subject;

    public function setUp(): void
    {
        $this->subject = new LtiLaunch(
            self::LAUNCH_URL,
            self::LAUNCH_PARAMS
        );
    }

    public function testGetters(): void
    {
        $this->assertSame(self::LAUNCH_URL, $this->subject->getToolLaunchUrl());
        $this->assertSame(self::LAUNCH_PARAMS, $this->subject->getToolLaunchParams());
        $this->assertSame(self::LAUNCH_URL . '?some=thing', $this->subject->getToolLaunchUrlWithParams());
    }
}
