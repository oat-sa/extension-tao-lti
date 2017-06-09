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
 * 
 */

namespace oat\taoLti\models\classes;

use oat\taoLti\models\classes\LtiMessages\LtiErrorMessage;
use \taoLti_models_classes_LtiLaunchData;
use \core_kernel_classes_Class;
use \core_kernel_classes_Resource;
use \taoLti_models_classes_LtiException;
use \common_Logger;

/**
 * Basic service to handle everything LTI
 * 
 * @author Joel Bout, <joel@taotesting.com>
 */
class OntologyLtiService extends AbstractLtiService
{

	/**
	 * Searches if this user was already created in TAO
	 * 
	 * @param taoLti_models_classes_LtiLaunchData $ltiContext
	 * @throws taoLti_models_classes_LtiException
	 * @return core_kernel_classes_Resource
	 */
	public function findUser(taoLti_models_classes_LtiLaunchData $ltiContext) {
		$class = new core_kernel_classes_Class(CLASS_LTI_USER);
		$instances = $class->searchInstances(array(
			PROPERTY_USER_LTIKEY		=> $ltiContext->getUserID(),
			PROPERTY_USER_LTICONSUMER	=> $this->getLtiConsumerResource($ltiContext)
		), array(
			'like'	=> false
		));
		if (count($instances) > 1) {
			throw new taoLti_models_classes_LtiException(
			    'Multiple user accounts found for user key \''.$ltiContext->getUserID().'\'',
                LtiErrorMessage::ERROR_SYSTEM_ERROR
            );
		}
		return count($instances) == 1 ? current($instances) : null;
	}
	
	/**
	 * Creates a new LTI User with the absolute minimum of required informations
	 * 
	 * @param taoLti_models_classes_LtiLaunchData $ltiContext
	 * @return core_kernel_classes_Resource
	 */
	public function spawnUser(taoLti_models_classes_LtiLaunchData $ltiContext) {
		$class = new core_kernel_classes_Class(CLASS_LTI_USER);
		//$lang = tao_models_classes_LanguageService::singleton()->getLanguageByCode(DEFAULT_LANG);
                
		$props = array(
			PROPERTY_USER_LTIKEY		=> $ltiContext->getUserID(),
			PROPERTY_USER_LTICONSUMER	=> $this->getLtiConsumerResource($ltiContext),
		    /*
			PROPERTY_USER_UILG			=> $lang,
			PROPERTY_USER_DEFLG			=> $lang,
			*/
			
		);
                
        if ($ltiContext->hasVariable(taoLti_models_classes_LtiLaunchData::LIS_PERSON_NAME_FULL)) {
			$props[RDFS_LABEL] = $ltiContext->getUserFullName();
		}
                
		if ($ltiContext->hasVariable(taoLti_models_classes_LtiLaunchData::LIS_PERSON_NAME_GIVEN)) {
			$props[PROPERTY_USER_FIRSTNAME] = $ltiContext->getUserGivenName();
		}
		if ($ltiContext->hasVariable(taoLti_models_classes_LtiLaunchData::LIS_PERSON_NAME_FAMILY)) {
			$props[PROPERTY_USER_LASTNAME] = $ltiContext->getUserFamilyName();
		}
		if ($ltiContext->hasVariable(taoLti_models_classes_LtiLaunchData::LIS_PERSON_CONTACT_EMAIL_PRIMARY)) {
			$props[PROPERTY_USER_MAIL] = $ltiContext->getUserEmail();
		}
		$user = $class->createInstanceWithProperties($props);
		common_Logger::i('added User '.$user->getLabel());

		return $user;
	}
}