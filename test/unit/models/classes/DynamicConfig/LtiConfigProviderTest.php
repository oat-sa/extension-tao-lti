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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoLti\test\unit\models\classes\DynamicConfig;

use oat\oatbox\session\SessionService;
use oat\tao\model\DynamicConfig\DynamicConfigProviderInterface;
use oat\taoLti\models\classes\DynamicConfig\LtiConfigProvider;
use oat\taoLti\models\classes\LtiLaunchData;
use oat\taoLti\models\classes\TaoLtiSession;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class LtiConfigProviderTest extends TestCase
{
    public function testGetConfigByName(): void
    {
        $ltiLaunchData = $this->createMock(LtiLaunchData::class);
        $ltiLaunchData->method('getCustomParameter')
            ->will(
                $this->returnCallback(function ($param) {
                    switch ($param) {
                        case LtiLaunchData::LTI_TAO_LOGIN_URL:
                            return 'https://example.com/login';
                        case LtiLaunchData::LTI_REDIRECT_AFTER_LOGOUT_URL:
                            return 'https://example.com/logout';
                        default:
                            return null;
                    }
                })
            );

        $taoLtiSession = $this->createMock(TaoLtiSession::class);
        $taoLtiSession->method('getLaunchData')
            ->willReturn($ltiLaunchData);

        $session = $this->createMock(SessionService::class);
        $session->method('getCurrentSession')
            ->willReturn($taoLtiSession);

        $fallbackConfigProvider = $this->createMock(DynamicConfigProviderInterface::class);
        $fallbackConfigProvider->method('getConfigByName')
            ->willReturnMap([
                [LtiConfigProvider::LOGOUT_URL_CONFIG_NAME, 'https://fallback.com/logout'],
                [LtiConfigProvider::PORTAL_URL_CONFIG_NAME, null], // Simulating no value from fallback
                [LtiConfigProvider::LOGIN_URL_CONFIG_NAME, 'https://fallback.com/login'],
            ]);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('warning');

        $ltiConfigProvider = new LtiConfigProvider($fallbackConfigProvider, $session, $logger);

        // Test LTI-specific configurations
        $this->assertSame(
            'https://example.com/logout',
            $ltiConfigProvider->getConfigByName(LtiConfigProvider::LOGOUT_URL_CONFIG_NAME)
        );
        $this->assertSame(
            'https://example.com/login',
            $ltiConfigProvider->getConfigByName(LtiConfigProvider::LOGIN_URL_CONFIG_NAME)
        );
        $this->assertNull(
            $ltiConfigProvider->getConfigByName(LtiConfigProvider::PORTAL_URL_CONFIG_NAME)
        );
    }

    public function testHasConfig(): void
    {
        // Simulating LTI environment where portal URL is provided
        $ltiLaunchData = $this->createMock(LtiLaunchData::class);
        $ltiLaunchData->method('hasReturnUrl')->willReturn(true);

        $taoLtiSession = $this->createMock(TaoLtiSession::class);
        $taoLtiSession->method('getLaunchData')->willReturn($ltiLaunchData);

        $session = $this->createMock(SessionService::class);
        $session->method('getCurrentSession')->willReturn($taoLtiSession);

        $fallbackConfigProvider = $this->createMock(DynamicConfigProviderInterface::class);
        $fallbackConfigProvider->method('getConfigByName')->willReturnMap([
            [LtiConfigProvider::LOGOUT_URL_CONFIG_NAME, null],
            [LtiConfigProvider::PORTAL_URL_CONFIG_NAME, 'https://example.com/portal'],
            [LtiConfigProvider::LOGIN_URL_CONFIG_NAME, null],
        ]);

        $logger = $this->createMock(LoggerInterface::class);

        $ltiConfigProvider = new LtiConfigProvider($fallbackConfigProvider, $session, $logger);
        $this->assertTrue($ltiConfigProvider->hasConfig(DynamicConfigProviderInterface::PORTAL_URL_CONFIG_NAME));

        // Simulating non-LTI environment
        $session = $this->createMock(SessionService::class);
        $session->method('getCurrentSession')->willReturn(null);

        $fallbackConfigProvider = $this->createMock(DynamicConfigProviderInterface::class);
        $fallbackConfigProvider->method('getConfigByName')->willReturnMap([
            [LtiConfigProvider::LOGOUT_URL_CONFIG_NAME, null],
            [LtiConfigProvider::PORTAL_URL_CONFIG_NAME, null],
            [LtiConfigProvider::LOGIN_URL_CONFIG_NAME, null],
        ]);


        $ltiConfigProvider = new LtiConfigProvider($fallbackConfigProvider, $session, $logger);
        $this->assertFalse($ltiConfigProvider->hasConfig(DynamicConfigProviderInterface::PORTAL_URL_CONFIG_NAME));
    }
}
