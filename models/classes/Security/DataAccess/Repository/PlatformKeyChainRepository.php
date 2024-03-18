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
use League\Flysystem\FilesystemInterface;
use OAT\Library\Lti1p3Core\Security\Key\Key;
use OAT\Library\Lti1p3Core\Security\Key\KeyChain;
use OAT\Library\Lti1p3Core\Security\Key\KeyChainInterface;
use OAT\Library\Lti1p3Core\Security\Key\KeyChainRepositoryInterface;
use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\security\Business\Domain\Key\Key as TaoKey;
use oat\tao\model\security\Business\Domain\Key\KeyChain as TaoKeyChain;
use oat\tao\model\security\Business\Domain\Key\KeyChainCollection;
use oat\tao\model\security\Business\Domain\Key\KeyChainQuery;
use oat\taoLti\models\classes\Exception\PlatformKeyChainException;

class PlatformKeyChainRepository extends ConfigurableService implements KeyChainRepositoryInterface
{
    public const SERVICE_ID = 'taoLti/PlatformKeyChainRepository';
    public const OPTION_DEFAULT_KEY_ID = 'defaultKeyId';
    public const OPTION_DEFAULT_KEY_ID_VALUE = 'defaultPlatformKeyId';
    public const OPTION_DEFAULT_KEY_NAME = 'defaultKeyName';
    public const OPTION_DEFAULT_KEY_NAME_VALUE = 'defaultPlatformKeyName';
    public const OPTION_DEFAULT_PUBLIC_KEY_PATH = 'defaultPublicKeyPath';
    public const OPTION_DEFAULT_PRIVATE_KEY_PATH = 'defaultPrivateKeyPath';
    public const OPTION_DEFAULT_PRIVATE_KEY_PASSPHRASE = 'defaultPrivateKeyPassphrase';
    public const FILE_SYSTEM_ID = 'ltiKeyChain';


    public function saveDefaultKeyChain(KeyChainInterface $keyChain): void
    {
        $this->save($keyChain, $this->getDefaultKeyId());
    }

    public function saveKeyChain(KeyChainInterface $keyChain): void
    {
        $this->save($keyChain, $keyChain->getIdentifier());
    }

    protected function save(KeyChainInterface $keyChain, string $identifier): void
    {
        $configs = $this->findConfiguration($identifier);

        if (empty($configs)) {
            throw new PlatformKeyChainException('Impossible to write LTI keys. Configuration not found');
        }

        $publicKeyPath = $configs[self::OPTION_DEFAULT_PUBLIC_KEY_PATH] ?? null;
        $privateKeyPath = $configs[self::OPTION_DEFAULT_PRIVATE_KEY_PATH] ?? null;
        $isPublicKeySaved = false;
        $isPrivateKeySaved = false;

        if ($publicKeyPath !== null && $privateKeyPath !== null) {
            $isPublicKeySaved = $this->getFileSystem()
                ->put(
                    ltrim($publicKeyPath, DIRECTORY_SEPARATOR),
                    $keyChain->getPublicKey()->getContent()
                );

            $isPrivateKeySaved = $this->getFileSystem()
                ->put(
                    ltrim($privateKeyPath, DIRECTORY_SEPARATOR),
                    $keyChain->getPrivateKey()->getContent()
                );
        }

        if (!$isPublicKeySaved || !$isPrivateKeySaved) {
            throw new PlatformKeyChainException('Impossible to write LTI keys');
        }
    }

    public function getDefaultKeyId(): string
    {
        $options = $this->getOptions();
        return reset($options)[self::OPTION_DEFAULT_KEY_ID] ?? '';
    }

    public function find(string $identifier): ?KeyChainInterface
    {
        $configs = $this->findConfiguration($identifier);

        if (empty($configs)) {
            return null;
        }

        $keyName = $configs[self::OPTION_DEFAULT_KEY_NAME] ?? '';
        $publicKeyPath = $configs[self::OPTION_DEFAULT_PUBLIC_KEY_PATH] ?? null;
        $privateKeyPath = $configs[self::OPTION_DEFAULT_PRIVATE_KEY_PATH] ?? null;
        $privateKeyPassphrase = $configs[self::OPTION_DEFAULT_PRIVATE_KEY_PASSPHRASE] ?? null;

        if (!$publicKeyPath || !$privateKeyPath) {
            throw new PlatformKeyChainException('The key path is not defined');
        }

        $publicKey = $this->getFileSystem()->read($publicKeyPath);
        $privateKey = $this->getFileSystem()->read($privateKeyPath);

        if ($publicKey === false || $privateKey === false) {
            throw new PlatformKeyChainException('Impossible to read LTI keys');
        }

        return new KeyChain(
            $identifier,
            $keyName,
            new Key($publicKey),
            new Key($privateKey, $privateKeyPassphrase)
        );
    }

    public function findAll(KeyChainQuery $query): KeyChainCollection
    {
        $options = $this->getOptions();
        foreach ($options as $configs) {
            $defaultKeyId = $configs[self::OPTION_DEFAULT_KEY_ID] ?? null;
            $defaultKeyName = $configs[self::OPTION_DEFAULT_KEY_NAME] ?? '';
            $publicKeyPath = $configs[self::OPTION_DEFAULT_PUBLIC_KEY_PATH] ?? null;
            $privateKeyPath = $configs[self::OPTION_DEFAULT_PRIVATE_KEY_PATH] ?? null;
            $privateKeyPassphrase = $configs[self::OPTION_DEFAULT_PRIVATE_KEY_PASSPHRASE] ?? null;

            if ($defaultKeyId && $publicKeyPath && $privateKeyPath) {
                $publicKey = $this->getFileSystem()->read($publicKeyPath);
                $privateKey = $this->getFileSystem()->read($privateKeyPath);

                $keyChains[] = new TaoKeyChain(
                    $defaultKeyId,
                    $defaultKeyName,
                    new TaoKey($publicKey),
                    new TaoKey($privateKey, $privateKeyPassphrase)
                );
            }
        }

        if (empty($keyChains)) {
            throw new PlatformKeyChainException('Impossible to read LTI keys');
        }

        return new KeyChainCollection(...$keyChains);
    }


    /**
     * @throws common_exception_NoImplementation
     */
    public function findByKeySetName(string $keySetName): array
    {
        throw new common_exception_NoImplementation();
    }

    private function getFileSystem(): FilesystemInterface
    {
        /** @var FileSystemService $fileSystemService */
        $fileSystemService = $this->getServiceLocator()
            ->get(FileSystemService::SERVICE_ID);

        return $fileSystemService->getFileSystem(self::FILE_SYSTEM_ID);
    }

    /**
     * @param string $identifier
     * @return array|null
     */
    public function findConfiguration(string $identifier): ?array
    {
        $options = $this->getOptions();
        foreach ($options as $configs) {
            if ($configs[self::OPTION_DEFAULT_KEY_ID] === $identifier) {
                return $configs;
            }
        }

        return null;
    }
}
