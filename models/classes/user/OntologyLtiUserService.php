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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *               
 * 
 */

namespace oat\taoLti\models\classes\user;

use oat\taoLti\models\classes\LtiMessages\LtiErrorMessage;


/**
 * Authentication adapter interface to be implemented by authentication methodes
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package taoLti
 
 */
class OntologyLtiUserService extends LtiUserService
{

    const PROPERTY_USER_LTICONSUMER = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#UserConsumer';

    const PROPERTY_USER_LTIKEY = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#UserKey';

    const CLASS_LTI_USER = 'http://www.tao.lu/Ontologies/TAOLTI.rdf#LTIConsumer';

    /**
     * Searches if this user was already created in TAO
     *
     * @param \taoLti_models_classes_LtiLaunchData $ltiContext
     * @throws \taoLti_models_classes_LtiException
     * @return LtiUser
     */
    public function findUser($userId, $ltiConsumer, $launchdata = null) {
        $class = new \core_kernel_classes_Class(self::CLASS_LTI_USER);
        $instances = $class->searchInstances(array(
            self::PROPERTY_USER_LTIKEY		=> $userId,
            self::PROPERTY_USER_LTICONSUMER	=> $ltiConsumer
        ), array(
            'like'	=> false
        ));
        if (count($instances) > 1) {
            throw new \taoLti_models_classes_LtiException(
                'Multiple user accounts found for user key \''.$userId.'\'',
                LtiErrorMessage::ERROR_SYSTEM_ERROR
            );
        }
        /** @var \core_kernel_classes_Resource $instance */
        if(count($instances) == 1){
            $instance = current($instances);
            $properties = $instance->getPropertiesValues(
                [
                    PROPERTY_USER_UILG,
                    PROPERTY_USER_FIRSTNAME,
                    PROPERTY_USER_LASTNAME,
                    PROPERTY_USER_MAIL,
                    PROPERTY_USER_ROLES
                ]
            );


            $roles = $this->getRoles($properties[PROPERTY_USER_ROLES]);
            return new LtiUser($launchdata, $instance->getUri(), $roles, (string)$properties[PROPERTY_USER_UILG][0], (string)$properties[PROPERTY_USER_FIRSTNAME][0], (string)$properties[PROPERTY_USER_LASTNAME][0], (string)$properties[PROPERTY_USER_MAIL][0]);

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
    public function spawnUser(\taoLti_models_classes_LtiLaunchData $ltiContext) {
        $class = new \core_kernel_classes_Class(self::CLASS_LTI_USER);
        //$lang = tao_models_classes_LanguageService::singleton()->getLanguageByCode(DEFAULT_LANG);

        $props = array(
            self::PROPERTY_USER_LTIKEY		=> $ltiContext->getUserID(),
            self::PROPERTY_USER_LTICONSUMER	=> \taoLti_models_classes_LtiService::singleton()->getLtiConsumerResource($ltiContext),
            /*
            PROPERTY_USER_UILG			=> $lang,
            PROPERTY_USER_DEFLG			=> $lang,
            */

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
        \common_Logger::i('added User '.$user->getLabel());


        $ltiUser = new LtiUser($ltiContext, $user->getUri(), $roles, $uiLanguage, $firstname, $lastname, $email, $label) ;

        return $ltiUser;
    }

}
