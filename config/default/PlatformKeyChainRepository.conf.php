<?php

use oat\taoLti\models\classes\Security\DataAccess\Repository\PlatformKeyChainRepository;

return new PlatformKeyChainRepository([
        [
            PlatformKeyChainRepository::OPTION_DEFAULT_KEY_ID => 'defaultPlatformKeyId',
            PlatformKeyChainRepository::OPTION_DEFAULT_KEY_NAME => 'defaultPlatformKeyName',
            PlatformKeyChainRepository::OPTION_DEFAULT_PUBLIC_KEY_PATH => '/platform/default/public.key',
            PlatformKeyChainRepository::OPTION_DEFAULT_PRIVATE_KEY_PATH => '/platform/default/private.key',
        ]
    ]
);
