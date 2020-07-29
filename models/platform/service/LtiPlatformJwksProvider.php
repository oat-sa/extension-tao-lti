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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA
 */

namespace oat\taoLti\models\platform\service;

use OAT\Library\Lti1p3Core\Security\Jwks\Exporter\JwksExporter;
use OAT\Library\Lti1p3Core\Security\Key\KeyChain;
use OAT\Library\Lti1p3Core\Security\Key\KeyChainRepository;
use oat\oatbox\service\ConfigurableService;

class LtiPlatformJwksProvider extends ConfigurableService
{
    public const LTI_PLATFORM_KEY_SET_NAME = 'ltiKeySet';

    public function getKeySet(): array
    {
        $keyChain = new KeyChain(
            self::LTI_PLATFORM_KEY_SET_NAME,
            self::LTI_PLATFORM_KEY_SET_NAME,
            $this->getLtiJwkProvider()
                ->getPublicKey()
        );

        $keyChainRepository = (new KeyChainRepository())->addKeyChain($keyChain);

        return (new JwksExporter($keyChainRepository))->export(self::LTI_PLATFORM_KEY_SET_NAME);
    }

    private function getLtiJwkProvider(): LtiPlatformJwkProvider
    {
        return $this->getServiceLocator()->get(LtiPlatformJwkProvider::class);
    }
}
