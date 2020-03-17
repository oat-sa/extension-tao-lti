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
use oat\generis\model\OntologyRdfs;
use oat\generis\test\unit\OntologyMockTest;
use oat\oatbox\service\ServiceManager;
use oat\search\base\QueryBuilderInterface;
use oat\search\base\QueryInterface;
use oat\search\base\SearchGateWayInterface;
use oat\tao\model\oauth\DataStore;
use Psr\Log\LoggerInterface;
use oat\generis\test\MockObject;

/**
 * Service methods to manage the LTI provider business objects.
 */
class RdfLtiProviderRepositoryTest extends OntologyMockTest
{
    /** @var RdfLtiProviderRepository */
    private $subject;

    /** @var ComplexSearchService|MockObject $finderService */
    private $searchService;

    /** @var LoggerInterface|MockObject $logger */
    private $logger;

    /** @var QueryBuilderInterface|MockObject $queryBuilder */
    private $queryBuilder;

    /** @var QueryInterface|MockObject $query */
    private $query;

    /** @var SearchGateWayInterface|MockObject $query */
    private $gateWay;

    public function setUp(): void
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

        /** @var ServiceManager|MockObject $serviceLocator */
        $serviceLocator = $this->getMockBuilder(ServiceManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        $serviceLocator->method('get')->with(ComplexSearchService::SERVICE_ID)->willReturn($this->searchService);

        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['error'])
            ->getMockForAbstractClass();

        $this->subject = new RdfLtiProviderRepository();
        $this->subject->setServiceLocator($serviceLocator);
        $this->subject->setLogger($this->logger);
    }

    public function testGetRootClass()
    {
        $this->subject->setModel($this->getOntologyMock());

        $rootClass = $this->subject->getRootClass();

        $this->assertInstanceOf(RdfClass::class, $rootClass);
        $this->assertEquals(RdfLtiProviderRepository::CLASS_URI, $rootClass->getUri());
    }

    public function testCount()
    {
        $count = 12;

        $this->searchService->method('searchType')
            ->with($this->queryBuilder, RdfLtiProviderRepository::CLASS_URI, true)
            ->willReturn($this->query);
        $this->queryBuilder->expects($this->once())->method('setCriteria')->with($this->query);
        $this->gateWay->method('count')->with($this->queryBuilder)->willReturn($count);

        $this->assertEquals($count, $this->subject->count());
    }

    public function testGetProvidersCountWithExceptionReturn0AndLogsException()
    {
        $message = 'the exception message';
        $this->searchService->method('searchType')
            ->with($this->queryBuilder, RdfLtiProviderRepository::CLASS_URI, true)
            ->willThrowException(new ErrorException($message));
        $this->logger->expects($this->once())->method('error')->with('Unable to retrieve providers: ' . $message, []);

        $this->assertEquals(0, $this->subject->count());
    }

    /**
     * @dataProvider criteriaToTest
     *
     * @param string       $method
     * @param string|array $param
     * @param array        $criteria
     */
    public function testFind($method, $param, array $criteria)
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

        $resource1 = $this->getRdfResourceMock($uri1, $label1, $key1, $secret1, $callbackUrl1);
        $resource2 = $this->getRdfResourceMock($uri2, $label2, $key2, $secret2, $callbackUrl2);
        $resources = [$resource1, $resource2];

        $ltiProvider1 = new LtiProvider($uri1, $label1, $key1, $secret1, $callbackUrl1);
        $ltiProvider2 = new LtiProvider($uri2, $label2, $key2, $secret2, $callbackUrl2);
        $expected = [$ltiProvider1, $ltiProvider2];

        $this->searchService->method('searchType')
            ->with($this->queryBuilder, RdfLtiProviderRepository::CLASS_URI, true)
            ->willReturn($this->query);

        $this->queryBuilder->expects($this->once())->method('setCriteria')->with($this->query);
        $this->gateWay->method('search')->with($this->queryBuilder)->willReturn($resources);
        if (count($criteria)) {
            foreach ($criteria as $key => $value) {
                $this->query->expects($this->once())->method('add')->with($key)->willReturn($this->query);
                $this->query->expects($this->once())->method('contains')->with($value)->willReturn($this->query);
            }
        }

        $this->assertEquals($expected, $this->subject->$method($param));
    }

    public function criteriaToTest()
    {
        $label = 'value';

        return [
            ['findAll', null, []],
            ['searchByLabel', $label, [OntologyRdfs::RDFS_LABEL => $label]],
        ];
    }

    public function testSearchByOauthKey()
    {
        $uri2 = 'uri2';
        $label2 = 'label2';
        $key2 = 'key2';
        $secret2 = 'secret2';
        $callbackUrl2 = 'callbackUrl2';

        $resource2 = $this->getRdfResourceMock($uri2, $label2, $key2, $secret2, $callbackUrl2);
        $ltiProvider2 = new LtiProvider($uri2, $label2, $key2, $secret2, $callbackUrl2);

        $this->searchService->method('searchType')
            ->with($this->queryBuilder, RdfLtiProviderRepository::CLASS_URI, true)
            ->willReturn($this->query);

        $this->queryBuilder->expects($this->once())->method('setCriteria')->with($this->query);
        $this->gateWay->method('search')->with($this->queryBuilder)->willReturn([$resource2]);
        $this->query->expects($this->once())
            ->method('add')
            ->with(DataStore::PROPERTY_OAUTH_KEY)
            ->willReturn($this->query);
        $this->query->expects($this->once())
            ->method('contains')
            ->with('key2')
            ->willReturn($this->query);

        $this->assertEquals($ltiProvider2, $this->subject->searchByOauthKey('key2'));
    }

    public function testFindWithExceptionReturn0AndLogsException()
    {
        $message = 'the exception message';
        $this->searchService->method('searchType')
            ->willThrowException(new ErrorException($message));
        $this->logger->expects($this->once())->method('error')->with('Unable to retrieve providers: ' . $message, []);

        $this->assertEquals([], $this->subject->findAll());
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
            ->with($this->queryBuilder, RdfLtiProviderRepository::CLASS_URI, true)
            ->willReturn($this->query);

        $this->queryBuilder->expects($this->once())->method('setCriteria')->with($this->query);
        $this->gateWay->method('search')->with($this->queryBuilder)->willReturn($resources);

        $this->logger->expects($this->once())->method('error')
            ->with('Unable to retrieve provider properties: Argument ' . $position . ' passed to ' . $class . '::' . $function . '() must be an ' . $type . ', string given', []);
        $this->assertEquals([], $this->subject->findAll());
    }

    /**
     * @param string $uri
     * @param string $label
     * @param string $key
     * @param string $secret
     * @param string $callbackUrl
     *
     * @return MockObject
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
