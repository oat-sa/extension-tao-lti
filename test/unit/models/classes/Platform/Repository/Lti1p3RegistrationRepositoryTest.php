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

use oat\generis\test\ServiceManagerMockTrait;
use OAT\Library\Lti1p3Core\Registration\Registration;
use OAT\Library\Lti1p3Core\Security\Key\Key;
use OAT\Library\Lti1p3Core\Security\Key\KeyChain;
use OAT\Library\Lti1p3Core\Security\Key\KeyChainInterface;
use OAT\Library\Lti1p3Core\Security\Key\KeyChainRepositoryInterface;
use oat\taoLti\models\classes\LtiProvider\LtiProvider;
use oat\taoLti\models\classes\LtiProvider\LtiProviderService;
use oat\taoLti\models\classes\Platform\LtiPlatformRegistration;
use oat\taoLti\models\classes\Platform\Repository\Lti1p3RegistrationRepository;
use oat\taoLti\models\classes\Platform\Repository\LtiPlatformRepositoryInterface;
use oat\taoLti\models\classes\Security\DataAccess\Repository\CachedPlatformKeyChainRepository;
use oat\taoLti\models\classes\Security\DataAccess\Repository\PlatformKeyChainRepository;
use oat\taoLti\models\classes\Security\DataAccess\Repository\ToolKeyChainRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class Lti1p3RegistrationRepositoryTest extends TestCase
{
    use ServiceManagerMockTrait;

    /** @var Lti1p3RegistrationRepository */
    private Lti1p3RegistrationRepository $subject;

    /** @var KeyChainRepositoryInterface|MockObject */
    private KeyChainRepositoryInterface $toolKeyChainRepository;

    /** @var KeyChainRepositoryInterface|MockObject */
    private KeyChainRepositoryInterface $cachedPlatformKeyChainRepository;

    /** @var LtiProviderService|MockObject */
    private LtiProviderService $ltiProviderService;

    /** @var LtiPlatformRepositoryInterface|MockObject */
    private LtiPlatformRepositoryInterface $ltiPlatformRepository;

    /** @var KeyChain */
    private KeyChain $platformKeyChain;

    /** @var KeyChain */
    private KeyChain $toolKeyChain;

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
        $this->ltiPlatformRepository = $this->createMock(LtiPlatformRepositoryInterface::class);
        $this->toolKeyChainRepository = $this->createMock(KeyChainRepositoryInterface::class);
        $this->cachedPlatformKeyChainRepository = $this->createMock(KeyChainRepositoryInterface::class);
        $platformKeyChainRepository = $this->createMock(PlatformKeyChainRepository::class);
        $this->subject = new Lti1p3RegistrationRepository(
            [Lti1p3RegistrationRepository::OPTION_ROOT_URL => 'ROOT_URL']
        );
        $this->subject->setServiceLocator(
            $this->getServiceManagerMock(
                [
                    ToolKeyChainRepository::class => $this->toolKeyChainRepository,
                    CachedPlatformKeyChainRepository::class => $this->cachedPlatformKeyChainRepository,
                    PlatformKeyChainRepository::class => $platformKeyChainRepository,
                    LtiProviderService::SERVICE_ID => $this->ltiProviderService,
                    LtiPlatformRepositoryInterface::SERVICE_ID => $this->ltiPlatformRepository,
                ]
            )
        );
    }

    public function testFindWithNoLtiProviderNorPlatform(): void
    {
        $this->ltiProviderService
            ->method('searchById')
            ->willReturn(null);

        $this->ltiPlatformRepository
            ->method('searchById')
            ->willReturn(null);

        $this->assertNull($this->subject->find('id'));
    }

    public function testItCreatesPlatformRegistration(): void
    {
        $this->expectToolAndPlatformKeys($this->toolKeyChain, $this->platformKeyChain);
        $this->ltiProviderService
            ->method('searchById')
            ->willReturn(null);

        $platform = new LtiPlatformRegistration(
            'id',
            'label',
            'audience',
            'http://oauth.aceess/token.url',
            'http://oidc.auth.url',
            'http://jwks.url',
            'clientId',
            'deploymentId'
        );

        $this->ltiPlatformRepository
            ->method('searchById')
            ->willReturn($platform);

        $registration = $this->subject->find('id');
        $this->assertInstanceOf(Registration::class, $registration);
        $this->assertSame($platform->getIdentifier(), $registration->getPlatform()->getIdentifier());
        $this->assertSame($platform->getClientId(), $registration->getClientId());
        $this->assertSame($platform->getAudience(), $registration->getPlatform()->getAudience());
        $this->assertSame([$platform->getDeploymentId()], $registration->getDeploymentIds());
        $this->assertSame($platform->getJwksUrl(), $registration->getPlatformJwksUrl());
        $this->assertSame(
            $platform->getOidcAuthenticationUrl(),
            $registration->getPlatform()->getOidcAuthenticationUrl()
        );
        $this->assertSame(
            $platform->getOAuth2AccessTokenUrl(),
            $registration->getPlatform()->getOAuth2AccessTokenUrl()
        );
    }

    public function testFindWillReturnRegistrationForTool(): void
    {
        $this->expectsProvider();

        $this->expectToolAndPlatformKeys($this->toolKeyChain, $this->platformKeyChain);

        $registration = $this->subject->find('ltiId');

        $this->assertInstanceOf(Registration::class, $registration);

        $this->assertSame('tao', $registration->getPlatform()->getIdentifier());
        $this->assertSame('tao', $registration->getPlatform()->getName());
        $this->assertSame(rtrim('ROOT_URL', '/'), $registration->getPlatform()->getAudience());
        $this->assertSame(
            'ROOT_URL' . 'taoLti/Security/oidc',
            $registration->getPlatform()->getOidcAuthenticationUrl()
        );
        $this->assertSame('ROOT_URL' . 'taoLti/Security/jwks', $registration->getPlatformJwksUrl());

        $this->assertSame(
            $this->platformKeyChain->getIdentifier(),
            $registration->getPlatformKeyChain()->getIdentifier()
        );
        $this->assertSame(
            $this->platformKeyChain->getKeySetName(),
            $registration->getPlatformKeyChain()->getKeySetName()
        );

        $this->assertSame(
            $this->platformKeyChain->getPublicKey()->getContent(),
            $registration->getPlatformKeyChain()->getPublicKey()->getContent()
        );
        $this->assertSame(
            $this->platformKeyChain->getPrivateKey()->getContent(),
            $registration->getPlatformKeyChain()->getPrivateKey()->getContent()
        );

        $this->assertSame($this->toolKeyChain->getIdentifier(), $registration->getToolKeyChain()->getIdentifier());
        $this->assertSame($this->toolKeyChain->getKeySetName(), $registration->getToolKeyChain()->getKeySetName());
        $this->assertSame(
            $this->toolKeyChain->getPublicKey()->getContent(),
            $registration->getToolKeyChain()->getPublicKey()->getContent()
        );
        $this->assertSame(
            $this->toolKeyChain->getPrivateKey()->getContent(),
            $registration->getToolKeyChain()->getPrivateKey()->getContent()
        );

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

        $this->expectToolAndPlatformKeys(null, $this->platformKeyChain);

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

        $this->expectToolAndPlatformKeys($this->toolKeyChain, $this->platformKeyChain);

        $registration = $this->subject->find('ltiId');

        $this->assertInstanceOf(Registration::class, $registration);
        $this->assertEquals('not-empty', $registration->getToolJwksUrl());
    }

    public function testFindWillNullWhenNoPlatformIsReturned(): void
    {
        $this->expectToolAndPlatformKeys($this->toolKeyChain, null);

        $this->assertNull($this->subject->find('ltiId'));
    }

    public function testFindWillReturnNullIfNoKeysFound(): void
    {
        $this->expectsProvider();
        $this->expectToolAndPlatformKeys(null, null);

        $this->assertNull($this->subject->find('registrationId'));
    }

    public function testFindAllAggregatesProvidersAndPlatforms(): void
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
            ->method('findAll')
            ->willReturn([$ltiProvider]);

        $this->expectToolAndPlatformKeys($this->toolKeyChain, $this->platformKeyChain);

        $platform = new LtiPlatformRegistration(
            'id',
            'label',
            'audience',
            'http://oauth.aceess/token.url',
            'http://oidc.auth.url',
            'http://jwks.url',
            'clientId',
            'deploymentId'
        );

        $this->ltiPlatformRepository
            ->method('findAll')
            ->willReturn([$platform]);

        $providersAndRegistrations = $this->subject->findAll();

        $this->assertIsArray($providersAndRegistrations);
        $this->assertCount(2, $providersAndRegistrations);
        $this->assertInstanceOf(Registration::class, $providersAndRegistrations[0]);
        $this->assertInstanceOf(Registration::class, $providersAndRegistrations[1]);
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

    private function expectToolAndPlatformKeys(
        ?KeyChainInterface $toolKeyChain,
        ?KeyChainInterface $platformKeyChain
    ): void {
        $this->toolKeyChainRepository
            ->method('find')
            ->willReturn($toolKeyChain);

        $this->cachedPlatformKeyChainRepository
            ->method('find')
            ->willReturn($platformKeyChain);
    }
}
