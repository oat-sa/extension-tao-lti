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
 * Copyright (c) 2019-2020 (original work) Open Assessment Technologies SA
 */

declare(strict_types=1);

namespace oat\taoLti\test\integration;

use InvalidArgumentException;
use oat\generis\test\TestCase;
use oat\tao\model\oauth\DataStore;
use oat\taoLti\models\classes\LtiProvider\ConfigurableLtiProviderRepository;
use oat\taoLti\models\classes\LtiProvider\LtiProvider;
use oat\taoLti\models\classes\LtiProvider\LtiProviderFactory;
use oat\taoLti\models\classes\LtiProvider\LtiProviderFieldsMapper;
use oat\taoLti\models\classes\LtiProvider\Validation\LtiProviderValidationService;
use oat\taoLti\models\classes\LtiProvider\Validation\ValidatorsFactory;

class ConfigurableLtiProviderRepositoryTest extends TestCase
{
    public function testConstructorCountFindAll(): void
    {
        $subject = $this->createSubject('lti_provider_list.json');

        $this->assertEquals(2, $subject->count());

        $providers = $subject->findAll();

        $this->assertInstanceOf(LtiProvider::class, $providers[0]);
        $this->assertEquals('provider1_uri', $providers[0]->getId());
        $this->assertEquals('provider1_label', $providers[0]->getLabel());
        $this->assertEquals('provider1_key', $providers[0]->getKey());
        $this->assertEquals('provider1_secret', $providers[0]->getSecret());
        $this->assertEquals('provider1_callback_url', $providers[0]->getCallbackUrl());
        $this->assertEquals('jwksUrl', $providers[0]->getToolJwksUrl());
        $this->assertEquals(['Learner'], $providers[0]->getRoles());

        $this->assertInstanceOf(LtiProvider::class, $providers[1]);
        $this->assertEquals('provider2_uri', $providers[1]->getId());
        $this->assertEquals('provider2_label', $providers[1]->getLabel());
        $this->assertEquals('provider2_key', $providers[1]->getKey());
        $this->assertEquals('provider2_secret', $providers[1]->getSecret());
        $this->assertEquals('provider2_callback_url', $providers[1]->getCallbackUrl());
        $this->assertEquals([], $providers[1]->getRoles());
    }

    public function testSearchByLabel(): void
    {
        $subject = $this->createSubject('lti_provider_list.json');

        $providers = $subject->searchByLabel('provider1');
        $this->assertEquals(1, count($providers));
        $this->assertInstanceOf(LtiProvider::class, $providers[0]);
        $this->assertEquals('provider1_uri', $providers[0]->getId());
        $this->assertEquals('provider1_label', $providers[0]->getLabel());
        $this->assertEquals('provider1_key', $providers[0]->getKey());
        $this->assertEquals('provider1_secret', $providers[0]->getSecret());
        $this->assertEquals('provider1_callback_url', $providers[0]->getCallbackUrl());
    }

    public function testSearchByOauthKey(): void
    {
        $subject = $this->createSubject('lti_provider_list.json');

        $provider = $subject->searchByOauthKey('provider2_key');
        $this->assertInstanceOf(LtiProvider::class, $provider);
        $this->assertEquals('provider2_uri', $provider->getId());
        $this->assertEquals('provider2_label', $provider->getLabel());
        $this->assertEquals('provider2_key', $provider->getKey());
        $this->assertEquals('provider2_secret', $provider->getSecret());
        $this->assertEquals('provider2_callback_url', $provider->getCallbackUrl());

        $this->assertNull($subject->searchByOauthKey('not_existing'));
    }

    public function testConstructorWithNullProviderListThrowsException(): void
    {
        $subject = $this->createSubject(null);

        $this->expectException(InvalidArgumentException::class);

        $subject->count();
    }

    public function testConstructorWithInvalidProviderListThrowsException(): void
    {
        $subject = $this->createSubject('incomplete_lti_provider_list.json');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"key": This field is required');

        $subject->count();
    }

    private function createSubject(string $providerListPath = null): ConfigurableLtiProviderRepository
    {
        $subject = new ConfigurableLtiProviderRepository(
            [
                ConfigurableLtiProviderRepository::OPTION_LTI_PROVIDER_LIST => $providerListPath ? json_decode(
                    file_get_contents(
                        __DIR__
                        . DIRECTORY_SEPARATOR
                        . '_resources'
                        . DIRECTORY_SEPARATOR
                        . $providerListPath
                    ),
                    true
                ) : null
            ]
        );

        $factory = new LtiProviderFactory();
        $validationService = new LtiProviderValidationService();
        $validationService->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    LtiProviderFieldsMapper::SERVICE_ID => new LtiProviderFieldsMapper(
                        [
                            LtiProviderFieldsMapper::OPTION_MAP => [
                                DataStore::PROPERTY_OAUTH_KEY => 'key',
                            ],
                        ]
                    ),
                    ValidatorsFactory::SERVICE_ID => new ValidatorsFactory(
                        [
                            ValidatorsFactory::OPTION_VALIDATORS => [
                                '1.1' => [
                                    DataStore::PROPERTY_OAUTH_KEY => [['notEmpty']],
                                ],
                            ]
                        ]
                    ),
                ]
            )
        );
        $factory->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    LtiProviderValidationService::class => $validationService,
                ]
            )
        );
        $subject->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    LtiProviderFactory::class => $factory
                ]
            )
        );

        return $subject;
    }
}
