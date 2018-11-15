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

namespace oat\taoLti\test\models\classes\user;

use oat\generis\test\TestCase;
use oat\taoLti\models\classes\LtiLaunchData;
use oat\taoLti\models\classes\user\LtiUserFactoryInterface;
use oat\taoLti\models\classes\user\LtiUserInterface;
use oat\taoLti\models\classes\user\LtiUserService;

class LtiUserServiceTest extends TestCase
{
    public function testSpawnUser()
    {
        /** @var LtiUserService $ltiUserService */
        $ltiUserService = $this->getMockBuilder(LtiUserService::class)
            ->setMethods(['updateUser', 'getOption'])
            ->getMockForAbstractClass();

        $ltiUserService
            ->method('getOption')
            ->willReturn('taoLti/LtiUserFactory');

        $mockFactory =  $this->getMockForAbstractClass(LtiUserFactoryInterface::class);
        $mockFactory
            ->method('create')
            ->willReturn($this->getMockForAbstractClass(LtiUserInterface::class));

        $ltiUserService->setServiceLocator(
            $this->getServiceLocatorMock([
                'taoLti/LtiUserFactory' => $mockFactory
            ])
        );

        $ltiContext = $this->getMockBuilder(LtiLaunchData::class)->disableOriginalConstructor()->getMock();
        $ltiContext
            ->method('getUserID')
            ->willReturn('1234');

        $this->assertInstanceOf(LtiUserInterface::class, $ltiUserService->spawnUser($ltiContext));

    }
}
