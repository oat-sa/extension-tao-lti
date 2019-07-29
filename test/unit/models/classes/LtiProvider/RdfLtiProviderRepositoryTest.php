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

use oat\generis\model\OntologyRdfs;
use oat\generis\test\TestCase;
use oat\oatbox\service\exception\InvalidServiceManagerException;
use oat\oatbox\service\ServiceManager;
use Psr\Log\LoggerInterface;

/**
 * Service methods to manage the LTI provider business objects.
 */
class RdfLtiProviderRepositoryTest extends TestCase
{
    /** @var RdfLtiProviderRepository */
    private $subject;

    /** @var RdfLtiProviderFinder|\PHPUnit_Framework_MockObject_MockObject $finderService */
    private $finderService;

    /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject $logger */
    private $logger;

    public function setUp()
    {
        $this->finderService = $this->getMockBuilder(RdfLtiProviderFinder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getResources', 'getResourcesCount'])
            ->getMock();

        /** @var ServiceManager|\PHPUnit_Framework_MockObject_MockObject $serviceLocator */
        $serviceLocator = $this->getMockBuilder(ServiceManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        $serviceLocator->method('get')->with(RdfLtiProviderFinder::class)->willReturn($this->finderService);

        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['error'])
            ->getMockForAbstractClass();

        $this->subject = new RdfLtiProviderRepository();
        $this->subject->setServiceLocator($serviceLocator);
        $this->subject->setLogger($this->logger);
    }

    public function testCount()
    {
        $count = 12;
        $this->finderService->method('getResourcesCount')->willReturn($count);
        $this->assertEquals($count, $this->subject->count());
    }

    public function testCountWithExceptionReturn0AndLogsException()
    {
        $message = 'the exception message';
        $this->finderService->method('getResourcesCount')
            ->willThrowException(new InvalidServiceManagerException($message));
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

        $resource1 = new LtiProviderResource($uri1);
        $resource1->setLabel($label1);
        $resource1->setKey($key1);
        $resource1->setSecret($secret1);
        $resource1->setCallbackUrl($callbackUrl1);
        $resource2 = new LtiProviderResource($uri2);
        $resource2->setLabel($label2);
        $resource2->setKey($key2);
        $resource2->setSecret($secret2);
        $resource2->setCallbackUrl($callbackUrl2);
        $resources = [$resource1, $resource2];

        $expected = [
            new LtiProvider($uri1, $label1, $key1, $secret1, $callbackUrl1),
            new LtiProvider($uri2, $label2, $key2, $secret2, $callbackUrl2),
        ];

        $this->finderService->method('getResources')->with($criteria)->willReturn($resources);
        $this->assertEquals($expected, $this->subject->$method($param));
    }

    public function criteriaToTest()
    {
        $key = 'key';
        $value = 'value';

        return [
            ['findAll', null, []],
            ['searchByLabel', $value, [OntologyRdfs::RDFS_LABEL => $value]],
            ['findBy', [$key => $value], [$key => $value]],
        ];
    }

    /**
     * @dataProvider methodsToTest
     *
     * @param string $method
     * @param string|array $param
     */
    public function testFindWithExceptionReturn0AndLogsException($method, $param)
    {
        $message = 'the exception message';
        $this->finderService->method('getResources')
            ->willThrowException(new InvalidServiceManagerException($message));
        $this->logger->expects($this->once())->method('error')->with('Unable to retrieve providers: ' . $message, []);

        $this->assertEquals([], $this->subject->$method($param));
    }

    public function methodsToTest()
    {
        return [
            ['findAll', null],
            ['searchByLabel', 'blah'],
            ['findBy', ['key' => 'value']],
        ];
    }
}
