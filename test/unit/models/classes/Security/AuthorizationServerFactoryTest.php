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

use League\OAuth2\Server\CryptKey;
use oat\generis\test\TestCase;
use OAT\Library\Lti1p3Core\Security\Jwks\Fetcher\JwksFetcher;
use OAT\Library\Lti1p3Core\Security\OAuth2\Grant\ClientAssertionCredentialsGrant;
use OAT\Library\Lti1p3Core\Security\OAuth2\Repository\AccessTokenRepository;
use OAT\Library\Lti1p3Core\Security\OAuth2\Repository\ClientRepository;
use OAT\Library\Lti1p3Core\Security\OAuth2\Repository\ScopeRepository;
use OAT\Library\Lti1p3Core\Security\OAuth2\ResponseType\ScopedBearerTokenResponse;
use oat\oatbox\cache\ItemPoolSimpleCacheAdapter;
use oat\oatbox\log\LoggerService;
use oat\tao\model\security\Business\Domain\Key\Key;
use oat\tao\model\security\Business\Domain\Key\KeyChain;
use oat\tao\test\unit\helpers\NoPrivacyTrait;
use oat\taoLti\models\classes\Platform\Repository\Lti1p3RegistrationRepository;
use oat\taoLti\models\classes\Security\AuthorizationServer\AuthorizationServerFactory;

class AuthorizationServerFactoryTest extends TestCase
{
    use NoPrivacyTrait;

    private $subject;

    private $registrationRepository;

    private $accessTokenRepository;

    private $cache;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|string
     */
    private $logger;

    protected function setUp(): void
    {
        $this->subject = new AuthorizationServerFactory([
            AuthorizationServerFactory::OPTION_ENCRYPTION_KEY => 'toto',
        ]);

        $this->registrationRepository = $this->createMock(Lti1p3RegistrationRepository::class);
        $this->accessTokenRepository = $this->createMock(accessTokenRepository::class);
        $this->cache = $this->createMock(ItemPoolSimpleCacheAdapter::class);
        $this->logger = $this->createMock(LoggerService::class);

        $this->subject->setServiceLocator($this->getServiceLocatorMock([
            Lti1p3RegistrationRepository::class => $this->registrationRepository,
            ItemPoolSimpleCacheAdapter::class => $this->cache,
            LoggerService::SERVICE_ID => $this->logger,
        ]));
    }

    public function testCreate()
    {
        $keyChainPrivateKey = '-----BEGIN RSA PRIVATE KEY-----
ABC-----END RSA PRIVATE KEY-----';

        $keyChain = new KeyChain(
            'toto',
            'toto',
            new Key('toto-public'),
            new Key($keyChainPrivateKey)
        );

        $authorizationServer = $this->subject->create($keyChain);

        $clientRepository = $this->getPrivateProperty($authorizationServer, 'clientRepository');
        $this->assertInstanceOf(ClientRepository::class, $clientRepository);
        $this->assertSame(
            $this->registrationRepository,
            $this->getPrivateProperty($clientRepository, 'repository')
        );
        $fetcher = $this->getPrivateProperty($clientRepository, 'fetcher');
        $this->assertInstanceOf(JwksFetcher::class, $fetcher);
        $this->assertSame($this->cache, $this->getPrivateProperty($fetcher, 'cache'));
        $this->assertSame($this->logger, $this->getPrivateProperty($fetcher, 'logger'));

        $accessTokenRepository = $this->getPrivateProperty($authorizationServer, 'accessTokenRepository');
        $this->assertInstanceOf(AccessTokenRepository::class, $accessTokenRepository);
        $this->assertSame(
            $this->cache,
            $this->getPrivateProperty($accessTokenRepository, 'cache')
        );
        $this->assertSame(
            $this->logger,
            $this->getPrivateProperty($accessTokenRepository, 'logger')
        );

        $accessTokenRepository = $this->getPrivateProperty($authorizationServer, 'accessTokenRepository');
        $this->assertInstanceOf(AccessTokenRepository::class, $accessTokenRepository);
        $this->assertSame(
            $this->cache,
            $this->getPrivateProperty($accessTokenRepository, 'cache')
        );
        $this->assertSame(
            $this->logger,
            $this->getPrivateProperty($accessTokenRepository, 'logger')
        );

        $scopeRepository = $this->getPrivateProperty($authorizationServer, 'scopeRepository');
        $this->assertInstanceOf(ScopeRepository::class, $scopeRepository);
        $scopes = $this->getPrivateProperty($scopeRepository, 'scopes');
        $this->assertArrayHasKey('https://purl.imsglobal.org/spec/lti-bo/scope/basicoutcome', $scopes);

        $privateKey = $this->getPrivateProperty($authorizationServer, 'privateKey');
        $this->assertInstanceOf(CryptKey::class, $privateKey);
        $this->assertSame($keyChainPrivateKey, file_get_contents($privateKey->getKeyPath()));

        $this->assertSame(
            'toto',
            $this->getPrivateProperty($authorizationServer, 'encryptionKey')
        );

        $this->assertInstanceOf(
            ScopedBearerTokenResponse::class,
            $this->getPrivateProperty($authorizationServer, 'responseType')
        );

        $this->assertArrayHasKey(
            ClientAssertionCredentialsGrant::GRANT_IDENTIFIER,
            $this->getPrivateProperty($authorizationServer, 'enabledGrantTypes')
        );
    }
}
