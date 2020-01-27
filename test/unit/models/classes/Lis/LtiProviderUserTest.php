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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 */

namespace oat\taoLti\test\unit\models\classes\Lis;

use oat\generis\test\MockObject;
use oat\generis\test\TestCase;
use oat\taoLti\models\classes\Lis\LtiProviderUser;
use oat\taoLti\models\classes\LtiProvider\LtiProvider;

class LtiProviderUserTest extends TestCase
{
    public function testCreation()
    {
        /** @var LtiProvider|MockObject $ltiProviderMock */
        $ltiProviderMock = $this->createMock(LtiProvider::class);
        $ltiProviderMock->method('getId')->willReturn('id1');
        $user = new LtiProviderUser($ltiProviderMock);
        $this->assertSame($ltiProviderMock, $user->getLtiProvider());
    }

    public function testUniqueId()
    {
        /** @var LtiProvider|MockObject $ltiProviderMock1 */
        $ltiProviderMock1 = $this->createMock(LtiProvider::class);
        $ltiProviderMock1->method('getId')->willReturn('id1');

        /** @var LtiProvider|MockObject $ltiProviderMock2 */
        $ltiProviderMock2 = $this->createMock(LtiProvider::class);
        $ltiProviderMock2->method('getId')->willReturn('id2');

        /** @var LtiProvider|MockObject $ltiProviderMock3 */
        $ltiProviderMock3 = $this->createMock(LtiProvider::class);
        $ltiProviderMock3->method('getId')->willReturn('id3');

        $user1 = new LtiProviderUser($ltiProviderMock1);
        $user2 = new LtiProviderUser($ltiProviderMock2);
        $user3 = new LtiProviderUser($ltiProviderMock3);
        $ids = array_unique([$user1->getIdentifier(), $user2->getIdentifier(), $user3->getIdentifier()]);
        $this->assertCount(3, $ids);
    }
}
