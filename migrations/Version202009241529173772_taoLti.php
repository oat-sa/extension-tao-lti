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
                        RdfLtiProviderRepository::LTI_TOOL_IDENTIFIER => [
                            'FEATURE_FLAG_LTI1P3'
                        ],
                        RdfLtiProviderRepository::LTI_VERSION => [
                            'FEATURE_FLAG_LTI1P3'
                        ],
                    ]
                ]
            )
        );

        $sectionVisibilityFilter = $this->getServiceManager()->get(SectionVisibilityFilter::SERVICE_ID);
        $featureFlagSections = $sectionVisibilityFilter->getOption(SectionVisibilityFilter::OPTION_FEATURE_FLAG_SECTIONS);
        $featureFlagSections['settings_manage_lti_keys'] = 'FEATURE_FLAG_LTI1P3';
        $sectionVisibilityFilter->setOption(SectionVisibilityFilter::OPTION_FEATURE_FLAG_SECTIONS, $featureFlagSections);
        $this->getServiceManager()->register(SectionVisibilityFilter::SERVICE_ID, $sectionVisibilityFilter);
    }

    public function down(Schema $schema): void
    {
        $this->getServiceManager()->unregister(FeatureFlagFormPropertyMapper::SERVICE_ID);
    }
}
