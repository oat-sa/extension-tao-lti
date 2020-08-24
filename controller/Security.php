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

namespace oat\taoLti\controller;

use League\OAuth2\Server\Exception\OAuthServerException;
use OAT\Library\Lti1p3Core\Security\Key\KeyChain;
use OAT\Library\Lti1p3Core\Security\Key\KeyChainRepository;
use OAT\Library\Lti1p3Core\Service\Server\Factory\AuthorizationServerFactory;
use OAT\Library\Lti1p3Core\Service\Server\Generator\AccessTokenResponseGenerator;
use OAT\Library\Lti1p3Core\Service\Server\Repository\AccessTokenRepository;
use OAT\Library\Lti1p3Core\Service\Server\Repository\ClientRepository;
use OAT\Library\Lti1p3Core\Service\Server\Repository\ScopeRepository;
use oat\tao\model\http\Controller;
use oat\tao\model\security\Business\Contract\JwksRepositoryInterface;
use oat\tao\model\security\Business\Domain\Key\KeyChain as OatKeyChain;
use oat\tao\model\security\Business\Domain\Key\KeyChainCollection;
use oat\tao\model\security\Business\Domain\Key\KeyChainQuery;
use oat\taoLti\models\classes\Cache\CacheItemPool;
use oat\taoLti\models\classes\Platform\Repository\Lti1p3RegistrationRepository;
use oat\taoLti\models\classes\Platform\Service\CachedKeyChainGenerator;
use oat\taoLti\models\classes\Platform\Service\KeyChainGeneratorInterface;
use oat\taoLti\models\classes\Platform\Service\Oidc\OidcLoginAuthenticatorInterface;
use oat\taoLti\models\classes\Platform\Service\Oidc\OidcLoginAuthenticatorProxy;
use oat\taoLti\models\classes\Security\DataAccess\Repository\CachedPlatformJwksRepository;
use oat\taoLti\models\classes\Security\DataAccess\Repository\CachedPlatformKeyChainRepository;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use function GuzzleHttp\Psr7\stream_for;

class Security extends Controller implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function oauth(): void
    {
        $adaptedKeyChainCollection = [];
        $factory = new AuthorizationServerFactory(
            new ClientRepository(
                $this->getRegistrationRepository()
            ),
            new AccessTokenRepository(
                $this->getCacheItemPool()
            ),
            new ScopeRepository(),
            'superSecretEncryptionKey' // TODO: You obviously have to add more entropy, this is an example
        );

        $platformRepository = $this->getPlatformKeyChainRepository();

        $keyChainCollection = $platformRepository->findAll(
            new KeyChainQuery('defaultPlatformKeyId')
        );

        if (count($keyChainCollection->getKeyChains()) === 0) {
            $keyChainCollection = new KeyChainCollection(
                [
                    $this->getKeyChainGenerator()->generate(),
                ]
            );
        }

        /** @var OatKeyChain $keyChain */
        foreach ($keyChainCollection->getKeyChains() as $keyChain) {
            $adaptedKeyChainCollection[] = new KeyChain(
                $keyChain->getIdentifier(),
                $keyChain->getName(),
                $keyChain->getPublicKey()->getValue(),
                $keyChain->getPrivateKey()->getValue()
            );
        }

        $repository = new KeyChainRepository($adaptedKeyChainCollection);

        $generator = new AccessTokenResponseGenerator($repository, $factory);

        try {
            // Extract keyChainIdentifier from request uri parameter
            $keyChainIdentifier = 'defaultPlatformKeyId';

            // Validate assertion, generate and sign access token response, using the key chain private key
            $this->setResponse(
                $generator->generate($this->getPsrRequest(), $this->getPsrResponse(), $keyChainIdentifier)
            );

        } catch (OAuthServerException $exception) {
            $this->setResponse($exception->generateHttpResponse($this->getPsrResponse()));
        }
    }

    public function jwks(): void
    {
        $response = $this->getPsrResponse()
            ->withHeader('ContentType', 'application/json')
            ->withBody(stream_for(json_encode($this->getJwksRepository()->find())));

        $this->setResponse($response);
    }

    public function oidc(): void
    {
        $response = $this->getOidcLoginAuthenticator()
            ->authenticate($this->getPsrRequest(), $this->getPsrResponse());

        $this->setResponse($response);
    }

    private function getJwksRepository(): JwksRepositoryInterface
    {
        return $this->getServiceLocator()->get(CachedPlatformJwksRepository::class);
    }

    private function getOidcLoginAuthenticator(): OidcLoginAuthenticatorInterface
    {
        return $this->getServiceLocator()->get(OidcLoginAuthenticatorProxy::class);
    }

    private function getRegistrationRepository(): Lti1p3RegistrationRepository
    {
        return $this->getServiceLocator()->get(Lti1p3RegistrationRepository::class);
    }

    private function getCacheItemPool(): CacheItemPool
    {
        return $this->getServiceLocator()->get(CacheItemPool::class);
    }

    private function getPlatformKeyChainRepository(): CachedPlatformKeyChainRepository
    {
        return $this->getServiceLocator()->get(CachedPlatformKeyChainRepository::class);
    }

    private function getKeyChainGenerator(): KeyChainGeneratorInterface
    {
        return $this->getServiceLocator()->get(CachedKeyChainGenerator::class);
    }
}
