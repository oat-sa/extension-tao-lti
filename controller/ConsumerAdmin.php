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
use core_kernel_classes_Class as KernelClass;
use oat\tao\model\oauth\DataStore;
use oat\taoLti\models\classes\ConsumerService;
use oat\taoLti\models\classes\Security\Business\Contract\SecretKeyServiceInterface;
use tao_actions_form_CreateInstance as CreateInstanceContainer;
use tao_actions_SaSModule;
use tao_helpers_form_Form as Form;

/**
 * This controller allows the additon and deletion
 * of LTI Oauth Consumers
 *
 * @package taoLti
 * @license GPLv2 http://www.opensource.org/licenses/gpl-2.0.php
 */
class ConsumerAdmin extends tao_actions_SaSModule
{
    /** @var KernelClass */
    private $currentClass;

    public function addInstanceForm(): void
    {
        if (!$this->isXmlHttpRequest()) {
            throw new BadRequestExcetpion('wrong request mode');
        }

        $addInstanceForm = $this->createNewInstanceContainer();

        $this->handleNewInstanceSubmission($addInstanceForm);

        $this->setData('formTitle', __('Create instance of ') . $this->getCurrentClass()->getLabel());
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

    protected function getCurrentClass(): KernelClass
    {
        if (null === $this->currentClass) {
            $this->currentClass = parent::getCurrentClass();
        }

        return $this->currentClass;
    }

    private function createNewInstanceContainer(): Form
    {
        $formContainer = new CreateInstanceContainer(
            [$this->getCurrentClass()],
            [
                CreateInstanceContainer::CSRF_PROTECTION_OPTION => true,
                'excludedProperties'                            => [DataStore::PROPERTY_OAUTH_SECRET],
            ]
        );

        return $formContainer->getForm();
    }

    /**
     * @param Form $addInstanceForm
     */
    private function handleNewInstanceSubmission(Form $addInstanceForm): void
    {
        if ($addInstanceForm->isSubmited() && $addInstanceForm->isValid()) {
            $properties                                   = $addInstanceForm->getValues();
            $properties[DataStore::PROPERTY_OAUTH_SECRET] = $this->getSecretKeyService()->generate();

            $instance = $this->createInstance([$this->getCurrentClass()], $properties);

            $this->setData('message', __('%s created', $instance->getLabel()));
            $this->setData('reload', true);
        }
    }

    private function getSecretKeyService(): SecretKeyServiceInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(SecretKeyServiceInterface::class);
    }
}
