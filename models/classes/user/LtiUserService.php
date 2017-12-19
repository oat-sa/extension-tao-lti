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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 */

namespace oat\taoLti\models\classes\user;

use oat\oatbox\service\ConfigurableService;


/**
 * Lti user service, allow us to find or spawn a lti user based on launch data
 *
 * @access public
 * @author Antoine Antoine, <joel@taotesting.com>
 * @package taoLti
 */
abstract class LtiUserService extends ConfigurableService
{
    const SERVICE_ID = 'taoLti/LtiUserService';

    /**
     * Returns the existing tao User that corresponds to
     * the LTI request or spawns it
     *
     * @param \taoLti_models_classes_LtiLaunchData $launchData
     * @throws \taoLti_models_classes_LtiException
     * @return LtiUser
     */
    public function findOrSpawnUser(\taoLti_models_classes_LtiLaunchData $launchData)
    {
        $taoUser = $this->findUser($launchData);
        if (is_null($taoUser)) {
            $taoUser = $this->spawnUser($launchData);
        }
        return $taoUser;
    }

    /**
     * Searches if this user was already created in TAO
     *
     * @param \taoLti_models_classes_LtiLaunchData $ltiContext
     * @throws \taoLti_models_classes_LtiException
     * @return LtiUser
     */
    public function findUser(\taoLti_models_classes_LtiLaunchData $ltiContext)
    {
        $ltiConsumer = $ltiContext->getLtiConsumer();
        $taoUserId = $this->getUserIdentifier($ltiContext->getUserID(), $ltiConsumer->getUri());
        if (is_null($taoUserId)) {
            return null;
        }

        $ltiUser = new LtiUser($ltiContext, $taoUserId);

        \common_Logger::t("LTI User '" . $ltiUser->getIdentifier() . "' found.");

        $this->updateUser($ltiUser, $ltiContext);

        return $ltiUser;
    }

    /**
     * @param LtiUser $user
     * @param \taoLti_models_classes_LtiLaunchData $ltiContext
     * @return mixed
     */
    abstract protected function updateUser(LtiUser $user, \taoLti_models_classes_LtiLaunchData $ltiContext);

    /**
     * Find the tao user identifier related to a lti user id and a consumer
     * @param string $ltiUserId
     * @param \core_kernel_classes_Resource $consumer
     * @return mixed
     */
    abstract public function getUserIdentifier($ltiUserId, $consumer);

    /**
     * Creates a new LTI User with the absolute minimum of required informations
     *
     * @param \taoLti_models_classes_LtiLaunchData $ltiContext
     * @return LtiUser
     */
    public function spawnUser(\taoLti_models_classes_LtiLaunchData $ltiContext)
    {
        //@TODO create LtiUser after create and save in db.
        $userId = $ltiContext->getUserID();

        $ltiUser = new LtiUser($ltiContext, $userId);

        $this->updateUser($ltiUser, $ltiContext);

        return $ltiUser;
    }


    /**
     * Get the user information from the tao user identifier
     * @param string $taoUserId
     * @return array structure that represent the user
     * ['userUri' => 'taoUserId',
     * 'roles' => ['firstRole', 'secondRole'],
     * 'language' => 'en-US',
     * 'firstname' => 'firstname,
     * 'lastname' => 'lastname',
     * 'email' => 'test@test.com',
     * 'label' => 'label'
     * ]
     */
    abstract public function getUserFromId($taoUserId);
}
