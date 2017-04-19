<?php

namespace oat\taoLti\scripts\install;

use oat\oatbox\extension\InstallAction;
use oat\taoLti\models\classes\CookieVerifyService;

class RegisterCookieVerifyService extends InstallAction
{

    public function __invoke($params)
    {
        $service = new CookieVerifyService([
            CookieVerifyService::OPTION_VERIFY_COOKIE => true
        ]);
        $service->setServiceManager($this->getServiceManager());
        $this->getServiceManager()->register(CookieVerifyService::SERVICE_ID, $service);
    }
}