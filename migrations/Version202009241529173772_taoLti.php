<?php

declare(strict_types=1);

namespace oat\taoLti\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\menu\SectionVisibilityFilter;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoLti\models\classes\LtiProvider\FeatureFlagFormPropertyMapper;
use oat\taoLti\models\classes\LtiProvider\RdfLtiProviderRepository;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202009241529173772_taoLti extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Disable LTI 1.3 section and LtiProvider form fields base on LTI1P3 Feature flag';
    }

    public function up(Schema $schema): void
    {
        $this->getServiceManager()->register(
            FeatureFlagFormPropertyMapper::SERVICE_ID,
            new FeatureFlagFormPropertyMapper(
                [
                    FeatureFlagFormPropertyMapper::OPTION_FEATURE_FLAG_FORM_FIELDS => [
                        RdfLtiProviderRepository::LTI_TOOL_IDENTIFIER => [
                            'LTI1P3'
                        ],
                        RdfLtiProviderRepository::LTI_TOOL_PUBLIC_KEY => [
                            'LTI1P3'
                        ],
                        RdfLtiProviderRepository::LTI_TOOL_JWKS_URL => [
                            'LTI1P3'
                        ],
                        RdfLtiProviderRepository::LTI_TOOL_LAUNCH_URL => [
                            'LTI1P3'
                        ],
                        RdfLtiProviderRepository::LTI_TOOL_OIDC_LOGIN_INITATION_URL => [
                            'LTI1P3'
                        ],
                        RdfLtiProviderRepository::LTI_TOOL_DEPLOYMENT_IDS => [
                            'LTI1P3'
                        ],
                        RdfLtiProviderRepository::LTI_TOOL_AUDIENCE => [
                            'LTI1P3'
                        ],
                        RdfLtiProviderRepository::LTI_TOOL_CLIENT_ID => [
                            'LTI1P3'
                        ],
                        RdfLtiProviderRepository::LTI_TOOL_NAME => [
                            'LTI1P3'
                        ],
                        RdfLtiProviderRepository::LTI_TOOL_IDENTIFIER => [
                            'LTI1P3'
                        ],
                        RdfLtiProviderRepository::LTI_VERSION => [
                            'LTI1P3'
                        ],
                    ]
                ]
            )
        );

        $this->getServiceManager()->register(
            SectionVisibilityFilter::SERVICE_ID,
            new SectionVisibilityFilter(
                [
                    SectionVisibilityFilter::OPTION_FEATURE_FLAG_SECTIONS => [
                        'settings_manage_lti_keys' => [
                            'LTI1P3'
                        ]
                    ]
                ]
            )
        );
    }

    public function down(Schema $schema): void
    {
        $this->getServiceManager()->unregister(FeatureFlagFormPropertyMapper::SERVICE_ID);
    }
}
