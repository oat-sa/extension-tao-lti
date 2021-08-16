<?php

declare(strict_types=1);

namespace oat\taoLti\migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\Exception\IrreversibleMigration;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\tao\scripts\update\OntologyUpdater;
use oat\taoLti\models\classes\Platform\Repository\LtiPlatformRepositoryInterface;
use oat\taoLti\models\classes\Platform\Repository\RdfLtiPlatformRepository;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202108101321114915_taoLti extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update the Ontology model to install LTI 1.3 Platforms and register LTI Platform repository';
    }

    public function up(Schema $schema): void
    {
        OntologyUpdater::syncModels();

        $this->getServiceManager()->register(
            LtiPlatformRepositoryInterface::SERVICE_ID,
            new RdfLtiPlatformRepository()
        );
    }

    public function down(Schema $schema): void
    {
        throw new IrreversibleMigration('Ontology should be re-synchronized after editing the source files.');
    }
}
