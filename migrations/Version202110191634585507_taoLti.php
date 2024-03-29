<?php

declare(strict_types=1);

namespace oat\taoLti\migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\Exception\IrreversibleMigration;
use oat\oatbox\reporting\Report;
use oat\tao\scripts\tools\migrations\AbstractMigration;

final class Version202110191634585507_taoLti extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove AuthorizationServerFactory config file';
    }

    public function up(Schema $schema): void
    {
        $configPath = __DIR__ . '/../../config/taoLti/AuthorizationServerFactory.conf.php';

        if (is_writable($configPath)) {
            $content = @file_get_contents($configPath);

            if (unlink($configPath)) {
                $this->addReport(Report::createInfo('AuthorizationServerFactory.conf.php has been removed. You can see deleted configuration below:'));

                $content && $this->addReport(Report::createInfo($content));
            }
        }

        $this->addReport(
            Report::createWarning(
                'This migration has breaking changes, please set environment variables instead of '
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
