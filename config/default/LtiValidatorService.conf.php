<?php

/**
 * Default config
 */

use oat\taoLti\models\classes\LaunchData\Validator\LtiValidatorService;
use oat\taoLti\models\classes\LaunchData\Validator\Lti11LaunchDataValidator;

return new LtiValidatorService([
    LtiValidatorService::OPTION_LAUNCH_DATA_VALIDATOR => new Lti11LaunchDataValidator()
]);
