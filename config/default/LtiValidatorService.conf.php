<?php

use oat\taoLti\models\classes\LaunchData\Validator\LtiValidatorService;
use oat\taoLti\models\classes\LaunchData\Validator\LaunchDataValidator;

/**
 * Default config
 */
return new LtiValidatorService([
    LtiValidatorService::OPTION_LAUNCH_DATA_VALIDATOR => new LaunchDataValidator()
]);
