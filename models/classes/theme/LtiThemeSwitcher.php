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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoLti\models\classes\theme;

use oat\tao\model\theme\ThemeService;
use oat\taoLti\models\classes\TaoLtiSession;

/**
 *
 * @author Joel Bout
 *
 * @deprecated Set the LtiThemeIdProvider (ThemeIdProviderInterface) to the ThemeService as option and use getTheme method!
 */
class LtiThemeSwitcher extends ThemeService implements LtiHeadless
{

    const OPTION_HEADLESS_PAGE = 'headless_page';

    const LTI_VARIABLE            = 'custom_theme';
    const LTI_PRESENTATION_TARGET = 'launch_presentation_document_target';

    /**
     * @return \oat\tao\model\theme\Theme
     * @throws \common_exception_Error
     * @throws \common_exception_InconsistentData
     * @throws \oat\taoLti\models\classes\LtiVariableMissingException
     */
    public function getTheme()
    {
        $currentSession = \common_session_SessionManager::getSession();
        if ($currentSession instanceof TaoLtiSession) {
            $launchData = $currentSession->getLaunchData();
            if ($launchData->hasVariable(self::LTI_VARIABLE) && $this->hasTheme($launchData->getVariable(self::LTI_VARIABLE))) {
                return $this->getThemeById($launchData->getVariable(self::LTI_VARIABLE));
            }
        }

        return parent::getTheme();
    }


    /**
     * Tells if the page has to be headless: without header and footer.
     * @return bool|mixed
     * @throws \common_exception_Error
     * @throws \oat\taoLti\models\classes\LtiVariableMissingException
     */
    public function isHeadless()
    {
        if ($this->hasOption(self::OPTION_HEADLESS_PAGE)) {
            return $this->getOption(self::OPTION_HEADLESS_PAGE);
        }

        $currentSession = \common_session_SessionManager::getSession();
        if ($currentSession instanceof TaoLtiSession) {
            $launchData = $currentSession->getLaunchData();
            $presentationTarget = $launchData->hasVariable(self::LTI_PRESENTATION_TARGET) ? $launchData->getVariable(self::LTI_PRESENTATION_TARGET) : '';
            return $presentationTarget == 'frame' || $presentationTarget == 'iframe';
        }

        return true;
    }
}
