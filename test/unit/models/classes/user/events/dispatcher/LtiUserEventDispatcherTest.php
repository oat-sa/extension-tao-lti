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

namespace oat\taoLti\test\unit\models\classes\user\events\dispatcher;

use oat\generis\test\MockObject;
use oat\generis\test\ServiceManagerMockTrait;
use oat\oatbox\event\EventManager;
use oat\taoLti\models\classes\LtiRoles;
use oat\taoLti\models\classes\user\events\dispatcher\LtiUserEventDispatcher;
use oat\taoLti\models\classes\user\events\LtiBackOfficeUserCreatedEvent;
use oat\taoLti\models\classes\user\events\LtiTestTakerCreatedEvent;
use oat\taoLti\models\classes\user\LtiUserInterface;
use PHPUnit\Framework\TestCase;

class LtiUserEventDispatcherTest extends TestCase
{
    use ServiceManagerMockTrait;

    /** @var LtiUserEventDispatcher */
    private LtiUserEventDispatcher $subject;

    /** @var EventManager|MockObject */
    private EventManager $eventManager;

    public function setUp(): void
    {
        $this->eventManager = $this->createMock(EventManager::class);

        $this->subject = new LtiUserEventDispatcher();
        $this->subject->setServiceLocator(
            $this->getServiceManagerMock(
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
            ->method('trigger')
            ->with(
                $this->isInstanceOf(LtiTestTakerCreatedEvent::class)
            )
        ;

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

    public function testDispatchIfUserIsNotLearner(): void
    {
        $this->eventManager
            ->expects($this->once())
            ->method('trigger')
            ->with(
                $this->isInstanceOf(LtiBackOfficeUserCreatedEvent::class)
            )
        ;

        $this->subject->dispatch(
            $this->createLtiUserMock(
                'test',
                [
                    LtiRoles::CONTEXT_INSTRUCTOR,
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
