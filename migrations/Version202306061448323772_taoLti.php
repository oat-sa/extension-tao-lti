<?php

declare(strict_types=1);

namespace oat\taoLti\migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\Exception\IrreversibleMigration;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\tao\scripts\update\OntologyUpdater;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202306061448323772_taoLti extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Refresh rdf database';
    }

    public function up(Schema $schema): void
    {
        OntologyUpdater::syncModels();
    }

    public function down(Schema $schema): void
    {
        throw new IrreversibleMigration(
            'The models should be updated via `SyncModels` script after reverting their RDF definitions.'
        );
    }
}
