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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA;
 *
 * @author Ricardo Quintanilha <ricardo.quintanilha@taotesting.com>
 */

declare(strict_types=1);

namespace oat\taoLti\models\classes\Platform\Repository;

use common_persistence_SqlPersistence as SqlPersistence;
use oat\generis\model\DependencyInjection\ServiceLink;
use oat\generis\persistence\PersistenceManager;
use OAT\Library\Lti1p3Core\Platform\Platform;
use OAT\Library\Lti1p3Core\Registration\Registration;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use OAT\Library\Lti1p3Core\Registration\RegistrationRepositoryInterface;
use OAT\Library\Lti1p3Core\Security\Key\KeyChainRepositoryInterface;
use oat\taoLti\models\classes\Platform\LtiPlatformRegistration;
use RuntimeException;

class Lti1p3RegistrationSnapshotRepository implements RegistrationRepositoryInterface
{
    /** @var PersistenceManager */
    private $persistenceManager;

    /** @var KeyChainRepositoryInterface */
    private $keyChainRepository;

    /** @var ServiceLink */
    private $platformKeyChainRepositoryLink;

    /** @var DefaultToolConfig */
    private $defaultToolConfig;

    /** @var string */
    private $persistenceId;

    public function __construct(
        PersistenceManager $persistenceManager,
        KeyChainRepositoryInterface $keyChainRepository,
        ServiceLink $platformKeyChainRepositoryLink,
        DefaultToolConfig $defaultToolConfig,
        string $persistenceId
    ) {
        $this->persistenceManager = $persistenceManager;
        $this->keyChainRepository = $keyChainRepository;
        $this->platformKeyChainRepositoryLink = $platformKeyChainRepositoryLink;
        $this->defaultToolConfig = $defaultToolConfig;
        $this->persistenceId = $persistenceId;
    }

    public function save(LtiPlatformRegistration $ltiPlatformRegistration): void
    {
        $registration = $this->find($ltiPlatformRegistration->getIdentifier());

        if ($registration === null) {
            $this->getPersistence()->insert(
                'lti_platform_registration',
                [
                    'statement_id' => $ltiPlatformRegistration->getIdentifier(),
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
                        'statement_id' => $ltiPlatformRegistration->getIdentifier()
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

    public function deleteByStatementId(string $statementId): void
    {
        $this->getPersistence()->exec(
            'DELETE FROM lti_platform_registration WHERE statement_id = :statement_id',
            ['statement_id' => $statementId]
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
        throw new RuntimeException('Find registration by tool is not supported');
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

    private function toRegistration(array $row): ?Registration
    {
        $toolKeyChain = $this->keyChainRepository
            ->find($this->platformKeyChainRepositoryLink->getService()->getDefaultKeyId());

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
            $this->defaultToolConfig->getTool(),
            [$row['deployment_id']],
            null,
            $toolKeyChain,
            $row['jwks_url'],
            $this->defaultToolConfig->getJwksUrl()
        );
    }

    private function getPersistence(): SqlPersistence
    {
        return $this->persistenceManager->getPersistenceById($this->persistenceId);
    }
}
