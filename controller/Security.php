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

use oat\tao\model\http\Controller;
use oat\tao\model\security\Business\Contract\JwksRepositoryInterface;
use oat\taoLti\models\classes\Platform\Service\Oidc\OidcLoginAuthenticatorInterface;
use oat\taoLti\models\classes\Platform\Service\Oidc\OidcLoginAuthenticatorProxy;
use oat\taoLti\models\classes\Security\DataAccess\Repository\CachedPlatformJwksRepository;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use function GuzzleHttp\Psr7\stream_for;

class Security extends Controller implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

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
}
