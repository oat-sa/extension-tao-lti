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

namespace oat\taoLti\models\classes\Security\AuthorizationServer;

use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use \OAT\Library\Lti1p3Core\Security\OAuth2\Factory\AuthorizationServerFactory as LibraryAuthorizationServerFactory;
use OAT\Library\Lti1p3Core\Security\Jwks\Fetcher\JwksFetcher;
use OAT\Library\Lti1p3Core\Security\OAuth2\Entity\Scope;
use OAT\Library\Lti1p3Core\Security\OAuth2\Repository\AccessTokenRepository;
use OAT\Library\Lti1p3Core\Security\OAuth2\Repository\ClientRepository;
use OAT\Library\Lti1p3Core\Security\OAuth2\Repository\ScopeRepository;
use oat\oatbox\cache\ItemPoolSimpleCacheAdapter;
use oat\oatbox\service\ConfigurableService;
use oat\taoLti\models\classes\Platform\Repository\Lti1p3RegistrationRepository;
use Psr\Cache\CacheItemPoolInterface;

class AuthorizationServerFactory extends ConfigurableService
{
    public const SERVICE_ID = 'taoLti/AuthorizationServerFactory';
    public const OPTION_ENCRYPTION_KEY = 'encryptionKey';

    /** @var LibraryAuthorizationServerFactory */
    private $implementation = null;

    // it is needed only for configurable encryption key, maybe there is another way to do it and avoid having this class
    public function getImplementation(): LibraryAuthorizationServerFactory
    {
        if (null === $this->implementation) {
            $this->implementation = new LibraryAuthorizationServerFactory(
                $this->getClientRepository(),
                $this->getAccessTokenRepository(),
                $this->getScopeRepository(),
                $this->getOption(self::OPTION_ENCRYPTION_KEY)
            );
        }

        return $this->implementation;
    }

    private function getRegistrationRepository(): Lti1p3RegistrationRepository
    {
        return $this->getServiceLocator()->get(Lti1p3RegistrationRepository::class);
    }

    private function getCacheItemPool(): CacheItemPoolInterface
    {
        return $this->getServiceLocator()->get(ItemPoolSimpleCacheAdapter::class);
    }

    private function getClientRepository(): ClientRepositoryInterface
    {
        return new ClientRepository(
            $this->getRegistrationRepository(),
            $this->getJwksFetcher(),
            $this->getLogger()
        );
    }

    private function getJwksFetcher(): JwksFetcher
    {
        return new JwksFetcher(
            $this->getCacheItemPool(),
            null,
            null,
            $this->getLogger()
        );
    }

    private function getAccessTokenRepository(): AccessTokenRepositoryInterface
    {
        return new AccessTokenRepository(
            $this->getCacheItemPool(),
            $this->getLogger()
        );
    }

    private function getScopeRepository(): ScopeRepositoryInterface
    {
        return new ScopeRepository(
            [
                new Scope('https://purl.imsglobal.org/spec/lti-bo/scope/basicoutcome'),
            ]
        );
    }
}
