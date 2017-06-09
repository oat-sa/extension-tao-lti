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

use oat\taoLti\models\classes\AbstractLtiService;

/**
 * Basic service to handle everything LTI
 * 
 * @author Joel Bout, <joel@taotesting.com>
 * @deprecated use AbstractLtiService
 */
class taoLti_models_classes_LtiService extends tao_models_classes_Service 
{

    /** @var AbstractLtiService $ltiService */
	private $ltiService = null;

	protected function __construct() {
        parent::__construct();
	}
	
	/**
	 * start a session from the provided OAuth Request
	 * 
	 * @param common_http_Request $request
	 * @throws common_user_auth_AuthFailedException
	 */
	public function startLtiSession(common_http_Request $request) {
	    $this->getLtiService()->startLtiSession($request);
	}
	
	/**
	 * Returns the current LTI session
     * @throws \taoLti_models_classes_LtiException
	 * @return \taoLti_models_classes_TaoLtiSession
	 */
	public function getLtiSession() {
	    return $this->getLtiService()->getLtiSession();
	}

    /**
     * @param $key
     * @return mixed
     * @throws taoLti_models_classes_LtiException
     */
	public function getCredential($key) {
		return $this->getLtiService()->getCredential($key);
	}
	
	/**
	 * Returns the LTI Consumer resource associated to this lti session
	 *
	 * @access public
	 * @author Joel Bout, <joel@taotesting.com>
	 * @return core_kernel_classes_Resource resource of LtiConsumer
	 * @throws tao_models_classes_oauth_Exception thrown if no Consumer found for key
	 */
	public function getLtiConsumerResource($launchData)
	{
        return $this->getLtiService()->getLtiConsumerResource($launchData);
	}
		
	/**
	 * Returns the existing tao User that corresponds to
	 * the LTI request or spawns it
	 * 
	 * @param taoLti_models_classes_LtiLaunchData $launchData
	 * @throws taoLti_models_classes_LtiException
	 * @return core_kernel_classes_Resource
	 */
	public function findOrSpawnUser(taoLti_models_classes_LtiLaunchData $launchData) {
        return $this->getLtiService()->findOrSpawnUser($launchData);
	}
	
	/**
	 * Searches if this user was already created in TAO
	 * 
	 * @param taoLti_models_classes_LtiLaunchData $ltiContext
	 * @throws taoLti_models_classes_LtiException
	 * @return core_kernel_classes_Resource
	 */
	public function findUser(taoLti_models_classes_LtiLaunchData $ltiContext) {
        return $this->getLtiService()->findUser($ltiContext);
	}
	
	/**
	 * Creates a new LTI User with the absolute minimum of required informations
	 * 
	 * @param taoLti_models_classes_LtiLaunchData $ltiContext
	 * @return core_kernel_classes_Resource
	 */
	public function spawnUser(taoLti_models_classes_LtiLaunchData $ltiContext) {
		return $this->getLtiService()->spawnUser($ltiContext);
	}

    /**
     * Get the lti service to call correct method
     * keep backward compatibility
     * @return AbstractLtiService
     */
	private function getLtiService()
    {
        if(is_null($this->ltiService)){
            $this->ltiService = $this->getServiceLocator()->get(AbstractLtiService::SERVICE_ID);
        }

        return $this->ltiService;
    }
}