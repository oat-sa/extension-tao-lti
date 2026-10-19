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
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoLti\models\classes\FeatureFlag;

use oat\oatbox\session\SessionService;
use oat\tao\model\featureFlag\FeatureFlagConfigHandlerInterface;
use oat\taoLti\models\classes\LtiLaunchData;
use oat\taoLti\models\classes\TaoLtiSession;

class WProofreaderClientConfigHandler implements FeatureFlagConfigHandlerInterface
{
    private const CLIENT_MODULE = 'tao/ckeditor/wproofreaderBootstrap';

    private SessionService $sessionService;

    public function __construct(SessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    public function __invoke(array $configs): array
    {
        $session = $this->sessionService->getCurrentSession();
        if (!$session instanceof TaoLtiSession) {
            return $configs;
        }

        $claim = $session->getLaunchData()->getCustomParameter(LtiLaunchData::LTI_SPELL_CHECK_CONFIG);
        if (!is_string($claim)) {
            return $configs;
        }

        $spellCheckConfig = json_decode($claim, true);
        if (!is_array($spellCheckConfig)) {
            return $configs;
        }

        $providerConfig = $spellCheckConfig['providerConfig'] ?? null;
        $serviceId = is_array($providerConfig) && is_string($providerConfig['serviceId'] ?? null)
            ? trim($providerConfig['serviceId'])
            : '';
        $srcUrl = is_array($providerConfig) && is_string($providerConfig['srcUrl'] ?? null)
            ? trim($providerConfig['srcUrl'])
            : '';

        if (
            ($spellCheckConfig['enabled'] ?? null) !== true
            || ($spellCheckConfig['providerId'] ?? null) !== 'wproofreader'
            || $serviceId === ''
            || ($srcUrl !== '' && !$this->isValidHttpsUrl($srcUrl))
        ) {
            return $configs;
        }

        $cleanProviderConfig = ['serviceId' => $serviceId];
        if ($srcUrl !== '') {
            $cleanProviderConfig['srcUrl'] = $srcUrl;
        }

        $configs[self::CLIENT_MODULE]['spellCheckConfig'] = [
            'enabled' => true,
            'providerId' => 'wproofreader',
            'providerConfig' => $cleanProviderConfig
        ];

        return $configs;
    }

    private function isValidHttpsUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false
            && parse_url($url, PHP_URL_SCHEME) === 'https';
    }
}
