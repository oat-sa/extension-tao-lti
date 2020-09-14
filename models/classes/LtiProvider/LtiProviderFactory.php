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

use core_kernel_classes_Resource;
use InvalidArgumentException;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\oauth\DataStore;

class LtiProviderFactory extends ConfigurableService
{
    public function createFromResource(core_kernel_classes_Resource $resource): LtiProvider
    {
        $propertiesValues = $resource->getPropertiesValues(
            [
                DataStore::PROPERTY_OAUTH_KEY,
                DataStore::PROPERTY_OAUTH_SECRET,
                DataStore::PROPERTY_OAUTH_CALLBACK,
                RdfLtiProviderRepository::LTI_VERSION,
                RdfLtiProviderRepository::LTI_TOOL_IDENTIFIER,
                RdfLtiProviderRepository::LTI_TOOL_NAME,
                RdfLtiProviderRepository::LTI_TOOL_CLIENT_ID,
                RdfLtiProviderRepository::LTI_TOOL_DEPLOYMENT_IDS,
                RdfLtiProviderRepository::LTI_TOOL_AUDIENCE,
                RdfLtiProviderRepository::LTI_TOOL_LAUNCH_URL,
                RdfLtiProviderRepository::LTI_TOOL_OIDC_LOGIN_INITATION_URL,
                RdfLtiProviderRepository::LTI_TOOL_JWKS_URL,
                RdfLtiProviderRepository::LTI_TOOL_PUBLIC_KEY,
            ]
        );

        return new LtiProvider(
            $resource->getUri(),
            $resource->getLabel(),
            (string)reset($propertiesValues[DataStore::PROPERTY_OAUTH_KEY]),
            (string)reset($propertiesValues[DataStore::PROPERTY_OAUTH_SECRET]),
            (string)reset($propertiesValues[DataStore::PROPERTY_OAUTH_CALLBACK]),
            [],
            $this->getLtiVersion($propertiesValues),
            (string)reset($propertiesValues[RdfLtiProviderRepository::LTI_TOOL_IDENTIFIER]),
            (string)reset($propertiesValues[RdfLtiProviderRepository::LTI_TOOL_NAME]),
            (string)reset($propertiesValues[RdfLtiProviderRepository::LTI_TOOL_CLIENT_ID]),
            $this->getDeploymentIds($propertiesValues),
            (string)reset($propertiesValues[RdfLtiProviderRepository::LTI_TOOL_AUDIENCE]),
            (string)reset($propertiesValues[RdfLtiProviderRepository::LTI_TOOL_OIDC_LOGIN_INITATION_URL]),
            (string)reset($propertiesValues[RdfLtiProviderRepository::LTI_TOOL_LAUNCH_URL]),
            (string)reset($propertiesValues[RdfLtiProviderRepository::LTI_TOOL_PUBLIC_KEY]),
            (string)reset($propertiesValues[RdfLtiProviderRepository::LTI_TOOL_JWKS_URL])
        );
    }

    public function createFromArray(array $provider): LtiProvider
    {
        $keys = ['uri', 'label', 'key', 'secret', 'callback_url'];

        foreach ($keys as $key) {
            if (!isset($provider[$key])) {
                throw new InvalidArgumentException(sprintf('Missing key \'%s\' in LTI provider list.', $key));
            }
        }

        return new LtiProvider(
            $provider['uri'],
            $provider['label'],
            $provider['key'],
            $provider['secret'],
            $provider['callback_url'],
            $provider['roles'] ?? []
        );
    }

    private function getLtiVersion(array $propertiesValues): string
    {
        if (empty($propertiesValues[RdfLtiProviderRepository::LTI_VERSION])) {
            return '1.1';
        }

        $version = (string)reset($propertiesValues[RdfLtiProviderRepository::LTI_VERSION])->getUri();

        return $version === RdfLtiProviderRepository::LTI_V_13 ? '1.3' : '1.1';
    }

    private function getDeploymentIds(array $propertiesValues): array
    {
        return array_filter(
            explode(
                ',',
                (string)reset($propertiesValues[RdfLtiProviderRepository::LTI_TOOL_DEPLOYMENT_IDS])
            )
        );
    }
}
