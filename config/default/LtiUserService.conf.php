<?php
return new \oat\taoLti\models\classes\user\OntologyLtiUserService([
    'transaction-safe' => false,
    'transaction-safe-retry' => 1,
    'factoryLtiUser' => 'taoLti/LtiUserFactory'
]);
