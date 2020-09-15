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

namespace unit\models\classes\Security;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use oat\generis\test\MockObject;
use oat\generis\test\TestCase;
use oat\tao\model\security\Business\Domain\Key\Key;
use oat\tao\model\security\Business\Domain\Key\KeyChain;
use oat\tao\model\security\Business\Domain\Key\KeyChainCollection;
use oat\tao\model\security\Business\Domain\Key\KeyChainQuery;
use oat\taoLti\models\classes\Security\AccessTokenResponseGenerator;
use oat\taoLti\models\classes\Security\AuthorizationServer\AuthorizationServerFactory;
use oat\taoLti\models\classes\Security\DataAccess\Repository\CachedPlatformKeyChainRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AccessTokenResponseGeneratorTest extends TestCase
{
    /** @var AccessTokenResponseGenerator */
    private $subject;

    /** @var CachedPlatformKeyChainRepository|MockObject */
    private $keyChainRepositoryMock;

    /** @var AuthorizationServerFactory|MockObject */
    private $authorizationServerFactoryMock;


    /** @var ServerRequestInterface|MockObject */
    private $requestMock;

    /** @var ResponseInterface|MockObject */
    private $responseMock;

    /** @var KeyChainCollection */
    private $keyChainCollection;

    /** @var KeyChain */
    private $keyChain;

    /** @var KeyChainQuery */
    private $keyChainQuery;

    public function setUp(): void
    {
        $this->requestMock = $this->createMock(ServerRequestInterface::class);
        $this->responseMock = $this->createMock(ResponseInterface::class);
        $this->keyChainRepositoryMock = $this->createMock(CachedPlatformKeyChainRepository::class);
        $this->authorizationServerFactoryMock = $this->createMock(AuthorizationServerFactory::class);

        $this->keyChainQuery = new KeyChainQuery();

        $this->keyChain = new KeyChain(
            'defaultPlatformKeyId',
            'name',
            new Key('publicKey'),
            new Key('privateKey')
        );

        $this->keyChainCollection = new KeyChainCollection(
            $this->keyChain,
            new KeyChain('identifier2', 'name2', new Key('publicKey2'), new Key('privateKey2'))
        );

        $this->subject = new AccessTokenResponseGenerator();


        $this->subject->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    CachedPlatformKeyChainRepository::class => $this->keyChainRepositoryMock,
                    AuthorizationServerFactory::class => $this->authorizationServerFactoryMock,
                ]
            )
        );
    }

    public function testGenerate(): void
    {
        $authorizationServer = $this->createMock(AuthorizationServer::class);

        $this->keyChainRepositoryMock
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($this->keyChainCollection)
            ->with($this->keyChainQuery);

        $this->authorizationServerFactoryMock
            ->method('create')
            ->with($this->keyChain)
            ->willReturn($authorizationServer);

        $authorizationServer
            ->expects($this->once())
            ->method('respondToAccessTokenRequest')
            ->willReturn($this->responseMock);

        $this->subject->generate($this->requestMock, $this->responseMock);
    }

    public function testGenerateWithoutKey(): void
    {
        $this->expectException(OAuthServerException::class);

        $this->keyChainRepositoryMock
            ->expects($this->once())
            ->method('findAll')
            ->willReturn(new KeyChainCollection())
            ->with($this->keyChainQuery);

        $this->subject->generate($this->requestMock, $this->responseMock);
    }
}
