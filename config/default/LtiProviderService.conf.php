<?php

use oat\taoLti\models\classes\LtiProvider\LtiProviderService;
use oat\taoLti\models\classes\LtiProvider\RdfLtiProviderRepository;

return new LtiProviderService([
    LtiProviderService::LTI_PROVIDER_LIST_IMPLEMENTATIONS => [
        new RdfLtiProviderRepository(),
    ]
]);
