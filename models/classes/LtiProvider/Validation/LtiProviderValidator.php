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

class LtiProviderValidator extends ConfigurableService
{
    /**
     * @var array
     */
    private $errors = [];

    /**
     * @throw InvalidArgumentException
     */
    public function validateArray(string $schema, array $data): void
    {
        $this->errors = [];

        foreach (array_keys($this->getValidationFactory()->getValidators($schema)) as $field) {
            $mappedField = $this->getConfigurationMapper()->map($field);

            if (!$mappedField) {
                continue;
            }

            foreach ($this->getValidationFactory()->createFormValidators($schema, $field) as $validators) {
                $this->validateMappedField($validators, $data, $mappedField);
            }
        }

        if (!empty($this->errors)) {
            $errors = $this->errors;
            $this->errors = [];
            throw new InvalidArgumentException(implode($errors, PHP_EOL));
        }
    }

    private function validateMappedField(array $validators, array $data, string $mappedField): void
    {
        foreach ($validators as $validator) {
            if ($validator->evaluate($data[$mappedField])) {
                continue;
            }
            $this->errors[] = sprintf('"%s": %s', $mappedField, $validator->getMessage());
        }
    }

    private function getConfigurationMapper(): LtiProviderFieldsMapper
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(LtiProviderFieldsMapper::SERVICE_ID);
    }

    private function getValidationFactory(): ValidatorsFactory
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(ValidatorsFactory::SERVICE_ID);
    }
}
