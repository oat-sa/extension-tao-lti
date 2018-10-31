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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA ;
 */

namespace oat\taoLti\models\classes\user;


use oat\generis\model\GenerisRdf;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\service\exception\InvalidService;

/**
 * Class LtiUserHelper
 * @package oat\taoLti\models\classes\user
 */
class LtiUserHelper extends ConfigurableService
{
    const SERVICE_ID = 'taoLti/LtiUserHelper';

    const OPTION_LTI_USER_SERVICE = 'ltiUserService';

    /**
     * LtiUserHelper constructor.
     * @param array $options
     * @throws \common_exception_Error
     */
    public function __construct(array $options)
    {
        parent::__construct($options);

        if (!$this->hasOption(static::OPTION_LTI_USER_SERVICE)) {
            throw new \common_exception_Error('Invalid Option provided: ' . static::OPTION_LTI_USER_SERVICE);
        }
    }

    /**
     * @return LtiUserService
     * @throws InvalidService
     */
    private function getLtiUserService()
    {
        $ltiUserService = $this->getServiceLocator()->get($this->getOption(self::OPTION_LTI_USER_SERVICE));

        if (!$ltiUserService instanceof LtiUserService) {
            throw new InvalidService('Service must implements ' . LtiUserService::class);
        }

        return $ltiUserService;
    }

    /**
     * @param string $userId
     *
     * @return array
     */
    public function getLtiUserData($userId)
    {
        $data = [];
        try {
            $data = $this->getLtiUserService()->getUserDataFromId($userId);
        } catch (InvalidService $e) {
            $this->getLogger()->error('Invalid service provided.');
        }

        return $data;
    }

    /**
     * @param array $userData
     * @return string
     */
    public function getUserName(array $userData)
    {
        $firstName = $this->getFirstName($userData);
        $lastName = $this->getLastName($userData);

        $userName = trim($firstName . ' ' . $lastName);

        return $userName;
    }

    /**
     * @param array $userData
     * @return mixed|string
     */
    public function getLastName(array $userData)
    {
        return isset($userData[GenerisRdf::PROPERTY_USER_LASTNAME])
            ? $userData[GenerisRdf::PROPERTY_USER_LASTNAME]
            : '';
    }

    /**
     * @param array $userData
     * @return string
     */
    public function getFirstName(array $userData)
    {
        return isset($userData[GenerisRdf::PROPERTY_USER_FIRSTNAME])
            ? $userData[GenerisRdf::PROPERTY_USER_FIRSTNAME]
            : '';
    }
}
