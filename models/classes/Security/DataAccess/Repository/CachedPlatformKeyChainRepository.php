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

use common_exception_NoImplementation;
use ErrorException;
use OAT\Library\Lti1p3Core\Security\Key\Key;
use OAT\Library\Lti1p3Core\Security\Key\KeyChain;
use OAT\Library\Lti1p3Core\Security\Key\KeyChainInterface;
use OAT\Library\Lti1p3Core\Security\Key\KeyChainRepositoryInterface;
use oat\oatbox\cache\SimpleCache;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\security\Business\Domain\Key\KeyChainCollection;
use oat\tao\model\security\Business\Domain\Key\KeyChainQuery;
use oat\tao\model\security\Business\Domain\Key\Key as TaoKey;
use oat\tao\model\security\Business\Domain\Key\KeyChain as TaoKeyChain;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

class CachedPlatformKeyChainRepository extends ConfigurableService implements KeyChainRepositoryInterface
{
    public const PRIVATE_PATTERN = 'PLATFORM_LTI_PRIVATE_KEY_%s';
    public const PUBLIC_PATTERN = 'PLATFORM_LTI_PUBLIC_KEY_%s';

    /**
     * @throws InvalidArgumentException
     * @throws ErrorException
     */
    public function saveDefaultKeyChain(KeyChainInterface $keyChain): void
    {
        $this->setKeys(
            $keyChain,
            $keyChain->getIdentifier()
        );

        $this->getPlatformKeyChainRepository()->saveDefaultKeyChain($keyChain);
    }

    public function find(string $identifier): ?KeyChainInterface
    {
        if ($this->exists($identifier)) {
            $rawKeys = $this->getCacheService()->getMultiple(
                [
                    sprintf(self::PRIVATE_PATTERN, $identifier),
                    sprintf(self::PUBLIC_PATTERN, $identifier),
                ]
            );

            $platformKeyChainRepository = $this->getPlatformKeyChainRepository();

            return new KeyChain(
                $platformKeyChainRepository->getOption(PlatformKeyChainRepository::OPTION_DEFAULT_KEY_ID),
                $platformKeyChainRepository->getOption(PlatformKeyChainRepository::OPTION_DEFAULT_KEY_NAME),
                new Key($rawKeys[sprintf(self::PUBLIC_PATTERN, $identifier)]),
                new Key($rawKeys[sprintf(self::PRIVATE_PATTERN, $identifier)])
            );
        }

        $keyChain = $this->getPlatformKeyChainRepository()->find($identifier);

        if ($keyChain !== null) {
            $this->setKeys($keyChain, $identifier);
        }

        return $keyChain;
    }

    public function findAll(KeyChainQuery $query): KeyChainCollection
    {
        if ($query->getIdentifier() == null) {
            $query = new KeyChainQuery($this->getPlatformKeyChainRepository()->getDefaultKeyId());
        }

        if ($this->exists($query->getIdentifier())) {
            $rawKeys = $this->getCacheService()->getMultiple(
                [
                    sprintf(self::PRIVATE_PATTERN, $query->getIdentifier()),
                    sprintf(self::PUBLIC_PATTERN, $query->getIdentifier()),
                ]
            );

            $platformKeyChainRepository = $this->getPlatformKeyChainRepository();

            return new KeyChainCollection(
                new TaoKeyChain(
                    $platformKeyChainRepository->getOption(PlatformKeyChainRepository::OPTION_DEFAULT_KEY_ID),
                    $platformKeyChainRepository->getOption(PlatformKeyChainRepository::OPTION_DEFAULT_KEY_NAME),
                    new TaoKey($rawKeys[sprintf(self::PUBLIC_PATTERN, $query->getIdentifier())]),
                    new TaoKey($rawKeys[sprintf(self::PRIVATE_PATTERN, $query->getIdentifier())])
                )
            );
        }

        $keyChainCollection = $this->getPlatformKeyChainRepository()->findAll($query);

        foreach ($keyChainCollection->getKeyChains() as $keyChain) {
            $this->setKeys($keyChain, $query->getIdentifier());
        }

        return $keyChainCollection;
    }

    /**
     * @throws common_exception_NoImplementation
     */
    public function findByKeySetName(string $keySetName): array
    {
        throw new common_exception_NoImplementation();
    }

    /**
     * @var KeyChainInterface|TaoKeyChain $keyChain
     * @throws InvalidArgumentException
     */
    private function setKeys($keyChain, string $identifier): void
    {
        $this->getCacheService()->set(
            sprintf(self::PRIVATE_PATTERN, $identifier),
            $keyChain->getPrivateKey()->getContent()
        );

        $this->getCacheService()->set(
            sprintf(self::PUBLIC_PATTERN, $identifier),
            $keyChain->getPublicKey()->getContent()
        );
    }

    private function exists(string $identifier): bool
    {
        return $this->getCacheService()->has(sprintf(self::PRIVATE_PATTERN, $identifier)) &&
            $this->getCacheService()->has(sprintf(self::PUBLIC_PATTERN, $identifier));
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
