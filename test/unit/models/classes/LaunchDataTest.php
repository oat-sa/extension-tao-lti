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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoLti\models\classes;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class LaunchDataTest extends TestCase
{
    /**
     * @throws LtiException
     */
    public function testInvalidReturnUrl(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning')->with("Invalid LTI Return URL 'notAurl'.");
        $emptyLaunch = new LtiLaunchData([LtiLaunchData::LAUNCH_PRESENTATION_RETURN_URL => 'notAurl'], []);
        $emptyLaunch->setLogger($logger);
        $this->assertFalse($emptyLaunch->hasReturnUrl());
    }

    /**
     * @throws LtiException
     */
    public function testNoReturnUrl(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('warning');
        $emptyLaunch = new LtiLaunchData([], []);
        $emptyLaunch->setLogger($logger);
        $this->assertFalse($emptyLaunch->hasReturnUrl());
    }

    /**
     * @throws LtiException
     */
    public function testGoodReturnUrl(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('warning');
        $emptyLaunch = new LtiLaunchData([LtiLaunchData::LAUNCH_PRESENTATION_RETURN_URL => 'http://valid.url.com'], []);
        $emptyLaunch->setLogger($logger);
        $this->assertTrue($emptyLaunch->hasReturnUrl());
    }

    public function testJsonEncode(): void
    {
        $sampleLaunch = new LtiLaunchData(['a$%' => '!@#$%^&*()_', 'b' => 'c'], ['\\\'s' => '+++']);
        $unserialized = LtiLaunchData::fromJsonArray(json_decode(json_encode($sampleLaunch), true));
        $this->assertEquals($sampleLaunch, $unserialized);

        $emptyLaunch = new LtiLaunchData([], []);
        $unserialized = LtiLaunchData::fromJsonArray(json_decode(json_encode($emptyLaunch), true));
        $this->assertEquals($emptyLaunch, $unserialized);
    }

    /**
     * @param array $ltiVariables
     *
     * @dataProvider dataProviderTestGetBooleanVariableInvalidValueThrowsException
     *
     * @throws LtiException
     * @throws LtiVariableMissingException
     */
    public function testGetBooleanVariableInvalidValueThrowsException(array $ltiVariables): void
    {
        $customParameters = [];
        $boolVariableKey = 'boolVariableKey';

        $this->expectException(LtiInvalidVariableException::class);

        $launchData = new LtiLaunchData($ltiVariables, $customParameters);
        $launchData->getBooleanVariable($boolVariableKey);
    }

    /**
     * @param array $ltiVariables
     * @param boolean $expectedResult
     *
     * @dataProvider dataProviderTestGetBooleanVariable
     *
     * @throws LtiException
     * @throws LtiVariableMissingException
     */
    public function testGetBooleanVariable(array $ltiVariables, bool $expectedResult): void
    {
        $customParameters = [];
        $boolVariableKey = 'boolVariableKey';

        $launchData = new LtiLaunchData($ltiVariables, $customParameters);
        $result = $launchData->getBooleanVariable($boolVariableKey);

        $this->assertEquals($expectedResult, $result, "Method must return correct boolean value");
    }

    public function dataProviderTestGetBooleanVariableInvalidValueThrowsException(): array
    {
        return [
            'Value integer 1' => [
                'ltiVariables' => [
                    'boolVariableKey' => 1,
                ]
            ],
            'Value integer 0' => [
                'ltiVariables' => [
                    'boolVariableKey' => 0,
                ]
            ],
            'Value string 1' => [
                'ltiVariables' => [
                    'boolVariableKey' => '1',
                ]
            ],
            'Value string 0' => [
                'ltiVariables' => [
                    'boolVariableKey' => '0',
                ]
            ],
            'Value random string' => [
                'ltiVariables' => [
                    'boolVariableKey' => 'DUMMY_VALUE',
                ]
            ]
        ];
    }

    public function dataProviderTestGetBooleanVariable(): array
    {
        return [
            'Value capital "TRUE"' => [
                'ltiVariables' => [
                    'boolVariableKey' => 'TRUE'
                ],
                'expectedResult' => true,
            ],
            'Value capital "true"' => [
                'ltiVariables' => [
                    'boolVariableKey' => 'true'
                ],
                'expectedResult' => true,
            ],
            'Value capital "FALSE"' => [
                'ltiVariables' => [
                    'boolVariableKey' => 'FALSE'
                ],
                'expectedResult' => false,
            ],
            'Value lover case "false"' => [
                'ltiVariables' => [
                    'boolVariableKey' => 'false'
                ],
                'expectedResult' => false,
            ]
        ];
    }
}
