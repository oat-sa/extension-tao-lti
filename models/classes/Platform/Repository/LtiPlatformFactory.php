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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA
 */

declare(strict_types=1);

namespace oat\taoLti\models\classes\Platform\Repository;

use core_kernel_classes_Resource;
use oat\oatbox\service\ConfigurableService;
use oat\taoLti\models\classes\Platform\LtiPlatformRegistration;

class LtiPlatformFactory extends ConfigurableService
{
    public function createFromResource(core_kernel_classes_Resource $resource): LtiPlatformRegistration
    {
        $propertiesValues = $resource->getPropertiesValues(
            [
                RdfLtiPlatformRepository::LTI_PLATFORM_CLIENT_ID,
                RdfLtiPlatformRepository::LTI_PLATFORM_DEPLOYMENT_ID,
                RdfLtiPlatformRepository::LTI_PLATFORM_AUDIENCE,
                RdfLtiPlatformRepository::LTI_PLATFORM_OAUTH2_ACCESS_TOKEN_URL,
                RdfLtiPlatformRepository::LTI_PLATFORM_OIDC_URL,
                RdfLtiPlatformRepository::LTI_PLATFORM_JWKS_URL,
            ]
        );

        return new LtiPlatformRegistration(
            $resource->getUri(),
            $resource->getLabel(),
            (string)reset($propertiesValues[RdfLtiPlatformRepository::LTI_PLATFORM_AUDIENCE]),
            (string)reset($propertiesValues[RdfLtiPlatformRepository::LTI_PLATFORM_OAUTH2_ACCESS_TOKEN_URL]),
            (string)reset($propertiesValues[RdfLtiPlatformRepository::LTI_PLATFORM_OIDC_URL]),
            (string)reset($propertiesValues[RdfLtiPlatformRepository::LTI_PLATFORM_JWKS_URL]),
            (string)reset($propertiesValues[RdfLtiPlatformRepository::LTI_PLATFORM_CLIENT_ID]),
            (string)reset($propertiesValues[RdfLtiPlatformRepository::LTI_PLATFORM_DEPLOYMENT_ID])
        );
    }
}
