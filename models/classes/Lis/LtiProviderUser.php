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

use common_user_User;
use oat\taoLti\models\classes\LtiProvider\LtiProvider;

/**
 * User authenticated by LisAuthAdapter
 */
class LtiProviderUser extends common_user_User
{
    public const PROPERTY_PROVIDER = 'property_provider';

    /**
     * @var LtiProvider
     */
    private $ltiProvider;

    /**
     * @param LtiProvider $ltiProvider
     */
    public function __construct(LtiProvider $ltiProvider)
    {
        $this->ltiProvider = $ltiProvider;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return 'ltiProvider_' . $this->ltiProvider->getId();
    }

    /**
     * @param string $property
     * @return LtiProvider[]
     */
    public function getPropertyValues($property)
    {
        if ($property === self::PROPERTY_PROVIDER) {
            return [$this->getLtiProvider()];
        }
        return [];
    }

    /**
     * @return bool
     */
    public function refresh()
    {
        return true;
    }

    /**
     * @return string[]
     */
    public function getRoles()
    {
        return [];
    }

    /**
     * @return LtiProvider
     */
    public function getLtiProvider()
    {
        return $this->ltiProvider;
    }
}
