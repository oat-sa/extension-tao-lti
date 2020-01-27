<?php

declare(strict_types=1);

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

use common_http_InvalidSignatureException;
use IMSGlobal\LTI\OAuth\OAuthConsumer;
use IMSGlobal\LTI\OAuth\OAuthRequest;
use IMSGlobal\LTI\OAuth\OAuthSignatureMethod_HMAC_SHA1;

class LisSignatureValidator
{
    /**
     * @param OAuthRequest  $oauthRequest
     * @param OAuthConsumer $oauthConsumer
     * @param string        $method
     * @param string        $url
     *
     * @return array
     * @throws common_http_InvalidSignatureException
     */
    public function validate(OAuthRequest $oauthRequest, OAuthConsumer $oauthConsumer, $method, $url)
    {
        $hmacMethod = new OAuthSignatureMethod_HMAC_SHA1();

        $oauthReq = OAuthRequest::from_consumer_and_token(
            $oauthConsumer,
            null,
            $method,
            $url,
            $oauthRequest->get_parameters()
        );

        $oauthReq->sign_request($hmacMethod, $oauthConsumer, null);

        if ($oauthRequest->get_parameter('oauth_signature') !== $oauthReq->get_parameter('oauth_signature')) {
            throw new common_http_InvalidSignatureException('Invalid signature.');
        }

        return $oauthReq->get_parameters();
    }
}
