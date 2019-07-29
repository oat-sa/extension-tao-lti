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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA
 */

namespace oat\taoLti\models\classes\LtiProvider;

use common_exception_Error as ErrorException;
use common_exception_InvalidArgumentType as InvalidArgumentTypeException;
use core_kernel_classes_Class as RdfClass;
use core_kernel_classes_Resource as RdfResource;
use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use oat\generis\test\unit\OntologyMockTest;
use oat\oatbox\service\ServiceManager;
use oat\search\base\QueryBuilderInterface;
use oat\search\base\QueryInterface;
use oat\search\base\SearchGateWayInterface;
use oat\tao\model\oauth\DataStore;
use Psr\Log\LoggerInterface;

class RdfLtiProviderFinderTest extends OntologyMockTest
{
    /** @var RdfLtiProviderFinder */
    private $subject;

    /** @var ComplexSearchService|\PHPUnit_Framework_MockObject_MockObject $finderService */
    private $searchService;

    /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject $logger */
    private $logger;

    /** @var QueryBuilderInterface|\PHPUnit_Framework_MockObject_MockObject $queryBuilder */
    private $queryBuilder;

    /** @var QueryInterface|\PHPUnit_Framework_MockObject_MockObject $query */
    private $query;

    /** @var SearchGateWayInterface|\PHPUnit_Framework_MockObject_MockObject $query */
    private $gateWay;

    public function testGetRootClass()
    {
        $subject = new RdfLtiProviderFinder();
        $subject->setModel($this->getOntologyMock());

        $rootClass = $subject->getRootClass();

        $this->assertInstanceOf(RdfClass::class, $rootClass);
        $this->assertEquals(RdfLtiProviderFinder::CLASS_URI, $rootClass->getUri());
    }

    public function setUp()
    {
        $this->query = $this->getMockBuilder(QueryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['add', 'contains'])
            ->getMockForAbstractClass();

        $this->queryBuilder = $this->getMockBuilder(QueryBuilderInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setCriteria'])
            ->getMockForAbstractClass();

        $this->gateWay = $this->getMockBuilder(SearchGateWayInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['count', 'search'])
            ->getMockForAbstractClass();

        $this->searchService = $this->getMockBuilder(ComplexSearchService::class)
            ->disableOriginalConstructor()
            ->setMethods(['query', 'searchType', 'getGateway'])
            ->getMock();
        $this->searchService->method('query')->willReturn($this->queryBuilder);
        $this->searchService->method('getGateway')->willReturn($this->gateWay);

        /** @var ServiceManager|\PHPUnit_Framework_MockObject_MockObject $serviceLocator */
        $serviceLocator = $this->getMockBuilder(ServiceManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        $serviceLocator->method('get')->with(ComplexSearchService::SERVICE_ID)->willReturn($this->searchService);

        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['error'])
            ->getMockForAbstractClass();

        $this->subject = new RdfLtiProviderFinder();
        $this->subject->setServiceLocator($serviceLocator);
        $this->subject->setLogger($this->logger);
    }

    public function testGetResourcesCount()
    {
        $count = 12;

        $this->searchService->method('searchType')
            ->with($this->queryBuilder, RdfLtiProviderFinder::CLASS_URI, true)
            ->willReturn($this->query);
        $this->queryBuilder->expects($this->once())->method('setCriteria')->with($this->query);
        $this->gateWay->method('count')->with($this->queryBuilder)->willReturn($count);

        $this->assertEquals($count, $this->subject->getResourcesCount());
    }

    public function testCountWithExceptionReturn0AndLogsException()
    {
        $message = 'the exception message';
        $this->searchService->method('searchType')
            ->with($this->queryBuilder, RdfLtiProviderFinder::CLASS_URI, true)
            ->willThrowException(new ErrorException($message));
        $this->logger->expects($this->once())->method('error')->with('Unable to retrieve providers: ' . $message, []);

        $this->assertEquals(0, $this->subject->getResourcesCount());
    }

    public function testFind()
    {
        $uri1 = 'uri1';
        $label1 = 'label1';
        $key1 = 'key1';
        $secret1 = 'secret1';
        $callbackUrl1 = 'callbackUrl1';
        $uri2 = 'uri2';
        $label2 = 'label2';
        $key2 = 'key2';
        $secret2 = 'secret2';
        $callbackUrl2 = 'callbackUrl2';

        $key = 'key';
        $value = 'value';
        $criteria = [$key => $value];

        $resource1 = $this->getRdfResourceMock($uri1, $label1, $key1, $secret1, $callbackUrl1);
        $resource2 = $this->getRdfResourceMock($uri2, $label2, $key2, $secret2, $callbackUrl2);
        $resources = [$resource1, $resource2];

        $ltiProviderResource1 = new LtiProviderResource($uri1);
        $ltiProviderResource1->setLabel($label1);
        $ltiProviderResource1->setKey($key1);
        $ltiProviderResource1->setSecret($secret1);
        $ltiProviderResource1->setCallbackUrl($callbackUrl1);
        $ltiProviderResource2 = new LtiProviderResource($uri2);
        $ltiProviderResource2->setLabel($label2);
        $ltiProviderResource2->setKey($key2);
        $ltiProviderResource2->setSecret($secret2);
        $ltiProviderResource2->setCallbackUrl($callbackUrl2);
        $expected = [$ltiProviderResource1, $ltiProviderResource2];

        $this->searchService->method('searchType')
            ->with($this->queryBuilder, RdfLtiProviderFinder::CLASS_URI, true)
            ->willReturn($this->query);

        $this->queryBuilder->expects($this->once())->method('setCriteria')->with($this->query);
        $this->gateWay->method('search')->with($this->queryBuilder)->willReturn($resources);
        $this->query->expects($this->once())->method('add')->with($key)->willReturn($this->query);
        $this->query->expects($this->once())->method('contains')->with($value)->willReturn($this->query);

        $this->assertEquals($expected, $this->subject->getResources($criteria));
    }

    public function testFindWithFaultyPropertyValuesReturnsEmptyArray()
    {
        $class = 'class';
        $function = 'function';
        $position = 0;
        $type = 'type';
        $object = 'object';

        $resource = $this->getMockBuilder(RdfResource::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPropertiesValues'])
            ->getMock();
        $resource->method('getPropertiesValues')->willThrowException(new InvalidArgumentTypeException($class, $function, $position, $type, $object));
        $resources = [$resource];

        $this->searchService->method('searchType')
            ->with($this->queryBuilder, RdfLtiProviderFinder::CLASS_URI, true)
            ->willReturn($this->query);

        $this->queryBuilder->expects($this->once())->method('setCriteria')->with($this->query);
        $this->gateWay->method('search')->with($this->queryBuilder)->willReturn($resources);

        $this->logger->expects($this->once())->method('error')
            ->with('Unable to retrieve provider properties: Argument ' . $position . ' passed to ' . $class . '::' . $function . '() must be an ' . $type . ', string given', []);
        $this->assertEquals([], $this->subject->getResources([]));
    }

    public function testFindWithWrongResourceTypeReturnsEmptyArray()
    {
        $class = 'class';
        $function = 'function';
        $position = 0;
        $type = 'type';
        $object = 'object';

        $resource = $this->getMockBuilder(RdfResource::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPropertiesValues','getUri'])
            ->getMock();
        $resource->method('getPropertiesValues')->willReturn([]);
        $resource->method('getUri')->willReturn('');
        $resources = [$resource];

        $this->searchService->method('searchType')
            ->with($this->queryBuilder, RdfLtiProviderFinder::CLASS_URI, true)
            ->willReturn($this->query);

        $this->queryBuilder->expects($this->once())->method('setCriteria')->with($this->query);
        $this->gateWay->method('search')->with($this->queryBuilder)->willReturn($resources);

        $this->logger->expects($this->once())->method('error')
            ->with('Unable to retrieve provider properties: cannot construct the resource because the uri cannot be empty, debug: ', []);
        $this->assertEquals([], $this->subject->getResources([]));
    }

    /**
     * @param string $uri
     * @param string $label
     * @param string $key
     * @param string $secret
     * @param string $callbackUrl
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getRdfResourceMock($uri, $label, $key, $secret, $callbackUrl)
    {
        $resource = $this->getMockBuilder(RdfResource::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUri', 'getLabel', 'getPropertiesValues'])
            ->getMock();
        $resource->method('getUri')->willReturn($uri);
        $resource->method('getLabel')->willReturn($label);
        $resource->method('getPropertiesValues')
            ->with([
                DataStore::PROPERTY_OAUTH_KEY,
                DataStore::PROPERTY_OAUTH_SECRET,
                DataStore::PROPERTY_OAUTH_CALLBACK,
            ])
            ->willReturn([
                DataStore::PROPERTY_OAUTH_KEY => [$key],
                DataStore::PROPERTY_OAUTH_SECRET => [$secret],
                DataStore::PROPERTY_OAUTH_CALLBACK => [$callbackUrl],
            ]);
        return $resource;
    }
}
