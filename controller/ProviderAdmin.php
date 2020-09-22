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

use oat\tao\model\featureFlag\Lti1p3FeatureFlag;
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
    private const FIELDS_EXCLUDED_FROM_LTI_1P3 = [
        'http://www.tao.lu/Ontologies/TAOLTI.rdf#toolIdentifier',
        'http://www.tao.lu/Ontologies/TAOLTI.rdf#toolPublicKey',
        'http://www.tao.lu/Ontologies/TAOLTI.rdf#ToolJwksUrl',
        'http://www.tao.lu/Ontologies/TAOLTI.rdf#toolLaunchUrl',
        'http://www.tao.lu/Ontologies/TAOLTI.rdf#toolOidcLoginInitiationUrl',
        'http://www.tao.lu/Ontologies/TAOLTI.rdf#toolDeploymentIds',
        'http://www.tao.lu/Ontologies/TAOLTI.rdf#toolAudience',
        'http://www.tao.lu/Ontologies/TAOLTI.rdf#toolClientId',
        'http://www.tao.lu/Ontologies/TAOLTI.rdf#toolName',
        'http://www.tao.lu/Ontologies/TAOLTI.rdf#toolIdentifier',
        'http://www.tao.lu/Ontologies/TAOLTI.rdf#ltiVersion',
    ];

    public function addInstanceForm()
    {
        if ($this->getFeatureFlag()->isLti1p3Enabled()) {
            $form = $this->createLti1p1ProviderForm();

            if ($form->isSubmited() && $form->isValid()) {
                $values = $this->extractValues($form);

                $instance = $this->createInstance([$this->getCurrentClass()], $values);

                $this->setData('message', __('%s created', $instance->getLabel()));
                $this->setData('reload', true);

            }
            $this->setData('formTitle', __('Edit Instance'));
            $this->setData('myForm', $form->render());
            $this->setView('form.tpl', 'tao');

            return;
        }

        parent::addInstanceForm();
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

    private function extractValues(tao_helpers_form_Form $form): array
    {
        $values = $form->getValues();
        $values = array_diff_key($values, array_flip(self::FIELDS_EXCLUDED_FROM_LTI_1P3));

        return $values;
    }

    private function createLti1p1ProviderForm(): tao_helpers_form_Form
    {
        $formContainer = new tao_actions_form_CreateInstance(
            [$this->getCurrentClass()],
            [
                tao_actions_form_CreateInstance::CSRF_PROTECTION_OPTION => true,
                'excludedProperties' => self::FIELDS_EXCLUDED_FROM_LTI_1P3,
            ]
        );

        return $formContainer->getForm();
    }

    private function getFeatureFlag(): Lti1p3FeatureFlag
    {
        return $this->getServiceLocator()->get(Lti1p3FeatureFlag::SERVICE_ID);
    }
}
