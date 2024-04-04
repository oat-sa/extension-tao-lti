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

namespace oat\taoLti\models\classes\DynamicConfig;

use oat\oatbox\session\SessionService;
use oat\tao\model\DynamicConfig\DynamicConfigProviderInterface;
use oat\taoLti\models\classes\LtiLaunchData;
use oat\taoLti\models\classes\TaoLtiSession;
use Psr\Log\LoggerInterface;
use Throwable;

class LtiConfigProvider implements DynamicConfigProviderInterface
{
    private DynamicConfigProviderInterface $configFallback;

    private SessionService $session;
    private LoggerInterface $logger;

    public function __construct(
        DynamicConfigProviderInterface $configFallback,
        SessionService $session,
        LoggerInterface $logger
    ) {
        $this->configFallback = $configFallback;
        $this->session = $session;
        $this->logger = $logger;
    }

    public function getConfigByName(string $name): ?string
    {
        return $this->getConfigByLtiClaimName($name) ?? $this->configFallback->getConfigByName($name);
    }

    public function hasConfig(string $name): bool
    {
        return $this->getConfigByName($name) !== null;
    }

    private function getConfigByLtiClaimName(string $name): ?string
    {
        $currentSession = $this->session->getCurrentSession();

        if (!$currentSession instanceof TaoLtiSession) {
            return null;
        }

        $ltiLaunchData = $currentSession->getLaunchData();

        if ($name === self::LOGIN_URL_CONFIG_NAME) {
            return $ltiLaunchData->getCustomParameter(LtiLaunchData::LTI_TAO_LOGIN_URL);
        }
        if ($name === self::LOGOUT_URL_CONFIG_NAME) {
            return $ltiLaunchData->getCustomParameter(LtiLaunchData::LTI_REDIRECT_AFTER_LOGOUT_URL);
        }

        try {
            if ($name === self::PLATFORM_URL_CONFIG_NAME && $ltiLaunchData->hasReturnUrl()) {
                return $ltiLaunchData->getReturnUrl();
            }
        } catch (Throwable $exception) {
            $this->logger->warning(
                sprintf('It was not possible to recover return url claim. Exception: %s', $exception)
            );
        }

        return null;
    }
}
