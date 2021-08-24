<?php

declare(strict_types=1);

namespace oat\taoLti\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\tao\model\search\SearchProxy;
use oat\taoLti\models\classes\ConsumerService;
use oat\taoLti\models\classes\ProviderService;

final class Version202108190925463772_taoLti extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Register LTI class URI to use generic search in ' . SearchProxy::class;
    }

    public function up(Schema $schema): void
    {
        $generisSearchWhitelist = $this->getLTIClassURI();
        $searchProxy = $this->getProxy();
        
        if ($searchProxy->hasOption(SearchProxy::OPTION_GENERIS_SEARCH_WHITELIST)) {
            $options = $searchProxy->getOption(SearchProxy::OPTION_GENERIS_SEARCH_WHITELIST);
            $generisSearchWhitelist = array_merge($options, $generisSearchWhitelist);
        }
        $searchProxy->setOption(SearchProxy::OPTION_GENERIS_SEARCH_WHITELIST, $generisSearchWhitelist);
        
        $this->getServiceManager()->register(SearchProxy::SERVICE_ID, $searchProxy);
    }

    public function down(Schema $schema): void
    {
        $generisSearchWhitelist = $this->getLTIClassURI();
        $searchProxy = $this->getProxy();
        if ($searchProxy->hasOption(SearchProxy::OPTION_GENERIS_SEARCH_WHITELIST)) {
            $options = $searchProxy->getOption(SearchProxy::OPTION_GENERIS_SEARCH_WHITELIST);
            $generisSearchlist = array_diff($options, $generisSearchWhitelist);
            $searchProxy->setOption(SearchProxy::OPTION_GENERIS_SEARCH_WHITELIST, $generisSearchlist);
        }
        $this->getServiceManager()->register(SearchProxy::SERVICE_ID, $searchProxy);
    }

    private function getProxy(): SearchProxy
    {
        return $this->getServiceManager()->get(SearchProxy::SERVICE_ID);
    }

    private function getLTIClassURI(): array
    {
        return [
            ConsumerService::CLASS_URI,
            ProviderService::CLASS_URI,
        ];
    }
}
