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

use InvalidArgumentException;
use oat\generis\test\TestCase;
use oat\taoLti\models\classes\LtiProvider\LtiProviderFieldsMapper;
use oat\taoLti\models\classes\LtiProvider\Validation\LtiProviderValidator;
use oat\taoLti\models\classes\LtiProvider\Validation\ValidatorsFactory;

class LtiProviderValidatorTest extends TestCase
{
    /**
     * @var LtiProviderValidator
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new LtiProviderValidator();
        $this->subject->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    ValidatorsFactory::class => new ValidatorsFactory(),
                    LtiProviderFieldsMapper::class => new LtiProviderFieldsMapper(),
                ]
            )
        );
    }

    public function testValidateBrokenArray(): void
    {
        $this->expectExceptionMessage('"key": This field is required');
        $this->expectException(InvalidArgumentException::class);
        $this->assertNull($this->subject->validateArray('1.1', []));
    }

    public function testValidateProperArray(): void
    {
        $this->assertNull(
            $this->subject->validateArray(
                '1.1',
                [
                    'key' => '1',
                    'secret' => '1',
                    'ltiVersion' => '1',
                ]
            )
        );
    }


}