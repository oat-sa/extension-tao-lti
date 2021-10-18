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

namespace oat\taoLti\models\classes\Platform\Repository;

use OAT\Library\Lti1p3Core\Platform\Platform;
use OAT\Library\Lti1p3Core\Registration\Registration;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use OAT\Library\Lti1p3Core\Registration\RegistrationRepositoryInterface;
use OAT\Library\Lti1p3Core\Security\Key\KeyChainRepositoryInterface;
use OAT\Library\Lti1p3Core\Tool\Tool;
use oat\oatbox\service\ConfigurableService;
use oat\taoLti\models\classes\LtiProvider\LtiProvider;
use oat\taoLti\models\classes\LtiProvider\LtiProviderService;
use oat\taoLti\models\classes\Platform\LtiPlatformRegistration;
use oat\taoLti\models\classes\Security\DataAccess\Repository\CachedPlatformKeyChainRepository;
use oat\taoLti\models\classes\Security\DataAccess\Repository\PlatformKeyChainRepository;
use oat\taoLti\models\classes\Security\DataAccess\Repository\ToolKeyChainRepository;

class Lti1p3RegistrationRepository extends ConfigurableService implements RegistrationRepositoryInterface
{
    public const SERVICE_ID = 'taoLti/Lti1p3RegistrationRepository';
    public const OPTION_ROOT_URL = 'rootUrl';
    private const PLATFORM_ID = 'tao';
    private const TOOL_ID = 'tao_tool';
    private const OIDC_PATH = 'taoLti/Security/oidc';
    private const OAUTH_PATH = 'taoLti/Security/oauth';
    private const JWKS_PATH = 'taoLti/Security/jwks';

    public function find(string $identifier): ?RegistrationInterface
    {
        $ltiProvider = $this->getLtiProviderService()->searchById($identifier);

        if (!$ltiProvider) {
            $ltiPlatform = $this->getLtiPlatformService()->searchById($identifier);
            if ($ltiPlatform) {
                return $this->createRegistrationByPlatform($ltiPlatform);
            } else {
                return null;
            }
        }

        return $this->createRegistrationByProvider($ltiProvider);
    }

    public function findAll(): array
    {
        $registrations = [];

        foreach ($this->getLtiProviderService()->findAll() as $ltiProvider) {
            $registrations[] = $this->createRegistrationByProvider($ltiProvider);
        }
        foreach ($this->getLtiPlatformService()->findAll() as $ltiPlatform) {
            $registrations[] = $this->createRegistrationByPlatform($ltiPlatform);
        }

        return $registrations;
    }

    public function findByClientId(string $clientId): ?RegistrationInterface
    {
        $ltiProvider = $this->getLtiProviderService()->searchByToolClientId($clientId);

        if (!$ltiProvider) {
            $ltiPlatform = $this->getLtiPlatformService()->searchByClientId($clientId);
            if ($ltiPlatform) {
                return $this->createRegistrationByPlatform($ltiPlatform);
            } else {
                return null;
            }
        }

        return $this->createRegistrationByProvider($ltiProvider);
    }

    public function findByPlatformIssuer(string $issuer, string $clientId = null): ?RegistrationInterface
    {
        $platform = $this->getLtiPlatformService()->searchByIssuer($issuer, $clientId);
        if (!$platform) {
            return null;
        }
        return $this->createRegistrationByPlatform($platform);
    }

    public function findByToolIssuer(string $issuer, string $clientId = null): ?RegistrationInterface
    {
        $provider = $this->getLtiProviderService()->searchByIssuer($issuer, $clientId);
        if (!$provider) {
            return null;
        }
        return $this->createRegistrationByProvider($provider);
    }

    private function getTool(LtiProvider $ltiProvider): Tool
    {
        return new Tool(
            $ltiProvider->getToolIdentifier(),
            $ltiProvider->getToolName(),
            $ltiProvider->getToolAudience(),
            $ltiProvider->getToolOidcLoginInitiationUrl(),
            $ltiProvider->getToolLaunchUrl()
        );
    }

    private function getDefaultPlatform(): Platform
    {
        return new Platform(
            self::PLATFORM_ID,
            self::PLATFORM_ID,
            rtrim($this->getOption(self::OPTION_ROOT_URL), '/'),
            $this->getOption(self::OPTION_ROOT_URL) . self::OIDC_PATH,
            $this->getOption(self::OPTION_ROOT_URL) . self::OAUTH_PATH
        );
    }

    private function getDefaultTool(): Tool
    {
        return new Tool(
            self::TOOL_ID,
            self::TOOL_ID,
            rtrim($this->getOption(self::OPTION_ROOT_URL), '/'),
            $this->getOption(self::OPTION_ROOT_URL) . self::OIDC_PATH
        );
    }

    private function createRegistrationByProvider(LtiProvider $ltiProvider): ?Registration
    {
        $toolKeyChain = $this->getToolKeyChainRepository()->find($ltiProvider->getId());

        $platformKeyChain = $this->getCachedPlatformKeyChainRepository()->find($ltiProvider->getId());

        if ($platformKeyChain === null) {
            return null;
        }

        return new Registration(
            $ltiProvider->getId(),
            $ltiProvider->getToolClientId(),
            $this->getDefaultPlatform(),
            $this->getTool($ltiProvider),
            $ltiProvider->getToolDeploymentIds(),
            $platformKeyChain,
            $toolKeyChain,
            $this->getOption(self::OPTION_ROOT_URL) . self::JWKS_PATH,
            $ltiProvider->getToolJwksUrl()
        );
    }

    private function createRegistrationByPlatform(LtiPlatformRegistration $ltiPlatform): ?Registration
    {
        // use platform key chain
        $toolKeyChain = $this->getCachedPlatformKeyChainRepository()
            ->find($this->getPlatformKeyChainRepository()->getDefaultKeyId());

        $platform = new Platform($ltiPlatform->getIdentifier(), $ltiPlatform->getIdentifier(), $ltiPlatform->getAudience(),
            $ltiPlatform->getOidcAuthenticationUrl(), $ltiPlatform->getOAuth2AccessTokenUrl());

        return new Registration(
            $ltiPlatform->getIdentifier(),
            $ltiPlatform->getClientId(),
            $platform,
            $this->getDefaultTool(),
            [$ltiPlatform->getDeploymentId()],
            null,
            $toolKeyChain,
            $ltiPlatform->getJwksUrl(),
            $this->getOption(self::OPTION_ROOT_URL) . self::JWKS_PATH
        );
    }

    private function getLtiProviderService(): LtiProviderService
    {
        return $this->getServiceLocator()->get(LtiProviderService::SERVICE_ID);
    }

    private function getLtiPlatformService(): LtiPlatformRepositoryInterface
    {
        return $this->getServiceLocator()->get(LtiPlatformRepositoryInterface::SERVICE_ID);
    }

    private function getToolKeyChainRepository(): KeyChainRepositoryInterface
    {
        return $this->getServiceLocator()->get(ToolKeyChainRepository::class);
    }

    private function getCachedPlatformKeyChainRepository(): KeyChainRepositoryInterface
    {
        return $this->getServiceLocator()->get(CachedPlatformKeyChainRepository::class);
    }

    private function getPlatformKeyChainRepository(): PlatformKeyChainRepository
    {
        return $this->getServiceLocator()->get(PlatformKeyChainRepository::class);
    }
}
