<?php

return new oat\taoLti\models\classes\Lis\LisOauthService(array(
    'store' => new oat\taoLti\models\classes\Lis\LisOauthDataStore(array(
        'nonce_store' => new oat\tao\model\oauth\nonce\NoNonce()
    ))
));
