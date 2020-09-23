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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoLti\models\classes\LtiProvider;

use core_kernel_classes_Resource;
use oat\oatbox\service\ConfigurableService;
use tao_helpers_form_Form;

class LtiProviderFormHandler extends ConfigurableService
{
    public function handle(tao_helpers_form_Form $form): core_kernel_classes_Resource
    {
            $values = $this->extractValues($form);
            return  $this->createInstance([$this->getCurrentClass()], $values);
    }

    private function extractValues(tao_helpers_form_Form $form): array
    {
        $values = $form->getValues();
        $values = array_diff_key($values, array_flip(LtiProviderFormFactory::LTI_1P3_ONLY_FIELDS));

        return $values;
    }
}
