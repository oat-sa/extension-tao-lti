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

use IMSGlobal\LTI\OAuth\OAuthConsumer;
use IMSGlobal\LTI\OAuth\OAuthException as LtiOAuthException;
use IMSGlobal\LTI\OAuth\OAuthToken;
use oat\generis\test\MockObject;
use oat\generis\test\TestCase;
use oat\oatbox\log\LoggerService;
use oat\tao\model\oauth\nonce\NoNonce;
use oat\taoLti\models\classes\Lis\LisOauthDataStore;
use oat\taoLti\models\classes\LtiProvider\LtiProvider;
use oat\taoLti\models\classes\LtiProvider\LtiProviderService;

class LisOauthDataStoreTest extends TestCase
{
    public function testLookupConsumer()
    {
        $ltiProviderMock = $this->createMock(LtiProvider::class);
        $ltiProviderMock->method('getCallbackUrl')->willReturn('clb_url');

        /** @var LtiProviderService|MockObject $ltiProviderServiceMock */
        $ltiProviderServiceMock = $this->createMock(LtiProviderService::class);
        $ltiProviderServiceMock->expects($this->once())
            ->method('searchByOauthKey')
            ->with('key1')
            ->willReturn($ltiProviderMock);

        $dataStore = new LisOauthDataStore();
        $dataStore->setServiceLocator($this->getServiceLocatorMock([
            LtiProviderService::SERVICE_ID => $ltiProviderServiceMock
        ]));

        /** @noinspection PhpUnhandledExceptionInspection */
        $consumer = $dataStore->lookup_consumer('key1');
        $this->assertSame($ltiProviderMock, $consumer->getLtiProvider());
        $this->assertSame('clb_url', $consumer->callback_url);
    }

    public function testLookupConsumerNotFound()
    {
        /** @var LtiProviderService|MockObject $ltiProviderServiceMock */
        $ltiProviderServiceMock = $this->createMock(LtiProviderService::class);
        $ltiProviderServiceMock->expects($this->once())
            ->method('searchByOauthKey')
            ->with('key1')
            ->willReturn(null);

        $dataStore = new LisOauthDataStore();
        $dataStore->setServiceLocator($this->getServiceLocatorMock([
            LtiProviderService::SERVICE_ID => $ltiProviderServiceMock
        ]));

        $this->expectException(LtiOAuthException::class);
        /** @noinspection PhpUnhandledExceptionInspection */
        $dataStore->lookup_consumer('key1');
    }

    public function testLookupToken()
    {
        /** @var OAuthConsumer|MockObject $oauthConsumerMock */
        $oauthConsumerMock = $this->createMock(OAuthConsumer::class);
        $dataStore = new LisOauthDataStore();
        $token = $dataStore->lookup_token($oauthConsumerMock, 'access', 'tttoken');
        $this->assertInstanceOf(OAuthToken::class, $token);
    }

    public function testLookupNonce()
    {
        /** @var NoNonce|MockObject $nonceStoreMock */
        $nonceStoreMock = $this->createMock(NoNonce::class);
        $nonceStoreMock->expects($this->once())
            ->method('isValid')
            ->with('1234567_kkk1_nnnonnnce')
            ->willReturn(true);

        /** @var OAuthConsumer|MockObject $oauthConsumerMock */
        $oauthConsumerMock = $this->createMock(OAuthConsumer::class);
        $oauthConsumerMock->key = 'kkk1';

        $dataStore = new LisOauthDataStore();
        $dataStore->setOption(LisOauthDataStore::OPTION_NONCE_STORE, $nonceStoreMock);
        $dataStore->setServiceLocator($this->getServiceLocatorMock([
            LoggerService::SERVICE_ID => $this->createMock(LoggerService::class),
        ]));

        /** @noinspection PhpUnhandledExceptionInspection */
        $res = $dataStore->lookup_nonce($oauthConsumerMock, 'token', 'nnnonnnce', '1234567');
        $this->assertFalse($res);
    }
}
