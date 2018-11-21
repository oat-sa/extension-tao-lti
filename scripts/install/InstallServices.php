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

use common_report_Report;
use oat\oatbox\extension\AbstractAction;
use oat\tao\model\mvc\error\ExceptionInterpreterService;
use oat\taoLti\models\classes\ExceptionInterpreter;
use oat\taoLti\models\classes\LtiException;
use oat\taoLti\models\classes\user\UserService;

/**
 * @package oat\taoLti\scripts\install
 * @author Aleh Hutnikau, <hutnikau@gmail.com>
 */
class InstallServices extends AbstractAction
{
    /**
     * @param $params
     * @return common_report_Report
     * @throws \common_Exception
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     */
    public function __invoke($params)
    {
        $exceptionInterpreterService = $this->getServiceManager()->get(ExceptionInterpreterService::SERVICE_ID);
        $interpreters = $exceptionInterpreterService->getOption(ExceptionInterpreterService::OPTION_INTERPRETERS);
        $interpreters[LtiException::class] = ExceptionInterpreter::class;
        $exceptionInterpreterService->setOption(ExceptionInterpreterService::OPTION_INTERPRETERS, $interpreters);
        $this->getServiceManager()->register(ExceptionInterpreterService::SERVICE_ID, $exceptionInterpreterService);

        $userService = $this->getServiceManager()->get(\tao_models_classes_UserService::SERVICE_ID);
        $config = $userService->getOptions();
        $newLtiUserService = new UserService($config);
        $this->getServiceManager()->register(\tao_models_classes_UserService::SERVICE_ID, $newLtiUserService);

        return common_report_Report::createSuccess('Successfully installed');
    }
}