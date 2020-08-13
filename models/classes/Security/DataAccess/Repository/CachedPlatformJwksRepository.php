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

use oat\oatbox\cache\MultipleCacheTrait;
use oat\oatbox\cache\SimpleCache;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\security\Business\Contract\JwksRepositoryInterface;
use oat\tao\model\security\Business\Domain\Key\Jwks;

class CachedPlatformJwksRepository extends ConfigurableService implements JwksRepositoryInterface
{
    public const JWKS_KEY = 'PLATFORM_JWKS';

    public function find(): Jwks
    {
        if ($this->getCacheService()->has(self::JWKS_KEY)) {
            $jwks = $this->getCacheService()->get(self::JWKS_KEY);

            return new Jwks(...$jwks['keys']);
        }

        $jwks = $this->getJwksRepository()->find();
        $this->getCacheService()->set(self::JWKS_KEY, $jwks->jsonSerialize());

        return $jwks;
    }

    private function getJwksRepository(): JwksRepositoryInterface
    {
        return $this->getServiceLocator()->get(PlatformJwksRepository::class);
    }

    private function getCacheService(): SimpleCache
    {
        return $this->getServiceLocator()->get(SimpleCache::SERVICE_ID);
    }
}
