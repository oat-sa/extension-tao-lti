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
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\taoLti\test\unit\models\classes\session\source;

use common_session_Session;
use oat\tao\model\session\Context\TenantDataSessionContext;
use oat\taoLti\models\classes\session\source\PortalSessionSourceMatcher;
use oat\taoLti\models\classes\TaoLtiSession;
use PHPUnit\Framework\TestCase;

class PortalSessionSourceMatcherTest extends TestCase
{
    public function testReturnsTrueForLtiSessionWithTenantDataContext(): void
    {
        $session = $this->createMock(TaoLtiSession::class);
        $session
            ->method('getContexts')
            ->with(TenantDataSessionContext::class)
            ->willReturn([new \stdClass()]);

        $service = new PortalSessionSourceMatcher();

        $this->assertTrue($service->matchesSource($session));
    }

    public function testReturnsFalseForLtiSessionWithoutTenantDataContext(): void
    {
        $session = $this->createMock(TaoLtiSession::class);
        $session
            ->method('getContexts')
            ->with(TenantDataSessionContext::class)
            ->willReturn([]);

        $service = new PortalSessionSourceMatcher();

        $this->assertFalse($service->matchesSource($session));
    }

    public function testReturnsFalseForNonLtiSession(): void
    {
        $session = $this->createMock(common_session_Session::class);
        $service = new PortalSessionSourceMatcher();

        $this->assertFalse($service->matchesSource($session));
    }
}
