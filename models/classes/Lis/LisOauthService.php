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

use common_exception_NoImplementation;
use common_http_Credentials;
use common_http_Request;
use oat\tao\model\oauth\OauthService;

/**
 * This class with own SERVICE_ID allows us to register in separately from oat\tao\model\oauth\OauthService with
 * own config values. Used only to utilize existing validation logic at the moment
 */
class LisOauthService extends OauthService
{
    public const SERVICE_ID = 'taoLti/LisOauthService';

    /**
     * @inheritDoc
     * @throws common_exception_NoImplementation
     */
    public function sign(common_http_Request $request, common_http_Credentials $credentials, $authorizationHeader = false)
    {
        throw new common_exception_NoImplementation('Signing not implemented');
    }

    /**
     * Perform a real check of body hash
     * @inheritDoc
     */
    protected function validateBodyHash($body, $bodyHash)
    {
        // No need to perform time insensitive comparison here, because possible
        // attacker already has both bodyHash and body
        return $this->calculateOauthBodyHash($body) === $bodyHash;
    }
}
