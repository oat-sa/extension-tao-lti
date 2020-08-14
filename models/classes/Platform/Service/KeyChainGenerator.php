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

namespace oat\taoLti\models\classes\Platform\Service;

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\security\Business\Domain\Key\Key;
use oat\tao\model\security\Business\Domain\Key\KeyChain;
use oat\taoLti\models\classes\Security\DataAccess\Repository\PlatformKeyChainRepository;

class KeyChainGenerator extends ConfigurableService implements KeyChainGeneratorInterface
{
    public function generate(): KeyChain
    {
        $resource = openssl_pkey_new($this->getOption(self::OPTION_DATA_STORE));
        openssl_pkey_export($resource, $privateKey);
        $publicKey = openssl_pkey_get_details($resource);

        return new KeyChain(
            PlatformKeyChainRepository::OPTION_DEFAULT_KEY_ID,
            PlatformKeyChainRepository::OPTION_DEFAULT_KEY_NAME,
            new Key($publicKey['key']),
            new Key($privateKey)
        );
    }
}
