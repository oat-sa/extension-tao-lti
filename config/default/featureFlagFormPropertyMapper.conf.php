<?php

use oat\taoLti\models\classes\LtiProvider\FeatureFlagFormPropertyMapper;
use oat\taoLti\models\classes\LtiProvider\RdfLtiProviderRepository;

return new FeatureFlagFormPropertyMapper(
    [
        FeatureFlagFormPropertyMapper::OPTION_FEATURE_FLAG_FORM_FIELDS => [
            RdfLtiProviderRepository::LTI_TOOL_IDENTIFIER => [
                'FEATURE_FLAG_LTI1P3'
            ],
            RdfLtiProviderRepository::LTI_TOOL_PUBLIC_KEY => [
                'FEATURE_FLAG_LTI1P3'
            ],
            RdfLtiProviderRepository::LTI_TOOL_JWKS_URL => [
                'FEATURE_FLAG_LTI1P3'
            ],
            RdfLtiProviderRepository::LTI_TOOL_LAUNCH_URL => [
                'FEATURE_FLAG_LTI1P3'
            ],
            RdfLtiProviderRepository::LTI_TOOL_OIDC_LOGIN_INITATION_URL => [
                'FEATURE_FLAG_LTI1P3'
            ],
            RdfLtiProviderRepository::LTI_TOOL_DEPLOYMENT_IDS => [
                'FEATURE_FLAG_LTI1P3'
            ],
            RdfLtiProviderRepository::LTI_TOOL_AUDIENCE => [
                'FEATURE_FLAG_LTI1P3'
            ],
            RdfLtiProviderRepository::LTI_TOOL_CLIENT_ID => [
                'FEATURE_FLAG_LTI1P3'
            ],
            RdfLtiProviderRepository::LTI_TOOL_NAME => [
                'FEATURE_FLAG_LTI1P3'
            ],
            RdfLtiProviderRepository::LTI_VERSION => [
                'FEATURE_FLAG_LTI1P3'
            ],
        ]
    ]
);
