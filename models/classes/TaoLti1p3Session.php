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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 */

namespace oat\taoLti\models\classes;

use common_exception_Error;
use core_kernel_classes_Resource;
use oat\taoLti\models\classes\ResourceLink\LinkService;

class TaoLti1p3Session extends TaoLtiSession
{
    /**
     * @var core_kernel_classes_Resource
     */
    private $ltiLink = null;

    /**
     * Returns an resource representing the incoming link
     *
     * @return core_kernel_classes_Resource
     * @throws LtiVariableMissingException
     * @throws common_exception_Error
     */
    public function getLtiLinkResource()
    {
        if (null === $this->ltiLink) {
            $service = $this->getServiceLocator()->get(LinkService::SERVICE_ID);
            $linkId = $service->getLinkId(
                $this->getLaunchData()->getVariable(LtiLaunchData::TOOL_CONSUMER_INSTANCE_ID),
                $this->getLaunchData()->getVariable(LtiLaunchData::RESOURCE_LINK_ID)
            );

            $this->ltiLink = new core_kernel_classes_Resource($linkId);
        }
        return $this->ltiLink;
    }
}
