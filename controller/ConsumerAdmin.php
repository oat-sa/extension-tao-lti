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

use common_exception_BadRequest as BadRequestExcetpion;
use oat\tao\model\oauth\DataStore;
use oat\taoLti\models\classes\ConsumerService;
use tao_actions_form_CreateInstance as CreateInstanceContainer;
use tao_actions_SaSModule;

/**
 * This controller allows the additon and deletion
 * of LTI Oauth Consumers
 *
 * @package taoLti
 * @license GPLv2 http://www.opensource.org/licenses/gpl-2.0.php
 */
class ConsumerAdmin extends tao_actions_SaSModule
{
    public function addInstanceForm(): void
    {
        if (!$this->isXmlHttpRequest()) {
            throw new BadRequestExcetpion('wrong request mode');
        }

        $class           = $this->getCurrentClass();
        $formContainer   = new CreateInstanceContainer(
            [$class],
            [
                CreateInstanceContainer::CSRF_PROTECTION_OPTION => true,
                'excludedProperties' => [DataStore::PROPERTY_OAUTH_SECRET]
            ]
        );

        $addInstanceForm = $formContainer->getForm();

        if ($addInstanceForm->isSubmited() && $addInstanceForm->isValid()) {
            $properties = $addInstanceForm->getValues();
            $instance   = $this->createInstance([$class], $properties);

            $this->setData('message', __('%s created', $instance->getLabel()));
            $this->setData('reload', true);
        }

        $this->setData('formTitle', __('Create instance of ') . $class->getLabel());
        $this->setData('myForm', $addInstanceForm->render());

        $this->setView('form.tpl', 'tao');
    }

    /**
     * @inheritDoc
     */
    protected function getClassService()
    {
        return $this->getServiceLocator()->get(ConsumerService::class);
    }
}
