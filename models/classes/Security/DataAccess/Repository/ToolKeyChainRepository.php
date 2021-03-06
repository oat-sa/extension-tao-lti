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

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\security\Business\Contract\KeyChainRepositoryInterface;
use oat\tao\model\security\Business\Domain\Key\Key;
use oat\tao\model\security\Business\Domain\Key\KeyChain;
use oat\tao\model\security\Business\Domain\Key\KeyChainCollection;
use oat\tao\model\security\Business\Domain\Key\KeyChainQuery;
use oat\taoLti\models\classes\LtiProvider\InvalidLtiProviderException;
use oat\taoLti\models\classes\LtiProvider\LtiProviderService;

class ToolKeyChainRepository extends ConfigurableService implements KeyChainRepositoryInterface
{
    public function save(KeyChain $keyChain): void
    {
    }

    public function findAll(KeyChainQuery $query): KeyChainCollection
    {
        $ltiProvider = $this->getLtiProviderService()->searchById($query->getIdentifier());

        if (!$ltiProvider) {
            throw new InvalidLtiProviderException('Lti Provider is not found');
        }

        if (empty($ltiProvider->getToolPublicKey())) {
            return new KeyChainCollection(...[]);
        }

        $keyChain = new KeyChain(
            $ltiProvider->getId(),
            $ltiProvider->getId(),
            new Key($ltiProvider->getToolPublicKey()),
            new Key('')
        );

        return new KeyChainCollection(...[$keyChain]);
    }

    private function getLtiProviderService(): LtiProviderService
    {
        return $this->getServiceLocator()->get(LtiProviderService::SERVICE_ID);
    }
}
