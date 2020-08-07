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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoLti\models\classes\Security\DataAccess\Repository;

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\security\Business\Contract\KeyChainRepositoryInterface;
use oat\tao\model\security\Business\Domain\Key\Key;
use oat\tao\model\security\Business\Domain\Key\KeyChain;
use oat\tao\model\security\Business\Domain\Key\KeyChainCollection;
use oat\tao\model\security\Business\Domain\Key\KeyChainQuery;

class ToolKeyChainRepository extends ConfigurableService implements KeyChainRepositoryInterface
{
    public function save(KeyChain $keyChain): void
    {
        //@TODO Implement when Provider is ready
    }

    public function findAll(KeyChainQuery $query): KeyChainCollection
    {
        /**
         * @TODO This config must come for LTIProvider configuration
         */
        $publicKey = file_get_contents(ROOT_PATH . 'tool.key');

        $keyChain = new KeyChain(
            '1',
            'myToolKeySetName',
            new Key($publicKey),
            new Key('')
        );

        return new KeyChainCollection(...[$keyChain]);
    }
}
