<?php

declare(strict_types=1);

namespace oat\taoLti\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoLti\scripts\install\RegisterPortalTheme;

/**
 * Auto-generated Migration: Please modify to your needs!
 *
 * phpcs:disable Squiz.Classes.ValidClassName
 */
final class Version202406101417393772_taoLti extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'This will revert change introduced in Version202406060802293772_taoLti.php for instances 
        with taoStyle enabled.';
    }

    public function up(Schema $schema): void
    {
        $this->runAction(new RegisterPortalTheme());
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
