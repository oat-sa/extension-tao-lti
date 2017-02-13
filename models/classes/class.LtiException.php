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

use oat\taoLti\models\classes\LtiMessages\LtiErrorMessage;

class taoLti_models_classes_LtiException extends common_Exception
{

    public function __construct($message = null, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code);
        common_Logger::singleton()->handleException($this);
    }

    /**
     * @var LtiMessage
     */
    protected $ltiMessage;

    /**
     * @return LtiMessage
     */
    public function getLtiMessage()
    {
        if ($this->ltiMessage === null){
            $this->ltiMessage = new LtiErrorMessage($this->getCode(), $this->getMessage());
        }
        return $this->ltiMessage;
    }

}