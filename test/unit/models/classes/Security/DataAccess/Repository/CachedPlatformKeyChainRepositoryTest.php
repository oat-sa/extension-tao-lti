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
use oat\generis\test\ServiceManagerMockTrait;
use OAT\Library\Lti1p3Core\Security\Key\Key;
use OAT\Library\Lti1p3Core\Security\Key\KeyChain;
use OAT\Library\Lti1p3Core\Security\Key\KeyChainInterface;
use oat\oatbox\cache\SimpleCache;
use oat\taoLti\models\classes\Security\DataAccess\Repository\CachedPlatformKeyChainRepository;
use oat\taoLti\models\classes\Security\DataAccess\Repository\PlatformKeyChainRepository;
use PHPUnit\Framework\TestCase;

class CachedPlatformKeyChainRepositoryTest extends TestCase
{
    use ServiceManagerMockTrait;

    private const KEY_CHAIN_ID = 'id';
    private const KEY_CHAIN_NAME = 'name';

    /** @var CachedPlatformKeyChainRepository */
    private CachedPlatformKeyChainRepository $subject;

    /** @var SimpleCache|MockObject */
    private SimpleCache $cache;

    /** @var PlatformKeyChainRepository|MockObject */
    private PlatformKeyChainRepository $platformKeyChainRepository;

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
            $this->getServiceManagerMock(
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
                [
                    sprintf(CachedPlatformKeyChainRepository::PRIVATE_PATTERN, self::KEY_CHAIN_ID),
                    $keyChain->getPrivateKey()->getContent(),
                ],
                [
                    sprintf(CachedPlatformKeyChainRepository::PUBLIC_PATTERN, self::KEY_CHAIN_ID),
                    $keyChain->getPublicKey()->getContent(),
                ],
            );

        $this->platformKeyChainRepository
            ->expects($this->once())
            ->method('saveDefaultKeyChain');

        $this->subject->saveDefaultKeyChain($keyChain);
    }

    public function testFindWhenCacheEmpty(): void
    {
        $keyChain = $this->getKeyChain();

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
                [
                    sprintf(CachedPlatformKeyChainRepository::PRIVATE_PATTERN, self::KEY_CHAIN_ID),
                    $keyChain->getPrivateKey()->getContent(),
                ],
                [
                    sprintf(CachedPlatformKeyChainRepository::PUBLIC_PATTERN, self::KEY_CHAIN_ID),
                    $keyChain->getPublicKey()->getContent(),
                ]
            );

        $this->platformKeyChainRepository
            ->expects($this->once())
            ->method('find')
            ->willReturn($this->getKeyChain());

        $keyChain = $this->subject->find(self::KEY_CHAIN_ID);

        $this->assertSame(self::KEY_CHAIN_ID, $keyChain->getIdentifier());
        $this->assertSame('privateKey', $keyChain->getPrivateKey()->getContent());
        $this->assertSame('publicKey', $keyChain->getPublicKey()->getContent());
    }

    public function testFind(): void
    {
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

        $keyChain = $this->subject->find(self::KEY_CHAIN_ID);

        $this->assertSame(self::KEY_CHAIN_ID, $keyChain->getIdentifier());
        $this->assertSame('privateKey', $keyChain->getPrivateKey()->getContent());
        $this->assertSame('publicKey', $keyChain->getPublicKey()->getContent());
    }

    private function getKeyChain(): KeyChainInterface
    {
        return new KeyChain(
            self::KEY_CHAIN_ID,
            self::KEY_CHAIN_NAME,
            new Key('publicKey'),
            new Key('privateKey')
        );
    }
}
