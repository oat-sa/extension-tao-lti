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

use oat\generis\test\TestCase;
use oat\oatbox\cache\SimpleCache;
use oat\tao\model\security\Business\Domain\Key\Jwk;
use oat\tao\model\security\Business\Domain\Key\Jwks;
use oat\taoLti\models\classes\Security\DataAccess\Repository\CachedPlatformJwksRepository;
use oat\taoLti\models\classes\Security\DataAccess\Repository\PlatformJwksRepository;

class CachedPlatformJwksRepositoryTest extends TestCase
{
    /** @var CachedPlatformJwksRepository */
    private $subject;

    /** @var SimpleCache */
    private $cacheMock;

    /** @var PlatformJwksRepository */
    private $platformJwksRepositoryMock;

    /** @var Jwks  */
    private $jwks;

    public function setUp(): void
    {
        $this->platformJwksRepositoryMock = $this->createMock(PlatformJwksRepository::class);
        $this->cacheMock = $this->createMock(SimpleCache::class);
        $this->subject = new CachedPlatformJwksRepository();
        $this->subject->setServiceLocator($this->getServiceLocatorMock(
            [
                PlatformJwksRepository::class => $this->platformJwksRepositoryMock,
                SimpleCache::SERVICE_ID => $this->cacheMock,
            ]
        ));

        $jwk = new Jwk(
            'kty',
            'e',
            'n',
            'kid',
            'alg',
            'use'
        );

        $this->jwks = new Jwks($jwk);

    }

    public function testCachedFind(): void
    {
        $this->platformJwksRepositoryMock
            ->method('find')
            ->willReturn($this->jwks);

        $this->cacheMock
            ->expects($this->once())
            ->method('has')
            ->with('PLATFORM_JWKS')
            ->willReturn(true);


        $this->cacheMock
            ->expects($this->once())
            ->method('delete')
            ->with('PLATFORM_JWKS');

        $this->cacheMock
            ->expects($this->once())
            ->method('set')
            ->with(
                'PLATFORM_JWKS', $this->jwks->jsonSerialize()
            );

        $this->subject->find();
    }

    public function testNotCachedFind(): void
    {

        $this->platformJwksRepositoryMock
            ->method('find')
            ->willReturn($this->jwks);

        $this->cacheMock
            ->expects($this->once())
            ->method('has')
            ->with('PLATFORM_JWKS')
            ->willReturn(false);


        $this->cacheMock
            ->expects($this->never())
            ->method('delete')
            ->with('PLATFORM_JWKS');

        $this->cacheMock
            ->expects($this->once())
            ->method('set')
            ->with(
                'PLATFORM_JWKS', $this->jwks->jsonSerialize()
            );

        $this->subject->find();
    }

}
