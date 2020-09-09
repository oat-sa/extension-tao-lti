<?php

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
                RdfLtiProviderRepository::LTI_TOOL_CLIENT_ID,
                RdfLtiProviderRepository::LTI_TOOL_AUDIENCE,
                RdfLtiProviderRepository::LTI_TOOL_OIDC_LOGIN_INITATION_URL,
                RdfLtiProviderRepository::LTI_TOOL_LAUNCH_URL,
                RdfLtiProviderRepository::LTI_TOOL_PUBLIC_KEY,
            ]
        );

        $version = (string)reset(
            $propertiesValues[RdfLtiProviderRepository::LTI_VERSION]
        ) === RdfLtiProviderRepository::LTI_V_11 ? '1.1' : '1.3';

        $toolDeploymentIds = explode(
            ',',
            (string)reset($propertiesValues[RdfLtiProviderRepository::LTI_TOOL_DEPLOYMENT_IDS])
        );

        return new LtiProvider(
            $resource->getUri(),
            $resource->getLabel(),
            (string)reset($propertiesValues[DataStore::PROPERTY_OAUTH_KEY]),
            (string)reset($propertiesValues[DataStore::PROPERTY_OAUTH_SECRET]),
            (string)reset($propertiesValues[DataStore::PROPERTY_OAUTH_CALLBACK]),
            [],
            $version,
            (string)reset($propertiesValues[RdfLtiProviderRepository::LTI_TOOL_CLIENT_ID]),
            is_array($toolDeploymentIds) ?: [],
            (string)reset($propertiesValues[RdfLtiProviderRepository::LTI_TOOL_AUDIENCE]),
            (string)reset($propertiesValues[RdfLtiProviderRepository::LTI_TOOL_OIDC_LOGIN_INITATION_URL]),
            (string)reset($propertiesValues[RdfLtiProviderRepository::LTI_TOOL_LAUNCH_URL]),
            (string)reset($propertiesValues[RdfLtiProviderRepository::LTI_TOOL_PUBLIC_KEY])
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
}
