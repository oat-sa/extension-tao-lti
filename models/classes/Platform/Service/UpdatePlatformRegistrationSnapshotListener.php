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
 *
 * @author Ricardo Quintanilha <ricardo.quintanilha@taotesting.com>
 */

declare(strict_types=1);

namespace oat\taoLti\models\classes\Platform\Service;

use oat\generis\model\data\event\ResourceCreated;
use oat\generis\model\data\event\ResourceDeleted;
use oat\generis\model\data\event\ResourceUpdated;
use oat\taoLti\models\classes\Platform\Repository\Lti1p3RegistrationSnapshotRepository;
use oat\taoLti\models\classes\Platform\Repository\LtiPlatformFactory;

class UpdatePlatformRegistrationSnapshotListener
{
    /** @var Lti1p3RegistrationSnapshotRepository */
    private $registrationSnapshotRepository;

    /** @var LtiPlatformFactory */
    private $ltiPlatformFactory;

    public function __construct(
        Lti1p3RegistrationSnapshotRepository $registrationSnapshotRepository,
        LtiPlatformFactory $ltiPlatformFactory
    ) {
        $this->registrationSnapshotRepository = $registrationSnapshotRepository;
        $this->ltiPlatformFactory = $ltiPlatformFactory;
    }

    public function whenResourceCreated(ResourceCreated $event): void
    {
        $ltiPlatformRegistration = $this->ltiPlatformFactory->createFromResource($event->getResource());

        $this->registrationSnapshotRepository->save($ltiPlatformRegistration);
    }

    public function whenResourceUpdated(ResourceUpdated $event): void
    {
        $ltiPlatformRegistration = $this->ltiPlatformFactory->createFromResource($event->getResource());

        $this->registrationSnapshotRepository->save($ltiPlatformRegistration);
    }

    public function whenResourceDeleted(ResourceDeleted $event): void
    {
        $this->registrationSnapshotRepository->deleteByStatementId($event->getId());
    }
}
