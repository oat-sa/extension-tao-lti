<?php

declare(strict_types=1);

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
 *
 */

namespace oat\taoLti\test\unit\models\classes\Platform;

use oat\generis\test\TestCase;
use oat\taoLti\models\classes\Platform\LtiPlatformRegistration;

class LtiPlatformTest extends TestCase
{
    /**
     * @dataProvider ltiDataProvider
     */
    public function testGetters(
        $id,
        $name,
        $audience,
        $oauth2AccessTokenUrl,
        $oidcAuthenticationUrl,
        $jwksUrl,
        $clientId,
        $deploymentId
    ): void {
        $subject = new LtiPlatformRegistration(
            $id,
            $name,
            $audience,
            $oauth2AccessTokenUrl,
            $oidcAuthenticationUrl,
            $jwksUrl,
            $clientId,
            $deploymentId
        );

        $this->assertEquals($id, $subject->getIdentifier());
        $this->assertEquals($name, $subject->getName());
        $this->assertEquals($clientId, $subject->getClientId());
        $this->assertEquals($deploymentId, $subject->getDeploymentId());
        $this->assertEquals($audience, $subject->getAudience());
        $this->assertEquals($oauth2AccessTokenUrl, $subject->getOAuth2AccessTokenUrl());
        $this->assertEquals($oidcAuthenticationUrl, $subject->getOidcAuthenticationUrl());
        $this->assertEquals($jwksUrl, $subject->getJwksUrl());
    }

    public function ltiDataProvider(): array
    {
        return [
            [
                'uid',
                'name',
                'audience',
                'http://oauth.aceess/token.url',
                'http://oidc.auth.url',
                'http://jwks.url',
                'client_id',
                'deployment_id'
            ],
            [
                '123',
                '',
                'audience',
                'http://oauth.aceess/token.url',
                'http://oidc.auth.url',
                'http://jwks.url',
                '',
                '',
            ],
        ];
    }
}
