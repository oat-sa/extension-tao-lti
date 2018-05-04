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

use oat\taoLti\models\classes\LtiLaunchData;

/**
 * Key value implementation of the lti user service
 *
 * @access public
 * @author Antoine Robin, <antoine@taotesting.com>
 * @package taoLti
 */
class KvLtiUserService extends LtiUserService
{
    const OPTION_PERSISTENCE = 'persistence';

    const LTI_USER = 'lti_ku_';

    const LTI_USER_LOOKUP = 'lti_ku_lkp_';

    /**
     * @var \common_persistence_KeyValuePersistence
     */
    private $persistence;

    /**
     * @return \common_persistence_KeyValuePersistence|\common_persistence_Persistence
     */
    protected function getPersistence()
    {
        if (is_null($this->persistence)) {
            $persistenceOption = $this->getOption(self::OPTION_PERSISTENCE);
            $this->persistence = (is_object($persistenceOption))
                ? $persistenceOption
                : \common_persistence_KeyValuePersistence::getPersistence($persistenceOption);
        }
        return $this->persistence;
    }

    /**
     * @param LtiUser $user
     * @param LtiLaunchData $ltiContext
     * @return mixed|void
     * @throws \common_Exception
     * @throws \oat\taoLti\models\classes\LtiVariableMissingException
     */
    protected function updateUser(LtiUser $user, LtiLaunchData $ltiContext)
    {
        $technicalId = self::LTI_USER . $ltiContext->getUserID() . $ltiContext->getLtiConsumer()->getUri();

        if (empty($user->getIdentifier())) {
            $user->setIdentifier($technicalId);
        }

        $taoUserId = $user->getIdentifier();

        $this->getPersistence()->set(
            self::LTI_USER . $ltiContext->getUserID() . $ltiContext->getLtiConsumer()->getUri(),
            json_encode($user)
        );

        if (!$this->getPersistence()->exists(self::LTI_USER_LOOKUP . $taoUserId)) {
            $this->getPersistence()->set(self::LTI_USER_LOOKUP . $taoUserId, $technicalId);
        }
    }

    /**
     * @inheritdoc
     */
    public function getUserIdentifier($ltiUserId, $consumer)
    {
        $data = $this->getPersistence()->get(self::LTI_USER . $ltiUserId . $consumer);
        if ($data === false) {
            return null;
        }

        return self::LTI_USER . $ltiUserId . $consumer;
    }


    /**
     * @inheritdoc
     */
    public function getUserDataFromId($taoUserId)
    {
        if (!$this->getPersistence()->exists(self::LTI_USER_LOOKUP . $taoUserId)) {
            if ($this->getPersistence()->exists($taoUserId)) {
                $this->getPersistence()->set(self::LTI_USER_LOOKUP . $taoUserId, $taoUserId);
                $data = $this->getPersistence()->get($taoUserId);
            } else {
                return false;
            }
        } else {
            $id = $this->getPersistence()->get(self::LTI_USER_LOOKUP . $taoUserId);
            $data = $this->getPersistence()->get($id);
        }

        return json_decode($data,true);
    }
}
