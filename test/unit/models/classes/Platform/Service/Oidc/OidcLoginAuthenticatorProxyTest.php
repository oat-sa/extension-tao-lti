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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoLti\test\unit\models\classes\Platform\Service\Oidc;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use oat\generis\test\TestCase;
use oat\taoLti\models\classes\Platform\Service\Oidc\Lti1p3OidcLoginAuthenticator;
use oat\taoLti\models\classes\Platform\Service\Oidc\OidcLoginAuthenticatorProxy;
use PHPUnit\Framework\MockObject\MockObject;

class OidcLoginAuthenticatorProxyTest extends TestCase
{
    /** @var OidcLoginAuthenticatorProxy */
    private $subject;

    /** @var Lti1p3OidcLoginAuthenticator|MockObject */
    private $oidcLoginAuthenticator;

    public function setUp(): void
    {
        $this->oidcLoginAuthenticator = $this->createMock(Lti1p3OidcLoginAuthenticator::class);
        $this->subject = new OidcLoginAuthenticatorProxy();
        $this->subject->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    Lti1p3OidcLoginAuthenticator::class => $this->oidcLoginAuthenticator
                ]
            )
        );
    }

    public function testAuthenticateWillProxyRequest(): void
    {
        $expectedResponse = new Response();

        $this->oidcLoginAuthenticator
            ->method('authenticate')
            ->willReturn($expectedResponse);

        $this->assertSame(
            $expectedResponse,
            $this->subject->authenticate(new ServerRequest('GET', ''), new Response())
        );
    }
}
