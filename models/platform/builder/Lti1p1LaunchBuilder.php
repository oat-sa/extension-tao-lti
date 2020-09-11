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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA
 */

namespace oat\taoLti\models\platform\builder;

use IMSGlobal\LTI\ToolProvider\ToolConsumer;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\user\User;
use oat\taoLti\models\classes\LtiLaunchData;
use oat\taoLti\models\tool\launch\LtiLaunch;
use oat\taoLti\models\tool\launch\LtiLaunchInterface;
use oat\taoLti\models\classes\LtiProvider\LtiProvider;

class Lti1p1LaunchBuilder extends ConfigurableService implements LtiLaunchBuilderInterface
{
    /** @var LtiProvider */
    private $ltiProvider;

    /** @var User */
    private $user;

    /** @var bool */
    private $openIdConnectLoginHint;

    /** @var array */
    private $roles;

    /** @var array */
    private $claims;

    /** @var string */
    private $launchUrl;

    public function withProvider(LtiProvider $ltiProvider): LtiLaunchBuilderInterface
    {
        $this->ltiProvider = $ltiProvider;

        return $this;
    }

    public function withUser(User $user): LtiLaunchBuilderInterface
    {
        $this->user = $user;

        return $this;
    }

    public function withOpenIdConnect(string $loginHint): LtiLaunchBuilderInterface
    {
        $this->openIdConnectLoginHint = $loginHint;

        return $this;
    }

    public function withRoles(array $roles): LtiLaunchBuilderInterface
    {
        $this->roles = $roles;

        return $this;
    }

    public function withClaims(array $claims): LtiLaunchBuilderInterface
    {
        $this->claims = $claims;

        return $this;
    }

    public function withLaunchUrl(string $launchUrl): LtiLaunchBuilderInterface
    {
        $this->launchUrl = $launchUrl;

        return $this;
    }

    public function build(): LtiLaunchInterface
    {
        $data = array_merge(
            $this->claims,
            [
                LtiLaunchData::LTI_VERSION => 'LTI-1p0',
                LtiLaunchData::USER_ID => $this->user->getIdentifier(),
                LtiLaunchData::ROLES => current($this->roles),
            ]
        );

        $data = ToolConsumer::addSignature(
            $this->launchUrl, //@TODO The launch URL must come from Provider configuration instead
            $this->ltiProvider->getKey(),
            $this->ltiProvider->getSecret(),
            $data
        );

        return new LtiLaunch($this->launchUrl, $data);
    }
}
