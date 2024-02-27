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

namespace oat\taoLti\models\classes\Platform\Service;

use OAT\Library\Lti1p3Core\Security\Key\KeyChainInterface;
use OAT\Library\Lti1p3Core\Security\Key\KeyChainRepositoryInterface;
use oat\oatbox\cache\SimpleCache;
use oat\oatbox\service\ConfigurableService;
use oat\taoLti\models\classes\Security\DataAccess\Repository\CachedPlatformJwksRepository;
use oat\taoLti\models\classes\Security\DataAccess\Repository\CachedPlatformKeyChainRepository;
use oat\taoLti\models\classes\Security\DataAccess\Repository\PlatformKeyChainRepository;
use Psr\SimpleCache\CacheInterface;

class CachedKeyChainGenerator extends ConfigurableService implements KeyChainGeneratorInterface
{
    public function generate(): KeyChainInterface
    {
        $keyChain = $this->getKeyChainGenerator()->generate();
        $this->getKeyChainRepository()->saveDefaultKeyChain($keyChain);

        $this->invalidateKeyChain($keyChain);
        $this->invalidateJwks();

        return $keyChain;
    }

    private function invalidateKeyChain(KeyChainInterface $keyChain): void
    {
        $this->getCache()->delete(
            sprintf(CachedPlatformKeyChainRepository::PRIVATE_PATTERN, $keyChain->getIdentifier())
        );

        $this->getCache()->delete(
            sprintf(CachedPlatformKeyChainRepository::PUBLIC_PATTERN, $keyChain->getIdentifier())
        );
    }

    private function invalidateJwks(): void
    {
        $this->getCache()->delete(CachedPlatformJwksRepository::JWKS_KEY);
    }

    private function getKeyChainGenerator(): KeyChainGeneratorInterface
    {
        return $this->getServiceLocator()->get(OpenSslKeyChainGenerator::class);
    }

    private function getKeyChainRepository(): KeyChainRepositoryInterface
    {
        return $this->getServiceLocator()->get(PlatformKeyChainRepository::class);
    }

    private function getCache(): CacheInterface
    {
        return $this->getServiceLocator()->get(SimpleCache::SERVICE_ID);
    }
}
