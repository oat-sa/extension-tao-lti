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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA
 *
 */

namespace oat\taoLti\models\classes\LtiMessages;

/**
 * Class LtiMessage
 *
 * Class represents message and log values to be used in the return url (launch_presentation_return_url)
 *
 * @see http://www.imsglobal.org/specs/ltiv1p0/implementation-guide#toc-3 - "launch_presentation_return_url" section
 * @package oat\taoLti\models\classes\LtiMessages
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class LtiErrorMessage extends LtiMessage
{

    /**
     * Launch forbidden due to expiration, reaching max attempts etc
     */
    const ERROR_LAUNCH_FORBIDDEN = 1;

    /**
     * Wrong value of parameter such as `proctored`, `secure` etc.
     */
    const ERROR_INVALID_PARAMETER = 2;

    /**
     * Missed parameter
     */
    const ERROR_MISSING_PARAMETER = 3;

    /**
     * os/browser does not comply requirements, java script is disabled)
     */
    const ERROR_CLIENT_REQUIREMENTS = 4;

    /**
     * User is not authorized
     */
    const ERROR_UNAUTHORIZED = 5;

    /**
     * other tao specific errors
     */
    const ERROR_SYSTEM_ERROR = 6;

    /**
     * @return array
     */
    public function getUrlParams()
    {
        $params = [
            'lti_errormsg' => $this->getMessage(),
            'lti_errorlog' => $this->getLog(),
        ];
        return $params;
    }
}