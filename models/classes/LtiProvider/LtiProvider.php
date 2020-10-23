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
 * Copyright (c) 2019-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

declare(strict_types=1);

namespace oat\taoLti\models\classes\LtiProvider;

use JsonSerializable;

/**
 * LTI provider business object.
 */
class LtiProvider implements JsonSerializable
{
    /** @var string */
    private $id;

    /** @var string */
    private $label;

    /** @var string */
    private $key;

    /** @var string */
    private $secret;

    /** @var string */
    private $callbackUrl;

    /** @var array */
    private $roles;

    /** @var string */
    private $ltiVersion;

    /** @var string */
    private $toolClientId;

    /** @var string */
    private $toolAudience;

    /** @var string[] */
    private $toolDeploymentIds = [];

    /** @var string */
    private $toolOidcLoginInitiationUrl;

    /** @var string */
    private $toolLaunchUrl;

    /** @var string */
    private $toolPublicKey;

    /** @var string */
    private $toolIdentifier;

    /** @var string */
    private $toolName;

    /** @var string */
    private $toolJwksUrl;

    public function __construct(
        string $id,
        string $label,
        string $key = null,
        string $secret = null,
        string $callbackUrl = null,
        array $roles = [],
        string $ltiVersion = null,
        string $toolIdentifier = null,
        string $toolName = null,
        string $toolClientId = null,
        array $toolDeploymentIds = [],
        string $toolAudience = null,
        string $toolOidcLoginInitiationUrl = null,
        string $toolLaunchUrl = null,
        string $toolPublicKey = null,
        string $toolJwksUrl = null
    ) {
        $this->id = $id;
        $this->label = $label;
        $this->key = $key;
        $this->secret = $secret;
        $this->callbackUrl = $callbackUrl;
        $this->roles = $roles;
        $this->ltiVersion = $ltiVersion;
        $this->toolClientId = $toolClientId;
        $this->toolDeploymentIds = $toolDeploymentIds;
        $this->toolAudience = $toolAudience;
        $this->toolOidcLoginInitiationUrl = $toolOidcLoginInitiationUrl;
        $this->toolLaunchUrl = $toolLaunchUrl;
        $this->toolPublicKey = $toolPublicKey;
        $this->toolIdentifier = $toolIdentifier;
        $this->toolName = $toolName;
        $this->toolJwksUrl = $toolJwksUrl;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function getCallbackUrl(): ?string
    {
        return $this->callbackUrl;
    }

    public function getLtiVersion(): string
    {
        return $this->ltiVersion;
    }

    public function getToolIdentifier(): string
    {
        return $this->toolIdentifier;
    }

    public function getToolName(): string
    {
        return $this->toolName;
    }

    public function getToolJwksUrl(): ?string
    {
        return $this->toolJwksUrl;
    }

    public function getToolClientId(): string
    {
        return $this->toolClientId;
    }

    public function getToolDeploymentIds(): array
    {
        return $this->toolDeploymentIds;
    }

    public function getToolAudience(): string
    {
        return $this->toolAudience;
    }

    public function getToolOidcLoginInitiationUrl(): string
    {
        return $this->toolOidcLoginInitiationUrl;
    }

    public function getToolLaunchUrl(): string
    {
        return $this->toolLaunchUrl;
    }

    public function getToolPublicKey(): ?string
    {
        return $this->toolPublicKey;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'uri' => $this->getId(),
            'text' => $this->getLabel(),
            'key' => $this->getKey(),
            'secret' => $this->getSecret(),
            'callback' => $this->getCallbackUrl(),
            'roles' => $this->getRoles(),
        ];
    }

    public function getRoles(): array
    {
        return $this->roles;
    }
}
