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
 *
 */

namespace oat\taoLti\models\classes\user;

use oat\oatbox\user\User;

/**
 * Class UserService
 * @package oat\taoLti\models\classes\user
 */
class UserService extends \tao_models_classes_UserService
{
    /**
     * @param $userId
     * @return User|array
     * @throws \common_exception_Error
     */
    public function getUserById($userId)
    {
        $user = parent::getUserById($userId);
        if (!$user || !$this->getResource($user->getIdentifier())->exists()) {
            /** @var LtiUserService $ltiUserService */
            $ltiUserService = $this->getServiceLocator()->get(LtiUserService::SERVICE_ID);
            $userData = $ltiUserService->getUserDataFromId($userId);
            if ($userData) {
                $user = new KvLtiUser($userData);
            }
        }
        return $user;
    }
}
