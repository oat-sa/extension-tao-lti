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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoLti\models\classes\theme;

use common_session_SessionManager as SessionManager;
use oat\tao\model\session\Context\TenantDataSessionContext;
use oat\tao\model\theme\DefaultTheme;
use oat\tao\model\theme\PortalTheme;
use oat\tao\model\theme\ThemeDetailsProviderInterface;
use oat\taoLti\models\classes\TaoLtiSession;

class PortalThemeDetailProvider implements ThemeDetailsProviderInterface
{
    public function getThemeId(): string
    {
        if ($this->isSessionFromPortal()) {
            return PortalTheme::THEME_ID;
        };

        return '';
    }

    /**
     * @inheritDoc
     */
    public function isHeadless(): bool
    {
        return false;
    }

    private function isSessionFromPortal(): bool
    {
        $session = SessionManager::getSession();
        return $session instanceof TaoLtiSession
            && !empty($session->getContexts(TenantDataSessionContext::class));
    }
}
