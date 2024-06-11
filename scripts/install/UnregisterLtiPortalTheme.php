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

use common_ext_ExtensionsManager;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\theme\DefaultTheme;
use oat\tao\model\theme\PortalTheme;
use oat\tao\model\theme\ThemeServiceInterface;
use oat\taoDelivery\scripts\install\installDeliveryFields;
use oat\taoLti\models\classes\theme\PortalThemeDetailProvider;
use oat\taoStyles\model\service\PersistenceThemeService;

class UnregisterLtiPortalTheme extends installDeliveryFields
{
    public function __invoke($params = [])
    {
        /** @var ThemeServiceInterface|ConfigurableService $service */
        $service = $this->getServiceManager()->get(ThemeServiceInterface::SERVICE_ID);
        $oldConfig = $service->getOptions();
        /** @var common_ext_ExtensionsManager $extManager */
        $extManager = $this->getServiceManager()->get(common_ext_ExtensionsManager::class);
        if ($extManager->isInstalled('taoStyles')) {
            unset($oldConfig['available']);
            $oldConfig['themeDetailsProviders'] = [
                new PortalThemeDetailProvider()
            ];
            $revertedService = $this->propagate(new PersistenceThemeService($oldConfig));
            $this->getServiceManager()->register(ThemeServiceInterface::SERVICE_ID, $revertedService);
            $revertedService->addTheme(new PortalTheme(), false);
            $revertedService->addTheme(new DefaultTheme(), false);

            return;
        }

        //Make sure current theme is set
        if (!isset($oldConfig['current'])) {
            $oldConfig['current'] = 'default';
        }

        $service->setOptions($oldConfig);
    }
}
