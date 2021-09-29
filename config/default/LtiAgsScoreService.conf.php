<?php

use OAT\Library\Lti1p3Ags\Factory\Score\ScoreFactory;
use OAT\Library\Lti1p3Ags\Service\Score\Client\ScoreServiceClient;
use oat\taoLti\models\classes\LtiAgsScoreService;

return new LtiAgsScoreService(
    [
        LtiAgsScoreService::OPTION_SCORE_SERVICE_CLIENT => new ScoreServiceClient(),
        LtiAgsScoreService::OPTION_SCORE_FACTORY => new ScoreFactory(),
    ]
);
