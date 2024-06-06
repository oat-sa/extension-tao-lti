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

namespace oat\taoLti\scripts\install;

use oat\oatbox\config\ConfigurationService;
use oat\oatbox\extension\InstallAction;
use oat\tao\model\theme\PortalTheme;
use oat\tao\model\theme\ThemeServiceInterface;
use oat\taoLti\models\classes\theme\PortalThemeService;

class RegisterPortalTheme extends InstallAction
{
    public function __invoke($params = [])
    {
        /** @var ConfigurationService $previousThemeService */
        $previousThemeService = $this->getServiceManager()->get(ThemeServiceInterface::SERVICE_ID);

        /** @var ThemeServiceInterface $service */
        $service = $this->propagate(new PortalThemeService());
        $service->setOptions($previousThemeService->getOptions());
        $service->addTheme(new PortalTheme(), false);

        $this->getServiceManager()->register(ThemeServiceInterface::SERVICE_ID, $service);
    }
}
