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
        /** @var LtiPlatformRepositoryInterface $ltiRepository */
        $ltiRepository = $this->getServiceLocator()->get(LtiPlatformRepositoryInterface::SERVICE_ID);

        /** @var Lti1p3RegistrationSnapshotRepository $snapshotRepository */
        $snapshotRepository = $this->getServiceLocator()->getContainer()->get(RegistrationRepositoryInterface::class);

        foreach ($ltiRepository->findAll() as $registration) {
            $snapshotRepository->save($registration);
        }
    }

    public function down(Schema $schema): void
    {
    }
}
