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

use LogicException;
use OAT\Library\Lti1p3Core\Platform\Platform;
use OAT\Library\Lti1p3Core\Registration\Registration;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use OAT\Library\Lti1p3Core\Registration\RegistrationRepositoryInterface;
use OAT\Library\Lti1p3Core\Security\Key\KeyChain;
use OAT\Library\Lti1p3Core\Tool\Tool;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\security\Business\Contract\KeyChainRepositoryInterface;
use oat\tao\model\security\Business\Domain\Key\KeyChain as TaoKeyChain;
use oat\tao\model\security\Business\Domain\Key\KeyChainQuery;
use oat\taoLti\models\classes\LtiProvider\LtiProvider;
use oat\taoLti\models\classes\Security\DataAccess\Repository\PlatformKeyChainRepository;
use oat\taoLti\models\classes\Security\DataAccess\Repository\ToolKeyChainRepository;

class Lti1p3RegistrationRepository extends ConfigurableService implements RegistrationRepositoryInterface
{
    private const OIDC_URL = ROOT_URL . 'taoLti/Security/oidc';
    private const PLATFORM_ID = 'tao';

    public function find(string $identifier): ?RegistrationInterface
    {
        $toolKeyChain = $this->getToolKeyChainRepository()
                ->findAll(new KeyChainQuery())
                ->getKeyChains()[0] ?? null;

        $platformKeyChain = $this->getPlatformKeyChainRepository()
                ->findAll(new KeyChainQuery())
                ->getKeyChains()[0] ?? null;

        if ($toolKeyChain === null || $platformKeyChain === null) {
            return null;
        }

        /**
         * @TODO Parameters must come from proper provider configuration (future task)
         */
        return new Registration(
            'registrationIdentifier',
            'client_id',
            $this->getDefaultPlatform(),
            $this->getTool(),
            $this->getDeploymentIds(),
            $this->translateKeyChain($platformKeyChain),
            $this->translateKeyChain($toolKeyChain)
        );
    }

    public function findAll(): array
    {
        $this->throwMissingImplementation(__METHOD__);
    }

    public function findByClientId(string $clientId): ?RegistrationInterface
    {
        $this->throwMissingImplementation(__METHOD__);
    }

    public function findByPlatformIssuer(string $issuer, string $clientId = null): ?RegistrationInterface
    {
        $this->throwMissingImplementation(__METHOD__);
    }

    public function findByToolIssuer(string $issuer, string $clientId = null): ?RegistrationInterface
    {
        $this->throwMissingImplementation(__METHOD__);
    }

    private function throwMissingImplementation(string $method): void
    {
        throw new LogicException('Method ' . $method . ' was not required at needs to be implemented');
    }

    private function getToolKeyChainRepository(): KeyChainRepositoryInterface
    {
        return $this->getServiceLocator()->get(ToolKeyChainRepository::class);
    }

    private function getPlatformKeyChainRepository(): KeyChainRepositoryInterface
    {
        return $this->getServiceLocator()->get(PlatformKeyChainRepository::SERVICE_ID);
    }

    private function translateKeyChain(TaoKeyChain $keyChain): KeyChain
    {
        return new KeyChain(
            $keyChain->getIdentifier(),
            $keyChain->getName(),
            $keyChain->getPublicKey()->getValue(),
            $keyChain->getPrivateKey()->getValue()
        );
    }

    /**
     * @TODO Parameter must come from proper provider configuration (future task)
     */
    private function getDeploymentIds(): array
    {
        return ['1'];
    }

    /**
     * @TODO Parameter must come from provider configuration (future task)
     */
    private function getTool(LtiProvider $ltiProvider = null): Tool
    {
        return new Tool(
            'local_demo',
            'local_demo',
            'http://localhost:8888/tool',
            'http://localhost:8888/lti1p3/oidc/login-initiation',
            'http://localhost:8888/tool/launch'
        );
    }

    private function getDefaultPlatform(): Platform
    {
        return new Platform(
            self::PLATFORM_ID,
            self::PLATFORM_ID,
            rtrim(ROOT_URL, '/'),
            self::OIDC_URL
        );
    }
}
