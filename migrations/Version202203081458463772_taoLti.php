<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoLti\migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use oat\tao\scripts\tools\migrations\AbstractMigration;

final class Version202203081458463772_taoLti extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create optimized table for querying LTI 1.3 platform registrations';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('lti_platform_registration');

        $table->addOption('engine', 'InnoDb');

        $table->addColumn('id', Types::INTEGER, ['unsigned' => true, 'autoincrement' => true, 'notnull' => true]);
        $table->addColumn('statement_id', Types::STRING, ['length' => 255, 'notnull' => true]);
        $table->addColumn('name', Types::STRING, ['length' => 255, 'notnull' => true]);
        $table->addColumn('audience', Types::STRING, ['length' => 255, 'notnull' => true]);
        $table->addColumn('client_id', Types::STRING, ['length' => 255, 'notnull' => true]);
        $table->addColumn('deployment_id', Types::STRING, ['length' => 255, 'notnull' => true]);
        $table->addColumn('oidc_authentication_url', Types::STRING, ['length' => 255, 'notnull' => true]);
        $table->addColumn('oauth2_access_token_url', Types::STRING, ['length' => 255, 'notnull' => true]);
        $table->addColumn('jwks_url', Types::STRING, ['length' => 255, 'notnull' => true]);
        $table->addColumn('updated_at', Types::DATETIME_MUTABLE, ['notnull' => true]);

        $table->setPrimaryKey(['id']);
        $table->addIndex(['audience', 'client_id'], "IDX_audience_client_id");
        $table->addIndex(['client_id'], "IDX_client_id");
        $table->addUniqueIndex(['statement_id'], 'UNQ_statement_id');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('lti_platform_registration');
    }
}
