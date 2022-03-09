<?php

declare(strict_types=1);

namespace oat\taoLti\migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoLti\models\classes\Platform\Repository\Lti1p3RegistrationRepository;
use oat\taoLti\models\classes\Platform\Repository\Lti1p3RegistrationSnapshotRepository;
use oat\taoLti\models\classes\Platform\Service\UpdatePlatformRegistrationSnapshotListener;

final class Version202203081458463772_taoLti extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Apply optimized query storage for LTI 1.3 platform registrations';
    }

    public function up(Schema $schema): void
    {
        $this->createTable($schema);

        $this->getServiceManager()->register(
            Lti1p3RegistrationRepository::SERVICE_ID,
            new Lti1p3RegistrationSnapshotRepository(
                [
                    Lti1p3RegistrationSnapshotRepository::OPTION_ROOT_URL => ROOT_URL,
                    Lti1p3RegistrationSnapshotRepository::OPTION_PERSISTENCE_ID => 'default'
                ]
            )
        );

        $this->getServiceManager()->register(
            UpdatePlatformRegistrationSnapshotListener::SERVICE_ID,
            new UpdatePlatformRegistrationSnapshotListener()
        );
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('lti_platform_registration');

        $this->getServiceManager()->register(
            Lti1p3RegistrationRepository::SERVICE_ID,
            new Lti1p3RegistrationRepository(
                [
                    Lti1p3RegistrationRepository::OPTION_ROOT_URL => ROOT_URL,
                ]
            )
        );

        $this->getServiceManager()->unregister(UpdatePlatformRegistrationSnapshotListener::SERVICE_ID);
    }

    private function createTable(Schema $schema): void
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
}
