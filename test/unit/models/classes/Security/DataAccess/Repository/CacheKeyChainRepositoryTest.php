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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoLti\test\unit\models\classes\Security\DataAccess\Repository;

use oat\generis\test\MockObject;
use oat\generis\test\TestCase;
use oat\oatbox\cache\SimpleCache;
use oat\tao\model\security\Business\Domain\Key\Key;
use oat\tao\model\security\Business\Domain\Key\KeyChain;
use oat\tao\model\security\Business\Domain\Key\KeyChainCollection;
use oat\tao\model\security\Business\Domain\Key\KeyChainQuery;
use oat\taoLti\models\classes\Security\DataAccess\Repository\CachedPlatformKeyChainRepository;
use oat\taoLti\models\classes\Security\DataAccess\Repository\PlatformKeyChainRepository;

class CacheKeyChainRepositoryTest extends TestCase
{
    public const IDENTIFIER = 'id';
    /** @var CachedPlatformKeyChainRepository */
    private $subject;

    /** @var SimpleCache|MockObject */
    private $simpleCacheMock;

    /** @var PlatformKeyChainRepository|MockObject */
    private $platformKeyChainRepositoryMock;

    public function setUp(): void
    {
        $this->subject = new CachedPlatformKeyChainRepository();
        $this->simpleCacheMock = $this->createMock(SimpleCache::class);
        $this->platformKeyChainRepositoryMock = $this->createMock(PlatformKeyChainRepository::class);

        $this->subject->setServiceLocator($this->getServiceLocatorMock(
            [
                SimpleCache::SERVICE_ID => $this->simpleCacheMock,
                PlatformKeyChainRepository::SERVICE_ID => $this->platformKeyChainRepositoryMock,
            ]
        ));
    }

    public function testSave(): void
    {
        $keyChain = $this->getKeyChain();

        $this->simpleCacheMock
            ->expects($this->exactly(2))
            ->method('set')
            ->withConsecutive(
                [sprintf(CachedPlatformKeyChainRepository::PRIVATE_PATTERN, self::IDENTIFIER), $keyChain->getPrivateKey()],
                [sprintf(CachedPlatformKeyChainRepository::PUBLIC_PATTERN, self::IDENTIFIER), $keyChain->getPublicKey()]
            );

        $this->platformKeyChainRepositoryMock
            ->expects($this->once())
            ->method('save');

        $this->subject->save($keyChain);
    }


    public function testFindAllWhenCacheEmpty(): void
    {
        $keyChain = $this->getKeyChain();
        $keyChainQuery = new KeyChainQuery(self::IDENTIFIER);

        $this->simpleCacheMock
            ->method('has')
            ->withConsecutive(
                [sprintf(CachedPlatformKeyChainRepository::PRIVATE_PATTERN, self::IDENTIFIER)],
                [sprintf(CachedPlatformKeyChainRepository::PUBLIC_PATTERN, self::IDENTIFIER)]
            )
            ->willReturnOnConsecutiveCalls(
                false,
                false
            );

        $this->simpleCacheMock
            ->expects($this->exactly(2))
            ->method('set')
            ->withConsecutive(
                [sprintf(CachedPlatformKeyChainRepository::PRIVATE_PATTERN, self::IDENTIFIER), $keyChain->getPrivateKey()],
                [sprintf(CachedPlatformKeyChainRepository::PUBLIC_PATTERN, self::IDENTIFIER), $keyChain->getPublicKey()]
            );

        $this->platformKeyChainRepositoryMock
            ->expects($this->once())
            ->method('findAll')
            ->willReturn(new KeyChainCollection($this->getKeyChain()));

        $this->subject->findAll($keyChainQuery);
    }

    public function testFindAll(): void
    {
        $keyChainQuery = new KeyChainQuery(self::IDENTIFIER);

        $this->simpleCacheMock
            ->method('has')
            ->withConsecutive(
                [sprintf(CachedPlatformKeyChainRepository::PRIVATE_PATTERN, self::IDENTIFIER)],
                [sprintf(CachedPlatformKeyChainRepository::PUBLIC_PATTERN, self::IDENTIFIER)]
            )
            ->willReturnOnConsecutiveCalls(
                true,
                true
            );

        $this->simpleCacheMock
            ->expects($this->once())
            ->method('getMultiple')
            ->with(
                [
                    sprintf(CachedPlatformKeyChainRepository::PRIVATE_PATTERN, self::IDENTIFIER),
                    sprintf(CachedPlatformKeyChainRepository::PUBLIC_PATTERN, self::IDENTIFIER),
                ]
            )->willReturn(
                [
                    'publicKey',
                    'privateKey',
                ]
            );

        $result = $this->subject->findAll($keyChainQuery);

        $keyChainCollection = $result->getKeyChains();
        $this->assertSame(self::IDENTIFIER, $keyChainCollection[0]->getIdentifier());
        $this->assertSame('privateKey', $keyChainCollection[0]->getPrivateKey()->getValue());
        $this->assertSame('publicKey', $keyChainCollection[0]->getPublicKey()->getValue());
    }

    private function getKeyChain(): KeyChain
    {
        $publicKey = new Key('publicKey');
        $privateKey = new Key('privateKey');

        return new KeyChain(
            self::IDENTIFIER,
            'name',
            $publicKey,
            $privateKey
        );
    }
}
