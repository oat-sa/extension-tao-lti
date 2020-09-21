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

use oat\oatbox\service\ConfigurableService;
use oat\oatbox\validator\ValidatorInterface;
use tao_helpers_form_FormFactory;
use tao_helpers_Uri;

class ValidatorsFactory extends ConfigurableService
{

    public const SERVICE_ID = 'taoLti/ValidatorsFactory';
    public const OPTION_VALIDATORS = 'validators';

    /**
     * @return ValidatorInterface[][]
     */
    public function createFormValidators(string $schema, string $field = null): array
    {
        $result = [];
        foreach ($this->getValidators($schema, $field) as $name => $validators) {
            foreach ($validators as $validator) {
                $result[tao_helpers_Uri::encode($name)][] = tao_helpers_form_FormFactory::getValidator(...$validator);
            }
        }
        return $result;
    }

    public function getValidators(string $schema, string $field = null): array
    {
        $validators = $this->getOption(self::OPTION_VALIDATORS)[$schema] ?? [];

        return $validators[$field] ? [$validators[$field]] : $validators;
    }
}
