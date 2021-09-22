<?php

declare(strict_types=1);

namespace oat\taoLti\migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\Exception\IrreversibleMigration;
use oat\tao\scripts\SyncModels;
use oat\tao\scripts\tools\migrations\AbstractMigration;

final class Version202109040100000000_taoLti extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add LTI 1p3 Learner Role';
    }

    public function up(Schema $schema): void
    {
        $this->addReport(
            $this->propagate(new SyncModels())([])
        );
    }

    public function down(Schema $schema): void
    {
        throw new IrreversibleMigration(
            'The models should be updated via `SyncModels` script after reverting their RDF definitions.'
        );
    }
}
