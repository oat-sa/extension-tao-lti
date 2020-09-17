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

use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use oat\taoLti\models\classes\LtiProvider\Form\LtiProviderForm;
use oat\taoLti\models\classes\LtiProvider\RdfLtiProviderRepository;
use tao_actions_form_CreateInstance;
use tao_actions_form_Instance;
use tao_actions_SaSModule;
use tao_models_classes_dataBinding_GenerisFormDataBinder;

/**
 * This controller allows the adding and deletion of LTI Oauth Providers
 *
 * @package taoLti
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

    public function editInstance()
    {
        $class = $this->getCurrentClass();
        $instance = $this->getCurrentInstance();
        $myFormContainer = $this->createForm($class, $instance);

        $myForm = $myFormContainer->getForm();

        if ($myForm->isSubmited() && $myForm->isValid()) {
            $values = $myForm->getValues();
            $binder = new tao_models_classes_dataBinding_GenerisFormDataBinder($instance);
            $binder->bind($values);
            $message = __('Instance saved');

            $this->setData('message', $message);
            $this->setData('reload', true);
        }

        $this->setData('formTitle', __('Edit Instance'));
        $this->setData('myForm', $myForm->render());
        $this->setView('form.tpl', 'tao');
    }

    private function createForm(
        core_kernel_classes_Class $class,
        core_kernel_classes_Resource $instance
    ): tao_actions_form_Instance {
        return new LtiProviderForm(
            $class,
            $instance,
            [tao_actions_form_CreateInstance::CSRF_PROTECTION_OPTION => true]
        );
    }
}
