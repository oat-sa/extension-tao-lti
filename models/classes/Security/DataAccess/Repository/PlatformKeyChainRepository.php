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
use League\Flysystem\FilesystemInterface;
use OAT\Library\Lti1p3Core\Security\Key\Key;
use OAT\Library\Lti1p3Core\Security\Key\KeyChain;
use OAT\Library\Lti1p3Core\Security\Key\KeyChainInterface;
use OAT\Library\Lti1p3Core\Security\Key\KeyChainRepositoryInterface;
use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\service\ConfigurableService;

class PlatformKeyChainRepository extends ConfigurableService implements KeyChainRepositoryInterface
{
    public const SERVICE_ID = 'taoLti/PlatformKeyChainRepository';
    public const OPTION_DEFAULT_KEY_ID = 'defaultKeyId';
    public const OPTION_DEFAULT_KEY_NAME = 'defaultKeyName';
    public const OPTION_DEFAULT_PUBLIC_KEY_PATH = 'defaultPublicKeyPath';
    public const OPTION_DEFAULT_PRIVATE_KEY_PATH = 'defaultPrivateKeyPath';
    public const FILE_SYSTEM_ID = 'ltiKeyChain';

    /**
     * @throws ErrorException
     */
    public function save(KeyChainInterface $keyChain): void
    {
        $isPublicKeySaved = $this->getFileSystem()
            ->put(
                ltrim($this->getOption(self::OPTION_DEFAULT_PUBLIC_KEY_PATH), DIRECTORY_SEPARATOR),
                $keyChain->getPublicKey()->getContent()
            );

        $isPrivateKeySaved = $this->getFileSystem()
            ->put(
                ltrim($this->getOption(self::OPTION_DEFAULT_PRIVATE_KEY_PATH), DIRECTORY_SEPARATOR),
                $keyChain->getPrivateKey()->getContent()
            );

        if (!$isPublicKeySaved || !$isPrivateKeySaved) {
            throw new ErrorException('Impossible to write LTI keys');
        }
    }

    public function getDefaultKeyId(): string
    {
        return $this->getOption(PlatformKeyChainRepository::OPTION_DEFAULT_KEY_ID, '');
    }

    /**
     * @throws common_exception_NoImplementation
     */
    public function find(string $identifier): ?KeyChainInterface
    {
        if ($identifier !== $this->getDefaultKeyId()) {
            return null;
        }

        $publicKey = $this->getFileSystem()
            ->read($this->getOption(self::OPTION_DEFAULT_PUBLIC_KEY_PATH));

        $privateKey = $this->getFileSystem()
            ->read($this->getOption(self::OPTION_DEFAULT_PRIVATE_KEY_PATH));

        if ($publicKey === false || $privateKey === false) {
            throw new ErrorException('Impossible to read LTI keys');
        }

        return new KeyChain(
            $this->getDefaultKeyId(),
            $this->getOption(self::OPTION_DEFAULT_KEY_NAME),
            new Key($publicKey),
            new Key($privateKey)
        );
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
}
