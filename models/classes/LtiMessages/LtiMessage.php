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
class LtiMessage
{

    /**
     * @var string
     */
    protected $log;

    /**
     * @var string
     */
    protected $message;

    /**
     * LtiMessage constructor.
     * @param $message
     * @param $log
     */
    public function __construct($message, $log)
    {
        $this->message = $message;
        $this->log = $log;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * @return array
     */
    public function getUrlParams()
    {
        $params = [];
        if (!empty($this->getMessage())) {
            $params['lti_msg'] = $this->getMessage();
        }
        if (!empty($this->getLog())) {
            $params['lti_log'] = $this->getLog();
        }
        return $params;
    }
}