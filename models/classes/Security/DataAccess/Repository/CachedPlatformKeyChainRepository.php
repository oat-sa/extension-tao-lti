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

use oat\oatbox\cache\SimpleCache;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\security\Business\Contract\KeyChainRepositoryInterface;
use oat\tao\model\security\Business\Domain\Key\Key;
use oat\tao\model\security\Business\Domain\Key\KeyChain;
use oat\tao\model\security\Business\Domain\Key\KeyChainCollection;
use oat\tao\model\security\Business\Domain\Key\KeyChainQuery;
use oat\taoLti\models\classes\Platform\Service\KeyChainGenerator;
use Psr\SimpleCache\InvalidArgumentException;

class CachedPlatformKeyChainRepository extends ConfigurableService implements KeyChainRepositoryInterface
{
    public const PRIVATE_PREFIX = 'PLATFORM_LTI_PRIVATE_KEY_';
    public const PUBLIC_PREFIX = 'PLATFORM_LTI_PUBLIC_KEY_';
    private const OPTION_DEFAULT_KEY_NAME = 'defaultKeyName';

    public function save(KeyChain $keyChain): void
    {
        $this->setKeys($keyChain);
        $this->getPlatformKeyChainRepository()->save($keyChain);
    }

    public function findAll(KeyChainQuery $query): KeyChainCollection
    {
        if (!(
            $this->getCacheService()->has(self::PRIVATE_PREFIX . $query->getIdentifier()) ||
            $this->getCacheService()->has(self::PUBLIC_PREFIX . $query->getIdentifier())
        )) {
            $this->setKeys($this->getKeyChainGenerator()->getKeyChain());
        }

        $keyChain = new KeyChain(
            $query->getIdentifier(),
            self::OPTION_DEFAULT_KEY_NAME,
            new Key($this->getCacheService()->get(self::PUBLIC_PREFIX . $query->getIdentifier())),
            new Key($this->getCacheService()->get(self::PRIVATE_PREFIX . $query->getIdentifier()))
        );

        return new KeyChainCollection($keyChain);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function setKeys(KeyChain $keyChain): void
    {
        $this->getCacheService()->set(self::PRIVATE_PREFIX . $keyChain->getIdentifier(), $keyChain->getPrivateKey());
        $this->getCacheService()->set(self::PUBLIC_PREFIX . $keyChain->getIdentifier(), $keyChain->getPublicKey());
    }

    private function getCacheService(): SimpleCache
    {
        return $this->getServiceLocator()->get(SimpleCache::SERVICE_ID);
    }

    private function getKeyChainGenerator(): KeyChainGenerator
    {
        return $this->getServiceLocator()->get(KeyChainGenerator::class);
    }

    private function getPlatformKeyChainRepository(): KeyChainRepositoryInterface
    {
        return $this->getServiceLocator()->get(PlatformKeyChainRepository::SERVICE_ID);
    }
}
