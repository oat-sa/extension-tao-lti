<?php

namespace oat\taoLti\models\classes;

use oat\oatbox\service\ConfigurableService;

class CookieVerifyService extends ConfigurableService
{
    const SERVICE_ID = 'taoLti/CookieVerifyService';

    const OPTION_VERIFY_COOKIE = 'verify_cookie';

    /**
     * Is verification of cookie required?
     *
     * @return bool
     */
    public function isVerifyCookieRequired()
    {
        return $this->getOption(self::OPTION_VERIFY_COOKIE) === true;
    }
}