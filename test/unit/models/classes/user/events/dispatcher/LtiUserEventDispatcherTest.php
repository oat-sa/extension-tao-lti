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

namespace oat\taoLti\test\models\classes\user\events\dispatcher;

use oat\generis\test\MockObject;
use oat\generis\test\TestCase;
use oat\oatbox\event\EventManager;
use oat\taoLti\models\classes\LtiRoles;
use oat\taoLti\models\classes\user\events\dispatcher\LtiUserEventDispatcher;
use oat\taoLti\models\classes\user\LtiUserInterface;

class LtiUserEventDispatcherTest extends TestCase
{
    /** @var LtiUserEventDispatcher */
    private $subject;

    /** @var EventManager|MockObject */
    private $eventManager;

    public function setUp(): void
    {
        $this->eventManager = $this->createMock(EventManager::class);

        $this->subject = new LtiUserEventDispatcher();
        $this->subject->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    EventManager::SERVICE_ID => $this->eventManager
                ]
            )
        );
    }

    public function testDispatchIfUserIsLearner(): void
    {
        $this->eventManager
            ->expects($this->once())
            ->method('trigger');

        $this->subject->dispatch(
            $this->createLtiUserMock(
                'test',
                [
                    LtiRoles::CONTEXT_LEARNER,
                    LtiRoles::CONTEXT_INSTRUCTOR,
                ]
            )
        );
    }

    public function testDoNotDispatchIfUserIsNotLearner(): void
    {
        $this->eventManager
            ->expects($this->never())
            ->method('trigger');

        $this->subject->dispatch(
            $this->createLtiUserMock(
                'test',
                [
                    LtiRoles::CONTEXT_ADMINISTRATOR
                ]
            )
        );
    }

    private function createLtiUserMock(string $id, array $roles): LtiUserInterface
    {
        $ltiUser = $this->createMock(LtiUserInterface::class);

        $ltiUser->method('getIdentifier')
            ->willReturn($id);

        $ltiUser->method('getRoles')
            ->willReturn($roles);

        return $ltiUser;
    }
}
