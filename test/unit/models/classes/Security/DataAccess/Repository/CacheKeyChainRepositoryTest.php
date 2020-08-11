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

/**
 * Created by PhpStorm.
 * User: bartlomiejmarszal
 * Date: 2020-08-11
 * Time: 13:50
 */

namespace oat\taoLti\test\unit\models\classes\Security\DataAccess\Repository;

use oat\generis\test\MockObject;
use oat\generis\test\TestCase;
use oat\oatbox\cache\SimpleCache;
use oat\tao\model\security\Business\Domain\Key\Key;
use oat\tao\model\security\Business\Domain\Key\KeyChain;
use oat\tao\model\security\Business\Domain\Key\KeyChainQuery;
use oat\taoLti\models\classes\Platform\Service\KeyChainGenerator;
use oat\taoLti\models\classes\Security\DataAccess\Repository\CacheKeyChainRepository;
use oat\taoLti\models\classes\Security\DataAccess\Repository\PlatformKeyChainRepository;

class CacheKeyChainRepositoryTest extends TestCase
{
    public const IDENTIFIER = 'id';
    /** @var CacheKeyChainRepository */
    private $subject;

    /** @var SimpleCache|MockObject */
    private $simpleCacheMock;

    /** @var KeyChainGenerator|MockObject */
    private $keyChainGeneratorMock;

    /** @var PlatformKeyChainRepository|MockObject */
    private $platformKeyChainRepositoryMock;

    public function setUp(): void
    {
        $this->subject = new CacheKeyChainRepository();
        $this->simpleCacheMock = $this->createMock(SimpleCache::class);
        $this->keyChainGeneratorMock = $this->createMock(KeyChainGenerator::class);
        $this->platformKeyChainRepositoryMock = $this->createMock(PlatformKeyChainRepository::class);

        $this->subject->setServiceLocator($this->getServiceLocatorMock(
            [
                SimpleCache::SERVICE_ID => $this->simpleCacheMock,
                KeyChainGenerator::class => $this->keyChainGeneratorMock,
                PlatformKeyChainRepository::SERVICE_ID => $this->platformKeyChainRepositoryMock
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
                [CacheKeyChainRepository::PRIVATE_PREFIX . self::IDENTIFIER, $keyChain->getPrivateKey()],
                [CacheKeyChainRepository::PUBLIC_PREFIX . self::IDENTIFIER, $keyChain->getPublicKey()]
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
                [CacheKeyChainRepository::PRIVATE_PREFIX . self::IDENTIFIER],
                [CacheKeyChainRepository::PUBLIC_PREFIX . self::IDENTIFIER]
            )
            ->willReturnOnConsecutiveCalls(
                false,
                false
            );

        $this->keyChainGeneratorMock
            ->expects($this->once())
            ->method('getKeyChain')
            ->willReturn($this->getKeyChain());

        $this->simpleCacheMock
            ->expects($this->exactly(2))
            ->method('set')
            ->withConsecutive(
                [CacheKeyChainRepository::PRIVATE_PREFIX . self::IDENTIFIER, $keyChain->getPrivateKey()],
                [CacheKeyChainRepository::PUBLIC_PREFIX . self::IDENTIFIER, $keyChain->getPublicKey()]
            );


        $this->simpleCacheMock
            ->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                [CacheKeyChainRepository::PUBLIC_PREFIX . self::IDENTIFIER],
                [CacheKeyChainRepository::PRIVATE_PREFIX . self::IDENTIFIER]
            )->willReturnOnConsecutiveCalls(
                'publicKey',
                'privateKey'
            );

        $this->subject->findAll($keyChainQuery);
    }

    public function testFindAll(): void
    {
        $keyChainQuery = new KeyChainQuery(self::IDENTIFIER);

        $this->simpleCacheMock
            ->method('has')
            ->withConsecutive(
                [CacheKeyChainRepository::PRIVATE_PREFIX . self::IDENTIFIER],
                [CacheKeyChainRepository::PUBLIC_PREFIX . self::IDENTIFIER]
            )
            ->willReturnOnConsecutiveCalls(
                true,
                true
            );

        $this->simpleCacheMock
            ->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                [CacheKeyChainRepository::PUBLIC_PREFIX . self::IDENTIFIER],
                [CacheKeyChainRepository::PRIVATE_PREFIX . self::IDENTIFIER]
            )->willReturnOnConsecutiveCalls(
                'publicKey',
                'privateKey'
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

        $keyChain = new KeyChain(
            self::IDENTIFIER,
            'name',
            $publicKey,
            $privateKey
        );

        return $keyChain;
    }
}
