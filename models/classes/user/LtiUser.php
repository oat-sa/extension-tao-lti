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

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;


/**
 * Authentication adapter interface to be implemented by authentication methodes
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package taoLti
 */
class LtiUser extends \common_user_User implements ServiceLocatorAwareInterface, \JsonSerializable
{
    use ServiceLocatorAwareTrait;

    /**
     * Data with which this session was launched
     * @var \taoLti_models_classes_LtiLaunchData
     */
    private $ltiLaunchData;

    /**
     * Local represenation of user
     * @var \core_kernel_classes_Resource
     */
    private $userUri;

    /**
     * Cache of the current user's lti roles
     * @var array
     */
    protected $roles;

    private $language;

    private $firstname;

    private $lastname;

    private $email;

    private $label;

    /**
     * Currently used UI languages.
     *
     * @var array
     */
    protected $uiLanguage;

    public function __construct($launchData, $userUri)
    {
        $this->ltiLaunchData = $launchData;
        $this->userUri = $userUri;
        $this->setRoles($this->determineTaoRoles($launchData));


        $firstname = '';
        $lastname = '';
        $email = '';
        $label = '';

        if ($launchData->hasLaunchLanguage()) {
            $launchLanguage = $launchData->getLaunchLanguage();
            $language = \taoLti_models_classes_LtiUtils::mapCode2InterfaceLanguage($launchLanguage);
        } else {
            $language = DEFAULT_LANG;
        }

        $languageResource = \tao_models_classes_LanguageService::singleton()->getLanguageByCode($language);

        if ($launchData->hasVariable(\taoLti_models_classes_LtiLaunchData::LIS_PERSON_NAME_FULL)) {
            $label = $launchData->getUserFullName();
        }

        if ($launchData->hasVariable(\taoLti_models_classes_LtiLaunchData::LIS_PERSON_NAME_GIVEN)) {
            $firstname = $launchData->getUserGivenName();
        }
        if ($launchData->hasVariable(\taoLti_models_classes_LtiLaunchData::LIS_PERSON_NAME_FAMILY)) {
            $lastname = $launchData->getUserFamilyName();
        }
        if ($launchData->hasVariable(\taoLti_models_classes_LtiLaunchData::LIS_PERSON_CONTACT_EMAIL_PRIMARY)) {
            $email = $launchData->getUserEmail();;
        }

        $this->language = $languageResource;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->email = $email;
        $this->label = $label;
    }

    public function setRoles($roles)
    {
        $newRoles = array_map(function($value){
            return ($value instanceof \core_kernel_classes_Resource) ? $value->getUri() : $value;
        }, $roles);

        $this->roles = $newRoles;
    }


    /**
     * (non-PHPdoc)
     * @see common_user_User::getIdentifier()
     */
    public function getIdentifier()
    {
        return $this->userUri;
    }


    public function getLaunchData()
    {
        return $this->ltiLaunchData;
    }

    /**
     * (non-PHPdoc)
     * @see common_user_User::getPropertyValues()
     */
    public function getPropertyValues($property)
    {
        $returnValue = null;
        switch ($property) {
            case PROPERTY_USER_DEFLG :
                $returnValue = array(DEFAULT_LANG);
                break;
            case PROPERTY_USER_UILG :
                if($this->language instanceof \core_kernel_classes_Resource){
                    $code = \tao_models_classes_LanguageService::singleton()->getCode($this->language);
                    $returnValue = array($code);
                } else{
                    $returnValue = array($this->language);
                }
                break;
            case PROPERTY_USER_ROLES :
                $returnValue = $this->roles;
                break;
            case PROPERTY_USER_FIRSTNAME :
                $returnValue = [$this->firstname];
                break;
            case PROPERTY_USER_LASTNAME :
                $returnValue = [$this->lastname];
                break;
            case RDFS_LABEL :
                $returnValue = [$this->label];
                break;
            default:
                \common_Logger::d('Unkown property ' . $property . ' requested from ' . __CLASS__);
                $returnValue = array();
        }
        return $returnValue;
    }


    /**
     * (non-PHPdoc)
     * @see common_user_User::refresh()
     */
    public function refresh()
    {
        // nothing to do
    }


    public function jsonSerialize()
    {
        return [
            'userUri' => $this->userUri,
            'roles' => $this->roles,
            'language' => $this->language->getUri(),
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'label' => $this->label,
        ];
    }

    /**
     * Getting tao roles associated to lti roles
     * @param \taoLti_models_classes_LtiLaunchData $ltiLaunchData
     * @return array
     */
    protected function determineTaoRoles(\taoLti_models_classes_LtiLaunchData $ltiLaunchData)
    {
        $roles = array();
        if ($ltiLaunchData->hasVariable(\taoLti_models_classes_LtiLaunchData::ROLES)) {
            foreach ($ltiLaunchData->getUserRoles() as $role) {
                $taoRole = \taoLti_models_classes_LtiUtils::mapLTIRole2TaoRole($role);
                if (!is_null($taoRole)) {
                    $roles[] = $taoRole;
                    foreach (\core_kernel_users_Service::singleton()->getIncludedRoles(new \core_kernel_classes_Resource($taoRole)) as $includedRole) {
                        $roles[] = $includedRole->getUri();
                    }
                }
            }
            $roles = array_unique($roles);
        } else {
            return array(INSTANCE_ROLE_LTI_BASE);
        }
        return $roles;
    }

}
