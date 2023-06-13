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
 * Copyright (c) 2021-2022 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoLti\models\classes\user;

use oat\tao\model\TaoOntology;
use oat\taoLti\models\classes\LtiLaunchData;
use oat\taoLti\models\classes\LtiRoles;
use oat\taoLti\models\classes\LtiUtils;
use oat\taoLti\models\classes\LtiVariableMissingException;

class Lti1p3User extends LtiUser
{
    /** @var string */
    private $registrationId = null;
    private ?string $userFirstTimeUri = null;
    private ?string $userLatestExtension = null;

    /**
     * @param LtiLaunchData $launchData
     * @throws \common_Exception
     * @throws \common_exception_Error
     * @throws LtiVariableMissingException
     */
    public function __construct($launchData, string $userUri = null)
    {
        if ($userUri === null) {
            $userUri = $launchData->hasVariable(LtiLaunchData::USER_ID)
                ? $launchData->getVariable(LtiLaunchData::USER_ID)
                : self::ANONYMOUS_USER_URI;
        }

        parent::__construct($launchData, $userUri);
    }

    public function getRegistrationId(): ?string
    {
        return $this->registrationId;
    }

    public function setRegistrationId(string $registrationId): self
    {
        $this->registrationId = $registrationId;

        return $this;
    }

    public function setUserFirstTimeUri(string $userFirstTimeUri): self
    {
        $this->userFirstTimeUri = $userFirstTimeUri;

        return $this;
    }

    public function setUserLatestExtension(string $userLatestExtension): self
    {
        $this->userLatestExtension = $userLatestExtension;

        return $this;
    }

    /**
     * Calculate your primary tao roles from the launch data
     *
     * @param LtiLaunchData $ltiLaunchData
     * @return array
     * @throws \common_Exception
     * @throws \common_exception_Error
     */
    protected function determineTaoRoles(LtiLaunchData $ltiLaunchData)
    {
        $roles = [];
        $ltiRoles = [];

        if ($ltiLaunchData->hasVariable(LtiLaunchData::ROLES)) {
            $ltiRoles = $ltiLaunchData->getUserRoles();
            foreach ($ltiRoles as $role) {
                $taoRole = LtiUtils::mapLTIRole2TaoRole($role);
                if (!is_null($taoRole)) {
                    $roles[] = $taoRole;
                }
            }
            $roles = array_unique($roles);
        }

        if (empty($roles)) {
            $roles[] = LtiRoles::INSTANCE_LTI_BASE;
        }

        return array_merge($roles, $ltiRoles);
    }

    public function getPropertyValues($property)
    {
        if ($property === TaoOntology::PROPERTY_USER_FIRST_TIME && !empty($this->userFirstTimeUri)) {
            return [$this->userFirstTimeUri];
        }

        if ($property === TaoOntology::PROPERTY_USER_LAST_EXTENSION && !empty($this->userLatestExtension)) {
            return [$this->userLatestExtension];
        }

        return parent::getPropertyValues($property);
    }
}
