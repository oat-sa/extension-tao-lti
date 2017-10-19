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

use oat\taoLti\models\classes\LtiMessages\LtiErrorMessage;


/**
 * Ontology implementation of the lti user service
 *
 * @access public
 * @author Antoine Robin, <antoine@taotesting.com>
 * @package taoLti
 */
class OntologyLtiUserService extends LtiUserService
{

    const PROPERTY_USER_LTICONSUMER = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#UserConsumer';

    const PROPERTY_USER_LTIKEY = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#UserKey';

    const CLASS_LTI_USER = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#LTIUser';

    const PROPERTY_USER_LAUNCHDATA = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#LaunchData';

    /**
     * Searches if this user was already created in TAO
     *
     * @param \taoLti_models_classes_LtiLaunchData $ltiContext
     * @throws \taoLti_models_classes_LtiException
     * @return LtiUser
     */
    public function findUser(\taoLti_models_classes_LtiLaunchData $ltiContext)
    {
        $class = new \core_kernel_classes_Class(self::CLASS_LTI_USER);
        $instances = $class->searchInstances(array(
            self::PROPERTY_USER_LTIKEY => $ltiContext->getUserID(),
            self::PROPERTY_USER_LTICONSUMER => $ltiContext->getLtiConsumer()
        ), array(
            'like' => false
        ));
        if (count($instances) > 1) {
            throw new \taoLti_models_classes_LtiException(
                'Multiple user accounts found for user key \'' . $ltiContext->getUserID() . '\'',
                LtiErrorMessage::ERROR_SYSTEM_ERROR
            );
        }
        /** @var \core_kernel_classes_Resource $instance */
        if (count($instances) == 1) {
            $instance = current($instances);
            $properties = $instance->getPropertiesValues(
                [
                    PROPERTY_USER_UILG,
                    PROPERTY_USER_FIRSTNAME,
                    PROPERTY_USER_LASTNAME,
                    PROPERTY_USER_MAIL,
                    PROPERTY_USER_ROLES,
                    self::PROPERTY_USER_LAUNCHDATA
                ]
            );

            $roles = $this->determineTaoRoles($ltiContext);
            $lang = current($properties[PROPERTY_USER_UILG]);
            if ($lang instanceof \core_kernel_classes_Resource) {
                $lang = $lang->getOnePropertyValue(new \core_kernel_classes_Property(RDF_VALUE))->literal;
            }
            $ltiUser = new LtiUser($ltiContext, $instance->getUri(), $properties[PROPERTY_USER_ROLES], $lang, (string)current($properties[PROPERTY_USER_FIRSTNAME]), (string)current($properties[PROPERTY_USER_LASTNAME]), (string)current($properties[PROPERTY_USER_MAIL]));

            if($roles !== array(INSTANCE_ROLE_LTI_BASE)){
                $ltiUser->setRoles($roles);
                $instance->editPropertyValues(new \core_kernel_classes_Property(PROPERTY_USER_ROLES), $roles);
            }

            return $ltiUser;
        } else {
            return null;
        }

    }

    /**
     * @inheritdoc
     */
    public function getUserIdentifier($userId, $ltiConsumer)
    {
        $class = new \core_kernel_classes_Class(self::CLASS_LTI_USER);
        $instances = $class->searchInstances(array(
            self::PROPERTY_USER_LTIKEY => $userId,
            self::PROPERTY_USER_LTICONSUMER => $ltiConsumer
        ), array(
            'like' => false
        ));
        if (count($instances) > 1) {
            throw new \taoLti_models_classes_LtiException(
                'Multiple user accounts found for user key \'' . $userId . '\'',
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
     * Creates a new LTI User with the absolute minimum of required informations
     *
     * @param \taoLti_models_classes_LtiLaunchData $ltiContext
     * @return LtiUser
     */
    public function spawnUser(\taoLti_models_classes_LtiLaunchData $ltiContext)
    {
        $class = new \core_kernel_classes_Class(self::CLASS_LTI_USER);
        //$lang = tao_models_classes_LanguageService::singleton()->getLanguageByCode(DEFAULT_LANG);

        $props = array(
            self::PROPERTY_USER_LTIKEY => $ltiContext->getUserID(),
            self::PROPERTY_USER_LTICONSUMER => $ltiContext->getLtiConsumer(),
        );

        $firstname = '';
        $lastname = '';
        $email = '';
        $label = '';
        if ($ltiContext->hasLaunchLanguage()) {
            $launchLanguage = $ltiContext->getLaunchLanguage();
            $uiLanguage = \taoLti_models_classes_LtiUtils::mapCode2InterfaceLanguage($launchLanguage);
        } else {
            $uiLanguage = DEFAULT_LANG;
        }

        $props[PROPERTY_USER_UILG] = $uiLanguage;

        if ($ltiContext->hasVariable(\taoLti_models_classes_LtiLaunchData::LIS_PERSON_NAME_FULL)) {
            $label = $ltiContext->getUserFullName();
            $props[RDFS_LABEL] = $label;
        }

        if ($ltiContext->hasVariable(\taoLti_models_classes_LtiLaunchData::LIS_PERSON_NAME_GIVEN)) {
            $firstname = $ltiContext->getUserGivenName();
            $props[PROPERTY_USER_FIRSTNAME] = $firstname;
        }
        if ($ltiContext->hasVariable(\taoLti_models_classes_LtiLaunchData::LIS_PERSON_NAME_FAMILY)) {
            $lastname = $ltiContext->getUserFamilyName();
            $props[PROPERTY_USER_LASTNAME] = $lastname;
        }
        if ($ltiContext->hasVariable(\taoLti_models_classes_LtiLaunchData::LIS_PERSON_CONTACT_EMAIL_PRIMARY)) {
            $email = $ltiContext->getUserEmail();;
            $props[PROPERTY_USER_MAIL] = $email;
        }

        $roles = $this->determineTaoRoles($ltiContext);
        $props[PROPERTY_USER_ROLES] = $roles;

        $user = $class->createInstanceWithProperties($props);
        \common_Logger::i('added User ' . $user->getLabel());


        $ltiUser = new LtiUser($ltiContext, $user->getUri(), $roles, $uiLanguage, $firstname, $lastname, $email, $label);

        return $ltiUser;
    }

}
