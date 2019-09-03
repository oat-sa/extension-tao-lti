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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoLti\test\integration\model;

use common_Config;
use oat\generis\test\TestCase;
use oat\taoLti\models\classes\LtiLaunchData;

class LtiLaunchDataTest extends TestCase
{
    public function setUp()
    {
        common_Config::load();
    }

    public function testFromRequestWithoutData()
    {
        $request = new \common_http_Request(
            'http://test.it/tao/tao/tao/' . base64_encode(json_encode(['toto' => 'test']))
        );
        $data = LtiLaunchData::fromRequest($request)->jsonSerialize();

        $this->assertArrayHasKey('customParams', $data);
        $this->assertArrayHasKey('toto', $data['customParams']);
        $this->assertEquals('test', $data['customParams']['toto']);

        $this->assertEmpty($data['variables']);
    }

    public function testFromRequestWithData()
    {
        $request = new \common_http_Request(
            'http://test.it/tao/tao/tao/' . base64_encode(json_encode(['toto' => 'test']))
        );
        $data = LtiLaunchData::fromRequest($request, ['thisisatest' => 'predefinedValue'])->jsonSerialize();

        $this->assertArrayHasKey('customParams', $data);
        $this->assertArrayHasKey('toto', $data['customParams']);
        $this->assertEquals('test', $data['customParams']['toto']);

        $this->assertArrayHasKey('variables', $data);
        $this->assertArrayHasKey('thisisatest', $data['variables']);
        $this->assertEquals('predefinedValue', $data['variables']['thisisatest']);
    }

    public function testFromRequestWithoutUrl()
    {
        $request = new \common_http_Request('http://test.it/now');
        $data = LtiLaunchData::fromRequest($request, ['thisisatest' => 'predefinedValue'])->jsonSerialize();

        $this->assertEmpty($data['customParams']);

        $this->assertArrayHasKey('variables', $data);
        $this->assertArrayHasKey('thisisatest', $data['variables']);
        $this->assertEquals('predefinedValue', $data['variables']['thisisatest']);
    }


}