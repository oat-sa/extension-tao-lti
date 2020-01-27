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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 */

namespace oat\taoLti\models\classes\Lis;

use oat\oatbox\service\ConfigurableService;
use Psr\Http\Message\ServerRequestInterface;

/**
 * LisAuthAdapter has params in constructor and couldn't be instantiated by ServiceLocator
 * So you can use this factory to get LisAuthAdapter in controllers/services to keep your code testable
 */
class LisAuthAdapterFactory extends ConfigurableService
{
    /**
     * @param ServerRequestInterface $request
     * @return LisAuthAdapter
     */
    public function create(ServerRequestInterface $request)
    {
        $adapter = new LisAuthAdapter($request);
        return $this->propagate($adapter);
    }
}
