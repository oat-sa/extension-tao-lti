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

use oat\generis\model\OntologyRdfs;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\service\exception\InvalidServiceManagerException;

/**
 * Service methods to manage the LTI provider business objects.
 */
class RdfLtiProviderRepository extends ConfigurableService implements LtiProviderRepositoryInterface
{
    public function count()
    {
        try {
            return $this->getFinderService()->getResourcesCount();
        } catch (InvalidServiceManagerException $exception) {
            $this->logError('Unable to retrieve providers: ' . $exception->getMessage());

            return 0;
        }
    }

    public function findAll()
    {
        return $this->findBy();
    }

    public function searchByLabel($queryString)
    {
        return $this->findBy([OntologyRdfs::RDFS_LABEL => $queryString]);
    }

    /**
     * Retrieves LTI Providers corresponding to the given criteria.
     *
     * @param array $criteria
     *
     * @return array
     */
    public function findBy(array $criteria = [])
    {
        try {
            $resources = $this->getFinderService()->getResources($criteria);
        } catch (InvalidServiceManagerException $exception) {
            $this->logError('Unable to retrieve providers: ' . $exception->getMessage());

            return [];
        }

        return array_map(
            function (LtiProviderResource $resource) {
                return new LtiProvider(
                    $resource->getUri(),
                    $resource->getLabel(),
                    $resource->getKey(),
                    $resource->getSecret(),
                    $resource->getCallbackUrl()
                );
            },
            $resources
        );
    }

    /**
     * Returns search service.
     *
     * @return ConfigurableService|RdfLtiProviderFinder
     * @throws InvalidServiceManagerException
     */
    private function getFinderService()
    {
        return $this->getServiceManager()->get(RdfLtiProviderFinder::class);
    }
}
