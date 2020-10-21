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

use core_kernel_classes_Literal;
use core_kernel_classes_Resource;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\oauth\DataStore;
use oat\taoLti\models\classes\LtiProvider\Validation\LtiProviderValidator;

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
        $ltiVersion = $provider[ConfigurableLtiProviderRepository::LTI_VERSION] ?? '1.1';

        $this->getValidationService()->validateArray($ltiVersion, $provider);

        return new LtiProvider(
            $provider['uri'],
            $provider['label'],
            $provider['key'],
            $provider['secret'],
            $provider['callback_url'],
            $provider['roles'] ?? [],
            $ltiVersion,
            $provider[ConfigurableLtiProviderRepository::LTI_TOOL_IDENTIFIER] ?? null,
            $provider[ConfigurableLtiProviderRepository::LTI_TOOL_NAME] ?? null,
            $provider[ConfigurableLtiProviderRepository::LTI_TOOL_CLIENT_ID] ?? null,
            $provider[ConfigurableLtiProviderRepository::LTI_TOOL_DEPLOYMENT_IDS] ?? [],
            $provider[ConfigurableLtiProviderRepository::LTI_TOOL_AUDIENCE] ?? null,
            $provider[ConfigurableLtiProviderRepository::LTI_TOOL_OIDC_LOGIN_INITATION_URL] ?? null,
            $provider[ConfigurableLtiProviderRepository::LTI_TOOL_LAUNCH_URL] ?? null,
            $provider[ConfigurableLtiProviderRepository::LTI_TOOL_PUBLIC_KEY] ?? null,
            $provider[ConfigurableLtiProviderRepository::LTI_TOOL_JWKS_URL] ?? null
        );
    }

    private function getLtiVersion(array $propertiesValues): string
    {
        $ltiVersionResource = reset($propertiesValues[RdfLtiProviderRepository::LTI_VERSION]);

        if ($ltiVersionResource instanceof core_kernel_classes_Literal && !empty(trim($ltiVersionResource->literal))) {
            return $ltiVersionResource->literal === RdfLtiProviderRepository::LTI_V_13 ? '1.3' : '1.1';
        }

        if (!$ltiVersionResource || !$ltiVersionResource instanceof core_kernel_classes_Resource) {
            return '1.1';
        }

        $version = (string) $ltiVersionResource->getUri();

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

    private function getValidationService(): LtiProviderValidator
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(LtiProviderValidator::class);
    }
}
