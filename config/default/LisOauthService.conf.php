<?php

return new oat\taoLti\models\classes\Lis\LisOauthService([
    'store' => new oat\taoLti\models\classes\Lis\LisOauthDataStore([
        'nonce_store' => new oat\tao\model\oauth\nonce\NoNonce()
    ]),
    'lockout' => new oat\tao\model\oauth\lockout\NoLockout(),
]);
