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
 */

namespace oat\taoLti\models\classes;

use common_Exception;
use common_exception_Error;
use common_http_Request;
use common_session_SessionManager;
use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use oat\oatbox\log\LoggerService;
use oat\tao\model\TaoOntology;
use oat\taoLti\models\classes\LtiMessages\LtiErrorMessage;
use Psr\Log\LogLevel;
use tao_models_classes_Service;

class LtiService extends tao_models_classes_Service
{
    const LIS_CONTEXT_ROLE_NAMESPACE = 'urn:lti:role:ims/lis/';

    const LTICONTEXT_SESSION_KEY = 'LTICONTEXT';

    /**
     * start a session from the provided OAuth Request
     *
     * @param common_http_Request $request
     *
     * @throws LtiException
     * @throws common_Exception
     * @throws \ResolverException
     */
    public function startLtiSession(common_http_Request $request)
    {
        try {
            /** @var FactoryLtiAuthAdapterService $factoryAuth */
            $factoryAuth = $this->getServiceLocator()->get(FactoryLtiAuthAdapterServiceInterface::SERVICE_ID);
            $adapter     = $factoryAuth->create($request);

            $user = $adapter->authenticate();

            $session = new TaoLtiSession($user);

            $this->getServiceLocator()->propagate($session);
            common_session_SessionManager::startSession($session);
        } catch (LtiInvalidVariableException $e) {
            $this->getServiceLocator()
                ->get(LoggerService::SERVICE_ID)
                ->log(LogLevel::INFO, $e->getMessage());

            throw new LtiException(
                __('You are not authorized to use this system'),
                LtiErrorMessage::ERROR_UNAUTHORIZED
            );
        }
    }

    /**
     * Returns the current LTI session
     *
     * @return TaoLtiSession
     * @throws LtiException
     * @throws common_exception_Error
     */
    public function getLtiSession()
    {
        $session = common_session_SessionManager::getSession();
        if (!$session instanceof TaoLtiSession) {
            throw new LtiException(__FUNCTION__ . ' called on a non LTI session', LtiErrorMessage::ERROR_SYSTEM_ERROR);
        }
        $this->getServiceLocator()->propagate($session);

        return $session;
    }

    /**
     * @param $key
     * @return mixed
     * @throws LtiException
     */
    public function getCredential($key)
    {
        $class = new core_kernel_classes_Class(ConsumerService::CLASS_URI);
        $instances = $class->searchInstances(array(TaoOntology::PROPERTY_OAUTH_KEY => $key), array('like' => false));
        if (count($instances) == 0) {
            throw new LtiException('No Credentials for consumer key ' . $key, LtiErrorMessage::ERROR_UNAUTHORIZED);
        }
        if (count($instances) > 1) {
            throw new LtiException(
                'Multiple Credentials for consumer key ' . $key,
                LtiErrorMessage::ERROR_INVALID_PARAMETER
            );
        }

        return current($instances);
    }

    /**
     * Returns the LTI Consumer resource associated to this lti session
     *
     * @access public
     * @author Joel Bout, <joel@taotesting.com>
     * @param LtiLaunchData $launchData
     * @return core_kernel_classes_Resource resource of LtiConsumer
     * @throws LtiVariableMissingException
     * @deprecated use LtiLaunchData::getLtiConsumer instead
     */
    public function getLtiConsumerResource($launchData)
    {
        return $launchData->getLtiConsumer();
    }
}