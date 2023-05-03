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
    public const CLASS_URI = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#LTIProvider';

    public const LTI_VERSION = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#ltiVersion';
    public const LTI_TOOL_CLIENT_ID = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#toolClientId';
    public const LTI_TOOL_IDENTIFIER = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#toolIdentifier';
    public const LTI_TOOL_NAME = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#toolName';
    public const LTI_TOOL_DEPLOYMENT_IDS = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#toolDeploymentIds';
    public const LTI_TOOL_AUDIENCE = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#toolAudience';
    public const LTI_TOOL_OIDC_LOGIN_INITATION_URL = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#toolOidcLoginInitiationUrl';
    public const LTI_TOOL_LAUNCH_URL = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#toolLaunchUrl';

    public const LTI_TOOL_JWKS_URL = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#ToolJwksUrl';
    public const LTI_TOOL_PUBLIC_KEY = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#toolPublicKey';

    public const LTI_V_11 = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#lti1p1';
    public const LTI_V_13 = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#lti1p3';

    public const DEFAULT_LTI_VERSION = self::LTI_V_11;

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
    public function findAll(): array
    {
        return $this->getProviders();
    }

    /**
     * @inheritdoc
     */
    public function searchByLabel(string $queryString): array
    {
        return $this->getProviders([OntologyRdfs::RDFS_LABEL => $queryString]);
    }

    /**
     * Retrieves providers from rdf store corresponding to the given criteria.
     *
     * @return LtiProvider[]
     */
    private function getProviders(array $criteria = []): array
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
            $this->logError('Unable to retrieve providers: ' . $e->getMessage());

            return $default;
        }
    }

    private function getLtiProviderFromResource(RdfResource $resource): LtiProvider
    {
        return $this->getLtiProviderFactory()->createFromResource($resource);
    }

    public function searchById(string $id): ?LtiProvider
    {
        $resource = $this->getResource($id);
        if ($resource->exists()) {
            $types = $resource->getTypes();
            $type = reset($types);
            if ($type->getUri() !== self::CLASS_URI) {
                return null;
            }
            return $this->getLtiProviderFromResource($this->getResource($id));
        }
        return null;
    }

    public function searchByOauthKey(string $oauthKey): ?LtiProvider
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

    public function searchByIssuer(string $issuer, string $clientId = null): ?LtiProvider
    {
        $criteria = [self::LTI_TOOL_AUDIENCE => $issuer];
        if ($clientId !== null) {
            $criteria[self::LTI_TOOL_CLIENT_ID] = $clientId;
        }
        $providers = $this->getProviders($criteria);
        $count = count($providers);
        if ($count === 0) {
            return null;
        }
        if ($count > 1) {
            $this->logWarning(sprintf(
                'Found %d LTI provider with the same clientId: %s and audience: %s',
                $count,
                $clientId,
                $issuer
            ));
        }
        return reset($providers);
    }

    private function getLtiProviderFactory(): LtiProviderFactory
    {
        return $this->getServiceLocator()->get(LtiProviderFactory::class);
    }
}
