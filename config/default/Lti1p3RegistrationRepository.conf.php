<?php

use oat\taoLti\models\classes\Platform\Repository\Lti1p3RegistrationRepository;

return new Lti1p3RegistrationRepository(
    [
        Lti1p3RegistrationRepository::OPTION_ROOT_URL => ROOT_URL,
    ]
);
