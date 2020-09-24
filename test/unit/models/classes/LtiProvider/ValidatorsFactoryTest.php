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
 * Copyright (c) 2016-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

declare(strict_types=1);

namespace oat\taoLti\test\unit\models\classes\LtiProvider;

use oat\generis\test\TestCase;
use oat\tao\model\oauth\DataStore;
use oat\taoLti\models\classes\LtiProvider\Validation\ValidatorsFactory;
use tao_helpers_form_validators_NotEmpty;

class ValidatorsFactoryTest extends TestCase
{
    /**
     * @var ValidatorsFactory
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new ValidatorsFactory();
    }

    public function testCreateFormValidators(): void
    {
        $factorizedValue = $this->subject->createFormValidators('1.1', DataStore::PROPERTY_OAUTH_KEY);
        $this->assertIsArray($factorizedValue);
        $this->assertInstanceOf(tao_helpers_form_validators_NotEmpty::class, $factorizedValue[0][0]);

        $this->assertEmpty($this->subject->createFormValidators('1.4', DataStore::PROPERTY_OAUTH_KEY));
        $this->assertEmpty($this->subject->createFormValidators('1.1', 'oooo')[0]);
    }
}
