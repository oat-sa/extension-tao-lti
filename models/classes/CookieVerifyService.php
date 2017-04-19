<?php

namespace oat\taoLti\models\classes;

use oat\oatbox\service\ConfigurableService;

class CookieVerifyService extends ConfigurableService
{
    const SERVICE_ID = 'taoLti/cookieVerify';

    const OPTION_VERIFY_COOKIE = 'verify_cookie';
}