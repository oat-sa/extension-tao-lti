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

use common_exception_BadRequest as BadRequestException;
use core_kernel_classes_Class as KernelClass;
use core_kernel_classes_Resource as KernelResource;
use oat\tao\model\controller\SignedFormInstance;
use oat\tao\model\oauth\DataStore;
use oat\taoLti\models\classes\ConsumerService;
use oat\taoLti\models\classes\Security\Business\Contract\SecretKeyServiceInterface;
use tao_actions_form_CreateInstance as CreateInstanceContainer;
use tao_actions_SaSModule;
use tao_helpers_form_Form as Form;
use tao_models_classes_dataBinding_GenerisFormDataBinder;
use tao_models_classes_dataBinding_GenerisFormDataBindingException as FormDataBindingException;

/**
 * This controller allows the additon and deletion
 * of LTI Oauth Consumers
 *
 * @package taoLti
 * @license GPLv2 http://www.opensource.org/licenses/gpl-2.0.php
 */
class ConsumerAdmin extends tao_actions_SaSModule
{
    private const EXCLUDED_FIELDS = [
        DataStore::PROPERTY_OAUTH_SECRET,
    ];

    /** @var KernelClass */
    private $currentClass;

    private $currentInstance;

    /**
     * @inheritDoc
     */
    public function addInstanceForm(): void
    {
        if (!$this->isXmlHttpRequest()) {
            throw new BadRequestException('wrong request mode');
        }

        $form = $this->createNewInstanceForm();

        $this->handleNewInstanceSubmission($form);

        $this->setData('formTitle', __('Create instance of ') . $this->getCurrentClass()->getLabel());
        $this->setData('myForm', $form->render());

        $this->setView('form.tpl', 'tao');
    }

    /**
     * @inheritDoc
     *
     * @throws FormDataBindingException
     */
    public function editInstance(): void
    {
        $form = $this->createExistingInstanceForm();

        $this->handleExistingInstanceSubmission($form);

        $this->setData('formTitle', __('Edit Instance'));
        $this->setData('myForm', $form->render());
        $this->setView('form.tpl', 'tao');
    }

    /**
     * @inheritDoc
     */
    protected function getClassService()
    {
        return $this->getServiceLocator()->get(ConsumerService::class);
    }

    /**
     * @inheritDoc
     */
    protected function getCurrentClass(): KernelClass
    {
        if (null === $this->currentClass) {
            $this->currentClass = parent::getCurrentClass();
        }

        return $this->currentClass;
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     *
     * @param string $parameterName
     *
     * @return KernelResource
     */
    protected function getCurrentInstance($parameterName = 'uri'): KernelResource
    {
        if (null === $this->currentInstance) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->currentInstance = parent::getCurrentInstance($parameterName);
        }

        return $this->currentInstance;
    }

    private function createNewInstanceForm(): Form
    {
        $formContainer = new CreateInstanceContainer(
            [$this->getCurrentClass()],
            [
                CreateInstanceContainer::CSRF_PROTECTION_OPTION => true,
                'excludedProperties'                            => self::EXCLUDED_FIELDS,
            ]
        );

        return $formContainer->getForm();
    }

    private function createExistingInstanceForm(): Form
    {
        $myFormContainer = new SignedFormInstance(
            $this->getCurrentClass(),
            $this->getCurrentInstance(),
            [SignedFormInstance::CSRF_PROTECTION_OPTION => true]
        );

        $form = $myFormContainer->getForm();
        $form->removeElement('id');

        return $form;
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

    /**
     * @param Form $form
     *
     * @throws FormDataBindingException
     */
    private function handleExistingInstanceSubmission(Form $form): void
    {
        if ($form->isSubmited() && $form->isValid()) {
            $values = $form->getValues();

            $values = array_diff_key($values, array_flip(self::EXCLUDED_FIELDS));

            $binder = new tao_models_classes_dataBinding_GenerisFormDataBinder($this->getCurrentInstance());
            $binder->bind($values);

            $this->setData('message', __('Instance saved'));
            $this->setData('reload', true);
        }
    }

    private function getSecretKeyService(): SecretKeyServiceInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(SecretKeyServiceInterface::class);
    }
}
