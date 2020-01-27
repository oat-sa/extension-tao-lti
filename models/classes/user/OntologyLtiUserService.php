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

use oat\generis\model\GenerisRdf;
use oat\generis\model\OntologyRdfs;
use oat\taoLti\models\classes\LtiException;
use oat\taoLti\models\classes\LtiLaunchData;
use oat\taoLti\models\classes\LtiMessages\LtiErrorMessage;
use oat\generis\model\data\ModelManager;
use oat\taoLti\models\classes\LtiVariableMissingException;

/**
 * Ontology implementation of the lti user service
 *
 * @access public
 * @author Antoine Robin, <antoine@taotesting.com>
 * @package taoLti
 */
class OntologyLtiUserService extends LtiUserService
{
    const CLASS_LTI_USER = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#LTIUser';

    /**
     * @deprecated no longer serves any purpose
     */
    const OPTION_TRANSACTION_SAFE = 'transaction-safe';

    /**
     * @deprecated no longer serves any purpose
     */
    const OPTION_TRANSACTION_SAFE_RETRY = 'transaction-safe-retry';

    /**
     * @TODO TT-273 split method in separate action (create and update)
     * @param LtiUser $user
     * @param LtiLaunchData $ltiContext
     * @return mixed|void
     * @throws \common_exception_Error
     * @throws \common_exception_InvalidArgumentType
     * @throws LtiVariableMissingException
     */
    protected function updateUser(LtiUserInterface $user, LtiLaunchData $ltiContext)
    {
        $userResource = new \core_kernel_classes_Resource($user->getIdentifier());

        if ($userResource->exists()) {
            $properties = $userResource->getPropertiesValues([
                GenerisRdf::PROPERTY_USER_UILG,
                GenerisRdf::PROPERTY_USER_FIRSTNAME,
                GenerisRdf::PROPERTY_USER_LASTNAME,
                GenerisRdf::PROPERTY_USER_MAIL,
                GenerisRdf::PROPERTY_USER_ROLES,
            ]);

            $hasUpdates = false;
            foreach ($properties as $key => $values) {
                if ($values != $user->getPropertyValues($key)) {
                    $userResource->editPropertyValues(new \core_kernel_classes_Property($key), $user->getPropertyValues($key));
                    $hasUpdates = true;
                }
            }

            if ($hasUpdates) {
                $this->userUpdatedEvent($userResource->getUri());
            }
        } else {
            $class = new \core_kernel_classes_Class(self::CLASS_LTI_USER);

            $props = [
                self::PROPERTY_USER_LTIKEY => $ltiContext->getUserID(),
                self::PROPERTY_USER_LTICONSUMER => $ltiContext->getLtiConsumer(),
                GenerisRdf::PROPERTY_USER_UILG => $user->getPropertyValues(GenerisRdf::PROPERTY_USER_UILG),
                OntologyRdfs::RDFS_LABEL => $user->getPropertyValues(OntologyRdfs::RDFS_LABEL),
                GenerisRdf::PROPERTY_USER_FIRSTNAME => $user->getPropertyValues(GenerisRdf::PROPERTY_USER_FIRSTNAME),
                GenerisRdf::PROPERTY_USER_LASTNAME => $user->getPropertyValues(GenerisRdf::PROPERTY_USER_LASTNAME),
                GenerisRdf::PROPERTY_USER_MAIL => $user->getPropertyValues(GenerisRdf::PROPERTY_USER_MAIL),
                GenerisRdf::PROPERTY_USER_ROLES => $user->getPropertyValues(GenerisRdf::PROPERTY_USER_ROLES),
            ];

            $userResource = $class->createInstanceWithProperties($props);
            $this->logInfo(
                sprintf(
                    'added User %s, LTI user Id: %s, LTI consumer %s',
                    $userResource->getLabel(),
                    $props[self::PROPERTY_USER_LTIKEY],
                    $props[self::PROPERTY_USER_LTICONSUMER]
                )
            );
            $this->userCreatedEvent($userResource->getUri());
        }
        $user->setIdentifier($userResource->getUri());
    }


    /**
     * @inheritdoc
     */
    public function getUserIdentifier($ltiUserId, $ltiConsumer)
    {
        $class = new \core_kernel_classes_Class(self::CLASS_LTI_USER);
        $instances = $class->searchInstances([
            self::PROPERTY_USER_LTIKEY => $ltiUserId,
            self::PROPERTY_USER_LTICONSUMER => $ltiConsumer
        ], [
            'like' => false
        ]);
        if (count($instances) > 1) {
            throw new LtiException(
                'Multiple user accounts found for user key \'' . $ltiUserId . '\'',
                LtiErrorMessage::ERROR_SYSTEM_ERROR
            );
        }
        /** @var \core_kernel_classes_Resource $instance */
        if (count($instances) == 1) {
            $instance = current($instances);
            return $instance->getUri();
        } else {
            return null;
        }
    }

    /**
     * @inheritdoc
     */
    public function getUserDataFromId($taoUserId)
    {
        $user = new \core_kernel_classes_Resource($taoUserId);
        if ($user->exists()) {
            $properties = $user->getPropertiesValues([
                GenerisRdf::PROPERTY_USER_UILG,
                OntologyRdfs::RDFS_LABEL,
                GenerisRdf::PROPERTY_USER_FIRSTNAME,
                GenerisRdf::PROPERTY_USER_LASTNAME,
                GenerisRdf::PROPERTY_USER_MAIL,
                GenerisRdf::PROPERTY_USER_ROLES
            ]);

            $userData = [];
            foreach ($properties as $key => $values) {
                if (count($values) > 1) {
                    foreach ($values as $value) {
                        $userData[$key][] = ($value instanceof \core_kernel_classes_Resource) ? $value->getUri() : (string) $value;
                    }
                } else {
                    $value = current($values);
                    $userData[$key] = ($value instanceof \core_kernel_classes_Resource) ? $value->getUri() : (string) $value;
                }
            }

            return $userData;
        }

        return null;
    }
}
