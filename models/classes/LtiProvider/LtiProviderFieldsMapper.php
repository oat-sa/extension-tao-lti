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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA
 */

declare(strict_types=1);

namespace oat\taoLti\models\classes\LtiProvider;

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\oauth\DataStore;

class LtiProviderFieldsMapper extends ConfigurableService
{
    private const MAP = [
        RdfLtiProviderRepository::LTI_VERSION => ConfigurableLtiProviderRepository::LTI_VERSION,
        RdfLtiProviderRepository::LTI_TOOL_CLIENT_ID => ConfigurableLtiProviderRepository::LTI_TOOL_CLIENT_ID,
        RdfLtiProviderRepository::LTI_TOOL_IDENTIFIER => ConfigurableLtiProviderRepository::LTI_TOOL_IDENTIFIER,
        RdfLtiProviderRepository::LTI_TOOL_NAME => ConfigurableLtiProviderRepository::LTI_TOOL_NAME,
        RdfLtiProviderRepository::LTI_TOOL_DEPLOYMENT_IDS => ConfigurableLtiProviderRepository::LTI_TOOL_DEPLOYMENT_IDS,
        RdfLtiProviderRepository::LTI_TOOL_AUDIENCE => ConfigurableLtiProviderRepository::LTI_TOOL_AUDIENCE,
        RdfLtiProviderRepository::LTI_TOOL_OIDC_LOGIN_INITATION_URL => ConfigurableLtiProviderRepository::LTI_TOOL_OIDC_LOGIN_INITATION_URL,
        RdfLtiProviderRepository::LTI_TOOL_LAUNCH_URL => ConfigurableLtiProviderRepository::LTI_TOOL_LAUNCH_URL,
        RdfLtiProviderRepository::LTI_TOOL_JWKS_URL => ConfigurableLtiProviderRepository::LTI_TOOL_JWKS_URL,
        RdfLtiProviderRepository::LTI_TOOL_PUBLIC_KEY => ConfigurableLtiProviderRepository::LTI_TOOL_PUBLIC_KEY,
        DataStore::PROPERTY_OAUTH_SECRET => 'secret',
        DataStore::PROPERTY_OAUTH_KEY => 'key',
        RdfLtiProviderRepository::LTI_V_11 => '1.1',
        RdfLtiProviderRepository::LTI_V_13 => '1.3',
    ];

    public function map(string $rdfUri): ?string
    {
        return self::MAP [$rdfUri] ?? null;
    }
}
