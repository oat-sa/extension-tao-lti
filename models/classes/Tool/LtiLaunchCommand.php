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

declare(strict_types=1);

namespace oat\taoLti\models\classes\Tool;

use oat\oatbox\user\User;
use oat\taoLti\models\classes\LtiProvider\LtiProvider;

class LtiLaunchCommand implements LtiLaunchCommandInterface
{
    /** @var LtiProvider */
    private $ltiProvider;

    /** @var User */
    private $user;

    /** @var bool */
    private $openIdLoginHint;

    /** @var array */
    private $roles;

    /** @var array */
    private $claims;

    /** @var string */
    private $resourceIdentifier;

    /** @var string */
    private $launchUrl;

    public function __construct(
        LtiProvider $ltiProvider,
        array $roles,
        array $claims,
        string $resourceIdentifier,
        User $user = null,
        string $openIdLoginHint = null,
        string $launchUrl = null
    )
    {
        $this->ltiProvider = $ltiProvider;
        $this->roles = $roles;
        $this->claims = $claims;
        $this->resourceIdentifier = $resourceIdentifier;
        $this->user = $user;
        $this->openIdLoginHint = $openIdLoginHint;
        $this->launchUrl = $launchUrl;
    }

    public function getLtiProvider(): LtiProvider
    {
        return $this->ltiProvider;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getClaims(): array
    {
        return $this->claims;
    }

    public function getResourceIdentifier(): string
    {
        return $this->resourceIdentifier;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getOpenIdLoginHint(): ?string
    {
        return $this->openIdLoginHint;
    }

    public function getLaunchUrl(): ?string
    {
        return $this->launchUrl;
    }

    public function isAnonymousLaunch(): bool
    {
        return $this->user === null;
    }

    public function isOpenIdConnectLaunch(): bool
    {
        return $this->openIdLoginHint === null;
    }
}
