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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA
 */

namespace oat\taoLti\models\classes\LtiProvider;

use InvalidArgumentException;
use oat\generis\test\TestCase;

/**
 * Service methods to manage the LTI provider business objects.
 */
class ConfigurableLtiProviderRepositoryTest extends TestCase
{
    public function testConstructorCountFindAll()
    {
        $subject = new ConfigurableLtiProviderRepository([
            ConfigurableLtiProviderRepository::OPTION_LTI_PROVIDER_LIST => json_decode(
                file_get_contents(__DIR__ . '/_resources/lti_provider_list.json'),
                true
            )
        ]);

        $this->assertEquals(2, $subject->count());

        $providers = $subject->findAll();
        $this->assertInstanceOf(LtiProvider::class, $providers[0]);
        $this->assertEquals('provider1_uri', $providers[0]->getId());
        $this->assertEquals('provider1_label', $providers[0]->getLabel());
        $this->assertEquals('provider1_key', $providers[0]->getKey());
        $this->assertEquals('provider1_secret', $providers[0]->getSecret());
        $this->assertEquals('provider1_callback_url', $providers[0]->getCallbackUrl());
        $this->assertEquals(['Learner'], $providers[0]->getRoles());

        $this->assertInstanceOf(LtiProvider::class, $providers[1]);
        $this->assertEquals('provider2_uri', $providers[1]->getId());
        $this->assertEquals('provider2_label', $providers[1]->getLabel());
        $this->assertEquals('provider2_key', $providers[1]->getKey());
        $this->assertEquals('provider2_secret', $providers[1]->getSecret());
        $this->assertEquals('provider2_callback_url', $providers[1]->getCallbackUrl());
        $this->assertEquals([], $providers[1]->getRoles());
    }

    public function testSearchByLabel()
    {
        $subject = new ConfigurableLtiProviderRepository([
            ConfigurableLtiProviderRepository::OPTION_LTI_PROVIDER_LIST => json_decode(
                file_get_contents(__DIR__ . '/_resources/lti_provider_list.json'),
                true
            )
        ]);

        $providers = $subject->searchByLabel('provider1');
        $this->assertEquals(1, count($providers));
        $this->assertInstanceOf(LtiProvider::class, $providers[0]);
        $this->assertEquals('provider1_uri', $providers[0]->getId());
        $this->assertEquals('provider1_label', $providers[0]->getLabel());
        $this->assertEquals('provider1_key', $providers[0]->getKey());
        $this->assertEquals('provider1_secret', $providers[0]->getSecret());
        $this->assertEquals('provider1_callback_url', $providers[0]->getCallbackUrl());
    }

    public function testSearchByOauthKey()
    {
        $subject = new ConfigurableLtiProviderRepository([
            ConfigurableLtiProviderRepository::OPTION_LTI_PROVIDER_LIST => json_decode(
                file_get_contents(__DIR__ . '/_resources/lti_provider_list.json'),
                true
            )
        ]);

        $provider = $subject->searchByOauthKey('provider2_key');
        $this->assertInstanceOf(LtiProvider::class, $provider);
        $this->assertEquals('provider2_uri', $provider->getId());
        $this->assertEquals('provider2_label', $provider->getLabel());
        $this->assertEquals('provider2_key', $provider->getKey());
        $this->assertEquals('provider2_secret', $provider->getSecret());
        $this->assertEquals('provider2_callback_url', $provider->getCallbackUrl());

        $this->assertNull($subject->searchByOauthKey('not_existing'));
    }

    public function testConstructorWithNullProviderListThrowsException()
    {
        $subject = new ConfigurableLtiProviderRepository([
            ConfigurableLtiProviderRepository::OPTION_LTI_PROVIDER_LIST => null
        ]);
        $this->expectException(InvalidArgumentException::class);

        $subject->count();
    }

    public function testConstructorWithInvalidProviderListThrowsException()
    {
        $subject = new ConfigurableLtiProviderRepository([
            ConfigurableLtiProviderRepository::OPTION_LTI_PROVIDER_LIST => json_decode(
                file_get_contents(__DIR__ . '/_resources/incomplete_lti_provider_list.json'),
                true
            )
        ]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing key \'callback_url\' in LTI provider list.');

        $subject->count();
    }
}
