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
    const OPTION_LTI_PROVIDER_LIST_URL = 'OPTION_LTI_PROVIDER_LIST_URL';
    const ENV_LTI_PROVIDER_LIST_URL = 'LTI_PROVIDER_LIST_URL';

    /**
     * @var array|LtiProvider[]
     */
    private $configuredProviders = [];

    public function __construct($options = array())
    {
        parent::__construct($options);

        $ltiProviderListFileName = $this->getOption(self::OPTION_LTI_PROVIDER_LIST_URL);

        // Temporary endpoint mock reading.
        $ltiProviderList = file_get_contents($ltiProviderListFileName);

        $providerList = json_decode($ltiProviderList, true);
        if ($providerList === null) {
            throw new \InvalidArgumentException('LTI provider list in .env is not a valid json string.');
        }

        foreach ($providerList as $provider) {
            $this->configuredProviders[] = new LtiProvider(
                $provider['uri'],
                $provider['label'],
                $provider['key'],
                $provider['secret'],
                $provider['callback_url']
            );
        }
    }

    public function count()
    {
        return count($this->configuredProviders);
    }

    public function findAll()
    {
        return $this->configuredProviders;
    }

    public function searchByLabel($label)
    {
        return array_filter(
            $this->configuredProviders,
            function (LtiProvider $provider) use ($label) {
                return stripos($provider->getLabel(), $label) !== false;
            }
        );
    }
}
