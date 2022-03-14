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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA;
 *
 * @author Ricardo Quintanilha <ricardo.quintanilha@taotesting.com>
 */

declare(strict_types=1);

namespace oat\taoLti\models\classes\Platform\Repository;

use OAT\Library\Lti1p3Core\Tool\Tool;

final class DefaultToolConfig
{
    private const TOOL_ID = 'tao_tool';
    private const OIDC_PATH = 'taoLti/Security/oidc';
    private const JWKS_PATH = 'taoLti/Security/jwks';

    /** @var string */
    private $baseUri;

    public function __construct(string $baseUri)
    {
        $this->baseUri = $baseUri;
    }

    public function getTool(): Tool
    {
        return new Tool(
            self::TOOL_ID,
            self::TOOL_ID,
            rtrim($this->baseUri, '/'),
            $this->baseUri . self::OIDC_PATH
        );
    }

    public function getJwksUrl(): string
    {
        return $this->baseUri . self::JWKS_PATH;
    }
}
