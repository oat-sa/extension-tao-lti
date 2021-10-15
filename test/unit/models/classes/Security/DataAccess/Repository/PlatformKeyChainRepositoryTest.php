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

namespace oat\taoLti\test\unit\models\classes\Security\DataAccess\Repository;

use ErrorException;
use oat\generis\test\TestCase;
use OAT\Library\Lti1p3Core\Security\Key\Key;
use OAT\Library\Lti1p3Core\Security\Key\KeyChain;
use OAT\Library\Lti1p3Core\Security\Key\KeyChainInterface;
use oat\oatbox\filesystem\FileSystem;
use oat\oatbox\filesystem\FileSystemService;
use oat\taoLti\models\classes\Security\DataAccess\Repository\PlatformKeyChainRepository;
use PHPUnit\Framework\MockObject\MockObject;

class PlatformKeyChainRepositoryTest extends TestCase
{
    /** @var PlatformKeyChainRepository */
    private $subject;

    /** @var FileSystem|MockObject */
    private $fileSystem;

    public function setUp(): void
    {
        $this->fileSystem = $this->createMock(FileSystem::class);

        $fileSystem = $this->createMock(FileSystemService::class);
        $fileSystem->method('getFileSystem')
            ->willReturn($this->fileSystem);

        $this->subject = new PlatformKeyChainRepository(
            [
                PlatformKeyChainRepository::OPTION_DEFAULT_KEY_ID => 'keyId',
                PlatformKeyChainRepository::OPTION_DEFAULT_KEY_NAME => 'keyName',
                PlatformKeyChainRepository::OPTION_DEFAULT_PUBLIC_KEY_PATH => '',
                PlatformKeyChainRepository::OPTION_DEFAULT_PRIVATE_KEY_PATH => '',
            ]
        );
        $this->subject->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    FileSystemService::SERVICE_ID => $fileSystem
                ]
            )
        );
    }

    public function testFind(): void
    {
        $this->fileSystem
            ->method('read')
            ->willReturnOnConsecutiveCalls(
                'publicKey',
                'privateKey'
            );

        $keyChain = $this->subject->find('keyId');

        $this->assertInstanceOf(KeyChainInterface::class, $keyChain);
        $this->assertEquals(
            $keyChain = new KeyChain(
                'keyId',
                'keyName',
                new Key('publicKey'),
                new Key('privateKey')
            ),
            $keyChain
        );
    }

    public function testFindFails(): void
    {
        $this->fileSystem
            ->method('read')
            ->willReturn(false);

        $keyChain = $this->subject->find('');

        $this->assertNull($keyChain);
    }

    public function testSave(): void
    {
        $this->fileSystem
            ->method('put')
            ->willReturn(true);

        $this->subject->save(
            new KeyChain('', '', new Key(''), new Key(''))
        );

        $this->expectNotToPerformAssertions();
    }

    public function testSaveFails(): void
    {
        $this->fileSystem
            ->method('put')
            ->willReturn(false);

        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('Impossible to write LTI keys');

        $this->subject->save(new KeyChain('', '', new Key(''), new Key('')));
    }

    public function testGetDefaultKeyId(): void
    {
        $this->assertSame(
            'keyId',
            $this->subject->getDefaultKeyId()
        );
    }
}
