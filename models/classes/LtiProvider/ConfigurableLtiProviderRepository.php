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

use oat\oatbox\service\ConfigurableService;

/**
 * Service methods to manage the LTI provider business objects.
 */
class ConfigurableLtiProviderRepository extends ConfigurableService implements LtiProviderRepositoryInterface
{
    const OPTION_LTI_PROVIDER_LIST = 'OPTION_LTI_PROVIDER_LIST';

    /**
     * @var LtiProvider[]
     */
    private $providers;

    public function count()
    {
        return count($this->getProviders());
    }

    /**
     * @inheritdoc
     */
    public function findAll(): array
    {
        return $this->getProviders();
    }

    /**
     * @inheritdoc
     */
    public function searchByLabel(string $label): array
    {
        return array_filter(
            $this->getProviders(),
            static function (LtiProvider $provider) use ($label) {
                return stripos($provider->getLabel(), $label) !== false;
            }
        );
    }

    /**
     * Get providers from configuration.
     *
     * @return LtiProvider[]
     */
    private function getProviders(): array
    {

        if ($this->providers === null) {
            $providerList = $this->getOption(self::OPTION_LTI_PROVIDER_LIST);
            if ($providerList === null) {
                throw new InvalidArgumentException('LTI provider list is not valid.');
            }

            $this->providers = [];

            foreach ($providerList as $provider) {
                $this->providers[] = $this->getLtiProviderFactory()->createFromArray($provider);
            }
        }

        return $this->providers;
    }

    public function searchById(string $id): ?LtiProvider
    {
        foreach ($this->getProviders() as $provider) {
            if ($provider->getId() === $id) {
                return $provider;
            }
        }
        return null;
    }

    public function searchByOauthKey(string $oauthKey): ?LtiProvider
    {
        foreach ($this->getProviders() as $provider) {
            if ($provider->getKey() === $oauthKey) {
                return $provider;
            }
        }
        return null;
    }

    private function getLtiProviderFactory(): LtiProviderFactory
    {
        return $this->getServiceLocator()->get(LtiProviderFactory::class);
    }
}
