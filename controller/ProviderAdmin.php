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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\taoLti\controller;

use oat\taoLti\models\classes\LtiProvider\LtiProviderFieldsMapper;
use oat\taoLti\models\classes\LtiProvider\Validation\LtiProviderValidationService;
use oat\taoLti\models\classes\LtiProvider\RdfLtiProviderRepository;
use oat\taoLti\models\classes\LtiProvider\Validation\ValidatorsFactory;
use tao_actions_SaSModule;
use tao_helpers_Uri;

/**
 * This controller allows the adding and deletion of LTI Oauth Providers
 *
 * @license GPLv2 http://www.opensource.org/licenses/gpl-2.0.php
 */
class ProviderAdmin extends tao_actions_SaSModule
{
    /**
     * (non-PHPdoc)
     *
     * @see \tao_actions_RdfController::getClassService()
     * @security("hide");
     */
    public function getClassService()
    {
        return $this->getServiceLocator()->get(RdfLtiProviderRepository::class);
    }

    protected function getExtraValidationRules(): array
    {
        return $this->getValidationFactory()->createFormValidators($this->getLtiVersion());
    }

    private function getValidationFactory(): ValidatorsFactory
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(ValidatorsFactory::SERVICE_ID);
    }

    private function getConfigurationMapper(): LtiProviderFieldsMapper
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(LtiProviderFieldsMapper::SERVICE_ID);
    }

    private function getLtiVersion(): string
    {
        $body = $this->getPsrRequest()->getParsedBody();

        $rawLtiVersion = $body[tao_helpers_Uri::encode(
            RdfLtiProviderRepository::LTI_VERSION
        )] ?? RdfLtiProviderRepository::DEFAULT_LTI_VERSION;

        return $this->getConfigurationMapper()->map(tao_helpers_Uri::decode($rawLtiVersion));
    }
}
