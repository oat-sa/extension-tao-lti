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

declare(strict_types=1);

namespace oat\taoLti\models\classes\LtiProvider\Validation;

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\oauth\DataStore;
use oat\taoLti\models\classes\LtiProvider\RdfLtiProviderRepository;

class ValidationRegistry extends ConfigurableService
{

    private const VALIDATORS = [
        '1.1' => [
            DataStore::PROPERTY_OAUTH_KEY => [['NotEmpty']],
            DataStore::PROPERTY_OAUTH_SECRET => [['NotEmpty']],
            RdfLtiProviderRepository::LTI_VERSION => [['NotEmpty']],
        ],
        '1.3' => [
            RdfLtiProviderRepository::LTI_VERSION => [['NotEmpty']],
            RdfLtiProviderRepository::LTI_TOOL_CLIENT_ID => [['NotEmpty']],
            RdfLtiProviderRepository::LTI_TOOL_IDENTIFIER => [['NotEmpty']],
            RdfLtiProviderRepository::LTI_TOOL_NAME => [['NotEmpty']],
            RdfLtiProviderRepository::LTI_TOOL_DEPLOYMENT_IDS => [['NotEmpty']],
            RdfLtiProviderRepository::LTI_TOOL_AUDIENCE => [['NotEmpty']],
            RdfLtiProviderRepository::LTI_TOOL_OIDC_LOGIN_INITATION_URL => [['NotEmpty'], ['Url']],
            RdfLtiProviderRepository::LTI_TOOL_LAUNCH_URL => [['Url']],
            RdfLtiProviderRepository::LTI_TOOL_JWKS_URL => [
                [
                    'Url',
                    ['allow_empty' => true],
                ],
                [
                    'AnyOf',
                    [
                        'reference' =>
                            [RdfLtiProviderRepository::LTI_TOOL_PUBLIC_KEY,],

                    ]
                ],
            ],
            RdfLtiProviderRepository::LTI_TOOL_PUBLIC_KEY => [
                [
                    'AnyOf',
                    [
                        'reference' =>
                            [RdfLtiProviderRepository::LTI_TOOL_JWKS_URL,],

                    ]
                ],
            ]
        ],
    ];

    public function getValidators(string $schema, string $field = null): array
    {
        $validators = self::VALIDATORS[$schema] ?? [];
        return isset($validators[$field]) ? [$validators[$field]] : $validators;
    }
}
