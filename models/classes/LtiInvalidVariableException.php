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
 *
 */

namespace oat\taoLti\models\classes;

use oat\taoLti\models\classes\LtiMessages\LtiErrorMessage;

class LtiInvalidVariableException extends LtiException
    implements \common_log_SeverityLevel
{
    public function __construct($variableName, $variableValue)
    {
        parent::__construct("Invalid LTI variable value {$variableName}:{$variableValue}", LtiErrorMessage::ERROR_INVALID_PARAMETER);
    }

    public function getSeverity() {
        return \common_Logger::DEBUG_LEVEL;
    }
}