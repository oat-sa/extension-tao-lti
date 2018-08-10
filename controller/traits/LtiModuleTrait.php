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

namespace oat\taoLti\controller\traits;

use oat\taoLti\models\classes\LtiException;
use oat\taoLti\models\classes\LtiLaunchData;
use oat\taoLti\models\classes\LtiMessages\LtiErrorMessage;
use \tao_helpers_Request;
use \common_exception_IsAjaxAction;
use \oat\tao\model\routing\FlowController;

trait LtiModuleTrait
{
    /**
     * Returns an error page
     *
     * Ignore the parameter returnLink as LTI session always
     * require a way for the consumer to return to his platform
     *
     * @param LtiException $error error to show
     * @param boolean $returnLink
     * @throws LtiException
     * @throws \InterruptedActionException
     * @throws \ResolverException
     * @throws \common_exception_Error
     * @throws \oat\taoLti\models\classes\LtiVariableMissingException
     * @throws common_exception_IsAjaxAction
     * @see \tao_actions_CommonModule::returnError()
     */
    protected function returnLtiError(LtiException $error, $returnLink = true)
    {
        // full trace of the error
        \common_Logger::e($error->__toString());

        if (tao_helpers_Request::isAjax()) {
            throw new common_exception_IsAjaxAction(__CLASS__ . '::' . __FUNCTION__);
        } else {
            $launchData = LtiLaunchData::fromRequest(\common_http_Request::currentRequest());

            if ($launchData->hasReturnUrl() && $error->getCode() != LtiErrorMessage::ERROR_UNAUTHORIZED) {
                $flowController = new FlowController();
                $flowController->redirect($this->getLtiReturnUrl($launchData, $error));
            }

            // In regard of the IMS LTI standard, we have to show a back button that refer to the
            // launch_presentation_return_url url param. So we have to retrieve this parameter before trying to start
            // the session
            $consumerLabel = $launchData->getToolConsumerName();
            if (!is_null($consumerLabel)) {
                $this->setData('consumerLabel', $consumerLabel);
            }

            $this->setData('message', $error->getMessage());
            $this->setView('error.tpl', 'taoLti');
        }
    }

    /**
     * @param LtiLaunchData $launchData
     * @param LtiException $error
     * @return string
     * @throws LtiException
     */
    private function getLtiReturnUrl(LtiLaunchData $launchData, LtiException $error)
    {
        $baseUrl = $launchData->getReturnUrl();
        $url = $baseUrl . (parse_url($baseUrl, PHP_URL_QUERY) ? '&' : '?') . http_build_query($error->getLtiMessage()->getUrlParams());
        return $url;
    }
}