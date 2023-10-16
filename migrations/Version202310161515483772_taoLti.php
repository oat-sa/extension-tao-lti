<?php

declare(strict_types=1);

namespace oat\taoLti\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\SyncModels;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * phpcs:disable Squiz.Classes.ValidClassName
 */
final class Version202310161515483772_taoLti extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update Ontology models';
    }


    public function up(Schema $schema): void
    {
        $this->addReport(
            $this->propagate(new SyncModels())([])
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException(
            'The models should be updated via `SyncModels` script after reverting their RDF definitions.'
        );
    }
}
