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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA
 */

declare(strict_types=1);

namespace oat\taoLti\models\classes\Tool\Service;

use IMSGlobal\LTI\ToolProvider\ToolConsumer;
use oat\oatbox\service\ConfigurableService;
use oat\taoLti\models\classes\LtiLaunchData;
use oat\taoLti\models\classes\Tool\LtiLaunch;
use oat\taoLti\models\classes\Tool\LtiLaunchCommandInterface;
use oat\taoLti\models\classes\Tool\LtiLaunchInterface;

class Lti1p1Launcher extends ConfigurableService implements LtiLauncherInterface
{
    public function launch(LtiLaunchCommandInterface $command): LtiLaunchInterface
    {
        $data = array_merge(
            $command->getClaims(),
            [
                LtiLaunchData::LTI_VERSION => 'LTI-1p0',
                LtiLaunchData::USER_ID => $command->getUser()->getIdentifier(),
                LtiLaunchData::ROLES => current($command->getRoles()),
            ]
        );

        $data = ToolConsumer::addSignature(
            $command->getLaunchUrl(),
            $command->getLtiProvider()->getKey(),
            $command->getLtiProvider()->getSecret(),
            $data
        );

        return new LtiLaunch($command->getLaunchUrl(), $data);
    }
}
