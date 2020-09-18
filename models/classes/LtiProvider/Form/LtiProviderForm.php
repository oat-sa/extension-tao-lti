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
 * Copyright (c) 2019-2020 (original work) Open Assessment Technologies SA
 */

declare(strict_types=1);

namespace oat\taoLti\models\classes\LtiProvider\Form;

use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\controller\SignedFormInstance;
use oat\taoLti\models\classes\LtiProvider\LtiProviderValidationService;
use oat\taoLti\models\classes\LtiProvider\RdfLtiProviderRepository;
use tao_helpers_Uri;
use Zend\ServiceManager\ServiceLocatorInterface;

class LtiProviderForm extends SignedFormInstance
{
    use OntologyAwareTrait;

    protected function initElements()
    {
        parent::initElements();

        $validationService = $this->getValidationService();
        $ltiVersion = $this->getLtiVersion();
        foreach ($this->getForm()->getElements() as $element) {
            $validators = $validationService->getFormValidators(
                $ltiVersion,
                tao_helpers_Uri::decode($element->getName())
            );
            if ($validators) {
                $element->addValidators($validators);
                $this->getForm()->addElement($element);
            }
        }
    }

    private function getValidationService(): LtiProviderValidationService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(LtiProviderValidationService::class);
    }

    private function getServiceLocator(): ServiceLocatorInterface
    {
        return ServiceManager::getServiceManager();
    }

    private function getLtiVersion(): string
    {
        $versionElement = $this->getForm()->getElement(
            tao_helpers_Uri::encode(RdfLtiProviderRepository::LTI_VERSION)
        );

        $versionElement->feed();

        return $this->getResource(
            !empty($versionElement->getEvaluatedValue()) ? $versionElement->getEvaluatedValue(
            ) : RdfLtiProviderRepository::DEFAULT_LTI_VERSION
        )->getLabel();
    }
}
