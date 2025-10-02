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

namespace oat\taoLti\scripts\install;

use oat\oatbox\extension\InstallAction;
use oat\tao\model\accessControl\AclRoleProvider;
use oat\tao\model\search\SearchProxy;
use oat\taoLti\models\classes\Acl\LtiAclRoleProvider;
use oat\taoLti\models\classes\LtiRoles;

class RegisterLtiAclRoleProvider extends InstallAction
{
    public function __invoke($params)
    {
        $this->getServiceManager()->register(AclRoleProvider::SERVICE_ID, new LtiAclRoleProvider());
        $searchProxy = $this->getServiceManager()->get(SearchProxy::SERVICE_ID);
        $generisSearchWhiteList = $searchProxy->getOption(SearchProxy::OPTION_GENERIS_SEARCH_WHITELIST);
        $generisSearchWhiteList[] = LtiRoles::ACL_CLASS_URI;
        $searchProxy->setOption(SearchProxy::OPTION_GENERIS_SEARCH_WHITELIST, $generisSearchWhiteList);
        $this->getServiceManager()->register(SearchProxy::SERVICE_ID, $searchProxy);
    }
}
