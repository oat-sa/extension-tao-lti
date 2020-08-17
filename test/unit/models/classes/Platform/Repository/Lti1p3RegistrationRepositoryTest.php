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

namespace oat\taoLti\test\unit\models\classes\Platform\Repository;

use oat\generis\test\TestCase;
use OAT\Library\Lti1p3Core\Registration\Registration;
use oat\tao\model\security\Business\Contract\KeyChainRepositoryInterface;
use oat\tao\model\security\Business\Domain\Key\Key;
use oat\tao\model\security\Business\Domain\Key\KeyChain;
use oat\tao\model\security\Business\Domain\Key\KeyChainCollection;
use oat\taoLti\models\classes\Platform\Repository\Lti1p3RegistrationRepository;
use oat\taoLti\models\classes\Security\DataAccess\Repository\CachedPlatformKeyChainRepository;
use oat\taoLti\models\classes\Security\DataAccess\Repository\ToolKeyChainRepository;
use PHPUnit\Framework\MockObject\MockObject;

class Lti1p3RegistrationRepositoryTest extends TestCase
{
    /** @var Lti1p3RegistrationRepository */
    private $subject;

    /** @var KeyChainRepositoryInterface|MockObject */
    private $toolKeyChainRepository;

    /** @var KeyChainRepositoryInterface|MockObject */
    private $platformKeyChainRepository;

    /** @var KeyChain */
    private $platformKeyChain;

    /** @var KeyChain */
    private $toolKeyChain;

    public function setUp(): void
    {
        $this->platformKeyChain = new KeyChain(
            'id_platform',
            'name_platform',
            new Key('platform_public_key'),
            new Key('platform_private_key')
        );
        $this->toolKeyChain = new KeyChain(
            'id_tool',
            'name_tool',
            new Key('tool_public_key'),
            new Key('tool_private_key')
        );
        $this->toolKeyChainRepository = $this->createMock(KeyChainRepositoryInterface::class);
        $this->platformKeyChainRepository = $this->createMock(CachedPlatformKeyChainRepository::class);
        $this->subject = new Lti1p3RegistrationRepository();
        $this->subject->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    ToolKeyChainRepository::class => $this->toolKeyChainRepository,
                    CachedPlatformKeyChainRepository::class => $this->platformKeyChainRepository
                ]
            )
        );
    }

    public function testFindWillReturnRegistrationForTool(): void
    {
        $this->expectToolAndPlatformKeys([$this->toolKeyChain], [$this->platformKeyChain]);

        $registration = $this->subject->find('registrationId');

        $this->assertInstanceOf(Registration::class, $registration);

        $this->assertSame('tao', $registration->getPlatform()->getIdentifier());
        $this->assertSame('tao', $registration->getPlatform()->getName());
        $this->assertSame(rtrim(ROOT_URL, '/'), $registration->getPlatform()->getAudience());
        $this->assertSame(ROOT_URL . 'taoLti/Security/oidc', $registration->getPlatform()->getOidcAuthenticationUrl());

        $this->assertSame($this->platformKeyChain->getIdentifier(), $registration->getPlatformKeyChain()->getIdentifier());
        $this->assertSame($this->platformKeyChain->getName(), $registration->getPlatformKeyChain()->getKeySetName());
        $this->assertSame($this->platformKeyChain->getPublicKey()->getValue(), $registration->getPlatformKeyChain()->getPublicKey()->getContent());
        $this->assertSame($this->platformKeyChain->getPrivateKey()->getValue(), $registration->getPlatformKeyChain()->getPrivateKey()->getContent());

        $this->assertSame($this->toolKeyChain->getIdentifier(), $registration->getToolKeyChain()->getIdentifier());
        $this->assertSame($this->toolKeyChain->getName(), $registration->getToolKeyChain()->getKeySetName());
        $this->assertSame($this->toolKeyChain->getPublicKey()->getValue(), $registration->getToolKeyChain()->getPublicKey()->getContent());
        $this->assertSame($this->toolKeyChain->getPrivateKey()->getValue(), $registration->getToolKeyChain()->getPrivateKey()->getContent());

        #
        # @TODO Assert as soon as we have these values coming from provider
        #
        $this->assertSame('client_id', $registration->getClientId());
        $this->assertSame('registrationIdentifier', $registration->getIdentifier());
        $this->assertSame(['1'], $registration->getDeploymentIds());
        $this->assertSame('1', $registration->getDefaultDeploymentId());
        $this->assertSame(null, $registration->getToolJwksUrl());

        #
        # @TODO Assert as soon as we have these values coming from provider
        #
        $this->assertSame('local_demo', $registration->getTool()->getIdentifier());
        $this->assertSame('local_demo', $registration->getTool()->getName());
        $this->assertSame('http://localhost:8888/tool', $registration->getTool()->getAudience());
        $this->assertSame('http://localhost:8888/tool/launch', $registration->getTool()->getLaunchUrl());
        $this->assertSame('http://localhost:8888/lti1p3/oidc/login-initiation', $registration->getTool()->getOidcLoginInitiationUrl());
    }

    public function testFindWillReturnNullIfNoKeysFound(): void
    {
        $this->expectToolAndPlatformKeys([], []);

        $this->assertNull($this->subject->find('registrationId'));
    }

    private function expectToolAndPlatformKeys(array $toolKeyChains, array $platformKeyChains): void
    {
        $this->toolKeyChainRepository
            ->method('findAll')
            ->willReturn(new KeyChainCollection(...$toolKeyChains));

        $this->platformKeyChainRepository
            ->method('findAll')
            ->willReturn(new KeyChainCollection(...$platformKeyChains));
    }
}
