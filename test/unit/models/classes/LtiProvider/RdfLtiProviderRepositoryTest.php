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
 * Copyright (c) 2019-2020 (original work) Open Assessment Technologies SA
 */

namespace oat\taoLti\models\classes\LtiProvider;

use common_exception_Error as ErrorException;
use common_exception_InvalidArgumentType as InvalidArgumentTypeException;
use core_kernel_classes_Class as RdfClass;
use core_kernel_classes_Resource as RdfResource;
use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use oat\generis\model\OntologyRdfs;
use oat\generis\test\unit\OntologyMockTest;
use oat\search\base\QueryBuilderInterface;
use oat\search\base\QueryInterface;
use oat\search\base\SearchGateWayInterface;
use oat\search\Query;
use oat\tao\model\oauth\DataStore;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

class RdfLtiProviderRepositoryTest extends OntologyMockTest
{
    /** @var RdfLtiProviderRepository */
    private $subject;

    /** @var ComplexSearchService|MockObject */
    private $searchService;

    /** @var LoggerInterface|MockObject */
    private $logger;

    /** @var QueryBuilderInterface|MockObject */
    private $queryBuilder;

    /** @var QueryInterface|MockObject */
    private $query;

    /** @var SearchGateWayInterface|MockObject */
    private $gateWay;

    /** @var LtiProviderFactory|MockObject */
    private $ltiProviderFactory;

    public function setUp(): void
    {
        $this->query = $this->createMock(Query::class);
        $this->queryBuilder = $this->createMock(QueryBuilderInterface::class);
        $this->gateWay = $this->createMock(SearchGateWayInterface::class);
        $this->searchService = $this->createMock(ComplexSearchService::class);
        $this->ltiProviderFactory = $this->createMock(LtiProviderFactory::class);

        $this->searchService
            ->method('query')
            ->willReturn($this->queryBuilder);

        $this->searchService
            ->method('getGateway')
            ->willReturn($this->gateWay);

        $this->logger = $this->createMock(LoggerInterface::class);

        $this->subject = new RdfLtiProviderRepository();
        $this->subject->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    ComplexSearchService::SERVICE_ID => $this->searchService,
                    LtiProviderFactory::class => $this->ltiProviderFactory
                ]
            )
        );
        $this->subject->setLogger($this->logger);
    }

    public function testGetRootClass(): void
    {
        $this->subject->setModel($this->getOntologyMock());

        $rootClass = $this->subject->getRootClass();

        $this->assertInstanceOf(RdfClass::class, $rootClass);
        $this->assertEquals(RdfLtiProviderRepository::CLASS_URI, $rootClass->getUri());
    }

    public function testCount(): void
    {
        $count = 12;

        $this->searchService
            ->method('searchType')
            ->with($this->queryBuilder, RdfLtiProviderRepository::CLASS_URI, true)
            ->willReturn($this->query);

        $this->queryBuilder
            ->expects($this->once())
            ->method('setCriteria')
            ->with($this->query);

        $this->gateWay
            ->method('count')
            ->with($this->queryBuilder)
            ->willReturn($count);

        $this->assertEquals($count, $this->subject->count());
    }

    public function testGetProvidersCountWithExceptionReturn0AndLogsException(): void
    {
        $message = 'the exception message';

        $this->searchService
            ->method('searchType')
            ->with($this->queryBuilder, RdfLtiProviderRepository::CLASS_URI, true)
            ->willThrowException(new ErrorException($message));

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('Unable to retrieve providers: ' . $message, []);

        $this->assertEquals(0, $this->subject->count());
    }

    /**
     * @dataProvider criteriaToTest
     */
    public function testFind(string $method, ?string $param, array $criteria): void
    {
        $resource1 = $this->createMock(RdfResource::class);
        $resource2 = $this->createMock(RdfResource::class);

        $resources = [$resource1, $resource2];

        $ltiProvider1 = $this->createMock(LtiProvider::class);
        $ltiProvider2 = $this->createMock(LtiProvider::class);

        $this->ltiProviderFactory
            ->method('createFromResource')
            ->willReturnOnConsecutiveCalls(
                ...[
                    $ltiProvider1,
                    $ltiProvider2,
                ]
            );

        $expected = [$ltiProvider1, $ltiProvider2];

        $this->searchService
            ->method('searchType')
            ->with($this->queryBuilder, RdfLtiProviderRepository::CLASS_URI, true)
            ->willReturn($this->query);

        $this->queryBuilder
            ->expects($this->once())
            ->method('setCriteria')
            ->with($this->query);

        $this->gateWay
            ->method('search')
            ->with($this->queryBuilder)
            ->willReturn($resources);

        if (count($criteria)) {
            foreach ($criteria as $key => $value) {
                $this->query
                    ->expects($this->once())
                    ->method('add')
                    ->with($key)
                    ->willReturn($this->query);

                $this->query
                    ->expects($this->once())
                    ->method('__call')
                    ->with('contains')
                    ->willReturnCallback(
                        function ($key) use ($value) {
                            if ($key === $value) {
                                return $this->query;
                            }
                        }
                    );
            }
        }

        $this->assertEquals($expected, $this->subject->$method($param));
    }

    public function criteriaToTest(): array
    {
        $label = 'value';

        return [
            [
                'findAll',
                null,
                []
            ],
            [
                'searchByLabel',
                $label,
                [
                    OntologyRdfs::RDFS_LABEL => $label
                ]
            ],
        ];
    }

    public function testSearchByOauthKey(): void
    {
        $resource = $this->createMock(RdfResource::class);
        $ltiProvider = $this->createMock(LtiProvider::class);

        $this->ltiProviderFactory
            ->method('createFromResource')
            ->willReturn($ltiProvider);

        $this->searchService
            ->method('searchType')
            ->with($this->queryBuilder, RdfLtiProviderRepository::CLASS_URI, true)
            ->willReturn($this->query);

        $this->queryBuilder
            ->expects($this->once())
            ->method('setCriteria')
            ->with($this->query);

        $this->gateWay
            ->method('search')
            ->with($this->queryBuilder)
            ->willReturn([$resource]);

        $this->query
            ->expects($this->once())
            ->method('add')
            ->with(DataStore::PROPERTY_OAUTH_KEY)
            ->willReturn($this->query);

        $this->query
            ->expects($this->once())
            ->method('__call')
            ->with('contains')
            ->willReturnCallback(
                function ($key) {
                    if ($key === 'key2') {
                        return $this->query;
                    }
                }
            );

        $this->assertEquals($ltiProvider, $this->subject->searchByOauthKey('key2'));
    }

    public function testFindWithExceptionReturn0AndLogsException()
    {
        $message = 'the exception message';

        $this->searchService
            ->method('searchType')
            ->willThrowException(new ErrorException($message));

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('Unable to retrieve providers: ' . $message, []);

        $this->assertEquals([], $this->subject->findAll());
    }

    public function testFindWithFaultyPropertyValuesReturnsEmptyArray(): void
    {
        $class = 'class';
        $function = 'function';
        $position = 0;
        $type = 'type';
        $object = 'object';

        $resource = $this->createMock(RdfResource::class);

        $this->ltiProviderFactory
            ->method('createFromResource')
            ->willThrowException(new InvalidArgumentTypeException($class, $function, $position, $type, $object));

        $resources = [$resource];

        $this->searchService
            ->method('searchType')
            ->with($this->queryBuilder, RdfLtiProviderRepository::CLASS_URI, true)
            ->willReturn($this->query);

        $this->queryBuilder
            ->expects($this->once())
            ->method('setCriteria')
            ->with($this->query);

        $this->gateWay
            ->method('search')
            ->with($this->queryBuilder)
            ->willReturn($resources);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                'Unable to retrieve provider properties: Argument ' . $position .
                ' passed to ' . $class . '::' . $function . '() must be an ' . $type . ', string given',
                []
            );

        $this->assertEquals([], $this->subject->findAll());
    }
}
