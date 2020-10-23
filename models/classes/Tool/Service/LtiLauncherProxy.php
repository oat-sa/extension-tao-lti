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

use LogicException;
use oat\oatbox\service\ConfigurableService;
use oat\taoLti\models\classes\Tool\LtiLaunchCommandInterface;
use oat\taoLti\models\classes\Tool\LtiLaunchInterface;

class LtiLauncherProxy extends ConfigurableService implements LtiLauncherInterface
{
    public function launch(LtiLaunchCommandInterface $command): LtiLaunchInterface
    {
        if ($command->getLtiProvider()->getLtiVersion() === '1.1') {
            return $this->getLti1p1Launcher()->launch($command);
        }

        if ($command->getLtiProvider()->getLtiVersion() === '1.3') {
            return $this->getLti1p3Launcher()->launch($command);
        }

        throw new LogicException(
            sprintf(
                'LTI version %s is not supported',
                $command->getLtiProvider()->getLtiVersion()
            )
        );
    }

    private function getLti1p3Launcher(): LtiLauncherInterface
    {
        return $this->getServiceLocator()->get(Lti1p3Launcher::class);
    }

    private function getLti1p1Launcher(): LtiLauncherInterface
    {
        return $this->getServiceLocator()->get(Lti1p1Launcher::class);
    }
}
