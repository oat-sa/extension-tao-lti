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

use IMSGlobal\LTI\OAuth\OAuthConsumer;
use oat\taoLti\models\classes\LtiProvider\LtiProvider;

/**
 * Class compatible with
 * @see \IMSGlobal\LTI\OAuth\OAuthServer
 * Provides OAuth key and secret from LtiProvider
 */
class LisOAuthConsumer extends OAuthConsumer
{
    /**
     * @var LtiProvider
     */
    private $ltiProvider;

    public function __construct(LtiProvider $ltiProvider, $callback_url = null)
    {
        parent::__construct($ltiProvider->getKey(), $ltiProvider->getSecret(), $callback_url);
        $this->ltiProvider = $ltiProvider;
    }

    /**
     * @return LtiProvider
     */
    public function getLtiProvider()
    {
        return $this->ltiProvider;
    }
}
