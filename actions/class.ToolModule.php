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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 */

use oat\taoLti\actions\LtiModule;
use oat\taoLti\models\classes\CookieVerifyService;

/**
 * An abstract tool controller to be extended by the concrete tools
 *
 * @package taoLti
 */
abstract class taoLti_actions_ToolModule extends LtiModule
{
    /**
     * Entrypoint of every tool
     */
    public function launch()
    {
        try {
            taoLti_models_classes_LtiService::singleton()->startLtiSession(common_http_Request::currentRequest());
            /** @var CookieVerifyService $cookieService */
            $cookieService = $this->getServiceManager()->get(CookieVerifyService::SERVICE_ID);
            if ($cookieService->isVerifyCookieRequired()) {
                if (tao_models_classes_accessControl_AclProxy::hasAccess('verifyCookie', 'CookieUtils', 'taoLti')) {
                    $this->redirect(_url('verifyCookie', 'CookieUtils', 'taoLti', [
                        'session'  => session_id(),
                        'redirect' => _url('run', null, null, $_GET)]));
                } else {
                    throw new taoLti_models_classes_LtiException(
                        __('You are not authorized to use this system'),
                        \oat\taoLti\models\classes\LtiMessages\LtiErrorMessage::ERROR_UNAUTHORIZED
                    );
                }
            } else {
                $this->forward('run', null, null, $_GET);
            }
        } catch (common_user_auth_AuthFailedException $e) {
            common_Logger::i($e->getMessage());
            throw new taoLti_models_classes_LtiException(
                __('The LTI connection could not be established'),
                \oat\taoLti\models\classes\LtiMessages\LtiErrorMessage::ERROR_UNAUTHORIZED
            );
        } catch (\taoLti_models_classes_LtiException $e) {
            // In regard of the IMS LTI standard, we have to show a back button that refer to the
            // launch_presentation_return_url url param. So we have to retrieve this parameter before trying to start
            // the session
            $params = common_http_Request::currentRequest()->getParams();
            if (isset($params[taoLti_models_classes_LtiLaunchData::TOOL_CONSUMER_INSTANCE_NAME])) {
                $this->setData('consumerLabel', $params[taoLti_models_classes_LtiLaunchData::TOOL_CONSUMER_INSTANCE_NAME]);
            } elseif (isset($params[taoLti_models_classes_LtiLaunchData::TOOL_CONSUMER_INSTANCE_DESCRIPTION])) {
                $this->setData('consumerLabel', $params[taoLti_models_classes_LtiLaunchData::TOOL_CONSUMER_INSTANCE_DESCRIPTION]);
            }

            if (isset($params[taoLti_models_classes_LtiLaunchData::LAUNCH_PRESENTATION_RETURN_URL])) {
                $returnUrl = $params[taoLti_models_classes_LtiLaunchData::LAUNCH_PRESENTATION_RETURN_URL];
                $serverName = $_SERVER['SERVER_NAME'];
                $pieces = parse_url($returnUrl);
                $domain = isset($pieces['host']) ? $pieces['host'] : '';
                if ($serverName == $domain) {
                    $this->setData('returnUrl', $returnUrl);
                }
            }

            common_Logger::i($e->getMessage());
            $this->returnLtiError($e, false);
        } catch (tao_models_classes_oauth_Exception $e) {
            common_Logger::i($e->getMessage());
            throw new taoLti_models_classes_LtiException(
                __('The LTI connection could not be established'),
                \oat\taoLti\models\classes\LtiMessages\LtiErrorMessage::ERROR_UNAUTHORIZED
            );
        }
    }

    /**
     * run() contains the actual tool's controller
     */
    abstract public function run();
}