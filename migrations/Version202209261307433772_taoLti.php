<?php

declare(strict_types=1);

namespace oat\taoLti\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\search\SearchProxy;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoLti\models\classes\Platform\Repository\RdfLtiPlatformRepository;

final class Version202209261307433772_taoLti extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add taoLti to OPTION_GENERIS_SEARCH_WHITELIST';
    }

    public function up(Schema $schema): void
    {
        /** @var SearchProxy $searchProxy */
        $searchProxy = $this->getServiceManager()->get(SearchProxy::SERVICE_ID);

        $searchProxy->extendGenerisSearchWhiteList(
            [RdfLtiPlatformRepository::CLASS_URI]
        );

        $this->registerService(SearchProxy::SERVICE_ID, $searchProxy);
    }

    public function down(Schema $schema): void
    {
        /** @var SearchProxy $searchProxy */
        $searchProxy = $this->getServiceManager()->get(SearchProxy::SERVICE_ID);

        $searchProxy->removeFromGenerisSearchWhiteList(
            [RdfLtiPlatformRepository::CLASS_URI]
        );

        $this->registerService(SearchProxy::SERVICE_ID, $searchProxy);
    }
}
