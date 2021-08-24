<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoLti\scripts\install;

use oat\oatbox\extension\InstallAction;
use oat\tao\model\search\SearchProxy;
use oat\taoLti\models\classes\ConsumerService;
use oat\taoLti\models\classes\ProviderService;

class GenerisSearchWhitelist extends InstallAction
{
    public function __invoke($params)
    {
        /** @var SearchProxy $searchProxy */
        $searchProxy = $this->getServiceManager()->get(SearchProxy::SERVICE_ID);

        $generisSearchWhitelist = [
            ConsumerService::CLASS_URI,
            ProviderService::CLASS_URI,
        ];

        if ($searchProxy->hasOption(SearchProxy::OPTION_GENERIS_SEARCH_WHITELIST)) {
            $options = $searchProxy->getOption(SearchProxy::OPTION_GENERIS_SEARCH_WHITELIST);
            $generisSearchWhitelist = array_merge($options, $generisSearchWhitelist);
        }
        $searchProxy->setOption(SearchProxy::OPTION_GENERIS_SEARCH_WHITELIST, $generisSearchWhitelist);

        $this->getServiceManager()->register(SearchProxy::SERVICE_ID, $searchProxy);
    }
}
