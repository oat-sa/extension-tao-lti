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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA;
 *
 *
 */

use oat\tao\scripts\update\OntologyUpdater;
use oat\tao\model\mvc\error\ExceptionInterpreterService;
use oat\taoLti\models\classes\CookieVerifyService;
use oat\taoLti\models\classes\ExceptionInterpreter;
use oat\taoLti\models\classes\user\OntologyLtiUserService;
use oat\taoLti\models\classes\user\LtiUserService;
use oat\taoLti\models\classes\ResourceLink\LinkService;
use oat\taoLti\models\classes\ResourceLink\OntologyLink;
/**
 * 
 * @author Joel Bout <joel@taotesting.com>
 */
class taoLti_scripts_update_Updater extends \common_ext_ExtensionUpdater
{

    /**
     * 
     * @param string $initialVersion
     * @return string $versionUpdatedTo
     */
    public function update($initialVersion)
    {
        $this->skip('0', '1.2');
        
        if ($this->isVersion('1.2')) {
            OntologyUpdater::syncModels();
            $this->setVersion('1.3.0');
        }

        $this->skip('1.3.0', '1.5.2');
        
        // add teacher assistant role
        if ($this->isVersion('1.5.2')) {
            OntologyUpdater::syncModels();
            $this->setVersion('1.6.0');
        }
        $this->skip('1.6.0', '1.12.0');

        if ($this->isVersion('1.12.0')) {
            $service = $this->getServiceManager()->get(ExceptionInterpreterService::SERVICE_ID);
            $interpreters = $service->getOption(ExceptionInterpreterService::OPTION_INTERPRETERS);
            $interpreters[\taoLti_models_classes_LtiException::class] = ExceptionInterpreter::class;
            $service->setOption(ExceptionInterpreterService::OPTION_INTERPRETERS, $interpreters);
            $this->getServiceManager()->register(ExceptionInterpreterService::SERVICE_ID, $service);
            $this->setVersion('1.13.0');
        }

        $this->skip('1.13.0', '2.0.0');

        if ($this->isVersion('2.0.0')) {
            $service = new CookieVerifyService([
                CookieVerifyService::OPTION_VERIFY_COOKIE => true
            ]);
            $service->setServiceManager($this->getServiceManager());
            $this->getServiceManager()->register(CookieVerifyService::SERVICE_ID, $service);

            $this->setVersion('2.1.0');
        }

        $this->skip('2.1.0', '3.3.1');

        if ($this->isVersion('3.3.1')) {
            $service = new OntologyLtiUserService();
            $service->setServiceManager($this->getServiceManager());
            $this->getServiceManager()->register(LtiUserService::SERVICE_ID, $service);

            $this->setVersion('3.4.0');
        }
        $this->skip('3.4.0', '3.4.4');

        if ($this->isVersion('3.4.4')) {
            $userService = $this->getServiceManager()->get(LtiUserService::SERVICE_ID);
            if ($userService instanceof OntologyLtiUserService) {
                $userService->setOption(OntologyLtiUserService::OPTION_TRANSACTION_SAFE, false);
                $userService->setOption(OntologyLtiUserService::OPTION_TRANSACTION_SAFE_RETRY, 1);
            }
            $this->getServiceManager()->register(LtiUserService::SERVICE_ID, $userService);
            
            $this->setVersion('3.5.0');
        }

        $this->skip('3.5.0', '3.6.0');

        if ($this->isVersion('3.6.0')) {
            if (!$this->getServiceManager()->has(LinkService::SERVICE_ID)) {
               $this->getServiceManager()->register(LinkService::SERVICE_ID, new OntologyLink());
            }
            $this->setVersion('3.7.0');
        }

        $this->skip('3.7.0', '3.7.1');
    }
}
