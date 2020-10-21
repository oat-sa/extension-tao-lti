<?php

use oat\taoLti\models\classes\Security\AuthorizationServer\AuthorizationServerFactory;

return new AuthorizationServerFactory(
    [
        AuthorizationServerFactory::OPTION_ENCRYPTION_KEY => 'verySecretKeyToReplace',
    ]
);
