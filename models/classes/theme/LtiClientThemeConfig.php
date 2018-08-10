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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoLti\models\classes\theme;

use oat\tao\model\clientConfig\sources\ThemeConfig;
use oat\taoLti\models\classes\TaoLtiSession;

/**
 *
 * @author Joel Bout
 */
class LtiClientThemeConfig extends ThemeConfig
{
    /**
     * (non-PHPdoc)
     * @see \oat\tao\model\clientConfig\ClientConfig::getConfig()
     */
    public function getConfig()
    {
        $config = parent::getConfig();
        $currentSession = \common_session_SessionManager::getSession();
        if ($currentSession instanceof TaoLtiSession) {
            $launchData = $currentSession->getLaunchData();
            if ($launchData->hasVariable(LtiThemeSwitcher::LTI_VARIABLE)) {
                $config["activeNamespace"] = $launchData->getVariable(LtiThemeSwitcher::LTI_VARIABLE);
            }
        }

        return $config;
    }
}
