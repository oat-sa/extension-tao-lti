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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

declare(strict_types=1);

namespace oat\taoLti\models\classes\Platform;

use OAT\Library\Lti1p3Core\Platform\PlatformInterface;

/**
 * LTI platform Value Object.
 */
class LtiPlatform implements PlatformInterface
{
    /** @var string */
    private $identifier;

    /** @var string */
    private $name;

    /** @var string */
    private $audience;

    /** @var string */
    private $oauth2AccessTokenUrl;

    /** @var string */
    private $oidcAuthenticationUrl;

    /** @var string */
    private $jwksUrl;

    /** @var string */
    private $clientId;

    /** @var string */
    private $deploymentId;

    public function __construct(
        string $identifier,
        string $name,
        string $audience,
        string $oauth2AccessTokenUrl,
        string $oidcAuthenticationUrl,
        string $jwksUrl,
        string $clientId,
        string $deploymentId
    ) {
        $this->identifier = $identifier;
        $this->name = $name;
        $this->audience = $audience;
        $this->oauth2AccessTokenUrl = $oauth2AccessTokenUrl;
        $this->oidcAuthenticationUrl = $oidcAuthenticationUrl;
        $this->jwksUrl = $jwksUrl;
        $this->clientId = $clientId;
        $this->deploymentId = $deploymentId;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAudience(): string
    {
        return $this->audience;
    }

    public function getOidcAuthenticationUrl(): string
    {
        return $this->oidcAuthenticationUrl;
    }

    public function getOAuth2AccessTokenUrl(): string
    {
        return $this->oauth2AccessTokenUrl;
    }

    public function getJwksUrl(): string
    {
        return $this->jwksUrl;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getDeploymentId(): string
    {
        return $this->deploymentId;
    }
}
