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

/**
 * LTI platform Value Object.
 */
class LtiPlatform
{
    /** @var string */
    private $id;

    /** @var string */
    private $label;

    /** @var string */
    private $clientId;

    /** @var string */
    private $deploymentId;

    /** @var string */
    private $audience;

    /** @var string */
    private $ouath2AccessTokenUrl;

    /** @var string */
    private $oidcAuthenticationUrl;

    /** @var string */
    private $jwksUrl;

    public function __construct(
        string $id,
        string $label,
        string $clientId,
        string $deploymentId,
        string $audience,
        string $ouath2AccessTokenUrl,
        string $oidcAuthenticationUrl,
        string $jwksUrl
    ) {
        $this->id = $id;
        $this->label = $label;
        $this->clientId = $clientId;
        $this->deploymentId = $deploymentId;
        $this->audience = $audience;
        $this->ouath2AccessTokenUrl = $ouath2AccessTokenUrl;
        $this->oidcAuthenticationUrl = $oidcAuthenticationUrl;
        $this->jwksUrl = $jwksUrl;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getDeploymentId(): string
    {
        return $this->deploymentId;
    }

    public function getAudience(): string
    {
        return $this->audience;
    }

    public function getOuath2AccessTokenUrl(): string
    {
        return $this->ouath2AccessTokenUrl;
    }

    public function getOidcAuthenticationUrl(): string
    {
        return $this->oidcAuthenticationUrl;
    }

    public function getJwksUrl(): string
    {
        return $this->jwksUrl;
    }
}
