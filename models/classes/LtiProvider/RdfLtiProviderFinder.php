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

use common_exception_Error as ErrorException;
use common_exception_InvalidArgumentType as InvalidArgumentTypeException;
use core_kernel_classes_Resource as RdfResource;
use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\oauth\DataStore;
use oat\tao\model\OntologyClassService;

/**
 * Service methods to manage the LTI provider business objects.
 */
class RdfLtiProviderFinder extends OntologyClassService
{
    const CLASS_URI = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#LTIProvider';

    /**
     * return the group top level class
     *
     * @return \core_kernel_classes_Class
     */
    public function getRootClass()
    {
        return $this->getClass(self::CLASS_URI);
    }

    /**
     * Retrieves the number of resources from rdf store corresponding to the given criteria.
     *
     * @return int
     */
    public function getResourcesCount()
    {
        return $this->queryResources([], 'count', 0);
    }

    /**
     * Retrieves resources from rdf store corresponding to the given criteria.
     *
     * @param array $criteria
     *
     * @return array|LtiProviderResource[]
     */
    public function getResources(array $criteria)
    {
        $resources = $this->queryResources($criteria, 'search', []);
        $ltiProviderResources = [];

        try {
            foreach ($resources as $resource) {
                $ltiProviderResources[] = $this->getLtiProviderProperties($resource);
            }
        } catch (InvalidArgumentTypeException $exception) {
            $this->logError('Unable to retrieve provider properties: ' . $exception->getMessage());

            return [];
        } catch (ErrorException $exception) {
            $this->logError('Unable to retrieve provider properties: ' . $exception->getMessage());

            return [];
        }

        return $ltiProviderResources;
    }

    /**
     * Completes the RdfResource by adding the key, secret and callbackUrl properties.
     *
     * @param RdfResource $resource Base RdfResource providing Uri and Label.
     *
     * @return LtiProviderResource
     * @throws InvalidArgumentTypeException
     * @throws ErrorException
     */
    private function getLtiProviderProperties(RdfResource $resource)
    {
        $propertiesValues = $resource->getPropertiesValues([
            DataStore::PROPERTY_OAUTH_KEY,
            DataStore::PROPERTY_OAUTH_SECRET,
            DataStore::PROPERTY_OAUTH_CALLBACK,
        ]);

        $ltiProviderResource = new LtiProviderResource($resource->getUri());
        $ltiProviderResource->setLabel($resource->getLabel());
        $ltiProviderResource->setKey((string)reset($propertiesValues[DataStore::PROPERTY_OAUTH_KEY]));
        $ltiProviderResource->setSecret((string)reset($propertiesValues[DataStore::PROPERTY_OAUTH_SECRET]));
        $ltiProviderResource->setCallbackUrl((string)reset($propertiesValues[DataStore::PROPERTY_OAUTH_CALLBACK]));

        return $ltiProviderResource;
    }

    /**
     * Retrieves resources from rdf store corresponding to the given criteria,
     * hydrate the result with the $hydration method
     * or returns $default on failure
     *
     * @param array     $criteria
     * @param string    $hydration Hydration method ("search" for actual results, "count" for counting results)
     * @param array|int $default   default value to return on failure
     *
     * @return mixed
     */
    private function queryResources(array $criteria, $hydration, $default)
    {
        try {
            $searchService = $this->getSearchService();
            $queryBuilder = $searchService->query();
            $query = $searchService->searchType($queryBuilder, self::CLASS_URI, true);
            if (count($criteria)) {
                foreach ($criteria as $property => $value) {
                    $query->add($property)->contains($value);
                }
            }
            $queryBuilder->setCriteria($query);

            return $searchService->getGateway()->$hydration($queryBuilder);
        } catch (ErrorException $e) {
            $this->logError('Unable to retrieve providers: ' . $e->getMessage());

            return $default;
        }
    }

    /**
     * Returns search service.
     *
     * @return ConfigurableService|ComplexSearchService
     */
    private function getSearchService()
    {
        return $this->getServiceLocator()->get(ComplexSearchService::SERVICE_ID);
    }
}
