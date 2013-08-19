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

/**
 * Authentication adapter interface to be implemented by authentication methodes
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package core
 * @subpackage kernel_users
 */
class taoLti_models_classes_LtiUser
	extends common_user_User
{
    /**
     * Data with which this session was launched
     * @var taoLti_models_classes_LtiLaunchData
     */
	private $ltiLaunchData;

	/**
	 * Local represenation of user
	 * @var core_kernel_classes_Resource
	 */
	private $userUri;
	
	/**
	 * Cache of the current user's lti roles
	 * @var array
	 */
	private $roles;
	
	public function __construct(taoLti_models_classes_LtiLaunchData $ltiLaunchData) {
	    $this->ltiLaunchData = $ltiLaunchData;
	    $this->userUri = taoLti_models_classes_LtiService::singleton()->findOrSpwanUser($ltiLaunchData)->getUri();
	}
	
	/**
	 * 
	 * @return taoLti_models_classes_LtiLaunchData
	 */
	public function getLaunchData() {
	    return $this->ltiLaunchData;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see common_user_User::getIdentifier()
	 */
    public function getIdentifier() {
        return $this->getTaoUser()->getUri();
    }
    
	public function getPropertyValues($property) {
	    $returnValue = null;
	    switch ($property) {
	    	case PROPERTY_USER_DEFLG :
	    	case PROPERTY_USER_UILG :
	    	    $returnValue = array($this->getLanguage());
	    	    break;
	    	case PROPERTY_USER_ROLES :
	    	    $returnValue = $this->getTaoUserRoles();
	    	    break;
	    	default:
	    	    common_Logger::d('Unkown property '.$property.' requested from '.__CLASS__);
	    	    $returnValue = array();
	    }
	    return $returnValue;
	}
	
	public function getLanguage() {
	    if ($this->getLaunchData()->hasLaunchLanguage()) {
	       taoLti_models_classes_LtiUtils::mapCode2InterfaceLanguage($this->getLaunchData()->getLaunchLanguage());
	    } else {
	        // no language given
	        return DEFAULT_LANG;
	    }
	}
	
	public function refresh() {
        // nothing to do	    
	}
	
	/**
	 * @return core_kernel_classes_Resource
	 */
	public function getTaoUser() {
	    return new core_kernel_classes_Resource($this->userUri);
	}
	
	public function getTaoUserRoles() {
	    if (is_null($this->roles)) {
	        $this->roles = array();
	        foreach ($this->getLaunchData()->getUserRoles() as $role) {
	            $taoRole = taoLti_models_classes_LtiUtils::mapLTIRole2TaoRole($role);
	            if (!is_null($taoRole)) {
	                $this->roles[] = $taoRole->getUri();
	            }
	        }
	    }
	    return $this->roles;
	}
	
}