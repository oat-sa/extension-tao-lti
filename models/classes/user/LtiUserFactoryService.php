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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */
namespace oat\taoLti\models\classes\user;

use oat\oatbox\service\ConfigurableService;
use oat\taoLti\models\classes\LtiLaunchData;

class LtiUserFactoryService extends ConfigurableService implements LtiUserFactoryInterface
{
    const SERVICE_ID = 'taoLti/LtiUserFactory';

    /**
     * @param LtiLaunchData $ltiContext
     * @param string $userId
     * @return LtiUserInterface
     * @throws \Exception
     */
    public function create(LtiLaunchData $ltiContext, $userId)
    {
        return new LtiUser($ltiContext, $userId);
    }
}