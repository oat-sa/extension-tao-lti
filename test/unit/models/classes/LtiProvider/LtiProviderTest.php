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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoLti\test\unit\models\classes\LtiProvider;

use oat\generis\test\TestCase;
use oat\taoLti\models\classes\LtiProvider\LtiProvider;

class LtiProviderTest extends TestCase
{
    public function testConstructorWithDefaultValues()
    {
        $subject = new LtiProvider();
        $this->assertEquals('', $subject->getUri());
        $this->assertEquals('', $subject->getLabel());
        $this->assertEquals('', $subject->getKey());
        $this->assertEquals('', $subject->getSecret());
        $this->assertEquals('', $subject->getCallbackUrl());
    }

    public function testGettersSetters()
    {
        $uri = 'http://uri.com/blah/blah/blah';
        $label = 'A beautiful label';
        $key = 'foo';
        $secret = 'bar';
        $callbackUrl = 'baz';

        $subject = new LtiProvider();

        $this->assertEquals($subject, $subject->setUri($uri));
        $this->assertEquals($subject, $subject->setLabel($label));
        $this->assertEquals($subject, $subject->setKey($key));
        $this->assertEquals($subject, $subject->setSecret($secret));
        $this->assertEquals($subject, $subject->setCallbackUrl($callbackUrl));

        $this->assertEquals($uri, $subject->getUri());
        $this->assertEquals($label, $subject->getLabel());
        $this->assertEquals($key, $subject->getKey());
        $this->assertEquals($secret, $subject->getSecret());
        $this->assertEquals($callbackUrl, $subject->getCallbackUrl());
    }

    public function testConstructorAndSerializer()
    {
        $uri = 'http://uri.com/blah/blah/blah';
        $label = 'A beautiful label';
        $key = 'foo';
        $secret = 'bar';
        $callbackUrl = 'baz';

        $subject = new LtiProvider($uri, $label, $key, $secret, $callbackUrl);
        $expected = [
            'id' => $uri,
            'uri' => $uri,
            'text' => $label,
            'key' => $key,
            'secret' => $secret,
            'callback' => $callbackUrl,
        ];
        $this->assertEquals($expected, $subject->jsonSerialize());
    }
}
