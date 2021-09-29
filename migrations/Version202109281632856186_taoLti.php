<?php

declare(strict_types=1);

namespace oat\taoLti\migrations;

use Doctrine\DBAL\Schema\Schema;
//use OAT\Library\Lti1p3Ags\Factory\Score\ScoreFactory;
//use OAT\Library\Lti1p3Ags\Service\Score\Client\ScoreServiceClient;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\tao\scripts\update\OntologyUpdater;
use oat\taoLti\models\classes\LtiAgsScoreService;

final class Version202109281632856186_taoLti extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        OntologyUpdater::syncModels();

        $this->getServiceManager()->register(
            LtiAgsScoreService::SERVICE_ID,
            new LtiAgsScoreService(
                // option are commented, coz there is an error on migration "Serialization of 'Closure' is not allowed"
//                [
//                    LtiAgsScoreService::OPTION_SCORE_SERVICE_CLIENT => new ScoreServiceClient(),
//                    LtiAgsScoreService::OPTION_SCORE_FACTORY => new ScoreFactory(),
//                ]
            )
        );
    }

    public function down(Schema $schema): void
    {
        $this->getServiceManager()->unregister(LtiAgsScoreService::SERVICE_ID);
    }
}
