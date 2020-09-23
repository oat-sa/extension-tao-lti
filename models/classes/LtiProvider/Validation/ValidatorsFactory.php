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
use oat\oatbox\validator\ValidatorInterface;
use oat\tao\model\oauth\DataStore;
use oat\taoLti\models\classes\LtiProvider\RdfLtiProviderRepository;
use tao_helpers_form_FormFactory;
use tao_helpers_Uri;

class ValidatorsFactory extends ConfigurableService
{

    private const VALIDATORS = [
        '1.1' => [
            DataStore::PROPERTY_OAUTH_KEY => [['notEmpty']],
            DataStore::PROPERTY_OAUTH_SECRET => [['notEmpty']],
            RdfLtiProviderRepository::LTI_VERSION => [['notEmpty']],
        ],
        '1.3' => [
            RdfLtiProviderRepository::LTI_VERSION => [['notEmpty']],
            RdfLtiProviderRepository::LTI_TOOL_CLIENT_ID => [['notEmpty']],
            RdfLtiProviderRepository::LTI_TOOL_IDENTIFIER => [['notEmpty']],
            RdfLtiProviderRepository::LTI_TOOL_NAME => [['notEmpty']],
            RdfLtiProviderRepository::LTI_TOOL_DEPLOYMENT_IDS => [['notEmpty']],
            RdfLtiProviderRepository::LTI_TOOL_AUDIENCE => [['notEmpty']],
            RdfLtiProviderRepository::LTI_TOOL_OIDC_LOGIN_INITATION_URL => [['notEmpty'], ['url']],
            RdfLtiProviderRepository::LTI_TOOL_LAUNCH_URL => [['url']],
            RdfLtiProviderRepository::LTI_TOOL_JWKS_URL => [
                [
                    'url',
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

    /**
     * @return ValidatorInterface[][]
     */
    public function createFormValidators(string $schema, string $field = null): array
    {
        $result = [];
        foreach ($this->getValidators($schema, $field) as $name => $validators) {
            foreach ($validators as $validator) {
                $result[tao_helpers_Uri::encode($name)][] = tao_helpers_form_FormFactory::getValidator(...$validator);
            }
        }
        return $result;
    }

    public function getValidators(string $schema, string $field = null): array
    {
        $validators = self::VALIDATORS[$schema] ?? [];
        return $validators[$field] ? [$validators[$field]] : $validators;
    }
}
