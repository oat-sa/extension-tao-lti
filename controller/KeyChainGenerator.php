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

use common_exception_MethodNotAllowed as MethodNotAllowed;
use oat\tao\model\http\HttpJsonResponseTrait;
use oat\taoLti\models\classes\Platform\Service\CachedKeyChainGenerator;
use oat\taoLti\models\classes\Platform\Service\KeyChainGeneratorInterface;
use tao_actions_CommonModule as CommonModule;
use Throwable;

class KeyChainGenerator extends CommonModule
{
    use HttpJsonResponseTrait;

    public function generate(): void
    {
        try {
            if (!$this->isRequestPost()) {
                throw new MethodNotAllowed();
            }
            $this->getKeyChainGenerator()->generate();

            $this->setSuccessJsonResponse([]);
        } catch (MethodNotAllowed $exception) {
            $this->setErrorJsonResponse($exception->getMessage(), 0, [], 404);
        } catch (Throwable $exception) {
            $this->setErrorJsonResponse($exception->getMessage(), 0, [], 500);
        }
    }

    private function getKeyChainGenerator(): KeyChainGeneratorInterface
    {
        return $this->getServiceLocator()->get(CachedKeyChainGenerator::class);
    }
}
