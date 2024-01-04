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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoLti\models\classes\Tool\Service;

use oat\taoLti\models\classes\Tool\Exception\WrongLtiRolesException;

class AuthoringLtiRoleService
{
    public function __construct(array $roleAllowed)
    {
        $this->roleAllowed = $roleAllowed;
    }

    /**
     * @throws WrongLtiRolesException
     */
    public function getValidRole(array $roles): string
    {
        $commonRoles = array_intersect($roles, $this->roleAllowed);

        if (empty($commonRoles)) {
            throw new WrongLtiRolesException();
        }
        return current($commonRoles);
    }
}
