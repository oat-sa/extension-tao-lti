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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 */

namespace oat\taoLti\test\unit\models\classes\Lis;

use common_http_InvalidSignatureException;
use IMSGlobal\LTI\OAuth\OAuthToken;
use oat\generis\test\MockObject;
use oat\generis\test\TestCase;
use oat\taoLti\models\classes\Lis\LisAuthAdapter;
use oat\taoLti\models\classes\Lis\LisAuthAdapterException;
use oat\taoLti\models\classes\Lis\LisOAuthConsumer;
use oat\taoLti\models\classes\Lis\LisOauthService;
use oat\taoLti\models\classes\Lis\LtiProviderUser;
use oat\taoLti\models\classes\LtiProvider\LtiProvider;
use Psr\Http\Message\ServerRequestInterface;

class LisAuthAdapterTest extends TestCase
{
    public function testAuthenticateSuccess()
    {
        /** @var ServerRequestInterface|MockObject $requestMock */
        $requestMock = $this->createMock(ServerRequestInterface::class);

        /** @var LtiProvider|MockObject $ltiProviderMock */
        $ltiProviderMock = $this->createMock(LtiProvider::class);

        /** @var LisOAuthConsumer|MockObject $oauthConsumerMock */
        $oauthConsumerMock = $this->createMock(LisOAuthConsumer::class);
        $oauthConsumerMock->method('getLtiProvider')->willReturn($ltiProviderMock);

        $oauthTokenMock = $this->createMock(OAuthToken::class);

        /** @var LisOauthService|MockObject $lisOauthServiceMock */
        $lisOauthServiceMock = $this->createMock(LisOauthService::class);
        $lisOauthServiceMock->expects($this->once())
            ->method('validatePsrRequest')
            ->with($requestMock)
            ->willReturn([$oauthConsumerMock, $oauthTokenMock]);

        $authAdapter = new LisAuthAdapter($requestMock);

        $authAdapter->setServiceLocator($this->getServiceLocatorMock([
            LisOauthService::SERVICE_ID => $lisOauthServiceMock
        ]));

        /** @noinspection PhpUnhandledExceptionInspection */
        $user = $authAdapter->authenticate();
        $this->assertInstanceOf(LtiProviderUser::class, $user);
        $this->assertSame($ltiProviderMock, $user->getLtiProvider());
    }

    public function testAuthenticateInvalidSignature()
    {
        /** @var ServerRequestInterface|MockObject $requestMock */
        $requestMock = $this->createMock(ServerRequestInterface::class);

        /** @var LisOauthService|MockObject $lisOauthServiceMock */
        $lisOauthServiceMock = $this->createMock(LisOauthService::class);
        $lisOauthServiceMock->expects($this->once())
            ->method('validatePsrRequest')
            ->with($requestMock)
            ->willThrowException(new common_http_InvalidSignatureException('mmm'));

        $authAdapter = new LisAuthAdapter($requestMock);

        $authAdapter->setServiceLocator($this->getServiceLocatorMock([
            LisOauthService::SERVICE_ID => $lisOauthServiceMock
        ]));

        $this->expectException(LisAuthAdapterException::class);
        /** @noinspection PhpUnhandledExceptionInspection */
        $authAdapter->authenticate();
    }
}
