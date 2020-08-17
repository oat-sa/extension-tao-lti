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
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

class CachedPlatformKeyChainRepository extends ConfigurableService implements KeyChainRepositoryInterface
{
    public const PRIVATE_PATTERN = 'PLATFORM_LTI_PRIVATE_KEY_%s';
    public const PUBLIC_PATTERN = 'PLATFORM_LTI_PUBLIC_KEY_%s';

    public function save(KeyChain $keyChain): void
    {
        new KeyChainQuery($keyChain->getIdentifier());

        $this->setKeys(
            $keyChain,
            new KeyChainQuery($keyChain->getIdentifier())
        );

        $this->getPlatformKeyChainRepository()->save($keyChain);
    }

    public function findAll(KeyChainQuery $query): KeyChainCollection
    {
        if ($this->isCacheAvailable($query)) {
            //TODO: Needs to be refactor if we have multiple key chains
            $rawKeys = $this->getCacheService()->getMultiple(
                [
                    sprintf(self::PRIVATE_PATTERN, $query->getIdentifier()),
                    sprintf(self::PUBLIC_PATTERN, $query->getIdentifier()),
                ]
            );

            $platformKeyChainRepository = $this->getPlatformKeyChainRepository();

            return new KeyChainCollection(
                new KeyChain(
                    $platformKeyChainRepository->getOption(PlatformKeyChainRepository::OPTION_DEFAULT_KEY_ID),
                    $platformKeyChainRepository->getOption(PlatformKeyChainRepository::OPTION_DEFAULT_KEY_NAME),
                    new Key($rawKeys[sprintf(self::PUBLIC_PATTERN, $query->getIdentifier())]),
                    new Key($rawKeys[sprintf(self::PRIVATE_PATTERN, $query->getIdentifier())])
                )
            );
        }

        $keyChainCollection = $this->getPlatformKeyChainRepository()->findAll($query);

        foreach ($keyChainCollection->getKeyChains() as $keyChain) {
            $this->setKeys($keyChain, $query);
        }

        return $keyChainCollection;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function setKeys(KeyChain $keyChain, KeyChainQuery $query): void
    {
        $this->getCacheService()->set(
            sprintf(self::PRIVATE_PATTERN,
                $query->getIdentifier()
            ),

            $keyChain->getPrivateKey()->getValue()
        );

        $this->getCacheService()->set(
            sprintf(self::PUBLIC_PATTERN,
                $query->getIdentifier()
            ),

            $keyChain->getPublicKey()->getValue()
        );
    }

    private function isCacheAvailable(KeyChainQuery $query): bool
    {
        return $this->getCacheService()->has(sprintf(self::PRIVATE_PATTERN, $query->getIdentifier())) &&
            $this->getCacheService()->has(sprintf(self::PUBLIC_PATTERN, $query->getIdentifier()));
    }

    private function getCacheService(): CacheInterface
    {
        return $this->getServiceLocator()->get(SimpleCache::SERVICE_ID);
    }

    private function getPlatformKeyChainRepository(): PlatformKeyChainRepository
    {
        return $this->getServiceLocator()->get(PlatformKeyChainRepository::SERVICE_ID);
    }
}
