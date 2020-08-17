<?php

use oat\taoLti\models\classes\Platform\Service\CachedKeyChainGenerator;

return new CachedKeyChainGenerator(
    [
        'sslConfig' => [
            'digest_alg' => 'sha256',
            'private_key_bits' => 4096,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]
    ]
);
