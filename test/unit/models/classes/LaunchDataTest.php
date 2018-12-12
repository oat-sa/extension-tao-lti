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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */
namespace oat\taoLti\models\classes;

use oat\generis\test\TestCase;
use Psr\Log\LoggerInterface;
use Prophecy\Argument;

class LaunchDataTest extends TestCase
{
    public function testInvalidReturnUrl()
    {
        $logger = $this->prophesize(LoggerInterface::class);
        $emptyLaunch = new LtiLaunchData([LtiLaunchData::LAUNCH_PRESENTATION_RETURN_URL => 'notAurl'], []);
        $emptyLaunch->setLogger($logger->reveal());
        $this->assertFalse($emptyLaunch->hasReturnUrl());
        $logger->warning("Invalid LTI Return URL 'notAurl'.", Argument::any())->shouldHaveBeenCalled();
    }

    public function testNoReturnUrl()
    {
        $logger = $this->prophesize(LoggerInterface::class);
        $emptyLaunch = new LtiLaunchData([], []);
        $emptyLaunch->setLogger($logger->reveal());
        $this->assertFalse($emptyLaunch->hasReturnUrl());
        $logger->warning(Argument::any(), Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testGoodReturnUrl()
    {
        $logger = $this->prophesize(LoggerInterface::class);
        $emptyLaunch = new LtiLaunchData([LtiLaunchData::LAUNCH_PRESENTATION_RETURN_URL => 'http://valid.url.com'], []);
        $emptyLaunch->setLogger($logger->reveal());
        $this->assertTrue($emptyLaunch->hasReturnUrl());
        $logger->warning(Argument::any(), Argument::any())->shouldNotHaveBeenCalled();
    }
}
