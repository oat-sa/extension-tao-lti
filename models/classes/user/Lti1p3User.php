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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoLti\models\classes\user;

use oat\taoLti\models\classes\LtiLaunchData;
use oat\taoLti\models\classes\LtiRoles;
use oat\taoLti\models\classes\LtiUtils;
use oat\taoLti\models\classes\LtiVariableMissingException;

/**
 * Authentication adapter interface to be implemented by authentication methodes
 *
 * @access public
 * @package taoLti
 */
class Lti1p3User extends LtiUser
{
    /**
     * @param LtiLaunchData $launchData
     * @throws \common_Exception
     * @throws \common_exception_Error
     * @throws LtiVariableMissingException
     */
    public function __construct($launchData)
    {
        $userUri = $launchData->hasVariable(LtiLaunchData::USER_ID)
            ? $launchData->getVariable(LtiLaunchData::USER_ID)
            : 'anonymous';

        parent::__construct($launchData, $userUri);
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
}
