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
class LtiProviderService extends ConfigurableService implements LtiProviderRepositoryInterface
{
    const SERVICE_ID = 'taoLti/LtiProviderService';
    const LTI_PROVIDER_LIST_IMPLEMENTATIONS = 'ltiProviderListImplementations';

    /**
     * Counts the number of LTI providers found from all implementations configured.
     *
     * @return int
     */
    public function count()
    {
        return $this->aggregate(
            0,
            static function ($count, LtiProviderRepositoryInterface $implementation) {
                return $count + $implementation->count();
            }
        );
    }

    /**
     * Gathers LTI providers found from all implementations configured.
     *
     * @return LtiProvider[]
     */
    public function findAll()
    {
        return $this->aggregate(
            [],
            static function ($providers, LtiProviderRepositoryInterface $implementation) {
                return array_merge($providers, $implementation->findAll());
            }
        );
    }

    /**
     * Gathers LTI providers found from all implementations configured and filters them by a string contained in label.
     *
     * @param string $label
     *
     * @return LtiProvider[]
     */
    public function searchByLabel($label)
    {
        return $this->aggregate(
            [],
            static function ($providers, LtiProviderRepositoryInterface $implementation) use ($label) {
                return array_merge($providers, $implementation->searchByLabel($label));
            }
        );
    }

    /**
     * Aggregates results of each implementation.
     *
     * @param array|int $result
     * @param callable $method
     *
     * @return array|int
     */
    private function aggregate($result, $method)
    {
        foreach ($this->getOption(self::LTI_PROVIDER_LIST_IMPLEMENTATIONS) as $implementation) {
            $this->propagate($implementation);
            $result = $method($result, $implementation);
        }

        return $result;
    }

    /**
     * @param string $id
     * @return LtiProvider
     */
    public function searchById($id)
    {
        return current(array_filter($this->aggregate(
            [],
            static function ($providers, LtiProviderRepositoryInterface $implementation) use ($id) {
                return array_merge($providers, [$implementation->searchById($id)]);
            }
        )));
    }

    /**
     * @param string $oauthKey
     * @return LtiProvider|null
     */
    public function searchByOauthKey($oauthKey)
    {
        $found = array_filter($this->aggregate(
            [],
            static function ($providers, LtiProviderRepositoryInterface $implementation) use ($oauthKey) {
                return array_merge($providers, [$implementation->searchByOauthKey($oauthKey)]);
            }
        ));
        return count($found) > 0
            ? reset($found)
            : null;
    }
}
