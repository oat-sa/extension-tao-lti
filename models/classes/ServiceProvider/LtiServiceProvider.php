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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoLti\models\classes\ServiceProvider;

use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use Monolog\Logger;
use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use OAT\Library\Lti1p3Core\Security\Jwks\Fetcher\JwksFetcher;
use OAT\Library\Lti1p3Core\Security\Jwks\Fetcher\JwksFetcherInterface;
use OAT\Library\Lti1p3Core\Security\OAuth2\Entity\Scope;
use OAT\Library\Lti1p3Core\Security\OAuth2\Factory\AuthorizationServerFactory;
use OAT\Library\Lti1p3Core\Security\OAuth2\Repository\AccessTokenRepository;
use OAT\Library\Lti1p3Core\Security\OAuth2\Repository\ClientRepository;
use OAT\Library\Lti1p3Core\Security\OAuth2\Repository\ScopeRepository;
use oat\oatbox\cache\ItemPoolSimpleCacheAdapter;
use oat\taoLti\models\classes\Platform\Repository\Lti1p3RegistrationRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

class LtiServiceProvider implements ContainerServiceProviderInterface
{
    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();
        $parameters = $configurator->parameters();

        /** @todo how to get logger? */
        $services
            ->set(LoggerInterface::class, Logger::class)
            ->public()
            ->args(
                [
                    'general'
                ]
            );

        $services
            ->set(JwksFetcherInterface::class, JwksFetcher::class)
            ->public()
            ->args(
                [
                    service(ItemPoolSimpleCacheAdapter::class),
                    null,
                    null,
                    service(LoggerInterface::class)
                ]
            );

        $services
            ->set(ClientRepositoryInterface::class, ClientRepository::class)
            ->public()
            ->args(
                [
                    service(Lti1p3RegistrationRepository::class),
                    service(JwksFetcherInterface::class),
                    service(LoggerInterface::class)
                ]
            );

        $services
            ->set(AccessTokenRepositoryInterface::class, AccessTokenRepository::class)
            ->public()
            ->args(
                [
                    service(ItemPoolSimpleCacheAdapter::class),
                    service(LoggerInterface::class)
                ]
            );

        $services
            ->set(ScopeEntityInterface::class, Scope::class)
            ->public()
            ->args(
                [
                    'https://purl.imsglobal.org/spec/lti-bo/scope/basicoutcome'
                ]
            );

        $services
            ->set(ScopeRepositoryInterface::class, ScopeRepository::class)
            ->public()
            ->args(
                [
                    [service(ScopeEntityInterface::class)]
                ]
            );

        $services
            ->set(AuthorizationServerFactory::class, AuthorizationServerFactory::class)
            ->public()
            ->args(
                [
                    service(ClientRepositoryInterface::class),
                    service(AccessTokenRepositoryInterface::class),
                    service(ScopeRepositoryInterface::class),
                    env('LTI_AUTHORIZATION_SERVER_FACTORY_ENCRYPTION_KEY')
                ]
            );
    }
}
