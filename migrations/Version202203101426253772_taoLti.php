<?php

declare(strict_types=1);

namespace oat\taoLti\migrations;

use Doctrine\DBAL\Schema\Schema;
use OAT\Library\Lti1p3Core\Registration\RegistrationRepositoryInterface;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoLti\models\classes\Platform\Repository\Lti1p3RegistrationSnapshotRepository;
use oat\taoLti\models\classes\Platform\Repository\LtiPlatformRepositoryInterface;

final class Version202203101426253772_taoLti extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Populate LTI 1.3 platform registration table';
    }

    public function up(Schema $schema): void
    {
        /** @var LtiPlatformRepositoryInterface $rdfRepository */
        $ltiRepository = $this->getServiceLocator()->get(LtiPlatformRepositoryInterface::SERVICE_ID);

        /** @var Lti1p3RegistrationSnapshotRepository $snaptshotRepository */
        $snapshotRepository = $this->getServiceLocator()->getContainer()->get(RegistrationRepositoryInterface::class);

        foreach ($ltiRepository->findAll() as $registration) {
            $snapshotRepository->save($registration);
        }
    }

    public function down(Schema $schema): void
    {
    }
}
