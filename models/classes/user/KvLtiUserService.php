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

    protected function updateUser(LtiUser $user, \taoLti_models_classes_LtiLaunchData $ltiContext)
    {
        $user->setIdentifier(self::LTI_USER . $ltiContext->getUserID() . $ltiContext->getLtiConsumer()->getUri());
        $this->getPersistence()->set(self::LTI_USER . $ltiContext->getUserID() . $ltiContext->getLtiConsumer()->getUri(), json_encode($user));
    }

    /**
     * @inheritdoc
     */
    public function getUserIdentifier($userId, $consumer)
    {
        $data = $this->getPersistence()->get(self::LTI_USER . $userId . $consumer);
        if ($data === false) {
            return null;
        }
        return self::LTI_USER . $userId . $consumer;
    }

    public function getUserFromId($userId)
    {
        $data = $this->getPersistence()->get($userId);

        return json_decode($data,true);
    }


}
