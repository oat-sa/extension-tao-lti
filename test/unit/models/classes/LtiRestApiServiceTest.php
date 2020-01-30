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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoLti\test\unit\models\classes;

use oat\generis\test\unit\OntologyMockTest;
use oat\taoLti\models\classes\LtiRestApiService;
use oat\taoLti\models\classes\ConsumerService;

class LtiRestApiServiceTest extends OntologyMockTest
{
    public function testGetRootClass()
    {
        $resourceProphet = $this->prophesize(\core_kernel_classes_Class::class);
        $resourceProphet->getUri()->willReturn(ConsumerService::CLASS_URI);
        $consumerServiceProphet = $this->prophesize(ConsumerService::class);
        $consumerServiceProphet->getRootClass()->willReturn($resourceProphet->reveal());
        
        $service = LtiRestApiService::singleton();
        $service->setModel($this->getOntologyMock());
        $service->setServiceLocator($this->getServiceLocatorMock([
            ConsumerService::class => $consumerServiceProphet->reveal()
        ]));
        $this->assertEquals(false, $service->isInScope(ConsumerService::CLASS_URI));
    }
}
