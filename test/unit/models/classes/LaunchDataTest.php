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

use oat\generis\test\TestCase;
use Psr\Log\LoggerInterface;
use Prophecy\Argument;

class LaunchDataTest extends TestCase
{
    public function testInvalidReturnUrl()
    {
        $logger = $this->prophesize(LoggerInterface::class);
        $emptyLaunch = new LtiLaunchData([LtiLaunchData::LAUNCH_PRESENTATION_RETURN_URL => 'notAurl'], []);
        $emptyLaunch->setLogger($logger->reveal());
        $this->assertFalse($emptyLaunch->hasReturnUrl());
        $logger->warning("Invalid LTI Return URL 'notAurl'.", Argument::any())->shouldHaveBeenCalled();
    }

    public function testNoReturnUrl()
    {
        $logger = $this->prophesize(LoggerInterface::class);
        $emptyLaunch = new LtiLaunchData([], []);
        $emptyLaunch->setLogger($logger->reveal());
        $this->assertFalse($emptyLaunch->hasReturnUrl());
        $logger->warning(Argument::any(), Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testGoodReturnUrl()
    {
        $logger = $this->prophesize(LoggerInterface::class);
        $emptyLaunch = new LtiLaunchData([LtiLaunchData::LAUNCH_PRESENTATION_RETURN_URL => 'http://valid.url.com'], []);
        $emptyLaunch->setLogger($logger->reveal());
        $this->assertTrue($emptyLaunch->hasReturnUrl());
        $logger->warning(Argument::any(), Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testJsonEncode()
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
    public function testGetBooleanVariableInvalidValueThrowsException(array $ltiVariables)
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
    public function testGetBooleanVariable(array $ltiVariables, $expectedResult)
    {
        $customParameters = [];
        $boolVariableKey = 'boolVariableKey';

        $launchData = new LtiLaunchData($ltiVariables, $customParameters);
        $result = $launchData->getBooleanVariable($boolVariableKey);

        $this->assertEquals($expectedResult, $result, "Method must return correct boolean value");
    }

    public function dataProviderTestGetBooleanVariableInvalidValueThrowsException()
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

    public function dataProviderTestGetBooleanVariable()
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
