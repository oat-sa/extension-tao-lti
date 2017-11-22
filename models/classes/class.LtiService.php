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

use oat\taoLti\models\classes\LtiMessages\LtiErrorMessage;

/**
 * Basic service to handle everything LTI
 * 
 * @author Joel Bout, <joel@taotesting.com>
 */
class taoLti_models_classes_LtiService extends tao_models_classes_Service 
{
	const LIS_CONTEXT_ROLE_NAMESPACE = 'urn:lti:role:ims/lis/';

	const LTICONTEXT_SESSION_KEY	= 'LTICONTEXT';
	
	protected function __construct() {
	}
	
	/**
	 * start a session from the provided OAuth Request
	 * 
	 * @param common_http_Request $request
	 * @throws common_user_auth_AuthFailedException
	 */
	public function startLtiSession(common_http_Request $request) {
        $adapter = new taoLti_models_classes_LtiAuthAdapter($request);
        $this->getServiceLocator()->propagate($adapter);
        $user = $adapter->authenticate();
        $session = new taoLti_models_classes_TaoLtiSession($user);
        $this->getServiceLocator()->propagate($session);
        common_session_SessionManager::startSession($session);
	}
	
	/**
	 * Returns the current LTI session
     * @throws \taoLti_models_classes_LtiException
	 * @return taoLti_models_classes_TaoLtiSession 
	 */
	public function getLtiSession() {
	    $session = common_session_SessionManager::getSession();
	    if (!$session instanceof taoLti_models_classes_TaoLtiSession) {
	        throw new taoLti_models_classes_LtiException(__FUNCTION__.' called on a non LTI session', LtiErrorMessage::ERROR_SYSTEM_ERROR);
	    }
	    return $session;
	}

    /**
     * @param $key
     * @return mixed
     * @throws taoLti_models_classes_LtiException
     */
	public function getCredential($key) {
		$class = new core_kernel_classes_Class(CLASS_LTI_CONSUMER);
		$instances = $class->searchInstances(array(PROPERTY_OAUTH_KEY => $key), array('like' => false));
		if (count($instances) == 0) {
			throw new taoLti_models_classes_LtiException('No Credentials for consumer key '.$key, LtiErrorMessage::ERROR_UNAUTHORIZED);
		}
		if (count($instances) > 1) {
			throw new taoLti_models_classes_LtiException('Multiple Credentials for consumer key '.$key, LtiErrorMessage::ERROR_INVALID_PARAMETER);
		}
		return current($instances);
	}
	
	/**
	 * Returns the LTI Consumer resource associated to this lti session
	 *
	 * @access public
	 * @author Joel Bout, <joel@taotesting.com>
	 * @param taoLti_models_classes_LtiLaunchData $launchData
     * @return core_kernel_classes_Resource resource of LtiConsumer
	 * @throws tao_models_classes_oauth_Exception thrown if no Consumer found for key
     * @deprecated use taoLti_models_classes_LtiLaunchData::getLtiConsumer instead
	 */
	public function getLtiConsumerResource($launchData)
	{
	    return $launchData->getLtiConsumer();
	}

}