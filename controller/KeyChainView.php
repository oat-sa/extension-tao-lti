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

namespace oat\taoLti\controller;

use OAT\Library\Lti1p3Core\Security\Key\KeyChainRepositoryInterface;
use oat\tao\helpers\UrlHelper;
use oat\tao\model\security\Business\Contract\JwksRepositoryInterface;
use oat\tao\model\security\Business\Domain\Key\KeyChain;
use oat\tao\model\security\Business\Domain\Key\KeyChainQuery;
use oat\taoLti\models\classes\Security\DataAccess\Repository\CachedPlatformJwksRepository;
use oat\taoLti\models\classes\Security\DataAccess\Repository\CachedPlatformKeyChainRepository;
use tao_actions_CommonModule as CommonModule;

class KeyChainView extends CommonModule
{
    public function view(): void
    {
        $this->setData('lti-jwks', json_encode($this->getJwksRepository()->find()));

        $this->setData('lti-key-chains', json_encode($this->getSafeKeyChains()));

        $this->setData(
            'lti-key-chain-generate-url',
            $this->getUrlGenerator()->buildUrl('generate', 'KeyChainGenerator')
        );

        $this->setView('ltiKeyChain/ltiKeyChainGenerate.tpl');
    }

    /**
     * @todo Remove or move this method once UX is validated
     */
    private function getSafeKeyChains(): array
    {
        $keyChains = $this->getKeyChainRepository()->findAll(new KeyChainQuery())->getKeyChains();

        return array_map(function (KeyChain $keyChain) {
            return [
                'identifier' => $keyChain->getIdentifier(),
                'name' => $keyChain->getName(),
                'public' => $keyChain->getPublicKey()->getValue(),
            ];
        }, $keyChains);
    }

    private function getJwksRepository(): JwksRepositoryInterface
    {
        return $this->getServiceLocator()->get(CachedPlatformJwksRepository::class);
    }

    private function getKeyChainRepository(): CachedPlatformKeyChainRepository
    {
        return $this->getServiceLocator()->get(CachedPlatformKeyChainRepository::class);
    }

    private function getUrlGenerator(): UrlHelper
    {
        return $this->getServiceLocator()->get(UrlHelper::class);
    }
}
