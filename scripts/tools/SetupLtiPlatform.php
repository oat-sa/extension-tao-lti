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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA ;
 */

declare(strict_types=1);

namespace oat\taoLti\scripts\tools;

use core_kernel_classes_Resource;
use oat\generis\model\OntologyRdfs;
use oat\oatbox\extension\script\ScriptAction;
use oat\oatbox\reporting\Report;
use oat\taoLti\controller\PlatformAdmin;
use oat\taoLti\models\classes\Platform\Repository\RdfLtiPlatformRepository;

/**
 * usage `sudo -u www-data php index.php 'oat\taoLti\scripts\tools\SetupLtiPlatform' -l label -cid client_id -a audience -tu token_url -ou oidc_url -ju jwks_url`
 */
class SetupLtiPlatform extends ScriptAction
{
    protected function provideOptions()
    {
        return [
            'label' => [
                'prefix' => 'l',
                'longPrefix' => 'label',
                'description' => 'Lti Platform label',
                'required' => true,
                'cast' => 'string'
            ],
            'client_id' => [
                'prefix' => 'cid',
                'longPrefix' => 'client_id',
                'description' => 'Lti Platform client ID',
                'required' => false,
                'cast' => 'string'
            ],
            'deployment_id' => [
                'prefix' => 'did',
                'longPrefix' => 'deployment_id',
                'description' => 'Lti Platform deployment ID',
                'required' => false,
                'cast' => 'string'
            ],
            'audience' => [
                'prefix' => 'a',
                'longPrefix' => 'audience',
                'description' => 'Lti Platform audience',
                'required' => true,
                'cast' => 'string'
            ],
            'token_url' => [
                'prefix' => 'tu',
                'longPrefix' => 'token_url',
                'description' => 'Lti Platform token url',
                'required' => true,
                'cast' => 'string'
            ],
            'oidc_url' => [
                'prefix' => 'ou',
                'longPrefix' => 'oidc_url',
                'description' => 'Lti Platform OIDC URL',
                'required' => true,
                'cast' => 'string'
            ],
            'jwks_url' => [
                'prefix' => 'ju',
                'longPrefix' => 'jwks_url',
                'description' => 'Lti Platform JWKS URL',
                'required' => true,
                'cast' => 'string'
            ],
        ];
    }

    protected function provideDescription()
    {
        return 'Script to create a LTI platform';
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
        $label = $this->getOption('label');
        $clientId = $this->getOption('client_id');
        $deploymentId = $this->getOption('deployment_id');
        $audience = $this->getOption('audience');
        $tokenUrl = $this->getOption('token_url');
        $oidcUrl = $this->getOption('oidc_url');
        $jwksUrl = $this->getOption('jwks_url');

        if (empty($label) || empty($audience) || empty($tokenUrl) || empty($jwksUrl)) {
            return Report::createError('Not all arguments were provided. Try to run the script with -h option');
        }

        $registrationController = $this->getRegistrationController();
        $registrationController->createInstance(
            [$registrationController->getClass(RdfLtiPlatformRepository::CLASS_URI)],
            [
                OntologyRdfs::RDFS_LABEL => $label,
                RdfLtiPlatformRepository::LTI_PLATFORM_CLIENT_ID => $clientId,
                RdfLtiPlatformRepository::LTI_PLATFORM_DEPLOYMENT_ID => $deploymentId,
                RdfLtiPlatformRepository::LTI_PLATFORM_AUDIENCE => $audience,
                RdfLtiPlatformRepository::LTI_PLATFORM_OAUTH2_ACCESS_TOKEN_URL => $tokenUrl,
                RdfLtiPlatformRepository::LTI_PLATFORM_OIDC_URL => $oidcUrl,
                RdfLtiPlatformRepository::LTI_PLATFORM_JWKS_URL => $jwksUrl,
            ]
        );

        return Report::createSuccess('Lti Platform registered successfully!');
    }

    private function getRegistrationController(): PlatformAdmin
    {
        /** @var PlatformAdmin $controller */
        $controller = $this->propagate(
            new class () extends PlatformAdmin {
                public function createInstance($classes, $properties): core_kernel_classes_Resource
                {
                    return parent::createInstance($classes, $properties);
                }
            }
        );
        $controller->initialize();

        return $controller;
    }
}
