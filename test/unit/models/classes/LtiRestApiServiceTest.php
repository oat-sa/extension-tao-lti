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

use core_kernel_classes_Class;
use core_kernel_persistence_smoothsql_SmoothModel;
use oat\generis\model\data\Ontology;
use oat\generis\test\ServiceManagerMockTrait;
use oat\taoLti\models\classes\ConsumerService;
use oat\taoLti\models\classes\LtiRestApiService;
use oat\taoLti\test\unit\OntologyMockTrait;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionMethod;
use tao_models_classes_CrudService;

class LtiRestApiServiceTest extends TestCase
{
    use ServiceManagerMockTrait;
    use OntologyMockTrait;

    /**
     * @throws ReflectionException
     */
    public function testGetRootClass(): void
    {
        $resourceMock = $this->createMock(core_kernel_classes_Class::class);
        $resourceMock->method('getUri')->willReturn(ConsumerService::CLASS_URI);
        $consumerServiceMock = $this->createMock(ConsumerService::class);
        $consumerServiceMock->method('getRootClass')->willReturn($resourceMock);

        $service = LtiRestApiService::singleton();
        $service->setModel($this->getOntologyMock());
        $service->setServiceLocator($this->getServiceManagerMock([
            ConsumerService::class => $consumerServiceMock,
        ]));

        $reflection = new ReflectionMethod(tao_models_classes_CrudService::class, 'getRootClass');
        $reflection->setAccessible(true);

        $rootClass = $reflection->invoke($service);

        $this->assertInstanceOf(core_kernel_classes_Class::class, $rootClass);
        $this->assertEquals(ConsumerService::CLASS_URI, $rootClass->getUri());
    }
}
