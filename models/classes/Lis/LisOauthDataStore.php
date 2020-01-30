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

namespace oat\taoLti\models\classes\Lis;

use IMSGlobal\LTI\OAuth\OAuthException as LtiOAuthException;
use IMSGlobal\LTI\OAuth\OAuthToken;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\service\exception\InvalidService;
use oat\oatbox\service\exception\InvalidServiceManagerException;
use oat\tao\model\oauth\ImsOauthDataStoreInterface;
use oat\tao\model\oauth\nonce\NonceStore;
use oat\taoLti\models\classes\LtiProvider\LtiProviderService;

/**
 * Implementation compatible with
 * @see \IMSGlobal\LTI\OAuth\OAuthDataStore
 * to be used in
 * @see \IMSGlobal\LTI\OAuth\OAuthServer
 * Retrieves consumers from LtiProviderService
 */
class LisOauthDataStore extends ConfigurableService implements ImsOauthDataStoreInterface
{
    public const OPTION_NONCE_STORE = 'nonce_store';

    /**
     * @inheritDoc
     * @throws LtiOAuthException
     */
    public function lookup_consumer($consumer_key)
    {
        $provider = $this->getLtiProviderService()->searchByOauthKey($consumer_key);
        if ($provider === null) {
            throw new LtiOAuthException('LTI provider with given consumer key not found');
        }
        return new LisOAuthConsumer($provider, $provider->getCallbackUrl());
    }

    /**
     * @inheritDoc
     */
    public function lookup_token($consumer, $token_type, $token)
    {
        return new OAuthToken($consumer, '');
    }

    /**
     * @inheritDoc
     * @throws InvalidService
     * @throws InvalidServiceManagerException
     */
    public function lookup_nonce($consumer, $token, $nonce, $timestamp)
    {
        /** @var NonceStore $store */
        $store = $this->getSubService(self::OPTION_NONCE_STORE, NonceStore::class);
        return !$store->isValid($timestamp . '_' . $consumer->key . '_' . $nonce);
    }

    /**
     * @inheritDoc
     */
    public function new_request_token($consumer, $callback = null)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function new_access_token($token, $consumer, $verifier = null)
    {
        return null;
    }

    /**
     * @return LtiProviderService
     */
    private function getLtiProviderService()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(LtiProviderService::SERVICE_ID);
    }
}
