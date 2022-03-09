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
 * Copyright (c) 2021-2022 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\taoLti\controller;

use oat\generis\model\data\event\ResourceCreated;
use oat\generis\model\data\event\ResourceDeleted;
use oat\generis\model\data\event\ResourceUpdated;
use oat\oatbox\event\EventManager;
use oat\oatbox\validator\ValidatorInterface;
use oat\taoLti\models\classes\Platform\Service\UpdatePlatformRegistrationSnapshotListener;
use oat\taoLti\models\classes\Platform\Validation\ValidatorsFactory;
use oat\taoLti\models\classes\Platform\Repository\RdfLtiPlatformRepository;
use tao_actions_SaSModule;

/**
 * Admin interface for managing LTI 1.3 Platforms entries.
 *
 * @package taoLti
 * @license GPLv2 http://www.opensource.org/licenses/gpl-2.0.php
 */
class PlatformAdmin extends tao_actions_SaSModule
{
    /**
     * @inheritDoc
     */
    public function initialize()
    {
        parent::initialize();

        /** @var EventManager $eventManager */
        $eventManager = $this->getServiceManager()->get(EventManager::SERVICE_ID);

        $eventManager->attach(
            ResourceCreated::class,
            [UpdatePlatformRegistrationSnapshotListener::SERVICE_ID, 'whenResourceCreated']
        );

        $eventManager->attach(
            ResourceUpdated::class,
            [UpdatePlatformRegistrationSnapshotListener::SERVICE_ID, 'whenResourceUpdated']
        );

        $eventManager->attach(
            ResourceDeleted::class,
            [UpdatePlatformRegistrationSnapshotListener::SERVICE_ID, 'whenResourceDeleted']
        );
    }

    /**
     * @inheritDoc
     */
    protected function getClassService()
    {
        return $this->getServiceLocator()->get(RdfLtiPlatformRepository::class);
    }

    /**
     * @return ValidatorInterface[][]
     */
    protected function getExtraValidationRules(): array
    {
        return $this->getValidationFactory()->createFormValidators();
    }

    private function getValidationFactory(): ValidatorsFactory
    {
        return $this->getServiceLocator()->get(ValidatorsFactory::class);
    }
}
