<?php

declare(strict_types=1);

namespace oat\taoLti\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoLti\models\classes\LtiProvider\FeatureFlagFormPropertyMapper;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202009241529173772_taoLti extends AbstractMigration
{

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->getServiceManager()->register(
            FeatureFlagFormPropertyMapper::SERVICE_ID,
            new FeatureFlagFormPropertyMapper(
                [
                    FeatureFlagFormPropertyMapper::OPTION_FEATURE_FLAG_FORM_FIELDS => [],
                ]
            )
        );

    }

    public function down(Schema $schema): void
    {
        $this->getServiceManager()->unregister(FeatureFlagFormPropertyMapper::SERVICE_ID);
    }
}
