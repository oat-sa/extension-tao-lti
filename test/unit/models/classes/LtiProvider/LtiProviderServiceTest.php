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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoLti\test\unit\models\classes\LtiProvider;

use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use oat\generis\test\MockObject;
use oat\generis\test\TestCase;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use oat\taoDelivery\model\execution\DeliveryExecutionService;
use oat\taoLti\models\classes\LtiProvider\LtiProvider;
use oat\taoLti\models\classes\LtiProvider\LtiProviderRepositoryInterface;
use oat\taoLti\models\classes\LtiProvider\LtiProviderService;
use oat\taoLti\models\classes\Platform\Service\InvalidLtiProviderException;

class LtiProviderServiceTest extends TestCase
{
    public const COUNT_1 = 10;
    public const COUNT_2 = 12;
    public const FIND_ALL_1 = ['key1' => 'value1'];
    public const FIND_ALL_2 = ['key2' => 'value2'];
    public const LABEL = 'the sought label';
    public const ID = 'uri';
    public const OAUTH_KEY = 'okey1';
    public const SEARCH_1 = ['key3' => 'value3'];
    public const SEARCH_2 = ['key4' => 'value4'];
    public const SEARCH_ID_RESULT = ['uri' => 'v4'];
    public const SEARCH_OAUTH_KEY_RESULT = ['uri' => 'v5'];

    private const LTI_PROVIDER_PATH = 'path/to/lti/provider';

    /** @var LtiProviderService */
    private $subject;

    /** @var LtiProvider|MockObject */
    private $ltiProviderMock;

    /** @var DeliveryExecutionService|MockObject */
    private $deliveryExecutionServiceMock;

    public function setUp(): void
    {
        $this->ltiProviderMock = $this->createMock(LtiProvider::class);
        $this->deliveryExecutionServiceMock = $this->createMock(DeliveryExecutionService::class);

        $repository1 = $this->createRepositoryMock(
            self::COUNT_1,
            self::FIND_ALL_1,
            self::LABEL,
            self::SEARCH_1,
            self::ID,
            self::OAUTH_KEY,
            $this->ltiProviderMock,
            null
        );
        $repository2 = $this->createRepositoryMock(
            self::COUNT_2,
            self::FIND_ALL_2,
            self::LABEL,
            self::SEARCH_2,
            self::ID,
            self::OAUTH_KEY,
            null,
            self::SEARCH_OAUTH_KEY_RESULT
        );

        $repository3 = $this->createRepositoryMock(
            self::COUNT_2,
            [$this->ltiProviderMock],
            self::LABEL,
            self::SEARCH_2,
            self::ID,
            self::OAUTH_KEY,
            null,
            self::SEARCH_OAUTH_KEY_RESULT
        );

        $this->subject = new LtiProviderService([
            LtiProviderService::LTI_PROVIDER_LIST_IMPLEMENTATIONS => [$repository1, $repository2, $repository3],
        ]);

        $this->subject->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    DeliveryExecutionService::SERVICE_ID => $this->deliveryExecutionServiceMock,
                ]
            )
        );
    }

    public function testCount(): void
    {
        $this->assertEquals(self::COUNT_1 + self::COUNT_2 + self::COUNT_2, $this->subject->count());
    }

    public function testFindAll(): void
    {
        $this->assertEquals(array_merge(self::FIND_ALL_1, self::FIND_ALL_2, [$this->ltiProviderMock]), $this->subject->findAll());
    }

    public function testSearchByLabel(): void
    {
        $this->assertEquals(array_merge(self::SEARCH_1, self::SEARCH_2), $this->subject->searchByLabel(self::LABEL));
    }

    public function testSearchById(): void
    {
        $this->assertEquals($this->ltiProviderMock, $this->subject->searchById(self::ID));
    }

    public function testSearchByOauthKey(): void
    {
        $this->assertEquals(self::SEARCH_OAUTH_KEY_RESULT, $this->subject->searchByOauthKey(self::OAUTH_KEY));
    }

    private function createRepositoryMock(
        $count,
        array $findAllResult,
        $label,
        array $searchResult,
        $searchId,
        $searchOauthKey,
        $searchByIdhResult,
        $searchByOauthKeyResult
    ) {
        $repository = $this->getMockBuilder(LtiProviderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['count', 'findAll', 'searchByLabel', 'searchById'])
            ->getMockForAbstractClass();
        $repository->method('count')->willReturn($count);
        $repository->method('findAll')->willReturn($findAllResult);
        $repository->method('searchByLabel')->with($label)->willReturn($searchResult);
        $repository->method('searchById')->with($searchId)->willReturn($searchByIdhResult);
        $repository->method('searchByOauthKey')->with($searchOauthKey)->willReturn($searchByOauthKeyResult);

        return $repository;
    }

    public function testSearchByDeliveryExecutionId(): void
    {
        $modelMock = $this->createMock(Ontology::class);
        $this->subject->setModel($modelMock);
        $deliveryResourceMock = $this->createMock(core_kernel_classes_Resource::class);
        $deliveryPropertyMock = $this->createMock(core_kernel_classes_Property::class);
        $deliveryExecutionMock = $this->createMock(DeliveryExecutionInterface::class);

        $this->deliveryExecutionServiceMock
            ->expects($this->once())
            ->method('getDeliveryExecution')
            ->willReturn($deliveryExecutionMock);

        $deliveryExecutionMock
            ->method('getDelivery')
            ->willReturn($deliveryResourceMock);

        $modelMock
            ->expects($this->once())
            ->method('getProperty')
            ->with('http://www.tao.lu/Ontologies/TAODelivery.rdf#AssembledDeliveryContainer')
            ->willReturn($deliveryPropertyMock);

        $deliveryResourceMock
            ->expects($this->once())
            ->method('getOnePropertyValue')
            ->willReturn($this->getDeliveryContainerProperty(
                self::ID,
                self::LTI_PROVIDER_PATH
            ));

        $this->subject->searchByDeliveryExecutionId('deliveryExecutionId');
    }

    private function getDeliveryContainerProperty(string $ltiProvider, string $ltiProviderPath): string
    {
        return sprintf(
            '{"container":"lti","params":{"ltiProvider":"%s","ltiPath":"%s"}}',
            $ltiProvider,
            $ltiProviderPath
        );
    }

    public function testFindByToolClientId(): void
    {
        $this->ltiProviderMock
            ->method('getToolClientId')
            ->willReturn('client_id');

        $this->assertSame($this->ltiProviderMock, $this->subject->searchByToolClientId('client_id'));
    }

    public function testFindByDifferentToolClientId(): void
    {
        $this->expectException(InvalidLtiProviderException::class);

        $this->ltiProviderMock
            ->method('getToolClientId')
            ->willReturn('another_client_id');

        $this->assertSame($this->ltiProviderMock, $this->subject->searchByToolClientId('client_id'));
    }
}
