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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoLti\models\classes\LtiProvider;

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\featureFlag\FeatureFlagChecker;

class FeatureFlagFormPropertyMapper extends ConfigurableService
{
    public const LTI_1P3_ONLY_FIELDS = [
        RdfLtiProviderRepository::LTI_TOOL_IDENTIFIER,
        RdfLtiProviderRepository::LTI_TOOL_PUBLIC_KEY,
        RdfLtiProviderRepository::LTI_TOOL_JWKS_URL,
        RdfLtiProviderRepository::LTI_TOOL_LAUNCH_URL,
        RdfLtiProviderRepository::LTI_TOOL_OIDC_LOGIN_INITATION_URL,
        RdfLtiProviderRepository::LTI_TOOL_DEPLOYMENT_IDS,
        RdfLtiProviderRepository::LTI_TOOL_AUDIENCE,
        RdfLtiProviderRepository::LTI_TOOL_CLIENT_ID,
        RdfLtiProviderRepository::LTI_TOOL_NAME,
        RdfLtiProviderRepository::LTI_TOOL_IDENTIFIER,
        RdfLtiProviderRepository::LTI_VERSION,
    ];

    public function getExcludedProperties(): array
    {
        if (!$this->getFeatureFlagChecker()->isEnabled('LTI1P3'))
        {
            return self::LTI_1P3_ONLY_FIELDS;
        }

        return [];
    }

    private function getFeatureFlagChecker(): FeatureFlagChecker
    {
        return $this->getServiceLocator()->get(FeatureFlagChecker::class);
    }
}
