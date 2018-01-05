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

use oat\taoLti\models\classes\user\LtiUser;
use oat\taoLti\models\classes\ResourceLink\LinkService;
use oat\taoLti\models\classes\LtiLink;
/**
 * The TAO layer ontop of the LtiSession
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package taoLti
 
 */
class taoLti_models_classes_TaoLtiSession extends common_session_DefaultSession
{

    /**
     * @var core_kernel_classes_Resource
     */
    private $ltiLink = null;

    public function __construct(LtiUser $user)
    {
        parent::__construct($user);
    }

    /**
     * Override tje default label construction
     * (non-PHPdoc)
     * @see common_session_DefaultSession::getUserLabel()
     */
    public function getUserLabel() {
        if ($this->getLaunchData()->hasVariable(taoLti_models_classes_LtiLaunchData::LIS_PERSON_NAME_FULL)) {
            return $this->getLaunchData()->getUserFullName();
        } else {
            $parts = array();
            if ($this->getLaunchData()->hasVariable(taoLti_models_classes_LtiLaunchData::LIS_PERSON_NAME_GIVEN)) {
                $parts[] = $this->getLaunchData()->getUserGivenName();
            }
            if ($this->getLaunchData()->hasVariable(taoLti_models_classes_LtiLaunchData::LIS_PERSON_NAME_FAMILY)) {
                $parts[] = $this->getLaunchData()->getUserFamilyName();
            }
            return empty($parts) ? __('user') : implode(' ', $parts); 
        }
        
    }
    
    /**
     * Returns the data that was transmitted during launch
     * 
     * @return taoLti_models_classes_LtiLaunchData
     */
    public function getLaunchData() {
        return $this->getUser()->getLaunchData();
    }
    
    /**
     * Returns an resource representing the incoming link
     * 
     * @throws common_exception_Error
     * @return core_kernel_classes_Resource
     */
    public function getLtiLinkResource()
    {
        if (is_null($this->ltiLink)) {
            $service = $this->getServiceLocator()->get(LinkService::SERVICE_ID);
            $consumer = $this->getLaunchData()->getLtiConsumer();
            $linkId = $service->getLinkId($consumer->getUri(), $this->getLaunchData()->getResourceLinkID());
            $this->ltiLink = new core_kernel_classes_Resource($linkId);
		}
		return $this->ltiLink;
	}
}
