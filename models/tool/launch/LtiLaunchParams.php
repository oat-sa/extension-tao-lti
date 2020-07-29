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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA
 */

namespace oat\taoLti\models\tool\launch;

class LtiLaunchParams
{
    /** @var string */
    private $providerId;

    /** @var string */
    private $launchUrl;

    /** @var string */
    private $resourceLinkId;

    public function __construct(string $providerId, string $launchUrl, string $resourceLinkId)
    {
        $this->providerId = $providerId;
        $this->launchUrl = $launchUrl;
        $this->resourceLinkId = $resourceLinkId;
    }

    public function getProviderId(): string
    {
        return $this->providerId;
    }

    public function getLaunchUrl(): string
    {
        return $this->launchUrl;
    }

    public function getResourceLinkId(): string
    {
        return $this->resourceLinkId;
    }
}
