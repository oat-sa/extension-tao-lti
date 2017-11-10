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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 */

namespace oat\taoLti\test\models\classes\user;

use oat\taoLti\models\classes\user\LtiUser;
use taoLti_models_classes_LtiLaunchData;

class LtiUserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @throws \Exception
     */
    public function testCreateLtiUserFromArrayWithLtiContext()
    {
        $ltiContext = $this->getMockBuilder(taoLti_models_classes_LtiLaunchData::class)->disableOriginalConstructor()->getMock();

        $ltiUser = LtiUser::createFromArrayWithLtiContext(
            [
                'userUri' => 'uri',
                'roles' => ['role1', 'role2'],
                'language' => 'en',
            ],
            $ltiContext
        );

        $this->assertInstanceOf(LtiUser::class, $ltiUser);
        $this->assertSame($ltiUser->getIdentifier(), 'uri');
        $this->assertSame($ltiUser->getLaunchData(), $ltiContext);
    }


    /**
     * @throws \Exception
     */
    public function testCreateLtiUserWithAllParams()
    {
        $ltiContext = $this->getMockBuilder(taoLti_models_classes_LtiLaunchData::class)->disableOriginalConstructor()->getMock();

        $ltiUser = LtiUser::createFromArrayWithLtiContext(
            [
                'userUri' => 'uri',
                'roles' => ['role1', 'role2'],
                'language' => 'en',
                'firstname' => 'Dummy firstname',
                'lastname' => 'Dummy lastname',
                'email' => 'Dummy email',
                'label' => 'Dummy label',
            ],
            $ltiContext
        );

        $this->assertInstanceOf(LtiUser::class, $ltiUser);
        $this->assertSame($ltiUser->getIdentifier(), 'uri');
        $this->assertSame($ltiUser->getLaunchData(), $ltiContext);
        $this->assertSame($ltiUser->getPropertyValues('http://www.tao.lu/Ontologies/generis.rdf#userUILg'), ['en']);
        $this->assertSame($ltiUser->getPropertyValues('http://www.tao.lu/Ontologies/generis.rdf#userFirstName'), ['Dummy firstname']);
        $this->assertSame($ltiUser->getPropertyValues('http://www.tao.lu/Ontologies/generis.rdf#userLastName'), ['Dummy lastname']);
        $this->assertSame($ltiUser->getPropertyValues('http://www.tao.lu/Ontologies/generis.rdf#userRoles'), ['role1', 'role2']);
    }

    /**
     * @expectedException \Exception
     * @throws \Exception
     */
    public function testCreateLtiUserInsufficientParams()
    {
        $ltiContext = $this->getMockBuilder(taoLti_models_classes_LtiLaunchData::class)->disableOriginalConstructor()->getMock();

        LtiUser::createFromArrayWithLtiContext(
            ['userUri' => 'uri'], $ltiContext
        );
    }

}
