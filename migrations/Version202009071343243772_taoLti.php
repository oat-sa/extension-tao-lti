<?php

declare(strict_types=1);

namespace oat\taoLti\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\tao\scripts\update\OntologyUpdater;
use oat\taoLti\models\classes\Platform\Repository\Lti1p3RegistrationRepository;

final class Version202009071343243772_taoLti extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->getServiceManager()->register(
            Lti1p3RegistrationRepository::SERVICE_ID,
            new Lti1p3RegistrationRepository(
                [
                    Lti1p3RegistrationRepository::OPTION_ROOT_URL => ROOT_URL,
                ]
            )
        );

        OntologyUpdater::syncModels();
    }

    public function down(Schema $schema): void
    {
        $this->getServiceManager()->unregister(Lti1p3RegistrationRepository::SERVICE_ID);
    }
}
