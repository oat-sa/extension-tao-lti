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
use oat\taoLti\models\classes\LtiProvider\LtiProvider;
use oat\taoLti\models\classes\LtiProvider\LtiProviderService;
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

    /** @var LtiProviderService|MockObject */
    private $ltiProviderService;

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
        $this->ltiProviderService = $this->createMock(LtiProviderService::class);
        $this->toolKeyChainRepository = $this->createMock(KeyChainRepositoryInterface::class);
        $this->platformKeyChainRepository = $this->createMock(KeyChainRepositoryInterface::class);
        $this->subject = new Lti1p3RegistrationRepository([Lti1p3RegistrationRepository::OPTION_ROOT_URL => 'ROOT_URL']);
        $this->subject->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    ToolKeyChainRepository::class => $this->toolKeyChainRepository,
                    CachedPlatformKeyChainRepository::class => $this->platformKeyChainRepository,
                    LtiProviderService::SERVICE_ID => $this->ltiProviderService
                ]
            )
        );
    }

    public function testFindWithNoLtiProvider(): void
    {
        $this->ltiProviderService
            ->method('searchById')
            ->willReturn(null);
        
        $this->assertNull($this->subject->find('id'));
    }

    public function testFindWillReturnRegistrationForTool(): void
    {
        $this->expectsProvider();

        $this->expectToolAndPlatformKeys([$this->toolKeyChain], [$this->platformKeyChain]);

        $registration = $this->subject->find('ltiId');

        $this->assertInstanceOf(Registration::class, $registration);

        $this->assertSame('tao', $registration->getPlatform()->getIdentifier());
        $this->assertSame('tao', $registration->getPlatform()->getName());
        $this->assertSame(rtrim('ROOT_URL', '/'), $registration->getPlatform()->getAudience());
        $this->assertSame('ROOT_URL' . 'taoLti/Security/oidc', $registration->getPlatform()->getOidcAuthenticationUrl());
        $this->assertSame('ROOT_URL' . 'taoLti/Security/jwks', $registration->getPlatformJwksUrl());

        $this->assertSame($this->platformKeyChain->getIdentifier(), $registration->getPlatformKeyChain()->getIdentifier());
        $this->assertSame($this->platformKeyChain->getName(), $registration->getPlatformKeyChain()->getKeySetName());
        $this->assertSame($this->platformKeyChain->getPublicKey()->getValue(), $registration->getPlatformKeyChain()->getPublicKey()->getContent());
        $this->assertSame($this->platformKeyChain->getPrivateKey()->getValue(), $registration->getPlatformKeyChain()->getPrivateKey()->getContent());

        $this->assertSame($this->toolKeyChain->getIdentifier(), $registration->getToolKeyChain()->getIdentifier());
        $this->assertSame($this->toolKeyChain->getName(), $registration->getToolKeyChain()->getKeySetName());
        $this->assertSame($this->toolKeyChain->getPublicKey()->getValue(), $registration->getToolKeyChain()->getPublicKey()->getContent());
        $this->assertSame($this->toolKeyChain->getPrivateKey()->getValue(), $registration->getToolKeyChain()->getPrivateKey()->getContent());

        $this->assertSame('client_id', $registration->getClientId());
        $this->assertSame('ltiId', $registration->getIdentifier());
        $this->assertSame(['1'], $registration->getDeploymentIds());
        $this->assertSame('1', $registration->getDefaultDeploymentId());
        $this->assertSame(null, $registration->getToolJwksUrl());

        $this->assertSame('toolIdentifier', $registration->getTool()->getIdentifier());
        $this->assertSame('toolName', $registration->getTool()->getName());
        $this->assertSame('audience', $registration->getTool()->getAudience());
        $this->assertSame('launch_url', $registration->getTool()->getLaunchUrl());
        $this->assertSame('oidc_url', $registration->getTool()->getOidcInitiationUrl());
    }

    public function testFindWillReturnRegistrationForToolWithoutToolKeyChain(): void
    {
        $this->expectsProvider();

        $this->expectToolAndPlatformKeys([], [$this->platformKeyChain]);

        $registration = $this->subject->find('ltiId');

        $this->assertInstanceOf(Registration::class, $registration);
        $this->assertNull($registration->getToolKeyChain());
    }

    public function testFindWillReturnRegistrationForToolWithoutToolJwksUrl(): void
    {
        $ltiProvider = $this->createMock(LtiProvider::class);
        $ltiProvider->method('getToolJwksUrl')
            ->willReturn('not-empty');

        $this->ltiProviderService
            ->method('searchById')
            ->willReturn($ltiProvider);

        $this->expectToolAndPlatformKeys([$this->toolKeyChain], [$this->platformKeyChain]);

        $registration = $this->subject->find('ltiId');

        $this->assertInstanceOf(Registration::class, $registration);
        $this->assertNull($registration->getToolKeyChain());
    }

    public function testFindWillNullWhenNoPlatformIsReturned(): void
    {
        $this->expectToolAndPlatformKeys([$this->toolKeyChain], []);

        $this->assertNull($this->subject->find('ltiId'));
    }

    public function testFindWillReturnNullIfNoKeysFound(): void
    {
        $this->expectsProvider();
        $this->expectToolAndPlatformKeys([], []);

        $this->assertNull($this->subject->find('registrationId'));
    }

    private function expectsProvider(): LtiProvider
    {
        $ltiProvider = $this->createMock(LtiProvider::class);

        $ltiProvider->method('getId')
            ->willReturn('ltiId');

        $ltiProvider->method('getToolName')
            ->willReturn('toolName');

        $ltiProvider->method('getToolIdentifier')
            ->willReturn('toolIdentifier');

        $ltiProvider->method('getToolPublicKey')
            ->willReturn('key');

        $ltiProvider->method('getToolAudience')
            ->willReturn('audience');

        $ltiProvider->method('getToolOidcLoginInitiationUrl')
            ->willReturn('oidc_url');

        $ltiProvider->method('getToolLaunchUrl')
            ->willReturn('launch_url');

        $ltiProvider->method('getToolClientId')
            ->willReturn('client_id');

        $ltiProvider->method('getToolDeploymentIds')
            ->willReturn(['1']);

        $this->ltiProviderService
            ->method('searchById')
            ->willReturn($ltiProvider);

        return $ltiProvider;
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
