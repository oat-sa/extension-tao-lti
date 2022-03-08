<?php

declare(strict_types=1);

namespace oat\taoLti\migrations;

use Doctrine\DBAL\Schema\Schema;
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

        // MetadataModified
        // ClassDeletedEvent
        // ResourceDeleted
    }

    public function down(Schema $schema): void
    {
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
}
