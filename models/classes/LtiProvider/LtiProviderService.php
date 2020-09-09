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

use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\taoDelivery\model\execution\DeliveryExecutionService;
use oat\taoDeliveryRdf\model\ContainerRuntime;
use oat\taoLti\models\classes\LtiException;
use oat\taoLti\models\classes\Platform\Service\InvalidLtiProviderException;

/**
 * Service methods to manage the LTI provider business objects.
 */
class LtiProviderService extends ConfigurableService implements LtiProviderRepositoryInterface
{
    use OntologyAwareTrait;

    public const SERVICE_ID = 'taoLti/LtiProviderService';
    public const LTI_PROVIDER_LIST_IMPLEMENTATIONS = 'ltiProviderListImplementations';

    /**
     * Counts the number of LTI providers found from all implementations configured.
     */
    public function count(): int
    {
        return $this->aggregate(
            0,
            static function ($count, LtiProviderRepositoryInterface $implementation) {
                return $count + $implementation->count();
            }
        );
    }

    public function searchByToolClientId(string $clientId): LtiProvider
    {
        foreach ($this->findAll() as $provider) {
            if ($provider instanceof LtiProvider && $clientId === $provider->getToolClientId()) {
                return $provider;
            }
        }

        throw new InvalidLtiProviderException(sprintf('Lti provider with client id %s does not exist', $clientId));
    }

    /**
     * Gathers LTI providers found from all implementations configured.
     *
     * @return LtiProvider[]
     */
    public function findAll(): array
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
    public function searchByLabel($label): array
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
     *
     * @return LtiProvider
     */
    public function searchById($id): LtiProvider
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
     *
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

    public function searchByDeliveryExecutionId(string $deliveryExecutionId): LtiProvider
    {
        $delivery = $this->getDeliveryExecutionService()
            ->getDeliveryExecution($deliveryExecutionId)
            ->getDelivery();

        $containerJson = $containerJson = json_decode(
            (string)$delivery->getOnePropertyValue(
                $this->getProperty(ContainerRuntime::PROPERTY_CONTAINER)
            ),
            true
        );

        if (!isset($containerJson['params']) && !isset($containerJson['params']['ltiProvider'])) {
            throw new LtiException('This delivery does not contain required lti provider defined');
        }

        return $this->searchById($containerJson['params']['ltiProvider']);
    }

    private function getDeliveryExecutionService(): DeliveryExecutionService
    {
        return $this->getServiceLocator()->get(DeliveryExecutionService::SERVICE_ID);
    }
}
