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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA ;
 */

namespace oat\taoLti\models\classes\user;

use oat\taoLti\models\classes\user\LtiUserHelper;
use oat\generis\test\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Class LtiUserHelperTest
 * @package oat\taoLti\models\classes\user
 */
class LtiUserHelperTest extends TestCase
{
    /**
     * @var LtiUserHelper
     */
    private $object;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();


        $this->object = new LtiUserHelper([
            LtiUserHelper::OPTION_LTI_USER_SERVICE => LtiUserService::SERVICE_ID
        ]);
    }

    /**
     * Test __constructor method withour required ltiUserService option
     */
    public function testConstructWithoutRequiredOption()
    {
        $this->expectException(\common_exception_Error::class);

        $ltiUserHelper = new LtiUserHelper([]);
    }

    /**
     * Test getLtiUserData method with wrong ltiUserService Interface
     */
    public function testGetLtiUserDataInvalidServiceInterface()
    {
        $loggerMock = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $loggerMock->expects($this->once())
            ->method('error')
            ->with('Invalid service provided.');

        $serviceLocator = $this->getServiceLocatorMock([
            LtiUserService::SERVICE_ID => new \stdClass(),
        ]);

        $this->object->setServiceLocator($serviceLocator);
        $this->object->setLogger($loggerMock);

        $expected = [];
        $result = $this->object->getLtiUserData('USER_ID');

        $this->assertEquals($expected, $result, 'User data must be as expected for wrong ltiUserService Interface.');
    }

    /**
     * Test getLtiUserData method
     */
    public function testGetLtiUserData()
    {
        $userId = 'DUMMY_USER_ID';
        $expectedUserData = ['DUMMY_USER_DATA'];

        $ltiUserServiceMock = $this->getMockForAbstractClass(LtiUserService::class);
        $ltiUserServiceMock->expects($this->once())
            ->method('getUserDataFromId')
            ->with($userId)
            ->willReturn($expectedUserData);

        $serviceLocatorMock = $this->getServiceLocatorMock([
            LtiUserService::SERVICE_ID => $ltiUserServiceMock
        ]);
        $this->object->setServiceLocator($serviceLocatorMock);

        $result = $this->object->getLtiUserData($userId);

        $this->assertEquals($expectedUserData, $result, 'User data must be as expected.');
    }

    /**
     * Test getLastName method
     *
     * @param $userData
     * @param $expected
     *
     * @dataProvider providerTestGetLastName
     */
    public function testGetLastName($userData, $expected)
    {
        $result = $this->object->getLastName($userData);

        $this->assertEquals($expected, $result, 'Last name must be as expected.');
    }

    /**
     * Test getFirstName method
     *
     * @param array $userData
     * @param $expected
     *
     * @dataProvider providerTestGetFirstName
     */
    public function testGetFirstName(array $userData, $expected)
    {
        $result = $this->object->getFirstName($userData);

        $this->assertEquals($expected, $result, 'First name must be as expected.');
    }

    /**
     * Test getUserName method
     *
     * @param array $userData
     * @param $expected
     *
     * @dataProvider providerTestGetUserName
     */
    public function testGetUserName(array $userData, $expected)
    {
        $result = $this->object->getUserName($userData);

        $this->assertEquals($expected, $result, 'Full username must be as expected.');
    }

    /**
     * @return array
     */
    public function providerTestGetLastName()
    {
        return [
            'Empty last name' => [
                'data' => [
                    "http://www.tao.lu/Ontologies/generis.rdf#userFirstName" => "John",
                    "http://www.tao.lu/Ontologies/generis.rdf#userLastName" => "",
                ],
                'expected' => ''
            ],
            'Last name does not exist' => [
                'data' => [
                    "http://www.tao.lu/Ontologies/generis.rdf#userFirstName" => "John",
                ],
                'expected' => ''
            ],
            'Correct last name' => [
                'data' => [
                    "http://www.tao.lu/Ontologies/generis.rdf#userFirstName" => "John",
                    "http://www.tao.lu/Ontologies/generis.rdf#userLastName" => "Doe",
                ],
                'expected' => 'Doe'
            ],
        ];
    }

    /**
     * @return array
     */
    public function providerTestGetFirstName()
    {
        return [
            'Empty first name' => [
                'data' => [
                    "http://www.tao.lu/Ontologies/generis.rdf#userFirstName" => "",
                    "http://www.tao.lu/Ontologies/generis.rdf#userLastName" => "Doe",
                ],
                'expected' => ''
            ],
            'First name does not exist' => [
                'data' => [
                    "http://www.tao.lu/Ontologies/generis.rdf#userLastName" => "Doe",
                ],
                'expected' => ''
            ],
            'Correct first name' => [
                'data' => [
                    "http://www.tao.lu/Ontologies/generis.rdf#userFirstName" => "John",
                    "http://www.tao.lu/Ontologies/generis.rdf#userLastName" => "Doe",
                ],
                'expected' => 'John'
            ],
        ];
    }

    /**
     * @return array
     */
    public function providerTestGetUserName()
    {
        return [
            'Empty first name' => [
                'data' => [
                    "http://www.tao.lu/Ontologies/generis.rdf#userFirstName" => "",
                    "http://www.tao.lu/Ontologies/generis.rdf#userLastName" => "Doe",
                ],
                'expected' => 'Doe'
            ],
            'Empty last name' => [
                'data' => [
                    "http://www.tao.lu/Ontologies/generis.rdf#userFirstName" => "John",
                    "http://www.tao.lu/Ontologies/generis.rdf#userLastName" => "",
                ],
                'expected' => 'John'
            ],
            'Empty first and last name' => [
                'data' => [
                    "http://www.tao.lu/Ontologies/generis.rdf#userFirstName" => "",
                    "http://www.tao.lu/Ontologies/generis.rdf#userLastName" => "",
                ],
                'expected' => ''
            ],
            'Correct first and last name' => [
                'data' => [
                    "http://www.tao.lu/Ontologies/generis.rdf#userFirstName" => "John",
                    "http://www.tao.lu/Ontologies/generis.rdf#userLastName" => "Doe",
                ],
                'expected' => 'John Doe'
            ],
        ];
    }
}

