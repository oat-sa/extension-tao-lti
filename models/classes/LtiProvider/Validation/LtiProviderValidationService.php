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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA
 */

declare(strict_types=1);

namespace oat\taoLti\models\classes\LtiProvider\Validation;

use InvalidArgumentException;
use oat\oatbox\service\ConfigurableService;
use oat\taoLti\models\classes\LtiProvider\LtiProviderFieldsMapper;

class LtiProviderValidationService extends ConfigurableService
{
    /**
     * @throw InvalidArgumentException
     */
    public function validateArray(string $schema, array $data): void
    {
        $errors = [];
        foreach (array_keys($this->getValidationFactory()->getValidatorsDefinitions($schema)) as $field) {
            $mappedField = $this->getConfigurationMapper()->map($field);
            if ($mappedField) {
                foreach ($this->getValidationFactory()->createFormValidators($schema, $field) as $validators) {
                    foreach ($validators as $validator) {
                        if (!$validator->evaluate($data[$mappedField])) {
                            $errors[] = sprintf('"%s": %s', $mappedField, $validator->getMessage());
                        }
                    }
                }
            }
        }

        if (!empty($errors)) {
            throw new InvalidArgumentException(implode($errors, PHP_EOL));
        }
    }

    private function getConfigurationMapper(): LtiProviderFieldsMapper
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(LtiProviderFieldsMapper::SERVICE_ID);
    }

    private function getValidationFactory(): ValidationFactory
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(ValidationFactory::SERVICE_ID);
    }
}
