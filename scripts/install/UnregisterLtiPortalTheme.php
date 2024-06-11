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
use Exception;
use oat\oatbox\extension\InstallAction;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\theme\DefaultTheme;
use oat\tao\model\theme\PortalTheme;
use oat\tao\model\theme\ThemeService;
use oat\tao\model\theme\ThemeServiceInterface;
use oat\taoLti\models\classes\theme\PortalThemeDetailProvider;
use oat\taoLti\models\classes\theme\PortalThemeService;
use oat\taoStyles\model\service\PersistenceThemeService;

class UnregisterLtiPortalTheme extends InstallAction
{
    public function __invoke($params = [])
    {
        /** @var ThemeServiceInterface|ConfigurableService $service */
        $service = $this->getServiceManager()->get(ThemeServiceInterface::SERVICE_ID);
        $oldConfig = $service->getOptions();

        //This provider will allow to display Portal Theme
        if (!isset($oldConfig['themeDetailsProviders'])) {
            $oldConfig['themeDetailsProviders'] = [
                new PortalThemeDetailProvider()
            ];
        }

        /** @var common_ext_ExtensionsManager $extManager */
        $extManager = $this->getServiceManager()->get(common_ext_ExtensionsManager::class);
        //If taoStyles is installed, we had PersistenceThemeService used as theming.conf.php and we should still use it
        if ($extManager->isInstalled('taoStyles') && $service instanceof PortalThemeService) {
            try {
                $oldConfig = $this->validateConfig($oldConfig);
            } catch (Exception $e) {
                $this->getLogger()->error($e->getMessage());
                return;
            }

            $revertedService = $this->propagate(new PersistenceThemeService($oldConfig));
            $this->getServiceManager()->register(ThemeServiceInterface::SERVICE_ID, $revertedService);
            $revertedService->addTheme(new PortalTheme(), false);
            $revertedService->addTheme(new DefaultTheme(), false);

            return;
        }

        //Make sure current theme is set
        if (!isset($oldConfig['current'])) {
            $oldConfig = $this->defineCurrent($oldConfig);
        }

        if ($service instanceof PortalThemeService) {
            $reverseService = $this->propagate(new ThemeService($oldConfig));
            $this->getServiceManager()->register(ThemeServiceInterface::SERVICE_ID, $reverseService);
        }

        $service->setOptions($oldConfig);
        $this->getServiceManager()->register(ThemeServiceInterface::SERVICE_ID, $service);
    }

    private function defineCurrent(array $config): array
    {
        if (!isset($config['available'])) {
            $config['available'] = [
                'default' => DefaultTheme::class,
                'portal' => array(
                    'class' => 'oat\\tao\\model\\theme\\PortalTheme',
                    'options' => array()
                )
            ];
        }

        if (!isset($config['available']['default'])) {
            $config['available']['default'] = DefaultTheme::class;
        }

        if (!isset($config['current'])) {
            $config['current'] = 'default';
        }

        return $config;
    }

    private function validateConfig(array $oldConfig): array
    {
        //On taoLtiThemeService registration we migrated all configs and we may encounter some unused configs
        unset($oldConfig['available']);
        if (!isset($oldConfig['persistence'])) {
            throw new Exception('Missing previous config for PersistenceThemeService');
        }

        return $oldConfig;
    }
}
