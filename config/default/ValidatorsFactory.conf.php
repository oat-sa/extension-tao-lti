<?php

use oat\tao\model\oauth\DataStore;
use oat\taoLti\models\classes\LtiProvider\RdfLtiProviderRepository;
use oat\taoLti\models\classes\LtiProvider\Validation\ValidatorsFactory;

return new ValidatorsFactory(
    [
        ValidatorsFactory::OPTION_VALIDATORS => [
            [
                '1.1' => [
                    DataStore::PROPERTY_OAUTH_KEY => [['notEmpty']],
                    DataStore::PROPERTY_OAUTH_SECRET => [['notEmpty']],
                    RdfLtiProviderRepository::LTI_VERSION => [['notEmpty']],
                ],
                '1.3' => [
                    RdfLtiProviderRepository::LTI_VERSION => [['notEmpty']],
                    RdfLtiProviderRepository::LTI_TOOL_CLIENT_ID => [['notEmpty']],
                    RdfLtiProviderRepository::LTI_TOOL_IDENTIFIER => [['notEmpty']],
                    RdfLtiProviderRepository::LTI_TOOL_NAME => [['notEmpty']],
                    RdfLtiProviderRepository::LTI_TOOL_DEPLOYMENT_IDS => [['notEmpty']],
                    RdfLtiProviderRepository::LTI_TOOL_AUDIENCE => [['notEmpty']],
                    RdfLtiProviderRepository::LTI_TOOL_OIDC_LOGIN_INITATION_URL => [['notEmpty'], ['url']],
                    RdfLtiProviderRepository::LTI_TOOL_LAUNCH_URL => [['url']],
                    RdfLtiProviderRepository::LTI_TOOL_JWKS_URL => [
                        [
                            'AnyOf',
                            [
                                'reference' =>
                                    [RdfLtiProviderRepository::LTI_TOOL_PUBLIC_KEY,],

                            ]
                        ],
                    ],
                    RdfLtiProviderRepository::LTI_TOOL_PUBLIC_KEY => [
                        [
                            'AnyOf',
                            [
                                'reference' =>
                                    [RdfLtiProviderRepository::LTI_TOOL_JWKS_URL,],

                            ]
                        ],
                    ]
                ],
            ]
        ]
    ]
);
