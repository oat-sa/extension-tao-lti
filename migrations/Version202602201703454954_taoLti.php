<?php

declare(strict_types=1);

namespace oat\taoLti\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\tao\scripts\update\OntologyUpdater;

/**
 * Auto-generated Migration: Please modify to your needs!
 *
 * phpcs:disable Squiz.Classes.ValidClassName
 */
final class Version202602201703454954_taoLti extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add missed ItemAuthor role for LTI 1p3 Content Developer users';
    }

    public function up(Schema $schema): void
    {
        OntologyUpdater::syncModels();
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
