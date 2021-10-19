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

namespace oat\taoLti\models\classes\Security\DataAccess\Repository;

use common_exception_NoImplementation;
use OAT\Library\Lti1p3Core\Security\Key\Key;
use OAT\Library\Lti1p3Core\Security\Key\KeyChain;
use OAT\Library\Lti1p3Core\Security\Key\KeyChainInterface;
use OAT\Library\Lti1p3Core\Security\Key\KeyChainRepositoryInterface;
use oat\oatbox\service\ConfigurableService;
use oat\taoLti\models\classes\LtiProvider\InvalidLtiProviderException;
use oat\taoLti\models\classes\LtiProvider\LtiProviderService;

class ToolKeyChainRepository extends ConfigurableService implements KeyChainRepositoryInterface
{
    /**
     * @throws InvalidLtiProviderException
     */
    public function find(string $identifier): ?KeyChainInterface
    {
        $ltiProvider = $this->getLtiProviderService()->searchById($identifier);

        if (!$ltiProvider) {
            throw new InvalidLtiProviderException('Lti Provider is not found');
        }

        if (empty($ltiProvider->getToolPublicKey())) {
            return null;
        }

        return new KeyChain(
            $ltiProvider->getId(),
            $ltiProvider->getId(),
            new Key($ltiProvider->getToolPublicKey()),
            new Key('')
        );
    }

    /**
     * @throws common_exception_NoImplementation
     */
    public function findByKeySetName(string $keySetName): array
    {
        throw new common_exception_NoImplementation();
    }

    private function getLtiProviderService(): LtiProviderService
    {
        return $this->getServiceLocator()->get(LtiProviderService::SERVICE_ID);
    }
}
