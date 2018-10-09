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

namespace oat\taoLti\models\classes;

use common_exception_Error;
use common_session_DefaultSession;
use core_kernel_classes_Resource;
use oat\taoLti\models\classes\ResourceLink\LinkService;
use oat\taoLti\models\classes\user\LtiUserInterface;

/**
 * The TAO layer ontop of the LtiSession
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package taoLti
 */
class TaoLtiSession extends common_session_DefaultSession
{
    /**
     * @var core_kernel_classes_Resource
     */
    private $ltiLink = null;

    public function __construct(LtiUserInterface $user)
    {
        parent::__construct($user);
    }

    /**
     * Override tje default label construction
     * (non-PHPdoc)
     * @see common_session_DefaultSession::getUserLabel()
     *
     * @throws LtiVariableMissingException
     */
    public function getUserLabel()
    {
        if ($this->getLaunchData()->hasVariable(LtiLaunchData::LIS_PERSON_NAME_FULL)) {
            return $this->getLaunchData()->getUserFullName();
        } else {
            $parts = array();
            if ($this->getLaunchData()->hasVariable(LtiLaunchData::LIS_PERSON_NAME_GIVEN)) {
                $parts[] = $this->getLaunchData()->getUserGivenName();
            }
            if ($this->getLaunchData()->hasVariable(LtiLaunchData::LIS_PERSON_NAME_FAMILY)) {
                $parts[] = $this->getLaunchData()->getUserFamilyName();
            }
            return empty($parts) ? __('user') : implode(' ', $parts);
        }
    }

    /**
     * Returns the data that was transmitted during launch
     *
     * @return LtiLaunchData
     */
    public function getLaunchData()
    {
        return $this->getUser()->getLaunchData();
    }

    /**
     * Returns an resource representing the incoming link
     *
     * @return core_kernel_classes_Resource
     * @throws LtiVariableMissingException
     * @throws common_exception_Error
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
