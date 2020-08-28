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

namespace oat\taoLti\models\classes\Platform\Service;

use OAT\Library\Lti1p3Core\Security\Key\KeyChain;
use OAT\Library\Lti1p3Core\Security\Key\KeyChainRepository;
use OAT\Library\Lti1p3Core\Service\Server\Entity\Scope;
use OAT\Library\Lti1p3Core\Service\Server\Factory\AuthorizationServerFactory as Lti1p3AuthorizationServerFactory;
use OAT\Library\Lti1p3Core\Service\Server\Generator\AccessTokenResponseGenerator;
use OAT\Library\Lti1p3Core\Service\Server\Repository\AccessTokenRepository;
use OAT\Library\Lti1p3Core\Service\Server\Repository\ClientRepository;
use OAT\Library\Lti1p3Core\Service\Server\Repository\ScopeRepository;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\security\Business\Contract\KeyChainRepositoryInterface;
use oat\tao\model\security\Business\Domain\Key\KeyChainQuery;
use oat\taoLti\models\classes\Cache\CacheItemPool;
use oat\taoLti\models\classes\Platform\Repository\Lti1p3RegistrationRepository;
use oat\taoLti\models\classes\Security\DataAccess\Repository\CachedPlatformKeyChainRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AccessTokenGeneratorService extends ConfigurableService implements AccessTokenGeneratorInterface
{
    public function generate(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        //Hack behaviour we should search for  tool
        $platformKeyChain = $this->getTranslatedPlatformKeyChain();
        $repository = new KeyChainRepository([$platformKeyChain]);

        $generator = new AccessTokenResponseGenerator(
            $repository,
            $this->getAuthorizationServerFactory()
        );

        return $generator->generate($request, $response, 'defaultPlatformKeyId');
    }

    private function getAuthorizationServerFactory(): Lti1p3AuthorizationServerFactory
    {
        return new Lti1p3AuthorizationServerFactory(
            new ClientRepository(
                $this->getRegistrationRepository()
            ),
            new AccessTokenRepository(
                $this->getCacheItemPool()
            ),
            new ScopeRepository(
                [
                    new Scope('https://purl.imsglobal.org/spec/lti-bo/scope/basicoutcome'),
                ]
            ),
            'superSecretEncryptionKey' // TODO: You obviously have to add more entropy, this is an example
        );
    }

    private function getTranslatedPlatformKeyChain(): KeyChain
    {
        $keyChainCollection = $this->getPlatformKeyChainRepository()->findAll(
            new KeyChainQuery('defaultPlatformKeyId')
        );

        $keyChains = $keyChainCollection->getKeyChains();
        $keyChain = reset($keyChains);

        return new KeyChain(
            'defaultPlatformKeyId',
            'myToolKeySetName',
            $keyChain->getPublicKey()->getValue(),
            $keyChain->getPrivateKey()->getValue()
        );
    }

    private function getRegistrationRepository(): Lti1p3RegistrationRepository
    {
        return $this->getServiceLocator()->get(Lti1p3RegistrationRepository::class);
    }

    private function getCacheItemPool(): CacheItemPool
    {
        return $this->getServiceLocator()->get(CacheItemPool::class);
    }

    private function getPlatformKeyChainRepository(): KeyChainRepositoryInterface
    {
        return $this->getServiceLocator()->get(CachedPlatformKeyChainRepository::class);
    }
}
