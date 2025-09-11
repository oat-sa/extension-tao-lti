<?php

declare(strict_types=1);

namespace oat\taoLti\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoLti\scripts\install\RegisterLtiAclRoleProvider;

/**
 * Auto-generated Migration: Please modify to your needs!
 *
 * phpcs:disable Squiz.Classes.ValidClassName
 */
final class Version202509091438353773_taoLti extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'This migration registers the LTI ACL role provider.';
    }

    public function up(Schema $schema): void
    {
        $this->runAction(new RegisterLtiAclRoleProvider());
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
