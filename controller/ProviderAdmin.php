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
 * Copyright (c) 2019-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

declare(strict_types=1);

namespace oat\taoLti\controller;

use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\tao\model\oauth\DataStore;
use oat\taoLti\models\classes\FeatureFlag\LtiFeatures;
use oat\taoLti\models\classes\LtiProvider\FeatureFlagFormPropertyMapper;
use oat\taoLti\models\classes\LtiProvider\RdfLtiProviderRepository;
use tao_actions_form_CreateInstance;
use tao_actions_SaSModule;
use tao_helpers_form_Form;

/**
 * This controller allows the adding and deletion of LTI Oauth Providers
 *
 * @package taoLti
 * @license GPLv2 http://www.opensource.org/licenses/gpl-2.0.php
 */
class ProviderAdmin extends tao_actions_SaSModule
{
    protected function getExcludedProperties(): array
    {
        return $this->getFeatureFlagFormPropertyMapper()->getExcludedProperties();
    }

    private function getFeatureFlagFormPropertyMapper(): FeatureFlagFormPropertyMapper
    {
        return $this->getServiceLocator()->get(FeatureFlagFormPropertyMapper::class);
    }

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
}
