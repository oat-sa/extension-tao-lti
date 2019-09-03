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

namespace oat\taoLti\test\integration\controller;

use common_http_Request;
use oat\generis\test\TestCase;
use oat\taoLti\controller\ToolModule;
use oat\taoLti\models\classes\LtiLaunchData;

class ToolModuleTest extends TestCase
{
    public function testBuildLaunchDataTest()
    {
        \common_Config::load();

        $request = new \common_http_Request(
            'http://test.it/tao/tao/tao/' . base64_encode(json_encode(['toto' => 'test']))
        );
        $toolModule = new ToolModuleMock();
        $data = $toolModule->buildLaunchData($request)->jsonSerialize();

        $this->assertArrayHasKey('variables', $data);
        $this->assertArrayHasKey(LtiLaunchData::LIS_OUTCOME_SERVICE_URL, $data['variables']);
        $this->assertEquals(_url('manageResults', 'ResultController', 'taoLtiConsumer'), $data['variables'][LtiLaunchData::LIS_OUTCOME_SERVICE_URL]);
    }
}

class ToolModuleMock extends ToolModule
{
    public function run()
    {
    }

    public function buildLaunchData(common_http_Request $request)
    {
        return parent::buildLaunchData($request);
    }

}