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

declare(strict_types=1);

namespace oat\taoLti\controller;

use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\tao\model\oauth\DataStore;
use oat\taoLti\models\classes\FeatureFlag\LtiFeatures;
use oat\taoLti\models\classes\LtiProvider\LtiProviderFormFactory;
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
    public function addLtiInstanceForm()
    {
        $form = $this->getProviderFormFactory()->create();

        if ($form->isSubmited() && $form->isValid()) {

            $instance = $this->createInstance([RdfLtiProviderRepository::CLASS_URI], $form->getValues());

            $this->setData('message', __('%s created', $instance->getLabel()));
            $this->setData('reload', true);
        }

        $this->setData('formTitle', __('Edit Instance'));
        $this->setData('myForm', $form->render());
        $this->setView('form.tpl', 'tao');

        return $form;
    }

    private function getProviderFormFactory(): LtiProviderFormFactory
    {
        return $this->getServiceLocator()->get(LtiProviderFormFactory::class);
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
