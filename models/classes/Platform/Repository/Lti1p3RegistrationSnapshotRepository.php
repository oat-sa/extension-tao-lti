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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoLti\models\classes\Platform\Repository;

use common_persistence_SqlPersistence;
use oat\generis\persistence\PersistenceManager;
use OAT\Library\Lti1p3Core\Platform\Platform;
use OAT\Library\Lti1p3Core\Registration\Registration;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use OAT\Library\Lti1p3Core\Registration\RegistrationRepositoryInterface;
use OAT\Library\Lti1p3Core\Security\Key\KeyChainRepositoryInterface;
use OAT\Library\Lti1p3Core\Tool\Tool;
use oat\oatbox\service\ConfigurableService;
use oat\taoLti\models\classes\LtiProvider\LtiProvider;
use oat\taoLti\models\classes\LtiProvider\LtiProviderService;
use oat\taoLti\models\classes\Platform\LtiPlatformRegistration;
use oat\taoLti\models\classes\Security\DataAccess\Repository\CachedPlatformKeyChainRepository;
use oat\taoLti\models\classes\Security\DataAccess\Repository\PlatformKeyChainRepository;
use oat\taoLti\models\classes\Security\DataAccess\Repository\ToolKeyChainRepository;

class Lti1p3RegistrationSnapshotRepository extends ConfigurableService implements RegistrationRepositoryInterface
{
    public const OPTION_ROOT_URL = 'rootUrl';
    public const OPTION_PERSISTENCE_ID = 'persistenceId';

    private const PLATFORM_ID = 'tao';
    private const TOOL_ID = 'tao_tool';
    private const OIDC_PATH = 'taoLti/Security/oidc';
    private const OAUTH_PATH = 'taoLti/Security/oauth';
    private const JWKS_PATH = 'taoLti/Security/jwks';

    public function save(LtiPlatformRegistration $ltiPlatformRegistration): void
    {
        $registration = $this->find($ltiPlatformRegistration->getIdentifier());

        if ($registration === null) {
            $this->getPersistence()->insert(
                'lti_platform_registration',
                [
                    'statement_id'=> $ltiPlatformRegistration->getIdentifier(),
                    'name' => $ltiPlatformRegistration->getName(),
                    'audience' => $ltiPlatformRegistration->getAudience(),
                    'client_id' => $ltiPlatformRegistration->getClientId(),
                    'deployment_id' => $ltiPlatformRegistration->getDeploymentId(),
                    'oidc_authentication_url' => $ltiPlatformRegistration->getOidcAuthenticationUrl(),
                    'oauth2_access_token_url' => $ltiPlatformRegistration->getOAuth2AccessTokenUrl(),
                    'jwks_url' => $ltiPlatformRegistration->getJwksUrl(),
                    'updated_at' => date('Y-m-d H:i:s')
                ]
            );

            return;
        }

        $this->getPersistence()->updateMultiple(
            'lti_platform_registration',
            [
                [
                    'conditions' => [
                        'statement_id'=> $ltiPlatformRegistration->getIdentifier()
                    ],
                    'updateValues' => [
                        'name' => $ltiPlatformRegistration->getName(),
                        'audience' => $ltiPlatformRegistration->getAudience(),
                        'client_id' => $ltiPlatformRegistration->getClientId(),
                        'deployment_id' => $ltiPlatformRegistration->getDeploymentId(),
                        'oidc_authentication_url' => $ltiPlatformRegistration->getOidcAuthenticationUrl(),
                        'oauth2_access_token_url' => $ltiPlatformRegistration->getOAuth2AccessTokenUrl(),
                        'jwks_url' => $ltiPlatformRegistration->getJwksUrl(),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]
                ]
            ]
        );
    }

    public function find(string $identifier): ?RegistrationInterface
    {
        $row = $this->getRow(['statement_id' => $identifier]);

        if (empty($row)) {
            return null;
        }

        return $this->toRegistration($row);
    }

    public function findAll(): array
    {
        $rows = $this->getPersistence()
            ->query('SELECT * FROM lti_platform_registration')
            ->fetchAll();

        return array_map(
            function (array $row) {
                return $this->toRegistration($row);
            },
            $rows
        );
    }

    public function findByClientId(string $clientId): ?RegistrationInterface
    {
        $row = $this->getRow(['client_id' => $clientId]);

        if (empty($row)) {
            return null;
        }

        return $this->toRegistration($row);
    }

    public function findByPlatformIssuer(string $issuer, string $clientId = null): ?RegistrationInterface
    {
        $queryParams = [
            'audience' => $issuer,
            'client_id' => $clientId
        ];

        $row = $this->getRow($queryParams);

        if (empty($row)) {
            return null;
        }

        return $this->toRegistration($row);
    }

    public function findByToolIssuer(string $issuer, string $clientId = null): ?RegistrationInterface
    {
    }

    private function getRow(array $queryParams = []): array
    {
        $queryParams = array_filter($queryParams);

        $query = 'SELECT * FROM lti_platform_registration';

        if (!empty($queryParams)) {
            $whereClauses = array_map(
                function ($column) {
                    return sprintf('%s = :%s', $column, $column);
                },
                array_keys($queryParams)
            );

            $query .= ' WHERE ' . implode(' AND ', $whereClauses);
        }

        $query .= ' LIMIT 1';

        $statement = $this->getPersistence()->query(
            $query,
            $queryParams
        );

        return $statement->fetch() ?: [];
    }

    public function deleteByStatementId(string $statementId): void
    {
        $this->getPersistence()->exec(
            'DELETE FROM lti_platform_registration WHERE statement_id = :statement_id',
            ['statement_id' => $statementId]
        );
    }

    private function getTool(LtiProvider $ltiProvider): Tool
    {
        return new Tool(
            $ltiProvider->getToolIdentifier(),
            $ltiProvider->getToolName(),
            $ltiProvider->getToolAudience(),
            $ltiProvider->getToolOidcLoginInitiationUrl(),
            $ltiProvider->getToolLaunchUrl()
        );
    }

    private function getDefaultPlatform(): Platform
    {
        return new Platform(
            self::PLATFORM_ID,
            self::PLATFORM_ID,
            rtrim($this->getOption(self::OPTION_ROOT_URL), '/'),
            $this->getOption(self::OPTION_ROOT_URL) . self::OIDC_PATH,
            $this->getOption(self::OPTION_ROOT_URL) . self::OAUTH_PATH
        );
    }

    private function getDefaultTool(): Tool
    {
        return new Tool(
            self::TOOL_ID,
            self::TOOL_ID,
            rtrim($this->getOption(self::OPTION_ROOT_URL), '/'),
            $this->getOption(self::OPTION_ROOT_URL) . self::OIDC_PATH
        );
    }

    private function toRegistration(array $row): ?Registration
    {
        $toolKeyChain = $this->getCachedPlatformKeyChainRepository()
            ->find($this->getPlatformKeyChainRepository()->getDefaultKeyId());

        $platform = new Platform(
            $row['statement_id'],
            $row['name'],
            $row['audience'],
            $row['oidc_authentication_url'],
            $row['oauth2_access_token_url']
        );

        return new Registration(
            $platform->getIdentifier(),
            $row['client_id'],
            $platform,
            $this->getDefaultTool(),
            [$row['deployment_id']],
            null,
            $toolKeyChain,
            $row['jwks_url'],
            $this->getOption(self::OPTION_ROOT_URL) . self::JWKS_PATH
        );
    }

    private function getPersistence(): common_persistence_SqlPersistence
    {
        return $this->getServiceLocator()->get(PersistenceManager::SERVICE_ID)
            ->getPersistenceById($this->getOption(self::OPTION_PERSISTENCE_ID));
    }

    private function getCachedPlatformKeyChainRepository(): KeyChainRepositoryInterface
    {
        return $this->getServiceLocator()->get(CachedPlatformKeyChainRepository::class);
    }

    private function getPlatformKeyChainRepository(): PlatformKeyChainRepository
    {
        return $this->getServiceLocator()->get(PlatformKeyChainRepository::class);
    }
}
