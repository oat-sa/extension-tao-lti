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

class LtiLaunch implements LtiLaunchInterface
{
    /** @var string */
    private $launchUrl;

    /** @var array */
    private $launchParams;

    public function __construct(
        string $launchUrl,
        array $launchParams
    )
    {
        $this->launchUrl = $launchUrl;
        $this->launchParams = $launchParams;
    }

    public function getToolLaunchUrl(): string
    {
        return $this->launchUrl;
    }

    public function getToolLaunchParams(): array
    {
        return $this->launchParams;
    }

    public function getToolLaunchUrlWithParams(): string
    {
        return sprintf('%s?%s', $this->launchUrl, http_build_query(array_filter($this->launchParams)));
    }
}
