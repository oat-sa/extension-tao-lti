<?php
use oat\taoLti\models\classes\user\OntologyLtiUserService;

return new OntologyLtiUserService([
    OntologyLtiUserService::OPTION_FACTORY_LTI_USER => 'taoLti/LtiUserFactory'
]);
