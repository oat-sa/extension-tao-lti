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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\taoLti\models\classes\Lis;

use common_http_InvalidSignatureException;
use common_user_auth_Adapter;
use common_user_User;
use oat\tao\model\oauth\lockout\LockOutException;
use Psr\Http\Message\ServerRequestInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class LisAuthAdapter implements common_user_auth_Adapter, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /** @var ServerRequestInterface */
    protected $request;

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @return common_user_User|LtiProviderUser
     * @throws LisAuthAdapterException
     */
    public function authenticate()
    {
        $oauthService = $this->getLisOauthService();
        try {
            /** @var LisOAuthConsumer $oauthConsumer */
            [$oauthConsumer, $token] = $oauthService->validatePsrRequest($this->request);
        } catch (common_http_InvalidSignatureException | LockOutException $exception) {
            // to meet interface requirement
            throw new LisAuthAdapterException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }

        return new LtiProviderUser($oauthConsumer->getLtiProvider());
    }

    /**
     * @return LisOauthService
     */
    private function getLisOauthService()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(LisOauthService::SERVICE_ID);
    }
}
