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

namespace oat\taoLti\controller;

use common_Exception;
use common_exception_Error;
use common_exception_IsAjaxAction;
use common_http_Request;
use tao_helpers_Request;
use common_Logger;
use common_user_auth_AuthFailedException;
use InterruptedActionException;
use oat\taoLti\models\classes\CookieVerifyService;
use oat\taoLti\models\classes\LaunchData\Validator\LtiValidatorService;
use oat\taoLti\models\classes\LtiException;
use oat\taoLti\models\classes\LtiLaunchData;
use oat\taoLti\models\classes\LtiMessages\LtiErrorMessage;
use oat\taoLti\models\classes\LtiService;
use ResolverException;
use tao_models_classes_accessControl_AclProxy;
use tao_models_classes_oauth_Exception;

/**
 * An abstract tool controller to be extended by the concrete tools
 *
 * @package taoLti
 */
abstract class ToolModule extends LtiModule
{
    /**
     * Entrypoint of every tool
     *
     * @throws LtiException
     * @throws ResolverException
     * @throws common_Exception
     * @throws common_exception_Error
     */
    public function launch()
    {
        try {
            $request = common_http_Request::currentRequest();
            $ltiLaunchData = LtiLaunchData::fromRequest($request);
            /** @var LtiValidatorService $validator */
            $validator = $this->getServiceLocator()->get(LtiValidatorService::SERVICE_ID);
            $validator->validateLaunchData($ltiLaunchData);

            LtiService::singleton()->startLtiSession($request);


            /** @var CookieVerifyService $cookieService */
            $cookieService = $this->getServiceManager()->get(CookieVerifyService::SERVICE_ID);
            if ($cookieService->isVerifyCookieRequired()) {
                if (tao_models_classes_accessControl_AclProxy::hasAccess('verifyCookie', 'CookieUtils', 'taoLti')) {
                    $cookieRedirect = _url(
                        'verifyCookie',
                        'CookieUtils',
                        'taoLti',
                        [
                            'session' => session_id(),
                            'redirect' => urlencode(_url('run', null, null, $_GET)),
                        ]
                    );
                    $this->redirect($cookieRedirect);
                } else {
                    throw new LtiException(
                        __('You are not authorized to use this system'),
                        LtiErrorMessage::ERROR_UNAUTHORIZED
                    );
                }
            } else {
                $this->forward('run', null, null, $_GET);
            }
        } catch (common_user_auth_AuthFailedException $e) {
            common_Logger::i($e->getMessage());
            throw new LtiException(
                __('The LTI connection could not be established'),
                LtiErrorMessage::ERROR_UNAUTHORIZED
            );
        } catch (LtiException $e) {
            common_Logger::i($e->__toString());

            if (tao_helpers_Request::isAjax()) {
                throw new common_exception_IsAjaxAction(__CLASS__ . '::' . __FUNCTION__);
            }
            throw $e;
        } catch (tao_models_classes_oauth_Exception $e) {
            common_Logger::i($e->getMessage());
            throw new LtiException(
                __('The LTI connection could not be established'),
                LtiErrorMessage::ERROR_UNAUTHORIZED
            );
        }
    }

    /**
     * run() contains the actual tool's controller
     */
    abstract public function run();
}