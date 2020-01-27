<?php

use oat\taoLti\models\classes\user\LtiUserFactoryService;
use oat\taoLti\models\classes\user\LtiUserService;

return new \oat\taoLti\models\classes\user\OntologyLtiUserService([
    LtiUserService::OPTION_FACTORY_LTI_USER => LtiUserFactoryService::SERVICE_ID,
]);
