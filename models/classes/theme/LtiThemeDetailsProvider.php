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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoLti\models\classes\theme;

use oat\oatbox\PhpSerializable;
use oat\oatbox\PhpSerializeStateless;
use oat\tao\model\theme\ThemeDetailsProviderInterface;
use oat\taoLti\models\classes\TaoLtiSession;

class LtiThemeDetailsProvider implements ThemeDetailsProviderInterface, PhpSerializable
{
    use PhpSerializeStateless;

    const LTI_CUSTOM_THEME_VARIABLE = 'custom_theme';
    const LTI_PRESENTATION_TARGET   = 'launch_presentation_document_target';

    /**
     * @inheritdoc
     */
    public function getThemeId()
    {
        $currentSession = \common_session_SessionManager::getSession();
        if ($currentSession instanceof TaoLtiSession) {
            $launchData = $currentSession->getLaunchData();
            if ($launchData->hasVariable(static::LTI_CUSTOM_THEME_VARIABLE)) {
                return $launchData->getVariable(static::LTI_CUSTOM_THEME_VARIABLE);
            }
        }

        return '';
    }

    /**
     * Tells if the page has to be headless: without header and footer.
     *
     * @return bool|mixed
     * @throws \common_exception_Error
     * @throws \oat\taoLti\models\classes\LtiVariableMissingException
     */
    public function isHeadless()
    {
        $currentSession = \common_session_SessionManager::getSession();
        if ($currentSession instanceof TaoLtiSession) {
            $launchData = $currentSession->getLaunchData();
            $presentationTarget = $launchData->hasVariable(static::LTI_PRESENTATION_TARGET)
                ? $launchData->getVariable(self::LTI_PRESENTATION_TARGET)
                : '';
            return $presentationTarget == 'frame' || $presentationTarget == 'iframe';
        }

        return true;
    }
}
