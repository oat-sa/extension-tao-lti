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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA
 */

namespace oat\taoLti\models\classes\Platform\Repository;

use core_kernel_classes_Class;
use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use oat\generis\model\OntologyRdfs;
use oat\tao\model\OntologyClassService;
use oat\taoLti\models\classes\Platform\LtiPlatformRegistration;
use common_exception_Error as ErrorException;
use core_kernel_classes_Resource as RdfResource;

/**
 * Service methods to manage the LTI 1.3 platform business objects using the RDF API.
 *
 * @package taoLti
 */
class RdfLtiPlatformRepository extends OntologyClassService implements LtiPlatformRepositoryInterface
{
    public const CLASS_URI = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#Platform';

    public const LTI_PLATFORM_CLIENT_ID = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#PlatformClientId';
    public const LTI_PLATFORM_DEPLOYMENT_ID = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#PlatformDeploymentId';
    public const LTI_PLATFORM_AUDIENCE = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#PlatformAudience';
    public const LTI_PLATFORM_OAUTH2_ACCESS_TOKEN_URL =
        'http://www.tao.lu/Ontologies/TAOLTI.rdf#PlatformOuath2AccessTokenUrl';
    public const LTI_PLATFORM_OIDC_URL = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#PlatformOidcAuthenticationUrl';
    public const LTI_PLATFORM_JWKS_URL = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#PlatformJwksUrl';

    /**
     * return the group top level class
     *
     * @return core_kernel_classes_Class
     */
    public function getRootClass()
    {
        return $this->getClass(self::CLASS_URI);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->queryResources([], 'count', 0);
    }

    /**
     * @inheritdoc
     */
    public function findAll(): array
    {
        return $this->getPlatforms();
    }

    /**
     * @inheritdoc
     */
    public function searchByLabel(string $queryString): array
    {
        return $this->getPlatforms([OntologyRdfs::RDFS_LABEL => $queryString]);
    }

    /**
     * Retrieves LTI platforms from RDF store corresponding to the given criteria.
     *
     * @return LtiPlatformRegistration[]
     */
    private function getPlatforms(array $criteria = []): array
    {
        $resources = $this->queryResources($criteria, 'search', []);
        $ltiPlatforms = [];

        foreach ($resources as $resource) {
            $ltiPlatforms[] = $this->getLtiPlatformFromResource($resource);
        }

        return $ltiPlatforms;
    }

    /**
     * Retrieves resources from rdf store corresponding to the given criteria,
     * hydrate the result with the $hydration method
     * or returns $default on failure
     *
     * @param array $criteria
     * @param string $hydration Hydration method ("search" for actual results, "count" for counting results)
     * @param array|int $default default value to return on failure
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
            $this->logError('Unable to retrieve platforms: ' . $e->getMessage());

            return $default;
        }
    }

    private function getLtiPlatformFromResource(RdfResource $resource): LtiPlatformRegistration
    {
        return $this->getLtiPlatformFactory()->createFromResource($resource);
    }

    public function searchById(string $id): ?LtiPlatformRegistration
    {
        $resource = $this->getResource($id);

        if (!$resource->exists()) {
            return null;
        }

        $types = $resource->getTypes();
        $type = reset($types);

        if ($type->getUri() !== self::CLASS_URI) {
            return null;
        }

        return $this->getLtiPlatformFromResource($this->getResource($id));
    }

    public function searchByClientId(string $clientId): ?LtiPlatformRegistration
    {
        $platforms = $this->getPlatforms([self::LTI_PLATFORM_CLIENT_ID => $clientId]);
        $count = count($platforms);
        if ($count === 0) {
            return null;
        }
        if ($count > 1) {
            $this->logWarning(sprintf('Found %d LTI platforms with the same clientId: %s', $count, $clientId));
        }
        return reset($platforms);
    }

    public function searchByIssuer(string $issuer, string $clientId = null): ?LtiPlatformRegistration
    {
        $criteria = [self::LTI_PLATFORM_AUDIENCE => $issuer];
        if ($clientId !== null) {
            $criteria[self::LTI_PLATFORM_CLIENT_ID] = $clientId;
        }
        $platforms = $this->getPlatforms($criteria);
        $count = count($platforms);
        if ($count === 0) {
            return null;
        }
        if ($count > 1) {
            $this->logWarning(sprintf(
                'Found %d LTI platforms with the same clientId: %s and audience: %s',
                $count,
                $clientId,
                $issuer
            ));
        }
        return reset($platforms);
    }

    private function getLtiPlatformFactory(): LtiPlatformFactory
    {
        return $this->getServiceLocator()->get(LtiPlatformFactory::class);
    }
}
