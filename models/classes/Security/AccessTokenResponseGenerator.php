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

namespace oat\taoLti\models\classes\Security;

use League\OAuth2\Server\Exception\OAuthServerException;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\security\Business\Contract\KeyChainRepositoryInterface;
use oat\tao\model\security\Business\Domain\Key\KeyChainQuery;
use oat\taoLti\models\classes\Security\AuthorizationServer\AuthorizationServerFactory;
use oat\taoLti\models\classes\Security\DataAccess\Repository\CachedPlatformKeyChainRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AccessTokenResponseGenerator extends ConfigurableService implements AccessTokenResponseGeneratorInterface
{
    /**
     * @throws \League\OAuth2\Server\Exception\OAuthServerException
     */
    public function generate(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $query = new KeyChainQuery();

        $keyChainCollection = $this->getKeyChainRepository()
            ->findAll($query)
            ->getKeyChains();

        $keyChain = reset($keyChainCollection);

        if (false === $keyChain) {
            throw new OAuthServerException(
                'Invalid key chain identifier',
                11,
                'key_chain_not_found',
                404
            );
        }

        return $this->getAuthorizationServerFactory()
            ->create($keyChain)
            ->respondToAccessTokenRequest($request, $response);
    }

    private function getKeyChainRepository(): KeyChainRepositoryInterface
    {
        return $this->getServiceLocator()->get(CachedPlatformKeyChainRepository::class);
    }

    private function getAuthorizationServerFactory(): AuthorizationServerFactory
    {
        return $this->getServiceLocator()->get(AuthorizationServerFactory::class);
    }
}
