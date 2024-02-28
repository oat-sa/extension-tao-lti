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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA ;
 */

declare(strict_types=1);

namespace oat\taoLti\scripts\tools;

use oat\oatbox\extension\script\ScriptAction;
use oat\oatbox\reporting\Report;
use oat\taoLti\models\classes\Security\DataAccess\Repository\PlatformKeyChainRepository;
use oat\taoLti\models\classes\Platform\Service\CachedKeyChainGenerator;

/**
 * usage `sudo -u www-data php index.php 'oat\taoLti\scripts\tools\GenerateKeys'
 *     -id 10_0 -kn Tenant_10 -pkp /platform/default/public_10.key -kp /platform/default/private_10.key`
 */
class GenerateKeys extends ScriptAction
{
    protected function provideOptions()
    {
        return [
            'key_id' => [
                'prefix' => 'id',
                'longPrefix' => 'key_id',
                'description' => 'Lti Platform key chain id',
                'required' => true,
                'cast' => 'string'
            ],
            'key_name' => [
                'prefix' => 'kn',
                'longPrefix' => 'key_name',
                'description' => 'Lti Platform key chain name',
                'required' => true,
                'cast' => 'string'
            ],
            'public_key_path' => [
                'prefix' => 'pkp',
                'longPrefix' => 'public_key_path',
                'description' => 'Lti Platform public key path',
                'required' => true,
                'cast' => 'string'
            ],
            'private_key_path' => [
                'prefix' => 'kp',
                'longPrefix' => 'private_key_path',
                'description' => 'Lti Platform private key path',
                'required' => true,
                'cast' => 'string'
            ],
        ];
    }

    protected function provideDescription()
    {
        return 'Script to create a LTI Platform Key Chain';
    }

    protected function provideUsage()
    {
        return [
            'prefix' => 'h',
            'longPrefix' => 'help',
            'description' => 'Prints the help.'
        ];
    }

    protected function run()
    {
        $keyId = $this->getOption('key_id');
        $keyName = $this->getOption('key_name');
        $publicKeyPath = $this->getOption('public_key_path');
        $privateKeyPath = $this->getOption('private_key_path');

        if (empty($keyId) || empty($keyName) || empty($publicKeyPath) || empty($privateKeyPath)) {
            return Report::createError(
                'Not all required arguments were provided. Try to run the script with -h option'
            );
        }
        $platformKeyChainRepository = $this->getServiceLocator()->get(PlatformKeyChainRepository::SERVICE_ID);
        $options = $platformKeyChainRepository->getOptions();
        $options[] = [
            PlatformKeyChainRepository::OPTION_DEFAULT_KEY_ID => $keyId,
            PlatformKeyChainRepository::OPTION_DEFAULT_KEY_NAME => $keyName,
            PlatformKeyChainRepository::OPTION_DEFAULT_PUBLIC_KEY_PATH => $publicKeyPath,
            PlatformKeyChainRepository::OPTION_DEFAULT_PRIVATE_KEY_PATH => $privateKeyPath,
        ];
        $platformKeyChainRepository->setOptions($options);
        $this->getServiceLocator()->register(PlatformKeyChainRepository::SERVICE_ID, $platformKeyChainRepository);

        /** @var CachedKeyChainGenerator $cachedKeyChainGenerator */
        $cachedKeyChainGenerator = $this->getServiceLocator()->get(CachedKeyChainGenerator::class);
        $cachedKeyChainGenerator->generate($keyId, $keyName);

        return Report::createSuccess('LTI Platform Key Chain generated successfully!');
    }
}
