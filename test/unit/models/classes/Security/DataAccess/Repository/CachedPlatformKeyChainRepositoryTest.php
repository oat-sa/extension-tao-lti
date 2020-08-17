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

class CachedPlatformKeyChainRepositoryTest extends TestCase
{
    private const KEY_CHAIN_ID = 'id';
    private const KEY_CHAIN_NAME = 'name';

    /** @var CachedPlatformKeyChainRepository */
    private $subject;

    /** @var SimpleCache|MockObject */
    private $cache;

    /** @var PlatformKeyChainRepository|MockObject */
    private $platformKeyChainRepository;

    public function setUp(): void
    {
        $this->subject = new CachedPlatformKeyChainRepository();
        $this->cache = $this->createMock(SimpleCache::class);
        $this->platformKeyChainRepository = $this->createMock(PlatformKeyChainRepository::class);

        $this->platformKeyChainRepository
            ->method('getOption')
            ->willReturnCallback(
                function ($key) {
                    if ($key === PlatformKeyChainRepository::OPTION_DEFAULT_KEY_ID) {
                        return self::KEY_CHAIN_ID;
                    }

                    if ($key === PlatformKeyChainRepository::OPTION_DEFAULT_KEY_NAME) {
                        return self::KEY_CHAIN_NAME;
                    }

                    return null;
                }
            );

        $this->subject->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    SimpleCache::SERVICE_ID => $this->cache,
                    PlatformKeyChainRepository::SERVICE_ID => $this->platformKeyChainRepository,
                ]
            )
        );
    }

    public function testSave(): void
    {
        $keyChain = $this->getKeyChain();

        $this->cache
            ->expects($this->exactly(2))
            ->method('set')
            ->withConsecutive(
                [sprintf(CachedPlatformKeyChainRepository::PRIVATE_PATTERN, self::KEY_CHAIN_ID), $keyChain->getPrivateKey()->getValue()],
                [sprintf(CachedPlatformKeyChainRepository::PUBLIC_PATTERN, self::KEY_CHAIN_ID), $keyChain->getPublicKey()->getValue()]
            );

        $this->platformKeyChainRepository
            ->expects($this->once())
            ->method('save');

        $this->assertNull($this->subject->save($keyChain));
    }

    public function testFindAllWhenCacheEmpty(): void
    {
        $keyChain = $this->getKeyChain();
        $keyChainQuery = new KeyChainQuery(self::KEY_CHAIN_ID);

        $this->cache
            ->method('has')
            ->withConsecutive(
                [sprintf(CachedPlatformKeyChainRepository::PRIVATE_PATTERN, self::KEY_CHAIN_ID)],
                [sprintf(CachedPlatformKeyChainRepository::PUBLIC_PATTERN, self::KEY_CHAIN_ID)]
            )
            ->willReturnOnConsecutiveCalls(
                false,
                false
            );

        $this->cache
            ->expects($this->exactly(2))
            ->method('set')
            ->withConsecutive(
                [sprintf(CachedPlatformKeyChainRepository::PRIVATE_PATTERN, self::KEY_CHAIN_ID), $keyChain->getPrivateKey()->getValue()],
                [sprintf(CachedPlatformKeyChainRepository::PUBLIC_PATTERN, self::KEY_CHAIN_ID), $keyChain->getPublicKey()->getValue()]
            );

        $this->platformKeyChainRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn(new KeyChainCollection($this->getKeyChain()));

        $result = $this->subject->findAll($keyChainQuery);

        $keyChainCollection = $result->getKeyChains();

        $this->assertSame(self::KEY_CHAIN_ID, $keyChainCollection[0]->getIdentifier());
        $this->assertSame('privateKey', $keyChainCollection[0]->getPrivateKey()->getValue());
        $this->assertSame('publicKey', $keyChainCollection[0]->getPublicKey()->getValue());
    }

    public function testFindAll(): void
    {
        $keyChainQuery = new KeyChainQuery(self::KEY_CHAIN_ID);

        $this->cache
            ->method('has')
            ->withConsecutive(
                [sprintf(CachedPlatformKeyChainRepository::PRIVATE_PATTERN, self::KEY_CHAIN_ID)],
                [sprintf(CachedPlatformKeyChainRepository::PUBLIC_PATTERN, self::KEY_CHAIN_ID)]
            )
            ->willReturnOnConsecutiveCalls(
                true,
                true
            );

        $this->cache
            ->expects($this->once())
            ->method('getMultiple')
            ->with(
                [
                    sprintf(CachedPlatformKeyChainRepository::PRIVATE_PATTERN, self::KEY_CHAIN_ID),
                    sprintf(CachedPlatformKeyChainRepository::PUBLIC_PATTERN, self::KEY_CHAIN_ID),
                ]
            )->willReturn(
                [
                    sprintf(CachedPlatformKeyChainRepository::PRIVATE_PATTERN, self::KEY_CHAIN_ID) => 'privateKey',
                    sprintf(CachedPlatformKeyChainRepository::PUBLIC_PATTERN, self::KEY_CHAIN_ID) => 'publicKey',
                ]
            );

        $result = $this->subject->findAll($keyChainQuery);

        $keyChainCollection = $result->getKeyChains();

        $this->assertSame(self::KEY_CHAIN_ID, $keyChainCollection[0]->getIdentifier());
        $this->assertSame('privateKey', $keyChainCollection[0]->getPrivateKey()->getValue());
        $this->assertSame('publicKey', $keyChainCollection[0]->getPublicKey()->getValue());
    }

    private function getKeyChain(): KeyChain
    {
        return new KeyChain(
            self::KEY_CHAIN_ID,
            self::KEY_CHAIN_NAME,
            new Key('publicKey'),
            new Key('privateKey')
        );
    }
}
