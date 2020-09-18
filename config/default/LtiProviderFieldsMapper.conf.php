<?php

use oat\tao\model\oauth\DataStore;
use oat\taoLti\models\classes\LtiProvider\ConfigurableLtiProviderRepository;
use oat\taoLti\models\classes\LtiProvider\LtiProviderFieldsMapper;
use oat\taoLti\models\classes\LtiProvider\RdfLtiProviderRepository;

return new LtiProviderFieldsMapper(
    [
        LtiProviderFieldsMapper::OPTION_MAP =>
            [
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
            ]
    ]
);
