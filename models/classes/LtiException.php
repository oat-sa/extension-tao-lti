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

namespace oat\taoLti\models\classes;

use oat\taoLti\models\classes\LtiMessages\LtiErrorMessage;

class LtiException extends \common_Exception
{
    /**
     * @var LtiErrorMessage
     */
    protected $ltiMessage;
    /**
     * @var string Unique key to determine error in log
     */
    private $key;

    /**
     * @var LtiLaunchData
     */
    protected $launchData = null;

    /**
     * LtiException constructor.
     * @param null $message
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        if (!is_null($previous)) {
            $message .= ' '. $previous->getMessage();
        }
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return LtiErrorMessage
     */
    public function getLtiMessage()
    {
        if ($this->ltiMessage === null) {
            $message = __('Error (%s): ', $this->getCode()) . $this->getMessage();
            $log = __('Error(%s): [key %s] %s "%s"', $this->getCode(), $this->getKey(), get_class($this), $this->getMessage());
            $this->ltiMessage = new LtiErrorMessage($message, $log);
        }
        return $this->ltiMessage;
    }

    public function setLaunchData(LtiLaunchData $data) {
        $this->launchData = $data;
    }

    public function getLaunchData() {
        return $this->launchData;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        if (!isset($this->key)) {
            $this->key = uniqid();
        }

        return $this->key;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return '[key ' . $this->getKey() . '] ' . parent::__toString();
    }
}
