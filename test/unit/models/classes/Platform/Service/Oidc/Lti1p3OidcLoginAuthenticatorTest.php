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
use OAT\Library\Lti1p3Core\Message\LtiMessage;
use OAT\Library\Lti1p3Core\Security\Oidc\OidcAuthenticator;
use oat\taoLti\models\classes\Platform\Service\Oidc\Lti1p3OidcLoginAuthenticator;
use PHPUnit\Framework\MockObject\MockObject;

class Lti1p3OidcLoginAuthenticatorTest extends TestCase
{
    /** @var Lti1p3OidcLoginAuthenticator */
    private $subject;

    /** @var OidcAuthenticator|MockObject */
    private $oidcLoginAuthenticator;

    public function setUp(): void
    {
        $this->oidcLoginAuthenticator = $this->createMock(OidcAuthenticator::class);
        $this->subject = new Lti1p3OidcLoginAuthenticator();
        $this->subject->withLoginAuthenticator($this->oidcLoginAuthenticator);
    }

    public function testAuthenticate(): void
    {
        $ltiMessage = new LtiMessage('');

        $this->oidcLoginAuthenticator
            ->method('authenticate')
            ->willReturn($ltiMessage);

        $request = new ServerRequest('GET', '');
        $response = new Response();

        $this->assertSame(
            $ltiMessage->toHtmlRedirectForm(),
            (string)$this->subject->authenticate($request, $response)->getBody()
        );
    }
}
