<?php

declare(strict_types=1);

namespace oat\taoLti\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\tao\scripts\update\OntologyUpdater;
use oat\taoLti\models\classes\LtiAgsScoreService;

final class Version202109281632856186_taoLti extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        OntologyUpdater::syncModels();

        file_put_contents(
            __DIR__ . '/../config/taoLti/LtiAgsScoreService.conf.php',
            $this->getConfigContent()
        );

        $this->getServiceManager()->register(
            LtiAgsScoreService::SERVICE_ID,
            new LtiAgsScoreService()
        );
    }

    public function down(Schema $schema): void
    {
        $this->getServiceManager()->unregister(LtiAgsScoreService::SERVICE_ID);

        unlink(__DIR__ . '/../config/taoLti/LtiAgsScoreService.conf.php');
    }

    private function getConfigContent(): string
    {
        return <<<EOD
<?php

use oat\taoLti\models\classes\LtiAgsScoreService;

return new LtiAgsScoreService(
    [
        LtiAgsScoreService::OPTION_SCORE_SERVICE_CLIENT => new \OAT\Library\Lti1p3Ags\Service\Score\Client\ScoreServiceClient(),
        LtiAgsScoreService::OPTION_SCORE_FACTORY => new \OAT\Library\Lti1p3Ags\Factory\Score\ScoreFactory(),
    ]
);
EOD;
    }
}
