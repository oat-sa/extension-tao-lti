<?php

declare(strict_types=1);

namespace oat\taoLti\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\tao\model\search\SearchProxy;

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
        
        if ($searchProxy->hasOption("generis_search_whitelist")) {
            $options = $searchProxy->getOption("generis_search_whitelist");
            $generisSearchWhitelist = array_merge($options, $generisSearchWhitelist);
        }
        $searchProxy->setOption("generis_search_whitelist", $generisSearchWhitelist);
        
        $this->getServiceManager()->register(SearchProxy::SERVICE_ID, $searchProxy);
    }

    public function down(Schema $schema): void
    {
        $generisSearchWhitelist = $this->getLTIClassURI();
        $searchProxy = $this->getProxy();
        if ($searchProxy->hasOption("generis_search_whitelist")) {
            $options = $searchProxy->getOption("generis_search_whitelist");
            $generisSearchlist = array_diff($options, $generisSearchWhitelist);
            $searchProxy->setOption("generis_search_whitelist", $generisSearchlist);
        }
    }

    private function getProxy(): SearchProxy
    {
        return $this->getServiceManager()->get(SearchProxy::SERVICE_ID);
    }

    private function getLTIClassURI(): array
    {
        return [
            'http://www.tao.lu/Ontologies/TAOLTI.rdf#LTIConsumer',
            'http://www.tao.lu/Ontologies/TAOLTI.rdf#LTIProvider',
            'http://www.tao.lu/Ontologies/TAOLTI.rdf#Platform',
        ];
    }
}
