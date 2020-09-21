<?php

declare(strict_types=1);

namespace oat\taoLti\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\oauth\DataStore;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\tao\scripts\update\OntologyUpdater;
use oat\taoLti\models\classes\LtiProvider\ConfigurableLtiProviderRepository;
use oat\taoLti\models\classes\LtiProvider\LtiProviderFieldsMapper;
use oat\taoLti\models\classes\LtiProvider\RdfLtiProviderRepository;
use oat\taoLti\models\classes\LtiProvider\Validation\ValidatorsFactory;

final class Version202009171027563772_taoLti extends AbstractMigration
{

    public function up(Schema $schema): void
    {
        OntologyUpdater::syncModels();

        $this->getServiceManager()->register(
            LtiProviderFieldsMapper::SERVICE_ID,
            new LtiProviderFieldsMapper(
                [
                    LtiProviderFieldsMapper::OPTION_MAP => [
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
            )
        );
        $this->getServiceLocator()->register(
            ValidatorsFactory::SERVICE_ID,
            new ValidatorsFactory(
                [
                    ValidatorsFactory::SERVICE_ID => [
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
                                    'OneOf',
                                    [
                                        'reference' =>
                                            [RdfLtiProviderRepository::LTI_TOOL_PUBLIC_KEY,],

                                    ]
                                ],
                            ],
                            RdfLtiProviderRepository::LTI_TOOL_PUBLIC_KEY => [
                                [
                                    'OneOf',
                                    [
                                        'reference' =>
                                            [RdfLtiProviderRepository::LTI_TOOL_JWKS_URL,],

                                    ]
                                ],
                            ],
                        ],
                    ]
                ]
            )
        );
    }

    public function down(Schema $schema): void
    {
    }
}
