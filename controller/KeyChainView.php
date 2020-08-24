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

use oat\tao\helpers\UrlHelper;
use oat\tao\model\security\Business\Contract\JwksRepositoryInterface;
use oat\tao\model\security\Business\Contract\KeyChainRepositoryInterface;
use oat\tao\model\security\Business\Domain\Key\KeyChainQuery;
use oat\taoLti\models\classes\Security\DataAccess\Repository\CachedPlatformJwksRepository;
use oat\taoLti\models\classes\Security\DataAccess\Repository\CachedPlatformKeyChainRepository;
use \tao_actions_CommonModule as CommonModule;

class KeyChainView extends CommonModule
{
    public function handle(/*ServerRequestInterface $request*/)// ResponseInterface
    {
        echo 'toto';
        $this->setData('jwks-key', json_encode($this->getJwksRepository()->findAll(new KeyChainQuery())));
        $this->setData('jwks-generate-url', $this->getUrlGenerator()->buildUrl('jwks', 'Security'));
        $this->setView('jwks/Jwks.tpl');


//        return $this->getPsrResponse();
    }

    public function view(): void
    {
        $this->setData('jwks-key', json_encode($this->getJwksRepository()->findAll(new KeyChainQuery())));
        $this->setData('jwks-generate-url', $this->getUrlGenerator()->buildUrl('jwks', 'Security'));
        $this->setView('jwks/Jwks.tpl');
    }


    private function getJwksRepository(): KeyChainRepositoryInterface
    {
        return $this->getServiceLocator()->get(CachedPlatformKeyChainRepository::class);
    }

    private function getUrlGenerator(): UrlHelper
    {
        return $this->getServiceLocator()->get(UrlHelper::class);
    }

}
