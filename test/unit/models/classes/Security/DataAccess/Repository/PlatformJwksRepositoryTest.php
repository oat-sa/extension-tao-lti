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

use oat\generis\test\ServiceManagerMockTrait;
use OAT\Library\Lti1p3Core\Security\Jwks\Exporter\Jwk\JwkExporterInterface;
use oat\tao\model\security\Business\Domain\Key\Key;
use oat\tao\model\security\Business\Domain\Key\KeyChain;
use oat\tao\model\security\Business\Domain\Key\KeyChainCollection;
use oat\taoLti\models\classes\Security\DataAccess\Repository\PlatformJwksRepository;
use oat\taoLti\models\classes\Security\DataAccess\Repository\PlatformKeyChainRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PlatformJwksRepositoryTest extends TestCase
{
    use ServiceManagerMockTrait;

    /** @var PlatformJwksRepository */
    private PlatformJwksRepository $subject;

    /** @var JwkExporterInterface|MockObject */
    private JwkExporterInterface $jwksExporter;

    /** @var PlatformKeyChainRepository|MockObject */
    private PlatformKeyChainRepository $keyChainRepository;

    public function setUp(): void
    {
        $this->jwksExporter = $this->createMock(JwkExporterInterface::class);
        $this->keyChainRepository = $this->createMock(PlatformKeyChainRepository::class);
        $this->subject = new PlatformJwksRepository();
        $this->subject->withJwksExporter($this->jwksExporter);
        $this->subject->setServiceLocator(
            $this->getServiceManagerMock(
                [
                    PlatformKeyChainRepository::SERVICE_ID => $this->keyChainRepository
                ]
            )
        );
    }

    public function testFind(): void
    {
        $keyChain = new KeyChain('id', 'name', new Key('123456'), new Key('654321'));
        $collection = new KeyChainCollection([$keyChain]);

        $this->keyChainRepository
            ->method('findAll')
            ->willReturn($collection);

        $this->jwksExporter
            ->method('export')
            ->willReturn(
                [
                    'kty' => 'kty',
                    'e' => 'e',
                    'n' => 'n',
                    'kid' => 'kid',
                    'alg' => 'alg',
                    'use' => 'use'
                ]
            );

        $this->assertSame(
            [
                'keys' => [
                    [
                        'kty' => 'kty',
                        'e' => 'e',
                        'n' => 'n',
                        'kid' => 'kid',
                        'alg' => 'alg',
                        'use' => 'use'
                    ]
                ]
            ],
            json_decode(
                json_encode(
                    $this->subject->find()->jsonSerialize()
                ),
                true
            )
        );
    }
}
