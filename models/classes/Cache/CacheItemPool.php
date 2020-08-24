<?php /** @noinspection ALL */

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


namespace oat\taoLti\models\classes\Cache;

use common_exception_NoImplementation;
use oat\oatbox\service\ConfigurableService;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class CacheItemPool extends ConfigurableService implements CacheItemPoolInterface
{
    public function getItem($key)
    {
        throw new common_exception_NoImplementation(__METHOD__);
    }

    public function getItems(array $keys = [])
    {
        throw new common_exception_NoImplementation(__METHOD__); // TODO: Implement getItems() method.
    }

    public function hasItem($key)
    {
        throw new common_exception_NoImplementation(__METHOD__); // TODO: Implement hasItem() method.
    }

    public function clear()
    {
        throw new common_exception_NoImplementation(__METHOD__); // TODO: Implement clear() method.
    }

    public function deleteItem($key)
    {
        throw new common_exception_NoImplementation(__METHOD__); // TODO: Implement deleteItem() method.
    }

    public function deleteItems(array $keys)
    {
        throw new common_exception_NoImplementation(__METHOD__); // TODO: Implement deleteItems() method.
    }

    public function save(CacheItemInterface $item)
    {
        throw new common_exception_NoImplementation(__METHOD__); // TODO: Implement save() method.
    }

    public function saveDeferred(CacheItemInterface $item)
    {
        throw new common_exception_NoImplementation(__METHOD__); // TODO: Implement saveDeferred() method.
    }

    public function commit()
    {
        throw new common_exception_NoImplementation(__METHOD__); // TODO: Implement commit() method.
    }
}
