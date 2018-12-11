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

use oat\generis\model\OntologyRdfs;
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
    protected function updateUser(LtiUserInterface $user, LtiLaunchData $ltiContext)
    {
        $technicalId = self::LTI_USER . $ltiContext->getUserID() . $ltiContext->getLtiConsumer()->getUri();

        if (empty($user->getIdentifier())) {
            $user->setIdentifier($technicalId);
        }

        $taoUserId = $user->getIdentifier();

        $data = $user->jsonSerialize();
        $data[self::PROPERTY_USER_LTIKEY] = $ltiContext->getUserID();
        $data[self::PROPERTY_USER_LTICONSUMER] = $ltiContext->getLtiConsumer()->getOnePropertyValue(
            new \core_kernel_classes_Property(OntologyRdfs::RDFS_LABEL)
        )->literal;

        $this->getPersistence()->set($technicalId, json_encode($data));
        $this->getPersistence()->set(self::LTI_USER_LOOKUP . $taoUserId, $technicalId);
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
        $decodedData = json_decode($data, true);
        if (isset($decodedData[LtiUser::USER_IDENTIFIER])) {
            return $decodedData[LtiUser::USER_IDENTIFIER];
        }

        return self::LTI_USER . $ltiUserId . $consumer;
    }


    /**
     * @inheritdoc
     */
    public function getUserDataFromId($taoUserId)
    {
        $id = $this->getPersistence()->get(self::LTI_USER_LOOKUP . $taoUserId);
        if ($id !== false) {
            $data = $this->getPersistence()->get($id);
        } else {
            $data = $this->getPersistence()->get($taoUserId);
            if ($data === false) {
                return null;
            }
            $this->getPersistence()->set(self::LTI_USER_LOOKUP . $taoUserId, $taoUserId);
        }

        return json_decode($data,true);
    }
}
