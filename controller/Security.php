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
use OAT\Library\Lti1p3Core\Registration\RegistrationRepositoryInterface;
use OAT\Library\Lti1p3Core\Security\Key\KeyChainRepositoryInterface;
use OAT\Library\Lti1p3Core\Security\OAuth2\Generator\AccessTokenResponseGenerator;
use OAT\Library\Lti1p3Core\Security\OAuth2\Generator\AccessTokenResponseGeneratorInterface;
use OAT\Library\Lti1p3Core\Security\Oidc\OidcInitiator;
use oat\tao\model\http\Controller;
use oat\tao\model\security\Business\Contract\JwksRepositoryInterface;
use oat\taoLti\models\classes\Platform\Repository\Lti1p3RegistrationRepository;
use oat\taoLti\models\classes\Platform\Service\Oidc\OidcLoginAuthenticatorInterface;
use oat\taoLti\models\classes\Platform\Service\Oidc\OidcLoginAuthenticatorProxy;

use oat\taoLti\models\classes\Security\AuthorizationServer\AuthorizationServerFactory;
use oat\taoLti\models\classes\Security\DataAccess\Repository\CachedPlatformJwksRepository;
use oat\taoLti\models\classes\Security\DataAccess\Repository\CachedPlatformKeyChainRepository;
use oat\taoLti\models\classes\Security\DataAccess\Repository\PlatformKeyChainRepository;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use function GuzzleHttp\Psr7\stream_for;

class Security extends Controller implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function oauth(): void
    {
        try {
            $this->setResponse(
                $this->getAccessTokenGenerator()->generate(
                    $this->getPsrRequest(),
                    $this->getPsrResponse(),
                    $this->getPlatformKeyChainRepository()->getDefaultKeyId()
                )
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

    public function oidcInitiation(): void
    {
        // Create the OIDC initiator
        $initiator = new OidcInitiator($this->getLti1p3RegistrationRepository());

        // Perform the OIDC initiation (including state generation)
        $message = $initiator->initiate($this->getPsrRequest());

        $this->redirect($message->toUrl());
    }

    private function getKeyChainRepository(): KeyChainRepositoryInterface
    {
        return $this->getServiceLocator()->get(CachedPlatformKeyChainRepository::class);
    }

    private function getPlatformKeyChainRepository(): PlatformKeyChainRepository
    {
        return $this->getServiceLocator()->get(PlatformKeyChainRepository::SERVICE_ID);
    }

    private function getAuthorizationServerFactory(): AuthorizationServerFactory
    {
        return $this->getServiceLocator()->get(AuthorizationServerFactory::class);
    }

    private function getJwksRepository(): JwksRepositoryInterface
    {
        return $this->getServiceLocator()->get(CachedPlatformJwksRepository::class);
    }

    private function getOidcLoginAuthenticator(): OidcLoginAuthenticatorInterface
    {
        return $this->getServiceLocator()->get(OidcLoginAuthenticatorProxy::class);
    }

    private function getLti1p3RegistrationRepository(): RegistrationRepositoryInterface
    {
        return $this->getServiceLocator()->get(Lti1p3RegistrationRepository::class);
    }

    private function getAccessTokenGenerator(): AccessTokenResponseGeneratorInterface
    {
        return new AccessTokenResponseGenerator(
            $this->getKeyChainRepository(),
            $this->getAuthorizationServerFactory()->getImplementation()
        );
    }
}
