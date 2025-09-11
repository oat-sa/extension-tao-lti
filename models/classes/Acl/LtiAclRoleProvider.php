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
 * Copyright (c) 2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoLti\models\classes\Acl;

use oat\generis\model\data\Ontology;
use oat\generis\model\GenerisRdf;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\session\SessionService;
use oat\tao\model\accessControl\AclRoleProvider;
use oat\taoLti\models\classes\LtiLaunchData;
use oat\taoLti\models\classes\LtiRoles;
use oat\taoLti\models\classes\TaoLtiSession;

class LtiAclRoleProvider extends ConfigurableService implements AclRoleProvider
{
    public function get(): string
    {
        $session = $this->getSessionService()->getCurrentSession();
        if ($session instanceof TaoLtiSession) {
            $roles = explode(
                ',',
                $session->getLaunchData()->getVariable(LtiLaunchData::ROLES)
            );

            $aclClass = $this->getOntologyService()->getClass(LtiRoles::ACL_CLASS_URI);
            // If aclClass exist and is a class
            if ($aclClass->exists() && $aclClass->isClass()) {
                $aclRoles = array_keys($aclClass->getInstances());

                if (!empty($aclRoles) && !empty(array_intersect($roles, $aclRoles))) {
                    return LtiRoles::ACL_CLASS_URI;
                }
            }
        }

        return GenerisRdf::CLASS_ROLE;
    }

    private function getOntologyService(): Ontology
    {
        return $this->getServiceManager()->get(Ontology::SERVICE_ID);
    }

    private function getSessionService(): SessionService
    {
        return $this->getServiceManager()->get(SessionService::SERVICE_ID);
    }
}
