<?php

declare(strict_types=1);

namespace oat\taoLti\migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\Exception\IrreversibleMigration;
use oat\oatbox\reporting\Report;
use oat\tao\scripts\SyncModels;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202110181634585506_taoLti extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Remove AuthorizationServerFactory config file';
    }

    public function up(Schema $schema): void
    {
        $configPath = __DIR__ . '/../config/taoLti/AuthorizationServerFactory.conf.php';

        if (file_exists($configPath) && unlink($configPath)) {
            $this->addReport(Report::createInfo('AuthorizationServerFactory.conf.php has been removed.'));
        }

        $this->addReport(
            Report::createInfo(
                'This migration has breaking changes, please set environment variables instead '
                . 'using AuthorizationServerFactory.conf.php, check README for more details.'
            )
        );
    }

    public function down(Schema $schema): void
    {
        throw new IrreversibleMigration(
            'Configuration file has been removed'
        );
    }
}
