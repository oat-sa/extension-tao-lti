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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\taoLti\controller;

use oat\oatbox\validator\ValidatorInterface;
use oat\taoLti\models\classes\Platform\Validation\ValidatorsFactory;
use oat\taoLti\models\classes\Platform\Repository\RdfLtiPlatformRepository;
use tao_actions_SaSModule;

/**
 * This controller allows the additon and deletion
 * of LTI 1.3 Platforms
 *
 * @package taoLti
 * @license GPLv2 http://www.opensource.org/licenses/gpl-2.0.php
 */
class PlatformAdmin extends tao_actions_SaSModule
{
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
