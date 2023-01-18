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
 * Copyright (c) 2019-2023 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoLti\test\unit\models\classes;

use common_http_Request as Request;
use oat\generis\test\TestCase;
use OAT\Library\Lti1p3Core\Message\Payload\Claim\AgsClaim;
use oat\taoLti\models\classes\LtiLaunchData;
use oat\generis\test\MockObject;

class LtiLaunchDataTest extends TestCase
{
    private const ROOT_URL = 'http://example.com/';

    public function setUp(): void
    {
        if (!defined('ROOT_URL')) {
            define('ROOT_URL', self::ROOT_URL);
        }
    }

    public function testFromRequest(): void
    {
        $params = ['key1' => 'value2'];
        $extraParams = ['key2' => 'value2'];
        $url = ROOT_URL . 'tao/tao/tao/' . base64_encode(json_encode($extraParams));

        $request = $this->createConfiguredMock(
            Request::class,
            [
                'getUrl' => $url,
                'getParams' => $params
            ]
        );

        $subject = LtiLaunchData::fromRequest($request);

        $this->assertEquals($params, $subject->getVariables());
        $this->assertEquals($extraParams, $subject->getCustomParameters());
    }

    public function testFromJsonArray(): void
    {
        $this->jsonSerialisationTest();
    }

    public function testJsonSerialize(): void
    {
        $this->jsonSerialisationTest();
    }

    private function jsonSerialisationTest(): void
    {
        $agsClaim = new AgsClaim(['scope1', 'scope2'], 'url1', 'url2');
        $subject = new LtiLaunchData([
            'key' => 'value',
            LtiLaunchData::AGS_CLAIMS => $agsClaim,
            'key2' => 'value2'
        ], ['custom_key' => 'value']);

        $toJsonAndBack = LtiLaunchData::fromJsonArray(
            json_decode(json_encode($subject), true)
        );

        $this->assertEquals(
            $subject->getVariables(),
            $toJsonAndBack->getVariables()
        );

        $this->assertEquals(
            $subject->getCustomParameters(),
            $toJsonAndBack->getCustomParameters()
        );
    }
}
