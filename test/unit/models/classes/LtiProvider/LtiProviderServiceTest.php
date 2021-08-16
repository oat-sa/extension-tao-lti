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
 * Copyright (c) 2016-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

declare(strict_types=1);

namespace oat\taoLti\test\unit\models\classes\LtiProvider;

use oat\generis\test\MockObject;
use oat\generis\test\TestCase;
use oat\taoLti\models\classes\LtiProvider\LtiProvider;
use oat\taoLti\models\classes\LtiProvider\LtiProviderRepositoryInterface;
use oat\taoLti\models\classes\LtiProvider\LtiProviderService;

class LtiProviderServiceTest extends TestCase
{
    /** @var LtiProviderService */
    private $subject;

    /** @var LtiProviderRepositoryInterface */
    private $repository1;

    /** @var LtiProviderRepositoryInterface */
    private $repository2;

    public function setUp(): void
    {
        $this->repository1 = $this->createMock(LtiProviderRepositoryInterface::class);
        $this->repository2 = $this->createMock(LtiProviderRepositoryInterface::class);

        $this->subject = new LtiProviderService(
            [
                LtiProviderService::LTI_PROVIDER_LIST_IMPLEMENTATIONS => [
                    $this->repository1,
                    $this->repository2,
                ],
            ]
        );
    }
    public function testCount(): void
    {
        $this->expectsCount($this->repository1, 2);
        $this->expectsCount($this->repository2, 3);

        $this->assertSame(5, $this->subject->count());
    }
    public function testFindAll(): void
    {
        $provider1 = $this->createLtiProvider();
        $provider2 = $this->createLtiProvider();
        $this->expectsFindAll($this->repository1, [$provider1]);
        $this->expectsFindAll($this->repository2, [$provider2]);
        $this->assertSame([$provider1, $provider2], $this->subject->findAll());
    }
    public function testSearchByLabel(): void
    {
        $provider = $this->createLtiProvider();
        $this->expectsSearchByLabel($this->repository1, 'label1', [$provider]);
        $this->expectsSearchByLabel($this->repository2, 'label1', []);
        $this->assertSame([$provider], $this->subject->searchByLabel('label1'));
    }
    public function testSearchById(): void
    {
        $provider = $this->createLtiProvider();
        $this->expectsSearchById($this->repository1, 'id', null);
        $this->expectsSearchById($this->repository2, 'id', $provider);
        $this->assertSame($provider, $this->subject->searchById('id'));
    }
    public function testSearchByOauthKey(): void
    {
        $provider = $this->createLtiProvider();
        $this->expectsSearchByOauthKey($this->repository1, 'key', null);
        $this->expectsSearchByOauthKey($this->repository2, 'key', $provider);
        $this->assertSame($provider, $this->subject->searchByOauthKey('key'));
    }

    public function testSearchByToolClientId(): void
    {
        $provider = $this->createLtiProvider();
        $provider2 = $this->createLtiProvider();

        $this->expectsFindAll($this->repository1, [$provider]);
        $this->expectsFindAll($this->repository2, [$provider2]);
        
        $provider
            ->expects($this->once())
            ->method('getToolClientId')
            ->willReturn('client_id');
        
        $this->subject->searchByToolClientId('client_id');
    }

    public function testFailingSearchByToolClientId(): void
    {
        $provider = $this->createLtiProvider();
        $provider2 = $this->createLtiProvider();

        $this->expectsFindAll($this->repository1, [$provider]);
        $this->expectsFindAll($this->repository2, [$provider2]);

        $provider
            ->expects($this->once())
            ->method('getToolClientId')
            ->willReturn('not_this');

        $provider2
            ->expects($this->once())
            ->method('getToolClientId')
            ->willReturn('also_not_this');

        $this->assertNull($this->subject->searchByToolClientId('client_id'));
    }

    /**
     * @return LtiProvider|MockObject
     */
    private function createLtiProvider(): LtiProvider
    {
        return $this->createMock(LtiProvider::class);
    }

    /**
     * @param LtiProviderRepositoryInterface|MockObject $repository
     */
    private function expectsFindAll(LtiProviderRepositoryInterface $repository, array $result): void
    {
        $repository->method('findAll')
            ->willReturn($result);
    }
    /**
     * @param LtiProviderRepositoryInterface|MockObject $repository
     */
    private function expectsCount(LtiProviderRepositoryInterface $repository, int $count): void
    {
        $repository->method('count')
            ->willReturn($count);
    }

    /**
     * @param LtiProviderRepositoryInterface|MockObject $repository
     */
    private function expectsSearchByLabel(LtiProviderRepositoryInterface $repository, string $label, array $result): void
    {
        $repository->method('searchByLabel')
            ->with($label)
            ->willReturn($result);
    }

    /**
     * @param LtiProviderRepositoryInterface|MockObject $repository
     */
    private function expectsSearchById(LtiProviderRepositoryInterface $repository, string $id, ?LtiProvider $result): void
    {
        $repository
            ->method('searchById')
            ->with($id)
            ->willReturn($result);
    }

    /**
     * @param LtiProviderRepositoryInterface|MockObject $repository
     */
    private function expectsSearchByOauthKey(LtiProviderRepositoryInterface $repository, string $oauthKey, ?LtiProvider $result): void
    {
        $repository
            ->method('searchByOauthKey')
            ->with($oauthKey)
            ->willReturn($result);
    }
}
