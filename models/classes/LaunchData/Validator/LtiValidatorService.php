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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA
 */

namespace oat\taoLti\models\classes\LaunchData\Validator;

use oat\oatbox\service\ConfigurableService;
use oat\taoLti\models\classes\LtiLaunchData;

class LtiValidatorService extends ConfigurableService
{
    const SERVICE_ID = 'taoLti/LtiValidatorService';

    const OPTION_LAUNCH_DATA_VALIDATOR = 'launchDataValidator';

    public function validateLaunchData(LtiLaunchData $data) {
        $validator = $this->getOption(self::OPTION_LAUNCH_DATA_VALIDATOR);
        if ($validator instanceof LtiValidatorInterface) {
            $validator->validate($data);
        }
    }
}
