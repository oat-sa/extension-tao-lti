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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 */

namespace oat\taoLti\test\unit\models\classes\Lis;

use IMSGlobal\LTI\OAuth\OAuthConsumer;
use oat\generis\test\MockObject;
use oat\generis\test\TestCase;
use oat\taoLti\models\classes\Lis\LisOAuthConsumer;
use oat\taoLti\models\classes\LtiProvider\LtiProvider;

class LisOAuthConsumerTest extends TestCase
{
    public function testCreation()
    {
        /** @var LtiProvider|MockObject $ltiProviderMock */
        $ltiProviderMock = $this->createMock(LtiProvider::class);
        $ltiProviderMock->method('getKey')->willReturn('kkk1');
        $ltiProviderMock->method('getSecret')->willReturn('sss1');

        $consumer = new LisOAuthConsumer($ltiProviderMock, 'clb_url');

        $this->assertInstanceOf(OAuthConsumer::class, $consumer);
        $this->assertSame('kkk1', $consumer->key);
        $this->assertSame('sss1', $consumer->secret);
        $this->assertSame('clb_url', $consumer->callback_url);
    }
}
