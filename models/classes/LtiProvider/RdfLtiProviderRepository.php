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
use oat\generis\model\OntologyRdfs;
use oat\tao\model\oauth\DataStore;
use oat\tao\model\OntologyClassService;

/**
 * Service methods to manage the LTI provider business objects.
 */
class RdfLtiProviderRepository extends OntologyClassService implements LtiProviderRepositoryInterface
{
    const CLASS_URI = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#LTIProvider';

    /**
     * @inheritdoc
     */
    public function getRootClass()
    {
        return $this->getClass(self::CLASS_URI);
    }

    /**
     * Returns the number of LtiProviders.
     *
     * @return int
     */
    public function count()
    {
        return $this->queryResources([], 'count', 0);
    }

    /**
     * @inheritdoc
     */
    public function findAll()
    {
        return $this->getProviders();
    }

    /**
     * @inheritdoc
     */
    public function searchByLabel($queryString)
    {
        return $this->getProviders([OntologyRdfs::RDFS_LABEL => $queryString]);
    }

    /**
     * Retrieves providers from rdf store corresponding to the given criteria.
     *
     * @param array $criteria
     *
     * @return LtiProvider[]
     */
    private function getProviders(array $criteria = [])
    {
        $resources = $this->queryResources($criteria, 'search', []);
        $ltiProviders = [];

        try {
            foreach ($resources as $resource) {
                $ltiProviders[] = $this->getLtiProviderFromResource($resource);
            }
        } catch (InvalidArgumentTypeException $exception) {
            $this->logError('Unable to retrieve provider properties: ' . $exception->getMessage());

            return [];
        }

        return $ltiProviders;
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
            /** @var ComplexSearchService $searchService */
            $searchService = $this->getServiceLocator()->get(ComplexSearchService::SERVICE_ID);
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
     * Completes the RdfResource by adding the key, secret and callbackUrl properties.
     *
     * @param RdfResource $resource Base RdfResource providing Uri and Label.
     *
     * @return LtiProvider
     * @throws InvalidArgumentTypeException
     */
    private function getLtiProviderFromResource(RdfResource $resource)
    {
        $propertiesValues = $resource->getPropertiesValues([
            DataStore::PROPERTY_OAUTH_KEY,
            DataStore::PROPERTY_OAUTH_SECRET,
            DataStore::PROPERTY_OAUTH_CALLBACK,
        ]);

        return new LtiProvider(
            $resource->getUri(),
            $resource->getLabel(),
            (string)reset($propertiesValues[DataStore::PROPERTY_OAUTH_KEY]),
            (string)reset($propertiesValues[DataStore::PROPERTY_OAUTH_SECRET]),
            (string)reset($propertiesValues[DataStore::PROPERTY_OAUTH_CALLBACK])
        );
    }

    /**
     * @param string $id
     * @return LtiProvider|null
     * @throws InvalidArgumentTypeException
     */
    public function searchById($id)
    {
        if ($this->getResource($id)->exists()) {
            return $this->getLtiProviderFromResource($this->getResource($id));
        }
        return null;
    }

    /**
     * @param string $oauthKey
     * @return LtiProvider|null
     */
    public function searchByOauthKey($oauthKey)
    {
        $providers = $this->getProviders([DataStore::PROPERTY_OAUTH_KEY => $oauthKey]);
        $count = count($providers);
        if ($count === 0) {
            return null;
        }
        if ($count > 1) {
            $this->logWarning("Found $count LTI providers with the same oauthKey: '$oauthKey'");
        }
        return reset($providers);
    }
}
