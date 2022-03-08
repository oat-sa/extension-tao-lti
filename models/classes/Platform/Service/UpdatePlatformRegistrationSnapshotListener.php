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
use oat\oatbox\service\ConfigurableService;
use oat\taoLti\models\classes\Platform\Repository\Lti1p3RegistrationRepository;
use oat\taoLti\models\classes\Platform\Repository\Lti1p3RegistrationSnapshotRepository;
use oat\taoLti\models\classes\Platform\Repository\LtiPlatformFactory;

class UpdatePlatformRegistrationSnapshotListener extends ConfigurableService
{
    public const SERVICE_ID = 'taoLti/UpdatePlatformRegistrationSnapshotListener';

    public function whenResourceCreated(ResourceCreated $event): void
    {
        $ltiPlatformRegistration = $this->getLtiPlatformFactory()
            ->createFromResource($event->getResource());

        $this->getRepository()->save($ltiPlatformRegistration);
    }

    private function getLtiPlatformFactory(): LtiPlatformFactory
    {
        return $this->getServiceLocator()->get(LtiPlatformFactory::class);
    }

    private function getRepository(): Lti1p3RegistrationSnapshotRepository
    {
        return $this->getServiceLocator()->get(Lti1p3RegistrationRepository::SERVICE_ID);
    }
}
