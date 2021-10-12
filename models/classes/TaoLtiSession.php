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
 * Copyright (c) 2013-2021 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\taoLti\models\classes;

use common_exception_Error;
use common_session_DefaultSession;
use core_kernel_classes_Resource;
use oat\taoLti\models\classes\ResourceLink\LinkService;
use oat\taoLti\models\classes\user\LtiUserInterface;

class TaoLtiSession extends common_session_DefaultSession
{
    private const VERSION_LTI_1P1 = '1.1';
    private const VERSION_LTI_1P3 = '1.3';

    /** @var string */
    private $linkId = null;

    /** @var string */
    private $version = self::VERSION_LTI_1P1;

    public function __construct(LtiUserInterface $user)
    {
        parent::__construct($user);
    }

    public static function fromVersion1p3(LtiUserInterface $user): self
    {
        $session = new self($user);

        $session->version = self::VERSION_LTI_1P3;

        return $session;
    }

    /**
     * Override the default label construction
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
            $parts = [];
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
     * Returns a resource representing the incoming link
     *
     * @throws LtiVariableMissingException
     * @throws common_exception_Error
     */
    public function getLtiLinkResource(): core_kernel_classes_Resource
    {
        if ($this->linkId === null) {
            /** @var LinkService $service */
            $service = $this->getServiceLocator()->get(LinkService::SERVICE_ID);

            $this->linkId = $service->getLinkId(
                $this->getLtiConsumer(),
                $this->getLaunchData()->getResourceLinkID()
            );
        }

        return new core_kernel_classes_Resource($this->linkId);
    }

    /**
     * Returns the consumer based on the LTI version
     *
     * @throws LtiVariableMissingException
     */
    protected function getLtiConsumer(): string
    {
        if ($this->version === self::VERSION_LTI_1P1) {
            return $this->getLaunchData()
                ->getLtiConsumer()
                ->getUri();
        }

        return (string)$this->getLaunchData()
            ->getVariable(LtiLaunchData::TOOL_CONSUMER_INSTANCE_ID);
    }
}
