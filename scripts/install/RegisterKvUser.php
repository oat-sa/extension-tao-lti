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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 *
 */
namespace oat\taoLti\scripts\install;

use oat\oatbox\extension\InstallAction;
use oat\taoLti\models\classes\user\KvLtiUserService;

/**
 * Class RegisterKvUser
 * @package oat\taoLti\scripts\install
 * @author Antoine Robin, <antoine@taotesting.com>
 */
class RegisterKvUser extends InstallAction
{

    /**
     * @param $params
     * @return \common_report_Report
     */
    public function __invoke($params)
    {
        if(count($params) === 0){
            return new \common_report_Report(\common_report_Report::TYPE_ERROR, __('Please give the key value persistence id'));
        }
        $persistenceId = reset($params);
        $ltiUser = new KvLtiUserService([KvLtiUserService::OPTION_PERSISTENCE => $persistenceId]);
        $this->registerService(KvLtiUserService::SERVICE_ID, $ltiUser);

        return new \common_report_Report(\common_report_Report::TYPE_SUCCESS, __('Registered Lti user in Key value'));
    }
}